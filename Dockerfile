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

# ✨ --- THIS IS THE FIX --- ✨

# 1. Create the .env file first
COPY .env .env

# 2. Install dependencies. This will create the vendor/autoload.php file.
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 3. NOW, you can safely run artisan commands.
RUN php artisan key:generate

# Set permissions for storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose the port Render will use
EXPOSE 10000

# The command to run when the container starts
CMD php artisan serve --host 0.0.0.0 --port 10000
