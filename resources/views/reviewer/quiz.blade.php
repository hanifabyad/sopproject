@extends(request()->is('admin/*') || (Auth::check() && Auth::user()->role === 'admin') ? 'layouts.admin' : 'layouts.reviewer')

@section('title', 'Uji Pemahaman SOP (15 Soal Pilihan Ganda)')
@section('header_title', 'Uji Pemahaman SOP')

@section('content')
<div class="space-y-6">

    <!-- TOP HEADER -->
    <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-[2px] p-6 shadow-sm border border-white/10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                @if(Auth::check() && Auth::user()->role === 'admin')
                    <x-back-button href="{{ route('admin.tracking') }}" variant="light" text="Kembali" />
                @else
                    <x-back-button href="{{ route('reviewer.dashboard') }}" variant="light" text="Kembali" />
                @endif
                <span class="text-white/30">|</span>
                <div class="flex items-center space-x-2 text-xs text-white/80 font-semibold">
                    <span>Uji Pemahaman SOP</span>
                    <span>/</span>
                    <span class="font-bold text-[#ffe16e]">{{ $document->doc_number ?? 'SOP' }}</span>
                </div>
            </div>
            <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-white">{{ $document->title }}</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium flex items-center gap-2">
                <span>Departemen: <strong>{{ $document->department }}</strong></span>
                <span class="text-white/40">&bull;</span>
                <span>Standar Kelulusan: <strong class="text-[#ffe16e]">{{ $quiz->passing_score ?? 60 }} Poin (KKM)</strong></span>
            </p>
        </div>

        @if($latestAttempt)
            <div class="bg-white/10 backdrop-blur-sm border border-white/20 p-3.5 rounded-[2px] text-right flex-shrink-0">
                <span class="text-[10px] text-white/80 font-bold block uppercase tracking-wider">Hasil Ujian Terakhir</span>
                <div class="flex items-center gap-2 justify-end mt-1">
                    <span class="text-xl font-black {{ $latestAttempt->status === 'passed' ? 'text-emerald-300' : 'text-rose-300' }}">
                        {{ $latestAttempt->score }}/100
                    </span>
                    <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-[2px] uppercase tracking-wider {{ $latestAttempt->status === 'passed' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                        {{ $latestAttempt->status === 'passed' ? 'Lulus KKM (>=60)' : 'Belum Lulus (<60)' }}
                    </span>
                </div>
            </div>
        @endif
    </div>

    <!-- ALERTS -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-900 font-bold text-xs rounded-r-md flex items-center gap-2 shadow-sm">
            <i class="ph ph-check-circle text-lg text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-900 font-bold text-xs rounded-r-md flex items-center gap-2 shadow-sm">
            <i class="ph ph-warning-circle text-lg text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-900 font-bold text-xs rounded-r-md flex items-center gap-2 shadow-sm">
            <i class="ph ph-info text-lg text-blue-600"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if(!empty($isDirut))
        <!-- NOTICE: EXEMPTION FOR DIRUT -->
        <div class="p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-950 rounded-r-md shadow-sm space-y-1">
            <div class="flex items-center gap-2 font-black text-xs text-amber-900">
                <i class="ph ph-shield-check text-base text-amber-600"></i>
                <span>Pemberitahuan Khusus: Jabatan Direktur Utama (DIRUT)</span>
            </div>
            <p class="text-[11.5px] text-amber-800 leading-relaxed font-medium">
                Sesuai kebijakan tata kelola sistem mutu e-QMS PT PKM Group, jabatan <strong>Direktur Utama</strong> dibebaskan dari kewajiban pengerjaan kuis pemahaman SOP. Halaman ini ditampilkan dalam <strong>Mode Peninjauan Materi</strong> (baca naskah SOP dan review butir pertanyaan).
            </p>
        </div>
    @endif

    <!-- MAIN SPLIT WORKSPACE: PDF READER LEFT, QUIZ FORM RIGHT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT: SOP PDF VIEWER (7 COLS) -->
        <div class="lg:col-span-7 bg-white rounded-[2px] p-5 shadow-sm border border-sand-200/60 space-y-3">
            <div class="flex items-center justify-between border-b border-sand-200/40 pb-3">
                <div class="flex items-center gap-2">
                    <i class="ph ph-file-pdf text-xl text-[#1677B8]"></i>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Materi Naskah SOP</h3>
                </div>
                <a href="{{ route('reviewer.stream.file', $document->id) }}" target="_blank" class="text-xs font-bold text-[#1677B8] hover:underline flex items-center gap-1">
                    <span>Buka Tab Penuh</span>
                    <i class="ph ph-arrow-square-out"></i>
                </a>
            </div>

            <iframe src="{{ route('reviewer.stream.file', $document->id) }}#toolbar=1" class="w-full h-[750px] rounded-[2px] border border-slate-200 bg-slate-50" title="Pratinjau SOP"></iframe>
        </div>

        <!-- RIGHT: 15-QUESTION QUIZ FORM (5 COLS) -->
        <div class="lg:col-span-5 bg-white rounded-[2px] p-5 shadow-sm border border-sand-200/60 space-y-4">
            <div class="border-b border-sand-200/40 pb-3 flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-extrabold text-slate-900">15 Soal Pilihan Ganda</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Semua butir soal pilihan ganda (A, B, C, D). Standar KKM kelulusan: <strong>60 poin</strong>.</p>
                </div>
                @if(Auth::check() && Auth::user()->role === 'admin')
                    <form action="{{ route('documents.quiz.regenerate', $document->id) }}" method="POST" onsubmit="return confirm('Apakah Anda ingin menganalisis ulang naskah dokumen SOP dan menyusun 15 soal kuis baru?')" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="whitespace-nowrap px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-300 text-[#1677B8] hover:text-[#125d91] rounded-[2px] transition-all text-xs flex items-center gap-1.5 font-bold cursor-pointer shadow-xs" title="Analisis Ulang Naskah SOP & Buat 15 Soal Baru">
                            <i class="ph ph-arrows-clockwise text-sm"></i>
                            <span class="text-[11px] font-bold">Analisis Ulang</span>
                        </button>
                    </form>
                @endif
            </div>

            <form action="{{ route('documents.quiz.submit', $document->id) }}" method="POST" class="space-y-4">
                @csrf

                @php 
                    $attemptAnswers = $latestAttempt?->answers ?? []; 
                @endphp

                @foreach($questions as $index => $q)
                    @php
                        $userAnswer = $attemptAnswers[$q->id] ?? null;
                        $isAnswered = !empty($userAnswer);
                        $isCorrect = $isAnswered && (strtoupper(trim($userAnswer)) === strtoupper(trim($q->correct_answer)));
                    @endphp

                    <div class="p-4 rounded-[2px] border {{ $latestAttempt ? ($isCorrect ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50/50 border-rose-200') : 'bg-slate-50/70 border-slate-200' }} space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <span class="font-extrabold text-xs text-slate-900 leading-snug">
                                <span class="text-[#1677B8]">Soal {{ $index + 1 }} dari {{ $questions->count() }}.</span> {{ $q->question }}
                            </span>
                            @if($latestAttempt)
                                <span class="text-[9.5px] font-black px-2 py-0.5 rounded-[2px] {{ $isCorrect ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }} flex-shrink-0">
                                    {{ $isCorrect ? '✓ Benar' : '✗ Salah' }}
                                </span>
                            @else
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded-[2px] bg-slate-200/70 text-slate-700 flex-shrink-0">
                                    Pilihan Ganda
                                </span>
                            @endif
                        </div>

                        @if(is_array($q->options))
                            <div class="space-y-1.5 pt-1">
                                @foreach($q->options as $optKey => $optText)
                                    @php
                                        $isSelected = ($userAnswer === $optKey);
                                        $isKeyCorrect = ($optKey === $q->correct_answer);
                                    @endphp
                                    <label class="flex items-start gap-2.5 p-2.5 bg-white rounded-[2px] border {{ $latestAttempt && $isKeyCorrect ? 'border-emerald-500 bg-emerald-50/30' : ($isSelected && !$isCorrect ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200') }} hover:border-[#1677B8] hover:bg-blue-50/30 cursor-pointer transition-all text-xs font-semibold text-slate-800">
                                        <input type="radio" 
                                               name="answers[{{ $q->id }}]" 
                                               value="{{ $optKey }}" 
                                               {{ $isSelected ? 'checked' : '' }}
                                               {{ empty($isDirut) ? 'required' : 'disabled' }} 
                                               class="mt-0.5 text-[#1677B8] focus:ring-[#1677B8]">
                                        <span class="leading-relaxed">
                                            <strong class="text-[#1677B8]">{{ $optKey }}.</strong> {{ $optText }}
                                            @if($latestAttempt && $isKeyCorrect)
                                                <span class="ml-1 text-[10px] text-emerald-700 font-extrabold">(Kunci Benar)</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        @if($latestAttempt && !$isCorrect)
                            <div class="p-2 bg-amber-50 rounded-[2px] border border-amber-200 text-[11px] text-amber-900 font-bold">
                                <span>Kunci Jawaban yang Benar: <strong>{{ $q->correct_answer }}. {{ $q->options[$q->correct_answer] ?? '' }}</strong></span>
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="pt-3 border-t border-slate-200">
                    @if(empty($isDirut))
                        <button type="submit" class="w-full py-3 bg-[#1677B8] hover:bg-[#125d91] text-white font-extrabold text-xs rounded-[2px] shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <i class="ph ph-check-circle text-base"></i>
                            <span>Kirim Jawaban Kuis (15 Soal)</span>
                        </button>
                    @else
                        <div class="w-full py-3 bg-slate-100 border border-slate-200 text-slate-600 font-bold text-xs rounded-[2px] text-center flex items-center justify-center gap-2">
                            <i class="ph ph-shield-check text-base text-amber-600"></i>
                            <span>Mode Peninjauan Direksi &bull; Tidak Ada Kewajiban Mengerjakan Kuis</span>
                        </div>
                    @endif
                </div>
            </form>
        </div>

    </div>

</div>
@endsection

