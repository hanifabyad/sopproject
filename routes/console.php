<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| e-QMS SOP Evaluation Scheduled Task
|--------------------------------------------------------------------------
*/
Artisan::command('eqms:check-evaluations', function () {
    $this->info('Memulai pengecekan evaluasi SOP...');
    
    $systemUser = \App\Models\User::where('role', 'admin')->first() ?: \App\Models\User::first();
    $systemUserId = $systemUser ? $systemUser->id : 1;

    $activeDocs = \App\Models\Document::where('status', 'active')->get();
    
    foreach ($activeDocs as $doc) {
        // 1. Inisialisasi effective_date jika belum ada
        if (empty($doc->effective_date)) {
            $activeLog = $doc->logs()->where('action', 'active')->first();
            $effectiveDate = $activeLog ? $activeLog->created_at->toDateString() : $doc->created_at->toDateString();
            
            $doc->effective_date = $effectiveDate;
            $doc->evaluation_due_date = date('Y-m-d', strtotime($effectiveDate . ' +1 year'));
            $doc->save();
        }
        
        $dueDate = $doc->evaluation_due_date;
        if (empty($dueDate)) {
            $effectiveDate = $doc->effective_date ?: $doc->created_at;
            $dueDate = \Carbon\Carbon::parse($effectiveDate)->addYear()->toDateString();
            $doc->evaluation_due_date = $dueDate;
            $doc->save();
        }
        $period = date('Y', strtotime($dueDate));
        
        // 2. Jika hari ini >= tanggal jatuh tempo evaluasi
        if (date('Y-m-d') >= $dueDate) {
            // Cek apakah data evaluasi untuk periode ini sudah dibuat
            $exists = \App\Models\Evaluation::where('document_id', $doc->id)
                ->where('evaluation_period', $period)
                ->exists();
                
            if (!$exists) {
                // Cari evaluator
                $evaluator = null;
                $deptName = strtoupper(trim($doc->department));
                $roles = [];
                switch ($deptName) {
                    case 'HC': $roles = ['KA.DEPT.HC']; break;
                    case 'IT': $roles = ['KA.DEPT.IT']; break;
                    case 'QMS': $roles = ['KA.DEPT.QMS', 'Management Representative']; break;
                    case 'HSE': $roles = ['KA.DEPT.HSE']; break;
                    case 'LEGAL':
                    case 'ADMIN & LEGAL': $roles = ['KA.DEPT.ADMIN & LEGAL']; break;
                    case 'INTERNAL AUDIT':
                    case 'INTERNAL AUDIT & RISK MANAGEMENT': 
                        $roles = ['KA.DEPT.INTERNAL AUDIT', 'Dept. Internal Audit', 'KA.DEPT.INTERNAL AUDIT & RISK MANAGEMENT']; 
                        break;
                    case 'FINANCE':
                    case 'KEUANGAN':
                    case 'F & A': 
                        $roles = ['KA.DEPT.F & A', 'KA.DEPT.KEUANGAN', 'Chief F&A', 'Ka. Div F&A']; 
                        break;
                    case 'LOGISTIC':
                    case 'OPS': 
                        $roles = ['KA.DEPT.SALES & MARKETING', 'Ka. Div Retail']; 
                        break;
                    case 'SPBU': $roles = ['Ka. BU SPBU']; break;
                    case 'LPG PSO':
                    case 'LPG NPSO':
                    case 'PKSP':
                    case 'TRP': 
                        $roles = ['Ka. BU Gas & SPBE', 'Ka. Div Retail', 'Wa. Ka. Div Retail']; 
                        break;
                    case 'INMAR (CNGM)': $roles = ['Ka. BU Inmarr']; break;
                    case 'CPT & MHM':
                    case 'SBS':
                    case 'GVI': 
                        $roles = ['Ka. BU CPT', 'Direktur CPT']; 
                        break;
                    case 'PROCUREMENT': $roles = ['KA.DEPT.PROCRUTMEN']; break;
                    case 'WAREHOUSE':
                    case 'ASET':
                    case 'GA': 
                        $roles = ['KA.DEPT.CORPORATE SEKTARIS', 'Chief of Staff']; 
                        break;
                    case 'KEUANGAN & ACCOUNTING': 
                        $roles = ['KA.DEPT.KEUANGAN', 'Chief F&A', 'Ka. Div F&A']; 
                        break;
                    default: 
                        $roles = ["KA.DEPT.{$deptName}", "Ka. BU {$deptName}"]; 
                        break;
                }
                
                $evaluatorUser = \App\Models\User::whereIn('role', $roles)->where('status', true)->first();
                
                // Buat evaluasi baru dengan status 'due'
                $newEval = \App\Models\Evaluation::create([
                    'document_id' => $doc->id,
                    'evaluator_id' => $evaluatorUser ? $evaluatorUser->id : null,
                    'evaluation_period' => $period,
                    'due_date' => $dueDate,
                    'status' => 'due',
                ]);
                
                $doc->evaluation_status = 'due';
                $doc->evaluation_id = $newEval->id;
                $doc->save();
                
                // Catat di timeline / Log Dokumen
                $doc->logs()->create([
                    'user_id' => $systemUserId,
                    'action' => 'evaluation_due',
                    'notes' => 'Jatuh tempo evaluasi SOP terdeteksi. Evaluasi baru dibuat untuk periode ' . $period . '.',
                ]);
                
                // Kirim notifikasi email ke evaluator
                if ($evaluatorUser && !empty(trim($evaluatorUser->email ?? ''))) {
                    try {
                        $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                            'login.magic',
                            now()->addDays(7),
                            [
                                'user_id' => $evaluatorUser->id,
                                'document_id' => $doc->id,
                            ]
                        );
                        
                        \Illuminate\Support\Facades\Mail::to($evaluatorUser->email)->queue(
                            new \App\Mail\DocumentEvaluationDueMail($doc, $evaluatorUser, $magicLoginUrl)
                        );
                        $this->info("Email notifikasi evaluasi terkirim ke: {$evaluatorUser->email}");
                    } catch (\Throwable $e) {
                        \Log::error("Gagal mengirim email evaluasi: " . $e->getMessage());
                    }
                }
            }
        }
    }
    
    // 3. Cari evaluasi status 'due' / 'in_review' yang melewati due_date untuk dirubah ke 'overdue'
    $overdueEvals = \App\Models\Evaluation::whereIn('status', ['due', 'in_review'])
        ->where('due_date', '<', date('Y-m-d'))
        ->get();
        
    foreach ($overdueEvals as $eval) {
        $eval->status = 'overdue';
        $eval->save();
        
        $eval->document->evaluation_status = 'overdue';
        $eval->document->save();
        
        $eval->document->logs()->create([
            'user_id' => $systemUserId,
            'action' => 'evaluation_overdue',
            'notes' => 'Evaluasi SOP melewati batas waktu (Overdue).',
        ]);

        // Kirim maksimal satu email pengingat per evaluasi per hari kerja.
        $reminderAlreadyLogged = $eval->document->logs()
            ->where('action', 'evaluation_overdue_email')
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if (now()->isWeekday() && !$reminderAlreadyLogged && $eval->evaluator && !empty(trim($eval->evaluator->email ?? ''))) {
            $eval->document->logs()->create([
                'user_id' => $systemUserId,
                'action' => 'evaluation_overdue_email',
                'notes' => 'Email pengingat evaluasi overdue dimasukkan ke queue untuk dikirim kepada evaluator.',
            ]);
            try {
                $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'login.magic',
                    now()->addDays(7),
                    [
                        'user_id'     => $eval->evaluator->id,
                        'document_id' => $eval->document->id,
                    ]
                );

                \Illuminate\Support\Facades\Mail::to($eval->evaluator->email)->queue(
                    new \App\Mail\DocumentEvaluationDueMail($eval->document, $eval->evaluator, $magicLoginUrl)
                );
            } catch (\Throwable $e) {
                \Log::error("Gagal mengirim email pengingat evaluasi harian: " . $e->getMessage());
            }
        }
    }
    
    $this->info('Selesai memproses evaluasi SOP.');
})->purpose('Check active SOPs that require periodic evaluation');

/*
|--------------------------------------------------------------------------
| e-QMS Purge Obsolete Documents > 3 Years (Poin 8)
|--------------------------------------------------------------------------
*/
Artisan::command('eqms:purge-obsolete', function () {
    $this->info('Memeriksa dokumen usang (Obsolete) yang telah melewati masa retensi 3 tahun...');
    
    $threeYearsAgo = now()->subYears(3);
    $obsoleteDocs = \App\Models\Document::where('status', 'obsolete')
        ->where(function($q) use ($threeYearsAgo) {
            $q->where('obsolete_at', '<=', $threeYearsAgo)
              ->orWhere(function($sub) use ($threeYearsAgo) {
                  $sub->whereNull('obsolete_at')
                      ->where('updated_at', '<=', $threeYearsAgo);
              });
        })->get();

    $count = 0;
    foreach ($obsoleteDocs as $doc) {
        $this->line("Menghapus dokumen permanen: [{$doc->doc_number}] {$doc->title}");
        
        // Hapus file fisik dari storage jika ada
        foreach ([$doc->file_final, $doc->file_preview, $doc->file_cover, $doc->file_lp, $doc->file_isi] as $filePath) {
            if ($filePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($filePath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($filePath);
            }
        }

        // Hapus dari E-Library katalog
        \App\Models\Library::where('document_id', $doc->id)->delete();

        $doc->delete();
        $count++;
    }

    $this->info("Pembersihan selesai: {$count} dokumen usang > 3 tahun telah dihapus secara permanen.");
})->purpose('Permanently purge obsolete documents after 3 years retention period');

// Schedule tasks
Schedule::command('eqms:check-evaluations')->weekdays()->at('08:00')->withoutOverlapping(30);
Schedule::command('eqms:send-periodic-quizzes')->weekdays()->at('08:15')->withoutOverlapping(30);
Schedule::command('cpt:check-expired')->dailyAt('08:20')->withoutOverlapping(30);
Schedule::command('eqms:purge-obsolete')->daily();

/*
|--------------------------------------------------------------------------
| Test Send Single Evaluation Email
|--------------------------------------------------------------------------
*/
Artisan::command('eqms:test-eval-email {recipient : Alamat email tujuan tes} {--doc= : ID Dokumen SOP (opsional)}', function () {
    $recipient = $this->argument('recipient');
    $docId = $this->option('doc');

    $this->info("Menyiapkan pengiriman tes email evaluasi ke: {$recipient}");

    $document = $docId 
        ? \App\Models\Document::find($docId)
        : \App\Models\Document::where('status', 'active')->first();

    if (!$document) {
        $this->error('Dokumen SOP tidak ditemukan untuk dilakukan uji coba.');
        return 1;
    }

    $evaluatorUser = \App\Models\User::where('role', '!=', 'admin')->first() 
        ?: \App\Models\User::first();

    $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'login.magic',
        now()->addDays(7),
        [
            'user_id'     => $evaluatorUser->id,
            'document_id' => $document->id,
        ]
    );

    try {
        \Illuminate\Support\Facades\Mail::to($recipient)->queue(
            new \App\Mail\DocumentEvaluationDueMail($document, $evaluatorUser, $magicLoginUrl)
        );

        $this->info("✅ Email evaluasi berhasil dikirim ke: {$recipient}");
        $this->line("   - Dokumen: [{$document->doc_number}] {$document->title}");
        $this->line("   - Departemen: {$document->department}");
        $this->line("   - Evaluator Simulasi: {$evaluatorUser->full_name} ({$evaluatorUser->role})");
        $this->line("   - Magic Login Link: {$magicLoginUrl}");
        return 0;
    } catch (\Throwable $e) {
        $this->error("❌ Gagal mengirim email: " . $e->getMessage());
        return 1;
    }
})->purpose('Kirim satu email simulasi evaluasi SOP ke alamat email tertentu');

/*
|--------------------------------------------------------------------------
| Generate Dummy SOP Evaluation & Send Email to Imam M
|--------------------------------------------------------------------------
*/
Artisan::command('eqms:dummy-eval-imam {--email= : Alamat email tujuan (default: akun Imam M)}', function () {
    $this->info('🚀 Memulai pembuatan data dummy & pengiriman notifikasi evaluasi SOP ke Imam M...');

    // 1. Cari user Imam M
    $imam = \App\Models\User::where('full_name', 'like', '%imam%')
        ->orWhere('username', 'like', '%imam%')
        ->first();

    if (!$imam) {
        $this->error('User Imam M tidak ditemukan di database.');
        return 1;
    }

    $targetEmail = $this->option('email') ?: $imam->email;
    $this->line("👤 Evaluator Terpilih: {$imam->full_name} ({$imam->role})");
    $this->line("📧 Email Tujuan: {$targetEmail}");

    // 2. Buat dokumen dummy SOP HC yang jatuh tempo evaluasi
    $docNumber = 'SOP-HC-EVAL-' . date('Ymd-His');
    $effectiveDate = date('Y-m-d', strtotime('-1 year'));
    $dueDate = date('Y-m-d'); // Hari ini jatuh tempo

    $dummyDoc = \App\Models\Document::create([
        'title' => 'SOP Penilaian Kinerja & Evaluasi Kompetensi Karyawan',
        'doc_number' => $docNumber,
        'department' => 'HC',
        'status' => 'active',
        'category' => 'Support',
        'doc_revision' => '1',
        'effective_date' => $effectiveDate,
        'evaluation_due_date' => $dueDate,
        'evaluation_status' => 'due',
        'file_final' => 'documents/dummy_eval_hc.pdf',
        'file_preview' => 'documents/dummy_eval_hc.pdf',
        'file_cover' => 'documents/dummy_eval_hc.pdf',
    ]);

    // 3. Buat record Evaluasi dengan status 'due'
    $evaluation = \App\Models\Evaluation::create([
        'document_id' => $dummyDoc->id,
        'evaluator_id' => $imam->id,
        'evaluation_period' => date('Y'),
        'due_date' => $dueDate,
        'status' => 'due',
    ]);

    $dummyDoc->evaluation_id = $evaluation->id;
    $dummyDoc->save();

    // 4. Catat Log Audit Dokumen
    $dummyDoc->logs()->create([
        'user_id' => $imam->id,
        'action' => 'evaluation_due',
        'notes' => 'Jatuh tempo evaluasi berkala SOP terdeteksi. Evaluator yang ditugaskan: ' . $imam->full_name . ' (' . $imam->role . ').',
    ]);

    // 5. Generate Signed Magic Login URL langsung ke form evaluasi
    $magicLoginUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'login.magic',
        now()->addDays(7),
        [
            'user_id' => $imam->id,
            'document_id' => $dummyDoc->id,
        ]
    );

    // 6. Kirim Email Notifikasi
    try {
        \Illuminate\Support\Facades\Mail::to($targetEmail)->queue(
            new \App\Mail\DocumentEvaluationDueMail($dummyDoc, $imam, $magicLoginUrl)
        );

        $this->info("✅ BERHASIL: Dokumen dummy evaluasi SOP telah dibuat & email terkirim ke {$targetEmail}!");
        $this->line("   📄 Dokumen: [{$dummyDoc->doc_number}] {$dummyDoc->title}");
        $this->line("   🏢 Departemen: {$dummyDoc->department}");
        $this->line("   📅 Jatuh Tempo: {$dummyDoc->evaluation_due_date}");
        $this->line("   🔗 Magic Login Link: {$magicLoginUrl}");
        return 0;
    } catch (\Throwable $e) {
        $this->error("❌ Gagal mengirim email evaluasi: " . $e->getMessage());
        return 1;
    }
})->purpose('Buat data dummy SOP yang jatuh tempo evaluasi dan kirim email notifikasi ke Imam M');

/*
|--------------------------------------------------------------------------
| Helper: Ambil Seluruh Personil Sesuai Bidang/Departemen SOP (Kepala & Anggota)
|--------------------------------------------------------------------------
| Menemukan seluruh personil internal (baik Kepala Departemen, Officer, Staff,
| maupun Anggota) yang relevan dengan bidang dokumen SOP, mengecualikan DIRUT.
*/
if (!function_exists('getEqmsDepartmentUsers')) {
    function getEqmsDepartmentUsers($department) {
        $dept = strtoupper(trim($department));
        $roles = [];
        $regexKeywords = [];

        switch ($dept) {
            case 'HC':
            case 'HRD':
            case 'HUMAN CAPITAL':
                $roles = ['KA.DEPT.HC', 'Staff HC', 'Anggota HC', 'Officer HC'];
                $regexKeywords = ['\bHC\b', '\bHRD\b', '\bHUMAN CAPITAL\b'];
                break;

            case 'IT':
            case 'TEKNOLOGI INFORMASI':
                $roles = ['KA.DEPT.IT', 'Staff IT', 'Anggota IT', 'Officer IT', 'Programmer', 'IT Support'];
                $regexKeywords = ['\bIT\b', '\bTEKNOLOGI INFORMASI\b', '\bINFORMATIKA\b'];
                break;

            case 'QMS':
                $roles = ['KA.DEPT.QMS', 'Management Representative', 'Staff QMS', 'Anggota QMS', 'Officer QMS'];
                $regexKeywords = ['\bQMS\b', '\bQUALITY\b', '\bMUTU\b'];
                break;

            case 'HSE':
            case 'K3':
                $roles = ['KA.DEPT.HSE', 'Staff HSE', 'Anggota HSE', 'Officer HSE'];
                $regexKeywords = ['\bHSE\b', '\bK3\b', '\bKESELAMATAN\b'];
                break;

            case 'LEGAL':
            case 'ADMIN & LEGAL':
                $roles = ['KA.DEPT.ADMIN & LEGAL', 'Staff Legal', 'Anggota Legal', 'Officer Legal'];
                $regexKeywords = ['\bLEGAL\b'];
                break;

            case 'INTERNAL AUDIT':
            case 'INTERNAL AUDIT & RISK MANAGEMENT':
                $roles = ['KA.DEPT.INTERNAL AUDIT', 'Dept. Internal Audit', 'KA.DEPT.INTERNAL AUDIT & RISK MANAGEMENT', 'Staff Audit', 'Auditor'];
                $regexKeywords = ['\bAUDIT\b', '\bRISK\b'];
                break;

            case 'FINANCE':
            case 'KEUANGAN':
            case 'F & A':
            case 'F&A':
            case 'KEUANGAN & ACCOUNTING':
                $roles = ['KA.DEPT.F & A', 'KA.DEPT.KEUANGAN', 'Chief F&A', 'Ka. Div F&A', 'Staff Keuangan', 'Staff Finance', 'Accounting'];
                $regexKeywords = ['\bFINANCE\b', '\bKEUANGAN\b', '\bF\s*&\s*A\b', '\bACCOUNTING\b'];
                break;

            case 'LOGISTIC':
            case 'OPS':
                $roles = ['KA.DEPT.SALES & MARKETING', 'Ka. Div Retail', 'Staff Logistik', 'Staff Ops'];
                $regexKeywords = ['\bLOGISTIC\b', '\bLOGISTIK\b', '\bOPS\b', '\bOPERASIONAL\b'];
                break;

            case 'SPBU':
                $roles = ['Ka. BU SPBU', 'Ka. Div Retail', 'Wa. Ka. Div Retail', 'Staff SPBU', 'Pengawas SPBU'];
                $regexKeywords = ['\bSPBU\b'];
                break;

            case 'LPG PSO':
            case 'LPG NPSO':
            case 'PKSP':
            case 'TRP':
            case 'GAS & SPBE':
                $roles = ['Ka. BU Gas & SPBE', 'Ka. Div Retail', 'Wa. Ka. Div Retail', 'Staff SPBE', 'Staff Gas'];
                $regexKeywords = ['\bGAS\b', '\bSPBE\b', '\bLPG\b', '\bPKSP\b', '\bTRP\b'];
                break;

            case 'INMAR (CNGM)':
            case 'INMAR':
            case 'CNGM':
                $roles = ['Ka. BU Inmarr', 'Chief F & A Inmarr', 'Staff Inmar'];
                $regexKeywords = ['\bINMAR\b', '\bINMARR\b', '\bCNGM\b'];
                break;

            case 'CPT & MHM':
            case 'CPT':
            case 'SBS':
            case 'GVI':
                $roles = ['Ka. BU CPT', 'Direktur CPT', 'Staff CPT'];
                $regexKeywords = ['\bCPT\b', '\bMHM\b', '\bSBS\b', '\bGVI\b'];
                break;

            case 'PROCUREMENT':
            case 'PROCRUTMEN':
                $roles = ['KA.DEPT.PROCRUTMEN', 'Staff Procurement', 'Staff Pengadaan'];
                $regexKeywords = ['\bPROC\w*\b', '\bPENGADAAN\b'];
                break;

            case 'WAREHOUSE':
            case 'ASET':
            case 'GA':
                $roles = ['KA.DEPT.CORPORATE SEKTARIS', 'Chief of Staff', 'Staff GA', 'Staff Gudang'];
                $regexKeywords = ['\bWAREHOUSE\b', '\bGUDANG\b', '\bASET\b', '\bGA\b', '\bSEKTARIS\b'];
                break;

            default:
                $roles = ["KA.DEPT.{$dept}", "Ka. BU {$dept}", "Staff {$dept}", "Anggota {$dept}"];
                $regexKeywords = ['\b' . preg_quote($dept, '/') . '\b'];
                break;
        }

        $allUsers = \App\Models\User::where('status', true)
            ->where('role', 'not like', '%Direktur Utama%')
            ->where('role', 'not like', '%DIRUT%')
            ->get();

        $matched = $allUsers->filter(function($u) use ($roles, $regexKeywords) {
            $userRole = trim($u->role ?? '');
            $username = trim($u->username ?? '');

            if (in_array($userRole, $roles)) {
                return true;
            }

            foreach ($regexKeywords as $pattern) {
                if (preg_match('/' . $pattern . '/i', $userRole) ||
                    preg_match('/' . $pattern . '/i', $username)) {
                    return true;
                }
            }

            return false;
        });

        return $matched->values();
    }
}

/*
|--------------------------------------------------------------------------
| e-QMS Uji Pemahaman SOP Berkala 6 Bulan (Seluruh Kepala & Anggota Bidang)
|--------------------------------------------------------------------------
| Memeriksa dokumen SOP berstatus 'active', menghitung siklus 6 bulanan,
| lalu mengirimkan undangan kuis via email ber-Magic Link ke seluruh
| personil terkait di bidang dokumen tersebut (kecuali Direktur Utama).
*/
Artisan::command('eqms:send-periodic-quizzes {--force : Abaikan riwayat dan paksa kirim ulang email}', function () {
    $force = $this->option('force');
    $this->info('🚀 Memulai pengecekan kuis pemahaman SOP berkala (Siklus 6 Bulan)...');

    $activeDocs = \App\Models\Document::where('status', 'active')->get();
    $totalSent = 0;
    $totalSkipped = 0;

    foreach ($activeDocs as $doc) {
        $effectiveDate = $doc->effective_date;
        if (empty($effectiveDate)) {
            $activeLog = $doc->logs()->where('action', 'active')->first();
            $effectiveDate = $activeLog ? $activeLog->created_at->toDateString() : $doc->created_at->toDateString();
            $doc->effective_date = $effectiveDate;
            $doc->save();
        }

        $effectiveCarbon = \Carbon\Carbon::parse($effectiveDate);
        $now = now();
        $monthsPassed = $effectiveCarbon->diffInMonths($now);

        // Siklus 6-bulanan: 0-5 bln = Siklus 1, 6-11 bln = Siklus 2, dst
        $cycleIndex = floor($monthsPassed / 6);
        $cycleStart = $effectiveCarbon->copy()->addMonths($cycleIndex * 6)->startOfDay();
        $cycleEnd = $cycleStart->copy()->addMonths(6)->endOfDay();
        $cycleLabel = 'Semester ' . ($cycleIndex + 1) . ' (' . $cycleStart->format('d/m/Y') . ' - ' . $cycleEnd->format('d/m/Y') . ')';

        $relevantUsers = getEqmsDepartmentUsers($doc->department);

        if ($relevantUsers->isEmpty()) {
            $this->line("⚠️ Dokumen [{$doc->doc_number}] ({$doc->department}): Tidak ada user aktif yang cocok.");
            continue;
        }

        $this->line("📄 Memproses SOP [{$doc->doc_number}] {$doc->title} (Bidang: {$doc->department}, {$cycleLabel}):");

        foreach ($relevantUsers as $user) {
            if (empty(trim($user->email ?? ''))) {
                continue;
            }

            // Cek apakah user sudah lulus (nilai >= 60) pada siklus 6 bulan berjalan ini
            $hasPassed = \App\Models\SopQuizAttempt::where('document_id', $doc->id)
                ->where('user_id', $user->id)
                ->where('score', '>=', 60)
                ->where('created_at', '>=', $cycleStart)
                ->exists();

            if ($hasPassed && !$force) {
                $this->line("   ⏭️  {$user->full_name} ({$user->role}): Sudah lulus kuis di siklus ini.");
                $totalSkipped++;
                continue;
            }

            // Cek apakah email undangan sudah pernah dikirimkan pada siklus 6 bulan ini
            $alreadyInvited = $doc->logs()
                ->where('action', 'periodic_quiz_invitation')
                ->where('user_id', $user->id)
                ->where('created_at', '>=', $cycleStart)
                ->exists();

            if ($alreadyInvited && !$force) {
                $this->line("   ⏭️  {$user->full_name} ({$user->role}): Undangan sudah dikirim pada siklus ini.");
                $totalSkipped++;
                continue;
            }

            // Generate Temporary Signed Magic URL (7 hari) langsung membuka kuis
            $quizUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'login.magic',
                now()->addDays(7),
                [
                    'user_id' => $user->id,
                    'document_id' => $doc->id,
                    'redirect_to' => route('documents.quiz.show', $doc->id),
                ]
            );

            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->queue(
                    new \App\Mail\PeriodicSopQuizInvitationMail($doc, $user, $quizUrl)
                );

                $doc->logs()->create([
                    'user_id' => $user->id,
                    'action' => 'periodic_quiz_invitation',
                    'notes' => "Undangan kuis pemahaman SOP berkala ({$cycleLabel}) dikirim ke {$user->full_name} ({$user->role}) via email: {$user->email}",
                ]);

                $this->info("   ✅ Email kuis berhasil di-queue untuk: {$user->full_name} ({$user->role}) <{$user->email}>");
                $totalSent++;
            } catch (\Throwable $e) {
                $this->error("   ❌ Gagal mengirim email kuis ke {$user->email}: " . $e->getMessage());
            }
        }
    }

    $this->info("✨ Selesai. Total email kuis dikirim: {$totalSent}, Dilewati (sudah lulus/terkirim): {$totalSkipped}.");
    return 0;
})->purpose('Kirim undangan kuis berkala 6-bulanan ke seluruh staf dan kepala departemen terkait');

/*
|--------------------------------------------------------------------------
| Test Send Single Periodic Quiz Invitation Email
|--------------------------------------------------------------------------
*/
Artisan::command('eqms:test-periodic-quiz {recipient : Alamat email tujuan tes} {--doc= : ID Dokumen SOP (opsional)}', function () {
    $recipient = $this->argument('recipient');
    $docId = $this->option('doc');

    $this->info("🧪 Menyiapkan simulasi tes email kuis pemahaman SOP 6-bulanan ke: {$recipient}");

    $document = $docId 
        ? \App\Models\Document::find($docId)
        : \App\Models\Document::where('status', 'active')->first();

    if (!$document) {
        $this->error('Dokumen SOP aktif tidak ditemukan untuk dilakukan pengujian.');
        return 1;
    }

    // Ambil personil departemen atau fallback user biasa
    $deptUsers = getEqmsDepartmentUsers($document->department);
    $simulatedUser = $deptUsers->first() ?: \App\Models\User::where('role', '!=', 'Direktur Utama')->first();

    $quizUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'login.magic',
        now()->addDays(7),
        [
            'user_id' => $simulatedUser->id,
            'document_id' => $document->id,
            'redirect_to' => route('documents.quiz.show', $document->id),
        ]
    );

    try {
        \Illuminate\Support\Facades\Mail::to($recipient)->queue(
            new \App\Mail\PeriodicSopQuizInvitationMail($document, $simulatedUser, $quizUrl)
        );

        $this->info("✅ Email simulasi kuis berkala berhasil dikirim ke: {$recipient}");
        $this->line("   - Dokumen: [{$document->doc_number}] {$document->title}");
        $this->line("   - Bidang/Departemen: {$document->department}");
        $this->line("   - Personil Simulasi: {$simulatedUser->full_name} ({$simulatedUser->role})");
        $this->line("   - Magic Quiz Link: {$quizUrl}");
        return 0;
    } catch (\Throwable $e) {
        $this->error("❌ Gagal mengirim email simulasi: " . $e->getMessage());
        return 1;
    }
})->purpose('Kirim satu email simulasi kuis berkala 6-bulanan ke alamat email tertentu');

/*
|--------------------------------------------------------------------------
| Test Send Single WhatsApp Notification (Fonnte)
|--------------------------------------------------------------------------
*/
Artisan::command('eqms:test-wa {number : Nomor WhatsApp tujuan (misal: 081234567890)} {--message= : Pesan teks uji coba}', function () {
    $number = $this->argument('number');
    $message = $this->option('message') ?: "Halo! Ini adalah pesan uji coba integrasi WhatsApp API Fonnte dari sistem e-QMS PT PKM Group pada " . date('d/m/Y H:i:s') . ".";

    $this->info("📲 Menguji pengiriman pesan WhatsApp ke: {$number}");

    /** @var \App\Services\WhatsAppService $waService */
    $waService = app(\App\Services\WhatsAppService::class);
    $normalized = $waService->normalize($number);

    if (!$normalized) {
        $this->error("❌ Format nomor telepon tidak valid. Gunakan format 08xx atau 628xx.");
        return 1;
    }

    $this->line("   - Nomor Terformat: {$normalized}");
    $this->line("   - Pesan: {$message}");

    try {
        $success = $waService->send($number, $message);
        if ($success) {
            $this->info("✅ Pesan WhatsApp berhasil terkirim melalui Fonnte Gateway!");
            return 0;
        } else {
            $this->error("❌ Pengiriman gagal. Silakan periksa status perangkat di dashboard Fonnte.");
            return 1;
        }
    } catch (\Throwable $e) {
        $this->error("❌ Exception: " . $e->getMessage());
        return 1;
    }
})->purpose('Kirim pesan uji coba notifikasi WhatsApp via Fonnte API Gateway');

