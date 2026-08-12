@extends('layouts.admin')

@section('title', 'Audit Trail - ' . $document->title)

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght=300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .eqms-scope {
        font-family: 'Poppins', sans-serif;
    }
</style>

<div class="p-8 eqms-scope">
    {{-- Header Ringkasan Status Dokumen --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- Identitas Utama Dokumen --}}
        <div class="bg-white rounded-2xl shadow-sm p-5 flex items-center justify-between border border-gray-100 md:col-span-2">
            <div class="flex items-center space-x-4">
                <div class="bg-[#1e293b] w-12 h-12 rounded-xl flex items-center justify-center text-white text-lg shadow-md">
                    <i class="fa-solid fa-file-shield"></i>
                </div>
                <div>
                    <h3 class="font-black text-[#1e293b] uppercase leading-tight text-sm tracking-tight">{{ $document->title }}</h3>
                    <span class="text-[9px] text-gray-400 font-bold uppercase tracking-wider block mt-0.5"><i class="fa-solid fa-charging-station text-[8px] text-gray-300 mr-0.5"></i> Unit: {{ $document->department }}</span>
                </div>
            </div>
            
            <a href="{{ route('admin.BU.index') }}" class="text-[9px] font-bold text-gray-400 uppercase tracking-wider bg-slate-50 border border-slate-100 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i> List BU
            </a>
        </div>

        {{-- Badge Status Terkini Sistem --}}
        <div class="bg-white rounded-2xl p-5 flex items-center justify-center shadow-sm border border-gray-100 relative overflow-hidden">
            @if($document->status === 'waiting')
                <span class="inline-flex items-center px-4 py-2 bg-amber-50 border border-amber-100 text-amber-600 rounded-xl text-[10px] font-black uppercase tracking-wider shadow-sm">
                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-2 animate-pulse"></span> Waiting Review
                </span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-amber-400"></div>
            @elseif($document->status === 'need_revision')
                <span class="inline-flex items-center px-4 py-2 bg-red-50 border border-red-100 text-red-500 rounded-xl text-[10px] font-black uppercase tracking-wider shadow-sm">
                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-2"></span> Need Revision
                </span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-red-500"></div>
            @elseif($document->status === 'active')
                <span class="inline-flex items-center px-4 py-2 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-xl text-[10px] font-black uppercase tracking-wider shadow-sm">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-2 animate-pulse"></span> Active / Approved
                </span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-400"></div>
            @else
                <span class="inline-flex items-center px-4 py-2 bg-gray-50 border border-gray-200 text-gray-500 rounded-xl text-[10px] font-black uppercase tracking-wider shadow-sm">
                    {{ $document->status }}
                </span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-400"></div>
            @endif
        </div>
    </div>

    {{-- Layout Utama Pembagian Kerja Area Dua Kolom --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- INTERFACE IFRAME PREVIEW DOKUMEN (KOLOM KIRI) --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
                <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-file-pdf text-red-500 text-xs"></i> Preview Dokumen SOP Terintegrasi
                    </h4>
                </div>
                
                <div class="rounded-xl overflow-hidden border border-gray-200 bg-slate-100 shadow-inner">
                    @php
                        $pathToShow = $document->file_final ?? $document->file_preview ?? $document->file_lp;
                    @endphp
                    <iframe src="{{ asset('storage/' . $pathToShow) }}#toolbar=0&navpanes=0" 
                            class="w-full min-h-[750px] border-none" 
                            style="height: 75vh;">
                    </iframe>
                </div>
            </div>
        </div>

        {{-- AUDIT TRAIL LOG TIMELINE & ACTIONS PANEL (KOLOM KANAN) --}}
        <div class="space-y-6">
            
            {{-- Card Timeline Pelacakan Stempel Persetujuan Berantai --}}
            <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
                <h4 class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-6 border-b border-gray-100 pb-3 flex items-center gap-1.5">
                    <i class="fa-solid fa-timeline text-blue-500"></i> Timeline Persetujuan (Audit Trail)
                </h4>
                
                <div class="relative space-y-6 pl-1">
                    @forelse($document->logs as $log)
                    <div class="relative pl-6 border-l-2 {{ $log->action == 'revisi' ? 'border-red-400' : ($log->action == 'transfer' ? 'border-purple-400' : 'border-blue-500') }} pb-2">
                        <div class="absolute -left-[7px] top-0 w-3 h-3 rounded-full {{ $log->action == 'revisi' ? 'bg-red-500' : ($log->action == 'transfer' ? 'bg-purple-500' : 'bg-blue-600') }} border-2 border-white shadow-sm"></div>
                        
                        <p class="text-[11px] font-black text-[#1e293b] uppercase tracking-tight flex flex-wrap items-center gap-1">
                            <i class="fa-regular fa-user text-[10px] text-gray-400"></i> {{ $log->user->username }} 
                            <span class="text-gray-400 font-medium text-[9px] lowercase">({{ $log->user->role }})</span>
                        </p>
                        
                        <div class="mt-1.5 p-3 bg-gray-50 rounded-xl border border-gray-100/70 shadow-inner">
                            <p class="text-[10px] font-semibold text-gray-500 leading-relaxed whitespace-pre-line lowercase">
                                "{{ $log->notes ?? 'tidak ada catatan tinjauan khusus' }}"
                            </p>
                        </div>
                        <p class="text-[8px] text-gray-400 font-bold mt-1.5 uppercase tracking-wide"><i class="fa-regular fa-clock mr-0.5"></i> {{ $log->created_at->format('d M Y - H:i') }} WIB</p>
                    </div>
                    @empty
                    <div class="text-center py-6 opacity-30 flex flex-col items-center">
                        <i class="fa-solid fa-folder-open text-2xl mb-2"></i>
                        <p class="text-[10px] font-bold uppercase tracking-wider">Belum ada riwayat aktivitas alur.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Card Oper Kendali Mutu (Estafet Berantai) --}}
            <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 relative overflow-hidden group">
                <div class="flex items-center space-x-2.5 mb-4 border-b border-gray-100 pb-3">
                    <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-sm shadow-inner">
                        <i class="fa-solid fa-shuffle"></i>
                    </div>
                    <h4 class="text-[10px] font-black uppercase tracking-wider text-[#1e293b]">Oper Kendali (Estafet Manual)</h4>
                </div>

                <form action="{{ route('admin.BU.transfer', $document->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[8px] font-bold uppercase text-gray-400 mb-1.5 tracking-wider">Pilih Peninjau Target Baru:</label>
                        <select name="reviewer_id" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl text-xs font-bold text-[#1e293b] focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all cursor-pointer">
                            @foreach($reviewers as $rev)
                                <option value="{{ $rev->id }}">{{ $rev->username }} ({{ $rev->role }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-[#1e293b] text-white rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all duration-300 shadow-sm hover:shadow-blue-100">
                        <i class="fa-solid fa-paper-plane mr-1"></i> Transfer Kendali Dokumen
                    </button>
                </form>
            </div>

            {{-- Tombol Khusus Penanganan Perbaikan Berkas Revisi --}}
            @if($document->status === 'need_revision')
                <div class="p-5 bg-red-50 border border-red-100 rounded-2xl flex flex-col gap-2 shadow-sm">
                    <p class="text-[10px] text-red-700 font-bold uppercase tracking-wide flex items-center gap-1">
                        <i class="fa-solid fa-triangle-exclamation text-xs text-red-500"></i> Atensi Koreksi Perbaikan Berkas
                    </p>
                    <p class="text-[11px] text-red-600/80 font-medium leading-relaxed lowercase pl-4">
                        silakan pelajari catatan penolakan pimpinan pada log timeline di atas, kemudian klik tombol di bawah untuk mengunggah draf perbaikan berkas.
                    </p>
                    <a href="{{ route('admin.BU.edit_revision', $document->id) }}" 
                       class="w-full inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl shadow-md hover:shadow-red-200 transition-all duration-300 text-[10px] uppercase tracking-widest gap-1.5 transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-wrench"></i> Perbaiki & Upload Revisi
                    </a>
                </div>
            @endif

            {{-- CONTROL PANEL AKHIR (DOWNLOAD & DELETE FLUID) --}}
            <div class="bg-white rounded-2xl shadow-md p-4 border border-gray-100 flex flex-col gap-3">
                {{-- Tombol Cetak/Unduh Dokumen Final --}}
                @if($document->status == 'active' && $document->file_final)
                    <a href="{{ asset('storage/' . $document->file_final) }}" 
                       download 
                       class="w-full block py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 shadow-md shadow-emerald-100 text-center transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-file-arrow-down text-xs mr-1"></i> Unduh Dokumen Sah Final
                    </a>
                @endif

                {{-- Tombol Pembersihan Berkas / Hapus Permanen --}}
                <form action="{{ route('admin.BU.document.delete', $document->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini secara permanen dari server database?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full flex items-center justify-center space-x-1.5 text-gray-400 hover:text-red-500 transition-colors py-2 text-[9px] font-bold uppercase tracking-wider group">
                        <i class="fa-solid fa-trash-can group-hover:animate-bounce"></i>
                        <span>Hapus Dokumen Dari Server</span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection