@extends('layouts.admin')

@section('title', 'Kelola Akun Pegawai')
@section('header_title', 'Manajemen Akun & Role Pegawai')

@section('content')
<div class="space-y-6">
    
    <!-- TOP HEADER BAR & SEARCH -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 border border-white/10 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#ffe16e] font-bold flex items-center gap-1">
                    <i class="ph ph-squares-four text-base"></i>
                    <span>Dashboard</span>
                </a>
                <span>/</span>
                <span class="text-[#ffe16e] font-bold capitalize">Kelola Akun Pegawai</span>
            </div>
            <h2 class="text-xl font-extrabold tracking-tight">Kelola Akun Pegawai</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">Daftar pengguna terdaftar dan manajemen wewenang peran e-QMS</p>
        </div>

        <div class="flex items-center gap-2.5">
            <!-- Search Form dengan Tombol Aksi Kaca Pembesar & Height Selaras -->
            <form action="{{ route('admin.users.index') }}" method="GET" class="relative flex items-center">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama, email, atau username..." 
                       class="h-[38px] pl-9 pr-8 bg-white border border-blue-300 focus:border-[#1677B8] focus:ring-1 focus:ring-[#1677B8] rounded-[2px] shadow-xs text-xs font-bold text-slate-900 outline-none w-64 sm:w-72 transition-all placeholder:text-slate-400 placeholder:font-normal">
                
                <!-- Tombol Cari (Klik Icon Kaca Pembesar) -->
                <button type="submit" class="absolute left-1 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center text-slate-400 hover:text-[#1677B8] transition-colors cursor-pointer border-none bg-transparent" title="Cari Pegawai">
                    <i class="ph ph-magnifying-glass text-base"></i>
                </button>

                @if(request('search'))
                    <a href="{{ route('admin.users.index') }}" class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center text-slate-400 hover:text-rose-600 transition-colors" title="Reset pencarian">
                        <i class="ph ph-x text-xs"></i>
                    </a>
                @endif
            </form>
            
            <!-- Tombol Tambah Pegawai dengan Height Selaras 38px -->
            <x-interactive-button text="Tambah Pegawai" variant="primary" icon="ph ph-user-plus text-base" href="{{ route('admin.users.create') }}" />
        </div>
    </div>

    <!-- USERS ENTERPRISE DATA TABLE (TRACKING STYLE) -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
        <!-- HEADER BIRU MUDA -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3 rounded-[2px]">
            <div class="flex items-center space-x-2 text-xs font-bold text-[#1677B8]">
                <i class="ph ph-users text-base"></i>
                <span class="capitalize text-slate-900 font-extrabold">Daftar Akun Pengguna Terdaftar</span>
            </div>
            <span class="text-[11px] text-[#1677B8] font-bold bg-white px-2.5 py-1.5 rounded-[2px] border border-blue-200 whitespace-nowrap shadow-2xs">
                Total {{ $users->total() }} Akun Pegawai
            </span>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-[2px]">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#1677B8] border-b border-[#1258a0] text-[10.5px] font-bold uppercase tracking-wider text-white">
                    <tr>
                        <th class="py-2.5 px-3 w-10 text-center">No</th>
                        <th class="py-2.5 px-4">Profil & Identitas Pegawai</th>
                        <th class="py-2.5 px-4">Jabatan / Role</th>
                        <th class="py-2.5 px-4 text-center">Status Akses</th>
                        <th class="py-2.5 px-4 text-right whitespace-nowrap w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs font-semibold text-slate-800">
                    @forelse($users as $index => $user)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-2.5 px-3 text-center font-bold text-slate-400 text-[11px] align-middle">
                            {{ $users->firstItem() + $index }}
                        </td>
                        <td class="py-2.5 px-4 align-middle">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-[2px] bg-[#1677B8] text-white flex items-center justify-center font-bold text-xs shadow-xs capitalize flex-shrink-0">
                                    {{ strtoupper(substr($user->full_name ?: $user->username, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="font-bold text-slate-900 text-xs hover:text-[#1677B8] transition-colors cursor-pointer truncate block"
                                       title="Edit pegawai: {{ $user->full_name ?: $user->username }}">{{ $user->full_name ?: $user->username }}</a>
                                    <div class="flex items-center gap-1.5 text-[10px] text-slate-500 mt-0.5">
                                        <span class="font-mono font-semibold text-slate-600">@ {{ $user->username }}</span>
                                        <span class="text-slate-300">•</span>
                                        <span class="truncate">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="py-2.5 px-4 align-middle">
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-800 rounded-[2px] text-[10.5px] font-bold border border-slate-200 inline-block">
                                {{ strtoupper($user->role) }}
                            </span>
                        </td>
                        
                        <td class="py-2.5 px-4 text-center align-middle">
                            @if($user->status)
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-[2px] text-[10px] font-bold border border-emerald-300 inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    <span>Aktif</span>
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-[2px] text-[10px] font-bold border border-rose-300 inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                    <span>Non-Aktif</span>
                                </span>
                            @endif
                        </td>
                        
                        <td class="py-2.5 px-4 text-right align-middle whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-1.5">
                                <x-interactive-button text="Edit" variant="outline" icon="ph ph-pencil-simple text-xs" href="{{ route('admin.users.edit', $user) }}" title="Edit Akun Pegawai" />
                                
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus permanen akun ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-interactive-button text="Hapus" variant="danger" icon="ph ph-trash text-xs" type="submit" title="Hapus Akun Permanen" />
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <i class="ph ph-users text-4xl text-slate-300"></i>
                                <h5 class="text-xs font-bold text-slate-700">Tidak ada data pegawai</h5>
                                <p class="text-[11px] text-slate-500 max-w-sm">
                                    Tidak ada data pengguna yang cocok dengan kriteria pencarian Anda.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
        <div class="pt-3 border-t border-slate-200">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

