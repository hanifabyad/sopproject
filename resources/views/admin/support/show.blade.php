@extends('layouts.admin')

@section('title', 'Detail Departemen Support - ' . $stats['name'])

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .eqms-scope {
        font-family: 'Poppins', sans-serif;
    }
</style>

<div class="p-8 eqms-scope">

    {{-- Ringkasan Statistik Atas (4 Kartu Presisi Selaras dengan BU) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- INFO DEPARTEMEN SUPPORT --}}
        <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100 flex items-center justify-between">
            <div>
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Departemen Support</span>
                <h3 class="text-base font-black text-[#1e293b] uppercase truncate max-w-[150px]">{{ $stats['name'] }}</h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-[#1e293b] text-base shadow-inner">
                <i class="fa-solid fa-briefcase"></i>
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
                <i class="fa-solid fa-folder-open text-blue-500"></i> Daftar Dokumen SOP
            </h4>
            <a href="{{ route('admin.support.create', $stats['name']) }}" 
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
                        <a href="{{ route('admin.support.document.detail', $doc->id) }}" class="font-bold text-gray-700 uppercase hover:text-blue-600 transition text-xs block tracking-tight">
                            {{ $doc->title }}
                        </a>
                        <p class="text-[9px] text-gray-400 font-bold uppercase mt-1 tracking-wide">
                            <i class="fa-regular fa-clock mr-0.5"></i> Pembaruan terakhir: {{ $doc->updated_at->format('d M Y') }}
                        </p>
                    </div>
                </div>

                {{-- Soft Semantic Badges Presisi --}}
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
</div>
@endsection