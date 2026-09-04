<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\Library;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminRecycleBinController extends Controller
{
    /**
     * Tampilkan daftar dokumen obsolete dalam masa retensi 3 tahun.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $department = $request->query('department');

        $query = Document::where('status', 'obsolete');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('doc_number', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($department && $department !== 'all') {
            $query->where('department', $department);
        }

        $documents = $query->latest('updated_at')->paginate(15)->withQueryString();

        // Ambil daftar unik departemen dari dokumen obsolete
        $departments = Document::where('status', 'obsolete')->distinct()->pluck('department')->filter()->values();

        // Hitung statistik
        $totalObsolete = Document::where('status', 'obsolete')->count();
        $dueForPurgeCount = Document::where('status', 'obsolete')
            ->where('updated_at', '<=', now()->subYears(3))
            ->count();

        return view('admin.recycle_bin.index', compact(
            'documents',
            'search',
            'department',
            'departments',
            'totalObsolete',
            'dueForPurgeCount'
        ));
    }

    /**
     * Pulihkan dokumen kembali ke status Aktif.
     */
    public function restore($id)
    {
        $doc = Document::findOrFail($id);

        if ($doc->status !== 'obsolete') {
            return back()->with('error', 'Dokumen ini tidak berstatus Obsolete.');
        }

        $doc->status = 'active';
        $doc->save();

        // Catat Audit Log
        DocumentLog::create([
            'document_id' => $doc->id,
            'user_id'     => Auth::id(),
            'action'      => 'pulihkan',
            'notes'       => 'Dokumen dipulihkan dari Recycle Bin & Masa Retensi kembali ke status Aktif.',
        ]);

        return back()->with('success', "Dokumen [{$doc->doc_number}] {$doc->title} berhasil dipulihkan kembali ke status Aktif.");
    }

    /**
     * Hapus permanen satu dokumen beserta berkas fisiknya.
     */
    public function forceDelete($id)
    {
        $doc = Document::findOrFail($id);

        $docTitle = $doc->title;
        $docNum = $doc->doc_number ?? '-';

        // 1. Hapus file fisik dari storage
        foreach ([$doc->file_final, $doc->file_preview, $doc->file_cover, $doc->file_lp, $doc->file_isi] as $filePath) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
        }

        // 2. Hapus entri dari E-Library jika ada
        if (!empty($doc->doc_number)) {
            Library::where('doc_number', $doc->doc_number)->delete();
        }
        if (!empty($doc->file_final)) {
            Library::where('file_path', $doc->file_final)->delete();
        }
        Library::where('title', $doc->title)->delete();

        // 3. Hapus relasi & record dokumen
        $doc->approvals()->delete();
        $doc->logs()->delete();
        $doc->attachments()->delete();
        \App\Models\DocumentSocialization::where('document_id', $doc->id)->delete();
        \App\Models\RevisionRequest::where('document_id', $doc->id)->delete();
        \App\Models\Evaluation::where('document_id', $doc->id)->delete();
        \App\Models\SopQuizAttempt::where('document_id', $doc->id)->delete();
        \App\Models\SopQuiz::where('document_id', $doc->id)->delete();
        $doc->delete();

        return back()->with('success', "Dokumen [{$docNum}] {$docTitle} telah dihapus secara permanen dari sistem.");
    }

    /**
     * Bersihkan seluruh dokumen yang sudah melewati masa retensi 3 tahun.
     */
    public function purgeAllDue()
    {
        $expiredDocs = Document::where('status', 'obsolete')
            ->where('updated_at', '<=', now()->subYears(3))
            ->get();

        $count = 0;
        foreach ($expiredDocs as $doc) {
            foreach ([$doc->file_final, $doc->file_preview, $doc->file_cover, $doc->file_lp, $doc->file_isi] as $filePath) {
                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
            if (!empty($doc->doc_number)) {
                Library::where('doc_number', $doc->doc_number)->delete();
            }
            if (!empty($doc->file_final)) {
                Library::where('file_path', $doc->file_final)->delete();
            }
            Library::where('title', $doc->title)->delete();

            $doc->approvals()->delete();
            $doc->logs()->delete();
            $doc->attachments()->delete();
            \App\Models\DocumentSocialization::where('document_id', $doc->id)->delete();
            \App\Models\RevisionRequest::where('document_id', $doc->id)->delete();
            \App\Models\Evaluation::where('document_id', $doc->id)->delete();
            \App\Models\SopQuizAttempt::where('document_id', $doc->id)->delete();
            \App\Models\SopQuiz::where('document_id', $doc->id)->delete();
            $doc->delete();
            $count++;
        }

        return back()->with('success', "Pembersihan selesai: {$count} dokumen usang yang melewati retensi 3 tahun telah dihapus permanen.");
    }
}
