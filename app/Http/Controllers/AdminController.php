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
                'total_pegawai'  => User::where('role', '!=', 'admin')->count(),
                'sop_support'    => Document::whereNotIn('department', $buUnits)->count(),
                'sop_divisi'     => Document::whereIn('department', $buUnits)->count(),
                'pending_review' => Document::where('status', 'waiting')->count(),
            ];

            $recentActivities = Document::latest()->take(5)->get();

            return view('admin.dashboard', compact('stats', 'recentActivities'));
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
                Mail::to($targetUser->email)->send(new NewDocumentReviewMail($document, $targetUser, $magicLoginUrl));
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
                Mail::to($targetUser->email)->send(new NewDocumentReviewMail($document, $targetUser, $magicLoginUrl));
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