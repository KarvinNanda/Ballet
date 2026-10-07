# Ballet — En Pointe International Ballet Studio

[![CI](https://github.com/KarvinNanda/Ballet/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/KarvinNanda/Ballet/actions/workflows/ci.yml)

Web app for the daily operations of a ballet school: attendance, class schedules, the mapping between teachers, students and classes, billing and payments, stock (items sold to students and buyers), and recurring reports.

Built with **Laravel 13**, **PHP 8.4** and **MySQL 8**. The UI is **Bootstrap 5.3** with a custom, mobile-friendly theme.

---

## Contents

- [Features](#features)
- [Roles and permissions](#roles-and-permissions)
- [Project structure](#project-structure)
- [Tech stack](#tech-stack)
- [Getting started](#getting-started)
- [Tests and CI](#tests-and-ci)
- [Production](#production)
- [Seeded accounts](#seeded-accounts)
- [Troubleshooting](#troubleshooting)

---

## Features

- **Authentication**: login (rate limited), logout, password reset by email (hashed, single-use tokens).
- **Master data**: admin, teacher, finance and student accounts; classes; courses (class types).
- **Class management**: create classes, assign teachers and students, freeze (level up) a class, reset quota.
- **Schedules**: single or multiple schedules per class.
- **Attendance**: teachers record attendance for their own classes; head can record and correct it. A student who must pay first cannot be recorded.
- **Transactions**: student bills and payments (paid / unpaid).
- **Stock**: items sold to buyers (in / out, report).
- **Buyer**: page where buyers order stock.
- **Reports** (PDF via DomPDF): class attendance, active students, stock, teacher.
- **Rules & regulations**: rich text (CKEditor 5), sanitized on the server.
- **Profile**: change your own profile and password.

---

## Roles and permissions

Five roles. Each role has its own area (`/head`, `/admin`, `/teacher`, `/finance`, `/buyer`) guarded by the `role:` middleware ([app/Http/Middleware/EnsureRole.php](app/Http/Middleware/EnsureRole.php)).

Head and admin share the same controllers and views (`staff`). What admin may not do is enforced on the server by Gates (403) and hidden in the views with `@can`. The Gates are defined in [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php).

| Feature | Head | Admin |
|---|---|---|
| Admin accounts | full | none |
| Rules & regulations | full | none |
| Finance accounts: list, create | yes | yes |
| Finance accounts: update, delete | yes | no (`finance.manage`) |
| Reports: class attendance, active students, stock, teacher | yes | yes |
| Stock: list | yes | yes |
| Stock: add, update, delete | yes | no (`stock.manage`) |
| Transactions: add, update (including price) | yes | yes |
| Transactions: update a transaction that is not Unpaid | yes | no (`transaction.edit-paid`) |
| Transactions: delete | yes | no (`transaction.delete`) |
| Students (including bank account, sender, MaxQuota) | yes | yes |
| Class freeze: update price | yes | no (`class.freeze-price`) |
| Attendance from the schedule page | yes | no (`attendance.record`) |
| Teachers, classes, courses, schedules | yes | yes |

Other roles:

- **Teacher**: sees and manages only their own classes and schedules, and records attendance once per schedule.
- **Finance**: marks transactions as paid, manages stock movements, and has its own stock and teacher reports.
- **Buyer**: orders stock.

---

## Project structure

```
Ballet/
├── .github/workflows/ci.yml        # GitHub Actions: tests on every push and PR to master
├── app/
│   ├── Console/Commands/           # age:calculation, students:remove-finished-trials
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── staff/              # Shared by admin and head (student, teacher, class, schedule,
│   │   │   │                       #   attendance, transaction, stock, report, finance accounts, dashboard)
│   │   │   ├── head/               # Head only: admin accounts, rules
│   │   │   ├── teacher/            # Teacher area
│   │   │   ├── finance/            # Finance area
│   │   │   └── auth/               # Login
│   │   ├── Middleware/             # EnsureRole, SecurityHeaders, TrustHosts, ...
│   │   └── Requests/Auth/          # LoginRequest
│   ├── Models/
│   ├── Support/                    # AttendanceRecorder, AttendancePaymentGate, TransactionQuota,
│   │                               #   HtmlSanitizer, NavigationMenu
│   └── helpers.php                 # staff_route(), staff_prefix()
├── config/navigation.php           # Sidebar and bottom nav per role
├── database/                       # Migrations, factories, seeders (demo data relative to today)
├── public/assets/                  # theme.css, app.js, Bootstrap, fonts, CKEditor (all local, no CDN)
├── resources/views/
│   ├── staff/                      # Views shared by admin and head
│   ├── head/                       # Head-only views (admin accounts, rules)
│   ├── teacher/ finance/ buyer/    # Other role areas
│   └── layouts/ partials/          # App layout, header, sidebar, toasts
├── routes/
│   ├── web.php                     # Role groups, head-only routes, redirects from old URLs
│   ├── staff.php                   # Shared routes, loaded twice: as admin.* under /admin, as head.* under /head
│   └── api.php                     # /api/user (Sanctum) only
└── tests/
    ├── Unit/                       # Pure logic, no database
    └── Feature/                    # HTTP and database tests (Feature/Staff: admin/head permissions)
```

In shared views, build links with `staff_route('student.show', $student)`. It picks `admin.` or `head.` from the current route, so the same view links to the viewer's own area.

---

## Tech stack

| Layer | Tool |
|---|---|
| Framework | Laravel 13 |
| Language | PHP 8.4 |
| Database | MySQL 8 (Docker, strict mode) |
| Frontend | Bootstrap 5.3 + `public/assets/css/theme.css` + jQuery 3.7 (all local, no CDN) |
| Fonts | Cormorant (headings) + Montserrat (body), self-hosted |
| PDF | barryvdh/laravel-dompdf ^3 |
| Editor | CKEditor 5 (local) + HTMLPurifier on the server |
| Local email | Mailpit (Docker) |
| Tests | PHPUnit 13 (`php artisan test`, database `ballet_test`) |
| CI | GitHub Actions |

---

## Getting started

### Requirements

- **PHP 8.4** with mbstring, openssl, pdo_mysql, xml, ctype, bcmath, fileinfo, gd, intl, zip (macOS: `brew install php@8.4`)
- **Composer 2**
- **Docker** (MySQL and Mailpit)

### Install

```bash
git clone <repository-url> Ballet && cd Ballet
composer install
cp .env.example .env          # set DB_PASSWORD and DB_ROOT_PASSWORD to random values
php artisan key:generate
docker compose up -d --wait   # MySQL 8 + Mailpit, values read from .env
php artisan migrate --seed    # create tables and demo data
php artisan serve
```

Open `http://127.0.0.1:8000` (`localhost:8000` may be taken by another local container). All email (password reset, new accounts) goes to Mailpit at `http://localhost:8025`.

Demo data is relative to today: every active class has a schedule **today**, so attendance can be tried right away. To reset the demo data: `php artisan migrate:fresh --seed` (this deletes all local data).

---

## Tests and CI

Create the test database once:

```bash
docker compose exec mysql sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS ballet_test; GRANT ALL ON ballet_test.* TO \`$MYSQL_USER\`@\`%\`;"'
```

Then:

```bash
php artisan test                      # everything
php artisan test --testsuite=Unit     # pure logic, no database, under a second
php artisan test --testsuite=Feature  # HTTP + database
php artisan test --filter=StudentTest # one class
```

Tests use the separate `ballet_test` database (forced in `phpunit.xml`), so local data is never touched. The database is migrated and seeded once per run.

**CI** ([.github/workflows/ci.yml](.github/workflows/ci.yml)) runs on every push and pull request to `master`:

1. PHP 8.4 and a MySQL 8 service container.
2. `composer install` and `composer audit` (fails on known security advisories).
3. `route:cache` and `route:list` (every route and controller must resolve).
4. Unit tests, then feature tests.

The workflow has a read-only token, uses throwaway database credentials that exist only inside the CI container, and pins every action to a commit SHA.

---

## Production

- `APP_ENV=production`, `APP_DEBUG=false`, and `APP_URL` set to the real HTTPS address (password reset links are built from `APP_URL`).
- `SESSION_SECURE_COOKIE=true`. Set `TRUSTED_PROXIES` when the app runs behind a proxy or load balancer.
- Set `MAIL_*` to a real SMTP server.
- Run `php artisan migrate` on every deploy.
- Add one cron entry for the scheduler:
  `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`
  It runs two daily commands: `age:calculation` (student ages) and `students:remove-finished-trials` (removes trial students who used their trial sessions from every class).
- Old `/admin/...` and `/head/...` bookmarks (for example `/admin/student/view`, `/head/view/class/freeze`) redirect (301) to the new URLs.

---

## Seeded accounts

After `php artisan migrate --seed` (local only, never in production):

| Role | Email | Password |
|---|---|---|
| Head | head@gmail.com | head123 |
| Admin | admin@gmail.com | admin123 |
| Finance | finance@gmail.com | finance123 |
| Teacher | teacher@gmail.com | teacher123 |
| Teacher | sari.teacher@gmail.com, dewi.teacher@gmail.com, maya.teacher@gmail.com | teacher123 |

---

## Troubleshooting

**`SQLSTATE[HY000] [1049] Unknown database`**
The database container is not up, or `.env` does not match it. Run `docker compose up -d --wait` and check `DB_*` in `.env`. For tests, create `ballet_test` (see [Tests and CI](#tests-and-ci)).

**`SQLSTATE[HY000] [2002] Connection refused`**
MySQL is still starting. `docker compose up -d --wait` waits for the health check.

**Email is not sent**
Locally, open Mailpit at `http://localhost:8025`. In production, check `MAIL_*`; for Gmail use an App Password, not the account password.

**403 or missing files under `storage/`**
Run `php artisan storage:link` and make sure `storage/` and `bootstrap/cache/` are writable.

---

## License

Internal project of En Pointe International Ballet Studio.
