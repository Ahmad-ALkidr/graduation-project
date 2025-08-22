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

# 1. Copy only composer files first (for caching)
COPY composer.json composer.lock ./

# 2. Install dependencies (بدون تشغيل artisan)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 3. Copy application files
COPY . .

# 4. نسخ env.example -> env (وقت build فقط، القيم الحقيقية هتيجي من Render)
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# 5. 👇 لا تعمل php artisan هنا (سيتم تشغيله وقت runtime داخل Render بعد تحميل env)
# RUN php artisan key:generate   ← ❌ شيلها

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port
EXPOSE 10000

# Run entrypoint
CMD ["/entrypoint.sh"]
