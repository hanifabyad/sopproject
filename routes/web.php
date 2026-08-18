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
    
    // Kelola Pegawai (CRUD User dengan Role Lengkap)
    Route::resource('users', UserController::class)->except(['show']);

    // Hapus dokumen E-Library secara permanen
    Route::delete('/library/{id}', [LibraryController::class, 'destroy'])->name('library.destroy');

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
        Route::put('/update-reviewer/{id}', [SupportController::class, 'updateReviewer'])->name('updateReviewer');
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
        
        // Fitur Oper Kendali (Transfer Dokumen)
        Route::post('/document/{id}/transfer', [BusinessUnitController::class, 'updateReviewer'])->name('transfer');

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
});