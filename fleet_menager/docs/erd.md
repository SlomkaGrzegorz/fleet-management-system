# Diagram ERD

Diagram klasyczny "Crow's Foot" zrealizowany w mermaid (GitHub i większość edytorów Markdown renderuje go natywnie).

```mermaid
erDiagram
    USERS ||--o{ VEHICLES                : "assigned_user_id"
    USERS ||--o{ VEHICLE_ASSIGNMENTS     : "user_id"
    USERS ||--o{ EVENTS                  : "reported_by"
    USERS ||--o{ COSTS                   : "entered_by"

    VEHICLES ||--o{ VEHICLE_ASSIGNMENTS  : "vehicle_id"
    VEHICLES ||--o{ EVENTS               : "vehicle_id"
    VEHICLES ||--o{ COSTS                : "vehicle_id"
    VEHICLES ||--o{ DOCUMENTS            : "vehicle_id"
    VEHICLES ||--o{ ALERTS               : "vehicle_id"

    EVENTS   ||--o{ COSTS                : "event_id (nullable)"
    EVENTS   ||--o{ DOCUMENTS            : "event_id (nullable)"
    EVENTS   ||--o{ ALERTS               : "event_id (nullable)"

    USERS {
        bigint id PK
        string name
        string email "UNIQUE"
        string password
        enum   role "admin|manager|driver"
        timestamp email_verified_at
        timestamps timestamps
    }

    VEHICLES {
        bigint  id PK
        string  plate_number "UNIQUE"
        string  make
        string  model
        smallint year
        string  vin "UNIQUE, 17"
        enum    status "active|in_service|retired|sold"
        bigint  assigned_user_id FK "→ users.id (nullable, SET NULL)"
        timestamps timestamps
    }

    VEHICLE_ASSIGNMENTS {
        bigint id PK
        bigint vehicle_id FK "CASCADE"
        bigint user_id    FK "CASCADE"
        date   assigned_from
        date   assigned_until "nullable"
        timestamps timestamps
    }

    EVENTS {
        bigint id PK
        bigint vehicle_id  FK "CASCADE"
        bigint reported_by FK "RESTRICT (users)"
        enum   type "insurance|inspection|service|repair|incident|other"
        date   event_date
        date   expiry_date "nullable"
        text   notes "nullable"
        enum   status "open|in_progress|closed"
        timestamps timestamps
    }

    COSTS {
        bigint  id PK
        bigint  vehicle_id FK "CASCADE"
        bigint  event_id   FK "SET NULL (nullable)"
        bigint  entered_by FK "RESTRICT (users)"
        enum    category "fuel|service|repair|insurance|tax|fine|parts|other"
        decimal amount "10,2"
        date    incurred_at
        string  description "nullable"
        timestamps timestamps
    }

    DOCUMENTS {
        bigint   id PK
        bigint   vehicle_id FK "CASCADE"
        bigint   event_id   FK "SET NULL (nullable)"
        string   filename
        string   path
        string   mime_type
        bigint   size_bytes
        timestamp uploaded_at
        timestamps timestamps
    }

    ALERTS {
        bigint id PK
        bigint vehicle_id FK "CASCADE"
        bigint event_id   FK "CASCADE (nullable)"
        enum   type "expiry_warning|overdue|reminder|incident"
        date   trigger_date
        bool   dismissed "default false"
        timestamp sent_at "nullable"
        timestamps timestamps
    }
```

## Interpretacja relacji

- **Użytkownik** może mieć przypisanych wiele pojazdów (kierowca aktualnie jeżdżący) — relacja 1:N przez `vehicles.assigned_user_id`. Historia przypisań trafia do `vehicle_assignments` (relacja N:M w czasie).
- **Pojazd** ma wiele zdarzeń (`events`), wiele kosztów, dokumentów i alertów.
- **Event** (zgłoszenie / serwis / ubezpieczenie / incydent) jest **opcjonalną kotwicą** dla kosztów, dokumentów i alertów. Dzięki `nullable + nullOnDelete` można usunąć event bez utraty powiązanego kosztu (np. faktura wciąż widoczna w raporcie).
- **Document** może być powiązany jednocześnie z pojazdem i z konkretnym zdarzeniem (np. zdjęcie z kolizji + faktura naprawcza).

## Polityka usuwania

| Relacja | Reguła | Powód |
|---------|--------|-------|
| `vehicles.assigned_user_id → users` | **SET NULL** | Usunięcie kierowcy nie kasuje pojazdu, wraca on do puli wolnych. |
| `events.reported_by → users` | **RESTRICT** | Nie pozwalamy usunąć usera, który ma zgłoszenia — wymusza re-przypisanie. |
| `costs.entered_by → users` | **RESTRICT** | Jak wyżej — koszt musi mieć autora dla audytu. |
| `vehicle_assignments.*` | **CASCADE** | Po usunięciu pojazdu/usera historia traci sens. |
| `events.vehicle_id` | **CASCADE** | Usunięcie pojazdu zamyka jego historię. |
| `costs.vehicle_id` | **CASCADE** | Jak wyżej. |
| `costs.event_id` | **SET NULL** | Koszt może istnieć niezależnie od konkretnego zgłoszenia. |
| `documents.*` | **CASCADE / SET NULL** | Dokumenty pojazdu giną z pojazdem; powiązanie z eventem może zniknąć. |
| `alerts.*` | **CASCADE** | Alerty nie mają sensu bez pojazdu. |
