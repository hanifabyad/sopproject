@extends('layouts.admin')

@section('title', 'Tambah SOP Support Baru - e-QMS')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .eqms-scope {
        font-family: 'Poppins', sans-serif;
    }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    
    .sequence-number {
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
</style>

<div class="p-8 eqms-scope">
    {{-- Header Modul Unggah --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-black text-[#1e293b] uppercase tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-cloud-arrow-up text-blue-600"></i> UNGGAH DOKUMEN SOP - {{ strtoupper($department) }}
            </h2>
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Manajemen Pengesahan Otomatis Berbasis Dynamic Workflow</p>
        </div>
        <a href="{{ route('admin.support.show', $department) }}" class="bg-white border border-gray-200 text-gray-700 px-5 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-wider hover:bg-gray-50 transition-all duration-300 shadow-sm flex items-center gap-2 transform hover:-translate-y-0.5">
            <i class="fa-solid fa-arrow-left text-blue-500"></i> Kembali
        </a>
    </div>

    {{-- Notifikasi Error Validasi --}}
    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 font-bold text-xs uppercase tracking-wide rounded-r-xl shadow-sm flex flex-col gap-1">
            <p class="flex items-center gap-1.5 font-black text-red-800"><i class="fa-solid fa-circle-xmark"></i> Terjadi kesalahan validasi berkas:</p>
            <ul class="list-disc ml-5 mt-1 text-[11px] text-red-600 font-semibold lowercase">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Utama Workflow Berantai --}}
    <form action="{{ route('admin.support.store', $department) }}" method="POST" enctype="multipart/form-data" id="sopForm">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- KOLOM KIRI: DETAIL INFORMASI & ALUR PENINJAUAN ESTAFET --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                    <h4 class="text-[10px] font-black uppercase text-[#1e293b] mb-5 tracking-wider border-b border-gray-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-blue-500"></i> Informasi Dasar
                    </h4>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-[9px] font-bold uppercase text-gray-400 tracking-wider mb-1.5 ml-1">Judul SOP Resmi</label>
                            <input type="text" name="title" required value="{{ old('title') }}" placeholder="Contoh: Prosedur IT & Keamanan Data"
                                class="w-full p-3.5 bg-gray-50 border border-gray-100 rounded-xl font-bold text-xs text-[#1e293b] focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all placeholder-gray-300">
                        </div>

                        <div>
                            <label class="block text-[9px] font-bold uppercase text-gray-400 tracking-wider mb-1.5 ml-1">Departemen Support</label>
                            <div class="relative">
                                <input type="text" value="{{ $department }}" readonly 
                                       class="w-full p-3.5 bg-slate-50 text-gray-400 border border-slate-100 rounded-xl font-black text-xs cursor-not-allowed uppercase pl-10">
                                <div class="absolute left-3.5 top-3.5 text-gray-300 text-xs">
                                    <i class="fa-solid fa-building"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="p-5 bg-blue-50/50 rounded-2xl border border-blue-100 shadow-sm">
                                <label class="block text-[10px] font-black uppercase text-blue-700 tracking-wider mb-2 flex items-center gap-1.5">
                                    <i class="fa-solid fa-wand-magic-sparkles text-blue-600"></i> Deteksi Penandatangan Otomatis
                                </label>
                                <p class="text-[9px] font-bold text-gray-500 uppercase leading-relaxed mb-3">
                                    Sistem e-QMS akan membaca struktur section Lembar Pengesahan (LP) secara otomatis:
                                </p>
                                <ul class="text-[9px] font-semibold text-slate-700 space-y-1.5 pl-2">
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-blue-600 text-[10px]"></i> <span><strong>Pembuat Dokumen</strong> &rarr; Stage Creator</span></li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-blue-600 text-[10px]"></i> <span><strong>Diperiksa dan Diketahui oleh:</strong> &rarr; Stage Reviewers (Paralel)</span></li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-blue-600 text-[10px]"></i> <span><strong>Disahkan oleh:</strong> &rarr; Stage Final Approver</span></li>
                                </ul>
                                <p class="mt-3 text-[8px] font-bold text-blue-600 italic uppercase">
                                    * Admin tidak perlu lagi memilih urutan penandatangan secara manual. Nama pegawai pada LP akan langsung dicocokkan ke sistem secara otomatis saat form disimpan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: AREA UPLOAD BUNDLING FILE PDF --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                    <h4 class="text-[10px] font-black uppercase text-gray-400 mb-5 tracking-wider border-b border-gray-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf text-red-500"></i> Dokumen Komponen Pendukung (PDF Only)
                    </h4>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- 1. FILE COVER --}}
                        <div class="p-5 bg-slate-50/50 rounded-xl border border-dashed border-gray-200 hover:border-blue-400 transition-all duration-300 flex flex-col justify-between group">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-blue-600 tracking-wide mb-1 flex items-center gap-1.5"><i class="fa-solid fa-heading"></i> 1. Halaman Cover</label>
                                <p class="text-[8px] font-medium text-gray-400 uppercase tracking-wider mb-4 pl-4">Halaman depan judul SOP utama</p>
                            </div>
                            <input type="file" name="file_cover" required accept="application/pdf" class="text-[9px] font-bold text-gray-400 cursor-pointer file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[9px] file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 transition-all">
                        </div>

                        {{-- 2. AUTO-GENERATED LEMBAR PENGESAHAN --}}
                        <div class="p-5 bg-blue-50/30 rounded-xl border border-dashed border-blue-200 hover:border-blue-500 transition-all duration-300 flex flex-col justify-between group sm:col-span-2">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-blue-700 tracking-wide mb-1 flex items-center gap-1.5"><i class="fa-solid fa-signature"></i> 2. Lembar Pengesahan (Auto-Generated)</label>
                                <p class="text-[8px] font-bold text-blue-400 italic mb-4 pl-4">*Tabel Lembar Pengesahan akan dibuat otomatis secara sistematis dan rapi oleh e-QMS</p>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-semibold text-slate-700 w-full">
                                <div>
                                    <label class="block text-[9px] font-bold uppercase text-gray-400 tracking-wider mb-1.5 ml-1">Kop & Logo Perusahaan</label>
                                    <select name="company_header" required class="w-full p-2.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-[#1e293b] outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="pkm">PT PUTRA KELANA MAKMUR (PKM) GROUP</option>
                                        <option value="sck">PT SATRIA CITRA KENCANA (SCK)</option>
                                        <option value="cpt">PT CAHAYA PERDANA TRANSALAM (CPT)</option>
                                        <option value="lbs">PT LINTAS BINTAN SAMUDERA (LBS)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold uppercase text-gray-400 tracking-wider mb-1.5 ml-1">Nomor Dokumen SOP</label>
                                    <input type="text" name="doc_number" required placeholder="Contoh: PM-SCK-MKT-01" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-[#1e293b] outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold uppercase text-gray-400 tracking-wider mb-1.5 ml-1">Revisi</label>
                                    <input type="text" name="doc_revision" required value="0" placeholder="Contoh: 0" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-[#1e293b] outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold uppercase text-gray-400 tracking-wider mb-1.5 ml-1">Tanggal Efektif</label>
                                    <input type="date" name="doc_date" required value="{{ date('Y-m-d') }}" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-[#1e293b] outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div class="sm:col-span-2 border-t border-blue-100/50 pt-4 mt-2">
                                    <h5 class="text-[9px] font-black uppercase text-blue-700 mb-3 tracking-wider flex items-center gap-1.5"><i class="fa-solid fa-users-gear text-blue-500"></i> Pilih Alur Penandatangan (Signers)</h5>
                                </div>
                                
                                <div>
                                    <label class="block text-[9px] font-bold uppercase text-gray-400 tracking-wider mb-1.5 ml-1">Pembuat Dokumen (Creator)</label>
                                    <select name="creator_id" required class="w-full p-2.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-[#1e293b] outline-none focus:ring-2 focus:ring-blue-500">
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
                                    <label class="block text-[9px] font-bold uppercase text-gray-400 tracking-wider mb-1.5 ml-1">Disahkan Oleh (Final Approver)</label>
                                    <select name="final_id" required class="w-full p-2.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-[#1e293b] outline-none focus:ring-2 focus:ring-blue-500">
                                        @foreach($reviewers as $user)
                                            @if(!empty($user->full_name))
                                                <option value="{{ $user->id }}" {{ $user->username == 'zikri' ? 'selected' : '' }}>
                                                    {{ $user->full_name }} ({{ $user->role }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="sm:col-span-2">
                                    <label class="block text-[9px] font-bold uppercase text-gray-400 tracking-wider mb-2 ml-1">Diperiksa Oleh (Reviewers - Pilih Minimal 3 Pegawai secara Paralel)</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 p-4 bg-white/70 border border-blue-100/50 rounded-xl max-h-48 overflow-y-auto custom-scrollbar shadow-inner w-full">
                                        @foreach($reviewers as $user)
                                            @if(!empty($user->full_name) && $user->username !== 'admin')
                                                <label class="flex items-center space-x-2.5 p-2 bg-slate-50/50 hover:bg-blue-50/30 rounded-lg border border-slate-100 cursor-pointer transition-all duration-200">
                                                    <input type="checkbox" name="reviewers[]" value="{{ $user->id }}" class="rounded text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 border-slate-200">
                                                    <span class="text-[10px] font-bold text-slate-700 leading-tight">
                                                        {{ $user->full_name }} <br><span class="text-[8px] text-slate-400 font-semibold uppercase">{{ $user->role }}</span>
                                                    </span>
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. FILE ISI --}}
                        <div class="p-5 bg-slate-50/50 rounded-xl border border-dashed border-gray-200 hover:border-blue-400 transition-all duration-300 flex flex-col justify-between group">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 tracking-wide mb-1 flex items-center gap-1.5"><i class="fa-solid fa-align-left"></i> 3. Isi Prosedur SOP</label>
                                <p class="text-[8px] font-medium text-gray-400 uppercase tracking-wider mb-4 pl-4">Lembar detail teks isi standar operasional</p>
                            </div>
                            <input type="file" name="file_isi" required accept="application/pdf" class="text-[9px] font-bold text-gray-400 cursor-pointer file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[9px] file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 transition-all">
                        </div>

                        {{-- 4. FILE LAMPIRAN MULTIPLE --}}
                        <div class="p-5 bg-slate-50/50 rounded-xl border border-dashed border-gray-200 hover:border-blue-400 transition-all duration-300 flex flex-col justify-between group">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-[10px] font-black uppercase text-gray-500 tracking-wide flex items-center gap-1.5"><i class="fa-solid fa-paperclip"></i> 4. Lampiran Pendukung (PDF)</label>
                                    <span id="lampiran_counter" class="text-[9px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">0 / 20 lampiran</span>
                                </div>
                                <p class="text-[8px] font-medium text-gray-400 uppercase tracking-wider mb-3 pl-4">Formulir atau tabel pelengkap (Maks 20 PDF, 5MB/file)</p>
                            </div>
                            <input type="file" id="file_lampiran_input" name="file_lampiran[]" multiple accept="application/pdf" class="text-[9px] font-bold text-gray-400 cursor-pointer file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[9px] file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 transition-all">
                            <div id="lampiran_list" class="mt-3 space-y-1.5 max-h-36 overflow-y-auto"></div>
                        </div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const fileInput = document.getElementById('file_lampiran_input');
                        const fileList = document.getElementById('lampiran_list');
                        const counter = document.getElementById('lampiran_counter');
                        let stagedFiles = [];

                        fileInput.addEventListener('change', function(e) {
                            const newFiles = Array.from(e.target.files);
                            newFiles.forEach(file => {
                                if (stagedFiles.length < 20 && !stagedFiles.some(f => f.name === file.name && f.size === file.size)) {
                                    stagedFiles.push(file);
                                }
                            });
                            updateFileInputAndUI();
                        });

                        function updateFileInputAndUI() {
                            const dt = new DataTransfer();
                            stagedFiles.forEach(f => dt.items.add(f));
                            fileInput.files = dt.files;

                            counter.textContent = `${stagedFiles.length} / 20 lampiran`;
                            fileList.innerHTML = '';

                            stagedFiles.forEach((file, index) => {
                                const sizeKb = (file.size / 1024).toFixed(1);
                                const div = document.createElement('div');
                                div.className = 'flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200 text-[9px] font-semibold text-slate-700 shadow-sm';
                                div.innerHTML = `
                                    <div class="flex items-center gap-2 truncate pr-2">
                                        <span class="text-blue-500 font-bold">${index + 1}.</span>
                                        <i class="fa-solid fa-file-pdf text-red-500"></i>
                                        <span class="truncate">${file.name}</span>
                                        <span class="text-[8px] text-gray-400">(${sizeKb} KB)</span>
                                    </div>
                                    <button type="button" class="text-red-400 hover:text-red-600 px-1.5 py-0.5 rounded font-bold" data-index="${index}">&times;</button>
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

                    {{-- Tombol Eksekusi Alur Penandatanganan Otomatis --}}
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-between bg-slate-50 p-5 rounded-xl border border-gray-100 gap-4 shadow-inner">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-sm shadow-sm flex-shrink-0">
                                <i class="fa-solid fa-robot"></i>
                            </div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase leading-relaxed">Sistem kecerdasan e-QMS akan secara otomatis mendeteksi penandatangan<br>dan menginisiasi alur persetujuan paralel dari Lembar Pengesahan.</p>
                        </div>
                        <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-[#1e293b] text-white rounded-xl font-bold uppercase text-[10px] tracking-wider shadow-md hover:bg-blue-600 hover:shadow-blue-100 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-rocket text-xs"></i> Jalankan Alur Pengesahan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection