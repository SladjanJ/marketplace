# DB dokument — Baza podataka

## 1. Cilj
Ovaj dokument definiše strukturu baze podataka za marketplace aplikaciju, uključujući tabele, polja i relacije.

---

## 2. Pregled sistema
Aplikacija koristi relacionu bazu podataka sa sledećim entitetima:
- users
- ads
- categories
- ad_images

---

## 3. Tabela: users
### Svrha
Čuva podatke o registrovanim korisnicima.

### Polja
- id — bigint, primary key, auto increment
- name — string, nullable false
- email — string, unique, nullable false
- email_verified_at — timestamp, nullable (Laravel default; email verification nije u upotrebi)
- password — string, nullable false
- role — string, default 'user'
- remember_token — string, nullable
- created_at — timestamp
- updated_at — timestamp

### Napomene
- role može da bude 'user' ili 'admin'

---

## 4. Tabela: categories
### Svrha
Čuva dostupne kategorije oglasa.

### Polja
- id — bigint, primary key, auto increment
- name — string, nullable false
- slug — string, unique, nullable false
- created_at — timestamp
- updated_at — timestamp

### Napomene
U MVP-u će biti fiksne kategorije, npr.:
- Prodaja
- Usluge

---

## 5. Tabela: ads
### Svrha
Čuva sve oglase koje korisnici objavljuju.

### Polja
- id — bigint, primary key, auto increment
- user_id — bigint, foreign key -> users.id
- category_id — bigint, foreign key -> categories.id
- title — string, nullable false
- description — text, nullable false
- price — decimal(10,2), nullable false
- location — string, nullable false
- contact_email — string, nullable false
- contact_phone — string, nullable false
- status — string, default 'pending'
- created_at — timestamp
- updated_at — timestamp

### Statusi
- pending
- approved
- rejected
- paused
- sold

### Relacije
- jedan korisnik ima više oglasa
- jedna kategorija ima više oglasa

---

## 6. Tabela: ad_images
### Svrha
Čuva slike koje pripadaju oglasu.

### Polja
- id — bigint, primary key, auto increment
- ad_id — bigint, foreign key -> ads.id
- image_path — string, nullable false
- created_at — timestamp
- updated_at — timestamp

### Relacije
- jedan oglas ima više slika

---

## 7. Veze između tabela

### users -> ads
- jedan user ima više ads
- jedan ad pripada jednom user-u

### categories -> ads
- jedna category ima više ads
- jedan ad pripada jednoj category

### ads -> ad_images
- jedan ad ima više ad_images
- jedna ad_image pripada jednom ad-u

---

## 8. Dodatna pravila poslovne logike
- oglas može da bude objavljen samo ako ima status approved
- admin može da promeni status oglasa u approved, rejected, paused ili sold
- korisnik može da uređuje samo svoje oglasе
- korisnik može da objavi maksimalno 2 oglasa dnevno
- neprijavljeni korisnici ne mogu da vide kontakt podatke niti da objavljuju oglase

---

## 9. Predlog inicijalnih podataka za kategorije
- Prodaja
- Usluge

---

## 10. Predlog implementacije u Laravel-u
- koristiti migracije za kreiranje tabela
- koristiti Eloquent modele: User, Ad, Category, AdImage
- koristiti foreign key constraint-e
- koristiti seedere za osnovne kategorije
