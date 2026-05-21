#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Entrypoint kontenera PHP-FPM Fleet Manager.
#
# Czeka na bazę MySQL, instaluje composerowe zależności (jeśli brak vendor/),
# uzupełnia .env, generuje APP_KEY, robi migrate --seed i uruchamia php-fpm.
# ---------------------------------------------------------------------------
set -e

APP_DIR="/var/www/html"
cd "$APP_DIR"

# .env - jeśli nie istnieje, kopiujemy z .env.example
if [ ! -f .env ]; then
    echo "[entrypoint] Brak .env - kopiuję .env.example"
    cp .env.example .env
fi

# composer install (gdy ktoś podpiął świeży kod bez vendor/)
if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] composer install"
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# APP_KEY
if ! grep -qE '^APP_KEY=base64:.+' .env; then
    echo "[entrypoint] artisan key:generate"
    php artisan key:generate --force
fi

# Storage link (faktury w storage/app/public)
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

# Czekamy na bazę
HOST="${DB_HOST:-mysql}"
PORT="${DB_PORT:-3306}"
echo "[entrypoint] Czekam na MySQL ${HOST}:${PORT}..."
for i in {1..60}; do
    if php -r "exit(@fsockopen('${HOST}', ${PORT}) ? 0 : 1);" >/dev/null 2>&1; then
        echo "[entrypoint] MySQL dostępny."
        break
    fi
    sleep 1
done

# Migracje i seedery
echo "[entrypoint] php artisan migrate --seed"
php artisan migrate --seed --force

# Cache konfiguracji w trybie produkcyjnym
if [ "${APP_ENV:-local}" != "local" ]; then
    php artisan config:cache
    php artisan route:cache
fi

# Uprawnienia do storage/bootstrap-cache (na wszelki wypadek)
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

echo "[entrypoint] Start: $@"
exec "$@"
