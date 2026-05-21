# Główne funkcje systemu

Aplikacja realizuje trzy role: **Kierowca**, **Fleet Manager**, **Administrator**.
Każda ma własny prefiks URL (`/driver`, `/manager`, `/admin`) chroniony middleware `role:*`. Admin przechodzi przez wszystkie middleware (`EnsureRole::handle()` ma wbudowany by-pass) i wszystkie Policy (`before()` zwraca `true`), więc może wykonać dowolną akcję obu pozostałych ról.

---

## Kierowca (`role: driver`)

URL bazowy: `/driver/*`. Kontrolery w `app/Http/Controllers/Driver/`.

| Funkcja | Endpoint | Kontroler / metoda |
|---------|----------|---------------------|
| Pulpit (przypisane pojazdy, koszty miesiąca) | `GET /driver/dashboard` | `DashboardController@index` |
| Lista własnych zgłoszeń | `GET /driver/incidents` | `IncidentController@index` |
| Formularz nowego zgłoszenia | `GET /driver/incidents/create` | `IncidentController@create` |
| Zapis zgłoszenia | `POST /driver/incidents` | `IncidentController@store` (walidacja: `StoreIncidentRequest`) |
| Szczegóły zgłoszenia | `GET /driver/incidents/{event}` | `IncidentController@show` |
| Lista własnych kosztów | `GET /driver/costs` | `CostController@index` |
| Formularz nowego kosztu | `GET /driver/costs/create` | `CostController@create` |
| Zapis kosztu (+ upload faktury) | `POST /driver/costs` | `CostController@store` (walidacja: `StoreCostRequest`) |

**Walidacja zgłoszenia** (`StoreIncidentRequest`): pojazd musi istnieć i typowo być przypisany do kierowcy, data ≤ dziś, opis 5–5000 znaków.

**Walidacja kosztu** (`StoreCostRequest`): kwota > 0, faktura: PDF/JPG/PNG ≤ 8 MB, data ≤ dziś. Plik trafia do `storage/app/public/invoices/{vehicle_id}` w obrębie transakcji DB.

---

## Fleet Manager (`role: manager`)

URL bazowy: `/manager/*`. Kontrolery w `app/Http/Controllers/Manager/`.

| Funkcja | Endpoint |
|---------|----------|
| Pulpit floty (statystyki, wygasające/przeterminowane) | `GET /manager/dashboard` |
| Lista pojazdów z filtrowaniem | `GET /manager/vehicles` |
| Szczegóły pojazdu (historia, koszty, zgłoszenia) | `GET /manager/vehicles/{vehicle}` |
| Przypisanie pojazdu kierowcy | `GET/POST /manager/vehicles/{vehicle}/assign` |
| Zakończenie przypisania | `DELETE /manager/vehicles/{vehicle}/assign` |
| Lista zgłoszeń kierowców (filtry: typ, status) | `GET /manager/events` |
| Szczegóły zgłoszenia | `GET /manager/events/{event}` |
| Zmiana statusu zgłoszenia | `PATCH /manager/events/{event}/status` |
| Lista kosztów (filtry: data, kategoria, pojazd) | `GET /manager/costs` |
| Eksport CSV kosztów dla księgowości | `GET /manager/costs/export` |
| Lista alertów (terminy) | `GET /manager/alerts` |
| Odznaczenie alertu | `PATCH /manager/alerts/{alert}/dismiss` |

### Przypisywanie pojazdu

`AssignmentController@store` (transakcja):
1. Zamyka poprzednie aktywne przypisanie (`assigned_until = today`).
2. Tworzy nowy wpis w `vehicle_assignments`.
3. Aktualizuje `vehicles.assigned_user_id`.

### Eksport CSV

Realizuje `App\Services\CostCsvExporter`:
- Streamowany `response()->stream` (nie ładujemy wszystkiego do pamięci).
- BOM `\xEF\xBB\xBF` żeby Excel poprawnie otworzył UTF-8.
- Separator `;` (ułatwia otwarcie w polskim Excelu).
- `chunk(500)` przez Eloquent.
- Filtry: `from`, `to`, `category`, `vehicle_id`.

### Monitoring terminów

`Event::scopeExpiringSoon($days = 30)` + `Event::scopeOverdue()` — odpalane na dashboardzie i w `AlertController@index`.

---

## Administrator (`role: admin`)

Admin ma **wszystkie** uprawnienia kierowcy i managera (middleware + `Policy::before()`), plus dodatkowe akcje w `/admin/*`.

| Funkcja | Endpoint |
|---------|----------|
| Dodanie pojazdu | `GET/POST /admin/vehicles` |
| Usunięcie pojazdu | `DELETE /admin/vehicles/{vehicle}` |
| Usunięcie zgłoszenia (np. duplikat) | `DELETE /admin/events/{event}` |
| Zarządzanie użytkownikami | `GET /admin/users`, `POST /admin/users`, `DELETE /admin/users/{user}` |
| Raporty zbiorcze | `GET /admin/reports` |
| Pobranie pełnego CSV kosztów | `GET /admin/reports/costs.csv` |

### Raporty

`Admin\ReportController@index` agreguje koszty SQL-em (`GROUP BY` + `SUM`) za wskazany okres (`from`/`to`):
- suma per kategoria,
- top 20 pojazdów wg kosztów,
- liczba incydentów,
- ogólny przegląd floty.

---

## Bezpieczeństwo i autoryzacja

**Trzy warstwy obrony**:

1. **Middleware `role`** (`EnsureRole`) — blokuje wejście do prefiksu URL niewłaściwej roli.
2. **Policies** (`VehiclePolicy`, `EventPolicy`, `CostPolicy`, `AlertPolicy`, `UserPolicy`) — kontrolują pojedyncze akcje. Każda ma metodę `before()`, która zwraca `true` dla admina.
3. **Form Requests** — walidują dane i sprawdzają `authorize()` zanim cokolwiek dotknie modelu.

Przykład wewnątrz `EventPolicy::view`: kierowca widzi zgłoszenie tylko jeśli jest jego autorem **albo** dotyczy jego pojazdu — manager widzi wszystkie.

---

## CRUD wg encji

| Encja | Create | Read | Update | Delete |
|-------|--------|------|--------|--------|
| `Vehicle` | Admin | Manager + Driver (swoje) | Manager (status) | Admin |
| `User` | Admin | Manager (drivers) | — | Admin |
| `Event` | Driver/Manager | Manager + autor | Manager + autor (gdy open) | Admin |
| `Cost` | Driver/Manager | Manager + autor + kierowca pojazdu | Driver (swoje) / Manager | Admin |
| `VehicleAssignment` | Manager | Manager | Manager | Manager |
| `Document` | przy uploadzie faktury | wszystkie role | — | (kaskada z eventem/vehicle) |
