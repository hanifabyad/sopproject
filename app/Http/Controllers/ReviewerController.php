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
    $document = Document::findOrFail($id);
    $user = Auth::user();

    // 1. TENTUKAN SUMBER FILE (LOGIKA ESTAFET)
    // Selalu ambil dari file_preview karena di sana tersimpan hasil ttd orang sebelumnya
    $sourcePath = storage_path('app/public/' . ($document->file_preview ?? $document->file_lp));

    // 2. TENTUKAN TEKS JANGKAR BERDASARKAN ROLE
    $anchorMap = [
        //PT LBS
        'KA.DEPT.QMS'         => '[sig01]',
        'Chief of Staff'      => '[sig02]',
        'Ka. BU Gas & SPBE'   => '[sig03]',
        'Chief F&A'           => '[sig04]',
        'Ka. Div Retail'      => '[sig05]',
        'Wa. Ka. Div Retail'  => '[sig06]',
        'Ka. Div F&A'         => '[sig07]',
        'Dept. Internal Audit'=> '[sig08]',
        'Direktur Utama'      => '[sig09]',
        

        //
    ];
    $targetText = $anchorMap[$user->role] ?? '[sig01]';

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
            return back()->withErrors(['msg' => 'Gagal memproses PDF: Format kompresi PDF tidak didukung dan QPDF gagal menormalisasi file.']);
        }
    }

    try {
        $parser = new \Smalot\PdfParser\Parser();
        $pdfParsed = $parser->parseFile($parsePath);
        $coordinates = $this->findTextCoordinates($pdfParsed, $targetText);

        // 4. PROSES PENEMPELAN STEMPEL (FPDI)
        $pdf = new \setasign\Fpdi\Fpdi();
        $pageCount = $pdf->setSourceFile($parsePath);
        
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            // Stempel diletakkan di halaman 2 (Lembar Pengesahan) jika gabungan, atau hal 1 jika tunggal
            if ($pageCount > 1) {
                if ($pageNo == 2) {
                    $this->drawDigitalStamp($pdf, $coordinates['x'], $coordinates['y'] + 9, $user->username);
                }
            } else {
                if ($pageNo == 1) {
                    $this->drawDigitalStamp($pdf, $coordinates['x'], $coordinates['y'] + 9, $user->username);
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
    // 6. LOGIKA ANTREAN OTOMATIS (THE MAGIC)
    // ================================================================

    // A. Update status antrean user saat ini menjadi 'approved'
    $currentApproval = \App\Models\DocumentApproval::where('document_id', $document->id)
        ->where('user_id', $user->id)
        ->where('status', 'current')
        ->first();
    
    if ($currentApproval) {
        $currentApproval->update([
            'status' => 'approved',
            // Menangkap isi inputan dari textarea Keputusan Reviewer
            'notes' => $request->notes ?? 'Setuju tanpa catatan.', 
            'processed_at' => now()
        ]);
    }

    // ================================================================
    // B. Cari apakah ada urutan berikutnya (sequence + 1)?
    // ================================================================
    $nextApproval = null;

    // KUNCI PENGAMAN: Cek dulu apakah data antrean saat ini ditemukan
    if ($currentApproval) {
        $nextApproval = \App\Models\DocumentApproval::where('document_id', $document->id)
            ->where('sequence', $currentApproval->sequence + 1)
            ->first();
            
        if ($nextApproval) {
            // --- SKENARIO 1: ADA PENINJAU BERIKUTNYA ---
            $nextApproval->update(['status' => 'current']);

            // Update dokumen agar menunjuk ke reviewer berikutnya
            $document->update([
                'reviewer_id' => $nextApproval->user_id,
                'file_preview' => $finalPath, // File yang sudah ada ttd dikirim ke orang berikutnya
                'status' => 'waiting'
            ]);

            $statusMsg = 'Dokumen berhasil disetujui dan diteruskan ke ' . $nextApproval->user->username;

        } else {
            // --- SKENARIO 2: SUDAH ORANG TERAKHIR ---
            $document->update([
                'file_final' => $finalPath,
                'file_preview' => $finalPath,
                'status' => 'active',
            ]);

            // 🔥 TENTUKAN KATEGORI & NAMA DIVISI BERDASARKAN DEPARTEMEN/UNIT
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

            // 🔥 TOMBOL OTOMATIS: Salin data SOP ini langsung ke dalam tabel Library!
            \App\Models\Library::create([
                'title'         => $document->title,
                'category'      => $category,
                'division_name' => $divisionName,
                'business_unit' => $bu,
                'company_name'  => $companyName,
                'file_path'     => $finalPath, // Membawa file hasil tanda tangan final semua orang!
                'uploaded_by'   => auth()->id(),
            ]);

            $statusMsg = 'Dokumen telah disetujui final oleh semua pihak dan resmi masuk E-Library!';
        }
    } else {
        // Fallback darurat: Jika data antrean 'current' tidak ditemukan di database
        return redirect()->route('reviewer.dashboard')
            ->with('error', 'Waduh, data antrean approval untuk dokumen ini tidak ditemukan atau sudah diproses.');
    }

    // C. Catat Log ke Timeline
   $document->logs()->create([
        'user_id' => $user->id,
        'action'  => 'active', // atau 'approved' sesuai enum kamu
        // Menyimpan catatan pimpinan ke log agar bisa dibaca admin nanti
        'notes'   => 'Disetujui oleh ' . $user->username . '. Catatan: ' . ($request->notes ?? '-'), 
    ]);

    // D. Kirim Email Notifikasi dengan Magic Link (Berumur 15 Menit)
    // ================================================================
    try {
        if (isset($nextApproval)) {
            // 1. GENERATE URL AUTO-LOGIN AMAN (Hanya Aktif 15 Menit sejak email terkirim)
            $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'login.magic', // Nama route penerima yang ada di web.php
                now()->addMinutes(15), // Durasi kedaluwarsa
                [
                    'user_id' => $nextApproval->user_id, 
                    'document_id' => $document->id
                ]
            );

            // 2. Kirim Email ke Pimpinan Berikutnya dengan Menitipkan Link Rahasia
            // Pastikan file Mailable 'NewDocumentReviewMail' kamu diubah untuk menerima parameter link ini!
            if ($nextApproval->user && $nextApproval->user->email) {
                Mail::to($nextApproval->user->email)->send(
                    new \App\Mail\NewDocumentReviewMail($document, $nextApproval->user, $magicLoginUrl)
                );
            }

        } else {
            // Notif ke pembuat SOP kalau dokumen sudah selesai di-approve semua pihak (Selesai Estafet)
            if (class_exists('\App\Mail\DocumentApprovedMail') && isset($document->user) && !empty($document->user->email)) {
                Mail::to($document->user->email)->send(new \App\Mail\DocumentApprovedMail($document));
            }
        }
    } catch (\Throwable $e) {
        \Log::error("e-QMS Email Error: " . $e->getMessage());
    }

    return redirect()->route('reviewer.dashboard')->with('success', $statusMsg);
}

    /**
     * Fungsi Detektif untuk mencari posisi teks di PDF
     */

// app/Http/Controllers/ReviewerController.php

        private function findTextCoordinates($pdfParsed, $targetText)
        {
            // 1. TENTUKAN KOORDINAT MANUAL (OPSIONAL/FALLBACK)
            
            $manualCoordinates = [
                //PT LBS
                '[sig01]' => ['x' => 143, 'y' => 90],  // Imam
                '[sig02]' => ['x' => 143, 'y' => 108], // Trinwetty
                '[sig03]' => ['x' => 143, 'y' => 116], // Tri Minarni
                '[sig04]' => ['x' => 143, 'y' => 125], // Ekowati
                '[sig05]' => ['x' => 143, 'y' => 134], // Ibnu Mirza
                '[sig06]' => ['x' => 143, 'y' => 143], // Lalu Wandi
                '[sig07]' => ['x' => 143, 'y' => 152], // Putri Larasati
                '[sig08]' => ['x' => 143, 'y' => 161], // Suhaimi
                '[sig09]' => ['x' => 143, 'y' => 177], // Zikri

                //PT....
            ];

            // 2. LOGIKA: CEK MANUAL DULU
            // Jika targetText ada di daftar manual, langsung gunakan koordinat tersebut
            if (isset($manualCoordinates[$targetText])) {
                return $manualCoordinates[$targetText];
            }

            // 3. JIKA TIDAK ADA DI MANUAL, BARU CARI OTOMATIS (CADANGAN)
            $page = $pdfParsed->getPages()[0];
            $textObjects = $page->getDataTm(); 

            foreach ($textObjects as $obj) {
                $cleanedText = trim($obj[1]);
                if ($cleanedText === $targetText) {
                    return [
                        'x' => $obj[0][4] * 0.264583,
                        'y' => 297 - ($obj[0][5] * 0.264583) + 6 
                    ];
                }
            }

            // Jika semua gagal, gunakan koordinat tengah dokumen
            return ['x' => 145, 'y' => 100]; 
        }

    private function drawDigitalStamp($pdf, $x, $y, $name) 
{
    // UKURAN BARU: Lebih panjang (30mm) untuk membungkus seluruh teks
    $w = 30; 
    $h = 8;  

    // 1. Gambar Latar Belakang Putih & Bingkai Luar
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect($x, $y, $w, $h, 'F');
    $pdf->SetDrawColor(30, 41, 59); // Warna Slate-700
    $pdf->SetLineWidth(0.3); // Membuat garis bingkai sedikit lebih tegas
    $pdf->Rect($x, $y, $w, $h);

    // 2. Gambar Kotak Label Samping (Warna Biru Tua/Slate)
    $labelW = 6; // Lebar label samping
    $pdf->SetFillColor(30, 41, 59);
    $pdf->Rect($x, $y, $labelW, $h, 'F'); 

    // 3. Tulisan Label (QMS)
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 5.5); 
    $pdf->SetXY($x, $y + 2.5);
    $pdf->Cell($labelW, 3, 'QMS', 0, 0, 'C');

    // 4. Informasi Persetujuan (Sisi Kanan - Area Lebih Luas)
    $pdf->SetTextColor(30, 41, 59);
    
    // Baris 1: Status Approval
    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->SetXY($x + 7, $y + 1.2);
    $pdf->Cell(22, 2, 'DIGITALLY APPROVED', 0, 0, 'L');

    // Baris 2: Nama User (Font sedikit diperbesar karena ruang cukup)
    $pdf->SetFont('Arial', '', 4);
    $pdf->SetXY($x + 7, $y + 3.5);
    $pdf->Cell(22, 2, 'User: ' . strtoupper($name), 0, 0, 'L');

    // Baris 3: Tanggal
    $pdf->SetXY($x + 7, $y + 5.8);
    $pdf->Cell(22, 2, 'Date: ' . date('d/m/Y H:i'), 0, 0, 'L');
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