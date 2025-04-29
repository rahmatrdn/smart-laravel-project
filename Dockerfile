FROM php:8.3-cli

# 1. Set working dir
WORKDIR /app

# 2. Copy your application code
COPY --chown=www-data:www-data . /app

# 3. Install OS libs + build tools, compile & enable extensions

RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip4 \
    libpng16-16 \
    libjpeg62-turbo \
    libfreetype6 \
    # Build dependencies needed only during compilation:
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    build-essential \
    nano \
    # Tools for composer fallback and general utility:
    git \
    unzip \
    # zip command-line utility (optional)
    # zip \
    && \
    # Configure GD extension
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    # Install core PHP extensions
    docker-php-ext-install -j$(nproc) zip gd pcntl opcache pdo pdo_mysql && \
    # Install redis extension via PECL
    pecl install redis && \
    pecl install swoole && \
    # Enable the PECL extension
    docker-php-ext-enable redis && \
    docker-php-ext-enable swoole && \
    # Clean up ONLY build dependencies (packages explicitly installed above will remain)
    apt-get purge -y --auto-remove build-essential \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev && \
    # Clean up apt cache
    rm -rf /var/lib/apt/lists/*

# 4. Copy only your custom tweaks into conf.d (so default php.ini stays intact) :contentReference[oaicite:0]{index=0}
# COPY ./deploy/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY ./deploy/php.ini /usr/local/etc/php/

# 5. Install Composer & Octane, run artisan tasks
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer
RUN composer install --optimize-autoloader --no-dev \
 && composer require laravel/octane \
 && php artisan storage:link \
 && php artisan optimize \
 && php artisan octane:install --server=frankenphp

# 6. Fix permissions, expose port and start Octane
RUN chown -R www-data:www-data storage bootstrap/cache
EXPOSE 8000
CMD ["php", "artisan", "octane:start", \
     "--workers=10", "--server=frankenphp", \
     "--host=0.0.0.0", "--port=8000"]
