@extends('layouts.admin')

@section('title', 'Audit Trail - ' . $document->title)
@section('header_title', 'Audit Trail & Detail Dokumen BU')

@section('content')
<div class="space-y-6">
    
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.BU.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-[#cfc6ac] bg-white text-[#4d4633] hover:bg-[#f7f6f2] hover:text-[#1e1c14] text-xs font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <span class="text-[#cfc6ac]">|</span>
            <div class="flex items-center gap-2 text-xs text-[#4d4633]">
                <a href="{{ route('admin.BU.index') }}" class="hover:text-[#705d00]">Business Unit</a>
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

    <!-- MAIN GRID 2 COLS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- PDF PREVIEW PLAYER (8 COLS) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 shadow-sm border border-[#e5dfd3] space-y-4">
            <div class="flex items-center justify-between border-b border-[#e5dfd3] pb-3">
                <div class="flex items-center space-x-2 text-xs font-bold text-[#1e1c14]">
                    <span class="material-symbols-outlined text-red-600">picture_as_pdf</span>
                    <span>Preview Dokumen SOP Terintegrasi</span>
                </div>
            </div>
            
            <div class="rounded-2xl overflow-hidden border border-[#e5dfd3] bg-[#f7f6f2] shadow-inner">
                @php
                    $pathToShow = $document->file_final ?? $document->file_preview ?? $document->file_lp;
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
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-[#e5dfd3] space-y-4">
                <div class="border-b border-[#e5dfd3] pb-3 flex items-center space-x-2">
                    <span class="material-symbols-outlined text-[#705d00] text-lg">history</span>
                    <h3 class="text-xs font-extrabold text-[#1e1c14] uppercase tracking-wider">Timeline Persetujuan</h3>
                </div>
                
                <div class="space-y-4 relative pl-2">
                    @forelse($document->logs as $log)
                    <div class="relative pl-6 border-l-2 {{ $log->action == 'revisi' ? 'border-red-500' : ($log->action == 'transfer' ? 'border-purple-500' : 'border-[#705d00]') }} pb-3 space-y-1">
                        <div class="absolute -left-[7px] top-0 w-3 h-3 rounded-full {{ $log->action == 'revisi' ? 'bg-red-500' : ($log->action == 'transfer' ? 'bg-purple-500' : 'bg-[#705d00]') }} border-2 border-white"></div>
                        
                        <p class="text-xs font-bold text-[#1e1c14]">
                            {{ $log->user->username }} 
                            <span class="text-[#4d4633] font-normal text-[11px]">({{ $log->user->role }})</span>
                        </p>
                        
                        <div class="p-3 bg-[#f7f6f2] rounded-xl border border-[#e5dfd3] text-xs text-[#4d4633] font-medium leading-relaxed">
                            "{{ $log->notes ?? 'tidak ada catatan tinjauan khusus' }}"
                        </div>
                        <p class="text-[10px] text-[#4d4633] font-semibold">{{ $log->created_at->format('d M Y - H:i') }} WIB</p>
                    </div>
                    @empty
                    <div class="text-center py-6 text-[#4d4633]">
                        <span class="material-symbols-outlined text-3xl text-[#d6cebf]">folder_open</span>
                        <p class="text-xs font-bold mt-1">Belum ada riwayat aktivitas alur.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- TRANSFER CARD -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-[#e5dfd3] space-y-4">
                <div class="flex items-center space-x-2 text-xs font-bold text-[#1e1c14] border-b border-[#e5dfd3] pb-3">
                    <span class="material-symbols-outlined text-[#705d00]">swap_horiz</span>
                    <h3 class="uppercase tracking-wider">Oper Kendali Manual</h3>
                </div>

                <form action="{{ route('admin.BU.transfer', $document->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-[#4d4633] mb-1.5">Pilih Reviewer Target Baru:</label>
                        <select name="reviewer_id" class="w-full p-3 bg-[#f7f6f2] border border-[#e5dfd3] rounded-2xl text-xs font-bold text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none" required>
                            @foreach($reviewers as $rev)
                                <option value="{{ $rev->id }}">{{ $rev->username }} ({{ $rev->role }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-[#333028] text-[#ffe16e] hover:bg-black rounded-2xl font-bold text-xs uppercase tracking-wider shadow-md transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">send</span>
                        <span>Transfer Kendali Dokumen</span>
                    </button>
                </form>
            </div>

            <!-- NEED REVISION ACTION CARD -->
            @if($document->status === 'need_revision')
                <div class="p-5 bg-red-50 border border-red-200 rounded-3xl space-y-3">
                    <div class="flex items-center gap-2 text-red-700 font-bold text-xs">
                        <span class="material-symbols-outlined text-base">warning</span>
                        <span>Atensi Perbaikan Berkas</span>
                    </div>
                    <p class="text-xs text-red-600 leading-relaxed">
                        Silakan pelajari catatan penolakan pada log timeline di atas, kemudian klik tombol di bawah untuk mengunggah draf perbaikan.
                    </p>
                    <a href="{{ route('admin.BU.edit_revision', $document->id) }}" 
                       class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-2xl shadow-md transition-all text-xs uppercase tracking-wider flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">build</span>
                        <span>Perbaiki & Upload Revisi</span>
                    </a>
                </div>
            @endif

            <!-- ACTION FOOTER CARD -->
            <div class="bg-white rounded-3xl p-4 shadow-sm border border-[#e5dfd3] space-y-3">
                @if($document->status == 'active' && $document->file_final)
                    <a href="{{ asset('storage/' . $document->file_final) }}" 
                       download 
                       class="w-full py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-2xl font-bold text-xs uppercase tracking-wider shadow-md transition-all text-center flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">download</span>
                        <span>Unduh Dokumen Sah Final</span>
                    </a>
                @endif

                <form action="{{ route('admin.BU.document.delete', $document->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini secara permanen dari server database?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2.5 text-red-600 hover:bg-red-50 rounded-2xl font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-base">delete</span>
                        <span>Hapus Dokumen Dari Server</span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection