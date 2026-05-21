# Fleet Manager

System bazodanowy do zarządzania flotą pojazdów firmy: zgłoszenia kierowców
(wypadki, faktury za paliwo, serwis), przypisywanie pojazdów, monitoring
terminów (ubezpieczenia, przeglądy), eksport kosztów do księgowości i raporty.

Aplikacja jest napisana w **Laravel 12 (PHP 8.3) + MySQL 8** i uruchamiana
w **Dockerze** (PHP-FPM + nginx + MySQL) bez żadnej zewnętrznej konfiguracji.

> Pełna dokumentacja techniczna — schemat bazy (ERD), opis każdej encji,
> instrukcja Dockera i polityka wersjonowania — znajduje się w katalogu
> [`docs/`](docs/).

---

## Spis treści

1. [Funkcje wg roli](#funkcje-wg-roli)
2. [Stack technologiczny](#stack-technologiczny)
3. [Szybki start (Docker)](#szybki-start-docker)
4. [Konta testowe](#konta-testowe)
5. [Najczęstsze komendy](#najczęstsze-komendy)
6. [Uruchomienie bez Dockera (XAMPP)](#uruchomienie-bez-dockera-xampp)
7. [Struktura projektu](#struktura-projektu)
8. [Testy](#testy)
9. [Wersjonowanie](#wersjonowanie)

---

## Funkcje wg roli

**Kierowca**
- Zgłasza incydenty (wypadek, naprawa, serwis) wraz z opisem i datą.
- Dodaje koszty (paliwo, części, mandaty) z opcjonalnym uploadem faktury (PDF/JPG/PNG).
- Przegląda historię własnych zgłoszeń i kosztów.

**Fleet Manager**
- Widzi wszystkie pojazdy floty wraz ze statusem (aktywny / serwis / wycofany).
- Przypisuje pojazdy kierowcom (z historią przypisań w `vehicle_assignments`).
- Przegląda i zmienia status zgłoszeń (open → in_progress → closed).
- Monitoruje terminy: wkrótce wygasające (30 dni) i przeterminowane.
- Eksportuje koszty do CSV (z UTF-8 BOM dla Excela) — gotowe do księgowości.

**Administrator**
- Wszystkie uprawnienia kierowcy i managera (via `Policy::before()` w policies).
- Dodaje nowe pojazdy.
- Usuwa zgłoszenia w razie pomyłki.
- Zarządza użytkownikami.
- Generuje raporty zbiorcze (koszty wg kategorii, top pojazdy, podsumowania).

Szczegóły uprawnień: [`docs/features.md`](docs/features.md).

---

## Stack technologiczny

| Warstwa | Technologia |
|--------|-------------|
| Backend | Laravel 12 (PHP 8.3) |
| Baza danych | MySQL 8.0 |
| Frontend | Blade + Tailwind 4 + Vite 7 |
| Autoryzacja | Sesyjna (Laravel) + custom middleware `role` + Policies |
| Konteneryzacja | Docker Compose: PHP-FPM, nginx 1.27, MySQL 8 |
| Eksport | CSV z UTF-8 BOM (streamowany przez `response()->stream`) |

---

## Szybki start (Docker)

### Wymagania na hoście
- Docker Desktop (Windows / macOS) lub Docker Engine + Compose plugin (Linux).
- Git.
- **Nic więcej** — PHP, Composer, Node, MySQL są w obrazie.

### Uruchomienie

```bash
# 1. Sklonuj repozytorium
git clone <URL_REPO> fleet-manager
cd fleet-manager

# 2. Skopiuj plik środowiskowy (Docker)
cp .env.example .env

# 3. Zbuduj obrazy i wystartuj kontenery
docker compose up -d --build

# 4. (Pierwsze uruchomienie) Build assetów frontu - jednorazowo
docker compose exec app npm install
docker compose exec app npm run build
```

Po chwili (entrypoint kontenera `app` zrobi `composer install`,
`key:generate`, `migrate --seed` i `storage:link`) aplikacja jest
dostępna pod:

- **http://localhost:8080** — aplikacja
- **http://localhost:8081** — phpMyAdmin (uruchom: `docker compose --profile tools up -d`)
- **localhost:3307** — MySQL z hosta (np. dla DBeavera; user `fleet`, hasło `secret`)

### Zatrzymanie

```bash
docker compose down            # zatrzymanie (dane zostają w wolumenie)
docker compose down -v         # zatrzymanie + skasowanie bazy
```

---

## Konta testowe

Wszystkie hasła: **`password`**

| Email | Rola |
|------|------|
| `admin@fleet.test` | Administrator |
| `manager@fleet.test` | Fleet Manager |
| `kierowca1@fleet.test` | Kierowca (Jan Kowalski) |
| `kierowca2@fleet.test` | Kierowca (Piotr Nowak) |

---

## Najczęstsze komendy

```bash
# Tail logów aplikacji
docker compose logs -f app

# Wejście do kontenera
docker compose exec app bash

# Artisan / Composer
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan tinker
docker compose exec app composer require <paczka>

# Frontend (Vite hot-reload na hoście)
docker compose exec app npm run dev   # wystawia 5173 - dodaj port jeśli używasz HMR

# Reset bazy i ponowny seed
docker compose exec app php artisan migrate:fresh --seed --force

# Cofnięcie ostatniej migracji (rollback)
docker compose exec app php artisan migrate:rollback
```

---

## Uruchomienie bez Dockera (XAMPP)

Jeśli wolisz lokalny PHP/MySQL z XAMPP-a:

```bash
# w .env ustaw:
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve     # http://127.0.0.1:8000
```

---

## Struktura projektu

```
fleet_menager/
├── app/
│   ├── Enums/UserRole.php           # enum 3 ról
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Driver/              # kontrolery kierowcy
│   │   │   ├── Manager/             # kontrolery managera
│   │   │   └── Admin/               # kontrolery admina
│   │   ├── Middleware/EnsureRole.php
│   │   └── Requests/                # Form Requests (walidacja)
│   ├── Models/                      # User, Vehicle, Event, Cost, ...
│   ├── Policies/                    # autoryzacja per encja
│   └── Services/CostCsvExporter.php
├── database/
│   ├── migrations/                  # pełna historia schematu
│   └── seeders/DatabaseSeeder.php
├── docker/
│   ├── nginx/default.conf
│   └── php/{Dockerfile,php.ini,entrypoint.sh}
├── docker-compose.yml
├── docs/                            # dokumentacja techniczna
│   ├── erd.md
│   ├── database.md
│   ├── features.md
│   ├── docker.md
│   ├── deployment.md
│   └── versioning.md
├── resources/views/                 # Blade
└── routes/web.php
```

---

## Testy

```bash
docker compose exec app php artisan test
```

Testy feature pokrywają najważniejsze ścieżki autoryzacji (driver/manager/admin)
oraz CRUD na incydentach i kosztach.

---

## Wersjonowanie

- Kod w Git z konwencją commitów `type: short message` (`feat:`, `fix:`, `docs:`, `chore:`).
- Każda zmiana schematu = osobny plik w `database/migrations/`. Nie modyfikujemy istniejących migracji w gałęzi `main` — zawsze nowa migracja `php artisan make:migration alter_xxx_add_yyy`.
- Pełny opis: [`docs/versioning.md`](docs/versioning.md).
