@extends('layouts.admin')

@section('title', 'Edit Akun Pegawai')
@section('header_title', 'Perbarui Informasi Akun Pegawai')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.users.index') }}" variant="light" />
            <span class="text-white/30">|</span>
            <div class="flex items-center gap-2 text-xs text-white/80">
                <a href="{{ route('admin.users.index') }}" class="hover:text-[#ffe16e] font-medium">Kelola Akun</a>
                <span>/</span>
                <span class="font-bold text-white">Edit Akun ({{ $user->username }})</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold tracking-tight text-white">Edit Akun Pegawai</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">Perbarui informasi nama, username, email, jabatan, atau status akses</p>
        </div>
    </div>

    <!-- MAIN FORM CONTAINER -->
    <div class="bg-white rounded-lg p-6 md:p-8 shadow-sm border border-sand-200/60 space-y-6">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-bold text-on-surface capitalize tracking-wider mb-2">Nama Lengkap</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all" required>
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-xs font-bold text-on-surface capitalize tracking-wider mb-2">Username (ID Login)</label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all" required>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-on-surface capitalize tracking-wider mb-2">Email</label>
                    <input type="email" name="email" value="{{ $user->email }}" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all" required>
                </div>

                <!-- Role -->
                @php
                    $roleGroups = $categorizedRoles ?? \App\Services\RoleManagementService::getCategorizedRoles();
                @endphp
                <div>
                    <label class="block text-xs font-bold text-on-surface capitalize tracking-wider mb-2">Jabatan / Role</label>
                    <select name="role" id="roleSelect" onchange="toggleCustomRole(this.value)" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all" required>
                        <option value="">-- Pilih Jabatan Pegawai --</option>
                        <option value="__custom__" {{ old('role') == '__custom__' ? 'selected' : '' }} class="font-bold text-[#1677B8] bg-blue-50 py-2">✨ + Ketik Jabatan Baru (Kustom)...</option>
                        @foreach($roleGroups as $categoryName => $roleList)
                            <option disabled class="font-extrabold text-[#1677B8] bg-slate-100 py-2">
                                ━━━ {{ strtoupper($categoryName) }} ━━━
                            </option>
                            @foreach($roleList as $role)
                                <option value="{{ $role }}" {{ (old('role', $user->role) == $role) ? 'selected' : '' }} class="font-semibold text-slate-800 py-1.5">
                                    &nbsp;&nbsp;&nbsp;&nbsp;• {{ $role }}
                                </option>
                            @endforeach
                            <option disabled class="bg-slate-50 py-0.5"></option>
                        @endforeach
                    </select>

                    <!-- Input Tambahan Jika Memilih Jabatan Kustom Baru -->
                    <div id="customRoleWrapper" class="{{ old('role') == '__custom__' ? '' : 'hidden' }} mt-2.5">
                        <label class="block text-[11px] font-bold text-[#1677B8] mb-1">Nama Jabatan Baru / Kustom</label>
                        <input type="text" name="custom_role" id="customRoleInput" value="{{ old('custom_role') }}" placeholder="Ketik jabatan, misal: Spv Maintenance, Legal Staff..." class="w-full bg-white border-2 border-[#1677B8]/40 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:border-[#1677B8] focus:ring-2 focus:ring-blue-100 outline-none transition-all placeholder-slate-400">
                        <p class="text-[10px] text-slate-500 mt-1">* Jabatan baru akan otomatis tersimpan ke daftar sistem.</p>
                    </div>
                </div>

                <!-- Status -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-on-surface capitalize tracking-wider mb-2">Status Akun</label>
                    <select name="status" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all" required>
                        <option value="1" {{ $user->status ? 'selected' : '' }}>AKTIF</option>
                        <option value="0" {{ !$user->status ? 'selected' : '' }}>NON-AKTIF</option>
                    </select>
                </div>
            </div>

            <!-- Password Change Section -->
            <div class="p-4 bg-[#fff9ed] rounded-md border border-sand-200 space-y-3">
                <label class="block text-xs font-bold text-gold-500">Ganti Password (Kosongkan jika tidak ingin diubah)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="password" name="password" class="w-full bg-white border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:ring-2 focus:ring-gold-500 outline-none" placeholder="Password Baru">
                    <input type="password" name="password_confirmation" class="w-full bg-white border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:ring-2 focus:ring-gold-500 outline-none" placeholder="Ulangi Password">
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-sand-200/40">
                <x-interactive-button text="Batal" variant="outline" href="{{ route('admin.users.index') }}" />
                <x-interactive-button text="Simpan Perubahan Akun" icon="ph ph-floppy-disk text-sm" class="w-full sm:w-auto" />
            </div>
        </form>
    </div>
</div>

<script>
function toggleCustomRole(val) {
    const wrapper = document.getElementById('customRoleWrapper');
    const input = document.getElementById('customRoleInput');
    if (val === '__custom__') {
        wrapper.classList.remove('hidden');
        input.setAttribute('required', 'required');
        input.focus();
    } else {
        wrapper.classList.add('hidden');
        input.removeAttribute('required');
    }
}
</script>
@endsection
