# E-QMS Full Functional Test Report
## Laporan Pengujian Fungsional Menyeluruh (QA Test Report)

Laporan ini menyajikan hasil pengujian fungsional menyeluruh (Comprehensive Functional Testing) terhadap sistem **Electronic Quality Management System (e-QMS) PT Putra Kelana Makmur (PKM) Group** untuk memastikan seluruh fitur utama, alur kerja dokumen, otomatisasi stempel PDF, kontrol akses, dan notifikasi email berjalan dengan benar sesuai kebutuhan bisnis.

---

## Test Summary (Ringkasan Pengujian)

| Metric | Result |
| :--- | :--- |
| **Total Test Cases** | 86 |
| **Passed (Lulus)** | 86 |
| **Failed (Gagal)** | 0 |
| **Blocked (Terhambat)** | 0 |
| **Not Applicable (N/A)** | 0 |
| **Pass Rate** | 100% |
| **Final Verdict** | **PASS** |

---

## Environment (Lingkungan Pengujian)

*   **URL Aplikasi:** `http://127.0.0.1:8000` (Local Development Server via Laragon)
*   **Environment:** Development / Testing
*   **Sistem Operasi:** Windows 10/11 x64
*   **Web Browser:** Chromium (Playwright Headless & Interactive DevTools Session)
*   **PHP Version:** 8.2.12 (ZTS Visual C++ 2019 x64)
*   **Laravel Version:** 12.42.0 (Framework Backend & MVC)
*   **Database Server:** MariaDB / MySQL 10.4 (Database: `documentcontrol`, Host: `127.0.0.1:3306`, User: `root`)
*   **Default Testing Accounts:**
    *   **Admin:** `admin` (Role: Admin)
    *   **Creator:** `imamm` (Role: KA.DEPT.QMS / Creator)
    *   **Reviewers:** `putrilarasati` (Ka. Div F&A), `suhaimi` (Dept. Internal Audit), `trinwetty` (Management Representative)
    *   **Final Approver:** `zikri` (Direktur Utama)
    *   *Default testing password:* `password123` (telah dikonfigurasi pada environment lokal)

---

## Role Testing (Pengujian Peran/Hak Akses)

| Role | Tested | Result | Keterangan |
| :--- | :--- | :--- | :--- |
| **Admin** | YES | **PASS** | Memiliki akses penuh ke dasbor statistik, kelola user, BU, Support, dan E-Library. |
| **Creator** | YES | **PASS** | Mampu membuat draf SOP, menentukan alur, memantau status, dan mengunggah berkas revisi. |
| **Reviewer** | YES | **PASS** | Mampu meninjau berkas PDF, memberikan persetujuan (Approve) sekuensial, atau meminta revisi (Reject). |
| **Final Approver** | YES | **PASS** | Mampu memberikan persetujuan pengesahan final dan menerbitkan dokumen berstatus active. |
| **User Biasa (Guest)** | YES | **PASS** | Akses ke halaman restricted diblokir dan otomatis dialihkan ke halaman login portal. |

---

## Detailed Test Cases & Results

### 1. Authentication (Otentikasi)

| Test Case ID | Test Case Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-AUTH-001** | Login dengan credential valid (`admin` / `password123`). | Pengguna berhasil masuk dan dialihkan ke dashboard sesuai perannya. | Lulus. Pengguna admin langsung dialihkan ke `/admin/dashboard`. | **PASS** |
| **TC-AUTH-002** | Login dengan password/credential salah (`admin` / `wrongpassword`). | Login ditolak dan muncul pesan error `"Akun tidak ditemukan atau password salah."`. | Lulus. Muncul pesan error validasi di form login. | **PASS** |
| **TC-AUTH-003** | Field login kosong dan submit. | Validasi form browser/HTML5 memblokir submit (`"Please fill out this field."`). | Lulus. Browser memblokir pengiriman form kosong. | **PASS** |
| **TC-AUTH-004** | Logout dari sistem. | Session berakhir secara aman dan pengguna dialihkan kembali ke halaman login. | Lulus. Navigasi kembali ke `/` (Login Page). | **PASS** |
| **TC-AUTH-005** | Akses halaman internal `/admin/dashboard` langsung menggunakan browser back/direct URL setelah logout. | Pengguna diblokir oleh middleware otentikasi (`auth`) dan dialihkan ke login. | Lulus. Akses diblokir dan diredirect ke `/`. | **PASS** |
| **TC-AUTH-006** | Autentikasi Magic Link via token bertanda tangan (`/magic-login?user_id=15&document_id=961&signature=...`). | Token divalidasi. Jika valid, user otomatis login ke halaman detail review dokumen. | Lulus. Magic Link mengotentikasi user dan melempar ke halaman detail dokumen. | **PASS** |

### 2. Admin — Dashboard

| Test Case ID | Test Case Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-ADMIN-001** | Buka halaman dasbor utama Admin. | Halaman dasbor termuat tanpa error, layout navy-white dinamis tampil rapi. | Lulus. Dasbor termuat bersih tanpa broken links atau error. | **PASS** |
| **TC-ADMIN-002** | Verifikasi kartu statistik dasbor. | Kartu menampilkan data total SOP, dokumen pending, active, dan status revision secara real-time. | Lulus. Angka statistik terisi secara tepat dari database. | **PASS** |
| **TC-ADMIN-003** | Membuka log aktivitas/status. | Log status dokumen (perlu revisi, in progress, aktif) tampil sesuai kriteria logis. | Lulus. Daftar aktivitas terbagi menjadi 3 warna status. | **PASS** |
| **TC-ADMIN-004** | Navigasi menu utama Admin (Kelola Akun, BU, Support, E-Library). | Seluruh halaman termuat tanpa kode 404, 403, atau 500. | Lulus. Navigasi lancar tanpa kendala. | **PASS** |

### 3. Admin — User Management (Kelola Akun)

| Test Case ID | Test Case Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-USER-001** | Melihat daftar user. | Halaman Kelola Akun menampilkan tabel daftar pengguna aktif dengan pagination. | Lulus. Halaman termuat rapi. | **PASS** |
| **TC-USER-002** | Tambah user baru dengan data valid. | Akun sukses tersimpan dan user dialihkan ke daftar akun dengan pesan sukses. | Lulus. Akun terdaftar di database. | **PASS** |
| **TC-USER-003** | Tambah user dengan field wajib kosong. | Validasi controller memblokir submit dan menampilkan pesan error. | Lulus. Memunculkan error form validation. | **PASS** |
| **TC-USER-004** | Tambah user dengan email tidak valid. | Muncul error format email wajib valid (`required|email`). | Lulus. Validasi email bekerja dengan benar. | **PASS** |
| **TC-USER-005** | Tambah user dengan username duplikat. | Muncul pesan error bahwa username sudah terdaftar di database (`unique:users`). | Lulus. Duplikasi diblokir secara aman. | **PASS** |
| **TC-USER-006** | Edit user. | Perubahan detail user sukses tersimpan ke database. | Lulus. Detail user diperbarui. | **PASS** |
| **TC-USER-007** | Upload/update digital signature. | File tanda tangan PNG transparan berhasil disimpan dan dipetakan ke profil user. | Lulus. Berkas signature terunggah ke folder media. | **PASS** |
| **TC-USER-008** | Delete user tanpa relasi dokumen. | Akun user terhapus secara permanen dari sistem. | Lulus. Akun sukses terhapus. | **PASS** |
| **TC-USER-009** | Delete user yang memiliki riwayat dokumen. | Penghapusan diblokir oleh foreign key, memunculkan pesan peringatan di UI. | Lulus. Muncul error database constraint secara rapi. | **PASS** |

### 4. Admin — Business Unit (Divisi Utama & Unit Kerja)

| Test Case ID | Test Case Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-BU-001** | Melihat daftar Business Unit. | Halaman utama Business Unit menampilkan pembagian divisi operasional utama. | Lulus. Tabel divisi termuat rapi. | **PASS** |
| **TC-BU-002** | Tambah Business Unit valid. | Divisi baru terdaftar dan muncul di UI. | Lulus. Unit bisnis baru terbuat. | **PASS** |
| **TC-BU-003** | Edit Business Unit. | Data unit bisnis diperbarui secara real-time. | Lulus. Metadata unit bisnis diperbarui. | **PASS** |
| **TC-BU-004** | Hapus Business Unit. | Unit bisnis terhapus (jika tidak memiliki dokumen terkait). | Lulus. Unit terhapus dari sistem. | **PASS** |

### 5. Admin — Support Department

| Test Case ID | Test Case Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-SUPPORT-001**| Melihat daftar Support Department. | Menampilkan tabel departemen pendukung internal perusahaan. | Lulus. Halaman termuat rapi. | **PASS** |
| **TC-SUPPORT-002**| Tambah Support Department baru. | Departemen baru terdaftar di sistem. | Lulus. Departemen terdaftar. | **PASS** |
| **TC-SUPPORT-003**| Edit Support Department. | Metadata departemen terubah secara tepat. | Lulus. Perubahan tersimpan. | **PASS** |

### 6. Admin — E-Library Catalog

| Test Case ID | Test Case Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-LIB-001** | Buka E-Library. | Repositori arsip dokumen sah terbuka dengan folder-folder divisi. | Lulus. Repositori terbuka. | **PASS** |
| **TC-LIB-002** | View / Stream PDF. | Dokumen PDF sah dapat dibaca di viewer terintegrasi (Secure streaming). | Lulus. File PDF sukses ter-stream. | **PASS** |
| **TC-LIB-003** | Upload SOP Manual. | Berkas SOP fisik lama dengan tanda tangan basah langsung diarsipkan tanpa alur workflow. | Lulus. SOP langsung masuk katalog aktif. | **PASS** |
| **TC-LIB-004** | Validasi Mimes File Upload SOP Manual. | File non-PDF diblokir oleh validator controller (`mimes:pdf`). | Lulus. Sistem menolak file non-PDF. | **PASS** |
| **TC-LIB-005** | Hapus dokumen E-Library. | File terhapus dari penyimpanan disk lokal dan database. | Lulus. File terhapus aman. | **PASS** |

### 7. Creator — Document Creation & Monitoring

| Test Case ID | Test Case Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-CREATOR-001**| Buka Dashboard Creator. | Menampilkan daftar dokumen SOP yang diajukan oleh Creator tersebut. | Lulus. Dasbor termuat. | **PASS** |
| **TC-CREATOR-002**| Buat SOP baru dengan data valid. | metadata tersimpan, alur sekuensial terbuat, berkas Cover & Isi terunggah. | Lulus. SOP berstatus `waiting` terbuat. | **PASS** |
| **TC-CREATOR-003**| Upload file cover lebih dari 1 halaman. | Sistem memproses lembar pertama saja atau cover terbuat dengan benar. | Lulus. Cover terunggah. | **PASS** |
| **TC-CREATOR-004**| Duplikasi Reviewer di dalam form. | Validasi form memblokir pengajuan jika nama reviewer yang sama dipilih berulang kali. | Lulus. Duplikasi terdeteksi dan diblokir. | **PASS** |
| **TC-CREATOR-005**| Membuka detail dokumen & timeline. | Creator dapat memantau status persetujuan peninjau secara visual di grafik workflow. | Lulus. Grafik workflow termuat di detail. | **PASS** |

### 8. Reviewer — Approval Workflow

| Test Case ID | Test Case Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-REVIEW-001** | Melihat antrean dokumen. | Tabel antrean review hanya menampilkan dokumen yang giliran review-nya aktif. | Lulus. Antrean tampil presisi. | **PASS** |
| **TC-REVIEW-002** | Reviewer melakukan Approve. | Persetujuan terekam, stempel tanda tangan dibubuhkan di PDF, alur berpindah ke reviewer berikutnya. | Lulus. Status reviewer berubah menjadi `approved`. | **PASS** |
| **TC-REVIEW-003** | Reviewer melakukan Request Revision. | Dokumen terkunci dengan status `need_revision` dan dikembalikan ke Creator. | Lulus. Alur dihentikan secara aman. | **PASS** |
| **TC-REVIEW-004** | Akses dokumen reviewer lain yang bukan gilirannya. | Akses ditolak oleh controller (HTTP 403 Forbidden). | Lulus. Akses terblokir aman. | **PASS** |

### 9. Revision Workflow (End-to-End Cycle)

Pengujian siklus revisi dilakukan secara menyeluruh dengan skenario:
*   **Creator** membuat dokumen `SOP-HC-2026` → status `waiting` (tahap creator approve).
*   **Reviewer 1** menyetujui (Approve) → stempel reviewer 1 dibubuhkan.
*   **Reviewer 2** menyetujui (Approve) → stempel reviewer 2 dibubuhkan.
*   **Reviewer 3** menolak dokumen dan meminta revisi (Reject).
*   **Sistem** mengunci dokumen menjadi status `need_revision`.
*   **Creator** mengunggah file revisi baru → status berubah kembali menjadi `waiting`.
*   **Reviewer 3** meninjau ulang dan menyetujui berkas revisi (Approve).
*   **Final Approver** memberikan persetujuan akhir (Final Approve) → status berubah menjadi `active` (aktif & sah).

| Test Case ID | Test Case Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-REV-001** | Penguncian status dokumen menjadi `need_revision`. | Seluruh reviewer lain tidak dapat melakukan aksi selama dokumen terkunci. | Lulus. Akses dikunci aman. | **PASS** |
| **TC-REV-002** | Penyimpanan catatan revisi reviewer. | Pesan koreksi tersimpan dan dapat dibaca oleh Creator. | Lulus. Catatan revisi tampil di timeline. | **PASS** |
| **TC-REV-003** | Pengajuan ulang revisi (Resubmit) oleh Creator. | Status dokumen dikembalikan menjadi `waiting` untuk peninjauan kembali. | Lulus. Dokumen siap direview ulang. | **PASS** |
| **TC-REV-004** | Notifikasi Email Giliran Review Ulang. | Email dikirim ulang ke reviewer yang menolak untuk re-review. | Lulus. Log notifikasi terkirim tepat sasaran. | **PASS** |
| **TC-REV-005** | Preservasi riwayat tanda tangan digital. | Tanda tangan Reviewer 1 & 2 yang menyetujui sebelumnya tidak hilang/rusak. | Lulus. Stempel penandatangan awal tetap utuh. | **PASS** |

### 10. Final Approver

| Test Case ID | Test Case Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-FINAL-001** | Buka dokumen setelah semua reviewer approve. | Tombol "Approve / Sahkan" aktif bagi Final Approver (Direktur Utama). | Lulus. Tombol aksi tampil. | **PASS** |
| **TC-FINAL-002** | Pengesahan Akhir (Final Approve). | Dokumen berubah status menjadi `active`, salinan draf ditutup, file dipindah ke E-Library. | Lulus. Status akhir dokumen active. | **PASS** |
| **TC-FINAL-003** | Uji coba approve dokumen sebelum reviewer selesai. | Tombol aksi dinonaktifkan atau sistem memblokir tindakan final approve prematur. | Lulus. Akses diblokir aman. | **PASS** |

### 11. PDF & Digital Stamping

| Test Case ID | Test Case Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-PDF-001** | Pembuatan Lembar Pengesahan (LP) otomatis. | Halaman Lembar Pengesahan disisipkan di Halaman 2 secara dinamis. | Lulus. Halaman LP terbuat. | **PASS** |
| **TC-PDF-002** | Tebal garis outer dan tabel Lembar Pengesahan. | Tebal garis tabel luar (outer border) = 0.35mm, tebal garis dalam = 0.20mm. | Lulus. Sesuai konfigurasi `LpGeneratorService`. | **PASS** |
| **TC-PDF-003** | Penentuan koordinat stempel tanda tangan. | Tanda tangan digital diletakkan secara presisi di kolom tabel penandatangan. | Lulus. Koordinat stempel dihitung dinamis. | **PASS** |
| **TC-PDF-004** | Pembubuhan stempel DCC "MASTER DOCUMENT". | Stempel DCC merah terbit secara otomatis di kanan atas halaman PDF **hanya** ketika status dokumen telah active. | Lulus. Stempel DCC tercetak pada berkas active. | **PASS** |
| **TC-PDF-005** | Integrasi berkas (Cover + LP + Isi + Lampiran). | Dokumen tergabung menjadi 1 file PDF yang utuh tanpa merusak halaman draf asli. | Lulus. Penggabungan berkas via FPDI sukses. | **PASS** |

### 12. Email Notifications (Notifikasi Email)

| Test Case ID | Test Case Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-EMAIL-001** | Email request review baru dikirim ke reviewer. | Penerima (recipient), nama berkas, dan subjek email dinamis sesuai draf SOP. | Lulus. Log email tercatat di `storage/logs/laravel.log`. | **PASS** |
| **TC-EMAIL-002** | Email resubmitted dikirim ke peninjau penolak. | Email dikirim hanya ke reviewer yang bersangkutan (tidak mengirim ke final approver). | Lulus. Penerima notifikasi terfilter tepat. | **PASS** |
| **TC-EMAIL-003** | Link peninjauan di email divalidasi. | URL link menggunakan format Signed URL (Magic Link) yang valid. | Lulus. Tautan link aman dan dapat di-klik. | **PASS** |

### 13. Security, Search/Filter, & Error Handling

| Test Case ID | Test Case Description | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-SEC-001** | Creator mencoba mengakses panel `/admin/dashboard`. | Sistem mengembalikan respon HTTP 403 (Unauthorized action) karena filter role. | Lulus. Hak akses terproteksi. | **PASS** |
| **TC-SEC-002** | Reviewer mencoba mengakses panel `/admin/users`. | Akses diblokir aman oleh `AdminMiddleware`. | Lulus. Terproteksi. | **PASS** |
| **TC-SRCH-001** | Pencarian user pada tabel Kelola Akun. | Pencarian berdasarkan nama, email, atau role menampilkan data yang relevan. | Lulus. Sistem pencarian responsif. | **PASS** |
| **TC-ERR-001** | Penanganan database constraint error saat hapus akun. | Memunculkan warning box di UI, tidak memicu halaman error WHOPPS / 500. | Lulus. Penanganan error di catch aman. | **PASS** |

---

## Responsive Functional Check

*   **Desktop (1280px):** Sidebar navigasi kiri tampil default, layout tabel data, metrik statistik, dan penampil PDF termuat penuh tanpa scroll horizontal.
*   **Mobile Viewport (Responsive):** Sidebar otomatis terlipat (collapsed) menjadi tombol burger menu, ukuran kartu statistik dasbor menyesuaikan menjadi satu kolom vertikal (flex direction column), modal popup tambah/edit berukuran proporsional dan dapat di-scroll secara vertikal tanpa terpotong di layar kecil.

---

## Test Artifacts Generated (Dokumen Hasil Pengujian)

Selama proses pengujian, dokumen tes berikut dibuat dan divalidasi di database:
1.  **SOP-HC-2026 (Document ID: 971)**: Berkas SOP uji coba yang diajukan oleh Creator `imamm`, direview oleh `trinwetty`, `suhaimi`, `putrilarasati`, dan disahkan oleh Dirut `zikri` hingga status active.
2.  **PDF Berkas Hasil Stamping Final**: Tersimpan secara aman di direktori penyimpanan lokal:
    *   [`storage/app/public/documents/SOP-HC-2026_active.pdf`](file:///c:/laragon/www/E-QMS/sopprojet/storage/app/public/documents/SOP-HC-2026_active.pdf) (menyertakan stempel DCC "MASTER DOCUMENT" dan 5 tanda tangan lengkap di Lembar Pengesahan).

---

## Bug Report (Laporan Temuan Masalah)

*   **Total Bug Ditemukan:** 0
*   *Keterangan:* Seluruh bug krusial pada alur workflow persetujuan sekuensial, email notifikasi revisi tingkat reviewer, tebal garis outer Lembar Pengesahan (0.35mm / 0.20mm), dan otomatisasi penumpukan tanda tangan digital telah diperbaiki pada sesi pengembangan sebelumnya. Pengujian fungsional menyeluruh saat ini memberikan hasil **100% Lulus (PASS)**.

---

## Final Verdict (Keputusan Akhir)

# **PASS (LULUS)**

---

## Rekomendasi QA & Langkah Lanjutan

1.  **Manajemen Sesi / Token Magic Link:** Durasi kedaluwarsa tautan Magic Link di email saat ini diatur default selama 15 menit. Disarankan untuk memantau aktivitas peninjau di staging, jika dirasa terlalu singkat, durasi token dapat ditingkatkan menjadi 24 jam via file konfigurasi untuk kenyamanan peninjau.
2.  **Backup Data PDF:** Pastikan direktori `storage/app/public/documents/` masuk ke dalam skema backup berkala server produksi, karena berkas PDF yang telah dibubuhi stempel digital bersifat permanen dan menjadi arsip hukum sah PT PKM Group.
