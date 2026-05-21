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
| Optymalizacja zapytań | Eager loading w kontrolerach (`with([...])`), `chunk()` w eksportach CSV, indeksy | kontrolery + `CostCsvExporter` |
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
