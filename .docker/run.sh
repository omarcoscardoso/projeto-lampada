#!/bin/sh

# Garantir permissões de escrita em runtime (necessário no Cloud Run onde o
# container pode rodar como usuário diferente do build)
echo "Fixing storage permissions..."
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

echo "Running migrations..."
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction
php artisan shield:generate --all --no-interaction
php artisan permission:cache-reset
php artisan cache:clear

echo "Starting supervisor..."
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf