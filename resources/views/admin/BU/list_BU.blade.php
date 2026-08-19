@extends('layouts.admin')

@section('title', 'Daftar - ' . ($stats['name'] ?? $namaDivisi))
@section('header_title', 'Daftar SOP Unit Bisnis')

@section('content')
<div class="space-y-6">

    @if(isset($stats))
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.BU.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-[#cfc6ac] bg-white text-[#4d4633] hover:bg-[#f7f6f2] hover:text-[#1e1c14] text-xs font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <span class="text-[#cfc6ac]">|</span>
            <div class="flex items-center gap-2 text-xs text-[#4d4633]">
                <a href="{{ route('admin.BU.index') }}" class="hover:text-[#705d00]">Business Unit</a>
                <span>/</span>
                <span class="font-medium text-[#1e1c14]">{{ $stats['name'] }}</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold text-[#1e1c14] uppercase tracking-tight">Unit Operasional - {{ $stats['name'] }}</h2>
            <p class="text-xs text-[#4d4633] mt-0.5">Daftar dokumen operasional dan status persetujuan SOP pada unit {{ $stats['name'] }}</p>
        </div>
    </div>

    <!-- STATS COUNTER GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- UNIT INFO -->
        <div class="bg-white p-5 rounded-lg border border-[#cfc6ac]/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-[#4d4633] uppercase tracking-wider block mb-0.5">Unit Operasional</span>
                <h3 class="text-base font-extrabold text-[#1e1c14] uppercase truncate max-w-[150px]">{{ $stats['name'] }}</h3>
            </div>
            <div class="w-9 h-9 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm">
                <span class="material-symbols-outlined text-lg">store</span>
            </div>
        </div>

        <!-- APPROVED COUNTER -->
        <div class="bg-white p-5 rounded-lg border border-[#cfc6ac]/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-[#4d4633] uppercase tracking-wider block mb-0.5">SOP Approved</span>
                <h3 class="text-2xl font-extrabold text-emerald-700">{{ $stats['approved'] }}</h3>
            </div>
            <div class="w-9 h-9 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/80 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-lg">verified</span>
            </div>
        </div>

        <!-- WAITING COUNTER -->
        <div class="bg-white p-5 rounded-lg border border-[#cfc6ac]/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-[#4d4633] uppercase tracking-wider block mb-0.5">SOP Waiting</span>
                <h3 class="text-2xl font-extrabold text-amber-700">{{ $stats['waiting'] }}</h3>
            </div>
            <div class="w-9 h-9 rounded-md bg-amber-50 text-amber-700 border border-amber-200/80 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-lg">hourglass_top</span>
            </div>
        </div>

        <!-- REVISI COUNTER -->
        <div class="bg-white p-5 rounded-lg border border-[#cfc6ac]/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-[#4d4633] uppercase tracking-wider block mb-0.5">SOP Revisi</span>
                <h3 class="text-2xl font-extrabold text-red-700">{{ $stats['revisi'] }}</h3>
            </div>
            <div class="w-9 h-9 rounded-md bg-red-50 text-red-700 border border-red-200/80 flex items-center justify-center font-bold">
                <span class="material-symbols-outlined text-lg">warning</span>
            </div>
        </div>
    </div>

    <!-- DOCUMENT TABLE CONTAINER -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#cfc6ac]/40 pb-4">
            <div class="flex items-center space-x-2 text-sm font-bold text-[#1e1c14]">
                <span class="material-symbols-outlined text-[#705d00] text-lg">folder_open</span>
                <span class="uppercase">Daftar Dokumen SOP - {{ $stats['name'] }}</span>
            </div>
            <a href="{{ route('admin.BU.create', $stats['name']) }}" 
               class="px-4 py-2 bg-[#333028] text-[#ffe16e] hover:bg-black rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center gap-1.5 self-start sm:self-auto">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Tambah SOP Baru</span>
            </a>
        </div>

        <div class="border border-[#cfc6ac]/70 rounded-md bg-white overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-body-main">
                    <thead class="bg-[#eee8db] border-b border-[#cfc6ac] text-[12px] font-semibold uppercase tracking-wider text-[#4d4633]">
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
                        <tr class="border-b border-[#e8e2d6] hover:bg-[#f7f6f2] transition-colors">
                            <td class="py-3.5 px-4 text-center text-xs font-bold text-[#4d4633]">
                                {{ $loop->iteration }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-md bg-[#fbf9f4] border border-[#cfc6ac]/60 text-[#705d00] flex items-center justify-center font-bold shadow-sm">
                                        <span class="material-symbols-outlined text-base">description</span>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.BU.detail', $doc->id) }}" class="font-semibold text-xs text-[#1e1c14] hover:text-[#705d00] transition uppercase block leading-tight">
                                            {{ $doc->title }}
                                        </a>
                                        <span class="text-[10px] text-[#4d4633] font-normal uppercase">Nomor: {{ $doc->doc_number ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center text-xs font-semibold text-[#4d4633]">
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
                                <a href="{{ route('admin.BU.detail', $doc->id) }}" class="px-4 py-1.5 rounded-md bg-[#705d00] text-white hover:bg-[#544600] text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
                                    <span>Detail</span>
                                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-[#4d4633]">
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
    <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.BU.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-[#cfc6ac] bg-white text-[#4d4633] hover:bg-[#f7f6f2] hover:text-[#1e1c14] text-xs font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <span class="text-[#cfc6ac]">|</span>
            <div class="flex items-center gap-2 text-xs text-[#4d4633]">
                <a href="{{ route('admin.BU.index') }}" class="hover:text-[#705d00]">Business Unit</a>
                <span>/</span>
                <span class="font-medium text-[#1e1c14]">Divisi {{ $namaDivisi }}</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold text-[#1e1c14] uppercase tracking-tight">Divisi {{ $namaDivisi }}</h2>
            <p class="text-xs text-[#4d4633] mt-0.5">Daftar unit bisnis operasional di bawah naungan Divisi {{ $namaDivisi }}</p>
        </div>
    </div>

    <!-- UNITS ENTERPRISE LINEAR DATA TABLE -->
    <div class="border border-[#cfc6ac]/70 rounded-md bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-body-main">
                <thead class="bg-[#eee8db] border-b border-[#cfc6ac] text-[12px] font-semibold uppercase tracking-wider text-[#4d4633]">
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
                    <tr class="border-b border-[#e8e2d6] hover:bg-[#f7f6f2] transition-colors">
                        <td class="py-3.5 px-4 text-center text-xs font-bold text-[#4d4633]">
                            {{ $loop->iteration }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm">
                                    <span class="material-symbols-outlined text-base">store</span>
                                </div>
                                <div>
                                    <span class="font-semibold text-[#1e1c14] text-[14px] uppercase block leading-tight">{{ $bu }}</span>
                                    <span class="text-[10px] text-[#4d4633] font-normal uppercase">Unit Operasional</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 bg-[#f7f6f2] border border-[#cfc6ac]/60 font-bold text-[#1e1c14] text-xs rounded-md inline-block">
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
                            <a href="{{ route('admin.BU.show', $bu) }}" class="px-4 py-1.5 rounded-md bg-[#705d00] text-white hover:bg-[#544600] text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
                                <span>Buka Dokumen</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
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
