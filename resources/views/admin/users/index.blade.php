@extends('layouts.admin')

@section('title', 'Kelola Akun Pegawai')
@section('header_title', 'Manajemen Akun & Role Pegawai')

@section('content')
<div class="space-y-6">
    
    <!-- TOP HEADER BAR & SEARCH -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 border border-white/10 shadow-sm">
        <div>
            <h2 class="text-xl font-extrabold tracking-tight">Kelola Akun Pegawai</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">Daftar pengguna terdaftar dan manajemen wewenang peran e-QMS</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ route('admin.users.index') }}" method="GET" class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." 
                       class="pl-9 pr-3 py-2 bg-white border border-sand-200 rounded-md shadow-sm text-xs font-semibold text-on-surface focus:ring-2 focus:ring-gold-500 outline-none w-64 transition-all placeholder-[#d6cebf]">
                <i class="ph ph-magnifying-glass absolute left-2.5 top-2.5 text-sm text-gold-dim"></i>
            </form>
            
            <a href="{{ route('admin.users.create') }}" 
               class="px-4 py-2 bg-[#ffe16e] hover:bg-amber-400 text-charcoal-900 rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center gap-2 border-none">
                <i class="ph ph-user-plus text-base"></i>
                <span>Tambah Pegawai</span>
            </a>
        </div>
    </div>

    <!-- USERS ENTERPRISE LINEAR DATA TABLE -->
    <div class="border border-sand-200/70 rounded-md bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-body-main">
                <thead class="bg-sand-50 border-b border-sand-200 text-[12px] font-semibold uppercase tracking-wider text-on-surface-variant">
                    <tr>
                        <th class="py-3 px-4">Profil Pegawai</th>
                        <th class="py-3 px-4">Jabatan / Role</th>
                        <th class="py-3 px-4">Status Akses</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e8e2d6] text-xs font-semibold text-on-surface">
                    @foreach($users as $user)
                    <tr class="hover:bg-canvas transition-colors border-b border-[#e8e2d6]">
                        <td class="py-3 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold text-xs shadow-sm uppercase">
                                    {{ strtoupper(substr($user->full_name ?: $user->username, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="font-bold text-on-surface text-xs hover:underline hover:text-gold-500 transition-colors cursor-pointer"
                                       title="Edit pegawai: {{ $user->full_name ?: $user->username }}">{{ $user->full_name ?: $user->username }}</a>
                                    <p class="text-[11px] text-on-surface-variant font-normal"><span class="font-mono">@ {{ $user->username }}</span> • {{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 bg-canvas text-gold-500 rounded-md text-[11px] font-bold border border-sand-200/60">
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
                                   class="w-8 h-8 rounded-md bg-white border border-sand-200 hover:bg-charcoal-900 hover:text-gold-fixed transition-all flex items-center justify-center text-on-surface shadow-sm"
                                   title="Edit Akun Pegawai">
                                    <span class="material-symbols-outlined text-base">edit</span>
                                </a>
                                
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus permanen akun ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-8 h-8 rounded-md bg-white border border-sand-200 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center text-rose-600 shadow-sm"
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
        <div class="px-6 py-4 bg-canvas border-t border-sand-200">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection