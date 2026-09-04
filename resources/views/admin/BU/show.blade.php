@extends('layouts.admin')

@section('title', 'Audit Trail - ' . $document->title)
@section('header_title', 'Audit Trail & Detail Dokumen BU')

@section('content')
<div class="space-y-6 bu-support-scope">
    
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.BU.show', $document->department) }}" variant="light" />
            <span class="text-white/30">|</span>
            <div class="flex items-center gap-2 text-xs text-white/80">
                <a href="{{ route('admin.BU.index') }}" class="hover:text-[#ffe16e] font-medium">Business Unit</a>
                <span>/</span>
                <a href="{{ route('admin.BU.show', $document->department) }}" class="hover:text-[#ffe16e] font-medium">{{ $document->department }}</a>
                <span>/</span>
                <span class="font-bold text-white">{{ $document->document_number ?? $document->doc_number }}</span>
            </div>
        </div>

        <!-- Baris Judul & Status Badge -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-white">{{ $document->title }}</h2>
                <p class="text-xs text-white/85 mt-0.5 font-medium">Versi Revisi: Rev {{ $document->doc_revision ?? '0' }} &bull; Tanggal: {{ $document->doc_date ?? $document->created_at->format('d M Y') }}</p>
            </div>
            <!-- Area Status Badge -->
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-md text-xs font-bold capitalize tracking-wider {{ $document->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($document->status === 'need_revision' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                    {{ strtoupper(str_replace('_', ' ', $document->status)) }}
                </span>
            </div>
        </div>
    </div>

    <!-- MAIN GRID 2 COLS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- PDF PREVIEW PLAYER (8 COLS) -->
        <div class="lg:col-span-8 bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
            <div class="flex items-center justify-between border-b border-sand-200/40 pb-3">
                <div class="flex items-center space-x-2 text-xs font-bold text-on-surface">
                    <span class="material-symbols-outlined text-red-600">picture_as_pdf</span>
                    <span>Preview Dokumen SOP Terintegrasi</span>
                </div>
            </div>
            
            <div class="rounded-md overflow-hidden border border-sand-200 bg-canvas shadow-inner">
                @php
                    $pathToShow = ($document->status === 'active' ? $document->file_final : null) ?? $document->file_preview ?? $document->file_lp;
                @endphp
                <iframe src="{{ asset('storage/' . $pathToShow) }}#toolbar=0&navpanes=0" 
                        class="w-full min-h-[750px] border-none" 
                        style="height: 75vh;">
                </iframe>
            </div>
        </div>

        <!-- SIDEBAR AUDIT LOG & ACTIONS (4 COLS) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- TIMELINE CARD -->
            <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
                <div class="border-b border-sand-200/40 pb-3 flex items-center space-x-2">
                    <span class="material-symbols-outlined text-[#00b4d8] text-lg">history</span>
                    <h3 class="text-xs font-extrabold text-on-surface capitalize tracking-wider">Timeline Persetujuan</h3>
                </div>
                
                <div class="space-y-4 relative pl-2">
                    @forelse($document->logs as $log)
                    <div class="relative pl-6 border-l-2 {{ $log->action == 'revisi' ? 'border-red-500' : ($log->action == 'transfer' ? 'border-purple-500' : 'border-gold-500') }} pb-3 space-y-1">
                        <div class="absolute -left-[7px] top-0 w-3 h-3 rounded-full {{ $log->action == 'revisi' ? 'bg-red-500' : ($log->action == 'transfer' ? 'bg-purple-500' : 'bg-gold-500') }} border-2 border-white"></div>
                        
                        <p class="text-xs font-bold text-on-surface">
                            {{ $log->user->username }} 
                            <span class="text-on-surface-variant font-normal text-[11px]">({{ $log->user->role }})</span>
                        </p>
                        
                        <div class="p-3 bg-canvas rounded-lg border border-sand-200 text-xs text-on-surface-variant font-medium leading-relaxed">
                            "{{ $log->notes ?? 'tidak ada catatan tinjauan khusus' }}"
                        </div>
                        <p class="text-[10px] text-on-surface-variant font-semibold">{{ $log->created_at->format('d M Y - H:i') }} WIB</p>
                    </div>
                    @empty
                    <div class="text-center py-6 text-on-surface-variant">
                        <span class="material-symbols-outlined text-3xl text-[#d6cebf]">folder_open</span>
                        <p class="text-xs font-bold mt-1">Belum ada riwayat aktivitas alur.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- ACTION PERBAIKAN -->
            @if($document->status === 'need_revision')
            <div class="bg-[#fff9ed] rounded-lg p-6 border border-sand-200/80 space-y-3">
                <h4 class="text-xs font-bold text-charcoal-900">Perbaikan Dokumen</h4>
                <p class="text-xs text-charcoal-700 leading-relaxed">
                    Dokumen ini membutuhkan revisi. Silakan periksa catatan peninjau di atas dan unggah berkas perbaikan.
                </p>
                <a href="{{ route('admin.BU.edit_revision', $document->id) }}" 
                   class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-md bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs shadow-sm transition-colors cursor-pointer">
                    <i class="ph ph-note-pencil text-sm"></i>
                    <span>Unggah Berkas Revisi</span>
                </a>
            </div>
            @endif
            
        </div>
    </div>
</div>
@endsection
