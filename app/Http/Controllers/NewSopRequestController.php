<?php

namespace App\Http\Controllers;

use App\Models\NewSopRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NewSopRequestController extends Controller
{
    /**
     * Daftar divisi & unit bisnis resmi e-QMS dikelompokkan per kategori
     */
    private function getDepartmentGroups(): array
    {
        return [
            'Departemen Support' => [
                'HC'               => 'HC (Human Capital)',
                'IT'               => 'IT (Information Technology)',
                'QMS'              => 'QMS (Quality Management System)',
                'HSE'              => 'HSE (Health, Safety & Environment)',
                'LEGAL'            => 'LEGAL (Legal & Compliance)',
                'INTERNAL AUDIT'   => 'INTERNAL AUDIT (Audit & Risk)',
                'FINANCE'          => 'FINANCE (Finance & Accounting)',
                'LOGISTIC'         => 'LOGISTIC & OPERASIONAL',
                'PROCUREMENT'      => 'PROCUREMENT (Pengadaan)',
                'WAREHOUSE'        => 'WAREHOUSE (Gudang & Logistik)',
                'ASET'             => 'ASET (Manajemen Aset)',
                'GA'               => 'GA (General Affairs)',
                'SEKRETARIS'       => 'CORPORATE SECRETARY',
            ],
            'Business Unit (BU) - Retail' => [
                'SPBU'             => 'BU - SPBU (Stasiun Pengisian Bahan Bakar)',
                'LPG PSO'          => 'BU - LPG PSO (Subsidi)',
                'LPG NPSO'         => 'BU - LPG NPSO (Non-Subsidi)',
                'PKSP'             => 'BU - PKSP (SPBE & Pengangkutan Khusus)',
                'TRP'              => 'BU - TRP (Transportasi Retail)',
            ],
            'Business Unit (BU) - Komersil' => [
                'CPT & MHM'        => 'BU - CPT & MHM',
                'SBS'              => 'BU - PT SBS',
                'GVI'              => 'BU - PT GVI',
            ],
            'Business Unit (BU) - Marine' => [
                'INMAR (CNGM)'     => 'BU - INMARR (Marine CNG & Bunkering)',
            ],
        ];
    }

    /**
     * Halaman Pengajuan SOP Baru untuk User (Formulir & Riwayat Pengajuan)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $departmentGroups = $this->getDepartmentGroups();

        $query = NewSopRequest::with(['user', 'admin', 'document.approvals.user']);
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $status = $request->query('status');
        if ($status && in_array($status, ['pending', 'in_progress', 'revision', 'approved', 'completed', 'rejected'], true)) {
            if ($status === 'approved') {
                $query->whereIn('status', ['approved', 'completed']);
            } else {
                $query->where('status', $status);
            }
        }

        $requests = $query->latest('id')->paginate(10)->withQueryString();

        // Statistics
        $baseQuery = NewSopRequest::query();
        if ($user->role !== 'admin') {
            $baseQuery->where('user_id', $user->id);
        }
        $totalCount = $baseQuery->count();
        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();
        $inProgressCount = (clone $baseQuery)->where('status', 'in_progress')->count();
        $revisionCount = (clone $baseQuery)->where('status', 'revision')->count();
        $approvedCount = (clone $baseQuery)->whereIn('status', ['approved', 'completed'])->count();
        $rejectedCount = (clone $baseQuery)->where('status', 'rejected')->count();

        return view('user.sop_requests.index', compact(
            'requests',
            'departmentGroups',
            'totalCount',
            'pendingCount',
            'inProgressCount',
            'revisionCount',
            'approvedCount',
            'rejectedCount',
            'search',
            'status'
        ));
    }

    /**
     * Simpan Pengajuan SOP Baru dari User
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'department'      => 'required|string|max:100',
            'description'     => 'required|string|max:3000',
            'attachment_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,png|max:20480',
        ]);

        $user = Auth::user();
        $attachmentPath = null;

        if ($request->hasFile('attachment_file')) {
            $attachmentPath = $request->file('attachment_file')->store('sop_requests/attachments', 'public');
        }

        $sopRequest = NewSopRequest::create([
            'user_id'         => $user->id,
            'title'           => $request->title,
            'department'      => $request->department,
            'description'     => $request->description,
            'attachment_file' => $attachmentPath,
            'status'          => 'pending',
        ]);

        // Kirim Notifikasi Email ke Admin QMS dengan Magic Link
        try {
            $adminUsers = \App\Models\User::where('role', 'admin')->whereNotNull('email')->get();
            foreach ($adminUsers as $admin) {
                if (!empty(trim($admin->email))) {
                    $actionUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                        'login.magic',
                        now()->addDays(7),
                        [
                            'user_id'     => $admin->id,
                            'redirect_to' => route('admin.user_reviews.index', ['tab' => 'new_sop', 'status' => 'pending']),
                        ]
                    );

                    \Illuminate\Support\Facades\Mail::to($admin->email)->queue(
                        new \App\Mail\NewSopRequestSubmittedMail($sopRequest, $user, $actionUrl)
                    );
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mengirim email usulan SOP baru ke Admin: " . $e->getMessage());
        }

        return redirect()->route('user.sop_requests.index')
            ->with('success', "Pengajuan pembuatan SOP baru '{$request->title}' berhasil dikirim ke Admin QMS untuk ditindaklanjuti.");
    }

    /**
     * User: Perbaiki Pengajuan yang Diminta Revisi
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $sopReq = NewSopRequest::where('id', $id)->firstOrFail();

        if ($user->role !== 'admin' && $sopReq->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $request->validate([
            'title'           => 'required|string|max:255',
            'department'      => 'required|string|max:100',
            'description'     => 'required|string|max:3000',
            'attachment_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,png|max:20480',
        ]);

        $dataToUpdate = [
            'title'       => $request->title,
            'department'  => $request->department,
            'description' => $request->description,
            'status'      => 'pending', // Dikembalikan ke pending agar ditinjau ulang oleh Admin
        ];

        if ($request->hasFile('attachment_file')) {
            if ($sopReq->attachment_file) {
                Storage::disk('public')->delete($sopReq->attachment_file);
            }
            $dataToUpdate['attachment_file'] = $request->file('attachment_file')->store('sop_requests/attachments', 'public');
        }

        $sopReq->update($dataToUpdate);

        return redirect()->route('user.sop_requests.index')
            ->with('success', "Pengajuan SOP '{$sopReq->title}' berhasil diperbaiki dan dikirim kembali ke Tim QMS untuk ditinjau.");
    }

    /**
     * Panel Admin: Kelola & Review Pengajuan SOP Baru
     */
    public function adminIndex(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = NewSopRequest::with(['user', 'admin', 'document']);

        if ($status && in_array($status, ['pending', 'in_progress', 'revision', 'approved', 'completed', 'rejected'], true)) {
            if ($status === 'approved') {
                $query->whereIn('status', ['approved', 'completed']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('username', 'like', "%{$search}%")
                         ->orWhere('full_name', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->latest('id')->paginate(15)->withQueryString();

        $totalAll = NewSopRequest::count();
        $totalPending = NewSopRequest::where('status', 'pending')->count();
        $totalInProgress = NewSopRequest::where('status', 'in_progress')->count();
        $totalRevision = NewSopRequest::where('status', 'revision')->count();
        $totalApproved = NewSopRequest::whereIn('status', ['approved', 'completed'])->count();
        $totalRejected = NewSopRequest::where('status', 'rejected')->count();

        return view('admin.sop_requests.index', compact(
            'requests',
            'totalAll',
            'totalPending',
            'totalInProgress',
            'totalRevision',
            'totalApproved',
            'totalRejected',
            'status',
            'search'
        ));
    }

    /**
     * Admin: Setujui Pengajuan SOP Baru
     */
    public function adminApprove(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $sopReq = NewSopRequest::findOrFail($id);
        $sopReq->update([
            'status'      => 'approved',
            'admin_id'    => Auth::id(),
            'admin_notes' => $request->admin_notes ?? 'Pengajuan SOP disetujui untuk diproses ke draf naskah resmi.',
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', "Pengajuan SOP '{$sopReq->title}' berhasil disetujui.");
    }

    /**
     * Admin: Minta Revisi / Kelengkapan dari Pemohon
     */
    public function adminRequestRevision(Request $request, $id)
    {
        $request->validate([
            'revision_notes' => 'required|string|max:1000',
        ]);

        $sopReq = NewSopRequest::findOrFail($id);
        $sopReq->update([
            'status'         => 'revision',
            'admin_id'       => Auth::id(),
            'revision_notes' => $request->revision_notes,
            'admin_notes'    => 'Pengajuan dikembalikan ke pemohon untuk revisi/kelengkapan data.',
            'reviewed_at'    => now(),
        ]);

        return redirect()->back()->with('info', "Permintaan revisi usulan SOP '{$sopReq->title}' telah dikirim ke pemohon.");
    }

    /**
     * Admin: Tandai Sedang Diproses QMS
     */
    public function adminMarkInProgress(Request $request, $id)
    {
        $sopReq = NewSopRequest::findOrFail($id);
        $sopReq->update([
            'status'      => 'in_progress',
            'admin_id'    => Auth::id(),
            'admin_notes' => $request->admin_notes ?? 'Pengajuan sedang dalam proses perancangan naskah SOP resmi.',
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', "Status pengajuan SOP '{$sopReq->title}' kini sedang diproses.");
    }

    /**
     * Admin: Tolak Pengajuan SOP Baru
     */
    public function adminReject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $sopReq = NewSopRequest::findOrFail($id);
        $sopReq->update([
            'status'      => 'rejected',
            'admin_id'    => Auth::id(),
            'admin_notes' => $request->admin_notes,
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('info', "Pengajuan SOP '{$sopReq->title}' telah ditolak.");
    }
}
