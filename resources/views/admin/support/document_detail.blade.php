@extends('layouts.admin')

@section('title', 'Audit Trail - ' . $document->title)
@section('header_title', 'Detail & Audit Trail Dokumen Support')

@section('content')
<div class="space-y-6">
    
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.support.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-[#cfc6ac] bg-white text-[#4d4633] hover:bg-[#f7f6f2] hover:text-[#1e1c14] text-xs font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <span class="text-[#cfc6ac]">|</span>
            <div class="flex items-center gap-2 text-xs text-[#4d4633]">
                <a href="{{ route('admin.support.index') }}" class="hover:text-[#705d00]">Support</a>
                <span>/</span>
                <span class="font-medium text-[#1e1c14]">{{ $document->document_number ?? $document->doc_number }}</span>
            </div>
        </div>

        <!-- Baris Judul & Status Badge -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-[#1e1c14] tracking-tight">{{ $document->title }}</h2>
                <p class="text-xs text-[#4d4633] mt-0.5">Versi Revisi: {{ $document->doc_revision ?? '0' }} &bull; Tanggal: {{ $document->doc_date ?? $document->created_at->format('d M Y') }}</p>
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
    <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 space-y-4">
        <div class="flex items-center space-x-2 border-b border-[#cfc6ac]/40 pb-3">
            <span class="material-symbols-outlined text-[#705d00] text-lg">conversion_path</span>
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-[#1e1c14]">Alur Workflow Persetujuan Multi-Stage</h3>
        </div>

        <div class="relative py-2">
            <!-- 1px Connecting Line -->
            <div class="hidden md:block absolute top-1/2 left-0 right-0 h-[1px] bg-[#cfc6ac] -translate-y-1/2 z-0"></div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 relative z-10">
                <!-- STAGE 1: PEMBUAT DOKUMEN -->
                <div class="p-3.5 bg-white rounded-md border border-[#cfc6ac] flex items-center space-x-3 shadow-sm">
                    <div class="w-8 h-8 rounded bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-sm flex-shrink-0">
                        <span class="material-symbols-outlined text-sm">check</span>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-[#705d00] block">Stage 1: Inisiator</span>
                        @php $creatorApp = $document->approvals->where('stage', 'creator')->first(); @endphp
                        <p class="text-xs font-bold text-[#1e1c14] truncate">{{ $creatorApp->user->full_name ?? $creatorApp->user->username ?? 'Initiator' }}</p>
                    </div>
                </div>

                <!-- STAGE 2: DIPERIKSA & DIKETAHUI (PARALEL REVIEWERS) -->
                <div class="p-3.5 bg-[#fbf9f4] rounded-md border border-[#cfc6ac] space-y-1.5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-[#705d00]">Stage 2: Peninjau</span>
                        <span class="w-2 h-2 rounded-full bg-[#705d00] animate-ping"></span>
                    </div>
                    <div class="space-y-1 max-h-20 overflow-y-auto custom-scrollbar">
                        @foreach($document->approvals->where('stage', 'reviewer') as $revApp)
                            <div class="flex items-center justify-between p-1 bg-white border border-[#cfc6ac]/40 rounded text-[11px] font-semibold text-[#1e1c14]">
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
                <div class="p-3.5 bg-white rounded-md border border-[#cfc6ac] flex items-center space-x-3 shadow-sm">
                    @php $finalApp = $document->approvals->where('stage', 'final')->first(); @endphp
                    <div class="w-8 h-8 rounded {{ ($finalApp && $finalApp->status === 'approved') ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-600' }} flex items-center justify-center font-bold text-xs shadow-sm flex-shrink-0">
                        <span class="material-symbols-outlined text-sm">{{ ($finalApp && $finalApp->status === 'approved') ? 'check' : 'draw' }}</span>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-[#705d00] block">Stage 3: Pengesahan Final</span>
                        <p class="text-xs font-bold text-[#1e1c14] truncate">{{ $finalApp->user->full_name ?? $finalApp->user->username ?? 'Pimpinan Final' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN SPLIT VIEW (COL 8 PDF VIEWER & COL 4 METADATA/TIMELINE/ACTIONS) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT COLUMN: PDF VIEWER CONTAINER (COL 8) -->
        <div class="lg:col-span-8 bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 space-y-4">
            <div class="flex items-center justify-between border-b border-[#cfc6ac]/40 pb-3">
                <div class="flex items-center space-x-2 text-xs font-bold text-[#1e1c14]">
                    <span class="material-symbols-outlined text-red-600 text-base">picture_as_pdf</span>
                    <span>Document Stream Player</span>
                </div>
                <span class="text-[10px] text-[#4d4633] font-semibold">Dokumen Resmi Terenkripsi</span>
            </div>

            <div class="h-[650px] bg-[#f7f6f2] rounded-md overflow-hidden border border-[#cfc6ac]">
                @php
                    $pathToShow = $pathFinal ?? $document->file_final ?? $document->file_preview ?? $document->file_lp;
                @endphp
                <iframe src="{{ asset('storage/' . $pathToShow) }}#toolbar=0" class="w-full h-full border-none"></iframe>
            </div>
        </div>

        <!-- RIGHT COLUMN: METADATA, ATTACHMENTS, RE-ASSIGNMENT, & AUDIT TRAIL (COL 4) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- RE-ASSIGNMENT TRANSFER CARD -->
            <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 space-y-3">
                <div class="flex items-center space-x-2 border-b border-[#cfc6ac]/40 pb-3">
                    <span class="material-symbols-outlined text-[#705d00] text-lg">swap_horiz</span>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-[#1e1c14]">Oper Kendali (Estafet Manual)</h4>
                </div>

                <form action="{{ route('admin.support.updateReviewer', $document->id) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-[#4d4633] mb-1">Pilih Peninjau Target Baru:</label>
                        <select name="new_reviewer_id" class="w-full p-2.5 bg-[#fbf9f4] border border-[#cfc6ac] rounded-md text-xs font-semibold text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none">
                            @foreach($reviewers as $rev)
                                <option value="{{ $rev->id }}">{{ $rev->username }} ({{ $rev->role }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full py-2.5 bg-[#333028] text-[#ffe16e] hover:bg-black rounded-md font-bold text-xs uppercase tracking-wider transition-all shadow-sm flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-base">send</span>
                        <span>Transfer Kendali Dokumen</span>
                    </button>
                </form>
            </div>

            <!-- ATTACHMENTS LIST CARD -->
            @php $allAtts = $document->all_attachments; @endphp
            @if($allAtts->count() > 0)
            <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 space-y-3">
                <div class="flex items-center space-x-2 border-b border-[#cfc6ac]/40 pb-3">
                    <span class="material-symbols-outlined text-[#705d00] text-lg">attach_file</span>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-[#1e1c14]">Berkas Lampiran ({{ $allAtts->count() }})</h4>
                </div>

                <div class="space-y-2 max-h-40 overflow-y-auto custom-scrollbar">
                    @foreach($allAtts as $idx => $att)
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="flex items-center justify-between p-2.5 bg-[#f7f6f2] rounded-md border border-[#cfc6ac] hover:bg-[#fff9ed] transition-all text-xs font-semibold text-[#1e1c14]">
                            <div class="flex items-center gap-2 truncate pr-2">
                                <span class="text-[#705d00] font-bold text-[10px]">{{ $idx + 1 }}.</span>
                                <span class="material-symbols-outlined text-red-600 text-sm">picture_as_pdf</span>
                                <span class="truncate text-[11px]">{{ $att->original_name ?? basename($att->file_path) }}</span>
                            </div>
                            <span class="material-symbols-outlined text-sm text-[#4d4633]">download</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- TIMELINE AUDIT TRAIL CARD -->
            <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 space-y-4">
                <div class="flex items-center space-x-2 border-b border-[#cfc6ac]/40 pb-3">
                    <span class="material-symbols-outlined text-[#705d00] text-lg">history</span>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-[#1e1c14]">Timeline Audit Log Aktivitas</h4>
                </div>

                <div class="max-h-72 overflow-y-auto custom-scrollbar pr-2 space-y-4 relative">
                    <div class="absolute left-2.5 top-2 bottom-2 w-0.5 bg-[#cfc6ac]"></div>

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
                            <p class="text-xs font-bold text-[#1e1c14]">
                                {{ $log->user->username }} <span class="text-[10px] text-[#4d4633] font-normal">({{ $log->user->role }})</span>
                            </p>
                            <div class="mt-1 p-2.5 bg-[#f7f6f2] rounded-md border border-[#cfc6ac] text-xs text-[#1e1c14]">
                                <p class="italic text-[11px] leading-relaxed">"{{ $log->notes ?? 'tidak ada catatan tinjauan khusus' }}"</p>
                            </div>
                            <p class="text-[10px] text-[#4d4633] font-semibold mt-1">{{ $log->created_at->format('d M Y - H:i') }} WIB</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-[#4d4633]">
                        <span class="material-symbols-outlined text-3xl text-[#d6cebf]">folder_open</span>
                        <p class="text-xs font-bold mt-1">Belum ada riwayat aktivitas alur.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- NEED REVISION CALLOUT -->
            @if($document->status === 'need_revision')
                <div class="p-5 bg-red-50 border border-red-200 rounded-lg space-y-3 shadow-sm">
                    <div class="flex items-center space-x-2 text-red-700 font-bold text-xs">
                        <span class="material-symbols-outlined text-base">warning</span>
                        <span>Perlu Perbaikan Dokumen</span>
                    </div>
                    <p class="text-[11px] text-red-600 leading-relaxed">
                        Dokumen ini memerlukan perbaikan berdasarkan hasil tinjauan reviewer. Klik tombol di bawah untuk mengunggah draf perbaikan.
                    </p>
                    <a href="{{ route('admin.support.edit_revision', $document->id) }}" 
                       class="w-full inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-md shadow-sm transition-all text-xs uppercase tracking-wider gap-2">
                        <span class="material-symbols-outlined text-base">build</span>
                        <span>Upload Revisi Dokumen</span>
                    </a>
                </div>
            @endif

            <!-- DOWNLOAD & DELETE ACTIONS -->
            <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 space-y-3">
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