# Stage 1: Install PHP dependencies with Composer
FROM composer:2.5 as vendor
WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist

# Stage 2: Build frontend assets with Node.js
FROM node:18-alpine as frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 3: Final production image
FROM php:8.2-fpm-alpine

# Install system dependencies: Nginx for web server, Supervisor for process management
RUN apk add --no-cache nginx supervisor curl mysql-client libzip-dev zip \
    && docker-php-ext-install pdo pdo_mysql zip bcmath opcache

# Create necessary directories and configurations
RUN mkdir -p /var/run/php /etc/supervisor/conf.d

# Configure PHP-FPM
COPY dockerfiles/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Configure Nginx
COPY dockerfiles/nginx.conf /etc/nginx/http.d/default.conf

# Configure Supervisor
COPY dockerfiles/supervisor.conf /etc/supervisor/conf.d/laravel.conf

WORKDIR /var/www/html

# Copy application code and compiled assets from previous stages
COPY --from=frontend /app .
COPY --from=vendor /app/vendor/ ./vendor

# Set correct ownership and permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80 to the outside world
EXPOSE 80

# Use an entrypoint script to run migrations and start services
COPY dockerfiles/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"] 