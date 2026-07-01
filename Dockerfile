FROM php:8.2-apache

WORKDIR /var/www/html

# System dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libjpeg-dev libzip-dev zip libicu-dev libfreetype6-dev libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        intl

# PHP extensions
RUN pecl install redis && docker-php-ext-enable redis

# Node.js for frontend build
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm

# Apache configuration
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf && \
    sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
COPY ./apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy full application before composer so artisan exists
COPY . .

# Create required directories for storage
RUN mkdir -p storage/app/public/products
RUN mkdir -p public

# Storage symlink must exist after COPY (idempotent; production symlink is created in build)
RUN rm -rf public/storage
RUN php artisan storage:link
RUN chown -R www-data:www-data storage bootstrap/cache public
RUN chmod -R 775 storage bootstrap/cache public

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer config -g process-timeout 2000 && composer install --no-dev --optimize-autoloader --prefer-dist --no-progress --no-interaction

# Frontend build
RUN npm ci --production=false --no-audit --no-fund
RUN npm run build

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Pre-flight cache clear (idempotent, swallow errors on first deploy)
RUN php artisan config:clear || true

EXPOSE 80

CMD sh -c "
echo '===== STORAGE DEBUG =====' &&
mkdir -p storage/app/public/products &&
rm -rf public/storage &&
php artisan storage:link &&
ls -la public &&
ls -la public/storage &&
ls -la storage/app/public &&
ls -la storage/app/public/products &&
php artisan migrate --force &&
php artisan db:seed --force &&
apache2-foreground"
