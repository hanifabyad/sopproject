@extends('layouts.reviewer')

@section('title', 'Peninjauan Digital - ' . $document->title)

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .eqms-scope {
        font-family: 'Poppins', sans-serif;
    }
</style>

<div class="p-8 eqms-scope">
    {{-- Header Modul Peninjauan --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-black text-[#1e293b] uppercase tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-file-shield text-blue-600"></i> PENINJAUAN DIGITAL
            </h2>
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Sistem Pengesahan Otomatis e-QMS Quality System</p>
        </div>
        <a href="{{ route('reviewer.dashboard') }}" class="bg-white border border-gray-200 text-gray-700 px-5 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-wider hover:bg-gray-50 transition-all duration-300 shadow-sm flex items-center gap-2 transform hover:-translate-y-0.5">
            <i class="fa-solid fa-xmark text-red-500"></i> Batal / Kembali
        </a>
    </div>

    {{-- Layout Utama Workspace (3 Kolom PDF : 1 Kolom Panel Aksi) --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        {{-- PREVIEW DRAF SOP UTUH (SISI KIRI - ANTI DOWNLOAD & PRINT) --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-file-pdf text-red-500 text-xs"></i> Lembar Verifikasi Dokumen Kerja
                    </h4>
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-md text-[8px] font-bold uppercase tracking-wider"><i class="fa-solid fa-eye mr-1"></i> Mode Pratinjau Sah</span>
                </div>

                <div class="rounded-xl overflow-hidden border border-gray-200 bg-slate-800 relative shadow-inner">
                    {{-- 🔐 AMANKAN DOKUMEN: Parameter #toolbar=0&navpanes=0&view=FitH dipasang ketat untuk menyembunyikan tombol download/print bawaan browser --}}
                    <iframe src="{{ route('reviewer.stream.file', $document->id) }}#toolbar=0&navpanes=0&view=FitH" 
                            class="w-full min-h-[750px] border-none" 
                            style="height: 75vh;"
                            id="pdfViewerFrame">
                    </iframe>
                    
                    {{-- Lapisan pelindung transparan di area luar iframe untuk memblokir klik kanan jahil --}}
                    <div class="absolute inset-0 bg-transparent pointer-events-none" oncontextmenu="return false;"></div>
                </div>
            </div>
        </div>

        {{-- PANEL KEPUTUSAN REVIEWER (SISI KANAN) --}}
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 sticky top-8 flex flex-col justify-between min-h-[350px]">
                <div class="w-full">
                    <h4 class="text-[10px] font-black uppercase mb-4 tracking-wider text-gray-400 border-b border-gray-100 pb-3 flex items-center gap-1.5">
                        <i class="fa-solid fa-gavel text-blue-500"></i> Keputusan Reviewer
                    </h4>
                    
                    <form id="review-form" method="POST" class="space-y-4">
                        @csrf
                        {{-- Input Catatan/Koreksi --}}
                        <div>
                            <label class="block text-[8px] font-bold uppercase text-gray-400 mb-1.5 tracking-wider">Catatan / Instruksi Tambahan:</label>
                            <textarea 
                                name="notes" 
                                placeholder="Tulis instruksi revisi atau catatan persetujuan Anda di sini..."
                                class="w-full bg-slate-50 border border-slate-100 rounded-xl p-3 text-xs font-medium text-gray-700 outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-300 resize-none shadow-inner"
                                rows="5"></textarea>
                        </div>
                        
                        {{-- Tombol Keputusan Semantik --}}
                        <div class="flex flex-col gap-2.5 pt-2">
                            {{-- Tombol Setuju & Sahkan (Hijau Mewah) --}}
                            <button type="submit" 
                                    formaction="{{ route('reviewer.approve', $document->id) }}"
                                    class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold uppercase text-[10px] tracking-wider shadow-md hover:shadow-emerald-100 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-file-signature text-xs"></i> Setuju & Sahkan Digital
                            </button>

                            {{-- Tombol Kembalikan / Tolak Revisi (Merah Berbahaya) --}}
                            <button type="submit" 
                                    formaction="{{ route('reviewer.reject', $document->id) }}"
                                    class="w-full py-3.5 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold uppercase text-[10px] tracking-wider shadow-md hover:shadow-red-100 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-rotate-left text-xs"></i> Kembalikan (Revisi)
                            </button>
                        </div>
                    </form>
                </div>
                
                {{-- Keterangan Otomatisasi Proteksi Bawah --}}
                <div class="mt-6 pt-3 border-t border-gray-50 flex items-center gap-2 text-gray-400">
                    <i class="fa-solid fa-shield-halved text-xs text-blue-500/70"></i>
                    <p class="text-[8px] font-semibold uppercase leading-relaxed tracking-wide">Enkripsi e-QMS mengamankan dokumen ini dari penyalinan data ilegal.</p>
                </div>
            </div>
        </div>

    </div>
</div>