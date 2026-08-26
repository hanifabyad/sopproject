@extends('layouts.reviewer')

@section('title', 'Peninjauan Digital - ' . $document->title)
@section('header_title', 'Peninjauan & Verifikasi Digital Dokumen SOP')

@section('content')
<div class="space-y-6">
    
    <!-- TOP STATUS HEADER BAR -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-[#e5dfd3] flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-on-surface-variant mb-1">
                <span class="px-3 py-1 bg-amber-50 border border-amber-200 text-amber-700 rounded-full font-bold text-[10px] uppercase flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>Awaiting Review</span>
                </span>
                <span>•</span>
                <span class="font-bold text-on-surface">{{ strtoupper($document->department) }}</span>
            </div>
            <h2 class="text-xl font-extrabold text-on-surface tracking-tight uppercase">{{ $document->title }}</h2>
        </div>

        <a href="{{ route('reviewer.dashboard') }}" class="px-4 py-2 bg-canvas border border-[#e5dfd3] text-charcoal-900 hover:bg-[#fff9ed] rounded-md font-bold text-xs transition-all flex items-center gap-1.5 self-start md:self-auto">
            <span class="material-symbols-outlined text-base">close</span>
            <span>Batal / Kembali</span>
        </a>
    </div>

    <!-- MAIN WORKSPACE SPLIT-SCREEN (VIEWER LEFT, ACTION PANEL RIGHT) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT: TOOLBAR + PDF VIEWER IFRAME -->
        <div class="lg:col-span-8 bg-white rounded-lg p-6 shadow-sm border border-[#e5dfd3] space-y-4 w-full">
            <!-- TOOLBAR TOP -->
            <div class="flex items-center justify-between border-b border-[#e5dfd3] pb-2 text-[10px] font-semibold text-on-surface">
                <div class="flex items-center space-x-2">
                    <span class="material-symbols-outlined text-red-600 text-sm">picture_as_pdf</span>
                    <span>Document Stream Player</span>
                </div>
                <div class="flex items-center space-x-2 text-[9px] text-on-surface-variant">
                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">visibility</span> Pratinjau Sah</span>
                    <a href="{{ route('reviewer.stream.file', $document->id) }}" target="_blank" class="flex items-center gap-1 hover:text-gold-500"><span class="material-symbols-outlined text-xs">open_in_new</span> Tab Baru</a>
                </div>
            </div>

            <!-- PDF VIEWPORT -->
            <div class="h-[650px] bg-canvas rounded-md overflow-hidden border border-[#e5dfd3] shadow-inner relative">
                <iframe src="{{ route('reviewer.stream.file', $document->id) }}#toolbar=0&navpanes=0&view=FitH" 
                        class="w-full h-full border-none" 
                        id="pdfViewerFrame">
                </iframe>
                <div class="absolute inset-0 bg-transparent pointer-events-none" oncontextmenu="return false;"></div>
            </div>
        </div>

        <!-- RIGHT: ACTION PANEL -->
        <div class="lg:col-span-4 w-full space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-[#e5dfd3] space-y-6">
                <!-- PANEL HEADER -->
                <div class="border-b border-[#e5dfd3] pb-3 space-y-1">
                    <div class="flex items-center space-x-2 text-gold-500">
                        <span class="material-symbols-outlined text-lg">gavel</span>
                        <h4 class="font-extrabold text-xs uppercase tracking-wider">Keputusan Peninjauan</h4>
                    </div>
                    <p class="text-[11px] text-on-surface-variant">Pilih tindakan persetujuan atau penolakan dokumen</p>
                </div>
                
                @if($document->status === 'need_revision')
                    <div class="p-5 bg-[#FFF9EE] border-l-4 border-amber-500 text-amber-900 rounded-r-md text-xs space-y-2.5 flex flex-col items-center text-center shadow-sm">
                        <span class="material-symbols-outlined text-4xl text-amber-500 animate-pulse">lock</span>
                        <h5 class="font-bold uppercase tracking-wider text-[11px]">Dokumen Ditangguhkan / Terkunci</h5>
                        <p class="text-on-surface-variant leading-relaxed">Salah satu reviewer meminta perbaikan (revisi) untuk dokumen ini. Proses persetujuan ditangguhkan sementara sampai Pembuat Dokumen mengunggah berkas revisi baru.</p>
                    </div>
                @else
                    <!-- DECISION FORM -->
                    <form id="review-form" method="POST" class="space-y-5">
                        @csrf
                        
                        <!-- DETAILS METADATA SUMMARY -->
                        <div class="p-3.5 bg-canvas rounded-md border border-[#e5dfd3] space-y-1.5 text-xs text-on-surface-variant">
                            <div class="flex justify-between">
                                <span>Role Peninjau:</span>
                                <strong class="text-on-surface uppercase">{{ Auth::user()->role }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>Status Antrean:</span>
                                <strong class="text-gold-500 uppercase">Waiting Review</strong>
                            </div>
                        </div>

                        <!-- NOTES TEXTAREA -->
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-on-surface-variant mb-1.5">Catatan / Instruksi Peninjau:</label>
                            <textarea 
                                name="notes" 
                                placeholder="Tulis instruksi perbaikan atau catatan persetujuan di sini..."
                                class="w-full bg-canvas border border-[#e5dfd3] rounded-md p-3.5 text-xs font-semibold text-on-surface outline-none focus:bg-white focus:ring-2 focus:ring-gold-500 transition-all placeholder-[#d6cebf] resize-none"
                                rows="5"></textarea>
                        </div>
                        
                        <!-- ACTION BUTTONS -->
                        <div class="space-y-3 pt-2">
                            <!-- Approve Button -->
                            <button type="submit" 
                                    id="approve-btn"
                                    formaction="{{ route('reviewer.approve', $document->id) }}"
                                    class="w-full py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-md font-bold uppercase text-xs tracking-wider shadow-md transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-base">check_circle</span>
                                <span>Setuju & Sahkan Digital</span>
                            </button>

                            <!-- Reject / Revision Button -->
                            <button type="submit" 
                                    id="reject-btn"
                                    formaction="{{ route('reviewer.reject', $document->id) }}"
                                    class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white rounded-md font-bold uppercase text-xs tracking-wider shadow-md transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-base">cancel</span>
                                <span>Kembalikan (Minta Revisi)</span>
                            </button>
                        </div>
                    </form>
                @endif

                <div class="pt-3 border-t border-[#e5dfd3] flex items-center space-x-2 text-[10px] text-on-surface-variant">
                    <span class="material-symbols-outlined text-base text-gold-500">verified</span>
                    <span>Sistem enkripsi e-QMS mengamankan dokumen ini.</span>
                </div>
            </div>

            <!-- DECISION HISTORY & NOTES CARD -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-[#e5dfd3] space-y-4">
                <div class="border-b border-[#e5dfd3] pb-3 space-y-1">
                    <div class="flex items-center space-x-2 text-charcoal-900">
                        <span class="material-symbols-outlined text-lg">history</span>
                        <h4 class="font-extrabold text-xs uppercase tracking-wider">Catatan & Riwayat Peninjauan</h4>
                    </div>
                    <p class="text-[11px] text-on-surface-variant">Lihat keputusan dan umpan balik dari pejabat lainnya</p>
                </div>

                <div class="space-y-3 max-h-[300px] overflow-y-auto custom-scrollbar">
                    @forelse($document->approvals as $approval)
                        @if($approval->user && $approval->stage !== 'creator')
                        <div class="p-3 bg-canvas rounded border border-[#e5dfd3]/60 space-y-1 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-on-surface text-[11px]">{{ $approval->user->full_name ?? $approval->user->username }}</span>
                                @if($approval->status === 'approved')
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[9px] font-bold uppercase">Disetujui</span>
                                @elseif($approval->status === 'rejected' || $approval->status === 'need_revision')
                                    <span class="px-2 py-0.5 bg-red-50 text-red-700 border border-red-200 rounded text-[9px] font-bold uppercase">Minta Revisi</span>
                                @elseif($approval->status === 'current')
                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded text-[9px] font-bold uppercase">Giliran Anda</span>
                                @else
                                    <span class="px-2 py-0.5 bg-gray-50 text-gray-500 border border-gray-200 rounded text-[9px] font-bold uppercase">Belum Giliran</span>
                                @endif
                            </div>
                            <div class="text-[10px] text-on-surface-variant uppercase font-bold">{{ $approval->user->role }}</div>
                            @if(filled($approval->notes))
                                <div class="mt-1.5 p-2 bg-white rounded border border-[#e5dfd3]/40 text-on-surface text-[11px] italic">
                                    "{{ $approval->notes }}"
                                </div>
                            @endif
                            @if($approval->processed_at)
                                <div class="text-[9px] text-on-surface-variant text-right">
                                    {{ date('d-m-Y H:i', strtotime($approval->processed_at)) }} WIB
                                </div>
                            @endif
                        </div>
                        @endif
                    @empty
                        <p class="text-xs text-on-surface-variant text-center">Belum ada riwayat persetujuan.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
