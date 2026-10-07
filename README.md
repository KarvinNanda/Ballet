# Ballet — En Pointe International Ballet Studio

Aplikasi web operasional untuk sekolah kursus Ballet. Project ini dibuat untuk menyederhanakan operasional harian sekolah: pencatatan absensi, penjadwalan kelas, mapping antara guru–murid–kelas, manajemen pembayaran (transaksi), manajemen stock (barang yang dijual ke murid/buyer), serta pembuatan laporan rutin.

Dibangun dengan **Laravel 13** + **PHP 8.4** + **MySQL 8**, dengan UI berbasis **Bootstrap 5.3** dan tema sendiri (mobile friendly).

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Role & Tugasnya](#role--tugasnya)
- [Struktur Folder](#struktur-folder)
- [Tech Stack](#tech-stack)
- [Cara Install & Jalanin Project](#cara-install--jalanin-project)
- [Akun Default (dari seeder)](#akun-default-dari-seeder)

---

## Fitur Utama

- **Authentication** — login, logout, forgot password (kirim reset link via email)
- **Master data** — kelola data Admin, Teacher, Student, Class, Course (ClassType), Finance
- **Class Management** — buat kelas, assign teacher & student ke kelas, level up kelas, freeze/unfreeze kelas, reset quota
- **Schedule** — buat/edit jadwal kelas (single atau multiple sekaligus)
- **Absensi** — guru/admin mencatat kehadiran murid per schedule
- **Transaction** — kelola tagihan & pembayaran murid (paid/unpaid)
- **Stock** — barang yang dijual ke buyer (in/out, report)
- **Buyer** — halaman publik untuk pembelian stock
- **Report** — Class Attendance Report, Active Student Report, Stock Report, Teacher Report (PDF via DomPDF)
- **Rule & Regulation** — kelola aturan sekolah
- **Profile** — ganti profil & password sendiri

---

## Role & Tugasnya

Aplikasi punya **5 role**, masing-masing punya middleware sendiri ([app/Http/Middleware/](app/Http/Middleware)) dan section sidebar yang berbeda.

| Role        | Tugas                                                                                                                                                                                                                                          |
|-------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Head**    | Role paling tinggi. Kelola **semua** master data (Admin, Teacher, Student, Class, Course, Finance), stock, transaction, rule & regulation, dan akses semua report (Teacher Report, Class Attendance, Stock Report, Active Student Report).     |
| **Admin**   | Operasional harian. Kelola Class, Course, Student, Teacher, Stock, Transaction, dan report Class Attendance + Active Student. **Tidak bisa** kelola Admin/Finance/Rule (itu wewenang Head).                                                    |
| **Teacher** | Lihat kelas yang dia ajar, lihat jadwal kelas, dan input absensi murid per schedule. Bisa juga buat/edit jadwal kelas-nya sendiri.                                                                                                             |
| **Finance** | Kelola pembayaran transaksi murid (paid/unpaid), kelola stock (in/out), serta report Stock, Teacher, dan Active Student dari sisi finance.                                                                                                     |
| **Buyer**   | Role publik untuk pengunjung yang ingin beli stock (mis. baju ballet, sepatu). Tidak perlu login penuh — diatur lewat middleware `authLogin`.                                                                                                  |

Mapping role ke entry-point dashboard:
- `/head` → HeadController
- `/admin` → AdminController
- `/teacher` → TeacherController
- `/finance` → FinanceController
- `/buyer` → BuyerController

---

## Struktur Folder

```
Ballet/
├── app/
│   ├── Console/                # Artisan commands
│   ├── Exceptions/             # Exception handlers
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── admin/          # Controller untuk role Admin
│   │   │   ├── auth/           # LoginController
│   │   │   ├── finance/        # Controller untuk role Finance
│   │   │   ├── head/           # Controller untuk role Head
│   │   │   ├── teacher/        # Controller untuk role Teacher
│   │   │   ├── BuyerController.php
│   │   │   ├── ForgotPasswordController.php
│   │   │   └── ProfileController.php
│   │   ├── Middleware/         # Role middleware (Admin, Head, Teacher, Finance, AuthLogin)
│   │   └── Kernel.php
│   ├── Mail/                   # Email template class (SendingEmail, ForgotPasswordEmail)
│   ├── Models/                 # Eloquent models (User, Student, ClassTransaction, dll)
│   └── Providers/
│
├── bootstrap/                  # Laravel bootstrap files
├── config/                     # Config files (database, mail, dll)
├── database/
│   ├── factories/              # Model factories untuk seeding
│   ├── migrations/             # Schema database (30+ migrations)
│   └── seeders/                # DatabaseSeeder + per-table seeder
│
├── lang/                       # Translation files
├── public/                     # Web entry point (index.php) + assets statik (vendor/, img/, dll)
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── Master/             # Layout utama (master.blade.php — header, sidebar, footer)
│       ├── admin/              # View untuk role Admin
│       ├── auth/               # login.blade.php
│       ├── buyer/              # View untuk Buyer
│       ├── email/              # Email template view
│       ├── finance/            # View untuk role Finance
│       ├── forgot-password/    # View forgot password flow
│       ├── head/               # View untuk role Head
│       ├── profile/            # Change profile & password
│       ├── teacher/            # View untuk role Teacher
│       └── vendor/             # Published vendor views (mail layout)
│
├── routes/
│   ├── api.php                 # API routes (sanctum)
│   ├── channels.php
│   ├── console.php
│   └── web.php                 # Web routes (semua route per role di-group di sini)
│
├── storage/                    # Logs, cache, file uploads
├── tests/                      # PHPUnit tests
├── .env.example                # Template environment file
├── artisan
├── composer.json
├── package.json
└── README.md
```

---

## Tech Stack

| Layer      | Tool                                                         |
|------------|--------------------------------------------------------------|
| Framework  | Laravel 13                                                   |
| Bahasa     | PHP 8.4                                                      |
| Database   | MySQL 8 (Docker, strict mode)                                |
| Frontend   | Bootstrap 5.3 + tema `public/assets/css/theme.css` + jQuery 3.7 (semua lokal, tanpa CDN) |
| Font       | Cormorant (judul) + Montserrat (isi), self-hosted            |
| PDF        | barryvdh/laravel-dompdf ^3                                   |
| Editor     | CKEditor 5 (lokal) + HTMLPurifier di server                  |
| Email lokal| Mailpit (Docker)                                             |
| Test       | PHPUnit 13 (`php artisan test`, database `ballet_test`)      |

---

## Cara Install & Jalanin Project

### 1. Prerequisite

- **PHP 8.4** dengan extension: mbstring, openssl, pdo_mysql, xml, ctype, bcmath, fileinfo, gd, intl, zip
  (macOS: `brew install php@8.4`)
- **Composer 2**
- **Docker** (untuk MySQL dan Mailpit)

### 2. Install

```bash
git clone <repository-url> Ballet && cd Ballet
composer install
cp .env.example .env          # isi DB_PASSWORD dan DB_ROOT_PASSWORD dengan nilai acak
php artisan key:generate
docker compose up -d --wait   # MySQL 8 + Mailpit, membaca nilai dari .env
php artisan migrate --seed    # buat tabel + data demo
php artisan serve
```

Buka `http://127.0.0.1:8000`. Semua email (forgot password, akun baru) masuk ke Mailpit: `http://localhost:8025`.

Data demo dibuat relatif terhadap hari ini: setiap kelas aktif punya jadwal **hari ini**, jadi absensi bisa langsung dicoba. Untuk mengulang data demo: `php artisan migrate:fresh --seed` (menghapus semua data lokal).

### 3. Test

```bash
docker compose exec mysql sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS ballet_test; GRANT ALL ON ballet_test.* TO \`$MYSQL_USER\`@\`%\`;"'
php artisan test
```

Test memakai database terpisah `ballet_test`, jadi data lokal tidak tersentuh.

### 4. Production

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` = alamat HTTPS asli (link reset password dibuat dari `APP_URL`).
- `SESSION_SECURE_COOKIE=true`.
- Isi `MAIL_*` dengan SMTP asli.
- Jalankan `php artisan migrate` setiap deploy.
- Pasang cron untuk scheduler (umur murid dihitung ulang setiap hari):
  `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`

---

## Akun Default (dari seeder)

Setelah `php artisan migrate --seed`, ada beberapa user default (khusus lokal, jangan dipakai di production):

| Role    | Email               | Password    |
|---------|---------------------|-------------|
| Head    | head@gmail.com      | head123     |
| Admin   | admin@gmail.com     | admin123    |
| Finance | finance@gmail.com   | finance123  |
| Teacher | teacher@gmail.com   | teacher123  |
| Teacher | sari.teacher@gmail.com, dewi.teacher@gmail.com, maya.teacher@gmail.com | teacher123 |

> Segera ganti password setelah login pertama untuk akun-akun ini.

---

## Troubleshooting

**`SQLSTATE[HY000] [1049] Unknown database 'ballet'`**
→ Database belum dibuat. Jalankan `CREATE DATABASE ballet;` dulu.

**`Specified key was too long; max key length is 767 bytes`**
→ Tambahkan ini di `app/Providers/AppServiceProvider.php` di method `boot()`:
```php
\Schema::defaultStringLength(191);
```

**Email tidak terkirim**
→ Cek konfigurasi SMTP di `.env`. Untuk Gmail, harus pakai App Password (bukan password akun).

**File upload error / 403 di route asset**
→ Jalankan `php artisan storage:link` dan pastikan folder `storage/` & `bootstrap/cache/` writable.

---

## Lisensi

Project internal En Pointe International Ballet Studio.
