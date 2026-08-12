@extends((Auth::user()?->role === 'admin') ? 'layouts.admin' : 'layouts.reviewer')

@section('title', 'e-QMS Digital Library')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .eqms-scope {
        font-family: 'Poppins', sans-serif;
    }
</style>

<div class="p-8 eqms-scope">
    {{-- 1. HEADER & BREADCRUMBS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-black text-[#1e293b] uppercase tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-book-bookmark text-blue-600"></i> Digital Library Portal
            </h2>
            <div class="flex items-center space-x-2 mt-2 text-[10px] font-bold uppercase tracking-widest text-gray-400 bg-gray-50 px-4 py-2 rounded-full w-fit border border-gray-100 shadow-sm">
                <a href="{{ route('library.index') }}" class="hover:text-blue-600 transition-all"><i class="fa-solid fa-house-chimney mr-1"></i> Library</a>
                @if(request('category')) 
                    <span class="text-gray-300">/</span> <a href="?category={{ request('category') }}" class="hover:text-blue-600">{{ request('category') }}</a> 
                @endif
                @if(request('div')) 
                    <span class="text-gray-300">/</span> <a href="?category=divisi&div={{ request('div') }}" class="hover:text-blue-600">{{ request('div') }}</a> 
                @endif
                @if(request('bu')) 
                    <span class="text-gray-300">/</span> <span class="text-blue-600 font-black">{{ request('bu') }}</span> 
                @endif
            </div>
        </div>

        @if(request('bu') && Auth::user()?->role === 'admin')
            <button onclick="openUploadManualModal()" class="bg-[#1e293b] text-white px-5 py-2.5 rounded-xl font-bold uppercase text-[10px] tracking-wider hover:bg-blue-600 transition-all duration-300 shadow-md hover:shadow-blue-100 flex items-center gap-2 transform hover:-translate-y-0.5">
                <i class="fa-solid fa-cloud-arrow-up text-xs"></i> Tambah SOP Manual
            </button>
        @endif
    </div>

    {{-- 2. KONTEN UTAMA KARDUS PUTIH PREMIUM --}}
    <div class="bg-white rounded-2xl shadow-md p-8 md:p-10 border border-gray-100 min-h-[500px] transition-all duration-300">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6">
            
            {{-- --- LEVEL 1: PILIH KATEGORI (DI-PERBAGUS TOTAL KARTUNYA ✨) --- --}}
            @if(!request('category'))
                <a href="?category=divisi" class="group flex items-center p-6 bg-white rounded-xl hover:bg-[#1e293b] transition-all duration-300 border-l-4 border-blue-600 shadow-md hover:shadow-xl border-t border-r border-b border-gray-100 hover:border-l-4 hover:border-blue-500 transform hover:-translate-y-1">
                    <div class="p-4 bg-blue-50 text-blue-600 rounded-xl shadow-inner group-hover:bg-blue-600 group-hover:text-white transition-all duration-300 text-2xl flex items-center justify-center w-14 h-14">
                        <i class="fa-solid fa-sitemap"></i>
                    </div>
                    <div class="ml-5">
                        <h4 class="font-black text-[#1e293b] uppercase text-sm group-hover:text-white transition-colors duration-300">Business Unit</h4>
                        <p class="text-[9px] text-gray-400 font-bold uppercase group-hover:text-white/60 tracking-widest mt-1">Monitoring Unit Bisnis & Entitas PT</p>
                    </div>
                </a>

                <a href="?category=support" class="group flex items-center p-6 bg-white rounded-xl hover:bg-[#1e293b] transition-all duration-300 border-l-4 border-purple-500 shadow-md hover:shadow-xl border-t border-r border-b border-gray-100 hover:border-l-4 hover:border-purple-400 transform hover:-translate-y-1">
                    <div class="p-4 bg-purple-50 text-purple-600 rounded-xl shadow-inner group-hover:bg-purple-600 group-hover:text-white transition-all duration-300 text-2xl flex items-center justify-center w-14 h-14">
                        <i class="fa-solid fa-folder-tree"></i>
                    </div>
                    <div class="ml-5">
                        <h4 class="font-black text-[#1e293b] uppercase text-sm group-hover:text-white transition-colors duration-300">Support Documents</h4>
                        <p class="text-[9px] text-gray-400 font-bold uppercase group-hover:text-white/60 tracking-widest mt-1">Arsip Dokumen Referensi Internal</p>
                    </div>
                </a>

            {{-- --- LEVEL 2: DAFTAR DIVISI (BUSINESS UNIT) --- --}}
            @elseif(request('category') == 'divisi' && !request('div'))
                @forelse($listDivisions as $d)
                    <a href="?category=divisi&div={{ urlencode($d) }}" class="group flex items-center p-5 bg-blue-50/40 rounded-2xl hover:bg-[#1e293b] transition-all duration-300 border border-blue-100/50 hover:border-blue-500 shadow-sm">
                        <div class="p-3.5 bg-white rounded-xl shadow-sm group-hover:scale-105 transition-transform text-xl flex items-center justify-center w-12 h-12">📁</div>
                        <div class="ml-4 font-bold text-[#1e293b] uppercase text-[11px] group-hover:text-white tracking-wide transition-colors duration-300">{{ $d }}</div>
                    </a>
                @empty
                    <div class="col-span-full text-center py-16 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 p-8 flex flex-col items-center justify-center">
                        <div class="text-4xl mb-3 opacity-40">📭</div>
                        <p class="text-xs font-bold uppercase text-gray-400 tracking-wider">Belum ada Divisi yang diarsip.</p>
                        <p class="text-[10px] text-blue-500 font-medium italic mt-1">Silakan sahkan dokumen estafet dari portal pimpinan!</p>
                    </div>
                @endforelse

            {{-- --- LEVEL 2 (SUPPORT): DAFTAR DEPARTEMEN SUPPORT --- --}}
            @elseif(request('category') == 'support' && !request('bu'))
                @foreach($supportDepts as $dept)
                    <a href="?category=support&bu={{ urlencode($dept) }}" class="group flex items-center p-5 bg-purple-50/40 rounded-2xl hover:bg-[#1e293b] transition-all duration-300 border border-purple-100/50 hover:border-purple-500 shadow-sm">
                        <div class="p-3.5 bg-white rounded-xl shadow-sm group-hover:scale-105 transition-transform text-xl flex items-center justify-center w-12 h-12">📁</div>
                        <div class="ml-4 font-bold text-[#1e293b] uppercase text-[11px] group-hover:text-white tracking-wide transition-colors duration-300">{{ $dept }}</div>
                    </a>
                @endforeach

            {{-- --- LEVEL 3: UNIT BISNIS --- --}}
            @elseif(request('div') && !request('bu'))
                @php 
                    $selectedDiv = (request('div') === 'KOMERSIL') ? 'COMMERCIAL' : request('div');
                    $bus = $divBuMap[$selectedDiv] ?? \App\Models\Library::where('division_name', request('div'))->distinct()->pluck('business_unit'); 
                @endphp
                @foreach($bus as $b)
                    <a href="?category=divisi&div={{ request('div') }}&bu={{ urlencode($b) }}" class="group flex items-center p-5 bg-gray-50 rounded-2xl hover:bg-blue-600 transition-all duration-300 border border-transparent shadow-sm hover:shadow-md">
                        <div class="p-3.5 bg-white rounded-xl shadow-sm group-hover:scale-105 transition-transform text-xl flex items-center justify-center w-12 h-12">📂</div>
                        <div class="ml-4 text-[#1e293b] group-hover:text-white font-bold uppercase text-xs transition-colors duration-300">{{ $b }}</div>
                    </a>
                @endforeach

            {{-- --- LEVEL 4: DAFTAR FILE SOP --- --}}
            
            @else
                @forelse($documents as $doc)
                {{-- CARD UTAMA DENGAN RE-POSITIONING --}}
                <div class="p-5 bg-gray-50 rounded-2xl hover:bg-white hover:shadow-xl transition-all duration-300 group border border-transparent hover:border-gray-100 flex flex-col justify-between h-64 shadow-sm relative">
                    
                    {{-- TOMBOL SAMPAH FLOATING (Hanya muncul untuk Admin di Pojok Kanan Atas) --}}
                    @if(Auth::user()?->role === 'admin')
                    <div class="absolute right-4 top-4 z-20">
                        {{-- Pastikan baris form action kamu tertulis presisi seperti ini --}}
                        <form action="{{ route('admin.library.destroy', ['id' => $doc->id]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini seutuhnya dari sistem Library?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-8 h-8 bg-white text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg shadow-sm border border-gray-100 flex items-center justify-center transition-all duration-300 hover:scale-105 group/trash">
                                <i class="fa-solid fa-trash-can text-xs group-hover/trash:animate-bounce"></i>
                            </button>
                        </form>
                    </div>
                    @endif

                    <div>
                        <div class="p-3 bg-[#1e293b] text-white rounded-xl w-fit mb-3 group-hover:bg-blue-600 transition-colors duration-300 shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-width="2"></path></svg>
                        </div>
                        <h4 class="font-bold text-[#1e293b] uppercase text-[11px] mb-1 line-clamp-2 leading-relaxed pr-6">{{ $doc->title }}</h4>
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider"><i class="fa-solid fa-building text-[8px] mr-1 text-gray-300"></i> {{ $doc->company_name }}</p>
                    </div>

                    <button onclick="viewPDF('{{ asset('storage/'.$doc->file_path) }}')" class="w-full py-3 bg-[#1e293b] text-white rounded-xl font-bold uppercase text-[9px] tracking-widest hover:bg-blue-600 transition-all duration-300 shadow-md group-hover:shadow-blue-100">
                        <i class="fa-solid fa-file-pdf mr-1.5 text-sm"></i> Lihat SOP Sah
                    </button>
                </div>
                @empty
                <div class="col-span-full py-16 flex flex-col items-center justify-center text-center bg-gray-50/50 rounded-2xl border border-dashed border-gray-200 px-6">
                    <div class="w-20 h-20 bg-white shadow-sm border border-gray-100 rounded-full flex items-center justify-center text-3xl mb-4 text-gray-300 animate-pulse">
                        <i class="fa-solid fa-folder-open text-gray-300"></i>
                    </div>
                    <h5 class="text-sm font-bold text-[#1e293b] uppercase tracking-wide">Belum Ada Dokumen Yang Terarsip</h5>
                    <p class="text-[10px] text-gray-400 font-medium max-w-sm mt-1 leading-relaxed lowercase">dokumen aktif akan otomatis masuk ke sini setelah seluruh rangkaian alur tanda tangan pimpinan selesai divalidasi sistem.</p>
                    
                    <div class="flex gap-3 mt-6">
                        <a href="{{ route('library.index') }}" class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-[9px] font-bold uppercase tracking-wider text-gray-500 hover:bg-gray-100 transition-all shadow-sm">
                            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali Ke Depan
                        </a>
                        @if(Auth::user()?->role === 'admin')
                        <button onclick="openUploadManualModal()" class="px-5 py-2.5 bg-blue-600 rounded-xl text-[9px] font-bold uppercase tracking-wider text-white hover:bg-blue-700 transition-all shadow-md shadow-blue-100">
                            <i class="fa-solid fa-plus mr-1.5"></i> Upload Sekarang
                        </button>
                        @endif
                    </div>
                </div>
                @endforelse
            @endif
        </div>
    </div>
</div>

{{-- MODAL PDF VIEWER --}}
<div id="pdfModal" class="fixed inset-0 z-[100] hidden bg-[#1e293b]/95 backdrop-blur-md flex flex-col p-6 transition-all duration-300">
    <div class="flex justify-between items-center text-white mb-4">
        <div class="flex items-center gap-2">
            <span class="bg-blue-600 text-xs px-3 py-1 rounded-full font-bold tracking-widest uppercase">e-QMS SYSTEM</span>
            <h3 class="font-extrabold uppercase italic tracking-wider text-lg">Digital Document Control Viewer</h3>
        </div>
        <button onclick="closePDF()" class="bg-red-500 text-white w-10 h-10 rounded-xl flex items-center justify-center font-bold text-xl hover:bg-red-600 shadow-lg hover:rotate-90 transition-all duration-300">&times;</button>
    </div>
    <div class="flex-1 bg-white rounded-2xl shadow-2xl overflow-hidden relative border border-white/10">
        <iframe id="pdfFrame" src="" class="w-full h-full border-0 min-h-full" style="height: 100%; min-height: 100%;"></iframe>
    </div>
</div>

{{-- MODAL UPLOAD MANUAL ADMIN --}}
@if(Auth::user()?->role === 'admin')
<div id="uploadManualModal" class="fixed inset-0 z-[100] hidden bg-[#1e293b]/80 flex items-center justify-center p-4 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-2xl w-full max-w-md p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-black text-[#1e293b] uppercase italic flex items-center gap-2"><i class="fa-solid fa-file-shield text-blue-600"></i> Upload SOP Manual</h3>
            <button onclick="closeUploadManualModal()" class="text-gray-400 hover:text-red-500 text-2xl transition-colors">&times;</button>
        </div>
        <form action="{{ route('library.store_manual') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="category" value="{{ request('category') }}">
            <input type="hidden" name="division_name" value="{{ request('div') }}">
            <input type="hidden" name="business_unit" value="{{ request('bu') }}">
            
            <div>
                <label class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1 block">Judul SOP Resmi</label>
                <input type="text" name="title" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3.5 font-bold text-xs text-[#1e293b] focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Ketik nama dokumen lengkap..." required>
            </div>
            <div>
                <label class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1 block">Lampirkan File PDF Dokumen (Maks 10MB)</label>
                <input type="file" name="file" accept=".pdf" class="w-full bg-gray-50 border border-gray-100 border-dashed rounded-xl p-3.5 text-xs font-bold text-gray-500 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer" required>
            </div>
            
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeUploadManualModal()" class="flex-1 font-bold text-gray-400 uppercase text-[10px] hover:text-gray-600 transition-all">Batal</button>
                <button type="submit" class="flex-1 bg-[#1e293b] text-white py-3.5 rounded-xl font-bold uppercase text-[10px] tracking-wider hover:bg-blue-600 transition-all duration-300 shadow-md hover:shadow-blue-100">Simpan SOP</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function viewPDF(url) {
        document.getElementById('pdfFrame').src = url + "#toolbar=0&navpanes=0";
        document.getElementById('pdfModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closePDF() {
        document.getElementById('pdfModal').classList.add('hidden');
        document.getElementById('pdfFrame').src = "";
        document.body.style.overflow = 'auto';
    }
    function openUploadManualModal() {
        document.getElementById('uploadManualModal').classList.remove('hidden');
    }
    function closeUploadManualModal() {
        document.getElementById('uploadManualModal').classList.add('hidden');
    }
</script>
@endsection