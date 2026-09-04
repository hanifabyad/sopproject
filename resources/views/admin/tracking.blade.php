@extends('layouts.admin')

@section('title', 'Tracking & Siklus Hidup Dokumen SOP')
@section('header_title', 'Tracking & Monitoring Siklus Hidup SOP')

@section('content')
<style>
    /* ==========================================================
       🖨️ ADVANCED LANDSCAPE PRINT SYSTEM FOR AUDIT REKAPITULASI
       ========================================================== */
    @media print {
        @page {
            size: landscape;
            margin: 8mm 10mm;
        }

        html, body {
            background: #ffffff !important;
            color: #0f172a !important;
            font-size: 10px !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Hide interactive non-printable elements */
        aside#sidebar,
        #sidebar-open-btn,
        #sidebar-backdrop,
        header,
        nav,
        .no-print,
        .filter-container,
        .pagination-container,
        .table-action-col {
            display: none !important;
        }

        /* Show corporate print header */
        .print-corporate-header {
            display: block !important;
        }

        /* Screen banner hidden in print */
        .screen-top-banner {
            display: none !important;
        }

        /* Main Container expanded */
        #main-container, main {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
        }

        /* Print Stats Grid */
        .print-stats-wrapper {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 8px !important;
            margin-bottom: 12px !important;
        }

        .print-card-box {
            border: 1px solid #cbd5e1 !important;
            background-color: #f8fafc !important;
            padding: 8px 10px !important;
            border-radius: 4px !important;
            box-shadow: none !important;
        }

        /* Print Table */
        .print-table-wrapper {
            border: 1px solid #cbd5e1 !important;
            border-radius: 4px !important;
            overflow: visible !important;
        }

        table.tracking-table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 9.5px !important;
        }

        table.tracking-table th {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            border: 1px solid #cbd5e1 !important;
            padding: 6px 8px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
        }

        table.tracking-table td {
            border: 1px solid #e2e8f0 !important;
            padding: 5.5px 8px !important;
            vertical-align: middle !important;
        }

        tr {
            page-break-inside: avoid !important;
        }

        /* Print status badges */
        .badge-print {
            border: 1px solid #cbd5e1 !important;
            padding: 2px 6px !important;
            border-radius: 3px !important;
            font-weight: 700 !important;
            display: inline-block !important;
        }
    }

    /* Screen default */
    .print-corporate-header {
        display: none;
    }
</style>

<div class="space-y-6">

    <!-- 🖨️ CORPORATE PRINT-ONLY HEADER (MUNCUL OTOMATIS SAAT CETAK LANDSCAPE) -->
    <div class="print-corporate-header border-b-2 border-slate-800 pb-3 mb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('img/logopkm.png') }}" class="h-12 object-contain" alt="Logo PT PKM Group">
                <div>
                    <h1 class="text-sm font-black text-slate-900 tracking-tight">PT PUTRA KELANA MAKMUR (PKM GROUP)</h1>
                    <h2 class="text-xs font-bold text-[#1677B8] uppercase tracking-wider">ELECTRONIC QUALITY MANAGEMENT SYSTEM (e-QMS)</h2>
                    <p class="text-[9px] text-slate-600 font-semibold">Laporan Monitoring, Tracking & Evaluasi Siklus Hidup Standar Operasional Prosedur (SOP)</p>
                </div>
            </div>
            <div class="text-right text-[8.5px] font-semibold text-slate-700 space-y-0.5 border-l border-slate-300 pl-3">
                <div>Tanggal Cetak: <strong>{{ now()->format('d F Y - H:i') }} WIB</strong></div>
                <div>Filter Periode Tahun: <strong>{{ $selectedYear && $selectedYear !== 'all' ? 'Tahun ' . $selectedYear : 'Semua Periode' }}</strong></div>
                <div>Filter Unit/Dept: <strong>{{ $selectedUnit && $selectedUnit !== 'all' ? $selectedUnit : 'Semua Unit & Dept' }}</strong></div>
                <div>Filter Status Dokumen: <strong>{{ $selectedStatus && $selectedStatus !== 'all' ? strtoupper($selectedStatus) : 'Semua Status' }}</strong></div>
            </div>
        </div>
    </div>

    <!-- TOP HEADER (LAYAR MONITOR) -->
    <div class="screen-top-banner bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.dashboard') }}" variant="light" text="Kembali" />
            <span class="text-white/30">|</span>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#ffe16e] font-medium">Dashboard</a>
                <span>/</span>
                <span class="font-bold text-white">Tracking & Siklus SOP</span>
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl md:text-2xl font-extrabold tracking-tight capitalize text-white">Monitoring & Tracking Dokumen SOP</h2>
                <p class="text-xs text-white/85 mt-0.5 font-medium">Pantau statistik kurun tahun, riwayat revisi, status keaktifan, dan evaluasi berkala di seluruh unit.</p>
            </div>

            <div class="flex items-center gap-2 no-print">
                <x-interactive-button text="Cetak Rekap" variant="outline" icon="ph ph-printer text-base" type="button" onclick="window.print()" />
            </div>
        </div>
    </div>

    <!-- FILTER BAR INTERAKTIF (HIDDEN IN PRINT) -->
    <div class="filter-container no-print bg-white rounded-lg p-5 shadow-sm border border-sand-200/60">
        <form method="GET" action="{{ route('admin.tracking') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Filter Kurun Tahun -->
            <div>
                <label class="block text-[11px] font-bold capitalize text-on-surface-variant mb-1.5 flex items-center gap-1">
                    <i class="ph ph-calendar text-sm text-[#1677B8]"></i>
                    <span>Kurun Tahun</span>
                </label>
                <select name="year" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 rounded-md px-3 py-2 text-xs font-bold text-on-surface outline-none focus:ring-2 focus:ring-[#1677B8] transition-all">
                    <option value="all" {{ $selectedYear == 'all' || !$selectedYear ? 'selected' : '' }}>Semua Tahun</option>
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ (string)$selectedYear === (string)$year ? 'selected' : '' }}>Tahun {{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Unit / Departemen -->
            <div>
                <label class="block text-[11px] font-bold capitalize text-on-surface-variant mb-1.5 flex items-center gap-1">
                    <i class="ph ph-buildings text-sm text-[#1677B8]"></i>
                    <span>Unit / Dept</span>
                </label>
                <select name="unit" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 rounded-md px-3 py-2 text-xs font-semibold text-on-surface outline-none focus:ring-2 focus:ring-[#1677B8] transition-all">
                    <option value="all" {{ $selectedUnit == 'all' || !$selectedUnit ? 'selected' : '' }}>Semua Unit & Dept</option>
                    @foreach($units as $u)
                        <option value="{{ $u }}" {{ $selectedUnit == $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status SOP -->
            <div>
                <label class="block text-[11px] font-bold capitalize text-on-surface-variant mb-1.5 flex items-center gap-1">
                    <i class="ph ph-chart-donut text-sm text-[#1677B8]"></i>
                    <span>Status Dokumen</span>
                </label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 rounded-md px-3 py-2 text-xs font-semibold text-on-surface outline-none focus:ring-2 focus:ring-[#1677B8] transition-all">
                    <option value="all" {{ $selectedStatus == 'all' || !$selectedStatus ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" {{ $selectedStatus == 'active' ? 'selected' : '' }}>Aktif & Berlaku</option>
                    <option value="need_revision" {{ $selectedStatus == 'need_revision' ? 'selected' : '' }}>Sedang Revisi</option>
                    <option value="waiting" {{ $selectedStatus == 'waiting' ? 'selected' : '' }}>Menunggu Review</option>
                    <option value="obsolete" {{ $selectedStatus == 'obsolete' ? 'selected' : '' }}>Tidak Terpakai / Arsip</option>
                </select>
            </div>

            <!-- Keyword Search -->
            <div>
                <label class="block text-[11px] font-bold capitalize text-on-surface-variant mb-1.5 flex items-center gap-1">
                    <i class="ph ph-magnifying-glass text-sm text-[#1677B8]"></i>
                    <span>Cari Judul / No. Dok</span>
                </label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari SOP..." class="w-full bg-white border border-slate-200 rounded-md pl-8 pr-3 py-2 text-xs font-medium text-on-surface outline-none focus:ring-2 focus:ring-[#1677B8] transition-all">
                    <i class="ph ph-magnifying-glass text-xs text-on-surface-variant absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <x-interactive-button text="Terapkan" variant="blue" class="flex-1 !h-[36px] !py-0 flex items-center" />
                <a href="{{ route('admin.tracking') }}" class="h-[36px] w-[36px] bg-canvas hover:bg-slate-50 text-on-surface-variant border border-slate-200 rounded-md text-xs font-bold transition-all flex items-center justify-center cursor-pointer flex-shrink-0" title="Reset Filter">
                    <i class="ph ph-arrow-counter-clockwise text-base"></i>
                </a>
            </div>
        </form>
    </div>

    @php
        $sortedYearly = $yearlyStats->sortBy('year')->values();
        $chartYears = $sortedYearly->pluck('year')->toJson();
        $chartTotals = $sortedYearly->pluck('total')->toJson();
        $chartActives = $sortedYearly->pluck('active_count')->toJson();
        $chartRevisions = $sortedYearly->pluck('revision_count')->toJson();

        $chartAgeLabels = json_encode(['< 1 Bulan', '1-6 Bulan', '6-12 Bulan', '1-2 Tahun', '> 2 Tahun']);
        $chartAgeData = json_encode([
            $ageStats['under_1m'] ?? 0,
            $ageStats['1m_to_6m'] ?? 0,
            $ageStats['6m_to_1y'] ?? 0,
            $ageStats['1y_to_2y'] ?? 0,
            $ageStats['over_2y'] ?? 0,
        ]);

        $totalSop = (int)$stats['total'];
        $donutSegments = [
            [
                'id' => 'seg-active',
                'label' => 'SOP Aktif & Sah',
                'short' => 'Aktif & Sah',
                'value' => (int)$stats['active'],
                'color' => '#10b981',
            ],
            [
                'id' => 'seg-revision',
                'label' => 'Dalam Proses Revisi',
                'short' => 'Perlu Revisi',
                'value' => (int)$stats['need_revision'],
                'color' => '#f59e0b',
            ],
            [
                'id' => 'seg-waiting',
                'label' => 'Menunggu Review',
                'short' => 'Menunggu Review',
                'value' => (int)$stats['waiting'],
                'color' => '#00b4d8',
            ],
            [
                'id' => 'seg-obsolete',
                'label' => 'Usang / Obsolete',
                'short' => 'Usang / Obsolete',
                'value' => (int)$stats['obsolete'],
                'color' => '#94a3b8',
            ],
        ];

        $size = 170;
        $strokeWidth = 18;
        $radius = ($size / 2) - ($strokeWidth / 2);
        $circumference = 2 * M_PI * $radius;
        $cumulativePercent = 0;
    @endphp

    <!-- VISUAL CHART ANALYTICS SECTION (3 CHARTS) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 no-print">
        
        <!-- CHART 1: SIKLUS HIDUP & STATUS DOKUMEN SOP (4 COLS) -->
        <div class="lg:col-span-4 bg-white rounded-lg p-5 shadow-sm border border-sand-200/60 flex flex-col justify-between space-y-4">
            <div class="flex items-center justify-between border-b border-sand-200/40 pb-3">
                <div class="flex items-center space-x-2 text-xs font-bold text-slate-800">
                    <i class="ph ph-chart-pie-slice text-base text-[#1677B8]"></i>
                    <span class="capitalize">Distribusi Status SOP</span>
                </div>
                <span class="text-[10px] text-slate-500 font-semibold">Total {{ $stats['total'] }} Dok</span>
            </div>

            <!-- ANIMATED CHART.JS DONUT CHART & LEGEND -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 py-2 flex-1">
                <div class="relative w-44 h-44 flex items-center justify-center flex-shrink-0">
                    <canvas id="documentStatusChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center px-3">
                        <p id="donut-center-label" class="text-[10px] font-semibold text-slate-500 truncate max-w-[110px]">Total SOP</p>
                        <p id="donut-center-value" class="text-2xl font-black text-slate-900 leading-tight">{{ $totalSop }}</p>
                        <p id="donut-center-percentage" class="text-[10px] font-bold text-emerald-600">[100%]</p>
                    </div>
                </div>

                <div class="space-y-1.5 w-full sm:w-auto text-xs flex-1 min-w-0">
                    @foreach($donutSegments as $seg)
                        @php
                            $pct = $totalSop > 0 ? number_format(($seg['value'] / $totalSop) * 100, 0) : 0;
                        @endphp
                        <div class="donut-legend-item flex items-center justify-between gap-2 p-1.5 rounded-[2px] border border-slate-200 bg-slate-50/70 hover:bg-slate-100 transition-all text-xs cursor-pointer"
                             data-id="{{ $seg['id'] }}" data-label="{{ $seg['label'] }}" data-value="{{ $seg['value'] }}" data-percentage="{{ $pct }}" data-color="{{ $seg['color'] }}">
                            <div class="flex items-center gap-1.5 font-semibold text-slate-700 text-[11px] truncate">
                                <span class="w-2 h-2 rounded-[2px] flex-shrink-0" style="background-color: {{ $seg['color'] }};"></span>
                                <span class="truncate">{{ $seg['short'] }}</span>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <span class="font-extrabold text-slate-900 text-xs">{{ $seg['value'] }}</span>
                                <span class="text-[10px] text-slate-500 font-medium">({{ $pct }}%)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- CHART 2: DISTRIBUSI USIA SOP AKTIF SEJAK DIBUAT (4 COLS) -->
        <div class="lg:col-span-4 bg-white rounded-lg p-5 shadow-sm border border-sand-200/60 flex flex-col justify-between space-y-4">
            <div class="flex items-center justify-between border-b border-sand-200/40 pb-3">
                <div class="flex items-center space-x-2 text-xs font-bold text-slate-800">
                    <i class="ph ph-hourglass-high text-base text-[#1677B8]"></i>
                    <span class="capitalize">Usia SOP Aktif Sejak Dibuat</span>
                </div>
                <span class="text-[10px] text-slate-700 font-bold bg-slate-100 px-2 py-0.5 rounded-[2px] border border-slate-200">
                    {{ $stats['active'] }} SOP Aktif
                </span>
            </div>

            <div class="h-60 relative w-full flex-1 flex items-center">
                <canvas id="activeSopAgeChart"></canvas>
            </div>
        </div>

        <!-- CHART 3: TREN PENERBITAN & REVISI SOP PER TAHUN (4 COLS) -->
        <div class="lg:col-span-4 bg-white rounded-lg p-5 shadow-sm border border-sand-200/60 flex flex-col justify-between space-y-4">
            <div class="flex items-center justify-between border-b border-sand-200/40 pb-3">
                <div class="flex items-center space-x-2 text-xs font-bold text-slate-800">
                    <i class="ph ph-chart-bar text-base text-[#1677B8]"></i>
                    <span class="capitalize">Tren Tahunan</span>
                </div>
                <div class="flex items-center gap-1.5 text-[9px] font-bold">
                    <span class="text-[#1677B8]">● Total</span>
                    <span class="text-emerald-600">● Aktif</span>
                    <span class="text-amber-600">● Revisi</span>
                </div>
            </div>

            <div class="h-60 relative w-full flex-1 flex items-center">
                <canvas id="yearlyTrendBarChart"></canvas>
            </div>
        </div>

    </div>

    <!-- TABEL DAFTAR MONITORING DOKUMEN -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4 print-table-wrapper">
        <!-- HEADER BIRU MUDA (HANYA BAGIAN INI) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3 rounded-[2px]">
            <div class="flex items-center space-x-2 text-xs font-bold text-[#1677B8]">
                <i class="ph ph-table text-base no-print"></i>
                <span class="capitalize text-slate-900 font-extrabold">Daftar Dokumen, Durasi & Status Siklus Hidup SOP</span>
            </div>
            
            <!-- SEARCH BAR LANGSUNG DI HEADER TABEL -->
            <div class="flex items-center gap-2.5 no-print">
                <form method="GET" action="{{ route('admin.tracking') }}" class="relative flex items-center">
                    @if(request('year')) <input type="hidden" name="year" value="{{ request('year') }}"> @endif
                    @if(request('unit')) <input type="hidden" name="unit" value="{{ request('unit') }}"> @endif
                    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif

                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari judul / no. SOP..." 
                               class="w-48 sm:w-60 bg-white border border-blue-300 focus:border-[#1677B8] focus:ring-1 focus:ring-[#1677B8] text-xs text-slate-900 font-bold placeholder:font-normal placeholder:text-slate-400 pl-8 pr-7 py-1.5 rounded-[2px] outline-none transition-all">
                        <i class="ph ph-magnifying-glass text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 text-sm"></i>
                        @if(request('search'))
                            <a href="{{ route('admin.tracking', request()->except('search')) }}" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-600 text-xs" title="Hapus pencarian">
                                <i class="ph ph-x"></i>
                            </a>
                        @endif
                    </div>
                </form>
                <span class="text-[11px] text-[#1677B8] font-bold bg-white px-2.5 py-1.5 rounded-[2px] border border-blue-200 whitespace-nowrap shadow-2xs">
                    {{ $documents->total() }} Dokumen
                </span>
            </div>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-[2px]">
            <table class="tracking-table w-full text-left border-collapse">
                <thead class="bg-[#1677B8] border-b border-[#1258a0] text-[11px] font-extrabold uppercase tracking-wider text-white">
                    <tr>
                        <th class="py-3 px-3 text-center w-12 whitespace-nowrap">No</th>
                        <th class="py-3 px-4 whitespace-nowrap">Dokumen SOP & Identitas</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap">Tgl Upload & SLA</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap">Status Dokumen</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap">Sosialisasi & Siklus</th>
                        <th class="py-3 px-3 text-right table-action-col no-print whitespace-nowrap w-20 no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs">
                    @forelse($documents as $index => $doc)
                    @php
                        $duration = $doc->process_duration_days;
                        $slaStatus = $doc->sla_status;
                        $activeRevReq = $doc->activeRevisionRequest;
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-2.5 px-2 text-center font-bold text-slate-400 text-[11px] align-middle">
                            {{ $documents->firstItem() + $index }}
                        </td>

                        <!-- KOLOM 1: DOKUMEN SOP & IDENTITAS -->
                        <td class="py-2.5 px-3 align-middle">
                            @php
                                $supportDepts = ['HC', 'IT', 'QMS', 'HSE', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];
                                $isSupport = in_array(strtoupper($doc->department), $supportDepts);
                                $detailUrl = $isSupport ? route('admin.support.document.detail', $doc->id) : route('admin.BU.detail', $doc->id);
                            @endphp
                            <div class="font-bold text-slate-900 text-xs leading-snug">
                                <a href="{{ $detailUrl }}" class="hover:text-[#1677B8] hover:underline transition-colors cursor-pointer">{{ $doc->title }}</a>
                            </div>
                            <div class="flex items-center gap-1.5 mt-1 flex-wrap text-[10px]">
                                <span class="font-mono font-semibold text-slate-600">{{ $doc->doc_number ?? 'No. Belum Diatur' }}</span>
                                <span class="text-slate-300">|</span>
                                <span class="font-mono font-bold text-slate-700">Rev {{ $doc->doc_revision ?? '0' }}</span>
                                <span class="text-slate-300">|</span>
                                <span class="font-bold text-[#1677B8]">{{ $doc->department }}</span>
                            </div>
                        </td>

                        <!-- KOLOM 2: TGL & DURASI SLA (BERSIH DENGAN ICON) -->
                        <td class="py-2.5 px-3 text-center whitespace-nowrap align-middle">
                            <div class="text-[11px] font-semibold text-slate-700">
                                {{ $doc->created_at ? $doc->created_at->format('d M Y') : '-' }}
                            </div>
                            <div class="mt-1 flex flex-col items-center gap-0.5">
                                @if($doc->status === 'active')
                                    <span class="inline-flex items-center justify-center gap-1 text-[10px] font-bold text-slate-800">
                                        <i class="ph ph-check-circle text-emerald-600 text-sm"></i>
                                        <span>{{ $duration }} Hari (Selesai)</span>
                                    </span>
                                @else
                                    @if($slaStatus === 'on_track')
                                        <span class="inline-flex items-center justify-center gap-1 text-[10px] font-bold text-slate-800">
                                            <i class="ph ph-timer text-[#1677B8] text-sm"></i>
                                            <span>{{ $duration }} Hari (On-Track)</span>
                                        </span>
                                    @elseif($slaStatus === 'warning')
                                        <span class="inline-flex items-center justify-center gap-1 text-[10px] font-bold text-amber-700">
                                            <i class="ph ph-warning text-amber-600 text-sm"></i>
                                            <span>{{ $duration }} Hari (Batas 13h)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center gap-1 text-[10px] font-bold text-rose-700">
                                            <i class="ph ph-warning-octagon text-rose-600 text-sm"></i>
                                            <span>{{ $duration }} Hari (>14 Hari)</span>
                                        </span>
                                    @endif
                                @endif

                                @if($duration > 14)
                                    <button type="button" 
                                            onclick="openSlaModal('{{ $doc->id }}', '{{ addslashes($doc->title) }}', {{ $duration }}, '{{ addslashes($doc->sla_notes ?? '') }}', '{{ $doc->slaActionUser->full_name ?? ($doc->slaActionUser->username ?? '') }}', '{{ $doc->sla_action_at ? $doc->sla_action_at->format('d M Y H:i') : '' }}')" 
                                            class="inline-flex items-center justify-center gap-1 text-[9px] font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 px-1.5 py-0.5 rounded-[2px] transition-all cursor-pointer no-print mt-0.5">
                                        <i class="ph ph-note-pencil text-amber-600"></i>
                                        <span>{{ !empty($doc->sla_notes) ? 'Catatan SLA' : 'Isi Alasan SLA' }}</span>
                                    </button>
                                @endif
                            </div>
                        </td>

                        <!-- KOLOM 3: STATUS DOKUMEN (BERSIH MONOKROM DENGAN ICON) -->
                        <td class="py-2.5 px-3 text-center whitespace-nowrap align-middle">
                            @if($doc->status === 'active')
                                <span class="inline-flex items-center gap-1.5 font-bold text-slate-900 text-xs">
                                    <i class="ph ph-check-circle-fill text-emerald-600 text-sm"></i>
                                    <span>Aktif (In-Use)</span>
                                </span>
                            @elseif($doc->status === 'need_revision')
                                <span class="inline-flex items-center gap-1.5 font-bold text-slate-900 text-xs">
                                    <i class="ph ph-arrows-clockwise text-amber-600 text-sm"></i>
                                    <span>Revisi Berjalan</span>
                                </span>
                            @elseif($doc->status === 'waiting')
                                <span class="inline-flex items-center gap-1.5 font-bold text-slate-900 text-xs">
                                    <i class="ph ph-clock text-[#1677B8] text-sm"></i>
                                    <span>Menunggu Review</span>
                                </span>
                            @elseif($doc->status === 'obsolete')
                                <span class="inline-flex items-center gap-1.5 font-bold text-slate-500 text-xs">
                                    <i class="ph ph-archive text-slate-400 text-sm"></i>
                                    <span>Usang / Arsip</span>
                                </span>
                            @else
                                <span class="font-bold text-slate-700 text-xs">
                                    {{ $doc->status }}
                                </span>
                            @endif
                        </td>

                        <!-- KOLOM 4: SOSIALISASI & SIKLUS -->
                        <td class="py-2.5 px-3 text-center whitespace-nowrap align-middle">
                            <div class="flex flex-col items-center justify-center gap-1">
                                @if($doc->status === 'active')
                                    @if($doc->socialization_status === 'submitted')
                                        <button type="button" onclick="openViewSocializationModal('{{ $doc->id }}')" class="h-6 px-2 text-[9.5px] font-bold text-slate-800 bg-white hover:bg-slate-50 border border-slate-300 rounded-[2px] flex items-center justify-center gap-1 transition-all cursor-pointer" title="Lihat Foto Bukti">
                                            <i class="ph ph-check-circle text-emerald-600"></i>
                                            <span>Sosialisasi OK</span>
                                        </button>
                                    @else
                                        <span class="text-[10.5px] text-slate-400 font-medium italic">Belum Ada Sosialisasi</span>
                                    @endif
                                @elseif($doc->status === 'need_revision' && $doc->revision_deadline)
                                    @php
                                        $isExp = now()->greaterThan($doc->revision_deadline);
                                        $remDays = max(0, (int)now()->diffInDays($doc->revision_deadline, false));
                                    @endphp
                                    <span class="text-[9.5px] font-bold text-slate-700 flex items-center gap-1">
                                        <i class="ph ph-hourglass-high {{ $isExp ? 'text-rose-600' : 'text-amber-600' }}"></i>
                                        <span>{{ $isExp ? 'SLA 7h Lewat' : "Sisa {$remDays}h Revisi" }}</span>
                                    </span>
                                @else
                                    <span class="text-[10px] text-slate-400 font-medium">-</span>
                                @endif

                                @if($activeRevReq && $activeRevReq->status === 'pending')
                                    <button type="button" onclick="openReviewRevisionModal('{{ $activeRevReq->id }}', '{{ addslashes($doc->title) }}', '{{ addslashes($activeRevReq->user->full_name ?? $activeRevReq->user->username) }}', '{{ addslashes($activeRevReq->reason) }}')" class="h-5 px-1.5 text-[9px] font-bold text-slate-800 bg-white hover:bg-slate-50 border border-slate-300 rounded-[2px] flex items-center justify-center gap-1 transition-all cursor-pointer mt-0.5">
                                        <i class="ph ph-bell text-amber-600"></i>
                                        <span>Tinjau Request</span>
                                    </button>
                                @endif
                            </div>
                        </td>

                        <!-- KOLOM 5: AKSI -->
                        <td class="py-2.5 px-2 text-right table-action-col no-print align-middle">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ $detailUrl }}" class="h-6 w-6 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded-[2px] flex items-center justify-center transition-colors" title="Audit Detail Dokumen">
                                    <i class="ph ph-eye text-xs text-slate-700"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">
                            <i class="ph ph-folder-open text-3xl text-slate-300 mb-1.5 block"></i>
                            <p class="text-xs font-bold text-slate-700">Tidak ada data dokumen SOP yang cocok.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

            <!-- BESPOKE CORPORATE PAGINATION (NO AI SLOP / NO DARK PILL) -->
            @if($documents->hasPages())
            <div class="no-print px-4 py-3 border-t border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-600 font-medium">
                    Menampilkan <span class="font-bold text-slate-900">{{ $documents->firstItem() }}</span> – <span class="font-bold text-slate-900">{{ $documents->lastItem() }}</span> dari <span class="font-bold text-[#1677B8]">{{ $documents->total() }}</span> dokumen
                </div>

                <div class="flex items-center gap-1">
                    {{-- Previous Page Link --}}
                    @if ($documents->onFirstPage())
                        <span class="h-7 px-2.5 flex items-center justify-center text-xs font-bold text-slate-400 bg-white border border-slate-200 rounded-[2px] cursor-not-allowed">
                            <i class="ph ph-caret-left text-xs"></i>
                        </span>
                    @else
                        <a href="{{ $documents->previousPageUrl() }}" class="h-7 px-2.5 flex items-center justify-center text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 hover:text-[#1677B8] border border-slate-300 rounded-[2px] transition-colors cursor-pointer" title="Halaman Sebelumnya">
                            <i class="ph ph-caret-left text-xs"></i>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($documents->getUrlRange(1, $documents->lastPage()) as $page => $url)
                        @if ($page == $documents->currentPage())
                            <span class="h-7 min-w-[28px] px-2 flex items-center justify-center text-xs font-extrabold text-white bg-[#1677B8] border border-[#1677B8] rounded-[2px] shadow-xs">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="h-7 min-w-[28px] px-2 flex items-center justify-center text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 hover:text-[#1677B8] border border-slate-300 rounded-[2px] transition-colors cursor-pointer">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($documents->hasMorePages())
                        <a href="{{ $documents->nextPageUrl() }}" class="h-7 px-2.5 flex items-center justify-center text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 hover:text-[#1677B8] border border-slate-300 rounded-[2px] transition-colors cursor-pointer" title="Halaman Berikutnya">
                            <i class="ph ph-caret-right text-xs"></i>
                        </a>
                    @else
                        <span class="h-7 px-2.5 flex items-center justify-center text-xs font-bold text-slate-400 bg-white border border-slate-200 rounded-[2px] cursor-not-allowed">
                            <i class="ph ph-caret-right text-xs"></i>
                        </span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

</div>

<!-- MODAL ACTION & ALASAN KETERLAMBATAN SLA (> 14 HARI) -->
<div id="slaModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-[#002b5c] via-[#1677B8] to-[#0ea5e9] text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-warning-octagon text-lg text-amber-300"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Tindak Lanjut Keterlambatan SLA</h3>
                    <p class="text-[10px] text-white/80 font-medium">Target Persetujuan: Maksimal 13 Hari</p>
                </div>
            </div>
            <button type="button" onclick="closeSlaModal()" class="text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <form id="slaActionForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Judul Dokumen</label>
                <div id="slaDocTitle" class="text-xs font-bold text-slate-900 bg-slate-50 p-2.5 rounded-md border border-slate-200">
                    -
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-md p-3 text-amber-900 text-xs">
                <div class="font-bold flex items-center gap-1.5">
                    <i class="ph ph-info"></i>
                    <span>Durasi Proses: <span id="slaDocDays" class="text-amber-950 font-extrabold">0</span> Hari (Melewati Target 13 Hari)</span>
                </div>
                <p class="text-[10.5px] mt-1 text-amber-800">Harap berikan penjelasan kendala / alasan mengapa proses persetujuan dokumen ini melebihi batas target SLA 14 hari.</p>
            </div>

            <div>
                <label for="sla_notes" class="block text-xs font-bold text-slate-800 mb-1.5 flex items-center justify-between">
                    <span>Alasan & Tindak Lanjut Keterlambatan <span class="text-rose-500">*</span></span>
                    <span id="slaLastUpdated" class="text-[10px] font-normal text-slate-400"></span>
                </label>
                <textarea id="sla_notes" name="sla_notes" rows="4" required placeholder="Contoh: Menunggu konfirmasi revisi lampiran teknis dari Dept Head terkait, atau kendala jadwal review pimpinan..." class="w-full text-xs p-3 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-medium text-slate-800"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <x-interactive-button text="Batal" variant="outline" type="button" onclick="closeSlaModal()" />
                <x-interactive-button text="Konfirmasi" variant="blue" icon="ph ph-floppy-disk text-base" type="submit" />
            </div>
        </form>
    </div>
</div>

<!-- MODAL 1: UPLOAD BUKTI SOSIALISASI SOP (POIN 3) -->
<div id="uploadSocializationModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-[#002b5c] via-[#1677B8] to-[#0ea5e9] text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-megaphone text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Unggah Bukti Sosialisasi SOP</h3>
                    <p class="text-[10px] text-white/80 font-medium">Bukti pelaksanaan implementasi SOP di lapangan</p>
                </div>
            </div>
            <button type="button" onclick="closeUploadSocializationModal()" class="text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <form id="uploadSocializationForm" method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Judul Dokumen SOP</label>
                <div id="socDocTitle" class="text-xs font-bold text-slate-900 bg-slate-50 p-2.5 rounded-md border border-slate-200">
                    -
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="socialization_date" class="block text-xs font-bold text-slate-800 mb-1">Tanggal Sosialisasi <span class="text-rose-500">*</span></label>
                    <input type="date" id="socialization_date" name="socialization_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs p-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-bold text-slate-800">
                </div>
                <div>
                    <label for="attendance_file" class="block text-xs font-bold text-slate-800 mb-1">Daftar Hadir (PDF/Gambar) <span class="text-rose-500">*</span></label>
                    <x-file-input name="attendance_file" accept=".pdf,.jpg,.jpeg,.png" label="Pilih daftar hadir" hint="PDF, JPG, PNG (Maks 10 MB)" :required="true" :maxSize="10" />
                </div>
            </div>

            <div>
                <label for="photos" class="block text-xs font-bold text-slate-800 mb-1">Foto Dokumentasi Kegiatan <span class="text-rose-500">*</span></label>
                <x-file-input name="photos[]" accept="image/*" label="Pilih foto dokumentasi" hint="Pilih 1 sampai 10 foto (JPG, PNG, Maks. 10 MB/foto)" :multiple="true" :required="true" :maxSize="10" />
            </div>

            <div>
                <label for="soc_notes" class="block text-xs font-bold text-slate-800 mb-1">Catatan / Ringkasan Sosialisasi</label>
                <textarea id="soc_notes" name="notes" rows="3" placeholder="Deskripsikan pelaksanaan sosialisasi, peserta yang hadir, dan arahan PIC..." class="w-full text-xs p-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-medium text-slate-800"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <x-interactive-button text="Batal" variant="outline" type="button" onclick="closeUploadSocializationModal()" />
                <x-interactive-button text="Konfirmasi" variant="blue" icon="ph ph-paper-plane-right text-base" type="submit" />
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: LIHAT BUKTI SOSIALISASI SOP (POIN 3) -->
<div id="viewSocializationModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-2xl w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-[#002b5c] via-[#1677B8] to-[#0ea5e9] text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-images text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Bukti Sosialisasi & Daftar Hadir</h3>
                    <p id="viewSocSubTitle" class="text-[10px] text-white/80 font-medium">Dokumentasi kegiatan sosialisasi SOP</p>
                </div>
            </div>
            <button type="button" onclick="closeViewSocializationModal()" class="text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <div id="viewSocContent" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
            <div class="animate-pulse space-y-3">
                <div class="h-4 bg-slate-200 rounded w-1/3"></div>
                <div class="h-20 bg-slate-100 rounded"></div>
            </div>
        </div>

        <div class="bg-slate-50 p-4 border-t border-slate-200 flex justify-end">
            <x-interactive-button text="Tutup" variant="outline" type="button" onclick="closeViewSocializationModal()" />
        </div>
    </div>
</div>

<!-- MODAL 3: PENGAJUAN REQUEST REVISI SOP OLEH USER (POIN 9) -->
<div id="requestRevisionModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-[#002b5c] via-[#1677B8] to-[#0ea5e9] text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-pencil-simple-line text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Ajukan Permohonan Revisi SOP</h3>
                    <p class="text-[10px] text-white/80 font-medium">Permohonan izin revisi ke Admin QMS</p>
                </div>
            </div>
            <button type="button" onclick="closeRequestRevisionModal()" class="text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <form id="requestRevisionForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Judul Dokumen SOP</label>
                <div id="reqRevDocTitle" class="text-xs font-bold text-slate-900 bg-slate-50 p-2.5 rounded-md border border-slate-200">
                    -
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-md p-3 text-[#002b5c] text-xs">
                <div class="font-bold flex items-center gap-1.5">
                    <i class="ph ph-info"></i>
                    <span>Ketentuan Permohonan Revisi</span>
                </div>
                <p class="text-[10.5px] mt-1 text-slate-700">Setelah Admin QMS menyetujui permohonan ini, Anda dapat langsung mengunggah berkas naskah revisi baru.</p>
            </div>

            <div>
                <label for="req_reason" class="block text-xs font-bold text-slate-800 mb-1.5">Alasan / Latar Belakang Revisi <span class="text-rose-500">*</span></label>
                <textarea id="req_reason" name="reason" rows="4" required placeholder="Jelaskan alasan perubahan proses operasional, penambahan klausul, atau penyesuaian regulasi..." class="w-full text-xs p-3 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-medium text-slate-800"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <x-interactive-button text="Batal" variant="outline" type="button" onclick="closeRequestRevisionModal()" />
                <x-interactive-button text="Konfirmasi" variant="blue" icon="ph ph-paper-plane-right text-base" type="submit" />
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: ADMIN TINJAU & APPROVE REQUEST REVISI (POIN 9) -->
<div id="reviewRevisionModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-[#002b5c] via-[#1677B8] to-[#0ea5e9] text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-shield-check text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Tinjau Permohonan Revisi User</h3>
                    <p class="text-[10px] text-white/80 font-medium">Persetujuan usulan revisi SOP</p>
                </div>
            </div>
            <button type="button" onclick="closeReviewRevisionModal()" class="text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Judul SOP & Pemohon</label>
                <div class="bg-slate-50 p-3 rounded-md border border-slate-200 space-y-1">
                    <p id="revModalDocTitle" class="text-xs font-bold text-slate-900">-</p>
                    <p class="text-[11px] text-slate-600">Pemohon: <strong id="revModalUser">-</strong></p>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Alasan Pengajuan</label>
                <div id="revModalReason" class="text-xs bg-amber-50 text-amber-950 p-3 rounded-md border border-amber-200 font-medium whitespace-pre-line leading-relaxed">
                    -
                </div>
            </div>

            <!-- Approve Form -->
            <form id="approveRevisionForm" method="POST" action="" class="space-y-3 pt-2 border-t border-slate-100">
                @csrf
                <div>
                    <label for="admin_rev_notes" class="block text-xs font-bold text-slate-800 mb-1">Catatan Admin (Opsional)</label>
                    <input type="text" id="admin_rev_notes" name="admin_notes" placeholder="Catatan persetujuan untuk pembuat dokumen..." class="w-full text-xs p-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 font-medium text-slate-800">
                </div>

                <div class="flex items-center justify-between gap-2 pt-2">
                    <x-interactive-button text="Tolak" variant="danger" icon="ph ph-x-circle text-base" type="button" onclick="submitRejectRevision()" />
                    <x-interactive-button text="Konfirmasi" variant="success" icon="ph ph-check-circle text-base" type="submit" />
                </div>
            </form>

            <form id="rejectRevisionForm" method="POST" action="" class="hidden">
                @csrf
                <input type="hidden" id="reject_admin_notes" name="admin_notes" value="Permohonan revisi belum dapat disetujui.">
            </form>
        </div>
    </div>
</div>

<!-- CHART.JS & DONUT CHART INTEGRATION -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Animated Chart.js Donut Chart
    const defaultLabel = "Total SOP";
    const defaultValue = "{{ $totalSop }}";
    const defaultPercentage = "[100%]";

    const centerLabel = document.getElementById('donut-center-label');
    const centerValue = document.getElementById('donut-center-value');
    const centerPct = document.getElementById('donut-center-percentage');

    const ctxStatus = document.getElementById('documentStatusChart');
    let statusChartInstance = null;

    if (ctxStatus) {
        statusChartInstance = new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Aktif & Sah', 'Perlu Revisi', 'Menunggu Review', 'Usang / Obsolete'],
                datasets: [{
                    data: [
                        {{ (int)$stats['active'] }},
                        {{ (int)$stats['need_revision'] }},
                        {{ (int)$stats['waiting'] }},
                        {{ (int)$stats['obsolete'] }}
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#00b4d8', '#94a3b8'],
                    hoverBackgroundColor: ['#059669', '#d97706', '#0284c7', '#64748b'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1200,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 10,
                        cornerRadius: 6,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 },
                        callbacks: {
                            label: function(context) {
                                const total = {{ $totalSop }} || 1;
                                const val = context.parsed;
                                const pct = Math.round((val / total) * 100);
                                return ` ${context.label}: ${val} Dokumen (${pct}%)`;
                            }
                        }
                    }
                },
                onHover: (event, activeElements) => {
                    if (activeElements && activeElements.length > 0) {
                        const index = activeElements[0].index;
                        const labels = ['SOP Aktif & Sah', 'Dalam Proses Revisi', 'Menunggu Review', 'Usang / Obsolete'];
                        const values = [{{ (int)$stats['active'] }}, {{ (int)$stats['need_revision'] }}, {{ (int)$stats['waiting'] }}, {{ (int)$stats['obsolete'] }}];
                        const colors = ['#10b981', '#f59e0b', '#00b4d8', '#94a3b8'];
                        const total = {{ $totalSop }} || 1;
                        const val = values[index];
                        const pct = Math.round((val / total) * 100);

                        if (centerLabel) centerLabel.textContent = labels[index];
                        if (centerValue) centerValue.textContent = val;
                        if (centerPct) {
                            centerPct.textContent = `[${pct}%]`;
                            centerPct.style.color = colors[index];
                        }
                    } else {
                        resetDonut();
                    }
                }
            }
        });
    }

    function resetDonut() {
        if (centerLabel) centerLabel.textContent = defaultLabel;
        if (centerValue) centerValue.textContent = defaultValue;
        if (centerPct) {
            centerPct.textContent = defaultPercentage;
            centerPct.style.color = '#10b981';
        }
        document.querySelectorAll('.donut-legend-item').forEach(el => {
            el.classList.remove('bg-blue-50', 'border-blue-300', 'shadow-sm');
        });
    }

    // Attach listeners to Legend items for sync with donut
    document.querySelectorAll('.donut-legend-item').forEach(legend => {
        legend.addEventListener('mouseenter', function() {
            const label = this.dataset.label;
            const val = this.dataset.value;
            const pct = this.dataset.percentage;
            const color = this.dataset.color;

            if (centerLabel) centerLabel.textContent = label;
            if (centerValue) centerValue.textContent = val;
            if (centerPct) {
                centerPct.textContent = `[${pct}%]`;
                centerPct.style.color = color || '#1677B8';
            }
            this.classList.add('bg-blue-50', 'border-blue-300', 'shadow-sm');
        });
        legend.addEventListener('mouseleave', resetDonut);
    });

    // 2. Bar Chart: Usia SOP Aktif Sejak Dibuat (POIN 2)
    const ctxAge = document.getElementById('activeSopAgeChart');
    if (ctxAge) {
        new Chart(ctxAge, {
            type: 'bar',
            data: {
                labels: {!! $chartAgeLabels !!},
                datasets: [
                    {
                        label: 'Jumlah SOP Aktif',
                        data: {!! $chartAgeData !!},
                        backgroundColor: [
                            '#38bdf8', // < 1 Bulan
                            '#0ea5e9', // 1-6 Bulan
                            '#10b981', // 6-12 Bulan
                            '#f59e0b', // 1-2 Tahun
                            '#8b5cf6'  // > 2 Tahun
                        ],
                        borderRadius: 4,
                        barPercentage: 0.65,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 10,
                        cornerRadius: 6,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.parsed.y} Dokumen Aktif`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: '600' }, color: '#475569' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: { stepSize: 1, precision: 0, font: { size: 10 }, color: '#64748b' }
                    }
                }
            }
        });
    }

    // 3. Multi-Bar Chart: Tren Tahunan SOP & Revisi
    const ctxTrend = document.getElementById('yearlyTrendBarChart');
    if (ctxTrend) {
        new Chart(ctxTrend, {
            type: 'bar',
            data: {
                labels: {!! $chartYears !!},
                datasets: [
                    {
                        label: 'Total SOP',
                        data: {!! $chartTotals !!},
                        backgroundColor: '#1677B8',
                        hoverBackgroundColor: '#125d91',
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'SOP Aktif',
                        data: {!! $chartActives !!},
                        backgroundColor: '#10b981',
                        hoverBackgroundColor: '#059669',
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Perlu Revisi',
                        data: {!! $chartRevisions !!},
                        backgroundColor: '#f59e0b',
                        hoverBackgroundColor: '#d97706',
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 10,
                        cornerRadius: 6,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 },
                        callbacks: {
                            label: function(context) {
                                return ` ${context.dataset.label}: ${context.parsed.y} Dokumen`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: '600' }, color: '#475569' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: { stepSize: 1, precision: 0, font: { size: 10 }, color: '#64748b' }
                    }
                }
            }
        });
    }
});

function switchRightChart(type) {
    const wrapAge = document.getElementById('wrapAgeChart');
    const wrapTrend = document.getElementById('wrapTrendChart');
    const btnAge = document.getElementById('btnTabAge');
    const btnTrend = document.getElementById('btnTabTrend');
    const title = document.getElementById('chartTabTitle');

    if (type === 'age') {
        wrapAge.classList.remove('hidden');
        wrapTrend.classList.add('hidden');
        btnAge.className = 'px-2 py-0.5 font-bold rounded bg-white text-[#1677B8] shadow-xs cursor-pointer';
        btnTrend.className = 'px-2 py-0.5 font-semibold text-slate-600 hover:text-slate-900 cursor-pointer';
        title.textContent = 'Usia Dokumen Aktif';
    } else {
        wrapAge.classList.add('hidden');
        wrapTrend.classList.remove('hidden');
        btnTrend.className = 'px-2 py-0.5 font-bold rounded bg-white text-[#1677B8] shadow-xs cursor-pointer';
        btnAge.className = 'px-2 py-0.5 font-semibold text-slate-600 hover:text-slate-900 cursor-pointer';
        title.textContent = 'Tren Penerbitan & Revisi';
    }
}

// Modal SLA Actions
function openSlaModal(docId, title, days, notes, actionUser, actionTime) {
    const modal = document.getElementById('slaModal');
    const form = document.getElementById('slaActionForm');
    const docTitle = document.getElementById('slaDocTitle');
    const docDays = document.getElementById('slaDocDays');
    const notesField = document.getElementById('sla_notes');
    const lastUpdated = document.getElementById('slaLastUpdated');

    form.action = `/admin/documents/${docId}/sla-action`;
    docTitle.textContent = title;
    docDays.textContent = days;
    notesField.value = notes || '';

    if (actionUser && actionTime) {
        lastUpdated.textContent = `Terakhir oleh: ${actionUser} (${actionTime})`;
    } else {
        lastUpdated.textContent = '';
    }

    modal.classList.remove('hidden');
}

function closeSlaModal() {
    const modal = document.getElementById('slaModal');
    modal.classList.add('hidden');
}

// Modal Upload Sosialisasi
function openUploadSocializationModal(docId, title) {
    const modal = document.getElementById('uploadSocializationModal');
    const form = document.getElementById('uploadSocializationForm');
    const docTitle = document.getElementById('socDocTitle');

    form.action = `/documents/${docId}/socialization`;
    docTitle.textContent = title;
    modal.classList.remove('hidden');
}

function closeUploadSocializationModal() {
    const modal = document.getElementById('uploadSocializationModal');
    modal.classList.add('hidden');
}

// Modal View Sosialisasi
function openViewSocializationModal(docId) {
    const modal = document.getElementById('viewSocializationModal');
    const content = document.getElementById('viewSocContent');
    modal.classList.remove('hidden');

    content.innerHTML = `
        <div class="text-center py-8">
            <i class="ph ph-spinner animate-spin text-3xl text-[#1677B8]"></i>
            <p class="text-xs text-slate-500 mt-2 font-medium">Memuat data sosialisasi...</p>
        </div>
    `;

    fetch(`/documents/${docId}/socialization`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.socialization) {
                const s = data.socialization;
                let photosHtml = '';
                if (s.photos_urls && s.photos_urls.length > 0) {
                    photosHtml = `
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-700">Foto-Foto Dokumentasi Kegiatan (${s.photos_urls.length} Foto):</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                ${s.photos_urls.map(url => `
                                    <a href="${url}" target="_blank" class="group block rounded-lg overflow-hidden border border-slate-200 shadow-sm relative aspect-video bg-slate-100">
                                        <img src="${url}" alt="Dokumentasi Sosialisasi" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300">
                                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold">
                                            <i class="ph ph-magnifying-glass-plus text-base mr-1"></i> Perbesar
                                        </div>
                                    </a>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }

                content.innerHTML = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-4 rounded-lg border border-slate-200 text-xs">
                            <div>
                                <span class="text-slate-500 font-semibold block text-[11px]">Tanggal Pelaksanaan:</span>
                                <strong class="text-slate-900">${s.socialization_date}</strong>
                            </div>
                            <div>
                                <span class="text-slate-500 font-semibold block text-[11px]">Diunggah Oleh:</span>
                                <strong class="text-slate-900">${s.user_name} (${s.submitted_at})</strong>
                            </div>
                        </div>

                        ${s.notes ? `
                            <div class="bg-amber-50/70 border border-amber-200 rounded-lg p-3.5 text-xs text-amber-950">
                                <span class="font-bold block text-[11px] uppercase tracking-wider text-amber-900 mb-1">Catatan Sosialisasi:</span>
                                <p class="leading-relaxed font-medium whitespace-pre-line">${s.notes}</p>
                            </div>
                        ` : ''}

                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2">
                                <i class="ph ph-file-pdf text-xl text-[#1677B8]"></i>
                                <div>
                                    <strong class="text-slate-900">Berkas Bukti Daftar Hadir</strong>
                                    <p class="text-[10px] text-slate-500">Telah ditandatangani oleh peserta sosialisasi</p>
                                </div>
                            </div>
                            <a href="${s.attendance_url}" target="_blank" class="px-3.5 py-1.5 bg-[#1677B8] hover:bg-[#125d91] text-white rounded font-bold text-xs flex items-center gap-1 shadow-sm transition-all">
                                <span>Buka File</span>
                                <i class="ph ph-arrow-square-out"></i>
                            </a>
                        </div>

                        ${photosHtml}
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="text-center py-6 text-slate-500 text-xs font-semibold">
                        Data sosialisasi tidak ditemukan.
                    </div>
                `;
            }
        })
        .catch(() => {
            content.innerHTML = `
                <div class="text-center py-6 text-rose-500 text-xs font-bold">
                    Gagal memuat data sosialisasi.
                </div>
            `;
        });
}

function closeViewSocializationModal() {
    const modal = document.getElementById('viewSocializationModal');
    modal.classList.add('hidden');
}

// Modal Request Revisi User
function openRequestRevisionModal(docId, title) {
    const modal = document.getElementById('requestRevisionModal');
    const form = document.getElementById('requestRevisionForm');
    const docTitle = document.getElementById('reqRevDocTitle');

    form.action = `/documents/${docId}/request-revision`;
    docTitle.textContent = title;
    modal.classList.remove('hidden');
}

function closeRequestRevisionModal() {
    const modal = document.getElementById('requestRevisionModal');
    modal.classList.add('hidden');
}

// Modal Admin Review Revision Request
function openReviewRevisionModal(reqId, docTitle, userName, reason) {
    const modal = document.getElementById('reviewRevisionModal');
    const approveForm = document.getElementById('approveRevisionForm');
    const rejectForm = document.getElementById('rejectRevisionForm');
    const titleEl = document.getElementById('revModalDocTitle');
    const userEl = document.getElementById('revModalUser');
    const reasonEl = document.getElementById('revModalReason');

    approveForm.action = `/admin/revision-requests/${reqId}/approve`;
    rejectForm.action = `/admin/revision-requests/${reqId}/reject`;
    titleEl.textContent = docTitle;
    userEl.textContent = userName;
    reasonEl.textContent = reason;

    modal.classList.remove('hidden');
}

function closeReviewRevisionModal() {
    const modal = document.getElementById('reviewRevisionModal');
    modal.classList.add('hidden');
}

function submitRejectRevision() {
    const reason = prompt('Masukkan alasan penolakan permohonan revisi:');
    if (reason && reason.trim() !== '') {
        const rejectNotes = document.getElementById('reject_admin_notes');
        const rejectForm = document.getElementById('rejectRevisionForm');
        rejectNotes.value = reason.trim();
        rejectForm.submit();
    }
}
</script>
@endsection


