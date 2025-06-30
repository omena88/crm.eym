# Stage 1: Build frontend assets
FROM node:18-alpine as frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Final production image
FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx supervisor curl mysql-client libzip-dev zip freetype-dev libpng-dev libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip bcmath opcache gd

# Instala Composer
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ENV COMPOSER_MEMORY_LIMIT=-1

# Copia solo los archivos necesarios para instalar dependencias
COPY composer.json composer.lock ./
COPY database/ database/
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist

# Copia el resto del código
COPY . .

# Copia los assets ya construidos
COPY --from=frontend /app/public/build /var/www/html/public/build

# Copia los archivos de configuración
COPY dockerfiles/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY dockerfiles/nginx.conf /etc/nginx/http.d/default.conf
COPY dockerfiles/supervisor.conf /etc/supervisor/conf.d/laravel.conf
COPY dockerfiles/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"] 