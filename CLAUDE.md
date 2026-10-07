# Ballet — project instructions

## Skills
- Always use the **superpowers** skills and the **ui-ux-pro-max** skills.

## Docs
- Specs and plans go to `docs/` (gitignored, local only). Do not write docs to Notion for this repo; this overrides the global CLAUDE.md.
- Do not commit, push, open PRs or create GitHub issues. The user does it. Deferred work goes in the final message only.

## Stack
- Laravel 13, PHP 8.4 (Homebrew `php@8.4`), MySQL 8 in Docker with strict mode, PHPUnit 13.
- Frontend: Bootstrap 5.3 + `public/assets/css/theme.css` + `public/assets/js/app.js`, all local (no CDN, no Vite build).
- Tests: `php artisan test` uses database `ballet_test` (seeded once per run from DatabaseSeeder).
- Main branch is `master`.

## Local setup
```bash
cp .env.example .env              # then set DB_PASSWORD and DB_ROOT_PASSWORD
composer install
php artisan key:generate
docker compose up -d --wait       # MySQL + Mailpit, values read from .env
php artisan migrate:fresh --seed  # drops all tables, recreates them, seeds data
php artisan serve                 # open http://127.0.0.1:8000 (localhost:8000 is another project's container)
```
- Emails go to Mailpit: http://localhost:8025
- Seeded logins: `admin@gmail.com / admin123`, `head@gmail.com / head123`, `teacher@gmail.com / teacher123`, `finance@gmail.com / finance123` (+ sari/dewi/maya.teacher@gmail.com / teacher123).
