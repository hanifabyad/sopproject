@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('header_title', 'Ringkasan Aktivitas e-QMS')

@section('content')
<div class="space-y-6">

    <!-- TOP SECTION: 12-COLUMN GRID (CARD 1 & CARD 2) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- BENTO CARD 1 (COL 12): DOCUMENT OVERVIEW (Gradient Blue-Teal) -->
        <div class="lg:col-span-12 bg-gradient-to-r from-[#1677B8] to-[#00b4d8] rounded-sm p-6 shadow-sm flex flex-col justify-between space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div>
                        <h2 class="text-xl font-extrabold text-white tracking-tight uppercase mt-1">Ringkasan Sistem Dokumen e-QMS</h2>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-sm bg-white/20 text-white flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-lg">analytics</span>
                </div>
            </div>

            <!-- 5 STAT BOXES INSIDE (bg-white/70 p-4 rounded-md border border-white/80) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 flex-grow">
                <!-- Box 1: Total SOP -->
                <div class="bg-white/85 p-5 rounded-md shadow-sm border border-white/80 h-full flex flex-col justify-between hover:bg-white hover:shadow transition-all group">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-on-surface-variant block whitespace-nowrap">Total SOP</span>
                            <h3 class="text-3xl font-black text-on-surface mt-1">{{ $stats['total_sop'] }}</h3>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-gold-500/10 text-gold-500 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-base">folder</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-on-surface/10 flex items-center justify-between">
                        <span class="text-[9px] font-bold text-gold-500 uppercase tracking-wider">SOP Terdaftar</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-gold-500/60"></span>
                    </div>
                </div>

                <!-- Box 2: SOP Support -->
                <a href="{{ route('admin.support.index') }}" class="bg-white/85 p-5 rounded-md shadow-sm border border-white/80 h-full flex flex-col justify-between hover:bg-white hover:shadow transition-all group">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-on-surface-variant block whitespace-nowrap">SOP Support</span>
                            <h3 class="text-3xl font-black text-on-surface mt-1 group-hover:text-gold-500 transition-colors">{{ $stats['sop_support'] }}</h3>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-base">support_agent</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-on-surface/10 flex items-center justify-between">
                        <span class="text-[9px] font-bold text-purple-700 uppercase tracking-wider group-hover:underline">Lihat Detail</span>
                        <span class="material-symbols-outlined text-xs text-purple-700 transform group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </div>
                </a>

                <!-- Box 3: SOP Unit Bisnis -->
                <a href="{{ route('admin.BU.index') }}" class="bg-white/85 p-5 rounded-md shadow-sm border border-white/80 h-full flex flex-col justify-between hover:bg-white hover:shadow transition-all group">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-on-surface-variant block whitespace-nowrap">SOP Unit Bisnis</span>
                            <h3 class="text-3xl font-black text-on-surface mt-1 group-hover:text-gold-500 transition-colors">{{ $stats['sop_divisi'] }}</h3>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-base">apartment</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-on-surface/10 flex items-center justify-between">
                        <span class="text-[9px] font-bold text-emerald-700 uppercase tracking-wider group-hover:underline">Lihat Detail</span>
                        <span class="material-symbols-outlined text-xs text-emerald-700 transform group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </div>
                </a>

                <!-- Box 4: Pending Review -->
                <div class="bg-white/85 p-5 rounded-md shadow-sm border border-white/80 h-full flex flex-col justify-between hover:bg-white hover:shadow transition-all group">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-on-surface-variant block whitespace-nowrap">In Approval</span>
                            <h3 class="text-3xl font-black text-on-surface mt-1">{{ $stats['pending_review'] }}</h3>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-base">hourglass_empty</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-on-surface/10 flex items-center justify-between">
                        <span class="text-[9px] font-bold text-amber-700 uppercase tracking-wider">Antrean Review</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    </div>
                </div>

                <!-- Box 5: Kelola Logo PT -->
                <a href="{{ route('admin.logo.index') }}" class="bg-[#FFF9EE] p-5 rounded-md shadow-sm border border-gold-500/25 h-full flex flex-col justify-between hover:bg-[#FFFDF9] hover:shadow transition-all group">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-gold-700 block whitespace-nowrap">Logo & Info PT</span>
                            <h3 class="text-2xl font-black text-gold-600 mt-1 group-hover:text-gold-700 transition-colors">Kelola</h3>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-gold-500/15 text-gold-600 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-base">image</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gold-500/15 flex items-center justify-between">
                        <span class="text-[9px] font-bold text-gold-600 uppercase tracking-wider group-hover:underline">Atur Logo</span>
                        <span class="material-symbols-outlined text-xs text-gold-600 transform group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- BOTTOM SECTION: 12-COLUMN GRID (CARD 3 & CARD 4) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- BENTO CARD 3 (COL 12): RECENT DOCUMENTS TABLE (White bg-white, rounded-lg, SEMANTIC TABLE) -->
        <div class="lg:col-span-12 bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
            <div class="flex items-center justify-between border-b border-sand-200/40 pb-4">
                <div class="flex items-center space-x-3">
                    <div>
                        <h3 class="text-base font-extrabold text-on-surface">Aktivitas Review Terkini</h3>
                        <p class="text-[11px] text-on-surface-variant">Dokumen SOP yang sedang diajukan atau diproses</p>
                    </div>
                </div>
            </div>

            <!-- 1. DOKUMEN REVISI -->
            <div class="space-y-2">
                <div class="flex items-center space-x-2 text-red-700 font-extrabold text-[11px] uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-red-600"></span>
                    <span>1. Dokumen Perlu Revisi</span>
                </div>
                <div class="overflow-x-auto border border-red-100 rounded-md">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-red-50 border-b border-red-200 text-xs font-semibold text-red-800">
                            <tr>
                                <th class="py-2.5 px-4 w-[40%]">Judul Dokumen SOP</th>
                                <th class="py-2.5 px-4 w-[15%]">Departemen / Unit</th>
                                <th class="py-2.5 px-4 w-[15%]">Terakhir Diperbarui</th>
                                <th class="py-2.5 px-4 w-[15%] text-center">Status</th>
                                <th class="py-2.5 px-4 w-[15%] text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-red-100 text-xs text-on-surface font-medium">
                            @forelse($revisiDocs as $activity)
                            @php
                                $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];
                                $isSupport = in_array(strtoupper($activity->department), $supportDepts);
                                $detailUrl = $isSupport ? route('admin.support.document.detail', $activity->id) : route('admin.BU.detail', $activity->id);
                            @endphp
                            <tr class="hover:bg-red-50/30 transition-colors">
                                <td class="py-2.5 px-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="material-symbols-outlined text-red-600 text-sm flex-shrink-0">assignment_late</span>
                                        <a href="{{ $detailUrl }}" class="font-semibold text-on-surface hover:text-[#1677B8] transition-colors">{{ $activity->title }}</a>
                                    </div>
                                </td>
                                <td class="py-2.5 px-4">
                                    <span class="px-2 py-0.5 bg-white border border-red-200 rounded text-[11px] font-semibold text-red-800">
                                        {{ $activity->department }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-on-surface-variant">
                                    {{ $activity->updated_at->diffForHumans() }}
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-red-100 text-red-800 border border-red-200">
                                        Need Revision
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <a href="{{ $detailUrl }}" class="px-3 py-1 bg-charcoal-900 hover:bg-black text-gold-fixed rounded text-xs font-semibold inline-flex items-center gap-1 shadow-sm transition-all">
                                        <span>Detail</span>
                                        <i class="ph ph-arrow-right text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-on-surface-variant text-xs">
                                    Tidak ada dokumen yang memerlukan revisi saat ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. DOKUMEN IN PROGRESS -->
            <div class="space-y-2 pt-2">
                <div class="flex items-center space-x-2 text-amber-700 font-extrabold text-[11px] uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>2. Dokumen Sedang Diproses / In Progress</span>
                </div>
                <div class="overflow-x-auto border border-amber-100 rounded-md">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-amber-50 border-b border-amber-200 text-xs font-semibold text-amber-800">
                            <tr>
                                <th class="py-2.5 px-4 w-[40%]">Judul Dokumen SOP</th>
                                <th class="py-2.5 px-4 w-[15%]">Departemen / Unit</th>
                                <th class="py-2.5 px-4 w-[15%]">Waktu Pengajuan</th>
                                <th class="py-2.5 px-4 w-[15%] text-center">Status</th>
                                <th class="py-2.5 px-4 w-[15%] text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-100 text-xs text-on-surface font-medium">
                            @forelse($inProgressDocs as $activity)
                            @php
                                $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];
                                $isSupport = in_array(strtoupper($activity->department), $supportDepts);
                                $detailUrl = $isSupport ? route('admin.support.document.detail', $activity->id) : route('admin.BU.detail', $activity->id);
                            @endphp
                            <tr class="hover:bg-amber-50/30 transition-colors">
                                <td class="py-2.5 px-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="material-symbols-outlined text-amber-500 text-sm flex-shrink-0">pending</span>
                                        <a href="{{ $detailUrl }}" class="font-semibold text-on-surface hover:text-[#1677B8] transition-colors">{{ $activity->title }}</a>
                                    </div>
                                </td>
                                <td class="py-2.5 px-4">
                                    <span class="px-2 py-0.5 bg-white border border-amber-200 rounded text-[11px] font-semibold text-amber-800">
                                        {{ $activity->department }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-on-surface-variant">
                                    {{ $activity->created_at->diffForHumans() }}
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                        In Approval
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <a href="{{ $detailUrl }}" class="px-3 py-1 bg-charcoal-900 hover:bg-black text-gold-fixed rounded text-xs font-semibold inline-flex items-center gap-1 shadow-sm transition-all">
                                        <span>Detail</span>
                                        <i class="ph ph-arrow-right text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-on-surface-variant text-xs">
                                    Tidak ada dokumen dalam proses persetujuan saat ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. DOKUMEN AKTIF -->
            <div class="space-y-2 pt-2">
                <div class="flex items-center space-x-2 text-emerald-700 font-extrabold text-[11px] uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>3. Dokumen Aktif & Sah / Active</span>
                </div>
                <div class="overflow-x-auto border border-emerald-100 rounded-md">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-emerald-50 border-b border-emerald-200 text-xs font-semibold text-emerald-800">
                            <tr>
                                <th class="py-2.5 px-4 w-[40%]">Judul Dokumen SOP</th>
                                <th class="py-2.5 px-4 w-[15%]">Departemen / Unit</th>
                                <th class="py-2.5 px-4 w-[15%]">Waktu Aktif</th>
                                <th class="py-2.5 px-4 w-[15%] text-center">Status</th>
                                <th class="py-2.5 px-4 w-[15%] text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 text-xs text-on-surface font-medium">
                            @forelse($activeDocs as $activity)
                            @php
                                $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];
                                $isSupport = in_array(strtoupper($activity->department), $supportDepts);
                                $detailUrl = $isSupport ? route('admin.support.document.detail', $activity->id) : route('admin.BU.detail', $activity->id);
                            @endphp
                            <tr class="hover:bg-emerald-50/30 transition-colors">
                                <td class="py-2.5 px-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="material-symbols-outlined text-emerald-600 text-sm flex-shrink-0">check_circle</span>
                                        <a href="{{ $detailUrl }}" class="font-semibold text-on-surface hover:text-[#1677B8] transition-colors">{{ $activity->title }}</a>
                                    </div>
                                </td>
                                <td class="py-2.5 px-4">
                                    <span class="px-2 py-0.5 bg-white border border-emerald-200 rounded text-[11px] font-semibold text-emerald-800">
                                        {{ $activity->department }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-on-surface-variant">
                                    {{ $activity->updated_at->diffForHumans() }}
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Active
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <a href="{{ $detailUrl }}" class="px-3 py-1 bg-charcoal-900 hover:bg-black text-gold-fixed rounded text-xs font-semibold inline-flex items-center gap-1 shadow-sm transition-all">
                                        <span>Detail</span>
                                        <i class="ph ph-arrow-right text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-on-surface-variant text-xs">
                                    Belum ada dokumen aktif yang diterbitkan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    </div>

<!-- MODAL SECTION -->
<div id="libraryModal" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center p-4 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md p-6 border border-sand-200">
        <div class="flex justify-between items-center border-b border-sand-200 pb-3 mb-4">
            <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider flex items-center gap-2">
                <span class="material-symbols-outlined text-gold-500">archive</span> Pindahkan ke E-Library
            </h3>
            <button onclick="closeLibraryModal()" class="text-on-surface-variant hover:text-red-600 text-xl font-bold transition-colors">&times;</button>
        </div>

        <form id="libraryForm" action="" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">Pilih Kategori Utama</label>
                <select name="category" id="selectCategory" onchange="toggleHierarchy()" class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="divisi">DIVISI</option>
                    <option value="support">SUPPORT</option>
                </select>
            </div>

            <div id="divisiFields" class="hidden space-y-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">Pilih Divisi Besar</label>
                    <select name="division" id="selectDivision" onchange="updateSubDivision()" class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all">
                        <option value="">-- Pilih Divisi --</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">Pilih Unit Bisnis</label>
                    <select name="sub_division" id="selectSubDivision" onchange="updateCompany()" class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all">
                        <option value="">-- Pilih Unit Bisnis --</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">Pilih Entitas PT</label>
                    <select name="company_name" id="selectCompany" class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all">
                        <option value="">-- Pilih PT --</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex space-x-3 border-t border-sand-200">
                <button type="button" onclick="closeLibraryModal()" class="flex-1 font-bold uppercase tracking-wider text-on-surface-variant text-[10px] hover:text-black transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-charcoal-900 text-gold-fixed py-2.5 rounded-md font-bold uppercase tracking-wider text-[10px] shadow-sm hover:bg-black transition-all">Simpan Dokumen</button>
            </div>
        </form>
    </div>
</div>

<script>
    const structure = {
        "RETAIL": {
            "SPBU": ["PT SCK", "PT MMS", "PT IS", "PT LEP"],
            "LPG PSO": ["PT SJN", "PT PJNP", "PT PJS", "PT LCPS", "PT BSN"],
            "LPG NPSO": ["PT LBS"],
            "SPPBE": ["PT PKSP"],
            "BBM RETAIL": ["PT BKI", "PT PJS", "PT ADHEL", "PT TRP"],
            "INMAR": ["PT CNGM", "PT PIMS", "PT TEMARINDO", "PT SCP"]
        },
        "KOMERSIL": {
            "TRANSPORTASI LAUT": ["PT CPT", "PT MHM"],
            "SHIPYARD": ["PT SBS"],
            "GAS INDUSTRI (CNG)": ["PT GVI", "PT MTG"]
        },
        "PROPERTY": {
            "ASET TANAH DAN BANGUNAN": ["PT KPP"]
        },
        "SCM": {
            "PROCURMENT": ["NON PT"],
            "WAREHOUSE": ["NON PT"],
            "ASET": ["NON PT"],
            "GA": ["NON PT"]
        },
        "FA": {
            "KEUANGAN & ACCOUNTING": ["NON PT"]
        }
    };

    function openLibraryModal(id, title) {
        document.getElementById('libraryModal').classList.remove('hidden');
        document.getElementById('libraryForm').action = `/admin/move-to-library/${id}`;
        
        const divSelect = document.getElementById('selectDivision');
        divSelect.innerHTML = '<option value="">-- Pilih Divisi --</option>';
        Object.keys(structure).forEach(div => {
            divSelect.innerHTML += `<option value="${div}">${div}</option>`;
        });
    }

    function updateSubDivision() {
        const div = document.getElementById('selectDivision').value;
        const subSelect = document.getElementById('selectSubDivision');
        subSelect.innerHTML = '<option value="">-- Pilih Unit Bisnis --</option>';
        
        if(structure[div]) {
            Object.keys(structure[div]).forEach(sub => {
                subSelect.innerHTML += `<option value="${sub}">${sub}</option>`;
            });
        }
    }

    function updateCompany() {
        const div = document.getElementById('selectDivision').value;
        const sub = document.getElementById('selectSubDivision').value;
        const compSelect = document.getElementById('selectCompany');
        compSelect.innerHTML = '<option value="">-- Pilih PT --</option>';
        
        if(structure[div] && structure[div][sub]) {
            structure[div][sub].forEach(comp => {
                compSelect.innerHTML += `<option value="${comp}">${comp}</option>`;
            });
        }
    }

    function toggleHierarchy() {
        const cat = document.getElementById('selectCategory').value;
        document.getElementById('divisiFields').classList.toggle('hidden', cat !== 'divisi');
    }

    function closeLibraryModal() {
        document.getElementById('libraryModal').classList.add('hidden');
    }


</script>
@endsection
