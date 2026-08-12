# E-QMS Project Audit (PT PKM Group)

> **Dokumen Audit Teknis & Pemetaan Codebase e-QMS**  
> **Tanggal Audit:** 12 Agustus 2026  
> **Status Codebase:** Eksisting (Legacy Development)  
> **Sifat Audit:** Read-Only / Non-Destruktif  

---

## 1. Executive Summary

Aplikasi **Electronic Quality Management System (e-QMS)** PT PKM Group dirancang untuk mengelola siklus hidup dokumen SOP (Standard Operating Procedure) dan Quality Management System perusahaan. Aplikasi ini memfasilitasi pembuatan, pengungahan (berdasarkan 4 bagian SOP: Cover, Lembar Pengesahan, Isi, Lampiran), alur persetujuan digital berantai (estafet approval), penempelan stempel/tanda tangan digital secara otomatis pada berkas PDF, penanganan revisi dokumen, notifikasi email dengan Magic Link auto-login, hingga pencatatan otomatis ke E-Library untuk dokumen yang telah disahkan.

Saat ini aplikasi dibangun di atas **Laravel 12.0** (PHP 8.2+) dengan database **MySQL/MariaDB**. Sebagian besar alur utama sudah terimplementasi, namun terdapat beberapa bug kritis (terutama `Class App\Mail\DocumentApprovedMail not found` saat persetujuan akhir dan ketiadaan role middleware pada route Admin) serta keterbatasan penanganan kompresi file PDF pada library FPDI.

---

## 2. Technology Stack

| Komponen | Teknologi / Library | Versi | Sumber Identifikasi |
|---|---|---|---|
| **Framework Utama** | Laravel Framework | `^12.0` | `composer.json` |
| **Bahasa Pemrograman** | PHP | `^8.2` | `composer.json` |
| **Database** | MySQL / MariaDB | 10.4.32-MariaDB | `.env.example` & SQL Dump `documentcontrol (1).sql` |
| **Frontend Framework** | Blade Templates | Built-in Laravel | `resources/views/` |
| **CSS Framework** | Tailwind CSS + DaisyUI | Tailwind `^4.0.0`, DaisyUI `^5.5.11` | `package.json` |
| **Build Tool** | Vite | `^7.0.7` | `vite.config.js`, `package.json` |
| **PDF Merger** | `webklex/laravel-pdfmerger` | `^1.3` | `composer.json`, `BusinessUnitController.php` |
| **PDF Parser** | `smalot/pdfparser` | `^2.12` | `composer.json`, `ReviewerController.php` |
| **PDF Stamping & FPDF** | `setasign/fpdi` & `setasign/fpdf` | FPDI `^2.6`, FPDF `^1.8` | `composer.json`, `ReviewerController.php` |
| **Email Package** | Laravel Native Mail (`Illuminate\Support\Facades\Mail`) | Built-in | `App\Mail\NewDocumentReviewMail` |
| **Auth System** | Laravel Session Auth + Signed URLs (Magic Link) | Built-in | `LoginController.php`, `web.php` |
| **Queue Connection** | Database Driver | Built-in | `.env.example` (`QUEUE_CONNECTION=database`) |
| **Scheduler / Cron** | Tidak ditemukan (Hanya command `inspire`) | - | `routes/console.php` |
| **File Storage** | Storage Disk Local/Public (`storage/app/public`) | Built-in | `config/filesystems.php`, `.env.example` |
| **External API** | Tidak ditemukan | - | Source code inspection |

---

## 3. Project Architecture

### Map Folder & File Penting

```
sopprojet/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AdminController.php        # Dashboard statistik Admin & transfer/library manual
│   │       ├── BusinessUnitController.php # Manajemen SOP BU, upload 4 file, antrean approval, revisi
│   │       ├── LibraryController.php      # E-Library, filter berjenjang, upload manual
│   │       ├── LoginController.php        # Auth login/logout & Magic Link auto-login
│   │       ├── ReviewerController.php     # Dashboard reviewer, FPDI PDF stamping, alur estafet, reject
│   │       ├── SupportController.php      # Manajemen SOP departemen support (HC, IT, QMS, dll)
│   │       └── UserController.php         # CRUD akun pegawai/pimpinan & role management
│   ├── Mail/
│   │   └── NewDocumentReviewMail.php      # Mailable notifikasi email + Magic Link
│   └── Models/
│       ├── Document.php                   # Model master dokumen SOP
│       ├── DocumentApproval.php           # Model antrean approval berantai
│       ├── DocumentLog.php                # Model riwayat audit trail log
│       ├── Library.php                    # Model dokumen sah E-Library
│       └── User.php                       # Model akun pengguna & role
├── database/
│   ├── migrations/                        # 14 file migrasi struktur database
│   └── seeders/                           # Seeder data user awal
├── resources/
│   └── views/
│       ├── admin/                         # View Blade area Admin (BU, Support, Users, Dashboard)
│       ├── emails/                        # View template email HTML
│       ├── layouts/                       # Main layout Blade (Admin & Reviewer)
│       ├── library/                       # View E-Library
│       └── reviewer/                      # View Blade area Pimpinan/Reviewer (Show, History, Dashboard)
├── routes/
│   ├── console.php                        # Artisan console commands
│   └── web.php                            # Routing utama aplikasi
└── storage/
    └── app/
        └── public/
            └── documents/                 # Direktori penyimpanan fisik PDF (covers, lps, contents, attachments, previews, final)
```

---

## 4. Database Structure

### Tabel Utama & Spesifikasi

#### 1. `users`
* **Fungsi:** Menyimpan akun Admin dan seluruh Pimpinan/Pejabat Penandatangan.
* **Kolom Penting:** `id`, `username`, `email`, `password`, `role`, `signature` (nullable), `status` (1/0).
* **Dipakai oleh:** Seluruh alur autentikasi dan penentuan urutan reviewer.

#### 2. `documents`
* **Fungsi:** Master data dokumen SOP yang sedang diproses maupun yang sudah aktif.
* **Kolom Penting:** `id`, `title`, `department`, `reviewer_id` (FK `users.id`), `file_cover`, `file_lp`, `file_preview`, `file_isi`, `file_lampiran`, `file_final`, `status` (`waiting`, `active`, `need_revision`, `archived`).
* **Relationship:** `belongsTo(User::class, 'reviewer_id')`, `hasMany(DocumentApproval::class)`, `hasMany(DocumentLog::class)`.

#### 3. `document_approvals`
* **Fungsi:** Mengelola rantai antrean persetujuan (estafet sequence) secara urut.
* **Kolom Penting:** `id`, `document_id` (FK `documents.id`), `user_id` (FK `users.id`), `sequence` (1..N), `status` (`pending`, `current`, `approved`, `rejected`), `notes`, `processed_at`.
* **Relationship:** `belongsTo(Document::class)`, `belongsTo(User::class)`.

#### 4. `document_logs`
* **Fungsi:** Menyimpan jejak audit trail setiap aksi pada dokumen.
* **Kolom Penting:** `id`, `document_id` (FK `documents.id`), `user_id` (FK `users.id`), `action` (`transfer`, `revisi`, `active`), `notes`.
* **Relationship:** `belongsTo(Document::class)`, `belongsTo(User::class)`.

#### 5. `libraries`
* **Fungsi:** Menyimpan arsip dokumen SOP sah yang sudah selesai ditandatangani semua pihak atau diunggah manual.
* **Kolom Penting:** `id`, `title`, `category`, `division_name`, `business_unit`, `company_name`, `file_path`, `uploaded_by` (FK `users.id`), `view_count`.
* **Relationship:** `belongsTo(User::class, 'uploaded_by')`.

### ERD Versi Teks (Sederhana)

```
  +---------------+        1:N       +----------------------+
  |     users     |<-----------------|  document_approvals  |
  +---------------+                  +----------------------+
   |        ^                                   |
   | 1:N    | FK (reviewer_id)                  | N:1
   v        |                                   v
  +---------------+        1:N       +----------------------+
  |   documents   |----------------->|    document_logs     |
  +---------------+                  +----------------------+
   |        |
   |        v (Auto-copy saat final approval)
   |  +---------------+
   +->|   libraries   |
      +---------------+
```

---

## 5. Roles & Authorization

### Daftar Role Eksisting
Role disimpan sebagai string pada kolom `users.role`:
- **Admin:** `admin`
- **Pimpinan / Reviewer:** `Direktur Utama`, `Ka. Div Retail`, `Wa. Ka. Div Retail`, `Chief of Staff`, `Management Representative`, `Marine Superintendent`, `Chief F&A`, `Ka. Div F&A`, `Ka. BU SPBU`, `Ka. BU Gas & SPBE`, `KA.DEPT.QMS`, `KA.DEPT.IT`, `Dept. Internal Audit`, `office`, `reviewer`, dll.

### Cara Aplikasi Membedakan Role
1. **Login Redirect:** `LoginController@showLoginForm` dan `login()` mengecek jika `Auth::user()->role === 'admin'`, diarahkan ke `/admin/dashboard`, selain itu ke `/reviewer/dashboard`.
2. **Potensi Celah Keamanan (Authorization Issue / IDOR):**
   - **TIDAK ADA Middleware Role / Gate / Policy pada Route Group Admin!**  
     Route `/admin/*` di `routes/web.php` hanya dibungkus `middleware('auth')`. User non-admin yang sedang login dapat mengetik URL `/admin/users` atau `/admin/BU` dan mengakses seluruh fitur Admin.
   - **Tidak ada pengecekan kepemilikan pada `ReviewerController@approve`:** User yang login sebagai reviewer tidak divalidasi apakah `Auth::id()` benar-benar sama dengan `reviewer_id` pada dokumen tersebut sebelum memproses stempel PDF.

---

## 6. Document Upload Flow (Alur Upload SOP BU)

```
[ Form UI Admin: /admin/BU/unit/{unit}/create ]
                       │
                       ▼
[ Route: POST /admin/BU/unit/{unit}/store ]
                       │
                       ▼
[ BusinessUnitController@store ]
                       │
    ┌──────────────────┴──────────────────┐
    │ 1. Validasi Title, PDF & Approvers  │
    └──────────────────┬──────────────────┘
                       │
    ┌──────────────────┴──────────────────┐
    │ 2. Simpan 4 File PDF Asli ke Storage│ (documents/covers, lps, contents, attachments)
    └──────────────────┬──────────────────┘
                       │
    ┌──────────────────┴──────────────────┐
    │ 3. PDFMerger: Merge 4 File PDF      │ -> storage/app/public/documents/previews/preview_{time}.pdf
    └──────────────────┬──────────────────┘
                       │
    ┌──────────────────┴──────────────────┐
    │ 4. Insert Record `documents`        │ (status = 'waiting', reviewer_id = approvers[0])
    └──────────────────┬──────────────────┘
                       │
    ┌──────────────────┴──────────────────┐
    │ 5. Insert Records `document_approvals`│ (Seq 1: 'current', Seq 2..N: 'pending')
    └──────────────────┬──────────────────┘
                       │
    ┌──────────────────┴──────────────────┐
    │ 6. Generate Temporary Signed URL    │ (Magic Link auto-login 24 Jam)
    └──────────────────┬──────────────────┘
                       │
    ┌──────────────────┴──────────────────┐
    │ 7. Kirim Email Notifikasi ke Reviewer#1│ (NewDocumentReviewMail)
    └─────────────────────────────────────┘
```

---

## 7. PDF Processing

1. **Library yang Digunakan:**
   - `Webklex\PDFMerger`: Menggabungkan berkas Cover, LP, Isi, dan Lampiran saat upload awal maupun revisi.
   - `Smalot\PdfParser\Parser`: Membaca objek teks PDF Tm matrix untuk pencarian koordinat dinamis.
   - `setasign\Fpdi\Fpdi` & `setasign\Fpdf`: Mengimpor halaman PDF dan menggambar grafik stempel digital.
2. **Mekanisme Stempel Digital & Koordinat:**
   - Dihubungkan via `$anchorMap` (misal `'KA.DEPT.QMS' => '[sig01]'`, `'Chief of Staff' => '[sig02]'`, dst.).
   - Memiliki tabel koordinat manual fallback dalam `ReviewerController::findTextCoordinates()`:
     - `[sig01]` -> X: 143, Y: 90
     - `[sig02]` -> X: 143, Y: 108
     - `[sig03]` -> X: 143, Y: 116
     - `[sig04]` -> X: 143, Y: 125
     - `[sig05]` -> X: 143, Y: 134
     - `[sig06]` -> X: 143, Y: 143
     - `[sig07]` -> X: 143, Y: 152
     - `[sig08]` -> X: 143, Y: 161
     - `[sig09]` -> X: 143, Y: 177
   - Stempel digambar pada **Halaman 2 (Lembar Pengesahan)** jika dokumen memiliki > 1 halaman.
3. **Estafet Accumulative Stamping:**
   - Setiap persetujuan mengambil berkas `$document->file_preview` (yang sudah memiliki stempel dari pejabat sebelumnya), mengimpor halamannya via FPDI, menambahkan stempel baru di posisi milik pejabat tersebut, dan menyimpan berkas baru `ESTAFET_{time}_{username}.pdf`. Stempel sebelumnya **tetap bertahan**.
4. **Penyebab Error Kompresi FPDI Free Parser:**
   - **Masalah:** Exception `This PDF document probably uses a compression technique which is not supported by the free parser shipped with FPDI.`
   - **Lokasi Code:** `ReviewerController.php` baris 77 (`$pdf->setSourceFile($sourcePath)`).
   - **Penyebab:** FPDI versi gratis tidak mendukung PDF versi 1.5+ yang menggunakan *Compressed Object Streams* (`/ObjStm`).

---

## 8. Current Approval Workflow (Estafet Berantai)

1. **Inisiasi Antrean:** Admin memilih urutan array `approvers` `[UserA, UserB, UserC]`. Tabel `document_approvals` menyimpan `sequence 1` (`status='current'`), `sequence 2..3` (`status='pending'`).
2. **Reviewer Current (UserA):** Membuka `/reviewer/dashboard` (filter query `document_approvals.user_id = Auth::id()` AND `status = 'current'`).
3. **Eksekusi Approval (`ReviewerController@approve`):**
   - Menempel stempel digital pada berkas PDF preview.
   - Mengubah status `document_approvals` UserA dari `current` menjadi `approved`.
   - Mencari antrean berikutnya (`sequence + 1` / UserB):
     - **Jika Ada Next Approver (UserB):** Status UserB diubah menjadi `current`, `$document->reviewer_id` diubah ke UserB, `$document->file_preview` diperbarui ke PDF berstempel baru, dan email + Magic Link dikirim ke UserB.
     - **Jika Sudah Approver Terakhir:** Status `$document->status` diubah menjadi `active`, berkas disalin otomatis ke tabel `libraries` (E-Library), dan mengirim email konfirmasi ke pembuat SOP.
4. **Apakah Sequential?** **YA**, alur berjalan secara berurutan (*sequential estafet*) berdasarkan kolom `sequence`.

---

## 9. Revision Workflow (Estafet Pintar)

1. **Pemicu Reject:** Reviewer mengklik *Reject* dengan wajib mengisi `notes` (catatan revisi). `ReviewerController@reject` mengubah status `document_approvals` reviewer tersebut menjadi `rejected` dan status utama `$document->status` menjadi `need_revision`.
2. **Admin Upload Revisi:** Admin membuka form revisi `/admin/BU/document/{id}/edit-revision`. Admin mengunggah file baru (misal file Isi baru).
3. **Merger Pintar (`BusinessUnitController@updateRevision`):**
   - Aplikasi mengambil Halaman 1-2 (Cover & LP berstempel) dari `$document->file_preview` lama.
   - Menggabungkan dengan file Isi baru dan Lampiran.
   - Mengubah status `document_approvals` milik reviewer yang menolak kembali menjadi `current`.
   - **Alur TIDAK diulang dari awal (Approver #1)**, melainkan langsung diteruskan ke reviewer yang melakukan rejection (karena stempel approver sebelumnya pada halaman LP tetap dipertahankan).

---

## 10. Email & Notification

- **Mekanisme:** Menggunakan native Laravel `Mail::to()->send(...)` (dikirim secara sinkron saat aksi dilakukan).
- **Mailable Class:** `App\Mail\NewDocumentReviewMail` (menerima `$document`, `$user`, `$magicLoginUrl`).
- **Magic Link Token:**
  - Diproduksi menggunakan `URL::temporarySignedRoute('login.magic', expiry, ['user_id', 'document_id'])`.
  - Masa berlaku token: 24 Jam (pada `AdminController` & `BusinessUnitController`) dan 15 Menit (pada `ReviewerController`).
  - Endpoint `/magic-login` memvalidasi `$request->hasValidSignature()`, kemudian melakukan `Auth::login($user, true)` secara otomatis tanpa password.

---

## 11. E-Library

1. **Kondisi Masuk Library:**
   - **Otomatis:** Saat persetujuan akhir (*final approver*) selesai di `ReviewerController@approve()`, record baru otomatis dibuat di tabel `libraries`.
   - **Manual:** Admin dapat mengunggah PDF langsung ke E-Library via `/library/store-manual` atau memindahkan dokumen dari detail BU via `moveToLibrary`.
2. **Struktur & Akses:**
   - Dikategorikan berdasarkan `category`, `division_name` (`RETAIL`, `KOMERSIL`, `SCM`, `FA`), `business_unit`, dan `company_name`.
   - Seluruh user terautentikasi dapat melihat dan memfilter perpustakaan (`/library`). Admin dapat menghapus berkas library (`DELETE /admin/library/{id}`).

---

## 12. Document Status Lifecycle

### Dokumen Status (`documents.status`)
- `waiting`: Dokumen baru diunggah/direvisi dan sedang menunggu antrean persetujuan.
- `active`: Dokumen telah disetujui penuh oleh seluruh rantai pejabat.
- `need_revision`: Dokumen ditolak oleh salah satu reviewer dan memerlukan perbaikan Admin.
- `archived`: Dokumen telah dipindahkan secara manual ke arsip E-Library.

### State Diagram

```
  [ UPLOAD NEW SOP ]
          │
          ▼
      (waiting) ──(Reviewer Rejects)──► (need_revision)
          │                                   │
  (Reviewer Approves)                   (Admin Uploads
          │                               Revision)
          ▼                                   │
    [Next Approver] ◄─────────────────────────┘
          │
    (Final Approver
       Approves)
          │
          ▼
       (active) ────(Move to Library)──► (archived)
          │
          ▼
   [ Auto-added to E-Library ]
```

---

## 13. Summary Table Features

| Modul | Sudah Ada | Kondisi | File Utama | Catatan |
|---|---|---|---|---|
| Authentication | ✅ | Complete | `LoginController.php` | Auth session & Magic link signed URL |
| User Management | ✅ | Complete | `UserController.php` | CRUD user & roles pimpinan |
| Business Unit SOP | ✅ | Complete | `BusinessUnitController.php` | Struktur 3 level (Divisi -> BU -> SOP) |
| Support SOP | ⚠️ | Partial | `SupportController.php` | Hanya reviewer tunggal, tidak ada estafet multi-approver |
| Upload SOP (4 Parts) | ✅ | Complete | `BusinessUnitController.php` | Cover, LP, Isi, Lampiran |
| PDF Merge | ✅ | Complete | `BusinessUnitController.php` | Menggunakan `Webklex\PDFMerger` |
| PDF Viewer / Stream | ✅ | Complete | `ReviewerController.php` | Response file PDF inline viewer |
| Digital Approval / Stamp | ⚠️ | Needs Fix | `ReviewerController.php` | FPDI/FPDF stempel. Hardcoded coordinates, error PDF 1.5+ |
| Approval Sequence (Estafet) | ⚠️ | Broken at End | `ReviewerController.php` | Estafet berantai berjalan, tapi CRASH di final step |
| Email Notification | ⚠️ | Needs Fix | `NewDocumentReviewMail.php` | Kirim Magic Link. Missing class `DocumentApprovedMail` |
| Revision (Estafet Pintar) | ✅ | Complete | `BusinessUnitController.php` | Mempertahankan LP lama dan meneruskan ke rejecter |
| Approval History / Log | ✅ | Complete | `ReviewerController.php`, `document_logs` | Audit trail riwayat tindakan |
| E-Library | ✅ | Complete | `LibraryController.php` | Auto-entry final approve + manual store & filter |
| Audit Trail / Logging | ✅ | Complete | `DocumentLog.php` | Mencatat log transfer, revisi, dan approval |
| Reminder / Scheduler | ❌ | Not Found | `routes/console.php` | Tidak ada cron job/scheduled task reminder |
| Role & Permission System | ⚠️ | Vulnerable | `web.php` | Role tersimpan di DB, tapi tidak ada middleware pembatas `/admin` |
| Dashboard Admin | ✅ | Complete | `AdminController.php` | Statistik Support vs BU |
| Dashboard Reviewer | ✅ | Complete | `ReviewerController.php` | Antrean dokumen khusus reviewer login |
| Search & Filter | ✅ | Complete | `LibraryController.php` | Filter bertingkat PT & BU |

---

## 14. Incomplete / Broken Features (Technical Debt)

### CRITICAL (Harus Diperbaiki Sebelum Production)
1. **Missing Class `DocumentApprovedMail` (Fatal Crash):**  
   Pada `ReviewerController.php` (baris 225), ketika persetujuan akhir terjadi, sistem memanggil:  
   `Mail::to($document->user->email)->send(new \App\Mail\DocumentApprovedMail($document));`  
   - File `app/Mail/DocumentApprovedMail.php` **TIDAK ADA** di codebase.
   - Relasi `$document->user` **TIDAK ADA** pada model `Document`.  
   *Dampak:* Setiap kali dokumen mencapai persetujuan akhir, sistem akan mengalami *Fatal PHP Exception / 500 Error*.
2. **Ketiadaan Role Middleware pada Route Admin (Vabilitas Keamanan):**  
   Group route `/admin/*` di `routes/web.php` hanya diproteksi `middleware('auth')`. User non-admin (Reviewer) yang mengetahui URL dapat langsung mengakses dashboard Admin dan mengelola user/dokumen.
3. **Uncatched FPDI Compression Exception:**  
   Tidak ada penanganan exception `try-catch` khusus pada FPDI saat mengimpor PDF yang terkompresi (PDF 1.5+), menyebabkan crash HTTP 500 saat reviewer mencoba meninjau dokumen tertentu.

### IMPORTANT
4. **Hardcoded Koordinat Stempel Digital:**  
   `ReviewerController::findTextCoordinates()` menggunakan koordinat statis `X=143, Y=90..177` untuk `[sig01]` hingga `[sig09]`. Jika template lembar pengesahan berubah layout, posisi stempel akan melesat/salah tempat.
5. **Inkonsistensi Masa Berlaku Magic Link:**  
   `AdminController` & `BusinessUnitController` menggunakan `addHours(24)`, sedangkan `ReviewerController` menggunakan `addMinutes(15)`.
6. **Disparitas Fitur Support SOP vs BU SOP:**  
   SOP Support hanya mendukung 1 reviewer tunggal tanpa tabel antrean `document_approvals`.

---

## 15. Security Findings

1. **Authorization Bypass (Broken Access Control):** Absence of admin role checks on `/admin/*` routes allows any logged-in standard user to escalate privileges via direct URL navigation.
2. **Insecure Magic Login:** Magic link URLs auto-login users instantly without password prompts. If an email link with a 24-hour expiration is shared or intercepted, unauthenticated users can access the recipient's account.
3. **Missing Resource-Level Ownership Checks:** Reviewer approval routes (`/reviewer/document/{id}/approve`) do not verify if `Auth::id()` matches the assigned `reviewer_id` for that document.

---

## 16. Important Files (Top 20)

1. `app/Http/Controllers/ReviewerController.php` — Fpdi stamping, estafet logic, approval/reject, E-library integration.
2. `app/Http/Controllers/BusinessUnitController.php` — 3-level BU SOP creation, 4-part upload, merger, smart revision.
3. `routes/web.php` — Route definitions, grouping, middleware mappings.
4. `app/Models/Document.php` — Master Document Eloquent Model.
5. `app/Models/DocumentApproval.php` — Approval Sequence Eloquent Model.
6. `app/Http/Controllers/LoginController.php` — Auth login, logout, & Magic Link handler.
7. `app/Http/Controllers/LibraryController.php` — E-Library controller, filters, & manual upload.
8. `app/Http/Controllers/AdminController.php` — Admin stats dashboard & library move.
9. `app/Http/Controllers/SupportController.php` — Support department SOP controller.
10. `app/Http/Controllers/UserController.php` — User account & role management CRUD.
11. `app/Mail/NewDocumentReviewMail.php` — Review invitation mailable class.
12. `app/Models/User.php` — User Eloquent Model.
13. `app/Models/DocumentLog.php` — Document Audit Trail Model.
14. `app/Models/Library.php` — E-Library Eloquent Model.
15. `resources/views/reviewer/show.blade.php` — Document review interface & action form.
16. `resources/views/admin/BU/create.blade.php` — 4-part upload & approver sequence creation form.
17. `resources/views/admin/BU/edit_revision.blade.php` — Revision upload form.
18. `resources/views/library/index.blade.php` — E-Library view Blade template.
19. `resources/views/emails/new_review_request.blade.php` — Email HTML template.
20. `composer.json` — Dependency specifications (Laravel 12, FPDI, FPDF, PDFMerger, PdfParser).

---

## 17. AS-IS Workflow Diagram

```
========================================================================================
                                ALUR E-QMS SAAT INI (AS-IS)
========================================================================================

[ ADMIN ]
   │
   ├─ 1. Login sebagai Admin (/login)
   │
   ├─ 2. Pilih Unit Bisnis / Support Dept (/admin/BU/ or /admin/support/)
   │
   ├─ 3. Upload 4 File SOP (Cover, LP, Isi, Lampiran) & Pilih Urutan Approver (1..N)
   │
   ▼
[ SYSTEM: BusinessUnitController@store ]
   │
   ├─ Merge 4 file -> storage/app/public/documents/previews/preview_{time}.pdf
   ├─ Create Record `documents` (status = 'waiting')
   ├─ Create Records `document_approvals` (Seq 1 = 'current', Seq 2..N = 'pending')
   ├─ Generate Magic Link Signed URL (berlaku 24 jam)
   └─ Kirim Email Notifikasi ke Approver #1 via `NewDocumentReviewMail`
   │
   ▼
[ APPROVER #1 (Pimpinan) ]
   │
   ├─ Klik Magic Link dari Email (Auto-login via `/magic-login`)
   │  ATAU Login manual (/login) -> Dashboard Reviewer (/reviewer/dashboard)
   │
   ├─ Buka Dokumen (/reviewer/document/{id}) & Cek PDF
   │
   ├─ MENGAMBIL KEPUTUSAN:
   │    │
   │    ├────────► [ APPROVE ] ──► System execute `ReviewerController@approve()`
   │    │                             │
   │    │                             ├─ FPDI parse [sig01..09] koordinat
   │    │                             ├─ FPDF gambar Stempel Digital pada Hal 2
   │    │                             ├─ Simpan ESTAFET_{time}_{user}.pdf
   │    │                             ├─ Update Approver #1 status = 'approved'
   │    │                             │
   │    │                             ├─ APAPAKAH ADA APPROVER SELANJUTNYA?
   │    │                             │    │
   │    │                             │    ├── (YA: Approver #2)
   │    │                             │    │     ├─ Set Approver #2 status = 'current'
   │    │                             │    │     ├─ Update document.reviewer_id & file_preview
   │    │                             │    │     ├─ Kirim Email + Magic Link ke Approver #2
   │    │                             │    │     └─ (Alur berulang ke Approver #2)
   │    │                             │    │
   │    │                             │    └── (TIDAK: Approver Terakhir)
   │    │                             │          ├─ Set document.status = 'active'
   │    │                             │          ├─ Auto-copy data ke tabel `libraries`
   │    │                             │          └─ 🔴 CRASH BUG: Panggil Class `DocumentApprovedMail` (TIDAK ADA)
   │    │
   │    └────────► [ REJECT ] ───► System execute `ReviewerController@reject()`
   │                                  │
   │                                  ├─ Set Approver status = 'rejected'
   │                                  ├─ Set document.status = 'need_revision'
   │                                  └─ Catat alasan penolakan di `document_logs`
   │                                  │
   │                                  ▼
   │                            [ ADMIN ]
   │                                  │
   │                                  ├─ Buka Form Edit Revisi (/admin/BU/document/{id}/edit-revision)
   │                                  ├─ Upload File Revisi (misal Isi baru)
   │                                  ├─ System menggabungkan Hal 1-2 berstempel lama + File Isi baru
   │                                  ├─ Set Approver yang menolak status = 'current'
   │                                  └─ Kirim Email + Magic Link ke Approver tersebut
   │                                  │
   │                                  ▼
   │                            (Alur dilanjutkan kembali dari Approver yang menolak)

========================================================================================
```

---

## 18. Questions / Unknowns

1. **Standardisasi Lembar Pengesahan:** Apakah posisi koordinat stempel digital `[sig01]`..`[sig09]` harus dinamis menggunakan visual drag-and-drop / PDF canvas parser, atau apakah template PDF lembar pengesahan akan dibuat memiliki standar ukuran fixed untuk seluruh PT di PKM Group?
2. **Handling PDF Compression:** Apakah diperbolehkan memasang utilitas CLI server (seperti Ghostscript `gs` / `pdftk`) untuk mengompres ulang PDF ke versi 1.4 sebelum diproses FPDI, atau menggunakan library commercial FPDI Parser?
3. **Mailable Notification Final:** Siapa penerima email akhir ketika persetujuan SOP telah selesai 100%? (Saat ini mencoba mengirim ke `$document->user->email`, tetapi relasi/user creator tidak tercatat di tabel `documents`).
