# Use a simple PHP base image
FROM php:8.2-cli

# Set working directory
WORKDIR /var/www/html

# Install system dependencies needed for Laravel
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    libpq-dev # For PostgreSQL

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy your application files
COPY . .

# Install composer dependencies
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Set permissions for storage and bootstrap cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# The command to run when the container starts
# Render will provide the $PORT variable
CMD php artisan serve --host 0.0.0.0 --port $PORT
