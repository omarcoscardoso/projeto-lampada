# =============================================================================
# Stage 1: Build do Frontend (Node.js)
# =============================================================================
FROM node:22-alpine AS frontend-builder
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY resources resources/
COPY app app/
COPY vite.config.js tailwind.config.js ./
RUN npm run build

# =============================================================================
# Stage 2: Imagem Final de Produção (PHP-FPM 8.4 + Nginx)
# =============================================================================
FROM php:8.4-fpm-alpine

# Instala dependências de runtime essenciais (sem compiladores pesados)
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    curl \
    git

# Instala extensões PHP de forma pré-otimizada
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql opcache zip gd bcmath intl

WORKDIR /var/www/html

# Composer CLI
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Instala dependências do Composer primeiro para aproveitar o cache de camadas
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copia o código da aplicação
COPY . .

# Copia os assets compilados do stage do Node.js
COPY --from=frontend-builder /app/public/build ./public/build

# Prepara .env para descoberta de pacotes e finaliza o autoload
RUN cp .env.example .env 2>/dev/null || true \
    && composer dump-autoload --optimize --no-dev

# Configurações de serviços
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY .docker/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY .docker/run.sh /usr/local/bin/run.sh
RUN chmod +x /usr/local/bin/run.sh

# Configuração de permissões e diretórios temporários
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && mkdir -p /var/www/html/storage/logs \
    && touch /var/www/html/storage/logs/laravel.log \
    && chown www-data:www-data /var/www/html/storage/logs/laravel.log \
    && chmod 664 /var/www/html/storage/logs/laravel.log \
    && mkdir -p /tmp/livewire \
    && chown -R www-data:www-data /tmp \
    && mkdir -p /var/lib/nginx/tmp/client_body \
    && chown -R www-data:www-data /var/lib/nginx \
    && mkdir -p /var/run \
    && chown -R www-data:www-data /var/run

EXPOSE 8080

CMD ["/usr/local/bin/run.sh"]
