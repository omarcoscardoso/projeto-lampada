#!/bin/sh

echo "Fixing storage permissions..."
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

echo "Preparing PHP-FPM socket directory..."
mkdir -p /var/run
chown -R www-data:www-data /var/run
chmod 777 /var/run

echo "Running migrations..."
php artisan migrate --force --no-interaction
php artisan permission:cache-reset
php artisan cache:clear

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf