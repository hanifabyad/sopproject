@extends('layouts.admin')

@section('title', 'Detail Departemen Support - ' . $stats['name'])
@section('header_title', 'Detail Dokumen Departemen Support')

@section('content')
<div class="space-y-6 bu-support-scope">

    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.support.index') }}" variant="light" />
            <span class="text-white/30">|</span>
            <div class="flex items-center gap-2 text-xs text-white/80">
                <a href="{{ route('admin.support.index') }}" class="hover:text-[#ffe16e] font-medium">Departemen Support</a>
                <span>/</span>
                <span class="font-bold text-white">{{ $stats['name'] }}</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold tracking-tight">Departemen Support - {{ $stats['name'] }}</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">Daftar dokumen operasional dan status persetujuan SOP pada departemen {{ $stats['name'] }}</p>
        </div>
    </div>

    <!-- STATS BENTO GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- SUPPORT DEPT INFO -->
        <div class="bg-white p-6 rounded-md border border-[#e5dfd3] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block mb-1">Departemen Support</span>
                <h3 class="text-base font-extrabold text-on-surface capitalize truncate max-w-[150px]">{{ $stats['name'] }}</h3>
            </div>
            <i class="ph ph-folder-open text-3xl text-[#00b4d8]"></i>
        </div>

        <!-- APPROVED COUNTER -->
        <div class="bg-white p-6 rounded-md border border-[#e5dfd3] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block mb-1">SOP Approved</span>
                <h3 class="text-3xl font-extrabold text-emerald-700">{{ $stats['approved'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-md bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-xl">verified</span>
            </div>
        </div>

        <!-- WAITING COUNTER -->
        <div class="bg-white p-6 rounded-md border border-[#e5dfd3] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block mb-1">SOP Waiting</span>
                <h3 class="text-3xl font-extrabold text-amber-700">{{ $stats['waiting'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-md bg-amber-50 text-amber-700 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-xl">hourglass_top</span>
            </div>
        </div>

        <!-- REVISI COUNTER -->
        <div class="bg-white p-6 rounded-md border border-[#e5dfd3] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block mb-1">SOP Revisi</span>
                <h3 class="text-3xl font-extrabold text-red-700">{{ $stats['revisi'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-md bg-red-50 text-red-700 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-xl">warning</span>
            </div>
        </div>
    </div>

    <!-- DOCUMENT TABLE BENTO CARD -->
    <div class="bg-white rounded-md p-6 md:p-8 shadow-sm border border-[#e5dfd3] space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#e5dfd3] pb-4">
            <div class="flex items-center space-x-2 text-sm font-bold text-on-surface">
                <span class="material-symbols-outlined text-[#00b4d8] text-lg">folder_open</span>
                <span>Daftar Dokumen SOP</span>
            </div>
            <x-interactive-button href="{{ route('admin.support.create', $stats['name']) }}" text="Tambah SOP Baru" class="self-start sm:self-auto" />
        </div>

        <div class="space-y-3">
            @forelse($documents as $doc)
            <div class="p-4 bg-canvas hover:bg-sky-50/50 rounded-md border border-[#e5dfd3] hover:border-[#00b4d8]/50 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 group">
                <div class="flex items-center space-x-3.5">
                    <i class="ph ph-file-text text-2xl text-[#00b4d8] group-hover:scale-110 group-hover:text-[#1677B8] transition-all flex-shrink-0"></i>
                    <div>
                        <a href="{{ route('admin.support.document.detail', $doc->id) }}" class="font-bold text-xs text-on-surface hover:text-gold-500 transition capitalize">
                            {{ $doc->title }}
                        </a>
                        <p class="text-[10px] text-on-surface-variant font-semibold mt-0.5">
                            Pembaruan terakhir: {{ $doc->updated_at->format('d M Y') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 self-start sm:self-auto">
                    <div>
                        @if($doc->status === 'waiting')
                            <span class="px-3 py-1.5 rounded text-[10px] font-bold bg-amber-50 border border-amber-200 text-amber-700 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>Waiting Review</span>
                            </span>
                        @elseif($doc->status === 'need_revision')
                            <span class="px-3 py-1.5 rounded text-[10px] font-bold bg-red-50 border border-red-200 text-red-700 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                <span>Need Revision</span>
                            </span>
                        @elseif($doc->status === 'active')
                            <span class="px-3 py-1.5 rounded text-[10px] font-bold bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Approved</span>
                            </span>
                        @else
                            <span class="px-3 py-1.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700">
                                {{ $doc->status }}
                            </span>
                        @endif
                    </div>
                    <x-interactive-button href="{{ route('admin.support.document.detail', $doc->id) }}" text="Buka Dokumen" variant="outline" />
                </div>
            </div>
            @empty
            <div class="py-16 text-center text-on-surface-variant space-y-2">
                <span class="material-symbols-outlined text-4xl text-[#d6cebf]">inbox</span>
                <p class="text-xs font-bold capitalize tracking-wider">Belum ada dokumen yang diunggah.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
