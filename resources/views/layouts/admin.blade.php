<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-canvas text-on-surface">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') - e-QMS PT PKM Group</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        canvas: '#f8fafc',
                        'sand-50': '#f1f5f9',
                        'sand-100': '#e2e8f0',
                        'sand-200': '#cbd5e1',
                        'on-surface': '#0f172a',
                        'on-surface-variant': '#475569',
                        'charcoal-900': '#002b5c',
                        'gold-500': '#00a8cc',
                        'gold-fixed': '#00b4d8',
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="{{ asset('css/custom-typography.css') }}">

    <style>
        /* Global typography & subpixel smoothing */
        html, body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        /* Sidebar transition smoothing */
        aside#sidebar {
            transition: width 0.35s cubic-bezier(0.16, 1, 0.3, 1), transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease;
            will-change: width, transform;
            backface-visibility: hidden;
            transform: translateZ(0);
        }

        #main-container {
            transition: padding-left 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease, transform 0.25s ease;
            will-change: padding-left;
            backface-visibility: hidden;
            transform: translateZ(0);
        }

        /* Smooth fade-in & slide transitions for sidebar text elements */
        .sidebar-text,
        .sidebar-logo-text,
        .sidebar-profile-text,
        .sidebar-chevron-btn,
        .sidebar-logout-btn span {
            white-space: nowrap;
            opacity: 1;
            transform: translateX(0);
            transition: opacity 0.28s cubic-bezier(0.16, 1, 0.3, 1) 0.04s, transform 0.28s cubic-bezier(0.16, 1, 0.3, 1) 0.04s;
        }

        /* Aceternity-style Link Micro-interactions */
        aside .sidebar-item {
            transition: transform 0.18s ease, background-color 0.18s ease, color 0.18s ease, padding 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        aside.w-64 .sidebar-item:hover i,
        aside.w-64 .sidebar-item:hover span.material-symbols-outlined {
            transform: translateX(3px);
            transition: transform 0.18s ease;
        }

        /* Smooth submenu fade-in */
        .sidebar-sub-menu {
            transition: max-height 0.25s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.2s ease;
        }
        .sidebar-sub-menu:not(.hidden) {
            animation: sidebarSubFadeIn 0.22s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }
        @keyframes sidebarSubFadeIn {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Sidebar Collapsed Styles (Desktop only) */
        @media (min-width: 1024px) {
            aside.w-16 {
                width: 4.25rem !important; /* 68px */
            }
            aside.w-16 nav {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            /* Cleanly hide non-icon elements */
            aside.w-16 .sidebar-text,
            aside.w-16 .sidebar-logo-text,
            aside.w-16 .sidebar-profile-text,
            aside.w-16 .sidebar-chevron-btn,
            aside.w-16 .sidebar-sub-menu,
            aside.w-16 .sidebar-logout-btn span {
                display: none !important;
            }

            /* Brand header center */
            aside.w-16 .sidebar-brand-wrapper {
                padding: 1.25rem 0 !important;
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                cursor: pointer;
            }
            aside.w-16 .sidebar-brand-wrapper > div {
                margin: 0 auto !important;
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
            }

            /* Navigation menu item boxes (both dropdown parent divs and direct <a> links) */
            aside.w-16 nav .space-y-1 > div:first-child,
            aside.w-16 nav > a.sidebar-item,
            aside.w-16 .sidebar-item-active {
                width: 2.75rem !important;
                height: 2.75rem !important;
                margin-left: auto !important;
                margin-right: auto !important;
                border-radius: 0.5rem !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0 !important;
            }

            /* Active item style */
            aside.w-16 .sidebar-item-active {
                background-color: #f8fafc !important;
                color: #1677B8 !important;
            }

            /* Link inside dropdown wrapper */
            aside.w-16 .sidebar-item {
                width: 100% !important;
                height: 100% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0 !important;
                margin: 0 !important;
                border-radius: 0.5rem !important;
            }

            /* Icons perfectly centered */
            aside.w-16 .sidebar-item i,
            aside.w-16 .sidebar-item span.material-symbols-outlined {
                margin-right: 0 !important;
                margin-left: 0 !important;
                font-size: 1.35rem !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            /* Profile Card */
            aside.w-16 .sidebar-profile-card {
                width: 2.75rem !important;
                height: 2.75rem !important;
                padding: 0 !important;
                margin: 0 auto !important;
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                border-radius: 0.5rem !important;
            }
            aside.w-16 .sidebar-profile-card i {
                margin: 0 !important;
                font-size: 1.5rem !important;
            }

            /* Logout Button */
            aside.w-16 .sidebar-logout-btn {
                width: 2.75rem !important;
                height: 2.75rem !important;
                padding: 0 !important;
                margin: 0 auto !important;
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                border-radius: 0.5rem !important;
            }
            aside.w-16 .sidebar-logout-btn i {
                margin: 0 !important;
                font-size: 1.35rem !important;
            }
        }

        /* Mobile scroll fix: allow natural scroll on small screens */
        @media (max-width: 1023px) {
            body {
                overflow-y: auto !important;
                height: auto !important;
            }
            #main-container {
                overflow: visible !important;
                height: auto !important;
            }
            main {
                overflow: visible !important;
                height: auto !important;
                min-height: 100vh;
            }
        }

        /* Page transition */
        .page-fade {
            opacity: 0;
            transform: translateY(8px);
        }
        .page-fade-active {
            opacity: 1;
            transform: translateY(0);
        }
            /* ==========================================================================
           21st.dev / Magic UI — Interactive Hover Button Component
           ========================================================================== */
        .btn-interactive {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
            border-radius: 0.5rem;
            font-weight: 600 !important;
            font-size: 0.75rem !important;
            letter-spacing: 0.025em;
            padding: 0.625rem 1.25rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 0;
            text-align: center;
            background-color: #002b5c;
            color: #00b4d8;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            text-decoration: none !important;
        }

        .btn-interactive:focus-visible {
            outline: 3px solid rgba(22, 119, 184, 0.35);
            outline-offset: 2px;
        }

        .btn-interactive:disabled,
        .btn-interactive[aria-disabled="true"] {
            cursor: not-allowed;
            opacity: 0.55;
            pointer-events: none;
        }

        .btn-interactive-default {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transform: translateX(0);
            opacity: 1;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
        }

        .btn-interactive-dot {
            width: 0.375rem;
            height: 0.375rem;
            border-radius: 9999px;
            background-color: currentColor;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-interactive-hover {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transform: translateX(2rem);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 2;
            color: #ffffff;
        }

        .btn-interactive-bg {
            position: absolute;
            left: 15%;
            top: 40%;
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background-color: #00b4d8;
            opacity: 0;
            transform: scale(1);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
            z-index: 0;
        }

        /* Hover state transitions */
        .btn-interactive:hover .btn-interactive-default {
            transform: translateX(2rem);
            opacity: 0;
        }

        .btn-interactive:hover .btn-interactive-hover {
            transform: translateX(0);
            opacity: 1;
        }

        .btn-interactive:hover .btn-interactive-bg {
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            transform: scale(2.2);
            opacity: 1;
            border-radius: 0.5rem;
        }

        /* Variant: Primary Theme */
        .btn-interactive-primary {
            background-color: #002b5c;
            color: #00b4d8;
        }

        /* Variant: Blue Theme (#1677B8) */
        .btn-interactive-blue {
            background-color: #1677B8;
            color: #ffffff;
        }
        .btn-interactive-blue .btn-interactive-dot {
            background-color: #ffe16e;
        }
        .btn-interactive-blue .btn-interactive-bg {
            background-color: #1260a0;
        }
        .btn-interactive-blue .btn-interactive-hover {
            color: #ffffff;
        }

        /* Variant: Danger / Red (#dc2626) */
        .btn-interactive-danger {
            background-color: #dc2626;
            color: #ffffff;
        }
        .btn-interactive-danger .btn-interactive-dot {
            background-color: #ffffff;
        }
        .btn-interactive-danger .btn-interactive-bg {
            background-color: #b91c1c;
        }
        .btn-interactive-danger .btn-interactive-hover {
            color: #ffffff;
        }

        /* Variant: Success / Green (#059669) */
        .btn-interactive-success {
            background-color: #059669;
            color: #ffffff;
        }
        .btn-interactive-success .btn-interactive-dot {
            background-color: #ffffff;
        }
        .btn-interactive-success .btn-interactive-bg {
            background-color: #047857;
        }
        .btn-interactive-success .btn-interactive-hover {
            color: #ffffff;
        }

        /* Secondary navigation action: keeps the hover animation without a filled base. */
        .btn-interactive-outline {
            background-color: transparent;
            color: #00a8cc;
            border: 1px solid #00a8cc;
            box-shadow: none;
        }

        .btn-interactive-outline .btn-interactive-bg {
            background-color: #00a8cc;
        }

        .btn-interactive-outline .btn-interactive-hover {
            color: #ffffff;
        }

        /* Section theme header helper */
        .section-theme-header {
            background: linear-gradient(110deg, #1677B8 0%, #00a8cc 100%);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.16);
        }
        .section-theme-header .text-on-surface,
        .section-theme-header .text-on-surface-variant,
        .section-theme-header a,
        .section-theme-header span {
            color: rgba(255, 255, 255, 0.88);
        }
        .section-theme-header h2,
        .section-theme-header .font-medium,
        .section-theme-header .font-extrabold {
            color: #ffffff;
        }
        .section-theme-header a:hover {
            color: #ffe16e;
        }
        .section-theme-header > div:first-child > a {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.24);
        }

        /* Global Print Rules */
        @media print {
            aside#sidebar,
            #sidebar-open-btn,
            #sidebar-backdrop,
            header,
            nav,
            .no-print {
                display: none !important;
            }

            body, html {
                background: #ffffff !important;
                color: #000000 !important;
                height: auto !important;
                overflow: visible !important;
            }

            #main-container {
                padding-left: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                overflow: visible !important;
                height: auto !important;
            }

            main {
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                height: auto !important;
            }
        }
    </style>
</head>
<body class="bg-canvas text-on-surface h-full font-sans antialiased lg:overflow-hidden overflow-y-auto selection:bg-gold-fixed selection:text-charcoal-900">
    <div class="flex lg:h-screen lg:overflow-hidden min-h-screen w-full">
        
        <!-- SIDEBAR (Gradient Blue-Teal) -->
        <aside id="sidebar" class="w-16 bg-gradient-to-b from-[#1677B8] to-[#00b4d8] text-white flex-shrink-0 flex flex-col z-40 fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 overflow-x-hidden">
            <!-- Brand Header -->
            <div onclick="handleBrandClick()" class="sidebar-brand-wrapper p-5 flex items-center justify-between border-b border-white/10 text-center relative group transition-all duration-200">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-9 h-9 flex-shrink-0 bg-white rounded-md p-1 shadow-sm">
                        <img src="{{ asset('img/logopkm.png') }}" class="w-full h-full object-contain" alt="Logo PKM Group">
                    </div>
                    <div class="text-left sidebar-logo-text">
                        <span class="text-sm font-extrabold tracking-tight capitalize leading-none text-white block">e-QMS Portal</span>
                        <span class="text-[9px] font-bold text-[#ffe16e] capitalize tracking-[0.1em] mt-0.5 block">PT PKM Group</span>
                    </div>
                </div>
            </div>
            
            <!-- Navigation Links (Rounded-LG Floating Boxes) -->
            <nav class="mt-3 flex-1 px-3 space-y-1.5 overflow-y-auto overflow-x-hidden custom-scrollbar">
                <!-- Dashboard Dropdown Group -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between rounded-lg font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.tracking') ? 'bg-canvas text-[#1677B8] font-extrabold sidebar-item-active' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        <a href="{{ route('admin.dashboard') }}" class="sidebar-item flex items-center flex-1 py-2.5 px-3.5 min-w-0 {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.tracking') ? 'text-[#1677B8]' : 'text-white/85 hover:text-white' }}">
                            <i class="ph ph-squares-four text-lg mr-2.5 {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.tracking') ? 'text-[#1677B8]' : 'text-white/90' }}"></i>
                            <span class="sidebar-text truncate">Dashboard</span>
                        </a>
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-dash')" 
                                class="sidebar-chevron-btn p-2 mr-1 rounded hover:bg-black/10 focus:outline-none transition-colors flex items-center justify-center cursor-pointer border-none bg-transparent text-inherit" title="Buka Submenu Dashboard">
                            <i id="nav-dash-chevron" class="ph ph-caret-down text-xs sidebar-chevron transition-transform duration-200"></i>
                        </button>
                    </div>
                    <div id="nav-dash" class="sidebar-sub-menu hidden pl-6 pr-1 py-1 space-y-1">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center px-3 py-1.5 rounded-md text-[11px] font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-white text-[#1677B8] font-bold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-gauge text-sm mr-2"></i>
                            <span>Dashboard Utama</span>
                        </a>
                        <a href="{{ route('admin.tracking') }}" 
                           class="flex items-center px-3 py-1.5 rounded-md text-[11px] font-semibold transition-all {{ request()->routeIs('admin.tracking') ? 'bg-white text-[#1677B8] font-bold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-chart-donut text-sm mr-2"></i>
                            <span>Tracking Siklus SOP</span>
                        </a>
                        <a href="{{ route('admin.evaluations.index') }}" 
                           class="flex items-center px-3 py-1.5 rounded-md text-[11px] font-semibold transition-all {{ request()->routeIs('admin.evaluations.*') ? 'bg-white text-[#1677B8] font-bold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-clipboard-text text-sm mr-2"></i>
                            <span>Evaluasi Berkala SOP</span>
                        </a>
                    </div>
                </div>

                <!-- Kelola Akun -->
                <a href="{{ route('admin.users.index') }}" 
                   class="sidebar-item flex items-center px-3.5 py-2.5 rounded-lg font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.users.*') ? 'bg-canvas text-[#1677B8] font-extrabold sidebar-item-active' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                    <i class="ph ph-users text-lg mr-2.5 {{ request()->routeIs('admin.users.*') ? 'text-[#1677B8]' : 'text-white/90' }}"></i>
                    <span class="sidebar-text">Kelola Akun User</span>
                </a>

                <!-- Business Unit Dropdown Group -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between rounded-lg font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.BU.*') ? 'bg-canvas text-[#1677B8] font-extrabold sidebar-item-active' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        <a href="{{ route('admin.BU.index') }}" class="sidebar-item flex items-center flex-1 py-2.5 px-3.5 min-w-0 {{ request()->routeIs('admin.BU.*') ? 'text-[#1677B8]' : 'text-white/85 hover:text-white' }}">
                            <i class="ph ph-buildings text-xl mr-2.5 {{ request()->routeIs('admin.BU.*') ? 'text-[#1677B8]' : 'text-white/90' }}"></i>
                            <span class="sidebar-text truncate">Business Unit (BU)</span>
                        </a>
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-bu')" 
                                class="sidebar-chevron-btn p-2 mr-1 rounded hover:bg-black/10 focus:outline-none transition-colors flex items-center justify-center cursor-pointer border-none bg-transparent text-inherit" title="Buka Daftar Unit Bisnis">
                            <i id="nav-bu-chevron" class="ph ph-caret-down text-xs sidebar-chevron transition-transform duration-200"></i>
                        </button>
                    </div>
                    <div id="nav-bu" class="sidebar-sub-menu hidden pl-3 pr-1 py-1 space-y-1 max-h-72 overflow-y-auto custom-scrollbar">
                        <a href="{{ route('admin.BU.index') }}" 
                           class="flex items-center px-2.5 py-1.5 rounded-md text-[11px] font-bold capitalize transition-all {{ request()->routeIs('admin.BU.index') ? 'bg-white text-[#1677B8] shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                            <i class="ph ph-squares-four text-sm mr-2 text-white/90"></i>
                            <span>Semua Divisi & Unit</span>
                        </a>

                        <!-- Sub-Folder Divisi Retail -->
                        <div class="pt-0.5">
                            <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                <span class="text-[10px] font-extrabold capitalize text-white/90 tracking-wider flex items-center gap-1.5">
                                    <i class="ph ph-folder text-sm text-white/90"></i>
                                    <span>Divisi Retail</span>
                                </span>
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-bu-retail')" 
                                        class="p-1 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center">
                                    <i id="nav-bu-retail-chevron" class="ph ph-caret-down text-[10px] transition-transform duration-200"></i>
                                </button>
                            </div>
                            <div id="nav-bu-retail" class="hidden pl-3 space-y-0.5 mt-0.5 border-l border-white/20 ml-2">
                                @foreach(['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'INMAR (CNGM)'] as $buName)
                                <a href="{{ route('admin.BU.show', $buName) }}" class="flex items-center px-2 py-1 rounded text-[10px] font-semibold transition-all {{ request()->is('admin/BU/unit/' . urlencode($buName)) ? 'bg-white text-[#1677B8] font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                                    <span class="truncate">{{ $buName }}</span>
                                </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- Sub-Folder Divisi Komersil -->
                        <div class="pt-0.5">
                            <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                <span class="text-[10px] font-extrabold capitalize text-white/90 tracking-wider flex items-center gap-1.5">
                                    <i class="ph ph-folder text-sm text-white/90"></i>
                                    <span>Divisi Komersil</span>
                                </span>
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-bu-komersil')" 
                                        class="p-1 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center">
                                    <i id="nav-bu-komersil-chevron" class="ph ph-caret-down text-[10px] transition-transform duration-200"></i>
                                </button>
                            </div>
                            <div id="nav-bu-komersil" class="hidden pl-3 space-y-0.5 mt-0.5 border-l border-white/20 ml-2">
                                @foreach(['CPT & MHM', 'SBS', 'GVI'] as $buName)
                                <a href="{{ route('admin.BU.show', $buName) }}" class="flex items-center px-2 py-1 rounded text-[10px] font-semibold transition-all {{ request()->is('admin/BU/unit/' . urlencode($buName)) ? 'bg-white text-[#1677B8] font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                                    <span class="truncate">{{ $buName }}</span>
                                </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- Sub-Folder Divisi SCM -->
                        <div class="pt-0.5">
                            <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                <span class="text-[10px] font-extrabold capitalize text-white/90 tracking-wider flex items-center gap-1.5">
                                    <i class="ph ph-folder text-sm text-white/90"></i>
                                    <span>Divisi SCM</span>
                                </span>
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-bu-scm')" 
                                        class="p-1 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center">
                                    <i id="nav-bu-scm-chevron" class="ph ph-caret-down text-[10px] transition-transform duration-200"></i>
                                </button>
                            </div>
                            <div id="nav-bu-scm" class="hidden pl-3 space-y-0.5 mt-0.5 border-l border-white/20 ml-2">
                                @foreach(['PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA'] as $buName)
                                <a href="{{ route('admin.BU.show', $buName) }}" class="flex items-center px-2 py-1 rounded text-[10px] font-semibold transition-all {{ request()->is('admin/BU/unit/' . urlencode($buName)) ? 'bg-white text-[#1677B8] font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                                    <span class="truncate">{{ $buName }}</span>
                                </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- Sub-Folder Divisi FA -->
                        <div class="pt-0.5">
                            <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                <span class="text-[10px] font-extrabold capitalize text-white/90 tracking-wider flex items-center gap-1.5">
                                    <i class="ph ph-folder text-sm text-white/90"></i>
                                    <span>Divisi FA</span>
                                </span>
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-bu-fa')" 
                                        class="p-1 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center">
                                    <i id="nav-bu-fa-chevron" class="ph ph-caret-down text-[10px] transition-transform duration-200"></i>
                                </button>
                            </div>
                            <div id="nav-bu-fa" class="hidden pl-3 space-y-0.5 mt-0.5 border-l border-white/20 ml-2">
                                <a href="{{ route('admin.BU.show', 'KEUANGAN & ACCOUNTING') }}" class="flex items-center px-2 py-1 rounded text-[10px] font-semibold transition-all {{ request()->is('admin/BU/unit/' . urlencode('KEUANGAN & ACCOUNTING')) ? 'bg-white text-[#1677B8] font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                                    <span class="truncate">KEUANGAN & ACCOUNTING</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support Dropdown Group -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between rounded-lg font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.support.*') ? 'bg-canvas text-[#1677B8] font-extrabold sidebar-item-active' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        <a href="{{ route('admin.support.index') }}" class="sidebar-item flex items-center flex-1 py-2.5 px-3.5 min-w-0 {{ request()->routeIs('admin.support.*') ? 'text-[#1677B8]' : 'text-white/85 hover:text-white' }}">
                            <i class="ph ph-headset text-xl mr-2.5 {{ request()->routeIs('admin.support.*') ? 'text-[#1677B8]' : 'text-white/90' }}"></i>
                            <span class="sidebar-text truncate">Departemen Support</span>
                        </a>
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-support')" 
                                class="sidebar-chevron-btn p-2 mr-1 rounded hover:bg-black/10 focus:outline-none transition-colors flex items-center justify-center cursor-pointer border-none bg-transparent text-inherit" title="Buka Daftar Departemen Support">
                            <i id="nav-support-chevron" class="ph ph-caret-down text-xs sidebar-chevron transition-transform duration-200"></i>
                        </button>
                    </div>
                    <div id="nav-support" class="sidebar-sub-menu hidden pl-4 pr-1 py-1 space-y-1 max-h-52 overflow-y-auto custom-scrollbar">
                        <a href="{{ route('admin.support.index') }}" 
                           class="flex items-center px-2.5 py-1.5 rounded-md text-[11px] font-bold capitalize transition-all {{ request()->routeIs('admin.support.index') ? 'bg-white text-[#1677B8] shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                            <i class="ph ph-squares-four text-sm mr-2 text-white/90"></i>
                            <span>Semua Departemen</span>
                        </a>
                        @foreach(['HC', 'IT', 'HSE', 'QMS', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'] as $dept)
                        <a href="{{ route('admin.support.show', $dept) }}" class="flex items-center px-2.5 py-1 rounded text-[10px] font-semibold transition-all {{ request()->is('admin/support/' . urlencode($dept)) ? 'bg-white text-[#1677B8] font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                            <span>{{ $dept }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- E-Library Dropdown Group -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between rounded-lg font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.library.*') || request()->routeIs('library.*') ? 'bg-canvas text-[#1677B8] font-extrabold sidebar-item-active' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        <a href="{{ route('admin.library.index') }}" class="sidebar-item flex items-center flex-1 py-2.5 px-3.5 min-w-0 {{ request()->routeIs('admin.library.*') || request()->routeIs('library.*') ? 'text-[#1677B8]' : 'text-white/85 hover:text-white' }}">
                            <i class="ph ph-books text-xl mr-2.5 {{ request()->routeIs('admin.library.*') || request()->routeIs('library.*') ? 'text-[#1677B8]' : 'text-white/90' }}"></i>
                            <span class="sidebar-text truncate">E-Library Catalog</span>
                        </a>
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-library')" 
                                class="sidebar-chevron-btn p-2 mr-1 rounded hover:bg-black/10 focus:outline-none transition-colors flex items-center justify-center cursor-pointer border-none bg-transparent text-inherit" title="Buka Kategori E-Library">
                            <i id="nav-library-chevron" class="ph ph-caret-down text-xs sidebar-chevron transition-transform duration-200"></i>
                        </button>
                    </div>
                    <div id="nav-library" class="sidebar-sub-menu hidden pl-3 pr-1 py-1 space-y-1 max-h-80 overflow-y-auto custom-scrollbar">
                        <a href="{{ route('admin.library.index') }}" 
                           class="flex items-center px-2.5 py-1.5 rounded-md text-[11px] font-semibold transition-all {{ request()->routeIs('admin.library.index') && !request('category') ? 'bg-white text-[#1677B8] font-bold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-squares-four text-sm mr-2 text-white/90"></i>
                            <span>Semua Katalog</span>
                        </a>

                        <!-- 1. Business Unit (Dengan Multi-Level Subfolder Per Divisi & Unit) -->
                        <div class="pt-0.5">
                            <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                <a href="{{ route('admin.library.index', ['category' => 'divisi']) }}" class="flex items-center space-x-2 text-[10px] font-bold capitalize tracking-wider text-white/90 hover:text-white flex-1">
                                    <i class="ph ph-tree-structure text-sm mr-1.5 text-white/90"></i>
                                    <span>1. Business Unit</span>
                                </a>
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-lib-bu')" 
                                        class="p-1 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center" title="Buka Divisi Business Unit">
                                    <i id="nav-lib-bu-chevron" class="ph ph-caret-down text-[10px] transition-transform duration-200"></i>
                                </button>
                            </div>
                            <div id="nav-lib-bu" class="hidden pl-2.5 space-y-1 mt-0.5 border-l border-white/20 ml-2">
                                <!-- Divisi Retail Subfolder -->
                                <div>
                                    <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                        <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'RETAIL']) }}" class="flex items-center text-[10px] font-semibold transition-all flex-1 {{ request('div') === 'RETAIL' && !request('bu') ? 'text-[#ffe16e] font-bold' : 'text-white/90 hover:text-white' }}">
                                            <i class="ph ph-gas-pump text-xs mr-1.5"></i>
                                            <span>Divisi Retail</span>
                                        </a>
                                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-lib-bu-retail')" 
                                                class="p-0.5 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center">
                                            <i id="nav-lib-bu-retail-chevron" class="ph ph-caret-down text-[9px] transition-transform duration-200"></i>
                                        </button>
                                    </div>
                                    <div id="nav-lib-bu-retail" class="hidden pl-3 space-y-0.5 mt-0.5 border-l border-white/15 ml-2">
                                        @foreach(['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'INMAR (CNGM)'] as $bu)
                                        <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'RETAIL', 'bu' => $bu]) }}" class="flex items-center px-2 py-1 rounded text-[10px] font-medium transition-all {{ request('bu') === $bu ? 'bg-white text-[#1677B8] font-bold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                                            <span class="truncate">{{ $bu }}</span>
                                        </a>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Divisi Komersil Subfolder -->
                                <div>
                                    <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                        <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'KOMERSIL']) }}" class="flex items-center text-[10px] font-semibold transition-all flex-1 {{ request('div') === 'KOMERSIL' && !request('bu') ? 'text-[#ffe16e] font-bold' : 'text-white/90 hover:text-white' }}">
                                            <i class="ph ph-boat text-xs mr-1.5"></i>
                                            <span>Divisi Komersil</span>
                                        </a>
                                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-lib-bu-komersil')" 
                                                class="p-0.5 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center">
                                            <i id="nav-lib-bu-komersil-chevron" class="ph ph-caret-down text-[9px] transition-transform duration-200"></i>
                                        </button>
                                    </div>
                                    <div id="nav-lib-bu-komersil" class="hidden pl-3 space-y-0.5 mt-0.5 border-l border-white/15 ml-2">
                                        @foreach(['CPT & MHM', 'SBS', 'GVI'] as $bu)
                                        <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'KOMERSIL', 'bu' => $bu]) }}" class="flex items-center px-2 py-1 rounded text-[10px] font-medium transition-all {{ request('bu') === $bu ? 'bg-white text-[#1677B8] font-bold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                                            <span class="truncate">{{ $bu }}</span>
                                        </a>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Divisi SCM Subfolder -->
                                <div>
                                    <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                        <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'SCM']) }}" class="flex items-center text-[10px] font-semibold transition-all flex-1 {{ request('div') === 'SCM' && !request('bu') ? 'text-[#ffe16e] font-bold' : 'text-white/90 hover:text-white' }}">
                                            <i class="ph ph-truck text-xs mr-1.5"></i>
                                            <span>Divisi SCM</span>
                                        </a>
                                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-lib-bu-scm')" 
                                                class="p-0.5 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center">
                                            <i id="nav-lib-bu-scm-chevron" class="ph ph-caret-down text-[9px] transition-transform duration-200"></i>
                                        </button>
                                    </div>
                                    <div id="nav-lib-bu-scm" class="hidden pl-3 space-y-0.5 mt-0.5 border-l border-white/15 ml-2">
                                        @foreach(['PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA'] as $bu)
                                        <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'SCM', 'bu' => $bu]) }}" class="flex items-center px-2 py-1 rounded text-[10px] font-medium transition-all {{ request('bu') === $bu ? 'bg-white text-[#1677B8] font-bold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                                            <span class="truncate">{{ $bu }}</span>
                                        </a>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Divisi FA Subfolder -->
                                <div>
                                    <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                        <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'FA']) }}" class="flex items-center text-[10px] font-semibold transition-all flex-1 {{ request('div') === 'FA' && !request('bu') ? 'text-[#ffe16e] font-bold' : 'text-white/90 hover:text-white' }}">
                                            <i class="ph ph-bank text-xs mr-1.5"></i>
                                            <span>Divisi FA</span>
                                        </a>
                                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-lib-bu-fa')" 
                                                class="p-0.5 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center">
                                            <i id="nav-lib-bu-fa-chevron" class="ph ph-caret-down text-[9px] transition-transform duration-200"></i>
                                        </button>
                                    </div>
                                    <div id="nav-lib-bu-fa" class="hidden pl-3 space-y-0.5 mt-0.5 border-l border-white/15 ml-2">
                                        <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'FA', 'bu' => 'KEUANGAN & ACCOUNTING']) }}" class="flex items-center px-2 py-1 rounded text-[10px] font-medium transition-all {{ request('bu') === 'KEUANGAN & ACCOUNTING' ? 'bg-white text-[#1677B8] font-bold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                                            <span class="truncate">KEUANGAN & ACCOUNTING</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Support Dept (9 Departemen) -->
                        <div class="pt-0.5">
                            <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                <a href="{{ route('admin.library.index', ['category' => 'support']) }}" class="flex items-center space-x-2 text-[10px] font-bold capitalize tracking-wider text-white/90 hover:text-white flex-1">
                                    <i class="ph ph-headset text-sm mr-1.5 text-white/90"></i>
                                    <span>2. Support Dept</span>
                                </a>
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-lib-supp')" 
                                        class="p-1 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center" title="Buka Departemen Support">
                                    <i id="nav-lib-supp-chevron" class="ph ph-caret-down text-[10px] transition-transform duration-200"></i>
                                </button>
                            </div>
                            <div id="nav-lib-supp" class="hidden pl-3 space-y-0.5 mt-0.5 border-l border-white/20 ml-2">
                                @foreach(['HC', 'IT', 'HSE', 'QMS', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'] as $dept)
                                <a href="{{ route('admin.library.index', ['category' => 'support', 'bu' => $dept]) }}" class="flex items-center px-2 py-1 rounded text-[10px] font-semibold transition-all {{ request('bu') === $dept && request('category') === 'support' ? 'bg-white text-[#1677B8] font-bold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                                    <span>{{ $dept }}</span>
                                </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- 3. Retail Divisi Accordion -->
                        <div class="pt-0.5">
                            <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'RETAIL']) }}" class="flex items-center space-x-2 text-[10px] font-semibold text-white/90 hover:text-white flex-1">
                                    <i class="ph ph-gas-pump text-sm mr-1.5 text-white/90"></i>
                                    <span>3. Retail Divisi</span>
                                </a>
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-lib-dir-retail')" 
                                        class="p-1 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center">
                                    <i id="nav-lib-dir-retail-chevron" class="ph ph-caret-down text-[10px] transition-transform duration-200"></i>
                                </button>
                            </div>
                            <div id="nav-lib-dir-retail" class="hidden pl-3 space-y-0.5 mt-0.5 border-l border-white/20 ml-2">
                                @foreach(['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'INMAR (CNGM)'] as $bu)
                                <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'RETAIL', 'bu' => $bu]) }}" class="flex items-center px-2 py-1 rounded text-[10px] font-medium transition-all {{ request('bu') === $bu ? 'bg-white text-[#1677B8] font-bold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                                    <span class="truncate">{{ $bu }}</span>
                                </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- 4. Komersil Divisi Accordion -->
                        <div class="pt-0.5">
                            <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'KOMERSIL']) }}" class="flex items-center space-x-2 text-[10px] font-semibold text-white/90 hover:text-white flex-1">
                                    <i class="ph ph-boat text-sm mr-1.5 text-white/90"></i>
                                    <span>4. Komersil Divisi</span>
                                </a>
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-lib-dir-komersil')" 
                                        class="p-1 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center">
                                    <i id="nav-lib-dir-komersil-chevron" class="ph ph-caret-down text-[10px] transition-transform duration-200"></i>
                                </button>
                            </div>
                            <div id="nav-lib-dir-komersil" class="hidden pl-3 space-y-0.5 mt-0.5 border-l border-white/20 ml-2">
                                @foreach(['CPT & MHM', 'SBS', 'GVI'] as $bu)
                                <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'KOMERSIL', 'bu' => $bu]) }}" class="flex items-center px-2 py-1 rounded text-[10px] font-medium transition-all {{ request('bu') === $bu ? 'bg-white text-[#1677B8] font-bold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                                    <span class="truncate">{{ $bu }}</span>
                                </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- 5. SCM Divisi Accordion -->
                        <div class="pt-0.5">
                            <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'SCM']) }}" class="flex items-center space-x-2 text-[10px] font-semibold text-white/90 hover:text-white flex-1">
                                    <i class="ph ph-truck text-sm mr-1.5 text-white/90"></i>
                                    <span>5. SCM Divisi</span>
                                </a>
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-lib-dir-scm')" 
                                        class="p-1 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center">
                                    <i id="nav-lib-dir-scm-chevron" class="ph ph-caret-down text-[10px] transition-transform duration-200"></i>
                                </button>
                            </div>
                            <div id="nav-lib-dir-scm" class="hidden pl-3 space-y-0.5 mt-0.5 border-l border-white/20 ml-2">
                                @foreach(['PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA'] as $bu)
                                <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'SCM', 'bu' => $bu]) }}" class="flex items-center px-2 py-1 rounded text-[10px] font-medium transition-all {{ request('bu') === $bu ? 'bg-white text-[#1677B8] font-bold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                                    <span class="truncate">{{ $bu }}</span>
                                </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- 6. FA Divisi Accordion -->
                        <div class="pt-0.5">
                            <div class="flex items-center justify-between px-2 py-1 rounded hover:bg-white/10 text-white/90 transition-colors">
                                <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'FA']) }}" class="flex items-center space-x-2 text-[10px] font-semibold text-white/90 hover:text-white flex-1">
                                    <i class="ph ph-bank text-sm mr-1.5 text-white/90"></i>
                                    <span>6. FA Divisi</span>
                                </a>
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleNavGroup('nav-lib-dir-fa')" 
                                        class="p-1 text-white/80 hover:text-white rounded hover:bg-white/20 focus:outline-none cursor-pointer border-none bg-transparent flex items-center justify-center">
                                    <i id="nav-lib-dir-fa-chevron" class="ph ph-caret-down text-[10px] transition-transform duration-200"></i>
                                </button>
                            </div>
                            <div id="nav-lib-dir-fa" class="hidden pl-3 space-y-0.5 mt-0.5 border-l border-white/20 ml-2">
                                <a href="{{ route('admin.library.index', ['category' => 'divisi', 'div' => 'FA', 'bu' => 'KEUANGAN & ACCOUNTING']) }}" class="flex items-center px-2 py-1 rounded text-[10px] font-medium transition-all {{ request('bu') === 'KEUANGAN & ACCOUNTING' ? 'bg-white text-[#1677B8] font-bold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/40 mr-1.5"></span>
                                    <span class="truncate">KEUANGAN & ACCOUNTING</span>
                                </a>
                            </div>
                        </div>

                        <!-- 7. Other / General -->
                        <a href="{{ route('admin.library.index', ['category' => 'general']) }}" 
                           class="flex items-center px-2.5 py-1.5 rounded-md text-[10px] font-semibold transition-all {{ request('category') == 'general' ? 'bg-white text-[#1677B8] font-bold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            <i class="ph ph-folders text-sm mr-2 text-white/90"></i>
                            <span>7. General & Other</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- User Footer & Logout (mt-auto) -->
            <div class="p-3 mt-auto space-y-3">
                <!-- User Profile Card -->
                <div class="sidebar-profile-card flex items-center space-x-3 p-2.5 rounded-sm bg-white/10 text-white transition-all duration-200">
                    <i class="ph ph-user-circle text-2xl text-white/90 flex-shrink-0"></i>
                    <div class="min-w-0 flex-1 sidebar-profile-text text-left">
                        <p class="text-xs font-extrabold text-white truncate leading-tight">{{ auth()->user()->name ?? auth()->user()->username ?? 'Administrator' }}</p>
                        <p class="text-[9px] text-[#ffe16e] font-bold tracking-wider mt-0.5 truncate">{{ auth()->user()->email ?? 'admin@eqms.com' }}</p>
                    </div>
                </div>

                <!-- Logout Button -->
                <form action="{{ route('logout') }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari sistem?');">
                    @csrf
                    <button type="submit" class="sidebar-logout-btn w-full flex items-center justify-center px-3.5 py-2.5 rounded-sm bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs capitalize tracking-wider transition-all duration-150 group border-none shadow-md cursor-pointer">
                        <i class="ph ph-sign-out text-base mr-2"></i>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTAINER -->
        <div id="main-container" class="page-fade flex-1 flex flex-col min-w-0 bg-canvas relative overflow-hidden lg:pl-16">
            
            <!-- Sidebar Open Trigger (Floating, visible only when sidebar is collapsed) -->
            <button id="sidebar-open-btn" onclick="toggleSidebar()" class="lg:hidden fixed top-4 left-4 z-30 p-2 bg-white text-on-surface rounded-sm shadow-sm border border-sand-200/80 focus:outline-none hover:bg-charcoal-900/5 transition-colors flex items-center justify-center cursor-pointer" title="Buka Menu">
                <i class="ph ph-sidebar text-lg"></i>
            </button>

            <!-- CANVAS AREA (Industrial Enterprise) -->
            <main class="flex-1 lg:overflow-y-auto pt-6 px-4 md:px-6 pb-6 md:pb-8 custom-scrollbar">
                @if(session('success'))
                    <div class="mb-5 p-3.5 bg-emerald-50 border-l-4 border-emerald-600 text-emerald-800 font-semibold text-xs rounded-r-md shadow-sm flex items-center space-x-2.5">
                        <span class="material-symbols-outlined text-base text-emerald-600">check_circle</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 p-3.5 bg-red-50 border-l-4 border-red-600 text-red-800 font-semibold text-xs rounded-r-md shadow-sm flex items-center space-x-2.5">
                        <span class="material-symbols-outlined text-base text-red-600">error</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Sidebar mobile backdrop overlay -->
    <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden"></div>

    <script>
        function handleBrandClick() {
            const sidebar = document.getElementById('sidebar');
            const mainContainer = document.getElementById('main-container');
            if (sidebar.classList.contains('w-16')) {
                sidebar.classList.remove('w-16');
                sidebar.classList.add('w-64', 'shadow-2xl', 'z-50');
                if (mainContainer && window.innerWidth >= 1024) {
                    mainContainer.classList.remove('lg:pl-16');
                    mainContainer.classList.add('lg:pl-64');
                }
            } else {
                sidebar.classList.remove('w-64', 'shadow-2xl', 'z-50');
                sidebar.classList.add('w-16');
                if (mainContainer && window.innerWidth >= 1024) {
                    mainContainer.classList.remove('lg:pl-64');
                    mainContainer.classList.add('lg:pl-16');
                }
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const mainContainer = document.getElementById('main-container');
            
            const isDesktop = window.innerWidth >= 1024;
            
            if (isDesktop) {
                if (sidebar.classList.contains('w-64')) {
                    sidebar.classList.remove('w-64', 'shadow-2xl', 'z-50');
                    sidebar.classList.add('w-16');
                    mainContainer.classList.remove('lg:pl-64');
                    mainContainer.classList.add('lg:pl-16');
                } else {
                    sidebar.classList.remove('w-16');
                    sidebar.classList.add('w-64', 'shadow-2xl', 'z-50');
                    mainContainer.classList.remove('lg:pl-16');
                    mainContainer.classList.add('lg:pl-64');
                }
            } else {
                sidebar.classList.remove('w-16');
                sidebar.classList.add('w-64');
                if (sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.remove('-translate-x-full');
                    backdrop.classList.remove('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                }
            }
        }

        function toggleNavGroup(groupId) {
            const sidebar = document.getElementById('sidebar');
            const mainContainer = document.getElementById('main-container');
            if (sidebar.classList.contains('w-16')) {
                sidebar.classList.remove('w-16');
                sidebar.classList.add('w-64', 'shadow-2xl', 'z-50');
                if (mainContainer && window.innerWidth >= 1024) {
                    mainContainer.classList.remove('lg:pl-16');
                    mainContainer.classList.add('lg:pl-64');
                }
            }

            const menu = document.getElementById(groupId);
            const chevron = document.getElementById(groupId + '-chevron');
            if (menu) {
                const isHidden = menu.classList.contains('hidden');
                menu.classList.toggle('hidden');
                if (chevron) {
                    chevron.classList.toggle('rotate-180', !isHidden);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const mainContainer = document.getElementById('main-container');
            const backdrop = document.getElementById('sidebar-backdrop');
            
            const isDesktop = window.innerWidth >= 1024;
            
            if (isDesktop) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('lg:translate-x-0', 'w-16');
                mainContainer.classList.remove('lg:pl-64');
                mainContainer.classList.add('lg:pl-16');

                // 21st.dev / Aceternity auto-hover expand & responsive auto-shrink page content
                let hoverTimeout;
                sidebar.addEventListener('mouseenter', () => {
                    if (window.innerWidth >= 1024) {
                        clearTimeout(hoverTimeout);
                        sidebar.classList.remove('w-16');
                        sidebar.classList.add('w-64', 'shadow-2xl', 'z-50');
                        mainContainer.classList.remove('lg:pl-16');
                        mainContainer.classList.add('lg:pl-64');
                    }
                });

                sidebar.addEventListener('mouseleave', () => {
                    if (window.innerWidth >= 1024) {
                        hoverTimeout = setTimeout(() => {
                            sidebar.classList.remove('w-64', 'shadow-2xl', 'z-50');
                            sidebar.classList.add('w-16');
                            mainContainer.classList.remove('lg:pl-64');
                            mainContainer.classList.add('lg:pl-16');
                            // Auto-collapse open submenus when cursor leaves
                            document.querySelectorAll('.sidebar-sub-menu').forEach(menu => {
                                menu.classList.add('hidden');
                            });
                            document.querySelectorAll('.sidebar-chevron').forEach(chevron => {
                                chevron.classList.remove('rotate-180');
                            });
                        }, 180);
                    }
                });
            } else {
                sidebar.classList.remove('w-16');
                sidebar.classList.add('w-64');
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }

            // Page transition fade-in. pageshow juga menangani browser Back/Forward
            // ketika halaman dipulihkan dari bfcache tanpa menjalankan DOMContentLoaded.
            const revealPage = () => {
                mainContainer.classList.add('page-fade-active');
            };
            requestAnimationFrame(revealPage);
            window.addEventListener('pageshow', revealPage);

            // Custom Confirm Modal Logic
            const modal = document.getElementById('custom-confirm-modal');
            const card = document.getElementById('custom-confirm-card');
            const titleEl = document.getElementById('custom-confirm-title');
            const iconEl = document.getElementById('custom-confirm-icon');
            const msgEl = document.getElementById('custom-confirm-message');
            const cancelBtn = document.getElementById('custom-confirm-cancel');
            const okBtn = document.getElementById('custom-confirm-ok');
            const closeBtn = document.getElementById('custom-confirm-close');
            
            let activeForm = null;

            if (modal && card && titleEl && iconEl && msgEl && cancelBtn && okBtn && closeBtn) {
                const initConfirmForms = () => {
                    document.querySelectorAll('form[onsubmit*="confirm("]').forEach(form => {
                        const onsubmitAttr = form.getAttribute('onsubmit');
                        const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
                        if (match) {
                            const message = match[1];
                            form.removeAttribute('onsubmit');
                            form.setAttribute('data-confirm', message);
                        }
                    });
                };

                initConfirmForms();

                const observer = new MutationObserver(() => {
                    initConfirmForms();
                });
                observer.observe(document.body, { childList: true, subtree: true });

                const showModal = (message, form) => {
                    activeForm = form;
                    msgEl.textContent = message;

                    if (message.toLowerCase().includes('keluar') || message.toLowerCase().includes('logout')) {
                        titleEl.textContent = 'Keluar Sistem';
                        iconEl.textContent = 'logout';
                        iconEl.className = 'material-symbols-outlined text-rose-500';
                        okBtn.textContent = 'Keluar';
                        okBtn.className = 'flex-1 bg-rose-600 hover:bg-rose-700 text-white py-2.5 rounded-md font-bold text-xs capitalize shadow-sm cursor-pointer transition-all border-none focus:outline-none';
                    } else {
                        titleEl.textContent = 'Konfirmasi Hapus';
                        iconEl.textContent = 'delete';
                        iconEl.className = 'material-symbols-outlined text-amber-500';
                        okBtn.textContent = 'Hapus';
                        okBtn.className = 'flex-1 bg-rose-600 hover:bg-rose-700 text-white py-2.5 rounded-md font-bold text-xs capitalize shadow-sm cursor-pointer transition-all border-none focus:outline-none';
                    }

                    modal.classList.remove('hidden');
                    modal.offsetHeight;
                    modal.style.opacity = '1';
                    card.classList.remove('scale-95', 'opacity-0');
                    card.classList.add('scale-100', 'opacity-100');
                };

                const hideModal = () => {
                    card.classList.remove('scale-100', 'opacity-100');
                    card.classList.add('scale-95', 'opacity-0');
                    modal.style.opacity = '0';
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        activeForm = null;
                    }, 200);
                };

                cancelBtn.addEventListener('click', hideModal);
                closeBtn.addEventListener('click', hideModal);
                
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) hideModal();
                });

                okBtn.addEventListener('click', () => {
                    if (activeForm) {
                        activeForm.setAttribute('data-confirmed', 'true');
                        activeForm.submit();
                    }
                    hideModal();
                });

                document.addEventListener('submit', (e) => {
                    const form = e.target;
                    const confirmMsg = form.getAttribute('data-confirm');
                    
                    if (confirmMsg && form.getAttribute('data-confirmed') !== 'true') {
                        e.preventDefault();
                        showModal(confirmMsg, form);
                        return;
                    }
                });
            }
        });
    </script>

    <!-- Custom Premium Confirm Modal -->
    <div id="custom-confirm-modal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity duration-200" style="opacity: 0;">
        <div class="bg-white rounded-lg w-full max-w-md p-6 md:p-8 shadow-2xl border border-sand-200 space-y-4 transform scale-95 opacity-0 transition-all duration-200 ease-out" id="custom-confirm-card">
            <!-- Header -->
            <div class="flex justify-between items-center border-b border-sand-200/40 pb-3">
                <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-gold-500" id="custom-confirm-icon">warning</span>
                    <span id="custom-confirm-title">Konfirmasi Aksi</span>
                </h3>
                <button type="button" id="custom-confirm-close" class="text-gray-400 hover:text-black text-2xl border-0 bg-transparent cursor-pointer">&times;</button>
            </div>
            <!-- Body -->
            <div class="py-2">
                <p class="text-xs font-semibold text-on-surface-variant leading-relaxed" id="custom-confirm-message"></p>
            </div>
            <!-- Footer -->
            <div class="flex space-x-3 pt-3">
                <button type="button" id="custom-confirm-cancel" class="flex-1 py-2.5 bg-sand-50 border border-sand-200 text-on-surface-variant rounded-md font-bold text-xs capitalize cursor-pointer hover:bg-sand-100 transition-all focus:outline-none">Batal</button>
                <button type="button" id="custom-confirm-ok" class="focus:outline-none"></button>
            </div>
        </div>
    </div>
</body>
</html>