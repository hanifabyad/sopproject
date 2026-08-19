@extends('layouts.admin')

@section('title', 'Edit Akun Pegawai')
@section('header_title', 'Perbarui Informasi Akun Pegawai')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-[#cfc6ac] bg-white text-[#4d4633] hover:bg-[#f7f6f2] hover:text-[#1e1c14] text-xs font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <span class="text-[#cfc6ac]">|</span>
            <div class="flex items-center gap-2 text-xs text-[#4d4633]">
                <a href="{{ route('admin.users.index') }}" class="hover:text-[#705d00]">Kelola Akun</a>
                <span>/</span>
                <span class="font-medium text-[#1e1c14]">Edit Akun ({{ $user->username }})</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold text-[#1e1c14] uppercase tracking-tight">Edit Akun Pegawai</h2>
            <p class="text-xs text-[#4d4633] mt-0.5">Perbarui informasi nama, username, email, jabatan, atau status akses</p>
        </div>
    </div>

    <!-- MAIN FORM CONTAINER -->
    <div class="bg-white rounded-lg p-6 md:p-8 shadow-sm border border-[#cfc6ac]/60 space-y-6">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-bold text-[#1e1c14] uppercase tracking-wider mb-2">Nama Lengkap</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="w-full bg-[#fbf9f4] border border-[#cfc6ac] rounded-md p-3 font-semibold text-xs text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none transition-all" required>
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-xs font-bold text-[#1e1c14] uppercase tracking-wider mb-2">Username (ID Login)</label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}" class="w-full bg-[#fbf9f4] border border-[#cfc6ac] rounded-md p-3 font-semibold text-xs text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none transition-all" required>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-[#1e1c14] uppercase tracking-wider mb-2">Email</label>
                    <input type="email" name="email" value="{{ $user->email }}" class="w-full bg-[#fbf9f4] border border-[#cfc6ac] rounded-md p-3 font-semibold text-xs text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none transition-all" required>
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-xs font-bold text-[#1e1c14] uppercase tracking-wider mb-2">Jabatan / Role</label>
                    <select name="role" class="w-full bg-[#fbf9f4] border border-[#cfc6ac] rounded-md p-3 font-semibold text-xs text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none transition-all" required>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ $user->role == $role ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-[#1e1c14] uppercase tracking-wider mb-2">Status Akun</label>
                    <select name="status" class="w-full bg-[#fbf9f4] border border-[#cfc6ac] rounded-md p-3 font-semibold text-xs text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none transition-all" required>
                        <option value="1" {{ $user->status ? 'selected' : '' }}>AKTIF</option>
                        <option value="0" {{ !$user->status ? 'selected' : '' }}>NON-AKTIF</option>
                    </select>
                </div>
            </div>

            <!-- Password Change Section -->
            <div class="p-4 bg-[#fff9ed] rounded-md border border-[#cfc6ac] space-y-3">
                <label class="block text-xs font-bold text-[#705d00]">Ganti Password (Kosongkan jika tidak ingin diubah)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="password" name="password" class="w-full bg-white border border-[#cfc6ac] rounded-md p-2.5 font-semibold text-xs text-[#1e1c14] focus:ring-2 focus:ring-[#705d00] outline-none" placeholder="Password Baru">
                    <input type="password" name="password_confirmation" class="w-full bg-white border border-[#cfc6ac] rounded-md p-2.5 font-semibold text-xs text-[#1e1c14] focus:ring-2 focus:ring-[#705d00] outline-none" placeholder="Ulangi Password">
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-[#cfc6ac]/40">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 bg-[#f7f6f2] border border-[#cfc6ac] text-[#4d4633] hover:bg-[#fff9ed] rounded-md font-bold text-xs uppercase text-center transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-[#333028] text-[#ffe16e] hover:bg-black rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">save</span>
                    <span>Simpan Perubahan Akun</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection