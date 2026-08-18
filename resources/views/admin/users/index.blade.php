@extends('layouts.admin')

@section('title', 'Kelola Akun Pegawai')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .eqms-scope {
        font-family: 'Poppins', sans-serif;
    }
</style>

<div class="p-8 eqms-scope">
    {{-- Header Halaman --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div class="flex items-center space-x-3">
            <div class="bg-[#1e293b] p-2.5 rounded-xl text-white shadow-md">
                <i class="fa-solid fa-users-gear text-lg"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-[#1e293b] uppercase tracking-tight">KELOLA AKUN PEGAWAI</h2>
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Manajemen Akses Pimpinan dan Staf e-QMS</p>
            </div>
        </div>

        {{-- Fitur Search & Tambah (UI Dipercantik dengan Efek Floating) --}}
        <div class="flex items-center space-x-3">
            <form action="{{ route('admin.users.index') }}" method="GET" class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." 
                       class="pl-10 pr-4 py-2.5 bg-white border border-gray-100 rounded-xl shadow-sm text-xs font-bold text-[#1e293b] focus:ring-2 focus:ring-blue-500 outline-none w-60 transition-all placeholder-gray-400">
                <div class="absolute left-3.5 top-3 text-gray-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
            </form>
            
            <a href="{{ route('admin.users.create') }}" 
               class="bg-[#1e293b] text-white px-5 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-wider hover:bg-blue-600 transition-all duration-300 shadow-md hover:shadow-blue-100 flex items-center gap-2 transform hover:-translate-y-0.5">
                <i class="fa-solid fa-user-plus text-xs"></i> Tambah Pegawai
            </a>
        </div>
    </div>

    {{-- Daftar User dalam Bentuk List Modern Kardus Putih --}}
    <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-[#1e293b] text-white text-left text-[9px] font-black uppercase tracking-wider border-none">
                        <th class="px-6 py-4.5">Profil Pegawai</th>
                        <th class="px-6 py-4.5">Jabatan / Role</th>
                        <th class="px-6 py-4.5">Status Akses</th>
                        <th class="px-6 py-4.5 text-center">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50/40 transition-colors duration-200 group">
                        <td class="px-6 py-4.5">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-slate-100 group-hover:bg-blue-50 text-[#1e293b] group-hover:text-blue-600 rounded-xl flex items-center justify-center font-extrabold text-sm shadow-inner transition-colors duration-300 uppercase">
                                    {{ strtoupper(substr($user->full_name ?: $user->username, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-[#1e293b] tracking-tight text-xs">{{ $user->full_name ?: $user->username }}</p>
                                    <p class="text-[10px] text-gray-400 font-medium mt-0.5"><span class="font-mono text-gray-500">@ {{ $user->username }}</span> • {{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4.5">
                            <span class="inline-block px-3 py-1.5 bg-blue-50/50 text-blue-600 rounded-xl text-[9px] font-bold uppercase tracking-wider border border-blue-100/30">
                                <i class="fa-solid fa-id-card-clip mr-1 text-[10px]"></i> {{ $user->role }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-4.5">
                            @if($user->status)
                                <span class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[9px] font-bold uppercase tracking-wider border border-emerald-100/40">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 bg-red-50 text-red-600 rounded-lg text-[9px] font-bold uppercase tracking-wider border border-red-100/40">
                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></span> Non-Aktif
                                </span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4.5">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="p-2 bg-gray-50 text-gray-500 border border-gray-100 rounded-xl hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all duration-300 shadow-sm text-xs flex items-center justify-center w-8 h-8"
                                   title="Edit Akun Pegawai">
                                    <i class="fa-solid fa-user-pen"></i>
                                </a>
                                
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus permanen akun ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" 
                                            class="p-2 bg-gray-50 text-red-500 border border-gray-100 rounded-xl hover:bg-red-500 hover:text-white hover:border-red-500 transition-all duration-300 shadow-sm text-xs flex items-center justify-center w-8 h-8"
                                            title="Hapus Akun Permanen">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Pagination Area --}}
        @if($users->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection