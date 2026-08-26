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
     * Menangani proses autentikasi dengan fitur Remember Me otomatis.
     */
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

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

        // 5. FIX LOGIKA REDIRECT: Selama dia BUKAN admin, lempar langsung ke halaman review dokumen!
        if ($user->role !== 'admin') {
            // Cek apakah user adalah creator dari dokumen ini DAN dokumen memerlukan revisi
            if ($documentId) {
                $doc = \App\Models\Document::find($documentId);
                if ($doc && $doc->status === 'need_revision') {
                    $isCreator = \App\Models\DocumentApproval::where('document_id', $documentId)
                        ->where('user_id', $user->id)
                        ->where('stage', 'creator')
                        ->exists();
                    if ($isCreator) {
                        $buDepartments = ['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'INMAR (CNGM)', 'CPT & MHM', 'SBS', 'GVI', 'PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA', 'KEUANGAN & ACCOUNTING'];
                        $creatorRoute = in_array($doc->department, $buDepartments, true)
                            ? 'admin.BU.creator_revise'
                            : 'admin.support.creator_revise';
                        return redirect()->route($creatorRoute, $documentId)
                            ->with('success', 'Berhasil masuk! Silakan unggah perbaikan dokumen.');
                    }
                }
            }
            return redirect()->route('reviewer.show', $documentId)
                ->with('success', 'Berhasil masuk otomatis! Silakan tinjau dokumen ini.');
        }

        // Jika yang klik ternyata admin, baru masuk ke dashboard admin
        return redirect()->route('admin.dashboard');
    }
}
