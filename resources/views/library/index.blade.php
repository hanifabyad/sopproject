@extends(request()->is('admin/*') || (Auth::check() && Auth::user()->role === 'admin') ? 'layouts.admin' : 'layouts.reviewer')

@section('title', 'e-QMS Digital Library')
@section('header_title', 'E-Library Catalog Dokumen SOP')

@section('content')
<div class="space-y-6">
    
    <!-- TOP BREADCRUMB & HEADER -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold mb-1">
                <a href="{{ request()->is('admin/*') ? route('admin.library.index') : route('library.index') }}" class="hover:text-[#ffe16e] font-bold flex items-center gap-1">
                    <i class="ph ph-books text-base"></i>
                    <span>Catalog</span>
                </a>
                @if(request('category')) 
                    <span>/</span> <a href="?category={{ request('category') }}" class="hover:text-[#ffe16e] font-semibold uppercase">{{ request('category') }}</a> 
                @endif
                @if(request('div')) 
                    <span>/</span> <a href="?category=divisi&div={{ request('div') }}" class="hover:text-[#ffe16e] font-semibold uppercase">{{ request('div') }}</a> 
                @endif
                @if(request('bu')) 
                    <span>/</span> <span class="text-[#ffe16e] font-bold uppercase">{{ request('bu') }}</span> 
                @endif
            </div>
            <h2 class="text-xl font-extrabold tracking-tight uppercase">Digital Library Repository</h2>
        </div>

        <div class="flex items-center gap-3">
            @if(request('bu') && Auth::user()?->role === 'admin')
                <button onclick="openUploadManualModal()" class="px-4 py-2 bg-[#ffe16e] text-charcoal-900 hover:bg-amber-400 rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center gap-2 self-start md:self-auto border-none cursor-pointer">
                    <i class="ph ph-cloud-arrow-up text-base"></i>
                    <span>Tambah SOP Manual</span>
                </button>
            @endif

            @if(request('category') == 'general' && Auth::user()?->role === 'admin')
                <button onclick="openCreateFolderModal()" class="px-4 py-2 bg-[#ffe16e] text-charcoal-900 hover:bg-amber-400 rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center gap-2 self-start md:self-auto border-none cursor-pointer">
                    <i class="ph ph-folder-plus text-base"></i>
                    <span>Buat Folder Baru</span>
                </button>
            @endif
        </div>
    </div>

    <!-- MAIN CONTENT AREA -->
    @if(!request('category'))
        <!-- 6 Categories snug boxes on grey canvas -->
        <div class="space-y-6">
            <div class="border-b border-sand-200/40 pb-3 flex items-center justify-between">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-on-surface">Pilih Kategori Utama E-Library</h3>
                <span class="text-[10px] text-on-surface-variant font-semibold">Arsip Dokumen Terverifikasi</span>
            </div>

             <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <a href="?category=divisi" class="p-5 bg-white hover:bg-[#fff9ed] rounded-lg border border-sand-200/60 hover:border-[#1677B8]/50 transition-all shadow-sm group flex flex-col justify-between h-44 cursor-pointer">
                    <div class="w-9 h-9 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                        <i class="ph ph-tree-structure text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-on-surface group-hover:text-[#1677B8] uppercase transition-colors">Business Unit</h4>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">SOP Unit Bisnis & Entitas PT</p>
                    </div>
                </a>

                <a href="?category=support" class="p-5 bg-white hover:bg-[#fff9ed] rounded-lg border border-sand-200/60 hover:border-[#1677B8]/50 transition-all shadow-sm group flex flex-col justify-between h-44 cursor-pointer">
                    <div class="w-9 h-9 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                        <i class="ph ph-headset text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-on-surface group-hover:text-[#1677B8] uppercase transition-colors">Support Dept</h4>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">HC, IT, QMS, HSE, Internal Audit</p>
                    </div>
                </a>

                <a href="?category=divisi&div=RETAIL" class="p-5 bg-white hover:bg-[#fff9ed] rounded-lg border border-sand-200/60 hover:border-[#1677B8]/50 transition-all shadow-sm group flex flex-col justify-between h-44 cursor-pointer">
                    <div class="w-9 h-9 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                        <i class="ph ph-storefront text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-on-surface group-hover:text-[#1677B8] uppercase transition-colors">Retail Divisi</h4>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">SPBU, LPG PSO, BBM Retail</p>
                    </div>
                </a>

                <a href="?category=divisi&div=KOMERSIL" class="p-5 bg-white hover:bg-[#fff9ed] rounded-lg border border-sand-200/60 hover:border-[#1677B8]/50 transition-all shadow-sm group flex flex-col justify-between h-44 cursor-pointer">
                    <div class="w-9 h-9 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                        <i class="ph ph-boat text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-on-surface group-hover:text-[#1677B8] uppercase transition-colors">Komersil Divisi</h4>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">Trans Laut, Shipyard, CNG</p>
                    </div>
                </a>

                <a href="?category=divisi&div=PROPERTY" class="p-5 bg-white hover:bg-[#fff9ed] rounded-lg border border-sand-200/60 hover:border-[#1677B8]/50 transition-all shadow-sm group flex flex-col justify-between h-44 cursor-pointer">
                    <div class="w-9 h-9 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-lg">business</span>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-on-surface group-hover:text-[#1677B8] uppercase transition-colors">Property Divisi</h4>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">Aset Tanah & Bangunan</p>
                    </div>
                </a>

                <a href="?category=general" class="p-5 bg-white hover:bg-[#fff9ed] rounded-lg border border-sand-200/60 hover:border-[#1677B8]/50 transition-all shadow-sm group flex flex-col justify-between h-44 cursor-pointer">
                    <div class="w-9 h-9 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                        <i class="ph ph-folders text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-on-surface group-hover:text-[#1677B8] uppercase transition-colors">General Library</h4>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">Formulir, Memo, SK, Logo, Dll</p>
                    </div>
                </a>
            </div>
        </div>
    @else
        <!-- MAIN CONTAINER FOR SUBPAGES -->
        <div class="bg-white rounded-lg p-6 md:p-8 shadow-sm border border-sand-200/60 min-h-[500px]">
            
            <!-- LEVEL 2 & 3: DIVISIONS / DEPARTMENTS / BUS GRID / GENERAL FOLDERS -->
            @if(request('category') == 'general' || !request('bu'))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @if(request('category') == 'general')
                    @forelse($generalFolders as $f)
                        <div class="p-4 bg-sand-50 hover:bg-[#fff9ed] rounded-md border border-sand-200 hover:border-gold-500/40 transition-all flex items-center justify-between shadow-sm group">
                            <a href="{{ route('library.folder.show', $f->id) }}" class="flex items-center space-x-3.5 flex-1">
                                <div class="w-9 h-9 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                                    <i class="ph ph-folder text-lg"></i>
                                </div>
                                <span class="font-bold text-xs text-on-surface uppercase tracking-wide group-hover:text-gold-500">{{ $f->name }}</span>
                            </a>
                            @if(Auth::user()?->role === 'admin')
                                <form action="{{ route('admin.library.folder.destroy', $f->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus folder ini beserta seluruh berkas di dalamnya secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 bg-white text-red-600 hover:bg-red-50 rounded shadow-sm border border-sand-200 flex items-center justify-center transition-all">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-full py-16 text-center text-on-surface-variant space-y-2">
                            <span class="material-symbols-outlined text-4xl text-[#d6cebf]">folder_open</span>
                            <p class="text-xs font-bold uppercase tracking-wider">Belum ada folder di General Library.</p>
                        </div>
                    @endforelse

                @elseif(request('category') == 'divisi' && !request('div'))
                    @php
                        $divIcons = [
                            'RETAIL' => 'storefront',
                            'COMMERCIAL' => 'directions_boat',
                            'KOMERSIL' => 'directions_boat',
                            'SCM' => 'local_shipping',
                            'FA' => 'account_balance',
                            'PROPERTY' => 'business',
                        ];
                    @endphp
                    @forelse($listDivisions as $d)
                        <a href="?category=divisi&div={{ urlencode($d) }}" class="p-4 bg-sand-50 hover:bg-[#fff9ed] rounded-md border border-sand-200 hover:border-gold-500/40 transition-all flex items-center space-x-3.5 shadow-sm group">
                            <div class="w-9 h-9 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined text-lg">{{ $divIcons[strtoupper($d)] ?? 'folder' }}</span>
                            </div>
                            <span class="font-bold text-xs text-on-surface uppercase tracking-wide group-hover:text-gold-500">{{ $d }}</span>
                        </a>
                    @empty
                        <div class="col-span-full py-16 text-center text-on-surface-variant space-y-2">
                            <span class="material-symbols-outlined text-4xl text-[#d6cebf]">folder_open</span>
                            <p class="text-xs font-bold uppercase tracking-wider">Belum ada Divisi yang diarsip.</p>
                        </div>
                    @endforelse

                @elseif(request('category') == 'support')
                    @foreach($supportDepts as $dept)
                        <a href="?category=support&bu={{ urlencode($dept) }}" class="p-4 bg-sand-50 hover:bg-[#fff9ed] rounded-md border border-sand-200 hover:border-gold-500/40 transition-all flex items-center space-x-3.5 shadow-sm group">
                            <div class="w-9 h-9 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined text-lg">support_agent</span>
                            </div>
                            <span class="font-bold text-xs text-on-surface uppercase tracking-wide group-hover:text-gold-500">{{ $dept }}</span>
                        </a>
                    @endforeach

                @elseif(request('div'))
                    @php 
                        $selectedDiv = (request('div') === 'KOMERSIL') ? 'COMMERCIAL' : request('div');
                        $bus = $divBuMap[$selectedDiv] ?? \App\Models\Library::where('division_name', request('div'))->distinct()->pluck('business_unit'); 
                    @endphp
                    @foreach($bus as $b)
                        <a href="?category=divisi&div={{ request('div') }}&bu={{ urlencode($b) }}" class="p-4 bg-sand-50 hover:bg-[#fff9ed] rounded-md border border-sand-200 hover:border-gold-500/40 transition-all flex items-center space-x-3.5 shadow-sm group">
                            <div class="w-9 h-9 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm group-hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined text-lg">store</span>
                            </div>
                            <span class="font-bold text-xs text-on-surface uppercase tracking-wide group-hover:text-gold-500">{{ $b }}</span>
                        </a>
                    @endforeach
                @endif
            </div>

        <!-- LEVEL 4: 3-COLUMN DOCUMENT CARDS GRID -->
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($documents as $doc)
                <div class="p-5 bg-sand-50 rounded-lg border border-sand-200/70 hover:border-gold-500/40 transition-all flex flex-col justify-between h-56 shadow-sm relative group">
                    
                    @if(Auth::user()?->role === 'admin')
                    <div class="absolute right-4 top-4 z-20">
                        <form action="{{ route('admin.library.destroy', ['id' => $doc->id]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini seutuhnya dari sistem Library?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-7 h-7 bg-white text-red-600 hover:bg-red-50 rounded shadow-sm border border-sand-200 flex items-center justify-center transition-all">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </form>
                    </div>
                    @endif

                    <div>
                        <div class="w-8 h-8 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold shadow-sm mb-3">
                            <i class="ph ph-file-pdf text-lg"></i>
                        </div>
                        <h4 class="font-bold text-on-surface text-xs line-clamp-2 leading-relaxed pr-6 uppercase">{{ $doc->title }}</h4>
                        <p class="text-[10px] text-on-surface-variant font-semibold uppercase mt-1">{{ $doc->company_name }}</p>
                    </div>

                    <button onclick="viewPDF('{{ route('library.document.stream', $doc->id) }}')" class="w-full py-2.5 bg-charcoal-900 text-gold-fixed hover:bg-black rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">visibility</span>
                        <span>Lihat SOP Sah</span>
                    </button>
                </div>
                @empty
                <div class="col-span-full py-16 flex flex-col items-center justify-center text-center space-y-3">
                    <span class="material-symbols-outlined text-5xl text-[#d6cebf]">folder_off</span>
                    <h5 class="text-sm font-bold text-on-surface uppercase">Belum Ada Dokumen Yang Terarsip</h5>
                    <p class="text-xs text-on-surface-variant max-w-sm">Dokumen aktif akan otomatis masuk ke E-Library setelah seluruh alur tanda tangan selesai.</p>
                    
                    <div class="flex gap-3 pt-2">
                        <a href="{{ route('library.index') }}" class="px-4 py-2 bg-sand-50 border border-sand-200 text-charcoal-900 rounded-md font-bold text-xs uppercase">
                            Kembali Ke Depan
                        </a>
                        @if(Auth::user()?->role === 'admin')
                        <button onclick="openUploadManualModal()" class="px-4 py-2 bg-charcoal-900 text-gold-fixed rounded-md font-bold text-xs uppercase">
                            Upload Sekarang
                        </button>
                        @endif
                    </div>
                </div>
                @endforelse
            </div>
        @endif
    </div>
@endif
</div>

<!-- MODAL PDF VIEWER -->
<div id="pdfModal" class="fixed inset-0 z-[100] hidden bg-black/80 backdrop-blur-sm flex flex-col p-6 transition-all">
    <div class="flex justify-between items-center text-white mb-4">
        <div class="flex items-center gap-2">
            <span class="bg-[#ffd92f] text-gold-500 text-[10px] px-2.5 py-0.5 rounded font-extrabold uppercase tracking-wider">e-QMS SYSTEM</span>
            <h3 class="font-bold text-sm tracking-wide">Digital Document Control Viewer</h3>
        </div>
        <div class="flex items-center gap-2">
            <a id="pdfOpenNewTab" href="#" target="_blank" 
               class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white text-xs font-bold rounded-md transition-colors">
                <i class="ph ph-arrow-square-out text-sm"></i>
                <span>Buka di Tab Baru</span>
            </a>
            <button onclick="closePDF()" class="bg-white/20 hover:bg-white/30 text-white w-8 h-8 rounded-md flex items-center justify-center font-bold text-lg">&times;</button>
        </div>
    </div>
    <div class="flex-1 bg-white rounded-lg shadow-2xl overflow-hidden relative">
        <iframe id="pdfFrame" src="" class="w-full h-full border-0"></iframe>
    </div>
</div>


<!-- MODAL UPLOAD MANUAL ADMIN -->
@if(Auth::user()?->role === 'admin')
<div id="uploadManualModal" class="fixed inset-0 z-[100] hidden bg-black/60 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-lg w-full max-w-md p-6 md:p-8 shadow-2xl border border-sand-200 space-y-4">
        <div class="flex justify-between items-center border-b border-sand-200/40 pb-3">
            <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-gold-500">cloud_upload</span>
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
                <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1 block">Judul SOP Resmi</label>
                <input type="text" name="title" class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none" placeholder="Ketik nama dokumen lengkap..." required>
            </div>
            <div>
                <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1 block">Lampirkan File PDF Dokumen (Maks 10MB)</label>
                <input type="file" name="file" accept=".pdf" class="w-full text-xs font-semibold text-on-surface-variant file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-charcoal-900 file:text-gold-fixed hover:file:bg-black cursor-pointer" required>
            </div>
            
            <div class="flex space-x-3 pt-3">
                <button type="button" onclick="closeUploadManualModal()" class="flex-1 py-2.5 bg-sand-50 border border-sand-200 text-on-surface-variant rounded-md font-bold text-xs uppercase">Batal</button>
                <button type="submit" class="flex-1 bg-charcoal-900 text-gold-fixed py-2.5 rounded-md font-bold text-xs uppercase shadow-sm">Simpan SOP</button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- MODAL CREATE FOLDER -->
@if(Auth::user()?->role === 'admin')
<div id="createFolderModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg p-6 max-w-md w-full border border-sand-200 shadow-lg">
        <div class="flex items-center justify-between border-b border-sand-200/40 pb-3 mb-4">
            <h3 class="font-extrabold text-sm text-on-surface uppercase tracking-wider">Buat Folder Baru</h3>
            <button onclick="closeCreateFolderModal()" class="text-gray-400 hover:text-gray-600">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <form action="{{ route('admin.library.folder.create') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="parent_id" value="">
            <div>
                <label class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider mb-1 block">Nama Folder</label>
                <input type="text" name="name" class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none" placeholder="Ketik nama folder..." required>
            </div>
            <div class="flex space-x-3 pt-3">
                <button type="button" onclick="closeCreateFolderModal()" class="flex-1 py-2.5 bg-sand-50 border border-sand-200 text-on-surface-variant rounded-md font-bold text-xs uppercase">Batal</button>
                <button type="submit" class="flex-1 bg-charcoal-900 text-gold-fixed py-2.5 rounded-md font-bold text-xs uppercase shadow-sm">Buat Folder</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function viewPDF(url) {
        // Tampilkan PDF dengan toolbar lengkap agar user bisa navigasi halaman
        document.getElementById('pdfFrame').src = url;
        document.getElementById('pdfOpenNewTab').href = url;
        document.getElementById('pdfModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closePDF() {
        document.getElementById('pdfModal').classList.add('hidden');
        document.getElementById('pdfFrame').src = "";
        document.getElementById('pdfOpenNewTab').href = "#";
        document.body.style.overflow = 'auto';
    }
    function openUploadManualModal() {
        document.getElementById('uploadManualModal').classList.remove('hidden');
    }
    function closeUploadManualModal() {
        document.getElementById('uploadManualModal').classList.add('hidden');
    }
    function openCreateFolderModal() {
        document.getElementById('createFolderModal').classList.remove('hidden');
    }
    function closeCreateFolderModal() {
        document.getElementById('createFolderModal').classList.add('hidden');
    }
</script>
@endsection


