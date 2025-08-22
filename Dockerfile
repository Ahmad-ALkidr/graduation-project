# Use PHP 8.2 CLI image
FROM php:8.2-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    libpq-dev \
    git \
    curl \
    && apt-get clean

RUN docker-php-ext-install pdo pdo_pgsql bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 1. Copy composer files only
COPY composer.json composer.lock ./

# 2. Install deps (بدون artisan key أو أي شيء)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 3. Copy application files (بدون .env لأنه مستبعد في .dockerignore)
COPY . .

# 4. إذا ما فيه .env وقت runtime، انسخ example
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# 5. الصلاحيات
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# 6. entrypoint.sh تنفيذي
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 10000

CMD ["/entrypoint.sh"]
