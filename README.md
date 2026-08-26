# e-QMS Portal — PT PKM Group

Electronic Quality Management System untuk mengelola dokumen SOP, proses review, approval digital, revisi, dan arsip dokumen sah dalam satu portal.

## Fitur

- Login admin, reviewer, dan magic link dari email.
- Manajemen akun pengguna, jabatan, dan tanda tangan digital.
- Manajemen SOP Business Unit dan Departemen Support.
- Upload Cover, Lembar Pengesahan, Isi SOP, dan Lampiran.
- Workflow approval otomatis berurutan.
- Reviewer dapat menyetujui atau meminta revisi dengan catatan.
- Stempel digital otomatis pada PDF.
- Dukungan format Lembar Pengesahan khusus PT CPT.
- Dukungan satu pengguna dengan beberapa jabatan approval.
- Nomor revisi otomatis: revisi 0 saat dokumen dibuat, lalu 1, 2, 3, dan seterusnya saat file revisi diunggah.
- Audit log aktivitas dokumen dan riwayat approval.
- Notifikasi email dan signed magic link.
- E-Library untuk dokumen yang sudah disahkan.
- Preview PDF langsung di halaman reviewer.
- Tampilan responsif untuk admin dan reviewer.

## Alur Dokumen

```text
Admin upload SOP
      ↓
Sistem membuat LP dan PDF gabungan
      ↓
Reviewer menerima email / magic link
      ↓
Reviewer approve atau meminta revisi
      ↓
Sistem menambahkan stamp digital
      ↓
Approval berikutnya aktif otomatis
      ↓
Semua approval selesai
      ↓
Dokumen menjadi aktif dan masuk E-Library
```

Jika reviewer meminta revisi, pembuat dokumen dapat mengunggah file baru. Nomor revisi hanya bertambah ketika file baru benar-benar diunggah, bukan ketika reviewer melakukan approval.

## Teknologi

- Laravel 12
- PHP 8.2+
- MySQL atau MariaDB
- Blade, Tailwind CSS, dan Vite
- FPDI/FPDF untuk stamp PDF
- PDFMerger untuk menggabungkan file PDF
- PDFParser untuk membaca struktur PDF
- Laravel Mail untuk notifikasi

## Persyaratan Instalasi

- PHP 8.2 atau lebih baru
- Composer
- Node.js dan npm
- MySQL/MariaDB
- Git
- QPDF (opsional, disarankan untuk PDF terkompresi)

Untuk Windows, project dapat dijalankan menggunakan Laragon.

## Instalasi Lokal

```bash
git clone https://github.com/hanifabyad/sopproject.git
cd sopproject
composer install
npm install
```

Buat environment file dan application key.

Windows:

```bash
copy .env.example .env
php artisan key:generate
```

Linux/macOS:

```bash
cp .env.example .env
php artisan key:generate
```

## Konfigurasi Database

Buat database baru, lalu sesuaikan `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eqms
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

Akun admin awal:

```text
Username: admin
Password: password123
```

Segera ganti password tersebut setelah login pada environment bersama atau production.

## Konfigurasi Email

Contoh konfigurasi Gmail SMTP:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=alamat-email@gmail.com
MAIL_PASSWORD=app-password-gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=alamat-email@gmail.com
MAIL_FROM_NAME="e-QMS PT PKM Group"
MAIL_TIMEOUT=10
```

Gunakan Gmail App Password, bukan password akun Gmail biasa. Untuk development tanpa mengirim email:

```env
MAIL_MAILER=log
```

Email akan ditulis ke `storage/logs/laravel.log`.

## Konfigurasi QPDF

QPDF digunakan sebagai fallback untuk PDF dengan kompresi yang tidak didukung parser gratis FPDI.

Windows:

```env
QPDF_BINARY_PATH="C:\\Program Files\\qpdf\\bin\\qpdf.exe"
```

Linux:

```env
QPDF_BINARY_PATH=/usr/bin/qpdf
```

## Menjalankan Aplikasi

Jalankan server Laravel dan Vite pada dua terminal:

```bash
php artisan serve
npm run dev
```

Atau gunakan script gabungan:

```bash
composer run dev
```

Aplikasi tersedia di `http://127.0.0.1:8000`.

Untuk build frontend production:

```bash
npm run build
```

## Testing

```bash
php artisan test
```

Test mencakup approval workflow, revisi dokumen, custom workflow, multi-jabatan, validasi reviewer, akses dokumen, dan pencegahan approval duplikat.

## Struktur Modul

```text
app/Http/Controllers/
├── AdminController.php
├── BusinessUnitController.php
├── LibraryController.php
├── LoginController.php
├── ReviewerController.php
├── SupportController.php
└── UserController.php

app/Services/
├── LpGeneratorService.php
├── LpSectionSignerParser.php
└── PdfSignaturePositionResolver.php

resources/views/
├── admin/
├── reviewer/
├── library/
├── emails/
└── layouts/
```

## Keamanan

- Jangan commit file `.env`.
- Jangan menyimpan password SMTP atau secret key di repository.
- Gunakan `APP_DEBUG=false` pada production.
- Gunakan HTTPS untuk magic link dan halaman approval.
- Ganti password admin bawaan setelah instalasi.

## Lisensi

Project ini dikembangkan untuk kebutuhan internal PT PKM Group.
