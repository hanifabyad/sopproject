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
    'Direktur CPT',
    
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
        // 1. Validasi input
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:8',
            'role'      => ['required'],
            'status'    => 'required',
        ]);

        try {
            // 2. Simpan ke Database
            User::create([
                'username'  => $request->username,
                'full_name' => $request->full_name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => $request->role,
                'status'    => $request->status ?? 1,
            ]);

            return redirect()->route('admin.users.index')->with('success', 'Akun pimpinan berhasil didaftarkan!');
        } catch (\Exception $e) {
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
            'full_name' => ['required', 'string', 'max:255'],
            'username'  => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'      => ['required', Rule::in($this->roles)],
            'status'    => ['required', 'boolean'],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'], 
        ]);

        $data = [
            'username'  => $request->username,
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'role'      => $request->role,
            'status'    => $request->status,
        ];
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return redirect()->route('admin.users.index')->with('success', 'Data akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')->with('error', 'Admin utama tidak bisa dihapus.');
        }

        try {
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', 'Akun berhasil dihapus permanen.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), '1451')) {
                return redirect()->route('admin.users.index')->with('error', 'Akun tidak dapat dihapus karena telah memiliki riwayat aktivitas atau dokumen pada sistem.');
            }
            return redirect()->route('admin.users.index')->with('error', 'Terjadi kesalahan sistem database saat menghapus akun.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.users.index')->with('error', 'Terjadi kesalahan sistem saat menghapus akun.');
        }
    }
}
