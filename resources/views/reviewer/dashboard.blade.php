@extends('layouts.reviewer')

@section('title', 'Antrean Review')
@section('header_title', 'Daftar Antrean Review SOP')

@section('content')
<div class="space-y-6">
    
    <!-- HEADER WELCOME CARD (Industrial Crisp Banner) -->
    <div class="bg-[#333028] text-[#eee8db] rounded-lg p-6 shadow-md relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6 border border-black/20">
        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-[#ffe16e] text-[#333028] mb-2.5">
                <span class="material-symbols-outlined text-xs">assignment_turned_in</span> Reviewer Workspace
            </span>
            <h2 class="text-xl md:text-2xl font-extrabold text-white tracking-tight uppercase">
                SOP Menunggu Review Anda
            </h2>
            <p class="text-xs text-[#eee8db]/70 mt-1 max-w-xl">
                Daftar dokumen mutu yang memerlukan pemeriksaan, catatan revisi, atau pengesahan tanda tangan digital dari Anda.
            </p>
        </div>

        <div class="relative z-10 flex items-center space-x-2 bg-white/10 px-3.5 py-2 rounded-md border border-white/15 text-xs font-semibold text-[#ffe16e]">
            <span class="w-2 h-2 rounded-full bg-[#ffe16e] animate-ping"></span>
            <span>Sistem Siaga Review</span>
        </div>
    </div>

    <!-- SEMANTIC DATA TABLE: LIST ANTREAN REVIEW -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 min-h-[400px] space-y-4">
        <div class="flex items-center justify-between border-b border-[#cfc6ac]/40 pb-4">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold">
                    <span class="material-symbols-outlined text-lg">pending_actions</span>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-[#1e1c14] uppercase tracking-wider">Antrean Persetujuan Active</h3>
                    <p class="text-[11px] text-[#4d4633]">Pilih dokumen untuk membuka pratinjau PDF dan memberikan keputusan</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto border border-[#cfc6ac]/60 rounded-md">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#eee8db] border-b border-[#cfc6ac] text-[11px] font-bold uppercase tracking-wider text-[#4d4633]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Judul Dokumen SOP</th>
                        <th class="py-3 px-4">Departemen / Unit</th>
                        <th class="py-3 px-4">Tanggal Pengajuan</th>
                        <th class="py-3 px-4 text-center">Aksi Tinjauan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e8e2d6] text-xs font-semibold text-[#1e1c14]">
                    @forelse($documents as $doc)
                    <tr class="border-b border-[#e8e2d6] hover:bg-[#f7f6f2] transition-colors">
                        <td class="py-3.5 px-4 text-center font-bold text-[#705d00]">{{ $loop->iteration }}</td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center space-x-3">
                                <span class="material-symbols-outlined text-red-600 text-lg flex-shrink-0">description</span>
                                <span class="font-bold text-[#1e1c14] uppercase hover:text-[#705d00] transition-colors">{{ $doc->title }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 bg-[#f7f6f2] border border-[#cfc6ac]/60 rounded-md text-[10px] font-bold uppercase text-[#333028]">
                                {{ $doc->department }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-[#4d4633] text-[11px]">
                            {{ $doc->created_at->format('d M Y - H:i') }} WIB
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('reviewer.show', $doc->id) }}" 
                               class="inline-flex items-center justify-center bg-[#333028] hover:bg-black text-[#ffe16e] px-4 py-2 rounded-md font-bold uppercase text-[10px] tracking-wider transition-all gap-1.5 shadow-sm">
                                <span>Review</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center text-[#4d4633]">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <span class="material-symbols-outlined text-4xl text-[#705d00]">task_alt</span>
                                <h5 class="text-xs font-bold text-[#1e1c14] uppercase tracking-wide">Semua Tugas Review Selesai</h5>
                                <p class="text-[11px] text-[#4d4633] max-w-sm">
                                    Saat ini belum ada antrean dokumen yang memerlukan pemeriksaan atau penandatanganan digital Anda.
                                </p>
                                <a href="{{ route('library.index') }}" class="mt-2 px-4 py-2 bg-[#f7f6f2] border border-[#cfc6ac] rounded-md text-[10px] font-bold uppercase tracking-wider text-[#333028] hover:bg-[#fff9ed] transition-all">
                                    Buka E-Library
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection