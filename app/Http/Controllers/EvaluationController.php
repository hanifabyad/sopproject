<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            case 'Ka. BU Inmarr': return ['INMAR (CNGM)'];
            case 'Ka. BU CPT':
            case 'Direktur CPT': return ['CPT & MHM', 'SBS', 'GVI'];
            case 'KA.DEPT.PROCRUTMEN': return ['PROCUREMENT'];
            case 'KA.DEPT.CORPORATE SEKTARIS': return ['WAREHOUSE', 'ASET', 'GA'];
            case 'Chief of Staff': return ['WAREHOUSE', 'ASET', 'GA'];
            case 'Chief F&A':
            case 'Ka. Div F&A': return ['FINANCE', 'KEUANGAN & ACCOUNTING'];
            case 'Ka. Div Retail':
            case 'Wa. Ka. Div Retail': return ['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'LOGISTIC', 'OPS'];
            default: return [];
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

        $evaluations = Evaluation::where(function ($query) use ($user, $depts) {
            $query->where('evaluator_id', $user->id)
                  ->orWhereHas('document', function ($q) use ($depts) {
                      $q->whereIn('department', $depts);
                  });
        })
        ->with('document')
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

        // Otorisasi Akses Evaluator
        if ($user->role !== 'admin') {
            $depts = $this->getDepartmentsForRole($user->role);
            $isAssigned = ($evaluation->evaluator_id === $user->id);
            if (!in_array($document->department, $depts, true) && !$isAssigned) {
                abort(403, 'Anda tidak memiliki wewenang untuk mengevaluasi SOP departemen ini.');
            }
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

        // Otorisasi Akses Evaluator
        if ($user->role !== 'admin') {
            $depts = $this->getDepartmentsForRole($user->role);
            $isAssigned = ($evaluation->evaluator_id === $user->id);
            if (!in_array($document->department, $depts, true) && !$isAssigned) {
                abort(403, 'Anda tidak memiliki wewenang untuk mengevaluasi SOP departemen ini.');
            }
        }

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

        $document->logs()->create([
            'user_id' => $user->id,
            'action'  => 'evaluation_submitted',
            'notes'   => 'Evaluasi SOP berhasil di-submit dengan kesimpulan: ' . $request->result . '.',
        ]);

        return redirect()->route('reviewer.dashboard')->with('success', 'Evaluasi SOP berhasil dikirim ke Admin QMS.');
    }

    /**
     * Admin: Halaman daftar semua evaluasi SOP.
     */
    public function adminIndex(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $query = Evaluation::with('document');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->whereHas('document', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('doc_number', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $evaluations = $query->orderBy('due_date', 'asc')->get();

        return view('admin.evaluations.index', compact('evaluations', 'status', 'search'));
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

                $document->logs()->create([
                    'user_id' => Auth::id(),
                    'action'  => 'evaluation_completed',
                    'notes'   => 'Admin QMS menyelesaikan evaluasi. Tindak lanjut: Dokumen tetap digunakan (CONTINUE). Evaluasi berikutnya dijadwalkan pada ' . date('d F Y', strtotime($nextDueDate)) . '.',
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
     * Mengalirkan file PDF secara aman untuk pratinjau evaluasi.
     */
    public function streamFile(int $id)
    {
        $evaluation = Evaluation::findOrFail($id);
        $document = $evaluation->document;
        $user = Auth::user();

        // Otorisasi Akses Evaluator
        if ($user->role !== 'admin') {
            $depts = $this->getDepartmentsForRole($user->role);
            if (!in_array($document->department, $depts, true)) {
                abort(403, 'Unauthorized access to this document.');
            }
        }

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
