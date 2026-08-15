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
