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
                    @if($isCptScope && $canViewCptContracts)
                    <button type="button" onclick="filterFolderTab('cpt-contracts')" id="tab-btn-cpt-contracts" class="folder-tab-btn px-4 py-2 rounded-[2px] font-bold text-xs capitalize tracking-wider bg-sand-50 text-on-surface-variant hover:bg-sky-50 hover:text-[#1677B8] border border-sand-200 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="ph ph-file-text text-sm text-[#1677B8]"></i>
                        <span>6. Register Kontrak & SPMP (CPT)</span>
                        @if($canManageCptContracts)
                        <span class="ml-1 text-[10px] bg-[#1677B8] text-white font-extrabold px-2 py-0.5 rounded-full">{{ $cptTotalCount }}</span>
                        @endif
                    </button>
                    @endif
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

            <!-- 📑 SECTION KHUSUS: TABEL REGISTER KONTRAK & SPMP BU CPT (PT Cahaya Perdana Transalam) -->
            @if($isCptScope && $canViewCptContracts)
            <div id="cptContractsSection" class="hidden space-y-6">
                <!-- HEADER & ACTIONS (RESPONSIVE FLEX FOR 125% ZOOM) -->
                <div class="p-5 bg-gradient-to-r from-slate-900 via-[#103A60] to-[#1677B8] text-white rounded-lg shadow-sm flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0 text-white border border-white/10">
                            <i class="ph ph-file-text text-2xl text-sky-300"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-extrabold tracking-tight">Register Kontrak & Kerjasama Proyek</h3>
                                <span class="bg-amber-400/20 text-amber-300 border border-amber-400/30 text-[10px] font-black px-2 py-0.5 rounded uppercase flex-shrink-0">BU CPT</span>
                            </div>
                            <p class="text-xs text-sky-100/80 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                <span>PT Cahaya Perdana Transalam</span>
                                <span class="text-sky-300/60">&bull;</span>
                                <span class="text-white/90">PIC Pengelola:</span>
                                <span class="font-extrabold text-amber-300 bg-amber-400/10 px-1.5 py-0.5 rounded border border-amber-400/20">
                                    {{ $currentCptPic ? ($currentCptPic->full_name ?: $currentCptPic->username) : 'Belum Ditentukan' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS (NO WRAP COLLISION ON 125% ZOOM) -->
                    <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap justify-start xl:justify-end">
                        @if($canAssignCptPic)
                        <button type="button" onclick="openAssignPicModal()" class="px-3 py-2 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded border border-white/20 shadow-xs transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer" title="Atur Staf PIC Pengelola Kontrak">
                            <i class="ph ph-user-gear text-sm text-sky-300"></i>
                            <span>Atur PIC Staf CPT</span>
                        </button>
                        @endif

                        @if($canManageCptContracts)
                        <button type="button" onclick="openImportContractModal()" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded shadow-xs transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                            <i class="ph ph-file-arrow-up text-sm"></i>
                            <span>Import Excel / CSV</span>
                        </button>
                        <button type="button" onclick="openCreateContractModal()" class="px-4 py-2 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs rounded shadow-sm transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                            <i class="ph ph-plus-circle text-sm"></i>
                            <span>+ Tambah Kontrak Baru</span>
                        </button>
                        @endif
                    </div>
                </div>

                @if($canManageCptContracts)
                <!-- BENTO METRICS CARDS -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    <div class="p-3.5 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Total Kontrak</span>
                        <div class="flex items-baseline gap-1.5 mt-1">
                            <span class="text-xl font-black text-slate-800">{{ $cptTotalCount }}</span>
                            <span class="text-[11px] text-slate-500 font-semibold">Dokumen</span>
                        </div>
                    </div>
                    <div class="p-3.5 bg-sky-50/60 rounded-lg border border-sky-200">
                        <span class="text-[10px] font-bold text-sky-700 uppercase tracking-wider block">Active</span>
                        <div class="flex items-baseline gap-1.5 mt-1">
                            <span class="text-xl font-black text-sky-800">{{ $cptActiveCount }}</span>
                            <span class="text-[11px] text-sky-600 font-semibold">Berjalan</span>
                        </div>
                    </div>
                    <div class="p-3.5 bg-amber-50/60 rounded-lg border border-amber-200">
                        <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider block">Still Not Yet</span>
                        <div class="flex items-baseline gap-1.5 mt-1">
                            <span class="text-xl font-black text-amber-800">{{ $cptStillNotYetCount }}</span>
                            <span class="text-[11px] text-amber-600 font-semibold">Tahap Awal</span>
                        </div>
                    </div>
                    <div class="p-3.5 bg-rose-50/60 rounded-lg border border-rose-200">
                        <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block">Expired</span>
                        <div class="flex items-baseline gap-1.5 mt-1">
                            <span class="text-xl font-black text-rose-800">{{ $cptExpiredCount }}</span>
                            <span class="text-[11px] text-rose-600 font-semibold">Kadaluarsa</span>
                        </div>
                    </div>
                    <div class="p-3.5 bg-emerald-50/60 rounded-lg border border-emerald-200 col-span-2 sm:col-span-1">
                        <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Completed</span>
                        <div class="flex items-baseline gap-1.5 mt-1">
                            <span class="text-xl font-black text-emerald-800">{{ $cptCompletedCount }}</span>
                            <span class="text-[11px] text-emerald-600 font-semibold">Selesai</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER & SEARCH BAR -->
                <form method="GET" action="{{ route('library.index') }}" class="p-4 bg-sand-50/80 rounded-lg border border-sand-200 flex flex-col md:flex-row items-center gap-3">
                    <input type="hidden" name="category" value="{{ request('category', 'divisi') }}">
                    <input type="hidden" name="div" value="{{ request('div', 'KOMERSIL') }}">
                    <input type="hidden" name="bu" value="{{ request('bu', 'CPT & MHM') }}">
                    <input type="hidden" name="tab" value="contracts">

                    <div class="relative flex-1 w-full">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" name="cpt_search" value="{{ request('cpt_search') }}" placeholder="Cari judul proyek, no. kontrak, customer, catatan..." class="w-full pl-9 pr-3 py-2 bg-white rounded border border-sand-300 text-xs font-semibold text-slate-800 focus:ring-1 focus:ring-[#1677B8] focus:border-[#1677B8] outline-none">
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <select name="cpt_status" class="px-3 py-2 bg-white rounded border border-sand-300 text-xs font-bold text-slate-700 focus:ring-1 focus:ring-[#1677B8] outline-none flex-1 md:w-40">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('cpt_status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="still_not_yet" {{ request('cpt_status') === 'still_not_yet' ? 'selected' : '' }}>Still Not Yet</option>
                            <option value="expired" {{ request('cpt_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="completed" {{ request('cpt_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>

                        <select name="cpt_customer" class="px-3 py-2 bg-white rounded border border-sand-300 text-xs font-bold text-slate-700 focus:ring-1 focus:ring-[#1677B8] outline-none flex-1 md:w-48">
                            <option value="">Semua Customer</option>
                            @foreach($cptCustomers as $cst)
                                <option value="{{ $cst }}" {{ request('cpt_customer') === $cst ? 'selected' : '' }}>{{ $cst }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="px-4 py-2 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs rounded transition-all flex items-center gap-1 cursor-pointer">
                            <i class="ph ph-funnel"></i>
                            <span>Filter</span>
                        </button>

                        @if(request('cpt_search') || request('cpt_status') || request('cpt_customer'))
                        <a href="?category={{ request('category', 'divisi') }}&div={{ request('div', 'KOMERSIL') }}&bu={{ request('bu', 'CPT & MHM') }}&tab=contracts" class="px-3 py-2 bg-white hover:bg-rose-50 text-rose-600 border border-sand-300 rounded font-bold text-xs transition-all flex items-center gap-1">
                            <i class="ph ph-x"></i>
                            <span>Reset</span>
                        </a>
                        @endif
                    </div>
                </form>

                <!-- CONTRACTS DATA TABLE -->
                <div class="overflow-x-auto rounded-lg border border-sand-200/80 shadow-sm bg-white">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gradient-to-r from-slate-100 via-sand-100 to-slate-100 text-slate-800 text-[11px] font-black uppercase tracking-wider border-b border-sand-200">
                                <th class="py-3 px-3 w-10 text-center">No</th>
                                <th class="py-3 px-4 min-w-[150px]">Customer</th>
                                <th class="py-3 px-3 min-w-[100px]">Type</th>
                                <th class="py-3 px-4 min-w-[220px]">Project Title</th>
                                <th class="py-3 px-3 min-w-[120px]">Project Name</th>
                                <th class="py-3 px-3 min-w-[160px]">Project Number</th>
                                <th class="py-3 px-3 min-w-[95px]">Start Date</th>
                                <th class="py-3 px-3 min-w-[95px]">End Date</th>
                                <th class="py-3 px-3 min-w-[110px] text-center">Status</th>
                                <th class="py-3 px-4 min-w-[180px]">Note</th>
                                <th class="py-3 px-3 min-w-[110px] text-center">Dokumen</th>
                                <th class="py-3 px-3 w-28 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sand-200">
                            @forelse($cptContracts as $idx => $contract)
                            <tr class="hover:bg-sky-50/40 transition-colors {{ $idx % 2 == 0 ? 'bg-white' : 'bg-sand-50/30' }}">
                                <td class="py-3 px-3 text-center font-bold text-slate-500">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4 font-extrabold text-slate-900">{{ $contract->customer }}</td>
                                <td class="py-3 px-3">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $contract->type ?: ($contract->contract_type ?: '-') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800 leading-relaxed">{{ $contract->project_title }}</td>
                                <td class="py-3 px-3 font-bold text-[#1677B8]">{{ $contract->project_name }}</td>
                                <td class="py-3 px-3 font-mono font-bold text-[11px] text-slate-700">{{ $contract->project_number }}</td>
                                <td class="py-3 px-3 text-slate-600 font-medium whitespace-nowrap">{{ $contract->start_date ? $contract->start_date->format('d M Y') : '-' }}</td>
                                <td class="py-3 px-3 text-slate-600 font-medium whitespace-nowrap">{{ $contract->end_date ? $contract->end_date->format('d M Y') : '-' }}</td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold uppercase tracking-wide border {{ $contract->status_badge_class }}">
                                        {{ $contract->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-600 leading-snug">
                                    {{ $contract->notes ?: '-' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($contract->document_file)
                                        <button type="button" onclick="viewPDF('{{ route('cpt_contracts.document', $contract->id) }}', '{{ addslashes($contract->project_title ?? $contract->project_name) }}')" class="px-2.5 py-1 bg-sky-50 hover:bg-[#1677B8] text-[#1677B8] hover:text-white rounded border border-sky-200 text-[11px] font-bold transition-all flex items-center justify-center gap-1 mx-auto cursor-pointer" title="Lihat Berkas PDF">
                                            <i class="ph ph-file-pdf text-xs"></i>
                                            <span>Lihat PDF</span>
                                        </button>
                                    @elseif($contract->document_link)
                                        <a href="{{ $contract->document_link }}" target="_blank" rel="noopener noreferrer" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-500 text-amber-800 hover:text-white rounded border border-amber-200 text-[11px] font-bold transition-all flex items-center justify-center gap-1 mx-auto" title="Buka Link Google Drive / Berkas Luar">
                                            <i class="ph ph-arrow-square-out text-xs"></i>
                                            <span>Link Drive</span>
                                        </a>
                                    @else
                                        <span class="text-[10px] text-slate-400 font-medium italic">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="openEditContractModal({{ json_encode($contract) }})" class="w-7 h-7 bg-white hover:bg-sky-50 text-[#1677B8] rounded border border-sand-300 flex items-center justify-center transition-all cursor-pointer" title="Edit Kontrak">
                                            <i class="ph ph-pencil-simple text-xs"></i>
                                        </button>
                                        <form action="{{ route('cpt_contracts.destroy', $contract->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data kontrak nomor {{ $contract->project_number }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-7 h-7 bg-white hover:bg-rose-50 text-rose-600 rounded border border-sand-300 flex items-center justify-center transition-all cursor-pointer" title="Hapus Kontrak">
                                                <i class="ph ph-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <i class="ph ph-file-dashed text-4xl text-slate-400"></i>
                                        <p class="text-xs font-bold text-slate-700">Tidak ada data kontrak CPT yang sesuai dengan filter.</p>
                                        <p class="text-[11px] text-slate-500">Klik tombol "+ Tambah Kontrak Baru" atau "Import Excel / CSV" untuk mendaftarkan kontrak kerja sama baru.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @else
                <!-- ADMIN RESTRICTED NOTICE (ISOLASI AKSES KONTRAK) -->
                <div class="p-10 bg-slate-50/80 border border-slate-200/80 rounded-xl text-center space-y-4 max-w-2xl mx-auto my-6 shadow-2xs">
                    <div class="w-14 h-14 rounded-full bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center mx-auto text-2xl font-black">
                        <i class="ph ph-shield-check"></i>
                    </div>
                    <div class="space-y-1.5">
                        <h4 class="text-base font-black text-slate-800">Akses Data Kontrak Dibatasi Khusus PIC</h4>
                        <p class="text-xs text-slate-600 leading-relaxed max-w-lg mx-auto">
                            Sesuai kebijakan keamanan, tabel Register Kontrak & SPMP BU CPT bersifat konfidensial dan hanya dapat dilihat serta dikelola oleh Staf PIC CPT yang ditunjuk.
                        </p>
                    </div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 shadow-2xs">
                        <i class="ph ph-user-check text-base text-emerald-600"></i>
                        <span>PIC Pengelola Aktif:</span>
                        <span class="text-[#1677B8] font-extrabold">{{ $currentCptPic ? ($currentCptPic->full_name ?: $currentCptPic->username) : 'Belum Ditentukan' }}</span>
                    </div>
                    @if($canAssignCptPic)
                    <div class="pt-2">
                        <button type="button" onclick="openAssignPicModal()" class="px-4 py-2 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs rounded shadow-xs transition-all inline-flex items-center gap-1.5 cursor-pointer">
                            <i class="ph ph-user-gear text-sm"></i>
                            <span>Atur / Ganti Staf PIC CPT</span>
                        </button>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endif
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

<!-- 📥 MODAL IMPORT KONTRAK CPT (EXCEL / CSV) -->
@if($canManageCptContracts)
<div id="importContractModal" class="fixed inset-0 z-[110] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl w-full max-w-2xl shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-[#103A60] to-[#1677B8] text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-file-arrow-up text-lg text-emerald-300"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Import Register Kontrak CPT (Excel / CSV)</h3>
                    <p class="text-[10px] text-white/85 font-medium">Unggah berkas spreadsheet Excel (.xlsx) atau CSV (.csv)</p>
                </div>
            </div>
            <button type="button" onclick="closeImportContractModal()" class="text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <form action="{{ route('cpt_contracts.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 max-h-[82vh] overflow-y-auto custom-scrollbar">
            @csrf
            
            <div class="p-4 bg-sky-50/70 rounded-lg border border-sky-200/80 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                            <i class="ph ph-file-xls text-base"></i>
                        </div>
                        <div>
                            <span class="text-xs font-black text-slate-800 block leading-tight">Format Template Spreadsheet</span>
                            <span class="text-[10px] text-slate-500 font-medium">Gunakan template resmi untuk kemudahan input massal</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <a href="{{ route('cpt_contracts.template', ['format' => 'excel']) }}" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-600 text-white rounded text-[11px] font-extrabold flex items-center gap-1.5 shadow-xs transition-all cursor-pointer" title="Download Template Microsoft Excel">
                            <i class="ph ph-file-xls text-sm"></i>
                            <span>Unduh Template Excel (.xls)</span>
                        </a>
                        <a href="{{ route('cpt_contracts.template', ['format' => 'csv']) }}" class="px-2.5 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded text-[11px] font-bold flex items-center gap-1 shadow-2xs transition-all cursor-pointer" title="Download Template CSV">
                            <i class="ph ph-file-csv text-sm text-[#1677B8]"></i>
                            <span>CSV</span>
                        </a>
                    </div>
                </div>

                <!-- PREVIEW TABEL STRUKTUR TEMPLATE -->
                <div class="space-y-1.5">
                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">Susunan Kolom & Contoh Format Data:</span>
                    <div class="overflow-x-auto rounded border border-slate-200 bg-white shadow-2xs max-h-40 custom-scrollbar">
                        <table class="w-full text-left border-collapse text-[10px]">
                            <thead>
                                <tr class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                                    <th class="p-1.5 border-r border-slate-200 whitespace-nowrap">No</th>
                                    <th class="p-1.5 border-r border-slate-200 whitespace-nowrap">Customer</th>
                                    <th class="p-1.5 border-r border-slate-200 whitespace-nowrap">Type</th>
                                    <th class="p-1.5 border-r border-slate-200 whitespace-nowrap">Project Title</th>
                                    <th class="p-1.5 border-r border-slate-200 whitespace-nowrap">Project Name</th>
                                    <th class="p-1.5 border-r border-slate-200 whitespace-nowrap">Project Number</th>
                                    <th class="p-1.5 border-r border-slate-200 whitespace-nowrap">Start Date</th>
                                    <th class="p-1.5 border-r border-slate-200 whitespace-nowrap">End Date</th>
                                    <th class="p-1.5 border-r border-slate-200 whitespace-nowrap">Status</th>
                                    <th class="p-1.5 border-r border-slate-200 whitespace-nowrap">Note</th>
                                    <th class="p-1.5 whitespace-nowrap">Document Link</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-600">
                                <tr class="hover:bg-slate-50">
                                    <td class="p-1.5 font-bold border-r border-slate-100 text-center">1</td>
                                    <td class="p-1.5 border-r border-slate-100 font-bold text-slate-800 whitespace-nowrap">PT Patra Logistik</td>
                                    <td class="p-1.5 border-r border-slate-100 whitespace-nowrap">Kontrak</td>
                                    <td class="p-1.5 border-r border-slate-100 truncate max-w-[130px]">Jasa Transportir BBM Laut VHS PT Timah...</td>
                                    <td class="p-1.5 border-r border-slate-100 font-bold text-[#1677B8] whitespace-nowrap">Timah</td>
                                    <td class="p-1.5 border-r border-slate-100 font-mono text-[9px] whitespace-nowrap">KTR-457/PL000010/2023-S0</td>
                                    <td class="p-1.5 border-r border-slate-100 whitespace-nowrap">2023-08-01</td>
                                    <td class="p-1.5 border-r border-slate-100 whitespace-nowrap">2024-12-31</td>
                                    <td class="p-1.5 border-r border-slate-100 font-bold text-sky-600">active</td>
                                    <td class="p-1.5 border-r border-slate-100 truncate max-w-[100px]">Kontrak berjalan</td>
                                    <td class="p-1.5 truncate max-w-[100px] text-blue-600">https://drive.google.com/...</td>
                                </tr>
                                <tr class="hover:bg-slate-50 bg-slate-50/50">
                                    <td class="p-1.5 font-bold border-r border-slate-100 text-center">2</td>
                                    <td class="p-1.5 border-r border-slate-100 font-bold text-slate-800 whitespace-nowrap">PT Patra Logistik</td>
                                    <td class="p-1.5 border-r border-slate-100 whitespace-nowrap">Addendum</td>
                                    <td class="p-1.5 border-r border-slate-100 truncate max-w-[130px]">Pengangkutan Jasa BBM PLN UP 3 Dumai...</td>
                                    <td class="p-1.5 border-r border-slate-100 font-bold text-[#1677B8] whitespace-nowrap">PLN UP 3 Dumai</td>
                                    <td class="p-1.5 border-r border-slate-100 font-mono text-[9px] whitespace-nowrap">KTR-118/PL000010/2023-S0</td>
                                    <td class="p-1.5 border-r border-slate-100 whitespace-nowrap">2023-07-01</td>
                                    <td class="p-1.5 border-r border-slate-100 whitespace-nowrap">2023-12-31</td>
                                    <td class="p-1.5 border-r border-slate-100 font-bold text-rose-600">expired</td>
                                    <td class="p-1.5 border-r border-slate-100 truncate max-w-[100px]">Menunggu kontrak baru</td>
                                    <td class="p-1.5 truncate max-w-[100px] text-blue-600">https://drive.google.com/...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="text-[10px] text-slate-500 font-medium">
                    Nilai Status yang didukung: <span class="text-sky-700 font-bold">active</span>, <span class="text-amber-700 font-bold">still_not_yet</span>, <span class="text-rose-700 font-bold">expired</span>, <span class="text-emerald-700 font-bold">completed</span>.
                </div>
            </div>

            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Pilih Berkas Spreadsheet (.xlsx, .xls, .csv) <span class="text-rose-500">*</span></label>
                <input type="file" name="file" required accept=".xlsx,.xls,.csv,.txt" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-bold file:bg-[#1677B8] file:text-white hover:file:bg-[#125d91] cursor-pointer bg-slate-50 border border-slate-300 rounded p-2">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeImportContractModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-[2px] transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs rounded-[2px] transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-file-arrow-up"></i>
                    <span>Proses Import Data</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- 👤 MODAL ATUR PIC STAF CPT (HANYA ADMIN) -->
@if(Auth::user()?->role === 'admin')
<div id="assignPicModal" class="fixed inset-0 z-[110] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl w-full max-w-md shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-slate-900 to-[#1677B8] text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-user-gear text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Tetapkan Hak Akses Staf PIC CPT</h3>
                    <p class="text-[10px] text-white/85 font-medium">Pilih 1 staf yang berwenang mengelola Register Kontrak</p>
                </div>
            </div>
            <button type="button" onclick="closeAssignPicModal()" class="text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <form action="{{ route('cpt_contracts.assign_pic') }}" method="POST" class="p-6 space-y-4">
            @csrf
            
            <div class="p-3 bg-amber-50 rounded border border-amber-200 text-xs text-amber-800 space-y-1">
                <div class="font-bold flex items-center gap-1">
                    <i class="ph ph-shield-check text-sm"></i>
                    <span>Kebijakan Hak Akses Staf CPT:</span>
                </div>
                <p class="text-[11px] leading-relaxed text-amber-900/80">
                    Hanya staf yang dipilih ini yang memiliki kewenangan penuh untuk menambah, mengedit, menghapus, serta mengimpor data kontrak CPT dan menerima email notifikasi kontrak expired.
                </p>
            </div>

            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Pilih User / Staf PIC <span class="text-rose-500">*</span></label>
                <select name="user_id" required class="w-full text-xs p-2.5 rounded border border-slate-300 font-bold text-slate-800 bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                    <option value="">-- Pilih Staf Pengelola Kontrak --</option>
                    @if(isset($allUsers))
                        @foreach($allUsers as $u)
                            <option value="{{ $u->id }}" {{ $u->can_manage_cpt_contracts ? 'selected' : '' }}>
                                {{ $u->full_name ? $u->full_name . ' (' . $u->username . ')' : $u->username }} &bull; [{{ $u->role }}] {{ $u->can_manage_cpt_contracts ? '🌟 (PIC Aktif)' : '' }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeAssignPicModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs rounded transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-check-circle"></i>
                    <span>Simpan Penugasan PIC</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- 📝 MODAL TAMBAH KONTRAK CPT (CREATE) -->
@if($canManageCptContracts)
<div id="createContractModal" class="fixed inset-0 z-[110] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl w-full max-w-2xl shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-[#103A60] to-[#1677B8] text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-file-plus text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Tambah Data Register Kontrak CPT</h3>
                    <p class="text-[10px] text-white/85 font-medium">Input data kontrak baru untuk BU PT Cahaya Perdana Transalam</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateContractModal()" class="text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <form action="{{ route('cpt_contracts.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto custom-scrollbar">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Customer <span class="text-rose-500">*</span></label>
                    <input type="text" name="customer" required placeholder="Contoh: PT Patra Logistik" class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Type Dokumen <span class="text-rose-500">*</span></label>
                    <input type="text" name="type" required list="contractTypeList" placeholder="Pilih atau ketik tipe dokumen..." class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                    <datalist id="contractTypeList">
                        <option value="Kontrak">
                        <option value="Addendum">
                        <option value="Surat Perintah Memulai Pekerjaan (SPMP)">
                        <option value="Perjanjian Kerjasama">
                        <option value="MoU">
                    </datalist>
                </div>
            </div>

            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Project Title (Judul Pekerjaan) <span class="text-rose-500">*</span></label>
                <textarea name="project_title" rows="2" required placeholder="Contoh: Jasa Transportir Angkutan BBM Laut untuk Layanan VHS PT Timah Tbk..." class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Project Name (Nama Singkat Proyek) <span class="text-rose-500">*</span></label>
                    <input type="text" name="project_name" required placeholder="Contoh: Timah, PLN UP 3 Dumai..." class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Project Number (Nomor Kontrak/SPMP) <span class="text-rose-500">*</span></label>
                    <input type="text" name="project_number" required placeholder="Contoh: KTR-457/PL000010/2023-S0" class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-mono font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Start Date</label>
                    <input type="date" name="start_date" class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">End Date</label>
                    <input type="date" name="end_date" class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                        <option value="active">Active (Sedang Berjalan)</option>
                        <option value="still_not_yet">Still Not Yet (Tahap Awal / Proses)</option>
                        <option value="expired">Expired (Kadaluarsa)</option>
                        <option value="completed">Completed (Selesai)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Note (Catatan Tambahan)</label>
                <textarea name="notes" rows="2" placeholder="Catatan negosiasi harga, PPHK, atau tindak lanjut..." class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none"></textarea>
            </div>

            <div class="p-3.5 bg-sand-50 rounded-lg border border-sand-200 space-y-3">
                <span class="text-[11px] font-black text-slate-800 uppercase tracking-wider block">Dokumen Berkas (Opsional)</span>
                <div>
                    <label class="text-[10px] font-bold text-slate-600 mb-1 block">Link Google Drive / Penyimpanan Cloud:</label>
                    <input type="url" name="document_link" placeholder="https://drive.google.com/..." class="w-full bg-white border border-slate-300 rounded-[2px] p-2 text-xs text-slate-900 focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-600 mb-1 block">Atau Upload Berkas PDF / Scan (Maks 20MB):</label>
                    <input type="file" name="document_file" accept=".pdf,.doc,.docx" class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-[#1677B8] file:text-white hover:file:bg-[#125d91] cursor-pointer">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateContractModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-[2px] transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs rounded-[2px] transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-check-circle"></i>
                    <span>Simpan Kontrak</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- ✏️ MODAL EDIT KONTRAK CPT (UPDATE) -->
@if($canManageCptContracts)
<div id="editContractModal" class="fixed inset-0 z-[110] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl w-full max-w-2xl shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-[#103A60] to-[#1677B8] text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-pencil-simple text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Edit Data Register Kontrak CPT</h3>
                    <p class="text-[10px] text-white/85 font-medium">Perbarui rincian kontrak, status, atau berkas pendukung</p>
                </div>
            </div>
            <button type="button" onclick="closeEditContractModal()" class="text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <form id="editContractForm" action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto custom-scrollbar">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Customer <span class="text-rose-500">*</span></label>
                    <input type="text" name="customer" id="edit_customer" required class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Type Dokumen <span class="text-rose-500">*</span></label>
                    <input type="text" name="type" id="edit_type" required list="contractTypeList" class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
            </div>

            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Project Title (Judul Pekerjaan) <span class="text-rose-500">*</span></label>
                <textarea name="project_title" id="edit_project_title" rows="2" required class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Project Name (Nama Singkat Proyek) <span class="text-rose-500">*</span></label>
                    <input type="text" name="project_name" id="edit_project_name" required class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Project Number (Nomor Kontrak/SPMP) <span class="text-rose-500">*</span></label>
                    <input type="text" name="project_number" id="edit_project_number" required class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-mono font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Start Date</label>
                    <input type="date" name="start_date" id="edit_start_date" class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">End Date</label>
                    <input type="date" name="end_date" id="edit_end_date" class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Status <span class="text-rose-500">*</span></label>
                    <select name="status" id="edit_status" required class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                        <option value="active">Active (Sedang Berjalan)</option>
                        <option value="still_not_yet">Still Not Yet (Tahap Awal / Proses)</option>
                        <option value="expired">Expired (Kadaluarsa)</option>
                        <option value="completed">Completed (Selesai)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Note (Catatan Tambahan)</label>
                <textarea name="notes" id="edit_notes" rows="2" class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none"></textarea>
            </div>

            <div class="p-3.5 bg-sand-50 rounded-lg border border-sand-200 space-y-3">
                <span class="text-[11px] font-black text-slate-800 uppercase tracking-wider block">Dokumen Berkas</span>
                <div>
                    <label class="text-[10px] font-bold text-slate-600 mb-1 block">Link Google Drive / Penyimpanan Cloud:</label>
                    <input type="url" name="document_link" id="edit_document_link" placeholder="https://drive.google.com/..." class="w-full bg-white border border-slate-300 rounded-[2px] p-2 text-xs text-slate-900 focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-600 mb-1 block">Ganti Berkas PDF / Scan (Opsional):</label>
                    <input type="file" name="document_file" accept=".pdf,.doc,.docx" class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-[#1677B8] file:text-white hover:file:bg-[#125d91] cursor-pointer">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditContractModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-[2px] transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs rounded-[2px] transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-check-circle"></i>
                    <span>Perbarui Data Kontrak</span>
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

    // 📑 CPT CONTRACT MODAL HANDLERS
    function openCreateContractModal() {
        const m = document.getElementById('createContractModal');
        if (m) m.classList.remove('hidden');
    }
    function closeCreateContractModal() {
        const m = document.getElementById('createContractModal');
        if (m) m.classList.add('hidden');
    }
    function openImportContractModal() {
        const m = document.getElementById('importContractModal');
        if (m) m.classList.remove('hidden');
    }
    function closeImportContractModal() {
        const m = document.getElementById('importContractModal');
        if (m) m.classList.add('hidden');
    }
    function openAssignPicModal() {
        const m = document.getElementById('assignPicModal');
        if (m) m.classList.remove('hidden');
    }
    function closeAssignPicModal() {
        const m = document.getElementById('assignPicModal');
        if (m) m.classList.add('hidden');
    }
    function openEditContractModal(contract) {
        const m = document.getElementById('editContractModal');
        const f = document.getElementById('editContractForm');
        if (m && f && contract) {
            f.action = "{{ url('/library/cpt-contracts') }}/" + contract.id;
            document.getElementById('edit_customer').value = contract.customer || '';
            document.getElementById('edit_type').value = contract.type || contract.contract_type || '';
            document.getElementById('edit_project_title').value = contract.project_title || '';
            document.getElementById('edit_project_name').value = contract.project_name || '';
            document.getElementById('edit_project_number').value = contract.project_number || '';
            
            if (contract.start_date) {
                document.getElementById('edit_start_date').value = contract.start_date.substring(0, 10);
            } else {
                document.getElementById('edit_start_date').value = '';
            }

            if (contract.end_date) {
                document.getElementById('edit_end_date').value = contract.end_date.substring(0, 10);
            } else {
                document.getElementById('edit_end_date').value = '';
            }

            document.getElementById('edit_status').value = contract.status || 'active';
            document.getElementById('edit_notes').value = contract.notes || '';
            document.getElementById('edit_document_link').value = contract.document_link || '';
            m.classList.remove('hidden');
        }
    }
    function closeEditContractModal() {
        const m = document.getElementById('editContractModal');
        if (m) m.classList.add('hidden');
    }

    function filterFolderTab(category) {
        // Reset button active styles
        document.querySelectorAll('.folder-tab-btn').forEach(btn => {
            btn.classList.remove('bg-[#1677B8]', 'text-white', 'shadow-xs', 'shadow-sm');
            btn.classList.add('bg-sand-50', 'text-on-surface-variant', 'border', 'border-sand-200');
        });

        const activeBtn = document.getElementById('tab-btn-' + category);
        if (activeBtn) {
            activeBtn.classList.remove('bg-sand-50', 'text-on-surface-variant', 'border', 'border-sand-200');
            activeBtn.classList.add('bg-[#1677B8]', 'text-white', 'shadow-xs');
        }

        const docGrid = document.getElementById('documentsGrid');
        const cptSection = document.getElementById('cptContractsSection');

        if (category === 'cpt-contracts') {
            if (docGrid) docGrid.classList.add('hidden');
            if (cptSection) cptSection.classList.remove('hidden');
        } else {
            if (cptSection) cptSection.classList.add('hidden');
            if (docGrid) {
                docGrid.classList.remove('hidden');
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
        }
    }

    // Check initial tab from URL params or CPT filters or edit parameter from email
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        const cptSearch = urlParams.get('cpt_search');
        const cptStatus = urlParams.get('cpt_status');
        const cptCustomer = urlParams.get('cpt_customer');
        const editContractId = urlParams.get('edit_contract_id');

        if (tab === 'contracts' || tab === 'cpt-contracts' || cptSearch || cptStatus || cptCustomer || editContractId) {
            filterFolderTab('cpt-contracts');
        }

        if (editContractId) {
            const contractsData = @json($cptContracts ?? []);
            const target = contractsData.find(c => c.id == editContractId);
            if (target) {
                setTimeout(() => openEditContractModal(target), 250);
            }
        }
    });

    // ESC Key to close any open modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            closePDF();
            if (typeof closeUploadManualModal === 'function') closeUploadManualModal();
            if (typeof closeCreateFolderModal === 'function') closeCreateFolderModal();
            if (typeof closeCreateContractModal === 'function') closeCreateContractModal();
            if (typeof closeImportContractModal === 'function') closeImportContractModal();
            if (typeof closeAssignPicModal === 'function') closeAssignPicModal();
            if (typeof closeEditContractModal === 'function') closeEditContractModal();
        }
    });
</script>
@endsection
