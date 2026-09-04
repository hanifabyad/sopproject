@extends('layouts.admin')

@section('title', 'Evaluasi SOP')
@section('header_title', 'Kelola Evaluasi SOP')

@section('content')
<div class="space-y-6">
    
    <!-- HEADER CARD -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.dashboard') }}" variant="light" text="Kembali" />
            <span class="text-white/30">|</span>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#ffe16e] font-medium">Dashboard</a>
                <span>/</span>
                <span class="font-bold text-white">Evaluasi SOP</span>
            </div>
        </div>

        <div>
            <h2 class="text-xl md:text-2xl font-extrabold tracking-tight capitalize text-white">Kelola Evaluasi Berkala SOP</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">
                Pemeriksaan kepatuhan, keefektifan operasional, dan tindak lanjut keputusan (revisi/obsolete) dokumen SOP yang telah aktif minimal 1 tahun.
            </p>
        </div>
    </div>

    <!-- FILTER BAR INTERAKTIF -->
    <div class="bg-white p-5 rounded-lg border border-sand-200/60 shadow-sm">
        <form action="{{ route('admin.evaluations.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <!-- Filter Status -->
            <div>
                <label class="block text-[11px] font-bold capitalize text-slate-700 mb-1.5 flex items-center gap-1">
                    <i class="ph ph-funnel text-sm text-[#1677B8]"></i>
                    <span>Status Evaluasi</span>
                </label>
                <select name="status" onchange="this.form.submit()" class="w-full h-[36px] text-xs px-3 rounded-[2px] border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-bold text-slate-800 bg-white">
                    <option value="">-- Semua Status --</option>
                    @foreach(['upcoming' => 'Upcoming (Jadwal Mendatang)', 'due' => 'Due (Jatuh Tempo)', 'in_review' => 'In Review (Sedang Diisi)', 'submitted' => 'Submitted (Menunggu Tinjau)', 'completed' => 'Completed (Selesai)', 'overdue' => 'Overdue (Terlambat)'] as $stKey => $stLabel)
                        <option value="{{ $stKey }}" {{ $status === $stKey ? 'selected' : '' }}>{{ $stLabel }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Hasil Akhir -->
            <div>
                <label class="block text-[11px] font-bold capitalize text-slate-700 mb-1.5 flex items-center gap-1">
                    <i class="ph ph-check-circle text-sm text-[#1677B8]"></i>
                    <span>Hasil Akhir Keputusan</span>
                </label>
                <select name="result" onchange="this.form.submit()" class="w-full h-[36px] text-xs px-3 rounded-[2px] border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-bold text-slate-800 bg-white">
                    <option value="">-- Semua Hasil Akhir --</option>
                    <option value="CONTINUE" {{ ($result ?? '') === 'CONTINUE' ? 'selected' : '' }}>CONTINUE (Tetap Berlaku)</option>
                    <option value="REVISION REQUIRED" {{ ($result ?? '') === 'REVISION REQUIRED' ? 'selected' : '' }}>REVISION REQUIRED (Perlu Revisi)</option>
                    <option value="NOT USED" {{ ($result ?? '') === 'NOT USED' ? 'selected' : '' }}>NOT USED (Tidak Digunakan)</option>
                    <option value="OBSOLETE" {{ ($result ?? '') === 'OBSOLETE' ? 'selected' : '' }}>OBSOLETE (Usang / Arsip)</option>
                </select>
            </div>

            <!-- Keyword Search -->
            <div>
                <label class="block text-[11px] font-bold capitalize text-slate-700 mb-1.5 flex items-center gap-1">
                    <i class="ph ph-magnifying-glass text-sm text-[#1677B8]"></i>
                    <span>Cari Judul / No. Dokumen / Dept</span>
                </label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari SOP..." 
                           class="w-full h-[36px] text-xs pl-8 pr-3 rounded-[2px] border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-medium text-slate-800">
                    <i class="ph ph-magnifying-glass text-xs text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            <!-- Action Buttons (Sejajar Presisi) -->
            <div class="flex items-center gap-2">
                <x-interactive-button text="Terapkan" variant="blue" icon="ph ph-magnifying-glass text-sm" type="submit" />
                <x-interactive-button text="" variant="outline" icon="ph ph-arrow-counter-clockwise text-base" href="{{ route('admin.evaluations.index') }}" title="Reset Filter" />
            </div>
        </form>
    </div>

    <!-- TABLE LIST -->
    <div class="bg-white rounded-lg border border-sand-200/60 shadow-sm overflow-hidden space-y-3 p-6">
        <!-- HEADER BIRU MUDA (KONSISTEN TRACKING) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3 rounded-[2px]">
            <div class="flex items-center space-x-2 text-xs font-bold text-[#1677B8]">
                <i class="ph ph-table text-base no-print"></i>
                <span class="capitalize text-slate-900 font-extrabold">Daftar Rekap Evaluasi Tahunan Dokumen SOP</span>
            </div>
            <span class="text-[11px] text-[#1677B8] font-bold bg-white px-2.5 py-1.5 rounded-[2px] border border-blue-200 whitespace-nowrap shadow-2xs">
                Total {{ $evaluations->count() }} Data Evaluasi
            </span>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-[2px]">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#1677B8] border-b border-[#1258a0] text-[10.5px] font-bold uppercase tracking-wider text-white">
                    <tr>
                        <th class="py-2.5 px-2 w-8 text-center">No</th>
                        <th class="py-2.5 px-3">Dokumen SOP & Identitas</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap">Jatuh Tempo & Periode</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap">Status Progres</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap">Hasil Evaluasi</th>
                        <th class="py-2.5 px-3 text-center whitespace-nowrap w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs font-semibold text-slate-800">
                    @forelse($evaluations as $eval)
                    @php
                        $statusClass = match($eval->status) {
                            'overdue'   => 'bg-red-50 text-red-700 border-red-300',
                            'due'       => 'bg-amber-50 text-amber-700 border-amber-300',
                            'in_review' => 'bg-blue-50 text-blue-700 border-blue-300',
                            'submitted' => 'bg-purple-50 text-purple-700 border-purple-300',
                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-300',
                            default     => 'bg-slate-50 text-slate-700 border-slate-300',
                        };

                        $resultClass = match($eval->result) {
                            'CONTINUE'          => 'bg-emerald-50 text-emerald-700 border-emerald-300',
                            'REVISION REQUIRED' => 'bg-blue-50 text-blue-700 border-blue-300',
                            'NOT USED'          => 'bg-amber-50 text-amber-700 border-amber-300',
                            'OBSOLETE'          => 'bg-rose-50 text-rose-700 border-rose-300',
                            default             => 'bg-slate-50 text-slate-500 border-slate-300',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-2.5 px-2 text-center font-bold text-slate-400 text-[11px] align-middle">{{ $loop->iteration }}</td>
                        
                        <!-- KOLOM 1: DOKUMEN SOP & IDENTITAS -->
                        <td class="py-2.5 px-3 align-middle">
                            @php
                                $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];
                                $isSupport = in_array(strtoupper($eval->document?->department ?? ''), $supportDepts);
                                $detailUrl = $eval->document ? ($isSupport ? route('admin.support.document.detail', $eval->document->id) : route('admin.BU.detail', $eval->document->id)) : null;
                            @endphp
                            <div class="font-bold text-slate-900 text-xs leading-snug">
                                @if($detailUrl)
                                    <a href="{{ $detailUrl }}" class="hover:text-[#1677B8] hover:underline transition-colors cursor-pointer">{{ $eval->document->title }}</a>
                                @else
                                    {{ $eval->document->title ?? 'SOP' }}
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 mt-1 flex-wrap text-[10px]">
                                <span class="font-mono font-semibold text-slate-600">{{ $eval->document->doc_number ?? 'No. Belum Diatur' }}</span>
                                <span class="text-slate-300">|</span>
                                <span class="font-mono font-bold text-slate-700">Rev {{ $eval->document->doc_revision ?? '0' }}</span>
                                <span class="text-slate-300">|</span>
                                <span class="font-bold text-[#1677B8]">{{ $eval->document->department }}</span>
                            </div>
                        </td>

                        <!-- KOLOM 2: JATUH TEMPO & PERIODE -->
                        <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                            <div class="font-mono text-xs text-slate-900 font-bold">
                                {{ $eval->due_date ? $eval->due_date->format('d/m/Y') : '-' }}
                            </div>
                            <div class="text-[10px] text-slate-500 font-medium mt-0.5">
                                Periode: {{ $eval->evaluation_period }}
                            </div>
                        </td>

                        <!-- KOLOM 3: STATUS PROGRES -->
                        <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                            <span class="inline-flex px-2 py-0.5 border rounded-[2px] text-[9.5px] font-bold uppercase tracking-tight {{ $statusClass }}">
                                {{ $eval->status }}
                            </span>
                        </td>

                        <!-- KOLOM 4: HASIL EVALUASI -->
                        <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                            @if($eval->result)
                            <span class="inline-flex px-2 py-0.5 border rounded-[2px] text-[9.5px] font-bold uppercase {{ $resultClass }}">
                                {{ $eval->result }}
                            </span>
                            @else
                            <span class="text-[10px] text-slate-400 font-semibold italic">Belum Ada</span>
                            @endif
                        </td>

                        <!-- KOLOM 5: AKSI TINDAKAN -->
                        <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                            @if(in_array($eval->status, ['submitted', 'completed']))
                                <x-interactive-button text="Tinjau" variant="blue" icon="ph ph-arrow-right text-xs" href="{{ route('admin.evaluations.show', $eval->id) }}" />
                            @else
                                <x-interactive-button text="Isi Form" variant="outline" icon="ph ph-note-pencil text-xs" href="{{ route('admin.evaluations.show', $eval->id) }}" />
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <i class="ph ph-folder-open text-3xl text-slate-300"></i>
                                <h5 class="text-xs font-bold text-slate-700">Tidak ada data evaluasi</h5>
                                <p class="text-[11px] text-slate-500 max-w-sm">
                                    Belum ada data evaluasi yang terdaftar atau cocok dengan kriteria filter Anda.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


