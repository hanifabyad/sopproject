#!/bin/sh
set -e

# Ensure storage directories exist (crucial when mounted as a Docker volume)
mkdir -p /var/www/storage/framework/cache/data \
         /var/www/storage/framework/sessions \
         /var/www/storage/framework/views \
         /var/www/storage/logs \
         /var/www/storage/app/public \
         /var/www/bootstrap/cache

# Fix permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Create storage symlink
php artisan storage:link --force || true

# Run key generate if empty
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run caching in production if configured
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"

