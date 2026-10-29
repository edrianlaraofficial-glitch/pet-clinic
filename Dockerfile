FROM php:8.4-apache AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev libsqlite3-dev libonig-dev libzip-dev libicu-dev \
    && docker-php-ext-install \
       pdo_pgsql pdo_sqlite mbstring bcmath intl zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=pgsql \
    LOG_CHANNEL=stderr

RUN sed -ri \
    -e 's!/var/www/html!/var/www/html/public!g' \
    -e 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' \
    /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && printf '<Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>\n' \
    > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --optimize-autoloader \
    --no-scripts \
    --no-autoloader

COPY . .

RUN composer dump-autoload \
    --no-dev \
    --no-interaction \
    --optimize \
    && mkdir -p storage/app/public \
                storage/framework/cache/data \
                storage/framework/sessions \
                storage/framework/views \
                storage/logs \
                bootstrap/cache \
    && ln -s ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache

FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY resources ./resources
COPY vite.config.js ./
COPY --from=app /var/www/html/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

FROM app AS runtime

COPY --from=frontend /app/public/build ./public/build

EXPOSE 10000

CMD ["apache2-foreground"]
