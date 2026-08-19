@extends('layouts.admin')

@section('title', 'Detail Departemen Support - ' . $stats['name'])
@section('header_title', 'Detail Dokumen Departemen Support')

@section('content')
<div class="space-y-6">

    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.support.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-[#cfc6ac] bg-white text-[#4d4633] hover:bg-[#f7f6f2] hover:text-[#1e1c14] text-xs font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <span class="text-[#cfc6ac]">|</span>
            <div class="flex items-center gap-2 text-xs text-[#4d4633]">
                <a href="{{ route('admin.support.index') }}" class="hover:text-[#705d00]">Departemen Support</a>
                <span>/</span>
                <span class="font-medium text-[#1e1c14]">{{ $stats['name'] }}</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold text-[#1e1c14] uppercase tracking-tight">Departemen Support - {{ $stats['name'] }}</h2>
            <p class="text-xs text-[#4d4633] mt-0.5">Daftar dokumen operasional dan status persetujuan SOP pada departemen {{ $stats['name'] }}</p>
        </div>
    </div>

    <!-- STATS BENTO GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- SUPPORT DEPT INFO -->
        <div class="bg-white p-6 rounded-3xl border border-[#e5dfd3] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-[#4d4633] uppercase tracking-wider block mb-1">Departemen Support</span>
                <h3 class="text-base font-extrabold text-[#1e1c14] uppercase truncate max-w-[150px]">{{ $stats['name'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm">
                <span class="material-symbols-outlined text-xl">folder_managed</span>
            </div>
        </div>

        <!-- APPROVED COUNTER -->
        <div class="bg-white p-6 rounded-3xl border border-[#e5dfd3] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-[#4d4633] uppercase tracking-wider block mb-1">SOP Approved</span>
                <h3 class="text-3xl font-extrabold text-emerald-700">{{ $stats['approved'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-xl">verified</span>
            </div>
        </div>

        <!-- WAITING COUNTER -->
        <div class="bg-white p-6 rounded-3xl border border-[#e5dfd3] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-[#4d4633] uppercase tracking-wider block mb-1">SOP Waiting</span>
                <h3 class="text-3xl font-extrabold text-amber-700">{{ $stats['waiting'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-xl">hourglass_top</span>
            </div>
        </div>

        <!-- REVISI COUNTER -->
        <div class="bg-white p-6 rounded-3xl border border-[#e5dfd3] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-[#4d4633] uppercase tracking-wider block mb-1">SOP Revisi</span>
                <h3 class="text-3xl font-extrabold text-red-700">{{ $stats['revisi'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-700 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-xl">warning</span>
            </div>
        </div>
    </div>

    <!-- DOCUMENT TABLE BENTO CARD -->
    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-[#e5dfd3] space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#e5dfd3] pb-4">
            <div class="flex items-center space-x-2 text-sm font-bold text-[#1e1c14]">
                <span class="material-symbols-outlined text-[#705d00] text-lg">folder_open</span>
                <span>Daftar Dokumen SOP</span>
            </div>
            <a href="{{ route('admin.support.create', $stats['name']) }}" 
               class="px-5 py-2.5 bg-[#333028] text-[#ffe16e] hover:bg-black rounded-2xl font-bold text-xs uppercase tracking-wider shadow-md transition-all flex items-center gap-2 self-start sm:self-auto">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Tambah SOP Baru</span>
            </a>
        </div>

        <div class="space-y-3">
            @forelse($documents as $doc)
            <div class="p-4 bg-[#f7f6f2] hover:bg-[#fff9ed] rounded-2xl border border-[#e5dfd3] transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 group">
                <div class="flex items-center space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-white text-[#705d00] flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-xl">description</span>
                    </div>
                    <div>
                        <a href="{{ route('admin.support.document.detail', $doc->id) }}" class="font-bold text-xs text-[#1e1c14] hover:text-[#705d00] transition uppercase">
                            {{ $doc->title }}
                        </a>
                        <p class="text-[10px] text-[#4d4633] font-semibold mt-0.5">
                            Pembaruan terakhir: {{ $doc->updated_at->format('d M Y') }}
                        </p>
                    </div>
                </div>

                <div class="self-start sm:self-auto">
                    @if($doc->status === 'waiting')
                        <span class="px-3 py-1.5 rounded-xl text-[10px] font-bold bg-amber-50 border border-amber-200 text-amber-700 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>Waiting Review</span>
                        </span>
                    @elseif($doc->status === 'need_revision')
                        <span class="px-3 py-1.5 rounded-xl text-[10px] font-bold bg-red-50 border border-red-200 text-red-700 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            <span>Need Revision</span>
                        </span>
                    @elseif($doc->status === 'active')
                        <span class="px-3 py-1.5 rounded-xl text-[10px] font-bold bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Approved</span>
                        </span>
                    @else
                        <span class="px-3 py-1.5 rounded-xl text-[10px] font-bold bg-gray-100 text-gray-700">
                            {{ $doc->status }}
                        </span>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-16 text-center text-[#4d4633] space-y-2">
                <span class="material-symbols-outlined text-4xl text-[#d6cebf]">inbox</span>
                <p class="text-xs font-bold uppercase tracking-wider">Belum ada dokumen yang diunggah.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection