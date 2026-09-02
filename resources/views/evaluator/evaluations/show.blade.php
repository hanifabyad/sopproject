@extends('layouts.reviewer')

@section('title', 'Evaluasi SOP - ' . ($document->title ?? 'SOP'))
@section('header_title', 'Evaluasi Berkala SOP')

@section('content')
<div class="space-y-6">

    <!-- TOP GRADIENT HEADER BAR -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 shadow-sm border border-white/10 flex flex-col gap-3">
        <!-- Baris Tombol Kembali & Breadcrumb di Kiri -->
        <div class="flex items-center gap-3">
            <x-back-button href="{{ route('evaluations.index') }}" variant="light" />
            <span class="text-white/30">|</span>
            <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold">
                <a href="{{ route('evaluations.index') }}" class="hover:text-[#ffe16e] font-bold">Daftar Evaluasi</a>
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
                Periode Evaluasi: <strong>{{ $evaluation->evaluation_period }}</strong> &bull; Versi SOP: <strong>Rev {{ $document->doc_revision ?? '0' }}</strong> &bull; Batas Waktu: <strong>{{ $evaluation->due_date ? $evaluation->due_date->format('d M Y') : '-' }}</strong>
            </p>
        </div>
    </div>

    <!-- MAIN WORKSPACE SPLIT-SCREEN -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT: TOOLBAR + PDF VIEWER IFRAME -->
        <div class="lg:col-span-6 bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4 w-full">
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

            <!-- PDF VIEWPORT -->
            <div class="h-[680px] bg-slate-900 rounded-md overflow-hidden border border-sand-200 relative shadow-inner">
                <iframe src="{{ route('evaluations.stream', $evaluation->id) }}#toolbar=0&navpanes=0&view=FitH" 
                        class="w-full h-full border-none" 
                        id="pdfViewerFrame">
                </iframe>
            </div>
        </div>

        <!-- RIGHT: EVALUATION FORM PANEL -->
        <div class="lg:col-span-6 w-full space-y-6">
            <form action="{{ route('evaluations.submit', $evaluation->id) }}" method="POST" class="bg-white p-6 rounded-lg shadow-sm border border-sand-200/60 space-y-5">
                @csrf
                
                <div class="border-b border-sand-200/50 pb-3 space-y-1">
                    <div class="flex items-center space-x-2 text-[#1677B8]">
                        <i class="ph ph-note-pencil text-xl"></i>
                        <h4 class="font-extrabold text-xs capitalize tracking-wider text-on-surface">Formulir Evaluasi SOP Tahunan</h4>
                    </div>
                    <p class="text-[11px] text-on-surface-variant">Lengkapi penilaian efektivitas dan kesesuaian SOP terhadap kegiatan operasional.</p>
                </div>

                @if($errors->any())
                <div class="p-3.5 bg-rose-50 text-rose-800 border border-rose-200 text-xs rounded-md">
                    <p class="font-bold mb-1 flex items-center gap-1.5">
                        <i class="ph ph-warning-circle text-base"></i>
                        <span>Terjadi kesalahan validasi:</span>
                    </p>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- A. STATUS PENGGUNAAN -->
                <div class="p-4 bg-canvas rounded-md border border-sand-200/60 space-y-2">
                    <label class="block text-[11px] font-extrabold capitalize text-on-surface tracking-wider">A. Status Penggunaan Dokumen</label>
                    <p class="text-[11px] text-on-surface-variant">Apakah SOP masih digunakan dalam kegiatan operasional unit Anda?</p>
                    <div class="space-y-2 text-xs font-semibold pt-1">
                        @foreach(['Digunakan secara rutin', 'Digunakan tetapi terdapat kendala', 'Jarangan digunakan', 'Tidak digunakan'] as $opt)
                        <label class="flex items-center space-x-2.5 cursor-pointer">
                            <input type="radio" name="usage_status" value="{{ $opt }}" class="text-[#1677B8] focus:ring-[#1677B8]" required onchange="toggleUsageReason(this.value)">
                            <span class="text-on-surface">{{ $opt }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- ALASAN TIDAK DIGUNAKAN -->
                <div id="usage-reason-container" class="hidden space-y-2 p-3.5 bg-amber-50/50 border border-amber-200 rounded-md">
                    <label class="block text-[11px] font-extrabold capitalize text-amber-900">Pilih Alasan Utama Tidak Digunakan:</label>
                    <select name="usage_reason" class="w-full text-xs p-2.5 rounded-md border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white font-medium">
                        <option value="">-- Pilih Alasan --</option>
                        <option value="Proses kerja sudah berubah">Proses kerja sudah berubah</option>
                        <option value="SOP tidak sesuai kondisi aktual">SOP tidak sesuai kondisi aktual</option>
                        <option value="SOP sudah digantikan prosedur lain">SOP sudah digantikan prosedur lain</option>
                        <option value="SOP tidak lagi diperlukan">SOP tidak lagi diperlukan</option>
                        <option value="SOP sulit diterapkan">SOP sulit diterapkan</option>
                        <option value="User tidak mengetahui / memahami SOP">User tidak mengetahui / memahami SOP</option>
                        <option value="Lainnya">Lainnya (Tuliskan pada saran rekomendasi)</option>
                    </select>
                </div>

                <!-- B. KESESUAIAN SOP -->
                <div class="p-4 bg-canvas rounded-md border border-sand-200/60 space-y-2">
                    <label class="block text-[11px] font-extrabold capitalize text-on-surface tracking-wider">B. Kesesuaian Proses Kerja</label>
                    <p class="text-[11px] text-on-surface-variant">Apakah isi SOP masih sesuai dengan proses kerja aktual di lapangan?</p>
                    <div class="space-y-2 text-xs font-semibold pt-1">
                        @foreach(['Sangat sesuai', 'Sesuai', 'Sebagian perlu diperbarui', 'Tidak sesuai'] as $opt)
                        <label class="flex items-center space-x-2.5 cursor-pointer">
                            <input type="radio" name="conformity_status" value="{{ $opt }}" class="text-[#1677B8] focus:ring-[#1677B8]" required onchange="toggleConformityNotes(this.value)">
                            <span class="text-on-surface">{{ $opt }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div id="conformity-notes-container" class="hidden space-y-1.5 p-3.5 bg-blue-50/50 border border-blue-200 rounded-md">
                    <label class="block text-[11px] font-extrabold capitalize text-blue-900">Jelaskan bagian SOP yang perlu diperbarui:</label>
                    <textarea name="conformity_notes" rows="2" class="w-full p-2.5 text-xs rounded-md border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white" placeholder="Tuliskan bagian, bab, atau formulir lampiran yang tidak sesuai..."></textarea>
                </div>

                <!-- C. PERUBAHAN PROSES -->
                <div class="p-4 bg-canvas rounded-md border border-sand-200/60 space-y-2">
                    <label class="block text-[11px] font-extrabold capitalize text-on-surface tracking-wider">C. Perubahan Proses Kerja</label>
                    <p class="text-[11px] text-on-surface-variant">Apakah terdapat perubahan alur / tools sejak SOP diterbitkan?</p>
                    <div class="space-y-2 text-xs font-semibold pt-1">
                        <label class="flex items-center space-x-2.5 cursor-pointer">
                            <input type="radio" name="process_change_status" value="Tidak ada" class="text-[#1677B8] focus:ring-[#1677B8]" required onchange="toggleProcessNotes(this.value)">
                            <span class="text-on-surface">Tidak ada perubahan alur</span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer">
                            <input type="radio" name="process_change_status" value="Ada" class="text-[#1677B8] focus:ring-[#1677B8]" required onchange="toggleProcessNotes(this.value)">
                            <span class="text-on-surface">Ada perubahan proses kerja</span>
                        </label>
                    </div>
                </div>

                <div id="process-notes-container" class="hidden space-y-1.5 p-3.5 bg-blue-50/50 border border-blue-200 rounded-md">
                    <label class="block text-[11px] font-extrabold capitalize text-blue-900">Jelaskan perubahan alur yang terjadi:</label>
                    <textarea name="process_change_notes" rows="2" class="w-full p-2.5 text-xs rounded-md border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white" placeholder="Uraikan alur kerja baru yang berlaku..."></textarea>
                </div>

                <!-- D. EFEKTIVITAS -->
                <div class="p-4 bg-canvas rounded-md border border-sand-200/60 space-y-2">
                    <label class="block text-[11px] font-extrabold capitalize text-on-surface tracking-wider">D. Tingkat Efektivitas SOP</label>
                    <p class="text-[11px] text-on-surface-variant">Apakah SOP efektif dalam mencapai target mutu & kelancaran kerja?</p>
                    <div class="grid grid-cols-2 gap-2 text-xs font-semibold pt-1">
                        @foreach(['Sangat efektif', 'Efektif', 'Cukup efektif', 'Kurang efektif', 'Tidak efektif'] as $opt)
                        <label class="flex items-center space-x-2.5 cursor-pointer">
                            <input type="radio" name="effectiveness_status" value="{{ $opt }}" class="text-[#1677B8] focus:ring-[#1677B8]" required>
                            <span class="text-on-surface">{{ $opt }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- E. KENDALA IMPLEMENTASI -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-extrabold capitalize text-on-surface">E. Kendala Penerapan di Lapangan (Opsional):</label>
                    <textarea name="implementation_issues" rows="2" class="w-full p-2.5 text-xs rounded-md border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white" placeholder="Tuliskan kendala teknis atau pemahaman personel..."></textarea>
                </div>

                <!-- F. REKOMENDASI -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-extrabold capitalize text-on-surface">F. Rekomendasi / Saran Perbaikan (Opsional):</label>
                    <textarea name="recommendation" rows="2" class="w-full p-2.5 text-xs rounded-md border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] bg-white" placeholder="Berikan saran perbaikan atau pembaruan..."></textarea>
                </div>

                <!-- HASIL AKHIR KESIMPULAN -->
                <div class="p-4 bg-amber-50/70 rounded-md border border-amber-200 space-y-2">
                    <label class="block text-[11px] font-extrabold capitalize text-amber-900 tracking-wider">Hasil Rekomendasi Akhir Evaluator</label>
                    <select name="result" class="w-full text-xs p-2.5 rounded-md border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1677B8] font-bold text-on-surface bg-white" required>
                        <option value="">-- Tentukan Rekomendasi Hasil --</option>
                        <option value="CONTINUE">CONTINUE (SOP Masih Relevan & Tetap Berlaku)</option>
                        <option value="REVISION REQUIRED">REVISION REQUIRED (SOP Butuh Direvisi)</option>
                        <option value="NOT USED">NOT USED (SOP Sudah Tidak Digunakan)</option>
                        <option value="OBSOLETE">OBSOLETE (SOP Usang / Ditiadakan)</option>
                    </select>
                    <p class="text-[10px] text-amber-800 font-medium">
                        *Hasil form ini akan dikirimkan ke Admin QMS untuk diverifikasi dan disahkan secara resmi.
                    </p>
                </div>

                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin mengirim hasil evaluasi SOP ini? Data yang terkirim tidak dapat diubah.')"
                        class="w-full btn-interactive btn-interactive-blue font-extrabold capitalize tracking-wider py-3 cursor-pointer">
                    <span class="btn-interactive-default">
                        <span class="btn-interactive-dot"></span>
                        <span>Kirim Hasil Evaluasi SOP</span>
                    </span>
                    <span class="btn-interactive-hover">
                        <span>Kirim Hasil Evaluasi SOP</span>
                        <i class="ph ph-paper-plane-tilt text-sm"></i>
                    </span>
                    <span class="btn-interactive-bg"></span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleUsageReason(val) {
        const container = document.getElementById('usage-reason-container');
        if (val === 'Tidak digunakan') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    function toggleConformityNotes(val) {
        const container = document.getElementById('conformity-notes-container');
        if (val === 'Sebagian perlu diperbarui' || val === 'Tidak sesuai') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    function toggleProcessNotes(val) {
        const container = document.getElementById('process-notes-container');
        if (val === 'Ada') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }
</script>
@endsection
