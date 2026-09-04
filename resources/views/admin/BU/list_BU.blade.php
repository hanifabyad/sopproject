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
        <!-- HEADER BIRU MUDA (TRACKING STYLE) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3 rounded-[2px]">
            <div class="flex items-center space-x-2 text-xs font-bold text-[#1677B8]">
                <i class="ph ph-table text-base"></i>
                <span class="capitalize text-slate-900 font-extrabold">Daftar Dokumen SOP - {{ $stats['name'] }}</span>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="text-[11px] text-[#1677B8] font-bold bg-white px-2.5 py-1.5 rounded-[2px] border border-blue-200 whitespace-nowrap shadow-2xs">
                    Total {{ $documents->count() }} Dokumen SOP
                </span>
                <x-interactive-button text="Tambah SOP Baru" variant="blue" icon="ph ph-plus text-sm" href="{{ route('admin.BU.create', $stats['name']) }}" />
            </div>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-[2px]">
            <table class="tracking-table w-full text-left border-collapse">
                <thead class="bg-[#1677B8] border-b border-[#1258a0] text-[10.5px] font-bold uppercase tracking-wider text-white">
                    <tr>
                        <th class="py-2.5 px-2 text-center w-8">No</th>
                        <th class="py-2.5 px-3">Dokumen SOP & Identitas</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap">Pembaruan Terakhir</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap">Status Approval</th>
                        <th class="py-2.5 px-3 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs font-semibold text-slate-800">
                    @forelse($documents as $doc)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-2.5 px-2 text-center font-bold text-slate-400 text-[11px] align-middle">
                            {{ $loop->iteration }}
                        </td>
                        <td class="py-2.5 px-3 align-middle">
                            <div class="font-bold text-slate-900 text-xs leading-snug">
                                <a href="{{ route('admin.BU.detail', $doc->id) }}" class="hover:text-[#1677B8] hover:underline transition-colors cursor-pointer">
                                    {{ $doc->title }}
                                </a>
                            </div>
                            <div class="flex items-center gap-1.5 mt-1 flex-wrap text-[10px]">
                                <span class="font-mono font-semibold text-slate-600">{{ $doc->doc_number ?? 'No. Belum Diatur' }}</span>
                                <span class="text-slate-300">|</span>
                                <span class="font-mono font-bold text-slate-700">Rev {{ $doc->doc_revision ?? '0' }}</span>
                                <span class="text-slate-300">|</span>
                                <span class="font-bold text-[#1677B8]">{{ $stats['name'] }}</span>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap font-mono text-xs text-slate-700">
                            {{ $doc->updated_at->format('d/m/Y') }}
                        </td>
                        <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                            @if($doc->status === 'waiting')
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[2px] text-[10px] font-bold bg-amber-50 border border-amber-300 text-amber-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>Waiting Review</span>
                                </span>
                            @elseif($doc->status === 'need_revision')
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[2px] text-[10px] font-bold bg-rose-50 border border-rose-300 text-rose-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    <span>Need Revision</span>
                                </span>
                            @elseif($doc->status === 'active')
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[2px] text-[10px] font-bold bg-emerald-50 border border-emerald-300 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Approved</span>
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-[2px] text-[10px] font-bold bg-slate-100 border border-slate-300 text-slate-700">
                                    {{ $doc->status }}
                                </span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-right align-middle whitespace-nowrap">
                            <x-interactive-button text="Buka Dokumen" variant="blue" icon="ph ph-arrow-right text-xs" href="{{ route('admin.BU.detail', $doc->id) }}" />
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <i class="ph ph-folder-open text-3xl text-slate-300"></i>
                                <h5 class="text-xs font-bold text-slate-700">Belum ada dokumen yang diunggah</h5>
                                <p class="text-[11px] text-slate-500 max-w-sm">Klik tombol Tambah SOP Baru untuk mengunggah dokumen baru pada unit ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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

    <!-- UNITS ENTERPRISE DATA TABLE (TRACKING STYLE) -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
        <!-- HEADER BIRU MUDA (TRACKING STYLE) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3 rounded-[2px]">
            <div class="flex items-center space-x-2 text-xs font-bold text-[#1677B8]">
                <i class="ph ph-table text-base"></i>
                <span class="capitalize text-slate-900 font-extrabold">Daftar Unit Bisnis di Bawah Divisi {{ $namaDivisi }}</span>
            </div>
            <span class="text-[11px] text-[#1677B8] font-bold bg-white px-2.5 py-1.5 rounded-[2px] border border-blue-200 whitespace-nowrap shadow-2xs">
                Total {{ count($statsBU) }} Unit Bisnis
            </span>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-[2px]">
            <table class="tracking-table w-full text-left border-collapse">
                <thead class="bg-[#1677B8] border-b border-[#1258a0] text-[10.5px] font-bold uppercase tracking-wider text-white">
                    <tr>
                        <th class="py-2.5 px-2 text-center w-8">No</th>
                        <th class="py-2.5 px-3">Unit Bisnis / Entitas</th>
                        <th class="py-2.5 px-3 text-center">Total Dokumen</th>
                        <th class="py-2.5 px-3 text-center">Dokumen Aktif</th>
                        <th class="py-2.5 px-3 text-center">Perlu Revisi / Menunggu</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs font-semibold text-slate-800">
                    @foreach($statsBU as $bu => $data)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-2.5 px-2 text-center font-bold text-slate-400 text-[11px] align-middle">
                            {{ $loop->iteration }}
                        </td>
                        <td class="py-2.5 px-3 align-middle">
                            <a href="{{ route('admin.BU.show', $bu) }}" class="flex items-center space-x-3 group hover:opacity-90 transition-opacity cursor-pointer">
                                <i class="ph ph-storefront text-2xl text-[#00b4d8] flex-shrink-0 group-hover:scale-110 transition-transform"></i>
                                <div>
                                    <span class="font-bold text-slate-900 text-xs capitalize block leading-tight group-hover:text-[#1677B8] hover:underline transition-colors">{{ $bu }}</span>
                                    <span class="text-[10px] text-slate-500 font-normal capitalize">Unit Operasional</span>
                                </div>
                            </a>
                        </td>
                        <td class="py-2.5 px-3 text-center align-middle font-mono font-bold text-xs text-slate-900">
                            {{ $data['total'] }} Dokumen
                        </td>
                        <td class="py-2.5 px-3 text-center align-middle">
                            <span class="inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-50 border border-emerald-300 px-2 py-0.5 rounded-[2px] text-[10px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                <span>{{ $data['active'] }} Aktif</span>
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-center align-middle">
                            <span class="inline-flex items-center gap-1.5 text-rose-700 bg-rose-50 border border-rose-300 px-2 py-0.5 rounded-[2px] text-[10px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                <span>{{ $data['inactive'] }} Menunggu/Revisi</span>
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-right align-middle">
                            <x-interactive-button text="Buka Dokumen" variant="blue" icon="ph ph-arrow-right text-xs" href="{{ route('admin.BU.show', $bu) }}" />
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


