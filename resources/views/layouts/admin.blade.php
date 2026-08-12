<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <title>e-QMS - @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 h-full overflow-hidden font(['Poppins', 'sans-serif'])"> 
    <div class="flex h-screen w-full">
        
        <aside class="w-64 bg-[#1e293b] text-white flex-shrink-0 flex flex-col shadow-xl z-20">
            
            <div class="p-6 flex flex-col items-center space-y-3 border-b border-white/5 w-full text-center">
                <div class="bg-white p-3 rounded-[2rem] shadow-lg shadow-blue-500/5 flex items-center justify-center w-24 h-24 transition-transform duration-300 hover:scale-105">
                    <img src="{{ asset('img/logo pkm 2.png') }}" class="w-full h-full object-contain">
                </div>
                
                <div class="flex flex-col items-center">
                    <span class="text-2xl font-black tracking-wider uppercase leading-none text-white">e-QMS</span>
                    <span class="text-[9px] font-black text-blue-400 uppercase tracking-[0.25em] mt-2">Quality System</span>
                </div>
            </div>
            
            <nav class="mt-6 flex-1 px-4 space-y-1.5 overflow-y-auto custom-scrollbar">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 transform hover:translate-x-1 group {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'hover:bg-white/5 text-gray-400 hover:text-white' }}">
                    <div class="w-5 text-center text-sm">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <span class="ml-3.5 font-bold text-xs uppercase tracking-wider">Dashboard</span>
                </a>

                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 transform hover:translate-x-1 group {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'hover:bg-white/5 text-gray-400 hover:text-white' }}">
                    <div class="w-5 text-center text-sm">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <span class="ml-3.5 font-bold text-xs uppercase tracking-wider">Kelola Akun</span>
                </a>

                <a href="{{ route('admin.support.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 transform hover:translate-x-1 group {{ request()->routeIs('admin.support.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'hover:bg-white/5 text-gray-400 hover:text-white' }}">
                    <div class="w-5 text-center text-sm">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <span class="ml-3.5 font-bold text-xs uppercase tracking-wider">Support</span>
                </a>

                <a href="{{ route('admin.BU.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 transform hover:translate-x-1 group {{ request()->routeIs('admin.BU.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'hover:bg-white/5 text-gray-400 hover:text-white' }}">
                    <div class="w-5 text-center text-sm">
                        <i class="fa-solid fa-folder-tree"></i>
                    </div>
                    <span class="ml-3.5 font-bold text-xs uppercase tracking-wider">Business Unit</span>
                </a>

                <a href="{{ route('library.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 transform hover:translate-x-1 group {{ request()->routeIs('library.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'hover:bg-white/5 text-gray-400 hover:text-white' }}">
                    <div class="w-5 text-center text-sm">
                        <i class="fa-solid fa-book-bookmark"></i>
                    </div>
                    <span class="ml-3.5 font-bold text-xs uppercase tracking-wider">Library</span>
                </a>
            </nav>

            <div class="p-4 border-t border-white/5">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition-all duration-200 font-bold group">
                        <div class="w-5 text-center text-sm group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </div>
                        <span class="ml-3.5 text-[11px] uppercase tracking-widest">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 bg-slate-50 relative">
            <header class="h-16 border-b border-gray-200 flex items-center justify-between px-8 bg-white z-10 shadow-sm">
                <h2 class="text-sm font-black text-[#1e293b] uppercase tracking-tight flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600 block"></span> E-QMS Quality Management System
                </h2>
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-[11px] font-black text-[#1e293b] uppercase leading-none">Administrator</p>
                        <p class="text-[9px] text-gray-400 font-semibold tracking-wider mt-1 uppercase">{{ auth()->user()->username ?? 'Master Admin' }}</p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-blue-900 flex items-center justify-center text-white font-extrabold shadow-md shadow-blue-900/10 uppercase tracking-tighter text-sm border-2 border-white">
                        {{ substr(auth()->user()->username ?? 'A', 0, 1) }}
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                @yield('content')
            </main>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    </style>
</body>
</html>