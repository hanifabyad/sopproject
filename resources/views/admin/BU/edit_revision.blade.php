@extends('layouts.admin')

@section('title', 'Perbaiki Dokumen SOP BU')
@section('header_title', 'Perbaikan Dokumen SOP Unit Bisnis')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- TOP BAR -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.BU.detail', $document->id) }}" class="px-3.5 py-1.5 bg-white border border-sand-200 text-charcoal-900 hover:bg-[#fff9ed] rounded-md font-bold text-xs transition-all shadow-sm flex items-center gap-1.5">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Kembali ke Detail Dokumen</span>
        </a>
    </div>

    <!-- MAIN CARD FORM CONTAINER -->
    <div class="bg-white rounded-lg p-6 md:p-8 shadow-sm border border-sand-200/60 space-y-6">
        <div class="border-b border-sand-200/40 pb-4">
            <span class="px-2.5 py-0.5 bg-red-50 text-red-700 font-bold text-[10px] uppercase rounded tracking-wider border border-red-200">Perbaikan Dokumen</span>
            <h2 class="text-xl font-extrabold text-on-surface uppercase tracking-tight mt-2">Unggah Perbaikan Dokumen SOP</h2>
            <p class="text-xs text-on-surface-variant mt-1">
                Unggah berkas baru <span class="font-bold text-red-600">hanya pada komponen yang memerlukan revisi</span>. Kolom yang dikosongkan akan tetap menggunakan berkas PDF lama.
            </p>
        </div>

        @if ($errors->any())
            <div class="p-3.5 bg-[#ffdad6] border-l-4 border-[#ba1a1a] text-[#ba1a1a] font-semibold text-xs rounded-r-md space-y-1">
                @foreach ($errors->all() as $error)
                    <p class="flex items-center gap-1.5"><span class="material-symbols-outlined text-sm">error</span> {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ auth()->user()->role === 'admin' ? route('admin.BU.update_revision', $document->id) : route('admin.BU.creator_update_revision', $document->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Judul Dokumen -->
            <div>
                <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1.5">Judul Dokumen / SOP</label>
                <input type="text" name="title" value="{{ old('title', $document->title) }}" required
                    class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-gold-500 outline-none transition-all">
            </div>

            <!-- 1. Cover -->
            <div class="p-4 bg-sand-50 rounded-md border border-sand-200 space-y-2">
                <label class="block text-xs font-bold text-on-surface">1. File Cover (PDF)</label>
                <p class="text-[11px] text-on-surface-variant">File saat ini: <span class="font-mono font-bold text-charcoal-900">{{ basename($document->file_cover) }}</span></p>
                <input type="file" name="file_cover" accept="application/pdf"
                    class="w-full text-xs font-semibold text-on-surface-variant file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-charcoal-900 file:text-gold-fixed hover:file:bg-black cursor-pointer">
            </div>

            <!-- 2. Lembar Pengesahan -->
            @if(empty($document->company_header))
            <div class="p-4 bg-sand-50 rounded-md border border-sand-200 space-y-2">
                <label class="block text-xs font-bold text-on-surface">2. File Lembar Pengesahan (PDF)</label>
                <p class="text-[11px] text-on-surface-variant">File saat ini: <span class="font-mono font-bold text-charcoal-900">{{ basename($document->file_lp) }}</span></p>
                <input type="file" name="file_lp" accept="application/pdf"
                    class="w-full text-xs font-semibold text-on-surface-variant file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-charcoal-900 file:text-gold-fixed hover:file:bg-black cursor-pointer">
            </div>
            @else
            <div class="p-4 bg-[#fff9ed] rounded-md border border-dashed border-sand-200 space-y-1">
                <div class="flex items-center space-x-2 text-gold-500 font-bold text-xs">
                    <span class="material-symbols-outlined text-base">auto_awesome</span>
                    <span>2. Lembar Pengesahan (Auto-Generated)</span>
                </div>
                <p class="text-[11px] text-on-surface-variant leading-relaxed">
                    Tabel Lembar Pengesahan di-generate secara otomatis oleh sistem menggunakan data peninjau aktif. Berkas LP akan diperbarui secara otomatis saat revisi dikirim.
                </p>
            </div>
            @endif

            <!-- 3. File Isi -->
            <div class="p-4 bg-sand-50 rounded-md border border-sand-200 space-y-2">
                <label class="block text-xs font-bold text-on-surface">3. File Isi SOP (PDF)</label>
                <p class="text-[11px] text-on-surface-variant">File saat ini: <span class="font-mono font-bold text-charcoal-900">{{ basename($document->file_isi) }}</span></p>
                <input type="file" name="file_isi" accept="application/pdf"
                    class="w-full text-xs font-semibold text-on-surface-variant file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-charcoal-900 file:text-gold-fixed hover:file:bg-black cursor-pointer">
            </div>

            <!-- 4. Lampiran -->
            <div class="p-4 bg-sand-50 rounded-md border border-sand-200 space-y-3">
                <label class="block text-xs font-bold text-on-surface">4. File Lampiran (PDF) — <span class="text-on-surface-variant font-normal">Opsional (Maksimal 20 file)</span></label>
                
                @php $allAtts = $document->all_attachments; @endphp
                @if($allAtts->count() > 0)
                    <p class="text-xs font-bold text-on-surface-variant">Lampiran Saat Ini (Centang file yang ingin DIHAPUS):</p>
                    <div class="space-y-2">
                        @foreach($allAtts as $att)
                            <label class="flex items-center justify-between p-2.5 bg-white rounded-md border border-sand-200 text-xs font-semibold cursor-pointer hover:bg-red-50 transition-all">
                                <div class="flex items-center gap-2 truncate pr-2">
                                    <span class="material-symbols-outlined text-red-600 text-sm">picture_as_pdf</span>
                                    <span class="font-mono text-on-surface truncate">{{ $att->original_name ?? basename($att->file_path) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 text-red-600 font-bold">
                                    <input type="checkbox" name="deleted_attachments[]" value="{{ $att->id }}" class="rounded text-red-600 focus:ring-red-500 w-3.5 h-3.5 border-sand-200">
                                    <span>Hapus</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-on-surface-variant">File saat ini: <span class="font-mono font-bold text-charcoal-900">Tidak ada lampiran</span></p>
                @endif

                <div class="pt-2">
                    <label class="block text-xs font-bold text-on-surface mb-1">Tambah Lampiran Baru (PDF):</label>
                    <input type="file" name="file_lampiran[]" multiple accept="application/pdf"
                        class="w-full text-xs font-semibold text-on-surface-variant file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-charcoal-900 file:text-gold-fixed hover:file:bg-black cursor-pointer">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-3">
                <button type="submit" 
                    class="w-full bg-charcoal-900 text-gold-fixed hover:bg-black font-bold py-3 rounded-md shadow-sm transition-all uppercase tracking-wider text-xs flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">send</span>
                    <span>Kirim File Perbaikan & Lewati yang Sudah Approved</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

