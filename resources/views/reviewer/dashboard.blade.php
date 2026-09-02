@extends('layouts.reviewer')

@section('title', 'Antrean Review')
@section('header_title', 'Daftar Antrean Review SOP')

@section('content')
<div class="space-y-6">
    
    <!-- HEADER WELCOME CARD -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/15 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold capitalize tracking-wider bg-gold-fixed text-charcoal-900 mb-2.5 shadow-sm">
                <i class="ph ph-check-square-offset text-xs"></i> Reviewer Workspace
            </span>
            <h2 class="text-xl md:text-2xl font-extrabold tracking-tight capitalize">
                SOP Menunggu Review Anda
            </h2>
            <p class="text-xs text-white/85 mt-1 max-w-xl font-medium">
                Daftar dokumen mutu yang memerlukan pemeriksaan, catatan revisi, atau pengesahan tanda tangan digital dari Anda.
            </p>
        </div>
    </div>

    <!-- SECTION 1: MENUNGGU AKSI SAYA -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
        <div class="flex items-center justify-between border-b border-sand-200/40 pb-4">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold">
                    <i class="ph ph-hourglass text-base"></i>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-on-surface capitalize tracking-wider">Menunggu Tindakan Saya</h3>
                    <p class="text-[11px] text-on-surface-variant">Dokumen yang saat ini memerlukan persetujuan atau tanda tangan dari Anda</p>
                </div>
            </div>
            @if($pendingDocuments->count() > 0)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-[10px] font-extrabold capitalize tracking-wider border border-amber-200">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                {{ $pendingDocuments->count() }} Antrean
            </span>
            @endif
        </div>

        <div class="overflow-x-auto border border-sand-200/60 rounded-md overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-sand-50 border-b border-sand-200 text-[11px] font-bold capitalize tracking-wider text-on-surface-variant">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Judul Dokumen SOP</th>
                        <th class="py-3 px-4 min-w-[155px]">Departemen / Unit</th>
                        <th class="py-3 px-4">Tanggal Pengajuan</th>
                        <th class="py-3 px-4 text-center">Aksi Tinjauan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e8e2d6] text-xs font-semibold text-on-surface">
                    @forelse($pendingDocuments as $doc)
                    <tr class="border-b border-[#e8e2d6] hover:bg-amber-50/30 transition-colors">
                        <td class="py-3.5 px-4 text-center font-bold text-gold-500">{{ $loop->iteration }}</td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center space-x-3">
                                <i class="ph ph-file-text text-amber-500 text-lg flex-shrink-0"></i>
                                <span class="font-bold text-on-surface capitalize hover:text-gold-500 transition-colors">{{ $doc->title }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center whitespace-nowrap px-2.5 py-1 bg-canvas border border-sand-200/60 rounded-md text-[10px] font-bold capitalize text-charcoal-900">
                                {{ $doc->department }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-on-surface-variant text-[11px]">
                            {{ $doc->created_at->format('d M Y - H:i') }} WIB
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('reviewer.show', $doc->id) }}" 
                               class="inline-flex items-center justify-center bg-charcoal-900 hover:bg-black text-gold-fixed px-4 py-2 rounded-md font-bold capitalize text-[10px] tracking-wider transition-all gap-1.5 shadow-sm">
                                <span>Review</span>
                                <i class="ph ph-arrow-right text-sm"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-on-surface-variant">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <i class="ph ph-check-circle text-4xl text-emerald-500"></i>
                                <h5 class="text-xs font-bold text-on-surface capitalize tracking-wide">Semua tugas review selesai</h5>
                                <p class="text-[11px] text-on-surface-variant max-w-sm">
                                    Saat ini belum ada antrean dokumen yang memerlukan pemeriksaan atau penandatanganan digital Anda.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION 2: SUDAH SAYA PROSES, MASIH BERJALAN -->
    @if($inProgressDocuments->count() > 0)
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
        <div class="flex items-center justify-between border-b border-sand-200/40 pb-4">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-md bg-[#1677B8] text-white flex items-center justify-center font-bold">
                    <i class="ph ph-clock-countdown text-base"></i>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-on-surface capitalize tracking-wider">Sudah Saya Setujui — Masih Berjalan</h3>
                    <p class="text-[11px] text-on-surface-variant">Dokumen yang telah Anda tandatangani namun masih menunggu proses dari pihak lain</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-extrabold capitalize tracking-wider border border-blue-200">
                {{ $inProgressDocuments->count() }} Dokumen
            </span>
        </div>

        <div class="overflow-x-auto border border-sand-200/60 rounded-md overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-sand-50 border-b border-sand-200 text-[11px] font-bold capitalize tracking-wider text-on-surface-variant">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Judul Dokumen SOP</th>
                        <th class="py-3 px-4 min-w-[155px]">Departemen / Unit</th>
                        <th class="py-3 px-4">Saya Setujui Pada</th>
                        <th class="py-3 px-4 text-center">Status Dokumen</th>
                        <th class="py-3 px-4 text-center">Lihat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e8e2d6] text-xs font-semibold text-on-surface">
                    @foreach($inProgressDocuments as $doc)
                    @php
                        $myApproval = $doc->my_approval;
                        $approvedAt = $myApproval?->processed_at
                            ? \Carbon\Carbon::parse($myApproval->processed_at)->format('d M Y')
                            : '-';
                        $docStatus = $doc->status;
                        $statusLabel = match($docStatus) {
                            'waiting'  => ['label' => 'Menunggu Reviewer Lain', 'bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'border' => 'border-amber-200'],
                            'revision' => ['label' => 'Perlu Revisi', 'bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200'],
                            default    => ['label' => ucfirst($docStatus), 'bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-200'],
                        };
                    @endphp
                    <tr class="border-b border-[#e8e2d6] hover:bg-blue-50/20 transition-colors">
                        <td class="py-3.5 px-4 text-center font-bold text-[#1677B8]">{{ $loop->iteration }}</td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center space-x-3">
                                <i class="ph ph-file-check text-emerald-500 text-lg flex-shrink-0"></i>
                                <span class="font-bold text-on-surface capitalize">{{ $doc->title }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center whitespace-nowrap px-2.5 py-1 bg-canvas border border-sand-200/60 rounded-md text-[10px] font-bold capitalize text-charcoal-900">
                                {{ $doc->department }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-on-surface-variant text-[11px]">
                            {{ $approvedAt }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold capitalize border {{ $statusLabel['bg'] }} {{ $statusLabel['text'] }} {{ $statusLabel['border'] }}">
                                {{ $statusLabel['label'] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('reviewer.show', $doc->id) }}" 
                               class="inline-flex items-center justify-center bg-white border border-sand-200 hover:bg-canvas text-charcoal-900 px-3 py-1.5 rounded-md font-bold text-[10px] tracking-wider transition-all gap-1 shadow-sm">
                                <i class="ph ph-eye text-sm"></i>
                                <span>Lihat</span>
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
