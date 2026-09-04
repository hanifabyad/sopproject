<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewDocumentReviewMail;
use Illuminate\Support\Facades\Log;
use App\Models\Library;

class AdminController extends Controller
{

    public function index()
    {
        $buUnits = ['SPBU', 'BBM RETAIL', 'GAS RETAIL', 'BBM INMAR', 'GAS INDUSTRI', 'MARINE TRANSPORT', 'SHIPYARD'];

        $stats = [
            'total_pegawai'        => User::where('role', '!=', 'admin')->count(),
            'total_sop'            => Document::count(),
            'sop_support'          => Document::whereNotIn('department', $buUnits)->count(),
            'sop_divisi'           => Document::whereIn('department', $buUnits)->count(),
            'pending_review'       => Document::where('status', 'waiting')->count(),
            'pending_new_sop'      => \App\Models\NewSopRequest::where('status', 'pending')->count(),
            'pending_revision_req' => \App\Models\RevisionRequest::where('status', 'pending')->count(),
        ];

        $recentNewSops = \App\Models\NewSopRequest::with('user')->latest()->take(5)->get();
        $revisiDocs = Document::where('status', 'need_revision')->latest()->take(5)->get();
        $inProgressDocs = Document::where('status', 'waiting')->latest()->take(5)->get();
        $activeDocs = Document::where('status', 'active')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentNewSops', 'revisiDocs', 'inProgressDocs', 'activeDocs'));
    }

    public function tracking(Request $request)
    {
        $selectedYear = $request->query('year');
        $selectedUnit = $request->query('unit');
        $selectedStatus = $request->query('status');
        $search = $request->query('search');

        // Query dokumen
        $query = Document::with(['approvals.user', 'reviewer']);

        if ($selectedYear && $selectedYear !== 'all') {
            $query->whereYear('created_at', $selectedYear);
        }

        if ($selectedUnit && $selectedUnit !== 'all') {
            $query->where('department', $selectedUnit);
        }

        if ($selectedStatus && $selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('doc_number', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $documents = $query->latest()->paginate(15)->withQueryString();

        // Rekapitulasi Statistik Global / Sesuai Filter Kurun Waktu
        $baseQuery = Document::query();
        if ($selectedYear && $selectedYear !== 'all') {
            $baseQuery->whereYear('created_at', $selectedYear);
        }
        if ($selectedUnit && $selectedUnit !== 'all') {
            $baseQuery->where('department', $selectedUnit);
        }

        $stats = [
            'total'          => (clone $baseQuery)->count(),
            'active'         => (clone $baseQuery)->where('status', 'active')->count(),
            'need_revision'  => (clone $baseQuery)->where('status', 'need_revision')->count(),
            'waiting'        => (clone $baseQuery)->where('status', 'waiting')->count(),
            'obsolete'       => (clone $baseQuery)->where('status', 'obsolete')->count(),
            // SOP Evaluation metrics
            'eval_due'       => (clone $baseQuery)->where('status', 'active')->where('evaluation_status', 'due')->count(),
            'eval_overdue'   => (clone $baseQuery)->where('status', 'active')->where('evaluation_status', 'overdue')->count(),
            'eval_in_review' => (clone $baseQuery)->where('status', 'active')->whereIn('evaluation_status', ['in_review', 'submitted'])->count(),
            'eval_completed' => (clone $baseQuery)->where('evaluation_status', 'completed')->count(),
        ];

        // 1. Rekapitulasi Usia SOP Aktif (Berapa lama SOP dibuat / berjalan)
        $activeDocsForAge = (clone $baseQuery)->where('status', 'active')->get();
        $ageStats = [
            'under_1m' => 0, // < 1 Bulan (< 30 hari)
            '1m_to_6m' => 0, // 1 - 6 Bulan (30 - 180 hari)
            '6m_to_1y' => 0, // 6 - 12 Bulan (181 - 365 hari)
            '1y_to_2y' => 0, // 1 - 2 Tahun (366 - 730 hari)
            'over_2y'  => 0, // > 2 Tahun (> 730 hari)
        ];

        foreach ($activeDocsForAge as $doc) {
            $days = $doc->active_lifespan_days;
            if ($days < 30) {
                $ageStats['under_1m']++;
            } elseif ($days <= 180) {
                $ageStats['1m_to_6m']++;
            } elseif ($days <= 365) {
                $ageStats['6m_to_1y']++;
            } elseif ($days <= 730) {
                $ageStats['1y_to_2y']++;
            } else {
                $ageStats['over_2y']++;
            }
        }

        // 2. Rekapitulasi SLA Approval Dokumen (Target: 13 Hari, Overdue: > 14 Hari)
        $allDocsForSla = (clone $baseQuery)->get();
        $slaStats = [
            'target_days'    => 13,
            'on_track'       => 0, // <= 10 hari
            'warning'        => 0, // 11 - 13 hari
            'overdue'        => 0, // > 14 hari
            'pending_action' => 0, // > 14 hari & belum ada sla_notes
        ];

        foreach ($allDocsForSla as $doc) {
            $pDays = $doc->process_duration_days;
            if ($pDays <= 10) {
                $slaStats['on_track']++;
            } elseif ($pDays <= 13) {
                $slaStats['warning']++;
            } else {
                $slaStats['overdue']++;
                if (empty($doc->sla_notes)) {
                    $slaStats['pending_action']++;
                }
            }
        }

        // Daftar tahun yang ada dalam database untuk filter kurun tahun
        $availableYears = Document::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values();

        if ($availableYears->isEmpty()) {
            $availableYears = collect([date('Y')]);
        }

        // Rekapitulasi per Tahun (Lifecycle Trend)
        $yearlyStats = Document::selectRaw('YEAR(created_at) as year, count(*) as total, SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_count, SUM(CASE WHEN status = "need_revision" THEN 1 ELSE 0 END) as revision_count')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        // Daftar semua unit/departemen untuk dropdown filter
        $units = Document::select('department')->distinct()->orderBy('department')->pluck('department')->filter()->values();

        return view('admin.tracking', compact(
            'documents',
            'stats',
            'ageStats',
            'slaStats',
            'availableYears',
            'yearlyStats',
            'units',
            'selectedYear',
            'selectedUnit',
            'selectedStatus',
            'search'
        ));
    }

    /**
     * Admin/User mencatat alasan / tindak lanjut keterlambatan SLA approval (> 14 hari)
     */
    public function updateSlaAction(Request $request, $id)
    {
        $request->validate([
            'sla_notes' => 'required|string|max:1000',
        ]);

        $document = Document::findOrFail($id);
        $document->update([
            'sla_notes'     => $request->sla_notes,
            'sla_action_by' => auth()->id(),
            'sla_action_at' => now(),
        ]);

        $document->logs()->create([
            'user_id' => auth()->id(),
            'action'  => 'sla_action_logged',
            'notes'   => 'Keterangan Keterlambatan SLA (>14 hari) dicatat: ' . $request->sla_notes,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alasan & tindak lanjut keterlambatan SLA berhasil disimpan.'
            ]);
        }

        return redirect()->back()->with('success', 'Alasan & tindak lanjut keterlambatan SLA berhasil disimpan.');
    }

    public function logoIndex()
    {
        $companyMap = \App\Services\LpGeneratorService::getCompanyMap();
        return view('admin.logo', compact('companyMap'));
    }

    public function updateCompanyLogo(Request $request)
    {
        $request->validate([
            'company_code' => 'required|string',
            'name'         => 'required|string|max:255',
            'address'      => 'required|string|max:255',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $code = strtolower(trim($request->company_code));
        $companyMap = \App\Services\LpGeneratorService::getCompanyMap();

        if (!isset($companyMap[$code])) {
            return back()->with('error', 'Kode perusahaan tidak valid.');
        }

        $companyMap[$code]['name'] = $request->name;
        $companyMap[$code]['address'] = $request->address;

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $code . '.' . $file->getClientOriginalExtension();
            
            // Pastikan direktori public/img ada
            $destPath = public_path('img');
            if (!file_exists($destPath)) {
                mkdir($destPath, 0755, true);
            }
            
            $file->move($destPath, $filename);
            $companyMap[$code]['logo'] = $filename;
        }

        // Simpan map kembali ke storage
        $jsonPath = storage_path('app/company_configs.json');
        file_put_contents($jsonPath, json_encode($companyMap, JSON_PRETTY_PRINT));

        return back()->with('success', 'Informasi & logo PT berhasil diperbarui!');
    }
    // Fungsi untuk menyimpan data dari form 3 file
    public function store(Request $request, $unit)
    {
        // 1. VALIDASI INPUT
        $request->validate([
            'title'         => 'required',
            'reviewer_id'   => 'required',
            'file_cover'    => 'required|mimes:pdf|max:5000',
            'file_lp'       => 'required|mimes:pdf|max:5000',
            'file_isi'      => 'required|mimes:pdf|max:10000',
            'file_lampiran' => 'nullable|mimes:pdf|max:5000',
        ]);

        try {
            // 2. INISIALISASI DATA DOKUMEN
            $document = new Document();
            $document->title = $request->title;
            $document->department = $unit; // Disimpan sebagai Unit Bisnis/Departemen
            $document->reviewer_id = $request->reviewer_id;
            $document->status = 'waiting';

            // 3. PROSES SIMPAN FILE KE STORAGE
            if ($request->hasFile('file_cover')) {
                $document->file_cover = $request->file('file_cover')->store('documents/covers', 'public');
            }
            if ($request->hasFile('file_lp')) {
                $document->file_lp = $request->file('file_lp')->store('documents/lps', 'public');
            }
            if ($request->hasFile('file_isi')) {
                $document->file_isi = $request->file('file_isi')->store('documents/contents', 'public');
            }
            if ($request->hasFile('file_lampiran')) {
                $document->file_lampiran = $request->file('file_lampiran')->store('documents/attachments', 'public');
            }

            // 4. SIMPAN KE DATABASE
            $document->save();

            // 5. LOGIKA PENGIRIMAN EMAIL NOTIFIKASI
            $targetUser = User::find($request->reviewer_id);
            if ($targetUser && $targetUser->email) {
                
                // GENERATE URL AUTO-LOGIN AMAN (Hanya Aktif 15 Menit sejak email terkirim)
                $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'login.magic',
                    now()->addMinutes(15),
                    [
                        'user_id' => $targetUser->id,
                        'document_id' => $document->id
                    ]
                );

                // Mengirim email undangan review ke pimpinan (Mengirim 3 Parameter Lengkap)
                Mail::to($targetUser->email)->queue(new NewDocumentReviewMail($document, $targetUser, $magicLoginUrl));
            }

            // 6. REDIRECT KE HALAMAN DAFTAR DOKUMEN (SHOW)
            // Mengarahkan ke admin.BU.show dengan membawa parameter unit ($unit)
            return redirect()->route('admin.BU.show', $unit)
                ->with('success', '✨ SOP Berhasil dikirim dan Notifikasi Email telah terkirim ke ' . $targetUser->username);

        } catch (\Exception $e) {
            // Mencatat error jika terjadi kegagalan sistem
            Log::error("Gagal simpan/kirim email: " . $e->getMessage());
            
            return redirect()->back()
                ->with('error', '❌ Terjadi Kesalahan: ' . $e->getMessage());
        }
    }


    public function assignReviewer(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        
        // Admin memilih target user dari dropdown (Kelola Akun)
        $document->update([
            'reviewer_id' => $request->reviewer_id, // ID user yang dipilih admin
            'status'      => 'waiting'
        ]);

        // AMBIL DATA USER DARI DATABASE
        $targetUser = User::find($request->reviewer_id);

        // KIRIM EMAIL NOTIFIKASI TUGAS BARU
        if ($targetUser && $targetUser->email) {
            try {
                // GENERATE URL AUTO-LOGIN AMAN (Hanya Aktif 15 Menit)
                $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'login.magic',
                    now()->addHours(24),
                    [
                        'user_id' => $targetUser->id,
                        'document_id' => $document->id
                    ]
                );

                // Kirim email (Mengirim 3 Parameter Lengkap)
                Mail::to($targetUser->email)->queue(new NewDocumentReviewMail($document, $targetUser, $magicLoginUrl));
            } catch (\Exception $e) {
                \Log::error("Email Gagal dikirim ke Reviewer: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Dokumen berhasil dikirim ke ' . $targetUser->username);
    }

    public function moveToLibrary(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        // Validasi input dari Admin
        $request->validate([
            'category' => 'required',
            'division' => 'required_if:category,divisi',
            'sub_division' => 'required_if:category,divisi',
            'company_name' => 'required_if:category,divisi',
        ]);

        // Simpan ke Tabel Library
       Library::create([
        'title'         => $document->title,
        'category'      => $request->category,
        'division_name' => $request->division, // Simpan ke division_name
        'business_unit' => $request->sub_division, // Simpan ke business_unit
        'company_name'  => $request->company_name,
        'file_path'     => $document->file_final,
        'uploaded_by'   => auth()->id(),
    ]);

        // Opsional: Tandai dokumen asli sebagai 'Archived' agar tidak muncul di list active lagi
        $document->update(['status' => 'archived']);

        return back()->with('success', 'SOP berhasil dipindahkan ke Library!');
    }

}
