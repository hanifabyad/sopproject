<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\DocumentLog;
use App\Models\Evaluation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EvaluationAndTrackingDummySeeder extends Seeder
{
    /**
     * Run the database seeds for evaluation and tracking data.
     */
    public function run(): void
    {
        // 1. PASTIKAN AKUN ADMIN & PIMPINAN / EVALUATOR TERSEDIA
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'full_name' => 'Administrator QMS',
                'email'     => 'admin@nexaflow.com',
                'password'  => Hash::make('password123'),
                'role'      => 'admin',
                'status'    => true,
            ]
        );

        $usersData = [
            [
                'username'  => 'dirut',
                'full_name' => 'Hendra Wijaya, S.E., M.M.',
                'email'     => 'dirut@pkmgroup.com',
                'role'      => 'Direktur Utama',
            ],
            [
                'username'  => 'kadiv_retail',
                'full_name' => 'Agus Setiawan, S.T.',
                'email'     => 'kadiv.retail@pkmgroup.com',
                'role'      => 'Ka. Div Retail',
            ],
            [
                'username'  => 'chief_fa',
                'full_name' => 'Linda Permata, S.E., Ak.',
                'email'     => 'chief.fa@pkmgroup.com',
                'role'      => 'Chief F&A',
            ],
            [
                'username'  => 'ka_qms',
                'full_name' => 'Trinwetty, S.Si.',
                'email'     => 'qms@pkmgroup.com',
                'role'      => 'KA.DEPT.QMS',
            ],
            [
                'username'  => 'ka_it',
                'full_name' => 'Rian Pratama, S.Kom.',
                'email'     => 'it@pkmgroup.com',
                'role'      => 'KA.DEPT.IT',
            ],
            [
                'username'  => 'ka_hc',
                'full_name' => 'Maya Kartika, S.Psi.',
                'email'     => 'hc@pkmgroup.com',
                'role'      => 'KA.DEPT.HC',
            ],
            [
                'username'  => 'ka_hse',
                'full_name' => 'Bambang Sutrisno, S.K.M.',
                'email'     => 'hse@pkmgroup.com',
                'role'      => 'KA.DEPT.HSE',
            ],
            [
                'username'  => 'ka_spbu',
                'full_name' => 'Dedi Supriyadi',
                'email'     => 'spbu@pkmgroup.com',
                'role'      => 'Ka. BU SPBU',
            ],
            [
                'username'  => 'ka_gas',
                'full_name' => 'Fajar Nugroho, S.T.',
                'email'     => 'gas@pkmgroup.com',
                'role'      => 'Ka. BU Gas & SPBE',
            ],
            [
                'username'  => 'ka_inmar',
                'full_name' => 'Capt. Surya Dharma, M.Mar.',
                'email'     => 'inmar@pkmgroup.com',
                'role'      => 'Ka. BU Inmarr',
            ],
            [
                'username'  => 'ka_cpt',
                'full_name' => 'Ir. Arif Hidayat',
                'email'     => 'cpt@pkmgroup.com',
                'role'      => 'Ka. BU CPT',
            ],
        ];

        $createdUsers = [];
        foreach ($usersData as $ud) {
            $createdUsers[$ud['role']] = User::firstOrCreate(
                ['username' => $ud['username']],
                [
                    'full_name' => $ud['full_name'],
                    'email'     => $ud['email'],
                    'password'  => Hash::make('password123'),
                    'role'      => $ud['role'],
                    'status'    => true,
                ]
            );
        }

        // Helper untuk reviewer default
        $dirut = $createdUsers['Direktur Utama'] ?? $admin;
        $qms = $createdUsers['KA.DEPT.QMS'] ?? $admin;

        // 2. DAFTAR DOKUMEN DUMMY UNTUK TRACKING & EVALUASI
        $documentsSeed = [
            // ==========================================
            // KELOMPOK 1: DOKUMEN EVALUASI BERKALA (STATUS: ACTIVE)
            // ==========================================
            [
                'title'               => 'SOP Manajemen Backup, Disaster Recovery, dan Keamanan Server',
                'department'          => 'IT',
                'doc_number'          => 'SOP-IT-001/PKM/2025',
                'doc_revision'        => '0',
                'doc_date'            => '2025-01-15',
                'effective_date'      => '2025-01-15',
                'evaluation_due_date' => Carbon::now()->subDays(5)->toDateString(), // Baru jatuh tempo 5 hari lalu
                'status'              => 'active',
                'evaluation_status'   => 'due',
                'created_at'          => Carbon::create(2025, 1, 15, 9, 0, 0),
                'evaluator_role'      => 'KA.DEPT.IT',
                'evaluation_data'     => [
                    'evaluation_period' => '2026',
                    'due_date'          => Carbon::now()->subDays(5)->toDateString(),
                    'status'            => 'due',
                ],
                'approvals'           => ['completed'],
            ],
            [
                'title'               => 'SOP Rekrutmen, Seleksi, dan Onboarding Karyawan Baru',
                'department'          => 'HC',
                'doc_number'          => 'SOP-HC-004/PKM/2024',
                'doc_revision'        => '1',
                'doc_date'            => '2024-05-10',
                'effective_date'      => '2024-05-10',
                'evaluation_due_date' => Carbon::now()->subMonths(2)->toDateString(), // Overdue 2 bulan
                'status'              => 'active',
                'evaluation_status'   => 'overdue',
                'created_at'          => Carbon::create(2024, 5, 10, 10, 0, 0),
                'evaluator_role'      => 'KA.DEPT.HC',
                'evaluation_data'     => [
                    'evaluation_period' => '2025',
                    'due_date'          => Carbon::now()->subMonths(2)->toDateString(),
                    'status'            => 'overdue',
                ],
                'approvals'           => ['completed'],
            ],
            [
                'title'               => 'SOP Tanggap Darurat Kebakaran, Gempa Bumi, dan Evakuasi Lapangan',
                'department'          => 'HSE',
                'doc_number'          => 'SOP-HSE-003/PKM/2025',
                'doc_revision'        => '0',
                'doc_date'            => '2025-03-20',
                'effective_date'      => '2025-03-20',
                'evaluation_due_date' => Carbon::now()->addDays(10)->toDateString(),
                'status'              => 'active',
                'evaluation_status'   => 'in_review',
                'created_at'          => Carbon::create(2025, 3, 20, 11, 30, 0),
                'evaluator_role'      => 'KA.DEPT.HSE',
                'evaluation_data'     => [
                    'evaluation_period' => '2026',
                    'due_date'          => Carbon::now()->addDays(10)->toDateString(),
                    'started_at'        => Carbon::now()->subDays(1),
                    'status'            => 'in_review',
                ],
                'approvals'           => ['completed'],
            ],
            [
                'title'               => 'SOP Penerimaan, Uji Densitas, dan Pengisian Tangki Pendam SPBU',
                'department'          => 'SPBU',
                'doc_number'          => 'SOP-BU-SPBU-012/PKM/2024',
                'doc_revision'        => '2',
                'doc_date'            => '2024-08-01',
                'effective_date'      => '2024-08-01',
                'evaluation_due_date' => Carbon::now()->toDateString(),
                'status'              => 'active',
                'evaluation_status'   => 'submitted',
                'created_at'          => Carbon::create(2024, 8, 1, 8, 0, 0),
                'evaluator_role'      => 'Ka. BU SPBU',
                'evaluation_data'     => [
                    'evaluation_period'      => '2025',
                    'due_date'               => Carbon::now()->toDateString(),
                    'started_at'             => Carbon::now()->subDays(3),
                    'submitted_at'           => Carbon::now()->subHours(5),
                    'status'                 => 'submitted',
                    'usage_status'           => 'Digunakan secara rutin',
                    'conformity_status'      => 'Sebagian perlu diperbarui',
                    'conformity_notes'       => 'Prosedur sampling kadar air pada BBM Biosolar B35 perlu penambahan alat ukur pasta air otomatis sesuai SOP Pertamina 2025.',
                    'process_change_status'  => 'Ada',
                    'process_change_notes'   => 'Terdapat metode ATG (Automatic Tank Gauge) terintegrasi sistem POS baru.',
                    'effectiveness_status'   => 'Cukup efektif',
                    'implementation_issues'  => 'Operator shift malam terkadang masih mencatat manual di logbook.',
                    'recommendation'         => 'Disarankan dilakukan revisi minor untuk menyelaraskan alur ATG dan uji kualitas bahan bakar nabati.',
                    'result'                 => 'REVISION REQUIRED',
                ],
                'approvals'           => ['completed'],
            ],
            [
                'title'               => 'SOP Pengendalian Dokumen Mutu, Catatan Rekaman, dan Distribusi e-QMS',
                'department'          => 'QMS',
                'doc_number'          => 'SOP-QMS-001/PKM/2025',
                'doc_revision'        => '0',
                'doc_date'            => '2025-02-10',
                'effective_date'      => '2025-02-10',
                'evaluation_due_date' => Carbon::now()->addYear()->toDateString(),
                'status'              => 'active',
                'evaluation_status'   => 'completed',
                'created_at'          => Carbon::create(2025, 2, 10, 14, 0, 0),
                'evaluator_role'      => 'KA.DEPT.QMS',
                'evaluation_data'     => [
                    'evaluation_period'      => '2026',
                    'due_date'               => Carbon::now()->subDays(15)->toDateString(),
                    'started_at'             => Carbon::now()->subDays(14),
                    'submitted_at'           => Carbon::now()->subDays(10),
                    'status'                 => 'completed',
                    'usage_status'           => 'Digunakan secara rutin',
                    'conformity_status'      => 'Sangat sesuai',
                    'process_change_status'  => 'Tidak ada',
                    'effectiveness_status'   => 'Sangat efektif',
                    'recommendation'         => 'SOP sangat efektif dan mendukung kelancaran audit eksternal ISO 9001:2015. SOP tetap aktif.',
                    'result'                 => 'CONTINUE',
                    'admin_id'               => $admin->id,
                    'admin_reviewed_at'      => Carbon::now()->subDays(8),
                    'admin_notes'            => 'Hasil evaluasi disetujui. SOP dinyatakan tetap berlaku tanpa perubahan hingga periode berikutnya.',
                ],
                'approvals'           => ['completed'],
            ],
            [
                'title'               => 'SOP Pengisian & Distribusi Tabung Gas LPG 3KG PSO',
                'department'          => 'LPG PSO',
                'doc_number'          => 'SOP-BU-LPG-007/PKM/2025',
                'doc_revision'        => '0',
                'doc_date'            => '2025-06-01',
                'effective_date'      => '2025-06-01',
                'evaluation_due_date' => Carbon::create(2026, 6, 1)->toDateString(),
                'status'              => 'active',
                'evaluation_status'   => 'upcoming',
                'created_at'          => Carbon::create(2025, 6, 1, 13, 0, 0),
                'evaluator_role'      => 'Ka. BU Gas & SPBE',
                'evaluation_data'     => [
                    'evaluation_period' => '2026',
                    'due_date'          => Carbon::create(2026, 6, 1)->toDateString(),
                    'status'            => 'upcoming',
                ],
                'approvals'           => ['completed'],
            ],
            [
                'title'               => 'SOP Pengajuan Reimbursement Operasional & Petty Cash Kantor',
                'department'          => 'FINANCE',
                'doc_number'          => 'SOP-FIN-005/PKM/2024',
                'doc_revision'        => '1',
                'doc_date'            => '2024-11-15',
                'effective_date'      => '2024-11-15',
                'evaluation_due_date' => Carbon::now()->addMonths(8)->toDateString(),
                'status'              => 'active',
                'evaluation_status'   => 'completed',
                'created_at'          => Carbon::create(2024, 11, 15, 10, 0, 0),
                'evaluator_role'      => 'Chief F&A',
                'evaluation_data'     => [
                    'evaluation_period'      => '2025',
                    'due_date'               => Carbon::now()->subMonths(1)->toDateString(),
                    'started_at'             => Carbon::now()->subMonths(1)->addDays(2),
                    'submitted_at'           => Carbon::now()->subMonths(1)->addDays(4),
                    'status'                 => 'completed',
                    'usage_status'           => 'Digunakan secara rutin',
                    'conformity_status'      => 'Sesuai',
                    'process_change_status'  => 'Tidak ada',
                    'effectiveness_status'   => 'Efektif',
                    'recommendation'         => 'Alur petty cash berjalan lancar dengan sistem transfer online.',
                    'result'                 => 'CONTINUE',
                    'admin_id'               => $admin->id,
                    'admin_reviewed_at'      => Carbon::now()->subMonths(1)->addDays(5),
                    'admin_notes'            => 'Disetujui untuk diperpanjang masa berlakunya.',
                ],
                'approvals'           => ['completed'],
            ],

            // ==========================================
            // KELOMPOK 2: DOKUMEN TRACKING SIKLUS (WAITING APPROVAL)
            // ==========================================
            [
                'title'               => 'SOP Pengelolaan Hak Akses User dan Keamanan Data Sistem e-QMS',
                'department'          => 'IT',
                'doc_number'          => 'SOP-IT-008/PKM/2026',
                'doc_revision'        => '0',
                'doc_date'            => Carbon::now()->subDays(4)->toDateString(),
                'status'              => 'waiting',
                'evaluation_status'   => 'upcoming',
                'created_at'          => Carbon::now()->subDays(4),
                'approvals'           => ['waiting_middle'], // Creator Approved, Reviewer Pending, Dirut Pending
            ],
            [
                'title'               => 'SOP Bunkering Penyaluran Bahan Bakar Minyak Kapal Laut (Inmar)',
                'department'          => 'INMAR (CNGM)',
                'doc_number'          => 'SOP-BU-INM-002/PKM/2026',
                'doc_revision'        => '0',
                'doc_date'            => Carbon::now()->subDays(6)->toDateString(),
                'status'              => 'waiting',
                'evaluation_status'   => 'upcoming',
                'created_at'          => Carbon::now()->subDays(6),
                'approvals'           => ['waiting_final'], // Creator Approved, Reviewer Approved, Dirut Pending
            ],
            [
                'title'               => 'SOP Pemeliharaan Floating Dock dan Inspeksi Lambung Kapal CPT',
                'department'          => 'CPT & MHM',
                'doc_number'          => 'SOP-BU-CPT-001/PKM/2026',
                'doc_revision'        => '0',
                'doc_date'            => Carbon::now()->subDays(2)->toDateString(),
                'status'              => 'waiting',
                'evaluation_status'   => 'upcoming',
                'created_at'          => Carbon::now()->subDays(2),
                'approvals'           => ['waiting_start'], // Pending Creator
            ],

            // ==========================================
            // KELOMPOK 3: DOKUMEN STATUS REVISI (NEED_REVISION)
            // ==========================================
            [
                'title'               => 'SOP Evaluasi Kinerja Tahunan (KPI) Pegawai PKM Group',
                'department'          => 'HC',
                'doc_number'          => 'SOP-HC-003/PKM/2026',
                'doc_revision'        => '0',
                'doc_date'            => Carbon::now()->subDays(12)->toDateString(),
                'status'              => 'need_revision',
                'evaluation_status'   => 'upcoming',
                'created_at'          => Carbon::now()->subDays(12),
                'approvals'           => ['rejected_with_notes'],
                'reject_notes'        => 'Mohon lengkapi bobot persentase penilaian Core Values dan lampirkan form appraisal terbaru.',
            ],
            [
                'title'               => 'SOP Kalibrasi Nozzle dan Uji Tera Metrologi Legal SPBU',
                'department'          => 'SPBU',
                'doc_number'          => 'SOP-BU-SPBU-005/PKM/2026',
                'doc_revision'        => '1',
                'doc_date'            => Carbon::now()->subDays(8)->toDateString(),
                'status'              => 'need_revision',
                'evaluation_status'   => 'upcoming',
                'created_at'          => Carbon::now()->subDays(8),
                'approvals'           => ['rejected_with_notes'],
                'reject_notes'        => 'Sesuaikan frekuensi pengujian berkala dengan Peraturan Menteri Perdagangan No. 67 Tahun 2018.',
            ],

            // ==========================================
            // KELOMPOK 4: DOKUMEN OBSOLETE / USANG
            // ==========================================
            [
                'title'               => 'SOP Penggunaan Sistem Email Korporat Server Lokal Konvensional',
                'department'          => 'IT',
                'doc_number'          => 'SOP-IT-001/PKM/2023',
                'doc_revision'        => '0',
                'doc_date'            => '2023-03-15',
                'effective_date'      => '2023-03-15',
                'status'              => 'obsolete',
                'evaluation_status'   => 'completed',
                'created_at'          => Carbon::create(2023, 3, 15, 10, 0, 0),
                'evaluator_role'      => 'KA.DEPT.IT',
                'evaluation_data'     => [
                    'evaluation_period'      => '2024',
                    'due_date'               => '2024-03-15',
                    'submitted_at'           => '2024-03-16 11:00:00',
                    'status'                 => 'completed',
                    'usage_status'           => 'Tidak digunakan',
                    'usage_reason'           => 'SOP sudah digantikan prosedur lain',
                    'conformity_status'      => 'Tidak sesuai',
                    'process_change_status'  => 'Ada',
                    'effectiveness_status'   => 'Tidak efektif',
                    'recommendation'         => 'Sistem email lokal sudah dimigrasikan ke Google Workspace Cloud. SOP ini harus diarsipkan (obsolete).',
                    'result'                 => 'OBSOLETE',
                    'admin_id'               => $admin->id,
                    'admin_reviewed_at'      => '2024-03-20 14:00:00',
                    'admin_notes'            => 'Penetapan status OBSOLETE. Digantikan oleh SOP Cloud Collaboration 2024.',
                ],
                'approvals'           => ['completed'],
            ],
            [
                'title'               => 'SOP Pembayaran Tunai dan Voucher Manual Kasir SPBU',
                'department'          => 'FINANCE',
                'doc_number'          => 'SOP-FIN-002/PKM/2023',
                'doc_revision'        => '0',
                'doc_date'            => '2023-06-20',
                'effective_date'      => '2023-06-20',
                'status'              => 'obsolete',
                'evaluation_status'   => 'completed',
                'created_at'          => Carbon::create(2023, 6, 20, 9, 30, 0),
                'evaluator_role'      => 'Chief F&A',
                'evaluation_data'     => [
                    'evaluation_period'      => '2024',
                    'due_date'               => '2024-06-20',
                    'submitted_at'           => '2024-06-22 10:00:00',
                    'status'                 => 'completed',
                    'usage_status'           => 'Tidak digunakan',
                    'usage_reason'           => 'SOP sudah digantikan prosedur lain',
                    'conformity_status'      => 'Tidak sesuai',
                    'effectiveness_status'   => 'Tidak efektif',
                    'recommendation'         => 'Seluruh transaksi kini menggunakan EDC QRIS & MyPertamina Cashless.',
                    'result'                 => 'OBSOLETE',
                    'admin_id'               => $admin->id,
                    'admin_reviewed_at'      => '2024-06-25 11:00:00',
                    'admin_notes'            => 'Dokumen diarsipkan.',
                ],
                'approvals'           => ['completed'],
            ],
        ];

        // 3. INSERT SEMUA DOKUMEN, LOG, APPROVAL, DAN EVALUASI
        foreach ($documentsSeed as $item) {
            $evaluatorUser = isset($item['evaluator_role']) && isset($createdUsers[$item['evaluator_role']])
                ? $createdUsers[$item['evaluator_role']]
                : $admin;

            // Cari atau buat Dokumen
            $doc = Document::firstOrNew(['doc_number' => $item['doc_number']]);
            $doc->fill([
                'title'               => $item['title'],
                'department'          => $item['department'],
                'reviewer_id'         => $dirut->id,
                'file_final'          => 'dummy.pdf',
                'file_preview'        => 'dummy.pdf',
                'doc_revision'        => $item['doc_revision'] ?? '0',
                'doc_date'            => $item['doc_date'] ?? Carbon::now()->toDateString(),
                'effective_date'      => $item['effective_date'] ?? null,
                'evaluation_due_date' => $item['evaluation_due_date'] ?? null,
                'status'              => $item['status'],
                'evaluation_status'   => $item['evaluation_status'] ?? 'upcoming',
                'created_at'          => $item['created_at'] ?? Carbon::now(),
                'updated_at'          => Carbon::now(),
            ]);
            $doc->save();

            // 4. GENERATE EVALUASI JIKA ADA
            if (!empty($item['evaluation_data'])) {
                $evalData = $item['evaluation_data'];
                $eval = Evaluation::updateOrCreate(
                    [
                        'document_id'       => $doc->id,
                        'evaluation_period' => $evalData['evaluation_period'],
                    ],
                    [
                        'evaluator_id'          => $evaluatorUser->id,
                        'due_date'              => $evalData['due_date'],
                        'started_at'            => $evalData['started_at'] ?? null,
                        'submitted_at'          => $evalData['submitted_at'] ?? null,
                        'status'                => $evalData['status'],
                        'usage_status'          => $evalData['usage_status'] ?? null,
                        'usage_reason'          => $evalData['usage_reason'] ?? null,
                        'conformity_status'     => $evalData['conformity_status'] ?? null,
                        'conformity_notes'      => $evalData['conformity_notes'] ?? null,
                        'process_change_status' => $evalData['process_change_status'] ?? null,
                        'process_change_notes'  => $evalData['process_change_notes'] ?? null,
                        'effectiveness_status'  => $evalData['effectiveness_status'] ?? null,
                        'implementation_issues' => $evalData['implementation_issues'] ?? null,
                        'recommendation'        => $evalData['recommendation'] ?? null,
                        'result'                => $evalData['result'] ?? null,
                        'admin_id'              => $evalData['admin_id'] ?? null,
                        'admin_reviewed_at'     => $evalData['admin_reviewed_at'] ?? null,
                        'admin_notes'           => $evalData['admin_notes'] ?? null,
                    ]
                );

                $doc->update(['evaluation_id' => $eval->id]);
            }

            // 5. GENERATE APPROVAL STEPS UNTUK TRACKING
            $approvalType = $item['approvals'][0] ?? 'completed';
            
            // Hapus approval lama jika ada untuk idempotency
            DocumentApproval::where('document_id', $doc->id)->delete();

            if ($approvalType === 'completed') {
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $qms->id,
                    'sequence'       => 1,
                    'stage'          => 'creator',
                    'signature_slot' => 'sig01',
                    'status'         => 'approved',
                    'notes'          => 'Draft SOP telah diperiksa dan sesuai standar mutu e-QMS.',
                    'processed_at'   => $doc->created_at->copy()->addHours(2),
                ]);
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $evaluatorUser->id,
                    'sequence'       => 2,
                    'stage'          => 'reviewer',
                    'signature_slot' => 'sig02',
                    'status'         => 'approved',
                    'notes'          => 'Disetujui oleh Kepala Unit/Departemen terkait.',
                    'processed_at'   => $doc->created_at->copy()->addHours(5),
                ]);
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $dirut->id,
                    'sequence'       => 3,
                    'stage'          => 'final',
                    'signature_slot' => 'sig09',
                    'status'         => 'approved',
                    'notes'          => 'Disahkan untuk diberlakukan secara resmi di seluruh unit perusahaan.',
                    'processed_at'   => $doc->created_at->copy()->addDay(),
                ]);
            } elseif ($approvalType === 'waiting_middle') {
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $qms->id,
                    'sequence'       => 1,
                    'stage'          => 'creator',
                    'signature_slot' => 'sig01',
                    'status'         => 'approved',
                    'notes'          => 'Format dan penomoran SOP valid.',
                    'processed_at'   => $doc->created_at->copy()->addHours(1),
                ]);
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $createdUsers['Chief of Staff']->id ?? $admin->id,
                    'sequence'       => 2,
                    'stage'          => 'reviewer',
                    'signature_slot' => 'sig02',
                    'status'         => 'current',
                ]);
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $dirut->id,
                    'sequence'       => 3,
                    'stage'          => 'final',
                    'signature_slot' => 'sig09',
                    'status'         => 'pending',
                ]);
            } elseif ($approvalType === 'waiting_final') {
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $qms->id,
                    'sequence'       => 1,
                    'stage'          => 'creator',
                    'signature_slot' => 'sig01',
                    'status'         => 'approved',
                    'notes'          => 'Telah disetujui pembuat.',
                    'processed_at'   => $doc->created_at->copy()->addHours(2),
                ]);
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $createdUsers['Ka. Div Retail']->id ?? $admin->id,
                    'sequence'       => 2,
                    'stage'          => 'reviewer',
                    'signature_slot' => 'sig05',
                    'status'         => 'approved',
                    'notes'          => 'Telah direview dan disetujui Kepala Divisi.',
                    'processed_at'   => $doc->created_at->copy()->addHours(6),
                ]);
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $dirut->id,
                    'sequence'       => 3,
                    'stage'          => 'final',
                    'signature_slot' => 'sig09',
                    'status'         => 'current',
                ]);
            } elseif ($approvalType === 'waiting_start') {
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $qms->id,
                    'sequence'       => 1,
                    'stage'          => 'creator',
                    'signature_slot' => 'sig01',
                    'status'         => 'current',
                ]);
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $createdUsers['Ka. BU CPT']->id ?? $admin->id,
                    'sequence'       => 2,
                    'stage'          => 'reviewer',
                    'signature_slot' => 'sig02',
                    'status'         => 'pending',
                ]);
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $dirut->id,
                    'sequence'       => 3,
                    'stage'          => 'final',
                    'signature_slot' => 'sig09',
                    'status'         => 'pending',
                ]);
            } elseif ($approvalType === 'rejected_with_notes') {
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $qms->id,
                    'sequence'       => 1,
                    'stage'          => 'creator',
                    'signature_slot' => 'sig01',
                    'status'         => 'approved',
                    'notes'          => 'Draft awal diajukan.',
                    'processed_at'   => $doc->created_at->copy()->addHours(1),
                ]);
                DocumentApproval::create([
                    'document_id'    => $doc->id,
                    'user_id'        => $dirut->id,
                    'sequence'       => 2,
                    'stage'          => 'reviewer',
                    'signature_slot' => 'sig02',
                    'status'         => 'rejected',
                    'notes'          => $item['reject_notes'] ?? 'Perlu perbaikan isi dokumen.',
                    'processed_at'   => $doc->created_at->copy()->addHours(8),
                ]);
            }

            // 6. GENERATE LOGS UNTUK AUDIT TRAIL
            DocumentLog::where('document_id', $doc->id)->delete();
            DocumentLog::create([
                'document_id' => $doc->id,
                'user_id'     => $qms->id,
                'action'      => 'upload',
                'notes'       => 'Dokumen SOP pertama kali diunggah ke sistem e-QMS.',
                'created_at'  => $doc->created_at,
            ]);

            if ($doc->status === 'active' || $doc->status === 'obsolete') {
                DocumentLog::create([
                    'document_id' => $doc->id,
                    'user_id'     => $dirut->id,
                    'action'      => 'approved_final',
                    'notes'       => 'Dokumen disahkan oleh Direktur Utama dan resmi berstatus aktif.',
                    'created_at'  => $doc->created_at->copy()->addDay(),
                ]);
            } elseif ($doc->status === 'need_revision') {
                DocumentLog::create([
                    'document_id' => $doc->id,
                    'user_id'     => $dirut->id,
                    'action'      => 'rejected',
                    'notes'       => 'Dokumen dikembalikan ke pembuat: ' . ($item['reject_notes'] ?? 'Perlu revisi.'),
                    'created_at'  => $doc->created_at->copy()->addHours(8),
                ]);
            }
        }
    }
}
