# Wersjonowanie i workflow w Gicie

## Zasady ogólne

1. **Cały kod, migracje, seedery i dokumentacja są w jednym repozytorium Git.**
2. Pliki środowiskowe (`.env`) i tymczasowe (`storage/logs/`, `node_modules/`, `vendor/`, `.idea/`) są w `.gitignore`.
3. **Gałąź `main` jest zawsze "live"** — odpalalna komendą `docker compose up -d --build` na świeżym kompie.
4. Każda funkcjonalność powstaje na gałęzi `feature/<nazwa>` i wraca przez Pull Request / merge.

## Konwencja commitów

Formuła `type: krótki opis` (Conventional Commits w wersji minimalnej):

| `type` | kiedy używać |
|--------|--------------|
| `feat` | nowa funkcja użytkowa |
| `fix` | poprawka błędu |
| `docs` | tylko dokumentacja |
| `db` | migracje / seedery / zmiany schematu |
| `refactor` | zmiana strukturalna kodu bez zmiany zachowania |
| `chore` | zależności, Docker, konfig, CI |
| `test` | testy |

Przykłady:
```
feat: dodanie eksportu CSV kosztów dla managera
db: indeks złożony (vehicle_id, incurred_at) w tabeli costs
fix: poprawka autoryzacji w EventPolicy::view dla kierowcy
docs: rozszerzenie sekcji o przypisaniach w features.md
```

## Iteracyjne zmiany w bazie

**Zasada żelazna**: nie modyfikujemy już zacommitowanych migracji w gałęzi `main`. Każda zmiana schematu = nowa migracja.

### Dodanie kolumny

```bash
docker compose exec app php artisan make:migration add_mileage_to_vehicles_table --table=vehicles
```

W wygenerowanym pliku:
```php
public function up(): void {
    Schema::table('vehicles', function (Blueprint $table) {
        $table->unsignedInteger('mileage_km')->nullable()->after('year');
    });
}

public function down(): void {
    Schema::table('vehicles', function (Blueprint $table) {
        $table->dropColumn('mileage_km');
    });
}
```

Po dopisaniu:
```bash
docker compose exec app php artisan migrate
```

### Cofnięcie

```bash
docker compose exec app php artisan migrate:rollback        # ostatnia batcha
docker compose exec app php artisan migrate:rollback --step=2
docker compose exec app php artisan migrate:reset           # wszystkie down()
```

### Pełen reset (dev)

```bash
docker compose exec app php artisan migrate:fresh --seed
```

## Wersjonowanie aplikacji

Tag semantyczny (`MAJOR.MINOR.PATCH`):
- `MAJOR` — niekompatybilna zmiana schematu lub API (np. usunięcie tabeli).
- `MINOR` — nowa funkcja / nowa migracja kompatybilna wstecz.
- `PATCH` — poprawki, refactor, dokumentacja.

```bash
git tag -a v0.2.0 -m "Eksport CSV + alerty terminowe"
git push --tags
```

## Workflow rozwoju nowej funkcji

```bash
# 1. Świeży start z main
git checkout main && git pull

# 2. Nowa gałąź
git checkout -b feature/incident-attachments

# 3. Pracujesz, commitujesz małymi krokami
git add app/Models/Document.php
git commit -m "feat: walidacja typów MIME dla załączników do incydentów"

# 4. Migracja (jeśli potrzebna)
docker compose exec app php artisan make:migration add_thumbnail_path_to_documents_table
git add database/migrations/*
git commit -m "db: kolumna thumbnail_path w documents"

# 5. Testy
docker compose exec app php artisan test

# 6. Merge przez PR (lub fast-forward)
git checkout main && git merge --no-ff feature/incident-attachments
git push
```

## Co należy do `.gitignore`

(plik już skonfigurowany przez Laravel; nie modyfikujemy)

- `/vendor/`, `/node_modules/`
- `/storage/*.key`, `/storage/logs/*`, `/storage/framework/{cache,sessions,views}/`
- `.env`, `.env.local`, `.env.production`
- `.idea/`, `.vscode/`
- `npm-debug.log`, `yarn-error.log`
- `public/storage` (link symboliczny, generowany przez `storage:link`)
- `public/build/` (artefakt vite — w produkcji budowany w CI/CD)

## Co MUSI być wersjonowane

- Wszystkie pliki w `app/`, `database/`, `routes/`, `resources/`, `config/`, `tests/`, `docs/`.
- `composer.json`, `composer.lock`, `package.json`, `package-lock.json`.
- `docker-compose.yml`, `docker/`, `.dockerignore`.
- `.env.example` (bez sekretów).
- `README.md`, cały katalog `docs/`.

## CI/CD (sugestia)

Minimalny pipeline GitHub Actions (do dodania w `.github/workflows/ci.yml`):

```yaml
name: CI
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env: { MYSQL_DATABASE: testing, MYSQL_ROOT_PASSWORD: root }
        ports: ['3306:3306']
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.3', extensions: pdo_mysql, mbstring, zip, gd }
      - run: composer install --no-interaction --prefer-dist
      - run: cp .env.example .env && php artisan key:generate
      - run: php artisan migrate --force
      - run: php artisan test
```
