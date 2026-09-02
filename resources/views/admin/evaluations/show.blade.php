@extends('layouts.admin')

@section('title', 'Tinjau Evaluasi - ' . ($document->title ?? 'SOP'))
@section('header_title', 'Tinjauan Hasil Evaluasi SOP')

@section('content')
<div class="space-y-6">

    <!-- TOP GRADIENT HEADER BAR -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('admin.evaluations.index') }}" variant="light" />
            <span class="text-white/30">|</span>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold">
                <a href="{{ route('admin.evaluations.index') }}" class="hover:text-[#ffe16e] font-bold">Daftar Evaluasi</a>
                <span>/</span>
                <span class="text-[#ffe16e] font-bold capitalize">{{ $document->department }}</span>
                <span>/</span>
                <span class="text-white/90 font-mono">{{ $document->doc_number ?? 'SOP' }}</span>
            </div>
        </div>

        <!-- Baris Judul -->
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold tracking-tight capitalize text-white">{{ $document->title }}</h2>
            <p class="text-xs text-white/85 mt-1 font-medium">
                Periode Evaluasi: <strong>{{ $evaluation->evaluation_period }}</strong> &bull; Versi Revisi: <strong>Rev {{ $document->doc_revision ?? '0' }}</strong> &bull; Status Operasional: <strong>{{ strtoupper($document->status) }}</strong>
            </p>
        </div>
    </div>

    <!-- MAIN WORKSPACE SPLIT-SCREEN -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT: HASIL TANGGAPAN EVALUATOR & PRATINJAU DOKUMEN PDF -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- RESPONSE SUMMARY CARD -->
            <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-5">
                <div class="border-b border-sand-200/50 pb-3 flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-md bg-blue-50 text-[#1677B8] flex items-center justify-center font-bold">
                            <i class="ph ph-clipboard-text text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-xs capitalize tracking-wider text-on-surface">Hasil Tanggapan Evaluator</h4>
                            <p class="text-[11px] text-on-surface-variant">Evaluator: <strong>{{ $evaluation->evaluator->full_name ?? ($evaluation->evaluator->username ?? 'Kepala Dept/BU') }}</strong></p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-extrabold capitalize tracking-wide {{ $evaluation->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                        <i class="ph {{ $evaluation->status === 'completed' ? 'ph-check-circle' : 'ph-clock' }}"></i>
                        Status: {{ strtoupper($evaluation->status) }}
                    </span>
                </div>

                <!-- RESPONSE QUESTIONS GRID -->
                <div class="space-y-3.5 text-xs font-semibold text-on-surface">
                    
                    <!-- A. Status Penggunaan -->
                    <div class="p-3.5 bg-canvas rounded-md border border-sand-200/60 space-y-1">
                        <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block">A. Status Penggunaan SOP</span>
                        <p class="text-on-surface font-extrabold text-xs">{{ $evaluation->usage_status ?: 'Belum diisi' }}</p>
                        @if($evaluation->usage_reason)
                        <div class="text-rose-700 text-[11px] font-bold mt-1.5 bg-rose-50 p-2.5 border border-rose-200 rounded-md">
                            Alasan: {{ $evaluation->usage_reason }}
                        </div>
                        @endif
                    </div>

                    <!-- B. Kesesuaian Proses Kerja -->
                    <div class="p-3.5 bg-canvas rounded-md border border-sand-200/60 space-y-1">
                        <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block">B. Kesesuaian Proses Kerja</span>
                        <p class="text-on-surface font-extrabold text-xs">{{ $evaluation->conformity_status ?: 'Belum diisi' }}</p>
                        @if($evaluation->conformity_notes)
                        <div class="text-[11px] font-medium mt-1.5 text-on-surface-variant bg-white p-2.5 border border-sand-200/70 rounded-md">
                            <span class="font-bold text-on-surface block text-[10px] mb-0.5">Catatan Bagian yang Perlu Diperbarui:</span>
                            {{ $evaluation->conformity_notes }}
                        </div>
                        @endif
                    </div>

                    <!-- C. Perubahan Proses Kerja -->
                    <div class="p-3.5 bg-canvas rounded-md border border-sand-200/60 space-y-1">
                        <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block">C. Perubahan Proses Kerja Lapangan</span>
                        <p class="text-on-surface font-extrabold text-xs">{{ $evaluation->process_change_status ?: 'Belum diisi' }}</p>
                        @if($evaluation->process_change_notes)
                        <div class="text-[11px] font-medium mt-1.5 text-on-surface-variant bg-white p-2.5 border border-sand-200/70 rounded-md">
                            <span class="font-bold text-on-surface block text-[10px] mb-0.5">Uraian Perubahan:</span>
                            {{ $evaluation->process_change_notes }}
                        </div>
                        @endif
                    </div>

                    <!-- D. Tingkat Efektivitas -->
                    <div class="p-3.5 bg-canvas rounded-md border border-sand-200/60 space-y-1">
                        <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block">D. Tingkat Efektivitas SOP</span>
                        <p class="text-on-surface font-extrabold text-xs">{{ $evaluation->effectiveness_status ?: 'Belum diisi' }}</p>
                    </div>

                    <!-- E. Kendala Implementasi -->
                    <div class="p-3.5 bg-canvas rounded-md border border-sand-200/60 space-y-1">
                        <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block">E. Kendala Penerapan di Lapangan</span>
                        <p class="text-on-surface font-medium leading-relaxed">{{ $evaluation->implementation_issues ?: '-' }}</p>
                    </div>

                    <!-- F. Rekomendasi Evaluator -->
                    <div class="p-3.5 bg-canvas rounded-md border border-sand-200/60 space-y-1">
                        <span class="text-[10px] font-bold text-on-surface-variant capitalize tracking-wider block">F. Rekomendasi / Saran Evaluator</span>
                        <p class="text-on-surface font-medium leading-relaxed">{{ $evaluation->recommendation ?: '-' }}</p>
                    </div>

                    <!-- Kesimpulan Hasil Evaluator -->
                    <div class="p-4 bg-amber-50 rounded-md border border-amber-200 space-y-1 text-center">
                        <span class="text-[10px] font-bold text-amber-900 capitalize tracking-wider block">Rekomendasi Keputusan Evaluator</span>
                        <span class="inline-flex px-3.5 py-1 bg-amber-100 border border-amber-300 text-amber-900 text-xs font-extrabold capitalize rounded-md mt-1 shadow-sm">
                            {{ $evaluation->result ?: 'BELUM ADA KESIMPULAN' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- PDF VIEWPORT CARD -->
            <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
                <div class="flex items-center justify-between border-b border-sand-200/50 pb-3 text-xs font-bold text-on-surface">
                    <div class="flex items-center space-x-2">
                        <i class="ph ph-file-pdf text-rose-600 text-lg"></i>
                        <span>Pratinjau Berkas Dokumen SOP</span>
                    </div>
                    <a href="{{ route('evaluations.stream', $evaluation->id) }}" target="_blank" class="flex items-center gap-1 text-[#1677B8] hover:text-[#0f5482] font-bold text-[11px] transition-colors">
                        <span>Buka Tab Baru</span>
                        <i class="ph ph-arrow-square-out text-sm"></i>
                    </a>
                </div>
                <div class="h-[580px] bg-slate-900 rounded-md overflow-hidden border border-sand-200 relative shadow-inner">
                    <iframe src="{{ route('evaluations.stream', $evaluation->id) }}#toolbar=0&navpanes=0&view=FitH" 
                            class="w-full h-full border-none">
                    </iframe>
                </div>
            </div>
        </div>

        <!-- RIGHT: ADMIN RESOLVE CONTROL PANEL -->
        <div class="lg:col-span-5 w-full">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-sand-200/60 space-y-6 sticky top-6">
                <div class="border-b border-sand-200/50 pb-3 space-y-1">
                    <div class="flex items-center space-x-2 text-[#1677B8]">
                        <i class="ph ph-gavel text-xl"></i>
                        <h4 class="font-extrabold text-xs capitalize tracking-wider text-on-surface">Tindak Lanjut Admin QMS</h4>
                    </div>
                    <p class="text-[11px] text-on-surface-variant">Tentukan keputusan akhir operasional untuk siklus hidup SOP ini.</p>
                </div>

                @if($evaluation->status === 'completed')
                    <!-- COMPLETED STATE DETAIL -->
                    <div class="p-5 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-lg text-xs space-y-3 shadow-sm">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-12 h-12 rounded-full bg-emerald-100 border border-emerald-200 flex items-center justify-center text-emerald-600 mb-2">
                                <i class="ph ph-check-circle text-2xl font-bold"></i>
                            </div>
                            <h5 class="font-extrabold capitalize tracking-wider text-xs text-emerald-900">Tindak Lanjut Selesai Diputuskan</h5>
                            <p class="text-[10px] text-emerald-700 mt-0.5">Hasil evaluasi telah resmi ditetapkan ke dalam sistem.</p>
                        </div>
                        <div class="space-y-2 border-t border-emerald-200 pt-3 font-semibold text-on-surface text-[11px]">
                            <div class="flex justify-between items-center">
                                <span class="text-on-surface-variant">Hasil Keputusan:</span>
                                <span class="px-2.5 py-0.5 rounded font-extrabold text-[10px] bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    {{ $evaluation->result }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-on-surface-variant">Admin Peninjau:</span>
                                <strong class="text-on-surface capitalize">{{ $evaluation->admin->full_name ?? ($evaluation->admin->username ?? 'Admin QMS') }}</strong>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-on-surface-variant">Tanggal Penetapan:</span>
                                <strong class="text-on-surface">{{ $evaluation->admin_reviewed_at ? $evaluation->admin_reviewed_at->format('d M Y - H:i') . ' WIB' : '-' }}</strong>
                            </div>
                        </div>
                        @if($evaluation->admin_notes)
                        <div class="bg-white p-3 rounded-md border border-emerald-200 mt-2 text-on-surface-variant font-medium text-[11px]">
                            <span class="block text-[10px] font-bold capitalize text-emerald-900 mb-1 flex items-center gap-1">
                                <i class="ph ph-note"></i> Catatan Resmi Admin:
                            </span>
                            {{ $evaluation->admin_notes }}
                        </div>
                        @endif
                    </div>
                @else
                    <!-- RESOLVE FORM -->
                    <form action="{{ route('admin.evaluations.resolve', $evaluation->id) }}" method="POST" class="space-y-5">
                        @csrf
                        
                        <div class="p-3.5 bg-canvas rounded-md border border-sand-200/60 space-y-1.5 text-xs text-on-surface-variant font-semibold">
                            <div class="flex justify-between items-center">
                                <span>Nomor SOP:</span>
                                <strong class="text-on-surface font-mono">{{ $document->doc_number ?? '-' }}</strong>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Versi Aktif:</span>
                                <strong class="text-on-surface">Rev {{ $document->doc_revision ?? '0' }}</strong>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Departemen:</span>
                                <strong class="text-on-surface">{{ $document->department }}</strong>
                            </div>
                        </div>

                        <!-- DECISION RADIOS -->
                        <div class="space-y-2.5">
                            <label class="block text-[11px] font-extrabold capitalize text-on-surface tracking-wider">Pilih Keputusan Akhir</label>
                            
                            <div class="space-y-2 text-xs font-semibold text-on-surface">
                                <label class="p-3 rounded-md border border-sand-200 hover:border-emerald-500 hover:bg-emerald-50/30 transition-all flex items-start space-x-2.5 cursor-pointer bg-white">
                                    <input type="radio" name="result" value="CONTINUE" class="mt-0.5 text-[#1677B8] focus:ring-[#1677B8]" required>
                                    <div>
                                        <span class="block font-bold text-emerald-800">CONTINUE</span>
                                        <span class="block text-[10px] text-on-surface-variant font-normal mt-0.5">SOP tetap aktif & relevan. Jadwalkan evaluasi otomatis 1 tahun ke depan.</span>
                                    </div>
                                </label>

                                <label class="p-3 rounded-md border border-sand-200 hover:border-blue-500 hover:bg-blue-50/30 transition-all flex items-start space-x-2.5 cursor-pointer bg-white">
                                    <input type="radio" name="result" value="REVISION REQUIRED" class="mt-0.5 text-[#1677B8] focus:ring-[#1677B8]">
                                    <div>
                                        <span class="block font-bold text-blue-800">REVISION REQUIRED</span>
                                        <span class="block text-[10px] text-on-surface-variant font-normal mt-0.5">SOP perlu revisi. Dokumen masuk status <em>need_revision</em> agar pembuat dapat mengunggah revisi.</span>
                                    </div>
                                </label>

                                <label class="p-3 rounded-md border border-sand-200 hover:border-amber-500 hover:bg-amber-50/30 transition-all flex items-start space-x-2.5 cursor-pointer bg-white">
                                    <input type="radio" name="result" value="NOT USED" class="mt-0.5 text-[#1677B8] focus:ring-[#1677B8]">
                                    <div>
                                        <span class="block font-bold text-amber-800">NOT USED</span>
                                        <span class="block text-[10px] text-on-surface-variant font-normal mt-0.5">SOP dinyatakan tidak digunakan. Status dokumen diubah menjadi <em>obsolete</em>.</span>
                                    </div>
                                </label>

                                <label class="p-3 rounded-md border border-sand-200 hover:border-rose-500 hover:bg-rose-50/30 transition-all flex items-start space-x-2.5 cursor-pointer bg-white">
                                    <input type="radio" name="result" value="OBSOLETE" class="mt-0.5 text-[#1677B8] focus:ring-[#1677B8]">
                                    <div>
                                        <span class="block font-bold text-rose-800">OBSOLETE</span>
                                        <span class="block text-[10px] text-on-surface-variant font-normal mt-0.5">SOP usang atau ditiadakan. Dokumen diarsipkan dan dinonaktifkan.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- ADMIN NOTES -->
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-extrabold capitalize text-on-surface">Catatan Tindak Lanjut QMS:</label>
                            <textarea name="admin_notes" rows="3" class="w-full p-3 text-xs rounded-md border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] transition-all bg-white" placeholder="Tuliskan alasan penetapan atau instruksi revisi lanjutan..." required></textarea>
                        </div>

                        <!-- SUBMIT RESOLVE -->
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menetapkan keputusan evaluasi ini? Tindakan ini akan merubah status operasional dokumen.')"
                                class="w-full btn-interactive btn-interactive-blue font-extrabold capitalize tracking-wider py-3 cursor-pointer">
                            <span class="btn-interactive-default">
                                <span class="btn-interactive-dot"></span>
                                <span>Simpan Keputusan Evaluasi</span>
                            </span>
                            <span class="btn-interactive-hover">
                                <span>Simpan Keputusan Evaluasi</span>
                                <i class="ph ph-arrow-right text-sm"></i>
                            </span>
                            <span class="btn-interactive-bg"></span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
