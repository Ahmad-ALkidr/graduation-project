# 1. نبدأ من صورة رسمية PHP مع Apache
FROM php:8.2-apache

# 2. تثبيت الأدوات المطلوبة
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip exif pcntl bcmath

# 3. تفعيل Apache mod_rewrite (مطلوب للـ Laravel routing)
RUN a2enmod rewrite

# 4. إعداد مجلد العمل
WORKDIR /var/www/html

# 5. نسخ composer أولاً (لتثبيت الحزم)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 6. نسخ ملفات المشروع
COPY . .

# 7. تثبيت المكتبات باستخدام composer
RUN composer install --no-dev --optimize-autoloader

# 8. إعداد أذونات مجلد storage و bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 9. إعداد Apache VirtualHost للـ Laravel (توجيه إلى public/)
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# 10. المنفذ
EXPOSE 80

# 11. أمر التشغيل
CMD ["apache2-foreground"]
