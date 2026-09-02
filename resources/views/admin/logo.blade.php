@extends('layouts.admin')

@section('title', 'Kelola Logo & Info PT')
@section('header_title', 'Kelola Informasi & Logo Perusahaan (PT)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.dashboard') }}" variant="light" text="Kembali" />
            <span class="text-white/30">|</span>
            <div class="flex items-center gap-2 text-xs text-white/80">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#ffe16e] font-medium">Dashboard</a>
                <span>/</span>
                <span class="font-bold text-white">Kelola Logo PT</span>
            </div>
        </div>

        <div>
            <h2 class="text-xl font-extrabold tracking-tight text-white">Kelola Informasi & Logo Perusahaan (PT)</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">Sesuaikan nama resmi, alamat kantor, dan logo yang akan dicetak di Lembar Pengesahan (LP) PDF secara dinamis</p>
        </div>
    </div>

    <!-- MAIN INTERACTION CONTAINER -->
    <div class="bg-white rounded-lg p-6 md:p-8 shadow-sm border border-sand-200/60 space-y-6">
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 font-semibold text-xs rounded-r-md shadow-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-base">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3.5 bg-[#ffdad6] border-l-4 border-[#ba1a1a] text-[#ba1a1a] font-semibold text-xs rounded-r-md shadow-sm space-y-1">
                <div class="flex items-center gap-2 font-bold">
                    <span class="material-symbols-outlined text-base">error</span>
                    <span>Gagal Menyimpan Perubahan:</span>
                </div>
                <ul class="list-disc ml-6 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left: Select Company Dropdown -->
            <div class="space-y-2">
                <label class="block text-xs font-bold capitalize tracking-wider text-on-surface-variant mb-1">Pilih Entitas Perusahaan</label>
                <select id="select_company_code" onchange="loadCompanyData()" class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all">
                    <option value="">-- Pilih Perusahaan --</option>
                    @foreach($companyMap as $code => $info)
                        <option value="{{ $code }}">{{ $info['name'] }} ({{ strtoupper($code) }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Right: Edit Form (Visible when company is selected) -->
            <div class="md:col-span-2">
                <form id="companyLogoForm" action="{{ route('admin.logo.update') }}" method="POST" enctype="multipart/form-data" class="hidden space-y-4">
                    @csrf
                    <input type="hidden" name="company_code" id="company_code_hidden">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold capitalize tracking-wider text-on-surface-variant mb-1">Nama Resmi Perusahaan</label>
                            <input type="text" name="name" id="company_name_input" required class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold capitalize tracking-wider text-on-surface-variant mb-1">Unggah Logo Baru (Opsional)</label>
                            <x-file-input name="logo" accept="image/*" label="Pilih logo baru" hint="Format gambar, maksimal 10 MB" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold capitalize tracking-wider text-on-surface-variant mb-1">Alamat Kantor</label>
                        <input type="text" name="address" id="company_address_input" required class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all">
                    </div>

                    <div class="flex items-center space-x-4 p-3 bg-sand-50 rounded-md border border-sand-200/50">
                        <div class="w-16 h-12 flex items-center justify-center bg-white rounded border border-sand-200 overflow-hidden">
                            <img id="current_logo_img" src="" alt="Logo" class="max-w-full max-h-full object-contain hidden">
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold capitalize tracking-wider text-on-surface-variant">File Logo Saat Ini</span>
                            <span id="current_logo_text" class="text-xs font-semibold text-on-surface"></span>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-interactive-button text="Simpan Perubahan" class="text-[10px]" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const companyMapData = @json($companyMap);

    function loadCompanyData() {
        const code = document.getElementById('select_company_code').value;
        const form = document.getElementById('companyLogoForm');
        const hiddenInput = document.getElementById('company_code_hidden');
        const nameInput = document.getElementById('company_name_input');
        const addressInput = document.getElementById('company_address_input');
        const currentLogoImg = document.getElementById('current_logo_img');
        const currentLogoText = document.getElementById('current_logo_text');
        
        if (code && companyMapData[code]) {
            form.classList.remove('hidden');
            hiddenInput.value = code;
            nameInput.value = companyMapData[code].name;
            addressInput.value = companyMapData[code].address;
            
            const logoName = companyMapData[code].logo;
            if (logoName) {
                currentLogoImg.src = `/img/${logoName}`;
                currentLogoImg.classList.remove('hidden');
                currentLogoText.innerText = logoName;
            } else {
                currentLogoImg.classList.add('hidden');
                currentLogoText.innerText = 'Tidak ada logo';
            }
        } else {
            form.classList.add('hidden');
        }
    }
</script>
@endsection
