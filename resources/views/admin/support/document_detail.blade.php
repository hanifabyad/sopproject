@extends('layouts.admin')

@section('title', 'Audit Trail - ' . $document->title)
@section('header_title', 'Detail & Audit Trail Dokumen Support')

@section('content')
<div class="space-y-6">
    
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.support.show', $document->department) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-sand-200 bg-white text-on-surface-variant hover:bg-canvas hover:text-on-surface text-xs font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <span class="text-sand-200">|</span>
            <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                <a href="{{ route('admin.support.index') }}" class="hover:text-gold-500">Support</a>
                <span>/</span>
                <a href="{{ route('admin.support.show', $document->department) }}" class="hover:text-gold-500">{{ strtoupper($document->department) }}</a>
                <span>/</span>
                <span class="font-medium text-on-surface">{{ $document->document_number ?? $document->doc_number }}</span>
            </div>
        </div>

        <!-- Baris Judul & Status Badge -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-on-surface tracking-tight">{{ $document->title }}</h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Versi Revisi: {{ $document->doc_revision ?? '0' }} &bull; Tanggal: {{ $document->doc_date ?? $document->created_at->format('d M Y') }}</p>
            </div>
            <!-- Area Status Badge -->
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider {{ $document->status === 'active' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($document->status === 'need_revision' ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-amber-100 text-amber-800 border border-amber-300') }}">
                    {{ strtoupper(str_replace('_', ' ', $document->status)) }}
                </span>
            </div>
        </div>
    </div>

    <!-- MINIMALIST HORIZONTAL STEPPER -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
        <div class="flex items-center space-x-2 border-b border-sand-200/40 pb-3">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-on-surface">Alur Workflow Persetujuan Multi-Stage</h3>
        </div>

        <div class="relative py-2">
            <!-- 1px Connecting Line -->
            <div class="hidden md:block absolute top-1/2 left-0 right-0 h-[1px] bg-sand-200 -translate-y-1/2 z-0"></div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 relative z-10">
                <!-- STAGE 1: PEMBUAT DOKUMEN -->
                <div class="p-3.5 bg-white rounded-md border border-sand-200 flex items-center space-x-3 shadow-sm">
                    <div class="w-8 h-8 rounded bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-sm flex-shrink-0">
                        <span class="material-symbols-outlined text-sm">check</span>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-gold-500 block">Stage 1: Inisiator</span>
                        @php $creatorApp = $document->approvals->where('stage', 'creator')->first(); @endphp
                        <p class="text-xs font-bold text-on-surface truncate">{{ $creatorApp->user->full_name ?? $creatorApp->user->username ?? 'Initiator' }}</p>
                    </div>
                </div>

                <!-- STAGE 2: DIPERIKSA & DIKETAHUI (PARALEL REVIEWERS) -->
                <div class="p-3.5 bg-sand-50 rounded-md border border-sand-200 space-y-1.5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-gold-500">Stage 2: Peninjau</span>
                        <span class="w-2 h-2 rounded-full bg-gold-500 animate-ping"></span>
                    </div>
                    <div class="space-y-1 max-h-20 overflow-y-auto custom-scrollbar">
                        @foreach($document->approvals->where('stage', 'reviewer') as $revApp)
                            <div class="flex items-center justify-between p-1 bg-white border border-sand-200/40 rounded text-[11px] font-semibold text-on-surface">
                                <span class="truncate">{{ $revApp->user->full_name ?? $revApp->user->username }}</span>
                                @if($revApp->status === 'approved')
                                    <span class="material-symbols-outlined text-emerald-600 text-sm">check_circle</span>
                                @elseif($revApp->status === 'current')
                                    <span class="material-symbols-outlined text-amber-600 text-sm animate-spin">sync</span>
                                @else
                                    <span class="material-symbols-outlined text-gray-400 text-sm">hourglass_empty</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- STAGE 3: DISAHKAN (FINAL APPROVER) -->
                <div class="p-3.5 bg-white rounded-md border border-sand-200 flex items-center space-x-3 shadow-sm">
                    @php $finalApps = $document->approvals->where('stage', 'final'); $allFinalApproved = $finalApps->isNotEmpty() && $finalApps->every(fn($app) => $app->status === 'approved'); @endphp
                    <div class="w-8 h-8 rounded {{ $allFinalApproved ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-600' }} flex items-center justify-center font-bold text-xs shadow-sm flex-shrink-0">
                        <span class="material-symbols-outlined text-sm">{{ $allFinalApproved ? 'check' : 'draw' }}</span>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-gold-500 block">Stage 3: Pengesahan Final</span>
                        <p class="text-xs font-bold text-on-surface">{{ $finalApps->map(fn($app) => $app->user->full_name ?? $app->user->username)->join(', ') ?: 'Pimpinan Final' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN SPLIT VIEW (COL 8 PDF VIEWER & COL 4 METADATA/TIMELINE/ACTIONS) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT COLUMN: PDF VIEWER CONTAINER (COL 8) -->
        <div class="lg:col-span-8 bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
            <div class="flex items-center justify-between border-b border-sand-200/40 pb-3">
                <div class="flex items-center space-x-2 text-xs font-bold text-on-surface">
                    <span class="material-symbols-outlined text-red-600 text-base">picture_as_pdf</span>
                    <span>Document Stream Player</span>
                </div>
                <span class="text-[10px] text-on-surface-variant font-semibold">Dokumen Resmi Terenkripsi</span>
            </div>

            <div class="h-[650px] bg-canvas rounded-md overflow-hidden border border-sand-200">
                @php
                    $pathToShow = $pathFinal ?? $document->file_final ?? $document->file_preview ?? $document->file_lp;
                @endphp
                <iframe src="{{ asset('storage/' . $pathToShow) }}#toolbar=0" class="w-full h-full border-none"></iframe>
            </div>
        </div>

        <!-- RIGHT COLUMN: METADATA, ATTACHMENTS, RE-ASSIGNMENT, & AUDIT TRAIL (COL 4) -->
        <div class="lg:col-span-4 space-y-6">
            

            <!-- ATTACHMENTS LIST CARD -->
            @php $allAtts = $document->all_attachments; @endphp
            @if($allAtts->count() > 0)
            <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-3">
                <div class="flex items-center space-x-2 border-b border-sand-200/40 pb-3">
                    <span class="material-symbols-outlined text-gold-500 text-lg">attach_file</span>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-on-surface">Berkas Lampiran ({{ $allAtts->count() }})</h4>
                </div>

                <div class="space-y-2 max-h-40 overflow-y-auto custom-scrollbar">
                    @foreach($allAtts as $idx => $att)
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="flex items-center justify-between p-2.5 bg-canvas rounded-md border border-sand-200 hover:bg-[#fff9ed] transition-all text-xs font-semibold text-on-surface">
                            <div class="flex items-center gap-2 truncate pr-2">
                                <span class="text-gold-500 font-bold text-[10px]">{{ $idx + 1 }}.</span>
                                <span class="material-symbols-outlined text-red-600 text-sm">picture_as_pdf</span>
                                <span class="truncate text-[11px]">{{ $att->original_name ?? basename($att->file_path) }}</span>
                            </div>
                            <span class="material-symbols-outlined text-sm text-on-surface-variant">download</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- TIMELINE AUDIT TRAIL CARD -->
            <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
                <div class="flex items-center space-x-2 border-b border-sand-200/40 pb-3">
                    <span class="material-symbols-outlined text-gold-500 text-lg">history</span>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-on-surface">Timeline Audit Log Aktivitas</h4>
                </div>

                <div class="max-h-72 overflow-y-auto custom-scrollbar pr-2 space-y-4 relative">
                    <div class="absolute left-2.5 top-2 bottom-2 w-0.5 bg-sand-200"></div>

                    @forelse($document->logs as $log)
                    <div class="relative flex items-start space-x-3 pl-1">
                        @php
                            $badgeBg = 'bg-red-600 text-white';
                            if($log->action == 'active') $badgeBg = 'bg-emerald-600 text-white';
                            if($log->action == 'transfer') $badgeBg = 'bg-amber-500 text-white';
                        @endphp
                        
                        <div class="relative z-10 w-5 h-5 rounded {{ $badgeBg }} flex items-center justify-center text-[10px] font-bold ring-2 ring-white">
                            {{ $loop->iteration }}
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-on-surface">
                                {{ $log->user->username }} <span class="text-[10px] text-on-surface-variant font-normal">({{ $log->user->role }})</span>
                            </p>
                            <div class="mt-1 p-2.5 bg-canvas rounded-md border border-sand-200 text-xs text-on-surface">
                                <p class="italic text-[11px] leading-relaxed">"{{ $log->notes ?? 'tidak ada catatan tinjauan khusus' }}"</p>
                            </div>
                            <p class="text-[10px] text-on-surface-variant font-semibold mt-1">{{ $log->created_at->format('d M Y - H:i') }} WIB</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-on-surface-variant">
                        <span class="material-symbols-outlined text-3xl text-[#d6cebf]">folder_open</span>
                        <p class="text-xs font-bold mt-1">Belum ada riwayat aktivitas alur.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- NEED REVISION CALLOUT -->
            @if($document->status === 'need_revision')
                @php
                    $authUser = auth()->user();
                    $isAdmin = $authUser->role === 'admin';
                    $isDocCreator = $document->approvals->where('stage', 'creator')->where('user_id', $authUser->id)->isNotEmpty();
                @endphp
                @if($isAdmin || $isDocCreator)
                <div class="p-5 bg-red-50 border border-red-200 rounded-lg space-y-3 shadow-sm">
                    <div class="flex items-center space-x-2 text-red-700 font-bold text-xs">
                        <span class="material-symbols-outlined text-base">warning</span>
                        <span>Perlu Perbaikan Dokumen</span>
                    </div>
                    <p class="text-[11px] text-red-600 leading-relaxed">
                        Dokumen ini memerlukan perbaikan berdasarkan hasil tinjauan reviewer. Klik tombol di bawah untuk mengunggah draf perbaikan.
                    </p>
                    @if($isAdmin)
                        <a href="{{ route('admin.support.edit_revision', $document->id) }}" 
                           class="w-full inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-md shadow-sm transition-all text-xs uppercase tracking-wider gap-2">
                            <span class="material-symbols-outlined text-base">build</span>
                            <span>Upload Revisi Dokumen</span>
                        </a>
                    @else
                        <a href="{{ route('admin.support.creator_revise', $document->id) }}" 
                           class="w-full inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-md shadow-sm transition-all text-xs uppercase tracking-wider gap-2">
                            <span class="material-symbols-outlined text-base">build</span>
                            <span>Upload Revisi Dokumen</span>
                        </a>
                    @endif
                </div>
                @endif
            @endif

            <!-- DOWNLOAD & DELETE ACTIONS -->
            <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-3">
                @if($document->status == 'active' && ($pathFinal || $document->file_final))
                    <a href="{{ asset('storage/' . ($document->file_final ?? $pathFinal)) }}" 
                       download 
                       class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-bold text-xs uppercase tracking-wider transition-all shadow-sm flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">download_for_offline</span>
                        <span>Unduh Dokumen Sah Final</span>
                    </a>
                @endif

                <form action="{{ route('admin.support.document.delete', $document->id) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini secara permanen?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2 text-red-600 hover:bg-red-50 rounded-md font-bold text-xs transition-all flex items-center justify-center gap-1.5 border border-red-200">
                        <span class="material-symbols-outlined text-base">delete</span>
                        <span>Hapus Permanen Dokumen</span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
