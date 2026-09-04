@extends('layouts.admin')

@section('title', 'Review Pengajuan SOP Baru')
@section('header_title', 'Pengajuan Pembuatan SOP Baru')

@section('content')
<div class="space-y-6">

    <!-- TOP BANNER -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.dashboard') }}" variant="light" text="Kembali" />
            <span class="text-white/30">|</span>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#ffe16e] font-medium">Dashboard</a>
                <span>/</span>
                <span class="font-bold text-white">Review SOP Baru</span>
            </div>
        </div>

        <div>
            <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-white">Review Pengajuan SOP Baru dari Unit / User</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">Daftar permohonan dan usulan pembuatan SOP baru yang diajukan oleh unit kerja. Buat dokumen SOP langsung dan pantau status tracking secara real-time.</p>
        </div>
    </div>

    <!-- STATS BENTO CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Card 1: Total Usulan -->
        <div class="bg-white p-4 rounded-[2px] border border-sand-200/60 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Total Usulan</span>
                <h3 class="text-xl font-black text-slate-800">{{ $totalAll ?? $requests->total() }}</h3>
            </div>
            <div class="w-8 h-8 rounded-[2px] bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center font-bold">
                <i class="ph ph-files text-lg"></i>
            </div>
        </div>

        <!-- Card 2: Menunggu Review -->
        <div class="bg-white p-4 rounded-[2px] border border-sand-200/60 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Menunggu</span>
                <h3 class="text-xl font-black text-amber-700">{{ $totalPending }}</h3>
            </div>
            <div class="w-8 h-8 rounded-[2px] bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center font-bold">
                <i class="ph ph-clock text-lg"></i>
            </div>
        </div>

        <!-- Card 3: Sedang Diproses -->
        <div class="bg-white p-4 rounded-[2px] border border-sand-200/60 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Di Proses</span>
                <h3 class="text-xl font-black text-[#1677B8]">{{ $totalInProgress ?? 0 }}</h3>
            </div>
            <div class="w-8 h-8 rounded-[2px] bg-blue-50 text-[#1677B8] border border-blue-200 flex items-center justify-center font-bold">
                <i class="ph ph-arrows-clockwise text-lg"></i>
            </div>
        </div>

        <!-- Card 4: Perlu Revisi -->
        <div class="bg-white p-4 rounded-[2px] border border-sand-200/60 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Revisi</span>
                <h3 class="text-xl font-black text-orange-700">{{ $totalRevision ?? 0 }}</h3>
            </div>
            <div class="w-8 h-8 rounded-[2px] bg-orange-50 text-orange-700 border border-orange-200 flex items-center justify-center font-bold">
                <i class="ph ph-warning-circle text-lg"></i>
            </div>
        </div>

        <!-- Card 5: Disetujui / Selesai -->
        <div class="bg-white p-4 rounded-[2px] border border-sand-200/60 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Selesai / Terbit</span>
                <h3 class="text-xl font-black text-emerald-700">{{ $totalApproved }}</h3>
            </div>
            <div class="w-8 h-8 rounded-[2px] bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold">
                <i class="ph ph-check-circle text-lg"></i>
            </div>
        </div>

        <!-- Card 6: Ditolak -->
        <div class="bg-white p-4 rounded-[2px] border border-sand-200/60 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Ditolak</span>
                <h3 class="text-xl font-black text-rose-700">{{ $totalRejected }}</h3>
            </div>
            <div class="w-8 h-8 rounded-[2px] bg-rose-50 text-rose-700 border border-rose-200 flex items-center justify-center font-bold">
                <i class="ph ph-x-circle text-lg"></i>
            </div>
        </div>
    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-sand-200/60">
        <form method="GET" action="{{ route('admin.sop_requests.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari berdasarkan nama usulan SOP, departemen, pemohon, atau deskripsi..." 
                       class="w-full h-[38px] text-xs pl-9 pr-8 rounded-[2px] border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-medium text-slate-800">
                <button type="submit" class="absolute left-1 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center text-slate-400 hover:text-[#1677B8] border-none bg-transparent cursor-pointer">
                    <i class="ph ph-magnifying-glass text-base"></i>
                </button>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" class="h-[38px] text-xs px-3 rounded-[2px] border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-semibold text-slate-700">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Review</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Di Proses</option>
                    <option value="revision" {{ request('status') === 'revision' ? 'selected' : '' }}>Perlu Revisi</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui / Selesai</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>

                <x-interactive-button text="Cari" variant="blue" icon="ph ph-magnifying-glass text-sm" class="flex-shrink-0" />
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.sop_requests.index') }}" class="h-[38px] px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-[2px] text-xs font-bold transition-all flex items-center justify-center" title="Reset Filter">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- TABEL USULAN SOP -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3 rounded-[2px]">
            <div class="flex items-center space-x-2 text-xs font-bold text-[#1677B8]">
                <i class="ph ph-table text-base"></i>
                <span class="capitalize text-slate-900 font-extrabold">Antrean Verifikasi & Tindak Lanjut Pengajuan SOP Baru</span>
            </div>
            <span class="text-[11px] text-[#1677B8] font-bold bg-white px-2.5 py-1.5 rounded-[2px] border border-blue-200 whitespace-nowrap shadow-2xs">
                Total {{ $requests->total() }} Data
            </span>
        </div>

        <div class="overflow-x-auto rounded-[2px] border border-slate-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#1677B8] border-b border-[#1258a0] text-[10.5px] font-extrabold text-white uppercase tracking-wider">
                        <th class="py-2.5 px-3 w-10 text-center">No</th>
                        <th class="py-2.5 px-3">Nama Usulan SOP</th>
                        <th class="py-2.5 px-3">Pemohon & Unit</th>
                        <th class="py-2.5 px-3">Deskripsi & Kebutuhan</th>
                        <th class="py-2.5 px-3 text-center">Lampiran</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-center">Tindak Lanjut & Pembuatan SOP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($requests as $idx => $req)
                        @php
                            $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA', 'SEKRETARIS', 'FINANCE', 'LEGAL'];
                            $isSupport = in_array(strtoupper($req->department), $supportDepts);
                            $createUrl = $isSupport 
                                ? route('admin.support.create', ['department' => $req->department, 'from_request_id' => $req->id])
                                : route('admin.BU.create', ['unit' => $req->department, 'from_request_id' => $req->id]);
                        @endphp
                        <tr class="hover:bg-blue-50/40 transition-colors">
                            <td class="py-3 px-3 text-center text-slate-400 font-bold">
                                {{ $requests->firstItem() + $idx }}
                            </td>
                            <td class="py-3 px-3 font-bold text-slate-800 max-w-xs">
                                <div class="font-bold text-slate-900 leading-snug">{{ $req->title }}</div>
                                @if($req->document)
                                    <div class="mt-1 flex items-center gap-1.5 text-[10px]">
                                        <span class="text-emerald-700 font-bold flex items-center gap-1">
                                            <i class="ph ph-link"></i>
                                            Dokumen: {{ $req->document->doc_number ?? 'SOP Terkait' }}
                                        </span>
                                    </div>
                                @endif
                                @if($req->revision_notes)
                                    <div class="mt-1 p-1.5 bg-orange-50 border border-orange-200 rounded-[2px] text-[10px] text-orange-800">
                                        <strong>Catatan Revisi:</strong> {{ $req->revision_notes }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-700 whitespace-nowrap">
                                <span class="font-bold block text-slate-900">{{ $req->user->full_name ?? ($req->user->username ?? 'User') }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $req->created_at->format('d M Y H:i') }}</span>
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-[2px] font-bold text-[10px] mt-1 inline-block border border-slate-200">
                                    {{ $req->department }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-600 max-w-xs">
                                <p class="line-clamp-2" title="{{ $req->description }}">{{ $req->description }}</p>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($req->attachment_file)
                                    <a href="{{ asset('storage/' . $req->attachment_file) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-white hover:bg-slate-50 border border-slate-300 rounded-[2px] text-[10.5px] font-bold text-[#1677B8] shadow-2xs" title="Unduh Berkas Lampiran">
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
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($req->status === 'pending')
                                        <!-- 1. TAHAP AWAL: PILIHAN PROSES ATAU TOLAK -->
                                        <form method="POST" action="{{ route('admin.sop_requests.mark_in_progress', $req->id) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-[10.5px] rounded-[2px] shadow-2xs inline-flex items-center gap-1 cursor-pointer transition-all" title="Proses Usulan Ini">
                                                <i class="ph ph-arrows-clockwise text-xs"></i>
                                                <span>Proses</span>
                                            </button>
                                        </form>

                                        <button type="button" onclick="openRejectModal({{ $req->id }}, '{{ addslashes($req->title) }}')" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-[10.5px] rounded-[2px] shadow-2xs inline-flex items-center gap-1 cursor-pointer transition-all" title="Tolak Usulan">
                                            <i class="ph ph-x text-xs"></i>
                                            <span>Tolak</span>
                                        </button>
                                    @elseif($req->status === 'in_progress')
                                        <!-- 2. TAHAP DIPROSES: BUAT SOP ATAU LACAK APPROVAL -->
                                        @php
                                            $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA', 'SEKRETARIS', 'FINANCE', 'LEGAL'];
                                            $isSupport = in_array(strtoupper($req->department), $supportDepts);
                                            $docDetailUrl = $req->document 
                                                ? ($isSupport ? route('admin.support.document.detail', $req->document->id) : route('admin.BU.detail', $req->document->id))
                                                : null;
                                        @endphp
                                        @if(!$req->document)
                                            <a href="{{ $createUrl }}" class="px-2.5 py-1 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-[10.5px] rounded-[2px] shadow-2xs inline-flex items-center gap-1 cursor-pointer transition-all" title="Buat Dokumen SOP Resmi">
                                                <i class="ph ph-plus-circle text-xs"></i>
                                                <span>Buat SOP</span>
                                            </a>
                                        @elseif($docDetailUrl)
                                            <a href="{{ $docDetailUrl }}" class="px-2.5 py-1 bg-white hover:bg-blue-50 text-[#1677B8] border border-blue-300 font-bold text-[10.5px] rounded-[2px] shadow-2xs inline-flex items-center gap-1 cursor-pointer transition-all" title="Lacak Alur Persetujuan Dokumen">
                                                <i class="ph ph-path text-xs"></i>
                                                <span>Lacak Approval</span>
                                            </a>
                                        @endif
                                    @elseif($req->status === 'completed' || $req->status === 'approved')
                                        <!-- 3. TAHAP SELESAI: LIHAT E-LIBRARY -->
                                        @if($req->document)
                                            <a href="{{ route('library.index', ['search' => $req->document->title]) }}" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 font-bold text-[10.5px] rounded-[2px] shadow-2xs inline-flex items-center gap-1">
                                                <i class="ph ph-books text-xs"></i>
                                                <span>Lihat E-Library</span>
                                            </a>
                                        @else
                                            <span class="text-emerald-700 font-bold text-[10.5px]">✓ SOP Sah</span>
                                        @endif
                                    @elseif($req->status === 'rejected')
                                        <!-- 4. TAHAP DITOLAK -->
                                        <span class="text-rose-500 font-medium text-[10.5px] italic">Ditolak</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-slate-400">
                                <i class="ph ph-file-plus text-3xl mb-1 block text-slate-300"></i>
                                <span class="font-bold text-xs text-slate-700">Tidak ada data usulan SOP baru.</span>
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

<!-- REVISI MODAL -->
<div id="revisionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
        <div class="bg-orange-600 text-white p-4 flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-1.5">
                <i class="ph ph-pencil-line text-base"></i>
                <span>Minta Revisi / Kelengkapan SOP</span>
            </h3>
            <button type="button" onclick="closeRevisionModal()" class="text-white/80 hover:text-white text-lg cursor-pointer border-none bg-transparent"><i class="ph ph-x"></i></button>
        </div>
        <form id="revisionForm" method="POST" action="" class="p-5 space-y-4 text-xs">
            @csrf
            <div>
                <p class="text-slate-700 font-bold mb-2" id="revisionDocTitle">-</p>
                <label for="revision_notes" class="block text-slate-600 font-bold mb-1">Catatan Poin Revisi untuk Pemohon <span class="text-rose-500">*</span></label>
                <textarea id="revision_notes" name="revision_notes" rows="4" required placeholder="Jelaskan apa saja yang perlu diperbaiki atau dilengkapi oleh pemohon (contoh: Lampirkan flowchart operasional, rincian pihak terlibat, dsb)..." class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-orange-500 font-medium"></textarea>
            </div>
            <div class="pt-2 flex justify-end gap-2 border-t border-slate-200">
                <x-interactive-button text="Batal" variant="outline" type="button" onclick="closeRevisionModal()" />
                <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs rounded-[2px] shadow-xs flex items-center gap-1.5 cursor-pointer border-none">
                    <i class="ph ph-paper-plane-tilt"></i>
                    <span>Kirim ke Pemohon</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- REJECT MODAL -->
<div id="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
        <div class="bg-rose-600 text-white p-4 flex items-center justify-between">
            <h3 class="font-bold text-sm">Tolak Pengajuan SOP Baru</h3>
            <button type="button" onclick="closeRejectModal()" class="text-white/80 hover:text-white text-lg cursor-pointer border-none bg-transparent"><i class="ph ph-x"></i></button>
        </div>
        <form id="rejectForm" method="POST" action="" class="p-5 space-y-4 text-xs">
            @csrf
            <div>
                <p class="text-slate-700 font-bold mb-1" id="rejectDocTitle">-</p>
                <label for="admin_notes" class="block text-slate-600 font-semibold mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                <textarea id="admin_notes" name="admin_notes" rows="3" required placeholder="Tuliskan alasan penolakan usulan SOP baru..." class="w-full text-xs p-2.5 rounded-[2px] border border-slate-300 focus:outline-none focus:ring-2 focus:ring-rose-500 font-medium"></textarea>
            </div>
            <div class="pt-2 flex justify-end gap-2 border-t border-slate-200">
                <x-interactive-button text="Batal" variant="outline" type="button" onclick="closeRejectModal()" />
                <x-interactive-button text="Konfirmasi Tolak" variant="danger" icon="ph ph-x text-sm" type="submit" />
            </div>
        </form>
    </div>
</div>

<script>
    function openRevisionModal(id, title) {
        document.getElementById('revisionDocTitle').textContent = 'Usulan: ' + title;
        document.getElementById('revisionForm').action = '/admin/sop-requests/' + id + '/request-revision';
        document.getElementById('revisionModal').classList.remove('hidden');
    }
    function closeRevisionModal() {
        document.getElementById('revisionModal').classList.add('hidden');
    }

    function openRejectModal(id, title) {
        document.getElementById('rejectDocTitle').textContent = 'Usulan: ' + title;
        document.getElementById('rejectForm').action = '/admin/sop-requests/' + id + '/reject';
        document.getElementById('rejectModal').classList.remove('hidden');
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>
@endsection
