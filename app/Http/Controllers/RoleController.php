<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RoleManagementService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Tampilkan master daftar jabatan per kategori dan jumlah pemegang jabatan
     */
    public function index(Request $request)
    {
        $categorizedRoles = RoleManagementService::getCategorizedRoles();
        $userCounts = RoleManagementService::getRoleUserCounts();
        $allRoles = RoleManagementService::getAllRoles();

        $search = trim((string)$request->input('search'));
        if ($search !== '') {
            $filtered = [];
            foreach ($categorizedRoles as $category => $roles) {
                $matchedRoles = array_filter($roles, function ($role) use ($search) {
                    return stripos($role, $search) !== false;
                });
                if (!empty($matchedRoles)) {
                    $filtered[$category] = array_values($matchedRoles);
                }
            }
            $categorizedRoles = $filtered;
        }

        // Ambil daftar user aktif yang terikat dengan jabatan
        $usersByRole = User::whereNotNull('role')
            ->where('role', '!=', '')
            ->where('role', '!=', 'admin')
            ->select('id', 'username', 'full_name', 'role')
            ->get()
            ->groupBy(fn($u) => strtolower(trim((string)$u->role)));

        return view('admin.roles.index', compact('categorizedRoles', 'userCounts', 'allRoles', 'usersByRole', 'search'));
    }

    /**
     * Tambah jabatan baru ke dalam master data
     */
    public function store(Request $request)
    {
        $request->validate([
            'role' => 'required|string|max:255',
        ], [
            'role.required' => 'Nama jabatan wajib diisi.',
            'role.max'      => 'Nama jabatan maksimal 255 karakter.',
        ]);

        $role = trim((string)$request->role);
        if (strtolower($role) === 'admin') {
            return back()->with('error', 'Role "admin" adalah hak akses internal sistem dan tidak dapat ditambahkan sebagai jabatan.');
        }

        if (RoleManagementService::registerRole($role)) {
            return back()->with('success', "Jabatan '{$role}' berhasil ditambahkan ke daftar sistem!");
        }

        return back()->with('error', 'Gagal menambahkan jabatan. Silakan coba kembali.');
    }

    /**
     * Hapus jabatan dari sistem dengan validasi keamanan
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
        ]);

        $role = trim((string)$request->role);
        if (strtolower($role) === 'admin') {
            return back()->with('error', 'Role "admin" tidak dapat dihapus.');
        }

        // Cek apakah masih ada user yang memegang role ini
        $activeUsers = User::where('role', $role)
            ->orWhereRaw('LOWER(role) = ?', [strtolower($role)])
            ->get();

        if ($activeUsers->isNotEmpty()) {
            $userNames = $activeUsers->take(3)->map(fn($u) => $u->full_name ?: $u->username)->implode(', ');
            $extra = $activeUsers->count() > 3 ? ' dan ' . ($activeUsers->count() - 3) . ' akun lainnya' : '';
            return back()->with('error', "Jabatan '{$role}' tidak dapat dihapus karena masih digunakan oleh {$activeUsers->count()} akun pegawai ({$userNames}{$extra}). Silakan alihkan jabatan akun tersebut terlebih dahulu.");
        }

        if (RoleManagementService::deleteRole($role)) {
            return back()->with('success', "Jabatan '{$role}' berhasil dihapus dari daftar sistem.");
        }

        return back()->with('error', 'Gagal menghapus jabatan dari sistem.');
    }
}
