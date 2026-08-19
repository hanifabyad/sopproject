@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('header_title', 'Ringkasan Aktivitas e-QMS')

@section('content')
<div class="space-y-6">

    <!-- TOP SECTION: 12-COLUMN GRID (CARD 1 & CARD 2) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- BENTO CARD 1 (COL 8): DOCUMENT OVERVIEW (Warm Beige bg-[#e3dbc9], rounded-lg) -->
        <div class="lg:col-span-8 bg-[#e3dbc9] rounded-lg p-6 shadow-sm flex flex-col justify-between space-y-6 border border-[#cfc6ac]">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 flex items-center justify-center flex-shrink-0">
                        <img src="{{ asset('img/logopkm.svg') }}" class="w-full h-full object-contain" alt="Logo PKM Group">
                    </div>
                    <div>
                        <span class="px-2.5 py-0.5 bg-white/80 text-[#705d00] font-extrabold text-[10px] uppercase rounded tracking-wider border border-white/60">Document Overview</span>
                        <h2 class="text-xl font-extrabold text-[#1e1c14] tracking-tight uppercase mt-1">Ringkasan Sistem Dokumen e-QMS</h2>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-md bg-white/80 text-[#333028] flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-lg">analytics</span>
                </div>
            </div>

            <!-- 4 STAT BOXES INSIDE (bg-white/70 p-4 rounded-md border border-white/80) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <!-- Box 1: Total SOP -->
                <div class="bg-white/80 p-4 rounded-md shadow-sm border border-white/80">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#4d4633] block">Total SOP</span>
                    <h3 class="text-2xl font-black text-[#1e1c14] mt-1">{{ $stats['total_sop'] }}</h3>
                    <div class="w-full bg-[#333028]/10 h-1 rounded-full mt-3 overflow-hidden">
                        <div class="bg-[#705d00] h-full rounded-full w-full"></div>
                    </div>
                </div>

                <!-- Box 2: SOP Support -->
                <a href="{{ route('admin.support.index') }}" class="bg-white/80 hover:bg-white p-4 rounded-md transition-all shadow-sm group border border-white/80">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#4d4633] block">SOP Support</span>
                    <h3 class="text-2xl font-black text-[#1e1c14] mt-1 group-hover:text-[#705d00] transition-colors">{{ $stats['sop_support'] }}</h3>
                    <div class="w-full bg-[#333028]/10 h-1 rounded-full mt-3 overflow-hidden">
                        <div class="bg-purple-600 h-full rounded-full w-full"></div>
                    </div>
                </a>

                <!-- Box 3: SOP Unit Bisnis -->
                <a href="{{ route('admin.BU.index') }}" class="bg-white/80 hover:bg-white p-4 rounded-md transition-all shadow-sm group border border-white/80">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#4d4633] block">SOP Bisnis Unit</span>
                    <h3 class="text-2xl font-black text-[#1e1c14] mt-1 group-hover:text-[#705d00] transition-colors">{{ $stats['sop_divisi'] }}</h3>
                    <div class="w-full bg-[#333028]/10 h-1 rounded-full mt-3 overflow-hidden">
                        <div class="bg-emerald-600 h-full rounded-full w-full"></div>
                    </div>
                </a>

                <!-- Box 4: Pending Review -->
                <div class="bg-white/80 p-4 rounded-md shadow-sm border border-white/80">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#4d4633] block">In Approval</span>
                    <h3 class="text-2xl font-black text-[#1e1c14] mt-1">{{ $stats['pending_review'] }}</h3>
                    <div class="w-full bg-[#333028]/10 h-1 rounded-full mt-3 overflow-hidden">
                        <div class="bg-amber-500 h-full rounded-full w-[70%]"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BENTO CARD 2 (COL 4): APPROVAL PIPELINE (Dark Charcoal bg-[#333028], rounded-lg) -->
        <div class="lg:col-span-4 bg-[#333028] text-white rounded-lg p-6 shadow-md flex flex-col justify-between space-y-6 border border-black/20">
            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                <div class="flex items-center space-x-2">
                    <span class="material-symbols-outlined text-[#ffe16e] text-lg">conversion_path</span>
                    <h3 class="text-xs font-extrabold uppercase tracking-wider">Approval Pipeline Status</h3>
                </div>
                <span class="px-2 py-0.5 bg-[#ffe16e]/20 text-[#ffe16e] rounded text-[9px] font-bold uppercase">Multi-Stage</span>
            </div>

            <div class="space-y-2.5 text-xs font-semibold">
                <!-- Step 1: Initiator -->
                <div class="p-3 bg-white/5 rounded-md border border-white/10 flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-5 h-5 rounded bg-[#ffe16e] text-[#333028] flex items-center justify-center font-extrabold text-[10px]">1</span>
                        <span>Draf Pembuat SOP</span>
                    </div>
                    <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded text-[9px] font-bold uppercase">Ready</span>
                </div>

                <!-- Step 2: Reviewers -->
                <div class="p-3 bg-white/5 rounded-md border border-white/10 flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-5 h-5 rounded bg-[#ffe16e] text-[#333028] flex items-center justify-center font-extrabold text-[10px]">2</span>
                        <span>Verifikasi Reviewer</span>
                    </div>
                    <span class="px-2 py-0.5 bg-amber-500/20 text-amber-300 rounded text-[9px] font-bold uppercase animate-pulse">Paralel</span>
                </div>

                <!-- Step 3: Final Approval -->
                <div class="p-3 bg-white/5 rounded-md border border-white/10 flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-5 h-5 rounded bg-[#ffe16e] text-[#333028] flex items-center justify-center font-extrabold text-[10px]">3</span>
                        <span>Pengesahan & Stamping</span>
                    </div>
                    <span class="px-2 py-0.5 bg-white/10 text-white/70 rounded text-[9px] font-bold uppercase">Final</span>
                </div>
            </div>

            <div class="p-3 bg-[#ffe16e]/10 border border-[#ffe16e]/20 rounded-md text-[11px] text-[#ffe16e] flex items-center space-x-2">
                <span class="material-symbols-outlined text-base">verified</span>
                <span>Otomatisasi Stempel Digital LP Terintegrasi</span>
            </div>
        </div>
    </div>

    <!-- BOTTOM SECTION: 12-COLUMN GRID (CARD 3 & CARD 4) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- BENTO CARD 3 (COL 8): RECENT DOCUMENTS TABLE (White bg-white, rounded-lg, SEMANTIC TABLE) -->
        <div class="lg:col-span-8 bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 space-y-4">
            <div class="flex items-center justify-between border-b border-[#cfc6ac]/40 pb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm">
                        <span class="material-symbols-outlined text-base">history</span>
                    </div>
                    <div>
                        <h3 class="text-xs font-extrabold text-[#1e1c14] uppercase tracking-wider">Aktivitas Review Terkini</h3>
                        <p class="text-[11px] text-[#4d4633]">Dokumen SOP yang sedang diajukan atau diproses</p>
                    </div>
                </div>

                <span class="px-2.5 py-1 bg-[#ffd92f]/20 text-[#705d00] rounded text-[10px] font-bold uppercase tracking-wider">Live Monitor</span>
            </div>

            <!-- SEMANTIC HTML DATA TABLE GRID -->
            <div class="overflow-x-auto border border-[#cfc6ac]/60 rounded-md">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#eee8db] border-b border-[#cfc6ac] text-[11px] font-bold uppercase tracking-wider text-[#4d4633]">
                        <tr>
                            <th class="py-3 px-4">Judul Dokumen SOP</th>
                            <th class="py-3 px-4">Departemen / Unit</th>
                            <th class="py-3 px-4">Waktu Pengajuan</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e8e2d6] text-xs font-semibold text-[#1e1c14]">
                        @forelse($recentActivities as $activity)
                        @php
                            $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];
                            $isSupport = in_array(strtoupper($activity->department), $supportDepts);
                            $detailUrl = $isSupport ? route('admin.support.document.detail', $activity->id) : route('admin.BU.detail', $activity->id);
                        @endphp
                        <tr class="border-b border-[#e8e2d6] hover:bg-[#f7f6f2] transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-2.5">
                                    <span class="material-symbols-outlined text-red-600 text-base flex-shrink-0">description</span>
                                    <span class="font-bold text-[#1e1c14] uppercase hover:text-[#705d00] transition-colors">{{ $activity->title }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 bg-[#f7f6f2] border border-[#cfc6ac]/60 rounded text-[10px] font-bold text-[#333028] uppercase">
                                    {{ $activity->department }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-[#4d4633] text-[11px]">
                                {{ $activity->created_at->diffForHumans() }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider border inline-flex items-center gap-1
                                    {{ $activity->status == 'active' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : ($activity->status == 'need_revision' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-amber-50 border-amber-200 text-amber-700') }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $activity->status == 'active' ? 'bg-emerald-500' : ($activity->status == 'need_revision' ? 'bg-red-500' : 'bg-amber-500') }}"></span>
                                    {{ $activity->status == 'active' ? 'Active' : ($activity->status == 'need_revision' ? 'Need Revision' : 'In Approval') }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ $detailUrl }}" class="px-3 py-1 bg-[#333028] hover:bg-black text-[#ffe16e] rounded text-[10px] font-bold uppercase tracking-wider inline-flex items-center gap-1">
                                    <span>Detail</span>
                                    <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-[#4d4633]">
                                <span class="material-symbols-outlined text-3xl text-[#d6cebf]">inbox</span>
                                <p class="font-bold text-xs uppercase tracking-wider mt-1">Belum ada aktivitas dokumen terbaru hari ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BENTO CARD 4 (COL 4): PENDING ACTIONS & AUDIT TIMELINE (White bg-white, rounded-lg) -->
        <div class="lg:col-span-4 bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 space-y-5">
            <div class="border-b border-[#cfc6ac]/40 pb-3 flex items-center space-x-2">
                <span class="material-symbols-outlined text-[#705d00] text-lg">pending_actions</span>
                <h3 class="text-xs font-extrabold text-[#1e1c14] uppercase tracking-wider">Pending Actions & Timeline</h3>
            </div>

            <!-- PENDING ACTION CARD SUB-ITEM -->
            <div class="p-3.5 bg-[#f7f6f2] rounded-md border border-[#cfc6ac]/60 space-y-1.5">
                <div class="flex items-center justify-between text-xs font-bold text-[#1e1c14]">
                    <span>Atensi Approval SOP</span>
                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded text-[9px] uppercase font-bold">Antrean</span>
                </div>
                <p class="text-[11px] text-[#4d4633]">Terdapat {{ $stats['pending_review'] }} dokumen menunggu keputusan reviewer hari ini.</p>
            </div>

            <!-- VERTICAL TIMELINE AUDIT -->
            <div class="space-y-4 relative pl-2 pt-1">
                <div class="relative pl-4 border-l-2 border-[#705d00] pb-2 space-y-0.5">
                    <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-[#705d00]"></div>
                    <p class="text-xs font-bold text-[#1e1c14]">Sistem e-QMS Online</p>
                    <p class="text-[10px] text-[#4d4633]">Status server aktif & siap memproses pengesahan LP.</p>
                </div>
                <div class="relative pl-4 border-l-2 border-emerald-500 pb-2 space-y-0.5">
                    <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-emerald-500"></div>
                    <p class="text-xs font-bold text-[#1e1c14]">Fitur Stamping Digital Ready</p>
                    <p class="text-[10px] text-[#4d4633]">Validasi stempel APPROVED adaptive sizing aktif.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL SECTION -->
<div id="libraryModal" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center p-4 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md p-6 border border-[#cfc6ac]">
        <div class="flex justify-between items-center border-b border-[#cfc6ac] pb-3 mb-4">
            <h3 class="text-sm font-bold text-[#1e1c14] uppercase tracking-wider flex items-center gap-2">
                <span class="material-symbols-outlined text-[#705d00]">archive</span> Pindahkan ke E-Library
            </h3>
            <button onclick="closeLibraryModal()" class="text-[#4d4633] hover:text-red-600 text-xl font-bold transition-colors">&times;</button>
        </div>

        <form id="libraryForm" action="" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#4d4633] mb-1">Pilih Kategori Utama</label>
                <select name="category" id="selectCategory" onchange="toggleHierarchy()" class="w-full bg-[#fbf9f4] border border-[#cfc6ac] rounded-md p-2.5 font-semibold text-xs text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none transition-all" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="divisi">DIVISI</option>
                    <option value="support">SUPPORT</option>
                </select>
            </div>

            <div id="divisiFields" class="hidden space-y-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-[#4d4633] mb-1">Pilih Divisi Besar</label>
                    <select name="division" id="selectDivision" onchange="updateSubDivision()" class="w-full bg-[#fbf9f4] border border-[#cfc6ac] rounded-md p-2.5 font-semibold text-xs text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none transition-all">
                        <option value="">-- Pilih Divisi --</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-[#4d4633] mb-1">Pilih Unit Bisnis</label>
                    <select name="sub_division" id="selectSubDivision" onchange="updateCompany()" class="w-full bg-[#fbf9f4] border border-[#cfc6ac] rounded-md p-2.5 font-semibold text-xs text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none transition-all">
                        <option value="">-- Pilih Unit Bisnis --</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-[#4d4633] mb-1">Pilih Entitas PT</label>
                    <select name="company_name" id="selectCompany" class="w-full bg-[#fbf9f4] border border-[#cfc6ac] rounded-md p-2.5 font-semibold text-xs text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none transition-all">
                        <option value="">-- Pilih PT --</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex space-x-3 border-t border-[#cfc6ac]">
                <button type="button" onclick="closeLibraryModal()" class="flex-1 font-bold uppercase tracking-wider text-[#4d4633] text-[10px] hover:text-black transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-[#333028] text-[#ffe16e] py-2.5 rounded-md font-bold uppercase tracking-wider text-[10px] shadow-sm hover:bg-black transition-all">Simpan Dokumen</button>
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
