# Stage 1: Build Frontend Assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP Application & Web Server
FROM php:8.2-fpm

# Install system dependencies (including qpdf for PDF stamping)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    qpdf \
    nginx \
    && rm -rf /var/lib/apt/lists/*

# Configure & install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first for optimal layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application source code
COPY . /var/www

# Copy compiled frontend assets from Stage 1
COPY --from=frontend /app/public/build /var/www/public/build

# Complete composer autoloading optimization
RUN composer dump-autoload --optimize --no-dev

# Setup proper permissions for Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose HTTP port
EXPOSE 80

# Configure Nginx & Entrypoint
COPY .docker/nginx.conf /etc/nginx/sites-available/default
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

