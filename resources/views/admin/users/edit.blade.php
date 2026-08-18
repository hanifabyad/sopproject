@extends('layouts.admin')

@section('title', 'Edit Akun Pegawai')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-[3rem] shadow-xl p-12">
    <div class="flex items-center space-x-4 mb-10">
        <div class="bg-[#1e293b] p-3 rounded-xl text-white shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.5 3.5a2.121 2.121 0 113 3L7 19l-4 1 1-4L16.5 3.5z"></path>
            </svg>
        </div>
        <div>
            <h2 class="text-3xl font-black text-[#1e293b] uppercase tracking-tighter">EDIT AKUN PEGAWAI</h2>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">Perbarui informasi jabatan atau status akses</p>
        </div>
    </div>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <label class="block text-[10px] font-black uppercase text-gray-400 mb-3 ml-1">Nama Lengkap</label>
                <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="w-full p-4 bg-gray-50 rounded-2xl border-none font-bold text-sm" required>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase text-gray-400 mb-3 ml-1">Username (ID Login)</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" class="w-full p-4 bg-gray-50 rounded-2xl border-none font-bold text-sm" required>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase text-gray-400 mb-3 ml-1">Email</label>
                <input type="email" name="email" value="{{ $user->email }}" class="w-full p-4 bg-gray-50 rounded-2xl border-none font-bold text-sm" required>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase text-gray-400 mb-3 ml-1">Jabatan / Role</label>
                <select name="role" class="w-full p-4 bg-gray-50 rounded-2xl border-none font-bold text-sm" required>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ $user->role == $role ? 'selected' : '' }}>{{ $role }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase text-gray-400 mb-3 ml-1">Status Akun</label>
                <select name="status" class="w-full p-4 bg-gray-50 rounded-2xl border-none font-bold text-sm" required>
                    <option value="1" {{ $user->status ? 'selected' : '' }}>AKTIF</option>
                    <option value="0" {{ !$user->status ? 'selected' : '' }}>NON-AKTIF</option>
                </select>
            </div>
        </div>

        <div class="bg-blue-50 p-6 rounded-3xl border border-blue-100 mt-4">
            <label class="block text-[10px] font-black uppercase text-blue-500 mb-3">Ganti Password (Kosongkan jika tidak ingin diubah)</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="password" name="password" class="w-full p-4 bg-white rounded-2xl border-none font-bold text-sm" placeholder="Password Baru">
                <input type="password" name="password_confirmation" class="w-full p-4 bg-white rounded-2xl border-none font-bold text-sm" placeholder="Ulangi Password">
            </div>
        </div>

        <div class="flex items-center space-x-4 pt-6">
            <a href="{{ route('admin.users.index') }}" class="flex-1 py-5 bg-gray-100 text-[#1e293b] rounded-2xl font-black uppercase text-xs text-center tracking-widest hover:bg-gray-200 transition-all">
                Batal
            </a>
            <button type="submit" class="flex-[2] py-5 bg-[#1e293b] text-white rounded-2xl font-black uppercase text-sm tracking-[0.2em] shadow-xl hover:bg-blue-600 transition-all">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection