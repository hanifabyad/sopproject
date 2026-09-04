@extends('layouts.admin')

@section('title', 'Arsip Dokumen')
@section('header_title', 'Arsip Dokumen & Masa Retensi SOP (3 Tahun)')

@section('content')
<div class="space-y-6">

    <!-- TOP BANNER -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-[2px] p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.dashboard') }}" variant="light" text="Kembali" />
            <span class="text-white/30">|</span>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#ffe16e] font-medium">Dashboard</a>
                <span>/</span>
                <span class="font-bold text-white">Arsip Dokumen</span>
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-white">Arsip Dokumen & Masa Retensi SOP</h2>
                <p class="text-xs text-white/85 mt-0.5 font-medium">Pusat arsip dokumen usang (Obsolete) selama masa retensi audit ISO 3 tahun sebelum pembersihan permanen.</p>
            </div>

        @if($dueForPurgeCount > 0)
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.recycle_bin.purge_due') }}" method="POST" onsubmit="return confirm('Peringatan: Seluruh dokumen usang yang sudah melewati masa retensi 3 tahun akan dihapus permanen beserta berkas fisiknya. Lanjutkan?')">
                @csrf
                <x-interactive-button text="Bersihkan {{ $dueForPurgeCount }} Dokumen > 3 Tahun" variant="danger" icon="ph ph-trash-simple text-base" type="submit" />
            </form>
        </div>
        @endif
    </div>

    <!-- STATS COUNTER BENTO -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-[2px] border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Total Dokumen Dalam Retensi</span>
                <h3 class="text-2xl font-black text-slate-800">{{ $totalObsolete }} <span class="text-xs font-semibold text-slate-500">SOP Obsolete</span></h3>
            </div>
            <div class="w-10 h-10 rounded-[2px] bg-blue-50 text-[#1677B8] border border-blue-200 flex items-center justify-center font-bold">
                <i class="ph ph-archive text-xl"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-[2px] border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Siap Dihapus Permanen</span>
                <h3 class="text-2xl font-black text-rose-700">{{ $dueForPurgeCount }} <span class="text-xs font-semibold text-slate-500">> 3 Tahun</span></h3>
            </div>
            <div class="w-10 h-10 rounded-[2px] bg-rose-50 text-rose-700 border border-rose-200 flex items-center justify-center font-bold">
                <i class="ph ph-clock-counter-clockwise text-xl"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-[2px] border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Kebijakan Retensi Dokumen</span>
                <h3 class="text-sm font-bold text-slate-800">3 Tahun Sejak Obsolete</h3>
                <p class="text-[10px] text-slate-500 mt-0.5">Standar Audit ISO 9001:2015</p>
            </div>
            <div class="w-10 h-10 rounded-[2px] bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold">
                <i class="ph ph-shield-check text-xl"></i>
            </div>
        </div>
    </div>

    <!-- FILTER BAR INTERAKTIF -->
    <div class="bg-white rounded-[2px] p-5 shadow-sm border border-sand-200/60">
        <form method="GET" action="{{ route('admin.recycle_bin.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <!-- Search Input -->
            <div class="sm:col-span-8">
                <label class="block text-[11px] font-bold capitalize text-slate-700 mb-1.5 flex items-center gap-1">
                    <i class="ph ph-magnifying-glass text-sm text-[#1677B8]"></i>
                    <span>Cari Dokumen Obsolete</span>
                </label>
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Ketik judul dokumen, nomor SOP, atau unit/dept..." 
                           class="w-full h-[36px] text-xs pl-8 pr-3 rounded-[2px] border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-medium text-slate-800">
                    <i class="ph ph-magnifying-glass text-xs text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            <!-- Filter Departemen -->
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold capitalize text-slate-700 mb-1.5 flex items-center gap-1">
                    <i class="ph ph-buildings text-sm text-[#1677B8]"></i>
                    <span>Unit / Departemen</span>
                </label>
                <select name="department" onchange="this.form.submit()" class="w-full h-[36px] bg-white border border-slate-200 rounded-[2px] px-3 text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-[#1677B8]">
                    <option value="all" {{ !$department || $department == 'all' ? 'selected' : '' }}>Semua Unit</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Reset -->
            <div class="sm:col-span-1">
                <a href="{{ route('admin.recycle_bin.index') }}" class="h-[36px] w-full bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 text-xs font-bold rounded-[2px] shadow-xs transition-all flex items-center justify-center cursor-pointer" title="Reset Filter">
                    <i class="ph ph-arrow-counter-clockwise text-base"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- TABEL RECYCLE BIN (TRACKING STYLE) -->
    <div class="bg-white rounded-[2px] p-6 shadow-sm border border-sand-200/60 space-y-4">
        <!-- HEADER BIRU MUDA -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3 rounded-[2px]">
            <div class="flex items-center space-x-2 text-xs font-bold text-[#1677B8]">
                <i class="ph ph-table text-base"></i>
                <span class="capitalize text-slate-900 font-extrabold">Daftar Dokumen Obsolete & Jadwal Retensi SOP</span>
            </div>
            <span class="text-[11px] text-[#1677B8] font-bold bg-white px-2.5 py-1.5 rounded-[2px] border border-blue-200 whitespace-nowrap shadow-2xs">
                Total {{ $documents->total() }} Dokumen Obsolete
            </span>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-[2px]">
            <table class="tracking-table w-full text-left border-collapse">
                <thead class="bg-[#1677B8] border-b border-[#1258a0] text-[10.5px] font-bold uppercase tracking-wider text-white">
                    <tr>
                        <th class="py-2.5 px-2 text-center w-8">No</th>
                        <th class="py-2.5 px-3">Dokumen SOP & Identitas</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap">Tgl Masuk Retensi</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap">Sisa Masa Retensi</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap">Status Retensi</th>
                        <th class="py-2.5 px-3 text-right whitespace-nowrap w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs font-semibold text-slate-800">
                    @forelse($documents as $index => $doc)
                    @php
                        $obsoleteDate = $doc->updated_at;
                        $purgeDate = (clone $obsoleteDate)->addYears(3);
                        $isExpired = now()->greaterThanOrEqualTo($purgeDate);
                        $daysRemaining = max(0, (int) now()->diffInDays($purgeDate, false));
                        $monthsRemaining = (int) ($daysRemaining / 30);
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-2.5 px-2 text-center font-bold text-slate-400 text-[11px] align-middle">
                            {{ $documents->firstItem() + $index }}
                        </td>

                        <!-- KOLOM 1: DOKUMEN SOP & IDENTITAS -->
                        <td class="py-2.5 px-3 align-middle">
                            @php
                                $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];
                                $isSupport = in_array(strtoupper($doc->department), $supportDepts);
                                $detailUrl = $isSupport ? route('admin.support.document.detail', $doc->id) : route('admin.BU.detail', $doc->id);
                            @endphp
                            <div class="font-bold text-slate-900 text-xs leading-snug">
                                <a href="{{ $detailUrl }}" class="hover:text-[#1677B8] hover:underline transition-colors cursor-pointer">{{ $doc->title }}</a>
                            </div>
                            <div class="flex items-center gap-1.5 mt-1 flex-wrap text-[10px]">
                                <span class="font-mono font-semibold text-slate-600">{{ $doc->doc_number ?? 'No. Belum Diatur' }}</span>
                                <span class="text-slate-300">|</span>
                                <span class="font-mono font-bold text-slate-700">Rev {{ $doc->doc_revision ?? '0' }}</span>
                                <span class="text-slate-300">|</span>
                                <span class="font-bold text-[#1677B8]">{{ $doc->department }}</span>
                            </div>
                        </td>

                        <!-- KOLOM 2: TGL MASUK RETENSI -->
                        <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                            <div class="font-mono text-xs text-slate-900 font-bold">
                                {{ $obsoleteDate->format('d/m/Y') }}
                            </div>
                            <div class="text-[10px] text-slate-500 font-medium mt-0.5">
                                {{ $obsoleteDate->format('H:i') }} WIB
                            </div>
                        </td>

                        <!-- KOLOM 3: SISA MASA RETENSI -->
                        <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                            @if($isExpired)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-100 text-rose-800 border border-rose-300 rounded-[2px] text-[10px] font-black">
                                    <i class="ph ph-warning"></i>
                                    <span>Melewati 3 Tahun (Siap Purge)</span>
                                </span>
                            @else
                                <div class="font-mono text-xs text-slate-800 font-bold">
                                    {{ $monthsRemaining }} Bulan ({{ $daysRemaining }} Hari)
                                </div>
                                <div class="text-[10px] text-slate-500 font-medium mt-0.5">
                                    Purge: {{ $purgeDate->format('d/m/Y') }}
                                </div>
                            @endif
                        </td>

                        <!-- KOLOM 4: STATUS RETENSI -->
                        <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                            @if($isExpired)
                                <span class="inline-flex px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-300 rounded-[2px] text-[9.5px] font-bold uppercase">
                                    KEDALUWARSA
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-300 rounded-[2px] text-[9.5px] font-bold uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    <span>ARSIP AKTIF</span>
                                </span>
                            @endif
                        </td>

                        <!-- KOLOM 5: AKSI (PULIHKAN & HAPUS PERMANEN) -->
                        <td class="py-2.5 px-3 text-right align-middle whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <!-- Tombol Pulihkan -->
                                <form action="{{ route('admin.recycle_bin.restore', $doc->id) }}" method="POST" class="inline" onsubmit="return confirm('Pulihkan dokumen ini kembali menjadi SOP Aktif?')">
                                    @csrf
                                    <x-interactive-button text="Pulihkan" variant="outline" icon="ph ph-arrow-counter-clockwise text-xs" type="submit" title="Pulihkan ke status Aktif" />
                                </form>

                                <!-- Tombol Hapus Permanen -->
                                <form action="{{ route('admin.recycle_bin.force_delete', $doc->id) }}" method="POST" class="inline" onsubmit="return confirm('PERINGATAN: Dokumen ini dan berkas fisiknya akan dihapus seutuhnya dari server secara permanen. Lanjutkan?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-interactive-button text="Hapus" variant="danger" icon="ph ph-trash text-xs" type="submit" title="Hapus Permanen" />
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <i class="ph ph-archive text-4xl text-slate-300"></i>
                                <h5 class="text-xs font-bold text-slate-700">Recycle Bin Bersih</h5>
                                <p class="text-[11px] text-slate-500 max-w-sm">
                                    Tidak ada dokumen usang (Obsolete) dalam masa retensi atau yang cocok dengan filter pencarian.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
        <div class="pt-3 border-t border-slate-200">
            {{ $documents->links() }}
        </div>
        @endif
    </div>

</div>
@endsection


