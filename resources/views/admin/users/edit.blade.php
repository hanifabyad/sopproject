@extends('layouts.admin')

@section('title', 'Edit Akun Pegawai')
@section('header_title', 'Perbarui Informasi Akun Pegawai')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-sand-200 bg-white text-on-surface-variant hover:bg-canvas hover:text-on-surface text-xs font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <span class="text-sand-200">|</span>
            <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                <a href="{{ route('admin.users.index') }}" class="hover:text-gold-500">Kelola Akun</a>
                <span>/</span>
                <span class="font-medium text-on-surface">Edit Akun ({{ $user->username }})</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold text-on-surface uppercase tracking-tight">Edit Akun Pegawai</h2>
            <p class="text-xs text-on-surface-variant mt-0.5">Perbarui informasi nama, username, email, jabatan, atau status akses</p>
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
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Nama Lengkap</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all" required>
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Username (ID Login)</label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all" required>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Email</label>
                    <input type="email" name="email" value="{{ $user->email }}" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all" required>
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Jabatan / Role</label>
                    <select name="role" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all" required>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ $user->role == $role ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Status Akun</label>
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
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 bg-canvas border border-sand-200 text-on-surface-variant hover:bg-[#fff9ed] rounded-md font-bold text-xs uppercase text-center transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-charcoal-900 text-gold-fixed hover:bg-black rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">save</span>
                    <span>Simpan Perubahan Akun</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection