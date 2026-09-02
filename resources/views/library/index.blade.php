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

        <div class="flex items-center gap-3">
            @if(request('bu') && Auth::user()?->role === 'admin')
                <x-interactive-button type="button" text="Tambah SOP Manual" icon="ph ph-cloud-arrow-up text-sm" onclick="openUploadManualModal()" class="self-start md:self-auto" />
            @endif

            @if(request('category') == 'general' && Auth::user()?->role === 'admin')
                <x-interactive-button type="button" text="Buat Folder Baru" icon="ph ph-folder-plus text-sm" onclick="openCreateFolderModal()" class="self-start md:self-auto" />
            @endif
        </div>
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
            <!-- TABS SUBFOLDER STANDAR ISO -->
            @if(request('bu'))
            <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-sand-200/60 custom-scrollbar">
                <button type="button" onclick="filterFolderTab('all')" id="tab-btn-all" class="folder-tab-btn px-4 py-2 rounded-md font-bold text-xs capitalize tracking-wider bg-[#1677B8] text-white shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-folder-open text-sm"></i>
                    <span>Semua Dokumen</span>
                </button>
                <button type="button" onclick="filterFolderTab('dokumen-mutu')" id="tab-btn-dokumen-mutu" class="folder-tab-btn px-4 py-2 rounded-md font-bold text-xs capitalize tracking-wider bg-sand-50 text-on-surface-variant hover:bg-[#fff9ed] hover:text-[#1677B8] border border-sand-200 transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-seal-check text-sm"></i>
                    <span>1. Dokumen Mutu</span>
                </button>
                <button type="button" onclick="filterFolderTab('sop')" id="tab-btn-sop" class="folder-tab-btn px-4 py-2 rounded-md font-bold text-xs capitalize tracking-wider bg-sand-50 text-on-surface-variant hover:bg-[#fff9ed] hover:text-[#1677B8] border border-sand-200 transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-files text-sm"></i>
                    <span>2. SOP Sah</span>
                </button>
                <button type="button" onclick="filterFolderTab('jobdesk')" id="tab-btn-jobdesk" class="folder-tab-btn px-4 py-2 rounded-md font-bold text-xs capitalize tracking-wider bg-sand-50 text-on-surface-variant hover:bg-[#fff9ed] hover:text-[#1677B8] border border-sand-200 transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-identification-card text-sm"></i>
                    <span>3. Jobdesk</span>
                </button>
                <button type="button" onclick="filterFolderTab('kpi')" id="tab-btn-kpi" class="folder-tab-btn px-4 py-2 rounded-md font-bold text-xs capitalize tracking-wider bg-sand-50 text-on-surface-variant hover:bg-[#fff9ed] hover:text-[#1677B8] border border-sand-200 transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-target text-sm"></i>
                    <span>4. KPI & Target</span>
                </button>
                <button type="button" onclick="filterFolderTab('ik-forms')" id="tab-btn-ik-forms" class="folder-tab-btn px-4 py-2 rounded-md font-bold text-xs capitalize tracking-wider bg-sand-50 text-on-surface-variant hover:bg-[#fff9ed] hover:text-[#1677B8] border border-sand-200 transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-clipboard-text text-sm"></i>
                    <span>5. IK & Formulir</span>
                </button>
            </div>
            @endif

            <!-- 3-COLUMN DOCUMENT CARDS GRID -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="documentsGrid">
                @forelse($documents as $doc)
                @php
                    $docTitleLower = strtolower($doc->title);
                    $tabCategory = 'sop';
                    if (str_contains($docTitleLower, 'visi') || str_contains($docTitleLower, 'misi') || str_contains($docTitleLower, 'kebijakan') || str_contains($docTitleLower, 'mutu') || str_contains($docTitleLower, 'peta proses')) {
                        $tabCategory = 'dokumen-mutu';
                    } elseif (str_contains($docTitleLower, 'job') || str_contains($docTitleLower, 'desk') || str_contains($docTitleLower, 'uraian jabatan')) {
                        $tabCategory = 'jobdesk';
                    } elseif (str_contains($docTitleLower, 'kpi') || str_contains($docTitleLower, 'target') || str_contains($docTitleLower, 'kinerja')) {
                        $tabCategory = 'kpi';
                    } elseif (str_contains($docTitleLower, 'ik') || str_contains($docTitleLower, 'instruksi') || str_contains($docTitleLower, 'form') || str_contains($docTitleLower, 'formulir')) {
                        $tabCategory = 'ik-forms';
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

                    <x-interactive-button text="Lihat Dokumen Sah" type="button" icon="ph ph-eye text-sm" onclick="viewPDF('{{ route('library.document.stream', $doc->id) }}')" class="w-full" />
                </div>
                @empty
                <div class="col-span-full py-16 flex flex-col items-center justify-center text-center space-y-3">
                    <span class="material-symbols-outlined text-5xl text-[#d6cebf]">folder_off</span>
                    <h5 class="text-sm font-bold text-on-surface capitalize">Belum Ada Dokumen Yang Terarsip</h5>
                    <p class="text-xs text-on-surface-variant max-w-sm">Dokumen aktif akan otomatis masuk ke E-Library setelah seluruh alur tanda tangan selesai.</p>
                    
                    <div class="flex items-center gap-3 pt-2">
                        <x-back-button href="{{ request()->is('admin/*') ? route('admin.library.index') : route('library.index') }}" text="Kembali ke Depan" />
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

<!-- MODAL PDF VIEWER -->
<div id="pdfModal" class="fixed inset-0 z-[100] hidden bg-black/80 backdrop-blur-sm flex flex-col p-6 transition-all">
    <div class="flex justify-between items-center text-white mb-4">
        <div class="flex items-center gap-2">
            <span class="bg-[#ffd92f] text-charcoal-900 text-[10px] px-2.5 py-0.5 rounded font-extrabold capitalize tracking-wider">e-QMS SYSTEM</span>
            <h3 class="font-bold text-sm tracking-wide">Digital Document Control Viewer</h3>
        </div>
        <div class="flex items-center gap-2">
            <a id="pdfOpenNewTab" href="#" target="_blank" 
               class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white text-xs font-bold rounded-md transition-colors">
                <i class="ph ph-arrow-square-out text-sm"></i>
                <span>Buka di Tab Baru</span>
            </a>
            <button onclick="closePDF()" class="bg-white/20 hover:bg-white/30 text-white w-8 h-8 rounded-md flex items-center justify-center font-bold text-lg cursor-pointer">&times;</button>
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
            <button onclick="closeUploadManualModal()" class="text-gray-400 hover:text-black text-2xl cursor-pointer">&times;</button>
        </div>
        <form action="{{ route('library.store_manual') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="category" value="{{ request('category') }}">
            <input type="hidden" name="division_name" value="{{ request('div') }}">
            <input type="hidden" name="business_unit" value="{{ request('bu') }}">
            
            <div>
                <label class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider mb-1 block">Judul SOP Resmi</label>
                <input type="text" name="title" class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none" placeholder="Ketik nama dokumen lengkap..." required>
            </div>
            <div>
                <label class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider mb-1 block">Lampirkan File PDF Dokumen (Maks 10MB)</label>
                <x-file-input name="file" accept=".pdf" label="Pilih file SOP" hint="PDF, maksimal 10 MB" :required="true" />
            </div>
            
            <div class="flex space-x-3 pt-3">
                <button type="button" onclick="closeUploadManualModal()" class="flex-1 py-2.5 bg-sand-50 border border-sand-200 text-on-surface-variant rounded-md font-bold text-xs capitalize cursor-pointer">Batal</button>
                <x-interactive-button text="Simpan SOP" class="flex-1" />
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
            <h3 class="font-extrabold text-sm text-on-surface capitalize tracking-wider">Buat Folder Baru</h3>
            <button onclick="closeCreateFolderModal()" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <form action="{{ route('admin.library.folder.create') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="parent_id" value="">
            <div>
                <label class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider mb-1 block">Nama Folder</label>
                <input type="text" name="name" class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none" placeholder="Ketik nama folder..." required>
            </div>
            <div class="flex space-x-3 pt-3">
                <button type="button" onclick="closeCreateFolderModal()" class="flex-1 py-2.5 bg-sand-50 border border-sand-200 text-on-surface-variant rounded-md font-bold text-xs capitalize cursor-pointer">Batal</button>
                <x-interactive-button text="Buat Folder" class="flex-1" />
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function viewPDF(url) {
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
</script>
@endsection
