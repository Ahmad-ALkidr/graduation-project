# Use PHP 8.2 CLI image
FROM php:8.2-cli

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    libpq-dev \
    git \
    curl \
    && apt-get clean

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ✨ --- THIS IS THE FIX --- ✨

# 1. Copy ALL application files first
COPY . .

# 2. Now that the 'artisan' file exists, run composer install
RUN composer install 

# 3. Generate the app key
RUN php artisan key:generate

# ✨ --- END OF FIX --- ✨

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Copy and set permissions for the entrypoint script
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Expose port for Render
EXPOSE 10000

# Run entrypoint
CMD ["/entrypoint.sh"]
