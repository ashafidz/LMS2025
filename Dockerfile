FROM php:8.3-fpm

# ============================================================
# System Dependencies
# ============================================================
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        xml \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ============================================================
# PHP Configuration
# ============================================================
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/custom.ini $PHP_INI_DIR/conf.d/custom.ini

# ============================================================
# Composer
# ============================================================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ============================================================
# Node.js 20 (untuk build Vite assets)
# ============================================================
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ============================================================
# Application
# ============================================================
WORKDIR /var/www/html

# Copy dependency files dulu (leverage Docker layer caching)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY package.json package-lock.json ./
RUN npm ci

# Copy seluruh source code
COPY . .

# Re-generate autoload classmap dengan seluruh source code
# (--no-scripts agar artisan tidak dipanggil saat build, karena belum ada DB)
RUN composer dump-autoload --optimize --no-dev --no-scripts

# Build frontend assets (Vite)
RUN npm run build && rm -rf node_modules

# ============================================================
# Permissions
# ============================================================
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# ============================================================
# Entrypoint (Copy assets to shared volume)
# ============================================================
RUN echo '#!/bin/sh' > /usr/local/bin/start.sh \
    && echo 'cp -R /var/www/html/public/* /var/www/html/shared_public/ 2>/dev/null || true' >> /usr/local/bin/start.sh \
    && echo 'exec php-fpm' >> /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

EXPOSE 9000
CMD ["/usr/local/bin/start.sh"]
