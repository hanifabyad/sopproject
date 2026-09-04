@extends('layouts.reviewer')

@section('title', 'Evaluasi SOP')
@section('header_title', 'Daftar Evaluasi SOP')

@section('content')
<div class="space-y-6">
    
    <!-- HEADER WELCOME CARD -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="relative z-10">
            <h2 class="text-xl md:text-2xl font-extrabold tracking-tight capitalize">
                Evaluasi SOP
            </h2>
            <p class="text-xs text-white/85 mt-1 max-w-xl font-medium">
                Pemeriksaan berkala tahunan dokumen SOP berstatus aktif untuk memastikan kepatuhan standar, efisiensi, dan keabsahan operasional di lapangan.
            </p>
        </div>
    </div>

    <!-- SECTION 1: MENUNGGU EVALUASI -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
        <div class="flex items-center justify-between border-b border-sand-200/40 pb-4">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-md bg-[#1677B8] text-white flex items-center justify-center font-bold">
                    <i class="ph ph-hourglass text-base"></i>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-on-surface capitalize tracking-wider">Menunggu Evaluasi Saya</h3>
                    <p class="text-[11px] text-on-surface-variant">Dokumen SOP aktif yang memerlukan peninjauan dan pengisian form evaluasi dari Anda</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto border border-sand-200/70 rounded-md">
            <table class="w-full text-left border-collapse">
                <thead class="bg-sand-50 border-b border-sand-200 text-[11px] font-bold capitalize tracking-wider text-on-surface-variant">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Judul Dokumen SOP</th>
                        <th class="py-3 px-4">Nomor Dokumen</th>
                        <th class="py-3 px-4 text-center">Versi</th>
                        <th class="py-3 px-4">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand-200/50 text-xs font-semibold text-on-surface">
                    @php
                        $pendingEvals = $evaluations->whereIn('status', ['due', 'in_review', 'overdue']);
                    @endphp
                    @forelse($pendingEvals as $eval)
                    @php
                        $statusClass = match($eval->status) {
                            'overdue' => 'bg-red-50 text-red-700 border-red-200',
                            'due' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'in_review' => 'bg-blue-50 text-blue-700 border-blue-200',
                            default => 'bg-slate-50 text-slate-700 border-slate-200',
                        };
                    @endphp
                    <tr class="border-b border-sand-200/40 hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 text-center font-bold text-[#1677B8]">{{ $loop->iteration }}</td>
                        <td class="py-3.5 px-4 font-bold capitalize text-on-surface">{{ $eval->document->title }}</td>
                        <td class="py-3.5 px-4 text-on-surface-variant font-mono text-[11px]">{{ $eval->document->doc_number ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center text-[#1677B8] font-bold">Rev {{ $eval->document->doc_revision ?? '0' }}</td>
                        <td class="py-3.5 px-4 text-on-surface-variant text-[11px]">
                            {{ $eval->due_date ? $eval->due_date->format('d M Y') : '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex px-2.5 py-0.5 border rounded-full text-[9px] font-extrabold capitalize tracking-wide {{ $statusClass }}">
                                {{ $eval->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <x-interactive-button text="Evaluasi" variant="blue" icon="ph ph-arrow-right text-xs" href="{{ route('evaluations.show', $eval->id) }}" />
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-on-surface-variant">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <i class="ph ph-check-circle text-4xl text-emerald-500"></i>
                                <h5 class="text-xs font-bold text-on-surface capitalize tracking-wide">Semua evaluasi selesai</h5>
                                <p class="text-[11px] text-on-surface-variant max-w-sm">
                                    Saat ini belum ada dokumen aktif di unit Anda yang memerlukan evaluasi tahunan.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION 2: EVALUASI TELAH DIKIRIM -->
    @php
        $completedEvals = $evaluations->whereIn('status', ['submitted', 'admin_review', 'completed']);
    @endphp
    @if($completedEvals->count() > 0)
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
        <div class="flex items-center justify-between border-b border-sand-200/40 pb-4">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-md bg-emerald-600 text-white flex items-center justify-center font-bold">
                    <i class="ph ph-check text-base"></i>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-on-surface capitalize tracking-wider">Evaluasi Telah Dikirim</h3>
                    <p class="text-[11px] text-on-surface-variant">Riwayat evaluasi dokumen yang telah Anda ajukan ke Admin QMS</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto border border-sand-200/70 rounded-md">
            <table class="w-full text-left border-collapse">
                <thead class="bg-sand-50 border-b border-sand-200 text-[11px] font-bold capitalize tracking-wider text-on-surface-variant">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Judul Dokumen SOP</th>
                        <th class="py-3 px-4">Nomor Dokumen</th>
                        <th class="py-3 px-4 text-center">Periode</th>
                        <th class="py-3 px-4">Tanggal Kirim</th>
                        <th class="py-3 px-4 text-center">Rekomendasi Anda</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand-200/50 text-xs font-semibold text-on-surface">
                    @foreach($completedEvals as $eval)
                    @php
                        $statusClass = match($eval->status) {
                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            default => 'bg-purple-50 text-purple-700 border-purple-200',
                        };
                    @endphp
                    <tr class="border-b border-sand-200/40 hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 text-center font-bold text-emerald-600">{{ $loop->iteration }}</td>
                        <td class="py-3.5 px-4 capitalize font-bold text-on-surface">{{ $eval->document->title }}</td>
                        <td class="py-3.5 px-4 text-on-surface-variant font-mono text-[11px]">{{ $eval->document->doc_number ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center font-bold">{{ $eval->evaluation_period }}</td>
                        <td class="py-3.5 px-4 text-on-surface-variant text-[11px]">
                            {{ $eval->submitted_at ? $eval->submitted_at->format('d M Y') : '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex px-2.5 py-0.5 bg-canvas border border-sand-200 text-charcoal-900 rounded text-[9px] font-extrabold capitalize">
                                {{ $eval->result ?? 'MENUNGGU TINDAK LANJUT' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex px-2.5 py-0.5 border rounded-full text-[9px] font-extrabold capitalize tracking-wide {{ $statusClass }}">
                                {{ $eval->status }}
                            </span>
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
