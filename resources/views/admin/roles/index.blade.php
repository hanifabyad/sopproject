@extends('layouts.admin')

@section('title', 'Kelola Master Jabatan')
@section('header_title', 'Master Data Jabatan')

@section('content')
<div class="space-y-6">

    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON (IDENTIK DENGAN KELOLA AKUN) -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-3">
            <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
            <div class="flex items-center gap-3">
                <x-back-button href="{{ route('admin.users.index') }}" variant="light" text="Kembali" />
                <span class="text-white/30">|</span>
                <div class="flex items-center gap-2 text-xs text-white/80">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#ffe16e] font-medium flex items-center gap-1">
                        <i class="ph ph-squares-four text-sm"></i>
                        <span>Dashboard</span>
                    </a>
                    <span>/</span>
                    <a href="{{ route('admin.users.index') }}" class="hover:text-[#ffe16e] font-medium">Kelola Akun</a>
                    <span>/</span>
                    <span class="font-bold text-white">Master Jabatan</span>
                </div>
            </div>

            <!-- Baris Judul -->
            <div>
                <h2 class="text-xl font-bold tracking-normal">Kelola Master Jabatan</h2>
                <p class="text-xs text-white/85 mt-0.5 font-normal">Daftar hierarki jabatan, kategori unit kerja, dan pengaturan hapus jabatan</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <!-- Search Form dengan Height Selaras 38px -->
            <form action="{{ route('admin.roles.index') }}" method="GET" class="relative flex items-center">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama jabatan..." 
                       class="h-[38px] pl-9 pr-8 bg-white border border-blue-300 focus:border-[#1677B8] focus:ring-1 focus:ring-[#1677B8] rounded-[2px] shadow-xs text-xs font-normal text-slate-900 outline-none w-56 sm:w-64 transition-all placeholder:text-slate-400">
                
                <button type="submit" class="absolute left-1 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center text-slate-400 hover:text-[#1677B8] transition-colors cursor-pointer border-none bg-transparent" title="Cari Jabatan">
                    <i class="ph ph-magnifying-glass text-base"></i>
                </button>

                @if(request('search'))
                    <a href="{{ route('admin.roles.index') }}" class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center text-slate-400 hover:text-rose-600 transition-colors" title="Reset pencarian">
                        <i class="ph ph-x text-xs"></i>
                    </a>
                @endif
            </form>
            
            <!-- Tombol Tambah Jabatan (Identik dengan Tombol Tambah Pegawai di Kelola Akun) -->
            <x-interactive-button text="Tambah Jabatan" variant="primary" icon="ph ph-plus-circle text-base" onclick="openAddRoleModal()" type="button" />
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="p-3.5 bg-emerald-50 border-l-3 border-emerald-500 rounded-[2px] flex items-center justify-between text-emerald-800 text-xs font-medium shadow-xs">
            <div class="flex items-center gap-2">
                <i class="ph ph-check-circle text-base text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 bg-transparent border-none cursor-pointer">
                <i class="ph ph-x text-xs"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-3.5 bg-rose-50 border-l-3 border-rose-500 rounded-[2px] flex items-center justify-between text-rose-800 text-xs font-medium shadow-xs">
            <div class="flex items-center gap-2">
                <i class="ph ph-warning-circle text-base text-rose-600"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 bg-transparent border-none cursor-pointer">
                <i class="ph ph-x text-xs"></i>
            </button>
        </div>
    @endif

    <!-- QUICK STATS BAR -->
    @php
        $totalRolesCount = count($allRoles);
        $totalCategoriesCount = count($categorizedRoles);
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        <div class="bg-white rounded-md p-3.5 border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-9 h-9 rounded bg-blue-50 text-[#1677B8] flex items-center justify-center text-base flex-shrink-0">
                <i class="ph ph-folders"></i>
            </div>
            <div>
                <p class="text-[11px] text-slate-500 font-normal">Total Kategori</p>
                <p class="text-sm font-semibold text-slate-800 leading-tight">{{ $totalCategoriesCount }} Kategori Unit Kerja</p>
            </div>
        </div>

        <div class="bg-white rounded-md p-3.5 border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-9 h-9 rounded bg-sky-50 text-sky-600 flex items-center justify-center text-base flex-shrink-0">
                <i class="ph ph-briefcase"></i>
            </div>
            <div>
                <p class="text-[11px] text-slate-500 font-normal">Total Jabatan</p>
                <p class="text-sm font-semibold text-slate-800 leading-tight">{{ $totalRolesCount }} Jabatan Terdaftar</p>
            </div>
        </div>

        <div class="bg-white rounded-md p-3.5 border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-9 h-9 rounded bg-amber-50 text-amber-600 flex items-center justify-center text-base flex-shrink-0">
                <i class="ph ph-shield-check"></i>
            </div>
            <div>
                <p class="text-[11px] text-slate-500 font-normal">Proteksi Integritas</p>
                <p class="text-xs font-medium text-slate-700 leading-tight">Jabatan aktif otomatis terkunci dari hapus</p>
            </div>
        </div>
    </div>

    <!-- CATEGORIZED ROLES LIST -->
    <div class="space-y-4">
        @php
            $categoryIcons = [
                'Direksi & Pimpinan Eksekutif'    => 'ph ph-buildings',
                'Departemen Support'              => 'ph ph-wrench',
                'Divisi Finance & Accounting'     => 'ph ph-chart-line-up',
                'Divisi Retail & Komersial'       => 'ph ph-storefront',
                'Unit Bisnis: SPBU & Retail BBM'  => 'ph ph-gas-pump',
                'Unit Bisnis: Gas & SPPBE'        => 'ph ph-flame',
                'Unit Bisnis: Inmarr / CNGM'      => 'ph ph-boat',
                'Unit Bisnis: CPT (Pelayaran & Maritim)' => 'ph ph-anchor',
                'Unit Bisnis: SBS'                => 'ph ph-factory',
                'Jabatan Kustom / Lainnya'        => 'ph ph-tag',
            ];
        @endphp

        @forelse($categorizedRoles as $categoryName => $roles)
            @php
                $catIcon = $categoryIcons[$categoryName] ?? 'ph ph-folder';
            @endphp
            <div class="bg-white rounded-md shadow-xs border border-slate-200/80 overflow-hidden">
                <!-- CATEGORY HEADER (DENGAN PHOSPHOR ICON, TANPA EMOTE) -->
                <div class="bg-slate-50/70 border-b border-slate-200 px-4 py-2.5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="{{ $catIcon }} text-sm text-[#1677B8]"></i>
                        <span class="text-xs font-semibold text-slate-800">{{ $categoryName }}</span>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-slate-200/60 text-slate-600 font-normal text-[11px]">
                        {{ count($roles) }} jabatan
                    </span>
                </div>

                <!-- ROLES TABLE -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-white border-b border-slate-100 text-[11px] font-medium text-slate-400">
                            <tr>
                                <th class="py-2 px-4 w-12 text-center font-normal">No</th>
                                <th class="py-2 px-4 font-normal">Nama Jabatan</th>
                                <th class="py-2 px-4 text-center w-40 font-normal">Status Pegawai</th>
                                <th class="py-2 px-4 text-right w-28 font-normal">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            @foreach($roles as $idx => $role)
                                @php
                                    $lowRole = strtolower(trim($role));
                                    $assignedUsers = $usersByRole->get($lowRole, collect());
                                    $assignedCount = $assignedUsers->count();
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-2.5 px-4 text-center text-slate-400 text-[11px]">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="py-2.5 px-4">
                                        <span class="font-normal text-slate-800 text-[12px]">{{ $role }}</span>
                                    </td>
                                    <td class="py-2.5 px-4 text-center">
                                        @if($assignedCount > 0)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-50 text-[#1677B8] font-normal text-[11px] border border-blue-200/60" 
                                                  title="Pegawai: {{ $assignedUsers->pluck('full_name')->filter()->implode(', ') }}">
                                                <i class="ph ph-user text-xs"></i>
                                                <span>{{ $assignedCount }} pegawai</span>
                                            </span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 rounded bg-slate-50 text-slate-400 font-normal text-[10.5px]">
                                                0 pegawai
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 text-right">
                                        @if($assignedCount === 0)
                                            <!-- Tombol Hapus Aktif -->
                                            <button type="button" 
                                                    onclick="confirmDeleteRole('{{ addslashes($role) }}')" 
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-normal text-rose-600 bg-rose-50/80 hover:bg-rose-100/80 border border-rose-200 rounded transition-colors cursor-pointer"
                                                    title="Hapus jabatan {{ $role }}">
                                                <i class="ph ph-trash text-xs"></i>
                                                <span>Hapus</span>
                                            </button>
                                        @else
                                            <!-- Tombol Terkunci (Ada Pengguna) -->
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10.5px] font-normal text-slate-400 bg-slate-50 border border-slate-200/60 rounded cursor-not-allowed"
                                                  title="Tidak dapat dihapus karena digunakan oleh {{ $assignedCount }} pegawai aktif">
                                                <i class="ph ph-lock text-xs"></i>
                                                <span>Terkunci</span>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-md p-10 text-center border border-slate-200 space-y-2.5">
                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-lg">
                    <i class="ph ph-magnifying-glass"></i>
                </div>
                <h3 class="text-xs font-semibold text-slate-700">Tidak ada jabatan yang sesuai pencarian</h3>
                <p class="text-xs text-slate-400">Silakan gunakan kata kunci lain atau reset filter.</p>
                <a href="{{ route('admin.roles.index') }}" class="inline-block px-3 py-1 bg-[#1677B8] text-white text-xs font-normal rounded">
                    Reset Pencarian
                </a>
            </div>
        @endforelse
    </div>

</div>

<!-- MODAL TAMBAH JABATAN BARU -->
<div id="addRoleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 hidden backdrop-blur-xs">
    <div class="bg-white rounded-md shadow-lg w-full max-w-sm border border-slate-200 overflow-hidden transform transition-all">
        <div class="bg-[#1677B8] px-4 py-3 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="ph ph-briefcase text-base"></i>
                <h3 class="font-medium text-xs">Tambah Master Jabatan Baru</h3>
            </div>
            <button type="button" onclick="closeAddRoleModal()" class="text-white/80 hover:text-white border-none bg-transparent cursor-pointer">
                <i class="ph ph-x text-sm"></i>
            </button>
        </div>

        <form action="{{ route('admin.roles.store') }}" method="POST" class="p-4 space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Nama Jabatan Baru</label>
                <input type="text" 
                       name="role" 
                       id="newRoleInput" 
                       placeholder="Contoh: Staff Legal, Spv Maintenance..." 
                       class="w-full bg-white border border-slate-300 rounded p-2 text-xs text-slate-800 focus:border-[#1677B8] focus:ring-1 focus:ring-[#1677B8] outline-none transition-all placeholder:text-slate-400 font-normal"
                       required>
                <p class="text-[11px] text-slate-400 mt-1">Gunakan huruf kapital di awal setiap kata (Title Case).</p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeAddRoleModal()" class="px-3 py-1.5 rounded border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-normal cursor-pointer bg-white">
                    Batal
                </button>
                <button type="submit" class="px-3 py-1.5 rounded bg-[#1677B8] hover:bg-[#1258a0] text-white text-xs font-medium cursor-pointer flex items-center gap-1 border-none">
                    <i class="ph ph-check text-xs"></i>
                    <span>Simpan Jabatan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL KONFIRMASI HAPUS JABATAN -->
<div id="deleteRoleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 hidden backdrop-blur-xs">
    <div class="bg-white rounded-md shadow-lg w-full max-w-sm border border-slate-200 overflow-hidden transform transition-all">
        <div class="p-5 text-center space-y-2.5">
            <div class="w-10 h-10 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto text-xl">
                <i class="ph ph-warning-circle"></i>
            </div>
            <h3 class="font-semibold text-slate-800 text-sm">Hapus Jabatan?</h3>
            <p class="text-xs text-slate-500 font-normal leading-relaxed">
                Yakin ingin menghapus jabatan <span id="deleteRoleNameLabel" class="font-semibold text-slate-700"></span> dari daftar sistem?
            </p>
        </div>

        <form action="{{ route('admin.roles.destroy') }}" method="POST" class="bg-slate-50 px-4 py-3 flex items-center justify-end gap-2 border-t border-slate-200">
            @csrf
            @method('DELETE')
            <input type="hidden" name="role" id="deleteRoleInput" value="">
            <button type="button" onclick="closeDeleteRoleModal()" class="px-3 py-1.5 rounded border border-slate-300 text-slate-600 hover:bg-white text-xs font-normal cursor-pointer bg-white">
                Batal
            </button>
            <button type="submit" class="px-3.5 py-1.5 rounded bg-rose-600 hover:bg-rose-700 text-white text-xs font-medium cursor-pointer flex items-center gap-1 border-none">
                <i class="ph ph-trash text-xs"></i>
                <span>Ya, Hapus</span>
            </button>
        </form>
    </div>
</div>

<script>
function openAddRoleModal() {
    document.getElementById('addRoleModal').classList.remove('hidden');
    setTimeout(() => document.getElementById('newRoleInput').focus(), 100);
}

function closeAddRoleModal() {
    document.getElementById('addRoleModal').classList.add('hidden');
}

function confirmDeleteRole(roleName) {
    document.getElementById('deleteRoleNameLabel').textContent = '"' + roleName + '"';
    document.getElementById('deleteRoleInput').value = roleName;
    document.getElementById('deleteRoleModal').classList.remove('hidden');
}

function closeDeleteRoleModal() {
    document.getElementById('deleteRoleModal').classList.add('hidden');
}

// Tutup modal jika klik luar
window.addEventListener('click', function(e) {
    const addModal = document.getElementById('addRoleModal');
    const deleteModal = document.getElementById('deleteRoleModal');
    if (e.target === addModal) closeAddRoleModal();
    if (e.target === deleteModal) closeDeleteRoleModal();
});
</script>
@endsection
