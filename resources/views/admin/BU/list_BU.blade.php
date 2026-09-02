@extends('layouts.admin')

@section('title', 'Daftar - ' . ($stats['name'] ?? $namaDivisi))
@section('header_title', 'Daftar SOP Unit Bisnis')

@section('content')
<div class="space-y-6 bu-support-scope">

    @if(isset($stats))
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.BU.index') }}" variant="light" />
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
                <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block mb-0.5">Unit Operasional</span>
                <h3 class="text-base font-extrabold text-on-surface capitalize truncate max-w-[150px]">{{ $stats['name'] }}</h3>
            </div>
            <span class="material-symbols-outlined text-3xl text-[#00b4d8]">storefront</span>
        </div>

        <!-- APPROVED COUNTER -->
        <div class="bg-white p-5 rounded-lg border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block mb-0.5">SOP Approved</span>
                <h3 class="text-2xl font-extrabold text-emerald-700">{{ $stats['approved'] }}</h3>
            </div>
            <div class="w-9 h-9 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/80 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-lg">verified</span>
            </div>
        </div>

        <!-- WAITING COUNTER -->
        <div class="bg-white p-5 rounded-lg border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block mb-0.5">SOP Waiting</span>
                <h3 class="text-2xl font-extrabold text-amber-700">{{ $stats['waiting'] }}</h3>
            </div>
            <div class="w-9 h-9 rounded-md bg-amber-50 text-amber-700 border border-amber-200/80 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-lg">hourglass_top</span>
            </div>
        </div>

        <!-- REVISI COUNTER -->
        <div class="bg-white p-5 rounded-lg border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block mb-0.5">SOP Revisi</span>
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
                <span class="material-symbols-outlined text-[#00b4d8] text-lg">folder_open</span>
                <span class="capitalize">Daftar Dokumen SOP - {{ $stats['name'] }}</span>
            </div>
            <x-interactive-button href="{{ route('admin.BU.create', $stats['name']) }}" text="Tambah SOP Baru" class="self-start sm:self-auto" />
        </div>

        <div class="border border-sand-200/70 rounded-md bg-white overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-body-main">
                    <thead class="bg-sand-50 border-b border-sand-200 text-[12px] font-semibold capitalize tracking-wider text-on-surface-variant">
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
                                    <i class="ph ph-file-text text-2xl text-[#00b4d8] flex-shrink-0"></i>
                                    <div>
                                        <a href="{{ route('admin.BU.detail', $doc->id) }}" class="font-semibold text-xs text-on-surface hover:text-[#1677B8] transition capitalize block leading-tight">
                                            {{ $doc->title }}
                                        </a>
                                        <span class="text-[10px] text-on-surface-variant font-normal capitalize">Nomor: {{ $doc->doc_number ?? '-' }}</span>
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
                                <x-interactive-button href="{{ route('admin.BU.detail', $doc->id) }}" text="Buka Dokumen" variant="outline" />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-4xl text-[#d6cebf] block mb-1">inbox</span>
                                <p class="text-xs font-bold capitalize tracking-wider">Belum ada dokumen yang diunggah.</p>
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
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.BU.index') }}" variant="light" />
            <span class="text-white/30">|</span>
            <div class="flex items-center gap-2 text-xs text-white/80">
                <a href="{{ route('admin.BU.index') }}" class="hover:text-[#ffe16e] font-medium">Business Unit</a>
                <span>/</span>
                <span class="font-bold text-white">Divisi {{ $namaDivisi }}</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold text-white tracking-tight">Divisi {{ $namaDivisi }}</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">Daftar unit bisnis operasional di bawah naungan Divisi {{ $namaDivisi }}</p>
        </div>
    </div>

    <!-- UNITS ENTERPRISE LINEAR DATA TABLE -->
    <div class="border border-sand-200/70 rounded-md bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-body-main">
                <thead class="bg-sand-50 border-b border-sand-200 text-[12px] font-semibold capitalize tracking-wider text-on-surface-variant">
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
                                <i class="ph ph-storefront text-2xl text-[#00b4d8] flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                                <div>
                                    <span class="font-semibold text-on-surface text-[14px] capitalize block leading-tight group-hover:text-[#1677B8] hover:underline transition-colors">{{ $bu }}</span>
                                    <span class="text-[10px] text-on-surface-variant font-normal capitalize">Unit Operasional</span>
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
                            <x-interactive-button href="{{ route('admin.BU.show', $bu) }}" text="Buka Dokumen" variant="outline" />
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
