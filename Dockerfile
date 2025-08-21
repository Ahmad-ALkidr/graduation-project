# Use official PHP 8.2 FPM image
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    curl \
    && docker-php-ext-install pdo pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# (اختياري) توليد المفتاح محليًا، أو ضع APP_KEY في Environment Variables على Render
# RUN php artisan key:generate

# Expose Laravel port
EXPOSE 8000

# Command to run Laravel server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
