# Use a imagem base do PHP
FROM php:8.4-fpm-alpine

# Instale as dependências de sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    git \
    build-base \
    zip \
    libzip-dev \
    postgresql-dev \
    mysql-client \
    curl \
    icu-dev \
    nodejs \
    npm \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    libjpeg-turbo-dev

RUN npm install -g npm@latest

# Instale as extensões do PHP
RUN docker-php-ext-install \
    pdo_mysql \
    opcache \
    zip \
    gd \
    bcmath \
    intl

# Configure e instale a extensão gd com suporte a freetype
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Configura o working directory
WORKDIR /var/www/html

# Copia os arquivos do composer para o cache
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Remove o arquivo de configuração padrão para evitar conflitos
RUN rm -rf /usr/local/var/log
RUN mkdir -p /usr/local/var/log/
RUN chown -R www-data:www-data /usr/local/var/log

# Copia o código da aplicação
COPY . .
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY .docker/run.sh /usr/local/bin/run.sh

RUN cp .env.example .env
RUN composer install --no-dev --optimize-autoloader
RUN php artisan key:generate --no-interaction

RUN npm install
RUN npm run build

RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html/storage 
RUN chmod -R 775 /var/www/html/bootstrap/cache
RUN chmod +x /usr/local/bin/run.sh
RUN mkdir -p /tmp/livewire 
RUN chown -R www-data:www-data /tmp
RUN mkdir -p /var/lib/nginx/tmp/client_body
RUN chown -R www-data:www-data /var/lib/nginx

EXPOSE 8080

CMD ["/usr/local/bin/run.sh"]

### PARA TESTE LOCAL DA IMAGEM COM AS VARIÁVEIS DE AMBIENTE
# docker build -t lampada-iprviamao-com-br:latest .
docker run -p 8080:8080 --rm \
    --add-host=host.docker.internal:$(docker network inspect bridge --format='{{(index .IPAM.Config 0).Gateway}}') \
    --env APP_NAME="lampada-iprviamao-com-br" \
    --env APP_ENV="development" \
    --env APP_KEY="base64:CfqGMuEtBoOibR6hJvcCZ9IG+Fq2F/0wZlqMhzGeqFc=" \
    --env APP_DEBUG="true" \
    --env APP_URL="http://localhost:8080" \
    --env DB_CONNECTION="mysql" \
    --env DB_HOST="host.docker.internal" \
    --env DB_PORT="3306" \
    --env DB_DATABASE="lampada" \
    --env DB_USERNAME="sail" \
    --env DB_PASSWORD="password" \
    lampada-iprviamao-com-br:latest