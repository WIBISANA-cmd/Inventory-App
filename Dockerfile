# syntax=docker/dockerfile:1

# Catatan: aplikasi ini adalah UI Bootstrap 5 klasik (template "KaiAdmin") yang di-serve
# langsung dari public/admin — tidak ada build step JS/CSS (Vite/Tailwind/Alpine sudah
# dihapus karena tidak pernah dipakai). Jadi tidak ada stage Node.js di sini.

##########################################################
# Stage 1: Base image (PHP 8.2 + Apache + extensions)
##########################################################
# Composer install SENGAJA dijalankan di atas image ini, bukan di image "composer:2".
# Image composer:2 memakai versi PHP terbaru (8.5) dan tidak punya ext-gd, sehingga
# `composer install` gagal (exit code 2) karena lock file butuh ext-gd
# (milon/barcode, simple-qrcode) dan nette/schema hanya mendukung PHP 8.1 - 8.4.
FROM php:8.2-apache AS base

# System dependencies required by Laravel + dompdf + gd + zip + pdo drivers
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libicu-dev \
        libxml2-dev \
        libonig-dev \
        libpq-dev \
        postgresql-client \
        default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1


##########################################################
# Stage 2: Install PHP (Composer) dependencies
##########################################################
FROM base AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative --no-scripts


##########################################################
# Stage 3: Runtime image (Apache + mod_php)
##########################################################
FROM base AS app

# Apache: enable rewrite (needed by Laravel's public/.htaccess) and point vhost to /public
RUN a2enmod rewrite headers
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Recommended production PHP settings
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html

# Application source
COPY --chown=www-data:www-data . .

# Composer vendor/ built in stage "vendor"
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor

# Laravel needs these writable at runtime
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/testing storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
