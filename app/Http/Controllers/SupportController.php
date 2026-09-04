<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\DocumentAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewDocumentReviewMail;
use Webklex\PDFMerger\Facades\PDFMergerFacade;

class SupportController extends Controller
{
    /**
     * Dashboard Utama Support: Menampilkan status dokumen per departemen.
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
            'inactive' => $docs = $documents->where('status', 'need_revision')->count(),
        ];

        return view('admin.support.show', compact('documents', 'stats'));
    }

    /**
     * Form Unggah: Menyiapkan daftar peninjau untuk dipilih Admin.
     */
    public function create(Request $request, string $department): View
    {
        $reviewers = User::all();
        $fromRequest = null;
        if ($request->filled('from_request_id')) {
            $fromRequest = \App\Models\NewSopRequest::find($request->from_request_id);
        }
        return view('admin.support.create', compact('department', 'reviewers', 'fromRequest'));
    }

    /**
     * Menyimpan dokumen baru Support, memvalidasi 4 berkas PDF, menggabungkannya,
     * serta otomatis mendeteksi penandatangan dari LP dan membuat antrean approval.
     */
    public function store(Request $request, string $department)
    {
        $uploadedNewFiles = [];

        try {
            // 1. Validasi Input
            $request->validate([
                'title'           => 'required|string|max:255',
                'file_cover'      => 'required|mimes:pdf|max:5000',
                'file_isi'        => 'required|mimes:pdf|max:10000',
                'file_lampiran'   => 'nullable|array|max:20',
                'file_lampiran.*' => 'file|mimes:pdf|max:5000',
                'company_header'  => 'required|string|max:50',
                'doc_number'      => 'required|string|max:255',
                'doc_revision'    => 'required|string|max:50',
                'doc_date'        => 'required|date',
                'creator_id'      => 'required|exists:users,id',
                'reviewers'       => 'required|array|min:3',
                'reviewers.*'     => 'exists:users,id|distinct',
                'final_ids'       => 'nullable|array|min:1',
                'final_ids.*'     => 'exists:users,id|distinct',
                'final_additional_ids'   => 'nullable|array',
                'final_additional_ids.*' => 'exists:users,id|distinct',
                'final_id'        => 'nullable|exists:users,id',
            ]);

            // 2. Simpan Berkas Fisik Asli
            $pathCover = $request->file('file_cover')->store('documents/covers', 'public');
            $uploadedNewFiles[] = $pathCover;

            $pathIsi = $request->file('file_isi')->store('documents/contents', 'public');
            $uploadedNewFiles[] = $pathIsi;

            $attachmentData = [];
            if ($request->hasFile('file_lampiran')) {
                $files = $request->file('file_lampiran');
                if (!is_array($files)) {
                    $files = [$files];
                }
                foreach ($files as $idx => $lampFile) {
                    $storedPath = $lampFile->store('documents/attachments', 'public');
                    $uploadedNewFiles[] = $storedPath;
                    $attachmentData[] = [
                        'original_name' => $lampFile->getClientOriginalName(),
                        'stored_name'   => basename($storedPath),
                        'file_path'     => $storedPath,
                        'mime_type'     => $lampFile->getClientMimeType() ?: 'application/pdf',
                        'file_size'     => $lampFile->getSize(),
                        'sequence'      => $idx + 1,
                    ];
                }
            }

            // 3. Validasi Halaman Cover (Harus tepat 1 Halaman) dan hitung halaman
            $pdfParser = new \Smalot\PdfParser\Parser();
            
            $coverPdf = $pdfParser->parseFile(storage_path('app/public/' . $pathCover));
            if (count($coverPdf->getPages()) !== 1) {
                throw new \Exception('File Cover harus 1 halaman. Silakan periksa kembali berkas Cover yang diunggah.');
            }

            $isiPdf = $pdfParser->parseFile(storage_path('app/public/' . $pathIsi));
            $isiPageCount = count($isiPdf->getPages());

            $attachmentsPageCount = 0;
            foreach ($attachmentData as $att) {
                $lampPdf = $pdfParser->parseFile(storage_path('app/public/' . $att['file_path']));
                $attachmentsPageCount += count($lampPdf->getPages());
            }

            $totalPages = 1 + $isiPageCount + $attachmentsPageCount; // LP + Content + Attachments; cover tidak dihitung

            // Retrieve signers
            $creatorUser = User::findOrFail($request->creator_id);
            $finalIds = array_values(array_filter((array)$request->input('final_additional_ids', [])));
            $finalIds = array_merge($finalIds, array_values(array_filter((array)$request->input('final_ids', []))));
            if (empty($finalIds) && $request->filled('final_id')) $finalIds = [$request->input('final_id')];
            $defaultFinal = User::whereRaw('LOWER(username) = ?', ['zikri'])->first();
            if ($defaultFinal) {
                $finalIds = array_values(array_filter($finalIds, fn ($id) => (string)$id !== (string)$defaultFinal->id));
            }
            
            // Enforce Hendro accounts are placed directly before Zikri
            $hendroIds = User::whereIn('id', $finalIds)
                ->where(function($q) {
                    $q->whereRaw('LOWER(username) LIKE ?', ['hendro%']);
                })
                ->pluck('id')
                ->toArray();
            if (!empty($hendroIds)) {
                $finalIds = array_values(array_filter($finalIds, fn ($id) => !in_array($id, $hendroIds)));
                $finalIds = array_merge($finalIds, $hendroIds);
            }

            if ($defaultFinal) {
                $finalIds[] = $defaultFinal->id;
            }
            if (empty($finalIds)) throw new \Exception('Select at least one final approver.');
            $finalUsers = User::whereIn('id', $finalIds)->get()->sortBy(fn ($user) => array_search($user->id, $finalIds))->values();
            $reviewerIdsOrdered = $request->reviewers;
            $reviewerUsers = User::whereIn('id', $reviewerIdsOrdered)->get()->sortBy(function($user) use ($reviewerIdsOrdered) {
                return array_search($user->id, $reviewerIdsOrdered);
            })->values();

            // Generate LP PDF using LpGeneratorService
            $lpGenerator = new \App\Services\LpGeneratorService();
            $lpData = [
                'title'          => $request->title,
                'doc_number'     => $request->doc_number,
                'doc_revision'   => $request->doc_revision,
                'doc_date'       => date('d F Y', strtotime($request->doc_date)),
                'revision_history' => [0 => $request->doc_date],
                'company_header' => $request->company_header,
                'total_pages'    => $totalPages
            ];

            $lpResult = $lpGenerator->generate($lpData, $creatorUser, $reviewerUsers, $finalUsers);
            $pathLp = $lpResult['file_path'];
            $coordinates = $lpResult['coordinates'];
            $uploadedNewFiles[] = $pathLp;

            // 4. Proses Penggabungan (Merge): Cover -> LP -> Isi -> Lampiran 1..N
            $tempFilesToClean = [];
            try {
                $mergeCover = $this->ensurePdfCompatible(storage_path('app/public/' . $pathCover), $tempFilesToClean);
                $mergeLp    = $this->ensurePdfCompatible(storage_path('app/public/' . $pathLp), $tempFilesToClean);
                $mergeIsi   = $this->ensurePdfCompatible(storage_path('app/public/' . $pathIsi), $tempFilesToClean);

                $merger = PDFMergerFacade::init();
                $merger->addPDF($mergeCover, 'all');
                $merger->addPDF($mergeLp, 'all');
                $merger->addPDF($mergeIsi, 'all');

                foreach ($attachmentData as $att) {
                    $mergeLamp = $this->ensurePdfCompatible(storage_path('app/public/' . $att['file_path']), $tempFilesToClean);
                    $merger->addPDF($mergeLamp, 'all');
                }

                $merger->merge();
                $previewName = 'preview_' . time() . '.pdf';
                $previewPath = 'documents/previews/' . $previewName;

                Storage::disk('public')->put($previewPath, $merger->output());
                $uploadedNewFiles[] = $previewPath;
            } finally {
                foreach ($tempFilesToClean as $tempFile) {
                    if (file_exists($tempFile)) {
                        @unlink($tempFile);
                    }
                }
            }

            // 5. Simpan ke DB & Buat Antrean Approval
            $createdDocument = null;
            $creatorUserObj = $creatorUser;

            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $department, $pathCover, $pathLp, $pathIsi, $attachmentData, $previewPath, $coordinates, $creatorUser, $reviewerUsers, $finalUsers, &$createdDocument) {
                $firstPathLamp = !empty($attachmentData) ? $attachmentData[0]['file_path'] : null;

                $createdDocument = Document::create([
                    'title'          => $request->title,
                    'department'     => $department,
                    'reviewer_id'    => $creatorUser->id, 
                    'file_cover'     => $pathCover,
                    'file_lp'        => $pathLp,
                    'file_isi'       => $pathIsi,
                    'file_lampiran'  => $firstPathLamp,
                    'file_preview'   => $previewPath,
                    'status'         => 'waiting',
                    'doc_number'     => $request->doc_number,
                    'doc_revision'   => $request->doc_revision,
                    'doc_date'       => $request->doc_date,
                    'company_header' => $request->company_header,
                ]);

                foreach ($attachmentData as $att) {
                    $createdDocument->attachments()->create($att);
                }

                $signersList = [];
                $seq = 1;

                // 1. Creator
                $signersList[] = [
                    'user' => $creatorUser,
                    'stage' => 'creator',
                    'status' => 'current',
                    'sequence' => $seq++
                ];

                // 2. Reviewers
                foreach ($reviewerUsers as $revUser) {
                    $signersList[] = [
                        'user' => $revUser,
                        'stage' => 'reviewer',
                        'status' => 'pending',
                        'sequence' => $seq++
                    ];
                }

                // 3. Final Approver
                foreach ($finalUsers as $finalUser) {
                    $signersList[] = [
                        'user' => $finalUser,
                        'stage' => 'final',
                        'status' => 'pending',
                        'sequence' => $seq++
                    ];
                }

                foreach ($signersList as $s) {
                    $approverUser = $s['user'];
                    $slotMeta = DocumentApproval::getSlotAndStageForUser($approverUser);

                    if (empty(trim($approverUser->full_name ?? ''))) {
                        throw new \Exception(
                            "Nama lengkap pegawai '{$approverUser->username}' belum dikonfigurasi. Silakan lengkapi data pegawai terlebih dahulu sebelum dipilih sebagai penandatangan."
                        );
                    }

                    // Map dynamic coordinates from generator
                    static $userOccurrences = [];
                    $occurrence = $userOccurrences[$approverUser->id] ?? 0;
                    $userOccurrences[$approverUser->id] = $occurrence + 1;
                    $pos = collect($coordinates)->first(fn ($coordinate) => $coordinate['user_id'] == $approverUser->id && ($coordinate['occurrence'] ?? 0) === $occurrence);
                    if (!$pos) {
                        throw new \Exception("Gagal menentukan koordinat tanda tangan untuk {$approverUser->full_name}.");
                    }

                    DocumentApproval::create([
                        'document_id'      => $createdDocument->id,
                        'user_id'          => $approverUser->id,
                        'sequence'         => $s['sequence'],
                        'stage'            => $s['stage'],
                        'status'           => $s['status'],
                        'notes'            => null,
                        'signature_slot'   => $s['stage'] === 'final' ? 'sig09_final_' . $occurrence : $slotMeta['signature_slot'],
                        'signature_page'   => $pos['page'],
                        'signature_x'      => $pos['x'],
                        'signature_y'      => $pos['y'],
                    ]);
                }

                $createdDocument->logs()->create([
                    'user_id' => auth()->id() ?? $creatorUser->id,
                    'action'  => 'diunggah',
                    'notes'   => 'Dokumen baru diunggah dan otomatis memicu alur persetujuan ke ' . ($creatorUser->full_name ?? $creatorUser->username) . '.'
                ]);

                // Hubungkan dengan Pengajuan SOP Baru jika berasal dari request pemohon
                if ($request->filled('from_request_id')) {
                    \App\Models\NewSopRequest::where('id', $request->from_request_id)->update([
                        'document_id' => $createdDocument->id,
                        'status'      => 'in_progress',
                        'admin_id'    => auth()->id(),
                        'admin_notes' => 'Dokumen SOP telah dibuat oleh Admin QMS dan kini memasuki alur review/approval.',
                        'reviewed_at' => now(),
                    ]);
                }
            });

            // Kirim Email Notifikasi ke Creator
            if ($creatorUserObj && $creatorUserObj->email) {
                try {
                    $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                        'login.magic',
                        now()->addHours(24),
                        [
                            'user_id' => $creatorUserObj->id,
                            'document_id' => $createdDocument->id
                        ]
                    );

                    Mail::to($creatorUserObj->email)->queue(
                        new NewDocumentReviewMail($createdDocument, $creatorUserObj, $magicLoginUrl)
                    );
                } catch (\Exception $e) {
                    \Log::error("e-QMS Email Store Notification Error: " . $e->getMessage());
                }
            }

            return redirect()->route('admin.support.show', $department)
                ->with('success', 'Dokumen berhasil diunggah. Alur persetujuan telah otomatis dikirimkan ke ' . ($creatorUserObj->username ?? 'Pembuat Dokumen'));

        } catch (\Throwable $e) {
            \Log::error("e-QMS Document Store Error: " . $e->getMessage());
            Storage::disk('public')->delete(array_filter($uploadedNewFiles));
            return back()->withInput()->withErrors(['msg' => $e->getMessage() ?: 'Dokumen gagal diproses. Silakan periksa berkas yang diunggah atau hubungi administrator.']);
        }
    }

    /**
     * Menampilkan detail dokumen Support
     */
    public function documentDetail(int $id): View
    {
        $document = Document::with(['reviewer', 'approvals.user', 'logs.user', 'attachments'])->findOrFail($id);
        $approvals = $document->approvals;
        $pathFinal = $document->file_final;
        $reviewers = User::all();
        return view('admin.support.document_detail', compact('document', 'approvals', 'pathFinal', 'reviewers'));
    }

    /**
     * Alias method untuk documentDetail
     */
    public function detail(int $id): View
    {
        return $this->documentDetail($id);
    }

    /**
     * Oper/pindahkan reviewer dokumen Support
     */
    public function updateReviewer(Request $request, int $id)
    {
        $request->validate([
            'new_reviewer_id' => 'required|exists:users,id',
        ]);

        $document = Document::findOrFail($id);
        $newUser = User::findOrFail($request->new_reviewer_id);

        $document->update([
            'reviewer_id' => $newUser->id,
        ]);

        $currentApproval = DocumentApproval::where('document_id', $document->id)
            ->where('status', 'current')
            ->first();

        if ($currentApproval) {
            $currentApproval->update([
                'user_id' => $newUser->id
            ]);
        }

        $document->logs()->create([
            'user_id' => auth()->id() ?? 1,
            'action'  => 'dioper',
            'notes'   => 'Dokumen dioper ke peninjau baru: ' . $newUser->username,
        ]);

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

                Mail::to($newUser->email)->queue(
                    new NewDocumentReviewMail($document, $newUser, $magicLoginUrl)
                );
            } catch (\Exception $e) {
                \Log::error("e-QMS Support Email Transfer Error: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Dokumen berhasil dioper ke ' . $newUser->username);
    }

    /**
     * Alias method untuk updateReviewer
     */
    public function transfer(Request $request, int $id)
    {
        return $this->updateReviewer($request, $id);
    }

    /**
     * Menghapus dokumen Support secara permanen beserta berkas fisiknya
     */
    public function destroy($id)
    {
        $document = Document::with('attachments')->findOrFail($id);
        $department = $document->department;

        $filesToDelete = [
            $document->file_cover,
            $document->file_lp,
            $document->file_isi,
            $document->file_lampiran,
            $document->file_preview,
            $document->file_final,
        ];

        foreach ($document->attachments as $att) {
            $filesToDelete[] = $att->file_path;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($document) {
            $document->attachments()->delete();
            $document->approvals()->delete();
            $document->logs()->delete();
            $document->delete();
        });

        foreach ($filesToDelete as $file) {
            if (!empty($file)) {
                $fullPath = storage_path('app/public/' . $file);
                if (file_exists($fullPath) && is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }
        }

        return redirect()->route('admin.support.show', $department)
                         ->with('success', 'Dokumen berhasil dihapus secara permanen!');
    }

    /**
     * Menampilkan form revisi dokumen Support
     */
    public function editRevision(int $id): View
    {
        $document = Document::with(['approvals.user', 'attachments'])->findOrFail($id);
        return view('admin.support.edit_revision', compact('document'));
    }

    /**
     * Memproses upload file revisi Support & estafet pintar
     */
    public function updateRevision(Request $request, int $id)
    {
        $uploadedNewFiles = [];
        $oldPhysicalFilesToDelete = [];

        try {
            $request->validate([
                'title'                 => 'required|string|max:255',
                'doc_revision'          => 'nullable|string|max:50',
                'file_cover'            => 'nullable|mimes:pdf|max:5000',
                'file_lp'               => 'nullable|mimes:pdf|max:5000',
                'file_isi'              => 'nullable|mimes:pdf|max:10000',
                'file_lampiran'         => 'nullable|array|max:20',
                'file_lampiran.*'       => 'file|mimes:pdf|max:5000',
                'deleted_attachments'   => 'nullable|array',
                'deleted_attachments.*' => 'integer',
            ]);

            $document = Document::with('attachments')->findOrFail($id);

            // Validasi: Wajib mengunggah minimal 1 berkas perbaikan baru atau perubahan
            $hasNewFile = $request->hasFile('file_cover') 
                || $request->hasFile('file_isi') 
                || $request->hasFile('file_lp') 
                || ($request->hasFile('file_lampiran') && count($request->file('file_lampiran')) > 0);
            $hasDeletedAttachments = !empty($request->input('deleted_attachments', []));
            $isTitleChanged = trim($request->input('title', '')) !== trim($document->title);
            $isRevisionChanged = $request->filled('doc_revision') && trim($request->input('doc_revision')) !== trim((string)$document->doc_revision);

            if (!$hasNewFile && !$hasDeletedAttachments && !$isTitleChanged && !$isRevisionChanged) {
                return back()->withErrors([
                    'file_isi' => 'Harap unggah minimal 1 berkas perbaikan baru (File Cover, File Isi SOP, atau Lampiran Baru) atau ubah data revisi sebelum mengirim revisi.',
                ])->withInput();
            }

            $unit = $document->department;
            $nextRevision = $request->filled('doc_revision') ? trim((string)$request->input('doc_revision')) : (string)((int)$document->doc_revision + 1);
            $revisionDate = now()->toDateString();

            // Handle Cover
            $pathCover = $document->file_cover;
            if ($request->hasFile('file_cover')) {
                $pathCover = $request->file('file_cover')->store('documents/covers', 'public');
                $uploadedNewFiles[] = $pathCover;
            }

            // Handle LP
            $pathLp = $document->file_lp;
            $isAutoGenerated = !empty($document->company_header);
            $coordinates = [];

            if ($isAutoGenerated) {
                // Generated dynamically later
            } else {
                if ($request->hasFile('file_lp')) {
                    $pathLp = $request->file('file_lp')->store('documents/lps', 'public');
                    $uploadedNewFiles[] = $pathLp;
                }
            }

            // Handle Isi
            $pathIsi = $document->file_isi;
            if ($request->hasFile('file_isi')) {
                $pathIsi = $request->file('file_isi')->store('documents/contents', 'public');
                $uploadedNewFiles[] = $pathIsi;
            }

            // Handle Attachments Revision
            $existingAttachments = $document->all_attachments;
            $deletedIds = $request->input('deleted_attachments', []);
            if (!is_array($deletedIds)) {
                $deletedIds = [];
            }

            $keptAttachments = [];
            $recordsToDelete = [];

            foreach ($existingAttachments as $att) {
                if (in_array((int)$att->id, array_map('intval', $deletedIds), true) || in_array((string)$att->id, $deletedIds, true)) {
                    $recordsToDelete[] = $att;
                    if (!empty($att->file_path)) {
                        $oldPhysicalFilesToDelete[] = $att->file_path;
                    }
                } else {
                    $keptAttachments[] = $att;
                }
            }

            $newAttachmentData = [];
            if ($request->hasFile('file_lampiran')) {
                $files = $request->file('file_lampiran');
                if (!is_array($files)) {
                    $files = [$files];
                }
                foreach ($files as $lampFile) {
                    $storedPath = $lampFile->store('documents/attachments', 'public');
                    $uploadedNewFiles[] = $storedPath;
                    $newAttachmentData[] = [
                        'original_name' => $lampFile->getClientOriginalName(),
                        'stored_name'   => basename($storedPath),
                        'file_path'     => $storedPath,
                        'mime_type'     => $lampFile->getClientMimeType() ?: 'application/pdf',
                        'file_size'     => $lampFile->getSize(),
                    ];
                }
            }

            $totalAttachmentsCount = count($keptAttachments) + count($newAttachmentData);
            if ($totalAttachmentsCount > 20) {
                throw new \Exception("Jumlah total lampiran setelah revisi tidak boleh melebihi 20 file. Saat ini: {$totalAttachmentsCount} file.");
            }

            // Generate LP dynamically if auto-generated
            if ($isAutoGenerated) {
                $pdfParser = new \Smalot\PdfParser\Parser();
                
                $coverPdf = $pdfParser->parseFile(storage_path('app/public/' . $pathCover));
                if (count($coverPdf->getPages()) !== 1) {
                    throw new \Exception('File Cover harus 1 halaman.');
                }

                $isiPdf = $pdfParser->parseFile(storage_path('app/public/' . $pathIsi));
                $isiPageCount = count($isiPdf->getPages());

                $attachmentsPageCount = 0;
                foreach ($keptAttachments as $att) {
                    if (!empty($att->file_path) && file_exists(storage_path('app/public/' . $att->file_path))) {
                        $lampPdf = $pdfParser->parseFile(storage_path('app/public/' . $att->file_path));
                        $attachmentsPageCount += count($lampPdf->getPages());
                    }
                }
                foreach ($newAttachmentData as $nAtt) {
                    if (!empty($nAtt['file_path']) && file_exists(storage_path('app/public/' . $nAtt['file_path']))) {
                        $lampPdf = $pdfParser->parseFile(storage_path('app/public/' . $nAtt['file_path']));
                        $attachmentsPageCount += count($lampPdf->getPages());
                    }
                }

                $totalPages = 1 + $isiPageCount + $attachmentsPageCount; // LP + Content + Attachments; cover tidak dihitung

                $creatorApp = DocumentApproval::where('document_id', $document->id)->where('stage', 'creator')->first();
                $reviewerApps = DocumentApproval::where('document_id', $document->id)->where('stage', 'reviewer')->orderBy('sequence', 'asc')->get();
                $finalApps = DocumentApproval::where('document_id', $document->id)->where('stage', 'final')->orderBy('sequence')->get();

                $creatorUser = $creatorApp->user;
                $reviewerUsers = $reviewerApps->map->user;
                $finalUsers = $finalApps->map->user;

                $lpGenerator = new \App\Services\LpGeneratorService();
                $lpData = [
                    'title'          => $request->title,
                    'doc_number'     => $document->doc_number,
                    'doc_revision'   => $nextRevision,
                    'doc_date'       => date('d F Y', strtotime($revisionDate)),
                'revision_date'  => $revisionDate,
                'revision_history' => collect([0 => $document->created_at])
                    ->merge($document->logs()->where('action', 'revisi')->where('notes', 'like', '%unggah%')->orderBy('created_at')->pluck('created_at')->mapWithKeys(fn ($date, $index) => [$index + 1 => $date]))
                    ->all(),
                'company_header' => $document->company_header,
                    'total_pages'    => $totalPages
                ];

                $lpResult = $lpGenerator->generate($lpData, $creatorUser, $reviewerUsers, $finalUsers);
                $pathLp = $lpResult['file_path'];
                $coordinates = $lpResult['coordinates'];
                $uploadedNewFiles[] = $pathLp;
            }

            // Proses Penggabungan (Merge PDF)
            $tempFilesToClean = [];
            try {
                $merger = PDFMergerFacade::init();
                
                if ($isAutoGenerated) {
                    $covPdf = $this->ensurePdfCompatible(storage_path('app/public/' . $pathCover), $tempFilesToClean);
                    $lpPdf = $this->ensurePdfCompatible(storage_path('app/public/' . $pathLp), $tempFilesToClean);
                    $isiPdf = $this->ensurePdfCompatible(storage_path('app/public/' . $pathIsi), $tempFilesToClean);

                    $merger->addPDF($covPdf, 'all');
                    $merger->addPDF($lpPdf, 'all');
                    $merger->addPDF($isiPdf, 'all');
                } else {
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
                }

                // Add kept attachments
                foreach ($keptAttachments as $att) {
                    if (!empty($att->file_path) && file_exists(storage_path('app/public/' . $att->file_path))) {
                        $lampPdf = $this->ensurePdfCompatible(storage_path('app/public/' . $att->file_path), $tempFilesToClean);
                        $merger->addPDF($lampPdf, 'all');
                    }
                }

                // Add new attachments
                foreach ($newAttachmentData as $nAtt) {
                    if (!empty($nAtt['file_path']) && file_exists(storage_path('app/public/' . $nAtt['file_path']))) {
                        $lampPdf = $this->ensurePdfCompatible(storage_path('app/public/' . $nAtt['file_path']), $tempFilesToClean);
                        $merger->addPDF($lampPdf, 'all');
                    }
                }

                $merger->merge();
                $previewName = 'preview_rev_' . time() . '.pdf';
                $previewPath = 'documents/previews/' . $previewName;
                
                Storage::disk('public')->put($previewPath, $merger->output());
                $uploadedNewFiles[] = $previewPath;

                // Tentukan target antrean peninjau berikutnya yang memerlukan revisi
                $rejectedApprovals = DocumentApproval::where('document_id', $document->id)
                    ->where('status', 'rejected')
                    ->orderBy('sequence', 'asc')
                    ->get();

                $isFullRevisionReset = false;
                $approvalsToActivate = collect();

                if ($rejectedApprovals->isNotEmpty()) {
                    $approvalsToActivate = $rejectedApprovals;
                } else {
                    // Fallback 1: Jika ada yang berstatus 'current' atau 'waiting'
                    $waitingApprovals = DocumentApproval::where('document_id', $document->id)
                        ->whereIn('status', ['current', 'waiting'])
                        ->orderBy('sequence', 'asc')
                        ->get();

                    if ($waitingApprovals->isNotEmpty()) {
                        $approvalsToActivate = $waitingApprovals;
                    } else {
                        // Fallback 2: Siklus Revisi Dokumen Aktif (seluruh approval sebelumnya 'approved')
                        $isFullRevisionReset = true;
                        $reviewerApprovals = DocumentApproval::where('document_id', $document->id)
                            ->where('stage', 'reviewer')
                            ->orderBy('sequence', 'asc')
                            ->get();

                        if ($reviewerApprovals->isNotEmpty()) {
                            $approvalsToActivate = $reviewerApprovals;
                        } else {
                            $firstFinal = DocumentApproval::where('document_id', $document->id)
                                ->where('stage', 'final')
                                ->orderBy('sequence', 'asc')
                                ->first();
                            if ($firstFinal) {
                                $approvalsToActivate = collect([$firstFinal]);
                            }
                        }
                    }
                }

                if ($approvalsToActivate->isEmpty()) {
                    throw new \Exception("Tidak ditemukan antrean peninjau yang memerlukan revisi.");
                }

                // Redraw currently approved stamps if any on auto-generated document
                if ($isAutoGenerated) {
                    $allApprovedQuery = DocumentApproval::where('document_id', $document->id)
                        ->where('status', 'approved')
                        ->orderBy('sequence', 'asc');

                    // Jika revisi penuh dokumen aktif, hanya stempel Creator yang dipertahankan awal
                    if ($isFullRevisionReset) {
                        $allApprovedQuery->where('stage', 'creator');
                    }

                    $allApproved = $allApprovedQuery->get();

                    if ($allApproved->isNotEmpty()) {
                        $pdfStamper = new \setasign\Fpdi\Fpdi();
                        $previewAbs = storage_path('app/public/' . $previewPath);
                        $pageCount = $pdfStamper->setSourceFile($previewAbs);

                        $stampsToDraw = [];
                        $userOccurrences = [];
                        foreach ($allApproved as $appItem) {
                            $occurrence = $userOccurrences[$appItem->user_id] ?? 0;
                            $userOccurrences[$appItem->user_id] = $occurrence + 1;
                            $pos = collect($coordinates)->first(fn ($coordinate) => (int)$coordinate['user_id'] === (int)$appItem->user_id && (int)($coordinate['occurrence'] ?? 0) === $occurrence);
                            $x = $pos ? $pos['x'] : (float)$appItem->signature_x;
                            $y = $pos ? $pos['y'] : (float)$appItem->signature_y;
                            $page = $pos ? $pos['page'] : ($appItem->signature_page ?? 1);

                            if ($x !== null && $y !== null) {
                                $stampsToDraw[] = [
                                    'x'            => (float)$x,
                                    'y'            => (float)$y,
                                    'page'         => (int)$page,
                                    'username'     => $appItem->user->username ?? 'user',
                                    'processed_at' => $appItem->processed_at ?? now(),
                                    'stage'        => $appItem->stage
                                ];
                            }
                        }

                        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                            $templateId = $pdfStamper->importPage($pageNo);
                            $size = $pdfStamper->getTemplateSize($templateId);
                            $pdfStamper->AddPage($size['orientation'], [$size['width'], $size['height']]);
                            $pdfStamper->useTemplate($templateId);

                            foreach ($stampsToDraw as $stamp) {
                                $stampTargetPage = ($pageCount > 1) ? ($stamp['page'] + 1) : $stamp['page'];
                                if ($pageNo == $stampTargetPage) {
                                    $carbonDate = \Carbon\Carbon::parse($stamp['processed_at'])->timezone('Asia/Jakarta');
                                    $dLine1 = $carbonDate->format('d/m/Y');
                                    $dLine2 = $carbonDate->format('H:i \W\I\B');

                                    // Hitung rowHeight dinamis berdasarkan jumlah reviewer
                                    $numReviewers = DocumentApproval::where('document_id', $document->id)
                                        ->where('stage', 'reviewer')
                                        ->count();
                                    $rowHeight = 12.0;
                                    if ($numReviewers > 5) {
                                        $rowHeight = max(8.0, 12.0 - ($numReviewers - 5) * 1.2);
                                    }
                                    $this->drawDigitalStampInController($pdfStamper, $stamp['x'], $stamp['y'], $stamp['username'], $rowHeight);
                                    $this->drawDateStampInController($pdfStamper, $size['width'], $stamp['y'], $dLine1, $dLine2);
                                }
                            }
                        }

                        $pdfStamper->Output('F', $previewAbs);
                    }
                }

            } finally {
                foreach ($tempFilesToClean as $tempFile) {
                    if (file_exists($tempFile)) {
                        @unlink($tempFile);
                    }
                }
            }

            $usersToNotify = [];

            \Illuminate\Support\Facades\DB::transaction(function () use ($document, $recordsToDelete, $keptAttachments, $newAttachmentData, $approvalsToActivate, $isFullRevisionReset, $request, $pathCover, $pathLp, $pathIsi, $previewPath, $isAutoGenerated, $coordinates, $nextRevision, $revisionDate, &$usersToNotify) {
                // Hapus record attachment dari DB
                foreach ($recordsToDelete as $r) {
                    if ($r->id > 0) {
                        DocumentAttachment::where('id', $r->id)->delete();
                    }
                }

                // Update sequence lampiran lama yang dipertahankan
                $seq = 1;
                foreach ($keptAttachments as $kAtt) {
                    if ($kAtt->id > 0) {
                        DocumentAttachment::where('id', $kAtt->id)->update(['sequence' => $seq++]);
                    } else {
                        $seq++;
                    }
                }

                // Buat record lampiran baru
                foreach ($newAttachmentData as $nAtt) {
                    $nAtt['sequence'] = $seq++;
                    $document->attachments()->create($nAtt);
                }

                if ($isFullRevisionReset) {
                    // 1. Creator stage -> 'approved'
                    DocumentApproval::where('document_id', $document->id)
                        ->where('stage', 'creator')
                        ->update([
                            'status'       => 'approved',
                            'processed_at' => now(),
                        ]);

                    // 2. Final stage -> 'pending' (menunggu seluruh reviewer selesai)
                    DocumentApproval::where('document_id', $document->id)
                        ->where('stage', 'final')
                        ->update([
                            'status'       => 'pending',
                            'processed_at' => null,
                        ]);

                    // Selesaikan request revisi aktif jika ada
                    \App\Models\RevisionRequest::where('document_id', $document->id)
                        ->where('status', 'approved')
                        ->update(['status' => 'completed']);
                }

                // Update target approval status ke 'current' dan reset processed_at
                foreach ($approvalsToActivate as $appToAct) {
                    $appToAct->update([
                        'status'       => 'current',
                        'processed_at' => null
                    ]);
                    $usersToNotify[] = $appToAct->user;
                }

                if ($isAutoGenerated) {
                    foreach ($coordinates as $pos) {
                        $approval = DocumentApproval::where('document_id', $document->id)
                            ->where('user_id', $pos['user_id'])
                            ->orderBy('sequence')
                            ->skip((int)($pos['occurrence'] ?? 0))
                            ->first();
                        if ($approval) {
                            $approval->update([
                                'signature_page' => $pos['page'],
                                'signature_x'    => $pos['x'],
                                'signature_y'    => $pos['y'],
                            ]);
                        }
                    }
                }

                $firstTarget = $approvalsToActivate->first();
                $allAtts = $document->attachments()->orderBy('sequence', 'asc')->get();
                $firstLamp = $allAtts->count() > 0 ? $allAtts->first()->file_path : null;

                $document->update([
                    'title'         => $request->title,
                    'doc_revision'  => $nextRevision,
                    'doc_date'      => $revisionDate,
                    'reviewer_id'   => $firstTarget->user_id,
                    'file_cover'    => $pathCover,
                    'file_lp'       => $pathLp,
                    'file_isi'      => $pathIsi,
                    'file_lampiran' => $firstLamp,
                    'file_preview'  => $previewPath,
                    'file_final'    => null,
                    'status'        => 'waiting', 
                ]);

                $document->logs()->create([
                    'user_id' => auth()->id() ?? 1,
                    'action'  => 'revisi',
                    'notes'   => 'Admin mengunggah file revisi baru. Alur otomatis dilanjutkan langsung ke: ' . $firstTarget->user->username,
                ]);
            });

            // Clean up old physical files after DB transaction commit
            foreach ($oldPhysicalFilesToDelete as $oldFile) {
                if (!empty($oldFile) && file_exists(storage_path('app/public/' . $oldFile))) {
                    @unlink(storage_path('app/public/' . $oldFile));
                }
            }

            // Send notification email to all activated reviewers (status baru: 'current')
            foreach ($usersToNotify as $notifyUser) {
                if ($notifyUser && $notifyUser->email) {
                    try {
                        $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                            'login.magic',
                            now()->addHours(24),
                            [
                                'user_id' => $notifyUser->id,
                                'document_id' => $document->id
                            ]
                        );

                        Mail::to($notifyUser->email)->queue(
                            new \App\Mail\DocumentRevisionResubmittedMail($document, $notifyUser, auth()->user(), $magicLoginUrl)
                        );
                    } catch (\Exception $e) {
                        \Log::error("e-QMS Support Email Revisi Error: " . $e->getMessage());
                    }
                }
            }

            // ================================================================
            // Kirim notifikasi informatif ke SEMUA peninjau lainnya (stage = 'reviewer')
            // agar mereka tahu berkas revisi baru telah diunggah.
            // Penandatangan Final (stage = 'final') sengaja dikecualikan sampai seluruh reviewer menyetujui.
            // ================================================================
            $activatedUserIds = collect($usersToNotify)->pluck('id')->filter()->toArray();
            $otherReviewerApprovals = DocumentApproval::where('document_id', $document->id)
                ->where('stage', 'reviewer')
                ->whereNotIn('user_id', $activatedUserIds)
                ->with('user')
                ->get();

            $notifiedOtherUserIds = [];
            foreach ($otherReviewerApprovals as $appItem) {
                $notifyUser = $appItem->user;
                if ($notifyUser && !empty(trim($notifyUser->email ?? '')) && !in_array($notifyUser->id, $notifiedOtherUserIds)) {
                    $notifiedOtherUserIds[] = $notifyUser->id;
                    try {
                        $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                            'login.magic',
                            now()->addHours(24),
                            [
                                'user_id' => $notifyUser->id,
                                'document_id' => $document->id
                            ]
                        );

                        Mail::to($notifyUser->email)->queue(
                            new \App\Mail\DocumentRevisionResubmittedMail($document, $notifyUser, auth()->user(), $magicLoginUrl)
                        );
                        \Log::info("e-QMS Support: Notifikasi revisi dikirim ke peninjau: User ID {$notifyUser->id} ({$notifyUser->username}) untuk Dokumen ID {$document->id}");
                    } catch (\Exception $e) {
                        \Log::error("e-QMS Support Email Revisi Error for User ID {$notifyUser->id}: " . $e->getMessage());
                    }
                }
            }

            return redirect()->route('admin.support.document.detail', $document->id)
                ->with('success', 'File revisi berhasil digabungkan dan dialirkan langsung ke ' . ($firstTarget->user->username ?? 'Peninjau'));

        } catch (\Throwable $e) {
            \Log::error("e-QMS Support Revision Error: " . $e->getMessage());
            Storage::disk('public')->delete(array_filter($uploadedNewFiles));
            return back()->withInput()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    /**
     * Form revisi dokumen khusus untuk Creator (bukan admin).
     * Authorization: user harus menjadi Creator (stage='creator') dari dokumen ini,
     * DAN status dokumen harus 'need_revision'.
     */
    public function creatorEditRevision(int $id): View
    {
        $document = Document::with(['approvals.user', 'attachments'])->findOrFail($id);

        // Authorization: cek apakah user saat ini adalah creator / approved requester / admin
        $isCreator = \App\Models\DocumentApproval::where('document_id', $document->id)
            ->where('user_id', auth()->id())
            ->where('stage', 'creator')
            ->exists();

        $isApprovedRequester = \App\Models\RevisionRequest::where('document_id', $document->id)
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->exists();

        $isOwner = $document->created_by === auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        if ((!$isCreator && !$isApprovedRequester && !$isOwner && !$isAdmin) || $document->status !== 'need_revision') {
            abort(403, 'Anda tidak memiliki akses untuk merevisi dokumen ini.');
        }

        return view('admin.support.edit_revision', compact('document'));
    }

    /**
     * Proses upload revisi dokumen dari Creator / Pemohon Revisi.
     */
    public function creatorUpdateRevision(Request $request, int $id)
    {
        $document = Document::findOrFail($id);

        $isCreator = \App\Models\DocumentApproval::where('document_id', $document->id)
            ->where('user_id', auth()->id())
            ->where('stage', 'creator')
            ->exists();

        $isApprovedRequester = \App\Models\RevisionRequest::where('document_id', $document->id)
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->exists();

        $isOwner = $document->created_by === auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        if ((!$isCreator && !$isApprovedRequester && !$isOwner && !$isAdmin) || $document->status !== 'need_revision') {
            abort(403, 'Anda tidak memiliki akses untuk merevisi dokumen ini.');
        }

        // Jalankan updateRevision — semua file processing, email, dan log tetap berjalan normal.
        // Karena updateRevision() akan redirect ke admin route (403 untuk non-admin),
        // kita tangkap exception redirect dan ganti dengan redirect ke reviewer dashboard.
        try {
            $this->updateRevision($request, $id);
        } catch (\Throwable $e) {
            return back()->withErrors(['msg' => $e->getMessage()]);
        }

        // Redirect ke reviewer dashboard yang accessible untuk non-admin creator
        return redirect()->route('reviewer.dashboard')
            ->with('success', 'File revisi berhasil dikirimkan. Proses persetujuan akan dilanjutkan oleh reviewer.');
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
            return $absolutePath;
        } catch (\Throwable $e) {
            $qpdfBin = config('app.qpdf_path', 'qpdf');
            if ((str_starts_with($qpdfBin, '/') || preg_match('/^[a-zA-Z]:\\\\/', $qpdfBin)) && !file_exists($qpdfBin)) {
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

    private function drawDigitalStampInController($pdf, $x, $y, $name, $rowHeight = null) 
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

    private function drawDateStampInController($pdf, $pageWidthMm, $signatureY, $dateLine1, $dateLine2)
    {
        $dateColumnCenterX = $pageWidthMm - 30.0;
        $pdf->SetTextColor(30, 41, 59);
        
        $pdf->SetFont('Arial', 'B', 7.8);
        $pdf->SetXY($dateColumnCenterX - 15.0, $signatureY + 1.2);
        $pdf->Cell(30.0, 2.0, $dateLine1, 0, 0, 'C');
        
        $pdf->SetFont('Arial', '', 6.2);
        $pdf->SetXY($dateColumnCenterX - 15.0, $signatureY + 3.6);
        $pdf->Cell(30.0, 2.0, $dateLine2, 0, 0, 'C');
    }
}
