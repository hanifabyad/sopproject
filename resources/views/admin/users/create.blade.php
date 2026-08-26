@extends('layouts.admin')

@section('title', 'Tambah Pegawai Baru')
@section('header_title', 'Registrasi & Registrasi Akun Pegawai')

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
                <span class="font-medium text-on-surface">Tambah Pegawai Baru</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold text-on-surface uppercase tracking-tight">Tambah Akun Pegawai Baru</h2>
            <p class="text-xs text-on-surface-variant mt-0.5">Input data pimpinan unit bisnis, reviewer, dan pendukung operasional e-QMS</p>
        </div>
    </div>

    <!-- MAIN FORM CONTAINER -->
    <div class="bg-white rounded-lg p-6 md:p-8 shadow-sm border border-sand-200/60 space-y-6">
        @if ($errors->any())
            <div class="p-3.5 bg-[#ffdad6] border-l-4 border-[#ba1a1a] text-[#ba1a1a] font-semibold text-xs rounded-r-md shadow-sm space-y-1">
                <div class="flex items-center gap-2 font-bold">
                    <span class="material-symbols-outlined text-base">error</span>
                    <span>Gagal Menyimpan Akun:</span>
                </div>
                <ul class="list-disc ml-6 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="status" value="1">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Nama Lengkap</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all placeholder-[#d6cebf]" placeholder="Masukkan nama lengkap" required>
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Username (ID Login)</label>
                    <input type="text" name="username" value="{{ old('username') }}" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all placeholder-[#d6cebf]" placeholder="Masukkan username" required>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all placeholder-[#d6cebf]" placeholder="Masukkan alamat email" required>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="password" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all placeholder-[#d6cebf]" placeholder="••••••••" required>
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all placeholder-[#d6cebf]" placeholder="••••••••" required>
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Role Jabatan / Posisi</label>
                    <select name="role" class="w-full bg-sand-50 border border-sand-200 rounded-md p-3 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all" required>
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                {{ $role }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-sand-200/40 flex justify-end">
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-charcoal-900 text-gold-fixed hover:bg-black rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">person_add</span>
                    <span>Simpan Pegawai</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection