<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use setasign\Fpdi\Fpdi;
use Smalot\PdfParser\Parser;
use Webklex\PDFMerger\Facades\PDFMergerFacade;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentApprovedMail;

class ReviewerController extends Controller
{
public function index()
{
    // Kita panggil langsung pakai DB facade agar tidak lewat Model (Anti-Zonk)
    $approvalIds = \DB::table('document_approvals')
                    ->where('user_id', auth()->id())
                    ->where('status', 'current')
                    ->pluck('document_id')
                    ->toArray();

    // Kita ambil datanya, pastikan ID-nya cocok
    $documents = \App\Models\Document::whereIn('id', $approvalIds)
                    ->latest()
                    ->get();

    // Hapus tanda // di bawah ini untuk tes terakhir:
    // return "Jumlah dokumen: " . count($documents) . " | ID Anda: " . auth()->id();

    return view('reviewer.dashboard', compact('documents'));
}

    public function show($id)
    {
        $document = Document::findOrFail($id);
        $pathToShow = $document->file_final ?? $document->file_preview ?? $document->file_lp;
        return view('reviewer.show', compact('document', 'pathToShow'));
    }

    public function approve(Request $request, $id)
    {
        $statusMsg = 'Dokumen berhasil diproses.';
        $usersToNotify = [];
        $documentId = $id;

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id, &$statusMsg, &$usersToNotify) {
                $document = Document::where('id', $id)->lockForUpdate()->firstOrFail();
                $user = Auth::user();
                \Log::info("e-QMS DEBUG USER INSIDE CONTROLLER: ID=" . ($user ? $user->id : 'NULL') . ", username=" . ($user ? $user->username : 'NULL'));

                // 1. TENTUKAN SUMBER FILE (LOGIKA ESTAFET)
                // Selalu ambil dari file_preview karena di sana tersimpan hasil ttd orang sebelumnya
                $sourcePath = storage_path('app/public/' . ($document->file_preview ?? $document->file_lp));

                // 2. TENTUKAN TEKS JANGKAR DARI DOCUMENT APPROVAL SIGNATURE_SLOT
                $currentApproval = \App\Models\DocumentApproval::where('document_id', $document->id)
                    ->where('user_id', $user->id)
                    ->where('status', 'current')
                    ->lockForUpdate()
                    ->first();

                if (!$currentApproval) {
                    throw new \Exception('Data antrean approval untuk dokumen ini tidak ditemukan atau sudah diproses.');
                }

                $slotTag = $currentApproval->signature_slot ?? null;

                if (!$slotTag) {
                    $slotMeta = \App\Models\DocumentApproval::getSlotAndStageForUser($user);
                    $slotTag = $slotMeta['signature_slot'];
                }

                $targetText = str_starts_with($slotTag, '[') ? $slotTag : '[' . $slotTag . ']';

                // 3. PROSES PENCARIAN KOORDINAT OTOMATIS & FALLBACK QPDF
                $tempFileToClean = null;
                $parsePath = $sourcePath;

                // Cek kompatibilitas FPDI terlebih dahulu. Jika gagal (misal unsupported compression), jalankan QPDF fallback.
                try {
                    $pdfCheck = new \setasign\Fpdi\Fpdi();
                    $pdfCheck->setSourceFile($sourcePath);
                } catch (\Throwable $e) {
                    $tempFileToClean = $this->normalizePdfWithQpdf($sourcePath);
                    if ($tempFileToClean) {
                        $parsePath = $tempFileToClean;
                    } else {
                        throw new \Exception('Gagal memproses PDF: Format kompresi PDF tidak didukung dan QPDF gagal menormalisasi file.');
                    }
                }

                // First, update current approval record in DB to record single-source-of-truth processed_at timestamp
                $nowTime = now();
                if ($currentApproval) {
                    $currentApproval->update([
                        'status'       => 'approved',
                        'notes'        => $request->notes ?? 'Setuju tanpa catatan.',
                        'processed_at' => $nowTime
                    ]);
                }

                $processedAtTime = $currentApproval->processed_at ?? $nowTime;
                $carbonDate = \Carbon\Carbon::parse($processedAtTime)->timezone('Asia/Jakarta');
                $dateLine1 = $carbonDate->format('d/m/Y');
                $dateLine2 = $carbonDate->format('H:i \W\I\B');
                $masterDateStr = strtoupper($carbonDate->locale('en')->format('d M Y'));

                $isFinalStage = ($currentApproval && ($currentApproval->stage === 'final'));

                try {
                    $hasDynamicCoords = ($currentApproval && 
                                         $currentApproval->signature_page !== null && 
                                         $currentApproval->signature_x !== null && 
                                         $currentApproval->signature_y !== null);

                    if ($hasDynamicCoords) {
                        $coordinates = [
                            'x' => (float)$currentApproval->signature_x,
                            'y' => (float)$currentApproval->signature_y
                        ];
                    } else {
                        // Legacy fallback for historical documents that have [sigXX] markers in the LP
                        $parser = new \Smalot\PdfParser\Parser();
                        $pdfParsed = $parser->parseFile($parsePath);
                        $coordinates = $this->findTextCoordinates($pdfParsed, $targetText);

                        if ($coordinates === null) {
                            \Log::warning("e-QMS Legacy Stamp: marker '{$targetText}' not found in LP. Doc ID {$document->id}, User ID {$user->id}. Approval rejected.");
                            throw new \Exception('Posisi tanda tangan pada Lembar Pengesahan tidak dapat ditemukan. Dokumen tidak diproses.');
                        }

                        // 🔥 SAVE THE RESOLVED COORDINATES TO THE DATABASE FOR THE SINGLE-SOURCE-OF-TRUTH!
                        if ($currentApproval) {
                            $currentApproval->update([
                                'signature_page' => $currentApproval->signature_page ?? 1,
                                'signature_x'    => $coordinates['x'],
                                'signature_y'    => $coordinates['y']
                            ]);
                        }
                    }

                    // 4. PROSES PENEMPELAN STEMPEL (FPDI)
                    $pdf = new \setasign\Fpdi\Fpdi();
                    $pageCount = $pdf->setSourceFile($parsePath);
                    
                    // Only draw the current reviewer's stamp during approval (estafet-chain and updateRevision take care of others)
                    $allApproved = collect([$currentApproval]);

                    $stampsToDraw = [];
                    foreach ($allApproved as $appItem) {
                        $itemHasCoords = ($appItem->signature_page !== null && 
                                          $appItem->signature_x !== null && 
                                          $appItem->signature_y !== null);

                        $x = null;
                        $y = null;
                        $page = $appItem->signature_page ?? 1;

                        if ($itemHasCoords) {
                            $x = (float)$appItem->signature_x;
                            $y = (float)$appItem->signature_y;
                        } else {
                            // Coba resolve dinamis jika marker masih bisa terbaca (fallback legacy)
                            try {
                                $slotT = $appItem->signature_slot ?? 'sig01';
                                $tText = str_starts_with($slotT, '[') ? $slotT : '[' . $slotT . ']';
                                
                                $parserObj = new \Smalot\PdfParser\Parser();
                                $pdfParsedObj = $parserObj->parseFile($parsePath);
                                $coords = $this->findTextCoordinates($pdfParsedObj, $tText);
                                
                                if ($coords !== null) {
                                    $x = $coords['x'];
                                    $y = $coords['y'];
                                    
                                    // Simpan hasil resolusi koordinat ke DB agar tersimpan permanen
                                    $appItem->update([
                                        'signature_page' => $page,
                                        'signature_x'    => $x,
                                        'signature_y'    => $y
                                    ]);
                                }
                            } catch (\Throwable $ex) {
                                \Log::warning("e-QMS Dynamic Redraw: Gagal mendeteksi marker untuk User ID {$appItem->user_id} di Doc ID {$document->id}: " . $ex->getMessage());
                            }
                        }

                        if ($x !== null && $y !== null) {
                            $stampsToDraw[] = [
                                'x'            => $x,
                                'y'            => $y,
                                'page'         => $page,
                                'username'     => $appItem->user->username ?? 'user',
                                'processed_at' => $appItem->processed_at ?? $nowTime,
                                'stage'        => $appItem->stage
                            ];
                        }
                    }

                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $templateId = $pdf->importPage($pageNo);
                        $size = $pdf->getTemplateSize($templateId);
                        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $pdf->useTemplate($templateId);

                        foreach ($stampsToDraw as $stamp) {
                            $stampTargetPage = ($pageCount > 1) ? ($stamp['page'] + 1) : $stamp['page'];
                            if ($pageNo == $stampTargetPage) {
                                $carbonDate = \Carbon\Carbon::parse($stamp['processed_at'])->timezone('Asia/Jakarta');
                                $dLine1 = $carbonDate->format('d/m/Y');
                                $dLine2 = $carbonDate->format('H:i \W\I\B');
                                
                                $this->drawDigitalStamp($pdf, $stamp['x'], $stamp['y'], $stamp['username']);
                                $this->drawDateStamp($pdf, $size['width'], $stamp['y'], $dLine1, $dLine2);
                                
                                if ($stamp['stage'] === 'final') {
                                    $noteAnchorY = null;
                                    try {
                                        $anchorParser = new \Smalot\PdfParser\Parser();
                                        $anchorFile = storage_path('app/public/' . ($document->file_lp ?? ''));
                                        if (!file_exists($anchorFile)) {
                                            $anchorFile = $parsePath;
                                        }
                                        $pdfParsedForAnchor = $anchorParser->parseFile($anchorFile);
                                        $noteAnchorY = $this->findKeteranganAnchorY($pdfParsedForAnchor, $size['height'] ?? 297.0);
                                    } catch (\Throwable $e) {
                                        $noteAnchorY = null;
                                    }
                                    $masterDateStr = strtoupper($carbonDate->locale('en')->format('d M Y'));
                                    $this->drawMasterDocumentStamp($pdf, $size['width'], $size['height'], $stamp['y'], $masterDateStr, $noteAnchorY);
                                }
                            }
                        }
                    }

                    // 5. SIMPAN FILE HASIL TANDA TANGAN
                    $fileName = 'ESTAFET_' . time() . '_' . $user->username . '.pdf';
                    $finalPath = 'documents/final/' . $fileName;
                    
                    if (!file_exists(storage_path('app/public/documents/final'))) {
                        mkdir(storage_path('app/public/documents/final'), 0777, true);
                    }
                    
                    $pdf->Output('F', storage_path('app/public/' . $finalPath));
                } finally {
                    if ($tempFileToClean && file_exists($tempFileToClean)) {
                        @unlink($tempFileToClean);
                    }
                }

                // ================================================================
                // 6. LOGIKA TRANSISI 3 STAGE APPROVAL (CREATOR -> REVIEWERS -> FINAL)
                // ================================================================
                if ($currentApproval) {
                    $stage = $currentApproval->stage ?? 'reviewer';

                    if ($stage === 'creator') {
                        // STAGE 1: Creator approved -> Aktifkan SEMUA reviewer secara paralel (status = 'current')
                        $pendingReviewerApprovals = \App\Models\DocumentApproval::where('document_id', $document->id)
                            ->where('stage', 'reviewer')
                            ->where('status', 'pending')
                            ->with('user')
                            ->get();

                        if ($pendingReviewerApprovals->count() > 0) {
                            foreach ($pendingReviewerApprovals as $appItem) {
                                if ($appItem->user) {
                                    $usersToNotify[] = $appItem->user;
                                }
                            }

                            \App\Models\DocumentApproval::where('document_id', $document->id)
                                ->where('stage', 'reviewer')
                                ->where('status', 'pending')
                                ->update(['status' => 'current']);

                            $document->update([
                                'file_preview' => $finalPath,
                                'status'       => 'waiting'
                            ]);

                            $statusMsg = 'Dokumen berhasil disetujui Pembuat Dokumen dan diteruskan ke seluruh Reviewer.';
                        } else {
                            // Jika tidak ada reviewer, langsung aktifkan stage final
                            $pendingFinalApprovals = \App\Models\DocumentApproval::where('document_id', $document->id)
                                ->where('stage', 'final')
                                ->where('status', 'pending')
                                ->with('user')
                                ->get();

                            foreach ($pendingFinalApprovals as $appItem) {
                                if ($appItem->user) {
                                    $usersToNotify[] = $appItem->user;
                                }
                            }

                            \App\Models\DocumentApproval::where('document_id', $document->id)
                                ->where('stage', 'final')
                                ->where('status', 'pending')
                                ->update(['status' => 'current']);

                            $document->update([
                                'file_preview' => $finalPath,
                                'status'       => 'waiting'
                            ]);

                            $statusMsg = 'Dokumen berhasil disetujui Pembuat Dokumen dan diteruskan ke Penandatangan Final.';
                        }
                    } elseif ($stage === 'reviewer') {
                        // STAGE 2: Reviewer approved -> Cek apakah SEMUA reviewer di dokumen ini sudah 'approved'
                        $pendingReviewers = \App\Models\DocumentApproval::where('document_id', $document->id)
                            ->where('stage', 'reviewer')
                            ->where('status', '!=', 'approved')
                            ->count();

                        if ($pendingReviewers === 0) {
                            // Semua reviewer yang ada di dokumen ini sudah 'approved' -> Transisikan Final Approver ke 'current'
                            $pendingFinalApprovals = \App\Models\DocumentApproval::where('document_id', $document->id)
                                ->where('stage', 'final')
                                ->where('status', 'pending')
                                ->with('user')
                                ->get();

                            foreach ($pendingFinalApprovals as $appItem) {
                                if ($appItem->user) {
                                    $usersToNotify[] = $appItem->user;
                                }
                            }

                            \App\Models\DocumentApproval::where('document_id', $document->id)
                                ->where('stage', 'final')
                                ->where('status', 'pending')
                                ->update(['status' => 'current']);

                            $document->update([
                                'file_preview' => $finalPath,
                                'status'       => 'waiting'
                            ]);

                            $statusMsg = 'Seluruh Reviewer telah menyetujui dokumen. Dokumen diteruskan ke Penandatangan Final.';
                        } else {
                            // Masih ada reviewer lain yang belum selesai
                            $document->update([
                                'file_preview' => $finalPath,
                                'status'       => 'waiting'
                            ]);

                            $statusMsg = 'Dokumen berhasil disetujui. Menunggu persetujuan Reviewer lainnya.';
                        }
                    } elseif ($stage === 'final') {
                        // STAGE 3: Final Approver approved -> Dokumen ACTIVE & Masuk E-Library
                        $document->update([
                            'file_final'   => $finalPath,
                            'file_preview' => $finalPath,
                            'status'       => 'active',
                        ]);

                        $bu = $document->department;
                        $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];

                        if (in_array(strtoupper($bu), $supportDepts)) {
                            $category     = 'support';
                            $divisionName = 'Support';
                            $companyName  = 'PT. CAHAYA PERDANA TRANSALAM';
                        } else {
                            $category = 'divisi';
                            if (in_array($bu, ['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'INMAR (CNGM)'])) {
                                $divisionName = 'RETAIL';
                            } elseif (in_array($bu, ['CPT & MHM', 'SBS', 'GVI'])) {
                                $divisionName = 'COMMERCIAL';
                            } elseif (in_array($bu, ['PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA'])) {
                                $divisionName = 'SCM';
                            } elseif (in_array($bu, ['KEUANGAN & ACCOUNTING'])) {
                                $divisionName = 'FA';
                            } else {
                                $divisionName = 'SCM';
                            }

                            $companyName = 'PT. CAHAYA PERDANA TRANSALAM';
                            if (in_array($bu, ['SPBU', 'LPG PSO', 'LPG NPSO'])) {
                                $companyName = 'PT. LINTAS BINTAN SAMUDERA';
                            }
                        }

                        \App\Models\Library::create([
                            'title'         => $document->title,
                            'category'      => $category,
                            'division_name' => $divisionName,
                            'business_unit' => $bu,
                            'company_name'  => $companyName,
                            'file_path'     => $finalPath,
                            'uploaded_by'   => auth()->id(),
                        ]);

                        $statusMsg = 'Dokumen telah disetujui final oleh semua pihak dan resmi masuk E-Library!';
                    }
                } else {
                    throw new \Exception('Data antrean approval untuk dokumen ini tidak ditemukan atau sudah diproses.');
                }

                // C. Catat Log ke Timeline
                $document->logs()->create([
                    'user_id' => $user->id,
                    'action'  => 'active',
                    'notes'   => 'Disetujui oleh ' . $user->username . '. Catatan: ' . ($request->notes ?? '-'), 
                ]);
            });

            // OUTSIDE DB TRANSACTION: Dispatch Email Notifications to Newly Activated Current Signers
            foreach ($usersToNotify as $notifyUser) {
                if (!empty(trim($notifyUser->email ?? ''))) {
                    try {
                        $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                            'login.magic',
                            now()->addHours(24),
                            [
                                'user_id' => $notifyUser->id,
                                'document_id' => $documentId
                            ]
                        );

                        Mail::to($notifyUser->email)->send(
                            new \App\Mail\NewDocumentReviewMail(\App\Models\Document::find($documentId), $notifyUser, $magicLoginUrl)
                        );
                    } catch (\Throwable $e) {
                        \Log::error("e-QMS Reviewer Stage Transition Email Error for User ID {$notifyUser->id}: " . $e->getMessage());
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::error("e-QMS Approval Error: " . $e->getMessage());
            return redirect()->route('reviewer.dashboard')
                ->with('error', 'Dokumen gagal diproses. Silakan coba kembali atau hubungi administrator.');
        }

        return redirect()->route('reviewer.dashboard')->with('success', $statusMsg);
    }

    /**
     * Fungsi Detektif untuk mencari posisi teks di PDF
     */

// app/Http/Controllers/ReviewerController.php

        private function findTextCoordinates($pdfParsed, $targetText)
        {
            $ptToMm = 25.4 / 72.0;
            $markerMmY = null;
            $pageWidthPt  = 612.0;
            $pageHeightPt = 792.0;

            // Scan all pages sequentially — LP may be on page 2+ in merged preview PDFs (Cover+LP+Content)
            foreach ($pdfParsed->getPages() as $page) {
                $details  = $page->getDetails();
                $mediaBox = $details['MediaBox'] ?? [0, 0, 612, 792];
                $pwPt     = (float)($mediaBox[2] ?? 612.0);
                $phPt     = (float)($mediaBox[3] ?? 792.0);
                $dataTm   = $page->getDataTm();

                foreach ($dataTm as $item) {
                    if (str_contains(trim($item[1]), $targetText)) {
                        $rawYPt      = $item[0][5];
                        $markerMmY   = ($phPt - $rawYPt) * $ptToMm;
                        $pageWidthPt  = $pwPt;
                        $pageHeightPt = $phPt;
                        break 2; // found — stop scanning
                    }
                }
            }

            // If the marker was not found on any page, return null so the caller
            // can throw a safe exception (PATH C: no marker = no stamp, no DB mutation).
            if ($markerMmY === null) {
                return null;
            }

            // Compute X using same ratio formula as PdfSignaturePositionResolver:
            // sigColumnCenterX = pageWidthMm * 0.6824, stampX = sigColumnCenterX - halfStampWidth (12.5mm).
            // This gives legacy fallback the SAME baseX semantic as DocumentApproval.signature_x,
            // so drawDigitalStamp() applies the universal +7.2 draw offset identically for both paths.
            $pageWidthMm = $pageWidthPt * $ptToMm;
            $stampX = round($pageWidthMm * 0.6824 - 12.50, 2);
            $stampY = $markerMmY - 4.10;

            return ['x' => $stampX, 'y' => $stampY];
        }

    private function drawDigitalStamp($pdf, $x, $y, $name) 
    {
        // Compact visual stamp box (25.0mm x 5.2mm to fit tight table cells safely across all templates)
        $w = 25.0; 
        $h = 5.2;  

        // Center X alignment: offset X by +7.2mm to center the compact stamp in the Tanda Tangan column (since signature_x was resolved based on marker)
        $x = $x + 7.2;

        // Center Y alignment: offset Y slightly to maintain vertical center inside cell row
        $y = $y + 0.9;

        // 1. Latar Belakang Putih Menutupi Marker & Bingkai Slate-700
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($x, $y, $w, $h, 'F');
        $pdf->SetDrawColor(30, 41, 59);
        $pdf->SetLineWidth(0.2);
        $pdf->Rect($x, $y, $w, $h);

        // 2. Kotak Label Samping (Warna Slate) - narrower (4.0mm) for better visual balance
        $labelW = 4.0;
        $pdf->SetFillColor(30, 41, 59);
        $pdf->Rect($x, $y, $labelW, $h, 'F'); 

        // 3. Tulisan Label (QMS) - Font size 4.2pt bold (centered vertically)
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 4.2); 
        $pdf->SetXY($x, $y + 1.6);
        $pdf->Cell($labelW, 2.0, 'QMS', 0, 0, 'C');

        // 4. Informasi Persetujuan - padding adjusted to start at +4.8 (0.8mm gap)
        $pdf->SetTextColor(30, 41, 59);
        
        // Baris 1: Status Approval - Font size 4.0pt bold (centered vertically)
        $pdf->SetFont('Arial', 'B', 4.0);
        $pdf->SetXY($x + 4.8, $y + 1.0);
        $pdf->Cell(19.5, 1.5, 'DIGITALLY APPROVED', 0, 0, 'L');

        // Baris 2: Nama User - Font size 3.6pt (centered vertically)
        $pdf->SetFont('Arial', '', 3.6);
        $pdf->SetXY($x + 4.8, $y + 2.7);
        $pdf->Cell(19.5, 1.5, 'User: ' . strtoupper($name), 0, 0, 'L');
    }

    private function drawDateStamp($pdf, $pageWidthMm, $signatureY, $dateLine1, $dateLine2)
    {
        // Tanggal column center X ratio ~ 84.5% of page width
        $dateColumnCenterX = $pageWidthMm * 0.845;
        
        $pdf->SetTextColor(30, 41, 59);
        
        // Line 1: DD/MM/YYYY (Font 5.8pt, Bold)
        $pdf->SetFont('Arial', 'B', 5.8);
        $pdf->SetXY($dateColumnCenterX - 15.0, $signatureY - 0.3);
        $pdf->Cell(30.0, 2.5, $dateLine1, 0, 0, 'C');
        
        // Line 2: HH:MM WIB (Font 4.2pt)
        $pdf->SetFont('Arial', '', 4.2);
        $pdf->SetXY($dateColumnCenterX - 15.0, $signatureY + 2.6);
        $pdf->Cell(30.0, 2.0, $dateLine2, 0, 0, 'C');
    }

    private function drawMasterDocumentStamp($pdf, $pageWidthMm, $pageHeightMm, $signatureY, $dateStr, $noteAnchorY = null)
    {
        $stampWidth = 45.0;
        $stampHeight = 22.0;
        $footerMargin = 12.0;

        // 1. Hitung koordinat X (Rata kanan di DALAM border LP, dengan innerRightGap = 3.0mm)
        // Outer right border LP terletak pada ~192.20mm (atau pageWidth * 0.89)
        $lpRightBoundary = min(192.20, $pageWidthMm - 18.0);
        $innerRightGap = 3.0;
        $x = $lpRightBoundary - $stampWidth - $innerRightGap;
        $x = max(10.0, min($x, $pageWidthMm - $stampWidth - 5.0));

        // 2. Hitung koordinat Y (Di bawah Keterangan: NA... dengan gap kecil 3.5mm)
        if ($noteAnchorY !== null && $noteAnchorY > 50.0 && $noteAnchorY < ($pageHeightMm - 30.0)) {
            $targetY = $noteAnchorY + 3.5;
        } else {
            $targetY = $signatureY + 14.0;
        }

        $maxY = $pageHeightMm - $stampHeight - $footerMargin;

        $y = $targetY;
        if ($y > $maxY) {
            $y = $maxY;
        }
        $y = max(10.0, $y);

        // 3. Matikan AutoPageBreak sementara agar FPDF tidak memicu pembuatan halaman baru secara otomatis
        $pdf->SetAutoPageBreak(false);

        // 4. Gambar Bingkai Persegi Panjang Transparan (Warna Biru `#1E40AF` / RGB 30, 64, 175)
        $pdf->SetDrawColor(30, 64, 175);
        $pdf->SetLineWidth(0.4);
        $pdf->Rect($x, $y, $stampWidth, $stampHeight);

        // 5. Render Teks dengan Absolut Positioning (ln = 0 untuk mencegah pergerakan kursor/newline)
        // Baris 1: "MASTER DOCUMENT" (Warna Biru, Bold, Centered)
        $pdf->SetTextColor(30, 64, 175);
        $pdf->SetFont('Arial', 'B', 8.0);
        $pdf->SetXY($x, $y + 3.5);
        $pdf->Cell($stampWidth, 4.0, 'MASTER DOCUMENT', 0, 0, 'C');

        // Baris 2: Tanggal e.g. "14 AUG 2026" (Warna Merah `#DC2626` / RGB 220, 38, 38, Bold, Centered)
        $pdf->SetTextColor(220, 38, 38);
        $pdf->SetFont('Arial', 'B', 9.0);
        $pdf->SetXY($x, $y + 9.0);
        $pdf->Cell($stampWidth, 4.5, $dateStr, 0, 0, 'C');

        // Baris 3: "DCC PKM GROUP" (Warna Biru, Bold, Centered)
        $pdf->SetTextColor(30, 64, 175);
        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->SetXY($x, $y + 14.5);
        $pdf->Cell($stampWidth, 4.0, 'DCC PKM GROUP', 0, 0, 'C');

        // 6. Kembalikan Setting AutoPageBreak ke Kondisi Semula
        $pdf->SetAutoPageBreak(true, 20.0);
    }

    private function findKeteranganAnchorY($pdfParsed, $pageHeightMm)
    {
        try {
            $pages = $pdfParsed->getPages();
            if (empty($pages)) {
                return null;
            }

            $page1 = $pages[0];
            $details = $page1->getDetails();
            $mediaBox = $details['MediaBox'] ?? [0, 0, 612, 792];
            $cropBox = $details['CropBox'] ?? $mediaBox;

            $mediaBoxTopPt = (float)($mediaBox[3] ?? 792.0);
            $cropBoxBottomPt = (float)($cropBox[1] ?? 0.0);

            $dataTm = $page1->getDataTm();

            foreach ($dataTm as $item) {
                $text = trim($item[1] ?? '');
                if (stripos($text, 'Keterangan') !== false) {
                    $rawYPt = (float)($item[0][5] ?? 0.0);
                    // Convert raw PDF baseline Y to top-down FPDF/FPDI Y coordinate in mm, accounting for CropBox offset
                    $yMmFromTop = ($mediaBoxTopPt - $rawYPt - $cropBoxBottomPt) * (25.4 / 72.0);
                    return $yMmFromTop;
                }
            }
        } catch (\Throwable $e) {
            // Silently fallback if anchor search fails
        }

        return null;
    }

    public function streamFile($id)
    {
        $document = Document::findOrFail($id);
        $path = storage_path('app/public/' . ($document->file_preview ?? $document->file_lp));
        return response()->file($path);
    }

// ================================================================
    // 🕒 FIX TOTAL: RIWAYAT SOP SELESAI (SINKRONISASI ALUR ESTAFET)
    // ================================================================
    public function history()
    {
        $user = Auth::user();

        // Cari semua data di tabel antrean yang pernah disetujui (approved) oleh user ini
        $approvedByMe = \App\Models\DocumentApproval::where('user_id', $user->id)
            ->where('status', 'approved') // Hanya mengambil yang sudah diklik approve oleh pimpinan ini
            ->pluck('document_id'); // Ambil kumpulan ID dokumennya saja

        // Tarik data dokumen utamanya dari database berdasarkan kumpulan ID di atas
        $documents = Document::whereIn('id', $approvedByMe)
            ->whereIn('status', ['active', 'waiting', 'rejected']) // Biar dokumen yang masih 'waiting' di pimpinan lain tetap kelihatan di riwayat pimpinan sebelumnya!
            ->latest()
            ->get();

        return view('reviewer.history', compact('documents'));
    }

public function reject(Request $request, $id)
{
    // 1. Validasi agar reviewer wajib mengisi alasan penolakan
    $request->validate([
        'notes' => 'required|string|max:500'
    ]);

    $document = Document::findOrFail($id);
    $user = auth()->user(); // Ambil data reviewer yang sedang login

    // ================================================================
    // 🔥 PERBAIKAN UTAMA 1: MATIKAN ANTREAN CURRENT DI SISI REVIEWER
    // ================================================================
    // Kita cari antrean milik reviewer ini yang statusnya sedang 'current'
    $currentApproval = \App\Models\DocumentApproval::where('document_id', $document->id)
        ->where('user_id', $user->id)
        ->where('status', 'current')
        ->first();

    if ($currentApproval) {
        // Ubah status antrean pimpinan ini menjadi 'rejected' agar dokumen HILANG dari antreannya
        $currentApproval->update([
            'status' => 'rejected', 
            'notes' => $request->notes,
            'processed_at' => now()
        ]);
    }

    // ================================================================
    // 🔥 PERBAIKAN UTAMA 2: UBAH STATUS DOKUMEN UTAMA SECARA GLOBAL
    // ================================================================
    // Kita ubah status dokumen menjadi 'need_revision' agar di dashboard admin 
    // ikut berubah statusnya, bukan cuma nambah catatannya saja.
    $document->update([
        'status' => 'need_revision'
    ]);

    // 3. Catat alasan revisi ke Timeline (Ini yang kemarin sudah jalan)
    $document->logs()->create([
        'user_id' => auth()->id(),
        'action'  => 'revisi',
        'notes'   => $request->notes ?? 'Dokumen memerlukan perbaikan.',
    ]);

    return redirect()->route('reviewer.dashboard') // 👈 Sesuaikan dengan nama route halaman antrean review Trinwetty
        ->with('info', 'Dokumen berhasil dikembalikan ke Admin untuk direvisi.');
}

    /**
     * Fallback helper: Normalisasi PDF via QPDF jika FPDI melempar compression error
     */
    private function normalizePdfWithQpdf(string $inputPath): ?string
    {
        $qpdfBin = 'C:\\Program Files\\qpdf 12.4.0\\bin\\qpdf.exe';
        if (!file_exists($qpdfBin)) {
            return null;
        }

        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qpdf_norm_' . uniqid() . '.pdf';
        $cmd = sprintf(
            '%s %s --object-streams=disable --force-version=1.4 %s 2>&1',
            escapeshellarg($qpdfBin),
            escapeshellarg($inputPath),
            escapeshellarg($tempPath)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode === 0 && file_exists($tempPath) && filesize($tempPath) > 0) {
            return $tempPath;
        }

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        return null;
    }

}