@extends('layouts.reviewer')

@section('title', 'Daftar Antrian Review')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .eqms-scope {
        font-family: 'Poppins', sans-serif;
    }
</style>

<div class="p-8 eqms-scope">
    {{-- Header Halaman Utama Pimpinan --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-[#1e293b] uppercase tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-file-signature text-blue-600"></i> SOP Menunggu Review
            </h2>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
                Portal Otorisasi Dokumen Mutu • {{ Auth::user()->role ?? Auth::user()->username }}
            </p>
        </div>
        <div class="px-3 py-1.5 bg-blue-50 border border-blue-100/50 text-blue-600 rounded-xl text-[9px] font-bold uppercase tracking-wider animate-pulse flex items-center gap-1.5 h-fit w-fit">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 block"></span> Sistem Siaga
        </div>
    </div>

    {{-- Container Box Berkas Konten Utama --}}
    <div class="bg-white rounded-2xl shadow-md p-8 md:p-10 border border-gray-100 min-h-[450px] transition-all duration-300">
        <div class="space-y-4">
            {{-- Loop data dokumen yang dikirim khusus ke reviewer_id login --}}
            @forelse($documents as $doc)
            <div class="flex items-center justify-between p-5 bg-gray-50 rounded-xl hover:bg-white hover:shadow-lg transition-all duration-300 border border-transparent hover:border-gray-100 group">
                <div class="flex items-center space-x-4">
                    {{-- Ikon Status File Interaktif --}}
                    <div class="w-12 h-12 bg-white text-[#1e293b] rounded-xl flex items-center justify-center text-lg shadow-sm group-hover:bg-[#1e293b] group-hover:text-white transition-all duration-300">
                        <i class="fa-regular fa-file-lines"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-[#1e293b] text-sm uppercase tracking-tight leading-snug group-hover:text-blue-600 transition-colors duration-300">{{ $doc->title }}</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wide mt-1 flex items-center gap-1">
                            <span class="text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md"><i class="fa-solid fa-building text-[8px] mr-0.5"></i> {{ $doc->department }}</span> 
                            • Diajukan: {{ $doc->created_at->format('d M Y') }}
                        </p>
                    </div>
                </div>

                {{-- Tombol Menuju Halaman Review Dokumen --}}
                <a href="{{ route('reviewer.show', $doc->id) }}" 
                   class="bg-[#1e293b] text-white px-6 py-3 rounded-xl font-bold uppercase text-[10px] tracking-wider shadow-md hover:bg-blue-600 hover:shadow-blue-100 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center gap-1.5">
                    Review Dokumen <i class="fa-solid fa-chevron-right text-[8px]"></i>
                </a>
            </div>
            @empty
            {{-- 🔥 TAMPILAN ANTRIAN KOSONG PREMIUM BARU (MENGGANTI YANG LAMA) --}}
            <div class="py-16 flex flex-col items-center justify-center text-center px-6">
                <div class="w-20 h-20 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center text-3xl mb-4 text-gray-300 animate-pulse shadow-inner">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <h5 class="text-sm font-bold text-[#1e293b] uppercase tracking-wide">Semua Tugas Review Selesai</h5>
                <p class="text-[10px] text-gray-400 font-medium max-w-sm mt-1 leading-relaxed lowercase">belum ada antrian berkas atau ajukan SOP baru yang memerlukan validasi tanda tangan digital anda untuk saat ini.</p>
                
                <div class="mt-6 flex gap-3">
                    <a href="{{ route('library.index') }}" class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-[9px] font-bold uppercase tracking-wider text-gray-500 hover:bg-gray-100 transition-all shadow-sm">
                        <i class="fa-solid fa-book-bookmark mr-1.5 text-blue-500"></i> Buka E-Library
                    </a>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection