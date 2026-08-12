<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full"> 
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-QMS- Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#111827] h-full w-full m-0 p-0 overflow-hidden"> 

    <div class="h-screen w-screen flex items-center justify-center relative overflow-hidden"> 
        
        <div class="absolute inset-0 z-0 h-full w-full"> 
            <svg class="w-full h-full" viewBox="0 0 1440 812" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 812C281.4 754.2 466.8 617 652.2 489C837.6 361 1007.4 204.4 1177.2 165.4C1347 126.4 1440 0 1440 0V812H0Z" fill="#111827"/> 
                <path d="M0 812C270 760 450 630 630 510C810 390 970 240 1130 190C1290 140 1440 0 1440 0V812H0Z" fill="#1F2937" opacity="0.5"/>
            </svg>
        </div>

        <div class="relative z-10 flex w-full max-w-7xl h-screen items-center">
            
            <div class="hidden lg:flex lg:w-3/5 items-center justify-start p-12">
                <div class="text-white ml-12"> 
                    <p class="text-4xl font-light mb-2 tracking-wide uppercase opacity-90">selamat datang di dokumen sistem</p>
                    <h1 class="text-8xl font-black tracking-tighter uppercase leading-none shadow-sm">E-QMS</h1>
                </div>
            </div>

            <div class="w-full lg:w-2/5 flex items-center justify-center p-8">
                <div class="w-full max-w-sm p-10 bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-gray-100 transform transition duration-500 hover:scale-105">
                    
                    <div class="flex flex-col items-center mb-8">
                        <div class="w-24 h-24 bg-white flex items-center justify-center rounded-2xl mb-4 p-1 scale-125">
                            <img src="{{ asset('img/logopkm.png') }}" alt="Logo Perusahaan" class="w-full h-full object-contain scale-225 items-center">
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">Silahkan Login</h3>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 text-center p-2 bg-red-50 rounded-lg">
                            @foreach ($errors->all() as $error)
                                <p class="text-red-600 text-xs font-semibold">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}"> 
                        @csrf 
                        
                        <div class="mb-4">
                            <input type="text" name="username" placeholder="Username" value="{{ old('username') }}" required 
                                class="w-full border border-gray-200 bg-gray-50 rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-900 focus:bg-white focus:outline-none transition-all placeholder-gray-400">
                        </div>

                        <div class="mb-6">
                            <input type="password" name="password" placeholder="Password" required 
                                class="w-full border border-gray-200 bg-gray-50 rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-900 focus:bg-white focus:outline-none transition-all placeholder-gray-400">
                        </div>

                        <button type="submit" class="w-full bg-blue-900 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-blue-800 active:scale-95 transition-all duration-150 uppercase tracking-wider text-sm">
                            Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 