<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\ReviewerController;
use App\Http\Controllers\BusinessUnitController;
use App\Http\Controllers\LibraryController;

// ==========================================
// 🔑 HALAMAN OTENTIKASI
// ==========================================
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/magic-login', [LoginController::class, 'magicLogin'])->name('login.magic');

// ==========================================
// 🛡️ ADMIN GROUP (NAVY-WHITE DASHBOARD)
// ==========================================
Route::prefix('admin')->name('admin.')->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    
    // Dashboard Utama & Statistik Terpisah (Support vs BU)
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    // Kelola Logo & Info PT (Dynamic LP logos)
    Route::get('/logo', [AdminController::class, 'logoIndex'])->name('logo.index');
    Route::post('/logo/update', [AdminController::class, 'updateCompanyLogo'])->name('logo.update');
    
    // Kelola Pegawai (CRUD User dengan Role Lengkap)
    Route::resource('users', UserController::class)->except(['show']);

    // E-Library Khusus Admin Workspace
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::post('/library/store-manual', [LibraryController::class, 'storeManual'])->name('library.store_manual');
    Route::delete('/library/{id}', [LibraryController::class, 'destroy'])->name('library.destroy');
    
    // Admin folder and file management
    Route::post('/library/folder', [LibraryController::class, 'createFolder'])->name('library.folder.create');
    Route::post('/library/folder/{id}/upload', [LibraryController::class, 'uploadFile'])->name('library.folder.upload');
    Route::delete('/library/folder/{id}', [LibraryController::class, 'deleteFolder'])->name('library.folder.destroy');
    Route::delete('/library/file/{id}', [LibraryController::class, 'deleteFile'])->name('library.file.destroy');

    // ------------------------------------------
    // 🏢 MANAJEMEN SOP DEPARTEMEN SUPPORT
    // ------------------------------------------
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('index');
        Route::get('/{department}', [SupportController::class, 'show'])->name('show');
        Route::get('/{department}/create', [SupportController::class, 'create'])->name('create');
        Route::post('/{department}/store', [SupportController::class, 'store'])->name('store');
        
        // Audit Trail Dokumen Support
        Route::get('/document/{id}', [SupportController::class, 'documentDetail'])->name('document.detail');
        Route::delete('/document/{id}/delete', [SupportController::class, 'destroy'])->name('document.delete');

        // Alur Revisi Support
        Route::get('/document/{id}/edit-revision', [SupportController::class, 'editRevision'])->name('edit_revision');
        Route::put('/document/{id}/update-revision', [SupportController::class, 'updateRevision'])->name('update_revision');
    });

    // ------------------------------------------
    // 📦 MANAJEMEN BUSINESS UNIT (BU) 3 LEVEL
    // ------------------------------------------
    Route::prefix('BU')->name('BU.')->group(function () {
        // Level 1: Divisi Utama
        Route::get('/', [BusinessUnitController::class, 'index'])->name('index');
        
        // Level 2: Daftar Unit Bisnis per Divisi
        Route::get('/divisi/{namaDivisi}', [BusinessUnitController::class, 'showDivisi'])->name('divisi.show');
        
        // Level 3: Daftar SOP per Unit Bisnis
        Route::get('/unit/{bu}', [BusinessUnitController::class, 'showBU'])->name('show');
        
        // Upload & Store Dokumen BU
        Route::get('/unit/{unit}/create', [BusinessUnitController::class, 'create'])->name('create');
        Route::post('/unit/{unit}/store', [BusinessUnitController::class, 'store'])->name('store');
        
        // Audit Trail Dokumen BU (Show Detail)
        Route::get('/document/{id}', [BusinessUnitController::class, 'documentDetail'])->name('detail');
        Route::delete('/document/{id}/delete', [BusinessUnitController::class, 'destroy'])->name('document.delete');
        
        // 🛠️ ALUR REVISI PINTAR
        Route::get('/document/{id}/edit-revision', [BusinessUnitController::class, 'editRevision'])->name('edit_revision');
        Route::put('/document/{id}/update-revision', [BusinessUnitController::class, 'updateRevision'])->name('update_revision');
    });
});

// ==========================================
// ✒️ PEJABAT GROUP (REVIEWER)
// ==========================================
Route::prefix('reviewer')->name('reviewer.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [ReviewerController::class, 'index'])->name('dashboard');
    Route::get('/document/{id}', [ReviewerController::class, 'show'])->name('show');
    Route::get('/document/{id}/stream', [ReviewerController::class, 'streamFile'])->name('stream.file');
    Route::post('/document/{id}/approve', [ReviewerController::class, 'approve'])->name('approve');
    Route::post('/document/{id}/reject', [ReviewerController::class, 'reject'])->name('reject');
    Route::get('/history', [ReviewerController::class, 'history'])->name('history');
});

// ==========================================
// 🌐 GLOBAL WORKSPACE LIBRARY (MANDIRI)
// ==========================================
Route::middleware('auth')->group(function () {
    // Jalur utama menu E-Library
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::post('/library/store-manual', [LibraryController::class, 'storeManual'])->name('library.store_manual');
    Route::get('/library/folder/{id}', [LibraryController::class, 'showFolder'])->name('library.folder.show');
    
    // Secure file streaming routes to bypass storage:link 403 Forbidden errors when hosted
    Route::get('/library/document/{id}/stream', [LibraryController::class, 'streamLibraryDoc'])->name('library.document.stream');
    Route::get('/library/file/{id}/stream', [LibraryController::class, 'streamGeneralFile'])->name('library.file.stream');
});

// ==========================================
// ✏️ CREATOR REVISION ROUTES (auth only, no admin middleware)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/admin/support/document/{id}/creator-revise', [SupportController::class, 'creatorEditRevision'])->name('admin.support.creator_revise');
    Route::put('/admin/support/document/{id}/creator-revise', [SupportController::class, 'creatorUpdateRevision'])->name('admin.support.creator_update_revision');
    Route::get('/admin/BU/document/{id}/creator-revise', [BusinessUnitController::class, 'creatorEditRevision'])->name('admin.BU.creator_revise');
    Route::put('/admin/BU/document/{id}/creator-revise', [BusinessUnitController::class, 'creatorUpdateRevision'])->name('admin.BU.creator_update_revision');
});
