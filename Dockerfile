# Multi-stage build: get Composer, then build PHP Apache image
FROM composer:2 AS composer

FROM php:8.2-apache

# Install system dependencies and PHP extensions needed by Laravel
RUN apt-get update \
    && apt-get install -y \
        git \
        unzip \
        nodejs \
        npm \
        libpq-dev \
        libonig-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        zip \
        gd \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set Apache document root to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copy application code into image
COPY . /var/www/html

# Build frontend assets with Vite (creates public/build/manifest.json)
RUN npm install \
    && npm run build \
    && rm -rf node_modules

# Ensure storage and cache directories are writable by the web server
RUN chown -R www-data:www-data storage bootstrap/cache \
    && find storage bootstrap/cache -type f -exec chmod 664 {} \; \
    && find storage bootstrap/cache -type d -exec chmod 775 {} \;

# Copy Composer from the composer stage and install PHP dependencies
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

EXPOSE 80

CMD ["apache2-foreground"]
