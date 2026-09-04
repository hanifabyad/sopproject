@extends(Auth::user()?->role === 'admin' ? 'layouts.admin' : 'layouts.reviewer')

@section('title', 'Permohonan Revisi SOP')
@section('header_title', 'Permohonan & Usulan Revisi SOP')

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
                <span class="font-bold text-white">Permohonan Revisi SOP</span>
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-white">Permohonan & Usulan Revisi Dokumen SOP</h2>
                <p class="text-xs text-white/85 mt-0.5 font-medium">Ajukan permohonan perubahan atau penyesuaian klausul SOP operasional bidang Anda langsung ke Admin QMS.</p>
            </div>

            <x-interactive-button text="Ajukan Revisi SOP Baru" variant="primary" icon="ph ph-plus-circle text-base" type="button" onclick="openNewRevisionModal()" />
        </div>
    </div>

    <!-- STATS BENTO CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-[2px] border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Total Usulan Diajukan</span>
                <h3 class="text-2xl font-black text-slate-800">{{ $totalMyRequests }} <span class="text-xs font-semibold text-slate-500">Permohonan</span></h3>
            </div>
            <div class="w-10 h-10 rounded-[2px] bg-blue-50 text-[#1677B8] border border-blue-200 flex items-center justify-center font-bold">
                <i class="ph ph-file-arrow-up text-xl"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-[2px] border border-sand-200/60 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Disetujui Admin</span>
                <h3 class="text-2xl font-black text-emerald-700">{{ $approvedCount }} <span class="text-xs font-semibold text-slate-500">Siap Unggah</span></h3>
            </div>
            <div class="w-10 h-10 rounded-[2px] bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold">
                <i class="ph ph-check-circle text-xl"></i>
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
    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="bg-white rounded-[2px] p-5 shadow-sm border border-sand-200/60">
        <form method="GET" action="{{ route('user.revision_requests.index') }}" class="flex items-center gap-3">
            <div class="relative flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari berdasarkan judul SOP, nomor dokumen, atau alasan permohonan..." 
                       class="w-full h-[38px] text-xs pl-9 pr-8 rounded-[2px] border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-medium text-slate-800">
                <button type="submit" class="absolute left-1 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center text-slate-400 hover:text-[#1677B8] border-none bg-transparent cursor-pointer">
                    <i class="ph ph-magnifying-glass text-base"></i>
                </button>
                @if(request('search'))
                    <a href="{{ route('user.revision_requests.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-600 text-xs" title="Reset pencarian">
                        <i class="ph ph-x"></i>
                    </a>
                @endif
            </div>

            <x-interactive-button text="Cari" variant="blue" icon="ph ph-magnifying-glass text-sm" />
        </form>
    </div>

    <!-- TABEL RIWAYAT PERMOHONAN REVISI (TRACKING STYLE) -->
    <div class="bg-white rounded-[2px] p-6 shadow-sm border border-sand-200/60 space-y-4">
        <!-- HEADER BIRU MUDA -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3 rounded-[2px]">
            <div class="flex items-center space-x-2 text-xs font-bold text-[#1677B8]">
                <i class="ph ph-table text-base"></i>
                <span class="capitalize text-slate-900 font-extrabold">Riwayat Permohonan Revisi Dokumen SOP</span>
            </div>
            <span class="text-[11px] text-[#1677B8] font-bold bg-white px-2.5 py-1.5 rounded-[2px] border border-blue-200 whitespace-nowrap shadow-2xs">
                Total {{ $requests->total() }} Data Pengajuan
            </span>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-[2px]">
            <table class="tracking-table w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10.5px] font-extrabold uppercase tracking-wider text-slate-700">
                    <tr>
                        <th class="py-2.5 px-2 text-center w-8 whitespace-nowrap">No</th>
                        <th class="py-2.5 px-2.5 whitespace-nowrap">Dokumen SOP & Identitas</th>
                        <th class="py-2.5 px-2.5 whitespace-nowrap">Alasan / Latar Belakang Perubahan</th>
                        <th class="py-2.5 px-2.5 text-center whitespace-nowrap w-32">Tgl Pengajuan</th>
                        <th class="py-2.5 px-2.5 text-center whitespace-nowrap w-28">Status Usulan</th>
                        <th class="py-2.5 px-2 text-right whitespace-nowrap w-32 no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs font-semibold text-slate-800">
                    @forelse($requests as $index => $req)
                    @php
                        $doc = $req->document;
                        $isSupport = in_array(strtoupper($doc->department ?? ''), ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL']);
                        $uploadRoute = $isSupport ? route('admin.support.creator_revise', $doc->id) : route('admin.BU.creator_revise', $doc->id);
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-2.5 px-2 text-center font-bold text-slate-400 text-[11px] align-middle">
                            {{ $requests->firstItem() + $index }}
                        </td>

                        <!-- KOLOM 1: DOKUMEN SOP & IDENTITAS -->
                        <td class="py-2.5 px-3 align-middle">
                            <div class="font-bold text-slate-900 text-xs leading-snug">
                                <a href="{{ route('reviewer.show', $doc->id) }}" class="hover:text-[#1677B8] hover:underline transition-colors block">{{ $doc->title }}</a>
                            </div>
                            <div class="flex items-center gap-1.5 mt-1 flex-wrap text-[10px]">
                                <span class="font-mono font-semibold text-slate-600">{{ $doc->doc_number ?? 'No. Belum Diatur' }}</span>
                                <span class="text-slate-300">|</span>
                                <span class="font-mono font-bold text-slate-700">Rev {{ $doc->doc_revision ?? '0' }}</span>
                                <span class="text-slate-300">|</span>
                                <span class="font-bold text-[#1677B8]">{{ $doc->department }}</span>
                            </div>
                        </td>

                        <!-- KOLOM 2: ALASAN REVISI -->
                        <td class="py-2.5 px-3 align-middle max-w-xs">
                            <p class="text-xs text-slate-800 line-clamp-2 leading-relaxed font-medium">
                                {{ $req->reason }}
                            </p>
                            @if($req->admin_notes)
                            <div class="mt-1 p-1.5 bg-amber-50 rounded-[2px] border border-amber-200 text-[10px] text-amber-800 font-medium">
                                <span class="font-bold">Catatan Admin:</span> {{ $req->admin_notes }}
                            </div>
                            @endif
                        </td>

                        <!-- KOLOM 3: TGL PENGAJUAN -->
                        <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                            <div class="font-mono text-xs text-slate-900 font-bold">
                                {{ $req->created_at->format('d/m/Y') }}
                            </div>
                            <div class="text-[10px] text-slate-400 font-medium mt-0.5">
                                {{ $req->created_at->format('H:i') }} WIB
                            </div>
                        </td>

                        <!-- KOLOM 4: STATUS USULAN -->
                        <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                            @if($req->status === 'approved')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-300 rounded-[2px] text-[9.5px] font-bold uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    <span>DISETUJUI</span>
                                </span>
                            @elseif($req->status === 'rejected')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-300 rounded-[2px] text-[9.5px] font-bold uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                    <span>DITOLAK ADMIN</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-300 rounded-[2px] text-[9.5px] font-bold uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>MENUNGGU REVIEW</span>
                                </span>
                            @endif
                        </td>

                        <!-- KOLOM 5: AKSI TINDAK LANJUT -->
                        <td class="py-2.5 px-3 text-right align-middle whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <x-interactive-button text="Detail" variant="outline" icon="ph ph-eye text-xs" type="button" 
                                        onclick="showRevisionDetailModal({
                                            id: {{ $req->id }},
                                            doc_title: '{{ addslashes($doc->title) }}',
                                            doc_number: '{{ addslashes($doc->doc_number ?? '-') }}',
                                            department: '{{ addslashes($doc->department ?? '-') }}',
                                            revision_number: '{{ $doc->doc_revision ?? '0' }}',
                                            reason: '{{ addslashes(str_replace(["\r", "\n"], [' ', '\n'], $req->reason)) }}',
                                            status: '{{ $req->status }}',
                                            created_at: '{{ $req->created_at->format('d F Y, H:i') }} WIB',
                                            deadline_at: '{{ $req->deadline_at ? $req->deadline_at->format('d F Y') : '-' }}',
                                            admin_notes: '{{ addslashes(str_replace(["\r", "\n"], [' ', '\n'], $req->admin_notes ?? '')) }}',
                                            upload_route: '{{ $uploadRoute }}',
                                            pdf_url: '{{ route('reviewer.stream.file', $doc->id) }}'
                                        })" />

                                @if($req->status === 'approved')
                                    <x-interactive-button text="Unggah Revisi" variant="success" icon="ph ph-upload-simple text-xs" href="{{ $uploadRoute }}" />
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <i class="ph ph-file-text text-4xl text-slate-300"></i>
                                <h5 class="text-xs font-bold text-slate-700">Belum Ada Pengajuan Revisi</h5>
                                <p class="text-[11px] text-slate-500 max-w-sm">
                                    Klik tombol kuning di atas untuk mengajukan usulan revisi SOP baru di bidang Anda.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
        <div class="pt-3 border-t border-slate-200">
            {{ $requests->links() }}
        </div>
        @endif
    </div>

</div>

<!-- MODAL FORMULIR PENGAJUAN REVISI SOP -->
<div id="newRevisionModal" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-xl w-full max-w-lg shadow-2xl border border-slate-200 overflow-hidden my-auto animate-in fade-in zoom-in duration-200">
        <!-- MODAL HEADER -->
        <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-[2px] bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-file-arrow-up text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Formulir Usulan Revisi SOP</h3>
                    <p class="text-[10px] text-white/85 font-medium">Permohonan akan langsung dikirim ke Admin QMS untuk diverifikasi</p>
                </div>
            </div>
            <button type="button" onclick="closeNewRevisionModal()" class="text-white/80 hover:text-white text-lg cursor-pointer border-none bg-transparent">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <!-- MODAL FORM -->
        <form id="newRevisionForm" action="" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- PILIH DOKUMEN SOP -->
            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Pilih Dokumen SOP Yang Ingin Direvisi <span class="text-rose-500">*</span></label>
                <select id="doc_select" onchange="updateFormAction(this.value)" required class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:ring-2 focus:ring-[#1677B8] font-bold text-slate-800 bg-white outline-none">
                    <option value="">-- Pilih Dokumen SOP Bidang Anda --</option>
                    @forelse($availableDocuments as $doc)
                        <option value="{{ $doc->id }}">
                            [{{ $doc->doc_number ?? 'SOP' }}] {{ $doc->title }} ({{ $doc->department }})
                        </option>
                    @empty
                        <option value="" disabled>Tidak ada dokumen SOP aktif pada bidang/departemen Anda</option>
                    @endforelse
                </select>
                <p class="text-[10px] text-slate-500 mt-1 font-medium">Hanya menampilkan dokumen SOP aktif yang berada di bawah lingkup departemen/bidang Anda.</p>
            </div>

            <!-- ALASAN REVISI -->
            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Alasan / Latar Belakang Perubahan <span class="text-rose-500">*</span></label>
                <textarea name="reason" rows="4" required placeholder="Jelaskan secara rinci latar belakang perlunya perubahan SOP (misal: penyesuaian instruksi kerja keselamatan terbaru, perubahan alur approval, pergantian PIC operasional, temuan audit)..." class="w-full text-xs p-3 rounded-[2px] border border-slate-300 focus:ring-2 focus:ring-[#1677B8] font-medium text-slate-800 outline-none leading-relaxed"></textarea>
            </div>

            <!-- INFO BOX REVISI -->
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-[2px] flex items-start gap-2.5 text-xs text-blue-900">
                <i class="ph ph-info text-base text-[#1677B8] flex-shrink-0 mt-0.5"></i>
                <div class="text-[10.5px] leading-relaxed">
                    <span class="font-bold">Ketentuan Revisi:</span> Setelah disetujui Admin QMS, Anda akan menerima email notifikasi dan dapat langsung mengunggah naskah revisi baru.
                </div>
            </div>

            <!-- SUBMIT BUTTONS -->
            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-200">
                <x-interactive-button text="Batal" variant="outline" type="button" onclick="closeNewRevisionModal()" />
                <x-interactive-button text="Konfirmasi" variant="blue" icon="ph ph-check-circle text-sm" type="submit" />
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL DETAIL PERMOHONAN REVISI (USER VIEW) -->
<!-- ========================================================================= -->
<div id="detailRevisionModal" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-xl w-full max-w-2xl shadow-2xl border border-slate-200 overflow-hidden my-auto animate-in fade-in zoom-in duration-200 max-h-[92vh] flex flex-col">
        <!-- HEADER -->
        <div class="p-4 bg-gradient-to-r from-[#1677B8] via-[#1260a0] to-[#0f4c81] text-white flex items-center justify-between flex-shrink-0 shadow-sm">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-[2px] bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-file-text text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Rincian Status Permohonan Revisi SOP</h3>
                    <p class="text-[10.5px] text-white/80 font-medium">Informasi tindak lanjut dan instruksi resmi dari Admin QMS</p>
                </div>
            </div>
            <button type="button" onclick="closeRevisionDetailModal()" class="text-white/80 hover:text-white text-lg cursor-pointer bg-transparent border-none">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <!-- BODY -->
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4 text-xs">
            <!-- 1. IDENTITAS DOKUMEN -->
            <div class="p-4 bg-gradient-to-r from-blue-50/70 to-slate-50 border border-blue-200 rounded-[2px] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-1">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-[#1677B8] block">Dokumen SOP</span>
                    <h4 class="text-sm font-black text-slate-900 leading-snug" id="modalRevDocTitle">-</h4>
                    <div class="flex items-center gap-2 text-xs text-slate-600 font-semibold pt-0.5">
                        <span class="font-mono bg-white px-2 py-0.5 rounded-[2px] border border-slate-300 text-slate-800" id="modalRevDocNumber">-</span>
                        <span>&bull;</span>
                        <span class="text-[#1677B8] font-bold" id="modalRevDocDept">-</span>
                        <span>&bull;</span>
                        <span class="text-slate-500" id="modalRevDocRev">-</span>
                    </div>
                </div>
                <div class="flex-shrink-0" id="modalRevStatusBadge">
                    <!-- Status Badge injected via JS -->
                </div>
            </div>

            <!-- 2. GRID INFO PENGAJUAN & TENGGAT SLA -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="p-3 bg-white border border-slate-200 rounded-[2px] shadow-2xs space-y-1">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                        <i class="ph ph-calendar text-xs text-[#1677B8]"></i> Waktu Pengajuan
                    </span>
                    <strong class="text-slate-900 font-black text-xs block" id="modalRevCreatedAt">-</strong>
                </div>

                <div id="modalRevDeadlineCard" class="p-3 bg-white border border-slate-200 rounded-[2px] shadow-2xs space-y-1">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                        <i class="ph ph-hourglass-high text-xs text-amber-600"></i> Target Selesai Revisi
                    </span>
                    <strong class="text-amber-900 font-black text-xs block" id="modalRevDeadline">-</strong>
                </div>
            </div>

            <!-- 3. ALASAN PENGAJUAN -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-[2px] space-y-1.5">
                <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                    <i class="ph ph-chat-text text-sm text-[#1677B8]"></i>
                    <span>Latar Belakang / Usulan Perubahan dari Pemohon:</span>
                </div>
                <p class="text-xs text-slate-800 leading-relaxed font-medium whitespace-pre-line pl-5" id="modalRevReason">-</p>
            </div>

            <!-- 4. CATATAN ADMIN -->
            <div id="modalRevAdminNotesCard" class="p-4 bg-amber-50/70 border border-amber-200 rounded-[2px] space-y-1.5 hidden">
                <div class="flex items-center gap-1.5 text-[11px] font-bold text-amber-900 uppercase tracking-wider">
                    <i class="ph ph-gavel text-sm text-amber-700"></i>
                    <span>Catatan & Instruksi Admin QMS:</span>
                </div>
                <p class="text-xs text-amber-950 leading-relaxed font-medium whitespace-pre-line pl-5" id="modalRevAdminNotes">-</p>
            </div>

            <!-- 5. TAUTAN BERKAS PDF SOP SAAT INI -->
            <div class="p-3.5 bg-white border border-slate-200 rounded-[2px] shadow-2xs flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[2px] bg-blue-50 text-[#1677B8] flex items-center justify-center font-bold flex-shrink-0">
                        <i class="ph ph-file-pdf text-xl"></i>
                    </div>
                    <div>
                        <strong class="text-xs text-slate-900 font-bold block">Dokumen SOP Master (Aktif)</strong>
                        <p class="text-[10px] text-slate-500">Gunakan naskah aktif sebagai acuan dalam menyusun naskah revisi</p>
                    </div>
                </div>
                <a id="modalRevPdfLink" href="#" target="_blank" class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold text-xs rounded-[2px] flex items-center gap-1 shadow-2xs transition-all">
                    <span>Lihat SOP</span>
                    <i class="ph ph-arrow-square-out text-sm"></i>
                </a>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between flex-shrink-0">
            <x-interactive-button text="Tutup" variant="outline" type="button" onclick="closeRevisionDetailModal()" />
            <div id="modalRevFooterActions">
                <!-- Action link injected via JS -->
            </div>
        </div>
    </div>
</div>

<script>
    function openNewRevisionModal() {
        const modal = document.getElementById('newRevisionModal');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
        }
    }

    function closeNewRevisionModal() {
        const modal = document.getElementById('newRevisionModal');
        if (modal) modal.classList.add('hidden');
    }

    function updateFormAction(docId) {
        const form = document.getElementById('newRevisionForm');
        if (docId) {
            form.action = `/documents/${docId}/request-revision`;
        } else {
            form.action = '';
        }
    }

    function showRevisionDetailModal(data) {
        document.getElementById('modalRevDocTitle').textContent = data.doc_title;
        document.getElementById('modalRevDocNumber').textContent = data.doc_number;
        document.getElementById('modalRevDocDept').textContent = 'Unit: ' + data.department;
        document.getElementById('modalRevDocRev').textContent = 'Rev. ' + data.revision_number;
        document.getElementById('modalRevCreatedAt').textContent = data.created_at;
        document.getElementById('modalRevReason').textContent = data.reason;
        
        const pdfLink = document.getElementById('modalRevPdfLink');
        if (data.pdf_url) {
            pdfLink.href = data.pdf_url;
            pdfLink.classList.remove('hidden');
        } else {
            pdfLink.classList.add('hidden');
        }

        // Status badge
        const badgeEl = document.getElementById('modalRevStatusBadge');
        const deadlineCard = document.getElementById('modalRevDeadlineCard');
        const footerActions = document.getElementById('modalRevFooterActions');

        if (data.status === 'approved') {
            badgeEl.innerHTML = `
                <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-300 rounded-[2px] text-xs font-black uppercase tracking-wider shadow-2xs">
                    <i class="ph ph-check-circle text-sm"></i> Disetujui Admin
                </span>
            `;
            if (deadlineCard) deadlineCard.classList.add('hidden');

            footerActions.innerHTML = `
                <a href="${data.upload_route}" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-[2px] shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-upload-simple text-sm"></i>
                    <span>Mulai Unggah Berkas Revisi</span>
                </a>
            `;
        } else if (data.status === 'rejected') {
            badgeEl.innerHTML = `
                <span class="inline-flex items-center gap-1 px-3 py-1 bg-rose-50 text-rose-700 border border-rose-300 rounded-[2px] text-xs font-black uppercase tracking-wider shadow-2xs">
                    <i class="ph ph-x-circle text-sm"></i> Ditolak Admin
                </span>
            `;
            deadlineCard.classList.add('hidden');
            footerActions.innerHTML = '';
        } else {
            badgeEl.innerHTML = `
                <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-50 text-amber-700 border border-amber-300 rounded-[2px] text-xs font-black uppercase tracking-wider shadow-2xs">
                    <i class="ph ph-clock text-sm"></i> Menunggu Review Admin
                </span>
            `;
            deadlineCard.classList.add('hidden');
            footerActions.innerHTML = '';
        }

        // Admin notes
        const notesBox = document.getElementById('modalRevAdminNotesBox');
        const notesEl = document.getElementById('modalRevAdminNotes');
        if (data.admin_notes && data.admin_notes.trim() !== '') {
            notesEl.textContent = data.admin_notes;
            notesBox.classList.remove('hidden');
        } else {
            if (data.status === 'approved') {
                notesEl.textContent = 'Permohonan revisi telah disetujui. Silakan unggah naskah revisi baru sebelum batas SLA berakhir.';
                notesBox.classList.remove('hidden');
            } else {
                notesBox.classList.add('hidden');
            }
        }

        const modal = document.getElementById('detailRevisionModal');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
        }
    }

    function closeRevisionDetailModal() {
        const modal = document.getElementById('detailRevisionModal');
        if (modal) modal.classList.add('hidden');
    }
</script>
@endsection
