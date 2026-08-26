@extends('layouts.admin')

@section('title', 'Detail Departemen Support - ' . $stats['name'])
@section('header_title', 'Detail Dokumen Departemen Support')

@section('content')
<div class="space-y-6">

    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.support.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-sand-200 bg-white text-on-surface-variant hover:bg-canvas hover:text-on-surface text-xs font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <span class="text-sand-200">|</span>
            <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                <a href="{{ route('admin.support.index') }}" class="hover:text-gold-500">Departemen Support</a>
                <span>/</span>
                <span class="font-medium text-on-surface">{{ $stats['name'] }}</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold text-on-surface uppercase tracking-tight">Departemen Support - {{ $stats['name'] }}</h2>
            <p class="text-xs text-on-surface-variant mt-0.5">Daftar dokumen operasional dan status persetujuan SOP pada departemen {{ $stats['name'] }}</p>
        </div>
    </div>

    <!-- STATS BENTO GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- SUPPORT DEPT INFO -->
        <div class="bg-white p-6 rounded-md border border-[#e5dfd3] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider block mb-1">Departemen Support</span>
                <h3 class="text-base font-extrabold text-on-surface uppercase truncate max-w-[150px]">{{ $stats['name'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm">
                <span class="material-symbols-outlined text-xl">folder_managed</span>
            </div>
        </div>

        <!-- APPROVED COUNTER -->
        <div class="bg-white p-6 rounded-md border border-[#e5dfd3] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider block mb-1">SOP Approved</span>
                <h3 class="text-3xl font-extrabold text-emerald-700">{{ $stats['approved'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-md bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-xl">verified</span>
            </div>
        </div>

        <!-- WAITING COUNTER -->
        <div class="bg-white p-6 rounded-md border border-[#e5dfd3] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider block mb-1">SOP Waiting</span>
                <h3 class="text-3xl font-extrabold text-amber-700">{{ $stats['waiting'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-md bg-amber-50 text-amber-700 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-xl">hourglass_top</span>
            </div>
        </div>

        <!-- REVISI COUNTER -->
        <div class="bg-white p-6 rounded-md border border-[#e5dfd3] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider block mb-1">SOP Revisi</span>
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
                <span class="material-symbols-outlined text-gold-500 text-lg">folder_open</span>
                <span>Daftar Dokumen SOP</span>
            </div>
            <a href="{{ route('admin.support.create', $stats['name']) }}" 
               class="px-5 py-2.5 bg-charcoal-900 text-gold-fixed hover:bg-black rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center gap-2 self-start sm:self-auto">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Tambah SOP Baru</span>
            </a>
        </div>

        <div class="space-y-3">
            @forelse($documents as $doc)
            <div class="p-4 bg-canvas hover:bg-[#fff9ed] rounded-md border border-[#e5dfd3] transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 group">
                <div class="flex items-center space-x-3.5">
                    <div class="w-8 h-8 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm flex-shrink-0">
                        <i class="ph ph-file-text text-base"></i>
                    </div>
                    <div>
                        <a href="{{ route('admin.support.document.detail', $doc->id) }}" class="font-bold text-xs text-on-surface hover:text-gold-500 transition uppercase">
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
                    <a href="{{ route('admin.support.document.detail', $doc->id) }}" class="px-4 py-1.5 rounded-md bg-charcoal-900 hover:bg-black text-gold-fixed text-xs font-medium inline-flex items-center gap-1.5 transition-colors shadow-sm cursor-pointer">
                        <span>Buka Dokumen</span>
                        <i class="ph ph-arrow-right text-sm"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="py-16 text-center text-on-surface-variant space-y-2">
                <span class="material-symbols-outlined text-4xl text-[#d6cebf]">inbox</span>
                <p class="text-xs font-bold uppercase tracking-wider">Belum ada dokumen yang diunggah.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection