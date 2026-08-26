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
        <div class="lg:col-span-4 w-full">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-[#e5dfd3] space-y-6">
                <!-- PANEL HEADER -->
                <div class="border-b border-[#e5dfd3] pb-3 space-y-1">
                    <div class="flex items-center space-x-2 text-gold-500">
                        <span class="material-symbols-outlined text-lg">gavel</span>
                        <h4 class="font-extrabold text-xs uppercase tracking-wider">Keputusan Peninjauan</h4>
                    </div>
                    <p class="text-[11px] text-on-surface-variant">Pilih tindakan persetujuan atau penolakan dokumen</p>
                </div>
                
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

                <div class="pt-3 border-t border-[#e5dfd3] flex items-center space-x-2 text-[10px] text-on-surface-variant">
                    <span class="material-symbols-outlined text-base text-gold-500">verified</span>
                    <span>Sistem enkripsi e-QMS mengamankan dokumen ini.</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
