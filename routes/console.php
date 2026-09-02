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
                        
                        \Illuminate\Support\Facades\Mail::to($evaluatorUser->email)->send(
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
    }
    
    $this->info('Selesai memproses evaluasi SOP.');
})->purpose('Check active SOPs that require periodic evaluation');

// Schedule the task daily
Schedule::command('eqms:check-evaluations')->daily();

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
        \Illuminate\Support\Facades\Mail::to($recipient)->send(
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
        \Illuminate\Support\Facades\Mail::to($targetEmail)->send(
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


