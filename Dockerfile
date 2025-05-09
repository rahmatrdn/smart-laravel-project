FROM php:8.3-cli

# 1. Set working dir
WORKDIR /app

COPY --chown=www-data:www-data . /app

# 2. Install OS libs, build tools & PHP extensions in satu layer
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip4 libpng16-16 libjpeg62-turbo libfreetype6 \
        libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
        build-essential nano git unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) zip gd pcntl opcache pdo pdo_mysql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    # hapus build deps
    && apt-get purge -y --auto-remove build-essential libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# 3. Copy konfigurasi PHP kustom
COPY ./deploy/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# 4. Copy hanya file composer untuk memanfaatkan cache
# COPY ./composer.json ./composer.lock /app/

# 5. Install PHP dependencies lewat Composer (layer terpisah)
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer
RUN composer install --optimize-autoloader --no-dev --prefer-dist \
    && rm -rf ~/.composer/cache

# 6. Copy seluruh kode aplikasi
# COPY --chown=www-data:www-data . /app

# 7. Artisan tasks & Octane
RUN php artisan storage:link \
    && php artisan optimize \
    && php artisan octane:install --server=frankenphp

# 8. Set permissions, expose port & start Octane
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000
CMD ["php", "artisan", "octane:start", "--workers=14", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]
