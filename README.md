# Ballet — En Pointe International Ballet Studio

Aplikasi web operasional untuk sekolah kursus Ballet. Project ini dibuat untuk menyederhanakan operasional harian sekolah: pencatatan absensi, penjadwalan kelas, mapping antara guru–murid–kelas, manajemen pembayaran (transaksi), manajemen stock (barang yang dijual ke murid/buyer), serta pembuatan laporan rutin.

Dibangun dengan **Laravel 9** + **PHP 8** + **MySQL**, dengan UI berbasis **Bootstrap 5** (template NiceAdmin).

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

| Layer            | Tool                                    |
|------------------|-----------------------------------------|
| Framework        | Laravel 9.19                            |
| Bahasa           | PHP ^8.0.2                              |
| Database         | MySQL / MariaDB                         |
| Frontend         | Bootstrap 5 (NiceAdmin template) + jQuery |
| Build tool       | Vite                                    |
| PDF              | barryvdh/laravel-dompdf ^2.0            |
| Excel            | maatwebsite/excel ^3.1                  |
| API auth         | Laravel Sanctum ^3.0                    |
| Editor           | CKEditor 4                              |
| UI helper        | SweetAlert2                             |

---

## Cara Install & Jalanin Project

### 1. Prerequisite

Pastikan sudah ke-install di mesin kamu:
- **PHP >= 8.0.2** dengan extension umum (mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, bcmath, fileinfo, gd)
- **Composer** ([getcomposer.org](https://getcomposer.org/))
- **MySQL / MariaDB** (atau pakai XAMPP/Laragon)
- **Node.js & npm** (opsional, kalau mau build asset via Vite)
- **Git**

### 2. Clone repository

```bash
git clone <repository-url> Ballet
cd Ballet
```

### 3. Install dependency PHP

```bash
composer install
```

Kalau ada error tentang versi PHP, cek `composer.json` (project ini butuh PHP `^8.0.2`).

### 4. Install dependency JS (opsional)

```bash
npm install
```

### 5. Copy file environment

```bash
cp .env.example .env
```

Di Windows (cmd):
```cmd
copy .env.example .env
```

### 6. Generate APP_KEY

```bash
php artisan key:generate
```

### 7. Konfigurasi `.env`

Buka file `.env`, sesuaikan minimal bagian berikut:

```env
APP_NAME=Ballet
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ballet
DB_USERNAME=root
DB_PASSWORD=

# Untuk fitur forgot password & email credential ke teacher baru
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email-kamu@gmail.com
MAIL_PASSWORD=app-password-gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=email-kamu@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

> **Note:** kalau pakai Gmail, generate **App Password** di akun Google (bukan password biasa), karena Gmail block SMTP login dengan password reguler.

### 8. Buat database

Bikin database kosong dengan nama sesuai `DB_DATABASE` di `.env` (default: `ballet`).

```sql
CREATE DATABASE ballet;
```

Atau lewat phpMyAdmin / DBeaver / TablePlus.

### 9. Jalankan migration + seeder

```bash
php artisan migrate --seed
```

Command ini akan:
- Bikin semua tabel sesuai [database/migrations/](database/migrations)
- Seed data awal (bank, rekening, class type, class transaction, student, stock, mapping teacher, dan **user default per role** — lihat bagian bawah)

### 10. Symlink storage (untuk upload file)

```bash
php artisan storage:link
```

### 11. Jalankan server

```bash
php artisan serve
```

Server jalan di `http://localhost:8000`.

Kalau mau build asset frontend:
```bash
npm run dev      # development (watch mode)
# atau
npm run build    # production build
```

---

## Akun Default (dari seeder)

Setelah `php artisan migrate --seed`, ada beberapa user default yang bisa langsung dipakai login:

| Role    | Email               | Password    |
|---------|---------------------|-------------|
| Head    | jose@gmail.com      | jose123     |
| Admin   | karvin@gmail.com    | karvin123   |
| Finance | felix@gmail.com     | felix123    |
| Teacher | saminjo@gmail.com   | saminjo123  |
| Teacher | cete@gmail.com      | cete123     |
| Teacher | ana@gmail.com       | ana123      |

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
