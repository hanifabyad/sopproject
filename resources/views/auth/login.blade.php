<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-QMS - Login PT PKM Group</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body class="bg-[#333028] font-sans h-full w-full m-0 p-0 overflow-x-hidden antialiased text-[#1e1c14]">

    <div class="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 lg:p-8 relative">
        
        <!-- SOFT DECORATIVE GLOW BACKGROUND -->
        <div class="absolute inset-0 z-0 pointer-events-none opacity-20 bg-[radial-gradient(#ffd92f_1px,transparent_1px)] [background-size:24px_24px]"></div>

        <div class="relative z-10 w-full max-w-5xl grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            
            <!-- LEFT HERO SECTION (7 COLS) -->
            <div class="lg:col-span-7 space-y-6 text-white p-6 lg:p-10">
                <div class="flex items-center space-x-3">
                    <span class="px-3.5 py-1 bg-[#ffd92f] text-[#333028] font-extrabold text-xs rounded-full uppercase tracking-wider">Quality System</span>
                    <span class="text-xs text-[#eee8db] font-semibold">PT PKM Group</span>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-semibold uppercase tracking-widest text-[#ffe16e]">Selamat Datang di Portal Dokumen</p>
                    <h1 class="text-5xl sm:text-6xl font-extrabold tracking-tight uppercase leading-none text-[#fff9ed]">e-QMS</h1>
                </div>

                <p class="text-xs sm:text-sm text-[#eee8db]/80 leading-relaxed max-w-md">
                    Electronic Quality Management System untuk digitalisasi alur pengesahan Lembar Pengesahan (LP) SOP & E-Library PT PKM Group.
                </p>

                <div class="pt-4 flex items-center space-x-6 text-xs text-[#ffe16e] font-semibold">
                    <div class="flex items-center space-x-2">
                        <span class="material-symbols-outlined text-base">verified</span>
                        <span>Stamping Legal</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="material-symbols-outlined text-base">security</span>
                        <span>Audit Trail Enkripsi</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT LOGIN CARD (5 COLS BENTO CARD) -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-3xl p-8 shadow-2xl border border-[#e5dfd3] space-y-6">
                    
                    <!-- BRANDING HEADER -->
                    <div class="flex flex-col items-center text-center space-y-3">
                        <div class="w-20 h-20 flex items-center justify-center">
                            <img src="{{ asset('img/logopkm.svg') }}" alt="Logo PKM Group" class="max-h-full max-w-full object-contain">
                        </div>
                        <div>
                            <h3 class="text-xl font-extrabold text-[#1e1c14]">Masuk ke Akun</h3>
                            <p class="text-xs text-[#4d4633] mt-0.5">Masukkan kredensial Anda untuk melanjutkan</p>
                        </div>
                    </div>

                    <!-- ERROR ALERT -->
                    @if ($errors->any())
                        <div class="p-3.5 bg-[#ffdad6] border-l-4 border-[#ba1a1a] text-[#ba1a1a] text-xs font-semibold rounded-r-xl space-y-1">
                            @foreach ($errors->all() as $error)
                                <p class="flex items-center gap-1.5"><span class="material-symbols-outlined text-sm">error</span> {{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <!-- FORM CONTRACT -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#4d4633] mb-1.5 ml-1">Username / ID Login</label>
                            <div class="relative">
                                <input type="text" name="username" placeholder="Masukkan username" value="{{ old('username') }}" required 
                                    class="w-full bg-[#f7f6f2] border border-[#e5dfd3] rounded-2xl py-3.5 pl-10 pr-4 font-semibold text-xs text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none transition-all placeholder-[#d6cebf]">
                                <span class="material-symbols-outlined absolute left-3 top-3.5 text-base text-[#705d00]">person</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-[#4d4633] mb-1.5 ml-1">Password</label>
                            <div class="relative">
                                <input type="password" name="password" placeholder="••••••••" required 
                                    class="w-full bg-[#f7f6f2] border border-[#e5dfd3] rounded-2xl py-3.5 pl-10 pr-4 font-semibold text-xs text-[#1e1c14] focus:bg-white focus:ring-2 focus:ring-[#705d00] outline-none transition-all placeholder-[#d6cebf]">
                                <span class="material-symbols-outlined absolute left-3 top-3.5 text-base text-[#705d00]">lock</span>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-4 bg-[#333028] text-[#ffe16e] hover:bg-black rounded-2xl font-bold uppercase text-xs tracking-wider shadow-md transition-all flex items-center justify-center gap-2 mt-2">
                            <span class="material-symbols-outlined text-base">login</span>
                            <span>Masuk Sistem</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
 