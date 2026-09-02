@extends('layouts.admin')

@section('title', 'Perbaiki Dokumen SOP BU')
@section('header_title', 'Perbaikan Dokumen SOP Unit Bisnis')

@section('content')
<div class="space-y-6">
    
    <!-- TOP HEADER CONTAINER WITH LEFT BACK BUTTON -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.BU.detail', $document->id) }}" variant="light" text="Kembali" />
            <span class="text-white/30">|</span>
            <div class="flex items-center gap-2 text-xs text-white/80 font-semibold">
                <a href="{{ route('admin.BU.index') }}" class="hover:text-[#ffe16e] font-medium">Business Unit</a>
                <span>/</span>
                <a href="{{ route('admin.BU.show', $document->department) }}" class="hover:text-[#ffe16e] font-medium">{{ $document->department }}</a>
                <span>/</span>
                <span class="font-bold text-white">Revisi ({{ $document->document_number ?? $document->doc_number }})</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl font-extrabold tracking-tight text-white capitalize">Unggah Perbaikan Dokumen SOP</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">Periksa catatan coretan reviewer di sisi kiri dan unggah draf perbaikan di sisi kanan</p>
        </div>
    </div>

    @php
        $latestReject = $document->latestRejectedApproval 
            ?? $document->approvals()->whereIn('status', ['rejected', 'need_revision'])->latest('id')->first();
        
        $latestRejectLog = $document->logs()->where('action', 'minta_revisi')->latest('id')->first();
        
        $reviewerNotes = ($latestReject && !empty(trim($latestReject->notes ?? ''))) 
            ? $latestReject->notes 
            : ($latestRejectLog ? $latestRejectLog->notes : null);
            
        $annotations = $latestReject ? $latestReject->annotations : null;
        
        $reviewerUser = $latestReject?->user 
            ?? $latestRejectLog?->user 
            ?? $document->approvals()->where('stage', 'reviewer')->latest('id')->first()?->user;
            
        $hasAnnotations = !empty($annotations) && $annotations !== '[]' && $annotations !== '{}' && $annotations !== 'null';

        $revisionFormAction = auth()->user()->role === 'admin' 
            ? route('admin.BU.update_revision', $document->id) 
            : route('admin.BU.creator_update_revision', $document->id);
    @endphp

    <!-- MAIN WORKSPACE SPLIT-SCREEN (PREVIEW LEFT, UPLOAD FORM RIGHT) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT: VISUAL PDF ANNOTATIONS & REVIEWER FEEDBACK (7 COLS) -->
        <div class="lg:col-span-7 xl:col-span-7 space-y-4 w-full">
            
            <!-- REVIEWER FEEDBACK CARD -->
            <div class="bg-white rounded-lg p-5 shadow-sm border border-sand-200/60 space-y-3">
                <div class="flex items-center justify-between border-b border-sand-200/40 pb-3">
                    <div class="flex items-center space-x-2.5">
                        <i class="ph ph-note-pencil text-2xl text-[#00b4d8]"></i>
                        <div>
                            <h3 class="text-xs font-extrabold text-on-surface capitalize tracking-wider">Catatan & Anotasi Perbaikan dari Reviewer</h3>
                            <p class="text-[11px] text-on-surface-variant">
                                Peninjau: <strong>{{ $reviewerUser ? ($reviewerUser->full_name ?? $reviewerUser->username) : 'Pimpinan Reviewer' }}</strong> 
                                @if($reviewerUser && $reviewerUser->role)
                                    ({{ $reviewerUser->role }})
                                @endif
                            </p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded bg-red-50 text-red-700 text-[10px] font-extrabold border border-red-200 flex items-center gap-1">
                        <i class="ph ph-warning-circle text-xs"></i>
                        <span>Perlu Perbaikan</span>
                    </span>
                </div>

                @if($reviewerNotes)
                <div class="p-3.5 bg-amber-50/80 border border-amber-200 rounded-md text-xs text-amber-950 space-y-1 shadow-sm">
                    <div class="flex items-center gap-1.5 font-bold text-[11px] uppercase tracking-wider text-amber-900">
                        <i class="ph ph-chat-text text-sm"></i>
                        <span>Instruksi / Catatan Revisi:</span>
                    </div>
                    <p class="leading-relaxed font-semibold pl-5 text-[11.5px] whitespace-pre-line">{{ $reviewerNotes }}</p>
                </div>
                @endif

                <!-- PDF VIEWER WITH VISUAL ANNOTATIONS -->
                <div class="space-y-2 pt-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-on-surface flex items-center gap-1.5">
                            <i class="ph ph-file-pdf text-sm text-[#1677B8]"></i>
                            <span>Pratinjau Berkas & Coretan Visual:</span>
                        </span>
                        @if($hasAnnotations)
                            <span class="text-[10px] bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded font-bold flex items-center gap-1">
                                <i class="ph ph-pencil-line text-xs"></i>
                                <span>Terdapat Coretan Anotasi</span>
                            </span>
                        @else
                            <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-semibold">
                                Naskah Dokumen Sebelum Revisi
                            </span>
                        @endif
                    </div>

                    <x-pdf-annotator 
                        :pdf-url="route('reviewer.stream.file', $document->id)"
                        :read-only="true"
                        :initial-annotations="$annotations"
                        height="700px"
                    />
                </div>
            </div>

        </div>

        <!-- RIGHT: UPLOAD REVISION FORM (5 COLS) -->
        <div class="lg:col-span-5 xl:col-span-5 w-full">
            <div class="bg-white rounded-lg p-5 md:p-6 shadow-sm border border-sand-200/60 space-y-5 sticky top-6">
                <div class="border-b border-sand-200/40 pb-3">
                    <span class="px-2 py-0.5 bg-red-50 text-red-700 font-bold text-[10px] uppercase rounded tracking-wider border border-red-200">Form Upload Revisi</span>
                    <h3 class="text-base font-extrabold text-on-surface capitalize tracking-tight mt-1.5">Unggah Berkas Perbaikan</h3>
                    <p class="text-[11px] text-on-surface-variant mt-0.5 leading-relaxed">
                        Unggah berkas baru <span class="font-bold text-red-600">hanya pada bagian yang memerlukan revisi</span>. Bagian yang dikosongkan akan tetap memakai berkas lama.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="p-3 bg-[#ffdad6] border-l-4 border-[#ba1a1a] text-[#ba1a1a] font-semibold text-xs rounded-r-md space-y-1">
                        @foreach ($errors->all() as $error)
                            <p class="flex items-center gap-1.5"><span class="material-symbols-outlined text-sm">error</span> {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form action="{{ $revisionFormAction }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Judul Dokumen -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface capitalize tracking-wider mb-1">Judul Dokumen / SOP</label>
                        <input type="text" name="title" value="{{ old('title', $document->title) }}" required
                            class="w-full bg-sand-50 border border-sand-200 rounded-md p-2.5 font-semibold text-xs text-on-surface focus:bg-white focus:ring-2 focus:ring-[#1677B8] outline-none transition-all">
                    </div>

                    <!-- 1. Cover -->
                    <div class="p-3 bg-sand-50 rounded-md border border-sand-200 space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface">1. File Cover (PDF)</label>
                        <p class="text-[10.5px] text-on-surface-variant">File saat ini: <span class="font-mono font-bold text-slate-800">{{ basename($document->file_cover) }}</span></p>
                        <x-file-input name="file_cover" accept="application/pdf" label="Pilih file cover baru" hint="PDF, maks 10 MB" />
                    </div>

                    <!-- 2. Lembar Pengesahan Info -->
                    <div class="p-3 bg-[#f0f9ff] rounded-md border border-[#00b4d8]/30 space-y-1">
                        <div class="flex items-center space-x-1.5 text-[#1677B8] font-bold text-xs">
                            <i class="ph ph-sparkle text-sm text-[#00b4d8]"></i>
                            <span>2. Lembar Pengesahan (Otomatis)</span>
                        </div>
                        <p class="text-[10.5px] text-on-surface-variant leading-relaxed">
                            LP, tabel tanda tangan, dan nomor revisi (Rev {{ (int)$document->doc_revision + 1 }}) dibuat otomatis oleh sistem.
                        </p>
                    </div>

                    <!-- 3. File Isi -->
                    <div class="p-3 bg-sand-50 rounded-md border border-sand-200 space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface">3. File Isi SOP (PDF)</label>
                        <p class="text-[10.5px] text-on-surface-variant">File saat ini: <span class="font-mono font-bold text-slate-800">{{ basename($document->file_isi) }}</span></p>
                        <x-file-input name="file_isi" accept="application/pdf" label="Pilih file isi SOP baru" hint="PDF, maks 10 MB" />
                    </div>

                    <!-- 4. Lampiran -->
                    <div class="p-3 bg-sand-50 rounded-md border border-sand-200 space-y-2.5">
                        <label class="block text-xs font-bold text-on-surface">4. File Lampiran (PDF) — <span class="text-on-surface-variant font-normal text-[10.5px]">Opsional</span></label>
                        
                        @php $allAtts = $document->all_attachments; @endphp
                        @if($allAtts->count() > 0)
                            <p class="text-[10.5px] font-bold text-on-surface-variant">Lampiran Saat Ini (Centang untuk Hapus):</p>
                            <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1 custom-scrollbar">
                                @foreach($allAtts as $att)
                                    <label class="flex items-center justify-between p-2 bg-white rounded border border-sand-200 text-xs font-semibold cursor-pointer hover:bg-red-50 transition-all">
                                        <div class="flex items-center gap-1.5 truncate pr-2">
                                            <i class="ph ph-file-pdf text-red-600 text-sm"></i>
                                            <span class="font-mono text-[11px] text-on-surface truncate">{{ $att->original_name ?? basename($att->file_path) }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 text-red-600 font-bold text-[10.5px]">
                                            <input type="checkbox" name="deleted_attachments[]" value="{{ $att->id }}" class="rounded text-red-600 focus:ring-red-500 w-3.5 h-3.5 border-sand-200">
                                            <span>Hapus</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-[10.5px] text-on-surface-variant">File saat ini: <span class="font-mono font-bold text-slate-800">Tidak ada lampiran</span></p>
                        @endif

                        <div class="pt-1">
                            <label class="block text-[11px] font-bold text-on-surface mb-1">Tambah Lampiran Baru (PDF):</label>
                            <x-file-input name="file_lampiran[]" accept="application/pdf" label="Pilih lampiran baru" hint="Pilih beberapa PDF (maks 20)" :multiple="true" />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" 
                            class="w-full btn-interactive font-extrabold capitalize tracking-wider py-3 shadow-md">
                            <span class="btn-interactive-default">
                                <span class="btn-interactive-dot"></span>
                                <span>Kirim File Perbaikan</span>
                            </span>
                            <span class="btn-interactive-hover">
                                <span>Kirim File Perbaikan</span>
                                <i class="ph ph-arrow-right text-sm"></i>
                            </span>
                            <span class="btn-interactive-bg"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
