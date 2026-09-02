@extends('layouts.admin')

@section('title', 'Tambah SOP Support Baru')
@section('header_title', 'Unggah SOP Departemen Support')

@section('content')
<div class="space-y-6 bu-support-scope">
    
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.support.show', $department) }}" variant="light" />
            <span class="text-white/30">|</span>
            <div class="flex items-center gap-2 text-xs text-white/80">
                <a href="{{ route('admin.support.index') }}" class="hover:text-[#ffe16e] font-medium">Departemen Support</a>
                <span>/</span>
                <a href="{{ route('admin.support.show', $department) }}" class="hover:text-[#ffe16e] font-medium">{{ strtoupper($department) }}</a>
                <span>/</span>
                <span class="font-bold text-white">Form Inisiasi SOP Support</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold tracking-tight">Form Inisiasi SOP Support</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">Unggah berkas komponen SOP untuk departemen {{ strtoupper($department) }}</p>
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
            
            <!-- INFORMASI SOP & SIGNERS -->
            <div class="lg:col-span-12 bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-5">
                <div class="flex items-center space-x-3 border-b border-sand-200/40 pb-3">
                    <div class="w-8 h-8 rounded-md bg-charcoal-900 text-gold-fixed flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-base">edit_note</span>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-xs capitalize tracking-wider text-on-surface">Informasi SOP Support</h4>
                        <p class="text-[11px] text-on-surface-variant">Isi metadata dokumen dan tentukan penanggung jawab alur</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-bold capitalize tracking-wider text-on-surface-variant mb-1">Judul Resmi Dokumen SOP</label>
                        <input type="text" name="title" required value="{{ old('title') }}" placeholder="Masukkan judul SOP"
                            class="w-full p-2.5 bg-sand-50 border border-sand-200 rounded-md font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all placeholder-[#d6cebf]">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold capitalize tracking-wider text-on-surface-variant mb-1">Kop & Logo Perusahaan</label>
                        <select name="company_header" required class="w-full p-2.5 bg-sand-50 border border-sand-200 rounded-md text-xs font-semibold text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none">
                            <option value="pkm">PT PUTRA KELANA MAKMUR (PKM) GROUP</option>
                            <option value="sck">PT SATRIA CITRA KENCANA (SCK)</option>
                            <option value="cpt">PT CAHAYA PERDANA TRANSALAM (CPT)</option>
                            <option value="lbs">PT LINTAS BINTAN SAMUDERA (LBS)</option>
                            <option value="bki">PT BINTANG KELANA INDONESIA (BKI)</option>
                            <option value="bsn">PT BAINTAN ANUGERAH PRATAMA (BSN)</option>
                            <option value="cngm">PT CITRA NUSANTARA GEMILANG MAKMUR (CNGM)</option>
                            <option value="dms">PT DAYA MAKMUR SEJAHTERA (DMS)</option>
                            <option value="dumas">PT DUMAS COAL INDONESIA (DUMAS)</option>
                            <option value="epcm">PT EKA PUTRA CIPTA MANDIRI (EPCM)</option>
                            <option value="edbm">PT EKA DAYA BAHARI MAS (EDBM)</option>
                            <option value="ekl">PT ERA KENCANA LARAS (EKL)</option>
                            <option value="hiswana">HISWANA MIGAS</option>
                            <option value="is">PT ISMADI SALAM (IS)</option>
                            <option value="lep">PT LINTAS ELOK PERSADA (LEP)</option>
                            <option value="mms">PT MARITIM MAKMUR SEJAHTERA (MMS)</option>
                            <option value="mkw">PT MITHA KELANA WIJAYA (MKW)</option>
                            <option value="mcnp">PT MITRA CIPTA NUSA PERSADA (MCNP)</option>
                            <option value="pims">PT PUTRA INDO MANDIRI SEJAHTERA (PIMS)</option>
                            <option value="pksp">PT PUTRA KELANA SENTOSA PRATAMA (PKSP)</option>
                            <option value="rap">PT RIAU ALAM PERMAI (RAP)</option>
                            <option value="sdrp">PT SATRIA DARMA RAYA PERKASA (SDRP)</option>
                            <option value="sir">PT SATRIA INDO RAYA (SIR)</option>
                            <option value="wimt">PT WAHANA INDAH MARITIM TANGGUH (WIMT)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold capitalize tracking-wider text-on-surface-variant mb-1">Nomor Dokumen SOP</label>
                        <input type="text" name="doc_number" required placeholder="Masukkan nomor dokumen" class="w-full p-2.5 bg-sand-50 border border-sand-200 rounded-md text-xs font-semibold text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold capitalize tracking-wider text-on-surface-variant mb-1">Revisi Dokumen</label>
                        <input type="text" name="doc_revision" required value="0" placeholder="0" class="w-full p-2.5 bg-sand-50 border border-sand-200 rounded-md text-xs font-semibold text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold capitalize tracking-wider text-on-surface-variant mb-1">Tanggal Efektif</label>
                        <input type="date" name="doc_date" required value="{{ date('Y-m-d') }}" class="w-full p-2.5 bg-sand-50 border border-sand-200 rounded-md text-xs font-semibold text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none">
                    </div>

                    <!-- SIGNERS SELECTION -->
                    <div>
                        <label class="block text-[10px] font-bold capitalize tracking-wider text-on-surface-variant mb-1">Pembuat Dokumen (Creator)</label>
                        <select name="creator_id" required class="w-full p-2.5 bg-sand-50 border border-sand-200 rounded-md text-xs font-semibold text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none">
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
                        <label class="block text-[10px] font-bold capitalize tracking-wider text-on-surface-variant mb-1">Disahkan Oleh (Final Approver)</label>
                        @php $zikri = $reviewers->firstWhere('username', 'zikri'); @endphp
                        <div class="flex items-center gap-2 p-2.5 bg-emerald-50 border border-emerald-200 rounded-md text-xs font-bold text-emerald-800"><span class="material-symbols-outlined text-base">verified_user</span><span>{{ $zikri->full_name ?? 'Zikri' }} (Direktur Utama) — wajib</span></div>
                        <button type="button" onclick="toggleAdditionalApprovers('support-additional-approvers', this)" class="mt-2 inline-flex items-center gap-1 px-2 py-1 bg-canvas border border-sand-200 text-on-surface-variant hover:border-gold-500 hover:text-gold-600 rounded text-[9px] font-extrabold capitalize tracking-wide transition-colors"><span class="material-symbols-outlined text-[13px]">person_add</span><span>Tambah Pengesah</span></button>
                        <div id="support-additional-approvers" class="hidden fixed inset-0 z-50 bg-charcoal-900/60 backdrop-blur-sm items-center justify-center p-4">
                            <div class="bg-white w-full max-w-md rounded-lg border border-sand-200 shadow-2xl p-4">
                            <div class="flex items-start justify-between gap-4 border-b border-sand-200/60 pb-3 mb-4"><div><p class="text-[10px] font-extrabold capitalize tracking-wider text-gold-500">Pilih tambahan</p><h4 class="text-sm font-extrabold text-on-surface mt-1">Pilih tambahan pengesah</h4></div><button type="button" onclick="closeAdditionalApprovers('support-additional-approvers')" class="w-8 h-8 rounded-md bg-canvas border border-sand-200 text-on-surface-variant flex items-center justify-center"><span class="material-symbols-outlined text-lg">close</span></button></div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto">
                            @foreach($reviewers as $user)
                                @if(!empty($user->full_name) && strtolower($user->username) !== 'zikri')
                                        <label class="flex items-center gap-2 p-2.5 bg-sand-50 border border-sand-200 rounded-md text-xs font-semibold text-on-surface"><input type="checkbox" name="final_additional_ids[]" value="{{ $user->id }}" class="rounded text-gold-500 focus:ring-gold-500">{{ $user->full_name }} ({{ $user->role }})</label>
                                @endif
                            @endforeach
                            </div><div class="flex justify-end mt-5"><button type="button" onclick="closeAdditionalApprovers('support-additional-approvers')" class="px-4 py-2 bg-charcoal-900 text-gold-fixed rounded-md text-[10px] font-extrabold capitalize tracking-wider">Simpan Pilihan</button></div>
                            </div>
                        </div>
                    </div>

                    <!-- MULTI REVIEWER SELECTION -->
                    <div class="sm:col-span-2 space-y-1.5">
                        <label class="block text-[10px] font-bold capitalize tracking-wider text-on-surface-variant">
                            Diperiksa Oleh (Reviewers Paralel)
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 bg-sand-50 rounded-md border border-sand-200 max-h-40 overflow-y-auto custom-scrollbar">
                            @foreach($reviewers as $user)
                                @if(!empty($user->full_name) && $user->username !== 'admin')
                                    <label class="flex items-center space-x-2 p-2 bg-white rounded border border-sand-200/60 hover:border-gold-500/40 cursor-pointer transition-all">
                                        <input type="checkbox" name="reviewers[]" value="{{ $user->id }}" class="rounded text-gold-500 focus:ring-gold-500 w-3.5 h-3.5 border-sand-200">
                                        <span class="text-[11px] font-semibold text-on-surface leading-tight">
                                            {{ $user->full_name }} <br><span class="text-[9px] text-on-surface-variant capitalize">{{ $user->role }}</span>
                                        </span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- BENTO 3 (COL 12): FILE PDF DROP ZONES & SUBMIT -->
            <div class="lg:col-span-12 bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-6">
                <div class="flex items-center space-x-2 border-b border-sand-200/40 pb-3">
                    <span class="material-symbols-outlined text-red-600 text-lg">upload_file</span>
                    <h4 class="font-extrabold text-xs capitalize tracking-wider text-on-surface">Upload Berkas PDF Komponen SOP Support</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Cover PDF -->
                    <div class="p-4 bg-sand-50 rounded-md border border-sand-200 hover:border-gold-500 transition-colors">
                        <label class="block text-xs font-bold text-on-surface mb-1">1. File Cover (PDF)</label>
                        <p class="text-[10px] text-on-surface-variant mb-3">Judul dan halaman depan SOP</p>
                        <x-file-input name="file_cover" accept="application/pdf" label="Pilih file cover" hint="PDF, maksimal 10 MB" :required="true" />
                    </div>

                    <!-- Isi PDF -->
                    <div class="p-4 bg-sand-50 rounded-md border border-sand-200 hover:border-gold-500 transition-colors">
                        <label class="block text-xs font-bold text-on-surface mb-1">2. File Isi Prosedur (PDF)</label>
                        <p class="text-[10px] text-on-surface-variant mb-3">Batang tubuh instruksi SOP</p>
                        <x-file-input name="file_isi" accept="application/pdf" label="Pilih file isi prosedur" hint="PDF, maksimal 10 MB" :required="true" />
                    </div>

                    <!-- Lampiran PDF (Multiple) -->
                    <div class="p-4 bg-sand-50 rounded-md border border-sand-200 hover:border-gold-500 transition-colors">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-on-surface">3. File Lampiran (Multiple PDF)</label>
                            <span id="lampiran_counter" class="text-[9px] font-bold text-gold-500 bg-[#ffd92f]/20 px-2 py-0.5 rounded">0 / 20</span>
                        </div>
                        <p class="text-[10px] text-on-surface-variant mb-3">Formulir & tabel pendukung (Opsional)</p>
                        <x-file-input id="file_lampiran_input" name="file_lampiran[]" accept="application/pdf" label="Pilih file lampiran" hint="Pilih satu atau beberapa PDF, maksimal 20 file" :multiple="true" />
                        <div id="lampiran_list" class="mt-3 space-y-1.5 max-h-32 overflow-y-auto custom-scrollbar"></div>
                    </div>
                </div>

                <!-- SUBMIT BAR -->
                <div class="pt-4 border-t border-sand-200/40 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <span class="text-xs text-on-surface-variant">Formulir akan dikirimkan ke reviewer yang dipilih setelah diajukan.</span>
                    <button type="submit" class="w-full sm:w-auto btn-interactive font-extrabold capitalize tracking-wider py-3">
                        <span class="btn-interactive-default">
                            <span class="btn-interactive-dot"></span>
                            <span>Kirim untuk Persetujuan</span>
                        </span>
                        <span class="btn-interactive-hover">
                            <span>Kirim untuk Persetujuan</span>
                            <i class="ph ph-arrow-right text-sm"></i>
                        </span>
                        <span class="btn-interactive-bg"></span>
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
            div.className = 'flex items-center justify-between p-2 bg-white rounded border border-sand-200 text-xs font-semibold text-on-surface';
            div.innerHTML = `
                <div class="flex items-center gap-2 truncate pr-2">
                    <span class="text-gold-500 font-bold">${index + 1}.</span>
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
<script>
function toggleAdditionalApprovers(id, button) {
    const panel = document.getElementById(id);
    panel.classList.toggle('hidden');
    panel.classList.toggle('flex');
    const icon = button.querySelector('.material-symbols-outlined:last-child');
    if (icon) icon.textContent = panel.classList.contains('hidden') ? 'expand_more' : 'expand_less';
}
function closeAdditionalApprovers(id) { const panel = document.getElementById(id); panel.classList.add('hidden'); panel.classList.remove('flex'); }
</script>
@endsection
