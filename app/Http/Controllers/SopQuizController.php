<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\SopQuiz;
use App\Models\SopQuizAttempt;
use App\Models\SopQuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Smalot\PdfParser\Parser;

class SopQuizController extends Controller
{
    /**
     * Tampilkan Halaman Baca SOP & Kuis 15 Soal Pilihan Ganda (Tanpa Essay)
     */
    public function showQuiz($documentId)
    {
        $document = Document::findOrFail($documentId);
        $user = Auth::user();
        $isDirut = $user && (str_contains(strtolower($user->role), 'dirut') || str_contains(strtolower($user->role), 'direktur'));

        // Cari atau buatkan template kuis standar jika belum ada
        $quiz = SopQuiz::firstOrCreate(
            ['document_id' => $document->id],
            [
                'title' => 'Uji Pemahaman: ' . $document->title,
                'passing_score' => 60, // KKM 60 sesuai arahan mentor
                'is_active' => true,
            ]
        );

        // Pastikan KKM selalu 60
        if ($quiz->passing_score != 60) {
            $quiz->update(['passing_score' => 60]);
        }

        // Jika soal belum ada, atau format lama (< 15 soal / ada essay), regenerasi ke 15 soal PG
        if ($quiz->questions()->count() !== 15 || $quiz->questions()->where('type', 'essay')->exists()) {
            $quiz->questions()->delete();
            $this->analyzeAndGenerateQuestions($quiz, $document);
        }

        $questions = $quiz->questions()->orderBy('sequence')->get();
        $latestAttempt = $user ? SopQuizAttempt::where('sop_quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first() : null;

        return view('reviewer.quiz', compact('document', 'quiz', 'questions', 'latestAttempt', 'isDirut'));
    }

    /**
     * Regenerasi Soal Berdasarkan Analisis Ulang Dokumen SOP (15 Soal Pilihan Ganda)
     */
    public function regenerateQuiz($documentId)
    {
        $document = Document::findOrFail($documentId);
        $quiz = SopQuiz::firstOrCreate(
            ['document_id' => $document->id],
            [
                'title' => 'Uji Pemahaman: ' . $document->title,
                'passing_score' => 60,
                'is_active' => true,
            ]
        );

        if ($quiz->passing_score != 60) {
            $quiz->update(['passing_score' => 60]);
        }

        // Hapus soal lama dan buat baru 15 soal PG dari analisis naskah SOP
        $quiz->questions()->delete();
        $this->analyzeAndGenerateQuestions($quiz, $document);

        return redirect()->route('documents.quiz.show', $document->id)->with('success', 'Soal kuis berhasil disusun ulang menjadi 15 soal pilihan ganda berdasarkan naskah dokumen SOP.');
    }

    /**
     * Submit Jawaban Kuis & Evaluasi Nilai KKM 60 (15 Pilihan Ganda)
     */
    public function submitQuiz(Request $request, $documentId)
    {
        $document = Document::findOrFail($documentId);
        $quiz = SopQuiz::where('document_id', $document->id)->firstOrFail();
        $user = Auth::user();

        // Pengecualian: DIRUT dibebaskan dari kewajiban menjawab kuis
        $isDirut = $user && (str_contains(strtolower($user->role), 'dirut') || str_contains(strtolower($user->role), 'direktur'));
        if ($isDirut) {
            return redirect()->route('documents.quiz.show', $document->id)
                ->with('info', 'Jabatan Direktur Utama dibebaskan dari kewajiban mengerjakan kuis pemahaman SOP.');
        }

        $questions = $quiz->questions()->get();
        $submittedAnswers = $request->input('answers', []);

        $totalQuestions = $questions->count();
        $correctCount = 0;

        foreach ($questions as $q) {
            $userAns = $submittedAnswers[$q->id] ?? null;

            if ($userAns && strtoupper(trim($userAns)) === strtoupper(trim($q->correct_answer))) {
                $correctCount++;
            }
        }

        // Skala nilai 0-100 dari 15 soal
        $totalScore = $totalQuestions > 0 ? (int) round(($correctCount / $totalQuestions) * 100) : 0;

        $passed = $totalScore >= ($quiz->passing_score ?: 60); // KKM 60
        $status = $passed ? 'passed' : 'failed';

        $attempt = SopQuizAttempt::create([
            'sop_quiz_id'  => $quiz->id,
            'document_id'  => $document->id,
            'user_id'      => $user->id,
            'score'        => $totalScore,
            'status'       => $status,
            'answers'      => $submittedAnswers,
            'attempt_date' => now(),
            'feedback'     => $passed 
                ? "Selamat! Anda telah lulus uji pemahaman SOP ini dengan skor {$totalScore}/100 ({$correctCount} dari {$totalQuestions} soal benar, KKM: {$quiz->passing_score})."
                : "Nilai Anda adalah {$totalScore}/100 ({$correctCount} dari {$totalQuestions} soal benar, di bawah KKM {$quiz->passing_score}). Sesuai aturan sosialisasi SOP, Anda wajib mengulang kuis sampai mencapai KKM.",
        ]);

        // Catat di timeline
        $document->logs()->create([
            'user_id' => $user->id,
            'action'  => 'sop_quiz_attempt',
            'notes'   => 'User (' . ($user->full_name ?? $user->username) . ') menyelesaikan kuis pemahaman SOP dengan skor ' . $totalScore . '/100 (' . strtoupper($status) . ').',
        ]);

        if ($passed) {
            return redirect()->route('documents.quiz.show', $document->id)->with('success', "Selamat! Anda LULUS dengan nilai {$totalScore}/100 ({$correctCount} dari {$totalQuestions} soal benar - KKM 60).");
        } else {
            return redirect()->route('documents.quiz.show', $document->id)->with('error', "Nilai Anda {$totalScore}/100 ({$correctCount} dari {$totalQuestions} soal benar - Kurang dari KKM 60). Seluruh peserta wajib lulus di atas KKM 60, silakan ulangi kuis.");
        }
    }

    /**
     * Analisis Naskah Dokumen SOP & Buat 15 Soal Pilihan Ganda (Tanpa Essay)
     */
    public function analyzeAndGenerateQuestions(SopQuiz $quiz, Document $doc)
    {
        $extractedText = $this->extractTextFromSopPdf($doc);
        $analysis = $this->parseSopClauses($extractedText, $doc);

        $docTitle = $doc->title;
        $dept = $doc->department ?: ($doc->business_unit ?: 'Unit Terkait');

        // Susun 15 Soal Pilihan Ganda Relevan dengan opsi jawaban berbobot dan panjang seimbang
        $questions = [
            // Soal 1: Maksud & Tujuan SOP
            [
                'type' => 'multiple_choice',
                'question' => "Berdasarkan naskah SOP [{$docTitle}], apa tujuan utama dari ditetapkannya prosedur ini?",
                'options' => [
                    'A' => $analysis['purpose'] ?: "Menetapkan standar operasional dan alur kerja terstruktur untuk {$docTitle} demi kepatuhan mutu.",
                    'B' => "Menyerahkan kebijakan operasional kepada masing-masing personil lapangan tanpa adanya panduan resmi.",
                    'C' => "Hanya sebagai pelengkap berkas pengarsipan administrasi kantor tanpa perlu diterapkan dalam rutinitas kerja.",
                    'D' => "Menghapus wewenang pengawasan atasan langsung dan manajemen mutu terhadap pelaksanaan tugas operasional."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 1,
            ],

            // Soal 2: Ruang Lingkup & Penerapan
            [
                'type' => 'multiple_choice',
                'question' => "Ruang lingkup dan batasan penerapan dari prosedur [{$docTitle}] ini mencakup:",
                'options' => [
                    'A' => $analysis['scope'] ?: "Seluruh rangkaian aktivitas kerja, personil, dan unit yang terlibat dalam {$docTitle} di lingkungan {$dept}.",
                    'B' => "Hanya terbatas pada staf magang dan tenaga harian lepas yang bertugas di luar area operasional utama.",
                    'C' => "Khusus untuk pihak ketiga dan vendor eksternal yang tidak memiliki ikatan kontrak kerja sama resmi.",
                    'D' => "Diterapkan secara situasional hanya saat adanya audit penjamin mutu atau inspeksi mendadak dari auditor."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 2,
            ],

            // Soal 3: Penanggung Jawab / PIC Pelaksana
            [
                'type' => 'multiple_choice',
                'question' => "Siapakah pihak atau jabatan yang bertanggung jawab memastikan kepatuhan pelaksanaan SOP [{$docTitle}] ini?",
                'options' => [
                    'A' => $analysis['pic'] ?: "Kepala Departemen, Supervisor, serta Personil Terkait yang berwenang di lingkungan {$dept}.",
                    'B' => "Pihak luar atau vendor eksternal yang tidak memiliki keterkaitan langsung dengan proses bisnis perusahaan.",
                    'C' => "Petugas keamanan pos jaga gerbang pabrik bersama koordinator kebersihan lingkungan kantor cabang.",
                    'D' => "Seluruh tanggung jawab diserahkan mandiri kepada staf baru tanpa perlu pengawasan atasan kerja."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 3,
            ],

            // Soal 4: Tahapan Awal / Persiapan Prosedur
            [
                'type' => 'multiple_choice',
                'question' => "Sebelum memulai pekerjaan sesuai SOP [{$docTitle}], langkah persiapan awal yang wajib dilakukan adalah:",
                'options' => [
                    'A' => $analysis['prep_step'] ?: "Memeriksa kelengkapan dokumen pendukung, kesiapan sarana kerja/K3, dan otorisasi atasan.",
                    'B' => "Langsung mengeksekusi tahapan kerja teknis di lapangan tanpa memeriksa kesiapan sarana keselamatan kerja.",
                    'C' => "Menghilangkan formulir checklist operasional dengan tujuan agar proses kerja terselesaikan lebih cepat.",
                    'D' => "Menyerahkan seluruh instruksi pekerjaan kepada rekan kerja lain secara lisan tanpa konfirmasi tertulis."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 4,
            ],

            // Soal 5: Otorisasi & Persetujuan Berjenjang
            [
                'type' => 'multiple_choice',
                'question' => "Ketentuan otorisasi yang sah sebelum suatu tindakan atau pengeluaran pada SOP [{$docTitle}] dijalankan adalah:",
                'options' => [
                    'A' => "Wajib mendapatkan verifikasi dan persetujuan dari atasan berwenang sesuai pendelegasian wewenang.",
                    'B' => "Cukup menggunakan kesepakatan lisan tanpa memerlukan pencatatan dokumen bukti pada sistem e-QMS.",
                    'C' => "Dapat diputuskan dan disetujui sendiri oleh pelaksana lapangan tanpa perlu konfirmasi kepada atasan.",
                    'D' => "Tidak memerlukan otorisasi persetujuan apapun asalkan target pekerjaan dapat diselesaikan tepat waktu."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 5,
            ],

            // Soal 6: Prosedur Teknis Inti
            [
                'type' => 'multiple_choice',
                'question' => "Dalam pelaksanaan alur kerja teknis SOP [{$docTitle}], bagaimana tindakan yang sesuai standar mutu e-QMS?",
                'options' => [
                    'A' => $analysis['core_step'] ?: "Melaksanakan tahapan kerja secara berurutan sesuai klausul naskah SOP dan memastikan kontrol mutu terpenuhi.",
                    'B' => "Mengubah urutan tahapan kerja teknis secara sepihak di lapangan tanpa melakukan evaluasi risiko terlebih dahulu.",
                    'C' => "Melewati tahapan validasi dan pengujian mutu apabila volume antrean pekerjaan sedang mengalami penumpukan.",
                    'D' => "Tidak mencatat riwayat pelaksanaan pekerjaan pada lembar formulir kerja resmi yang telah disediakan."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 6,
            ],

            // Soal 7: Verifikasi Kelayakan Dokumen Pendukung
            [
                'type' => 'multiple_choice',
                'question' => "Tindakan verifikasi apa yang wajib dilakukan terhadap berkas pendukung pada prosedur [{$docTitle}]?",
                'options' => [
                    'A' => "Memvalidasi keabsahan data, kecocokan tanggal/nominal, serta kesesuaian lampiran sebelum diproses.",
                    'B' => "Langsung menyetujui dokumen tanpa memeriksa kebenaran data lampiran demi mempercepat antrean proses.",
                    'C' => "Memusnahkan berkas lampiran pendukung yang dinilai memperlambat proses administrasi pemeriksaan kerja.",
                    'D' => "Melimpahkan tugas pemeriksaan kelayakan dokumen kepada pihak eksternal tanpa adanya pengawasan internal."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 7,
            ],

            // Soal 8: Dokumen Rekam Kerja & Formulir Bukti
            [
                'type' => 'multiple_choice',
                'question' => "Dokumen pendukung atau bukti rekam kerja yang wajib dilengkapi dalam pelaksanaan SOP [{$docTitle}] adalah:",
                'options' => [
                    'A' => $analysis['record_doc'] ?: "Formulir checklist, lembar verifikasi, atau berita acara pelaksanaan yang telah disetujui.",
                    'B' => "Catatan informal pada lembar memo pribadi tanpa mencantumkan identitas nomor dokumen kontrol yang sah.",
                    'C' => "Tidak diperlukan rekaman bukti atau arsip dokumen apapun dalam menjalankan tahapan operasional kerja.",
                    'D' => "Cukup berupa dokumentasi foto pribadi tanpa disertai tanda tangan resmi dari pejabat verifikator dokumen."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 8,
            ],

            // Soal 9: Penanganan Penyimpangan & Ketidaksesuaian
            [
                'type' => 'multiple_choice',
                'question' => "Apabila ditemukan ketidaksesuaian (non-conformance), kendala operasional, atau pembatalan saat menjalankan SOP ini, apa yang harus dilakukan?",
                'options' => [
                    'A' => "Menghentikan risiko bahaya, melakukan tindakan mitigasi awal, dan segera melapor ke atasan untuk tindakan korektif.",
                    'B' => "Tetap melanjutkan pekerjaan secara diam-diam dan menyembunyikan kendala penyimpangan dari pengawasan pimpinan.",
                    'C' => "Melakukan manipulasi terhadap data hasil pengukuran mutu agar laporan akhir tetap terlihat memenuhi target.",
                    'D' => "Meninggalkan area pekerjaan begitu saja tanpa memberikan informasi tertulis kepada pengawas operasional."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 9,
            ],

            // Soal 10: Kepatuhan Terhadap Batas Waktu (SLA)
            [
                'type' => 'multiple_choice',
                'question' => "Mengapa kepatuhan terhadap jangka waktu pelaksanaan (SLA/timeline) dalam SOP [{$docTitle}] sangat penting dipatuhi?",
                'options' => [
                    'A' => "Untuk menjaga kelancaran alur kerja organisasi, mencegah pemborosan biaya, dan menjamin ketepatan target mutu.",
                    'B' => "Agar seluruh berkas pekerjaan dapat ditunda pelaksanaannya hingga mendekati batas waktu akhir penyerahan.",
                    'C' => "Hanya sekadar memenuhi formalitas tampilan pengisian sistem komputer tanpa berdampak pada efisiensi kerja.",
                    'D' => "Tidak memiliki pengaruh nyata terhadap efektivitas operasional maupun kepuasan pemangku kepentingan unit."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 10,
            ],

            // Soal 11: Analisis & Mitigasi Risiko
            [
                'type' => 'multiple_choice',
                'question' => "Potensi dampak atau kerugian yang dapat timbul apabila tahapan dalam SOP [{$docTitle}] ini diabaikan oleh pelaksana adalah:",
                'options' => [
                    'A' => "Terjadinya kerugian finansial, kegagalan mutu pelayanan, sanksi ketidakpatuhan, atau temuan saat audit mutu.",
                    'B' => "Meningkatnya produktivitas dan kepuasan pelanggan secara signifikan tanpa adanya risiko kelalaian kerja.",
                    'C' => "Mendapatkan apresiasi otomatis dari tim penjamin mutu atas inisiatif pemotongan prosedur kerja resmi.",
                    'D' => "Semua sistem operasional dan keuangan perusahaan akan senantiasa berjalan normal tanpa hambatan apapun."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 11,
            ],

            // Soal 12: Akuntabilitas & Pelaporan Hasil
            [
                'type' => 'multiple_choice',
                'question' => "Kepada pihak mana laporan akhir pelaksanaan atau arsip rekaman kerja dari SOP [{$docTitle}] harus diserahkan/dilaporkan?",
                'options' => [
                    'A' => "Kepada Atasan Langsung, Departemen Terkait ({$dept}), dan diarsipkan pada sistem manajemen mutu perusahaan.",
                    'B' => "Disimpan sebagai dokumen rahasia pribadi pelaksana tanpa boleh diakses oleh pihak manajemen mutu internal.",
                    'C' => "Cukup disampaikan melalui percakapan lisan santai tanpa memerlukan pembuatan laporan rekapitulasi tertulis.",
                    'D' => "Diberikan kepada pihak eksternal yang tidak memiliki kewenangan maupun hak akses audit terhadap perusahaan."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 12,
            ],

            // Soal 13: Prinsip Efisiensi Biaya & Anggaran
            [
                'type' => 'multiple_choice',
                'question' => "Terkait pengelolaan biaya atau sumber daya dalam pelaksanaan SOP [{$docTitle}], prinsip apa yang wajib diterapkan?",
                'options' => [
                    'A' => "Menerapkan prinsip kehati-hatian, efisiensi anggaran, dan kewajaran tarif sesuai plafon resmi perusahaan.",
                    'B' => "Memilih opsi dengan biaya paling tinggi tanpa melakukan perbandingan alternatif tarif yang lebih efisien.",
                    'C' => "Mempergunakan alokasi anggaran milik unit kerja lain tanpa adanya persetujuan resmi dari Bagian Keuangan.",
                    'D' => "Menghilangkan pencatatan nota transaksi pengeluaran operasional agar proses pencairan uang berjalan singkat."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 13,
            ],

            // Soal 14: Penyimpanan Arsip & Ketertelusuran (Traceability)
            [
                'type' => 'multiple_choice',
                'question' => "Bagaimana ketentuan penyimpanan arsip atau bukti pelaksanaan SOP agar tetap tertelusur (traceable) saat audit e-QMS?",
                'options' => [
                    'A' => "Disimpan terpusat dengan nomor dokumen/log yang jelas serta mudah diakses oleh auditor yang berwenang.",
                    'B' => "Segera dimusnahkan setelah pekerjaan hari itu selesai dengan alasan untuk menghemat ruang lemari berkas fisik.",
                    'C' => "Ditempatkan pada arsip tersembunyi yang sengaja tidak dapat dijangkau oleh sistem pengendalian mutu kantor.",
                    'D' => "Tidak perlu disimpan dalam bentuk fisik maupun digital karena seluruh berkas rentan mengalami kerusakan data."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 14,
            ],

            // Soal 15: Evaluasi Berkala SOP
            [
                'type' => 'multiple_choice',
                'question' => "Berdasarkan standar tata kelola mutu e-QMS PT PKM Group, tinjauan dan evaluasi berkala terhadap SOP ini dilakukan:",
                'options' => [
                    'A' => "Secara berkala setiap 1 (satu) tahun sekali atau sewaktu-waktu jika ada pembaruan proses kerja dan regulasi.",
                    'B' => "Hanya dibuat satu kali saat pendirian unit usaha dan tidak diperbolehkan untuk direvisi di masa mendatang.",
                    'C' => "Setiap sepuluh tahun sekali tanpa perlu mempertimbangkan kebutuhan peningkatan efisiensi dan perbaikan mutu.",
                    'D' => "Hanya dilakukan apabila perusahaan sedang menghadapi sanksi hukum dari pihak regulator ketenagakerjaan."
                ],
                'correct_answer' => 'A',
                'points' => 10,
                'sequence' => 15,
            ],
        ];

        foreach ($questions as $qData) {
            // Acak urutan opsi jawaban dan sesuaikan correct_answer ke huruf acak (A, B, C, atau D)
            $shuffled = $this->shuffleOptionsAndKey($qData['options'], $qData['correct_answer']);
            $qData['options'] = $shuffled['options'];
            $qData['correct_answer'] = $shuffled['correct_answer'];

            SopQuizQuestion::create(array_merge($qData, ['sop_quiz_id' => $quiz->id]));
        }
    }

    /**
     * Acak urutan opsi jawaban (A, B, C, D) dan sesuaikan huruf kunci jawaban (correct_answer)
     */
    private function shuffleOptionsAndKey(array $options, string $correctKey): array
    {
        $correctText = $options[$correctKey] ?? '';
        $optionTexts = array_values($options);

        // Acak urutan opsi jawaban
        shuffle($optionTexts);

        $letters = ['A', 'B', 'C', 'D'];
        $shuffledOptions = [];
        $newCorrectKey = 'A';

        foreach ($optionTexts as $index => $text) {
            $letter = $letters[$index] ?? chr(65 + $index);
            $shuffledOptions[$letter] = $text;
            if ($text === $correctText) {
                $newCorrectKey = $letter;
            }
        }

        return [
            'options'        => $shuffledOptions,
            'correct_answer' => $newCorrectKey,
        ];
    }

    /**
     * Ekstraksi teks dari berkas PDF dokumen SOP
     */
    private function extractTextFromSopPdf(Document $doc): string
    {
        $candidatePaths = [
            storage_path('app/public/' . $doc->file_isi),
            storage_path('app/public/' . $doc->file_final),
            storage_path('app/public/' . $doc->file_cover),
        ];

        $parser = new Parser();
        $fullText = '';

        foreach ($candidatePaths as $path) {
            if (!empty($path) && file_exists($path)) {
                try {
                    $pdf = $parser->parseFile($path);
                    $fullText .= " " . $pdf->getText();
                } catch (\Exception $e) {
                    // Abaikan dan lanjutkan
                }
            }
        }

        return trim(preg_replace('/\s+/', ' ', $fullText));
    }

    /**
     * Parsing klausul utama dari teks naskah SOP
     */
    private function parseSopClauses(string $text, Document $doc): array
    {
        $dept = $doc->department ?: ($doc->business_unit ?: 'Departemen Terkait');
        $docTitle = $doc->title;

        $purpose = null;
        $scope = null;
        $pic = null;
        $prepStep = null;
        $coreStep = null;
        $recordDoc = null;

        if (!empty($text)) {
            // Analisis Tujuan
            if (preg_match('/(?:tujuan|maksud)\s*[:\-.]?\s*([^.\n]{20,150})/i', $text, $matches)) {
                $purpose = trim($matches[1]);
            }

            // Analisis Ruang Lingkup
            if (preg_match('/(?:ruang\s*lingkup|cakupan|batasan)\s*[:\-.]?\s*([^.\n]{20,150})/i', $text, $matches)) {
                $scope = trim($matches[1]);
            }

            // Analisis Penanggung Jawab
            if (preg_match('/(?:penanggung\s*jawab|tanggung\s*jawab|wewenang|pelaksana)\s*[:\-.]?\s*([^.\n]{15,120})/i', $text, $matches)) {
                $pic = trim($matches[1]);
            }

            // Analisis Prosedur / Tahapan
            if (preg_match('/(?:prosedur|tahapan|instruksi\s*kerja|langkah\s*kerja)\s*[:\-.]?\s*([^.\n]{20,160})/i', $text, $matches)) {
                $coreStep = trim($matches[1]);
            }

            // Analisis Dokumen Terkait / Formulir
            if (preg_match('/(?:dokumen\s*terkait|formulir|lampiran|rekaman)\s*[:\-.]?\s*([^.\n]{15,120})/i', $text, $matches)) {
                $recordDoc = trim($matches[1]);
            }
        }

        return [
            'purpose'    => $purpose ?: "Menstandarisasi alur kerja operasional {$docTitle} agar berjalan efektif, tertib, dan memenuhi standar kepatuhan PT PKM Group.",
            'scope'      => $scope ?: "Berlaku untuk seluruh operasional dan personil pelaksana yang menangani aktivitas {$docTitle} di lingkungan {$dept}.",
            'pic'        => $pic ?: "Kepala Divisi/Departemen {$dept}, Supervisor, dan seluruh personil pelaksana yang ditugaskan.",
            'prep_step'  => $prepStep ?: "Memeriksa kelayakan alat kerja, kelengkapan APD keselamatan, dan izin kerja sebelum memulai {$docTitle}.",
            'core_step'  => $coreStep ?: "Menjalankan setiap tahapan instruksi kerja teknis secara akurat dan tidak melompati verifikasi keselamatan/mutu.",
            'record_doc' => $recordDoc ?: "Formulir checklist mutu {$docTitle}, Berita Acara, dan Lembar Log Harian operasional.",
        ];
    }
}
