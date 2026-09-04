@extends(Auth::user()?->role === 'admin' ? 'layouts.admin' : 'layouts.reviewer')

@section('title', 'Unggah Bukti Sosialisasi - ' . $document->title)
@section('header_title', 'Sosialisasi Dokumen SOP')

@section('content')
<div class="space-y-6">

    <!-- TOP HEADER -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-[2px] p-5 shadow-sm border border-white/15 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('user.socializations.index') }}" variant="light" text="Kembali" />
            <span class="text-white/30">|</span>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold">
                <a href="{{ Auth::user()?->role === 'admin' ? route('admin.dashboard') : route('reviewer.dashboard') }}" class="hover:text-[#ffe16e] font-bold flex items-center gap-1">
                    <i class="ph ph-squares-four text-base"></i>
                    <span>Dashboard</span>
                </a>
                <span>/</span>
                <a href="{{ route('user.socializations.index') }}" class="hover:text-[#ffe16e] font-bold">Sosialisasi SOP</a>
                <span>/</span>
                <span class="font-bold text-white">Unggah Bukti</span>
            </div>
        </div>

        <div class="space-y-1">
            <h2 class="text-xl font-extrabold tracking-tight capitalize text-white">{{ $document->title }}</h2>
            <div class="flex items-center gap-2 text-xs text-white/90 font-medium">
                <span class="px-2 py-0.5 bg-white/20 rounded-[2px] font-bold text-[10.5px] uppercase tracking-wider">{{ $document->department }}</span>
                @if($document->doc_number)
                    <span>•</span>
                    <span class="font-mono text-white/95 font-semibold text-[11px]">{{ $document->doc_number }}</span>
                @endif
                <span>•</span>
                <span class="font-semibold text-[11px]">Revisi {{ $document->doc_revision ?? '0' }}</span>
            </div>
        </div>
    </div>

    <!-- MAIN WORKSPACE: SPLIT-SCREEN (PDF VIEWER KIRI, FORM UNGGAH KANAN) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- SISI KIRI: PRATINJAU NASKAH SOP RESMI (7 COLS) -->
        <div class="lg:col-span-7 bg-white rounded-[2px] shadow-sm border border-sand-200/80 overflow-hidden flex flex-col h-[750px]">
            <div class="bg-slate-50 p-3.5 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center space-x-2 text-[#1677B8]">
                    <i class="ph ph-file-pdf text-lg"></i>
                    <h4 class="font-bold text-xs text-slate-800">Naskah SOP Resmi (Untuk Materi Sosialisasi)</h4>
                </div>
                <a href="{{ route('reviewer.stream.file', $document->id) }}" target="_blank" class="text-[11px] text-[#1677B8] hover:underline font-bold flex items-center gap-1">
                    <span>Buka Penuh</span>
                    <i class="ph ph-arrow-square-out"></i>
                </a>
            </div>
            
            <div class="flex-1 bg-slate-900">
                <iframe src="{{ route('reviewer.stream.file', $document->id) }}#toolbar=1" class="w-full h-full border-none" title="Naskah SOP"></iframe>
            </div>
        </div>

        <!-- SISI KANAN: FORMULIR UNGGAH BUKTI SOSIALISASI (5 COLS) -->
        <div class="lg:col-span-5 space-y-4">
            
            @if($existingSocialization && $existingSocialization->status === 'verified')
                <!-- KARTU SUDAH TERVERIFIKASI -->
                <div class="bg-white p-5 rounded-[2px] shadow-sm border border-emerald-200 space-y-4">
                    <div class="flex items-center space-x-2.5 text-emerald-700 border-b border-emerald-100 pb-3">
                        <i class="ph ph-seal-check text-2xl"></i>
                        <div>
                            <h4 class="font-extrabold text-xs">Sosialisasi Telah Terverifikasi & Sah</h4>
                            <p class="text-[11px] text-emerald-600">Dokumen SOP ini telah disosialisasikan ke tim lapangan.</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="p-3 bg-emerald-50 rounded-[2px] border border-emerald-100 space-y-2">
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase font-bold block">Tanggal Pelaksanaan:</span>
                                <strong class="text-slate-800">{{ $existingSocialization->socialization_date ? $existingSocialization->socialization_date->format('d F Y') : '-' }}</strong>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase font-bold block">PIC Penanggung Jawab:</span>
                                <strong class="text-slate-800">{{ $existingSocialization->user->full_name ?? ($existingSocialization->user->username ?? 'PIC Unit') }}</strong>
                            </div>
                            @if($existingSocialization->attendance_file)
                                <div>
                                    <span class="text-[10px] text-slate-500 uppercase font-bold block mb-1">Lembar Daftar Hadir:</span>
                                    <a href="{{ asset('storage/' . $existingSocialization->attendance_file) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 bg-white text-[#1677B8] border border-blue-200 rounded-[2px] font-bold hover:bg-blue-50">
                                        <i class="ph ph-file-pdf text-sm"></i>
                                        <span>Buka Berkas Daftar Hadir</span>
                                    </a>
                                </div>
                            @endif
                        </div>

                        @if(!empty($existingSocialization->photos) && count($existingSocialization->photos) > 0)
                            <div>
                                <span class="text-[11px] font-bold text-slate-700 block mb-2">Foto Dokumentasi Kegiatan:</span>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($existingSocialization->photos as $p)
                                        <a href="{{ asset('storage/' . $p) }}" target="_blank" class="block aspect-video rounded-[2px] overflow-hidden border border-slate-200 hover:opacity-90 shadow-2xs">
                                            <img src="{{ asset('storage/' . $p) }}" class="w-full h-full object-cover" alt="Foto">
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- FORMULIR INPUT BUKTI SOSIALISASI -->
            <div class="bg-white p-5 rounded-[2px] shadow-sm border border-sand-200/80 space-y-4">
                <div class="border-b border-sand-200/60 pb-3">
                    <div class="flex items-center space-x-2 text-[#1677B8]">
                        <i class="ph ph-upload-simple text-xl"></i>
                        <h4 class="font-extrabold text-xs text-slate-800">
                            {{ $existingSocialization ? 'Perbarui / Unggah Ulang Bukti' : 'Formulir Unggah Bukti Sosialisasi' }}
                        </h4>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5">Lampirkan daftar hadir bertanda tangan dan foto-foto kegiatan.</p>
                </div>

                @if ($errors->any())
                    <div class="p-3 bg-rose-50 border-l-4 border-rose-500 text-rose-800 font-bold text-xs rounded-r space-y-1">
                        @foreach ($errors->all() as $error)
                            <p class="flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('documents.socialization.store', $document->id) }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf
                    <input type="hidden" name="attendance_session_token" id="form_attendance_session_token" value="">

                    <!-- 1. TANGGAL SOSIALISASI -->
                    <div>
                        <label for="socialization_date" class="block text-slate-700 font-bold mb-1">
                            1. Tanggal Pelaksanaan Sosialisasi <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" 
                               id="socialization_date" 
                               name="socialization_date" 
                               value="{{ old('socialization_date', date('Y-m-d')) }}" 
                               required 
                               class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-medium text-slate-800">
                    </div>

                    <!-- 2. LEMBAR DAFTAR HADIR PESERTA (PILIH FORM / UPLOAD) -->
                    <div class="space-y-2">
                        <label class="block text-slate-700 font-bold">
                            2. Berkas Lembar Daftar Hadir Peserta <span class="text-rose-500">*</span>
                        </label>

                        <!-- TOGGLE METODE: FORM OTOMATIS vs UPLOAD BERKAS FISIK -->
                        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-[2px] border border-slate-200">
                            <label id="tabOptForm" onclick="setAttendanceMethod('form')" class="flex items-center justify-center gap-1.5 p-2 rounded-[2px] cursor-pointer transition-all text-xs font-bold text-[#1677B8] bg-white shadow-xs border border-blue-200">
                                <input type="radio" name="attendance_method" value="form" class="sr-only" onchange="setAttendanceMethod('form')" checked>
                                <i class="ph ph-note-pencil text-sm"></i>
                                <span>Isi Form Daftar Hadir</span>
                            </label>
                            <label id="tabOptUpload" onclick="setAttendanceMethod('upload')" class="flex items-center justify-center gap-1.5 p-2 rounded-[2px] cursor-pointer transition-all text-xs font-bold text-slate-600 hover:text-slate-900 border border-transparent">
                                <input type="radio" name="attendance_method" value="upload" class="sr-only" onchange="setAttendanceMethod('upload')">
                                <i class="ph ph-upload-simple text-sm"></i>
                                <span>Upload Berkas / Foto</span>
                            </label>
                        </div>

                        <!-- KONTEN 1: FORM GENERATOR BS FORM 9 -->
                        <div id="sectionAttendanceForm" class="p-3.5 bg-blue-50/40 rounded-[2px] border border-blue-200 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-slate-700 font-bold text-[11px] mb-0.5">Kop Surat / Entitas PT <span class="text-rose-500">*</span></label>
                                    <select name="company" id="form_company" class="w-full text-xs p-2 rounded-[2px] border border-slate-300 focus:ring-1 focus:ring-[#1677B8] bg-white font-semibold text-slate-800">
                                        @php
                                            $currentComp = strtolower((string)($document->company_header ?? 'pkm'));
                                        @endphp
                                        <option value="pkm" {{ $currentComp === 'pkm' ? 'selected' : '' }}>PT PUTRA KELANA MAKMUR (PKM) GROUP</option>
                                        <option value="sck" {{ $currentComp === 'sck' ? 'selected' : '' }}>PT SATRIA CITRA KENCANA (SCK)</option>
                                        <option value="cpt" {{ $currentComp === 'cpt' ? 'selected' : '' }}>PT CAHAYA PERDANA TRANSALAM (CPT)</option>
                                        <option value="lbs" {{ $currentComp === 'lbs' ? 'selected' : '' }}>PT LINTAS BINTAN SAMUDERA (LBS)</option>
                                        <option value="bki" {{ $currentComp === 'bki' ? 'selected' : '' }}>PT BINTANG KELANA INDONESIA (BKI)</option>
                                        <option value="bsn" {{ $currentComp === 'bsn' ? 'selected' : '' }}>PT BAINTAN ANUGERAH PRATAMA (BSN)</option>
                                        <option value="cngm" {{ $currentComp === 'cngm' ? 'selected' : '' }}>PT CITRA NUSANTARA GEMILANG MAKMUR (CNGM)</option>
                                        <option value="dms" {{ $currentComp === 'dms' ? 'selected' : '' }}>PT DAYA MAKMUR SEJAHTERA (DMS)</option>
                                        <option value="dumas" {{ $currentComp === 'dumas' ? 'selected' : '' }}>PT DUMAS COAL INDONESIA (DUMAS)</option>
                                        <option value="epcm" {{ $currentComp === 'epcm' ? 'selected' : '' }}>PT EKA PUTRA CIPTA MANDIRI (EPCM)</option>
                                        <option value="edbm" {{ $currentComp === 'edbm' ? 'selected' : '' }}>PT EKA DAYA BAHARI MAS (EDBM)</option>
                                        <option value="ekl" {{ $currentComp === 'ekl' ? 'selected' : '' }}>PT ERA KENCANA LARAS (EKL)</option>
                                        <option value="hiswana" {{ $currentComp === 'hiswana' ? 'selected' : '' }}>HISWANA MIGAS</option>
                                        <option value="is" {{ $currentComp === 'is' ? 'selected' : '' }}>PT ISMADI SALAM (IS)</option>
                                        <option value="lep" {{ $currentComp === 'lep' ? 'selected' : '' }}>PT LINTAS ELOK PERSADA (LEP)</option>
                                        <option value="mms" {{ $currentComp === 'mms' ? 'selected' : '' }}>PT MARITIM MAKMUR SEJAHTERA (MMS)</option>
                                        <option value="mkw" {{ $currentComp === 'mkw' ? 'selected' : '' }}>PT MITHA KELANA WIJAYA (MKW)</option>
                                        <option value="mcnp" {{ $currentComp === 'mcnp' ? 'selected' : '' }}>PT MITRA CIPTA NUSA PERSADA (MCNP)</option>
                                        <option value="pims" {{ $currentComp === 'pims' ? 'selected' : '' }}>PT PUTRA INDO MANDIRI SEJAHTERA (PIMS)</option>
                                        <option value="pksp" {{ $currentComp === 'pksp' ? 'selected' : '' }}>PT PUTRA KELANA SENTOSA PRATAMA (PKSP)</option>
                                        <option value="rap" {{ $currentComp === 'rap' ? 'selected' : '' }}>PT RIAU ALAM PERMAI (RAP)</option>
                                        <option value="sdrp" {{ $currentComp === 'sdrp' ? 'selected' : '' }}>PT SATRIA DARMA RAYA PERKASA (SDRP)</option>
                                        <option value="sir" {{ $currentComp === 'sir' ? 'selected' : '' }}>PT SATRIA INDO RAYA (SIR)</option>
                                        <option value="wimt" {{ $currentComp === 'wimt' ? 'selected' : '' }}>PT WAHANA INDAH MARITIM TANGGUH (WIMT)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-bold text-[11px] mb-0.5">Nama Pemateri / PIC</label>
                                    <input type="text" name="speaker" id="form_speaker" value="{{ Auth::user()?->full_name ?? Auth::user()?->username }}" placeholder="Nama Pemateri..." class="w-full text-xs p-2 rounded-[2px] border border-slate-300 focus:ring-1 focus:ring-[#1677B8] font-semibold text-slate-800 bg-white">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-slate-700 font-bold text-[11px] mb-0.5">Waktu / Jam Kegiatan</label>
                                    <input type="text" name="time" id="form_time" value="09:00 WIB - Selesai" class="w-full text-xs p-2 rounded-[2px] border border-slate-300 font-medium text-slate-800 bg-white">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-bold text-[11px] mb-0.5">Tempat / Lokasi</label>
                                    <input type="text" name="location" id="form_location" value="Ruang Rapat / Lokasi Unit" class="w-full text-xs p-2 rounded-[2px] border border-slate-300 font-medium text-slate-800 bg-white">
                                </div>
                            </div>

                            <!-- ACTION BOX: BUAT QR PRESENSI -->
                            <div class="p-3 bg-white rounded-[2px] border border-blue-200/80 flex flex-col sm:flex-row items-center justify-between gap-2 shadow-2xs">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-8 h-8 rounded-[2px] bg-sky-100 text-[#1677B8] flex items-center justify-center font-bold flex-shrink-0">
                                        <i class="ph ph-qr-code text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="font-extrabold text-xs text-slate-900 leading-tight">Presensi Mandiri via Scan QR</p>
                                        <p class="text-[10px] text-slate-500" id="qrSessionStatusText">Peserta mengisi nama & jabatan saat hadir di ruangan</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-interactive-button text="Buka Layar QR Presensi" variant="blue" icon="ph ph-broadcast text-sm" type="button" onclick="startQrSessionFromCreate()" id="btnOpenQrModalMain" />
                                </div>
                            </div>

                            <!-- BADGE STATUS SESI PRESENSI AKTIF DI FORM (MUNCUL SETELAH QR DIBUAT) -->
                            <div id="activeSessionBadgeBox" class="hidden p-3 bg-emerald-50 border border-emerald-200 rounded-[2px] flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                    </span>
                                    <div>
                                        <p class="text-xs font-black text-emerald-900">Sesi QR Presensi Tersimpan & Aktif</p>
                                        <p class="text-[10.5px] text-emerald-700 font-semibold" id="formScannedCountText">0 Peserta telah mengisi presensi</p>
                                    </div>
                                </div>
                                <button type="button" onclick="startQrSessionFromCreate(false)" class="text-xs font-bold text-emerald-800 hover:text-emerald-900 underline cursor-pointer bg-transparent border-none">
                                    Lihat / Buka QR &rarr;
                                </button>
                            </div>
                        </div>

                        <!-- KONTEN 2: UPLOAD BERKAS SCAN / FISIK -->
                        <div id="sectionAttendanceUpload" class="hidden p-3.5 bg-slate-50 rounded-[2px] border border-slate-200 space-y-2">
                            <x-file-input id="attendance_upload_field" name="attendance_file" accept=".pdf,.jpg,.jpeg,.png" label="Pilih lembar daftar hadir fisik" hint="PDF, JPG, PNG bertanda tangan peserta (Maks. 10 MB)" :required="false" :maxSize="10" />
                        </div>
                    </div>

                    <!-- 3. FOTO DOKUMENTASI KEGIATAN (OPSIONAL) -->
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">
                            3. Foto Dokumentasi Pelaksanaan Kegiatan (Opsional)
                        </label>
                        <x-file-input name="photos[]" accept=".jpg,.jpeg,.png" label="Pilih foto dokumentasi" hint="Pilih 1 sampai 10 foto dokumentasi (Opsional, JPG, PNG, Maks. 10 MB/foto)" :multiple="true" :required="false" :maxSize="10" />
                    </div>

                    <!-- 4. NOTULEN / CATATAN PELAKSANAAN -->
                    <div>
                        <label for="notes" class="block text-slate-700 font-bold mb-1">
                            4. Notulen / Catatan Pelaksanaan (Opsional)
                        </label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="Tuliskan catatan pelaksanaan kegiatan sosialisasi (misal: jumlah peserta hadir, poin-poin tanya jawab, dsb)..." 
                                  class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-medium text-slate-800">{{ old('notes') }}</textarea>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <div class="pt-2">
                        <x-interactive-button text="Konfirmasi" variant="blue" icon="ph ph-check-circle text-base" type="submit" class="w-full justify-center py-2.5" />
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- ========================================================================= -->
<!-- MODAL 3: LAYAR INTERAKTIF QR PRESENSI SOSIALISASI & LIVE PESERTA -->
<!-- ========================================================================= -->
<div id="qrSessionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/75 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden border border-slate-200 flex flex-col max-h-[92vh] animate-in fade-in zoom-in duration-200">
        <!-- MODAL HEADER -->
        <div class="px-6 py-4 bg-gradient-to-r from-[#002b5c] via-[#1677B8] to-[#00b4d8] text-white flex items-center justify-between shadow-sm flex-shrink-0">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center font-bold shadow-inner">
                    <i class="ph ph-qr-code text-2xl text-white"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold tracking-tight text-white">Layar QR Presensi Sosialisasi SOP</h3>
                    <p class="text-xs text-white/85 font-medium">Tampilkan QR ini kepada peserta di ruangan untuk pengisian daftar hadir</p>
                </div>
            </div>
            <button type="button" onclick="closeQrSessionModal()" class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all cursor-pointer border border-white/20">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <!-- MODAL BODY -->
        <div class="p-6 space-y-5 overflow-y-auto custom-scrollbar flex-1 text-xs">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-stretch">
                <!-- QR CODE DISPLAY CARD -->
                <div class="md:col-span-5 flex flex-col items-center justify-between p-5 bg-gradient-to-b from-slate-50 to-slate-100/70 rounded-2xl border border-slate-200 text-center shadow-xs">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10.5px] font-extrabold mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Sesi Presensi Aktif</span>
                    </div>

                    <div id="qrSvgContainer" class="w-48 h-48 bg-white p-2.5 rounded-xl shadow-sm border border-slate-200 flex items-center justify-center">
                        <i class="ph ph-spinner animate-spin text-3xl text-[#1677B8]"></i>
                    </div>

                    <div class="mt-3">
                        <span class="text-xs font-black text-slate-900 uppercase tracking-wider block">Scan QR via Kamera HP</span>
                        <p class="text-[10.5px] text-slate-500 font-medium mt-0.5">Membuka formulir nama & jabatan digital</p>
                    </div>
                </div>

                <!-- SESSION DETAILS & ACTIONS -->
                <div class="md:col-span-7 flex flex-col justify-between space-y-3.5">
                    <!-- AGENDA CARD -->
                    <div class="p-4 bg-gradient-to-br from-blue-50/90 to-sky-50/50 rounded-xl border border-blue-200 space-y-1.5 shadow-2xs">
                        <span class="text-[10px] font-extrabold text-[#1677B8] uppercase tracking-wider flex items-center gap-1">
                            <i class="ph ph-calendar-check text-xs"></i>
                            <span>Agenda Sosialisasi SOP</span>
                        </span>
                        <h4 id="qrModalAgenda" class="text-sm font-black text-slate-900 leading-snug">Sosialisasi {{ $document->title }}</h4>
                        <div class="flex items-center gap-2 pt-0.5 text-xs font-semibold text-slate-600">
                            <i class="ph ph-user-circle text-slate-500 text-sm"></i>
                            <span id="qrModalSpeaker"></span>
                        </div>
                    </div>

                    <!-- SWITCHER MODE URL -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-extrabold text-slate-700 flex items-center gap-1">
                                <i class="ph ph-link text-[#1677B8]"></i>
                                <span>Tautan Akses Formulir:</span>
                            </label>
                            <div id="qrModeSwitcherBox" class="inline-flex rounded-[2px] p-0.5 bg-slate-100 border border-slate-200 text-[10.5px] font-bold">
                                <button type="button" onclick="switchQrUrlMode('lan')" id="btnModeLan" class="px-2.5 py-1 rounded-[2px] bg-white text-[#1677B8] shadow-xs cursor-pointer transition-all border-none font-extrabold">
                                    📱 Scan HP (Wi-Fi)
                                </button>
                                <button type="button" onclick="switchQrUrlMode('local')" id="btnModeLocal" class="px-2.5 py-1 rounded-[2px] text-slate-600 hover:text-slate-900 cursor-pointer transition-all border-none font-bold">
                                    💻 Laptop (Local)
                                </button>
                            </div>
                        </div>

                        <!-- URL INPUT & COPY / OPEN BUTTONS -->
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <i class="ph ph-link text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-sm"></i>
                                <input type="text" id="qrDirectUrlInput" readonly class="w-full text-xs font-mono pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-[2px] text-slate-800 font-semibold focus:bg-white focus:border-[#1677B8] outline-none select-all transition-all shadow-inner">
                            </div>
                            <button type="button" onclick="copyPresensiLink()" id="btnCopyPresensiLink" class="h-9 px-3.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-[2px] font-bold text-xs flex items-center gap-1.5 transition-all shadow-xs cursor-pointer flex-shrink-0">
                                <i class="ph ph-copy text-sm text-[#1677B8]"></i>
                                <span>Salin</span>
                            </button>
                            <a href="#" target="_blank" id="btnOpenPresensiTab" class="h-9 px-4 bg-[#1677B8] hover:bg-[#1260a0] text-white rounded-[2px] font-bold text-xs flex items-center gap-1.5 transition-all shadow-xs cursor-pointer border-none flex-shrink-0">
                                <i class="ph ph-arrow-square-out text-sm"></i>
                                <span>Buka Form</span>
                            </a>
                        </div>

                        <!-- HINT TEXT -->
                        <div id="qrModeHintBox" class="p-2.5 rounded-[2px] bg-amber-50/80 border border-amber-200/80 text-[10.5px] text-amber-900 flex items-start gap-2">
                            <i class="ph ph-info text-base text-amber-600 flex-shrink-0 mt-0.5"></i>
                            <p id="qrModeHint" class="leading-relaxed">
                                Mode Scan HP aktif. Pastikan server dijalankan dengan: <code class="bg-white px-1.5 py-0.5 rounded-[2px] border border-amber-300 font-mono font-bold text-amber-800">php artisan serve --host=0.0.0.0 --port=8000</code>
                            </p>
                        </div>
                    </div>

                    <!-- DOWNLOAD PDF BUTTON -->
                    <div class="pt-0.5">
                        <a href="#" target="_blank" id="btnDownloadSessionPdf" class="w-full h-11 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-extrabold text-xs flex items-center justify-center gap-2 shadow-sm hover:shadow-md transition-all cursor-pointer border-none">
                            <i class="ph ph-printer text-lg"></i>
                            <span>Cetak / Unduh Lembar Hadir (PDF)</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- LIVE ATTENDEE LIST -->
            <div class="border-t border-slate-200 pt-5 space-y-3">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider">
                            Peserta yang Sudah Scan Presensi
                        </h4>
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-black border border-emerald-200">
                            <span id="liveAttendeeCount">0</span> Peserta
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 text-[11px] text-slate-500 font-semibold bg-slate-100 px-2.5 py-1 rounded-[2px]">
                        <i class="ph ph-arrows-clockwise text-slate-400"></i>
                        <span>Update otomatis setiap 3 detik</span>
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[10.5px] font-extrabold text-slate-700 uppercase tracking-wider">
                            <tr>
                                <th class="py-2.5 px-3 w-10 text-center">No</th>
                                <th class="py-2.5 px-3">Nama Lengkap Peserta</th>
                                <th class="py-2.5 px-3">Jabatan / Bagian</th>
                                <th class="py-2.5 px-3 w-36 text-center">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody id="liveAttendeeTbody" class="divide-y divide-slate-100 font-medium text-slate-800">
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400 text-xs italic">
                                    <i class="ph ph-users text-2xl text-slate-300 block mb-1"></i>
                                    Belum ada peserta yang mengisi presensi. Tampilkan QR Code di atas.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- FOOTER ACTIONS -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between flex-shrink-0">
            <span class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                <i class="ph ph-shield-check text-base text-emerald-600"></i>
                <span>Presensi terintegrasi ISO & audit trail otomatis.</span>
            </span>
            <button type="button" onclick="closeQrSessionModal()" class="h-9 px-5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded-[2px] font-bold text-xs transition-all shadow-xs cursor-pointer">
                Selesai & Tutup
            </button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    let livePollingTimer = null;
    let currentSessionToken = null;
    let qrCodeInstance = null;
    let activeSessionUrls = { lan: '', local: '' };

    function switchQrUrlMode(mode) {
        const btnLan = document.getElementById('btnModeLan');
        const btnLocal = document.getElementById('btnModeLocal');
        const hint = document.getElementById('qrModeHint');
        const targetUrl = mode === 'local' 
            ? (activeSessionUrls.local || window.location.origin + '/presensi/' + currentSessionToken) 
            : (activeSessionUrls.lan || activeSessionUrls.local);

        if (mode === 'local') {
            if (btnLocal) btnLocal.className = 'px-2.5 py-1 rounded-[2px] bg-white text-[#1677B8] shadow-xs cursor-pointer transition-all border-none font-extrabold';
            if (btnLan) btnLan.className = 'px-2.5 py-1 rounded-[2px] text-slate-600 hover:text-slate-900 cursor-pointer transition-all border-none font-bold';
            if (hint) hint.innerHTML = 'Mode Laptop aktif. Buka langsung di tab browser ini menggunakan tombol <b>Buka Form</b>.';
        } else {
            if (btnLan) btnLan.className = 'px-2.5 py-1 rounded-[2px] bg-white text-[#1677B8] shadow-xs cursor-pointer transition-all border-none font-extrabold';
            if (btnLocal) btnLocal.className = 'px-2.5 py-1 rounded-[2px] text-slate-600 hover:text-slate-900 cursor-pointer transition-all border-none font-bold';
            if (hint) hint.innerHTML = 'Mode Scan HP aktif. Pastikan server dijalankan dengan: <code class="bg-white px-1.5 py-0.5 rounded-[2px] border border-amber-300 font-mono font-bold text-amber-800">php artisan serve --host=0.0.0.0 --port=8000</code>';
        }

        const input = document.getElementById('qrDirectUrlInput');
        const openBtn = document.getElementById('btnOpenPresensiTab');
        if (input) input.value = targetUrl;
        if (openBtn) openBtn.href = targetUrl;

        // Re-render QR code
        if (targetUrl) {
            const container = document.getElementById('qrSvgContainer');
            if (container) {
                container.innerHTML = '';
                qrCodeInstance = new QRCode(container, {
                    text: targetUrl,
                    width: 180,
                    height: 180,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
            }
        }
    }

    function setAttendanceMethod(method) {
        const secForm = document.getElementById('sectionAttendanceForm');
        const secUpload = document.getElementById('sectionAttendanceUpload');
        const tabForm = document.getElementById('tabOptForm');
        const tabUpload = document.getElementById('tabOptUpload');
        const fileControl = document.querySelector('#sectionAttendanceUpload input[type="file"]');

        if (method === 'form') {
            secForm.classList.remove('hidden');
            secUpload.classList.add('hidden');
            secForm.style.display = '';
            secUpload.style.display = 'none';
            
            tabForm.className = "flex items-center justify-center gap-1.5 p-2 rounded-[2px] cursor-pointer transition-all text-xs font-bold text-[#1677B8] bg-white shadow-xs border border-blue-200";
            tabUpload.className = "flex items-center justify-center gap-1.5 p-2 rounded-[2px] cursor-pointer transition-all text-xs font-bold text-slate-600 hover:text-slate-900 border border-transparent";
            
            if (fileControl) fileControl.removeAttribute('required');
        } else {
            secForm.classList.add('hidden');
            secUpload.classList.remove('hidden');
            secForm.style.display = 'none';
            secUpload.style.display = '';

            tabUpload.className = "flex items-center justify-center gap-1.5 p-2 rounded-[2px] cursor-pointer transition-all text-xs font-bold text-[#1677B8] bg-white shadow-xs border border-blue-200";
            tabForm.className = "flex items-center justify-center gap-1.5 p-2 rounded-[2px] cursor-pointer transition-all text-xs font-bold text-slate-600 hover:text-slate-900 border border-transparent";
            
            if (fileControl) fileControl.setAttribute('required', 'required');
        }
    }

    async function startQrSessionFromCreate(forceNew = false) {
        // Jika sesi sudah pernah dibuat dan tidak dipaksa baru, cukup buka kembali modal tanpa menghapus data
        if (currentSessionToken && !forceNew) {
            document.getElementById('qrSessionModal').classList.remove('hidden');
            pollLiveAttendees();
            if (!livePollingTimer) {
                livePollingTimer = setInterval(pollLiveAttendees, 3000);
            }
            return;
        }

        const company = document.getElementById('form_company')?.value || 'pkm';
        const speaker = document.getElementById('form_speaker')?.value || '{{ Auth::user()?->full_name ?? Auth::user()?->username }}';
        const sessionDate = document.getElementById('socialization_date')?.value || '{{ date('Y-m-d') }}';
        const time = document.getElementById('form_time')?.value || '09:00 WIB - Selesai';
        const location = document.getElementById('form_location')?.value || 'Ruang Rapat / Lokasi Unit';

        const payload = {
            company: company,
            agenda: 'Sosialisasi {{ addslashes($document->title) }}',
            document_id: {{ $document->id }},
            doc_number: '{{ addslashes($document->doc_number) }}',
            session_date: sessionDate,
            session_time: time,
            location: location,
            speaker: speaker
        };

        try {
            const res = await fetch('{{ route('socializations.sessions.create') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.success) {
                currentSessionToken = data.token;
                activeSessionUrls = {
                    lan: data.lan_url || data.presensi_url,
                    local: data.local_url || (window.location.origin + '/presensi/' + data.token)
                };

                // Deteksi jika berjalan di Server Hosting / Domain Publik (Bukan localhost)
                const isHosted = (data.local_url === data.lan_url) || (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1');
                const switcherBox = document.getElementById('qrModeSwitcherBox');
                const hintBox = document.getElementById('qrModeHintBox');
                if (isHosted) {
                    if (switcherBox) switcherBox.innerHTML = '<span class="px-2.5 py-1 text-emerald-700 bg-emerald-50 rounded-[2px] border border-emerald-200 inline-flex items-center gap-1 font-bold text-[10.5px]"><i class="ph ph-globe text-emerald-600"></i> Server Production</span>';
                    if (hintBox) hintBox.classList.add('hidden');
                }

                // Ikat token ke form submission
                const hiddenInput = document.getElementById('form_attendance_session_token');
                if (hiddenInput) hiddenInput.value = data.token;
                
                // Tampilkan badge status aktif di form utama
                const badgeBox = document.getElementById('activeSessionBadgeBox');
                if (badgeBox) badgeBox.classList.remove('hidden');

                // Render High-Compatibility QR Code
                const container = document.getElementById('qrSvgContainer');
                container.innerHTML = '';
                qrCodeInstance = new QRCode(container, {
                    text: data.presensi_url,
                    width: 180,
                    height: 180,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });

                document.getElementById('qrDirectUrlInput').value = data.presensi_url;
                document.getElementById('btnOpenPresensiTab').href = data.presensi_url;
                document.getElementById('qrModalAgenda').textContent = data.session.agenda;
                document.getElementById('qrModalSpeaker').textContent = `Pemateri: ${data.session.speaker} • ${data.session.session_time}`;
                document.getElementById('btnDownloadSessionPdf').href = `/socializations/sessions/${data.token}/download-pdf`;

                // Reset table & counter
                document.getElementById('liveAttendeeCount').textContent = '0';
                document.getElementById('liveAttendeeTbody').innerHTML = `
                    <tr>
                        <td colspan="4" class="py-4 text-center text-slate-400 text-xs italic">
                            Belum ada peserta yang mengisi presensi. Tampilkan QR Code di atas.
                        </td>
                    </tr>
                `;

                // Buka Modal QR
                document.getElementById('qrSessionModal').classList.remove('hidden');

                // Mulai live polling setiap 3 detik
                if (livePollingTimer) clearInterval(livePollingTimer);
                livePollingTimer = setInterval(pollLiveAttendees, 3000);
            } else {
                alert('Gagal membuat sesi QR Presensi.');
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan saat membuat sesi QR.');
        }
    }

    function reRenderQrFromInput() {
        const url = document.getElementById('qrDirectUrlInput').value.trim();
        if (url && qrCodeInstance) {
            const container = document.getElementById('qrSvgContainer');
            container.innerHTML = '';
            qrCodeInstance = new QRCode(container, {
                text: url,
                width: 180,
                height: 180,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    }

    async function pollLiveAttendees() {
        if (!currentSessionToken) return;

        try {
            const res = await fetch(`/socializations/sessions/${currentSessionToken}/live`);
            const data = await res.json();

            if (data.success) {
                document.getElementById('liveAttendeeCount').textContent = data.count;
                
                // Update teks di form utama agar user melihat datanya tersimpan
                const scannedCountText = document.getElementById('formScannedCountText');
                if (scannedCountText) {
                    scannedCountText.textContent = `${data.count} Peserta telah mengisi presensi via QR`;
                }
                const mainStatusText = document.getElementById('qrSessionStatusText');
                if (mainStatusText && data.count > 0) {
                    mainStatusText.textContent = `🟢 ${data.count} Peserta telah tercatat hadir secara digital.`;
                }

                const tbody = document.getElementById('liveAttendeeTbody');

                if (data.participants && data.participants.length > 0) {
                    tbody.innerHTML = '';
                    data.participants.forEach((p, idx) => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-slate-50/80 transition-colors';
                        const timeStr = p.attended_at ? new Date(p.attended_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '-';
                        tr.innerHTML = `
                            <td class="py-2.5 px-3 text-center font-bold text-slate-600">${idx + 1}</td>
                            <td class="py-2.5 px-3 font-bold text-slate-900">${p.name}</td>
                            <td class="py-2.5 px-3 text-slate-600">${p.department || '-'}</td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-extrabold text-[10px]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Hadir (${timeStr})
                                </span>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            }
        } catch (err) {
            console.error('Error polling attendees:', err);
        }
    }

    function closeQrSessionModal() {
        if (livePollingTimer) {
            clearInterval(livePollingTimer);
            livePollingTimer = null;
        }
        document.getElementById('qrSessionModal').classList.add('hidden');

        // Pastikan token terikat ke form
        if (currentSessionToken) {
            const hiddenInput = document.getElementById('form_attendance_session_token');
            if (hiddenInput) hiddenInput.value = currentSessionToken;
            const badgeBox = document.getElementById('activeSessionBadgeBox');
            if (badgeBox) badgeBox.classList.remove('hidden');
        }
    }

    function copyPresensiLink() {
        const input = document.getElementById('qrDirectUrlInput');
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value);

        const btn = document.getElementById('btnCopyPresensiLink');
        const origText = btn.innerHTML;
        btn.innerHTML = '<i class="ph ph-check text-sm"></i> Tersalin!';
        setTimeout(() => {
            btn.innerHTML = origText;
        }, 2000);
    }
</script>
@endsection
