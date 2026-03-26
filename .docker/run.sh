#!/bin/sh

# Aguardar o banco de dados estar disponível (opcional, mas recomendado)
# (substitua 'db' pelo nome do serviço do seu banco de dados no docker-compose)
# A função abaixo pode ser adicionada ao script, ou você pode usar uma imagem como "wait-for-it.sh"
# wait-for-it db:3306 -t 30 

echo "Running migrations..."
php artisan migrate --force --no-interaction

echo "Starting supervisor..."
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf