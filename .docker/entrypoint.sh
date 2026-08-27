#!/bin/sh

# Create storage link if not exists
php artisan storage:link --force

# Run key generate if empty
if [ -z "$APP_KEY" ]; then
    php artisan key:generate
fi

# Run caching
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"
