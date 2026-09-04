<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Presensi Sosialisasi & Uji Pemahaman SOP - e-QMS PT PKM Group</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
            -webkit-tap-highlight-color: transparent;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between py-3 px-3 sm:py-6 sm:px-6 bg-[#f8fafc]">

    <div class="max-w-lg w-full mx-auto space-y-3 sm:space-y-4">
        
        <!-- HEADER RESMI PERUSAHAAN (SESUAI DESIGN GUIDELINES) -->
        <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white p-3.5 sm:p-5 rounded-[2px] shadow-sm border border-white/10 space-y-1 sm:space-y-1.5">
            <div class="flex items-center space-x-2 text-[10px] sm:text-[10.5px] font-extrabold tracking-wider uppercase text-[#ffe16e]">
                <i class="ph ph-shield-check text-sm"></i>
                <span>e-QMS PT PKM GROUP</span>
            </div>
            <div>
                <h1 class="text-sm sm:text-base font-black tracking-tight text-white leading-snug">
                    PT PUTRA KELANA MAKMUR (PKM GROUP)
                </h1>
                <p class="text-[10.5px] sm:text-xs text-white/90 font-medium mt-0.5">
                    Presensi Kehadiran & Uji Pemahaman Dokumen SOP
                </p>
            </div>
        </div>

        <!-- CARD INFO KEGIATAN & FORM CONTAINER -->
        <div class="bg-white rounded-[2px] p-3.5 sm:p-6 shadow-sm border border-slate-200 space-y-3.5 sm:space-y-4">
            
            <!-- BANNER AGENDA SOSIALISASI -->
            <div class="bg-[#f0f9ff] border border-blue-200 p-3 sm:p-3.5 rounded-[2px] space-y-1">
                <div class="flex items-center justify-between gap-1">
                    <span class="px-1.5 py-0.5 bg-[#1677B8] text-white font-extrabold text-[9px] uppercase tracking-wider rounded-[2px]">
                        Agenda Sosialisasi
                    </span>
                    @if($session->doc_number)
                        <span class="text-[10.5px] sm:text-[11px] font-mono font-bold text-[#1677B8] truncate max-w-[200px]">
                            {{ $session->doc_number }}
                        </span>
                    @endif
                </div>
                <h2 class="text-xs sm:text-sm font-black text-slate-900 leading-snug pt-0.5">
                    {{ $session->agenda }}
                </h2>
            </div>

            <!-- DETAIL JADWAL & PEMATERI -->
            <div class="grid grid-cols-2 gap-1.5 sm:gap-2 text-xs">
                <div class="bg-slate-50 p-2 sm:p-2.5 rounded-[2px] border border-slate-200">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Tanggal</span>
                    <span class="font-bold text-slate-800 text-[10.5px] sm:text-[11px] truncate block">{{ $session->session_date ? $session->session_date->translatedFormat('d F Y') : '-' }}</span>
                </div>
                <div class="bg-slate-50 p-2 sm:p-2.5 rounded-[2px] border border-slate-200">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Waktu Sesi</span>
                    <span class="font-bold text-slate-800 text-[10.5px] sm:text-[11px] truncate block">{{ $session->session_time ?? '09:00 WIB' }}</span>
                </div>
                <div class="bg-slate-50 p-2 sm:p-2.5 rounded-[2px] border border-slate-200 col-span-2">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Pemateri / Tim Sosialisasi</span>
                    <span class="font-bold text-slate-800 text-[10.5px] sm:text-[11px]">{{ $session->speaker ?? '-' }}</span>
                </div>
            </div>

            <!-- TAHAP 1: FORM PRESENSI KEHADIRAN -->
            <div id="formSection" class="pt-2 border-t border-slate-200 space-y-3.5">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[9.5px] font-extrabold text-[#1677B8] uppercase tracking-wider block">Tahap 1 dari 2</span>
                        <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">
                            Isi Data Kehadiran Anda
                        </h3>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[9.5px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-[2px] border border-emerald-200">
                        <i class="ph ph-user-check text-xs text-emerald-600"></i>
                        Presensi Mandiri
                    </span>
                </div>

                <form id="presensiForm" onsubmit="submitForm(event)" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] sm:text-xs font-bold text-slate-700 mb-1">
                            Nama Lengkap Peserta / Operator <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="inputName" required
                               value="{{ $currentUser?->full_name ?? ($currentUser?->username ?? '') }}"
                               placeholder="Contoh: Budi Santoso"
                               class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2 sm:p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] sm:text-xs font-bold text-slate-700 mb-1">
                            Jabatan / Bagian / Unit Kerja <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="department" id="inputDept" required
                               value="{{ $currentUser?->role ?? '' }}"
                               placeholder="Contoh: Operator Lapangan / Staff HC"
                               class="w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2 sm:p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none">
                    </div>

                    @if(!empty($quiz) && count($questions) > 0)
                        <div class="p-2.5 bg-[#f0f9ff] border border-blue-200 rounded-[2px] flex items-start gap-2 text-[10.5px] text-blue-950 font-medium">
                            <i class="ph ph-info text-sm text-[#1677B8] flex-shrink-0 mt-0.5"></i>
                            <span>Setelah konfirmasi kehadiran, Anda akan langsung menjawab <strong>15 Soal Kuis Pemahaman (KKM 60)</strong>.</span>
                        </div>
                    @endif

                    <div class="pt-1">
                        <button type="submit" id="btnSubmit" class="w-full py-2.5 sm:py-3 bg-[#1677B8] hover:bg-[#125d91] active:bg-[#0f4e7a] text-white font-bold text-xs rounded-[2px] shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer uppercase tracking-wider">
                            <i class="ph ph-check-circle text-base"></i>
                            <span>Konfirmasi Kehadiran & Lanjut Kuis</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAHAP 2: KUIS PEMAHAMAN SOP (15 SOAL PILIHAN GANDA, KKM 60) -->
            @if(!empty($quiz) && count($questions) > 0)
            <div id="quizSection" class="hidden pt-2 border-t border-slate-200 space-y-3">
                <!-- BANNER TAHAP 2: RINGKAS, COMPACT & PAS DI HP -->
                <div class="p-2.5 sm:p-3 bg-[#f0f9ff] border border-blue-200 rounded-[2px] space-y-1.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="px-1.5 py-0.5 bg-[#1677B8] text-white font-extrabold text-[9px] uppercase tracking-wider rounded-[2px] flex-shrink-0">
                                Tahap 2
                            </span>
                            <h4 class="text-[11px] sm:text-xs font-black text-slate-900 truncate" id="quizParticipantName">Peserta: -</h4>
                        </div>
                        <span class="text-[10px] sm:text-[10.5px] font-extrabold text-[#1677B8] whitespace-nowrap bg-white px-2 py-0.5 border border-blue-200 rounded-[2px]">
                            KKM: <strong>60 Poin</strong>
                        </span>
                    </div>
                    <p class="text-[10px] text-slate-600 font-medium leading-tight flex items-center gap-1">
                        <i class="ph ph-info text-[#1677B8] flex-shrink-0"></i>
                        <span>Jawab 15 soal di bawah. Seluruh peserta wajib mencapai nilai di atas KKM 60.</span>
                    </p>
                </div>

                <form id="quizForm" onsubmit="submitQuiz(event)" class="space-y-3">
                    @csrf
                    <input type="hidden" name="participant_id" id="quizParticipantId" value="">

                    @foreach($questions as $index => $q)
                        <div class="p-3 sm:p-4 rounded-[2px] border bg-white border-slate-200 space-y-2 shadow-2xs">
                            <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-1.5">
                                <span class="font-black text-[11px] sm:text-xs text-[#1677B8] leading-tight">
                                    Soal No. {{ $index + 1 }} dari {{ count($questions) }}
                                </span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Pilihan Ganda</span>
                            </div>
                            <p class="text-[11.5px] sm:text-xs font-semibold text-slate-900 leading-relaxed">
                                {{ $q->question }}
                            </p>

                            @if(is_array($q->options))
                                <div class="space-y-1.5 sm:space-y-2 pt-0.5">
                                    @foreach($q->options as $optKey => $optText)
                                        <label class="flex items-start gap-2.5 p-2 sm:p-2.5 bg-slate-50 rounded-[2px] border border-slate-200 hover:border-[#1677B8] hover:bg-[#f0f9ff] active:bg-blue-100/60 cursor-pointer transition-all text-[11px] sm:text-xs font-medium text-slate-800 select-none">
                                            <input type="radio" 
                                                   name="answers[{{ $q->id }}]" 
                                                   value="{{ $optKey }}" 
                                                   required 
                                                   class="mt-0.5 text-[#1677B8] focus:ring-[#1677B8] w-4 h-4 flex-shrink-0 cursor-pointer">
                                            <span class="flex-1 min-w-0 leading-relaxed">
                                                <strong class="text-[#1677B8] mr-1">{{ $optKey }}.</strong> {{ $optText }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <!-- STICKY BUTTON BAR KHUSUS HP DENGAN BACKDROP -->
                    <div class="sticky bottom-2 sm:bottom-3 z-20 pt-2">
                        <div class="bg-white/95 backdrop-blur-xs p-1.5 border border-slate-200 rounded-[2px] shadow-lg">
                            <button type="submit" id="btnQuizSubmit" class="w-full py-2.5 sm:py-3 bg-[#1677B8] hover:bg-[#125d91] active:bg-[#0f4e7a] text-white font-extrabold text-xs rounded-[2px] shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer uppercase tracking-wider">
                                <i class="ph ph-paper-plane-tilt text-base"></i>
                                <span>Kirim Jawaban (15 Soal)</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endif

            <!-- TAHAP 3: HASIL KUIS & EVALUASI BENAR/SALAH (HIDDEN BY DEFAULT) -->
            <div id="quizResultSection" class="hidden py-1 space-y-3 sm:space-y-4">
                <!-- SCORE CARD BANNER -->
                <div id="scoreBannerCard" class="p-3.5 sm:p-5 rounded-[2px] text-center space-y-2 border">
                    <div id="scoreIconContainer" class="w-10 h-10 sm:w-12 sm:h-12 rounded-[2px] flex items-center justify-center mx-auto shadow-xs"></div>
                    <h3 id="scoreStatusTitle" class="text-xs sm:text-sm font-black uppercase tracking-wider leading-snug"></h3>
                    <div class="flex items-center justify-center gap-2">
                        <span id="scoreDisplay" class="text-2xl sm:text-3xl font-black"></span>
                        <span id="scoreBadge" class="text-[9.5px] sm:text-[10px] font-black px-2 py-0.5 rounded-[2px] uppercase"></span>
                    </div>
                    <p id="scoreMsg" class="text-[11px] sm:text-xs font-medium leading-relaxed max-w-md mx-auto"></p>
                </div>

                <!-- TOMBOL RETAKE JIKA TIDAK LULUS -->
                <div id="retakeButtonContainer" class="hidden pt-1">
                    <button type="button" onclick="retakeQuiz()" class="w-full py-2.5 sm:py-3 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-black text-[11px] sm:text-xs rounded-[2px] shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer uppercase tracking-wider">
                        <i class="ph ph-arrow-counter-clockwise text-base"></i>
                        <span>Ulangi Kuis Sekarang (Wajib Lulus KKM 60)</span>
                    </button>
                </div>

                <!-- RINCIAN EVALUASI JAWABAN (BENAR / SALAH) -->
                <div class="space-y-2.5 pt-2">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-1.5">
                        <h4 class="text-[11px] sm:text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="ph ph-list-checks text-sm sm:text-base text-[#1677B8]"></i>
                            <span>Koreksi & Pembahasan Soal</span>
                        </h4>
                        <span class="text-[9.5px] text-slate-500 font-bold">15 Butir Soal</span>
                    </div>

                    <div id="evaluationsContainer" class="space-y-2 sm:space-y-2.5">
                        <!-- Diisi via JavaScript -->
                    </div>
                </div>

                <!-- TOMBOL SELESAI / RESET -->
                <div class="pt-3 border-t border-slate-200 text-center space-y-1.5">
                    <button type="button" onclick="resetForm()" class="text-[11px] sm:text-xs font-bold text-slate-500 hover:text-slate-800 underline cursor-pointer bg-transparent border-none">
                        Presensi untuk peserta / operator lain
                    </button>
                </div>
            </div>

            <!-- SUCCESS SECTION BIASA (HANYA JIKA TIDAK ADA KUIS) -->
            <div id="successSection" class="hidden text-center py-6 px-3 space-y-3">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-700 rounded-[2px] flex items-center justify-center mx-auto border border-emerald-300">
                    <i class="ph ph-check text-2xl font-bold"></i>
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-black text-slate-900 uppercase tracking-wider">Kehadiran Berhasil Dicatat!</h3>
                    <p id="successMsg" class="text-xs text-slate-600 font-medium mt-1"></p>
                </div>
                <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-[2px] text-xs text-emerald-800 font-bold inline-block">
                    Status: <span class="text-emerald-700 font-black">HADIR</span> &bull; <span id="successTime"></span> WIB
                </div>
                <div class="pt-2">
                    <button type="button" onclick="resetForm()" class="text-xs font-bold text-slate-500 hover:text-slate-700 underline cursor-pointer bg-transparent border-none">
                        Presensi untuk peserta lain
                    </button>
                </div>
            </div>

        </div>

        <!-- FOOTER / COPYRIGHT RESMI -->
        <p class="text-center text-[10px] text-slate-500 font-semibold">
            e-QMS &copy; {{ date('Y') }} PT PUTRA KELANA MAKMUR (PKM GROUP)
        </p>

    </div>

    <script>
        let currentParticipant = null;
        const hasQuiz = {{ (!empty($quiz) && count($questions) > 0) ? 'true' : 'false' }};

        async function submitForm(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            const name = document.getElementById('inputName').value.trim();
            const dept = document.getElementById('inputDept').value.trim();

            if (!name || !dept) {
                alert('Silakan lengkapi nama dan jabatan Anda.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner animate-spin text-base"></i> Menyimpan...';

            try {
                const res = await fetch('{{ route('socializations.presensi.store', $session->token) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: name, department: dept })
                });

                const data = await res.json();

                if (data.success) {
                    currentParticipant = data.participant;

                    if (hasQuiz) {
                        // Beralih ke Tahap 2: Kuis Pemahaman SOP
                        document.getElementById('formSection').classList.add('hidden');
                        document.getElementById('quizSection').classList.remove('hidden');
                        document.getElementById('quizParticipantId').value = currentParticipant.id;
                        document.getElementById('quizParticipantName').textContent = `Peserta: ${name}`;
                        window.scrollTo({ top: 80, behavior: 'smooth' });
                    } else {
                        // Jika tidak ada kuis, tampilkan success section langsung
                        document.getElementById('formSection').classList.add('hidden');
                        document.getElementById('successSection').classList.remove('hidden');
                        document.getElementById('successMsg').textContent = `Terima kasih, ${name}. Presensi Anda telah tersimpan secara resmi.`;
                        const now = new Date();
                        document.getElementById('successTime').textContent = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
                    }
                } else {
                    alert(data.message || 'Gagal mencatat presensi. Silakan coba lagi.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ph ph-check-circle text-base"></i> <span>Konfirmasi Kehadiran & Lanjut Kuis</span>';
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan. Silakan periksa koneksi Anda dan coba lagi.');
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-check-circle text-base"></i> <span>Konfirmasi Kehadiran & Lanjut Kuis</span>';
            }
        }

        async function submitQuiz(e) {
            e.preventDefault();
            const btn = document.getElementById('btnQuizSubmit');
            const form = document.getElementById('quizForm');
            const formData = new FormData(form);

            const answers = {};
            for (let [key, val] of formData.entries()) {
                const match = key.match(/^answers\[(\d+)\]$/);
                if (match) {
                    answers[match[1]] = val;
                }
            }

            const participantId = document.getElementById('quizParticipantId').value;
            if (!participantId) {
                alert('Data peserta tidak ditemukan. Silakan isi presensi terlebih dahulu.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner animate-spin text-base"></i> Memeriksa Jawaban...';

            try {
                const res = await fetch('{{ route('socializations.presensi.quiz_submit', $session->token) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        participant_id: participantId,
                        answers: answers
                    })
                });

                const data = await res.json();

                if (data.success) {
                    renderQuizResults(data);
                } else {
                    alert(data.message || 'Gagal memproses kuis.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ph ph-paper-plane-tilt text-base"></i> <span>Kirim Jawaban (15 Soal)</span>';
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan saat mengirim kuis.');
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-paper-plane-tilt text-base"></i> <span>Kirim Jawaban (15 Soal)</span>';
            }
        }

        function renderQuizResults(data) {
            document.getElementById('quizSection').classList.add('hidden');
            document.getElementById('quizResultSection').classList.remove('hidden');
            window.scrollTo({ top: 80, behavior: 'smooth' });

            const card = document.getElementById('scoreBannerCard');
            const icon = document.getElementById('scoreIconContainer');
            const title = document.getElementById('scoreStatusTitle');
            const scoreDisplay = document.getElementById('scoreDisplay');
            const scoreBadge = document.getElementById('scoreBadge');
            const scoreMsg = document.getElementById('scoreMsg');
            const retakeContainer = document.getElementById('retakeButtonContainer');

            scoreDisplay.textContent = `${data.score}/100`;

            if (data.passed) {
                card.className = 'p-3.5 sm:p-5 rounded-[2px] text-center space-y-2 border bg-emerald-50 border-emerald-300 text-emerald-950';
                icon.className = 'w-10 h-10 sm:w-12 sm:h-12 rounded-[2px] flex items-center justify-center mx-auto shadow-xs bg-emerald-600 text-white';
                icon.innerHTML = '<i class="ph ph-check text-xl sm:text-2xl font-bold"></i>';
                title.textContent = 'SELAMAT, ANDA LULUS UJI PEMAHAMAN!';
                title.className = 'text-xs sm:text-sm font-black uppercase tracking-wider text-emerald-900';
                scoreBadge.className = 'text-[9.5px] sm:text-[10px] font-black px-2 py-0.5 rounded-[2px] uppercase bg-emerald-600 text-white';
                scoreBadge.textContent = `Lulus KKM (${data.correct_count}/${data.total_count} Benar) • HADIR`;
                scoreMsg.innerHTML = `Nilai Anda <strong>${data.score}/100</strong> telah melampaui standar KKM ${data.passing_score}.<br><span class="text-emerald-800 font-extrabold">✓ Nama Anda telah resmi dimasukkan ke dalam Lembar Daftar Hadir Sosialisasi SOP.</span>`;
                retakeContainer.classList.add('hidden');
            } else {
                card.className = 'p-3.5 sm:p-5 rounded-[2px] text-center space-y-2 border bg-rose-50 border-rose-300 text-rose-950';
                icon.className = 'w-10 h-10 sm:w-12 sm:h-12 rounded-[2px] flex items-center justify-center mx-auto shadow-xs bg-rose-600 text-white';
                icon.innerHTML = '<i class="ph ph-warning text-xl sm:text-2xl font-bold"></i>';
                title.textContent = 'BELUM MENCAPAI KKM (NAMA BELUM MASUK DAFTAR HADIR)';
                title.className = 'text-xs sm:text-sm font-black uppercase tracking-wider text-rose-900';
                scoreBadge.className = 'text-[9.5px] sm:text-[10px] font-black px-2 py-0.5 rounded-[2px] uppercase bg-rose-600 text-white';
                scoreBadge.textContent = `Belum Lulus (${data.correct_count}/${data.total_count} Benar) • BELUM SAH`;
                scoreMsg.innerHTML = `Nilai Anda <strong>${data.score}/100</strong> masih di bawah KKM ${data.passing_score}.<br><span class="text-rose-700 font-extrabold">⚠️ Nama Anda belum dimasukkan ke daftar hadir.</span> Sesuai ketentuan, Anda wajib mengulang kuis dan lulus minimal 60 agar nama Anda sah tercatat.`;
                retakeContainer.classList.remove('hidden');
            }

            // Render detail evaluasi per soal
            const container = document.getElementById('evaluationsContainer');
            container.innerHTML = '';

            data.evaluations.forEach((item, idx) => {
                const itemDiv = document.createElement('div');
                itemDiv.className = `p-2.5 sm:p-3 rounded-[2px] border text-[11px] sm:text-xs space-y-1.5 ${item.is_correct ? 'bg-emerald-50/50 border-emerald-300' : 'bg-rose-50/50 border-rose-300'}`;

                itemDiv.innerHTML = `
                    <div class="flex items-start justify-between gap-2 border-b border-slate-200/60 pb-1">
                        <span class="font-extrabold text-slate-900 leading-snug">
                            <span class="${item.is_correct ? 'text-emerald-700' : 'text-rose-700'}">No. ${idx + 1}:</span> ${item.question}
                        </span>
                        <span class="px-1.5 py-0.5 rounded-[2px] text-[9px] font-black uppercase tracking-wider flex-shrink-0 ${item.is_correct ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200'}">
                            ${item.is_correct ? '✓ Benar' : '✗ Salah'}
                        </span>
                    </div>
                    <div class="space-y-1 pt-0.5 font-medium">
                        <div class="flex items-start gap-1 text-[10.5px] sm:text-[11px]">
                            <span class="text-slate-500 font-bold min-w-[85px] sm:min-w-[95px]">Jawaban Anda:</span>
                            <span class="${item.is_correct ? 'text-emerald-800 font-bold' : 'text-rose-800 font-bold'}">
                                ${item.user_answer ? item.user_answer + '. ' + item.user_answer_text : 'Tidak dijawab'}
                            </span>
                        </div>
                        ${!item.is_correct ? `
                        <div class="flex items-start gap-1 text-[10.5px] sm:text-[11px] p-1.5 sm:p-2 bg-amber-50 rounded-[2px] border border-amber-200">
                            <span class="text-amber-900 font-bold min-w-[85px] sm:min-w-[95px]">Kunci Benar:</span>
                            <span class="text-amber-950 font-black">
                                ${item.correct_answer}. ${item.correct_answer_text}
                            </span>
                        </div>
                        ` : ''}
                    </div>
                `;

                container.appendChild(itemDiv);
            });
        }

        function retakeQuiz() {
            // Reset pilihan radio di form kuis
            const form = document.getElementById('quizForm');
            const radios = form.querySelectorAll('input[type="radio"]');
            radios.forEach(r => r.checked = false);

            document.getElementById('quizResultSection').classList.add('hidden');
            document.getElementById('quizSection').classList.remove('hidden');
            const btn = document.getElementById('btnQuizSubmit');
            btn.disabled = false;
            btn.innerHTML = '<i class="ph ph-paper-plane-tilt text-base"></i> <span>Kirim Jawaban (15 Soal)</span>';
            window.scrollTo({ top: 80, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('inputName').value = '';
            document.getElementById('inputDept').value = '';
            document.getElementById('successSection').classList.add('hidden');
            document.getElementById('quizSection').classList.add('hidden');
            document.getElementById('quizResultSection').classList.add('hidden');
            document.getElementById('formSection').classList.remove('hidden');

            const btn = document.getElementById('btnSubmit');
            btn.disabled = false;
            btn.innerHTML = '<i class="ph ph-check-circle text-base"></i> <span>Konfirmasi Kehadiran & Lanjut Kuis</span>';
            currentParticipant = null;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>
