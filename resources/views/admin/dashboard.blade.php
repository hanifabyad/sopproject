@extends('layouts.admin')

@section('title', 'e-QMS Admin Overview')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .eqms-scope {
        font-family: 'Poppins', sans-serif;
    }
</style>

<div class="p-8 eqms-scope">
    {{-- Welcome Header --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-[#1e293b] uppercase tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-blue-600"></i> Ringkasan Aktivitas
            </h2>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Selamat datang kembali, Administrator e-QMS</p>
        </div>
        <div class="text-[10px] font-bold text-gray-400 bg-gray-50 border border-gray-100 px-4 py-2 rounded-xl shadow-sm w-fit h-fit uppercase tracking-wider">
            <i class="fa-solid fa-clock mr-1 text-blue-500"></i> {{ now()->format('d M Y') }}
        </div>
    </div>

    {{-- Statistik Utama (UI Cerdas Bergradasi Segar & Clickable) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        {{-- Total User --}}
        <a href="{{ route('admin.users.index') }}" class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 relative overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-lg flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total User Active</p>
                <h3 class="text-3xl font-black text-[#1e293b]">{{ $stats['total_pegawai'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-lg shadow-inner group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-400"></div>
        </a>

        {{-- Menunggu Review --}}
        <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 relative overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-lg flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Menunggu Review</p>
                <h3 class="text-3xl font-black text-[#1e293b]">{{ $stats['pending_review'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 text-lg shadow-inner group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-yellow-400"></div>
        </div>

        {{-- SOP Support --}}
        <a href="{{ route('admin.support.index') }}" class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 relative overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-lg flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">SOP Support </p>
                <h3 class="text-3xl font-black text-[#1e293b]">{{ $stats['sop_support'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 text-lg shadow-inner group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-fuchsia-400"></div>
        </a>

        {{-- SOP Unit Bisnis --}}
        <a href="{{ route('admin.BU.index') }}" class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 relative overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-lg flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">SOP Bisnis Unit</p>
                <h3 class="text-3xl font-black text-[#1e293b]">{{ $stats['sop_divisi'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-lg shadow-inner group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                <i class="fa-solid fa-file-shield"></i>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
        </a>
    </div>

    {{-- Aktivitas Terbaru (Desain Ringkas & Elegan) --}}
    <div class="bg-white rounded-2xl shadow-md p-8 border border-gray-100 min-h-[400px]">
        <div class="flex items-center justify-between border-b border-gray-100 pb-5 mb-6">
            <h3 class="text-lg font-extrabold text-[#1e293b] uppercase tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-list-check text-blue-500 text-base"></i> Aktivitas Review Terkini
            </h3>
            <span class="px-3 py-1.5 bg-blue-50 border border-blue-100/50 text-blue-600 rounded-xl text-[9px] font-bold uppercase tracking-wider animate-pulse flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 block"></span> Update Real-Time
            </span>
        </div>

        <div class="space-y-4">
            @forelse($recentActivities as $activity)
            @php
                $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];
                $isSupport = in_array(strtoupper($activity->department), $supportDepts);
                $detailUrl = $isSupport ? route('admin.support.document.detail', $activity->id) : route('admin.BU.detail', $activity->id);
            @endphp
            <a href="{{ $detailUrl }}" class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-white hover:shadow-md transition-all duration-300 border border-transparent hover:border-gray-100 group cursor-pointer">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-[#1e293b] text-white rounded-xl flex items-center justify-center text-base group-hover:bg-blue-600 transition-colors duration-300 shadow-sm">
                        <i class="fa-regular fa-file-lines"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-[#1e293b] uppercase text-xs tracking-tight group-hover:text-blue-600 transition-colors">{{ $activity->title }}</h4>
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wide mt-0.5">
                            <span class="text-gray-500 font-semibold">{{ $activity->department }}</span> • <i class="fa-regular fa-clock mr-0.5"></i> {{ $activity->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                <div>
                    <span class="px-3 py-1.5 rounded-xl text-[8px] font-bold uppercase tracking-wider shadow-sm border
                        {{ $activity->status == 'active' ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : ($activity->status == 'need_revision' ? 'bg-red-50 border-red-100 text-red-500' : 'bg-amber-50 border-amber-100 text-amber-600') }}">
                        <i class="{{ $activity->status == 'active' ? 'fa-solid fa-circle-check' : ($activity->status == 'need_revision' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-spinner') }} mr-1"></i>
                        {{ $activity->status == 'active' ? 'Active' : ($activity->status == 'need_revision' ? 'Need Revision' : 'Waiting') }}
                    </span>
                </div>
            </a>
            @empty
            <div class="flex flex-col items-center justify-center py-16 opacity-30 text-center">
                <div class="text-4xl mb-2">📥</div>
                <p class="font-bold uppercase tracking-wider text-xs">Belum ada aktivitas terbaru hari ini.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- MODAL SECTION --}}
<div id="libraryModal" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center p-4 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 transform transition-all border border-gray-100">
        <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
            <h3 class="text-lg font-black text-[#1e293b] uppercase italic flex items-center gap-2"><i class="fa-solid fa-box-archive text-blue-600"></i> Pindahkan ke Library</h3>
            <button onclick="closeLibraryModal()" class="text-gray-400 hover:text-red-500 text-2xl transition-colors">&times;</button>
        </div>

        <form id="libraryForm" action="" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[9px] font-bold uppercase tracking-widest text-gray-400 mb-1">Pilih Kategori Utama</label>
                <select name="category" id="selectCategory" onchange="toggleHierarchy()" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 font-bold text-xs text-[#1e293b] focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="divisi">DIVISI</option>
                    <option value="support">SUPPORT</option>
                </select>
            </div>

            <div id="divisiFields" class="hidden space-y-4">
                <div>
                    <label class="block text-[9px] font-bold uppercase tracking-widest text-gray-400 mb-1">Pilih Divisi Besar</label>
                    <select name="division" id="selectDivision" onchange="updateSubDivision()" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 font-bold text-xs text-[#1e293b] focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        <option value="">-- Pilih Divisi --</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-[9px] font-bold uppercase tracking-widest text-gray-400 mb-1">Pilih Unit Bisnis</label>
                    <select name="sub_division" id="selectSubDivision" onchange="updateCompany()" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 font-bold text-xs text-[#1e293b] focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        <option value="">-- Pilih Unit Bisnis --</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[9px] font-bold uppercase tracking-widest text-gray-400 mb-1">Pilih Entitas PT</label>
                    <select name="company_name" id="selectCompany" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 font-bold text-xs text-[#1e293b] focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        <option value="">-- Pilih PT --</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeLibraryModal()" class="flex-1 font-bold uppercase tracking-wider text-gray-400 text-[10px] hover:text-gray-600 transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-[#1e293b] text-white py-3 rounded-xl font-bold uppercase tracking-wider text-[10px] shadow-md hover:bg-blue-600 hover:shadow-blue-100 transition-all">Simpan Dokumen</button>
            </div>
        </form>
    </div>
</div>

{{-- SCRIPT SECTION --}}
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