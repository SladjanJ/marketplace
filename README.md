# Marketplace

**Classified ads marketplace with admin moderation — built as a Laravel portfolio MVP.**

[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com/)
[![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Blade](https://img.shields.io/badge/Blade-templates-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com/docs/blade)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-11-36648F?style=flat-square)](https://phpunit.de/)

Marketplace is an MVP web application where **Users** post classified ads and an **Admin** reviews them before they go live. Guests can browse approved listings; only signed-in users can post ads or see seller contact details. There is no chat and no online payment.

Languages: **Serbian (SR, Latin)** and **English (EN)**.

---

## Table of contents

- [Features](#features)
- [Tech stack](#tech-stack)
- [Architecture](#architecture)
- [User roles & flows](#user-roles--flows)
- [Business rules](#business-rules)
- [Routes](#routes)
- [Getting started](#getting-started)
- [Environment variables](#environment-variables)
- [MySQL setup](#mysql-setup)
- [Database migrations](#database-migrations)
- [Deployment](#deployment)
- [Manual testing & demo](#manual-testing--demo)
- [Security](#security)
- [Scripts](#scripts)
- [Roadmap (post-MVP)](#roadmap-post-mvp)

---

## Features

### Authentication
- Register and log in (account is active immediately — no email verification)
- Login, logout, forgot password, reset password via email link
- Two roles: **user** and **admin**
- Guests can browse approved ads; posting and contact details require login

### Ads
- Create, view, edit, and delete your own ads
- Required fields: title, description, price, category, location, contact email, contact phone, 1–4 photos
- Categories (MVP): **Sale** / **Prodaja**, **Services** / **Usluge**
- New ads start as `pending` and wait for admin review
- Owner can pause a live ad, put it back on sale, or mark it as sold
- Editing a rejected ad sends it back to `pending`
- Daily posting limit: **2 ads per user**
- Image gallery with lightbox on the ad page

### Admin
- Dedicated dashboard at `/admin/dashboard`
- Pending queue with approve / reject
- Reviewed table for already moderated ads
- Admin can open any ad (including pending) from the dashboard

### Profile
- Account name and email
- Full list of the user’s ads with status badges
- Language setting (saved on the user record)

### Internationalization
- First-visit language popup (Serbian / English)
- Closing the popup keeps English and hides it next time
- Choice stored in session + cookie; signed-in users also save `users.locale`
- Language can be changed later in profile settings
- UI strings, categories, statuses, and validation messages are translated

---

## Tech stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2, Laravel 12, Eloquent ORM |
| Auth | Laravel session auth, custom controllers |
| UI | Blade, Bootstrap 5.3 (CDN), Bootstrap Icons |
| i18n | Laravel localization (`lang/en`, `lang/sr`) |
| Database | MySQL (local XAMPP / phpMyAdmin) |
| Files | `storage/app/public` via `php artisan storage:link` |
| Tests | PHPUnit 11 feature tests |
| Tooling | Composer, Vite (available; live UI uses Bootstrap CDN) |

---

## Architecture

```
app/
├── Http/
│   ├── Controllers/        # Auth, ads, profile, admin, locale, password reset
│   └── Middleware/         # SetLocale, EnsureAdmin
├── Models/                 # User, Ad, AdImage
├── Policies/               # AdPolicy (view / update / delete / changeStatus)
└── Support/                # LocaleManager
resources/views/
├── ads/                    # Index, create, edit, show, shared form
├── admin/                  # Moderation dashboard
├── auth/                   # Login, register, forgot / reset password
├── profile/
├── partials/               # Ad card, create-ad card
└── layout.blade.php
lang/
├── en/                     # ui, categories, status, auth, validation
└── sr/
database/
├── migrations/
├── seeders/                # Demo user + admin
└── factories/
routes/web.php
tests/Feature/MarketplaceTest.php
```

**Access rules:**
- Guests → approved ads only; redirected to login for create / edit / profile
- Users → own ads for edit / delete / pause / sold; contact details on approved ads
- Admins → `/admin/*` via `auth` + `admin` middleware; can view pending ads
- Policies block other users from editing or changing someone else’s ad

**Ad workflow:**
1. User submits an ad → `pending`
2. Admin approves or rejects
3. Approved ads appear on the public home page
4. Owner may pause (hidden) or mark sold (final); resume from paused goes live immediately
5. Owner edit of a rejected ad returns it to `pending`

---

## User roles & flows

| Role | Description |
|------|-------------|
| **Guest** | Browse approved ads; cannot post or see seller contact |
| **User** | Register, post ads, manage own listings, see contact details |
| **Admin** | Review pending ads and approve or reject them |

### Typical demo path

1. Open the site → choose language (or close the popup for English)
2. Browse approved ads as a guest — contact block asks you to log in
3. Register (or log in as the seeded user) → create an ad with 1–4 photos
4. Ad is `pending`; owner sees it under **Your ads** on home and on the profile
5. Log in as admin → `/admin/dashboard` → approve or reject
6. Approved ad appears on the public listing; guests still do not see contact
7. Owner can pause, resume, mark sold, edit, or delete

---

## Business rules

| Rule | Value |
|------|-------|
| Roles | `user`, `admin` |
| Ads per user per day | 2 |
| Photos per ad | 1–4 (JPG, PNG, WebP, max 4 MB each) |
| Categories | `sale`, `services` |
| New ad status | `pending` |
| Public listing | `approved` only |
| Owner status changes | `approved` → `paused` / `sold`; `paused` → `approved` / `sold` |
| Sold / pending / rejected | Owner cannot change status (rejected: edit to resubmit) |
| Contact details | Visible only to authenticated users |
| Email verification | Not used — registration logs the user in immediately |
| Payment | None (classified ads only) |

---

## Routes

| Route | Access | Description |
|-------|--------|-------------|
| `GET /` | Public | Latest approved ads (+ owner’s non-public ads when signed in) |
| `POST /locale` | Public | Set language (`en` / `sr`) |
| `GET /ads/{ad}` | Public* | Ad details (`*` pending / paused / sold: owner or admin only) |
| `GET /login`, `POST /login` | Guest | Login |
| `GET /register`, `POST /register` | Guest | Register and sign in |
| `GET /forgot-password`, `POST /forgot-password` | Guest | Request reset email |
| `GET /reset-password/{token}`, `POST /reset-password` | Guest | Set a new password |
| `POST /logout` | Auth | Log out |
| `GET /ads/create`, `POST /ads` | Auth | Create ad |
| `GET /ads/{ad}/edit`, `PUT /ads/{ad}` | Owner | Edit ad |
| `PATCH /ads/{ad}/status` | Owner | Pause / resume / mark sold |
| `DELETE /ads/{ad}` | Owner | Delete ad and photos |
| `GET /profile` | Auth | Profile, ads, language settings |
| `GET /admin/dashboard` | Admin | Moderation dashboard |
| `POST /admin/ads/{ad}/approve` | Admin | Approve ad |
| `POST /admin/ads/{ad}/reject` | Admin | Reject ad |

---

## Getting started

### Prerequisites

- PHP 8.2+
- Composer
- MySQL (XAMPP / phpMyAdmin is fine)
- Node.js + npm (optional — only if you run Vite)

### Install & run

```bash
git clone <your-repo-url>
cd marketplace
composer install
copy .env.example .env
php artisan key:generate
```

On macOS / Linux use `cp .env.example .env` instead of `copy`.

Create a MySQL database named `marketplace`, then set `DB_*` in `.env` (see [Environment variables](#environment-variables)).

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

`storage:link` is required so uploaded ad photos are served from `/storage`.

### Tests

```bash
php artisan test
```

Feature tests use an in-memory SQLite database (`phpunit.xml`) and do not need MySQL.

---

## Environment variables

Copy `.env.example` to `.env`. **Never commit `.env`.**

| Variable | Required | Description |
|----------|----------|-------------|
| `APP_NAME` | Yes | App name (default `PlaceMarket`) |
| `APP_KEY` | Yes | `php artisan key:generate` |
| `APP_URL` | Yes | Local: `http://127.0.0.1:8000` — must match how you open the app (images and password-reset links use this) |
| `APP_LOCALE` | No | Fallback locale (`en`) |
| `DB_CONNECTION` | Yes | `mysql` |
| `DB_HOST` | Yes | `127.0.0.1` |
| `DB_PORT` | Yes | `3306` by default; some XAMPP setups use another port (e.g. `3304`) |
| `DB_DATABASE` | Yes | `marketplace` |
| `DB_USERNAME` | Yes | Usually `root` locally |
| `DB_PASSWORD` | Yes | Local XAMPP is often empty |
| `MAIL_MAILER` | Yes | `log` for local (writes to `storage/logs`), or SMTP / Resend for real reset emails |
| `MAIL_FROM_ADDRESS` | Yes (SMTP) | Sender address |
| `MAIL_PASSWORD` | Yes (SMTP) | SMTP / Resend API key — **never commit** |

After changing `.env`:

```bash
php artisan config:clear
```

Without SMTP, password-reset emails are written to the log (`MAIL_MAILER=log`) or skipped depending on your config. The reset **flow** still works in tests with `MAIL_MAILER=array`.

---

## MySQL setup

1. Start MySQL (XAMPP Control Panel or your local service).
2. In phpMyAdmin (or the MySQL CLI), create an empty database: `marketplace`.
3. Match `.env` to your server (`DB_PORT` is often `3306`; confirm in `my.ini` if the connection fails).
4. Run `php artisan migrate --seed`.

Seeded accounts (password for both: `password`):

| Role | Email | Password |
|------|-------|----------|
| User | `test@example.com` | `password` |
| Admin | `admin@example.com` | `password` |

---

## Database migrations

| Migration | Purpose |
|-----------|---------|
| `0001_01_01_000000` | `users` (incl. `role`), password reset tokens, sessions |
| `0001_01_01_000001` | Cache tables |
| `0001_01_01_000002` | Jobs tables |
| `2026_08_09_000000` | `ads`, `ad_images` |
| `2026_08_14_000000` | `users.locale`; normalize category values to `sale` / `services` |

**Tables (MVP):**

- `users` — name, email, password, `role` (`user` / `admin`), `locale`
- `ads` — title, description, price, `category` (string), location, `contact_info`, `status`
- `ad_images` — `path` on the `public` disk

Categories are stored as slugs (`sale`, `services`) and translated in `lang/*/categories.php`, not as a separate `categories` table.

---

## Deployment

Typical Laravel host (shared hosting, Forge, or a VPS):

1. Point the web root at `public/`
2. Set production `.env` (`APP_ENV=production`, `APP_DEBUG=false`, real `APP_URL`, MySQL, SMTP)
3. `composer install --no-dev --optimize-autoloader`
4. `php artisan migrate --force`
5. `php artisan storage:link`
6. `php artisan config:cache` and `php artisan route:cache`

Use hosting SMTP or [Resend](https://resend.com) with a verified domain for password-reset emails in production.

---

## Manual testing & demo

### Demo accounts

After `php artisan db:seed`:

| Role | Email | Password |
|------|-------|----------|
| User | `test@example.com` | `password` |
| Admin | `admin@example.com` | `password` |

### Demo checklist

- [ ] Language popup on first visit; SR/EN switch changes navbar and forms
- [ ] Guest sees only approved ads and no seller email / phone
- [ ] Register → immediately logged in → create ad with photos
- [ ] Third ad the same day is blocked (daily limit)
- [ ] Pending ad visible to owner on home (**Your ads**) and profile, hidden from guests
- [ ] Admin approves → ad appears on the public listing
- [ ] Admin rejects → owner edits → status returns to `pending`
- [ ] Owner: Pause, Put back on sale, Mark as sold
- [ ] Click ad photo → full-size lightbox
- [ ] Other user cannot edit or delete someone else’s ad
- [ ] Forgot password sends a reset link (check `storage/logs` if `MAIL_MAILER=log`)

---

## Security

- Laravel **CSRF** on all POST / PUT / PATCH / DELETE forms
- **`auth`** middleware on create / edit / delete / profile
- **`admin`** middleware (`EnsureAdmin`) on `/admin/*` — non-admins get 403
- **`AdPolicy`** for view, update, delete, and owner status transitions
- Server-side validation on ads, auth, locale, and password reset
- Daily ad limit enforced in the controller, not only in the UI
- Guests cannot read contact details
- Uploaded images stored on the `public` disk; max 4 files, image MIME types only
- `.gitignore` excludes `.env`, `/docs`, vendor, and local tooling

Before pushing to a public repository, confirm no secrets are staged:

```bash
git status
git diff --staged
```

---

## Scripts

```bash
php artisan serve              # Dev server (http://127.0.0.1:8000)
php artisan migrate --seed     # Run migrations and create demo users
php artisan storage:link       # Public symlink for uploaded photos
php artisan test               # PHPUnit feature / unit tests
php artisan config:clear       # After .env changes
composer run dev               # Serve + queue + logs + Vite (optional)
```

---

## Roadmap (post-MVP)

Not included in the current MVP:

- Chat between users
- Likes and comments
- Wishlist / favorites
- Advanced filters and recommendations
- Richer admin analytics
- Payments or premium listings
- Separate `categories` table
- Search by title / location on the home page

---

## License

Private project — all rights reserved unless otherwise specified.
