FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies, PHP extensions and Node.js
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libjpeg-dev libzip-dev zip libicu-dev \
    libfreetype6-dev libpq-dev default-mysql-client postgresql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql gd zip intl \
    && pecl install redis && docker-php-ext-enable redis \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Configure Apache document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy custom Apache vhost (ensure the file exists in the repo under ./apache/)
COPY ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Install Composer binary
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer config -g process-timeout 2000

# Copy composer config first for dependency caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-autoloader --no-scripts --prefer-dist --no-progress --no-interaction

# Copy npm config first for package caching
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

# Copy the rest of the application source code
COPY . .

# Complete composer autoload generation, build frontend assets, and prepare storage
RUN composer dump-autoload --optimize --no-dev \
    && npm run build \
    && mkdir -p storage/app/public/products \
    && rm -rf public/storage \
    && php artisan storage:link \
    && chown -R www-data:www-data storage bootstrap/cache public \
    && chmod -R 775 storage bootstrap/cache public

# Pre‑flight cache clear (idempotent)
RUN php artisan config:clear || true

# Add entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
