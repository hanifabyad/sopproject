<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-QMS Admin — @yield('title', 'Quality Management System')</title>
    
    <!-- Google Fonts & Material Symbols Outlined -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#333028] text-[#1e1c14] h-full font-sans antialiased overflow-hidden selection:bg-[#ffe16e] selection:text-[#333028]">
    <div class="flex h-screen w-full overflow-hidden">
        
        <!-- SIDEBAR (Dark Charcoal #333028) -->
        <aside class="w-64 bg-[#333028] text-[#eee8db] flex-shrink-0 flex flex-col z-30 relative border-r border-[#454137]">
            <!-- Brand Header -->
            <div class="p-5 flex flex-col items-center border-b border-white/10 text-center relative group">
                <div class="flex items-center justify-center w-16 h-16">
                    <img src="{{ asset('img/logopkm.svg') }}" class="w-full h-full object-contain" alt="Logo PKM Group">
                </div>
                
                <div class="mt-2.5 flex flex-col items-center">
                    <span class="text-base font-extrabold tracking-tight uppercase leading-none text-white">e-QMS Portal</span>
                    <span class="text-[10px] font-bold text-[#ffe16e] uppercase tracking-[0.2em] mt-1 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#ffe16e] inline-block animate-pulse"></span> PT PKM Group
                    </span>
                </div>
            </div>
            
            <!-- Navigation Links (Rounded-MD Sharp Boxes) -->
            <nav class="mt-3 flex-1 px-3 space-y-1.5 overflow-y-auto custom-scrollbar">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-3.5 py-2.5 rounded-md font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.dashboard') ? 'bg-[#ffe16e] text-[#333028] font-extrabold' : 'text-[#eee8db]/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="material-symbols-outlined text-lg mr-2.5 {{ request()->routeIs('admin.dashboard') ? 'text-[#333028]' : 'text-[#ffe16e]' }}">dashboard</span>
                    <span>Dashboard</span>
                </a>

                <!-- Kelola Akun -->
                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center px-3.5 py-2.5 rounded-md font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.users.*') ? 'bg-[#ffe16e] text-[#333028] font-extrabold' : 'text-[#eee8db]/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="material-symbols-outlined text-lg mr-2.5 {{ request()->routeIs('admin.users.*') ? 'text-[#333028]' : 'text-[#ffe16e]' }}">group</span>
                    <span>Kelola Akun User</span>
                </a>

                <!-- Business Unit -->
                <a href="{{ route('admin.BU.index') }}" 
                   class="flex items-center px-3.5 py-2.5 rounded-md font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.BU.*') ? 'bg-[#ffe16e] text-[#333028] font-extrabold' : 'text-[#eee8db]/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="material-symbols-outlined text-lg mr-2.5 {{ request()->routeIs('admin.BU.*') ? 'text-[#333028]' : 'text-[#ffe16e]' }}">apartment</span>
                    <span>Business Unit (BU)</span>
                </a>

                <!-- Support -->
                <a href="{{ route('admin.support.index') }}" 
                   class="flex items-center px-3.5 py-2.5 rounded-md font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.support.*') ? 'bg-[#ffe16e] text-[#333028] font-extrabold' : 'text-[#eee8db]/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="material-symbols-outlined text-lg mr-2.5 {{ request()->routeIs('admin.support.*') ? 'text-[#333028]' : 'text-[#ffe16e]' }}">support_agent</span>
                    <span>Departemen Support</span>
                </a>

                <!-- E-Library -->
                <a href="{{ route('admin.library.index') }}" 
                   class="flex items-center px-3.5 py-2.5 rounded-md font-semibold text-xs tracking-wide transition-all duration-150 group {{ request()->routeIs('admin.library.*') || request()->routeIs('library.*') ? 'bg-[#ffe16e] text-[#333028] font-extrabold' : 'text-[#eee8db]/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="material-symbols-outlined text-lg mr-2.5 {{ request()->routeIs('admin.library.*') || request()->routeIs('library.*') ? 'text-[#333028]' : 'text-[#ffe16e]' }}">local_library</span>
                    <span>E-Library Catalog</span>
                </a>
            </nav>

            <!-- User Footer & Logout (mt-auto) -->
            <div class="p-3 border-t border-white/10 mt-auto">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-3.5 py-2 rounded-md bg-red-500/10 text-red-300 hover:bg-red-600 hover:text-white font-bold text-xs transition-all duration-150 group border border-red-500/20">
                        <span class="material-symbols-outlined text-base mr-2">logout</span>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTAINER -->
        <div class="flex-1 flex flex-col min-w-0 bg-[#333028] relative overflow-hidden">
            
            <!-- TOP NAVBAR -->
            <header class="h-14 flex items-center justify-between px-6 bg-[#333028] text-white z-20 border-b border-[#454137]">
                <div class="flex items-center space-x-3">
                    <span class="w-2 h-2 rounded-full bg-[#ffe16e] inline-block animate-pulse"></span>
                    <h1 class="text-xs font-extrabold tracking-wider uppercase text-[#eee8db]">
                        @yield('header_title', 'Electronic Quality Management System')
                    </h1>
                </div>

                <!-- Right Utilities: Profile -->
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2.5 border-l border-white/15 pl-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-bold leading-tight">{{ auth()->user()->name ?? auth()->user()->username ?? 'Administrator' }}</p>
                            <p class="text-[9px] text-[#ffe16e] font-semibold tracking-wider uppercase mt-0.5">{{ auth()->user()->role ?? 'Admin Master' }}</p>
                        </div>
                        <div class="w-8 h-8 rounded-md bg-[#ffe16e] text-[#333028] flex items-center justify-center font-extrabold text-xs uppercase shadow-sm">
                            {{ strtoupper(substr(auth()->user()->username ?? 'A', 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- CANVAS AREA (Industrial Enterprise: #F7F6F2 with subtle rounded-tl-xl or rounded-none) -->
            <main class="flex-1 bg-[#F7F6F2] rounded-tl-xl overflow-y-auto p-6 md:p-8 custom-scrollbar border-t border-l border-[#cfc6ac]">
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
</body>
</html>

