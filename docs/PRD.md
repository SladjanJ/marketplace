# PRD — Marketplace Web Aplikacija

## 1. Uvod
Ovaj dokument definiše plan razvoja marketplace aplikacije u Laravelu i PHP-u, sa fokusom na CRUD funkcionalnost za oglase, moderaciju od strane administratora, podršku za srpski i engleski jezik i kvalitetan portfolio projekat.

Cilj je da aplikacija bude realna, laka za korišćenje, estetski dizajnirana i spremna za buduće proširenje.

---

## 2. Glavni cilj projekta
Izgraditi funkcionalan marketplace sistem u kom korisnik može:
- da kreira oglas,
- da pregleda oglase,
- da ažurira svoj oglas,
- da obriše svoj oglas,
- dok admin može da pregleda i odobri ili odbije oglase.

---

## 3. Lični cilj
Ovaj projekat treba da posluži kao jak portfolio projekat za buduće klijente i poslodavce, sa naglaskom na:
- Laravel i PHP razvoj,
- autentikaciju i autorizaciju,
- CRUD operacije,
- admin panel,
- kvalitetan UI/UX,
- realan business workflow.

---

## 4. Ciljna publika
### 4.1 Korisnici
Korisnici koji žele da:
- objave oglas,
- pregledaju oglase,
- upravljaju svojim oglasima,
- vide status svojih objava.

### 4.2 Administrator
Osoba koja:
- pregleda nove oglase,
- odobrava ili odbija objave,
- nadgleda sadržaj aplikacije.

---

## 5. Funkcionalni zahtevi

### 5.1 Autentikacija i pristup
- korisnik može da se registruje i prijavi,
- nakon registracije korisnik dobija email sa linkom za verifikaciju naloga,
- nalog se smatra potvrđenim tek nakon klika na verification link iz emaila,
- nakon uspešne verifikacije korisnik se preusmerava na početnu (hero) stranicu kao prijavljen korisnik,
- korisnik mora da bude prijavljen i da ima verifikovan email da bi kreirao oglas,
- neprijavljeni korisnici mogu da pregledaju oglase,
- neprijavljeni korisnici ne mogu da kontaktiraju vlasnika oglasa niti da objavljuju oglase,
- aplikacija mora imati dve uloge: user i admin.
- admin pristupa posebnoj ruti, npr. /admin.

### 5.2 CRUD za oglase
Korisnik može:
- da kreira oglas,
- da vidi oglas,
- da ažurira oglas,
- da obriše oglas.

### 5.3 Obavezna polja oglasa
Svaki oglas mora da sadrži:
- naslov oglasa,
- opis oglasa,
- cenu,
- kategoriju,
- lokaciju,
- kontakt informacije,
- najmanje jednu sliku (obavezno),
- maksimalno 4 slike,
- datum objave,
- status oglasa.

### 5.4 Kategorije oglasa
Prilikom kreiranja oglasa korisnik bira kategoriju.

U MVP verziji će biti fiksne kategorije, npr.:
- Prodaja
- Usluge

### 5.5 Status oglasa
Svaki oglas ima status:
- pending — čeka odobrenje,
- approved — odobren,
- rejected — odbijen,
- paused — pauziran,
- sold — prodato.

### 5.6 Admin moderacija
Admin može:
- da vidi sve oglase u sistemu,
- da vidi oglase koji čekaju odobrenje,
- da odobri oglas,
- da odbije oglas.

### 5.7 Status na samom oglasu
Korisnik može da vidi status svog oglasa direktno na oglasu i da ima opciju za promenu statusa ako oglas više nije aktuelan, npr.:
- pauziran,
- prodato.

### 5.8 Profil korisnika
Korisnik ima profil u kom može da vidi:
- svoje osnovne informacije,
- svoje oglase,
- status svakog oglasa,
- opciju za uređivanje svojih oglasa.

### 5.9 Jezici i lokalizacija
Aplikacija mora imati izbor jezika na prvom pristupu:
- engleski,
- srpski.

Na prvom ulasku korisniku se prikazuje poruka sa izborom jezika, a nakon izbora aplikacija ostaje na tom jeziku sve dok korisnik ne promeni jezik u podešavanjima.

### 5.10 Ograničenje objavljivanja oglasa
Da bi se smanjio spam i nepoželjan sadržaj, korisnik može da objavi maksimalno 2 oglasa dnevno.

### 5.11 Kontaktiranje oglasa
Neprijavljeni korisnici ne mogu da kontaktiraju vlasnika oglasa.
Prijavljeni korisnici mogu da vide kontakt podatke samo ako su registrovani.

---

## 6. Nefunkcionalni zahtevi
- sigurnost: zaštita od CSRF, XSS, SQL injection i neovlašćenog pristupa,
- jednostavan i moderan UI,
- estetski dizajn bez pretrpanosti,
- responzivan dizajn za desktop i mobilne uređaje,
- modularna i održiva arhitektura,
- lako proširivanje u budućnosti.

---

## 7. Tehnološki stack
- Backend: PHP 8.2 + Laravel 12
- Baza: MySQL preko XAMPP / phpMyAdmin
- Frontend: Blade, Vite, Tailwind CSS
- Ostalo: Composer, NPM, Git/GitHub

---

## 8. Struktura podataka

### 8.1 Tabela users
- id
- name
- email
- password
- role
- created_at
- updated_at

### 8.2 Tabela ads
- id
- user_id
- title
- description
- price
- category_id
- location
- contact_email
- contact_phone
- status
- created_at
- updated_at

### 8.3 Tabela categories
- id
- name
- slug
- created_at
- updated_at

### 8.4 Tabela ad_images
- id
- ad_id
- image_path
- created_at
- updated_at

### 8.5 Relacije
- User ima mnogo oglasa
- Ad pripada jednoj kategoriji
- Ad ima više slika

---

## 9. Korisnički tokovi

### 9.1 Tok korisnika
1. Korisnik ulazi na aplikaciju.
2. Prvi put vidi popup za izbor jezika.
3. Može da pregleda oglase bez prijave.
4. Ako želi da objavi oglas, mora da se registruje.
5. Nakon registracije dobija email za verifikaciju i vidi obaveštenje da potvrdi nalog.
6. Klikom na link iz emaila potvrđuje nalog i dolazi na početnu stranicu kao prijavljen korisnik.
7. Korisnik kreira oglas sa svim obaveznim poljima.
8. Oglas ide u status pending.
9. Admin pregledava i odobrava ili odbija oglas.
10. Ako je odobren, oglas postaje javno dostupan.
11. Korisnik može da uređuje svoj oglas i da menja podatke kao što je cena ili opis.
12. Korisnik može da vidi sve svoje oglase u profilu.

### 9.2 Tok administratora
1. Admin se prijavljuje.
2. Ulazi u admin dashboard.
3. Pregleda oglase koji čekaju odobrenje.
4. Odlučuje da li će oglas odobriti ili odbiti.
5. Može da pregleda sve objave u sistemu.

---

## 10. Dizajn i korisničko iskustvo
### 10.1 Opšti stil
Dizajn treba da bude:
- lep,
- moderan,
- privlačan,
- estetski popunjen,
- a ne pretrpan.

### 10.2 Početna stranica
- prikaz hero sekcije,
- prikaz najnovijih ili najaktuelnijih oglasa,
- pretraga i filteri po kategoriji i lokaciji,
- dugme za kreiranje oglasa za prijavljene korisnike.

### 10.3 Stranica za kreiranje oglasa
- formular sa poljima za naslov, opis, cenu, kategoriju, lokaciju, kontakt podatke i slike,
- jasne greške pri nevalidnom unosu,
- potvrda da je oglas poslat na čekanje.

### 10.4 Stranica oglasa
- prikaz detalja oglasa,
- slike,
- kontakt podaci,
- status traka na vrhu oglasa u profilu korisnika.

### 10.5 Profil korisnika
- osnovne informacije korisnika,
- lista njegovih oglasa,
- status svakog oglasa,
- opcije za uređivanje i brisanje oglasa.

### 10.6 Admin dashboard
- tabela svih oglasa,
- filter po statusu,
- dugmad za odobravanje i odbijanje,
- osnovne statistike po mogućnosti.

---

## 11. Bezbednosni zahtevi
- samo prijavljeni i email-verifikovani korisnici mogu da objavljuju oglase,
- korisnik može da uređuje i briše samo svoje oglase,
- admin ima pristup svim oglasima,
- validacija svih polja,
- zaštita od neovlašćenih akcija,
- ograničenje objavljivanja oglasa na 2 dnevno po korisniku.

---

## 12. MVP funkcionalnosti
U prvoj verziji implementirati sledeće:
- registracija i prijava,
- email verifikacija naloga nakon registracije,
- uloge user i admin,
- kreiranje oglasa,
- prikaz liste oglasa,
- pregled detalja oglasa,
- uređivanje i brisanje sopstvenih oglasa,
- admin dashboard,
- moderacija oglasa,
- izbor jezika na prvom pristupu,
- profil korisnika sa njegovim oglasima,
- ograničenje objavljivanja na 2 oglasa dnevno,
- upload više slika.

---

## 13. Van opsega MVP-a
Ovo neće biti deo prve verzije:
- chat između korisnika,
- lajkovanje i komentari,
- wishlist/favorites,
- napredni filteri i preporuke,
- napredna analytics za admina,
- plaćanja ili premium funkcionalnosti.

---

## 14. Plan razvoja po fazama

### Faza 1 — Osnova projekta
- Laravel setup,
- povezivanje sa MySQL bazom,
- konfiguracija environment fajla,
- kreiranje osnovnih migracija.

### Faza 2 — Autentikacija i role
- registracija i prijava,
- email verifikacija (slanje maila, potvrda linkom, redirect na početnu),
- role user/admin,
- middleware za pristup (`auth`, `verified`, `admin`).

### Faza 3 — CRUD oglasi
- kreiranje oglasa,
- prikaz liste oglasa,
- detalji oglasa,
- ažuriranje i brisanje oglasa.

### Faza 4 — Admin moderacija
- admin dashboard,
- status pending/approved/rejected,
- odobravanje i odbijanje oglasa.

### Faza 5 — Profil korisnika
- profil stranica,
- lista korisnikovih oglasa,
- status traka na oglasima.

### Faza 6 — Jezici i UX
- izbor jezika na prvom pristupu,
- lokalizacija osnovnih UI elemenata,
- lepši dizajn i responzivnost.

### Faza 7 — Testiranje i finalizacija
- testiranje funkcionalnosti,
- ispravljanje grešaka,
- priprema za demo i portfolio prezentaciju.

---

## 15. Acceptance criteria
Aplikacija se smatra uspešno implementiranom kada:
- korisnik može da se registruje i prijavi,
- nakon registracije korisnik dobija email i mora da potvrdi nalog klikom na link,
- nakon verifikacije korisnik stiže na početnu stranicu kao prijavljen,
- korisnik može da pregleda oglase bez prijave,
- prijavljeni i verifikovani korisnik može da kreira oglas,
- korisnik može da uređuje i briše svoj oglas,
- admin može da odobri ili odbije oglas,
- prikazan je profil korisnika sa njegovim oglasima,
- aplikacija podržava srpski i engleski jezik,
- aplikacija ima estetski dizajn i osnovnu bezbednost,
- korisnik može da objavi maksimalno 2 oglasa dnevno.

---

## 16. Rizici i izazovi
- prevelik fokus na dizajn umesto na funkcionalnost,
- nejasna struktura statusa i workflow-a,
- potreba za dobrim validacijama i zaštitom od spam-a,
- vreme potrebno za upload slika i organizaciju fajlova,
- potreba za pažljivim planiranjem admin panela.

---

## 17. Zaključak
Ova verzija projekta predstavlja dobar MVP za marketplace aplikaciju sa realnim funkcionalnim flow-om, jasnim ulogama i dobrom osnovom za buduće proširenje. Fokus će biti na clean implementaciji, kvalitetnom korisničkom iskustvu i tome da projekt izgleda ozbiljno i profesionalno.

Nijedna dodatna pitanja ne izgledaju neophodna za nastavak, pa se može preći na implementaciju MVP verzije.
