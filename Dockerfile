# ==================================================
# Stage 1: Composer build
# ==================================================
FROM composer:2 AS builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY . .

RUN composer dump-autoload --optimize


# ==================================================
# Stage 2: Apache runtime
# ==================================================
FROM php:8.2-apache

WORKDIR /var/www

# System dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    default-mysql-client \
    unzip \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions (MySQL instead of PostgreSQL)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot to /var/www/public
ENV APACHE_DOCUMENT_ROOT /var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy application
COPY --from=builder /app /var/www

# Create missing storage directories
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache

# Permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]