@extends('layouts.admin')

@section('title', 'Support - Total SOP')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .eqms-scope {
        font-family: 'Poppins', sans-serif;
    }
</style>

<div class="p-8 eqms-scope">
    {{-- Header Utama --}}
    <div class="flex items-center space-x-3 mb-8">
        <div class="bg-[#1e293b] p-2.5 rounded-xl text-white shadow-md">
            <i class="fa-solid fa-folder-open text-lg"></i>
        </div>
        <div>
            <h2 class="text-2xl font-black text-[#1e293b] uppercase tracking-tight">SUPPORT - TOTAL DOKUMEN SOP</h2>
            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Monitoring Dokumen Support Department e-QMS</p>
        </div>
    </div>

    {{-- Grid Kartu Support Ringkas (3 Kolom Seimbang) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($stats as $dept => $data)
        <a href="{{ route('admin.support.show', $dept) }}" 
           class="group relative bg-[#1e293b] rounded-2xl p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:shadow-xl overflow-hidden border border-transparent hover:border-gray-100 flex flex-col justify-between min-h-[170px]">
            
            {{-- Background Glow Dekoratif Minimalis --}}
            <div class="absolute -right-10 -top-10 bg-white/5 w-32 h-32 rounded-full group-hover:bg-[#1e293b]/5 transition-all duration-300"></div>

            <div class="relative z-10 w-full">
                {{-- Bagian Atas Card --}}
                <div class="flex items-start justify-between mb-4">
                    {{-- Ikon Identitas Kontainer --}}
                    <div class="bg-white/10 p-3.5 rounded-xl text-white group-hover:bg-[#1e293b]/5 group-hover:text-[#1e293b] transition-colors duration-300 text-lg flex items-center justify-center w-11 h-11">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>

                    {{-- Angka Total Dokumen Ringkas --}}
                    <div class="text-right">
                        <span class="text-4xl font-black text-white group-hover:text-[#1e293b] transition-all duration-300 block leading-none">
                            {{ $data['total'] }}
                        </span>
                        <span class="text-[8px] font-bold text-white/40 group-hover:text-[#1e293b]/40 uppercase tracking-wider block mt-1">Total SOP</span>
                    </div>
                </div>

                {{-- Bagian Tengah: Nama Departemen Support --}}
                <div class="mb-4">
                    <h3 class="text-lg font-extrabold text-white group-hover:text-[#1e293b] uppercase tracking-tight transition-colors duration-300">
                        {{ $dept }} DEPT.
                    </h3>
                </div>

                {{-- Bagian Bawah: Statistik Ringkas Horizontal --}}
                <div class="flex items-center space-x-5 border-t border-white/10 group-hover:border-gray-100 mt-4 pt-4 transition-colors duration-300">
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-green-400 block shadow-sm shadow-green-200"></span>
                        <p class="text-[10px] font-bold text-white/60 group-hover:text-gray-500 uppercase tracking-wide">
                            Aktif: <span class="font-black text-white group-hover:text-green-600 ml-0.5">{{ $data['active'] }}</span>
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-red-400 block shadow-sm shadow-red-200"></span>
                        <p class="text-[10px] font-bold text-white/60 group-hover:text-gray-500 uppercase tracking-wide">
                            Revisi: <span class="font-black text-white group-hover:text-red-500 ml-0.5">{{ $data['inactive'] }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection