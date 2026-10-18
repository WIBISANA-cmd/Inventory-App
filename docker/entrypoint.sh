#!/bin/sh
set -e

cd /var/www/html

# Re-assert writable permissions in case a volume was mounted over storage/bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

php artisan package:discover --ansi || true
php artisan config:clear

# Tidak ada file .env di dalam image (di-exclude .dockerignore), jadi `key:generate` akan
# gagal dan membuat container crash-loop (Traefik lalu membalas "404 page not found").
# Generate key sementara langsung ke environment variable.
if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] WARNING: APP_KEY is not set. Generating a temporary one - set APP_KEY as a persistent environment variable in Dokploy to avoid session/cookie invalidation on every deploy."
    APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    export APP_KEY
fi

php artisan storage:link || true

# Migration yang gagal (DB belum siap / kredensial salah) tidak boleh mematikan container,
# supaya web server tetap jalan dan error-nya terlihat di log Dokploy.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "[entrypoint] Running database migrations..."
    attempt=1
    until php artisan migrate --force; do
        if [ "$attempt" -ge "${MIGRATION_RETRIES:-5}" ]; then
            echo "[entrypoint] ERROR: migrations failed after $attempt attempts - check DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD. Starting web server anyway."
            break
        fi
        echo "[entrypoint] Migration attempt $attempt failed, retrying in 5s..."
        attempt=$((attempt + 1))
        sleep 5
    done
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
