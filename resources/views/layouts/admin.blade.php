<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-QMS Admin — @yield('title', 'Quality Management System')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/logopkm.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('img/logopkm.png') }}">
    
    <!-- Google Fonts & Material Symbols Outlined & Phosphor Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Sidebar Collapsed Styles (Desktop only) */
        @media (min-width: 1024px) {
            aside.w-16 {
                width: 4rem !important; /* 64px */
            }
            aside.w-16 .sidebar-text,
            aside.w-16 .sidebar-logo-text,
            aside.w-16 .sidebar-profile-text {
                display: none !important;
            }
            aside.w-16 .sidebar-brand-wrapper {
                padding: 1rem 0 !important;
                justify-content: center !important;
                cursor: pointer;
            }
            aside.w-16 .sidebar-brand-wrapper button {
                display: none !important;
            }
            aside.w-16 .sidebar-brand-wrapper img {
                margin: 0 auto !important;
            }
            aside.w-16 .sidebar-item {
                justify-content: center !important;
                padding: 0 !important;
                width: 2.5rem !important;
                height: 2.5rem !important;
                margin-left: auto !important;
                margin-right: auto !important;
                border-radius: 0.375rem !important;
            }
            aside.w-16 .sidebar-item i {
                margin-right: 0 !important;
                font-size: 1.25rem !important;
            }
            aside.w-16 .sidebar-profile-card {
                width: 2.5rem !important;
                height: 2.5rem !important;
                padding: 0 !important;
                margin: 0 auto !important;
                justify-content: center !important;
                border-radius: 0.375rem !important;
            }
            aside.w-16 .sidebar-profile-card i {
                margin: 0 !important;
            }
            aside.w-16 .sidebar-logout-btn {
                width: 2.5rem !important;
                height: 2.5rem !important;
                padding: 0 !important;
                margin: 0 auto !important;
                justify-content: center !important;
                border-radius: 0.375rem !important;
            }
            aside.w-16 .sidebar-logout-btn span {
                display: none !important;
            }
            aside.w-16 .sidebar-logout-btn i {
                margin-right: 0 !important;
                font-size: 1.25rem !important;
            }
        }

        /* Active menu item curved cut-out effect */
        @media (min-width: 1024px) {
            aside.w-64 nav {
                padding-right: 0 !important; /* Touch the right edge of sidebar */
            }
            aside.w-64 .sidebar-item {
                border-radius: 9999px 0 0 9999px !important; /* smooth half-pill left */
                transition: all 0.2s ease;
            }
            aside.w-64 .sidebar-item-active {
                position: relative;
                background-color: #f8fafc !important; /* matches bg-canvas exactly */
                color: #1677B8 !important;
                border-radius: 9999px 0 0 9999px !important;
                margin-right: -1.5rem !important; /* extends past sidebar rounded corner */
                padding-right: 1.5rem !important; /* compensate so text doesn't shift */
                z-index: 10;
                box-shadow: none !important;
            }
            aside.w-64 .sidebar-item-active i {
                color: #1677B8 !important;
            }
            aside.w-64 .sidebar-item-active::before,
            aside.w-64 .sidebar-item-active::after {
                content: "";
                position: absolute;
                right: 0;
                width: 20px;
                height: 20px;
                background-color: transparent;
                pointer-events: none;
                z-index: 20;
            }
            aside.w-64 .sidebar-item-active::before {
                top: -20px;
                border-bottom-right-radius: 20px;
                box-shadow: 10px 10px 0 10px #f8fafc; /* concave top curve */
            }
            aside.w-64 .sidebar-item-active::after {
                bottom: -20px;
                border-top-right-radius: 20px;
                box-shadow: 10px -10px 0 10px #f8fafc; /* concave bottom curve */
            }
        }
        
        /* Collapsed active menu style */
        @media (min-width: 1024px) {
            aside.w-16 .sidebar-item-active {
                border-radius: 0.375rem !important;
                margin-left: auto !important;
                margin-right: auto !important;
                background-color: white !important;
                color: #1677B8 !important;
            }
            aside.w-16 .sidebar-item-active i {
                color: #1677B8 !important;
            }
            aside.w-16 .sidebar-item-active::before,
            aside.w-16 .sidebar-item-active::after {
                display: none !important;
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

        /* Page transition animations */
        .page-fade {
            opacity: 0;
            transform: translateY(8px);
            transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .page-fade-active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="bg-canvas text-on-surface h-full font-sans antialiased lg:overflow-hidden overflow-y-auto selection:bg-gold-fixed selection:text-charcoal-900">
    <div class="flex lg:h-screen lg:overflow-hidden min-h-screen w-full">
        
        <!-- SIDEBAR (Gradient Blue-Teal) -->
        <aside id="sidebar" class="w-64 bg-gradient-to-b from-[#1677B8] to-[#00b4d8] text-white flex-shrink-0 flex flex-col z-40 fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 transition-all duration-200 ease-in-out rounded-r-3xl">
            <!-- Brand Header -->
            <div onclick="handleBrandClick()" class="sidebar-brand-wrapper p-5 flex items-center justify-between border-b border-white/10 text-center relative group transition-all duration-200">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-9 h-9 flex-shrink-0">
                        <img src="{{ asset('img/logopkm.png') }}" class="w-full h-full object-contain" alt="Logo PKM Group">
                    </div>
                    <div class="text-left sidebar-logo-text">
                        <span class="text-sm font-extrabold tracking-tight uppercase leading-none text-white block">e-QMS Portal</span>
                        <span class="text-[9px] font-bold text-[#ffe16e] uppercase tracking-[0.1em] mt-0.5 block">PT PKM Group</span>
                    </div>
                </div>
                <button onclick="event.stopPropagation(); toggleSidebar();" class="p-1 hover:bg-white/10 rounded-sm text-white focus:outline-none transition-colors flex items-center justify-center flex-shrink-0 cursor-pointer" title="Collapse Menu">
                    <i class="ph ph-sidebar text-lg"></i>
                </button>
            </div>
            
            <!-- Navigation Links (Rounded-LG Floating Boxes) -->
            <nav class="mt-3 flex-1 px-3 space-y-1.5 overflow-y-auto overflow-x-hidden custom-scrollbar">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="sidebar-item flex items-center px-3.5 py-2.5 rounded-lg font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.dashboard') ? 'bg-canvas text-[#1677B8] font-extrabold sidebar-item-active' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                    <i class="ph ph-squares-four text-lg mr-2.5 {{ request()->routeIs('admin.dashboard') ? 'text-[#1677B8]' : 'text-white/90' }}"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>

                <!-- Kelola Akun -->
                <a href="{{ route('admin.users.index') }}" 
                   class="sidebar-item flex items-center px-3.5 py-2.5 rounded-lg font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.users.*') ? 'bg-canvas text-[#1677B8] font-extrabold sidebar-item-active' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                    <i class="ph ph-users text-lg mr-2.5 {{ request()->routeIs('admin.users.*') ? 'text-[#1677B8]' : 'text-white/90' }}"></i>
                    <span class="sidebar-text">Kelola Akun User</span>
                </a>

                <!-- Business Unit -->
                <a href="{{ route('admin.BU.index') }}" 
                   class="sidebar-item flex items-center px-3.5 py-2.5 rounded-lg font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.BU.*') ? 'bg-canvas text-[#1677B8] font-extrabold sidebar-item-active' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                    <i class="ph ph-buildings text-lg mr-2.5 {{ request()->routeIs('admin.BU.*') ? 'text-[#1677B8]' : 'text-white/90' }}"></i>
                    <span class="sidebar-text">Business Unit (BU)</span>
                </a>

                <!-- Support -->
                <a href="{{ route('admin.support.index') }}" 
                   class="sidebar-item flex items-center px-3.5 py-2.5 rounded-lg font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.support.*') ? 'bg-canvas text-[#1677B8] font-extrabold sidebar-item-active' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                    <i class="ph ph-headset text-lg mr-2.5 {{ request()->routeIs('admin.support.*') ? 'text-[#1677B8]' : 'text-white/90' }}"></i>
                    <span class="sidebar-text">Departemen Support</span>
                </a>

                <!-- E-Library -->
                <a href="{{ route('admin.library.index') }}" 
                   class="sidebar-item flex items-center px-3.5 py-2.5 rounded-lg font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.library.*') || request()->routeIs('library.*') ? 'bg-canvas text-[#1677B8] font-extrabold sidebar-item-active' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                    <i class="ph ph-books text-lg mr-2.5 {{ request()->routeIs('admin.library.*') || request()->routeIs('library.*') ? 'text-[#1677B8]' : 'text-white/90' }}"></i>
                    <span class="sidebar-text">E-Library Catalog</span>
                </a>
            </nav>

            <!-- User Footer & Logout (mt-auto) -->
            <div class="p-3 mt-auto space-y-3 border-none outline-none">
                <!-- User Profile Card -->
                <div class="sidebar-profile-card flex items-center space-x-3 p-2.5 rounded-sm bg-white border-0 text-on-surface transition-all duration-200">
                    <i class="ph ph-user-circle text-2xl text-charcoal-900 flex-shrink-0"></i>
                    <div class="min-w-0 flex-1 sidebar-profile-text text-left">
                        <p class="text-xs font-extrabold text-on-surface truncate leading-tight">{{ auth()->user()->name ?? auth()->user()->username ?? 'Administrator' }}</p>
                        <p class="text-[9px] text-gold-dim font-bold tracking-wider mt-0.5 truncate">{{ auth()->user()->email ?? 'admin@eqms.com' }}</p>
                    </div>
                </div>

                <!-- Logout Button -->
                <form action="{{ route('logout') }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari sistem?');">
                    @csrf
                    <button type="submit" class="sidebar-logout-btn w-full flex items-center justify-center px-3.5 py-2.5 rounded-sm bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs uppercase tracking-wider transition-all duration-150 group border-none shadow-md cursor-pointer">
                        <i class="ph ph-sign-out text-base mr-2"></i>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTAINER -->
        <div id="main-container" class="page-fade flex-1 flex flex-col min-w-0 bg-canvas relative overflow-hidden transition-all duration-200 ease-in-out lg:pl-64">
            
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
            if (sidebar.classList.contains('w-16')) {
                toggleSidebar();
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const mainContainer = document.getElementById('main-container');
            
            const isDesktop = window.innerWidth >= 1024;
            
            if (isDesktop) {
                if (sidebar.classList.contains('w-64')) {
                    // Collapse to w-16
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-16');
                    mainContainer.classList.remove('lg:pl-64');
                    mainContainer.classList.add('lg:pl-16');
                    localStorage.setItem('sidebar-collapsed', 'true');
                } else {
                    // Expand to w-64
                    sidebar.classList.remove('w-16');
                    sidebar.classList.add('w-64');
                    mainContainer.classList.remove('lg:pl-16');
                    mainContainer.classList.add('lg:pl-64');
                    localStorage.setItem('sidebar-collapsed', 'false');
                }
            } else {
                // Mobile behavior (always translate w-64)
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

        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const mainContainer = document.getElementById('main-container');
            const backdrop = document.getElementById('sidebar-backdrop');
            
            const isDesktop = window.innerWidth >= 1024;
            const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            
            if (isDesktop) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('lg:translate-x-0');
                if (isCollapsed) {
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-16');
                    mainContainer.classList.remove('lg:pl-64');
                    mainContainer.classList.add('lg:pl-16');
                } else {
                    sidebar.classList.remove('w-16');
                    sidebar.classList.add('w-64');
                    mainContainer.classList.remove('lg:pl-16');
                    mainContainer.classList.add('lg:pl-64');
                }
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

            // Page transition fade-out on link clicks
            document.querySelectorAll('a').forEach(link => {
                if (
                    link.hostname === window.location.hostname &&
                    !link.hash &&
                    !link.href.startsWith('javascript:') &&
                    !link.href.startsWith('#') &&
                    link.target !== '_blank'
                ) {
                    link.addEventListener('click', (e) => {
                        if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
                        e.preventDefault();
                        const href = link.href;
                        mainContainer.classList.remove('page-fade-active');
                        setTimeout(() => {
                            window.location.href = href;
                        }, 220);
                    });
                }
            });

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

            // Function to convert traditional confirm to custom confirm on submit
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

            // Initialize immediately
            initConfirmForms();

            // Watch for dynamically added forms
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
                    okBtn.className = 'flex-1 bg-rose-600 hover:bg-rose-700 text-white py-2.5 rounded-md font-bold text-xs uppercase shadow-sm cursor-pointer transition-all border-none focus:outline-none';
                } else {
                    titleEl.textContent = 'Konfirmasi Hapus';
                    iconEl.textContent = 'delete';
                    iconEl.className = 'material-symbols-outlined text-amber-500';
                    okBtn.textContent = 'Hapus';
                    okBtn.className = 'flex-1 bg-rose-600 hover:bg-rose-700 text-white py-2.5 rounded-md font-bold text-xs uppercase shadow-sm cursor-pointer transition-all border-none focus:outline-none';
                }

                modal.classList.remove('hidden');
                // Force reflow
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
                if (form.getAttribute('data-confirmed') === 'true') {
                    return;
                }

                const confirmMsg = form.getAttribute('data-confirm');
                if (confirmMsg) {
                    e.preventDefault();
                    showModal(confirmMsg, form);
                }
            });
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
                <button type="button" id="custom-confirm-cancel" class="flex-1 py-2.5 bg-sand-50 border border-sand-200 text-on-surface-variant rounded-md font-bold text-xs uppercase cursor-pointer hover:bg-sand-100 transition-all focus:outline-none">Batal</button>
                <button type="button" id="custom-confirm-ok" class="focus:outline-none"></button>
            </div>
        </div>
    </div>
</body>
</html>


