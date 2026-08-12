@extends('layouts.admin')

@section('title', 'Riwayat Audit SOP')

@section('content')
<style>
    /* Styling scrollbar agar tipis dan cantik */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<div class="p-8">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-3xl font-black text-[#1e293b] uppercase tracking-tighter">Audit Trail Dokumen</h2>
        <a href="{{ route('admin.BU.show', $document->department) }}" class="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-[#1e293b]">← Kembali ke Daftar</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        {{-- SISI KIRI: Preview Dokumen --}}
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white p-10 rounded-[3rem] shadow-xl border border-gray-100">
                <div class="flex items-center space-x-6 mb-8 border-b pb-8">
                    <div class="bg-[#1e293b] p-5 rounded-3xl text-white shadow-lg">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-[#1e293b] uppercase tracking-tight">{{ $document->title }}</h3>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Status Saat Ini: 
                            <span class="{{ $document->status == 'active' ? 'text-green-500' : 'text-yellow-500' }}">
                                {{ strtoupper($document->status == 'active' ? 'APPROVED' : 'WAITING REVIEW') }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="aspect-[16/9] bg-gray-100 rounded-[2rem] overflow-hidden border-4 border-gray-50 shadow-inner">
                    <iframe src="{{ asset('storage/' . $pathFinal) }}#toolbar=0" class="w-full h-full border-none"></iframe>
                </div>
            </div>
        </div>

        {{-- SISI KANAN: Sidebar --}}
        <div class="flex flex-col space-y-6">
            
            {{-- 1. Timeline Persetujuan dengan SCROLL --}}
            <div class="bg-white p-8 rounded-[2rem] shadow-lg border border-gray-50 flex flex-col" style="height: 500px;">
                <h4 class="text-sm font-black text-[#1e293b] uppercase mb-6 tracking-widest border-b pb-4 flex-none">Timeline Persetujuan</h4>
                
                <div class="custom-scrollbar overflow-y-auto pr-3 flex-grow">
                    <div class="space-y-10 relative">
                        {{-- Garis vertikal timeline --}}
                        <div class="absolute left-[15px] top-2 bottom-2 w-0.5 bg-gray-100"></div>

                        {{-- Point 1: Dokumen Diunggah --}}
                        <div class="relative flex items-start space-x-6">
                            <div class="relative z-10 bg-blue-600 w-8 h-8 rounded-full flex items-center justify-center text-white ring-4 ring-white shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs font-black text-[#1e293b] uppercase">Dokumen Diunggah</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">{{ $document->created_at->format('d M Y - H:i') }} WIB</p>
                            </div>
                        </div>

                        {{-- Loop Log Aktivitas --}}
                        @foreach($document->logs as $log)
                        <div class="relative flex items-start space-x-6">
                            @php
                                $colorClass = 'bg-red-500';
                                if($log->action == 'active') $colorClass = 'bg-green-500';
                                if($log->action == 'transfer') $colorClass = 'bg-yellow-400';
                            @endphp
                            
                            <div class="relative z-10 {{ $colorClass }} w-8 h-8 rounded-full flex items-center justify-center text-white ring-4 ring-white shadow-md text-[10px] font-bold">
                                {{ $loop->iteration + 1 }}
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-black text-[#1e293b] uppercase">
                                    {{ $log->user->username }} <span class="text-[9px] text-gray-400">({{ $log->user->role }})</span>
                                </p>
                                <div class="mt-3 p-4 {{ $log->action == 'active' ? 'bg-green-50' : ($log->action == 'transfer' ? 'bg-yellow-50' : 'bg-red-50') }} rounded-2xl border-l-4 {{ $log->action == 'active' ? 'border-green-500' : ($log->action == 'transfer' ? 'border-yellow-400' : 'border-red-500') }} shadow-sm">
                                    <p class="text-xs text-[#1e293b] font-medium leading-relaxed italic">"{{ $log->notes }}"</p>
                                </div>
                                <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase tracking-tighter">{{ $log->created_at->format('d M Y - H:i') }} WIB</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 2. FITUR ESTAFET --}}
            <div class="bg-[#1e293b] p-8 rounded-[2rem] shadow-2xl text-white flex-none">
                <h4 class="text-[10px] font-black uppercase mb-4 tracking-[0.2em] text-blue-400">Oper Kendali (Estafet)</h4>
                <form action="{{ route('admin.BU.transfer', $document->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-[9px] font-bold uppercase text-gray-400 mb-2">Pilih Target Peninjau Berikutnya:</label>
                        <select name="reviewer_id" class="w-full p-4 bg-gray-800 rounded-2xl border-none focus:ring-2 focus:ring-blue-500 font-bold text-xs text-white" required>
                            <option value="">-- Pilih Peninjau --</option>
                            @foreach($reviewers as $user)
                                <option value="{{ $user->id }}" {{ $document->reviewer_id == $user->id ? 'selected' : '' }}>
                                    {{ strtoupper($user->username) }} ({{ strtoupper($user->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-white hover:text-[#1e293b] transition-all duration-500 shadow-lg">
                        Oper Dokumen Sekarang
                    </button>
                </form>
            </div>

            {{-- 3. FITUR HAPUS --}}
            <div class="mt-4 p-4 text-center flex-none">
                <form action="{{ route('admin.BU.document.delete', $document->id) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini secara permanen?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center space-x-2 text-red-400 hover:text-red-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        <span class="text-[10px] font-black uppercase tracking-widest">Hapus Permanen</span>
                    </button>
                </form>
            </div>

            {{-- 4. Tombol Cetak --}}
            @if($document->status == 'active')
            <div class="bg-green-50 p-6 rounded-[2rem] border border-green-100 flex-none">
                <p class="text-[10px] text-green-600 font-black uppercase mb-3 text-center">Dokumen Telah Disetujui Final</p>
                <a href="{{ asset('storage/' . $pathFinal) }}" target="_blank" class="flex items-center justify-center w-full py-4 bg-green-600 text-white rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-green-700 transition-all shadow-lg">
                    Cetak / Unduh Final
                </a>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection