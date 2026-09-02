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
                    <h1 class="text-sm font-black text-slate-900 tracking-tight">PT PRIMA KARYA MANDIRI (PKM GROUP)</h1>
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
    <div class="screen-top-banner bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#ffe16e] font-bold flex items-center gap-1">
                    <i class="ph ph-squares-four text-base"></i>
                    <span>Dashboard</span>
                </a>
                <span>/</span>
                <span class="text-[#ffe16e] font-bold capitalize">Tracking & Siklus SOP</span>
            </div>
            <h2 class="text-xl font-extrabold tracking-tight capitalize">Monitoring & Tracking Dokumen SOP</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">Pantau statistik kurun tahun, riwayat revisi, status keaktifan, dan evaluasi berkala di seluruh unit.</p>
        </div>

        <div class="flex items-center gap-2 no-print">
            <button onclick="window.print()" class="px-4 py-2.5 bg-white/15 hover:bg-white/25 text-white rounded-md font-bold text-xs capitalize tracking-wider shadow-sm transition-all flex items-center gap-2 border border-white/20 cursor-pointer">
                <i class="ph ph-printer text-lg"></i>
                <span>Cetak Rekap</span>
            </button>
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

    <!-- VISUAL CHART ANALYTICS SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 no-print">
        <!-- CHART 1: SIKLUS HIDUP & STATUS DOKUMEN SOP (5 COLS) -->
        <div class="lg:col-span-5 bg-white rounded-lg p-5 shadow-sm border border-sand-200/60 flex flex-col justify-between space-y-4">
            <div class="flex items-center justify-between border-b border-sand-200/40 pb-3">
                <div class="flex items-center space-x-2 text-xs font-bold text-on-surface">
                    <i class="ph ph-chart-pie-slice text-base text-[#1677B8]"></i>
                    <span class="capitalize">Distribusi Status & Siklus SOP</span>
                </div>
                <span class="text-[10px] text-on-surface-variant font-semibold">Total {{ $stats['total'] }} Dokumen</span>
            </div>

            <!-- INTERACTIVE SVG DONUT CHART & LEGEND -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 py-2 flex-1">
                <!-- SVG DONUT CONTAINER -->
                <div id="donut-wrapper" class="relative flex items-center justify-center flex-shrink-0" style="width: {{ $size }}px; height: {{ $size }}px;">
                    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" class="overflow-visible -rotate-90 origin-center pointer-events-none">
                        <!-- Base background ring -->
                        <circle
                            cx="{{ $size / 2 }}"
                            cy="{{ $size / 2 }}"
                            r="{{ $radius }}"
                            fill="none"
                            stroke="#f1f5f9"
                            stroke-width="{{ $strokeWidth }}"
                            style="pointer-events: none;"
                        />
                        
                        <!-- Dynamic Data Segments -->
                        @foreach($donutSegments as $idx => $seg)
                            @if($seg['value'] > 0 && $totalSop > 0)
                                @php
                                    $pct = ($seg['value'] / $totalSop) * 100;
                                    $strokeDasharray = (($pct / 100) * $circumference) . ' ' . $circumference;
                                    $strokeDashoffset = -($cumulativePercent / 100) * $circumference;
                                    $cumulativePercent += $pct;
                                @endphp
                                <circle
                                    id="{{ $seg['id'] }}-circle"
                                    cx="{{ $size / 2 }}"
                                    cy="{{ $size / 2 }}"
                                    r="{{ $radius }}"
                                    fill="none"
                                    stroke="{{ $seg['color'] }}"
                                    stroke-width="{{ $strokeWidth }}"
                                    stroke-dasharray="{{ $strokeDasharray }}"
                                    stroke-dashoffset="{{ $strokeDashoffset }}"
                                    stroke-linecap="round"
                                    data-id="{{ $seg['id'] }}"
                                    data-label="{{ $seg['label'] }}"
                                    data-value="{{ $seg['value'] }}"
                                    data-percentage="{{ number_format($pct, 0) }}"
                                    data-color="{{ $seg['color'] }}"
                                    class="donut-segment transition-all duration-300 origin-center cursor-pointer"
                                    style="transform-origin: center; pointer-events: stroke;"
                                />
                            @endif
                        @endforeach
                    </svg>

                    <!-- Center Dynamic Content -->
                    <div id="donut-center-container" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center px-3 transition-all duration-200">
                        <p id="donut-center-label" class="text-[10px] font-semibold text-on-surface-variant truncate max-w-[110px] transition-all duration-200">
                            Total SOP
                        </p>
                        <p id="donut-center-value" class="text-2xl font-black text-on-surface leading-tight transition-all duration-200">
                            {{ $totalSop }}
                        </p>
                        <p id="donut-center-percentage" class="text-[10px] font-bold text-emerald-600 transition-all duration-200">
                            [100%]
                        </p>
                    </div>
                </div>

                <!-- LEGEND LIST -->
                <div class="space-y-1.5 w-full sm:w-auto text-xs flex-1 min-w-0">
                    @foreach($donutSegments as $seg)
                        @php
                            $pct = $totalSop > 0 ? number_format(($seg['value'] / $totalSop) * 100, 0) : 0;
                        @endphp
                        <div id="{{ $seg['id'] }}-legend" 
                             data-id="{{ $seg['id'] }}"
                             data-label="{{ $seg['label'] }}"
                             data-value="{{ $seg['value'] }}"
                             data-percentage="{{ $pct }}"
                             data-color="{{ $seg['color'] }}"
                             class="donut-legend-item flex items-center justify-between gap-2 p-1.5 rounded-md border border-slate-200/60 bg-canvas hover:bg-slate-100/80 transition-all duration-200 cursor-pointer">
                            <div class="flex items-center gap-1.5 font-semibold text-on-surface text-[11px] truncate">
                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $seg['color'] }};"></span>
                                <span class="truncate">{{ $seg['short'] }}</span>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <span class="font-extrabold text-on-surface text-xs">{{ $seg['value'] }}</span>
                                <span class="text-[10px] text-on-surface-variant font-medium">({{ $pct }}%)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- CHART 2: TREN PENERBITAN & REVISI SOP PER TAHUN (7 COLS) -->
        <div class="lg:col-span-7 bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 flex flex-col justify-between space-y-4">
            <div class="flex items-center justify-between border-b border-sand-200/40 pb-3">
                <div class="flex items-center space-x-2 text-xs font-bold text-on-surface">
                    <i class="ph ph-chart-bar text-base text-[#1677B8]"></i>
                    <span class="capitalize">Tren Penerbitan & Revisi SOP per Tahun</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#1677B8] bg-blue-50 px-2 py-0.5 rounded border border-blue-200/60">
                        <span class="w-2 h-2 rounded-full bg-[#1677B8]"></span> Total
                    </span>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/60">
                        <span class="w-2 h-2 rounded-full bg-[#10b981]"></span> Aktif
                    </span>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200/60">
                        <span class="w-2 h-2 rounded-full bg-[#f59e0b]"></span> Revisi
                    </span>
                </div>
            </div>

            <!-- BAR CHART CANVAS -->
            <div class="h-64 relative w-full flex-1 flex items-center">
                <canvas id="yearlyTrendBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- TABEL DAFTAR MONITORING DOKUMEN -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4 print-table-wrapper">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-sand-200/40 pb-3">
            <div class="flex items-center space-x-2 text-xs font-bold text-on-surface">
                <i class="ph ph-table text-base text-[#1677B8] no-print"></i>
                <span class="capitalize">Daftar Dokumen & Status Siklus Hidup SOP</span>
            </div>
            <span class="text-xs text-on-surface-variant font-semibold">Menampilkan {{ $documents->count() }} dari {{ $documents->total() }} dokumen</span>
        </div>

        <div class="border border-sand-200/70 rounded-md bg-white overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="tracking-table w-full text-left border-collapse">
                    <thead class="bg-sand-50 border-b border-sand-200 text-[11px] font-semibold capitalize tracking-wider text-on-surface-variant">
                        <tr>
                            <th class="py-3 px-3 text-center w-10">#</th>
                            <th class="py-3 px-4">Nomor Dokumen</th>
                            <th class="py-3 px-4">Judul Dokumen SOP</th>
                            <th class="py-3 px-4 whitespace-nowrap">Unit / Departemen</th>
                            <th class="py-3 px-4 text-center whitespace-nowrap">Versi Revisi</th>
                            <th class="py-3 px-4 text-center whitespace-nowrap">Tahun / Tanggal</th>
                            <th class="py-3 px-4 text-center whitespace-nowrap">Status Berlaku</th>
                            <th class="py-3 px-4 text-right table-action-col no-print whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sand-200/50 text-xs">
                        @forelse($documents as $index => $doc)
                        <tr class="hover:bg-sand-50/50 transition-colors">
                            <td class="py-3 px-3 text-center font-bold text-on-surface-variant">
                                {{ $documents->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] font-semibold text-slate-800 whitespace-nowrap">
                                {{ $doc->doc_number ?? 'NOMOR BELUM DIATUR' }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-on-surface leading-snug">
                                    {{ $doc->title }}
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 bg-slate-100 border border-slate-200/80 rounded text-[10px] font-bold text-slate-800 badge-print inline-block whitespace-nowrap">
                                    {{ $doc->department }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold badge-print {{ $doc->doc_revision ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                                    Rev {{ $doc->doc_revision ?? '0' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center text-[11px] text-on-surface-variant">
                                <div>{{ $doc->created_at ? $doc->created_at->format('d M Y') : '-' }}</div>
                                <span class="text-[9px] font-bold text-[#1677B8]">Thn {{ $doc->created_at ? $doc->created_at->format('Y') : '-' }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($doc->status === 'active')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center justify-center gap-1 badge-print">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 no-print"></span>
                                        <span>Aktif (In-Use)</span>
                                    </span>
                                @elseif($doc->status === 'need_revision')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize tracking-wider bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center justify-center gap-1 badge-print">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 no-print"></span>
                                        <span>Revisi Berjalan</span>
                                    </span>
                                @elseif($doc->status === 'waiting')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize tracking-wider bg-blue-50 text-blue-700 border border-blue-200 inline-flex items-center justify-center gap-1 badge-print">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 no-print"></span>
                                        <span>Menunggu Review</span>
                                    </span>
                                @elseif($doc->status === 'obsolete')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize tracking-wider bg-slate-100 text-slate-600 border border-slate-300 inline-flex items-center justify-center gap-1 badge-print">
                                        <span>Usang / Arsip</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize tracking-wider bg-gray-100 text-gray-700 border border-gray-200 badge-print">
                                        {{ $doc->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right table-action-col no-print">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.BU.detail', $doc->id) }}" class="p-1.5 bg-canvas hover:bg-sand-100 text-on-surface border border-sand-200 rounded-md transition-colors shadow-sm" title="Audit Detail Dokumen">
                                        <i class="ph ph-eye text-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-on-surface-variant">
                                <i class="ph ph-folder-open text-4xl text-sand-300 mb-2 block"></i>
                                <p class="text-xs font-bold text-on-surface">Tidak ada data dokumen SOP yang cocok dengan filter.</p>
                                <p class="text-[11px] text-on-surface-variant mt-0.5">Silakan ganti kriteria tahun, departemen, atau kata kunci pencarian.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION (HIDDEN IN PRINT) -->
            @if($documents->hasPages())
            <div class="pagination-container no-print p-4 border-t border-sand-200/60 bg-sand-50/50">
                {{ $documents->links() }}
            </div>
            @endif
        </div>
    </div>

</div>

<!-- CHART.JS & DONUT CHART INTEGRATION -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Interactive SVG Donut Chart Setup
    const defaultLabel = "Total SOP";
    const defaultValue = "{{ $totalSop }}";
    const defaultPercentage = "[100%]";

    const centerLabel = document.getElementById('donut-center-label');
    const centerValue = document.getElementById('donut-center-value');
    const centerPct = document.getElementById('donut-center-percentage');

    function highlightSegment(segId, label, value, percentage, color) {
        if (centerLabel) {
            centerLabel.textContent = label;
            centerLabel.style.color = '#111827';
        }
        if (centerValue) centerValue.textContent = value;
        if (centerPct) {
            centerPct.textContent = `[${percentage}%]`;
            centerPct.style.color = color || '#1677B8';
        }

        // Segments
        document.querySelectorAll('.donut-segment').forEach(el => {
            if (el.dataset.id === segId) {
                el.style.filter = `drop-shadow(0px 0px 8px ${color}) brightness(1.15)`;
                el.style.transform = 'scale(1.04)';
                el.style.opacity = '1';
            } else {
                el.style.filter = 'none';
                el.style.transform = 'scale(1)';
                el.style.opacity = '0.35';
            }
        });

        // Legends
        document.querySelectorAll('.donut-legend-item').forEach(el => {
            if (el.dataset.id === segId) {
                el.classList.add('bg-blue-50', 'border-blue-300', 'shadow-sm');
            } else {
                el.classList.remove('bg-blue-50', 'border-blue-300', 'shadow-sm');
            }
        });
    }

    function resetDonut() {
        if (centerLabel) {
            centerLabel.textContent = defaultLabel;
            centerLabel.style.color = '';
        }
        if (centerValue) centerValue.textContent = defaultValue;
        if (centerPct) {
            centerPct.textContent = defaultPercentage;
            centerPct.style.color = '#10b981';
        }

        document.querySelectorAll('.donut-segment').forEach(el => {
            el.style.filter = 'none';
            el.style.transform = 'scale(1)';
            el.style.opacity = '1';
        });

        document.querySelectorAll('.donut-legend-item').forEach(el => {
            el.classList.remove('bg-blue-50', 'border-blue-300', 'shadow-sm');
        });
    }

    // Attach listeners to SVG circles
    document.querySelectorAll('.donut-segment').forEach(circle => {
        circle.addEventListener('mouseenter', function() {
            highlightSegment(
                this.dataset.id,
                this.dataset.label,
                this.dataset.value,
                this.dataset.percentage,
                this.dataset.color
            );
        });
        circle.addEventListener('mouseleave', resetDonut);
    });

    // Attach listener to wrapper container
    const donutWrapper = document.getElementById('donut-wrapper');
    if (donutWrapper) {
        donutWrapper.addEventListener('mouseleave', resetDonut);
    }

    // Attach listeners to Legend items
    document.querySelectorAll('.donut-legend-item').forEach(legend => {
        legend.addEventListener('mouseenter', function() {
            highlightSegment(
                this.dataset.id,
                this.dataset.label,
                this.dataset.value,
                this.dataset.percentage,
                this.dataset.color
            );
        });
        legend.addEventListener('mouseleave', resetDonut);
    });

    // 2. Multi-Bar Chart: Tren Tahunan SOP & Revisi
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
                        ticks: { font: { size: 11, weight: '600' }, color: '#475569' }
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
</script>
@endsection
