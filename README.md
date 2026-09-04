# 🏢 Electronic Quality Management System (e-QMS) — PT Putra Kelana Makmur Group

Sistem Manajemen Mutu Terpadu Berbasis Digital (e-QMS) untuk standarisasi pembuatan, evaluasi, revisi, tanda tangan digital multi-tahap (estafet), stempel sah otomatis (*auto-stamping*), permohonan SOP baru, bukti sosialisasi QR code, kuis pemahaman karyawan, dan katalog E-Library resmi untuk seluruh unit bisnis & departemen support di lingkungan **PT Putra Kelana Makmur (PKM Group)**.

---

## 📑 Daftar Isi
1. [Arsitektur & Spesifikasi Teknologi](#-arsitektur--spesifikasi-teknologi)
2. [Prasyarat Sistem (Prerequisites)](#-prasyarat-sistem-prerequisites)
3. [🚀 Pilihan 1: Deployment via Docker (Rekomendasi Produksi & Staging)](#-pilihan-1-deployment-via-docker-rekomendasi-produksi--staging)
4. [🖥️ Pilihan 2: Deployment via Direct Server (Non-Docker / VPS / Bare Metal)](#-pilihan-2-deployment-via-direct-server-non-docker--vps--bare-metal)
5. [🗄️ Panduan Database, Migrasi & Seeder](#-panduan-database-migrasi--seeder)
6. [⚙️ Konfigurasi Environment (.env) Penting](#-konfigurasi-environment-env-penting)
7. [🛡️ Service Background: Queue Worker & Scheduler](#-service-background-queue-worker--scheduler)
8. [🔍 Checklist Verifikasi Pasca-Deployment (Go-Live)](#-checklist-verifikasi-pasca-deployment-go-live)
9. [🛠️ Pemecahan Masalah (Troubleshooting)](#-pemecahan-masalah-troubleshooting)

---

## 🏗️ Arsitektur & Spesifikasi Teknologi

| Komponen | Spesifikasi / Library |
| :--- | :--- |
| **Framework** | Laravel 12 (PHP 8.2+) |
| **Database** | MySQL 8.0+ / MariaDB 10.5+ (Engine: InnoDB, Charset: utf8mb4) |
| **Frontend UI** | Blade Templating, Tailwind CSS, Phosphor Icons, Material Symbols, Vite |
| **PDF Processing & Merging** | FPDI 2.6+, FPDF 1.86+, PDFMerger, Smalot PDFParser, PDF.js |
| **PDF Compression Engine** | **QPDF 11+** (Krusial untuk normalisasi & digital stamping) |
| **Background Processing** | Laravel Database Queue Worker & Cron Scheduler |
| **Authentication & Security** | SHA-256 Signed Magic Links, Role-Based Access Control (RBAC), Session Database |

---

## 📋 Prasyarat Sistem (Prerequisites)

Jika melakukan instalasi tanpa Docker, pastikan server telah memenuhi spesifikasi berikut:
* **PHP:** Versi **8.2** atau lebih baru.
* **Ekstensi PHP Wajib:** `pdo_mysql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd` (dengan FreeType & JPEG support), `zip`, `xml`, `curl`.
* **QPDF Binary:** Wajib terpasang di sistem (`apt-get install qpdf` pada Ubuntu/Debian).
* **Composer:** Versi 2.5+.
* **Node.js:** Versi 18 LTS atau 20 LTS & NPM.
* **Web Server:** Nginx (rekomendasi) atau Apache dengan `mod_rewrite`.

---

## 🚀 Pilihan 1: Deployment via Docker (Rekomendasi Produksi & Staging)

Proyek ini telah dilengkapi dengan konfigurasi **Multi-Stage Dockerfile** dan `docker-compose.yml` yang siap untuk lingkungan produksi, mencakup container:
1. `eqms-app` (Nginx + PHP 8.2 FPM + QPDF Engine)
2. `eqms-queue` (Laravel Background Queue Worker)
3. `eqms-scheduler` (Laravel Task Scheduler / Cron)
4. `eqms-db` (MySQL 8.0)

### Langkah-langkah Deployment Docker:

#### 1. Clone Repository
```bash
git clone https://github.com/hanifabyad/sopproject.git /var/www/eqms
cd /var/www/eqms
```

#### 2. Salin dan Sesuaikan File Environment
```bash
cp .env.example .env
```
Buka file `.env` dan atur parameter produksi penting:
```env
APP_NAME="e-QMS PT PKM Group"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://eqms.pkmgroup.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=eqms_prod
DB_USERNAME=eqms_user
DB_PASSWORD=GantiDenganPasswordKuat123!
DB_ROOT_PASSWORD=GantiDenganPasswordRootKuat123!

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=notifikasi.eqms@gmail.com
MAIL_PASSWORD=app_password_google_anda
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notifikasi.eqms@gmail.com
MAIL_FROM_NAME="e-QMS PT PKM Group"

QPDF_BINARY_PATH=/usr/bin/qpdf
```

#### 3. Build dan Jalankan Container
```bash
docker compose up -d --build
```

#### 4. Jalankan Migrasi Database & Seeding
```bash
# Generate APP_KEY jika belum ada di .env
docker compose exec app php artisan key:generate --force

# Migrasi tabel database
docker compose exec app php artisan migrate --force

# Jalankan Seeder Pengguna Awal (Akun Pejabat & Admin)
docker compose exec app php artisan db:seed --class=UserSeeder --force
docker compose exec app php artisan db:seed --class=LibraryFolderSeeder --force
```

#### 5. Buat Storage Link & Optimize Cache
```bash
docker compose exec app php artisan storage:link --force
docker compose exec app php artisan optimize
```

#### 6. Setup Reverse Proxy SSL (Nginx Host / Cloudflare / Traefik)
Arahkan reverse proxy domain Anda ke port container `8000` (atau port yang didefinisikan di `.env` `APP_PORT`).

Contoh konfigurasi Nginx Reverse Proxy Host:
```nginx
server {
    listen 80;
    server_name eqms.pkmgroup.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name eqms.pkmgroup.com;

    ssl_certificate /etc/letsencrypt/live/eqms.pkmgroup.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/eqms.pkmgroup.com/privkey.pem;

    client_max_body_size 50M;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

---

## 🖥️ Pilihan 2: Deployment via Direct Server (Non-Docker / VPS / Bare Metal)

Gunakan metode ini jika server produksi menggunakan VPS Ubuntu/Debian standar dengan Nginx dan PHP-FPM bawaan OS.

### 1. Install Dependencies OS & QPDF
```bash
sudo apt update && sudo apt install -y \
    nginx \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-bcmath \
    php8.2-curl \
    php8.2-gd \
    php8.2-zip \
    php8.2-intl \
    qpdf \
    supervisor \
    unzip \
    git
```

Pastikan QPDF terpasang dengan benar:
```bash
qpdf --version
which qpdf  # Output umumnya: /usr/bin/qpdf
```

### 2. Clone Repository & Install Vendor PHP / JS
```bash
cd /var/www
sudo git clone https://github.com/hanifabyad/sopproject.git eqms
cd /var/www/eqms

# Install package PHP produksi (tanpa development dependencies)
composer install --no-dev --optimize-autoloader

# Build aset frontend (Tailwind/Vite)
npm install
npm run build
```

### 3. Konfigurasi Environment (.env)
```bash
cp .env.example .env
nano .env
```
Isi konfigurasi database MySQL, mail SMTP, dan pastikan:
```env
APP_ENV=production
APP_DEBUG=false
QPDF_BINARY_PATH=/usr/bin/qpdf
```
Generate kunci aplikasi:
```bash
php artisan key:generate --force
```

### 4. Atur Hak Akses & Storage Symlink
```bash
sudo chown -R www-data:www-data /var/www/eqms/storage /var/www/eqms/bootstrap/cache
sudo chmod -R 775 /var/www/eqms/storage /var/www/eqms/bootstrap/cache

# Buat public symlink untuk storage berkas
php artisan storage:link
```

### 5. Migrasi & Seeding Database
```bash
php artisan migrate --force

# Pilihan A: Setup Bersih Produksi (Hanya Admin & Pejabat Utama)
php artisan db:seed --class=UserSeeder --force
php artisan db:seed --class=LibraryFolderSeeder --force

# Pilihan B: Setup Lengkap Staging/UAT (Termasuk Katalog & Data Uji)
# php artisan db:seed --force
```

### 6. Konfigurasi Nginx Web Server
Buat file konfigurasi `/etc/nginx/sites-available/eqms`:
```nginx
server {
    listen 80;
    server_name eqms.pkmgroup.com;
    root /var/www/eqms/public;

    index index.php index.html;
    charset utf-8;
    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
Aktifkan konfigurasi:
```bash
sudo ln -s /etc/nginx/sites-available/eqms /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 7. Konfigurasi Supervisor untuk Queue Worker
Email notifikasi approval, magic login, dan pengesahan dokumen diproses di antrean background (*asynchronous*). Wajib menjalankan queue worker via Supervisor.

Buat file `/etc/supervisor/conf.d/eqms-worker.conf`:
```ini
[program:eqms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/eqms/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/eqms/storage/logs/worker.log
stopwaitsecs=3600
```
Jalankan supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start eqms-worker:*
```

### 8. Konfigurasi Cron Job untuk Scheduler
Tambahkan baris scheduler Laravel ke crontab user `www-data`:
```bash
sudo crontab -u www-data -e
```
Tambahkan:
```cron
* * * * * cd /var/www/eqms && php artisan schedule:run >> /dev/null 2>&1
```

### 9. Optimasi Cache Produksi
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🗄️ Panduan Database, Migrasi & Seeder

### Opsi 1: Setup Produksi Bersih (Clean Production)
Digunakan saat pertama kali go-live di server produksi asli:
```bash
php artisan migrate --force
php artisan db:seed --class=UserSeeder --force
php artisan db:seed --class=LibraryFolderSeeder --force
```

### Opsi 2: Setup Staging / UAT (Full Master Data & Evaluasi)
Digunakan untuk server testing / demonstrasi kepada manajemen:
```bash
php artisan migrate --seed --force
```

### 🔑 Akun & Kredensial Default (Pasca-Seeder)

| Role / Jabatan | Username | Email Default | Password Awal |
| :--- | :--- | :--- | :--- |
| **Administrator e-QMS** | `admin` | `admin@pkmgroup.com` | `password123` |
| **Direktur Utama** | `dirut` | `dirut@pkmgroup.com` | `password123` |
| **Ka. Div Retail** | `kadiv_retail` | `kadiv.retail@pkmgroup.com` | `password123` |
| **Chief F&A** | `chief_fa` | `chief.fa@pkmgroup.com` | `password123` |
| **KA.DEPT.QMS** | `ka_qms` | `qms@pkmgroup.com` | `password123` |
| **KA.DEPT.IT** | `ka_it` | `it@pkmgroup.com` | `password123` |
| **KA.DEPT.HC** | `ka_hc` | `hc@pkmgroup.com` | `password123` |
| **KA.DEPT.HSE** | `ka_hse` | `hse@pkmgroup.com` | `password123` |
| **Ka. BU SPBU** | `ka_spbu` | `spbu@pkmgroup.com` | `password123` |
| **Ka. BU Gas & SPBE**| `ka_gas` | `gas@pkmgroup.com` | `password123` |
| **Ka. BU Inmarr** | `ka_inmar` | `inmar@pkmgroup.com` | `password123` |
| **Ka. BU CPT** | `ka_cpt` | `cpt@pkmgroup.com` | `password123` |

> ⚠️ **PENTING UNTUK TIM IT:** Segera ubah password default seluruh akun di atas melalui menu **Kelola Pegawai / Users** setelah instalasi awal selesai!

---

## ⚙️ Konfigurasi Environment (.env) Penting

| Key | Nilai Produksi yang Dianjurkan | Deskripsi |
| :--- | :--- | :--- |
| `APP_ENV` | `production` | Mengaktifkan mode produksi dan mematikan stack trace error publik. |
| `APP_DEBUG` | `false` | **Wajib `false`** untuk mencegah kebocoran informasi keamanan. |
| `APP_URL` | `https://eqms.pkmgroup.com` | Digunakan untuk pembuatan Magic Signed URL pada email. |
| `SESSION_DRIVER` | `database` | Menjaga sesi login stabil saat multi-server / restart worker. |
| `QUEUE_CONNECTION` | `database` | Memproses email notifikasi dan penandatanganan secara asynchronous. |
| `QPDF_BINARY_PATH` | `/usr/bin/qpdf` | Path mutlak ke executable QPDF di server. |
| `FILESYSTEM_DISK` | `public` | Tempat penyimpanan file PDF naskah, revisi, dan E-Library. |

---

## 🛡️ Service Background: Queue Worker & Scheduler

Aplikasi e-QMS mengandalkan dua proses background yang **wajib selalu aktif**:

1. **Queue Worker (`php artisan queue:work`):**
   * Mengirim email undangan review bertanda tangan digital (*Magic Signed Link*).
   * Mengirim email pemberitahuan revisi, permohonan baru, dan dokumen selesai sah.
   * Mengirim reminder evaluasi berkala.

2. **Cron Scheduler (`php artisan schedule:run`):**
   * Pengecekan status kedaluwarsa dokumen (*SLA Overdue*).
   * Pembersihan otomatis dokumen yang melewati masa retensi 3 tahun di Recycle Bin.
   * Notifikasi otomatis evaluasi SOP 6 bulan & 1 tahun.

---

## 🔍 Checklist Verifikasi Pasca-Deployment (Go-Live)

Lakukan pengecekan checklist berikut sebelum menyerahkan sistem ke pengguna operasional:

- [ ] Web app dapat diakses via HTTPS tanpa peringatan SSL.
- [ ] Login Admin berhasil (`admin` / `password123`).
- [ ] Folder storage terhubung (`storage/app/public` ter-symlink ke `public/storage`).
- [ ] Unggah naskah SOP baru berhasil dan PDF gabungan terbuat di antrean review.
- [ ] Stempel digital (*digital stamp*) tertera dengan benar di Lembar Pengesahan (memastikan QPDF berfungsi).
- [ ] Email notifikasi keluar berhasil diterima di inbox pengguna.
- [ ] Magic link di email dapat diklik dan langsung membuka dokumen tanpa login manual.
- [ ] Supervisor queue worker berstatus `RUNNING`.
- [ ] Cron schedule berjalan setiap menit.

---

## 🛠️ Pemecahan Masalah (Troubleshooting)

### 1. Error: *"QPDF failed to normalize file"* atau Stempel Digital Tidak Muncul
* **Penyebab:** Binary QPDF belum terpasang atau path di `.env` salah.
* **Solusi:** 
  ```bash
  which qpdf
  # Pastikan di .env: QPDF_BINARY_PATH=/usr/bin/qpdf
  ```

### 2. Error: 403 Forbidden saat Membuka Preview Dokumen PDF
* **Penyebab:** Symlink public storage belum dibuat atau hak akses folder storage salah.
* **Solusi:**
  ```bash
  php artisan storage:link --force
  sudo chown -R www-data:www-data storage bootstrap/cache
  sudo chmod -R 775 storage bootstrap/cache
  ```

### 3. Email Notifikasi Tidak Terkirim
* **Penyebab:** Queue worker belum dijalankan atau konfigurasi SMTP `.env` salah.
* **Solusi:**
  ```bash
  # Cek status supervisor
  sudo supervisorctl status eqms-worker:*

  # Cek isi antrean jobs
  php artisan queue:monitor database:default

  # Cek error log Laravel
  tail -n 50 storage/logs/laravel.log
  ```

### 4. Membersihkan Seluruh Cache Aplikasi (Setelah Update Kode)
```bash
php artisan optimize:clear
php artisan optimize
```

---

## 👥 Kontak Tim Pengembang & Support

Untuk kendala teknis lebih lanjut terkait arsitektur kode atau integrasi infrastruktur:
* **Tim Pengembang e-QMS:** IT & Quality Assurance Department — PT Putra Kelana Makmur Group.
