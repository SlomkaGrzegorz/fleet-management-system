# Struktura bazy danych

Wszystkie tabele tworzone są migracjami w `database/migrations/`. Poniżej szczegółowy opis każdej z tabel domenowych (pomijam standardowe Laravelowe: `cache`, `jobs`, `sessions`, `password_reset_tokens`).

Schemat ERD: [`erd.md`](erd.md).

## `users`

Tabela rozszerzona o kolumnę `role` (migracja `2026_05_19_124117_add_role_to_users_table`).

| Kolumna | Typ | Uwagi |
|---------|-----|-------|
| `id` | `bigint unsigned` | PK, auto-increment |
| `name` | `varchar(255)` | |
| `email` | `varchar(255)` | UNIQUE |
| `email_verified_at` | `timestamp NULL` | |
| `password` | `varchar(255)` | hash bcrypt |
| `role` | `ENUM('admin','manager','driver')` | default `'driver'` — castowany na `App\Enums\UserRole` |
| `remember_token`, `created_at`, `updated_at` | standard Laravel | |

## `vehicles`

| Kolumna | Typ | Uwagi |
|---------|-----|-------|
| `id` | PK | |
| `plate_number` | `varchar` UNIQUE | numer rejestracyjny |
| `make`, `model` | `varchar` | |
| `year` | `smallint unsigned` | rok produkcji |
| `vin` | `char(17)` UNIQUE NULL | VIN |
| `status` | `ENUM('active','in_service','retired','sold')` | default `'active'` |
| `assigned_user_id` | FK → `users.id` ON DELETE **SET NULL** | aktualny kierowca |

**Indeksy**: `plate_number` (unique), `vin` (unique), domyślny indeks na FK.

## `vehicle_assignments`

Historia przypisań pojazdu do kierowcy. Pozwala spojrzeć wstecz "kto jeździł danym pojazdem w marcu".

| Kolumna | Typ | Uwagi |
|---------|-----|-------|
| `id` | PK | |
| `vehicle_id` | FK → `vehicles.id` ON DELETE **CASCADE** | |
| `user_id` | FK → `users.id` ON DELETE **CASCADE** | |
| `assigned_from` | `date` | |
| `assigned_until` | `date NULL` | NULL = trwa nadal |

**Indeksy złożone**: `(vehicle_id, assigned_from)`, `(user_id, assigned_from)`.

## `events`

Centralna tabela zgłoszeń / serwisów / ubezpieczeń.

| Kolumna | Typ | Uwagi |
|---------|-----|-------|
| `id` | PK | |
| `vehicle_id` | FK → `vehicles.id` **CASCADE** | |
| `reported_by` | FK → `users.id` **RESTRICT** | autor zgłoszenia |
| `type` | ENUM `insurance / inspection / service / repair / incident / other` | |
| `event_date` | `date` | data zdarzenia |
| `expiry_date` | `date NULL` | termin ważności (dla ubezpieczeń, przeglądów) |
| `notes` | `text NULL` | opis |
| `status` | ENUM `open / in_progress / closed` | default `'open'` |

**Indeksy**: `(vehicle_id, event_date)`, `expiry_date`, `status`, `type`.

## `costs`

Koszty floty — paliwo, serwis, ubezpieczenia, mandaty itp. Eksportowane do CSV dla księgowości.

| Kolumna | Typ | Uwagi |
|---------|-----|-------|
| `id` | PK | |
| `vehicle_id` | FK → `vehicles.id` **CASCADE** | |
| `event_id` | FK → `events.id` **SET NULL** | opcjonalne powiązanie ze zdarzeniem (np. koszt naprawy po wypadku) |
| `entered_by` | FK → `users.id` **RESTRICT** | autor wpisu |
| `category` | ENUM `fuel / service / repair / insurance / tax / fine / parts / other` | |
| `amount` | `decimal(10,2)` | PLN |
| `incurred_at` | `date` | data poniesienia |
| `description` | `varchar(255) NULL` | |

**Indeksy**: `(vehicle_id, incurred_at)`, `category`, `event_id`.

## `documents`

Załączniki: faktury, zdjęcia z kolizji, dokumenty rejestracyjne. Plik trzymany w `storage/app/public/`.

| Kolumna | Typ | Uwagi |
|---------|-----|-------|
| `id` | PK | |
| `vehicle_id` | FK → `vehicles.id` **CASCADE** | |
| `event_id` | FK → `events.id` **SET NULL** | opcjonalne |
| `filename` | `varchar` | oryginalna nazwa |
| `path` | `varchar` | ścieżka w storage |
| `mime_type` | `varchar` | |
| `size_bytes` | `bigint unsigned` | |
| `uploaded_at` | `timestamp default CURRENT_TIMESTAMP` | |

## `alerts`

Cache na potrzeby przyszłego cronu / wysyłki maili o wygasających terminach. Aktualnie kontroler managera generuje listy na bieżąco z `events.expiry_date`, ale tabela jest gotowa pod alerty zaplanowane.

| Kolumna | Typ | Uwagi |
|---------|-----|-------|
| `id` | PK | |
| `vehicle_id` | FK → `vehicles.id` **CASCADE** | |
| `event_id` | FK → `events.id` **CASCADE NULL** | |
| `type` | ENUM `expiry_warning / overdue / reminder / incident` | |
| `trigger_date` | `date` | kiedy alert ma się odpalić |
| `dismissed` | `boolean default false` | |
| `sent_at` | `timestamp NULL` | |

**Indeksy**: `(trigger_date, dismissed)`, `vehicle_id`, `event_id`.

---

## Normalizacja

Schemat jest w **3NF**:
- klucze proste, każda tabela ma własne `id`,
- atrybuty zależne wyłącznie od klucza głównego (np. `make`/`model` siedzą tylko w `vehicles`),
- historia przypisań wyseparowana z `vehicles` do `vehicle_assignments` żeby uniknąć anomalii aktualizacji,
- koszty i dokumenty są osobnymi encjami — pozwala to mieć N kosztów na jeden incydent oraz dokumenty bez powiązania ze zdarzeniem.

## Optymalizacja zapytań

Wybór indeksów wynika z faktycznych zapytań w kontrolerach:
- Lista kosztów po dacie + pojazd → indeks `(vehicle_id, incurred_at)`.
- Dashboard managera: wygasające w 30 dni → indeks na `expiry_date` + `status`.
- Filtrowanie zgłoszeń per typ/status → indeksy `type`, `status`.
- Historia przypisań kierowcy → `(user_id, assigned_from)`.

Jeśli baza spuchnie (>500k wierszy w `costs`), kolejnym krokiem optymalizacji byłoby partycjonowanie `costs` po roku z `incurred_at` lub utworzenie materializowanego widoku z miesięcznymi sumami.
