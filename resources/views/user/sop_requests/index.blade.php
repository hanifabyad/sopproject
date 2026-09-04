@extends(Auth::user()?->role === 'admin' ? 'layouts.admin' : 'layouts.reviewer')

@section('title', 'Pengajuan SOP Baru')
@section('header_title', 'Pengajuan Pembuatan SOP Baru')

@section('content')
<div class="space-y-6">

    <!-- TOP BANNER -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ Auth::user()?->role === 'admin' ? route('admin.dashboard') : route('reviewer.dashboard') }}" variant="light" text="Kembali" />
            <span class="text-white/30">|</span>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold">
                <a href="{{ Auth::user()?->role === 'admin' ? route('admin.dashboard') : route('reviewer.dashboard') }}" class="hover:text-[#ffe16e] font-medium">Dashboard</a>
                <span>/</span>
                <span class="font-bold text-white">Pengajuan SOP Baru</span>
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-white">Pengajuan Pembuatan SOP Baru</h2>
                <p class="text-xs text-white/85 mt-0.5 font-medium">Usulkan pembuatan Standar Operasional Prosedur baru untuk unit kerja Anda. Lacak status dan linimasa progres verifikasi secara real-time.</p>
            </div>

            <a href="#formPengajuan" class="h-10 px-5 bg-white text-[#1677B8] hover:bg-slate-50 font-black text-xs rounded-[2px] shadow-sm transition-all flex items-center gap-2 cursor-pointer border-none flex-shrink-0 self-start md:self-auto">
                <i class="ph ph-plus-circle text-base"></i>
                <span>Buat Usulan SOP Baru</span>
            </a>
        </div>
    </div>

    <!-- STATS BENTO CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <div class="bg-white p-4 rounded-[2px] border border-sand-200/60 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Total Pengajuan</span>
                <h3 class="text-xl font-black text-slate-800">{{ $totalCount }}</h3>
            </div>
            <div class="w-8 h-8 rounded-[2px] bg-blue-50 text-[#1677B8] border border-blue-200 flex items-center justify-center font-bold">
                <i class="ph ph-file-plus text-lg"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-[2px] border border-sand-200/60 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Menunggu Review</span>
                <h3 class="text-xl font-black text-amber-700">{{ $pendingCount }}</h3>
            </div>
            <div class="w-8 h-8 rounded-[2px] bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center font-bold">
                <i class="ph ph-clock text-lg"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-[2px] border border-sand-200/60 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Di Proses QMS</span>
                <h3 class="text-xl font-black text-[#1677B8]">{{ $inProgressCount }}</h3>
            </div>
            <div class="w-8 h-8 rounded-[2px] bg-blue-50 text-[#1677B8] border border-blue-200 flex items-center justify-center font-bold">
                <i class="ph ph-arrows-clockwise text-lg"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-[2px] border border-sand-200/60 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Perlu Revisi</span>
                <h3 class="text-xl font-black text-orange-700">{{ $revisionCount }}</h3>
            </div>
            <div class="w-8 h-8 rounded-[2px] bg-orange-50 text-orange-700 border border-orange-200 flex items-center justify-center font-bold">
                <i class="ph ph-warning-circle text-lg"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-[2px] border border-sand-200/60 shadow-xs flex items-center justify-between col-span-2 sm:col-span-1">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Selesai / Terbit</span>
                <h3 class="text-xl font-black text-emerald-700">{{ $approvedCount }}</h3>
            </div>
            <div class="w-8 h-8 rounded-[2px] bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold">
                <i class="ph ph-check-circle text-lg"></i>
            </div>
        </div>
    </div>

    <!-- FORMULIR PENGAJUAN SOP BARU -->
    <div id="formPengajuan" class="bg-white rounded-lg shadow-sm border border-sand-200/80 p-6 space-y-4">
        <div class="border-b border-sand-200/60 pb-3 flex items-center justify-between">
            <div class="flex items-center space-x-2 text-[#1677B8]">
                <i class="ph ph-pencil-simple-line text-xl"></i>
                <h3 class="font-extrabold text-sm text-slate-800">Formulir Usulan Standar Operasional Prosedur Baru</h3>
            </div>
            <span class="text-[11px] text-slate-400 font-medium">* Wajib diisi</span>
        </div>

        @if ($errors->any())
            <div class="p-3.5 bg-rose-50 border-l-4 border-rose-500 text-rose-800 font-bold text-xs rounded-r space-y-1">
                @foreach ($errors->all() as $error)
                    <p class="flex items-center gap-1.5"><i class="ph ph-warning-circle text-sm"></i> {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('user.sop_requests.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- 1. NAMA SOP -->
                <div>
                    <label for="title" class="block text-slate-700 font-bold mb-1">
                        1. Nama / Judul Usulan SOP <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}" 
                           required 
                           placeholder="Contoh: SOP PENGELOLAAN LIMBAH B3 UNIT BISNIS" 
                           class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-bold text-slate-800 uppercase">
                </div>

                <!-- 2. DIVISI / DEPARTEMEN / UNIT BISNIS -->
                <div>
                    <label for="department" class="block text-slate-700 font-bold mb-1">
                        2. Divisi / Departemen / Unit Bisnis (BU) Pemohon <span class="text-rose-500">*</span>
                    </label>
                    <select id="department" 
                            name="department" 
                            required 
                            class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-semibold text-slate-800">
                        <option value="">-- Pilih Unit Bisnis (BU) atau Departemen Support --</option>
                        @foreach($departmentGroups as $groupLabel => $depts)
                            <optgroup label="{{ $groupLabel }}">
                                @foreach($depts as $deptCode => $deptName)
                                    <option value="{{ $deptCode }}" {{ old('department') === $deptCode ? 'selected' : '' }}>
                                        {{ $deptName }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 3. DESKRIPSI & TUJUAN PEMBUATAN -->
            <div>
                <label for="description" class="block text-slate-700 font-bold mb-1">
                    3. Deskripsi, Latar Belakang & Alur Garis Besar Proses Bisnis <span class="text-rose-500">*</span>
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4" 
                          required 
                          placeholder="Jelaskan secara rinci:&#10;1. Mengapa SOP ini dibutuhkan?&#10;2. Siapa saja pihak/bagian yang akan terlibat dalam alur prosedur ini?&#10;3. Ringkasan langkah operasional atau poin-poin utama yang ingin dibakukan..." 
                          class="w-full text-xs p-3 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-medium text-slate-800 leading-relaxed">{{ old('description') }}</textarea>
            </div>

            <!-- 4. BERKAS DRAF / LAMPIRAN PENDUKUNG (OPSIONAL) -->
            <div>
                <label class="block text-slate-700 font-bold mb-1">
                    4. Berkas Draf / Lampiran Pendukung <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <x-file-input name="attachment_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png" label="Pilih berkas draf / lampiran" hint="Word, PDF, Excel, atau Gambar (Maks. 20 MB)" :maxSize="20" />
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs rounded-[2px] shadow-xs transition-all flex items-center gap-1.5 cursor-pointer border-none">
                    <i class="ph ph-paper-plane-tilt text-sm"></i>
                    <span>Kirim Pengajuan ke Tim QMS</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TABEL RIWAYAT PENGAJUAN SAYA -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3 rounded-[2px]">
            <div class="flex items-center space-x-2 text-xs font-bold text-[#1677B8]">
                <i class="ph ph-table text-base"></i>
                <span class="capitalize text-slate-900 font-extrabold">Riwayat Pengajuan SOP Baru Saya & Tracking Progres</span>
            </div>
            <span class="text-[11px] text-[#1677B8] font-bold bg-white px-2.5 py-1.5 rounded-[2px] border border-blue-200 whitespace-nowrap shadow-2xs">
                Total {{ $requests->total() }} Data
            </span>
        </div>

        <div class="overflow-x-auto rounded-[2px] border border-slate-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[10.5px] font-extrabold text-slate-700 uppercase tracking-wider">
                        <th class="py-2.5 px-3 w-10 text-center">No</th>
                        <th class="py-2.5 px-3">Nama Usulan SOP</th>
                        <th class="py-2.5 px-3">Divisi / Dept</th>
                        <th class="py-2.5 px-3">Deskripsi / Kebutuhan</th>
                        <th class="py-2.5 px-3 text-center">Lampiran</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-center">Tracking Progres Real-Time</th>
                        <th class="py-2.5 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($requests as $idx => $req)
                        <tr class="hover:bg-blue-50/40 transition-colors">
                            <td class="py-3 px-3 text-center text-slate-400 font-bold">
                                {{ $requests->firstItem() + $idx }}
                            </td>
                            <td class="py-3 px-3 font-bold text-slate-800 max-w-xs">
                                <span class="font-bold text-slate-900 block">{{ $req->title }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $req->created_at->format('d M Y H:i') }}</span>
                                @if($req->status === 'revision' && $req->revision_notes)
                                    <div class="mt-1 p-1.5 bg-orange-50 border border-orange-200 rounded-[2px] text-[10px] text-orange-800">
                                        <strong>Poin Revisi dari QMS:</strong> {{ $req->revision_notes }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-[2px] font-bold text-[10.5px] border border-slate-200">
                                    {{ $req->department }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-600 max-w-xs truncate" title="{{ $req->description }}">
                                {{ Str::limit($req->description, 60) }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($req->attachment_file)
                                    <a href="{{ asset('storage/' . $req->attachment_file) }}" target="_blank" class="inline-flex items-center gap-1 text-[#1677B8] font-bold hover:underline" title="Unduh Berkas Lampiran">
                                        <i class="ph ph-file-arrow-down text-base"></i>
                                        <span>Draf</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                @if($req->status === 'completed' || $req->status === 'approved')
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-[2px] font-bold text-[10.5px] inline-flex items-center gap-1">
                                        <i class="ph ph-check-circle"></i>
                                        <span>{{ $req->status === 'completed' ? 'SOP Terbit' : 'Disetujui' }}</span>
                                    </span>
                                @elseif($req->status === 'in_progress')
                                    <span class="px-2.5 py-1 bg-blue-50 text-[#1677B8] border border-blue-200 rounded-[2px] font-bold text-[10.5px] inline-flex items-center gap-1">
                                        <i class="ph ph-arrows-clockwise animate-spin"></i>
                                        <span>Di Proses</span>
                                    </span>
                                @elseif($req->status === 'revision')
                                    <span class="px-2.5 py-1 bg-orange-50 text-orange-700 border border-orange-200 rounded-[2px] font-bold text-[10.5px] inline-flex items-center gap-1">
                                        <i class="ph ph-warning-circle"></i>
                                        <span>Perlu Revisi</span>
                                    </span>
                                @elseif($req->status === 'rejected')
                                    <span class="px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-[2px] font-bold text-[10.5px] inline-flex items-center gap-1">
                                        <i class="ph ph-x-circle"></i>
                                        <span>Ditolak</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-[2px] font-bold text-[10.5px] inline-flex items-center gap-1">
                                        <i class="ph ph-clock"></i>
                                        <span>Menunggu Review</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <button type="button" onclick='openTrackingModal(@json($req))' class="px-2.5 py-1 bg-white hover:bg-blue-50 text-[#1677B8] border border-blue-300 font-bold text-[10.5px] rounded-[2px] shadow-2xs inline-flex items-center gap-1 cursor-pointer transition-all">
                                    <i class="ph ph-path text-sm"></i>
                                    <span>Lacak Progres</span>
                                </button>
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                @if($req->status === 'revision')
                                    <button type="button" onclick='openEditRequestModal(@json($req))' class="px-2.5 py-1 bg-orange-600 hover:bg-orange-700 text-white font-bold text-[10.5px] rounded-[2px] shadow-2xs inline-flex items-center gap-1 cursor-pointer transition-all border-none">
                                        <i class="ph ph-pencil-simple-line text-xs"></i>
                                        <span>Perbaiki Usulan</span>
                                    </button>
                                @elseif($req->status === 'completed' && $req->document)
                                    <a href="{{ route('library.index', ['search' => $req->document->title]) }}" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 font-bold text-[10.5px] rounded-[2px] shadow-2xs inline-flex items-center gap-1">
                                        <i class="ph ph-books text-xs"></i>
                                        <span>Lihat di E-Library</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[10.5px] italic">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-400">
                                <i class="ph ph-file-plus text-3xl mb-1 block text-slate-300"></i>
                                <span class="font-bold text-xs text-slate-700">Belum ada usulan SOP baru yang diajukan.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="pt-2">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

</div>

<!-- REAL-TIME TRACKING MODAL -->
<div id="trackingModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
        <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="ph ph-path text-xl"></i>
                <h3 class="font-bold text-sm">Linimasa & Tracking Real-Time SOP</h3>
            </div>
            <button type="button" onclick="closeTrackingModal()" class="text-white/80 hover:text-white text-lg cursor-pointer border-none bg-transparent"><i class="ph ph-x"></i></button>
        </div>
        <div class="p-5 space-y-4 text-xs">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Judul Usulan SOP</span>
                <h4 id="trackTitle" class="font-bold text-sm text-slate-800">-</h4>
                <div class="flex items-center gap-2 mt-1">
                    <span id="trackDept" class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-[2px] font-bold text-[10px] border border-slate-200">-</span>
                    <span id="trackDate" class="text-[10.5px] text-slate-400">-</span>
                </div>
            </div>

            <!-- TIMELINE STEPS -->
            <div class="border-t border-slate-100 pt-4 space-y-4">
                <!-- Step 1: Pengajuan Masuk -->
                <div class="flex items-start gap-3">
                    <div id="step1Dot" class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                        <i class="ph ph-check"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-slate-800">1. Pengajuan Dikirimkan</h5>
                        <p class="text-[11px] text-slate-500">Usulan pembuatan SOP telah tercatat dalam antrean sistem e-QMS.</p>
                    </div>
                </div>

                <!-- Step 2: Verifikasi Tim QMS -->
                <div class="flex items-start gap-3">
                    <div id="step2Dot" class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        2
                    </div>
                    <div>
                        <h5 id="step2Title" class="font-bold text-slate-800">2. Verifikasi & Analisis Tim QMS</h5>
                        <p id="step2Desc" class="text-[11px] text-slate-500">Tim QMS melakukan telaah kebutuhan klausul dan kelayakan proses bisnis.</p>
                        <div id="step2Notes" class="mt-1 hidden p-2 bg-orange-50 border border-orange-200 rounded-[2px] text-[10.5px] text-orange-800"></div>
                    </div>
                </div>

                <!-- Step 3: Perancangan Dokumen SOP -->
                <div class="flex items-start gap-3">
                    <div id="step3Dot" class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        3
                    </div>
                    <div>
                        <h5 id="step3Title" class="font-bold text-slate-800">3. Pembuatan Naskah & Alur Persetujuan</h5>
                        <p id="step3Desc" class="text-[11px] text-slate-500">Penyusunan naskah SOP resmi, Lembar Pengesahan, dan alur tanda tangan digital.</p>
                        <div id="step3Doc" class="mt-1 hidden p-2 bg-blue-50 border border-blue-200 rounded-[2px] text-[10.5px] text-[#1677B8] font-bold"></div>
                    </div>
                </div>

                <!-- Step 4: SOP Terbit / Sah -->
                <div class="flex items-start gap-3">
                    <div id="step4Dot" class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        4
                    </div>
                    <div>
                        <h5 id="step4Title" class="font-bold text-slate-800">4. SOP Terbit & Aktif di E-Library</h5>
                        <p id="step4Desc" class="text-[11px] text-slate-500">Dokumen telah disahkan secara legal digital dan siap disosialisasikan ke seluruh unit kerja.</p>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-200 flex justify-end">
                <x-interactive-button text="Tutup" variant="outline" type="button" onclick="closeTrackingModal()" />
            </div>
        </div>
    </div>
</div>

<!-- EDIT REVISI MODAL -->
<div id="editRequestModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
        <div class="bg-orange-600 text-white p-4 flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-1.5">
                <i class="ph ph-pencil-simple-line text-base"></i>
                <span>Perbaiki Usulan Pengajuan SOP Baru</span>
            </h3>
            <button type="button" onclick="closeEditRequestModal()" class="text-white/80 hover:text-white text-lg cursor-pointer border-none bg-transparent"><i class="ph ph-x"></i></button>
        </div>
        <form id="editRequestForm" method="POST" action="" enctype="multipart/form-data" class="p-5 space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="p-3 bg-orange-50 border border-orange-200 rounded-[2px] text-orange-800 text-xs">
                <span class="font-bold block mb-1">Catatan Revisi dari Tim QMS:</span>
                <p id="editRevisionNotesText" class="font-medium">-</p>
            </div>

            <div>
                <label for="edit_title" class="block text-slate-700 font-bold mb-1">Judul SOP <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_title" name="title" required class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-bold text-slate-800 uppercase">
            </div>

            <div>
                <label for="edit_department" class="block text-slate-700 font-bold mb-1">Unit / Departemen <span class="text-rose-500">*</span></label>
                <select id="edit_department" name="department" required class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-semibold text-slate-800">
                    @foreach($departmentGroups as $groupLabel => $depts)
                        <optgroup label="{{ $groupLabel }}">
                            @foreach($depts as $deptCode => $deptName)
                                <option value="{{ $deptCode }}">{{ $deptName }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="edit_description" class="block text-slate-700 font-bold mb-1">Deskripsi & Perbaikan Kebutuhan <span class="text-rose-500">*</span></label>
                <textarea id="edit_description" name="description" rows="4" required class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-medium text-slate-800"></textarea>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Perbarui Berkas Draf / Lampiran (Opsional)</label>
                <input type="file" name="attachment_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png" class="w-full text-xs border border-slate-300 rounded-[2px] p-2 bg-white text-slate-700">
                <span class="text-[10.5px] text-slate-400 block mt-0.5">Biarkan kosong jika tidak mengubah berkas draf yang lama.</span>
            </div>

            <div class="pt-2 flex justify-end gap-2 border-t border-slate-200">
                <x-interactive-button text="Batal" variant="outline" type="button" onclick="closeEditRequestModal()" />
                <button type="submit" class="px-4 py-2 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs rounded-[2px] shadow-xs flex items-center gap-1.5 cursor-pointer border-none">
                    <i class="ph ph-paper-plane-tilt"></i>
                    <span>Kirim Ulang ke QMS</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openTrackingModal(req) {
        document.getElementById('trackTitle').textContent = req.title;
        document.getElementById('trackDept').textContent = req.department;
        document.getElementById('trackDate').textContent = new Date(req.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

        const step2Dot = document.getElementById('step2Dot');
        const step2Title = document.getElementById('step2Title');
        const step2Desc = document.getElementById('step2Desc');
        const step2Notes = document.getElementById('step2Notes');

        const step3Dot = document.getElementById('step3Dot');
        const step3Doc = document.getElementById('step3Doc');

        const step4Dot = document.getElementById('step4Dot');

        // Reset
        step2Dot.className = 'w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs flex-shrink-0';
        step2Dot.innerHTML = '2';
        step2Notes.classList.add('hidden');

        step3Dot.className = 'w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs flex-shrink-0';
        step3Dot.innerHTML = '3';
        step3Doc.classList.add('hidden');

        step4Dot.className = 'w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs flex-shrink-0';
        step4Dot.innerHTML = '4';

        if (req.status === 'revision') {
            step2Dot.className = 'w-6 h-6 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold text-xs flex-shrink-0';
            step2Dot.innerHTML = '<i class="ph ph-warning-circle"></i>';
            step2Title.textContent = '2. Memerlukan Revisi Pemohon';
            step2Desc.textContent = 'Tim QMS telah memeriksa usulan dan meminta kelengkapan/penyesuaian data.';
            if (req.revision_notes) {
                step2Notes.textContent = 'Catatan: ' + req.revision_notes;
                step2Notes.classList.remove('hidden');
            }
        } else if (req.status === 'rejected') {
            step2Dot.className = 'w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center font-bold text-xs flex-shrink-0';
            step2Dot.innerHTML = '<i class="ph ph-x"></i>';
            step2Title.textContent = '2. Usulan Ditolak';
            step2Desc.textContent = req.admin_notes || 'Usulan belum memenuhi kriteria standardisasi.';
        } else if (req.status === 'in_progress') {
            step2Dot.className = 'w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs flex-shrink-0';
            step2Dot.innerHTML = '<i class="ph ph-check"></i>';

            step3Dot.className = 'w-6 h-6 rounded-full bg-[#1677B8] text-white flex items-center justify-center font-bold text-xs flex-shrink-0';
            step3Dot.innerHTML = '<i class="ph ph-arrows-clockwise animate-spin"></i>';
            if (req.document) {
                step3Doc.textContent = 'Dokumen: ' + (req.document.doc_number || req.document.title) + ' (Tahap Approval)';
                step3Doc.classList.remove('hidden');
            }
        } else if (req.status === 'approved' || req.status === 'completed') {
            step2Dot.className = 'w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs flex-shrink-0';
            step2Dot.innerHTML = '<i class="ph ph-check"></i>';

            step3Dot.className = 'w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs flex-shrink-0';
            step3Dot.innerHTML = '<i class="ph ph-check"></i>';

            step4Dot.className = 'w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs flex-shrink-0';
            step4Dot.innerHTML = '<i class="ph ph-check-circle"></i>';
        } else {
            // pending
            step2Dot.className = 'w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-xs flex-shrink-0';
            step2Dot.innerHTML = '<i class="ph ph-clock"></i>';
            step2Title.textContent = '2. Dalam Antrean Verifikasi QMS';
        }

        document.getElementById('trackingModal').classList.remove('hidden');
    }

    function closeTrackingModal() {
        document.getElementById('trackingModal').classList.add('hidden');
    }

    function openEditRequestModal(req) {
        document.getElementById('editRequestForm').action = '/sop-requests/' + req.id;
        document.getElementById('editRevisionNotesText').textContent = req.revision_notes || 'Silakan lengkapi atau perbaiki draf usulan SOP Anda.';
        document.getElementById('edit_title').value = req.title;
        document.getElementById('edit_department').value = req.department;
        document.getElementById('edit_description').value = req.description;
        document.getElementById('editRequestModal').classList.remove('hidden');
    }

    function closeEditRequestModal() {
        document.getElementById('editRequestModal').classList.add('hidden');
    }
</script>
@endsection
