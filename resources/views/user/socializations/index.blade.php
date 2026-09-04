@extends(Auth::user()?->role === 'admin' ? 'layouts.admin' : 'layouts.reviewer')

@section('title', 'Sosialisasi SOP')
@section('header_title', 'Sosialisasi Dokumen SOP')

@section('content')
<div class="space-y-6">

    <!-- TOP BANNER -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-[2px] p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ Auth::user()?->role === 'admin' ? route('admin.dashboard') : route('reviewer.dashboard') }}" variant="light" text="Kembali" />
            <span class="text-white/30">|</span>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold">
                <a href="{{ Auth::user()?->role === 'admin' ? route('admin.dashboard') : route('reviewer.dashboard') }}" class="hover:text-[#ffe16e] font-medium">Dashboard</a>
                <span>/</span>
                <span class="font-bold text-white">Sosialisasi SOP</span>
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-white">Sosialisasi Dokumen SOP</h2>
                <p class="text-xs text-white/85 mt-0.5 font-medium">Unggah berkas daftar hadir dan foto dokumentasi kegiatan sosialisasi SOP di unit/departemen Anda.</p>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0 self-start md:self-auto">
                <x-interactive-button text="Cetak Daftar Hadir" variant="outline" icon="ph ph-printer text-base" type="button" onclick="openAttendanceModal()" />
                @if($availableDocuments->count() === 1)
                    <x-interactive-button text="Unggah Bukti Sosialisasi" variant="primary" icon="ph ph-plus-circle text-base" href="{{ route('documents.socialize', $availableDocuments->first()->id) }}" />
                @else
                    <x-interactive-button text="Unggah Bukti Sosialisasi" variant="primary" icon="ph ph-plus-circle text-base" type="button" onclick="openNewSocializationModal()" />
                @endif
            </div>
        </div>
    </div>

    <!-- STATS BENTO CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-[2px] border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Total Bukti Diunggah</span>
                <h3 class="text-2xl font-black text-slate-800">{{ $totalUploaded }} <span class="text-xs font-semibold text-slate-500">Kegiatan</span></h3>
            </div>
            <div class="w-10 h-10 rounded-[2px] bg-blue-50 text-[#1677B8] border border-blue-200 flex items-center justify-center font-bold">
                <i class="ph ph-users-three text-xl"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-[2px] border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Menunggu Verifikasi Admin</span>
                <h3 class="text-2xl font-black text-amber-700">{{ $pendingCount }} <span class="text-xs font-semibold text-slate-500">Dalam Antrean</span></h3>
            </div>
            <div class="w-10 h-10 rounded-[2px] bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center font-bold">
                <i class="ph ph-clock text-xl"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-[2px] border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Terverifikasi & Sah</span>
                <h3 class="text-2xl font-black text-emerald-700">{{ $verifiedCount }} <span class="text-xs font-semibold text-slate-500">Selesai</span></h3>
            </div>
            <div class="w-10 h-10 rounded-[2px] bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold">
                <i class="ph ph-seal-check text-xl"></i>
            </div>
        </div>
    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="bg-white rounded-[2px] p-5 shadow-sm border border-sand-200/60">
        <form method="GET" action="{{ route('user.socializations.index') }}" class="flex items-center gap-3">
            <div class="relative flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari berdasarkan judul SOP, nomor dokumen, atau catatan sosialisasi..." 
                       class="w-full h-[38px] text-xs pl-9 pr-8 rounded-[2px] border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-medium text-slate-800">
                <button type="submit" class="absolute left-1 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center text-slate-400 hover:text-[#1677B8] border-none bg-transparent cursor-pointer">
                    <i class="ph ph-magnifying-glass text-base"></i>
                </button>
                @if(request('search'))
                    <a href="{{ route('user.socializations.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-600 text-xs" title="Reset pencarian">
                        <i class="ph ph-x"></i>
                    </a>
                @endif
            </div>

            <x-interactive-button text="Cari" variant="blue" icon="ph ph-magnifying-glass text-sm" />
        </form>
    </div>

    @if($availableDocuments->count() > 0)
    <!-- DAFTAR DOKUMEN AKTIF YANG PERLU DISOSIALISASIKAN -->
    <div class="bg-white rounded-[2px] p-6 shadow-sm border border-sand-200/60 space-y-4">
        <div class="flex items-center justify-between border-b border-sand-200/60 pb-3">
            <div class="flex items-center space-x-2 text-[#1677B8]">
                <i class="ph ph-bell-ringing text-xl animate-bounce"></i>
                <div>
                    <h3 class="font-extrabold text-sm text-slate-800">Dokumen SOP Aktif (Siap Disosialisasikan)</h3>
                    <p class="text-[11px] text-slate-500">Pilih dokumen SOP di unit Anda untuk masuk langsung ke formulir pengunggahan bukti sosialisasi.</p>
                </div>
            </div>
            <span class="px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-[2px] font-bold text-xs">
                {{ $availableDocuments->count() }} SOP Tersedia
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($availableDocuments as $adoc)
                <div class="p-4 rounded-[2px] border border-slate-200 hover:border-[#1677B8] bg-slate-50/50 hover:bg-blue-50/20 transition-all flex flex-col justify-between gap-3 group">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 bg-white border border-slate-200 text-slate-700 rounded-[2px] font-bold text-[10px] uppercase">
                                {{ $adoc->department }}
                            </span>
                            <span class="text-[10px] text-slate-500 font-semibold">
                                Rev. {{ $adoc->doc_revision ?? '0' }}
                            </span>
                        </div>
                        <h4 class="font-extrabold text-xs text-slate-800 group-hover:text-[#1677B8] transition-colors line-clamp-2" title="{{ $adoc->title }}">
                            {{ $adoc->title }}
                        </h4>
                        @if($adoc->doc_number)
                            <p class="font-mono text-[10.5px] text-slate-500 font-semibold">{{ $adoc->doc_number }}</p>
                        @endif
                    </div>

                    <div class="pt-2 border-t border-slate-200/80 flex items-center gap-2">
                        <x-interactive-button text="Unggah Bukti Sosialisasi" variant="blue" icon="ph ph-upload-simple text-sm" href="{{ route('documents.socialize', $adoc->id) }}" class="w-full justify-center" />
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- TABEL RIWAYAT SOSIALISASI -->
    <div class="bg-white rounded-[2px] p-6 shadow-sm border border-sand-200/60 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3 rounded-[2px]">
            <div class="flex items-center space-x-2 text-xs font-bold text-[#1677B8]">
                <i class="ph ph-table text-base"></i>
                <span class="capitalize text-slate-900 font-extrabold">Daftar Pelaksanaan Sosialisasi SOP</span>
            </div>
            <span class="text-[11px] text-[#1677B8] font-bold bg-white px-2.5 py-1.5 rounded-[2px] border border-blue-200 whitespace-nowrap shadow-2xs">
                Total {{ $socializations->total() }} Data
            </span>
        </div>

        <div class="overflow-x-auto rounded-[2px] border border-slate-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[10.5px] font-extrabold text-slate-700 uppercase tracking-wider">
                        <th class="py-2.5 px-2 w-8 text-center whitespace-nowrap">No</th>
                        <th class="py-2.5 px-2.5 whitespace-nowrap">Dokumen SOP</th>
                        <th class="py-2.5 px-2.5 whitespace-nowrap w-36">Tgl Pelaksanaan</th>
                        <th class="py-2.5 px-2.5 text-center whitespace-nowrap w-32">Berkas Bukti</th>
                        <th class="py-2.5 px-2.5 text-center whitespace-nowrap w-32">Status Verifikasi</th>
                        <th class="py-2.5 px-2 text-center whitespace-nowrap w-16 no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse($socializations as $idx => $soc)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-3 text-center text-slate-500 font-bold">
                            {{ $socializations->firstItem() + $idx }}
                        </td>
                        <td class="py-3 px-4">
                            @if($soc->document)
                                <a href="{{ route('reviewer.show', $soc->document_id) }}" class="font-extrabold text-slate-900 hover:text-[#1677B8] hover:underline transition-colors block">{{ $soc->document->title }}</a>
                            @else
                                <div class="font-extrabold text-slate-900">-</div>
                            @endif
                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                <span class="font-mono text-[#1677B8] font-bold">{{ $soc->document->doc_number ?? 'SOP' }}</span>
                                <span>&bull;</span>
                                <span class="px-1.5 py-0.2 bg-slate-100 rounded-[2px] text-[10px] uppercase font-bold text-slate-700">{{ $soc->document->department ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="font-bold text-slate-800">{{ $soc->socialization_date ? $soc->socialization_date->format('d M Y') : '-' }}</div>
                            <div class="text-[10.5px] text-slate-400 font-normal">Diunggah: {{ $soc->created_at ? $soc->created_at->format('d/m/Y H:i') : '-' }}</div>
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                @if($soc->attendance_file)
                                    <a href="{{ asset('storage/' . $soc->attendance_file) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 bg-sky-50 text-[#1677B8] border border-[#1677B8]/30 rounded-[2px] text-[10.5px] font-bold hover:bg-[#1677B8] hover:text-white transition-all" title="Buka Daftar Hadir">
                                        <i class="ph ph-file-pdf text-sm"></i>
                                        <span>Daftar Hadir</span>
                                    </a>
                                @endif
                                @if(!empty($soc->photos) && count($soc->photos) > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-amber-50 text-amber-800 border border-amber-300 rounded-[2px] text-[10.5px] font-bold">
                                        <i class="ph ph-image text-sm"></i>
                                        <span>{{ count($soc->photos) }} Foto</span>
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($soc->status === 'verified')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-[2px] text-[10.5px] font-extrabold uppercase">
                                    <i class="ph ph-seal-check text-xs"></i>
                                    <span>Terverifikasi</span>
                                </span>
                            @elseif($soc->status === 'rejected')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-[2px] text-[10.5px] font-extrabold uppercase">
                                    <i class="ph ph-x-circle text-xs"></i>
                                    <span>Perlu Revisi</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-[2px] text-[10.5px] font-extrabold uppercase">
                                    <i class="ph ph-clock text-xs"></i>
                                    <span>Menunggu Verifikasi</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <x-interactive-button text="Lihat Detail" variant="outline" icon="ph ph-eye text-xs" type="button" onclick="showSocializationDetail({{ $soc->id }}, '{{ addslashes($soc->document->title ?? '') }}', '{{ $soc->socialization_date ? $soc->socialization_date->format('d F Y') : '-' }}', '{{ addslashes($soc->notes ?? '-') }}', '{{ $soc->attendance_file ? asset('storage/' . $soc->attendance_file) : '' }}', {{ json_encode(collect($soc->photos ?? [])->map(fn($p) => asset('storage/' . $p))->toArray()) }})" />
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <i class="ph ph-users-three text-4xl text-slate-300"></i>
                                <h5 class="text-xs font-bold text-slate-700">Belum Ada Bukti Sosialisasi</h5>
                                <p class="text-[11px] text-slate-500 max-w-sm">
                                    Klik tombol kuning di atas untuk mengunggah berkas daftar hadir dan foto sosialisasi SOP bidang Anda.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($socializations->hasPages())
        <div class="pt-3 border-t border-slate-200">
            {{ $socializations->links() }}
        </div>
        @endif
    </div>

</div>



<!-- MODAL DETAIL SOSIALISASI -->
<div id="detailSocializationModal" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-xl w-full max-w-2xl shadow-2xl border border-slate-200 overflow-hidden my-auto animate-in fade-in zoom-in duration-200 max-h-[90vh] flex flex-col">
        <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white p-5 flex items-center justify-between flex-shrink-0">
            <h3 class="text-sm font-black" id="modalDetailTitle">Detail Bukti Sosialisasi</h3>
            <button type="button" onclick="closeDetailSocializationModal()" class="text-white/80 hover:text-white text-lg cursor-pointer border-none bg-transparent">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <div class="p-6 space-y-4 overflow-y-auto custom-scrollbar flex-1 text-xs">
            <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-[2px] border border-slate-200">
                <div>
                    <span class="text-[10px] text-slate-500 uppercase font-bold block">Tanggal Sosialisasi</span>
                    <strong class="text-slate-800 text-xs" id="modalDetailDate">-</strong>
                </div>
                <div>
                    <span class="text-[10px] text-slate-500 uppercase font-bold block">Berkas Daftar Hadir</span>
                    <a id="modalDetailAttLink" href="#" target="_blank" class="inline-flex items-center gap-1 text-[#1677B8] font-bold hover:underline">
                        <i class="ph ph-file-pdf"></i>
                        <span>Buka Lembar Kehadiran</span>
                    </a>
                </div>
            </div>

            <div>
                <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Catatan Kegiatan</span>
                <p class="p-3 bg-white rounded-[2px] border border-slate-200 text-slate-700 font-medium whitespace-pre-line leading-relaxed" id="modalDetailNotes">-</p>
            </div>

            <div>
                <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-2">Galeri Foto Dokumentasi</span>
                <div id="modalDetailPhotos" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    <!-- Dynamic images injected via JS -->
                </div>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end">
            <button type="button" onclick="closeDetailSocializationModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs rounded-[2px] transition-all cursor-pointer border-none">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- MODAL UNGGAH BUKTI SOSIALISASI BARU -->
<div id="newSocializationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden p-4 overflow-y-auto">
    <div class="bg-white rounded-[2px] shadow-xl w-full max-w-lg overflow-hidden border border-slate-200 flex flex-col max-h-[90vh] my-auto animate-in fade-in duration-200">
        <!-- HEADER -->
        <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white p-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center space-x-2">
                <i class="ph ph-upload-simple text-xl"></i>
                <h3 class="font-bold text-sm">Unggah Bukti Pelaksanaan Sosialisasi</h3>
            </div>
            <button type="button" onclick="closeNewSocializationModal()" class="text-white/80 hover:text-white text-lg cursor-pointer border-none bg-transparent">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <!-- FORM -->
        <form id="newSocializationForm" method="POST" action="" enctype="multipart/form-data" class="p-5 space-y-4 overflow-y-auto custom-scrollbar flex-1 text-xs">
            @csrf
            
            <input type="hidden" name="attendance_session_token" id="modal_attendance_session_token" value="">

            <!-- PILIH DOKUMEN SOP -->
            <div>
                <label for="soc_doc_id" class="block text-slate-700 font-bold mb-1">Pilih Dokumen SOP Aktif <span class="text-rose-500">*</span></label>
                <div class="flex items-center gap-2">
                    <select id="soc_doc_id" name="document_id" required onchange="updateSocFormAction(this.value)" class="flex-1 text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-semibold text-slate-800">
                        <option value="">-- Pilih SOP yang Telah Disosialisasikan --</option>
                        @foreach($availableDocuments as $adoc)
                            <option value="{{ $adoc->id }}" {{ (string)request('upload_doc') === (string)$adoc->id ? 'selected' : '' }}>
                                [{{ $adoc->department }}] {{ $adoc->title }} (Rev. {{ $adoc->doc_revision ?? '0' }})
                            </option>
                        @endforeach
                    </select>
                    <button type="button" onclick="goToDedicatedSocializePage()" id="btnGoToDedicatedPage" class="hidden px-3 py-2 bg-[#1677B8] hover:bg-[#1260a0] text-white font-bold text-xs rounded-[2px] transition-all whitespace-nowrap cursor-pointer border-none" title="Buka Halaman Formulir Penuh">
                        <span>Buka Form &rarr;</span>
                    </button>
                </div>
            </div>

            <!-- TANGGAL SOSIALISASI -->
            <div>
                <label for="soc_date" class="block text-slate-700 font-bold mb-1">Tanggal Pelaksanaan Kegiatan <span class="text-rose-500">*</span></label>
                <input type="date" id="soc_date" name="socialization_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-medium text-slate-800">
            </div>

            <!-- PILIHAN METODE DAFTAR HADIR -->
            <div class="space-y-2">
                <label class="block text-slate-700 font-bold mb-1">Berkas Lembar Daftar Hadir Peserta <span class="text-rose-500">*</span></label>
                
                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-[2px] border border-slate-200 text-xs">
                    <label id="modalTabOptForm" class="flex items-center justify-center gap-1.5 p-2 rounded-[2px] cursor-pointer transition-all font-bold text-[#1677B8] bg-white shadow-xs border border-blue-200">
                        <input type="radio" name="attendance_method" value="form" class="sr-only" onchange="setModalAttendanceMethod('form')" checked>
                        <i class="ph ph-note-pencil text-sm"></i>
                        <span>Isi Form Daftar Hadir</span>
                    </label>
                    <label id="modalTabOptUpload" class="flex items-center justify-center gap-1.5 p-2 rounded-[2px] cursor-pointer transition-all font-bold text-slate-600 hover:text-slate-900 border border-transparent">
                        <input type="radio" name="attendance_method" value="upload" class="sr-only" onchange="setModalAttendanceMethod('upload')">
                        <i class="ph ph-upload-simple text-sm"></i>
                        <span>Upload Berkas / Foto</span>
                    </label>
                </div>

                <!-- FORM KOP & QR PRESENSI -->
                <div id="modalSecAttendanceForm" class="p-3.5 bg-blue-50/40 rounded-[2px] border border-blue-200 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-slate-700 font-bold text-[10.5px] mb-0.5">Kop Surat / Entitas PT <span class="text-rose-500">*</span></label>
                            <select id="modal_company" name="company" class="w-full text-xs p-2 rounded-[2px] border border-slate-300 focus:ring-1 focus:ring-[#1677B8] bg-white font-semibold text-slate-800">
                                <option value="pkm" selected>PT PUTRA KELANA MAKMUR (PKM) GROUP</option>
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
                            <label class="block text-slate-700 font-bold text-[10.5px] mb-0.5">Pemateri / PIC Kegiatan</label>
                            <input type="text" id="modal_speaker" name="speaker" value="{{ Auth::user()?->full_name ?? Auth::user()?->username }}" placeholder="Nama Pemateri..." class="w-full text-xs p-2 rounded-[2px] border border-slate-300 focus:ring-1 focus:ring-[#1677B8] font-semibold text-slate-800 bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                        <div>
                            <label class="block text-slate-700 font-bold text-[10.5px] mb-0.5">Waktu / Jam</label>
                            <input type="text" id="modal_time" name="time" value="09:00 WIB - Selesai" class="w-full text-xs p-2 rounded-[2px] border border-slate-300 font-medium text-slate-800 bg-white">
                        </div>
                        <div>
                            <label class="block text-slate-700 font-bold text-[10.5px] mb-0.5">Lokasi / Tempat</label>
                            <input type="text" id="modal_location" name="location" value="Ruang Rapat / Unit Kerja" class="w-full text-xs p-2 rounded-[2px] border border-slate-300 font-medium text-slate-800 bg-white">
                        </div>
                    </div>

                    <!-- ACTION BUTTONS: BUAT QR PRESENSI -->
                    <div class="p-3 bg-white rounded-[2px] border border-blue-200/80 flex flex-col sm:flex-row items-center justify-between gap-2 shadow-2xs">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-[2px] bg-sky-100 text-[#1677B8] flex items-center justify-center font-bold flex-shrink-0">
                                <i class="ph ph-qr-code text-lg"></i>
                            </div>
                            <div>
                                <p class="font-extrabold text-xs text-slate-900 leading-tight">Presensi Mandiri via Scan QR</p>
                                <p class="text-[10px] text-slate-500" id="modalQrSessionStatusText">Peserta mengisi nama & jabatan saat hadir di ruangan</p>
                            </div>
                        </div>

                        <x-interactive-button text="Buka Layar QR Presensi" variant="blue" icon="ph ph-broadcast text-sm" type="button" onclick="startQrSessionFromModal()" />
                    </div>

                    <!-- BADGE STATUS SESI PRESENSI AKTIF DI MODAL FORM -->
                    <div id="modalActiveSessionBadgeBox" class="hidden p-3 bg-emerald-50 border border-emerald-200 rounded-[2px] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                            <div>
                                <p class="text-xs font-black text-emerald-900">Sesi QR Presensi Tersimpan & Aktif</p>
                                <p class="text-[10.5px] text-emerald-700 font-semibold" id="modalFormScannedCountText">0 Peserta telah mengisi presensi</p>
                            </div>
                        </div>
                        <button type="button" onclick="startQrSessionFromModal(false)" class="text-xs font-bold text-emerald-800 hover:text-emerald-900 underline cursor-pointer bg-transparent border-none">
                            Lihat / Buka QR &rarr;
                        </button>
                    </div>
                </div>

                <!-- UPLOAD FILE -->
                <div id="modalSecAttendanceUpload" class="hidden p-3 bg-slate-50 rounded-[2px] border border-slate-200 space-y-1.5">
                    <x-file-input name="attendance_file" accept=".pdf,.jpg,.jpeg,.png" label="Pilih berkas daftar hadir fisik" hint="PDF, JPG, PNG bertanda tangan (Maks. 10 MB)" :required="false" :maxSize="10" />
                </div>
            </div>

            <!-- UNGGAH FOTO DOKUMENTASI (OPSIONAL) -->
            <div>
                <label class="block text-slate-700 font-bold mb-1">Foto-Foto Dokumentasi Kegiatan (Opsional)</label>
                <x-file-input name="photos[]" accept=".jpg,.jpeg,.png" label="Pilih foto dokumentasi" hint="Pilih 1 sampai 10 foto (Opsional, JPG, PNG, Maks. 10 MB/foto)" :multiple="true" :required="false" :maxSize="10" />
            </div>

            <!-- NOTULEN / CATATAN KEGIATAN -->
            <div>
                <label for="soc_notes" class="block text-slate-700 font-bold mb-1">Notulen / Catatan Pelaksanaan (Opsional)</label>
                <textarea id="soc_notes" name="notes" rows="3" placeholder="Tuliskan ringkasan jalannya sosialisasi, daftar pertanyaan tim pelaksana, atau catatan evaluasi lapangan..." class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-medium text-slate-800"></textarea>
            </div>

            <!-- FOOTER ACTIONS -->
            <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                <x-interactive-button text="Batal" variant="outline" type="button" onclick="closeNewSocializationModal()" />
                <x-interactive-button text="Konfirmasi" variant="blue" icon="ph ph-check-circle text-sm" type="submit" />
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: LAYAR INTERAKTIF QR PRESENSI SOSIALISASI & LIVE PESERTA -->
<!-- ========================================================================= -->
<div id="qrSessionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/75 backdrop-blur-xs hidden p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden border border-slate-200 flex flex-col max-h-[92vh] my-auto animate-in fade-in zoom-in duration-200">
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
                        <h4 id="qrModalAgenda" class="text-sm font-black text-slate-900 leading-snug"></h4>
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

<!-- MODAL GENERATOR DAFTAR HADIR BS FORM 9 -->
<div id="attendanceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden p-4 overflow-y-auto">
    <div class="bg-white rounded-[2px] shadow-xl w-full max-w-xl overflow-hidden border border-slate-200 flex flex-col max-h-[92vh] my-auto animate-in fade-in duration-200">
        
        <!-- MODAL HEADER -->
        <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white p-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center space-x-2">
                <i class="ph ph-printer text-xl"></i>
                <div>
                    <h3 class="font-extrabold text-sm">Generator Formulir Daftar Hadir (BS Form 9)</h3>
                    <p class="text-[11px] text-white/80">Cetak lembar daftar hadir resmi berstempel status hadir.</p>
                </div>
            </div>
            <button type="button" onclick="closeAttendanceModal()" class="text-white/80 hover:text-white text-lg cursor-pointer border-none bg-transparent">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <!-- MODAL BODY -->
        <form method="POST" action="{{ route('socializations.attendance_sheet.generate') }}" target="_blank" class="p-5 space-y-4 overflow-y-auto custom-scrollbar flex-1 text-xs">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <!-- 1. PILIH ENTITAS / PT HEADER -->
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Pilih Entitas Perusahaan (Kop Surat) <span class="text-rose-500">*</span></label>
                    <select name="company" required class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-bold text-slate-800">
                        <option value="pkm" selected>PT PUTRA KELANA MAKMUR (PKM) GROUP</option>
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

                <!-- 2. PEMATERI / PIC -->
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Nama Pemateri / PIC Kegiatan</label>
                    <input type="text" name="speaker" value="{{ Auth::user()?->full_name ?? Auth::user()?->username }}" placeholder="Nama Pemateri..." class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-semibold text-slate-800">
                </div>
            </div>

            <!-- 3. AGENDA & PILIH SOP -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-slate-700 font-bold mb-1">Agenda / Judul Sosialisasi <span class="text-rose-500">*</span></label>
                    <input type="text" id="att_agenda" name="agenda" value="Sosialisasi Standar Operasional Prosedur (SOP)" required class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-bold text-slate-800">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Nomor SOP (Opsional)</label>
                    <input type="text" id="att_doc_number" name="doc_number" placeholder="No. Dokumen..." class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-mono text-slate-800">
                </div>
            </div>

            <!-- 4. TANGGAL, WAKTU & TEMPAT -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal Kegiatan <span class="text-rose-500">*</span></label>
                    <input type="text" name="date" value="{{ date('d F Y') }}" required class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-medium text-slate-800">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Waktu / Jam</label>
                    <input type="text" name="time" value="09:00 WIB - Selesai" class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-medium text-slate-800">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tempat / Lokasi</label>
                    <input type="text" name="location" value="Ruang Rapat / Lokasi Unit" class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-medium text-slate-800">
                </div>
            </div>

            <!-- FOOTER ACTIONS -->
            <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                <x-interactive-button text="Tutup" variant="outline" type="button" onclick="closeAttendanceModal()" />
                <x-interactive-button text="Cetak Lembar Daftar Hadir (PDF)" variant="blue" icon="ph ph-printer text-base" type="submit" />
            </div>
        </form>
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

    async function startQrSessionFromModal(forceNew = false) {
        // Jika sesi sudah pernah dibuat dan tidak dipaksa baru, cukup buka kembali modal tanpa menghapus data
        if (currentSessionToken && !forceNew) {
            document.getElementById('qrSessionModal').classList.remove('hidden');
            pollLiveAttendees();
            if (!livePollingTimer) {
                livePollingTimer = setInterval(pollLiveAttendees, 3000);
            }
            return;
        }

        const selectDoc = document.getElementById('soc_doc_id');
        const docId = selectDoc ? selectDoc.value : null;
        const selectedOption = selectDoc ? selectDoc.options[selectDoc.selectedIndex] : null;
        const docTitle = selectedOption ? selectedOption.text : 'Sosialisasi Standar Operasional Prosedur';
        
        const company = document.getElementById('modal_company')?.value || 'pkm';
        const speaker = document.getElementById('modal_speaker')?.value || '{{ Auth::user()?->full_name ?? Auth::user()?->username }}';
        const sessionDate = document.getElementById('soc_date')?.value || '{{ date('Y-m-d') }}';
        const time = document.getElementById('modal_time')?.value || '09:00 WIB - Selesai';
        const location = document.getElementById('modal_location')?.value || 'Ruang Rapat / Unit Kerja';

        const payload = {
            company: company,
            agenda: docTitle.replace(/\s+/g, ' ').trim(),
            document_id: docId || null,
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

                // Ikat token ke form submission modal
                const hiddenInput = document.getElementById('modal_attendance_session_token');
                if (hiddenInput) hiddenInput.value = data.token;
                
                // Tampilkan badge status aktif di modal form utama
                const badgeBox = document.getElementById('modalActiveSessionBadgeBox');
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
                const qrModal = document.getElementById('qrSessionModal');
                if (qrModal) {
                    if (qrModal.parentElement !== document.body) {
                        document.body.appendChild(qrModal);
                    }
                    qrModal.classList.remove('hidden');
                }

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
                
                // Update teks di form agar terlihat tersimpan
                const scannedCountText = document.getElementById('modalFormScannedCountText');
                if (scannedCountText) {
                    scannedCountText.textContent = `${data.count} Peserta telah mengisi presensi via QR`;
                }
                const mainStatusText = document.getElementById('modalQrSessionStatusText');
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

        // Pastikan token terikat ke form modal
        if (currentSessionToken) {
            const hiddenInput = document.getElementById('modal_attendance_session_token');
            if (hiddenInput) hiddenInput.value = currentSessionToken;
            const badgeBox = document.getElementById('modalActiveSessionBadgeBox');
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

    function removeCustomRow(btn) {
        const row = btn.closest('.custom-row');
        if (row) {
            row.remove();
        }
    }

    function goToDedicatedSocializePage() {
        const selectDoc = document.getElementById('soc_doc_id');
        if (selectDoc && selectDoc.value) {
            window.location.href = `/documents/${selectDoc.value}/socialize`;
        }
    }

    function updateSocFormAction(docId) {
        const form = document.getElementById('newSocializationForm');
        const btnGo = document.getElementById('btnGoToDedicatedPage');
        if (docId) {
            form.action = `/documents/${docId}/socialization`;
            if (btnGo) btnGo.classList.remove('hidden');
        } else {
            form.action = '';
            if (btnGo) btnGo.classList.add('hidden');
        }
    }

    function openNewSocializationModal(preSelectedDocId = null) {
        const modal = document.getElementById('newSocializationModal');
        const selectDoc = document.getElementById('soc_doc_id');
        
        if (preSelectedDocId && selectDoc) {
            selectDoc.value = preSelectedDocId;
            updateSocFormAction(preSelectedDocId);
        } else if (selectDoc && selectDoc.value) {
            updateSocFormAction(selectDoc.value);
        }

        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
        }
    }

    function closeNewSocializationModal() {
        const modal = document.getElementById('newSocializationModal');
        if (modal) modal.classList.add('hidden');
    }

    function openAttendanceModal() {
        const modal = document.getElementById('attendanceModal');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
        }
    }

    function closeAttendanceModal() {
        const modal = document.getElementById('attendanceModal');
        if (modal) modal.classList.add('hidden');
    }

    function showSocializationDetail(id, title, date, notes, attUrl, photos) {
        document.getElementById('modalDetailTitle').textContent = 'Bukti Sosialisasi: ' + title;
        document.getElementById('modalDetailDate').textContent = date;
        document.getElementById('modalDetailNotes').textContent = notes;
        
        const attLink = document.getElementById('modalDetailAttLink');
        if (attUrl) {
            attLink.href = attUrl;
            attLink.classList.remove('hidden');
        } else {
            attLink.classList.add('hidden');
        }

        const photoContainer = document.getElementById('modalDetailPhotos');
        photoContainer.innerHTML = '';
        if (photos && photos.length > 0) {
            photos.forEach(imgUrl => {
                const a = document.createElement('a');
                a.href = imgUrl;
                a.target = '_blank';
                a.className = 'block rounded-[2px] overflow-hidden border border-slate-200 hover:opacity-90 shadow-2xs group relative aspect-video bg-slate-100';
                a.innerHTML = `<img src="${imgUrl}" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-200" alt="Dokumentasi">`;
                photoContainer.appendChild(a);
            });
        } else {
            photoContainer.innerHTML = '<p class="col-span-3 text-slate-400 text-xs italic">Tidak ada foto dokumentasi yang dilampirkan.</p>';
        }

        const modal = document.getElementById('detailSocializationModal');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
        }
    }

    function closeDetailSocializationModal() {
        const modal = document.getElementById('detailSocializationModal');
        if (modal) modal.classList.add('hidden');
    }

    // Auto-open modal jika URL membawa parameter upload_doc
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const uploadDocId = urlParams.get('upload_doc') || urlParams.get('doc_id');
        if (uploadDocId) {
            openNewSocializationModal(uploadDocId);
        }
    });
</script>
@endsection
