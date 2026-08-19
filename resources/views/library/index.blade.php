@extends(request()->is('admin/*') || (Auth::check() && Auth::user()->role === 'admin') ? 'layouts.admin' : 'layouts.reviewer')

@section('title', 'e-QMS Digital Library')
@section('header_title', 'E-Library Catalog Dokumen SOP')

@section('content')
<div class="space-y-6">
    
    <!-- TOP BREADCRUMB & HEADER -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-[#4d4633] font-semibold mb-1">
                <a href="{{ request()->is('admin/*') ? route('admin.library.index') : route('library.index') }}" class="hover:text-[#705d00] font-bold flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">local_library</span>
                    <span>Catalog</span>
                </a>
                @if(request('category')) 
                    <span>/</span> <a href="?category={{ request('category') }}" class="hover:text-[#705d00] font-semibold uppercase">{{ request('category') }}</a> 
                @endif
                @if(request('div')) 
                    <span>/</span> <a href="?category=divisi&div={{ request('div') }}" class="hover:text-[#705d00] font-semibold uppercase">{{ request('div') }}</a> 
                @endif
                @if(request('bu')) 
                    <span>/</span> <span class="text-[#705d00] font-bold uppercase">{{ request('bu') }}</span> 
                @endif
            </div>
            <h2 class="text-xl font-extrabold text-[#1e1c14] tracking-tight uppercase">Digital Library Repository</h2>
        </div>

        @if(request('bu') && Auth::user()?->role === 'admin')
            <button onclick="openUploadManualModal()" class="px-4 py-2 bg-[#333028] text-[#ffe16e] hover:bg-black rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center gap-2 self-start md:self-auto">
                <span class="material-symbols-outlined text-base">cloud_upload</span>
                <span>Tambah SOP Manual</span>
            </button>
        @endif
    </div>

    <!-- MAIN CONTAINER -->
    <div class="bg-white rounded-lg p-6 md:p-8 shadow-sm border border-[#cfc6ac]/60 min-h-[500px]">
        
        <!-- LEVEL 1: 5-COLUMN CATEGORY GRID -->
        @if(!request('category'))
            <div class="space-y-6">
                <div class="border-b border-[#cfc6ac]/40 pb-3 flex items-center justify-between">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-[#1e1c14]">Pilih Kategori Utama E-Library</h3>
                    <span class="text-[10px] text-[#4d4633] font-semibold">Arsip Dokumen Terverifikasi</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <a href="?category=divisi" class="p-5 bg-[#fbf9f4] hover:bg-[#fff9ed] rounded-md border border-[#cfc6ac] hover:border-[#705d00]/50 transition-all shadow-sm group flex flex-col justify-between h-44">
                        <div class="w-9 h-9 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                            <span class="material-symbols-outlined text-lg">account_tree</span>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-[#1e1c14] group-hover:text-[#705d00] uppercase">Business Unit</h4>
                            <p class="text-[10px] text-[#4d4633] mt-0.5">SOP Unit Bisnis & Entitas PT</p>
                        </div>
                    </a>

                    <a href="?category=support" class="p-5 bg-[#fbf9f4] hover:bg-[#fff9ed] rounded-md border border-[#cfc6ac] hover:border-[#705d00]/50 transition-all shadow-sm group flex flex-col justify-between h-44">
                        <div class="w-9 h-9 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                            <span class="material-symbols-outlined text-lg">support_agent</span>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-[#1e1c14] group-hover:text-[#705d00] uppercase">Support Dept</h4>
                            <p class="text-[10px] text-[#4d4633] mt-0.5">HC, IT, QMS, HSE, Internal Audit</p>
                        </div>
                    </a>

                    <a href="?category=divisi&div=RETAIL" class="p-5 bg-[#fbf9f4] hover:bg-[#fff9ed] rounded-md border border-[#cfc6ac] hover:border-[#705d00]/50 transition-all shadow-sm group flex flex-col justify-between h-44">
                        <div class="w-9 h-9 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                            <span class="material-symbols-outlined text-lg">storefront</span>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-[#1e1c14] group-hover:text-[#705d00] uppercase">Retail Divisi</h4>
                            <p class="text-[10px] text-[#4d4633] mt-0.5">SPBU, LPG PSO, BBM Retail</p>
                        </div>
                    </a>

                    <a href="?category=divisi&div=KOMERSIL" class="p-5 bg-[#fbf9f4] hover:bg-[#fff9ed] rounded-md border border-[#cfc6ac] hover:border-[#705d00]/50 transition-all shadow-sm group flex flex-col justify-between h-44">
                        <div class="w-9 h-9 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                            <span class="material-symbols-outlined text-lg">directions_boat</span>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-[#1e1c14] group-hover:text-[#705d00] uppercase">Komersil Divisi</h4>
                            <p class="text-[10px] text-[#4d4633] mt-0.5">Trans Laut, Shipyard, CNG</p>
                        </div>
                    </a>

                    <a href="?category=divisi&div=PROPERTY" class="p-5 bg-[#fbf9f4] hover:bg-[#fff9ed] rounded-md border border-[#cfc6ac] hover:border-[#705d00]/50 transition-all shadow-sm group flex flex-col justify-between h-44">
                        <div class="w-9 h-9 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                            <span class="material-symbols-outlined text-lg">location_city</span>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-[#1e1c14] group-hover:text-[#705d00] uppercase">Property Divisi</h4>
                            <p class="text-[10px] text-[#4d4633] mt-0.5">Aset Tanah & Bangunan</p>
                        </div>
                    </a>
                </div>
            </div>

        <!-- LEVEL 2 & 3: DIVISIONS / DEPARTMENTS / BUS GRID -->
        @elseif(!request('bu'))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @if(request('category') == 'divisi' && !request('div'))
                    @php
                        $divIcons = [
                            'RETAIL' => 'storefront',
                            'COMMERCIAL' => 'directions_boat',
                            'KOMERSIL' => 'directions_boat',
                            'SCM' => 'local_shipping',
                            'FA' => 'account_balance',
                            'PROPERTY' => 'location_city',
                        ];
                    @endphp
                    @forelse($listDivisions as $d)
                        <a href="?category=divisi&div={{ urlencode($d) }}" class="p-4 bg-[#fbf9f4] hover:bg-[#fff9ed] rounded-md border border-[#cfc6ac] hover:border-[#705d00]/40 transition-all flex items-center space-x-3.5 shadow-sm group">
                            <div class="w-9 h-9 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined text-lg">{{ $divIcons[strtoupper($d)] ?? 'folder' }}</span>
                            </div>
                            <span class="font-bold text-xs text-[#1e1c14] uppercase tracking-wide group-hover:text-[#705d00]">{{ $d }}</span>
                        </a>
                    @empty
                        <div class="col-span-full py-16 text-center text-[#4d4633] space-y-2">
                            <span class="material-symbols-outlined text-4xl text-[#d6cebf]">folder_open</span>
                            <p class="text-xs font-bold uppercase tracking-wider">Belum ada Divisi yang diarsip.</p>
                        </div>
                    @endforelse

                @elseif(request('category') == 'support')
                    @foreach($supportDepts as $dept)
                        <a href="?category=support&bu={{ urlencode($dept) }}" class="p-4 bg-[#fbf9f4] hover:bg-[#fff9ed] rounded-md border border-[#cfc6ac] hover:border-[#705d00]/40 transition-all flex items-center space-x-3.5 shadow-sm group">
                            <div class="w-9 h-9 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined text-lg">support_agent</span>
                            </div>
                            <span class="font-bold text-xs text-[#1e1c14] uppercase tracking-wide group-hover:text-[#705d00]">{{ $dept }}</span>
                        </a>
                    @endforeach

                @elseif(request('div'))
                    @php 
                        $selectedDiv = (request('div') === 'KOMERSIL') ? 'COMMERCIAL' : request('div');
                        $bus = $divBuMap[$selectedDiv] ?? \App\Models\Library::where('division_name', request('div'))->distinct()->pluck('business_unit'); 
                    @endphp
                    @foreach($bus as $b)
                        <a href="?category=divisi&div={{ request('div') }}&bu={{ urlencode($b) }}" class="p-4 bg-[#fbf9f4] hover:bg-[#fff9ed] rounded-md border border-[#cfc6ac] hover:border-[#705d00]/40 transition-all flex items-center space-x-3.5 shadow-sm group">
                            <div class="w-9 h-9 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined text-lg">store</span>
                            </div>
                            <span class="font-bold text-xs text-[#1e1c14] uppercase tracking-wide group-hover:text-[#705d00]">{{ $b }}</span>
                        </a>
                    @endforeach
                @endif
            </div>

        <!-- LEVEL 4: 3-COLUMN DOCUMENT CARDS GRID -->
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($documents as $doc)
                <div class="p-5 bg-[#fbf9f4] rounded-lg border border-[#cfc6ac]/70 hover:border-[#705d00]/40 transition-all flex flex-col justify-between h-56 shadow-sm relative group">
                    
                    @if(Auth::user()?->role === 'admin')
                    <div class="absolute right-4 top-4 z-20">
                        <form action="{{ route('admin.library.destroy', ['id' => $doc->id]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini seutuhnya dari sistem Library?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-7 h-7 bg-white text-red-600 hover:bg-red-50 rounded shadow-sm border border-[#cfc6ac] flex items-center justify-center transition-all">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </form>
                    </div>
                    @endif

                    <div>
                        <div class="w-8 h-8 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm mb-3">
                            <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                        </div>
                        <h4 class="font-bold text-[#1e1c14] text-xs line-clamp-2 leading-relaxed pr-6 uppercase">{{ $doc->title }}</h4>
                        <p class="text-[10px] text-[#4d4633] font-semibold uppercase mt-1">{{ $doc->company_name }}</p>
                    </div>

                    <button onclick="viewPDF('{{ asset('storage/'.$doc->file_path) }}')" class="w-full py-2.5 bg-[#333028] text-[#ffe16e] hover:bg-black rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">visibility</span>
                        <span>Lihat SOP Sah</span>
                    </button>
                </div>
                @empty
                <div class="col-span-full py-16 flex flex-col items-center justify-center text-center space-y-3">
                    <span class="material-symbols-outlined text-5xl text-[#d6cebf]">folder_off</span>
                    <h5 class="text-sm font-bold text-[#1e1c14] uppercase">Belum Ada Dokumen Yang Terarsip</h5>
                    <p class="text-xs text-[#4d4633] max-w-sm">Dokumen aktif akan otomatis masuk ke E-Library setelah seluruh alur tanda tangan selesai.</p>
                    
                    <div class="flex gap-3 pt-2">
                        <a href="{{ route('library.index') }}" class="px-4 py-2 bg-[#fbf9f4] border border-[#cfc6ac] text-[#333028] rounded-md font-bold text-xs uppercase">
                            Kembali Ke Depan
                        </a>
                        @if(Auth::user()?->role === 'admin')
                        <button onclick="openUploadManualModal()" class="px-4 py-2 bg-[#333028] text-[#ffe16e] rounded-md font-bold text-xs uppercase">
                            Upload Sekarang
                        </button>
                        @endif
                    </div>
                </div>
                @endforelse
            </div>
        @endif
    </div>
</div>

<!-- MODAL PDF VIEWER -->
<div id="pdfModal" class="fixed inset-0 z-[100] hidden bg-black/80 backdrop-blur-sm flex flex-col p-6 transition-all">
    <div class="flex justify-between items-center text-white mb-4">
        <div class="flex items-center gap-2">
            <span class="bg-[#ffd92f] text-[#705d00] text-[10px] px-2.5 py-0.5 rounded font-extrabold uppercase tracking-wider">e-QMS SYSTEM</span>
            <h3 class="font-bold text-sm tracking-wide">Digital Document Control Viewer</h3>
        </div>
        <button onclick="closePDF()" class="bg-white/20 hover:bg-white/30 text-white w-8 h-8 rounded-md flex items-center justify-center font-bold text-lg">&times;</button>
    </div>
    <div class="flex-1 bg-white rounded-lg shadow-2xl overflow-hidden relative">
        <iframe id="pdfFrame" src="" class="w-full h-full border-0"></iframe>
    </div>
</div>

<!-- MODAL UPLOAD MANUAL ADMIN -->
@if(Auth::user()?->role === 'admin')
<div id="uploadManualModal" class="fixed inset-0 z-[100] hidden bg-black/60 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-lg w-full max-w-md p-6 md:p-8 shadow-2xl border border-[#cfc6ac] space-y-4">
        <div class="flex justify-between items-center border-b border-[#cfc6ac]/40 pb-3">
            <h3 class="text-base font-bold text-[#1e1c14] flex items-center gap-2">
                <span class="material-symbols-outlined text-[#705d00]">cloud_upload</span>
                <span>Upload SOP Manual</span>
            </h3>
            <button onclick="closeUploadManualModal()" class="text-gray-400 hover:text-black text-2xl">&times;</button>
        </div>
        <form action="{{ route('library.store_manual') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="category" value="{{ request('category') }}">
            <input type="hidden" name="division_name" value="{{ request('div') }}">
            <input type="hidden" name="business_unit" value="{{ request('bu') }}">
            
            <div>
                <label class="text-[10px] font-bold text-[#4d4633] uppercase tracking-wider mb-1 block">Judul SOP Resmi</label>
                <input type="text" name="title" class="w-full bg-[#fbf9f4] border border-[#cfc6ac] rounded-md p-2.5 font-semibold text-xs text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none" placeholder="Ketik nama dokumen lengkap..." required>
            </div>
            <div>
                <label class="text-[10px] font-bold text-[#4d4633] uppercase tracking-wider mb-1 block">Lampirkan File PDF Dokumen (Maks 10MB)</label>
                <input type="file" name="file" accept=".pdf" class="w-full text-xs font-semibold text-[#4d4633] file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-[#333028] file:text-[#ffe16e] hover:file:bg-black cursor-pointer" required>
            </div>
            
            <div class="flex space-x-3 pt-3">
                <button type="button" onclick="closeUploadManualModal()" class="flex-1 py-2.5 bg-[#fbf9f4] border border-[#cfc6ac] text-[#4d4633] rounded-md font-bold text-xs uppercase">Batal</button>
                <button type="submit" class="flex-1 bg-[#333028] text-[#ffe16e] py-2.5 rounded-md font-bold text-xs uppercase shadow-sm">Simpan SOP</button>
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

