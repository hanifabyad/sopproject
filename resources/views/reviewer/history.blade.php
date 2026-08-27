@extends('layouts.reviewer')

@section('title', 'Riwayat')
@section('header_title', 'Riwayat Dokumen')

@section('content')
<div class="space-y-6">
    <!-- HEADER HERO BANNER -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-sm p-6 shadow-sm relative overflow-hidden flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold text-white tracking-tight uppercase">Riwayat Dokumen</h2>
            <p class="text-xs text-white/85 mt-1 font-medium">Dokumen yang pernah Anda setujui atau proses dalam alur e-QMS.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3.5 py-2 bg-white/20 border border-white/10 rounded-md text-xs font-bold text-white shadow-inner">
                <span class="material-symbols-outlined text-base text-[#ffe16e]">history</span>
                <span>{{ $documents->count() }} Total Dokumen</span>
            </div>
        </div>
    </div>

    <!-- KATEGORI 1 (ATAS): DOKUMEN MASIH BERJALAN -->
    <div class="bg-white rounded-sm shadow-sm border border-sand-200/60 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-sand-200/60 flex items-center justify-between gap-3 bg-amber-50/40">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-md bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center justify-center font-bold">
                    <span class="material-symbols-outlined text-lg">hourglass_top</span>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-on-surface uppercase tracking-wider flex items-center gap-2">
                        Dokumen Masih Berjalan
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-extrabold">{{ $inProgressDocs->count() }}</span>
                    </h3>
                    <p class="text-[11px] text-on-surface-variant mt-0.5">Dokumen yang telah Anda proses namun alur persetujuan masih berjalan di pihak lain.</p>
                </div>
            </div>
        </div>

        @if($inProgressDocs->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-0 table-fixed text-left">
                    <thead class="bg-sand-50 border-b border-sand-200 text-[10px] font-extrabold uppercase tracking-wider text-on-surface-variant">
                        <tr>
                            <th class="hidden sm:table-cell px-2 py-3 w-8 text-center">No</th>
                            <th class="px-2 sm:px-4 py-3 w-[40%]">Dokumen SOP</th>
                            <th class="hidden 2xl:table-cell px-3 sm:px-5 py-3">Unit / Departemen</th>
                            <th class="px-2 sm:px-4 py-3 w-[16%]">Status Alur</th>
                            <th class="px-2 sm:px-4 py-3 w-[19%]">Terakhir Diproses</th>
                            <th class="px-2 sm:px-4 py-3 w-[13%] text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sand-200/60 text-xs">
                        @foreach($inProgressDocs as $index => $doc)
                            @php
                                $myApprovalData = \App\Models\DocumentApproval::where('document_id', $doc->id)->where('user_id', Auth::id())->latest('processed_at')->first();
                                $myNotes = $myApprovalData?->notes;
                                $processedAt = $myApprovalData?->processed_at ?? $doc->updated_at;
                                $processedAt = $processedAt ? \Carbon\Carbon::parse($processedAt) : null;
                            @endphp
                            <tr class="hover:bg-[#fffaf0] transition-colors">
                                <td class="hidden sm:table-cell px-2 py-4 text-center text-[11px] font-bold text-sand-500">{{ $loop->iteration }}</td>
                                <td class="px-2 sm:px-4 py-4">
                                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                        <div class="w-8 h-8 sm:w-9 sm:h-9 flex-shrink-0 rounded-md bg-amber-500/10 text-amber-700 border border-amber-200 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-base sm:text-lg">pending_actions</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-on-surface truncate max-w-[180px] sm:max-w-[290px]">{{ $doc->title }}</p>
                                            <p class="text-[10px] text-on-surface-variant mt-0.5">No. {{ $doc->doc_number ?? '-' }} · Rev. {{ $doc->doc_revision ?? '0' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden 2xl:table-cell px-3 sm:px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wide text-on-surface-variant">
                                        <span class="material-symbols-outlined text-sm text-[#1677B8]">domain</span>{{ $doc->department ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-5 py-4">
                                    @if($doc->status === 'rejected' || $doc->status === 'need_revision')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-rose-50 border border-rose-200 text-rose-700 text-[10px] font-extrabold uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>Perlu Revisi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-50 border border-amber-200 text-amber-700 text-[10px] font-extrabold uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Proses Review
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-5 py-4 text-[11px] font-semibold text-on-surface-variant whitespace-normal">
                                    {{ $processedAt?->format('d M Y, H:i') ?? '-' }}
                                </td>
                                <td class="px-3 sm:px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5 sm:gap-2">
                                        <a href="{{ route('reviewer.show', $doc->id) }}" class="inline-flex items-center gap-1 px-2.5 sm:px-3 py-2 bg-charcoal-900 text-gold-fixed hover:bg-black rounded-md text-[10px] font-extrabold uppercase tracking-wide transition-colors">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
                                            <span class="hidden sm:inline">Lihat</span>
                                        </a>
                                        @if(filled($myNotes))
                                            <button type="button" onclick='openNotesModal(@json($doc->title), @json($myNotes))' class="inline-flex items-center justify-center w-8 h-8 bg-canvas border border-sand-200 text-on-surface-variant hover:text-[#1677B8] hover:border-[#1677B8] rounded-md transition-colors" title="Lihat catatan">
                                                <span class="material-symbols-outlined text-base">comment</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 px-6 text-center text-xs text-on-surface-variant">
                <span class="material-symbols-outlined text-sand-400 text-2xl block mb-1">check_circle</span>
                Tidak ada dokumen yang sedang berjalan saat ini.
            </div>
        @endif
    </div>

    <!-- KATEGORI 2 (BAWAH): DOKUMEN SUDAH SELESAI -->
    <div class="bg-white rounded-sm shadow-sm border border-sand-200/60 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-sand-200/60 flex items-center justify-between gap-3 bg-emerald-50/40">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 flex items-center justify-center font-bold">
                    <span class="material-symbols-outlined text-lg">task_alt</span>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-on-surface uppercase tracking-wider flex items-center gap-2">
                        Dokumen Sudah Selesai
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold">{{ $completedDocs->count() }}</span>
                    </h3>
                    <p class="text-[11px] text-on-surface-variant mt-0.5">Dokumen yang telah selesai melalui seluruh alur persetujuan dan resmi tersimpan di E-Library.</p>
                </div>
            </div>
        </div>

        @if($completedDocs->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-0 table-fixed text-left">
                    <thead class="bg-sand-50 border-b border-sand-200 text-[10px] font-extrabold uppercase tracking-wider text-on-surface-variant">
                        <tr>
                            <th class="hidden sm:table-cell px-2 py-3 w-8 text-center">No</th>
                            <th class="px-2 sm:px-4 py-3 w-[40%]">Dokumen SOP</th>
                            <th class="hidden 2xl:table-cell px-3 sm:px-5 py-3">Unit / Departemen</th>
                            <th class="px-2 sm:px-4 py-3 w-[16%]">Status Final</th>
                            <th class="px-2 sm:px-4 py-3 w-[19%]">Terakhir Diproses</th>
                            <th class="px-2 sm:px-4 py-3 w-[13%] text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sand-200/60 text-xs">
                        @foreach($completedDocs as $index => $doc)
                            @php
                                $myApprovalData = \App\Models\DocumentApproval::where('document_id', $doc->id)->where('user_id', Auth::id())->latest('processed_at')->first();
                                $myNotes = $myApprovalData?->notes;
                                $processedAt = $myApprovalData?->processed_at ?? $doc->updated_at;
                                $processedAt = $processedAt ? \Carbon\Carbon::parse($processedAt) : null;
                            @endphp
                            <tr class="hover:bg-[#f4fdf8] transition-colors">
                                <td class="hidden sm:table-cell px-2 py-4 text-center text-[11px] font-bold text-sand-500">{{ $loop->iteration }}</td>
                                <td class="px-2 sm:px-4 py-4">
                                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                        <div class="w-8 h-8 sm:w-9 sm:h-9 flex-shrink-0 rounded-md bg-emerald-600 text-white flex items-center justify-center">
                                            <span class="material-symbols-outlined text-base sm:text-lg">verified</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-on-surface truncate max-w-[180px] sm:max-w-[290px]">{{ $doc->title }}</p>
                                            <p class="text-[10px] text-on-surface-variant mt-0.5">No. {{ $doc->doc_number ?? '-' }} · Rev. {{ $doc->doc_revision ?? '0' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden 2xl:table-cell px-3 sm:px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wide text-on-surface-variant">
                                        <span class="material-symbols-outlined text-sm text-[#1677B8]">domain</span>{{ $doc->department ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-extrabold uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Selesai / Aktif
                                    </span>
                                </td>
                                <td class="px-3 sm:px-5 py-4 text-[11px] font-semibold text-on-surface-variant whitespace-normal">
                                    {{ $processedAt?->format('d M Y, H:i') ?? '-' }}
                                </td>
                                <td class="px-3 sm:px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5 sm:gap-2">
                                        <a href="{{ route('reviewer.show', $doc->id) }}" class="inline-flex items-center gap-1 px-2.5 sm:px-3 py-2 bg-charcoal-900 text-gold-fixed hover:bg-black rounded-md text-[10px] font-extrabold uppercase tracking-wide transition-colors">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
                                            <span class="hidden sm:inline">Lihat</span>
                                        </a>
                                        @if(filled($myNotes))
                                            <button type="button" onclick='openNotesModal(@json($doc->title), @json($myNotes))' class="inline-flex items-center justify-center w-8 h-8 bg-canvas border border-sand-200 text-on-surface-variant hover:text-[#1677B8] hover:border-[#1677B8] rounded-md transition-colors" title="Lihat catatan">
                                                <span class="material-symbols-outlined text-base">comment</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 px-6 text-center text-xs text-on-surface-variant">
                <span class="material-symbols-outlined text-sand-400 text-2xl block mb-1">history</span>
                Belum ada dokumen yang selesai disetujui secara final.
            </div>
        @endif
    </div>
</div>

<!-- MODAL CATATAN APPROVAL -->
<div id="notesModal" class="fixed inset-0 z-[110] hidden bg-charcoal-900/70 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-lg w-full max-w-xl p-6 shadow-2xl border border-sand-200">
        <div class="flex items-start justify-between gap-4 border-b border-sand-200/60 pb-4">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-gold-500">Catatan Approval</p>
                <h3 id="modalDocTitle" class="text-sm font-extrabold text-on-surface mt-1"></h3>
            </div>
            <button type="button" onclick="closeNotesModal()" class="w-8 h-8 rounded-md bg-canvas text-on-surface-variant hover:text-rose-600 border border-sand-200 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <div class="mt-5 p-4 bg-canvas border border-sand-200 rounded-md max-h-80 overflow-y-auto">
            <p id="modalNotesContent" class="text-xs text-on-surface leading-relaxed whitespace-pre-line"></p>
        </div>
        <div class="mt-5 flex justify-end">
            <button type="button" onclick="closeNotesModal()" class="px-4 py-2 bg-charcoal-900 text-gold-fixed hover:bg-black rounded-md text-[10px] font-extrabold uppercase tracking-wider">Tutup</button>
        </div>
    </div>
</div>

<script>
function openNotesModal(title, notes){
    document.getElementById('modalDocTitle').textContent=title;
    document.getElementById('modalNotesContent').textContent=notes;
    document.getElementById('notesModal').classList.remove('hidden');
    document.body.style.overflow='hidden';
}
function closeNotesModal(){
    document.getElementById('notesModal').classList.add('hidden');
    document.body.style.overflow='';
}
window.addEventListener('keydown',event=>{if(event.key==='Escape')closeNotesModal();});
</script>
@endsection
