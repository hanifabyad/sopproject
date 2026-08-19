@extends('layouts.admin')

@section('title', 'Kelola Akun Pegawai')
@section('header_title', 'Manajemen Akun & Role Pegawai')

@section('content')
<div class="space-y-6">
    
    <!-- TOP HEADER BAR & SEARCH -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white rounded-lg p-6 border border-[#cfc6ac]/60 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 bg-[#ffd92f]/20 text-[#705d00] font-bold text-[10px] uppercase rounded tracking-wider border border-[#ffd92f]/40">Akses Pegawai</span>
            </div>
            <h2 class="text-xl font-extrabold text-[#1e1c14] tracking-tight mt-1">Kelola Akun Pegawai</h2>
            <p class="text-xs text-[#4d4633] mt-0.5">Daftar pengguna terdaftar dan manajemen wewenang peran e-QMS</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ route('admin.users.index') }}" method="GET" class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." 
                       class="pl-9 pr-3 py-2 bg-white border border-[#cfc6ac] rounded-md shadow-sm text-xs font-semibold text-[#1e1c14] focus:ring-2 focus:ring-[#705d00] outline-none w-64 transition-all placeholder-[#d6cebf]">
                <span class="material-symbols-outlined absolute left-2.5 top-2 text-base text-[#705d00]">search</span>
            </form>
            
            <a href="{{ route('admin.users.create') }}" 
               class="px-4 py-2 bg-[#333028] text-[#ffe16e] hover:bg-black rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-base">person_add</span>
                <span>Tambah Pegawai</span>
            </a>
        </div>
    </div>

    <!-- USERS ENTERPRISE LINEAR DATA TABLE -->
    <div class="border border-[#cfc6ac]/70 rounded-md bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-body-main">
                <thead class="bg-[#eee8db] border-b border-[#cfc6ac] text-[12px] font-semibold uppercase tracking-wider text-[#4d4633]">
                    <tr>
                        <th class="py-3 px-4">Profil Pegawai</th>
                        <th class="py-3 px-4">Jabatan / Role</th>
                        <th class="py-3 px-4">Status Akses</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e8e2d6] text-xs font-semibold text-[#1e1c14]">
                    @foreach($users as $user)
                    <tr class="hover:bg-[#f7f6f2] transition-colors border-b border-[#e8e2d6]">
                        <td class="py-3 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold text-xs shadow-sm uppercase">
                                    {{ strtoupper(substr($user->full_name ?: $user->username, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-[#1e1c14] text-xs">{{ $user->full_name ?: $user->username }}</p>
                                    <p class="text-[11px] text-[#4d4633] font-normal"><span class="font-mono">@ {{ $user->username }}</span> • {{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 bg-[#f7f6f2] text-[#705d00] rounded-md text-[11px] font-bold border border-[#cfc6ac]/60">
                                {{ strtoupper($user->role) }}
                            </span>
                        </td>
                        
                        <td class="py-3 px-4">
                            @if($user->status)
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-md text-[10px] font-semibold border border-emerald-200/80 inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Aktif</span>
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-md text-[10px] font-semibold border border-rose-200/80 inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    <span>Non-Aktif</span>
                                </span>
                            @endif
                        </td>
                        
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="w-8 h-8 rounded-md bg-white border border-[#cfc6ac] hover:bg-[#333028] hover:text-[#ffe16e] transition-all flex items-center justify-center text-[#1e1c14] shadow-sm"
                                   title="Edit Akun Pegawai">
                                    <span class="material-symbols-outlined text-base">edit</span>
                                </a>
                                
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus permanen akun ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-8 h-8 rounded-md bg-white border border-[#cfc6ac] hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center text-rose-600 shadow-sm"
                                            title="Hapus Akun Permanen">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
        <div class="px-6 py-4 bg-[#f7f6f2] border-t border-[#cfc6ac]">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection