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

# Create the .env file
RUN cp .env.example .env

# Install composer dependencies
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Generate the application key
RUN php artisan key:generate

# ❌ --- The migrate command has been REMOVED from here --- ❌

# Set permissions for storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose the port Render will use
EXPOSE 10000

# ✨ --- THIS IS THE FIX --- ✨
# The command to run when the container starts.
# It will now run migrations first, and then start the server.
CMD php artisan migrate --force && php artisan serve --host 0.0.0.0 --port 10000
