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
    $userId = auth()->id();

    // 1. Dokumen yang SEKARANG menunggu aksi dari saya (giliran saya)
    $currentIds = \DB::table('document_approvals')
                    ->where('user_id', $userId)
                    ->where('status', 'current')
                    ->pluck('document_id')
                    ->toArray();

    $pendingDocuments = \App\Models\Document::whereIn('id', $currentIds)
                    ->latest()
                    ->get();

    // 2. Dokumen yang sudah saya approve tapi MASIH dalam proses (belum active)
    //    Ini mencakup dokumen berstatus: waiting, revision (perlu revisi dari creator)
    $myApprovedIds = \DB::table('document_approvals')
                    ->where('user_id', $userId)
                    ->where('status', 'approved')
                    ->pluck('document_id')
                    ->toArray();

    // Ambil dokumen yang sudah saya approve tapi belum final (belum 'active')
    // dan bukan dokumen yang sudah ada di currentIds (agar tidak duplikat)
    $inProgressDocuments = \App\Models\Document::whereIn('id', $myApprovedIds)
                    ->whereNotIn('status', ['active']) // hanya yang masih berjalan
                    ->whereNotIn('id', $currentIds)    // jangan duplikasi
                    ->latest()
                    ->get()
                    ->map(function ($doc) use ($userId) {
                        // Tambahkan info approval saya untuk context
                        $doc->my_approval = \App\Models\DocumentApproval::where('document_id', $doc->id)
                            ->where('user_id', $userId)
                            ->first();
                        return $doc;
                    });

    // Untuk backward compat dengan view lama, tetap pass $documents untuk antrean aktif
    $documents = $pendingDocuments;

    return view('reviewer.dashboard', compact('documents', 'pendingDocuments', 'inProgressDocuments'));
}

    public function show($id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        if ($user->role !== 'admin') {
            $isAssigned = \App\Models\DocumentApproval::where('document_id', $document->id)
                ->where('user_id', $user->id)
                ->exists();
            $isActive = ($document->status === 'active');

            if (!$isAssigned && !$isActive) {
                abort(403, 'Unauthorized access to this document.');
            }
        }

        $pathToShow = $document->file_final ?? $document->file_preview ?? $document->file_lp;
        return view('reviewer.show', compact('document', 'pathToShow'));
    }

    public function approve(Request $request, $id)
    {
        $statusMsg = 'Dokumen berhasil diproses.';
        $usersToNotify = [];
        $creatorToNotify = null;
        $finalNotifiedUsers = [];
        $documentId = $id;

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id, &$statusMsg, &$usersToNotify, &$creatorToNotify, &$finalNotifiedUsers) {
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
                    // Satu orang dapat memegang dua jabatan/slot approval dengan satu akun.
                    // Satu klik pada magic-link menyelesaikan seluruh slot final miliknya.
                    if ($currentApproval->stage === 'final') {
                        $sameUserFinalApprovals = \App\Models\DocumentApproval::where('document_id', $document->id)
                            ->where('user_id', $user->id)
                            ->where('stage', 'final')
                            ->where('status', 'current')
                            ->where('id', '!=', $currentApproval->id)
                            ->get();
                        foreach ($sameUserFinalApprovals as $additionalApproval) {
                            $additionalApproval->update([
                                'status' => 'approved',
                                'notes' => $request->notes ?? 'Setuju tanpa catatan.',
                                'processed_at' => $nowTime,
                            ]);
                        }
                        $allApproved = $allApproved->merge($sameUserFinalApprovals);
                    }

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
                                'user_id'      => $appItem->user_id,
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
                                
                                // Hitung rowHeight dinamis berdasarkan jumlah reviewer
                                $numReviewers = \App\Models\DocumentApproval::where('document_id', $document->id)
                                    ->where('stage', 'reviewer')
                                    ->count();
                                $rowHeight = 12.0;
                                if ($numReviewers > 5) {
                                    $rowHeight = max(8.0, 12.0 - ($numReviewers - 5) * 1.2);
                                }
                                $this->drawDigitalStamp($pdf, $stamp['x'], $stamp['y'], $stamp['username'], $rowHeight);
                                $this->drawDateStamp($pdf, $size['width'], $stamp['y'], $dLine1, $dLine2);
                                
                                // Get the ultimate final approver's user ID to ensure the DCC Master Document stamp is drawn only once.
                                $ultimateFinalUserId = \DB::table('document_approvals')
                                    ->where('document_id', $document->id)
                                    ->where('stage', 'final')
                                    ->orderBy('id', 'desc')
                                    ->value('user_id');

                                if ($stamp['stage'] === 'final' && isset($stamp['user_id']) && $stamp['user_id'] == $ultimateFinalUserId) {
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
                                    $dccLabel = strtolower((string)$document->company_header) === 'cpt' ? 'DCC CPT' : 'DCC PKM GROUP';
                                    $this->drawMasterDocumentStamp($pdf, $size['width'], $size['height'], $stamp['y'], $masterDateStr, $noteAnchorY, $dccLabel);
                                }
                            }
                        }
                    }

                    // 5. SIMPAN FILE HASIL TANDA TANGAN
                    $isFinal = ($currentApproval && $currentApproval->stage === 'final');
                    if ($isFinal) {
                        $cleanTitle = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $document->title);
                        $fileName = $cleanTitle . '_' . time() . '.pdf';
                    } else {
                        $fileName = 'ESTAFET_' . time() . '_' . $user->username . '.pdf';
                    }
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
                        $remainingFinalApprovals = \App\Models\DocumentApproval::where('document_id', $document->id)
                            ->where('stage', 'final')
                            ->where('status', '!=', 'approved')
                            ->count();

                        if ($remainingFinalApprovals > 0) {
                            $document->update([
                                'file_preview' => $finalPath,
                                'status' => 'waiting',
                            ]);
                            $statusMsg = 'Persetujuan final Anda berhasil dicatat. Menunggu penandatangan final lainnya.';
                        } else {
                        // STAGE 3: seluruh final approver approved -> Dokumen ACTIVE & Masuk E-Library
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

                        $creatorApproval = \App\Models\DocumentApproval::where('document_id', $document->id)
                                                                        ->where('stage', 'creator')
                                                                        ->first();
                        if ($creatorApproval && $creatorApproval->user) {
                            $creatorToNotify = $creatorApproval->user;
                        }

                        // Get all users who participated in the approval workflow (creator + reviewers)
                        $allWorkflowApprovals = \App\Models\DocumentApproval::where('document_id', $document->id)
                            ->whereIn('stage', ['creator', 'reviewer'])
                            ->with('user')
                            ->get();

                        foreach ($allWorkflowApprovals as $appItem) {
                            if ($appItem->user && !empty(trim($appItem->user->email ?? ''))) {
                                // Exclude creator to prevent duplicates
                                if (!$creatorToNotify || $appItem->user->id !== $creatorToNotify->id) {
                                    $finalNotifiedUsers[$appItem->user->id] = $appItem->user;
                                }
                            }
                        }

                        $statusMsg = 'Dokumen telah disetujui final oleh semua pihak dan resmi masuk E-Library!';
                        }
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

            if ($creatorToNotify && !empty(trim($creatorToNotify->email ?? ''))) {
                try {
                    $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                        'login.magic',
                        now()->addHours(24),
                        [
                            'user_id' => $creatorToNotify->id,
                            'document_id' => $documentId
                        ]
                    );

                    Mail::to($creatorToNotify->email)->send(
                        new \App\Mail\DocumentApprovedMail(\App\Models\Document::find($documentId), $creatorToNotify, $magicLoginUrl)
                    );
                } catch (\Throwable $e) {
                    \Log::error("e-QMS Final Approval Email Error for Creator ID {$creatorToNotify->id}: " . $e->getMessage());
                }
            }

            foreach ($finalNotifiedUsers as $notifyUser) {
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
                        new \App\Mail\DocumentApprovedMail(\App\Models\Document::find($documentId), $notifyUser, $magicLoginUrl)
                    );
                } catch (\Throwable $e) {
                    \Log::error("e-QMS Final Completion Notification Email Error for User ID {$notifyUser->id}: " . $e->getMessage());
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

    private function drawDigitalStamp($pdf, $x, $y, $name, $rowHeight = null) 
    {
        if ($rowHeight === null) {
            $rowHeight = 12.0;
            try {
                $user = \App\Models\User::where('username', $name)->first();
                if ($user) {
                    $app = \App\Models\DocumentApproval::where('user_id', $user->id)
                        ->whereBetween('signature_y', [$y - 0.1, $y + 0.1])
                        ->first();
                    if ($app) {
                        $numReviewers = \App\Models\DocumentApproval::where('document_id', $app->document_id)
                            ->where('stage', 'reviewer')
                            ->count();
                        if ($numReviewers > 5) {
                            $rowHeight = max(8.0, 12.0 - ($numReviewers - 5) * 1.2);
                        }
                    }
                }
            } catch (\Throwable $e) {
                $rowHeight = 12.0;
            }
        }

        // Tanda Tangan cell width is always 40mm. We want stamp to be as large as possible with safe margins.
        $w = 34.0;
        $h = $rowHeight - 3.0; // Margin 1.5mm top and bottom

        // Center X and Y inside the cell
        // Cell starts at X=125, width=40. Center is 145.
        $drawX = 145.0 - ($w / 2.0);
        // DB signature Y is calculated based on cell center: cellCenterY = y + 3.5
        $cellCenterY = $y + 3.5;
        $drawY = $cellCenterY - ($h / 2.0);

        // 1. Draw outer white background and border
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($drawX, $drawY, $w, $h, 'F');
        $pdf->SetDrawColor(30, 41, 59);
        $pdf->SetLineWidth(0.2);
        $pdf->Rect($drawX, $drawY, $w, $h);

        // 2. Kotak Label Samping (Green)
        $labelW = 5.0;
        $pdf->SetFillColor(30, 41, 59);
        $pdf->Rect($drawX, $drawY, $labelW, $h, 'F');

        // 3. Label QMS (Centered horizontally & vertically in the label box)
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->SetXY($drawX, $drawY + ($h - 2.0) / 2.0);
        $pdf->Cell($labelW, 2.0, 'QMS', 0, 0, 'C');

        // 4 & 5. Dynamically calculate largest approved font size and vertical alignment
        $userText = 'User: ' . strtoupper($name);
        
        // Base username font size (scaled to stamp height)
        $userFontSize = max(2.5, min(4.5, $h * 0.45));
        
        // Apply adaptive scaling to username if it's long
        if (strlen($userText) > 24) {
            $userFontSize *= 0.85;
        }
        if (strlen($userText) > 30) {
            $userFontSize *= 0.70;
        }

        // Loop to find largest approved font size
        $approvedFontSize = 6.0;
        $maxTextW = $w - $labelW - 3.0; // 26mm max width with margins
        $userHText = $userFontSize * 0.3528;
        $maxTextH = $h - $userHText - 1.2; // vertical height limit for APPROVED
        
        for ($fs = 6.0; $fs <= 24.0; $fs += 0.5) {
            $pdf->SetFont('Arial', 'B', $fs);
            $wText = $pdf->GetStringWidth('APPROVED');
            $hText = $fs * 0.3528;
            if ($wText <= $maxTextW && $hText <= $maxTextH) {
                $approvedFontSize = $fs;
            } else {
                break;
            }
        }

        // Terapkan penyesuaian visual: kurangi 4pt dari hasil adaptive terbesar
        $approvedFontSize = max(5.0, $approvedFontSize - 4.0);

        $approvedH = $approvedFontSize * 0.3528;
        $userH = $userFontSize * 0.3528;
        $middleGap = 0.3;
        $totalTextH = $approvedH + $userH + $middleGap;
        $topPadding = ($h - $totalTextH) / 2.0;

        // Draw APPROVED
        $pdf->SetTextColor(30, 41, 59);
        $pdf->SetFont('Arial', 'B', $approvedFontSize);
        $pdf->SetXY($drawX + $labelW + 1.2, $drawY + $topPadding);
        $pdf->Cell($w - $labelW - 2.0, $approvedH, 'APPROVED', 0, 0, 'L');

        // Draw Username
        $pdf->SetTextColor(30, 41, 59);
        $pdf->SetFont('Arial', '', $userFontSize);
        $pdf->SetXY($drawX + $labelW + 1.2, $drawY + $topPadding + $approvedH + $middleGap);
        $pdf->Cell($w - $labelW - 2.0, $userH, $userText, 0, 0, 'L');
    }

    private function drawDateStamp($pdf, $pageWidthMm, $signatureY, $dateLine1, $dateLine2)
    {
        // Tanggal column center X (column is at X=165, width=30)
        $dateColumnCenterX = $pageWidthMm - 30.0;
        
        $pdf->SetTextColor(30, 41, 59);
        
        // Line 1: DD/MM/YYYY (Font 7.8pt, Bold)
        $pdf->SetFont('Arial', 'B', 7.8);
        $pdf->SetXY($dateColumnCenterX - 15.0, $signatureY + 1.2);
        $pdf->Cell(30.0, 2.0, $dateLine1, 0, 0, 'C');
        
        // Line 2: HH:MM WIB (Font 6.2pt)
        $pdf->SetFont('Arial', '', 6.2);
        $pdf->SetXY($dateColumnCenterX - 15.0, $signatureY + 3.6);
        $pdf->Cell(30.0, 2.0, $dateLine2, 0, 0, 'C');
    }

    private function drawMasterDocumentStamp($pdf, $pageWidthMm, $pageHeightMm, $signatureY, $dateStr, $noteAnchorY = null, $dccLabel = 'DCC PKM GROUP')
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

        // 2. Hitung koordinat Y (Di bawah Keterangan: NA... dengan gap kecil 12.0mm)
        if ($noteAnchorY !== null && $noteAnchorY > 50.0 && $noteAnchorY < ($pageHeightMm - 30.0)) {
            $targetY = $noteAnchorY + 12.0;
        } else {
            $targetY = $signatureY + 24.0;
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
        $pdf->Cell($stampWidth, 4.0, $dccLabel, 0, 0, 'C');

        // 6. Kembalikan Setting AutoPageBreak ke Kondisi Semula
        $pdf->SetAutoPageBreak(true, 20.0);
    }

    private function findKeteranganAnchorY($pdfParsed, $pageHeightMm)
    {
        try {
            $pages = $pdfParsed->getPages();
            if (count($pages) < 2) {
                return null;
            }

            // Page index 1 = Lembar Pengesahan (2nd page in merged doc)
            $page1 = $pages[1];
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
        $user = Auth::user();

        if ($user->role !== 'admin') {
            $isAssigned = \App\Models\DocumentApproval::where('document_id', $document->id)
                ->where('user_id', $user->id)
                ->exists();
            $isActive = ($document->status === 'active');

            if (!$isAssigned && !$isActive) {
                abort(403, 'Unauthorized access to this document.');
            }
        }

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
        // Ini adalah permintaan revisi dari reviewer, bukan upload revisi baru.
        // Dibedakan agar tidak dihitung sebagai nomor revisi dokumen.
        'action'  => 'minta_revisi',
        'notes'   => $request->notes ?? 'Dokumen memerlukan perbaikan.',
    ]);

    // Kirim notifikasi email ke pembuat dokumen (creator)
    try {
        $creatorApproval = \App\Models\DocumentApproval::where('document_id', $document->id)
            ->where('stage', 'creator')
            ->first();
        if ($creatorApproval && $creatorApproval->user && !empty(trim($creatorApproval->user->email ?? ''))) {
            $creator = $creatorApproval->user;
            $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'login.magic',
                now()->addHours(24),
                [
                    'user_id' => $creator->id,
                    'document_id' => $document->id
                ]
            );

            \Illuminate\Support\Facades\Mail::to($creator->email)->send(
                new \App\Mail\DocumentRevisionRequestedMail($document, $creator, $user, $request->notes ?? 'Dokumen memerlukan perbaikan.', $magicLoginUrl)
            );
        }
    } catch (\Throwable $e) {
        \Log::error("e-QMS Revision Email Notification Error: " . $e->getMessage());
    }

    return redirect()->route('reviewer.dashboard') // 👈 Sesuaikan dengan nama route halaman antrean review Trinwetty
        ->with('info', 'Dokumen berhasil dikembalikan ke Admin untuk direvisi.');
}

    /**
     * Fallback helper: Normalisasi PDF via QPDF jika FPDI melempar compression error
     */
    private function normalizePdfWithQpdf(string $inputPath): ?string
    {
        $qpdfBin = config('app.qpdf_path', 'qpdf');
        if ((str_starts_with($qpdfBin, '/') || preg_match('/^[a-zA-Z]:\\\\/', $qpdfBin)) && !file_exists($qpdfBin)) {
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
