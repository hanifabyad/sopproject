@extends('layouts.admin')

@section('title', 'Evaluasi SOP')
@section('header_title', 'Kelola Evaluasi SOP')

@section('content')
<div class="space-y-6">
    
    <!-- HEADER CARD -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <div>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#ffe16e] font-bold flex items-center gap-1">
                    <i class="ph ph-squares-four text-base"></i>
                    <span>Dashboard</span>
                </a>
                <span>/</span>
                <span class="text-[#ffe16e] font-bold capitalize">Evaluasi SOP</span>
            </div>
            <h2 class="text-xl font-extrabold tracking-tight capitalize">Kelola Evaluasi Berkala SOP</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">
                Pemeriksaan kepatuhan, keefektifan operasional, dan tindak lanjut keputusan (revisi/obsolete) dokumen SOP yang telah aktif minimal 1 tahun.
            </p>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-5 rounded-lg border border-sand-200/60 shadow-sm">
        <form action="{{ route('admin.evaluations.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
            <div class="flex flex-col md:flex-row gap-3 flex-1">
                <div class="w-full md:w-56">
                    <label class="block text-[11px] font-bold capitalize text-on-surface-variant mb-1 flex items-center gap-1">
                        <i class="ph ph-funnel text-sm text-[#1677B8]"></i>
                        <span>Status Evaluasi</span>
                    </label>
                    <select name="status" onchange="this.form.submit()" class="w-full text-xs p-2.5 rounded-md border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-bold text-on-surface bg-white">
                        <option value="">-- Semua Status --</option>
                        @foreach(['upcoming' => 'Upcoming (Jadwal Mendatang)', 'due' => 'Due (Jatuh Tempo)', 'in_review' => 'In Review (Sedang Diisi)', 'submitted' => 'Submitted (Menunggu Tinjau)', 'completed' => 'Completed (Selesai)', 'overdue' => 'Overdue (Terlambat)'] as $stKey => $stLabel)
                            <option value="{{ $stKey }}" {{ $status === $stKey ? 'selected' : '' }}>{{ $stLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-[11px] font-bold capitalize text-on-surface-variant mb-1 flex items-center gap-1">
                        <i class="ph ph-magnifying-glass text-sm text-[#1677B8]"></i>
                        <span>Cari Judul / No. Dokumen / Dept</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari SOP..." 
                               class="w-full text-xs p-2.5 pl-8 rounded-md border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-medium">
                        <i class="ph ph-magnifying-glass text-xs text-on-surface-variant absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                    </div>
                </div>
            </div>
            <div class="flex gap-2 items-center">
                <x-interactive-button text="Terapkan" variant="blue" class="!h-[38px] !py-0 flex items-center" />
                <a href="{{ route('admin.evaluations.index') }}" class="h-[38px] w-[38px] bg-canvas hover:bg-slate-50 border border-slate-200 text-on-surface-variant text-xs font-bold rounded-md shadow-sm transition-all flex items-center justify-center cursor-pointer flex-shrink-0" title="Reset Filter">
                    <i class="ph ph-arrow-counter-clockwise text-base"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- TABLE LIST -->
    <div class="bg-white rounded-lg border border-sand-200/60 shadow-sm overflow-hidden space-y-3 p-6">
        <div class="flex items-center justify-between border-b border-sand-200/40 pb-3">
            <div class="flex items-center space-x-2 text-xs font-bold text-on-surface">
                <i class="ph ph-table text-base text-[#1677B8]"></i>
                <span class="capitalize">Daftar Rekap Evaluasi Tahunan</span>
            </div>
            <span class="text-xs text-on-surface-variant font-semibold">Total {{ $evaluations->count() }} data evaluasi</span>
        </div>

        <div class="overflow-x-auto border border-sand-200/70 rounded-md">
            <table class="w-full text-left border-collapse">
                <thead class="bg-sand-50 border-b border-sand-200 text-[11px] font-bold capitalize tracking-wider text-on-surface-variant">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Judul Dokumen SOP</th>
                        <th class="py-3 px-4">Nomor Dokumen</th>
                        <th class="py-3 px-4">Unit / Dept</th>
                        <th class="py-3 px-4">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Hasil Akhir</th>
                        <th class="py-3 px-4 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand-200/50 text-xs font-semibold text-on-surface">
                    @forelse($evaluations as $eval)
                    @php
                        $statusClass = match($eval->status) {
                            'overdue' => 'bg-red-50 text-red-700 border-red-200',
                            'due' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'in_review' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'submitted' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            default => 'bg-slate-50 text-slate-700 border-slate-200',
                        };

                        $resultClass = match($eval->result) {
                            'CONTINUE' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'REVISION REQUIRED' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'NOT USED' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'OBSOLETE' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-slate-50 text-slate-500 border-slate-200',
                        };
                    @endphp
                    <tr class="border-b border-sand-200/40 hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 text-center font-bold text-[#1677B8]">{{ $loop->iteration }}</td>
                        <td class="py-3.5 px-4">
                            <span class="block capitalize font-bold text-on-surface">{{ $eval->document->title }}</span>
                            <span class="text-[10px] text-on-surface-variant">Periode: {{ $eval->evaluation_period }} &bull; Rev {{ $eval->document->doc_revision ?? '0' }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-on-surface-variant">{{ $eval->document->doc_number ?? '-' }}</td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex px-2.5 py-0.5 bg-canvas border border-sand-200 rounded text-[10px] font-extrabold capitalize text-charcoal-900">
                                {{ $eval->document->department }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-[11px] text-on-surface-variant">
                            {{ $eval->due_date ? $eval->due_date->format('d M Y') : '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex px-2.5 py-0.5 border rounded-full text-[9px] font-extrabold capitalize tracking-wide {{ $statusClass }}">
                                {{ $eval->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex px-2 py-0.5 border rounded text-[9px] font-extrabold capitalize {{ $resultClass }}">
                                {{ $eval->result ?? 'BELUM SUBMIT' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if(in_array($eval->status, ['submitted', 'completed']))
                            <a href="{{ route('admin.evaluations.show', $eval->id) }}" 
                               class="inline-flex items-center justify-center bg-[#1677B8] hover:bg-[#1260a0] text-white px-3.5 py-1.5 rounded-md font-bold capitalize text-[10px] tracking-wider transition-all gap-1.5 shadow-sm border-none cursor-pointer">
                                <span>Tinjau</span>
                                <i class="ph ph-arrow-right text-xs"></i>
                            </a>
                            @else
                            <span class="text-[10px] text-on-surface-variant font-bold">Menunggu Evaluator</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-on-surface-variant">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <i class="ph ph-folder-open text-4xl text-sand-300"></i>
                                <h5 class="text-xs font-bold text-on-surface capitalize tracking-wide">Tidak ada data evaluasi</h5>
                                <p class="text-[11px] text-on-surface-variant max-w-sm">
                                    Belum ada data evaluasi yang terdaftar atau cocok dengan kriteria pencarian Anda.
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
