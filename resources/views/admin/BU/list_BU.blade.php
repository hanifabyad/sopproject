@extends('layouts.admin')

@section('title', 'Daftar - ' . ($stats['name'] ?? $namaDivisi))
@section('header_title', 'Daftar SOP Unit Bisnis')

@section('content')
<div class="space-y-6">

    @if(isset($stats))
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.BU.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-white/20 bg-white/10 text-white hover:bg-white/20 hover:text-white text-xs font-semibold transition-colors shadow-sm cursor-pointer">
                <i class="ph ph-arrow-left text-xs"></i>
                <span>Kembali</span>
            </a>
            <span class="text-white/30">|</span>
            <div class="flex items-center gap-2 text-xs text-white/80">
                <a href="{{ route('admin.BU.index') }}" class="hover:text-[#ffe16e] font-medium">Business Unit</a>
                <span>/</span>
                <span class="font-bold text-white">{{ $stats['name'] }}</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold tracking-tight">Unit Operasional - {{ $stats['name'] }}</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">Daftar dokumen operasional dan status persetujuan SOP pada unit {{ $stats['name'] }}</p>
        </div>
    </div>

    <!-- STATS COUNTER GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- UNIT INFO -->
        <div class="bg-white p-5 rounded-lg border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider block mb-0.5">Unit Operasional</span>
                <h3 class="text-base font-extrabold text-on-surface uppercase truncate max-w-[150px]">{{ $stats['name'] }}</h3>
            </div>
            <div class="w-9 h-9 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm">
                <span class="material-symbols-outlined text-lg">store</span>
            </div>
        </div>

        <!-- APPROVED COUNTER -->
        <div class="bg-white p-5 rounded-lg border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider block mb-0.5">SOP Approved</span>
                <h3 class="text-2xl font-extrabold text-emerald-700">{{ $stats['approved'] }}</h3>
            </div>
            <div class="w-9 h-9 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/80 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-lg">verified</span>
            </div>
        </div>

        <!-- WAITING COUNTER -->
        <div class="bg-white p-5 rounded-lg border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider block mb-0.5">SOP Waiting</span>
                <h3 class="text-2xl font-extrabold text-amber-700">{{ $stats['waiting'] }}</h3>
            </div>
            <div class="w-9 h-9 rounded-md bg-amber-50 text-amber-700 border border-amber-200/80 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-lg">hourglass_top</span>
            </div>
        </div>

        <!-- REVISI COUNTER -->
        <div class="bg-white p-5 rounded-lg border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider block mb-0.5">SOP Revisi</span>
                <h3 class="text-2xl font-extrabold text-red-700">{{ $stats['revisi'] }}</h3>
            </div>
            <div class="w-9 h-9 rounded-md bg-red-50 text-red-700 border border-red-200/80 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-lg">warning</span>
            </div>
        </div>
    </div>

    <!-- DOCUMENT TABLE CONTAINER -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-sand-200/40 pb-4">
            <div class="flex items-center space-x-2 text-sm font-bold text-on-surface">
                <span class="material-symbols-outlined text-gold-500 text-lg">folder_open</span>
                <span class="uppercase">Daftar Dokumen SOP - {{ $stats['name'] }}</span>
            </div>
            <a href="{{ route('admin.BU.create', $stats['name']) }}" 
               class="px-4 py-2 bg-charcoal-900 text-gold-fixed hover:bg-black rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center gap-1.5 self-start sm:self-auto">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Tambah SOP Baru</span>
            </a>
        </div>

        <div class="border border-sand-200/70 rounded-md bg-white overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-body-main">
                    <thead class="bg-sand-50 border-b border-sand-200 text-[12px] font-semibold uppercase tracking-wider text-on-surface-variant">
                        <tr>
                            <th class="py-3 px-4 text-center w-12">#</th>
                            <th class="py-3 px-4">Judul Dokumen SOP</th>
                            <th class="py-3 px-4 text-center">Pembaruan Terakhir</th>
                            <th class="py-3 px-4 text-center">Status Approval</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e8e2d6]">
                        @forelse($documents as $doc)
                        <tr class="border-b border-[#e8e2d6] hover:bg-canvas transition-colors">
                            <td class="py-3.5 px-4 text-center text-xs font-bold text-on-surface-variant">
                                {{ $loop->iteration }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm">
                                        <i class="ph ph-file-text text-base"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.BU.detail', $doc->id) }}" class="font-semibold text-xs text-on-surface hover:text-gold-500 transition uppercase block leading-tight">
                                            {{ $doc->title }}
                                        </a>
                                        <span class="text-[10px] text-on-surface-variant font-normal uppercase">Nomor: {{ $doc->doc_number ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center text-xs font-semibold text-on-surface-variant">
                                {{ $doc->updated_at->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($doc->status === 'waiting')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 border border-amber-200 text-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Waiting Review</span>
                                    </span>
                                @elseif($doc->status === 'need_revision')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 border border-red-200 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        <span>Need Revision</span>
                                    </span>
                                @elseif($doc->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 border border-emerald-200 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Approved</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-700">
                                        {{ $doc->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('admin.BU.detail', $doc->id) }}" class="px-4 py-1.5 rounded-md bg-charcoal-900 hover:bg-black text-gold-fixed text-xs font-medium inline-flex items-center gap-1.5 transition-colors shadow-sm cursor-pointer">
                                    <span>Buka Dokumen</span>
                                    <i class="ph ph-arrow-right text-sm"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-4xl text-[#d6cebf] block mb-1">inbox</span>
                                <p class="text-xs font-bold uppercase tracking-wider">Belum ada dokumen yang diunggah.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @else
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.BU.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-sand-200 bg-white text-on-surface-variant hover:bg-canvas hover:text-on-surface text-xs font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <span class="text-sand-200">|</span>
            <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                <a href="{{ route('admin.BU.index') }}" class="hover:text-gold-500">Business Unit</a>
                <span>/</span>
                <span class="font-medium text-on-surface">Divisi {{ $namaDivisi }}</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold text-on-surface uppercase tracking-tight">Divisi {{ $namaDivisi }}</h2>
            <p class="text-xs text-on-surface-variant mt-0.5">Daftar unit bisnis operasional di bawah naungan Divisi {{ $namaDivisi }}</p>
        </div>
    </div>

    <!-- UNITS ENTERPRISE LINEAR DATA TABLE -->
    <div class="border border-sand-200/70 rounded-md bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-body-main">
                <thead class="bg-sand-50 border-b border-sand-200 text-[12px] font-semibold uppercase tracking-wider text-on-surface-variant">
                    <tr>
                        <th class="py-3 px-4 text-center w-12">#</th>
                        <th class="py-3 px-4">Unit Bisnis / Entitas</th>
                        <th class="py-3 px-4 text-center">Total SOP</th>
                        <th class="py-3 px-4 text-center">Dokumen Aktif</th>
                        <th class="py-3 px-4 text-center">Perlu Revisi / Menunggu</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e8e2d6]">
                    @foreach($statsBU as $bu => $data)
                    <tr class="border-b border-[#e8e2d6] hover:bg-canvas transition-colors">
                        <td class="py-3.5 px-4 text-center text-xs font-bold text-on-surface-variant">
                            {{ $loop->iteration }}
                        </td>
                        <td class="py-3.5 px-4">
                            <a href="{{ route('admin.BU.show', $bu) }}" class="flex items-center space-x-3 group hover:opacity-85 transition-opacity cursor-pointer">
                                <div class="w-8 h-8 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                                    <span class="material-symbols-outlined text-base">store</span>
                                </div>
                                <div>
                                    <span class="font-semibold text-on-surface text-[14px] uppercase block leading-tight group-hover:text-[#1677B8] hover:underline transition-colors">{{ $bu }}</span>
                                    <span class="text-[10px] text-on-surface-variant font-normal uppercase">Unit Operasional</span>
                                </div>
                            </a>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 bg-canvas border border-sand-200/60 font-bold text-on-surface text-xs rounded-md inline-block">
                                {{ $data['total'] }} Dokumen
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 rounded-md text-xs font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                <span>{{ $data['active'] }} Dokumen</span>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1.5 text-rose-700 bg-rose-50 border border-rose-200/80 px-2.5 py-1 rounded-md text-xs font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                <span>{{ $data['inactive'] }} Dokumen</span>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('admin.BU.show', $bu) }}" class="px-4 py-1.5 rounded-md bg-charcoal-900 hover:bg-black text-gold-fixed text-xs font-medium inline-flex items-center gap-1.5 transition-colors shadow-sm cursor-pointer">
                                <span>Buka Dokumen</span>
                                <i class="ph ph-arrow-right text-sm"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
