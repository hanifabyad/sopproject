@extends(request()->is('admin/*') || (Auth::check() && Auth::user()->role === 'admin') ? 'layouts.admin' : 'layouts.reviewer')

@section('title', 'e-QMS Digital Library')
@section('header_title', 'E-Library Catalog Dokumen SOP')

@section('content')
<div class="space-y-6">
    
    <!-- TOP BREADCRUMB & HEADER -->
    @php
        $baseUrl = request()->is('admin/*') ? route('admin.library.index') : route('library.index');
        $backUrl = null;
        if (request('bu')) {
            if (request('category') === 'support') {
                $backUrl = $baseUrl . '?category=support';
            } elseif (request('div')) {
                $backUrl = $baseUrl . '?category=divisi&div=' . urlencode(request('div'));
            } else {
                $backUrl = $baseUrl . '?category=divisi';
            }
        } elseif (request('div')) {
            $backUrl = $baseUrl . '?category=divisi';
        } elseif (request('category') || !empty($search)) {
            $backUrl = $baseUrl;
        }
    @endphp
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold mb-1">
                @if($backUrl)
                    <x-back-button href="{{ $backUrl }}" variant="light" />
                    <span class="text-white/30">|</span>
                @endif
                <a href="{{ $baseUrl }}" class="hover:text-[#ffe16e] font-bold flex items-center gap-1">
                    <i class="ph ph-books text-base"></i>
                    <span>Catalog</span>
                </a>
                @if(request('category')) 
                    <span>/</span> <a href="?category={{ request('category') }}" class="hover:text-[#ffe16e] font-semibold capitalize">{{ request('category') }}</a> 
                @endif
                @if(request('div')) 
                    <span>/</span> <a href="?category=divisi&div={{ request('div') }}" class="hover:text-[#ffe16e] font-semibold capitalize">{{ request('div') }}</a> 
                @endif
                @if(request('bu')) 
                    <span>/</span> <span class="text-[#ffe16e] font-bold capitalize">{{ request('bu') }}</span> 
                @endif
            </div>
            <h2 class="text-xl font-extrabold tracking-tight capitalize">Digital Library Repository</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">Pusat repositori dokumen mutu, SOP sah, job description, dan instrumen kerja e-QMS.</p>
        </div>

        @if(Auth::user()?->role === 'admin')
        <div class="flex items-center gap-2.5">
            <button type="button" onclick="openCreateFolderModal()" class="px-3.5 py-2 bg-white/10 hover:bg-white/20 border border-white/25 text-white rounded-[2px] font-bold text-xs capitalize tracking-wider shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="ph ph-folder-plus text-base text-[#ffe16e]"></i>
                <span>Buat Folder</span>
            </button>
            @if(request('bu'))
            <button type="button" onclick="openUploadManualModal()" class="px-3.5 py-2 bg-white text-[#1677B8] hover:bg-slate-100 rounded-[2px] font-extrabold text-xs capitalize tracking-wider shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="ph ph-cloud-arrow-up text-base"></i>
                <span>Unggah Berkas / Dokumen</span>
            </button>
            @endif
        </div>
        @endif
    </div>

    <!-- SEARCH & FILTER BAR E-LIBRARY -->
    <div class="bg-white rounded-lg p-5 shadow-sm border border-sand-200/60">
        <form method="GET" action="{{ request()->is('admin/*') ? route('admin.library.index') : route('library.index') }}" class="flex flex-col sm:flex-row gap-3 items-center">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            @if(request('div'))
                <input type="hidden" name="div" value="{{ request('div') }}">
            @endif
            @if(request('bu'))
                <input type="hidden" name="bu" value="{{ request('bu') }}">
            @endif

            <!-- Search Input -->
            <div class="relative flex-1 w-full">
                <input type="text" 
                       name="search" 
                       value="{{ $search ?? '' }}" 
                       placeholder="Cari SOP berdasarkan judul, nomor dokumen, unit bisnis, atau entitas PT..." 
                       class="w-full bg-canvas border border-sand-200 rounded-md pl-10 pr-4 py-2.5 text-xs font-semibold text-on-surface outline-none focus:ring-2 focus:ring-[#1677B8] transition-all">
                <i class="ph ph-magnifying-glass text-base text-[#1677B8] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            </div>

            <!-- Filter Tahun -->
            <div class="w-full sm:w-48">
                <select name="year" onchange="this.form.submit()" class="w-full bg-canvas border border-sand-200 rounded-md px-3 py-2.5 text-xs font-bold text-on-surface outline-none focus:ring-2 focus:ring-[#1677B8] transition-all">
                    <option value="all" {{ ($year ?? 'all') == 'all' ? 'selected' : '' }}>Semua Tahun</option>
                    @if(isset($availableYears))
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ (string)($year ?? '') === (string)$y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <x-interactive-button text="Cari" variant="blue" icon="ph ph-magnifying-glass text-sm" class="flex-1 sm:flex-none" />
                @if(!empty($search) || (!empty($year) && $year !== 'all'))
                    <a href="{{ request()->is('admin/*') ? route('admin.library.index') : route('library.index') }}" 
                       class="px-3 py-2.5 bg-canvas hover:bg-sand-100 text-on-surface-variant border border-sand-200 rounded-md text-xs font-bold transition-all flex items-center justify-center cursor-pointer" title="Reset Pencarian">
                        <i class="ph ph-x text-sm"></i>
                    </a>
                @endif
            </div>
        </form>

        @if(!empty($search) || (!empty($year) && $year !== 'all'))
            <div class="mt-3 pt-3 border-t border-sand-200/40 flex items-center justify-between text-xs">
                <span class="text-on-surface-variant">
                    Hasil pencarian: <strong class="text-on-surface">{{ $documents->count() }} dokumen SOP</strong>
                    @if(isset($matchingFiles) && $matchingFiles->count() > 0)
                        dan <strong class="text-on-surface">{{ $matchingFiles->count() }} berkas general</strong>
                    @endif
                    ditemukan.
                </span>
                <a href="{{ request()->is('admin/*') ? route('admin.library.index') : route('library.index') }}" class="text-[#1677B8] font-bold hover:underline text-[11px]">
                    Hapus Filter
                </a>
            </div>
        @endif
    </div>

    <!-- HASIL PENCARIAN FILE GENERAL JIKA ADA -->
    @if(isset($matchingFiles) && $matchingFiles->count() > 0)
    <div class="bg-white rounded-lg p-5 shadow-sm border border-sand-200/60 space-y-3">
        <div class="flex items-center space-x-2 text-xs font-bold text-on-surface capitalize border-b border-sand-200/40 pb-2">
            <i class="ph ph-files text-base text-[#1677B8]"></i>
            <span>Berkas Ditemukan di Folder General ({{ $matchingFiles->count() }})</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($matchingFiles as $mf)
            <div class="p-3.5 bg-sand-50 rounded-md border border-sand-200 flex items-center justify-between shadow-sm">
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] text-on-surface-variant block font-bold capitalize">{{ $mf->folder->name ?? 'General' }}</span>
                    <a href="{{ route('library.file.stream', $mf->id) }}" target="_blank" class="text-xs font-bold text-on-surface hover:text-[#1677B8] truncate block mt-0.5">
                        {{ $mf->name }}
                    </a>
                </div>
                <a href="{{ route('library.file.stream', $mf->id) }}" target="_blank" class="p-1.5 bg-white text-[#1677B8] hover:bg-sand-100 rounded border border-sand-200 ml-2" title="Buka File">
                    <i class="ph ph-arrow-square-out text-sm"></i>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- MAIN CONTENT AREA -->
    @if(!request('category') && empty($search))
        <!-- Categories boxes on grey canvas -->
        <div class="space-y-6">
            <div class="border-b border-sand-200/40 pb-3 flex items-center justify-between">
                <h3 class="text-xs font-extrabold capitalize tracking-wider text-on-surface">Pilih Kategori Utama E-Library</h3>
                <span class="text-[10px] text-on-surface-variant font-semibold">Arsip Dokumen Terverifikasi</span>
            </div>

             <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- 1. HUB BUSINESS UNIT -->
                <a href="?category=divisi" class="p-5 bg-white hover:bg-sky-50/50 rounded-lg border border-sand-200/60 hover:border-[#00b4d8]/60 transition-all shadow-sm group flex flex-col justify-between h-44 cursor-pointer">
                    <div class="flex items-center justify-start pt-1">
                        <i class="ph ph-tree-structure text-5xl text-[#00b4d8] group-hover:scale-110 group-hover:text-[#1677B8] transition-all duration-200"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-on-surface group-hover:text-[#1677B8] capitalize transition-colors">Semua Business Unit</h4>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">Hub 4 Divisi & Seluruh Entitas PT</p>
                    </div>
                </a>

                <!-- 2. HUB SUPPORT DEPT -->
                <a href="?category=support" class="p-5 bg-white hover:bg-sky-50/50 rounded-lg border border-sand-200/60 hover:border-[#00b4d8]/60 transition-all shadow-sm group flex flex-col justify-between h-44 cursor-pointer">
                    <div class="flex items-center justify-start pt-1">
                        <i class="ph ph-headset text-5xl text-[#00b4d8] group-hover:scale-110 group-hover:text-[#1677B8] transition-all duration-200"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-on-surface group-hover:text-[#1677B8] capitalize transition-colors">Support Dept</h4>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">HC, IT, QMS, HSE, Internal Audit, dll</p>
                    </div>
                </a>

                <!-- 3. RETAIL DIVISI -->
                <a href="?category=divisi&div=RETAIL" class="p-5 bg-white hover:bg-sky-50/50 rounded-lg border border-sand-200/60 hover:border-[#00b4d8]/60 transition-all shadow-sm group flex flex-col justify-between h-44 cursor-pointer">
                    <div class="flex items-center justify-start pt-1">
                        <i class="ph ph-gas-pump text-5xl text-[#00b4d8] group-hover:scale-110 group-hover:text-[#1677B8] transition-all duration-200"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-on-surface group-hover:text-[#1677B8] capitalize transition-colors">Retail Divisi</h4>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">SPBU, LPG PSO, LPG NPSO, PKSP, TRP, INMAR</p>
                    </div>
                </a>

                <!-- 4. KOMERSIL DIVISI -->
                <a href="?category=divisi&div=KOMERSIL" class="p-5 bg-white hover:bg-sky-50/50 rounded-lg border border-sand-200/60 hover:border-[#00b4d8]/60 transition-all shadow-sm group flex flex-col justify-between h-44 cursor-pointer">
                    <div class="flex items-center justify-start pt-1">
                        <i class="ph ph-boat text-5xl text-[#00b4d8] group-hover:scale-110 group-hover:text-[#1677B8] transition-all duration-200"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-on-surface group-hover:text-[#1677B8] capitalize transition-colors">Komersil Divisi</h4>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">CPT & MHM, SBS, GVI</p>
                    </div>
                </a>

                <!-- 5. SCM DIVISI -->
                <a href="?category=divisi&div=SCM" class="p-5 bg-white hover:bg-sky-50/50 rounded-lg border border-sand-200/60 hover:border-[#00b4d8]/60 transition-all shadow-sm group flex flex-col justify-between h-44 cursor-pointer">
                    <div class="flex items-center justify-start pt-1">
                        <i class="ph ph-truck text-5xl text-[#00b4d8] group-hover:scale-110 group-hover:text-[#1677B8] transition-all duration-200"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-on-surface group-hover:text-[#1677B8] capitalize transition-colors">Supply Chain Management</h4>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">Procurement, Warehouse, Aset, GA</p>
                    </div>
                </a>

                <!-- 6. FA DIVISI -->
                <a href="?category=divisi&div=FA" class="p-5 bg-white hover:bg-sky-50/50 rounded-lg border border-sand-200/60 hover:border-[#00b4d8]/60 transition-all shadow-sm group flex flex-col justify-between h-44 cursor-pointer">
                    <div class="flex items-center justify-start pt-1">
                        <i class="ph ph-bank text-5xl text-[#00b4d8] group-hover:scale-110 group-hover:text-[#1677B8] transition-all duration-200"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-on-surface group-hover:text-[#1677B8] capitalize transition-colors">Finance & Accounting</h4>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">Keuangan, Accounting, Perpajakan</p>
                    </div>
                </a>

                <!-- 7. GENERAL / OTHER -->
                <a href="?category=general" class="p-5 bg-white hover:bg-sky-50/50 rounded-lg border border-sand-200/60 hover:border-[#00b4d8]/60 transition-all shadow-sm group flex flex-col justify-between h-44 cursor-pointer">
                    <div class="flex items-center justify-start pt-1">
                        <i class="ph ph-folders text-5xl text-[#00b4d8] group-hover:scale-110 group-hover:text-[#1677B8] transition-all duration-200"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-on-surface group-hover:text-[#1677B8] capitalize transition-colors">General Library & Other</h4>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">Formulir Bebas, Memo Internal, SK Direksi, Brand Identity & Template</p>
                    </div>
                </a>
            </div>
        </div>
    @else
        <!-- MAIN CONTAINER FOR SUBPAGES / SEARCH RESULTS -->
        <div class="bg-white rounded-lg p-6 md:p-8 shadow-sm border border-sand-200/60 min-h-[500px] space-y-6">
            
            <!-- LEVEL 2 & 3: DIVISIONS / DEPARTMENTS / BUS GRID / GENERAL FOLDERS (Hanya jika tidak sedang search) -->
            @if(empty($search) && (request('category') == 'general' || !request('bu')))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @if(request('category') == 'general')
                    @forelse($generalFolders as $f)
                        <div class="p-4 bg-sand-50 hover:bg-sky-50/50 rounded-md border border-sand-200 hover:border-[#00b4d8]/50 transition-all flex items-center justify-between shadow-sm group">
                            <a href="{{ route('library.folder.show', $f->id) }}" class="flex items-center space-x-3.5 flex-1">
                                <i class="ph ph-folder text-3xl text-[#00b4d8] group-hover:scale-110 transition-transform flex-shrink-0"></i>
                                <span class="font-bold text-xs text-on-surface capitalize tracking-wide group-hover:text-[#1677B8]">{{ $f->name }}</span>
                            </a>
                            @if(Auth::user()?->role === 'admin')
                                <form action="{{ route('admin.library.folder.destroy', $f->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus folder ini beserta seluruh berkas di dalamnya secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 bg-white text-red-600 hover:bg-red-50 rounded shadow-sm border border-sand-200 flex items-center justify-center transition-all cursor-pointer">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-full py-16 text-center text-on-surface-variant space-y-2">
                            <i class="ph ph-folder-open text-5xl text-[#00b4d8]/60"></i>
                            <p class="text-xs font-bold capitalize tracking-wider">Belum ada folder di kategori Other.</p>
                        </div>
                    @endforelse

                @elseif(request('category') == 'divisi' && !request('div'))
                    @php
                        $divIcons = [
                            'RETAIL'     => 'ph-gas-pump',
                            'COMMERCIAL' => 'ph-boat',
                            'KOMERSIL'   => 'ph-boat',
                            'SCM'        => 'ph-truck',
                            'FA'         => 'ph-bank',
                        ];
                    @endphp
                    @forelse($listDivisions as $d)
                        <a href="?category=divisi&div={{ urlencode($d) }}" class="p-4 bg-sand-50 hover:bg-sky-50/50 rounded-md border border-sand-200 hover:border-[#00b4d8]/50 transition-all flex items-center space-x-3.5 shadow-sm group">
                            <i class="ph {{ $divIcons[strtoupper($d)] ?? 'ph-folder' }} text-3xl text-[#00b4d8] group-hover:scale-110 transition-transform flex-shrink-0"></i>
                            <div>
                                <span class="font-bold text-xs text-on-surface capitalize tracking-wide group-hover:text-[#1677B8] block">{{ $d }}</span>
                                <span class="text-[10px] text-on-surface-variant font-medium">
                                    {{ count($divBuMap[strtoupper($d)] ?? []) }} Unit Bisnis
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full py-16 text-center text-on-surface-variant space-y-2">
                            <i class="ph ph-folder-open text-5xl text-[#00b4d8]/60"></i>
                            <p class="text-xs font-bold capitalize tracking-wider">Belum ada Divisi yang diarsip.</p>
                        </div>
                    @endforelse

                @elseif(request('category') == 'support')
                    @php
                        $supportIcons = [
                            'HC'             => 'ph-users',
                            'IT'             => 'ph-cpu',
                            'HSE'            => 'ph-shield-check',
                            'QMS'            => 'ph-seal-check',
                            'INTERNAL AUDIT' => 'ph-clipboard-text',
                            'LOGISTIC'       => 'ph-truck',
                            'OPS'            => 'ph-gear',
                            'FINANCE'        => 'ph-currency-dollar',
                            'LEGAL'          => 'ph-scales',
                        ];
                    @endphp
                    @foreach($supportDepts as $dept)
                        <a href="?category=support&bu={{ urlencode($dept) }}" class="p-4 bg-sand-50 hover:bg-sky-50/50 rounded-md border border-sand-200 hover:border-[#00b4d8]/50 transition-all flex items-center space-x-3.5 shadow-sm group">
                            <i class="ph {{ $supportIcons[$dept] ?? 'ph-headset' }} text-3xl text-[#00b4d8] group-hover:scale-110 transition-transform flex-shrink-0"></i>
                            <span class="font-bold text-xs text-on-surface capitalize tracking-wide group-hover:text-[#1677B8]">{{ $dept }}</span>
                        </a>
                    @endforeach

                @elseif(request('div'))
                    @php 
                        $selectedDiv = (strtoupper(request('div')) === 'COMMERCIAL') ? 'KOMERSIL' : strtoupper(request('div'));
                        $bus = $divBuMap[$selectedDiv] ?? \App\Models\Library::where('division_name', request('div'))->distinct()->pluck('business_unit'); 
                        $buIcons = [
                            'SPBU'                  => 'ph-gas-pump',
                            'LPG PSO'               => 'ph-fire',
                            'LPG NPSO'              => 'ph-flame',
                            'PKSP'                  => 'ph-wrench',
                            'TRP'                   => 'ph-truck',
                            'INMAR (CNGM)'          => 'ph-anchor',
                            'INMAR'                 => 'ph-anchor',
                            'CPT & MHM'             => 'ph-boat',
                            'SBS'                   => 'ph-anchor-simple',
                            'GVI'                   => 'ph-fire-simple',
                            'PROCUREMENT'           => 'ph-shopping-cart',
                            'WAREHOUSE'             => 'ph-warehouse',
                            'ASET'                  => 'ph-buildings',
                            'GA'                    => 'ph-wrench',
                            'KEUANGAN & ACCOUNTING' => 'ph-currency-dollar',
                        ];
                    @endphp
                    @foreach($bus as $b)
                        <a href="?category=divisi&div={{ request('div') }}&bu={{ urlencode($b) }}" class="p-4 bg-sand-50 hover:bg-sky-50/50 rounded-md border border-sand-200 hover:border-[#00b4d8]/50 transition-all flex items-center space-x-3.5 shadow-sm group">
                            <i class="ph {{ $buIcons[$b] ?? 'ph-storefront' }} text-3xl text-[#00b4d8] group-hover:scale-110 transition-transform flex-shrink-0"></i>
                            <span class="font-bold text-xs text-on-surface capitalize tracking-wide group-hover:text-[#1677B8]">{{ $b }}</span>
                        </a>
                    @endforeach
                @endif
            </div>

        <!-- LEVEL 4: DOKUMEN MUTU & SUBFOLDER TAB (Jika di level Unit/BU atau Search Mode) -->
        @else
            <!-- TABS SUBFOLDER STANDAR ISO & FOLDER DEPARTEMEN -->
            @if(request('bu') || (request('category') === 'support' && request('bu')) || (request('category') === 'divisi' && request('bu')))
                @php
                    $currentScopeName = request('bu') ?: (request('div') ?: request('category'));
                @endphp

                <!-- SECTION FOLDER DIREKTORI DEPARTEMEN / UNIT -->
                <div class="space-y-3 pb-2 border-b border-sand-200/60">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="ph ph-folders text-lg text-[#1677B8]"></i>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Folder & Direktori {{ $currentScopeName }}</h3>
                        </div>
                    </div>

                    @if(isset($departmentFolders) && $departmentFolders->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            @foreach($departmentFolders as $df)
                                <div class="p-3.5 bg-sand-50 hover:bg-sky-50/50 rounded-[2px] border border-sand-200 hover:border-[#00b4d8]/60 transition-all flex items-center justify-between shadow-xs group">
                                    <a href="{{ route('library.folder.show', $df->id) }}" class="flex items-center space-x-3 flex-1 min-w-0">
                                        <i class="ph ph-folder text-2xl text-[#00b4d8] group-hover:scale-110 transition-transform flex-shrink-0"></i>
                                        <div class="min-w-0 flex-1">
                                            <span class="font-bold text-xs text-slate-900 group-hover:text-[#1677B8] truncate block">{{ $df->name }}</span>
                                            <span class="text-[10px] text-slate-500 font-medium">
                                                {{ $df->files->count() }} Berkas
                                                @if($df->children->count() > 0)
                                                    &bull; {{ $df->children->count() }} Subfolder
                                                @endif
                                            </span>
                                        </div>
                                    </a>
                                    @if(Auth::user()?->role === 'admin')
                                        <form action="{{ route('admin.library.folder.destroy', $df->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus folder ini beserta seluruh isinya?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-6 h-6 bg-white hover:bg-rose-50 text-rose-600 rounded border border-slate-200 flex items-center justify-center transition-all cursor-pointer opacity-0 group-hover:opacity-100" title="Hapus Folder">
                                                <i class="ph ph-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-3 bg-slate-50 rounded-[2px] border border-dashed border-slate-300 flex items-center justify-between text-xs text-slate-500">
                            <span class="flex items-center gap-1.5 font-medium">
                                <i class="ph ph-folder-dashed text-base text-slate-400"></i>
                                <span>Belum ada sub-folder khusus di departemen ini. Buat folder untuk mengelompokkan formulir, panduan, atau berkas pendukung.</span>
                            </span>
                        </div>
                    @endif
                </div>

                <!-- TABS SUBFOLDER STANDAR ISO -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-sand-200/60 custom-scrollbar">
                    <button type="button" onclick="filterFolderTab('all')" id="tab-btn-all" class="folder-tab-btn px-4 py-2 rounded-[2px] font-bold text-xs capitalize tracking-wider bg-[#1677B8] text-white shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="ph ph-folder-open text-sm"></i>
                        <span>Semua Dokumen SOP</span>
                    </button>
                    <button type="button" onclick="filterFolderTab('dokumen-mutu')" id="tab-btn-dokumen-mutu" class="folder-tab-btn px-4 py-2 rounded-[2px] font-bold text-xs capitalize tracking-wider bg-sand-50 text-on-surface-variant hover:bg-[#fff9ed] hover:text-[#1677B8] border border-sand-200 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="ph ph-seal-check text-sm"></i>
                        <span>1. Dokumen Mutu</span>
                    </button>
                    <button type="button" onclick="filterFolderTab('sop')" id="tab-btn-sop" class="folder-tab-btn px-4 py-2 rounded-[2px] font-bold text-xs capitalize tracking-wider bg-sand-50 text-on-surface-variant hover:bg-[#fff9ed] hover:text-[#1677B8] border border-sand-200 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="ph ph-files text-sm"></i>
                        <span>2. SOP Sah</span>
                    </button>
                    <button type="button" onclick="filterFolderTab('jobdesk')" id="tab-btn-jobdesk" class="folder-tab-btn px-4 py-2 rounded-[2px] font-bold text-xs capitalize tracking-wider bg-sand-50 text-on-surface-variant hover:bg-[#fff9ed] hover:text-[#1677B8] border border-sand-200 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="ph ph-identification-card text-sm"></i>
                        <span>3. Jobdesk</span>
                    </button>
                    <button type="button" onclick="filterFolderTab('kpi')" id="tab-btn-kpi" class="folder-tab-btn px-4 py-2 rounded-[2px] font-bold text-xs capitalize tracking-wider bg-sand-50 text-on-surface-variant hover:bg-[#fff9ed] hover:text-[#1677B8] border border-sand-200 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="ph ph-target text-sm"></i>
                        <span>4. KPI & Target</span>
                    </button>
                    <button type="button" onclick="filterFolderTab('ik-forms')" id="tab-btn-ik-forms" class="folder-tab-btn px-4 py-2 rounded-[2px] font-bold text-xs capitalize tracking-wider bg-sand-50 text-on-surface-variant hover:bg-[#fff9ed] hover:text-[#1677B8] border border-sand-200 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="ph ph-clipboard-text text-sm"></i>
                        <span>5. IK & Formulir</span>
                    </button>
                </div>
            @endif

            <!-- 3-COLUMN DOCUMENT CARDS GRID -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="documentsGrid">
                @forelse($documents as $doc)
                @php
                    $tabCategory = $doc->doc_type ?? null;
                    if (!$tabCategory) {
                        $docTitleLower = strtolower($doc->title);
                        $tabCategory = 'sop';
                        if (str_contains($docTitleLower, 'visi') || str_contains($docTitleLower, 'misi') || str_contains($docTitleLower, 'kebijakan') || str_contains($docTitleLower, 'mutu') || str_contains($docTitleLower, 'peta proses') || str_contains($docTitleLower, 'pedoman')) {
                            $tabCategory = 'dokumen-mutu';
                        } elseif (str_contains($docTitleLower, 'job') || str_contains($docTitleLower, 'desk') || str_contains($docTitleLower, 'uraian jabatan') || str_contains($docTitleLower, 'job description')) {
                            $tabCategory = 'jobdesk';
                        } elseif (str_contains($docTitleLower, 'kpi') || str_contains($docTitleLower, 'target') || str_contains($docTitleLower, 'kinerja') || str_contains($docTitleLower, 'sasaran')) {
                            $tabCategory = 'kpi';
                        } elseif (str_contains($docTitleLower, 'ik') || str_contains($docTitleLower, 'instruksi') || str_contains($docTitleLower, 'form') || str_contains($docTitleLower, 'formulir')) {
                            $tabCategory = 'ik-forms';
                        }
                    }
                @endphp
                <div class="doc-card p-5 bg-sand-50 rounded-lg border border-sand-200/70 hover:border-gold-500/40 transition-all flex flex-col justify-between h-60 shadow-sm relative group" data-tab-category="{{ $tabCategory }}">
                    
                    @if(Auth::user()?->role === 'admin')
                    <div class="absolute right-4 top-4 z-20">
                        <form action="{{ route('admin.library.destroy', ['id' => $doc->id]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini seutuhnya dari sistem Library?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-7 h-7 bg-white text-red-600 hover:bg-red-50 rounded shadow-sm border border-sand-200 flex items-center justify-center transition-all cursor-pointer">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </form>
                    </div>
                    @endif

                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <i class="ph ph-file-pdf text-3xl text-[#00b4d8]"></i>
                            <span class="text-[9px] font-mono font-bold text-on-surface-variant bg-white px-2 py-0.5 rounded border border-sand-200">
                                {{ $doc->created_at ? $doc->created_at->format('Y') : '-' }}
                            </span>
                        </div>
                        <h4 class="font-bold text-on-surface text-xs line-clamp-2 leading-relaxed pr-6 capitalize">{{ $doc->title }}</h4>
                        <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] text-[#1677B8] font-bold capitalize">{{ $doc->business_unit ?? $doc->division_name ?? $doc->category }}</span>
                            <span class="text-sand-300">•</span>
                            <span class="text-[9px] text-on-surface-variant font-semibold capitalize truncate">{{ $doc->company_name }}</span>
                        </div>
                    </div>

                    <x-interactive-button text="Lihat Dokumen Sah" type="button" icon="ph ph-eye text-sm" onclick="viewPDF('{{ route('library.document.stream', $doc->id) }}', '{{ addslashes($doc->title) }}')" class="w-full" />
                </div>
                @empty
                <div class="col-span-full py-16 flex flex-col items-center justify-center text-center space-y-3">
                    <span class="material-symbols-outlined text-5xl text-[#d6cebf]">folder_off</span>
                    <h5 class="text-sm font-bold text-on-surface capitalize">Belum Ada Dokumen Yang Terarsip</h5>
                    <p class="text-xs text-on-surface-variant max-w-sm">Dokumen aktif akan otomatis masuk ke E-Library setelah seluruh alur tanda tangan selesai.</p>
                    
                    <div class="flex items-center gap-3 pt-2">
                        <x-back-button href="{{ request()->is('admin/*') ? route('admin.library.index') : route('library.index') }}" text="Kembali" />
                        @if(Auth::user()?->role === 'admin' && request('bu'))
                        <x-interactive-button type="button" text="Upload Sekarang" onclick="openUploadManualModal()" />
                        @endif
                    </div>
                </div>
                @endforelse
            </div>
        @endif
    </div>
@endif
</div>

<!-- PROTECTED PDF VIEWER COMPONENT (CANVAS / NO DOWNLOAD) -->
<x-protected-pdf-viewer />


<!-- MODAL UPLOAD BERKAS & DOKUMEN E-LIBRARY ADMIN -->
@if(Auth::user()?->role === 'admin')
<div id="uploadManualModal" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl w-full max-w-lg shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-cloud-arrow-up text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Unggah Dokumen / Berkas E-Library</h3>
                    <p class="text-[10px] text-white/85 font-medium">Unggah dokumen ke Tab Kategori SOP atau ke Folder Direktori</p>
                </div>
            </div>
            <button type="button" onclick="closeUploadManualModal()" class="text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <form action="{{ route('admin.library.upload') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            
            <input type="hidden" name="category" value="{{ request('category') }}">
            <input type="hidden" name="division" value="{{ request('div') }}">
            <input type="hidden" name="department" value="{{ request('category') === 'support' ? request('bu') : '' }}">
            <input type="hidden" name="business_unit" value="{{ request('category') === 'divisi' ? request('bu') : '' }}">

            <!-- PILIH TUJUAN UNGGAHAN (TAB KATEGORI / FOLDER) -->
            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Pilih Folder / Tab Kategori Tujuan <span class="text-rose-500">*</span></label>
                <select name="folder_id" id="upload_folder_select" onchange="toggleNewFolderInput()" class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:ring-1 focus:ring-[#1677B8] font-bold text-slate-800 bg-white outline-none">
                    <optgroup label="📋 Tab Kategori Dokumen Mutu & SOP">
                        <option value="tab:sop" selected>2. SOP Sah (Standard Operating Procedure)</option>
                        <option value="tab:dokumen-mutu">1. Dokumen Mutu (Manual / Pedoman Mutu)</option>
                        <option value="tab:jobdesk">3. Jobdesk (Uraian Jabatan)</option>
                        <option value="tab:kpi">4. KPI & Target (Sasaran Mutu)</option>
                        <option value="tab:ik-forms">5. IK & Formulir Standar</option>
                    </optgroup>

                    <optgroup label="📁 Folder / Direktori Khusus {{ request('bu') ?: (request('div') ?: 'Departemen') }}">
                        <option value="">-- Direktori Utama (Root {{ request('bu') ?: (request('div') ?: 'Departemen') }}) --</option>
                        @if(isset($departmentFolders) && $departmentFolders->count() > 0)
                            @foreach($departmentFolders as $df)
                                <option value="{{ $df->id }}">{{ $df->name }}</option>
                                @foreach($df->children as $sub)
                                    <option value="{{ $sub->id }}">&nbsp;&nbsp;↳ {{ $sub->name }}</option>
                                @endforeach
                            @endforeach
                        @endif
                    </optgroup>

                    <option value="__create_new__">+ Buat Folder Baru Sekaligus...</option>
                </select>
            </div>

            <!-- INPUT NAMA FOLDER BARU (JIKA PILIH BUAT BARU) -->
            <div id="field_new_folder_inline" class="hidden">
                <label class="text-[11px] font-bold text-[#1677B8] uppercase tracking-wider mb-1 block">Nama Folder Baru Yang Ingin Dibuat</label>
                <input type="text" name="new_folder_name" placeholder="Ketik nama folder baru..." class="w-full bg-slate-50 border border-blue-300 rounded-[2px] p-2 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
            </div>

            <!-- NOMOR DOKUMEN (OPSIONAL) -->
            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Nomor Dokumen <span class="text-slate-400 font-normal">(Opsional)</span></label>
                <input type="text" name="doc_number" class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none" placeholder="Contoh: SOP-PKM-HC-001">
            </div>

            <!-- JUDUL / NAMA BERKAS -->
            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Judul Dokumen / Nama Berkas <span class="text-rose-500">*</span></label>
                <input type="text" name="custom_name" required class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none" placeholder="Ketik judul lengkap dokumen / berkas...">
            </div>

            <!-- PILIH FILE -->
            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Pilih Berkas File <span class="text-rose-500">*</span></label>
                <x-file-input name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip" label="Pilih berkas dokumen" hint="PDF, Word, Excel, PPT, Gambar, ZIP (Maksimal 30 MB)" :required="true" :maxSize="30" />
            </div>
            
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeUploadManualModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-[2px] transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs rounded-[2px] transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-cloud-arrow-up"></i>
                    <span>Unggah Dokumen</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- MODAL CREATE FOLDER -->
@if(Auth::user()?->role === 'admin')
<div id="createFolderModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl p-6 max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-md bg-blue-50 text-[#1677B8] flex items-center justify-center font-bold">
                    <i class="ph ph-folder-plus text-base"></i>
                </div>
                <h3 class="font-extrabold text-sm text-slate-900 capitalize tracking-wider">
                    Buat Folder Baru {{ request('bu') ? 'di ' . request('bu') : (request('div') ? 'di ' . request('div') : '') }}
                </h3>
            </div>
            <button onclick="closeCreateFolderModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer text-lg">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <form action="{{ route('admin.library.folder.create') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="parent_id" value="">
            <input type="hidden" name="category" value="{{ request('category') }}">
            <input type="hidden" name="division" value="{{ request('div') }}">
            <input type="hidden" name="department" value="{{ request('category') === 'support' ? request('bu') : '' }}">
            <input type="hidden" name="business_unit" value="{{ request('category') === 'divisi' ? request('bu') : '' }}">

            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Nama Folder <span class="text-rose-500">*</span></label>
                <input type="text" name="name" class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none" placeholder="Contoh: Formulir {{ request('bu') ?? 'HC' }}, Materi Training, Kebijakan Internal..." required>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeCreateFolderModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-[2px] transition-all cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs rounded-[2px] transition-all shadow-xs cursor-pointer">
                    Buat Folder
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function toggleNewFolderInput() {
        const select = document.getElementById('upload_folder_select');
        const inlineField = document.getElementById('field_new_folder_inline');
        if (select && inlineField) {
            if (select.value === '__create_new__') {
                inlineField.classList.remove('hidden');
            } else {
                inlineField.classList.add('hidden');
            }
        }
    }
</script>

<script>
    function toggleUploadCategoryFields() {
        const catType = document.getElementById('upload_category_type').value;
        const fGeneral = document.getElementById('field_general_folder');
        const fBu = document.getElementById('field_bu_selector');
        const fSupport = document.getElementById('field_support_selector');

        if (catType === 'general') {
            fGeneral.classList.remove('hidden');
            fBu.classList.add('hidden');
            fSupport.classList.add('hidden');
        } else if (catType === 'bu') {
            fGeneral.classList.add('hidden');
            fBu.classList.remove('hidden');
            fSupport.classList.add('hidden');
        } else if (catType === 'support') {
            fGeneral.classList.add('hidden');
            fBu.classList.add('hidden');
            fSupport.classList.remove('hidden');
        }
    }
</script>

<script>
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

    function filterFolderTab(category) {
        // Reset button active styles
        document.querySelectorAll('.folder-tab-btn').forEach(btn => {
            btn.classList.remove('bg-[#1677B8]', 'text-white', 'shadow-sm');
            btn.classList.add('bg-sand-50', 'text-on-surface-variant', 'border', 'border-sand-200');
        });

        const activeBtn = document.getElementById('tab-btn-' + category);
        if (activeBtn) {
            activeBtn.classList.remove('bg-sand-50', 'text-on-surface-variant', 'border', 'border-sand-200');
            activeBtn.classList.add('bg-[#1677B8]', 'text-white', 'shadow-sm');
        }

        // Filter cards in grid
        const cards = document.querySelectorAll('#documentsGrid .doc-card');
        cards.forEach(card => {
            const cardCat = card.getAttribute('data-tab-category');
            if (category === 'all' || cardCat === category) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // ESC Key to close any open modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            closePDF();
            if (typeof closeUploadManualModal === 'function') closeUploadManualModal();
            if (typeof closeCreateFolderModal === 'function') closeCreateFolderModal();
        }
    });
</script>
@endsection
