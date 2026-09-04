<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login.
     * Jika user sudah login, arahkan ke dashboard yang sesuai dengan role-nya.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            // Jika bukan admin (Reviewer/Ka.Dept), arahkan ke dashboard reviewer
            return redirect()->route('reviewer.dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Menangani proses autentikasi dengan fitur Remember Me otomatis (Bisa Username atau Email).
     */
    public function login(Request $request)
    {
        // Validasi input
        $input = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $input['username'];
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $fieldType => $loginInput,
            'password' => $input['password'],
        ];

        // KUNCI PERBAIKAN: Tambahkan parameter 'true' sebagai argumen kedua
        // Ini akan mengaktifkan cookie 'remember_me' secara otomatis
        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

            // Pengecekan Role setelah login berhasil
            if (Auth::user()->role === 'admin') {
                return redirect()->intended('/admin/dashboard');
            } 
            
            return redirect()->intended('/reviewer/dashboard');
        }

        // Jika login gagal
        return back()->withErrors([
            'username' => 'Akun tidak ditemukan atau password salah.',
        ])->onlyInput('username');
    }

    /**
     * Menangani proses logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Tambahkan parameter 'true' di sini sebagai argumen kedua
        // Ini akan membuat Laravel menyimpan cookie "remember me" secara otomatis
        if (Auth::attempt($credentials, true)) { 
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Waduh, email atau password-nya nggak cocok nih.',
        ])->onlyInput('email');
    }

    /**
     * 🔥 FITUR BARU: Menangani Auto-Login via Magic Link dari Email (Aman & Berdurasi 15 Menit)
     */
    public function magicLogin(Request $request)
    {
        // 1. Cek validitas Tanda Tangan Digital & Waktu Expired
        if (! $request->hasValidSignature()) {
            return redirect()->route('login')->with('error', 'Link auto-login tidak sah atau sudah kedaluwarsa. Silakan login manual.');
        }

        // 2. FIX: Tangkap parameter secara langsung dari Request objek (Biar sinkron dengan Signed URL)
        $userId = $request->user_id;
        $documentId = $request->document_id;

        // 3. Cari datanya di sistem database e-QMS
        $user = \App\Models\User::find($userId);

        if (!$user) {
            return redirect()->route('login')->with('error', 'Akun peninjau tidak ditemukan di sistem.');
        }

        // 4. Lakukan login otomatis di latar belakang tanpa menanyakan password
        \Auth::login($user, true); // Ditambah true agar mengaktifkan 'Remember Me'
        $request->session()->regenerate();

        // 5. FIX LOGIKA REDIRECT
        if ($request->filled('redirect_to')) {
            $msg = 'Berhasil masuk otomatis ke sistem e-QMS.';
            if (str_contains($request->redirect_to, 'socializ')) {
                $msg = 'Berhasil masuk otomatis! Silakan unggah berkas bukti sosialisasi SOP.';
            } elseif (str_contains($request->redirect_to, 'revision')) {
                $msg = 'Berhasil masuk otomatis ke halaman Permohonan Revisi SOP.';
            } elseif (str_contains($request->redirect_to, 'quiz')) {
                $msg = 'Berhasil masuk otomatis! Silakan kerjakan kuis pemahaman SOP.';
            }
            return redirect($request->redirect_to)->with('success', $msg);
        }

        if ($user->role !== 'admin') {
            $evaluationId = $request->evaluation_id;
            $type = $request->type;

            // A. Jika link berasal dari notifikasi evaluasi SOP (atau membawa evaluation_id / type=evaluation)
            if ($type === 'evaluation' || $evaluationId) {
                if ($evaluationId) {
                    return redirect()->route('evaluations.show', $evaluationId)
                        ->with('success', 'Berhasil masuk! Silakan isi formulir evaluasi berkala SOP.');
                }
                if ($documentId) {
                    $eval = \App\Models\Evaluation::where('document_id', $documentId)->latest()->first();
                    if ($eval) {
                        return redirect()->route('evaluations.show', $eval->id)
                            ->with('success', 'Berhasil masuk! Silakan isi formulir evaluasi berkala SOP.');
                    }
                }
            }

            if ($documentId) {
                $doc = \App\Models\Document::find($documentId);

                // B. Jika dokumen aktif dan memiliki evaluasi yang 'due' / 'in_review' / 'overdue'
                if ($doc && $doc->status === 'active' && in_array($doc->evaluation_status, ['due', 'in_review', 'overdue', 'submitted'])) {
                    $eval = \App\Models\Evaluation::where('document_id', $documentId)->latest()->first();
                    if ($eval) {
                        return redirect()->route('evaluations.show', $eval->id)
                            ->with('success', 'Berhasil masuk! Silakan isi formulir evaluasi berkala SOP.');
                    }
                }

                // C. Cek apakah user adalah creator / pemohon revisi yang disetujui DAN dokumen memerlukan revisi
                if ($doc && $doc->status === 'need_revision') {
                    $isCreator = \App\Models\DocumentApproval::where('document_id', $documentId)
                        ->where('user_id', $user->id)
                        ->where('stage', 'creator')
                        ->exists();

                    $isApprovedRequester = \App\Models\RevisionRequest::where('document_id', $documentId)
                        ->where('user_id', $user->id)
                        ->where('status', 'approved')
                        ->exists();

                    $isOwner = $doc->created_by === $user->id;

                    if ($isCreator || $isApprovedRequester || $isOwner || $user->role === 'admin') {
                        $buDepartments = ['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'INMAR (CNGM)', 'CPT & MHM', 'SBS', 'GVI', 'PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA', 'KEUANGAN & ACCOUNTING'];
                        $creatorRoute = in_array(strtoupper($doc->department), $buDepartments, true)
                            ? 'admin.BU.creator_revise'
                            : 'admin.support.creator_revise';
                        return redirect()->route($creatorRoute, $documentId)
                            ->with('success', 'Berhasil masuk! Silakan unggah perbaikan dokumen.');
                    }
                }
            }

            // D. Default: Halaman review persetujuan reviewer
            return redirect()->route('reviewer.show', $documentId)
                ->with('success', 'Berhasil masuk otomatis! Silakan tinjau dokumen ini.');
        }

        // Jika yang klik ternyata admin, baru masuk ke dashboard admin
        return redirect()->route('admin.dashboard');
    }
}
