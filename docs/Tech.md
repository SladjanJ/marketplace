# Tech dokument — Arhitektura i tehnička implementacija

## 1. Cilj dokumenta
Ovaj dokument opisuje tehničku arhitekturu i implementacioni pristup za marketplace aplikaciju u Laravelu.

---

## 2. Tehnološki stack
### Backend
- PHP 8.2
- Laravel 12
- Eloquent ORM
- Blade templating engine
- Laravel validation i middleware
- Laravel authentication system

### Frontend
- Vite
- Tailwind CSS
- Blade views
- Alpine.js opcionalno, ako bude potreban za interaktivne UI elemente

### Baza podataka
- MySQL preko XAMPP / phpMyAdmin
- Lokalno: `DB_HOST=127.0.0.1`, `DB_PORT=3304` (XAMPP my.ini), `DB_DATABASE=marketplace`, `DB_USERNAME=root`
- Migracije: `php artisan migrate`

### Alati
- Composer
- NPM
- Git/GitHub

---

## 3. Arhitektura aplikacije
Aplikacija će biti organizovana kao standardni Laravel MVC aplikacioni sistem sa sledećim slojevima:
- Routes — definisanje ruta
- Controllers — obrada zahteva
- Models — poslovni model i interakcija sa bazom
- Views — Blade prikazi
- Middleware — zaštita ruta i uloga
- Storage — upload slika

### Struktura po folderima
- app/Http/Controllers
- app/Models
- resources/views
- routes/web.php
- database/migrations
- database/seeders
- public/uploads ili storage/app/public

---

## 4. Glavne module

### 4.1 Autentikacija
- Laravel built-in auth sistem
- registracija i prijava
- email verifikacija (`MustVerifyEmail`, signed link)
- role-based access control

### 4.1.1 Email (Resend)
Za slanje verification mailova koristiti Resend:
- `composer require resend/resend-php`
- `MAIL_MAILER=resend`
- `RESEND_KEY` = API key iz Resend dashboarda
- `MAIL_FROM_ADDRESS` = `onboarding@resend.dev` (test) ili verifikovani domain (produkcija)
- `MAIL_FROM_NAME` = Marketplace
- `APP_URL` mora da odgovara URL-u aplikacije (npr. `http://127.0.0.1:8000`)

Na free / test planu `onboarding@resend.dev` često šalje samo na email vezan za Resend nalog.
Za slanje na bilo koji inbox treba verified domain u Resendu.

Nakon izmene `.env`: `php artisan config:clear`

### 4.2 Oglasi
- CRUD operacije za oglase
- status moderation workflow
- upload slika
- ograničenje objavljivanja na 2 oglasa dnevno

### 4.3 Admin panel
- posebna ruta za admina, npr. /admin
- pregled svih oglasa
- odobravanje i odbijanje oglasa
- upravljanje statusom oglasa po potrebi

### 4.4 Profil korisnika
- stranica sa osnovnim informacijama korisnika
- prikaz svih njegovih oglasa
- statusi oglasa
- opcija za promenu statusa oglasa ako je oglas pauziran ili prodato

### 4.5 Lokalizacija
- izbor jezika na prvom pristupu
- čuvanje izbora jezika u session-u ili user podešavanjima

---

## 5. Modeli i relacije
### User
- ima mnogo Ads
- ima rolu user/admin

### Ad
- pripada jednom User-u
- pripada jednoj Category
- ima više AdImage zapisa

### Category
- ima mnogo Ads

### AdImage
- pripada jednom Ad-u

---

## 6. Rute
### Javne rute
- GET / — početna stranica
- GET /ads — lista oglasa
- GET /ads/{id} — detalji oglasa

### Autentifikovane rute
- GET /ads/create — forma za kreiranje oglasa
- POST /ads — čuvanje oglasa
- GET /ads/{id}/edit — forma za izmenu oglasa
- PUT/PATCH /ads/{id} — ažuriranje oglasa
- DELETE /ads/{id} — brisanje oglasa
- GET /profile — profil korisnika

### Admin rute
- GET /admin/ads — lista svih oglasa za administraciju
- POST /admin/ads/{id}/approve — odobravanje
- POST /admin/ads/{id}/reject — odbijanje

---

## 7. Workflow oglasa
1. Korisnik kreira oglas.
2. Oglas se čuva sa statusom pending.
3. Admin dobija oglas na pregled.
4. Admin odobrava ili odbija oglas.
5. Ako je odobren, oglas postaje javno prikazan.
6. Korisnik može da izmeni oglas i nakon odobrenja, uz odgovarajuće validacije.

---

## 8. Bezbednost
- Laravel CSRF zaštita za POST/PUT/DELETE forme
- middleware za autentikaciju i autorizaciju
- policy ili authorization check za vlasništvo oglasa
- validacija svih unosa
- zaštita od neovlašćenog pristupa admin ruta

---

## 9. Upload slika
- slike će biti smeštene u storage/app/public
- kroz symlink će biti dostupne preko public/storage
- svaka slika će imati naziv po ID-u ili hash-u
- podržati više slika po oglasu

---

## 10. Lokalizacija
- implementacija kroz Laravel localization fajlove
- osnovni tekstovi na srpskom i engleskom
- izbor jezika na prvom pristupu kroz session
- opcionalno čuvanje izbora u user profilu

---

## 11. Dizajn i UX
- Tailwind CSS za brze i moderne UI komponente
- jednostavan layout bez pretrpanosti
- responsivan dizajn za mobilne uređaje
- jasno razdvojene sekcije: početna, oglasi, profil, admin

---

## 12. Implementacioni redosled
1. Konfigurisati bazu i environment
2. Napraviti migracije i modele
3. Implementirati autentikaciju i role
4. Implementirati CRUD za oglase
5. Dodati upload slika
6. Implementirati admin moderaciju
7. Dodati profil korisnika
8. Dodati jezik i lokalizaciju
9. Dodati dizajn i responzivnost
10. Testirati i finalizovati

---

## 13. Napomene za razvoj
- prva verzija treba da bude clean MVP
- ne dodavati komplikovane funkcije pre nego što osnovni flow radi
- fokusirati se na kvalitet, jasnoću i portfolio vrednost
- svaki korak treba da bude testiran pre nego što se pređe na sledeći
