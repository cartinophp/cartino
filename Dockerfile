# Cartino - Production Docker Setup
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    redis \
    supervisor \
    nginx

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    redis \
    intl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy application code
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Production optimizations
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan cartino:optimize --all

# Expose port
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Development stage
FROM base AS development

# Install development dependencies
RUN composer install --optimize-autoloader --no-interaction --prefer-dist

# Install Node.js and npm
RUN apk add --no-cache nodejs npm

# Copy package files
COPY package.json package-lock.json ./

# Install Node dependencies
RUN npm ci

# Build assets
RUN npm run build

# Copy development configuration
COPY docker/php-dev.ini /usr/local/etc/php/conf.d/app.ini

CMD ["php-fpm"]

# Testing stage
FROM development AS testing

# Install testing tools
RUN composer require --dev phpunit/phpunit

# Copy test configuration
COPY phpunit.xml ./

# Run tests
RUN vendor/bin/phpunit

# Production stage
FROM base AS production

# Remove unnecessary files
RUN rm -rf tests/ \
    && rm -rf docker/ \
    && rm -rf node_modules/ \
    && rm -rf .git/

# Final optimizations
RUN php artisan storage:link \
    && php artisan migrate --force \
    && php artisan db:seed --force
