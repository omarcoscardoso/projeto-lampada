#!/bin/sh
set -e

echo "Fixing storage permissions..."
mkdir -p /var/www/html/storage/logs \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/bootstrap/cache

chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Preparing PHP-FPM socket directory..."
mkdir -p /var/run
chown -R www-data:www-data /var/run
chmod 777 /var/run

echo "Warming up Laravel and Filament caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache || true
php artisan filament:cache-components || true
php artisan permission:cache-reset || true

echo "Running migrations..."
php artisan migrate --force --no-interaction

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf