# Zgodność z wymaganiami projektu

Mapowanie wymagań z opisu projektu na realizację w repozytorium.

## 2. Zakres projektu

| Wymaganie | Realizacja | Lokalizacja |
|-----------|------------|-------------|
| Wybór SZBD i konfiguracja środowiska | MySQL 8.0 w Dockerze + alternatywa XAMPP | `docker-compose.yml`, `.env.example`, `docs/docker.md` |
| Weryfikacja działania bazy | Healthcheck `mysqladmin ping` w compose, klient z hosta na `:3307`, phpMyAdmin (profil `tools`) | `docker-compose.yml` |
| Analiza wymagań i wstępny model | Funkcje per rola + 7 encji domenowych | `docs/features.md`, `docs/erd.md` |
| Schemat ERD | Diagram w mermaid + opis relacji i polityki usuwania | `docs/erd.md` |
| Iteracyjne projektowanie | Migracje w `database/migrations/` jako historia zmian | `database/migrations/` |
| Normalizacja | 3NF — wyseparowane `vehicle_assignments`, `documents`, `costs` | `docs/database.md` (sekcja "Normalizacja") |
| Optymalizacja schematu | Indeksy proste i złożone na często filtrowanych kolumnach | `docs/database.md` (sekcja "Optymalizacja zapytań") |
| Migracje i rollback | `php artisan migrate / rollback / fresh`; każda migracja ma `up()` i `down()` | `database/migrations/*.php` |
| Seedery | `DatabaseSeeder` tworzy 4 użytkowników, 3 pojazdy, przypisania, zgłoszenia i koszty | `database/seeders/DatabaseSeeder.php` |
| Interfejs użytkownika | Blade + Tailwind, dashboardy per rola, formularze, listy z filtrami | `resources/views/`, `app/Http/Controllers/` |
| CRUD dla głównych encji | Vehicle, Event, Cost, User, VehicleAssignment | `app/Http/Controllers/{Driver,Manager,Admin}/` |
| Klucze obce i cascade delete | `cascadeOnDelete`, `restrictOnDelete`, `nullOnDelete` na każdym FK | wszystkie migracje `2026_05_19_*` |
| Indeksy i ograniczenia unikalności | `plate_number`, `vin` unique; indeksy złożone na `(vehicle_id, event_date)`, `(vehicle_id, incurred_at)`, `(user_id, assigned_from)` itp. | migracje |
| Testowanie CRUD | Testy feature w `tests/Feature/` (auth + autoryzacja kierowcy/managera/admina) | `tests/` |
| Optymalizacja zapytań | Eager loading w kontrolerach (`with([...])`), `chunk()` w eksportach CSV, indeksy, **cache** statystyk dashboardu (60s) | kontrolery + `CostCsvExporter` + `Manager\DashboardController` |
| Dokumentacja techniczna | README + `docs/` (6 plików) | `README.md`, `docs/` |

## 3. Wymagania techniczne

| Wymaganie | Realizacja |
|-----------|------------|
| **Baza**: PostgreSQL lub MySQL | MySQL 8.0 |
| **Framework/Język**: dowolny | Laravel 12 (PHP 8.3) |
| **Docker** | `docker-compose.yml` + dedykowany `docker/php/Dockerfile`, dwa profile (default + `tools`) |
| **Git** | repo z konwencją commitów (`docs/versioning.md`) |
| **Migracje definiują schemat** | Każda zmiana = osobny plik migracji |
| **Seedery** | `DatabaseSeeder` z testowymi danymi |
| **README z instrukcją** | `README.md` w korzeniu |
<<<<<<< HEAD
=======

## 4. Kryteria oceny

| Kryterium | Gdzie szukać |
|-----------|--------------|
| **Model ERD** | `docs/erd.md` (mermaid + tabela polityki usuwania) |
| **Implementacja bazy danych (migracje)** | `database/migrations/` (7 migracji domenowych + Laravelowe) |
| **Implementacja więzów integralności** | Każda migracja w `2026_*` — `constrained()->cascadeOnDelete()` / `restrictOnDelete()` / `nullOnDelete()`, indeksy, unique |
| **Poprawność działania GUI** | `resources/views/` + ekrany dashboard / lista / formularz dla każdej roli, autoryzacja via Policies, **AJAX dismiss alertów** |
| **Użycie Dockera** | `docker compose up -d --build` startuje całość w 3-5 min; `docs/docker.md` opisuje architekturę |
| **Seedery i migracje** | `php artisan migrate --seed` jest częścią entrypointu, działa od zera |
| **Testowanie i optymalizacja** | `tests/Feature/` + sekcja "Optymalizacja zapytań" w `docs/database.md` |
| **Dokumentacja techniczna** | `README.md` + `docs/{erd,database,features,docker,deployment,versioning,compliance}.md` |

## 5. Lista 20 wymagań technicznych (rozszerzenie)

| # | Wymaganie | Status | Lokalizacja w kodzie |
|---|-----------|--------|----------------------|
| 1 | framework MVC | ✅ | Laravel 12 — `app/Http/Controllers/`, `app/Models/`, `resources/views/`; `composer.json` → `"laravel/framework": "^12.0"` |
| 2 | framework CSS | ✅ | Tailwind 4 — `resources/css/app.css`, `package.json`, `vite.config.js` |
| 3 | baza danych | ✅ | MySQL 8 — `docker-compose.yml` (serwis `mysql`), `database/migrations/` |
| 4 | cache | ✅ | `Cache::remember('manager.dashboard.stats', 60, ...)` w `app/Http/Controllers/Manager/DashboardController.php`; driver `database` w `config/cache.php` + migracja `create_cache_table` |
| 5 | dependency manager | ✅ | Composer (`composer.json`) + npm (`package.json`) |
| 6 | HTML | ✅ | Blade — `resources/views/layouts/app.blade.php` + każdy widok per rola |
| 7 | CSS | ✅ | Tailwind klasy w widokach — np. `bg-white p-4 rounded shadow` |
| 8 | JavaScript | ✅ | Vanilla JS (`onsubmit="confirm()"`) + fetch API w `resources/views/manager/alerts/index.blade.php` (dismiss alertu); axios w `resources/js/bootstrap.js` |
| 9 | routing + pretty URLs | ✅ | `routes/web.php` — grupy `prefix('admin')`, route model binding, named routes |
| 10 | ORM | ✅ | Eloquent — wszystkie modele w `app/Models/`; cast enum w `User` |
| 11 | uwierzytelnianie | ✅ | `app/Http/Controllers/Auth/LoginController.php`, middleware `auth`, `'password' => 'hashed'` cast |
| 12 | **lokalizacja** | ✅ | `lang/en.json` + middleware `app/Http/Middleware/SetLocale.php` + `LocaleController` + switcher PL/EN w `layouts/app.blade.php`; trasa `/locale/{locale}` |
| 13 | **mailing** | ✅ | `app/Mail/IncidentReported.php` (Markdown Mailable) + `resources/views/emails/incident.blade.php` + wysyłka w `Driver\IncidentController::store()` do wszystkich managerów; driver `log` (mail trafia do `storage/logs/laravel.log`) |
| 14 | formularze | ✅ | Form Requesty w `app/Http/Requests/` + formularze Blade z `@csrf` |
| 15 | **asynchroniczne interakcje** | ✅ | `fetch()` z PATCH + JSON response w `resources/views/manager/alerts/index.blade.php`; `AlertController::dismiss` zwraca JSON gdy `wantsJson()` |
| 16 | konsumpcja API | ❌ | Brak (nie zaimplementowane) |
| 17 | publikacja API | ❌ | Brak `routes/api.php` (nie zaimplementowane) |
| 18 | RWD | ✅ | Klasy responsywne Tailwind (`md:grid-cols-3`, `md:grid-cols-5`, `flex-wrap`) w widokach |
| 19 | **logger** | ✅ | `Log::channel('fleet')->info()` w `LoginController`, `Driver\IncidentController`, `Driver\CostController`, `Admin\VehicleController`, `Admin\EventController`; dedykowany kanał `fleet` w `config/logging.php` (plik dzienny `storage/logs/fleet-YYYY-MM-DD.log`) |
| 20 | deployment | ✅ | Docker: `docker-compose.yml`, `docker/php/Dockerfile`, `docker/nginx/default.conf`, `docker/php/entrypoint.sh`, instrukcja w `README.md` |

**Wynik: 18/20 (90%)** ✅
>>>>>>> 3f466d6 (Little Changes)
