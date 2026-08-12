<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Daftar role lengkap sesuai dokumen posisi
     */
private $roles = [
    // --- STRUKTUR PIMPINAN & BU ---
    'Direktur Utama', 
    'Ka. Div Retail', 
    'Wa. Ka. Div Retail', 
    'Chief of Staff',
    'Management Representative', // Trinwetty
    'Marine Superintendent',    // Indrajat
    'Chief F&A',
    'Ka. Div F&A',
    
    // --- UNIT BISNIS (BU) ---
    'Ka. BU SPBU', 'Chief F&A SPBU', 'Ka. Operasional SPBU', 'Ka. Operasional BBM Retail', 'Koordinator Sales & Marketing',
    'Ka. BU Gas & SPBE', 'Chief F&A Gas', 'Ka. Operasional',
    'Ka. BU Inmarr', 'Chief F & A Inmarr', 'Ka. Operasional Inmarr',
    'Ka. BU CPT',
    
    // --- DEPARTEMEN SUPPORT ---
    'KA.DEPT.HC', 'KA.DEPT.ADMIN & LEGAL', 'KA.DEPT.IT', 'KA.DEPT.CORPORATE SEKTARIS', 
    'KA.DEPT.INTERNAL AUDIT & RISK MANAGEMENT', 'KA.DEPT.PAJAK', 'KA.DEPT.F & A', 
    'KA.DEPT.KEUANGAN', 'KA.DEPT.SALES & MARKETING', 'KA.DEPT.QMS', 'KA.DEPT.HSE', 
    'KA.DEPT.PROCRUTMEN', 'KA.DEPT.INTERNAL AUDIT','Dept. Internal Audit',
    
    // --- LAINNYA ---
    'office', 'reviewer'
];
    public function index(Request $request)
    {
        $search = $request->input('search');
        $users = User::where('role', '!=', 'admin')
            ->when($search, function ($query, $search) {
                return $query->where('username', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%")
                             ->orWhere('role', 'like', "%{$search}%");
            })
            ->orderBy('username')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = $this->roles;
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
{
    // 1. Validasi dengan pesan error kustom agar kita tahu apa yang salah
    $request->validate([
        'username' => 'required|string|max:255|unique:users',
        'email'    => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8', // Hapus 'confirmed' jika di form tidak ada input password_confirmation
        'role'     => ['required'], // Longgarkan dulu validasinya untuk memastikan data masuk
        'status'   => 'required',
    ]);

    try {
        // 2. Simpan ke Database
        User::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'status'   => $request->status ?? 1, // Default aktif jika kosong
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Akun pimpinan berhasil didaftarkan!');
    } catch (\Exception $e) {
        // Jika gagal karena database, balik ke form dengan pesan error asli
        return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
    }
}

    public function edit(User $user)
    {
        if ($user->role == 'admin') {
            return redirect()->route('admin.users.index')->with('error', 'Akun Admin tidak bisa diedit.');
        }

        $roles = $this->roles;
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'     => ['required', Rule::in($this->roles)],
            'status'   => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'], 
        ]);

        $data = [
            'username' => $request->username,
            'email'    => $request->email,
            'role'     => $request->role,
            'status'   => $request->status,
        ];
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return redirect()->route('admin.users.index')->with('success', 'Data akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->role == 'admin') {
            return redirect()->route('admin.users.index')->with('error', 'Admin utama tidak bisa dihapus.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Akun berhasil dihapus.');
    }
}