# Ballet — En Pointe International Ballet Studio

[![CI](https://github.com/KarvinNanda/Ballet/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/KarvinNanda/Ballet/actions/workflows/ci.yml)

Web app for the daily operations of a ballet school: attendance, class schedules, the mapping between teachers, students and classes, billing and payments, stock (items sold to students and buyers), and recurring reports.

Built with **Laravel 13**, **PHP 8.4** and **MySQL 8**. The UI is **Bootstrap 5.3** with a custom theme that works on desktop, tablet and phone.

---

## Contents

- [Features](#features)
- [Roles and permissions](#roles-and-permissions)
- [Project structure](#project-structure)
- [Tech stack](#tech-stack)
- [Getting started](#getting-started)
- [Tests and CI](#tests-and-ci)
- [Security](#security)
- [Production](#production)
- [Seeded accounts](#seeded-accounts)
- [Troubleshooting](#troubleshooting)

---

## Features

- **Authentication**: login (rate limited per account and per IP), logout, password reset by email (hashed, single-use tokens, 30 minutes).
- **Accounts**: admin, teacher and finance accounts are created without a password. The new user gets an email with a link to set their own password.
- **Students**: list with search and status filter (active, inactive, trial), detail page with tabs (profile, classes, transactions), terms and conditions must be accepted when a student is created.
- **Classes and courses**: create classes, assign teachers and students, freeze (level up) a class, reset quota. A course cannot be deleted while classes use it. Changing a course price updates only the Unpaid bills of that course.
- **Schedules**: single or weekly schedules per class. Teachers cannot schedule into the past or move a session that has already started.
- **Attendance**:
  - A teacher records attendance from the start of a session until the end of the next day (Asia/Jakarta time). After saving it is locked for the teacher.
  - The head can record and correct any session, for example to add a student the teacher forgot (shown with a "Not recorded yet" badge).
  - A student who must pay first cannot be recorded ("Payment required").
- **Transactions**: student bills and payments. Discount is an amount (`50000`) or a percent (`10%`); totals are computed by `App\Support\Discount`. Marking a bill as paid adds its quota to the student once, also when the form is submitted twice.
- **Stock**: items, stock in (finance) and sales (buyer). A sale above the stock is refused.
- **Buyer**: the cashier page: pick an item, enter the buyer name and quantity, record the sale.
- **Reports** (PDF via DomPDF, open in a new tab): class attendance, active students, stock, teacher reward.
- **Rules & regulations**: rich text (CKEditor 5), sanitized on the server.
- **Profile**: change your own profile and password.
- **Lists**: search (matches `%` and `_` literally), status filters, sortable columns (`aria-sort`), pagination that keeps search, status and sort.

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

- **Teacher**: sees only their own classes and schedules. The home page lists today's sessions and yesterday's sessions that are still open. Records attendance once per session, inside the attendance window.
- **Finance**: marks transactions as paid, records stock in, and has its own stock, active student and teacher reports.
- **Buyer**: records sales of stock items. There is no screen to create buyer accounts; see [Seeded accounts](#seeded-accounts).

---

## Project structure

```
Ballet/
├── .github/workflows/ci.yml        # GitHub Actions: tests on every push and PR to master
├── app/
│   ├── Console/Commands/           # age:calculation, students:remove-finished-trials,
│   │                               #   users:replace-default-passwords
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── staff/              # Shared by admin and head (student, teacher, class, schedule,
│   │   │   │                       #   attendance, transaction, stock, report, finance accounts, dashboard)
│   │   │   ├── head/               # Head only: admin accounts, rules
│   │   │   ├── teacher/            # Teacher area
│   │   │   ├── finance/            # Finance area
│   │   │   └── auth/               # Login
│   │   ├── Middleware/             # EnsureRole, SecurityHeaders (CSP), TrustProxies, ...
│   │   └── Requests/               # Form Requests per area: Staff/, Teacher/, Finance/, Buyer/,
│   │                               #   Head/, Profile/, Auth/, plus SearchRequest for list pages
│   ├── Models/
│   ├── Rules/                      # BoundedDate, NotBeforeJakartaNow
│   ├── Support/                    # Domain logic used by several controllers:
│   │                               #   AttendanceRecorder, AttendanceWindow, AttendancePaymentGate,
│   │                               #   TransactionQuota, Discount, StockMovement, AccountInvite,
│   │                               #   Like, HtmlSanitizer, NavigationMenu, TeacherReportQuery, ...
│   └── helpers.php                 # staff_route(), staff_prefix(), own_url()
├── config/navigation.php           # Sidebar and bottom nav per role
├── database/                       # Migrations, factories, seeders (demo data relative to today)
├── public/assets/
│   ├── css/theme.css               # The whole theme (tokens, components, phone cards, touch targets)
│   ├── js/app.js                   # Shared behaviour: confirm dialogs, tables as cards on phones, ...
│   ├── js/pages/                   # Page scripts (rule editor, transaction form)
│   └── vendor/                     # Bootstrap, jQuery, CKEditor, fonts (all local, no CDN)
├── resources/views/
│   ├── components/                 # Page kit: page-header, filter-bar, sort-th, status-badge,
│   │                               #   empty-state, confirm-form, row-menu, tabs, form/*
│   ├── staff/                      # Views shared by admin and head
│   ├── head/                       # Head-only views (admin accounts, rules)
│   ├── teacher/ finance/ buyer/    # Other role areas
│   ├── profile/ auth/ forgot-password/
│   └── layouts/ partials/          # App and guest layouts, header, sidebar, toasts
├── routes/
│   ├── web.php                     # Role groups, head-only routes, redirects from old URLs
│   ├── staff.php                   # Shared routes, loaded twice: as admin.* under /admin, as head.* under /head
│   └── api.php                     # /api/user (Sanctum) only
└── tests/
    ├── Unit/                       # Pure logic, no database
    └── Feature/                    # HTTP and database tests (Staff/, Teacher/, Finance/, Buyer/, Gaps/, ...)
```

Conventions:

- In shared views, build links with `staff_route('student.show', $student)`. It picks `admin.` or `head.` from the current route, so the same view links to the viewer's own area.
- Pages are built from the components in `resources/views/components` (`<x-page-header>`, `<x-filter-bar>`, `<x-form.field>`, ...). Copy an existing page in the same area before writing a new one.
- No inline JavaScript: no `<script>` blocks without `src`, no `onclick=""`, no `javascript:` URLs. Put behaviour in `public/assets/js/`. `tests/Feature/Staff/InlineScriptTest.php` fails the build otherwise (the Content Security Policy would block it in the browser anyway).
- A "return to the previous page" URL from the request goes through `own_url()` (views) or `backTo()` (controllers), never straight into a redirect or a link.

---

## Tech stack

| Layer | Tool |
|---|---|
| Framework | Laravel 13 |
| Language | PHP 8.4 |
| Database | MySQL 8 (Docker, strict mode) |
| Frontend | Bootstrap 5.3 + `public/assets/css/theme.css` + `public/assets/js/app.js` + jQuery 3.7 (all local, no CDN, no build step) |
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
php artisan serve             # or: php artisan serve --port=8010
```

Open `http://127.0.0.1:8000` (`localhost:8000` may be taken by another local container; use `--port=8010` in that case). All email (password reset, new accounts) goes to Mailpit at `http://localhost:8025`.

Demo data is relative to today: every active class has a schedule **today**, so attendance can be tried right away. To reset the demo data: `php artisan migrate:fresh --seed` (this deletes all local data).

---

## Tests and CI

Create the test database once:

```bash
docker compose exec mysql sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS ballet_test; GRANT ALL ON ballet_test.* TO \`$MYSQL_USER\`@\`%\`;"'
```

Then:

```bash
php artisan test                      # everything (about 880 tests, under a minute)
php artisan test --testsuite=Unit     # pure logic, no database
php artisan test --testsuite=Feature  # HTTP + database
php artisan test --filter=StudentTest # one class
```

Tests use the separate `ballet_test` database (forced in `phpunit.xml`), so local data is never touched. The database is migrated and seeded once per run. Run one test process at a time: two runs in parallel share `ballet_test` and fail at random.

**CI** ([.github/workflows/ci.yml](.github/workflows/ci.yml)) runs on every push and pull request to `master`:

1. PHP 8.4 and a MySQL 8 service container.
2. `composer install` and `composer audit` (fails on known security advisories).
3. `route:cache` and `route:list` (every route and controller must resolve).
4. Unit tests, then feature tests.

The workflow has a read-only token, uses throwaway database credentials that exist only inside the CI container, and pins every action to a commit SHA.

---

## Security

- **Content Security Policy** is enforced by [app/Http/Middleware/SecurityHeaders.php](app/Http/Middleware/SecurityHeaders.php): `script-src 'self'`, no plugins, no framing by other sites. The same middleware sends `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, and HSTS over HTTPS. If a page breaks in production because of the policy, set `CSP_REPORT_ONLY=true` in `.env` (and run `php artisan config:cache`): the browser then only reports violations in the console.
- **Rate limits** ([app/Providers/RouteServiceProvider.php](app/Providers/RouteServiceProvider.php)): every write request (POST, PUT, DELETE) 30 per minute per user; account creation 10 per hour; login 5 attempts; forgot password 5 per minute.
- **Validation**: every write goes through a Form Request in `app/Http/Requests`. List pages drop invalid filters instead of failing (`SearchRequest`).
- **Passwords**: accounts never get a guessable default password (see `AccountInvite`). `users:replace-default-passwords` finds and replaces old `ballet` + date-of-birth passwords from imported data.

---

## Production

- `APP_ENV=production`, `APP_DEBUG=false`, and `APP_URL` set to the real HTTPS address (password reset and invite links are built from `APP_URL`).
- `SESSION_SECURE_COOKIE=true`. Set `TRUSTED_PROXIES` when the app runs behind a proxy or load balancer, so `$request->isSecure()` is true and HSTS is sent.
- `CSP_REPORT_ONLY=false` (the default). See [Security](#security).
- `CACHE_DRIVER` must be shared by all PHP workers (`database` or `redis`): the rate limiters count in the cache. `array` never works; `file` only works on a single server.
- Set `MAIL_*` to a real SMTP server and `MAIL_FROM_ADDRESS`.
- Run `php artisan migrate` on every deploy, then `php artisan route:cache && php artisan config:cache && php artisan view:cache`.
- After importing the client's existing data:
  1. `php artisan migrate`. The unique-index migrations stop with a message if the data has duplicates; clean those first.
  2. `php artisan users:replace-default-passwords` (dry run: lists the accounts), then the same command with `--apply` (sets random passwords and emails each user a set-password link).
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

There is no seeded buyer account. To try the buyer pages locally:

```bash
php artisan tinker --execute="App\Models\User::factory()->create(['role' => 'buyer', 'email' => 'buyer@gmail.com', 'password' => bcrypt('buyer123')]);"
```

---

## Troubleshooting

**`SQLSTATE[HY000] [1049] Unknown database`**
The database container is not up, or `.env` does not match it. Run `docker compose up -d --wait` and check `DB_*` in `.env`. For tests, create `ballet_test` (see [Tests and CI](#tests-and-ci)).

**`SQLSTATE[HY000] [2002] Connection refused`** or **`failed to connect to the docker API`**
Docker Desktop or MySQL is still starting. Open Docker Desktop, then `docker compose up -d --wait` waits for the health check.

**A button or editor does nothing, and the browser console shows "Refused to execute ... Content Security Policy"**
Some script is inline or comes from another host. Move it to `public/assets/js/`. As a temporary measure in production, set `CSP_REPORT_ONLY=true`.

**Old styles after an update**
The theme is a plain CSS file; hard refresh the browser (Cmd+Shift+R / Ctrl+Shift+R).

**Email is not sent**
Locally, open Mailpit at `http://localhost:8025`. In production, check `MAIL_*`; for Gmail use an App Password, not the account password.

**403 or missing files under `storage/`**
Run `php artisan storage:link` and make sure `storage/` and `bootstrap/cache/` are writable.

---

## License

Internal project of En Pointe International Ballet Studio.
