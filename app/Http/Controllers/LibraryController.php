<?php

namespace App\Http\Controllers;

use App\Models\Library;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Document; 

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil parameter dari URL
        $category = $request->category;
        $div      = $request->div;
        $bu       = $request->bu;
        $company  = $request->company;
        $search   = $request->search;
        $year     = $request->year;

        $query = Library::query();

        // Standardize div parameter name (COMMERCIAL <-> KOMERSIL)
        if ($div === 'COMMERCIAL') {
            $div = 'KOMERSIL';
        }

        // 2. Logika Filter Dokumen (Agar dokumen muncul sesuai level)
        if ($company) {
            $query->where('company_name', $company);
        } elseif ($bu) {
            if ($category) {
                $query->where('category', $category);
            }
            if (str_contains(strtoupper($bu), 'INMAR')) {
                $query->where(function($q) {
                    $q->where('business_unit', 'like', '%INMAR%');
                });
            } else {
                $query->where('business_unit', $bu);
            }
        } elseif ($div) {
            $divUpper = strtoupper($div);
            if ($divUpper === 'KOMERSIL' || $divUpper === 'COMMERCIAL') {
                $query->whereIn('division_name', ['KOMERSIL', 'COMMERCIAL']);
            } else {
                $query->where('division_name', $div);
            }
        } elseif ($category) {
            $query->where('category', $category);
        }

        // 3. Pencarian Kata Kunci di Katalog Library
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('business_unit', 'like', "%{$search}%")
                  ->orWhere('division_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('support_type', 'like', "%{$search}%");
            });
        }

        // 4. Filter Tahun
        if ($year && $year !== 'all') {
            $query->whereYear('created_at', $year);
        }

        $documents = $query->latest()->get();

        // Pencarian berkas di folder general jika ada search query
        $matchingFiles = collect([]);
        if ($search) {
            $matchingFiles = \App\Models\LibraryFile::with('folder')
                ->where('name', 'like', "%{$search}%")
                ->latest()
                ->get();
        }

        // 5. Ambil data Divisi Business Unit Baku (RETAIL, KOMERSIL, SCM, FA)
        $listDivisions = ['RETAIL', 'KOMERSIL', 'SCM', 'FA'];

        // Map Business Units per Division
        $divBuMap = [
            'RETAIL'     => ['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'INMAR (CNGM)'],
            'KOMERSIL'   => ['CPT & MHM', 'SBS', 'GVI'],
            'COMMERCIAL' => ['CPT & MHM', 'SBS', 'GVI'],
            'SCM'        => ['PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA'],
            'FA'         => ['KEUANGAN & ACCOUNTING'],
        ];

        // Daftar Departemen Support Lengkap
        $supportDepts = ['HC', 'IT', 'HSE', 'QMS', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];

        // Ambil folder utama General Library
        $generalFolders = \App\Models\LibraryFolder::whereNull('parent_id')
            ->whereNull('department')
            ->whereNull('business_unit')
            ->whereNull('division')
            ->orderBy('name')
            ->get();

        // Ambil folder khusus Departemen / BU jika sedang berada di dalam scope
        $departmentFolders = collect([]);
        if ($category || $div || $bu) {
            $departmentFolders = \App\Models\LibraryFolder::whereNull('parent_id')
                ->where(function($q) use ($category, $div, $bu) {
                    if ($category === 'support' && $div) {
                        $q->where('department', $div)->orWhere('name', 'like', "%{$div}%");
                    } elseif ($bu) {
                        $q->where('business_unit', $bu)->orWhere('name', 'like', "%{$bu}%");
                    } elseif ($div) {
                        $q->where('division', $div)->orWhere('name', 'like', "%{$div}%");
                    } else {
                        $q->where('category', $category);
                    }
                })
                ->with(['children', 'files'])
                ->orderBy('name')
                ->get();
        }

        // Ambil tahun yang tersedia di katalog library
        $availableYears = Library::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values();

        return view('library.index', compact(
            'documents', 'category', 'div', 'bu', 'company', 
            'listDivisions', 'divBuMap', 'supportDepts', 'generalFolders', 'departmentFolders',
            'search', 'year', 'availableYears', 'matchingFiles'
        ));
    }

    public function storeManual(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'file'  => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip|max:25600',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Jika memilih folder general tertentu
            if ($request->filled('folder_id')) {
                $folder = \App\Models\LibraryFolder::find($request->folder_id);
                if ($folder) {
                    $path = $file->storeAs('library_general', $fileName, 'public');
                    \App\Models\LibraryFile::create([
                        'folder_id'   => $folder->id,
                        'name'        => $request->title ?: $file->getClientOriginalName(),
                        'path'        => $path,
                        'mime_type'   => $file->getClientMimeType(),
                        'size'        => $file->getSize(),
                        'uploaded_by' => auth()->id(),
                    ]);

                    return back()->with('success', "Berkas berhasil diunggah ke folder '{$folder->name}'!");
                }
            }

            $path = $file->storeAs('library_manual', $fileName, 'public');

            // Ambil nama PT default berdasarkan Unit Bisnis jika kosong
            $companyName = $request->company_name;
            if (empty($companyName)) {
                if (in_array($request->business_unit, ['SPBU', 'LPG PSO', 'LPG NPSO'])) {
                    $companyName = 'PT. LINTAS BINTAN SAMUDERA';
                } else {
                    $companyName = 'PT. CAHAYA PERDANA TRANSALAM';
                }
            }

            Library::create([
                'title'         => $request->title,
                'category'      => $request->category ?: 'divisi',
                'division_name' => $request->division_name,
                'business_unit' => $request->business_unit,
                'support_type'  => $request->support_type,
                'company_name'  => $companyName,
                'file_path'     => $path,
                'uploaded_by'   => auth()->id(),
            ]);

            return back()->with('success', 'Dokumen berhasil diunggah ke katalog E-Library!');
        }

        return back()->with('error', 'Gagal mengupload berkas.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // 1. CARI TARGET PADA MODEL LIBRARY (Bukan Document!)
        $libraryCard = Library::findOrFail($id);

        // 2. HAPUS FILE FISIK DI STORAGE LOKAL (Gunakan kolom file_path milik tabel libraries)
        if ($libraryCard->file_path && Storage::disk('public')->exists($libraryCard->file_path)) {
            Storage::disk('public')->delete($libraryCard->file_path);
        }

        // 3. HAPUS DATA DARI DATABASE LIBRARIES
        $libraryCard->delete();

        return redirect()->back()->with('success', 'Dokumen Sah berhasil dihapus permanen dari sistem E-Library!');
    }

    // ==========================================
    // NEW METHODS FOR GENERAL HIERARCHICAL LIBRARY
    // ==========================================

    public function showFolder($id)
    {
        $folder = \App\Models\LibraryFolder::with(['children', 'files.uploader', 'parent'])->findOrFail($id);
        
        // Generate breadcrumbs path
        $breadcrumbs = [];
        $temp = $folder;
        while ($temp) {
            array_unshift($breadcrumbs, $temp);
            $temp = $temp->parent;
        }

        return view('library.folder', compact('folder', 'breadcrumbs'));
    }

    public function createFolder(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name'          => 'required|string|max:255',
            'parent_id'     => 'nullable|exists:library_folders,id',
            'category'      => 'nullable|string|max:100',
            'division'      => 'nullable|string|max:100',
            'department'    => 'nullable|string|max:100',
            'business_unit' => 'nullable|string|max:100',
        ]);

        \App\Models\LibraryFolder::create($request->only(
            'name', 'parent_id', 'category', 'division', 'department', 'business_unit'
        ));

        return back()->with('success', 'Folder baru berhasil dibuat!');
    }

    public function uploadFile(Request $request, $folderId = null)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'file' => 'required|file|max:30720', // max 30MB
        ]);

        $destination = $folderId ?: $request->input('folder_id');

        // 1. CEK APAKAH TARGETNYA ADALAH TAB DOKUMEN MUTU / SOP / JOBDESK / KPI / IK
        if ($destination && str_starts_with($destination, 'tab:')) {
            $docType = str_replace('tab:', '', $destination);
            $file = $request->file('file');
            $customTitle = $request->input('custom_name');
            $title = $customTitle ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('library_manual', $fileName, 'public');

            $buName = $request->business_unit ?: ($request->category === 'divisi' ? $request->bu : null);
            $divName = $request->division ?: $request->div;
            $deptName = $request->department ?: ($request->category === 'support' ? $request->bu : null);

            $companyName = 'PT. PUTRA KELANA MAKMUR';
            if (in_array($buName, ['SPBU', 'LPG PSO', 'LPG NPSO'])) {
                $companyName = 'PT. LINTAS BINTAN SAMUDERA';
            } elseif (in_array($buName, ['CPT & MHM', 'SBS', 'GVI'])) {
                $companyName = 'PT. CAHAYA PERDANA TRANSALAM';
            }

            Library::create([
                'title'         => $title,
                'doc_number'    => $request->input('doc_number'),
                'category'      => $request->category ?: ($deptName ? 'support' : 'divisi'),
                'doc_type'      => $docType,
                'division_name' => $divName,
                'business_unit' => $buName,
                'support_type'  => $deptName,
                'company_name'  => $companyName,
                'file_path'     => $path,
                'uploaded_by'   => auth()->id(),
            ]);

            $tabNames = [
                'dokumen-mutu' => '1. Dokumen Mutu',
                'sop'          => '2. SOP Sah',
                'jobdesk'      => '3. Jobdesk',
                'kpi'          => '4. KPI & Target',
                'ik-forms'     => '5. IK & Formulir',
            ];
            $tabLabel = $tabNames[$docType] ?? 'Katalog Dokumen';

            return back()->with('success', "Dokumen '{$title}' berhasil diunggah ke tab {$tabLabel}!");
        }

        // 2. JIKA MEMILIH BUAT FOLDER BARU
        $targetFolderId = is_numeric($destination) ? (int)$destination : null;
        if ((!$targetFolderId || $destination === '__create_new__') && $request->filled('new_folder_name')) {
            $newFolder = \App\Models\LibraryFolder::create([
                'name'          => $request->new_folder_name,
                'category'      => $request->category,
                'division'      => $request->division,
                'department'    => $request->department,
                'business_unit' => $request->business_unit,
            ]);
            $targetFolderId = $newFolder->id;
        }

        // 3. JIKA MASUK KE FOLDER DEPARTEMEN ATAU FOLDER DEFAULT
        if (!$targetFolderId) {
            $scopeName = $request->department ?: ($request->business_unit ?: ($request->division ?: 'General'));
            $defaultFolder = \App\Models\LibraryFolder::firstOrCreate([
                'name'          => 'Berkas & Formulir ' . $scopeName,
                'category'      => $request->category,
                'division'      => $request->division,
                'department'    => $request->department,
                'business_unit' => $request->business_unit,
            ]);
            $targetFolderId = $defaultFolder->id;
        }

        $folder = \App\Models\LibraryFolder::findOrFail($targetFolderId);

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $customTitle = $request->input('custom_name');
            $originalName = $customTitle ?: $uploadedFile->getClientOriginalName();
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $uploadedFile->getClientOriginalName());
            $path = $uploadedFile->storeAs('library_general', $fileName, 'public');

            \App\Models\LibraryFile::create([
                'folder_id'   => $folder->id,
                'name'        => $originalName,
                'path'        => $path,
                'mime_type'   => $uploadedFile->getClientMimeType(),
                'size'        => $uploadedFile->getSize(),
                'uploaded_by' => auth()->id(),
            ]);

            return back()->with('success', "Berkas '{$originalName}' berhasil diunggah ke folder {$folder->name}!");
        }

        return back()->with('error', 'Gagal mengunggah berkas.');
    }

    public function deleteFolder($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $folder = \App\Models\LibraryFolder::findOrFail($id);

        // Delete all files in this folder (and child folders via DB cascade cascade onDelete)
        // Let's manually clean the storage files first:
        $this->deletePhysicalFolderFiles($folder);

        $folder->delete();

        return redirect()->back()->with('success', 'Folder beserta seluruh isinya berhasil dihapus!');
    }

    public function deleteFile($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $file = \App\Models\LibraryFile::findOrFail($id);

        if ($file->path && Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        $file->delete();

        return back()->with('success', 'Berkas berhasil dihapus dari folder!');
    }

    private function deletePhysicalFolderFiles($folder)
    {
        foreach ($folder->files as $file) {
            if ($file->path && Storage::disk('public')->exists($file->path)) {
                Storage::disk('public')->delete($file->path);
            }
        }
        foreach ($folder->children as $child) {
            $this->deletePhysicalFolderFiles($child);
        }
    }

    public function streamLibraryDoc($id)
    {
        $lib = \App\Models\Library::findOrFail($id);
        
        $normalizedPath = str_replace('\\', '/', $lib->file_path);
        $path = storage_path('app/public/' . $normalizedPath);
        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->file($path);
    }

    public function streamGeneralFile($id)
    {
        $file = \App\Models\LibraryFile::findOrFail($id);
        
        $normalizedPath = str_replace('\\', '/', $file->path);
        $path = storage_path('app/public/' . $normalizedPath);
        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->file($path);
    }
}