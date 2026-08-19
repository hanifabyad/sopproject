@extends('layouts.admin')

@section('title', 'Tambah SOP Support Baru')
@section('header_title', 'Unggah SOP Departemen Support')

@section('content')
<div class="space-y-6">
    
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.support.show', $department) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-[#cfc6ac] bg-white text-[#4d4633] hover:bg-[#f7f6f2] hover:text-[#1e1c14] text-xs font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <span class="text-[#cfc6ac]">|</span>
            <div class="flex items-center gap-2 text-xs text-[#4d4633]">
                <a href="{{ route('admin.support.index') }}" class="hover:text-[#705d00]">Departemen Support</a>
                <span>/</span>
                <a href="{{ route('admin.support.show', $department) }}" class="hover:text-[#705d00]">{{ strtoupper($department) }}</a>
                <span>/</span>
                <span class="font-medium text-[#1e1c14]">Form Inisiasi SOP Support</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold text-[#1e1c14] uppercase tracking-tight">Form Inisiasi SOP Support</h2>
            <p class="text-xs text-[#4d4633] mt-0.5">Unggah berkas komponen SOP untuk departemen {{ strtoupper($department) }}</p>
        </div>
    </div>

    <!-- ERROR ALERT -->
    @if($errors->any())
        <div class="p-3.5 bg-[#ffdad6] border-l-4 border-[#ba1a1a] text-[#ba1a1a] font-semibold text-xs rounded-r-md shadow-sm space-y-1">
            <div class="flex items-center gap-2 font-bold text-sm">
                <span class="material-symbols-outlined text-base">error</span>
                <span>Terjadi kesalahan validasi data:</span>
            </div>
            <ul class="list-disc ml-6 space-y-0.5 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- MAIN 12-COLUMN BENTO FORM -->
    <form action="{{ route('admin.support.store', $department) }}" method="POST" enctype="multipart/form-data" id="sopForm">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- BENTO 1 (COL 7): BASIC FORM DATA & SIGNERS -->
            <div class="lg:col-span-7 bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 space-y-5">
                <div class="flex items-center space-x-3 border-b border-[#cfc6ac]/40 pb-3">
                    <div class="w-8 h-8 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-base">edit_note</span>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-xs uppercase tracking-wider text-[#1e1c14]">Informasi SOP Support & Parameter Signer</h4>
                        <p class="text-[11px] text-[#4d4633]">Isi metadata dokumen dan tentukan penanggung jawab alur</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-[#4d4633] mb-1">Judul Resmi Dokumen SOP</label>
                        <input type="text" name="title" required value="{{ old('title') }}" placeholder="Contoh: Prosedur Tata Kelola IT & Keamanan Siber"
                            class="w-full p-2.5 bg-[#fbf9f4] border border-[#cfc6ac] rounded-md font-semibold text-xs text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none transition-all placeholder-[#d6cebf]">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-[#4d4633] mb-1">Kop & Logo Perusahaan</label>
                        <select name="company_header" required class="w-full p-2.5 bg-[#fbf9f4] border border-[#cfc6ac] rounded-md text-xs font-semibold text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none">
                            <option value="pkm">PT PUTRA KELANA MAKMUR (PKM) GROUP</option>
                            <option value="sck">PT SATRIA CITRA KENCANA (SCK)</option>
                            <option value="cpt">PT CAHAYA PERDANA TRANSALAM (CPT)</option>
                            <option value="lbs">PT LINTAS BINTAN SAMUDERA (LBS)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-[#4d4633] mb-1">Nomor Dokumen SOP</label>
                        <input type="text" name="doc_number" required placeholder="Contoh: SOP-HC-001" class="w-full p-2.5 bg-[#fbf9f4] border border-[#cfc6ac] rounded-md text-xs font-semibold text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-[#4d4633] mb-1">Revisi Dokumen</label>
                        <input type="text" name="doc_revision" required value="0" placeholder="0" class="w-full p-2.5 bg-[#fbf9f4] border border-[#cfc6ac] rounded-md text-xs font-semibold text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-[#4d4633] mb-1">Tanggal Efektif</label>
                        <input type="date" name="doc_date" required value="{{ date('Y-m-d') }}" class="w-full p-2.5 bg-[#fbf9f4] border border-[#cfc6ac] rounded-md text-xs font-semibold text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none">
                    </div>

                    <!-- SIGNERS SELECTION -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-[#4d4633] mb-1">Pembuat Dokumen (Creator)</label>
                        <select name="creator_id" required class="w-full p-2.5 bg-[#fbf9f4] border border-[#cfc6ac] rounded-md text-xs font-semibold text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none">
                            @foreach($reviewers as $user)
                                @if(!empty($user->full_name))
                                    <option value="{{ $user->id }}" {{ $user->id == auth()->id() ? 'selected' : '' }}>
                                        {{ $user->full_name }} ({{ $user->role }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-[#4d4633] mb-1">Disahkan Oleh (Final Approver)</label>
                        <select name="final_id" required class="w-full p-2.5 bg-[#fbf9f4] border border-[#cfc6ac] rounded-md text-xs font-semibold text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none">
                            @foreach($reviewers as $user)
                                @if(!empty($user->full_name))
                                    <option value="{{ $user->id }}" {{ $user->username == 'zikri' ? 'selected' : '' }}>
                                        {{ $user->full_name }} ({{ $user->role }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- MULTI REVIEWER SELECTION -->
                    <div class="sm:col-span-2 space-y-1.5">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-[#4d4633]">
                            Diperiksa Oleh (Reviewers Paralel)
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 bg-[#fbf9f4] rounded-md border border-[#cfc6ac] max-h-40 overflow-y-auto custom-scrollbar">
                            @foreach($reviewers as $user)
                                @if(!empty($user->full_name) && $user->username !== 'admin')
                                    <label class="flex items-center space-x-2 p-2 bg-white rounded border border-[#cfc6ac]/60 hover:border-[#705d00]/40 cursor-pointer transition-all">
                                        <input type="checkbox" name="reviewers[]" value="{{ $user->id }}" class="rounded text-[#705d00] focus:ring-[#705d00] w-3.5 h-3.5 border-[#cfc6ac]">
                                        <span class="text-[11px] font-semibold text-[#1e1c14] leading-tight">
                                            {{ $user->full_name }} <br><span class="text-[9px] text-[#4d4633] uppercase">{{ $user->role }}</span>
                                        </span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- BENTO 2 (COL 5): LIVE WORKFLOW PREVIEW CARD -->
            <div class="lg:col-span-5 bg-[#e3dbc9]/40 rounded-lg p-6 shadow-sm border border-[#cfc6ac] flex flex-col justify-between space-y-6">
                <div>
                    <div class="flex items-center space-x-2 border-b border-[#333028]/10 pb-3 mb-4">
                        <span class="material-symbols-outlined text-[#705d00] text-lg">conversion_path</span>
                        <h4 class="font-extrabold text-xs uppercase tracking-wider text-[#1e1c14]">Simulasi Live Workflow Approval</h4>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="p-3 bg-white/80 rounded-md border border-white/60 flex items-center justify-between">
                            <span class="font-bold text-[#4d4633]">Stage 1: Pembuat (Creator)</span>
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[9px] font-bold uppercase">Initiator</span>
                        </div>
                        <div class="p-3 bg-white/80 rounded-md border border-white/60 flex items-center justify-between">
                            <span class="font-bold text-[#4d4633]">Stage 2: Peninjau Paralel</span>
                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded text-[9px] font-bold uppercase">Multi-Reviewer</span>
                        </div>
                        <div class="p-3 bg-white/80 rounded-md border border-white/60 flex items-center justify-between">
                            <span class="font-bold text-[#4d4633]">Stage 3: Pengesahan Final</span>
                            <span class="px-2 py-0.5 bg-[#333028] text-[#ffe16e] rounded text-[9px] font-bold uppercase">Digital Stamp</span>
                        </div>
                    </div>
                </div>

                <div class="p-3.5 bg-white/80 rounded-md border border-white/80 space-y-1.5">
                    <div class="flex items-center space-x-2 text-[#705d00] font-bold text-xs">
                        <span class="material-symbols-outlined text-base">verified</span>
                        <span>Otomatisasi LP Generator Active</span>
                    </div>
                    <p class="text-[10px] text-[#4d4633] leading-relaxed">
                        Lembar Pengesahan (LP) 2 halaman standar ISO e-QMS akan digenerate secara otomatis begitu form diajukan.
                    </p>
                </div>
            </div>

            <!-- BENTO 3 (COL 12): FILE PDF DROP ZONES & SUBMIT -->
            <div class="lg:col-span-12 bg-white rounded-lg p-6 shadow-sm border border-[#cfc6ac]/60 space-y-6">
                <div class="flex items-center space-x-2 border-b border-[#cfc6ac]/40 pb-3">
                    <span class="material-symbols-outlined text-red-600 text-lg">upload_file</span>
                    <h4 class="font-extrabold text-xs uppercase tracking-wider text-[#1e1c14]">Upload Berkas PDF Komponen SOP Support</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Cover PDF -->
                    <div class="p-4 bg-[#fbf9f4] rounded-md border border-[#cfc6ac] hover:border-[#705d00] transition-colors">
                        <label class="block text-xs font-bold text-[#1e1c14] mb-1">1. File Cover (PDF)</label>
                        <p class="text-[10px] text-[#4d4633] mb-3">Judul dan halaman depan SOP</p>
                        <input type="file" name="file_cover" required accept="application/pdf" class="text-xs font-semibold text-[#4d4633] file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-[#333028] file:text-[#ffe16e] cursor-pointer">
                    </div>

                    <!-- Isi PDF -->
                    <div class="p-4 bg-[#fbf9f4] rounded-md border border-[#cfc6ac] hover:border-[#705d00] transition-colors">
                        <label class="block text-xs font-bold text-[#1e1c14] mb-1">2. File Isi Prosedur (PDF)</label>
                        <p class="text-[10px] text-[#4d4633] mb-3">Batang tubuh instruksi SOP</p>
                        <input type="file" name="file_isi" required accept="application/pdf" class="text-xs font-semibold text-[#4d4633] file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-[#333028] file:text-[#ffe16e] cursor-pointer">
                    </div>

                    <!-- Lampiran PDF (Multiple) -->
                    <div class="p-4 bg-[#fbf9f4] rounded-md border border-[#cfc6ac] hover:border-[#705d00] transition-colors">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-[#1e1c14]">3. File Lampiran (Multiple PDF)</label>
                            <span id="lampiran_counter" class="text-[9px] font-bold text-[#705d00] bg-[#ffd92f]/20 px-2 py-0.5 rounded">0 / 20</span>
                        </div>
                        <p class="text-[10px] text-[#4d4633] mb-3">Formulir & tabel pendukung (Opsional)</p>
                        <input type="file" id="file_lampiran_input" name="file_lampiran[]" multiple accept="application/pdf" class="text-xs font-semibold text-[#4d4633] file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-[#333028] file:text-[#ffe16e] cursor-pointer">
                        <div id="lampiran_list" class="mt-3 space-y-1.5 max-h-32 overflow-y-auto custom-scrollbar"></div>
                    </div>
                </div>

                <!-- SUBMIT BAR -->
                <div class="pt-4 border-t border-[#cfc6ac]/40 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <span class="text-xs text-[#4d4633]">Memulai workflow persetujuan akan mengirimkan antrean ke reviewer terpilih.</span>
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-[#333028] text-[#ffe16e] hover:bg-black rounded-md font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">send</span>
                        <span>Simpan & Jalankan Approval Workflow</span>
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file_lampiran_input');
    const fileList = document.getElementById('lampiran_list');
    const counter = document.getElementById('lampiran_counter');
    let stagedFiles = [];

    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const newFiles = Array.from(e.target.files);
            newFiles.forEach(file => {
                if (stagedFiles.length < 20 && !stagedFiles.some(f => f.name === file.name && f.size === file.size)) {
                    stagedFiles.push(file);
                }
            });
            updateFileInputAndUI();
        });
    }

    function updateFileInputAndUI() {
        const dt = new DataTransfer();
        stagedFiles.forEach(f => dt.items.add(f));
        fileInput.files = dt.files;

        counter.textContent = `${stagedFiles.length} / 20`;
        fileList.innerHTML = '';

        stagedFiles.forEach((file, index) => {
            const sizeKb = (file.size / 1024).toFixed(1);
            const div = document.createElement('div');
            div.className = 'flex items-center justify-between p-2 bg-white rounded border border-[#cfc6ac] text-xs font-semibold text-[#1e1c14]';
            div.innerHTML = `
                <div class="flex items-center gap-2 truncate pr-2">
                    <span class="text-[#705d00] font-bold">${index + 1}.</span>
                    <span class="material-symbols-outlined text-red-600 text-sm">picture_as_pdf</span>
                    <span class="truncate text-[11px]">${file.name}</span>
                </div>
                <button type="button" class="text-red-600 hover:text-black font-bold px-1.5 py-0.5 rounded" data-index="${index}">&times;</button>
            `;
            div.querySelector('button').addEventListener('click', function() {
                stagedFiles.splice(index, 1);
                updateFileInputAndUI();
            });
            fileList.appendChild(div);
        });
    }
});
</script>
@endsection