#!/bin/sh
set -e

cd /var/www/html

# Re-assert writable permissions in case a volume was mounted over storage/bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

php artisan package:discover --ansi || true
php artisan config:clear

if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] WARNING: APP_KEY is not set. Generating a temporary one - set APP_KEY as a persistent environment variable in Dokploy to avoid session/cookie invalidation on every deploy."
    php artisan key:generate --force
fi

php artisan storage:link || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "[entrypoint] Running database migrations..."
    php artisan migrate --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
