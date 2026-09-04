<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\ReviewerController;
use App\Http\Controllers\BusinessUnitController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\EvaluationController;

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
    // Tracking SOP & Siklus Dokumen
    Route::get('/tracking', [AdminController::class, 'tracking'])->name('tracking');
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
    Route::post('/library/upload', [LibraryController::class, 'uploadFile'])->name('library.upload');
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

    // Kelola Evaluasi SOP oleh Admin
    Route::get('/evaluations', [EvaluationController::class, 'adminIndex'])->name('evaluations.index');
    Route::get('/evaluations/{id}', [EvaluationController::class, 'adminShow'])->name('evaluations.show');
    Route::post('/evaluations/{id}/resolve', [EvaluationController::class, 'adminResolve'])->name('evaluations.resolve');

    // Pusat Verifikasi & Review Aktivitas User (Request Revisi, Sosialisasi & Kuis)
    Route::get('/user-reviews', [\App\Http\Controllers\AdminUserReviewController::class, 'index'])->name('user_reviews.index');

    // 🗑️ Recycle Bin & Masa Retensi Dokumen Obsolete (3 Tahun)
    Route::prefix('recycle-bin')->name('recycle_bin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdminRecycleBinController::class, 'index'])->name('index');
        Route::post('/{id}/restore', [\App\Http\Controllers\AdminRecycleBinController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [\App\Http\Controllers\AdminRecycleBinController::class, 'forceDelete'])->name('force_delete');
        Route::post('/purge-due', [\App\Http\Controllers\AdminRecycleBinController::class, 'purgeAllDue'])->name('purge_due');
    });

    // Catatan / Tindak Lanjut SLA Overdue Dokumen
    Route::post('/documents/{id}/sla-action', [AdminController::class, 'updateSlaAction'])->name('documents.sla_action');

    // Persetujuan & Penolakan Request Revisi oleh Admin
    Route::post('/revision-requests/{id}/approve', [\App\Http\Controllers\SocializationAndRevisionController::class, 'approveRevisionRequest'])->name('revision_requests.approve');
    Route::post('/revision-requests/{id}/reject', [\App\Http\Controllers\SocializationAndRevisionController::class, 'rejectRevisionRequest'])->name('revision_requests.reject');

    // 💡 Kelola & Review Pengajuan SOP Baru oleh Admin
    Route::get('/sop-requests', [\App\Http\Controllers\NewSopRequestController::class, 'adminIndex'])->name('sop_requests.index');
    Route::post('/sop-requests/{id}/approve', [\App\Http\Controllers\NewSopRequestController::class, 'adminApprove'])->name('sop_requests.approve');
    Route::post('/sop-requests/{id}/reject', [\App\Http\Controllers\NewSopRequestController::class, 'adminReject'])->name('sop_requests.reject');
    Route::post('/sop-requests/{id}/request-revision', [\App\Http\Controllers\NewSopRequestController::class, 'adminRequestRevision'])->name('sop_requests.request_revision');
    Route::post('/sop-requests/{id}/mark-in-progress', [\App\Http\Controllers\NewSopRequestController::class, 'adminMarkInProgress'])->name('sop_requests.mark_in_progress');

    // Pengaturan Logo Perusahaan
    Route::get('/company-logos', [AdminController::class, 'logoIndex'])->name('company_logos.index');
    Route::post('/company-logos', [AdminController::class, 'updateCompanyLogo'])->name('company_logos.update');

    // Verifikasi & Penolakan Bukti Sosialisasi oleh Admin
    Route::get('/socializations/{id}', [\App\Http\Controllers\SocializationAndRevisionController::class, 'showAdminSocialization'])->name('socializations.show');
    Route::post('/socializations/{id}/verify', [\App\Http\Controllers\SocializationAndRevisionController::class, 'verifySocialization'])->name('socializations.verify');
    Route::post('/socializations/{id}/reject', [\App\Http\Controllers\SocializationAndRevisionController::class, 'rejectSocialization'])->name('socializations.reject');
});

// ==========================================
// 📢 SOSIALISASI, REQUEST REVISI & KUIS 6 BULAN (AUTH USER)
// ==========================================
Route::middleware('auth')->group(function () {
    // 💡 Pengajuan SOP Baru (User & Staff)
    Route::get('/sop-requests', [\App\Http\Controllers\NewSopRequestController::class, 'index'])->name('user.sop_requests.index');
    Route::post('/sop-requests', [\App\Http\Controllers\NewSopRequestController::class, 'store'])->name('user.sop_requests.store');
    Route::put('/sop-requests/{id}', [\App\Http\Controllers\NewSopRequestController::class, 'update'])->name('user.sop_requests.update');

    // 📢 Pusat Bukti Sosialisasi SOP (User & PIC)
    Route::get('/socializations', [\App\Http\Controllers\SocializationAndRevisionController::class, 'userSocializationsIndex'])->name('user.socializations.index');
    Route::get('/documents/{id}/socialize', [\App\Http\Controllers\SocializationAndRevisionController::class, 'createSocialization'])->name('documents.socialize');
    Route::post('/attendance-sheet/generate', [\App\Http\Controllers\SocializationAndRevisionController::class, 'generateAttendanceSheet'])->name('socializations.attendance_sheet.generate');
    
    // 📱 QR Code Sesi Presensi Kehadiran Sosialisasi
    Route::post('/socializations/sessions', [\App\Http\Controllers\SocializationAndRevisionController::class, 'createAttendanceSession'])->name('socializations.sessions.create');
    Route::get('/socializations/sessions/{token}/live', [\App\Http\Controllers\SocializationAndRevisionController::class, 'getAttendanceSessionLive'])->name('socializations.sessions.live');
    Route::get('/socializations/sessions/{token}/download-pdf', [\App\Http\Controllers\SocializationAndRevisionController::class, 'downloadSessionPdf'])->name('socializations.sessions.download_pdf');

    // 📝 Pusat Permohonan Revisi SOP (User & Staff)
    Route::get('/revision-requests', [\App\Http\Controllers\SocializationAndRevisionController::class, 'userRevisionRequestsIndex'])->name('user.revision_requests.index');

    Route::post('/documents/{id}/socialization', [\App\Http\Controllers\SocializationAndRevisionController::class, 'storeSocialization'])->name('documents.socialization.store');
    Route::get('/documents/{id}/socialization', [\App\Http\Controllers\SocializationAndRevisionController::class, 'showSocialization'])->name('documents.socialization.show');
    Route::post('/documents/{id}/request-revision', [\App\Http\Controllers\SocializationAndRevisionController::class, 'storeRevisionRequest'])->name('documents.request_revision.store');

    // Kuis Pemahaman SOP 6 Bulan (10 Soal: 7 PG + 3 Essay, KKM 70) (Poin 12)
    Route::get('/documents/{id}/quiz', [\App\Http\Controllers\SopQuizController::class, 'showQuiz'])->name('documents.quiz.show');
    Route::post('/documents/{id}/quiz/submit', [\App\Http\Controllers\SopQuizController::class, 'submitQuiz'])->name('documents.quiz.submit');
    Route::post('/documents/{id}/quiz/regenerate', [\App\Http\Controllers\SopQuizController::class, 'regenerateQuiz'])->name('documents.quiz.regenerate');
});

// ==========================================
// 📱 PUBLIC PRESENSI & KUIS OPERATOR VIA SCAN QR CODE
// ==========================================
Route::get('/presensi/{token}', [\App\Http\Controllers\SocializationAndRevisionController::class, 'showPresensiPage'])->name('socializations.presensi.show');
Route::post('/presensi/{token}', [\App\Http\Controllers\SocializationAndRevisionController::class, 'submitPresensi'])->name('socializations.presensi.store');
Route::post('/presensi/{token}/quiz', [\App\Http\Controllers\SocializationAndRevisionController::class, 'submitPresensiQuiz'])->name('socializations.presensi.quiz_submit');
Route::get('/presensi/{token}/download-pdf', [\App\Http\Controllers\SocializationAndRevisionController::class, 'downloadSessionPdf'])->name('socializations.presensi.download_pdf');

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
// 📋 EVALUASI SOP GROUP (EVALUATOR)
// ==========================================
Route::prefix('evaluations')->name('evaluations.')->middleware('auth')->group(function () {
    Route::get('/', [EvaluationController::class, 'index'])->name('index');
    Route::get('/{id}', [EvaluationController::class, 'show'])->name('show');
    Route::get('/{id}/stream', [EvaluationController::class, 'streamFile'])->name('stream');
    Route::post('/{id}/submit', [EvaluationController::class, 'submit'])->name('submit');
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

    // 📑 REGISTER KONTRAK & SPMP BU CPT (E-LIBRARY)
    Route::post('/library/cpt-contracts/assign-pic', [\App\Http\Controllers\CptContractController::class, 'assignPic'])->name('cpt_contracts.assign_pic');
    Route::post('/library/cpt-contracts/check-all-expired', [\App\Http\Controllers\CptContractController::class, 'checkAllExpired'])->name('cpt_contracts.check_all_expired');
    Route::post('/library/cpt-contracts/{id}/notify-expired', [\App\Http\Controllers\CptContractController::class, 'notifyExpired'])->name('cpt_contracts.notify_expired');
    Route::get('/library/cpt-contracts/template', [\App\Http\Controllers\CptContractController::class, 'downloadTemplate'])->name('cpt_contracts.template');
    Route::post('/library/cpt-contracts/import', [\App\Http\Controllers\CptContractController::class, 'import'])->name('cpt_contracts.import');
    Route::post('/library/cpt-contracts', [\App\Http\Controllers\CptContractController::class, 'store'])->name('cpt_contracts.store');
    Route::put('/library/cpt-contracts/{id}', [\App\Http\Controllers\CptContractController::class, 'update'])->name('cpt_contracts.update');
    Route::delete('/library/cpt-contracts/{id}', [\App\Http\Controllers\CptContractController::class, 'destroy'])->name('cpt_contracts.destroy');
    Route::get('/library/cpt-contracts/{id}/document', [\App\Http\Controllers\CptContractController::class, 'viewDocument'])->name('cpt_contracts.document');
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
