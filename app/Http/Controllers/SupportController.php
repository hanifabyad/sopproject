<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use App\Models\DocumentApproval;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webklex\PDFMerger\Facades\PDFMergerFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewDocumentReviewMail;

class SupportController extends Controller
{
    /**
     * Dashboard Utama: Menampilkan status dokumen per departemen.
     *
     */
    public function index(): View
    {
        $departments = ['HC', 'IT', 'QMS', 'HSE', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];
        $stats = [];

        foreach ($departments as $dept) {
            $docs = Document::where('department', $dept)->get();
            
            $stats[$dept] = [
                'total'    => $docs->count(),
                'active'   => $docs->where('status', 'active')->count(),
                'inactive' => $docs->where('status', 'need_revision')->count(),
            ];
        }

        return view('admin.support.index', compact('stats'));
    }

    /**
     * Daftar Dokumen: Menampilkan semua SOP dalam satu departemen.
     *
     */
    public function show(string $department): View
    {
        $documents = Document::where('department', $department)->latest()->get();

        $stats = [
            'name'     => $department,
            'approved' => $documents->where('status', 'active')->count(),
            'waiting'  => $documents->where('status', 'waiting')->count(),
            'revisi'   => $documents->where('status', 'need_revision')->count(),
            'active'   => $documents->where('status', 'active')->count(),
            'inactive' => $documents->where('status', 'need_revision')->count(),
        ];

        return view('admin.support.show', compact('documents', 'stats'));
    }

    /**
     * Form Unggah: Menyiapkan daftar peninjau untuk dipilih Admin.
     *
     */
    public function create(string $department): View
    {
        $reviewers = User::all(); 
        return view('admin.support.create', compact('department', 'reviewers'));
    }

    /**
     * Store: Menggabungkan 4 PDF, membuat antrean approval berantai, dan mengirim email.
     *
     */
    public function store(Request $request, string $department): RedirectResponse
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

        // 3. Proses Penggabungan (Merge): Cover -> LP -> Isi -> Lampiran
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

        // 4. Simpan Data SOP ke Database
        $document = Document::create([
            'title'         => $request->title,
            'department'    => $department,
            'reviewer_id'   => $request->approvers[0], 
            'file_cover'    => $pathCover,
            'file_lp'       => $pathLp,
            'file_isi'      => $pathIsi,
            'file_lampiran' => $pathLamp,
            'file_preview'  => $previewPath,
            'status'        => 'waiting',
        ]);

        // 5. Generate Data Antrean Berantai Awal (Document Approvals)
        foreach ($request->approvers as $index => $approverId) {
            DocumentApproval::create([
                'document_id' => $document->id,
                'user_id'     => $approverId,
                'sequence'    => $index + 1, 
                'status'      => ($index === 0) ? 'current' : 'pending', 
            ]);
        }

        // 6. Logika Pengiriman Email Notifikasi + Magic Link
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
                    new NewDocumentReviewMail($document, $firstReviewer, $magicLoginUrl)
                );
            } catch (\Throwable $e) {
                \Log::error("e-QMS Support Email Error: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.support.show', $department)
                         ->with('success', 'Alur Pengesahan Berantai Support Berhasil Dimulai!');
    }

    /**
     * Audit Trail: Melihat detail perjalanan dan komentar dokumen.
     *
     */
    public function documentDetail(int $id): View
    {
        $document = Document::with('logs.user')->findOrFail($id);
        $reviewers = User::all();
        $pathFinal = $document->file_final ?? $document->file_preview ?? $document->file_lp ?? $document->file_path;
        
        return view('admin.support.document_detail', compact('document', 'reviewers', 'pathFinal'));
    }

    /**
     * Estafet: Mengoper dokumen ke peninjau (Reviewer) lain.
     *
     */
    public function updateReviewer(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reviewer_id' => 'required|exists:users,id',
        ]);

        $document = Document::findOrFail($id);
        $newUser = User::find($request->reviewer_id);

        // 1. Update reviewer_id pada tabel documents
        $document->update([
            'reviewer_id' => $newUser->id,
            'status'      => 'waiting'
        ]);

        // 2. Update user_id pada antrean document_approvals yang berstatus current
        $currentApproval = DocumentApproval::where('document_id', $document->id)
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

                Mail::to($newUser->email)->send(
                    new NewDocumentReviewMail($document, $newUser, $magicLoginUrl)
                );
            } catch (\Throwable $e) {
                \Log::error("e-QMS Support Email Transfer Error: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Dokumen berhasil dioper ke ' . $newUser->username);
    }

public function destroy($id)
{
    $document = Document::findOrFail($id);
    
    // 1. Hapus file fisik dari folder storage agar tidak menumpuk
    $filePath = storage_path('app/public/' . $document->file_path);
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // 2. Hapus data dari database
    $document->delete();

    // 3. Kembali ke daftar departemen dengan pesan sukses
    return redirect()->route('admin.support.show', $document->department)
                     ->with('success', 'Dokumen berhasil dihapus secara permanen!');
}

    // ================================================================
    // 🔥 BARU: MENAMPILKAN FORM REVISI DOKUMEN SUPPORT
    // ================================================================
    public function editRevision(int $id): View
    {
        $document = Document::with('approvals.user')->findOrFail($id);
        return view('admin.support.edit_revision', compact('document'));
    }

    // ================================================================
    // 🔥 BARU: MEMPROSES UPLOAD FILE REVISI SUPPORT & ESTAFET PINTAR
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
            $merger = PDFMergerFacade::init();
            
            if ($request->hasFile('file_isi')) {
                if (!empty($document->file_preview) && file_exists(storage_path('app/public/' . $document->file_preview))) {
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
                $isiPdf = $this->ensurePdfCompatible(storage_path('app/public/' . $pathIsi), $tempFilesToClean);
                $merger->addPDF($isiPdf, 'all');
            } else {
                if (!empty($document->file_preview) && file_exists(storage_path('app/public/' . $document->file_preview))) {
                    $prevPdf = $this->ensurePdfCompatible(storage_path('app/public/' . $document->file_preview), $tempFilesToClean);
                    $merger->addPDF($prevPdf, 'all');
                }
            }

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

            if (!empty($activeLamp) && is_string($activeLamp)) {
                $fullLampPath = storage_path('app/public/' . $activeLamp);
                if (file_exists($fullLampPath)) {
                    $lampPdf = $this->ensurePdfCompatible($fullLampPath, $tempFilesToClean);
                    $merger->addPDF($lampPdf, 'all');
                }
            }

            $merger->merge();
            $previewName = 'preview_rev_' . time() . '.pdf';
            $previewPath = 'documents/previews/' . $previewName;
            
            Storage::disk('public')->put($previewPath, $merger->output());

        } catch (\Exception $e) {
            return back()->withErrors(['msg' => "Gagal Merge PDF Revisi: " . $e->getMessage()]);
        } finally {
            foreach ($tempFilesToClean as $tempFile) {
                if (file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            }
        }

        // 4. GENERATE ULANG VARIABEL TARGET ANTREAN
        $nextTargetApproval = DocumentApproval::where('document_id', $document->id)
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
            'reviewer_id'   => $nextTargetApproval->user_id,
            'file_cover'    => $pathCover,
            'file_lp'       => $pathLp,
            'file_isi'      => $pathIsi,
            'file_lampiran' => $pathLamp,
            'file_preview'  => $previewPath,
            'status'        => 'waiting', 
        ]);

        // 7. Catat Aksi ke Timeline Log
        $document->logs()->create([
            'user_id' => auth()->id() ?? 1,
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

                Mail::to($reviewerUser->email)->send(
                    new NewDocumentReviewMail($document, $reviewerUser, $magicLoginUrl)
                );
            } catch (\Exception $e) {
                \Log::error("e-QMS Support Email Revisi Error: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.support.document.detail', $document->id)
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

            $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qpdf_merge_norm_' . uniqid() . '.pdf';
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