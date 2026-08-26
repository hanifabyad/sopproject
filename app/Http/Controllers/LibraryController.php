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

        $query = Library::query();

        // Standardize div parameter name (KOMERSIL -> COMMERCIAL)
        if ($div === 'KOMERSIL') {
            $div = 'COMMERCIAL';
        }

        // 2. Logika Filter Dokumen (Agar dokumen muncul sesuai level)
        if ($company) {
            $query->where('company_name', $company);
        } elseif ($bu) {
            if ($category) {
                $query->where('category', $category);
            }
            $query->where('business_unit', $bu);
        } elseif ($div) {
            if ($div === 'COMMERCIAL') {
                $query->whereIn('division_name', ['COMMERCIAL', 'KOMERSIL']);
            } else {
                $query->where('division_name', $div);
            }
        } elseif ($category) {
            $query->where('category', $category);
        }

        $documents = $query->latest()->get();

        // 3. Ambil data Divisi Business Unit Baku (RETAIL, COMMERCIAL, SCM, FA)
        $listDivisions = ['RETAIL', 'COMMERCIAL', 'SCM', 'FA'];

        // Map Business Units per Division jika data DB belum terisi
        $divBuMap = [
            'RETAIL'     => ['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'INMAR (CNGM)'],
            'COMMERCIAL' => ['CPT & MHM', 'SBS', 'GVI'],
            'SCM'        => ['PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA'],
            'FA'         => ['KEUANGAN & ACCOUNTING'],
        ];

        // Daftar Departemen Support Lengkap
        $supportDepts = ['HC', 'IT', 'HSE', 'QMS', 'INTERNAL AUDIT', 'LOGISTIC', 'OPS', 'FINANCE', 'LEGAL'];

        // Ambil folder utama General Library
        $generalFolders = \App\Models\LibraryFolder::whereNull('parent_id')->orderBy('name')->get();

        return view('library.index', compact('documents', 'category', 'div', 'bu', 'company', 'listDivisions', 'divBuMap', 'supportDepts', 'generalFolders'));
    }

    public function storeManual(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
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
                'company_name'  => $companyName, // 👈 Terisi otomatis demi keamanan data
                'file_path'     => $path,
                'uploaded_by'   => auth()->id(),
            ]);

            return back()->with('success', 'Dokumen berhasil ditambahkan secara manual ke E-Library!');
        }

        return back()->with('error', 'Gagal mengupload file.');
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
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:library_folders,id',
        ]);

        \App\Models\LibraryFolder::create($request->only('name', 'parent_id'));

        return back()->with('success', 'Folder baru berhasil dibuat!');
    }

    public function uploadFile(Request $request, $folderId)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'file' => 'required|file|max:20480', // max 20MB
        ]);

        $folder = \App\Models\LibraryFolder::findOrFail($folderId);

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();
            $fileName = time() . '_' . $originalName;
            $path = $uploadedFile->storeAs('library_general', $fileName, 'public');

            \App\Models\LibraryFile::create([
                'folder_id'   => $folder->id,
                'name'        => $originalName,
                'path'        => $path,
                'mime_type'   => $uploadedFile->getClientMimeType(),
                'size'        => $uploadedFile->getSize(),
                'uploaded_by' => auth()->id(),
            ]);

            return back()->with('success', 'Berkas berhasil diunggah ke folder!');
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
        
        $path = storage_path('app/public/' . $lib->file_path);
        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->file($path);
    }

    public function streamGeneralFile($id)
    {
        $file = \App\Models\LibraryFile::findOrFail($id);
        
        $path = storage_path('app/public/' . $file->path);
        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->file($path);
    }
}