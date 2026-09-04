@extends(request()->is('admin/*') || (Auth::check() && Auth::user()->role === 'admin') ? 'layouts.admin' : 'layouts.reviewer')

@section('title', 'e-QMS E-Library Folder: ' . $folder->name)
@section('header_title', 'General Library File Explorer')

@section('content')
<div class="space-y-6">
    
    <!-- TOP BREADCRUMB & HEADER -->
    @php
        $backParams = [];
        if ($folder->category) $backParams['category'] = $folder->category;
        if ($folder->division) $backParams['div'] = $folder->division;
        if ($folder->department) $backParams['bu'] = $folder->department;
        elseif ($folder->business_unit) $backParams['bu'] = $folder->business_unit;

        $folderBackUrl = $folder->parent_id 
            ? route('library.folder.show', $folder->parent_id) 
            : (count($backParams) > 0 ? route('library.index', $backParams) : (request()->is('admin/*') ? route('admin.library.index', ['category' => 'general']) : route('library.index', ['category' => 'general'])));
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
                
                @if($folder->department || $folder->business_unit)
                    <span>/</span>
                    <a href="{{ route('library.index', $backParams) }}" class="hover:text-[#ffe16e] font-semibold capitalize">
                        {{ $folder->department ?: $folder->business_unit }}
                    </a>
                @else
                    <span>/</span>
                    <a href="{{ route('library.index') }}?category=general" class="hover:text-[#ffe16e] font-semibold capitalize">General Library</a>
                @endif
                
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

                            @if(in_array(strtolower($ext), ['pdf', 'png', 'jpg', 'jpeg']))
                                <button onclick="viewFile('{{ route('library.file.stream', $file->id) }}', '{{ strtolower($ext) }}', '{{ addslashes($file->name) }}')" class="w-full py-2 bg-[#1677B8] hover:bg-[#125d91] text-white rounded-[2px] text-[10px] font-bold text-center capitalize tracking-wider flex items-center justify-center gap-1 transition-all cursor-pointer">
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

<!-- PROTECTED PDF VIEWER COMPONENT (CANVAS / NO DOWNLOAD) -->
<x-protected-pdf-viewer />

<!-- MODAL VIEW FILE (GAMBAR / NON-PDF PREVIEW) -->
<div id="fileViewModal" class="fixed inset-0 bg-slate-900/85 backdrop-blur-xs z-[100] flex flex-col p-2 sm:p-4 md:p-6 hidden" onclick="handleFileModalBackdrop(event)">
    <div class="w-full h-full flex flex-col max-w-6xl mx-auto shadow-2xl rounded-[2px] overflow-hidden bg-slate-900 border border-slate-700" onclick="event.stopPropagation()">
        <!-- MODAL TOP BAR -->
        <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white px-4 py-2.5 flex items-center justify-between flex-shrink-0 shadow-sm border-b border-white/10 select-none">
            <div class="flex items-center space-x-2.5 min-w-0 pr-3">
                <div class="w-7 h-7 bg-white/20 rounded-[2px] flex items-center justify-center flex-shrink-0 text-white">
                    <i class="ph ph-image text-base text-[#ffe16e]"></i>
                </div>
                <div class="min-w-0">
                    <h3 id="fileViewTitle" class="text-xs font-semibold uppercase tracking-wider text-white truncate">Preview Berkas</h3>
                    <p class="text-[10px] text-white/80 font-medium truncate">e-QMS PT PKM Group &bull; E-Library File Viewer (Hanya Baca)</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 flex-shrink-0">
                @if(Auth::check() && Auth::user()->role === 'admin')
                <a id="fileViewNewTab" href="#" target="_blank" 
                   class="px-2.5 py-1.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-[2px] border border-white/25 flex items-center gap-1.5 transition-all shadow-xs" 
                   title="Buka Dokumen di Tab Baru (Khusus Admin)">
                    <i class="ph ph-arrow-square-out text-sm"></i>
                    <span class="hidden sm:inline">Tab Baru</span>
                </a>
                @endif
                <button type="button" onclick="closeFile()" 
                        class="px-3 py-1.5 bg-white text-[#1677B8] hover:bg-slate-100 font-extrabold text-xs rounded-[2px] flex items-center gap-1.5 transition-all shadow-sm cursor-pointer" 
                        title="Tutup Preview Dokumen (Tekan ESC)">
                    <i class="ph ph-x text-sm"></i>
                    <span>Tutup (ESC)</span>
                </button>
            </div>
        </div>

        <!-- MODAL BODY: GAMBAR -->
        <div class="flex-1 w-full bg-slate-950 overflow-hidden relative flex items-center justify-center p-4" oncontextmenu="return false;">
            <img id="imageFrame" class="max-w-full max-h-full object-contain select-none shadow-xl border border-slate-700 hidden" src="" style="-webkit-touch-callout: none;">
        </div>
    </div>
</div>

<!-- MODAL CREATE SUBFOLDER -->
<!-- MODAL CREATE SUBFOLDER -->
@if(Auth::user()?->role === 'admin')
<div id="createFolderModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl p-6 max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-md bg-blue-50 text-[#1677B8] flex items-center justify-center font-bold">
                    <i class="ph ph-folder-plus text-base"></i>
                </div>
                <h3 class="font-extrabold text-sm text-slate-900 capitalize tracking-wider">Buat Subfolder</h3>
            </div>
            <button onclick="closeCreateFolderModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer text-lg">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <form action="{{ route('admin.library.folder.create') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $folder->id }}">
            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Nama Subfolder <span class="text-rose-500">*</span></label>
                <input type="text" name="name" class="w-full bg-slate-50 border border-slate-300 rounded-md p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#1677B8] outline-none" placeholder="Ketik nama subfolder..." required>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeCreateFolderModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-md transition-all cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs rounded-md transition-all shadow-sm cursor-pointer">
                    Buat Subfolder
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL UPLOAD FILE -->
<div id="uploadFileModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl w-full max-w-md shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-[#002b5c] via-[#1677B8] to-[#0ea5e9] text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold">
                    <i class="ph ph-cloud-arrow-up text-lg text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">Unggah Berkas ke Folder Ini</h3>
                    <p class="text-[10px] text-white/80 font-medium">Folder: {{ $folder->name }}</p>
                </div>
            </div>
            <button onclick="closeUploadFileModal()" class="text-white/80 hover:text-white text-lg cursor-pointer">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <form action="{{ route('admin.library.folder.upload', $folder->id) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1 block">Pilih Berkas Dokumen <span class="text-rose-500">*</span></label>
                <x-file-input name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip" label="Pilih berkas dokumen" hint="PDF, Word, Excel, PPT, Gambar, ZIP (Maksimal 20 MB)" :required="true" :maxSize="20" />
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeUploadFileModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-md transition-all cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs rounded-md transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
                    <i class="ph ph-cloud-arrow-up"></i>
                    <span>Unggah Berkas</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function viewFile(url, ext, name) {
        if (ext === 'pdf') {
            viewPDF(url, name);
            return;
        }

        document.getElementById('fileViewTitle').textContent = name || 'Preview Berkas';
        @if(Auth::check() && Auth::user()->role === 'admin')
            const newTabEl = document.getElementById('fileViewNewTab');
            if (newTabEl) newTabEl.href = url;
        @endif

        document.getElementById('imageFrame').src = url;
        document.getElementById('imageFrame').classList.remove('hidden');
        document.getElementById('fileViewModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeFile() {
        document.getElementById('fileViewModal').classList.add('hidden');
        document.getElementById('imageFrame').src = "";
        document.body.style.overflow = 'auto';
    }

    function handleFileModalBackdrop(e) {
        if (e.target.id === 'fileViewModal') {
            closeFile();
        }
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

    // Client-side Security & Anti-Leak protections + ESC Key to Close Modal
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    document.addEventListener('keydown', function(e) {
        // ESC key to close any open modal
        if (e.key === 'Escape' || e.keyCode === 27) {
            closeFile();
            if (typeof closeCreateFolderModal === 'function') closeCreateFolderModal();
            if (typeof closeUploadFileModal === 'function') closeUploadFileModal();
        }

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
