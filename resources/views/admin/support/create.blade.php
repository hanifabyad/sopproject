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
                            <label class="block text-[9px] font-black uppercase text-blue-600 mb-2.5 ml-1 tracking-widest flex items-center gap-1">
                                <i class="fa-solid fa-route"></i> Alur Peninjauan (Pilih Sesuai Urutan)
                            </label>
                            
                            <div class="space-y-2 max-h-[300px] overflow-y-auto pr-1 custom-scrollbar shadow-inner bg-slate-50/50 p-2 rounded-xl border border-gray-100" id="reviewerList">
                                @foreach($reviewers as $reviewer)
                                <label class="relative flex items-center p-3 bg-white rounded-xl border border-gray-100 hover:border-blue-200 cursor-pointer transition-all duration-200 group overflow-hidden shadow-sm">
                                    <input type="checkbox" name="approvers[]" value="{{ $reviewer->id }}" 
                                           class="reviewer-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 transition-all cursor-pointer">
                                    
                                    <div class="ml-3">
                                        <p class="text-xs font-black text-[#1e293b] group-hover:text-blue-600 transition-colors uppercase tracking-tight">{{ $reviewer->username }}</p>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wide mt-0.5"><i class="fa-solid fa-id-card-clip text-[8px] text-gray-300"></i> {{ $reviewer->role }}</p>
                                    </div>

                                    <div class="sequence-number absolute right-3 w-6 h-6 bg-blue-600 text-white rounded-lg flex items-center justify-center text-[10px] font-black shadow-md shadow-blue-600/20 hidden">
                                        0
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            <p class="mt-2.5 text-[8px] font-bold text-gray-400 italic leading-relaxed uppercase">
                                * PENTING: Klik pimpinan secara berurutan. Contoh: Klik Imam (akan otomatis jadi No.1), klik Trinwetty (No.2), dst.
                            </p>
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

                        {{-- 2. FILE LEMBAR PENGESAHAN --}}
                        <div class="p-5 bg-blue-50/30 rounded-xl border border-dashed border-blue-200 hover:border-blue-500 transition-all duration-300 flex flex-col justify-between group">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-blue-700 tracking-wide mb-1 flex items-center gap-1.5"><i class="fa-solid fa-signature"></i> 2. Lembar Pengesahan</label>
                                <p class="text-[8px] font-bold text-blue-400 italic mb-4 pl-4">*Wajib menyertakan jangkar tanda tangan: [sig01], [sig02], dll.</p>
                            </div>
                            <input type="file" name="file_lp" required accept="application/pdf" class="text-[9px] font-bold text-blue-900 cursor-pointer file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[9px] file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 transition-all">
                        </div>

                        {{-- 3. FILE ISI --}}
                        <div class="p-5 bg-slate-50/50 rounded-xl border border-dashed border-gray-200 hover:border-blue-400 transition-all duration-300 flex flex-col justify-between group">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 tracking-wide mb-1 flex items-center gap-1.5"><i class="fa-solid fa-align-left"></i> 3. Isi Prosedur SOP</label>
                                <p class="text-[8px] font-medium text-gray-400 uppercase tracking-wider mb-4 pl-4">Lembar detail teks isi standar operasional</p>
                            </div>
                            <input type="file" name="file_isi" required accept="application/pdf" class="text-[9px] font-bold text-gray-400 cursor-pointer file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[9px] file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 transition-all">
                        </div>

                        {{-- 4. FILE LAMPIRAN --}}
                        <div class="p-5 bg-slate-50/50 rounded-xl border border-dashed border-gray-200 hover:border-blue-400 transition-all duration-300 flex flex-col justify-between group">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 tracking-wide mb-1 flex items-center gap-1.5"><i class="fa-solid fa-paperclip"></i> 4. Lampiran Pendukung</label>
                                <p class="text-[8px] font-medium text-gray-400 uppercase tracking-wider mb-4 pl-4">Formulir atau tabel pelengkap (Opsional)</p>
                            </div>
                            <input type="file" name="file_lampiran" accept="application/pdf" class="text-[9px] font-bold text-gray-400 cursor-pointer file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[9px] file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 transition-all">
                        </div>
                    </div>

                    {{-- Tombol Eksekusi Alur Penandatanganan Otomatis --}}
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-between bg-slate-50 p-5 rounded-xl border border-gray-100 gap-4 shadow-inner">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-sm shadow-sm flex-shrink-0">
                                <i class="fa-solid fa-robot"></i>
                            </div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase leading-relaxed">Sistem kecerdasan e-QMS akan menjalankan estafet otomatis<br>ke email pimpinan sesuai dengan urutan angka klik yang anda pilih.</p>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.reviewer-checkbox');
        const form = document.getElementById('sopForm');
        let selectedOrder = [];

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = this.closest('label');
                const badge = label.querySelector('.sequence-number');

                if (this.checked) {
                    selectedOrder.push(this.value);
                    label.classList.add('bg-blue-50/60', 'border-blue-300', 'shadow-md');
                } else {
                    selectedOrder = selectedOrder.filter(id => id !== this.value);
                    label.classList.remove('bg-blue-50/60', 'border-blue-300', 'shadow-md');
                    badge.classList.add('hidden');
                }
                updateBadges();
            });
        });

        function updateBadges() {
            selectedOrder.forEach((id, index) => {
                const cb = document.querySelector(`.reviewer-checkbox[value="${id}"]`);
                const badge = cb.closest('label').querySelector('.sequence-number');
                badge.innerText = index + 1;
                badge.classList.remove('hidden');
            });
        }

        form.addEventListener('submit', function(e) {
            if (selectedOrder.length === 0) {
                e.preventDefault();
                alert('🚨 ERROR KERJA: Mohon pilih minimal satu Pimpinan Peninjau pada daftar alur peninjauan!');
                return false;
            }

            document.querySelectorAll('input[name="approvers[]"]').forEach(el => el.remove());
            
            selectedOrder.forEach(id => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'approvers[]';
                hiddenInput.value = id;
                form.appendChild(hiddenInput);
            });
        });
    });
</script>
@endsection