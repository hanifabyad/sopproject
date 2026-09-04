<?php

namespace Database\Seeders;

use App\Mail\DocumentEvaluationDueMail;
use App\Mail\DocumentRevisionRequestedMail;
use App\Mail\RevisionRequestApprovedMail;
use App\Mail\SocializationSubmittedMail;
use App\Models\Document;
use App\Models\Evaluation;
use App\Models\DocumentSocialization;
use App\Models\RevisionRequest;
use App\Models\SopQuiz;
use App\Models\SopQuizAttempt;
use App\Models\SopQuizQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DummyActivitySeeder extends Seeder
{
    public function run(): void
    {
        // 0. Bersihkan data dummy lama
        RevisionRequest::query()->delete();
        DocumentSocialization::query()->delete();
        SopQuizAttempt::query()->delete();
        SopQuizQuestion::query()->delete();
        SopQuiz::query()->delete();

        $admin = User::where('role', 'admin')->first() ?? User::first();
        $staffUsers = User::where('id', '!=', $admin->id)->get();
        if ($staffUsers->isEmpty()) {
            $staffUsers = collect([$admin]);
        }

        $documents = Document::where('status', 'active')->orWhere('status', 'need_revision')->take(8)->get();
        if ($documents->isEmpty()) {
            $documents = Document::take(8)->get();
        }

        if ($documents->isEmpty()) {
            return;
        }

        // ==========================================
        // 1. DUMMY PERMOHONAN REVISI SOP & EMAIL NOTIFIKASI
        // ==========================================
        $revisionReasons = [
            'Penyesuaian batas toleransi suhu pada tangki penampungan BBM sesuai instruksi audit HSSE terbaru.',
            'Penggantian alur koordinasi pengajuan lembur dari sistem manual fisik menjadi approval via e-QMS.',
            'Pembaruan struktur organisasi divisi dan pergantian PIC penanggung jawab lembar checklist harian SPBU.',
            'Penambahan klausul prosedur keselamatan darurat kebakaran gas LPG PSO 3KG saat proses unloading.',
            'Perubahan format laporan rekapitulasi penjualan harian dan sinkronisasi data POS kasir ke cloud ERP.',
        ];

        foreach ($revisionReasons as $idx => $reason) {
            $doc = $documents[$idx % $documents->count()];
            $user = $staffUsers[$idx % $staffUsers->count()];
            
            $status = $idx === 0 ? 'pending' : ($idx === 1 ? 'approved' : ($idx === 2 ? 'approved' : ($idx === 3 ? 'rejected' : 'pending')));
            $approvedAt = $status === 'approved' ? now()->subDays(2) : null;
            $deadline = $status === 'approved' ? now()->addDays(5) : null;
            $adminNotes = $status === 'approved' 
                ? 'Disetujui. Silakan unggah naskah revisi sebelum batas waktu 7 hari kerja.' 
                : ($status === 'rejected' ? 'Alasan perubahan belum mendesak. Pembahasan dialihkan ke evaluasi tahunan.' : null);

            $revReq = RevisionRequest::create([
                'document_id' => $doc->id,
                'user_id'     => $user->id,
                'reason'      => $reason,
                'status'      => $status,
                'admin_id'    => $status !== 'pending' ? $admin->id : null,
                'approved_at' => $approvedAt,
                'deadline_at' => $deadline,
                'admin_notes' => $adminNotes,
                'created_at'  => now()->subDays(rand(1, 6)),
            ]);

            // Kirim notifikasi email untuk data dummy baru
            try {
                if ($status === 'pending' && $admin && $admin->email) {
                    Mail::to($admin->email)->send(new DocumentRevisionRequestedMail($doc, $admin, $user, $reason, route('login'), false));
                } elseif ($status === 'approved' && $user && $user->email) {
                    $uploadUrl = $doc->category === 'support' 
                        ? route('admin.support.creator_revise', $doc->id) 
                        : route('admin.BU.creator_revise', $doc->id);
                    Mail::to($user->email)->send(new RevisionRequestApprovedMail($doc, $revReq, $user, $uploadUrl));
                }
            } catch (\Exception $e) {
                Log::warning("Seeder email error (Revision Request): " . $e->getMessage());
            }
        }

        // ==========================================
        // 2. DUMMY BUKTI SOSIALISASI SOP & EMAIL NOTIFIKASI
        // ==========================================
        $socializationNotes = [
            'Sosialisasi SOP telah dilaksanakan di ruang meeting bersama seluruh operator shift pagi. Pemahaman materi berjalan interaktif.',
            'Sosialisasi dilaksanakan secara hybrid bersama PIC regional. Seluruh peserta telah menandatangani lembar daftar hadir.',
            'Briefing SOP operasional baru dilakukan saat apel pagi dan simulasi langsung di area operasional unit.',
            'Pemaparan prosedur keselamatan kerja bersama tim HSE & Security, ditutup dengan tanya jawab teknis.',
        ];

        foreach ($socializationNotes as $idx => $notes) {
            $doc = $documents[$idx % $documents->count()];
            $user = $staffUsers[$idx % $staffUsers->count()];

            $soc = DocumentSocialization::create([
                'document_id'        => $doc->id,
                'user_id'            => $user->id,
                'socialization_date' => now()->subDays(rand(2, 15)),
                'notes'              => $notes,
                'attendance_file'    => 'socializations/attendance/sample_daftar_hadir.pdf',
                'photos'             => [
                    'https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800&auto=format&fit=crop&q=80',
                ],
                'status'             => $idx === 0 ? 'submitted' : 'approved',
                'created_at'         => now()->subDays(rand(1, 5)),
            ]);

            // Kirim notifikasi email bukti sosialisasi ke Admin
            try {
                if ($admin && $admin->email && $idx === 0) {
                    Mail::to($admin->email)->send(new SocializationSubmittedMail($doc, $soc, $user));
                }
            } catch (\Exception $e) {
                Log::warning("Seeder email error (Socialization): " . $e->getMessage());
            }
        }

        // ==========================================
        // 3. DUMMY EVALUASI SOP JATUH TEMPO & EMAIL NOTIFIKASI
        // ==========================================
        if ($documents->isNotEmpty()) {
            $evalDoc = $documents->first();
            $evalUser = $staffUsers->first() ?? $admin;

            Evaluation::updateOrCreate(
                [
                    'document_id'       => $evalDoc->id,
                    'evaluation_period' => now()->format('Y'),
                ],
                [
                    'due_date'          => now()->addDays(14),
                    'status'            => 'due',
                    'result'            => null,
                ]
            );

            try {
                if ($evalUser && $evalUser->email) {
                    Mail::to($evalUser->email)->send(new DocumentEvaluationDueMail($evalDoc, $evalUser, route('login')));
                }
            } catch (\Exception $e) {
                Log::warning("Seeder email error (Evaluation Due): " . $e->getMessage());
            }
        }

        // ==========================================
        // 4. DUMMY KUIS & HASIL UJIAN PEMAHAMAN KARYAWAN
        // ==========================================
        foreach ($documents as $idx => $doc) {
            $quiz = SopQuiz::create([
                'document_id'   => $doc->id,
                'title'         => 'Uji Pemahaman: ' . $doc->title,
                'passing_score' => 70,
                'is_active'     => true,
            ]);

            // Buat 2 soal PG dan 1 essay
            SopQuizQuestion::create([
                'sop_quiz_id'    => $quiz->id,
                'type'           => 'multiple_choice',
                'question'       => 'Apa tujuan utama dari penerapan prosedur standar ini di unit operasional?',
                'options'        => [
                    'A' => 'Menstandarisasi alur kerja dan menjamin keselamatan kerja sesuai standar ISO',
                    'B' => 'Menambah dokumen administratif tanpa implementasi lapangan',
                    'C' => 'Menggantikan seluruh struktur organisasi perusahaan',
                    'D' => 'Menghapus kewajiban pelaporan harian shift kerja'
                ],
                'correct_answer' => 'A',
                'points'         => 35,
                'sequence'       => 1,
            ]);

            SopQuizQuestion::create([
                'sop_quiz_id'    => $quiz->id,
                'type'           => 'multiple_choice',
                'question'       => 'Siapa yang bertanggung jawab melakukan verifikasi berkas checklist harian?',
                'options'        => [
                    'A' => 'Pihak Eksternal',
                    'B' => 'PIC / Supervisor Unit terkait',
                    'C' => 'Hanya Direksi Pusat',
                    'D' => 'Pihak vendor pengangkut'
                ],
                'correct_answer' => 'B',
                'points'         => 35,
                'sequence'       => 2,
            ]);

            SopQuizQuestion::create([
                'sop_quiz_id'    => $quiz->id,
                'type'           => 'essay',
                'question'       => 'Jelaskan langkah mitigasi darurat jika terjadi deviasi atau insiden saat menjalankan prosedur ini!',
                'options'        => null,
                'correct_answer' => 'Lakukan isolasi bahaya, hentikan pekerjaan, dan segera laporkan ke supervisor/HSE.',
                'points'         => 30,
                'sequence'       => 3,
            ]);

            // Buat beberapa riwayat pengerjaan kuis
            $scores = [85, 95, 60, 100, 75, 55, 90, 70];
            $score = $scores[$idx % count($scores)];
            $user = $staffUsers[$idx % $staffUsers->count()];

            SopQuizAttempt::create([
                'sop_quiz_id'  => $quiz->id,
                'document_id'  => $doc->id,
                'user_id'      => $user->id,
                'score'        => $score,
                'status'       => $score >= 70 ? 'passed' : 'failed',
                'attempt_date' => now()->subDays(rand(1, 10))->subHours(rand(1, 6)),
                'feedback'     => $score >= 70 
                    ? 'Selamat, Anda telah memahami isi SOP ini dengan baik dan lulus standar KKM.' 
                    : 'Nilai belum mencapai standar KKM 70. Silakan pelajari kembali materi SOP dan ikuti remedial.',
            ]);
        }

        // ==========================================
        // 5. DUMMY ARSIP DOKUMEN (OBSOLETE & RETENSI)
        // ==========================================
        $obsoleteDoc = Document::where('status', 'obsolete')->first();
        if (!$obsoleteDoc) {
            $sampleDoc = Document::first();
            if ($sampleDoc) {
                $docCopy = $sampleDoc->replicate();
                $docCopy->title = $sampleDoc->title . ' (Versi Lama / Digantikan)';
                $docCopy->doc_number = ($sampleDoc->doc_number ?? 'SOP-PKM-001') . '-OBS';
                $docCopy->status = 'obsolete';
                $docCopy->updated_at = now()->subMonths(14);
                $docCopy->save();
            }
        }
    }
}
