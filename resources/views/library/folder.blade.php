@extends(request()->is('admin/*') || (Auth::check() && Auth::user()->role === 'admin') ? 'layouts.admin' : 'layouts.reviewer')

@section('title', 'e-QMS E-Library Folder: ' . $folder->name)
@section('header_title', 'General Library File Explorer')

@section('content')
<div class="space-y-6">
    
    <!-- TOP BREADCRUMB & HEADER -->
    @php
        $folderBackUrl = $folder->parent_id 
            ? route('library.folder.show', $folder->parent_id) 
            : (request()->is('admin/*') ? route('admin.library.index', ['category' => 'general']) : route('library.index', ['category' => 'general']));
    @endphp
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold mb-1">
                <x-back-button href="{{ $folderBackUrl }}" variant="light" />
                <span class="text-white/30">|</span>
                <a href="{{ route('library.index') }}" class="hover:text-[#ffe16e] font-bold flex items-center gap-1">
                    <i class="ph ph-books text-base"></i>
                    <span>Catalog</span>
                </a>
                <span>/</span>
                <a href="{{ route('library.index') }}?category=general" class="hover:text-[#ffe16e] font-semibold capitalize">General Library</a>
                
                @foreach($breadcrumbs as $bc)
                    <span>/</span>
                    @if($loop->last)
                        <span class="text-[#ffe16e] font-bold capitalize">{{ $bc->name }}</span>
                    @else
                        <a href="{{ route('library.folder.show', $bc->id) }}" class="hover:text-[#ffe16e] font-semibold capitalize">{{ $bc->name }}</a>
                    @endif
                @endforeach
            </div>
            <h2 class="text-xl font-extrabold tracking-tight capitalize">Folder: {{ $folder->name }}</h2>
        </div>

        <div class="flex items-center gap-3">
            @if(Auth::user()?->role === 'admin')
                <button onclick="openCreateFolderModal()" class="px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-md font-bold text-xs capitalize tracking-wider shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                    <i class="ph ph-folder-plus text-base"></i>
                    <span>Buat Subfolder</span>
                </button>
                <x-interactive-button type="button" text="Upload Berkas" variant="blue" icon="ph ph-cloud-arrow-up text-sm" onclick="openUploadFileModal()" />
            @endif
        </div>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="bg-white rounded-lg p-6 md:p-8 shadow-sm border border-sand-200/60 min-h-[500px]">
        
        <!-- SUBFOLDERS SECTION -->
        <div class="space-y-4">
            <div class="border-b border-sand-200/40 pb-3">
                <h3 class="text-xs font-extrabold capitalize tracking-wider text-on-surface">Folder & Direktori</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @forelse($folder->children as $child)
                    <div class="p-4 bg-sand-50 hover:bg-sky-50/50 rounded-md border border-sand-200 hover:border-[#00b4d8]/50 transition-all flex items-center justify-between shadow-sm group">
                        <a href="{{ route('library.folder.show', $child->id) }}" class="flex items-center space-x-3.5 flex-1">
                            <i class="ph ph-folder text-3xl text-[#00b4d8] group-hover:scale-110 transition-transform flex-shrink-0"></i>
                            <span class="font-bold text-xs text-on-surface capitalize tracking-wide group-hover:text-[#1677B8]">{{ $child->name }}</span>
                        </a>
                        @if(Auth::user()?->role === 'admin')
                            <form action="{{ route('admin.library.folder.destroy', $child->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus folder ini beserta seluruh berkas di dalamnya secara permanen?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-7 h-7 bg-white text-red-600 hover:bg-red-50 rounded shadow-sm border border-sand-200 flex items-center justify-center transition-all cursor-pointer">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full py-6 text-center text-on-surface-variant/60 text-xs italic font-semibold">
                        Tidak ada subfolder di dalam folder ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- FILES SECTION -->
        <div class="space-y-4 mt-8">
            <div class="border-b border-sand-200/40 pb-3 flex items-center justify-between">
                <h3 class="text-xs font-extrabold capitalize tracking-wider text-on-surface">Daftar Berkas Dokumen</h3>
                <span class="text-[10px] text-on-surface-variant font-semibold">Format Bebas (PDF, Word, Excel, Gambar, dll)</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($folder->files as $file)
                    @php
                        $ext = pathinfo($file->path, PATHINFO_EXTENSION);
                        $icon = 'ph-file';
                        if (in_array(strtolower($ext), ['pdf'])) {
                            $icon = 'ph-file-pdf';
                        } elseif (in_array(strtolower($ext), ['doc', 'docx'])) {
                            $icon = 'ph-file-doc';
                        } elseif (in_array(strtolower($ext), ['xls', 'xlsx'])) {
                            $icon = 'ph-file-xls';
                        } elseif (in_array(strtolower($ext), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) {
                            $icon = 'ph-file-image';
                        }
                    @endphp
                    <div class="p-5 bg-sand-50 rounded-lg border border-sand-200/70 hover:border-[#00b4d8]/50 transition-all flex flex-col justify-between min-h-[16rem] h-fit shadow-sm relative group">
                        
                        @if(Auth::user()?->role === 'admin')
                        <div class="absolute right-4 top-4 z-20">
                            <form action="{{ route('admin.library.file.destroy', $file->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus berkas ini secara permanen?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-7 h-7 bg-white text-red-600 hover:bg-red-50 rounded shadow-sm border border-sand-200 flex items-center justify-center transition-all cursor-pointer">
                                    <i class="ph ph-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                        @endif

                        <div>
                            <div class="flex items-center mb-3">
                                <i class="ph {{ $icon }} text-3xl text-[#00b4d8]"></i>
                            </div>
                            <h4 class="font-bold text-on-surface text-xs line-clamp-2 leading-relaxed pr-6 capitalize">{{ $file->name }}</h4>
                            <p class="text-[9px] text-[#8e8775] mt-1 font-semibold capitalize">Oleh: {{ $file->uploader->full_name ?? $file->uploader->username }}</p>
                            <p class="text-[9px] text-[#8e8775] font-semibold">Ukuran: {{ round($file->size / 1024, 2) }} KB</p>
                            <p class="text-[9px] text-[#8e8775] font-semibold">Waktu: {{ $file->created_at->format('Y/m/d H:i') }}</p>
                        </div>

                        <div class="flex flex-col space-y-1.5 pt-2 border-t border-sand-200/30">
                            @if(in_array(strtolower($ext), ['pdf', 'png', 'jpg', 'jpeg']))
                                <button onclick="viewFile('{{ route('library.file.stream', $file->id) }}', '{{ strtolower($ext) }}')" class="w-full py-2 bg-charcoal-900 hover:bg-black text-gold-fixed rounded text-[10px] font-bold text-center capitalize tracking-wider flex items-center justify-center gap-1 transition-all">
                                    <i class="ph ph-eye text-xs"></i>
                                    <span>Preview Dokumen</span>
                                </button>
                                @if(Auth::user()?->role === 'admin')
                                    <a href="{{ route('library.file.stream', $file->id) }}" download class="w-full py-2 bg-sand-50 hover:bg-sand-100 border border-sand-200 rounded text-[10px] font-bold text-on-surface-variant text-center capitalize tracking-wider flex items-center justify-center gap-1 transition-all">
                                        <i class="ph ph-download-simple text-xs"></i>
                                        <span>Download Berkas</span>
                                    </a>
                                @endif
                            @else
                                @if(Auth::user()?->role === 'admin')
                                    <a href="{{ route('library.file.stream', $file->id) }}" download class="w-full py-2 bg-sand-50 hover:bg-sand-100 border border-sand-200 rounded text-[10px] font-bold text-on-surface-variant text-center capitalize tracking-wider flex items-center justify-center gap-1 transition-all">
                                        <i class="ph ph-download-simple text-xs"></i>
                                        <span>Download Berkas</span>
                                    </a>
                                @else
                                    <button disabled class="w-full py-2 bg-sand-50/50 border border-sand-200/50 rounded text-[10px] font-bold text-gray-400 text-center capitalize tracking-wider flex items-center justify-center gap-1 cursor-not-allowed">
                                        <i class="ph ph-lock text-xs"></i>
                                        <span>Download Khusus Admin</span>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center text-on-surface-variant space-y-2">
                        <span class="material-symbols-outlined text-4xl text-[#d6cebf]">feed</span>
                        <p class="text-xs font-bold capitalize tracking-wider">Tidak ada berkas di dalam folder ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<!-- MODAL VIEW FILE -->
<div id="fileViewModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden p-4 md:p-10">
    <div class="bg-on-surface rounded-lg w-full h-full flex flex-col relative overflow-hidden shadow-2xl border border-sand-200/20">
        <button onclick="closeFile()" class="absolute right-4 top-4 z-50 w-8 h-8 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black transition-all">
            <span class="material-symbols-outlined text-lg">close</span>
        </button>
        <div class="flex-1 w-full h-full flex items-center justify-center p-4">
            <iframe id="pdfFrame" class="w-full h-full rounded hidden" src="" frameborder="0"></iframe>
            <img id="imageFrame" class="max-w-full max-h-full object-contain rounded hidden" src="">
        </div>
    </div>
</div>

<!-- MODAL CREATE SUBFOLDER -->
@if(Auth::user()?->role === 'admin')
<div id="createFolderModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg p-6 max-w-md w-full border border-sand-200 shadow-lg">
        <div class="flex items-center justify-between border-b border-sand-200/40 pb-3 mb-4">
            <h3 class="font-extrabold text-sm text-on-surface capitalize tracking-wider">Buat Subfolder</h3>
            <button onclick="closeCreateFolderModal()" class="text-gray-400 hover:text-gray-600">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <form action="{{ route('admin.library.folder.create') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $folder->id }}">
            <div>
                <label class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider mb-1 block">Nama Folder</label>
                <input type="text" name="name" class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none" placeholder="Ketik nama folder..." required>
            </div>
            <div class="flex space-x-3 pt-3">
                <button type="button" onclick="closeCreateFolderModal()" class="flex-1 py-2.5 bg-sand-50 border border-sand-200 text-on-surface-variant rounded-md font-bold text-xs capitalize">Batal</button>
                <x-interactive-button text="Buat Folder" class="flex-1" />
            </div>
        </form>
    </div>
</div>

<!-- MODAL UPLOAD FILE -->
<div id="uploadFileModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg p-6 max-w-md w-full border border-sand-200 shadow-lg">
        <div class="flex items-center justify-between border-b border-sand-200/40 pb-3 mb-4">
            <h3 class="font-extrabold text-sm text-on-surface capitalize tracking-wider">Upload Berkas Baru</h3>
            <button onclick="closeUploadFileModal()" class="text-gray-400 hover:text-gray-600">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <form action="{{ route('admin.library.folder.upload', $folder->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider mb-1 block">Pilih Berkas (Maks 20MB)</label>
                <x-file-input name="file" label="Pilih berkas" hint="Maksimal 20 MB" :max-size="20" :required="true" />
            </div>
            <div class="flex space-x-3 pt-3">
                <button type="button" onclick="closeUploadFileModal()" class="flex-1 py-2.5 bg-sand-50 border border-sand-200 text-on-surface-variant rounded-md font-bold text-xs capitalize">Batal</button>
                <x-interactive-button text="Upload Berkas" variant="success" class="flex-1" />
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function viewFile(url, ext) {
        if (ext === 'pdf') {
            document.getElementById('imageFrame').classList.add('hidden');
            document.getElementById('pdfFrame').src = url + "#toolbar=0&navpanes=0";
            document.getElementById('pdfFrame').classList.remove('hidden');
        } else {
            document.getElementById('pdfFrame').classList.add('hidden');
            document.getElementById('pdfFrame').src = "";
            document.getElementById('imageFrame').src = url;
            document.getElementById('imageFrame').classList.remove('hidden');
        }
        document.getElementById('fileViewModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeFile() {
        document.getElementById('fileViewModal').classList.add('hidden');
        document.getElementById('pdfFrame').src = "";
        document.getElementById('imageFrame').src = "";
        document.body.style.overflow = 'auto';
    }
    function openCreateFolderModal() {
        document.getElementById('createFolderModal').classList.remove('hidden');
    }
    function closeCreateFolderModal() {
        document.getElementById('createFolderModal').classList.add('hidden');
    }
    function openUploadFileModal() {
        document.getElementById('uploadFileModal').classList.remove('hidden');
    }
    function closeUploadFileModal() {
        document.getElementById('uploadFileModal').classList.add('hidden');
    }

    // Client-side Security & Anti-Leak protections
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    document.addEventListener('keydown', function(e) {
        // Disable F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U, Ctrl+S, Ctrl+P
        if (
            e.keyCode === 123 || 
            (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74)) || 
            (e.ctrlKey && (e.keyCode === 85 || e.keyCode === 83 || e.keyCode === 80))
        ) {
            e.preventDefault();
            return false;
        }
    });

    // CSS injection for print and user-select protection
    const style = document.createElement('style');
    style.innerHTML = `
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        @media print {
            body {
                display: none !important;
            }
        }
    `;
    document.head.appendChild(style);
</script>
@endsection
