@extends('layouts.admin')

@section('title', 'Daftar - ' . ($stats['name'] ?? $namaDivisi))

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .eqms-scope {
        font-family: 'Poppins', sans-serif;
    }
</style>

<div class="p-8 eqms-scope">

    {{-- ================================================================ --}}
    {{-- 📂 BAGIAN 1: DAFTAR DOKUMEN (Muncul saat Unit diklik, misal: SPBU) --}}
    {{-- ================================================================ --}}
    @if(isset($stats))
    {{-- Ringkasan Statistik Atas (Lebih Padat & Berkelas) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- INFO UNIT BISNIS --}}
        <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100 flex items-center justify-between">
            <div>
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Unit Operasional</span>
                <h3 class="text-base font-black text-[#1e293b] uppercase truncate max-w-[150px]">{{ $stats['name'] }}</h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-[#1e293b] text-base shadow-inner">
                <i class="fa-solid fa-charging-station"></i>
            </div>
        </div>

        {{-- APPROVED COUNTER --}}
        <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100 relative overflow-hidden group flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">SOP Approved</p>
                <h3 class="text-3xl font-black text-[#1e293b]">{{ $stats['approved'] }}</h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-base shadow-inner">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-green-400"></div>
        </div>

        {{-- WAITING COUNTER --}}
        <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100 relative overflow-hidden group flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">SOP Waiting</p>
                <h3 class="text-3xl font-black text-[#1e293b]">{{ $stats['waiting'] }}</h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 text-base shadow-inner">
                <i class="fa-solid fa-spinner"></i>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-yellow-400"></div>
        </div>

        {{-- REVISI COUNTER --}}
        <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100 relative overflow-hidden group flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">SOP Revisi</p>
                <h3 class="text-3xl font-black text-[#1e293b]">{{ $stats['revisi'] }}</h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center text-red-500 text-base shadow-inner">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-red-500"></div>
        </div>
    </div>

    {{-- List Tabel Dokumen Utama --}}
    <div class="bg-white rounded-2xl shadow-md p-8 border border-gray-100 min-h-[400px]">
        <div class="flex justify-between items-center border-b border-gray-100 pb-5 mb-6">
            <h4 class="text-sm font-extrabold text-[#1e293b] uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-folder-tree text-blue-500"></i> Daftar Dokumen SOP
            </h4>
            <a href="{{ route('admin.BU.create', $stats['name']) }}" 
               class="bg-[#1e293b] text-white px-4 py-2 rounded-xl text-[10px] font-bold uppercase tracking-wider hover:bg-blue-600 transition-all duration-300 shadow-md flex items-center gap-1.5 transform hover:-translate-y-0.5">
                <i class="fa-solid fa-plus text-xs"></i> Tambah SOP Baru
            </a>
        </div>

        <div class="space-y-4">
            @forelse($documents as $doc)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-white hover:shadow-md transition-all duration-300 border border-transparent hover:border-gray-100 group">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-white text-[#1e293b] rounded-xl flex items-center justify-center text-base group-hover:bg-blue-600 group-hover:text-white transition-all duration-300 shadow-sm">
                        <i class="fa-regular fa-file-lines"></i>
                    </div>
                    <div>
                        <a href="{{ route('admin.BU.detail', $doc->id) }}" class="font-bold text-gray-700 uppercase hover:text-blue-600 transition text-xs block tracking-tight">
                            {{ $doc->title }}
                        </a>
                        <p class="text-[9px] text-gray-400 font-bold uppercase mt-1 tracking-wide">
                            <i class="fa-regular fa-clock mr-0.5"></i> Pembaruan terakhir: {{ $doc->updated_at->format('d M Y') }}
                        </p>
                    </div>
                </div>

                {{-- Soft Semantic Badges --}}
                <div>
                    @if($doc->status === 'waiting')
                        <span class="px-3 py-1.5 rounded-xl text-[8px] font-bold uppercase tracking-wider bg-amber-50 border border-amber-100 text-amber-600 shadow-sm">
                            <i class="fa-solid fa-spinner mr-1"></i> Waiting Review
                        </span>
                    @elseif($doc->status === 'need_revision')
                        <span class="px-3 py-1.5 rounded-xl text-[8px] font-bold uppercase tracking-wider bg-red-50 border border-red-100 text-red-500 shadow-sm">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i> Need Revision
                        </span>
                    @elseif($doc->status === 'active')
                        <span class="px-3 py-1.5 rounded-xl text-[8px] font-bold uppercase tracking-wider bg-emerald-50 border border-emerald-100 text-emerald-600 shadow-sm">
                            <i class="fa-solid fa-circle-check mr-1"></i> Approved
                        </span>
                    @else
                        <span class="px-3 py-1.5 rounded-xl text-[8px] font-bold uppercase tracking-wider bg-gray-50 border border-gray-200 text-gray-500 shadow-sm">
                            {{ $doc->status }}
                        </span>
                    @endif
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-16 opacity-30 text-center">
                <div class="text-4xl mb-2">📥</div>
                <p class="font-bold uppercase tracking-wider text-xs">Belum ada dokumen yang diunggah.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 🏢 BAGIAN 2: TAMPILAN KARTU UNIT (Halaman Divisi, misal: RETAIL) --}}
    {{-- ================================================================ --}}
    @else
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div class="flex items-center space-x-3">
            <div class="bg-[#1e293b] p-2.5 rounded-xl text-white shadow-md">
                <i class="fa-solid fa-network-wired text-lg"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-[#1e293b] uppercase tracking-tight">DIVISI {{ $namaDivisi }}</h2>
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Monitoring Dokumen Berdasarkan Kelompok Unit Bisnis</p>
            </div>
        </div>

        <a href="{{ route('admin.BU.index') }}" 
           class="bg-white border border-gray-200 text-gray-700 px-5 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-wider hover:bg-gray-50 transition-all duration-300 shadow-sm flex items-center gap-2 transform hover:-translate-y-0.5">
            <i class="fa-solid fa-arrow-left-long text-xs text-blue-500"></i> Kembali Ke Divisi
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($statsBU as $bu => $data)
        <a href="{{ route('admin.BU.show', $bu) }}" 
           class="group relative bg-[#1e293b] rounded-2xl p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:shadow-xl overflow-hidden border border-transparent hover:border-gray-100 flex flex-col justify-between min-h-[170px]">
            
            <div class="absolute -right-10 -top-10 bg-white/5 w-32 h-32 rounded-full group-hover:bg-[#1e293b]/5 transition-all duration-300"></div>

            <div class="relative z-10 w-full">
                <div class="flex items-start justify-between mb-4">
                    <div class="bg-white/10 p-3.5 rounded-xl text-white group-hover:bg-[#1e293b]/5 group-hover:text-[#1e293b] transition-colors duration-300 text-lg flex items-center justify-center w-11 h-11">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>

                    <div class="text-right">
                        <span class="text-4xl font-black text-white group-hover:text-[#1e293b] transition-all duration-300 block leading-none">
                            {{ $data['total'] }}
                        </span>
                        <span class="text-[8px] font-bold text-white/40 group-hover:text-[#1e293b]/40 uppercase tracking-wider block mt-1">Total SOP</span>
                    </div>
                </div>

                <div class="mb-4">
                    <h3 class="text-lg font-extrabold text-white group-hover:text-[#1e293b] uppercase tracking-tight transition-colors duration-300 leading-tight">
                        {{ $bu }}
                    </h3>
                </div>

                <div class="flex items-center space-x-5 border-t border-white/10 group-hover:border-gray-100 mt-4 pt-4 transition-colors duration-300">
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-green-400 block shadow-sm shadow-green-200"></span>
                        <p class="text-[10px] font-bold text-white/60 group-hover:text-gray-500 uppercase tracking-wide">
                            Aktif: <span class="font-black text-white group-hover:text-green-600 ml-0.5">{{ $data['active'] }}</span>
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-red-400 block shadow-sm shadow-red-200"></span>
                        <p class="text-[10px] font-bold text-white/60 group-hover:text-gray-500 uppercase tracking-wide">
                            Revisi: <span class="font-black text-white group-hover:text-red-500 ml-0.5">{{ $data['inactive'] }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection