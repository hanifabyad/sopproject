@extends('layouts.reviewer')

@section('title', 'Peninjauan Digital - ' . $document->title)
@section('header_title', 'Peninjauan & Verifikasi Digital Dokumen SOP')

@section('content')
<div class="space-y-6">
    
    <!-- TOP STATUS HEADER BAR (BLUE GRADIENT THEME) -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-5 shadow-sm border border-white/15 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('reviewer.dashboard') }}" variant="light" text="Kembali" />
            <span class="text-white/30">|</span>
            <div class="flex items-center space-x-2 text-xs text-white/85 font-semibold">
                <a href="{{ route('reviewer.dashboard') }}" class="hover:text-[#ffe16e] font-bold flex items-center gap-1">
                    <i class="ph ph-squares-four text-base"></i>
                    <span>Dashboard</span>
                </a>
                <span>/</span>
                <span class="px-2 py-0.5 bg-white/20 rounded font-bold text-[10px] uppercase tracking-wider text-white border border-white/20">
                    {{ $document->department }}
                </span>
                @if($document->doc_number)
                    <span>•</span>
                    <span class="font-mono text-white/90 text-[11px]">{{ $document->doc_number }}</span>
                @endif
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold text-white tracking-tight capitalize">{{ $document->title }}</h2>
        </div>
    </div>

    @php
        $myApproval = $document->approvals->where('user_id', Auth::id())->sortByDesc('sequence')->first();
        $isMyTurn = ($myApproval && $myApproval->status === 'current') || (Auth::user()->role === 'admin' && $document->status !== 'active' && $document->status !== 'need_revision');
        $hasApproved = $myApproval && $myApproval->status === 'approved';
        $isPendingTurn = $myApproval && $myApproval->status === 'pending';
    @endphp

    <!-- MAIN WORKSPACE SPLIT-SCREEN (VIEWER LEFT, ACTION PANEL RIGHT) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT: INTERACTIVE VISUAL PDF ANNOTATOR & MARKUP TOOL -->
        <div class="lg:col-span-8 space-y-3 w-full">
            <x-pdf-annotator 
                :pdf-url="route('reviewer.stream.file', $document->id) . '?t=' . ($document->updated_at ? $document->updated_at->timestamp : time())"
                :read-only="!$isMyTurn"
                input-name="annotations"
                form-id="review-form"
                height="720px"
            />
        </div>

        <!-- RIGHT: UNIFIED ACTION & HISTORY PANEL (EQUAL HEIGHT WITH PDF) -->
        <div class="lg:col-span-4 w-full flex flex-col space-y-4 lg:h-[720px]">
            
            <!-- 1. DECISION CARD (TOP) -->
            <div class="bg-white p-5 rounded-lg shadow-sm border border-sand-200/80 space-y-4 flex-shrink-0">
                <!-- PANEL HEADER -->
                <div class="border-b border-sand-200/60 pb-2.5 flex items-center justify-between">
                    <div class="flex items-center space-x-2 text-[#1677B8]">
                        <i class="ph ph-gavel text-lg"></i>
                        <h4 class="font-extrabold text-xs capitalize tracking-wider text-on-surface">Keputusan Peninjauan</h4>
                    </div>
                    @if($document->status === 'active')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Dokumen Aktif & Sah
                        </span>
                    @elseif($hasApproved)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Sudah Anda Setujui
                        </span>
                    @elseif($isPendingTurn)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            Menunggu Giliran
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Menunggu Tindakan Anda
                        </span>
                    @endif
                </div>
                
                @if($document->status === 'need_revision')
                    <div class="p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-900 rounded-r-md text-xs space-y-2 text-center">
                        <i class="ph ph-lock-key text-3xl text-amber-500 block mx-auto animate-pulse"></i>
                        <h5 class="font-bold text-[11px]">Dokumen Ditangguhkan / Terkunci</h5>
                        <p class="text-[10px] text-on-surface-variant leading-relaxed">Salah satu reviewer meminta perbaikan (revisi). Proses persetujuan ditangguhkan sampai Pembuat Dokumen mengunggah berkas revisi baru.</p>
                    </div>
                @elseif($document->status === 'active')
                    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-900 rounded-r-md text-xs space-y-2">
                        <div class="flex items-center gap-2 font-bold text-[11px] text-emerald-800">
                            <i class="ph ph-check-circle text-lg text-emerald-600"></i>
                            <span>Dokumen Telah Sah & Aktif</span>
                        </div>
                        <p class="text-[11px] text-emerald-700 leading-relaxed">Seluruh tahapan persetujuan dan pengesahan telah selesai. Stempel digital resmi telah disematkan pada dokumen.</p>
                    </div>
                @elseif($hasApproved && !$isMyTurn)
                    <div class="p-4 bg-emerald-50/90 border border-emerald-200 text-emerald-950 rounded-lg text-xs space-y-2">
                        <div class="flex items-center gap-2 font-bold text-[11.5px] text-emerald-800">
                            <i class="ph ph-seal-check text-xl text-emerald-600"></i>
                            <span>Persetujuan Anda Berhasil Disimpan</span>
                        </div>
                        <p class="text-[11px] text-emerald-800/90 leading-relaxed">
                            Anda telah memberikan persetujuan pada dokumen ini
                            @if($myApproval && $myApproval->processed_at)
                                pada <strong>{{ \Carbon\Carbon::parse($myApproval->processed_at)->timezone('Asia/Jakarta')->format('d M Y, H:i \W\I\B') }}</strong>.
                            @else
                                .
                            @endif
                        </p>
                        <div class="p-2.5 bg-white/80 rounded border border-emerald-200/60 text-[10.5px] text-emerald-700 flex items-center gap-1.5">
                            <i class="ph ph-info text-sm flex-shrink-0"></i>
                            <span>Dokumen saat ini sedang menunggu penyelesaian persetujuan oleh peninjau lainnya. Stempel digital Anda telah dibubuhkan pada lembar pratinjau.</span>
                        </div>
                    </div>
                @elseif($isPendingTurn)
                    <div class="p-4 bg-slate-50 border border-slate-200 text-slate-800 rounded-lg text-xs space-y-2">
                        <div class="flex items-center gap-2 font-bold text-[11px] text-slate-700">
                            <i class="ph ph-hourglass-simple text-lg text-slate-500"></i>
                            <span>Menunggu Giliran Peninjauan</span>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            Giliran peninjauan Anda belum aktif. Akses persetujuan akan otomatis terbuka setelah peninjau pada tahap sebelumnya selesai memberikan keputusan.
                        </p>
                    </div>
                @else
                    <!-- DECISION FORM -->
                    <form id="review-form" method="POST" class="space-y-3">
                        @csrf
                        
                        <!-- DETAILS METADATA SUMMARY -->
                        <div class="p-2.5 bg-canvas rounded-md border border-sand-200 text-[11px] text-on-surface-variant flex items-center justify-between">
                            <span>Role Anda:</span>
                            <strong class="text-on-surface capitalize font-bold text-slate-800">{{ Auth::user()->role }}</strong>
                        </div>

                        <!-- NOTES TEXTAREA -->
                        <div>
                            <textarea 
                                id="reviewer-notes"
                                name="notes" 
                                placeholder="Tuliskan catatan perbaikan atau alasan revisi di sini..."
                                class="w-full bg-canvas border border-sand-200 rounded-md p-3 text-xs font-semibold text-on-surface outline-none focus:bg-white focus:ring-2 focus:ring-[#1677B8] transition-all placeholder-slate-400 resize-none"
                                rows="4"></textarea>
                        </div>
                        
                        <!-- ACTION BUTTONS -->
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <!-- Approve Button -->
                            <button type="submit" 
                                    id="approve-btn"
                                    formaction="{{ route('reviewer.approve', $document->id) }}"
                                    class="py-2.5 px-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-md font-bold capitalize text-xs tracking-wider shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="ph ph-check-circle text-base"></i>
                                <span>Setuju & Sahkan</span>
                            </button>

                            <!-- Reject / Revision Button -->
                            <button type="submit" 
                                    id="reject-btn"
                                    formaction="{{ route('reviewer.reject', $document->id) }}"
                                    class="py-2.5 px-2 bg-rose-600 hover:bg-rose-700 text-white rounded-md font-bold capitalize text-xs tracking-wider shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="ph ph-arrow-u-up-left text-base"></i>
                                <span>Minta Revisi</span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            <!-- 2. DECISION HISTORY & NOTES CARD (BOTTOM, FILLS REST OF HEIGHT) -->
            <div class="bg-white p-4 rounded-lg shadow-sm border border-sand-200/80 flex-1 min-h-0 flex flex-col">
                <div class="border-b border-sand-200/60 pb-2 mb-2 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center space-x-1.5 text-slate-800">
                        <i class="ph ph-clock-counter-clockwise text-base text-[#1677B8]"></i>
                        <h4 class="font-extrabold text-[11px] capitalize tracking-wider text-on-surface">Riwayat & Log</h4>
                    </div>
                    
                    <div class="flex items-center gap-1.5">
                        <!-- Switcher Tab: Peninjau vs Log -->
                        <div class="flex items-center bg-slate-100 p-0.5 rounded text-[9.5px] font-bold">
                            <button type="button" id="tab-btn-approvals" class="px-2 py-0.5 rounded bg-white text-[#1677B8] shadow-xs cursor-pointer transition-all">
                                Peninjau
                            </button>
                            <button type="button" id="tab-btn-logs" class="px-2 py-0.5 rounded text-slate-500 hover:text-slate-900 cursor-pointer transition-all">
                                Log ({{ $document->logs->count() }})
                            </button>
                        </div>

                        <!-- Expand Modal Button -->
                        <button type="button" onclick="openTimelineModal()" class="px-1.5 py-0.5 rounded bg-sky-50 text-[#1677B8] hover:bg-[#1677B8] hover:text-white border border-[#1677B8]/20 font-bold text-[9.5px] flex items-center gap-1 transition-all cursor-pointer" title="Perbesar & Lihat Seluruh Log Riwayat">
                            <i class="ph ph-arrows-out-simple text-xs"></i>
                            <span class="hidden sm:inline">Expand</span>
                        </button>
                    </div>
                </div>

                <!-- TAB 1: CURRENT APPROVAL SLOTS -->
                <div id="tab-content-approvals" class="space-y-1.5 flex-1 overflow-y-auto pr-1 custom-scrollbar">
                    @forelse($document->approvals as $approval)
                        @if($approval->user && $approval->stage !== 'creator')
                        @php
                            $isRejected = ($approval->status === 'rejected' || $approval->status === 'need_revision');
                            $isApproved = ($approval->status === 'approved');
                            $isCurrent  = ($approval->status === 'current');
                        @endphp
                        <div class="p-2 px-2.5 bg-canvas rounded-md border border-sand-200/80 text-[11px] transition-all {{ 
                            $isRejected ? 'border-l-[3.5px] border-l-rose-500' : 
                            ($isApproved ? 'border-l-[3.5px] border-l-emerald-500' : 
                            ($isCurrent ? 'border-l-[3.5px] border-l-amber-500' : 'border-l-[3.5px] border-l-slate-300')) 
                        }}">
                            <!-- Top Row: Name + Role + Status Badge -->
                            <div class="flex justify-between items-center gap-1">
                                <div class="flex items-center gap-1.5 min-w-0 truncate">
                                    <span class="font-bold text-[10.5px] truncate text-on-surface">
                                        {{ $approval->user->full_name ?? $approval->user->username }}
                                    </span>
                                    <span class="text-[9px] font-semibold text-slate-400 capitalize flex-shrink-0">
                                        ({{ $approval->user->role }})
                                    </span>
                                </div>
                                <div class="flex-shrink-0">
                                    @if($isApproved)
                                        <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[8.5px] font-bold capitalize inline-flex items-center gap-0.5">
                                            <i class="ph ph-check text-[9px]"></i> Disetujui
                                        </span>
                                    @elseif($isRejected)
                                        <span class="px-1.5 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded text-[8.5px] font-bold capitalize inline-flex items-center gap-0.5">
                                            <i class="ph ph-warning-octagon text-[9px]"></i> Minta Revisi
                                        </span>
                                    @elseif($isCurrent)
                                        <span class="px-1.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded text-[8.5px] font-bold capitalize inline-flex items-center gap-0.5">
                                            <i class="ph ph-hourglass text-[9px]"></i> Giliran
                                        </span>
                                    @else
                                        <span class="px-1.5 py-0.5 bg-slate-100 text-slate-500 border border-slate-200 rounded text-[8.5px] font-bold capitalize">
                                            Antre
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Middle Row: Notes (if any) -->
                            @if(filled($approval->notes))
                                <div class="mt-1 p-1.5 px-2 bg-white rounded border border-sand-200 text-[10px] leading-snug text-on-surface">
                                    @if($isRejected)
                                        <div class="flex items-start gap-1">
                                            <i class="ph ph-note-pencil text-rose-600 text-xs mt-0.5 flex-shrink-0"></i>
                                            <span>{{ $approval->notes }}</span>
                                        </div>
                                    @else
                                        <span class="italic text-slate-600">"{{ $approval->notes }}"</span>
                                    @endif
                                </div>
                            @endif

                            <!-- Bottom Row: Annotation Tag + Timestamp -->
                            <div class="mt-1 flex items-center justify-between text-[8.5px] font-medium text-slate-400">
                                @if($isRejected && !empty($approval->annotations) && $approval->annotations !== '[]' && $approval->annotations !== '{}')
                                    <span class="inline-flex items-center gap-1 font-semibold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">
                                        <i class="ph ph-pencil-simple-line"></i> Anotasi PDF
                                    </span>
                                @else
                                    <span></span>
                                @endif

                                @if($approval->processed_at)
                                    <span class="text-slate-400">
                                        {{ date('d M Y, H:i', strtotime($approval->processed_at)) }} WIB
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endif
                    @empty
                        <div class="text-center py-4 text-on-surface-variant">
                            <i class="ph ph-chat-circle-dots text-2xl text-sand-300 mb-0.5 block"></i>
                            <p class="text-[11px] font-semibold">Belum ada riwayat persetujuan.</p>
                        </div>
                    @endforelse
                </div>

                <!-- TAB 2: FULL CHRONOLOGICAL AUDIT LOGS -->
                <div id="tab-content-logs" class="space-y-2 flex-1 overflow-y-auto pr-1 custom-scrollbar hidden">
                    @forelse($document->logs()->with('user')->latest('id')->get() as $log)
                    <div class="p-2 px-2.5 bg-canvas rounded-md border border-sand-200/80 text-[10.5px] space-y-1">
                        <div class="flex justify-between items-center gap-1">
                            <div class="flex items-center gap-1.5 min-w-0 truncate">
                                <span class="font-bold text-[10.5px] truncate text-on-surface">
                                    {{ $log->user->full_name ?? $log->user->username ?? 'Sistem' }}
                                </span>
                                <span class="text-[9px] font-semibold text-slate-400 capitalize flex-shrink-0">
                                    ({{ $log->user->role ?? '-' }})
                                </span>
                            </div>
                            <span class="text-[8.5px] px-1.5 py-0.5 rounded font-bold uppercase {{ 
                                in_array($log->action, ['approved', 'active', 'final_approved']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                (in_array($log->action, ['rejected', 'minta_revisi', 'need_revision']) ? 'bg-rose-50 text-rose-700 border border-rose-200' :
                                (in_array($log->action, ['revised', 'resubmitted', 'unggah_revisi']) ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-700 border border-slate-200'))
                            }}">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </div>

                        @if(filled($log->notes))
                            <p class="p-1.5 bg-white rounded border border-sand-200/80 text-[10px] text-slate-700 italic leading-snug">
                                "{{ $log->notes }}"
                            </p>
                        @endif

                        <p class="text-[8.5px] text-slate-400 font-medium text-right">
                            {{ $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}
                        </p>
                    </div>
                    @empty
                    <div class="text-center py-4 text-on-surface-variant">
                        <i class="ph ph-list-dashes text-2xl text-sand-300 mb-0.5 block"></i>
                        <p class="text-[11px] font-semibold">Belum ada riwayat aktivitas log.</p>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>

<!-- 🔍 EXPAND MODAL: SELURUH LOG & RIWAYAT AUDIT TRAIL LENGKAP -->
<div id="modal-full-timeline" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl shadow-2xl border border-sand-200 w-full max-w-3xl max-h-[85vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white p-4 px-6 flex items-center justify-between border-b border-white/10">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center">
                    <i class="ph ph-clock-counter-clockwise text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold tracking-tight capitalize text-white">Seluruh Log & Riwayat Peninjauan Dokumen</h3>
                    <p class="text-[11px] text-white/85 font-medium">
                        {{ $document->doc_number ?? 'SOP' }} &bull; {{ $document->title }} (Rev {{ $document->doc_revision ?? '0' }})
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeTimelineModal()" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white text-base transition-colors cursor-pointer" title="Tutup Modal">
                <i class="ph ph-x font-bold"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable Timeline) -->
        <div class="p-6 overflow-y-auto space-y-6 flex-1 custom-scrollbar bg-slate-50/50">
            
            <!-- Summary Status Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-3 bg-white rounded-lg border border-sand-200 shadow-xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Status Dokumen</span>
                    <span class="text-xs font-extrabold text-slate-800 capitalize mt-0.5 block">{{ str_replace('_', ' ', $document->status) }}</span>
                </div>
                <div class="p-3 bg-white rounded-lg border border-sand-200 shadow-xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Departemen / Unit</span>
                    <span class="text-xs font-extrabold text-slate-800 uppercase mt-0.5 block">{{ $document->department }}</span>
                </div>
                <div class="p-3 bg-white rounded-lg border border-sand-200 shadow-xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Aktivitas Log</span>
                    <span class="text-xs font-extrabold text-[#1677B8] mt-0.5 block">{{ $document->logs->count() }} Rekaman Log</span>
                </div>
            </div>

            <!-- Full Audit Trail Timeline -->
            <div class="bg-white rounded-lg p-5 border border-sand-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-sand-200/60 pb-2.5">
                    <div class="flex items-center gap-2 text-xs font-extrabold text-slate-800">
                        <i class="ph ph-list-checks text-base text-[#1677B8]"></i>
                        <span>Urutan Kronologis Aktivitas (Dari Awal Sampai Terkini)</span>
                    </div>
                </div>

                <div class="space-y-4 relative pl-3">
                    <div class="absolute left-6 top-3 bottom-3 w-0.5 bg-slate-200"></div>

                    @forelse($document->logs()->with('user')->oldest('id')->get() as $idx => $log)
                    <div class="relative flex items-start space-x-3.5">
                        @php
                            $badgeColor = 'bg-slate-700 text-white';
                            if(in_array($log->action, ['approved', 'active', 'final_approved'])) $badgeColor = 'bg-emerald-600 text-white';
                            if(in_array($log->action, ['rejected', 'minta_revisi', 'need_revision'])) $badgeColor = 'bg-rose-600 text-white';
                            if(in_array($log->action, ['revised', 'resubmitted', 'unggah_revisi'])) $badgeColor = 'bg-blue-600 text-white';
                            if(in_array($log->action, ['creator_approval', 'creator_approved'])) $badgeColor = 'bg-amber-600 text-white';
                        @endphp
                        
                        <div class="relative z-10 w-6 h-6 rounded-full {{ $badgeColor }} flex items-center justify-center text-[10px] font-extrabold ring-4 ring-white shadow-xs flex-shrink-0">
                            {{ $idx + 1 }}
                        </div>

                        <div class="flex-1 bg-slate-50/80 p-3 rounded-lg border border-sand-200/80 space-y-1.5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-extrabold text-slate-900">
                                        {{ $log->user->full_name ?? $log->user->username ?? 'Sistem' }}
                                    </span>
                                    <span class="text-[10px] font-semibold text-slate-500 bg-white px-1.5 py-0.5 rounded border border-sand-200 capitalize">
                                        {{ $log->user->role ?? 'Sistem' }}
                                    </span>
                                </div>
                                <span class="text-[9.5px] px-2 py-0.5 rounded-full font-bold uppercase {{ 
                                    in_array($log->action, ['approved', 'active', 'final_approved']) ? 'bg-emerald-100 text-emerald-800' :
                                    (in_array($log->action, ['rejected', 'minta_revisi', 'need_revision']) ? 'bg-rose-100 text-rose-800' :
                                    (in_array($log->action, ['revised', 'resubmitted', 'unggah_revisi']) ? 'bg-blue-100 text-blue-800' : 'bg-slate-200 text-slate-700'))
                                }}">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                            </div>

                            @if(filled($log->notes))
                                <div class="p-2.5 bg-white rounded border border-sand-200 text-xs text-slate-800 leading-relaxed font-medium whitespace-pre-line">
                                    {{ $log->notes }}
                                </div>
                            @endif

                            <div class="flex items-center justify-between text-[10px] text-slate-400 font-medium pt-0.5">
                                <span>Aktivitas #{{ $idx + 1 }}</span>
                                <span>{{ $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->format('d F Y - H:i:s') . ' WIB' : '-' }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-slate-400">
                        <i class="ph ph-tray text-3xl mb-1 block"></i>
                        <p class="text-xs font-semibold">Belum ada riwayat aktivitas yang tercatat pada dokumen ini.</p>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="bg-white p-3.5 px-6 border-t border-sand-200 flex items-center justify-end">
            <button type="button" onclick="closeTimelineModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-md text-xs font-bold transition-all cursor-pointer">
                Tutup Riwayat
            </button>
        </div>
    </div>
</div>

<script>
    function openTimelineModal() {
        const modal = document.getElementById('modal-full-timeline');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function closeTimelineModal() {
        const modal = document.getElementById('modal-full-timeline');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Tab Switcher Peninjau vs Log
        const tabBtnApprovals = document.getElementById('tab-btn-approvals');
        const tabBtnLogs = document.getElementById('tab-btn-logs');
        const contentApprovals = document.getElementById('tab-content-approvals');
        const contentLogs = document.getElementById('tab-content-logs');

        if (tabBtnApprovals && tabBtnLogs && contentApprovals && contentLogs) {
            tabBtnApprovals.addEventListener('click', () => {
                tabBtnApprovals.classList.add('bg-white', 'text-[#1677B8]', 'shadow-xs');
                tabBtnApprovals.classList.remove('text-slate-500');
                tabBtnLogs.classList.remove('bg-white', 'text-[#1677B8]', 'shadow-xs');
                tabBtnLogs.classList.add('text-slate-500');
                contentApprovals.classList.remove('hidden');
                contentLogs.classList.add('hidden');
            });

            tabBtnLogs.addEventListener('click', () => {
                tabBtnLogs.classList.add('bg-white', 'text-[#1677B8]', 'shadow-xs');
                tabBtnLogs.classList.remove('text-slate-500');
                tabBtnApprovals.classList.remove('bg-white', 'text-[#1677B8]', 'shadow-xs');
                tabBtnApprovals.classList.add('text-slate-500');
                contentLogs.classList.remove('hidden');
                contentApprovals.classList.add('hidden');
            });
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeTimelineModal();
            }
        });

        const reviewForm = document.getElementById('review-form');
        const rejectBtn = document.getElementById('reject-btn');
        const approveBtn = document.getElementById('approve-btn');

        if (rejectBtn) {
            rejectBtn.addEventListener('click', function () {
                if (typeof window.syncAnnotatorPayload === 'function') {
                    window.syncAnnotatorPayload();
                }
            });
        }

        if (approveBtn) {
            approveBtn.addEventListener('click', function () {
                if (typeof window.syncAnnotatorPayload === 'function') {
                    window.syncAnnotatorPayload();
                }
            });
        }

        if (reviewForm) {
            reviewForm.addEventListener('submit', function () {
                if (typeof window.syncAnnotatorPayload === 'function') {
                    window.syncAnnotatorPayload();
                }
            });
        }
    });
</script>
@endsection
