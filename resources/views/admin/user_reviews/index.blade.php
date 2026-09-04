@extends('layouts.admin')

@section('title', 'Verifikasi & Review Aktivitas User')
@section('header_title', 'Pusat Verifikasi Aktivitas SOP')

@section('content')
<div class="w-full space-y-6">

    <!-- TOP HEADER BAR -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-[2px] p-5 md:p-6 shadow-sm border border-white/10 flex flex-col gap-3 w-full">
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.dashboard') }}" variant="light" text="Kembali" />
            <span class="text-white/30">|</span>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-medium">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#ffe16e] font-medium">Dashboard</a>
                <span>/</span>
                <span class="font-semibold text-white">Verifikasi Aktivitas User</span>
            </div>
        </div>

        <div>
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-white capitalize">Pusat Verifikasi & Review Aktivitas User</h2>
            <p class="text-xs text-white/85 mt-0.5 font-normal">
                Pusat pengawasan tindak lanjut permohonan revisi, bukti sosialisasi SOP, dan evaluasi hasil kuis pemahaman karyawan.
            </p>
        </div>
    </div>



    <!-- 4 EXECUTIVE METRIC WIDGETS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
        <!-- Card 1: Permohonan Revisi Pending -->
        <a href="{{ route('admin.user_reviews.index', ['tab' => 'revision', 'status' => 'pending']) }}" class="bg-white p-4 rounded-[2px] shadow-sm border border-sand-200/60 hover:border-amber-400 transition-all flex items-center justify-between group cursor-pointer">
            <div>
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Permohonan Revisi</span>
                <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $pendingRevisionCount }}</span>
                <span class="text-[10px] font-medium text-amber-700 flex items-center gap-1 mt-0.5">
                    <i class="ph ph-clock"></i>
                    <span>Membutuhkan persetujuan</span>
                </span>
            </div>
            <div class="w-10 h-10 rounded-[2px] bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center text-xl font-bold group-hover:scale-105 transition-transform">
                <i class="ph ph-git-pull-request"></i>
            </div>
        </a>

        <!-- Card 2: Bukti Sosialisasi -->
        <a href="{{ route('admin.user_reviews.index', ['tab' => 'socialization']) }}" class="bg-white p-4 rounded-[2px] shadow-sm border border-sand-200/60 hover:border-[#1677B8] transition-all flex items-center justify-between group cursor-pointer">
            <div>
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Bukti Sosialisasi</span>
                <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $totalSocializationCount }}</span>
                <span class="text-[10px] font-medium text-emerald-700 flex items-center gap-1 mt-0.5">
                    <i class="ph ph-check-circle"></i>
                    <span>Foto & daftar hadir tersimpan</span>
                </span>
            </div>
            <div class="w-10 h-10 rounded-[2px] bg-blue-50 text-[#1677B8] border border-blue-200 flex items-center justify-center text-xl font-bold group-hover:scale-105 transition-transform">
                <i class="ph ph-camera"></i>
            </div>
        </a>

        <!-- Card 3: Usulan SOP Baru -->
        <a href="{{ route('admin.user_reviews.index', ['tab' => 'new_sop']) }}" class="bg-white p-4 rounded-[2px] shadow-sm border border-sand-200/60 hover:border-indigo-400 transition-all flex items-center justify-between group cursor-pointer">
            <div>
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Usulan SOP Baru</span>
                <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $pendingNewSopCount }}</span>
                <span class="text-[10px] font-medium text-indigo-700 flex items-center gap-1 mt-0.5">
                    <i class="ph ph-file-plus"></i>
                    <span>{{ $totalNewSopCount }} total diajukan</span>
                </span>
            </div>
            <div class="w-10 h-10 rounded-[2px] bg-indigo-50 text-indigo-600 border border-indigo-200 flex items-center justify-center text-xl font-bold group-hover:scale-105 transition-transform">
                <i class="ph ph-file-plus"></i>
            </div>
        </a>

        <!-- Card 4: Rekap Kuis Karyawan -->
        <a href="{{ route('admin.user_reviews.index', ['tab' => 'quiz', 'quiz_category' => 'all']) }}" class="bg-white p-4 rounded-[2px] shadow-sm border border-sand-200/60 hover:border-emerald-500 transition-all flex items-center justify-between group cursor-pointer">
            <div>
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Total Uji Kuis</span>
                <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $totalQuizCount }}</span>
                <span class="text-[10px] font-medium text-[#1677B8] flex items-center gap-1 mt-0.5">
                    <i class="ph ph-exam"></i>
                    <span>{{ $totalAllQuizTakersCount }} Peserta Diuji</span>
                </span>
            </div>
            <div class="w-10 h-10 rounded-[2px] bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center text-xl font-bold group-hover:scale-105 transition-transform">
                <i class="ph ph-graduation-cap"></i>
            </div>
        </a>
    </div>

    <!-- MAIN WORKSPACE CONTAINER (Fit edge-to-edge) -->
    <div class="bg-white rounded-[2px] p-4 sm:p-6 shadow-sm border border-sand-200/60 space-y-5 w-full">
        
        <!-- TAB NAVIGATION (Fit Edge-to-Edge) -->
        <div class="border-b border-slate-200 grid grid-cols-2 lg:grid-cols-4 w-full">
            <a href="{{ route('admin.user_reviews.index', ['tab' => 'revision']) }}" 
               class="pb-3 px-2 text-xs font-semibold transition-all flex items-center justify-center gap-2 border-b-2 text-center {{ $activeTab === 'revision' ? 'border-[#1677B8] text-[#1677B8]' : 'border-transparent text-slate-500 hover:text-slate-900 font-normal' }}">
                <i class="ph ph-git-pull-request text-base shrink-0"></i>
                <span class="truncate">Permohonan Revisi SOP</span>
                @if($pendingRevisionCount > 0)
                    <span class="px-1.5 py-0.5 bg-rose-500 text-white rounded-full text-[9.5px] font-bold leading-none shadow-2xs shrink-0">
                        {{ $pendingRevisionCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.user_reviews.index', ['tab' => 'socialization']) }}" 
               class="pb-3 px-2 text-xs font-semibold transition-all flex items-center justify-center gap-2 border-b-2 text-center {{ $activeTab === 'socialization' ? 'border-[#1677B8] text-[#1677B8]' : 'border-transparent text-slate-500 hover:text-slate-900 font-normal' }}">
                <i class="ph ph-camera text-base shrink-0"></i>
                <span class="truncate">Verifikasi Bukti Sosialisasi</span>
                @if($pendingSocializationCount > 0)
                    <span class="px-1.5 py-0.5 bg-rose-500 text-white rounded-full text-[9.5px] font-bold leading-none shadow-2xs shrink-0">
                        {{ $pendingSocializationCount }}
                    </span>
                @else
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 border border-slate-200 rounded-full text-[9.5px] font-medium shrink-0">
                        {{ $totalSocializationCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.user_reviews.index', ['tab' => 'new_sop']) }}" 
               class="pb-3 px-2 text-xs font-semibold transition-all flex items-center justify-center gap-2 border-b-2 text-center {{ $activeTab === 'new_sop' ? 'border-[#1677B8] text-[#1677B8]' : 'border-transparent text-slate-500 hover:text-slate-900 font-normal' }}">
                <i class="ph ph-file-plus text-base shrink-0"></i>
                <span class="truncate">Usulan SOP Baru</span>
                @if($pendingNewSopCount > 0)
                    <span class="px-1.5 py-0.5 bg-rose-500 text-white rounded-full text-[9.5px] font-bold leading-none shadow-2xs shrink-0">
                        {{ $pendingNewSopCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.user_reviews.index', ['tab' => 'quiz', 'quiz_category' => $quizCategory ?? 'all']) }}" 
               class="pb-3 px-2 text-xs font-semibold transition-all flex items-center justify-center gap-2 border-b-2 text-center {{ $activeTab === 'quiz' ? 'border-[#1677B8] text-[#1677B8]' : 'border-transparent text-slate-500 hover:text-slate-900 font-normal' }}">
                <i class="ph ph-exam text-base shrink-0"></i>
                <span class="truncate">Hasil Kuis Pemahaman SOP</span>
                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 border border-slate-200 rounded-full text-[9.5px] font-medium shrink-0">
                    {{ $totalQuizCount }}
                </span>
            </a>
        </div>

        <!-- SEARCH & FILTER BAR -->
        <form method="GET" action="{{ route('admin.user_reviews.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end w-full">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            @if($activeTab === 'quiz')
                <input type="hidden" name="quiz_category" value="{{ $quizCategory ?? 'all' }}">
            @endif

            <!-- Input Cari -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-medium text-slate-700 mb-1">Pencarian Data</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i class="ph ph-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul SOP, nomor dokumen, pemohon..." 
                           class="w-full pl-9 pr-3 h-[36px] bg-slate-50 border border-slate-200 rounded-[2px] text-xs font-normal text-slate-800 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                </div>
            </div>

            <!-- Filter Status (Revisi & Kuis) -->
            @if(in_array($activeTab, ['revision', 'quiz']))
            <div>
                <label class="block text-[11px] font-medium text-slate-700 mb-1">Filter Status</label>
                <select name="status" class="w-full px-3 h-[36px] bg-slate-50 border border-slate-200 rounded-[2px] text-xs font-normal text-slate-800 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                    <option value="">Semua Status</option>
                    @if($activeTab === 'revision')
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved (Disetujui)</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                    @elseif($activeTab === 'quiz')
                        <option value="all_passed" {{ in_array($status, ['passed', 'all_passed']) ? 'selected' : '' }}>Semua Lulus (KKM ≥ 60)</option>
                        <option value="remedial" {{ in_array($status, ['failed', 'remedial']) ? 'selected' : '' }}>Perlu Remedial (< 60)</option>
                        <option value="no_participants" {{ $status === 'no_participants' ? 'selected' : '' }}>Belum Ada Peserta</option>
                    @endif
                </select>
            </div>
            @else
            <div></div>
            @endif

            <!-- Tombol Terapkan & Reset -->
            <div class="flex items-center gap-2">
                <x-interactive-button text="Terapkan" variant="blue" icon="ph ph-funnel text-sm" class="flex-1" />
                <a href="{{ route('admin.user_reviews.index', ['tab' => $activeTab, 'quiz_category' => $quizCategory ?? 'all']) }}" class="h-[36px] w-[36px] bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-[2px] text-xs font-medium transition-all flex items-center justify-center cursor-pointer flex-shrink-0" title="Reset Filter">
                    <i class="ph ph-arrow-counter-clockwise"></i>
                </a>
            </div>
        </form>

        <!-- ========================================================================= -->
        <!-- TAB 1 CONTENT: PERMOHONAN REVISI SOP -->
        <!-- ========================================================================= -->
        @if($activeTab === 'revision')
            <div class="space-y-4">
                <!-- HEADER BANNER BIRU MUDA -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3.5 rounded-[2px]">
                    <div class="flex items-center space-x-2 text-xs font-bold text-[#1677B8]">
                        <i class="ph ph-git-pull-request text-base"></i>
                        <span class="capitalize text-slate-900 font-extrabold">Daftar Permohonan Revisi Dokumen SOP oleh User</span>
                    </div>
                    <span class="text-xs text-[#1677B8] font-bold">Menampilkan {{ $revisionRequests->count() }} dari {{ $revisionRequests->total() }} pengajuan</span>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-[2px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-[#1677B8] border-b border-[#1258a0] text-[10.5px] font-extrabold uppercase tracking-wider text-white">
                            <tr>
                                <th class="py-2.5 px-2 text-center w-8 whitespace-nowrap">No</th>
                                <th class="py-2.5 px-2.5 whitespace-nowrap">Dokumen SOP & Identitas</th>
                                <th class="py-2.5 px-2.5 whitespace-nowrap w-36">Pemohon & Tanggal</th>
                                <th class="py-2.5 px-2.5 whitespace-nowrap">Alasan Permohonan</th>
                                <th class="py-2.5 px-2.5 text-center whitespace-nowrap w-32">Status Usulan</th>
                                <th class="py-2.5 px-2 text-right whitespace-nowrap w-20 no-sort">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-xs font-semibold text-slate-800">
                            @forelse($revisionRequests as $index => $req)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-2.5 px-2 text-center font-bold text-slate-400 text-[11px] align-middle">
                                    {{ $revisionRequests->firstItem() + $index }}
                                </td>

                                <!-- Dokumen SOP & Identitas -->
                                <td class="py-2.5 px-3 align-middle">
                                    @php
                                        $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];
                                        $isSupport = in_array(strtoupper($req->document?->department ?? ''), $supportDepts);
                                        $detailUrl = $req->document ? ($isSupport ? route('admin.support.document.detail', $req->document->id) : route('admin.BU.detail', $req->document->id)) : null;
                                    @endphp
                                    <div class="font-bold text-slate-900 leading-snug">
                                        @if($detailUrl)
                                            <a href="{{ $detailUrl }}" class="hover:text-[#1677B8] hover:underline transition-colors cursor-pointer">{{ $req->document->title ?? 'SOP' }}</a>
                                        @else
                                            {{ $req->document->title ?? 'SOP' }}
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1 text-[10px] text-slate-500">
                                        <span class="font-mono font-semibold">{{ $req->document->doc_number ?? 'No. Belum Diatur' }}</span>
                                        <span>&bull;</span>
                                        <span class="font-mono">Rev {{ $req->document->doc_revision ?? '0' }}</span>
                                        <span>&bull;</span>
                                        <span class="font-bold text-[#1677B8]">{{ $req->document->department ?? '-' }}</span>
                                    </div>
                                </td>

                                <!-- Pemohon & Tanggal -->
                                <td class="py-2.5 px-3 align-middle">
                                    <span class="block font-bold text-slate-900">{{ $req->user->full_name ?? ($req->user->username ?? 'User') }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $req->created_at ? $req->created_at->format('d M Y - H:i') . ' WIB' : '-' }}</span>
                                </td>

                                <!-- Alasan Permohonan -->
                                <td class="py-2.5 px-3 align-middle max-w-xs">
                                    <p class="text-xs text-slate-700 leading-relaxed line-clamp-2" title="{{ $req->reason }}">
                                        {{ $req->reason }}
                                    </p>
                                    @if($req->admin_notes)
                                        <div class="text-[10.5px] text-slate-500 mt-1 border-t border-slate-100 pt-1">
                                            <strong>Catatan Admin:</strong> {{ $req->admin_notes }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Status & SLA -->
                                <td class="py-2.5 px-3 text-center whitespace-nowrap align-middle">
                                    @if($req->status === 'pending')
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700">
                                            <i class="ph ph-hourglass-high text-amber-600 text-sm"></i>
                                            <span>Pending (Menunggu)</span>
                                        </span>
                                    @elseif($req->status === 'approved')
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700">
                                            <i class="ph ph-check-circle text-emerald-600 text-sm"></i>
                                            <span>Disetujui</span>
                                        </span>
                                    @elseif($req->status === 'rejected')
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-700">
                                            <i class="ph ph-x-circle text-rose-600 text-sm"></i>
                                            <span>Ditolak</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi Admin -->
                                <td class="py-2.5 px-2 text-right whitespace-nowrap align-middle">
                                    @if($req->status === 'pending')
                                        <div class="flex items-center justify-end gap-1.5">
                                            <x-interactive-button text="Tinjau" variant="outline" icon="ph ph-gavel text-[#1677B8]" type="button" onclick="openReviewModal('{{ $req->id }}', '{{ addslashes($req->document->title ?? '') }}', '{{ addslashes($req->user->full_name ?? $req->user->username) }}', '{{ addslashes($req->reason) }}')" />
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-400 font-bold">Selesai Ditinjau</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400">
                                    <i class="ph ph-folder-open text-3xl text-slate-300 mb-1.5 block"></i>
                                    <p class="text-xs font-bold text-slate-700">Belum ada data permohonan revisi yang cocok.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($revisionRequests->hasPages())
                    <div class="pt-2">
                        {{ $revisionRequests->links() }}
                    </div>
                @endif
            </div>

        <!-- ========================================================================= -->
        <!-- TAB 2 CONTENT: BUKTI SOSIALISASI SOP -->
        <!-- ========================================================================= -->
        @elseif($activeTab === 'socialization')
            <div class="space-y-4">
                <!-- STATS BENTO CARDS -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="bg-white p-4 rounded-[2px] border border-slate-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Total Bukti Diunggah</span>
                            <h3 class="text-xl font-black text-slate-800">{{ $totalSocializationCount }} <span class="text-xs font-semibold text-slate-500">Kegiatan</span></h3>
                        </div>
                        <div class="w-9 h-9 rounded-[2px] bg-blue-50 text-[#1677B8] border border-blue-200 flex items-center justify-center font-bold">
                            <i class="ph ph-users-three text-lg"></i>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-[2px] border border-slate-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Menunggu Verifikasi Admin</span>
                            <h3 class="text-xl font-black text-amber-700">{{ $pendingSocializationCount }} <span class="text-xs font-semibold text-slate-500">Dalam Antrean</span></h3>
                        </div>
                        <div class="w-9 h-9 rounded-[2px] bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center font-bold">
                            <i class="ph ph-clock text-lg"></i>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-[2px] border border-slate-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Terverifikasi & Sah</span>
                            <h3 class="text-xl font-black text-emerald-700">{{ $verifiedSocializationCount }} <span class="text-xs font-semibold text-slate-500">Selesai</span></h3>
                        </div>
                        <div class="w-9 h-9 rounded-[2px] bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold">
                            <i class="ph ph-seal-check text-lg"></i>
                        </div>
                    </div>
                </div>

                <!-- HEADER BANNER BIRU MUDA + TOMBOL UNGGAH -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3.5 rounded-[2px]">
                    <div class="flex items-center space-x-2 text-xs font-bold text-[#1677B8]">
                        <i class="ph ph-camera text-base"></i>
                        <span class="capitalize text-slate-900 font-extrabold">Daftar Bukti Pelaksanaan Sosialisasi SOP oleh Unit</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-[#1677B8] font-bold bg-white px-2.5 py-1 rounded-[2px] border border-blue-200">
                            Total {{ $socializations->total() }} Data
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-[2px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-[#1677B8] border-b border-[#1258a0] text-[10.5px] font-extrabold uppercase tracking-wider text-white">
                            <tr>
                                <th class="py-2.5 px-2 text-center w-8 whitespace-nowrap">No</th>
                                <th class="py-2.5 px-2.5 whitespace-nowrap">Dokumen SOP & Unit</th>
                                <th class="py-2.5 px-2.5 whitespace-nowrap w-36">PIC & Tgl Sosialisasi</th>
                                <th class="py-2.5 px-2.5 text-center whitespace-nowrap w-32">Berkas Bukti</th>
                                <th class="py-2.5 px-2.5 whitespace-nowrap">Catatan Kegiatan</th>
                                <th class="py-2.5 px-2.5 text-center whitespace-nowrap w-32">Status Verifikasi</th>
                                <th class="py-2.5 px-2 text-right whitespace-nowrap w-16 no-sort">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-xs font-semibold text-slate-800">
                            @forelse($socializations as $index => $soc)
                            @php
                                $photoCount = is_array($soc->photos) ? count($soc->photos) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-2.5 px-2 text-center font-bold text-slate-400 text-[11px] align-middle">
                                    {{ $socializations->firstItem() + $index }}
                                </td>

                                <!-- Dokumen SOP & Unit -->
                                <td class="py-2.5 px-3 align-middle">
                                    @php
                                        $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];
                                        $isSupport = in_array(strtoupper($soc->document?->department ?? ''), $supportDepts);
                                        $detailUrl = $soc->document ? ($isSupport ? route('admin.support.document.detail', $soc->document->id) : route('admin.BU.detail', $soc->document->id)) : null;
                                    @endphp
                                    <div class="font-bold text-slate-900 leading-snug">
                                        @if($detailUrl)
                                            <a href="{{ $detailUrl }}" class="hover:text-[#1677B8] hover:underline transition-colors cursor-pointer">{{ $soc->document->title ?? 'SOP' }}</a>
                                        @else
                                            {{ $soc->document->title ?? 'SOP' }}
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1 text-[10px] text-slate-500">
                                        <span class="font-mono font-semibold">{{ $soc->document->doc_number ?? 'No. Belum Diatur' }}</span>
                                        <span>&bull;</span>
                                        <span class="font-bold text-[#1677B8]">{{ $soc->document->department ?? '-' }}</span>
                                    </div>
                                </td>

                                <!-- PIC & Tgl Sosialisasi -->
                                <td class="py-2.5 px-3 align-middle whitespace-nowrap">
                                    <span class="block font-bold text-slate-900">{{ $soc->user->full_name ?? ($soc->user->username ?? 'PIC') }}</span>
                                    <span class="text-[10px] text-slate-500">Tgl: {{ $soc->socialization_date ? $soc->socialization_date->format('d M Y') : '-' }}</span>
                                </td>

                                <!-- Berkas Bukti (Daftar Hadir & Foto) -->
                                <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($soc->attendance_file)
                                            <a href="{{ asset('storage/' . $soc->attendance_file) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 bg-white hover:bg-slate-50 border border-slate-300 rounded-[2px] text-[10px] font-bold text-slate-800 shadow-xs transition-colors" title="Buka Berkas Lembar Daftar Hadir">
                                                <i class="ph ph-file-pdf text-red-600"></i>
                                                <span>PDF</span>
                                            </a>
                                        @endif
                                        <button type="button" onclick="openViewSocializationModal('{{ $soc->id }}')" class="inline-flex items-center gap-1 px-2 py-1 bg-white hover:bg-slate-50 border border-slate-300 rounded-[2px] text-[10px] font-bold text-slate-800 cursor-pointer shadow-xs" title="Lihat Foto Dokumentasi">
                                            <i class="ph ph-images text-[#1677B8]"></i>
                                            <span>{{ $photoCount }} Foto</span>
                                        </button>
                                    </div>
                                </td>

                                <!-- Catatan Kegiatan -->
                                <td class="py-2.5 px-3 align-middle max-w-xs">
                                    <p class="text-xs text-slate-700 leading-relaxed line-clamp-2">
                                        {{ $soc->notes ?: 'Sosialisasi SOP berjalan lancar.' }}
                                    </p>
                                </td>

                                <!-- Status Verifikasi -->
                                <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                                    @if($soc->status === 'verified')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-[2px] text-[10px] font-extrabold uppercase">
                                            <i class="ph ph-seal-check text-xs"></i>
                                            <span>Terverifikasi</span>
                                        </span>
                                    @elseif($soc->status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-[2px] text-[10px] font-extrabold uppercase">
                                            <i class="ph ph-x-circle text-xs"></i>
                                            <span>Perlu Revisi</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-[2px] text-[10px] font-extrabold uppercase">
                                            <i class="ph ph-clock text-xs"></i>
                                            <span>Menunggu Verifikasi</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-2.5 px-2 text-right whitespace-nowrap align-middle">
                                    <x-interactive-button text="Detail" variant="outline" icon="ph ph-eye text-[#1677B8]" type="button" onclick="openViewSocializationModal('{{ $soc->id }}')" class="ml-auto" />
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    <i class="ph ph-folder-open text-3xl text-slate-300 mb-1.5 block"></i>
                                    <p class="text-xs font-bold text-slate-700">Belum ada data bukti sosialisasi yang diunggah.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($socializations->hasPages())
                    <div class="pt-2">
                        {{ $socializations->links() }}
                    </div>
                @endif
            </div>

        <!-- ========================================================================= -->
        <!-- TAB 3 CONTENT: REKAP KUIS PEMAHAMAN SOP (1 SOP PER BARIS) -->
        <!-- ========================================================================= -->
        @elseif($activeTab === 'quiz')
            <div class="space-y-4 w-full">
                <!-- SUB-FILTER KATEGORI KUIS (PILL SELECTOR) -->
                <div class="flex flex-wrap items-center gap-2 p-1.5 bg-slate-100/80 border border-slate-200 rounded-[2px] w-full">
                    <span class="text-[11px] font-medium text-slate-500 pl-2 pr-1 whitespace-nowrap">Kategori Kuis:</span>
                    
                    <!-- Opsi 1: Semua Sumber -->
                    <a href="{{ route('admin.user_reviews.index', ['tab' => 'quiz', 'quiz_category' => 'all', 'status' => $status, 'search' => $search]) }}" 
                       class="px-3 py-1.5 rounded-[2px] text-xs transition-all flex items-center gap-1.5 whitespace-nowrap {{ $quizCategory === 'all' ? 'bg-white text-[#1677B8] font-semibold shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900 font-normal hover:bg-white/60' }}">
                        <i class="ph ph-squares-four text-sm"></i>
                        <span>Semua Sumber</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $quizCategory === 'all' ? 'bg-blue-50 text-[#1677B8] font-semibold' : 'bg-slate-200/80 text-slate-600 font-medium' }}">{{ $totalAllQuizTakersCount }}</span>
                    </a>

                    <!-- Opsi 2: Kuis Berkala 6 Bulan -->
                    <a href="{{ route('admin.user_reviews.index', ['tab' => 'quiz', 'quiz_category' => 'periodic', 'status' => $status, 'search' => $search]) }}" 
                       class="px-3 py-1.5 rounded-[2px] text-xs transition-all flex items-center gap-1.5 whitespace-nowrap {{ $quizCategory === 'periodic' ? 'bg-white text-[#1677B8] font-semibold shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900 font-normal hover:bg-white/60' }}">
                        <i class="ph ph-calendar-check text-sm text-[#1677B8]"></i>
                        <span>Kuis Berkala 6 Bulan (User / Internal)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $quizCategory === 'periodic' ? 'bg-blue-50 text-[#1677B8] font-semibold' : 'bg-slate-200/80 text-slate-600 font-medium' }}">{{ $totalPeriodicTakersCount }}</span>
                    </a>

                    <!-- Opsi 3: Kuis Sosialisasi / Presensi QR -->
                    <a href="{{ route('admin.user_reviews.index', ['tab' => 'quiz', 'quiz_category' => 'socialization', 'status' => $status, 'search' => $search]) }}" 
                       class="px-3 py-1.5 rounded-[2px] text-xs transition-all flex items-center gap-1.5 whitespace-nowrap {{ $quizCategory === 'socialization' ? 'bg-white text-[#1677B8] font-semibold shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900 font-normal hover:bg-white/60' }}">
                        <i class="ph ph-qr-code text-sm text-sky-600"></i>
                        <span>Kuis Sosialisasi (Daftar Hadir / Presensi QR)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $quizCategory === 'socialization' ? 'bg-blue-50 text-[#1677B8] font-semibold' : 'bg-slate-200/80 text-slate-600 font-medium' }}">{{ $totalSocializationTakersCount }}</span>
                    </a>
                </div>

                <!-- HEADER BANNER BIRU MUDA -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3 rounded-[2px] w-full">
                    <div class="flex items-center space-x-2 text-xs font-semibold text-[#1677B8]">
                        <i class="ph ph-exam text-base"></i>
                        <span class="text-slate-800 font-semibold">
                            Rekap Evaluasi Kuis Pemahaman SOP 
                            <span class="font-normal text-slate-500">(Standar KKM 60 Poin • 
                            @if($quizCategory === 'periodic')
                                Kategori: Kuis Berkala 6 Bulan
                            @elseif($quizCategory === 'socialization')
                                Kategori: Kuis Sosialisasi Presensi QR
                            @else
                                Kategori: Semua Sumber Ujian
                            @endif
                            )</span>
                        </span>
                    </div>
                    <span class="text-xs text-slate-600 font-medium">Menampilkan {{ $quizSops->count() }} dari {{ $quizSops->total() }} Dokumen SOP</span>
                </div>

                <!-- TABEL REKAP KUIS (FIT FULL WIDTH) -->
                <div class="overflow-x-auto border border-slate-200 rounded-[2px] w-full">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-[#1677B8] border-b border-[#1258a0] text-[11px] font-semibold text-white">
                            <tr>
                                <th class="py-2.5 px-3 text-center w-10 whitespace-nowrap">No</th>
                                <th class="py-2.5 px-3 whitespace-nowrap">Dokumen SOP & Identitas</th>
                                <th class="py-2.5 px-3 text-center whitespace-nowrap w-36">Total Peserta</th>
                                <th class="py-2.5 px-3 text-center whitespace-nowrap w-44">Rasio Lulus & Skor</th>
                                <th class="py-2.5 px-3 text-center whitespace-nowrap w-44">Status Kelulusan</th>
                                <th class="py-2.5 px-3 text-right whitespace-nowrap w-36 no-sort">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-xs font-normal text-slate-800">
                            @forelse($quizSops as $index => $sop)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-2.5 px-3 text-center text-slate-400 text-[11px] align-middle">
                                    {{ $quizSops->firstItem() + $index }}
                                </td>

                                <!-- Dokumen SOP & Identitas -->
                                <td class="py-2.5 px-3 align-middle">
                                    @php
                                        $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];
                                        $isSupport = in_array(strtoupper($sop->department ?? ''), $supportDepts);
                                        $detailUrl = $isSupport ? route('admin.support.document.detail', $sop->id) : route('admin.BU.detail', $sop->id);
                                    @endphp
                                    <div class="font-semibold text-slate-900 leading-snug">
                                        <a href="{{ $detailUrl }}" class="hover:text-[#1677B8] hover:underline transition-colors cursor-pointer">{{ $sop->title }}</a>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5 text-[11px] text-slate-500 font-normal">
                                        <span class="font-mono text-slate-600">{{ $sop->doc_number ?? 'No. Belum Diatur' }}</span>
                                        <span>&bull;</span>
                                        <span class="text-slate-600">{{ $sop->department ?? '-' }}</span>
                                        <span>&bull;</span>
                                        <span class="text-slate-500">{{ $sop->quiz?->questions?->count() ?? 15 }} Soal PG</span>
                                    </div>
                                    @if($quizCategory === 'all')
                                    <div class="flex items-center gap-2 mt-1 text-[10px] text-slate-500 font-normal">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#1677B8]"></span>
                                            <span>Berkala: <strong class="font-medium text-slate-700">{{ $sop->periodic_count }}</strong></span>
                                        </span>
                                        <span>&bull;</span>
                                        <span class="inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                            <span>Sosialisasi: <strong class="font-medium text-slate-700">{{ $sop->socialization_count }}</strong></span>
                                        </span>
                                    </div>
                                    @endif
                                </td>

                                <!-- Total Peserta Ujian -->
                                <td class="py-2.5 px-3 text-center whitespace-nowrap align-middle">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium rounded-[2px]">
                                        <i class="ph ph-users text-[#1677B8]"></i>
                                        <span>{{ $sop->quiz_total_takers }} Peserta</span>
                                    </span>
                                </td>

                                <!-- Rasio Lulus & Rata-rata Nilai -->
                                <td class="py-2.5 px-3 text-center whitespace-nowrap align-middle">
                                    @if($sop->quiz_total_takers > 0)
                                        <div class="text-xs font-medium">
                                            <span class="text-emerald-700">{{ $sop->quiz_passed_count }} Lulus</span>
                                            <span class="text-slate-300 mx-1">|</span>
                                            <span class="{{ $sop->quiz_failed_count > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $sop->quiz_failed_count }} Remedial</span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-normal mt-0.5">
                                            Rata-rata: <span class="font-semibold text-slate-800">{{ $sop->quiz_avg_score }}</span> / 100
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-400 font-normal">Belum ada nilai</span>
                                    @endif
                                </td>

                                <!-- Status Kelulusan Keseluruhan SOP -->
                                <td class="py-2.5 px-3 text-center whitespace-nowrap align-middle">
                                    @if($sop->quiz_overall_status === 'all_passed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-medium rounded-[2px]">
                                            <i class="ph ph-check-circle text-emerald-600 text-sm"></i>
                                            <span>Semua Lulus (≥60)</span>
                                        </span>
                                    @elseif($sop->quiz_overall_status === 'remedial')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-medium rounded-[2px]">
                                            <i class="ph ph-warning-circle text-rose-600 text-sm"></i>
                                            <span>Perlu Remedial ({{ $sop->quiz_failed_count }} Gagal)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-50 text-slate-500 border border-slate-200 text-[11px] font-normal rounded-[2px]">
                                            <i class="ph ph-clock text-slate-400 text-sm"></i>
                                            <span>Belum Ada Peserta</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-2.5 px-3 text-right whitespace-nowrap align-middle space-x-1">
                                    <button type="button" 
                                            onclick='openQuizParticipantsModal(@json($sop->id), @json($sop->title), @json($sop->doc_number ?? "-"), @json($sop->department ?? "-"), @json($sop->all_quiz_participants), @json($sop->quiz_overall_status), @json($quizCategory))'
                                            class="inline-flex items-center gap-1 h-7 px-2.5 text-xs font-medium text-[#1677B8] bg-white hover:bg-blue-50 border border-blue-200 rounded-[2px] transition-all cursor-pointer shadow-2xs" 
                                            title="Lihat Daftar Peserta & Nilai">
                                        <i class="ph ph-users text-[#1677B8]"></i>
                                        <span>Peserta ({{ $sop->quiz_total_takers }})</span>
                                    </button>

                                    <a href="{{ route('documents.quiz.show', $sop->id) }}" 
                                       class="inline-flex items-center gap-1 h-7 px-2.5 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-[2px] transition-all shadow-2xs" 
                                       title="Lihat Bank Soal">
                                        <i class="ph ph-exam text-[#1677B8]"></i>
                                        <span>Bank Soal</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400">
                                    <i class="ph ph-folder-open text-3xl text-slate-300 mb-1.5 block"></i>
                                    <p class="text-xs font-medium text-slate-600">Belum ada data SOP yang sesuai dengan kategori ini.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($quizSops->hasPages())
                    <div class="pt-2">
                        {{ $quizSops->links() }}
                    </div>
                @endif
            </div>

        <!-- TAB 4: USULAN SOP BARU -->
        @elseif($activeTab === 'new_sop')
            <div class="space-y-3">
                <div class="overflow-x-auto rounded-[2px] border border-slate-200">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-[#1677B8] border-b border-[#1258a0] text-[10px] font-extrabold text-white uppercase tracking-wider">
                                <th class="py-2.5 px-3 w-10 text-center">No</th>
                                <th class="py-2.5 px-3">Nama Usulan SOP</th>
                                <th class="py-2.5 px-3">Pemohon</th>
                                <th class="py-2.5 px-3">Divisi / Dept</th>
                                <th class="py-2.5 px-3">Deskripsi & Kebutuhan</th>
                                <th class="py-2.5 px-3 text-center">Lampiran</th>
                                <th class="py-2.5 px-3 text-center">Status</th>
                                <th class="py-2.5 px-3 text-center">Aksi & Pembuatan SOP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($newSopRequests as $idx => $req)
                                @php
                                    $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA', 'SEKRETARIS', 'FINANCE', 'LEGAL'];
                                    $isSupport = in_array(strtoupper($req->department), $supportDepts);
                                    $createUrl = $isSupport 
                                        ? route('admin.support.create', ['department' => $req->department, 'from_request_id' => $req->id])
                                        : route('admin.BU.create', ['unit' => $req->department, 'from_request_id' => $req->id]);
                                    $docDetailUrl = $req->document 
                                        ? ($isSupport ? route('admin.support.document.detail', $req->document->id) : route('admin.BU.detail', $req->document->id))
                                        : null;
                                @endphp
                                <tr class="hover:bg-blue-50/40 transition-colors">
                                    <td class="py-3 px-3 text-center text-slate-400 font-bold">
                                        {{ $newSopRequests->firstItem() + $idx }}
                                    </td>
                                    <td class="py-3 px-3 font-bold text-slate-800 max-w-xs">
                                        <div class="font-bold text-slate-900 leading-snug">{{ $req->title }}</div>
                                        @if($req->document && $docDetailUrl)
                                            <div class="mt-1 flex items-center gap-1.5 text-[10px]">
                                                <a href="{{ $docDetailUrl }}" class="text-[#1677B8] hover:text-[#1258a0] font-bold flex items-center gap-1 hover:underline" title="Klik untuk melacak alur persetujuan dokumen secara langsung">
                                                    <i class="ph ph-link text-xs"></i>
                                                    <span>Dokumen: {{ $req->document->doc_number ?? 'SOP Terkait' }}</span>
                                                    <i class="ph ph-arrow-square-out text-[9px]"></i>
                                                </a>
                                            </div>
                                        @endif
                                        @if($req->revision_notes)
                                            <div class="mt-1 p-1.5 bg-orange-50 border border-orange-200 rounded-[2px] text-[10px] text-orange-800">
                                                <strong>Catatan Revisi:</strong> {{ $req->revision_notes }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-slate-700 whitespace-nowrap">
                                        <span class="font-bold block">{{ $req->user->full_name ?? ($req->user->username ?? 'User') }}</span>
                                        <span class="text-[10px] text-slate-400 block">{{ $req->created_at->format('d M Y H:i') }}</span>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-[2px] font-bold text-[10.5px] border border-slate-200">
                                            {{ $req->department }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-slate-600 max-w-xs">
                                        <p class="line-clamp-2" title="{{ $req->description }}">{{ $req->description }}</p>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if($req->attachment_file)
                                            <a href="{{ asset('storage/' . $req->attachment_file) }}" target="_blank" class="inline-flex items-center gap-1 text-[#1677B8] font-bold hover:underline" title="Unduh Berkas Lampiran">
                                                <i class="ph ph-file-arrow-down text-base"></i>
                                                <span>Unduh</span>
                                            </a>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        @if($req->status === 'completed')
                                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-[2px] font-bold text-[10.5px] inline-flex items-center gap-1">
                                                <i class="ph ph-check-circle"></i>
                                                <span>SOP Terbit & Sah</span>
                                            </span>
                                        @elseif($req->status === 'approved')
                                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-[2px] font-bold text-[10.5px] inline-flex items-center gap-1">
                                                <i class="ph ph-check"></i>
                                                <span>Disetujui</span>
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

                                                <button type="button" onclick="openRejectNewSopModal({{ $req->id }}, '{{ addslashes($req->title) }}')" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-[10.5px] rounded-[2px] shadow-2xs inline-flex items-center gap-1 cursor-pointer transition-all" title="Tolak Usulan">
                                                    <i class="ph ph-x text-xs"></i>
                                                    <span>Tolak</span>
                                                </button>
                                            @elseif($req->status === 'in_progress')
                                                <!-- 2. TAHAP DIPROSES: BUAT SOP ATAU LACAK APPROVAL -->
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
                                    <td colspan="8" class="text-center py-10 text-slate-400">
                                        <i class="ph ph-file-plus text-3xl mb-1 block text-slate-300"></i>
                                        <span class="text-xs font-bold text-slate-700">Tidak ada data usulan SOP baru.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($newSopRequests->hasPages())
                    <div class="pt-2">
                        {{ $newSopRequests->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: TINJAU & PUTUSKAN PERMOHONAN REVISI (ADMIN) -->
<!-- ========================================================================= -->
<div id="reviewRevisionModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <!-- MODAL HEADER -->
        <div class="px-5 py-4 bg-gradient-to-r from-[#1677B8] via-[#1260a0] to-[#0f4c81] text-white flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-[2px] bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-git-pull-request text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black tracking-wide">Tinjau Permohonan Revisi SOP</h3>
                    <p class="text-[10.5px] text-white/80 font-medium">Evaluasi usulan perubahan dokumen dari pengguna</p>
                </div>
            </div>
            <button type="button" onclick="closeReviewModal()" class="text-white/80 hover:text-white text-lg cursor-pointer bg-transparent border-none">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <!-- MODAL BODY -->
        <div class="p-6 space-y-4">
            <!-- INFO DOKUMEN & PEMOHON -->
            <div class="bg-slate-50 p-4 rounded-[2px] border border-slate-200/80 space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Dokumen SOP</span>
                        <h4 id="revModalDocTitle" class="font-extrabold text-slate-900 text-sm leading-snug mt-0.5"></h4>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block">Diajukan Oleh</span>
                        <span id="revModalUser" class="font-bold text-[#1677B8]"></span>
                    </div>
                    <span class="px-2 py-0.5 rounded-[2px] bg-blue-50 text-blue-700 border border-blue-200 text-[10.5px] font-bold">
                        Permohonan Revisi
                    </span>
                </div>
            </div>

            <!-- ALASAN DARI PEMOHON -->
            <div class="bg-amber-50/80 border border-amber-200/90 p-4 rounded-[2px] space-y-1.5 shadow-2xs">
                <div class="flex items-center gap-1.5 text-amber-900">
                    <i class="ph ph-chat-centered-text text-base text-amber-700"></i>
                    <span class="font-extrabold text-[11px] uppercase tracking-wider">Alasan Permohonan User:</span>
                </div>
                <p id="revModalReason" class="text-xs font-semibold text-amber-950 leading-relaxed whitespace-pre-line pl-0.5"></p>
            </div>

            <!-- FORM PERSETUJUAN / AKSI -->
            <form id="approveRevisionForm" method="POST" action="" class="space-y-4 pt-1">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        <span>Catatan / Arahan Admin</span>
                        <span class="text-[10.5px] font-normal text-slate-400 ml-1">(Opsional untuk persetujuan)</span>
                    </label>
                    <textarea name="admin_notes" id="approve_admin_notes" rows="2" placeholder="Tulis instruksi tambahan, fokus revisi, atau catatan untuk pemohon..." 
                              class="w-full p-3 bg-slate-50 border border-slate-300 rounded-[2px] text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-[#1677B8] focus:border-transparent outline-none transition-all"></textarea>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="flex items-center justify-between gap-3 pt-3 border-t border-slate-200">
                    <x-interactive-button text="Tolak" variant="danger" icon="ph ph-x-circle text-base" type="button" onclick="openRejectRevisionModalFromReview()" />
                    <x-interactive-button text="Konfirmasi" variant="success" icon="ph ph-check-circle text-base" type="submit" />
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1B: POP-UP KONFIRMASI PENOLAKAN REVISI (WAJIB ALASAN) -->
<!-- ========================================================================= -->
<div id="rejectRevisionModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <!-- MODAL HEADER -->
        <div class="px-5 py-4 bg-gradient-to-r from-rose-600 to-rose-700 text-white flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-[2px] bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-x-circle text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black tracking-wide">Tolak Permohonan Revisi</h3>
                    <p class="text-[10.5px] text-white/80 font-medium">Berikan alasan penolakan secara jelas</p>
                </div>
            </div>
            <button type="button" onclick="closeRejectRevisionModal()" class="text-white/80 hover:text-white text-lg cursor-pointer bg-transparent border-none">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <!-- FORM PENOLAKAN -->
        <form id="rejectRevisionForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Dokumen SOP</span>
                <p id="rejectModalDocTitle" class="text-xs font-bold text-slate-900 bg-slate-50 p-2.5 rounded-[2px] border border-slate-200 leading-snug"></p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    <span>Alasan Penolakan</span>
                    <span class="text-rose-500 font-bold">*</span>
                </label>
                <textarea name="admin_notes" id="reject_admin_notes" rows="3" required placeholder="Tuliskan alasan mengapa permohonan revisi ini ditolak (catatan ini akan dikirimkan kepada pemohon)..." 
                          class="w-full p-3 bg-slate-50 border border-slate-300 rounded-[2px] text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-transparent outline-none transition-all"></textarea>
                <p class="text-[10.5px] text-slate-500 mt-1">Alasan penolakan wajib diisi untuk transparansi kepada pemohon.</p>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200">
                <x-interactive-button text="Batal" variant="outline" type="button" onclick="backToReviewModalFromReject()" />
                <x-interactive-button text="Konfirmasi" variant="danger" icon="ph ph-x text-base" type="submit" />
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: DETAIL & VERIFIKASI BUKTI SOSIALISASI SOP (ADMIN) -->
<!-- ========================================================================= -->
<div id="viewSocializationModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-3xl w-full overflow-hidden max-h-[92vh] flex flex-col animate-in fade-in zoom-in duration-200">
        <!-- MODAL HEADER -->
        <div class="p-4 bg-gradient-to-r from-[#1677B8] via-[#1260a0] to-[#0f4c81] text-white flex items-center justify-between flex-shrink-0 shadow-sm">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-[2px] bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-camera text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Detail & Verifikasi Sosialisasi SOP</h3>
                    <p class="text-[10.5px] text-white/80 font-medium">Pemeriksaan bukti daftar hadir dan dokumentasi pelaksanaan kegiatan unit</p>
                </div>
            </div>
            <button type="button" onclick="closeViewSocializationModal()" class="text-white/80 hover:text-white text-lg cursor-pointer bg-transparent border-none">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <!-- MODAL BODY -->
        <div id="viewSocContent" class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-5">
            <!-- Dynamic Content loaded via AJAX -->
        </div>

        <!-- MODAL FOOTER -->
        <div id="viewSocFooter" class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between flex-shrink-0">
            <!-- Verification buttons loaded dynamically based on status -->
        </div>
    </div>
</div>

<script>
let currentActiveRevisionData = {
    id: null,
    docTitle: '',
    userName: '',
    reason: ''
};

function openReviewModal(reqId, docTitle, userName, reason) {
    currentActiveRevisionData = { id: reqId, docTitle, userName, reason };

    const modal = document.getElementById('reviewRevisionModal');
    const approveForm = document.getElementById('approveRevisionForm');
    const titleEl = document.getElementById('revModalDocTitle');
    const userEl = document.getElementById('revModalUser');
    const reasonEl = document.getElementById('revModalReason');
    const notesEl = document.getElementById('approve_admin_notes');

    approveForm.action = `/admin/revision-requests/${reqId}/approve`;
    titleEl.textContent = docTitle;
    userEl.textContent = userName;
    reasonEl.textContent = reason;
    if (notesEl) notesEl.value = '';

    modal.classList.remove('hidden');
}

function closeReviewModal() {
    const modal = document.getElementById('reviewRevisionModal');
    modal.classList.add('hidden');
}

function openRejectRevisionModalFromReview() {
    closeReviewModal();

    const rejectModal = document.getElementById('rejectRevisionModal');
    const rejectForm = document.getElementById('rejectRevisionForm');
    const titleEl = document.getElementById('rejectModalDocTitle');
    const notesEl = document.getElementById('reject_admin_notes');

    rejectForm.action = `/admin/revision-requests/${currentActiveRevisionData.id}/reject`;
    titleEl.textContent = currentActiveRevisionData.docTitle;
    if (notesEl) {
        notesEl.value = '';
        setTimeout(() => notesEl.focus(), 150);
    }

    rejectModal.classList.remove('hidden');
}

function closeRejectRevisionModal() {
    const rejectModal = document.getElementById('rejectRevisionModal');
    rejectModal.classList.add('hidden');
}

function backToReviewModalFromReject() {
    closeRejectRevisionModal();
    const modal = document.getElementById('reviewRevisionModal');
    modal.classList.remove('hidden');
}

function openViewSocializationModal(docId) {
    const modal = document.getElementById('viewSocializationModal');
    const content = document.getElementById('viewSocContent');
    const footer = document.getElementById('viewSocFooter');
    modal.classList.remove('hidden');

    content.innerHTML = `
        <div class="text-center py-12">
            <i class="ph ph-spinner animate-spin text-4xl text-[#1677B8]"></i>
            <p class="text-xs text-slate-500 mt-2.5 font-bold">Memuat rincian lengkap bukti sosialisasi...</p>
        </div>
    `;
    footer.innerHTML = `
        <button type="button" onclick="closeViewSocializationModal()" class="px-4 py-2 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs rounded-[2px] border border-slate-300 ml-auto cursor-pointer">
            Tutup
        </button>
    `;

    fetch(`/admin/socializations/${docId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.socialization) {
                const s = data.socialization;

                // Status Badge
                let statusBadge = '';
                if (s.status === 'verified') {
                    statusBadge = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-300 rounded-[2px] text-xs font-black uppercase tracking-wider shadow-2xs">
                            <i class="ph ph-seal-check text-sm"></i> Terverifikasi & Sah
                        </span>
                    `;
                } else if (s.status === 'rejected') {
                    statusBadge = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 border border-rose-300 rounded-[2px] text-xs font-black uppercase tracking-wider shadow-2xs">
                            <i class="ph ph-x-circle text-sm"></i> Perlu Revisi / Ditolak
                        </span>
                    `;
                } else {
                    statusBadge = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 border border-amber-300 rounded-[2px] text-xs font-black uppercase tracking-wider shadow-2xs">
                            <i class="ph ph-clock text-sm"></i> Menunggu Verifikasi Admin
                        </span>
                    `;
                }

                // Photos Gallery
                let photosHtml = '';
                const photosList = s.photos || [];
                if (photosList.length > 0) {
                    photosHtml = `
                        <div class="space-y-2.5 pt-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                                    <i class="ph ph-images text-base text-[#1677B8]"></i>
                                    <span>Galeri Foto Dokumentasi Kegiatan (${photosList.length} Foto)</span>
                                </label>
                                <span class="text-[10px] text-slate-500 font-semibold">Klik foto untuk melihat ukuran penuh</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                ${photosList.map((url, idx) => `
                                    <a href="${url}" target="_blank" class="group block rounded-[2px] overflow-hidden border border-slate-200 shadow-2xs relative aspect-video bg-slate-100 transition-all hover:border-[#1677B8] hover:shadow-md">
                                        <img src="${url}" alt="Dokumentasi ${idx + 1}" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300">
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-[11px] font-extrabold gap-1">
                                            <i class="ph ph-magnifying-glass-plus text-base"></i>
                                            <span>Lihat Penuh</span>
                                        </div>
                                        <span class="absolute bottom-1.5 right-1.5 px-1.5 py-0.5 bg-black/60 text-white text-[9px] font-bold rounded-[2px]">
                                            #${idx + 1}
                                        </span>
                                    </a>
                                `).join('')}
                            </div>
                        </div>
                    `;
                } else {
                    photosHtml = `
                        <div class="p-4 bg-slate-50 rounded-[2px] border border-slate-200 text-center text-slate-500 text-xs font-medium">
                            Tidak ada foto dokumentasi yang dilampirkan.
                        </div>
                    `;
                }

                // Render Content
                content.innerHTML = `
                    <!-- 1. IDENTITAS DOKUMEN SOP -->
                    <div class="p-4 bg-gradient-to-r from-blue-50/70 to-slate-50 border border-blue-200 rounded-[2px] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-[#1677B8] block">Dokumen SOP Terkait</span>
                            <h4 class="text-sm font-black text-slate-900 leading-snug">${s.doc_title}</h4>
                            <div class="flex items-center gap-2 text-xs text-slate-600 font-semibold pt-0.5">
                                <span class="font-mono bg-white px-2 py-0.5 rounded-[2px] border border-slate-300 text-slate-800">${s.doc_number}</span>
                                <span>&bull;</span>
                                <span class="text-[#1677B8] font-bold">Unit: ${s.department}</span>
                                <span>&bull;</span>
                                <span class="text-slate-500">Rev. ${s.revision_number}</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            ${statusBadge}
                        </div>
                    </div>

                    <!-- 2. GRID INFO PELAKSANAAN & PIC -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="p-3.5 bg-white border border-slate-200 rounded-[2px] shadow-2xs space-y-1">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                                <i class="ph ph-calendar text-xs text-[#1677B8]"></i> Waktu & Tanggal Pelaksanaan
                            </span>
                            <div class="text-xs font-black text-slate-900">${s.socialization_date}</div>
                            <p class="text-[10.5px] text-slate-500 font-medium">Diunggah pada: ${s.created_at}</p>
                        </div>

                        <div class="p-3.5 bg-white border border-slate-200 rounded-[2px] shadow-2xs space-y-1">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                                <i class="ph ph-user text-xs text-[#1677B8]"></i> PIC / Penanggung Jawab Unit
                            </span>
                            <div class="text-xs font-black text-slate-900">${s.user_name}</div>
                            <p class="text-[10.5px] text-slate-500 font-medium">Jabatan: ${s.user_role}</p>
                        </div>
                    </div>

                    <!-- 3. CATATAN / NOTULEN KEGIATAN -->
                    <div class="p-4 bg-amber-50/60 border border-amber-200/80 rounded-[2px] space-y-1.5">
                        <div class="flex items-center gap-1.5 text-[11px] font-extrabold text-amber-900 uppercase tracking-wider">
                            <i class="ph ph-note-pencil text-sm text-amber-700"></i>
                            <span>Catatan & Ringkasan Kegiatan:</span>
                        </div>
                        <p class="text-xs text-slate-800 leading-relaxed font-medium whitespace-pre-line pl-5">
                            ${s.notes ? s.notes : 'Sosialisasi SOP telah selesai dilaksanakan kepada seluruh personel terkait.'}
                        </p>
                    </div>

                    <!-- 4. LEMBAR DAFTAR HADIR (PDF) -->
                    <div class="p-4 bg-white border border-slate-200 rounded-[2px] shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[2px] bg-red-50 text-red-600 border border-red-200 flex items-center justify-center font-bold flex-shrink-0">
                                <i class="ph ph-file-pdf text-2xl"></i>
                            </div>
                            <div>
                                <strong class="text-xs text-slate-900 font-extrabold block">Berkas Lembar Daftar Hadir Peserta</strong>
                                <p class="text-[10.5px] text-slate-500 font-medium">Dokumen bukti kehadiran resmi yang telah ditandatangani peserta</p>
                            </div>
                        </div>
                        ${s.attendance_file ? `
                            <a href="${s.attendance_file}" target="_blank" class="h-[34px] px-4 bg-[#1677B8] hover:bg-[#1260a0] text-white rounded-[2px] font-extrabold text-xs flex items-center justify-center gap-1.5 shadow-xs transition-all whitespace-nowrap">
                                <i class="ph ph-arrow-square-out text-sm"></i>
                                <span>Buka & Unduh PDF</span>
                            </a>
                        ` : `
                            <span class="text-xs text-slate-400 font-semibold italic">Tidak ada berkas daftar hadir</span>
                        `}
                    </div>

                    <!-- 5. GALERI FOTO -->
                    ${photosHtml}
                `;

                // Render Footer with Actions
                let actionButtons = '';
                if (s.status !== 'verified') {
                    actionButtons = `
                        <div class="flex items-center gap-2">
                            <form method="POST" action="${s.reject_url}" onsubmit="return handleRejectSocialization(event, this)">
                                @csrf
                                <input type="hidden" name="admin_notes" id="soc_reject_notes">
                                <button type="submit" class="px-4 py-2 bg-white hover:bg-rose-50 text-rose-700 font-extrabold text-xs rounded-[2px] border border-rose-300 shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer">
                                    <i class="ph ph-x-circle text-sm"></i>
                                    <span>Minta Revisi / Tolak</span>
                                </button>
                            </form>

                            <form method="POST" action="${s.verify_url}">
                                @csrf
                                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-[2px] shadow-sm transition-all flex items-center gap-1.5 cursor-pointer border-none">
                                    <i class="ph ph-seal-check text-sm"></i>
                                    <span>Sahkan & Verifikasi Sosialisasi</span>
                                </button>
                            </form>
                        </div>
                    `;
                } else {
                    actionButtons = `
                        <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-[2px] border border-emerald-200">
                            <i class="ph ph-check-circle text-sm"></i>
                            <span>Bukti Sosialisasi telah diverifikasi dan sah</span>
                        </div>
                    `;
                }

                footer.innerHTML = `
                    <button type="button" onclick="closeViewSocializationModal()" class="px-4 py-2 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs rounded-[2px] border border-slate-300 transition-all cursor-pointer">
                        Tutup
                    </button>
                    ${actionButtons}
                `;
            } else {
                content.innerHTML = `
                    <div class="text-center py-10 text-slate-500 text-xs font-bold">
                        <i class="ph ph-warning-circle text-3xl text-amber-500 mb-2 block"></i>
                        Data sosialisasi tidak ditemukan atau belum diunggah.
                    </div>
                `;
            }
        })
        .catch(() => {
            content.innerHTML = `
                <div class="text-center py-10 text-rose-500 text-xs font-bold">
                    <i class="ph ph-x-circle text-3xl text-rose-500 mb-2 block"></i>
                    Gagal memuat rincian data sosialisasi.
                </div>
            `;
        });
}

function handleRejectSocialization(e, form) {
    e.preventDefault();
    const reason = prompt('Masukkan catatan perbaikan / alasan penolakan bukti sosialisasi:');
    if (reason && reason.trim() !== '') {
        const input = form.querySelector('#soc_reject_notes');
        input.value = reason.trim();
        form.submit();
    }
}

function closeViewSocializationModal() {
    const modal = document.getElementById('viewSocializationModal');
    modal.classList.add('hidden');
}
</script>


<!-- ========================================================================= -->
<!-- MODAL: PENOLAKAN USULAN SOP BARU -->
<!-- ========================================================================= -->
<div id="rejectNewSopModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-[2px] shadow-xl border border-slate-200 max-w-md w-full overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-rose-50/50">
            <div class="flex items-center space-x-2 text-rose-800">
                <i class="ph ph-x-circle text-lg text-rose-600"></i>
                <h3 class="text-xs font-bold capitalize">Tolak Usulan SOP Baru</h3>
            </div>
            <button type="button" onclick="closeRejectNewSopModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer border-none bg-transparent">
                <i class="ph ph-x text-base"></i>
            </button>
        </div>

        <form id="rejectNewSopForm" method="POST" action="" class="p-5 space-y-4">
            @csrf
            <div>
                <p class="text-xs text-slate-600 font-semibold mb-1">Usulan SOP:</p>
                <p id="rejectNewSopTitle" class="text-xs font-bold text-slate-900 bg-slate-50 p-2 rounded-[2px] border border-slate-200"></p>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                <textarea name="admin_notes" id="rejectNewSopNotes" rows="3" required placeholder="Tuliskan alasan mengapa usulan SOP ini belum dapat disetujui..." 
                          class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-[2px] text-xs font-semibold text-slate-800 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                <x-interactive-button text="Batal" variant="outline" type="button" onclick="closeRejectNewSopModal()" />
                <x-interactive-button text="Konfirmasi" variant="danger" icon="ph ph-x text-sm" type="submit" />
            </div>
        </form>
    </div>
</div>

<script>
function openRejectNewSopModal(id, title) {
    const modal = document.getElementById('rejectNewSopModal');
    const form = document.getElementById('rejectNewSopForm');
    const titleEl = document.getElementById('rejectNewSopTitle');
    const notesEl = document.getElementById('rejectNewSopNotes');

    form.action = `/admin/sop-requests/${id}/reject`;
    titleEl.textContent = title;
    notesEl.value = '';
    modal.classList.remove('hidden');
}

function closeRejectNewSopModal() {
    document.getElementById('rejectNewSopModal').classList.add('hidden');
}
</script>

<!-- ========================================================================= -->
<!-- MODAL: PERMINTAAN REVISI USULAN SOP BARU -->
<!-- ========================================================================= -->
<div id="revisionNewSopModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-[2px] shadow-xl border border-slate-200 max-w-md w-full overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-orange-600 text-white">
            <div class="flex items-center space-x-2">
                <i class="ph ph-pencil-line text-lg"></i>
                <h3 class="text-xs font-bold capitalize">Minta Revisi Usulan SOP</h3>
            </div>
            <button type="button" onclick="closeRevisionNewSopModal()" class="text-white/80 hover:text-white cursor-pointer border-none bg-transparent">
                <i class="ph ph-x text-base"></i>
            </button>
        </div>

        <form id="revisionNewSopForm" method="POST" action="" class="p-5 space-y-4">
            @csrf
            <div>
                <p class="text-xs text-slate-600 font-semibold mb-1">Usulan SOP:</p>
                <p id="revisionNewSopTitle" class="text-xs font-bold text-slate-900 bg-slate-50 p-2 rounded-[2px] border border-slate-200"></p>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Poin Revisi untuk Pemohon <span class="text-rose-500">*</span></label>
                <textarea name="revision_notes" id="revisionNewSopNotes" rows="4" required placeholder="Jelaskan apa yang perlu dilengkapi atau diperbaiki oleh pemohon..." 
                          class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-[2px] text-xs font-semibold text-slate-800 focus:bg-white focus:ring-1 focus:ring-orange-500 outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                <x-interactive-button text="Batal" variant="outline" type="button" onclick="closeRevisionNewSopModal()" />
                <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs rounded-[2px] shadow-xs flex items-center gap-1.5 cursor-pointer border-none">
                    <i class="ph ph-paper-plane-tilt"></i>
                    <span>Kirim ke Pemohon</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRevisionNewSopModal(id, title) {
    const modal = document.getElementById('revisionNewSopModal');
    const form = document.getElementById('revisionNewSopForm');
    const titleEl = document.getElementById('revisionNewSopTitle');
    const notesEl = document.getElementById('revisionNewSopNotes');

    form.action = `/admin/sop-requests/${id}/request-revision`;
    titleEl.textContent = title;
    notesEl.value = '';
    modal.classList.remove('hidden');
}

function closeRevisionNewSopModal() {
    document.getElementById('revisionNewSopModal').classList.add('hidden');
}
</script>

<!-- ========================================================================= -->
<!-- MODAL: DAFTAR PESERTA & NILAI KUIS SOP -->
<!-- ========================================================================= -->
<div id="quizParticipantsModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-[2px] shadow-2xl border border-slate-200 max-w-3xl w-full overflow-hidden flex flex-col max-h-[90vh]">
        <!-- HEADER -->
        <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white p-4 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <i class="ph ph-users-three text-xl text-[#ffe16e]"></i>
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-white">Rincian Hasil Kuis Pemahaman SOP</h3>
                    <p id="quizModalDocNumber" class="text-[10.5px] text-white/80 font-mono"></p>
                </div>
            </div>
            <button type="button" onclick="closeQuizParticipantsModal()" class="text-white/70 hover:text-white cursor-pointer border-none bg-transparent">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <!-- INFO BANNER -->
        <div class="p-4 bg-slate-50 border-b border-slate-200">
            <h4 id="quizModalDocTitle" class="text-xs font-semibold text-slate-900 leading-snug"></h4>
            <div class="flex flex-wrap items-center gap-3 mt-2 text-[11px]">
                <span class="text-slate-500 font-normal">Departemen: <strong id="quizModalDept" class="text-slate-800 font-medium"></strong></span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-slate-500 font-normal">Standar KKM: <span class="text-[#1677B8] font-semibold">60 Poin</span></span>
                <span class="text-slate-300">&bull;</span>
                <div id="quizModalStatusBadge"></div>
            </div>
        </div>

        <!-- FILTER KATEGORI PESERTA DI DALAM MODAL -->
        <div class="px-4 py-2 bg-slate-100/70 border-b border-slate-200 flex items-center gap-1.5 overflow-x-auto">
            <span class="text-[10.5px] text-slate-500 font-medium mr-1">Filter Sumber:</span>
            <button type="button" id="modalPillBtn_all" onclick="filterModalParticipants('all')" class="px-2.5 py-1 text-xs rounded-[2px] font-medium transition-all bg-white text-[#1677B8] border border-slate-300 shadow-2xs cursor-pointer">
                Semua (<span id="modalPillCountAll">0</span>)
            </button>
            <button type="button" id="modalPillBtn_periodic" onclick="filterModalParticipants('periodic')" class="px-2.5 py-1 text-xs rounded-[2px] font-medium transition-all text-slate-600 hover:text-slate-900 cursor-pointer">
                Kuis Berkala 6 Bulan (<span id="modalPillCountPeriodic">0</span>)
            </button>
            <button type="button" id="modalPillBtn_socialization" onclick="filterModalParticipants('socialization')" class="px-2.5 py-1 text-xs rounded-[2px] font-medium transition-all text-slate-600 hover:text-slate-900 cursor-pointer">
                Presensi QR Sosialisasi (<span id="modalPillCountSocialization">0</span>)
            </button>
        </div>

        <!-- TABEL PESERTA -->
        <div class="p-4 overflow-y-auto flex-1 max-h-[60vh]">
            <div class="overflow-x-auto border border-slate-200 rounded-[2px]">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-100 border-b border-slate-200 text-[11px] font-semibold text-slate-700">
                        <tr>
                            <th class="py-2.5 px-3 text-center w-8">No</th>
                            <th class="py-2.5 px-3">Nama Peserta</th>
                            <th class="py-2.5 px-3">Jabatan / Bagian</th>
                            <th class="py-2.5 px-3">Jalur Ujian</th>
                            <th class="py-2.5 px-3 text-center w-24">Skor</th>
                            <th class="py-2.5 px-3 text-center w-28">Status</th>
                            <th class="py-2.5 px-3 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="quizParticipantsTableBody" class="divide-y divide-slate-200 text-xs font-normal text-slate-800">
                        <!-- Diisi via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="p-3 bg-slate-50 border-t border-slate-200 flex justify-between items-center text-xs">
            <span id="quizModalTotalCount" class="text-slate-500 font-medium text-[11px]"></span>
            <button type="button" onclick="closeQuizParticipantsModal()" class="px-4 py-2 bg-white hover:bg-slate-100 text-slate-700 font-medium text-xs rounded-[2px] border border-slate-300 transition-all cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
let currentModalParticipants = [];
let currentModalCategory = 'all';

function openQuizParticipantsModal(id, title, docNumber, department, allParticipants, overallStatus, initialCategory) {
    document.getElementById('quizModalDocTitle').textContent = title;
    document.getElementById('quizModalDocNumber').textContent = `No: ${docNumber}`;
    document.getElementById('quizModalDept').textContent = department;

    const statusBadgeContainer = document.getElementById('quizModalStatusBadge');
    if (overallStatus === 'all_passed') {
        statusBadgeContainer.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10.5px] font-medium rounded-[2px]"><i class="ph ph-check-circle text-xs"></i> Semua Peserta Lulus (≥60)</span>';
    } else if (overallStatus === 'remedial') {
        statusBadgeContainer.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 text-[10.5px] font-medium rounded-[2px]"><i class="ph ph-warning-circle text-xs"></i> Perlu Remedial (< 60)</span>';
    } else {
        statusBadgeContainer.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-100 text-slate-600 border border-slate-200 text-[10.5px] font-normal rounded-[2px]"><i class="ph ph-clock text-xs"></i> Belum Ada Peserta</span>';
    }

    currentModalParticipants = Array.isArray(allParticipants) ? allParticipants : Object.values(allParticipants || {});

    // Update pill counts
    const periodicCount = currentModalParticipants.filter(p => p.category === 'periodic').length;
    const socCount = currentModalParticipants.filter(p => p.category === 'socialization').length;
    document.getElementById('modalPillCountAll').textContent = currentModalParticipants.length;
    document.getElementById('modalPillCountPeriodic').textContent = periodicCount;
    document.getElementById('modalPillCountSocialization').textContent = socCount;

    // Filter awal sesuai kategori aktif di halaman
    filterModalParticipants(initialCategory && ['periodic', 'socialization'].includes(initialCategory) ? initialCategory : 'all');

    document.getElementById('quizParticipantsModal').classList.remove('hidden');
}

function filterModalParticipants(category) {
    currentModalCategory = category;

    // Update button styling
    ['all', 'periodic', 'socialization'].forEach(cat => {
        const btn = document.getElementById(`modalPillBtn_${cat}`);
        if (btn) {
            if (cat === category) {
                btn.className = 'px-2.5 py-1 text-xs rounded-[2px] font-semibold transition-all bg-white text-[#1677B8] border border-slate-300 shadow-2xs cursor-pointer';
            } else {
                btn.className = 'px-2.5 py-1 text-xs rounded-[2px] font-normal transition-all text-slate-600 hover:text-slate-900 cursor-pointer';
            }
        }
    });

    let filtered = currentModalParticipants;
    if (category === 'periodic') {
        filtered = currentModalParticipants.filter(p => p.category === 'periodic');
    } else if (category === 'socialization') {
        filtered = currentModalParticipants.filter(p => p.category === 'socialization');
    }

    document.getElementById('quizModalTotalCount').textContent = `Menampilkan ${filtered.length} dari ${currentModalParticipants.length} Peserta Terdaftar`;

    const tbody = document.getElementById('quizParticipantsTableBody');
    tbody.innerHTML = '';

    if (filtered.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="py-8 text-center text-slate-400">
                    <i class="ph ph-users text-2xl text-slate-300 mb-1 block"></i>
                    Belum ada peserta untuk kategori ini pada SOP ini.
                </td>
            </tr>
        `;
    } else {
        filtered.forEach((p, idx) => {
            const isPassed = p.status === 'passed' || p.score >= 60;
            const dateStr = p.attempted_at ? new Date(p.attempted_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
            
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition-colors';
            tr.innerHTML = `
                <td class="py-2.5 px-3 text-center text-slate-400 text-[11px]">${idx + 1}</td>
                <td class="py-2.5 px-3 font-semibold text-slate-800">${p.name || '-'}</td>
                <td class="py-2.5 px-3 text-slate-600 font-normal">${p.department || '-'}</td>
                <td class="py-2.5 px-3">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[2px] text-[10px] font-medium ${p.category === 'socialization' ? 'bg-sky-50 text-sky-800 border border-sky-200' : 'bg-blue-50 text-blue-800 border border-blue-200'}">
                        ${p.source}
                    </span>
                </td>
                <td class="py-2.5 px-3 text-center font-semibold ${isPassed ? 'text-emerald-700' : 'text-rose-700'}">
                    ${p.score} / 100
                </td>
                <td class="py-2.5 px-3 text-center">
                    ${isPassed 
                        ? '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10.5px] font-medium rounded-[2px]"><i class="ph ph-check text-xs"></i> Lulus</span>'
                        : '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 text-[10.5px] font-medium rounded-[2px]"><i class="ph ph-x text-xs"></i> Remedial</span>'
                    }
                </td>
                <td class="py-2.5 px-3 text-right text-slate-500 text-[11px] whitespace-nowrap">${dateStr}</td>
            `;
            tbody.appendChild(tr);
        });
    }
}

function closeQuizParticipantsModal() {
    document.getElementById('quizParticipantsModal').classList.add('hidden');
}
</script>
@endsection


