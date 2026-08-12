@extends('layouts.reviewer')

@section('title', 'Riwayat Persetujuan')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .eqms-scope {
        font-family: 'Poppins', sans-serif;
    }
</style>

<div class="p-8 eqms-scope">
    {{-- Header Judul Dinamis --}}
    <div class="mb-8">
        <h2 class="text-2xl font-black text-[#1e293b] uppercase tracking-tight flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> Riwayat SOP Selesai
        </h2>
        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
            Arsip Rekam Jejak Validasi Mutu • Dept. {{ Auth::user()->department }}
        </p>
    </div>

    {{-- Container List Kardus Putih Modern --}}
    <div class="bg-white rounded-2xl shadow-md p-8 border border-gray-100 min-h-[450px]">
        <div class="space-y-4">
            @forelse($documents as $doc)
            <div class="flex items-center justify-between p-5 bg-gray-50 rounded-xl hover:bg-white hover:shadow-lg transition-all duration-300 border border-transparent hover:border-gray-100 group">
                <div class="flex items-center space-x-4">
                    {{-- Ikon Arsip Berkas Sah Baru (Upgrade Visual ✨) --}}
                    <div class="w-12 h-12 bg-white text-blue-600 rounded-xl flex items-center justify-center text-lg shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                        <i class="fa-solid fa-file-circle-check"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-[#1e293b] text-sm uppercase tracking-tight group-hover:text-blue-600 transition-colors duration-300leading-snug">{{ $doc->title }}</h3>
                        <div class="flex items-center space-x-2.5 mt-1.5">
                            {{-- Soft Badge Status Definition --}}
                            @if($doc->status == 'active')
                                <span class="bg-emerald-50 border border-emerald-100 text-emerald-600 text-[8px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider">
                                    <i class="fa-solid fa-circle-check mr-0.5"></i> Disetujui
                                </span>
                            @else
                                <span class="bg-amber-50 border border-amber-100 text-amber-600 text-[8px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider">
                                    <i class="fa-solid fa-triangle-exclamation mr-0.5"></i> Revisi
                                </span>
                            @endif
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wide">
                                <i class="fa-regular fa-calendar-check mr-0.5"></i> Selesai: {{ $doc->updated_at->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Tombol Lihat Catatan Panel Kanan --}}
                <div class="text-right flex flex-col items-end justify-center min-w-[150px]">
                    @php
                        $myApprovalData = \App\Models\DocumentApproval::where('document_id', $doc->id)
                                            ->where('user_id', Auth::id())
                                            ->first();
                        $myNotes = $myApprovalData ? $myApprovalData->notes : '-';
                    @endphp

                    @if(!empty($myNotes) && $myNotes !== '-')
                        <button onclick="openNotesModal('{{ $doc->title }}', '{{ e($myNotes) }}')" 
                                class="px-4 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-blue-600 hover:text-white hover:border-blue-600 rounded-xl text-[9px] font-bold uppercase tracking-wider transition-all duration-300 shadow-sm flex items-center gap-1.5 group">
                            <i class="fa-regular fa-comment-dots text-sm text-blue-500 group-hover:text-white transition-colors"></i> 
                            Lihat Catatan
                        </button>
                    @else
                        <span class="text-[9px] font-bold text-gray-300 uppercase tracking-wider italic bg-gray-100 px-3 py-1.5 rounded-xl border border-gray-50">
                            Tanpa Catatan
                        </span>
                    @endif
                </div>
            </div>
            @empty
            {{-- Empty State Riwayat Kosong --}}
            <div class="py-16 flex flex-col items-center justify-center text-center px-6">
                <div class="w-20 h-20 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center text-3xl mb-4 text-gray-300 animate-pulse shadow-inner">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <h5 class="text-sm font-bold text-[#1e293b] uppercase tracking-wide">Belum Ada Riwayat Peninjauan</h5>
                <p class="text-[10px] text-gray-400 font-medium max-w-sm mt-1 leading-relaxed lowercase">seluruh berkas dokumen yang telah anda tindak lanjuti (approve/reject) akan terekam otomatis ke dalam papan kontrol ini.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- 💬 MODAL POP-UP UNTUK MELIHAT CATATAN REVIEW (UKURAN LEBIH LEGA & PROSEDURAL) --}}
<div id="notesModal" class="fixed inset-0 z-[110] hidden bg-[#1e293b]/80 flex items-center justify-center p-4 backdrop-blur-sm transition-all duration-300">
    <div class="bg-white rounded-3xl w-full max-w-xl p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300 flex flex-col">
        
        <div class="flex justify-between items-center border-b border-gray-100 pb-5 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-lg shadow-sm">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-[#1e293b] uppercase tracking-wide">Catatan Tinjauan Anda</h3>
                    <p id="modalDocTitle" class="text-[11px] text-gray-400 font-bold uppercase tracking-wider mt-0.5 truncate max-w-[350px]"></p>
                </div>
            </div>
            <button onclick="closeNotesModal()" class="text-gray-400 hover:text-red-500 text-3xl transition-colors font-semibold" style="line-height: 0">&times;</button>
        </div>
        
        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 min-h-[120px] max-h-80 overflow-y-auto shadow-inner">
            <p id="modalNotesContent" class="text-sm font-medium text-gray-700 leading-relaxed whitespace-pre-line"></p>
        </div>
        
        <div class="pt-5 mt-3 flex justify-end">
            <button onclick="closeNotesModal()" class="px-6 py-3 bg-[#1e293b] text-white rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-blue-600 transition-all duration-300 shadow-md hover:shadow-blue-100">
                Tutup Jendela
            </button>
        </div>
    </div>
</div>

{{-- 📜 JAVASCRIPT ANIMASI MODAL DRIVER --}}
<script>
    function openNotesModal(docTitle, notesText) {
        document.getElementById('modalDocTitle').innerText = docTitle;
        document.getElementById('modalNotesContent').innerText = notesText;
        
        const modal = document.getElementById('notesModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeNotesModal() {
        const modal = document.getElementById('notesModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Dukungan Tombol ESC Keyboard untuk Menutup Jendela
    window.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeNotesModal();
        }
    });
</script>
@endsection