<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RoleManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
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
        $roles = RoleManagementService::getAllRoles();
        $categorizedRoles = RoleManagementService::getCategorizedRoles();
        return view('admin.users.create', compact('roles', 'categorizedRoles'));
    }

    public function store(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'full_name'   => 'required|string|max:255',
            'username'    => 'required|string|max:255|unique:users',
            'email'       => 'required|string|email|max:255|unique:users',
            'password'    => 'required|string|min:8',
            'role'        => 'required|string',
            'custom_role' => 'nullable|string|max:255',
            'status'      => 'required',
        ]);

        $role = trim($request->role);
        if ($role === '__custom__') {
            $role = trim((string)$request->custom_role);
            if ($role === '') {
                return back()->withInput()->withErrors(['custom_role' => 'Nama jabatan baru/kustom wajib diisi jika memilih opsi kustom.']);
            }
            RoleManagementService::registerRole($role);
        } else {
            RoleManagementService::registerRole($role);
        }

        try {
            // 2. Simpan ke Database
            User::create([
                'username'  => $request->username,
                'full_name' => $request->full_name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => $role,
                'status'    => $request->status ?? 1,
            ]);

            return redirect()->route('admin.users.index')->with('success', 'Akun pimpinan/pegawai berhasil didaftarkan!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function edit(User $user)
    {
        if ($user->role == 'admin') {
            return redirect()->route('admin.users.index')->with('error', 'Akun Admin tidak bisa diedit.');
        }

        $roles = RoleManagementService::getAllRoles();
        if (!empty($user->role) && !in_array($user->role, $roles)) {
            $roles[] = $user->role;
        }
        $categorizedRoles = RoleManagementService::getCategorizedRoles();
        return view('admin.users.edit', compact('user', 'roles', 'categorizedRoles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'full_name'   => ['required', 'string', 'max:255'],
            'username'    => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email'       => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'        => ['required', 'string'],
            'custom_role' => ['nullable', 'string', 'max:255'],
            'status'      => ['required', 'boolean'],
            'password'    => ['nullable', 'string', 'min:8', 'confirmed'], 
        ]);

        $role = trim($request->role);
        if ($role === '__custom__') {
            $role = trim((string)$request->custom_role);
            if ($role === '') {
                return back()->withInput()->withErrors(['custom_role' => 'Nama jabatan baru/kustom wajib diisi jika memilih opsi kustom.']);
            }
            RoleManagementService::registerRole($role);
        } else {
            RoleManagementService::registerRole($role);
        }

        $data = [
            'username'  => $request->username,
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'role'      => $role,
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
