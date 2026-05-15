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

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && mkdir -p /var/www/html/storage/logs \
    && touch /var/www/html/storage/logs/laravel.log \
    && chown www-data:www-data /var/www/html/storage/logs/laravel.log \
    && chmod 664 /var/www/html/storage/logs/laravel.log

RUN chmod +x /usr/local/bin/run.sh
RUN mkdir -p /tmp/livewire \
    && chown -R www-data:www-data /tmp
RUN mkdir -p /var/lib/nginx/tmp/client_body \
    && chown -R www-data:www-data /var/lib/nginx

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
    --env ABIBLIA_DIGITAL_TOKEN="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdHIiOiJXZWQgQXByIDAxIDIwMjYgMTM6MjE6NDAgR01UKzAwMDAuY2FyZG9zby5vbGl2ZWlyYUBnbWFpbC5jb20iLCJpYXQiOjE3NzUwNDk3MDB9.ZEGSP9F3luK0Ug23qt707W5Y8F6X5SnrLTz2xi2XoSw" \
    --env GOOGLE_CLIENT_ID="1042749847534-8ovq26s5gjvebv2q2002k4fj3cqbeu5t.apps.googleusercontent.com" \
    --env GOOGLE_CLIENT_SECRET="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" \
    --env GOOGLE_REDIRECT_URL="${APP_URL}/auth/google/callback" \
    --env GOOGLE_CLOUD_PROJECT_ID="projeto-lampada-001" \
    --env GOOGLE_CLOUD_STORAGE_BUCKET="projetolampada" \
    --env GOOGLE_CLOUD_KEY_JSON='{"type": "service_account", "project_id": "projeto-lampada-001", "private_key_id": "80474a69eaf706b659f8edd5a5525a729ce237bd", "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC+C5c1J6jevW+e\nAkfGyxHAL2WRwcNwx9isw1PshweRHXpATD+v7TX93vJbXLD6FtvqcVllqegvBllw\n0ttZiSPkqzjV/+k6cf879A9Ruq/rRhsvtir/BOOAmvqsmzm7ZzrRssPqjYRd1QQp\nXvWC5gHIqS/eMW0aTISichVoyuwwgBXW+S8+p8yKsHseQigFRojNjFmJyJmEcuXQ\nHpnPcXHctqc/N8PuB2R22M1heYZDxxMV+NE2KSA9mOv6IJQfQ+9i2kxutDd08Bop\n5eWbiaN3Sx39BOV9Z1luBbQOgMCgXiaYzymgcDWDDtfZqjfMP3hiTrSZy+7byj9H\nr6VBVk7hAgMBAAECggEACCpfk3Y6JmLhkTqUGq2mPDmVHcGDbBy8GbU1TYoCOFkZ\nw/o6uMyCh79M7sHBbWduyMiYwJpfqmiVjnhS2iqfLw+vzBsYKm2UO79ZaqewsBVV\nrPPA7m7ndwLBr0mmnf+aGoE5s4Hj3HO+/2eP+TE4haaGUt/RCiKcRs4LzzI72nwN\nb6M9847gW02JDsBFg2wd4SH1eHKZVwpvwI0B101X7eTqHFL+sUFfijZXZdL5gnIQ\nCT+QOqcc3qdqTZQWo7w3x1I8c1fKVLhQRNwfMhsNExs7mfvaV1ixls2M7VVkycBj\nl1BtWuZWxsGgMBcFD4H61zonOaF7Q8pH9I347Ibo6QKBgQDehhrCj1CClRZhNukS\nwAfDO1jg+2f6xDkbt2iSJo+CdgeCiCJia+8XuZcwUdYJhVUT2T5f+FkTi1L9netJ\nZWYRaBvfV5o2RnM2JXi89LERV/qNvV4x/dTUuKtA0hfKvwjibucVkI3Y51OknnQ8\nu7PwkzS394XcE16/Oe9QwykhmQKBgQDaoqnBSVFo95m6PK97vBReEQohUniBDhcl\ny4CSS/yV9IAG+er1ANsfWsBZCBq8JtdSRPshDDvkq5xQV6ZPqam6J/jHbY+P7PXf\n1LT5oQQ3JkDCgP/HIPlbvbF6ghmPPMl0yKOjtYtdVpyxRit0iI7c0/ou1mqVoKzj\nz8zyOip0iQKBgQCKssnhNVT4X5b4dzJldn4gKVCnwuw3uLDj3rj2R8Sxi4H40YOl\nFyOLBSoAezHO82VpHsKrLO/Qp8nNvO3X7LTm6p5c4oyDgfvz5v1PwbDQX6cTS9J3\nlIBhKs3LdGhR/iq6gGfW4CgZ524SMJXA+ToaHJcCh+zOlOA8jw7kpxBvGQKBgQCn\nD1YBAGH5gAByIv6/4GX7vq9r6NV1X2vmkEos/20AtDPDoOGc1kuY+MIzBZNQI6my\nDk2J4gw93bdyWJcXFgA/410gHaJuClWR87lZaWSMM2mWdfV7lcGUDS14+8JGBd+1\nob4QUJ8t8gmHF6QKxnHLYRoxAdute3nAFT9382QPaQKBgAjlEf6HgNzQALuyHxOE\nC9XzgxT/w/waVPlbv1c5Yd/NfENzvlGHkSqoZOdc5nh5QsW8ZH8ZgBF9WXPjlSaJ\nGJOKVDOrZMAM6hQxa/WcaORhnHUZF6mnRBGCzxQDcn+l45CzW003vHqnHzID2ZPF\nUOoFrcm5NNoewNoMkViUSqi8\n-----END PRIVATE KEY-----\n", "client_email": "tts-lampada@projeto-lampada-001.iam.gserviceaccount.com", "client_id": "110294118319408262546", "auth_uri": "https://accounts.google.com/o/oauth2/auth", "token_uri": "https://oauth2.googleapis.com/token", "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs", "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/tts-lampada%40projeto-lampada-001.iam.gserviceaccount.com", "universe_domain": "googleapis.com"}' \
    --env GOOGLE_TTS_API_KEY="AIzaSyDBAC5sVS7uHAQ0azr6Bw4D1Aavor7Zc6A" \
    lampada-iprviamao-com-br:latest
