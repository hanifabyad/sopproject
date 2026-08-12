<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail; // Pastikan Mail di-import dengan benar
use Illuminate\Support\Facades\Storage; // Pastikan Storage di-import dengan benar
use Webklex\PDFMerger\Facades\PDFMergerFacade;

class BusinessUnitController extends Controller
{
    private $divisi = [
        'RETAIL' => ['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'INMAR (CNGM)'],
        'KOMERSIL' => ['CPT & MHM', 'SBS', 'GVI'],
        'SCM' => ['PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA'],
        'FA' => ['KEUANGAN & ACCOUNTING']
    ];

    public function index(): View
    {
        $statsDivisi = [];
        foreach ($this->divisi as $namaDivisi => $daftarBU) {
            $docs = Document::whereIn('department', $daftarBU)->get();
            $statsDivisi[$namaDivisi] = [
                'total'    => $docs->count(),
                'active'   => $docs->where('status', 'active')->count(),
                'inactive' => $docs->where('status', 'need_revision')->count(),
                'bu_count' => count($daftarBU)
            ];
        }
        return view('admin.BU.index', compact('statsDivisi'));
    }

    public function showDivisi($namaDivisi): View
    {
        $daftarBU = $this->divisi[$namaDivisi] ?? abort(404);
        $statsBU = [];
        foreach ($daftarBU as $bu) {
            $docs = Document::where('department', $bu)->get();
            $statsBU[$bu] = [
                'total'    => $docs->count(),
                'active'   => $docs->where('status', 'active')->count(),
                'inactive' => $docs->where('status', 'need_revision')->count(),
            ];
        }
        return view('admin.BU.list_bu', compact('statsBU', 'namaDivisi'));
    }

    /**
     * Menampilkan Daftar SOP dalam satu unit bisnis.
     */
    public function showBU($bu): View
    {
        $documents = Document::where('department', $bu)->latest()->get();
        
        $stats = [
            'name'     => $bu,
            'approved' => $documents->where('status', 'active')->count(),
            'waiting'  => $documents->where('status', 'waiting')->count(),
            'revisi'   => $documents->where('status', 'need_revision')->count(),
        ];
        
        return view('admin.BU.list_BU', compact('documents', 'stats'));
    }

    public function create(string $unit): View
    {
        $reviewers = User::all(); 
        return view('admin.BU.create', compact('unit', 'reviewers'));
    }

   public function store(Request $request, string $unit)
    {
        // 1. Validasi
        $request->validate([
            'title'         => 'required|string|max:255',
            'approvers'     => 'required|array|min:1', 
            'file_cover'    => 'required|mimes:pdf|max:5000',
            'file_lp'       => 'required|mimes:pdf|max:5000',
            'file_isi'      => 'required|mimes:pdf|max:10000',
            'file_lampiran' => 'nullable|mimes:pdf|max:5000',
        ]);

        // 2. Simpan 4 File Asli
        $pathCover = $request->file('file_cover')->store('documents/covers', 'public');
        $pathLp    = $request->file('file_lp')->store('documents/lps', 'public');
        $pathIsi   = $request->file('file_isi')->store('documents/contents', 'public');
        $pathLamp  = $request->hasFile('file_lampiran') 
                     ? $request->file('file_lampiran')->store('documents/attachments', 'public') 
                     : null;

        // 3. Proses Penggabungan (Merge) untuk Preview Pertama
        $tempFilesToClean = [];
        try {
            $mergeCover = $this->ensurePdfCompatible(storage_path('app/public/' . $pathCover), $tempFilesToClean);
            $mergeLp    = $this->ensurePdfCompatible(storage_path('app/public/' . $pathLp), $tempFilesToClean);
            $mergeIsi   = $this->ensurePdfCompatible(storage_path('app/public/' . $pathIsi), $tempFilesToClean);
            $mergeLamp  = $pathLamp ? $this->ensurePdfCompatible(storage_path('app/public/' . $pathLamp), $tempFilesToClean) : null;

            $merger = PDFMergerFacade::init();
            $merger->addPDF($mergeCover, 'all');
            $merger->addPDF($mergeLp, 'all');
            $merger->addPDF($mergeIsi, 'all');
            if ($mergeLamp) $merger->addPDF($mergeLamp, 'all');

            $merger->merge();
            $previewName = 'preview_' . time() . '.pdf';
            $previewPath = 'documents/previews/' . $previewName;
            
            Storage::disk('public')->put($previewPath, $merger->output());
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => "Gagal Merge: " . $e->getMessage()]);
        } finally {
            foreach ($tempFilesToClean as $tempFile) {
                if (file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            }
        }

        // 4. Simpan Data SOP ke Database (Disimpan di variabel $document)
        $document = Document::create([
            'title'         => $request->title,
            'department'    => $unit,
            'reviewer_id'   => $request->approvers[0], 
            'file_cover'    => $pathCover,
            'file_lp'       => $pathLp,
            'file_isi'      => $pathIsi,
            'file_lampiran' => $pathLamp,
            'file_preview'  => $previewPath,
            'status'        => 'waiting',
        ]);

        // 🔥 GENERATE DATA ANTREAN BERANTAI AWAL (DOCUMENT APPROVALS)
        foreach ($request->approvers as $index => $approverId) {
            \App\Models\DocumentApproval::create([
                'document_id' => $document->id,
                'user_id'     => $approverId,
                'sequence'    => $index + 1, 
                'status'      => ($index === 0) ? 'current' : 'pending', 
            ]);
        }

        // 5. LOGIKA PENGIRIMAN EMAIL NOTIFIKASI + MAGIC LINK
        $firstReviewer = User::find($request->approvers[0]);

        if ($firstReviewer && $firstReviewer->email) {
            try {
                $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'login.magic',
                    now()->addHours(24),
                    [
                        'user_id' => $firstReviewer->id,
                        'document_id' => $document->id
                    ]
                );

                Mail::to($firstReviewer->email)->send(
                    new \App\Mail\NewDocumentReviewMail($document, $firstReviewer, $magicLoginUrl)
                );
            } catch (\Exception $e) {
                \Log::error("e-QMS Email Error: " . $e->getMessage());
            }
        }

        // Kembalikan ke halaman daftar Unit Bisnis setelah sukses upload
        return redirect()->route('admin.BU.show', $unit)->with('success', 'Alur Pengesahan Berantai Berhasil Dimulai!');
    }

    public function destroy(int $id)

    

{

    $document = Document::findOrFail($id);

    $unit = $document->department;



    // Daftar file yang harus dibersihkan dari folder storage

    $filesToDelete = [

        $document->file_cover,

        $document->file_lp,

        $document->file_isi,

        $document->file_lampiran

    ];



    foreach ($filesToDelete as $file) {

        if (!empty($file)) {

            $fullPath = storage_path('app/public/' . $file);

            

            // Perbaikan utama: Cek apakah itu file dan bukan folder

            if (file_exists($fullPath) && is_file($fullPath)) {

                unlink($fullPath);

            }

        }

    }



    $document->delete();



    return redirect()->route('admin.BU.show', $unit)->with('success', 'Dokumen berhasil dihapus selamanya!');

}



    // BusinessUnitController.php



public function documentDetail(int $id) // <-- Pastikan namanya documentDetail

{

    $document = Document::with('logs.user')->findOrFail($id);

    $reviewers = User::all(); 

    

    return view('admin.BU.show', compact('document', 'reviewers'));

}



// BusinessUnitController.php



public function updateReviewer(Request $request, int $id) 
{
    $request->validate([
        'reviewer_id' => 'required|exists:users,id'
    ]);

    $document = \App\Models\Document::findOrFail($id);
    $newUser = \App\Models\User::find($request->reviewer_id);

    // 1. Update reviewer_id pada tabel documents
    $document->update([
        'reviewer_id' => $newUser->id,
        'status'      => 'waiting'
    ]);

    // 2. Update user_id pada antrean document_approvals yang berstatus current
    $currentApproval = \App\Models\DocumentApproval::where('document_id', $document->id)
        ->where('status', 'current')
        ->first();

    if ($currentApproval) {
        $currentApproval->update([
            'user_id' => $newUser->id
        ]);
    }

    // 3. Catat ke log
    $document->logs()->create([
        'user_id' => auth()->id() ?? 1,
        'action'  => 'transfer',
        'notes'   => 'Admin memindahkan kendali dokumen kepada: ' . $newUser->username, 
    ]);

    // 4. Kirim email & magic link ke reviewer baru (jika email ada)
    if ($newUser->email) {
        try {
            $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'login.magic',
                now()->addHours(24),
                [
                    'user_id' => $newUser->id,
                    'document_id' => $document->id
                ]
            );

            \Illuminate\Support\Facades\Mail::to($newUser->email)->send(
                new \App\Mail\NewDocumentReviewMail($document, $newUser, $magicLoginUrl)
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("e-QMS Email Transfer Error: " . $e->getMessage());
        }
    }

    return redirect()->back()->with('success', 'Dokumen berhasil dioper ke ' . $newUser->username);
}

// ================================================================
    // 🔥 BARU: MENAMPILKAN FORM REVISI DOKUMEN
    // ================================================================
    public function editRevision(int $id): \Illuminate\View\View
    {
        // Ambil data dokumen beserta antrean yang berstatus rejected untuk info di form jika perlu
        $document = Document::with('approvals.user')->findOrFail($id);
        
        return view('admin.BU.edit_revision', compact('document'));
    }

    // ================================================================
    // 🔥 PERBAIKAN TOTAL: MEMPROSES UPLOAD FILE REVISI & ESTAFET PINTAR
    // ================================================================
    // ================================================================
    // 🔥 REVISI STRUKTUR TOTAL: URUTAN LOGIS, ANTI-HILANG & ANTI-ERROR
    // ================================================================
    public function updateRevision(Request $request, int $id)
    {
        // 1. Validasi
        $request->validate([
            'title'         => 'required|string|max:255',
            'file_cover'    => 'nullable|mimes:pdf|max:5000',
            'file_lp'       => 'nullable|mimes:pdf|max:5000',
            'file_isi'      => 'nullable|mimes:pdf|max:10000',
            'file_lampiran' => 'nullable|mimes:pdf|max:5000',
        ]);

        $document = Document::findOrFail($id);
        $unit = $document->department;

        // 2. Simpan File Fisik Baru ke Storage (Jika ada)
        $pathCover = $request->hasFile('file_cover') 
            ? $request->file('file_cover')->store('documents/covers', 'public') 
            : $document->file_cover;

        $pathLp = $request->hasFile('file_lp') 
            ? $request->file('file_lp')->store('documents/lps', 'public') 
            : $document->file_lp;

        $pathIsi = $request->hasFile('file_isi') 
            ? $request->file('file_isi')->store('documents/contents', 'public') 
            : $document->file_isi;

        $pathLamp = $request->hasFile('file_lampiran') 
            ? $request->file('file_lampiran')->store('documents/attachments', 'public') 
            : $document->file_lampiran;

        // 3. PROSES STRATEGI GABUNG PDF (MERGER)
        $tempFilesToClean = [];
        try {
            $merger = \Webklex\PDFMerger\Facades\PDFMergerFacade::init();
            
            // A. JIKA ADMIN MENGGANTI FILE ISI
            if ($request->hasFile('file_isi')) {
                
                if (!empty($document->file_preview) && file_exists(storage_path('app/public/' . $document->file_preview))) {
                    // Ambil Cover & LP berstempel dari preview lama (Halaman 1-2)
                    $prevPdf = $this->ensurePdfCompatible(storage_path('app/public/' . $document->file_preview), $tempFilesToClean);
                    $merger->addPDF($prevPdf, [1, 2]); 
                } else {
                    if (!empty($pathCover)) {
                        $covPdf = $this->ensurePdfCompatible(storage_path('app/public/' . $pathCover), $tempFilesToClean);
                        $merger->addPDF($covPdf, 'all');
                    }
                    if (!empty($pathLp)) {
                        $lpPdf = $this->ensurePdfCompatible(storage_path('app/public/' . $pathLp), $tempFilesToClean);
                        $merger->addPDF($lpPdf, 'all');
                    }
                }
                
                // Masukkan file isi yang baru (Otomatis menimpa isi lama)
                $isiPdf = $this->ensurePdfCompatible(storage_path('app/public/' . $pathIsi), $tempFilesToClean);
                $merger->addPDF($isiPdf, 'all');
                
            } else {
                // B. JIKA ADMIN TIDAK MENGUBAH ISI (Ambil utuh preview lama)
                if (!empty($document->file_preview) && file_exists(storage_path('app/public/' . $document->file_preview))) {
                    $prevPdf = $this->ensurePdfCompatible(storage_path('app/public/' . $document->file_preview), $tempFilesToClean);
                    $merger->addPDF($prevPdf, 'all');
                }
            }

            // ⭐ AMANKAN LAMPIRAN SEBELUM PROSES MERGE DIEKSEKUSI ⭐
            $activeLamp = null;
            if ($request->hasFile('file_lampiran')) {
                $activeLamp = $pathLamp;
            } else {
                if (!empty($document->file_lampiran)) {
                    if (is_array($document->file_lampiran) || is_object($document->file_lampiran)) {
                        foreach ((array)$document->file_lampiran as $item) {
                            if (is_string($item) && !empty($item)) {
                                $activeLamp = $item;
                                break; 
                            }
                        }
                    } else {
                        $activeLamp = $document->file_lampiran;
                    }
                }
            }

            // Masukkan lampiran ke antrean halaman paling bawah jika file fisiknya ada
            if (!empty($activeLamp) && is_string($activeLamp)) {
                $fullLampPath = storage_path('app/public/' . $activeLamp);
                if (file_exists($fullLampPath)) {
                    $lampPdf = $this->ensurePdfCompatible($fullLampPath, $tempFilesToClean);
                    $merger->addPDF($lampPdf, 'all');
                }
            }

            // EKSEKUSI FINAL BUNDLING PDF REVISI UTUH
            $merger->merge();
            $previewName = 'preview_rev_' . time() . '.pdf';
            $previewPath = 'documents/previews/' . $previewName;
            
            \Illuminate\Support\Facades\Storage::disk('public')->put($previewPath, $merger->output());

        } catch (\Exception $e) {
            return back()->withErrors(['msg' => "Gagal Merge PDF Revisi: " . $e->getMessage()]);
        } finally {
            foreach ($tempFilesToClean as $tempFile) {
                if (file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            }
        }

        // 4. GENERATE ULANG VARIABEL TARGET ANTREAN (PENYELAMAT ERROR UNDEFINED)
        $nextTargetApproval = \App\Models\DocumentApproval::where('document_id', $document->id)
            ->whereIn('status', ['rejected', 'current', 'waiting'])
            ->orderBy('sequence', 'asc')
            ->first();

        if (!$nextTargetApproval) {
            return back()->withErrors(['msg' => "Tidak ditemukan antrean peninjau yang memerlukan revisi."]);
        }

        // 5. UPDATE TARGET JADI CURRENT
        $nextTargetApproval->update([
            'status' => 'current',
            'processed_at' => null
        ]);

        // 6. UPDATE MASTER DATA DOKUMEN UTAMA
        $document->update([
            'title'         => $request->title,
            'reviewer_id'   => $nextTargetApproval->user_id, // Mengarah lurus ke Trinwetty
            'file_cover'    => $pathCover,
            'file_lp'       => $pathLp,
            'file_isi'      => $pathIsi,
            'file_lampiran' => $pathLamp,
            'file_preview'  => $previewPath,
            'status'        => 'waiting', 
        ]);

        // 7. Catat Aksi ke Timeline Log
        $document->logs()->create([
            'user_id' => auth()->id(),
            'action'  => 'revisi',
            'notes'   => 'Admin mengunggah file revisi baru. Alur otomatis dilanjutkan langsung ke: ' . $nextTargetApproval->user->username,
        ]);

        // 8. Kirim Email Notifikasi & Magic Link
        $reviewerUser = $nextTargetApproval->user;
        if ($reviewerUser && $reviewerUser->email) {
            try {
                $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'login.magic',
                    now()->addHours(24),
                    [
                        'user_id' => $reviewerUser->id,
                        'document_id' => $document->id
                    ]
                );

                \Illuminate\Support\Facades\Mail::to($reviewerUser->email)->send(
                    new \App\Mail\NewDocumentReviewMail($document, $reviewerUser, $magicLoginUrl)
                );
            } catch (\Exception $e) {
                \Log::error("e-QMS Email Revisi Error: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.BU.detail', $document->id)
            ->with('success', 'File revisi berhasil digabungkan dan dialirkan langsung ke ' . $reviewerUser->username);
    }

    /**
     * Helper privat: Memeriksa dan menormalisasi PDF via QPDF jika FPDI melempar compression error.
     */
    private function ensurePdfCompatible(string $absolutePath, array &$tempFilesToClean): string
    {
        if (!file_exists($absolutePath)) {
            return $absolutePath;
        }

        try {
            $pdfCheck = new \setasign\Fpdi\Fpdi();
            $pdfCheck->setSourceFile($absolutePath);
            return $absolutePath; // File original compatible, gunakan langsung
        } catch (\Throwable $e) {
            // FPDI gagal (misal unsupported compression), coba normalisasi dengan QPDF
            $qpdfBin = 'C:\\Program Files\\qpdf 12.4.0\\bin\\qpdf.exe';
            if (!file_exists($qpdfBin)) {
                return $absolutePath;
            }

            $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qpdf_bu_norm_' . uniqid() . '.pdf';
            $cmd = sprintf(
                '%s %s --object-streams=disable --force-version=1.4 %s 2>&1',
                escapeshellarg($qpdfBin),
                escapeshellarg($absolutePath),
                escapeshellarg($tempPath)
            );

            exec($cmd, $output, $returnCode);

            if ($returnCode === 0 && file_exists($tempPath) && filesize($tempPath) > 0) {
                $tempFilesToClean[] = $tempPath;
                return $tempPath;
            }

            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            return $absolutePath;
        }
    }

}