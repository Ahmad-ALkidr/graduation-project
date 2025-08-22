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



# --- Optimize Composer caching ---

# Copy only composer files first

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader



# Copy application files

COPY . .



# Copy entrypoint script

COPY entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh



# Copy .env.example if .env not exists

RUN if [ ! -f .env ]; then cp .env.example .env; fi



# Generate app key

RUN php artisan key:generate



# Set permissions

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache



# Expose port for Render

EXPOSE 10000



# Run entrypoint

CMD ["/entrypoint.sh"]
