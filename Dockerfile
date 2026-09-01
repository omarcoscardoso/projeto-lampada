# =============================================================================
# Stage 1: Dependências do Composer (Vendor)
# =============================================================================
FROM composer:2 AS vendor-builder
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# =============================================================================
# Stage 2: Build do Frontend (Node.js)
# =============================================================================
FROM node:22-alpine AS frontend-builder
WORKDIR /app

COPY package*.json ./
RUN npm ci

# Copia vendor (necessário para Livewire/Alpine ESM e classes Blade)
COPY --from=vendor-builder /app/vendor vendor/
COPY resources resources/
COPY app app/
COPY public public/
COPY vite.config.js tailwind.config.js ./
RUN npm run build

# =============================================================================
# Stage 3: Imagem Final de Produção (PHP-FPM 8.4 + Nginx)
# =============================================================================
FROM php:8.4-fpm-alpine

# Instala dependências de runtime e compila extensões PHP com dependências virtuais limpas
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    curl \
    git \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype \
    icu-libs \
    && apk add --no-cache --virtual .build-deps \
    build-base \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    opcache \
    zip \
    gd \
    bcmath \
    intl \
    && apk del .build-deps

WORKDIR /var/www/html

# Composer CLI
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copia dependências pré-instaladas do Composer
COPY --chown=www-data:www-data --from=vendor-builder /app/vendor ./vendor
COPY --chown=www-data:www-data composer.json composer.lock ./

# Copia o código da aplicação
COPY --chown=www-data:www-data . .

# Copia os assets compilados do stage do Node.js
COPY --chown=www-data:www-data --from=frontend-builder /app/public/build ./public/build

# Prepara .env para descoberta de pacotes e finaliza o autoload otimizado
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
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
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
