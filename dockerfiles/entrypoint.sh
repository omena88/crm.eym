#!/bin/sh
set -e

# Wait for the database to be ready
# This is a simple loop; for production, consider a more robust solution like dockerize
echo "Waiting for database..."
while ! mysqladmin ping -h"$DB_HOST" --silent; do
    sleep 1
done
echo "Database is ready."

# Run Laravel setup commands
php artisan storage:link || true
php artisan migrate --force
php artisan optimize:clear

# Start Supervisor to manage nginx and php-fpm
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/laravel.conf 