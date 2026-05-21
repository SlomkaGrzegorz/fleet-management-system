# Docker — jak działa środowisko

Środowisko developerskie jest oparte o **Docker Compose** (`docker-compose.yml`) i składa się z czterech usług. Wszystko spina sieć `fleet` (bridge), żaden kontener nie wymaga ręcznej konfiguracji portów oprócz nginx (`8080`), MySQL (`3307` na hoście) i opcjonalnie phpMyAdmin (`8081`).

## Schemat

```
                          ┌────────────────┐
        host:8080 ──────► │  nginx 1.27    │ ──── fastcgi ────►  ┌────────────────┐
                          │  /var/www/html │                     │  app (PHP-FPM) │
                          └────────────────┘                     │  PHP 8.3 + node│
                                                                 │  composer,     │
                                                                 │  artisan       │
                                                                 └────────┬───────┘
                                                                          │
                                                                          ▼
                                                                   ┌────────────────┐
                                                                   │  MySQL 8.0     │
                                                                   │  vol mysql_data│
                                                                   └────────────────┘
                          ┌────────────────┐
        host:8081 ──────► │  phpMyAdmin    │ ◄── --profile tools
                          └────────────────┘
```

## Pliki

| Plik | Rola |
|------|------|
| `docker-compose.yml` | definicja usług, sieci, wolumenów |
| `docker/php/Dockerfile` | obraz PHP-FPM 8.3 + composer + node 20 + rozszerzenia (`pdo_mysql`, `gd`, `intl`, `bcmath`, `zip`, `mbstring`, `opcache`) |
| `docker/php/php.ini` | konfiguracja PHP (timezone, memory_limit, OPcache, upload limits) |
| `docker/php/entrypoint.sh` | inicjalizacja kontenera (composer, key:generate, migrate --seed, storage:link, czekanie na MySQL) |
| `docker/nginx/default.conf` | konfiguracja nginx — przekierowuje do `app:9000` (FastCGI) |
| `.dockerignore` | wyklucza `.git`, `node_modules`, `vendor`, `.env` z kontekstu buildu |

## Usługi

### `app` (PHP-FPM)
- Obraz budowany lokalnie z `docker/php/Dockerfile`.
- Mounty: cały projekt do `/var/www/html`, plus dwa volumes-cache na `vendor/` i `node_modules/` (żeby Windows host nie zabijał wydajności bind-mountów).
- `entrypoint.sh` przy pierwszym starcie:
  1. Kopiuje `.env.example → .env` jeśli brak.
  2. `composer install` jeśli brak `vendor/`.
  3. `php artisan key:generate` jeśli `APP_KEY` puste.
  4. `php artisan storage:link`.
  5. Czeka aż MySQL odpowie na `fsockopen`.
  6. `php artisan migrate --seed --force`.
  7. Uruchamia `php-fpm`.

### `nginx`
- Obraz `nginx:1.27-alpine`.
- Konfiguracja zamontowana read-only z `docker/nginx/default.conf`.
- Słucha `80` w kontenerze, mapowane na `8080` na hoście.
- `try_files` → `index.php?$query_string` (standardowy routing Laravela).

### `mysql`
- Obraz `mysql:8.0`.
- Healthcheck przez `mysqladmin ping` — `depends_on: condition: service_healthy` w usłudze `app` gwarantuje, że migracje nie ruszą zanim baza jest gotowa.
- Wolumen `mysql_data` persystuje dane między restartami.
- Domyślne credsy: `fleet / secret` (oraz root `rootsecret` dla phpMyAdmin).
- Z hosta dostępny na **`localhost:3307`** (nie 3306, żeby nie zderzyć się z XAMPP-owym MySQL).

### `phpmyadmin` (opcjonalnie)
- Profil `tools` — domyślnie nie startuje. Włącz tylko gdy potrzeba:
  ```bash
  docker compose --profile tools up -d phpmyadmin
  ```
- `http://localhost:8081`, użytkownik `root` / `rootsecret`.

## Wolumeny

| Wolumen | Cel |
|---------|-----|
| `mysql_data` | Dane MySQL (przeżywa `docker compose down`). |
| `vendor_cache` | `vendor/` jako wolumen — przyspiesza I/O na Windows. |
| `node_modules_cache` | jak wyżej dla `node_modules/`. |

`docker compose down -v` skasuje wszystko (przydatne do twardego resetu).

## Najczęstsze problemy

- **Port 8080 zajęty** — zmień mapowanie w `docker-compose.yml` na np. `8088:80`.
- **MySQL na hoście blokuje 3307** — `netstat -an | findstr 3307`; zmień port hosta na 3308.
- **Powolne `composer install` na Windows** — pierwszy build trwa ~3-5 min (instalacja rozszerzeń PHP + composer + node). Kolejne starty są szybsze dzięki cache wolumenom.
- **Brak frontu (CSS)** — pierwszego razu trzeba odpalić `docker compose exec app npm install && docker compose exec app npm run build`. Hot-reload via `npm run dev` wymaga wystawienia portu 5173 (dodaj do nginx / mapowania portu w `app`).

## Production hint

Plik `docker-compose.yml` jest **devowy**. W produkcji:
- Dorzucić Dockerfile multi-stage z `composer install --no-dev --optimize-autoloader` i `npm run build` w build-time.
- Wyłączyć bind-mounty kodu, kopiować kod do obrazu.
- `APP_ENV=production`, `APP_DEBUG=false`.
- Reverse-proxy (Traefik/nginx) z TLS przed kontenerem.
- Backupy `mysql_data` (mysqldump w cronie lub Percona xtrabackup).
