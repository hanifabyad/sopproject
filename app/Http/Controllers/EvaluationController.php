<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Evaluation;
use App\Models\User;
use App\Mail\EvaluationSubmittedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EvaluationController extends Controller
{
    /**
     * Helper to get responsible departments for a given user role.
     */
    private function getDepartmentsForRole(string $role): array
    {
        switch ($role) {
            case 'KA.DEPT.HC': return ['HC'];
            case 'KA.DEPT.IT': return ['IT'];
            case 'KA.DEPT.QMS':
            case 'Management Representative': return ['QMS'];
            case 'KA.DEPT.HSE': return ['HSE'];
            case 'KA.DEPT.ADMIN & LEGAL': return ['LEGAL', 'ADMIN & LEGAL'];
            case 'KA.DEPT.INTERNAL AUDIT':
            case 'Dept. Internal Audit':
            case 'KA.DEPT.INTERNAL AUDIT & RISK MANAGEMENT': return ['INTERNAL AUDIT', 'INTERNAL AUDIT & RISK MANAGEMENT'];
            case 'KA.DEPT.F & A':
            case 'KA.DEPT.KEUANGAN': return ['FINANCE', 'KEUANGAN', 'F & A'];
            case 'KA.DEPT.SALES & MARKETING': return ['LOGISTIC', 'OPS'];
            case 'Ka. BU SPBU': return ['SPBU'];
            case 'Ka. BU Gas & SPBE': return ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'];
            case 'Ka. BU Inmarr':
            case 'Chief F & A Inmarr': return ['INMAR (CNGM)'];
            case 'Ka. BU CPT':
            case 'Direktur CPT': return ['CPT & MHM', 'SBS', 'GVI'];
            case 'KA.DEPT.PROCRUTMEN': return ['PROCUREMENT'];
            case 'KA.DEPT.CORPORATE SEKTARIS': return ['WAREHOUSE', 'ASET', 'GA'];
            case 'Chief of Staff': return ['WAREHOUSE', 'ASET', 'GA', 'HC', 'IT', 'QMS', 'HSE'];
            case 'Chief F&A':
            case 'Ka. Div F&A': return ['FINANCE', 'KEUANGAN & ACCOUNTING', 'KEUANGAN', 'F & A'];
            case 'Ka. Div Retail':
            case 'Wa. Ka. Div Retail': return ['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'LOGISTIC', 'OPS'];
            case 'Direktur Utama': return ['HC', 'IT', 'QMS', 'HSE', 'LEGAL', 'INTERNAL AUDIT', 'FINANCE', 'LOGISTIC', 'OPS', 'SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'INMAR (CNGM)', 'CPT & MHM', 'SBS', 'GVI', 'PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA'];
            default:
                if (stripos($role, 'HC') !== false || stripos($role, 'Human') !== false) return ['HC'];
                if (stripos($role, 'IT') !== false) return ['IT'];
                if (stripos($role, 'QMS') !== false) return ['QMS'];
                if (stripos($role, 'HSE') !== false) return ['HSE'];
                if (stripos($role, 'SPBU') !== false) return ['SPBU'];
                if (stripos($role, 'LPG') !== false || stripos($role, 'Gas') !== false) return ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'];
                if (stripos($role, 'Inmar') !== false) return ['INMAR (CNGM)'];
                if (stripos($role, 'CPT') !== false) return ['CPT & MHM', 'SBS', 'GVI'];
                return [];
        }
    }

    /**
     * Evaluator: Daftar tugas evaluasi SOP yang didelegasikan.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.evaluations.index');
        }

        $depts = $this->getDepartmentsForRole($user->role);

        // Tampilkan seluruh tugas evaluasi SOP yang aktif / berjalan
        $evaluationsQuery = Evaluation::whereHas('document', function ($q) {
            $q->where('status', 'active');
        })->with('document');

        // Jika user memiliki pemetaan departemen spesifik, filter atau utamakan departemennya
        if (!empty($depts)) {
            $evaluationsQuery->where(function ($query) use ($user, $depts) {
                $query->where('evaluator_id', $user->id)
                      ->orWhereHas('document', function ($q) use ($user, $depts) {
                          $q->whereIn('department', $depts)
                            ->orWhereHas('approvals', function($aq) use ($user) {
                                $aq->where('user_id', $user->id);
                            });
                      });
            });
        }

        $evaluations = $evaluationsQuery
            ->orderByRaw("FIELD(status, 'overdue', 'due', 'in_review', 'submitted', 'completed')")
            ->orderBy('due_date', 'asc')
            ->get();

        return view('evaluator.evaluations.index', compact('evaluations'));
    }

    /**
     * Evaluator: Halaman pengisian form evaluasi.
     */
    public function show(int $id)
    {
        $user = Auth::user();
        $evaluation = Evaluation::with('document.logs')->findOrFail($id);
        $document = $evaluation->document;

        if ((int) $evaluation->evaluator_id !== (int) $user->id) {
            abort(403);
        }

        // Mulai evaluasi jika statusnya 'due' atau 'overdue'
        if (in_array($evaluation->status, ['due', 'overdue'])) {
            $evaluation->update([
                'status' => 'in_review',
                'started_at' => now(),
            ]);
            $document->update([
                'evaluation_status' => 'in_review'
            ]);
        }

        return view('evaluator.evaluations.show', compact('evaluation', 'document'));
    }

    /**
     * Evaluator: Mengirimkan form tanggapan evaluasi.
     */
    public function submit(Request $request, int $id)
    {
        $user = Auth::user();
        $evaluation = Evaluation::findOrFail($id);
        $document = $evaluation->document;

        // Validasi Form
        $request->validate([
            'usage_status'          => 'required|string',
            'usage_reason'          => 'nullable|string',
            'conformity_status'     => 'required|string',
            'conformity_notes'      => 'nullable|string',
            'process_change_status' => 'required|string',
            'process_change_notes'  => 'nullable|string',
            'effectiveness_status'  => 'required|string',
            'implementation_issues'  => 'nullable|string',
            'recommendation'        => 'nullable|string',
            'result'                => 'required|string|in:CONTINUE,REVISION REQUIRED,NOT USED,OBSOLETE',
        ]);

        $evaluation->update([
            'evaluator_id'          => $user->id,
            'usage_status'          => $request->usage_status,
            'usage_reason'          => $request->usage_reason,
            'conformity_status'     => $request->conformity_status,
            'conformity_notes'      => $request->conformity_notes,
            'process_change_status' => $request->process_change_status,
            'process_change_notes'  => $request->process_change_notes,
            'effectiveness_status'  => $request->effectiveness_status,
            'implementation_issues'  => $request->implementation_issues,
            'recommendation'        => $request->recommendation,
            'result'                => $request->result,
            'status'                => 'submitted',
            'submitted_at'          => now(),
        ]);

        $document->update([
            'evaluation_status' => 'submitted'
        ]);

        if ($request->result === 'CONTINUE') {
            $nextDueDate = date('Y-m-d', strtotime(($evaluation->due_date ? $evaluation->due_date->toDateString() : date('Y-m-d')) . ' +1 year'));
            try {
                $this->stampEvaluationContinued($document, $evaluation, $nextDueDate);
            } catch (\Throwable $e) {
                \Log::error("Gagal membubuhkan stempel evaluasi CONTINUE di submit: " . $e->getMessage());
            }
        }

        $document->logs()->create([
            'user_id' => $user->id,
            'action'  => 'evaluation_submitted',
            'notes'   => 'Evaluasi SOP berhasil di-submit dengan kesimpulan: ' . $request->result . '.',
        ]);

        // Kirim Notifikasi Email ke Admin QMS dengan Magic Login
        try {
            $adminUsers = User::where('role', 'admin')->whereNotNull('email')->get();
            foreach ($adminUsers as $admin) {
                if (!empty(trim($admin->email))) {
                    $actionUrl = URL::temporarySignedRoute(
                        'login.magic',
                        now()->addDays(7),
                        [
                            'user_id'     => $admin->id,
                            'redirect_to' => route('admin.evaluations.show', $evaluation->id),
                        ]
                    );

                    Mail::to($admin->email)->queue(
                        new EvaluationSubmittedMail($document, $evaluation, $user, $actionUrl)
                    );
                }
            }
        } catch (\Throwable $e) {
            \Log::error("Gagal mengirim email notifikasi hasil evaluasi ke Admin: " . $e->getMessage());
        }

        return redirect()->route('reviewer.dashboard')->with('success', 'Evaluasi SOP berhasil dikirim ke Admin QMS.');
    }

    /**
     * Admin: Halaman daftar semua evaluasi SOP.
     */
    public function adminIndex(Request $request)
    {
        $status = $request->input('status');
        $result = $request->input('result');
        $search = $request->input('search');

        $query = Evaluation::with('document');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($result)) {
            $query->where('result', $result);
        }

        if (!empty($search)) {
            $query->whereHas('document', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('doc_number', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $evaluations = $query->orderBy('due_date', 'asc')->get();

        return view('admin.evaluations.index', compact('evaluations', 'status', 'result', 'search'));
    }

    /**
     * Admin: Halaman detail & review evaluasi SOP.
     */
    public function adminShow(int $id)
    {
        $evaluation = Evaluation::with(['document', 'evaluator'])->findOrFail($id);
        $document = $evaluation->document;
        return view('admin.evaluations.show', compact('evaluation', 'document'));
    }

    /**
     * Admin: Tindak lanjut & penyelesaian hasil evaluasi.
     */
    public function adminResolve(Request $request, int $id)
    {
        $request->validate([
            'result'      => 'required|string|in:CONTINUE,REVISION REQUIRED,NOT USED,OBSOLETE',
            'admin_notes' => 'nullable|string',
        ]);

        $evaluation = Evaluation::findOrFail($id);
        $document = $evaluation->document;

        DB::transaction(function () use ($request, $evaluation, $document) {
            $result = $request->result;

            $evaluation->update([
                'admin_id'          => Auth::id(),
                'admin_reviewed_at' => now(),
                'admin_notes'       => $request->admin_notes,
                'result'            => $result,
                'status'            => 'completed',
            ]);

            if ($result === 'CONTINUE') {
                // SOP tetap aktif, jadwalkan evaluasi tahun depan
                $nextDueDate = date('Y-m-d', strtotime($evaluation->due_date->toDateString() . ' +1 year'));
                
                $document->update([
                    'status'             => 'active',
                    'evaluation_status'  => 'completed',
                    'evaluation_due_date' => $nextDueDate,
                ]);

                // Berikan stempel evaluasi CONTINUE di pojok kiri bawah Lembar Pengesahan
                try {
                    $this->stampEvaluationContinued($document, $evaluation, $nextDueDate);
                } catch (\Throwable $e) {
                    \Log::error("Gagal membubuhkan stempel evaluasi CONTINUE: " . $e->getMessage());
                }

                $document->logs()->create([
                    'user_id' => Auth::id(),
                    'action'  => 'evaluation_completed',
                    'notes'   => 'Admin QMS menyelesaikan evaluasi. Tindak lanjut: Dokumen tetap digunakan (CONTINUE). Stempel evaluasi berkala dibubuhkan pada Lembar Pengesahan. Evaluasi berikutnya: ' . date('d F Y', strtotime($nextDueDate)) . '.',
                ]);

            } elseif ($result === 'REVISION REQUIRED') {
                // SOP perlu direvisi. Ubah status dokumen menjadi 'need_revision' agar creator bisa edit
                $document->update([
                    'status'            => 'need_revision',
                    'evaluation_status' => 'completed',
                    'evaluation_id'     => $evaluation->id,
                ]);

                $document->logs()->create([
                    'user_id' => Auth::id(),
                    'action'  => 'evaluation_completed',
                    'notes'   => 'Admin QMS menyelesaikan evaluasi. Tindak lanjut: Diharuskan revisi (REVISION REQUIRED). Dokumen masuk status need_revision.',
                ]);

            } else {
                // NOT USED atau OBSOLETE. Dokumen digolongkan kedalam obsolete
                $document->update([
                    'status'            => 'obsolete',
                    'evaluation_status' => 'completed',
                ]);

                $document->logs()->create([
                    'user_id' => Auth::id(),
                    'action'  => 'evaluation_completed',
                    'notes'   => 'Admin QMS menyelesaikan evaluasi. Tindak lanjut: Dokumen dinonaktifkan (' . $result . '). Status diubah menjadi obsolete.',
                ]);
            }
        });

        return redirect()->route('admin.evaluations.index')->with('success', 'Tindak lanjut evaluasi SOP berhasil disimpan.');
    }

    /**
     * Helper: Membubuhkan stempel evaluasi berkala (CONTINUE) di pojok kiri bawah Lembar Pengesahan.
     */
    private function stampEvaluationContinued(Document $document, Evaluation $evaluation, string $nextDueDate): void
    {
        $sourceRel = $document->file_final ?? $document->file_preview ?? $document->file_lp;
        if (empty($sourceRel)) {
            return;
        }

        // Hindari menimpa ulang jika file_final sudah memiliki stempel evaluasi sesi ini
        if (str_contains($sourceRel, 'EVAL_CONTINUED_' . $document->id . '_')) {
            return;
        }

        $sourcePath = storage_path('app/public/' . str_replace('\\', '/', $sourceRel));
        if (!file_exists($sourcePath)) {
            return;
        }

        $tempFileToClean = null;
        $pdf = new \setasign\Fpdi\Fpdi();

        try {
            $pageCount = $pdf->setSourceFile($sourcePath);
        } catch (\Throwable $e) {
            $normalized = $this->normalizePdfWithQpdf($sourcePath);
            if ($normalized) {
                $tempFileToClean = $normalized;
                $pageCount = $pdf->setSourceFile($normalized);
            } else {
                throw $e;
            }
        }

        try {
            // Target LP page: if multiple pages, LP is page 2 (Page 1 = Cover, Page 2 = LP)
            $lpPageTarget = ($pageCount > 1) ? 2 : 1;

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                if ($pageNo === $lpPageTarget) {
                    $stampWidth = 38.0;
                    $stampHeight = 14.0;

                    // X Coordinate: Kiri di DALAM border LP sejajar tabel (X = 18.0 mm)
                    $x = 18.0;

                    // Y Coordinate: PAS DI BAWAH TABEL PENGESAHAN (tidak terlalu bawah)
                    // Ambil koordinat tanda tangan terbawah (final approver) pada dokumen
                    $maxSigY = \App\Models\DocumentApproval::where('document_id', $document->id)
                        ->whereNotNull('signature_y')
                        ->max('signature_y');

                    if ($maxSigY && $maxSigY > 100.0) {
                        $y = (float)$maxSigY + 22.0;
                    } else {
                        $y = 198.0;
                    }

                    // Pastikan tidak menabrak tabel riwayat revisi atau footer di bawah (240mm)
                    if ($y > 220.0) {
                        $y = 220.0;
                    }

                    $pdf->SetAutoPageBreak(false);

                    // 1. Gambar Bingkai Stempel Hijau Zamrud (Emerald Green)
                    $pdf->SetDrawColor(5, 150, 105);
                    $pdf->SetLineWidth(0.4);
                    $pdf->Rect($x, $y, $stampWidth, $stampHeight);

                    // 2. Baris 1 (Atas): "REVIEWED"
                    $pdf->SetTextColor(4, 120, 87);
                    $pdf->SetFont('Arial', 'B', 10.0);
                    $pdf->SetXY($x, $y + 2.5);
                    $pdf->Cell($stampWidth, 4.5, 'REVIEWED', 0, 0, 'C');

                    // 3. Baris 2 (Bawah): Tanggal Evaluasi (Contoh: "03 SEP 2026")
                    $evalDateStr = strtoupper(now()->locale('en')->format('d M Y'));
                    $pdf->SetTextColor(30, 41, 59);
                    $pdf->SetFont('Arial', 'B', 8.0);
                    $pdf->SetXY($x, $y + 7.8);
                    $pdf->Cell($stampWidth, 4.0, $evalDateStr, 0, 0, 'C');

                    $pdf->SetAutoPageBreak(true, 20.0);
                }
            }

            $fileName = 'EVAL_CONTINUED_' . $document->id . '_' . time() . '.pdf';
            $newRelPath = 'documents/final/' . $fileName;
            $newAbsPath = storage_path('app/public/' . $newRelPath);

            if (!file_exists(dirname($newAbsPath))) {
                mkdir(dirname($newAbsPath), 0777, true);
            }

            $pdf->Output('F', $newAbsPath);

            $oldFinal = $document->file_final;
            $document->update([
                'file_final'   => $newRelPath,
                'file_preview' => $newRelPath,
            ]);

            // Sinkronisasi E-Library jika ada
            if ($oldFinal) {
                \App\Models\Library::where('file_path', $oldFinal)->update(['file_path' => $newRelPath]);
            }
        } finally {
            if ($tempFileToClean && file_exists($tempFileToClean)) {
                @unlink($tempFileToClean);
            }
        }
    }

    /**
     * Fallback helper: Normalisasi PDF via QPDF jika FPDI melempar compression error
     */
    private function normalizePdfWithQpdf(string $inputPath): ?string
    {
        $qpdfBin = config('app.qpdf_path', 'qpdf');
        if ((str_starts_with($qpdfBin, '/') || preg_match('/^[a-zA-Z]:\\\\/', $qpdfBin)) && !file_exists($qpdfBin)) {
            return null;
        }

        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qpdf_norm_' . uniqid() . '.pdf';
        $cmd = sprintf(
            '%s %s --object-streams=disable --force-version=1.4 %s 2>&1',
            escapeshellarg($qpdfBin),
            escapeshellarg($inputPath),
            escapeshellarg($tempPath)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode === 0 && file_exists($tempPath) && filesize($tempPath) > 0) {
            return $tempPath;
        }

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        return null;
    }

    /**
     * Mengalirkan file PDF secara aman untuk pratinjau evaluasi.
     */
    public function streamFile(int $id)
    {
        $evaluation = Evaluation::findOrFail($id);
        $document = $evaluation->document;
        $user = Auth::user();

        $relativeFile = $document->file_final ?? $document->file_preview ?? $document->file_lp;
        $path = null;

        if (!empty($relativeFile)) {
            $relativeFile = str_replace('\\', '/', $relativeFile);
            $targetPath = storage_path('app/public/' . $relativeFile);
            if (is_file($targetPath)) {
                $path = $targetPath;
            }
        }

        // Fallback ke dummy.pdf jika file belum ada (misal pada data dummy testing)
        if (!$path && is_file(storage_path('app/public/dummy.pdf'))) {
            $path = storage_path('app/public/dummy.pdf');
        }

        if (!$path || !is_file($path)) {
            abort(404, 'Berkas PDF SOP tidak ditemukan pada penyimpanan server.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
        ]);
    }
}
