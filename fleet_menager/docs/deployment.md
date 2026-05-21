# Instrukcja uruchomienia projektu

Dokument opisuje uruchomienie **na świeżym komputerze** od momentu klonowania repo. Zakłada, że host ma zainstalowanego Dockera (Docker Desktop dla Windows/macOS lub Docker Engine + Compose plugin dla Linuxa) oraz Git.

## 1. Wymagania na hoście

| Komponent | Wersja |
|-----------|--------|
| Docker Engine | ≥ 24.0 |
| Docker Compose plugin | ≥ 2.20 |
| Git | dowolna |
| **Wolne porty** | 8080 (nginx), 3307 (MySQL), 8081 (opcjonalnie phpMyAdmin) |

Sprawdzenie:
```bash
docker --version
docker compose version
git --version
```

## 2. Pobranie kodu

```bash
git clone <URL_REPO> fleet-manager
cd fleet-manager
```

Jeśli pracujesz w istniejącym katalogu — upewnij się, że jesteś na właściwej gałęzi:
```bash
git status
git pull origin main
```

## 3. Plik środowiskowy

```bash
cp .env.example .env
```

Plik `.env` jest w `.gitignore` (nie wersjonowany). Jeśli chcesz zmienić port aplikacji, hasło MySQL lub timezone — edytuj `.env` przed startem kontenerów.

> Entrypoint kontenera `app` i tak skopiuje `.env.example → .env` automatycznie, jeśli plik nie istnieje.

## 4. Pierwsze uruchomienie

```bash
docker compose up -d --build
```

Co się dzieje:
1. Docker buduje obraz `fleet_app` (PHP-FPM 8.3 + composer + node 20).
2. Startują kontenery `nginx`, `app`, `mysql`.
3. `mysql` przechodzi healthcheck.
4. Entrypoint `app` instaluje composerowe paczki, generuje `APP_KEY`, robi `storage:link` i `migrate --seed`.

Pierwszy build trwa **3–5 minut**. Kolejne `up -d` to sekundy (cache wolumenów).

## 5. Build frontendu (jednorazowo)

```bash
docker compose exec app npm install
docker compose exec app npm run build
```

Po tym kroku `public/build/` zawiera skompilowany CSS Tailwind i JS Vite.

> W trakcie developmentu możesz zamiast `npm run build` użyć `npm run dev` (HMR). Wymaga dodania mapowania `5173:5173` w `docker-compose.yml`.

## 6. Sprawdzenie czy działa

Otwórz w przeglądarce: **http://localhost:8080**

Strona powinna przekierować na `/login`. Zaloguj się jednym z testowych kont:

| Email | Rola | Hasło |
|-------|------|-------|
| `admin@fleet.test` | Admin | `password` |
| `manager@fleet.test` | Manager | `password` |
| `kierowca1@fleet.test` | Kierowca | `password` |

## 7. Zatrzymanie / restart

```bash
docker compose down          # zatrzymanie, dane MySQL zostają
docker compose down -v       # zatrzymanie + skasowanie volumeów (twardy reset)
docker compose restart app   # restart pojedynczej usługi
```

## 8. Reset bazy

```bash
docker compose exec app php artisan migrate:fresh --seed --force
```

## 9. Logi i diagnostyka

```bash
docker compose logs -f app          # logi PHP-FPM / Laravel
docker compose logs -f nginx        # dostępu / 404
docker compose logs -f mysql        # baza
docker compose exec app tail -f storage/logs/laravel.log
```

## 10. Dostęp do bazy z hosta

- **Klient typu DBeaver / TablePlus / DataGrip**: host `localhost`, port `3307`, user `fleet`, hasło `secret`, baza `fleet_management`.
- **phpMyAdmin**: `docker compose --profile tools up -d phpmyadmin` → http://localhost:8081 (`root` / `rootsecret`).

## 11. Alternatywne uruchomienie pod XAMPP (bez Dockera)

Wymagania: PHP 8.2+, Composer, Node 20+, MySQL/MariaDB z XAMPP-a.

```bash
composer install
cp .env.example .env
# w .env: DB_HOST=127.0.0.1, DB_USERNAME=root, DB_PASSWORD=
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve        # http://127.0.0.1:8000
```

## 12. Typowe problemy

| Objaw | Rozwiązanie |
|-------|-------------|
| `port is already allocated 8080` | zajęty port — zmień `8080:80` w `docker-compose.yml`. |
| `SQLSTATE[HY000] [2002]` przy starcie | MySQL jeszcze startuje; entrypoint czeka 60s, ale możesz `docker compose restart app`. |
| `Permission denied` na `storage/` | `docker compose exec app chown -R www-data:www-data storage bootstrap/cache`. |
| `CSRF token mismatch` po długiej bezczynności | wyczyść cookies przeglądarki. |
| Brak stylów (gołe HTML) | brak builda Vite — odpal `docker compose exec app npm run build`. |
