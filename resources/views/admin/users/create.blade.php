@extends('layouts.admin')

@section('title', 'Tambah Pegawai Baru')

@section('content')
<div class="max-w-5xl mx-auto bg-white rounded-[3rem] shadow-xl p-12">
    {{-- Header --}}
    <div class="flex items-center space-x-4 mb-10">
        <div class="bg-[#1e293b] p-3 rounded-xl text-white shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
        </div>
        <div>
            <h2 class="text-3xl font-black text-[#1e293b] uppercase tracking-tighter">REGISTRASI PEGAWAI</h2>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">Input data pimpinan unit bisnis dan departemen support</p>
        </div>
    </div>

    {{-- Alert Error: Muncul jika validasi gagal --}}
    @if ($errors->any())
        <div class="mb-8 p-6 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-2xl shadow-sm">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <p class="font-black uppercase text-xs tracking-widest">Gagal Menyimpan Akun:</p>
            </div>
            <ul class="list-disc list-inside text-xs font-bold space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-8">
        @csrf
        
        {{-- Default status aktif --}}
        <input type="hidden" name="status" value="1">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Username --}}
            <div>
                <label class="block text-[10px] font-black uppercase text-gray-400 mb-3 ml-1">Nama Lengkap</label>
                <input type="text" name="username" value="{{ old('username') }}" class="w-full p-4 bg-gray-50 rounded-2xl border-2 border-transparent focus:border-[#1e293b] focus:bg-white transition-all font-bold text-sm" placeholder="Contoh: Baim Wong" required>
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-[10px] font-black uppercase text-gray-400 mb-3 ml-1">Alamat Email (Untuk Notifikasi)</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full p-4 bg-gray-50 rounded-2xl border-2 border-transparent focus:border-[#1e293b] focus:bg-white transition-all font-bold text-sm" placeholder="email@perusahaan.com" required>
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-[10px] font-black uppercase text-gray-400 mb-3 ml-1">Password</label>
                <input type="password" name="password" class="w-full p-4 bg-gray-50 rounded-2xl border-2 border-transparent focus:border-[#1e293b] focus:bg-white transition-all font-bold text-sm" placeholder="••••••••" required>
            </div>

            {{-- Konfirmasi Password [PENTING] --}}
            <div>
                <label class="block text-[10px] font-black uppercase text-gray-400 mb-3 ml-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="w-full p-4 bg-gray-50 rounded-2xl border-2 border-transparent focus:border-[#1e293b] focus:bg-white transition-all font-bold text-sm" placeholder="••••••••" required>
            </div>

            {{-- Dropdown Role Gabungan --}}
            <div class="md:col-span-2">
                <label class="block text-[10px] font-black uppercase text-gray-400 mb-3 ml-1">Role Jabatan / Posisi</label>
                <select name="role" class="w-full p-4 bg-gray-50 rounded-2xl border-2 border-transparent focus:border-[#1e293b] focus:bg-white transition-all font-bold text-sm" required>
    <option value="">-- Pilih Jabatan --</option>
    @foreach($roles as $role)
        <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
            {{ $role }}
        </option>
    @endforeach
</select>
            </div>
        </div>

        <div class="pt-6">
            <button type="submit" class="w-full py-5 bg-[#1e293b] text-white rounded-2xl font-black uppercase text-sm tracking-[0.2em] shadow-xl hover:bg-blue-600 hover:-translate-y-1 transition-all duration-300">
                Simpan & Aktifkan Akun
            </button>
        </div>
    </form>
</div>
@endsection