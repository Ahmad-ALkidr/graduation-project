#!/usr/bin/env bash
# exit on error
set -o errexit

# تثبيت الاعتماديات
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# تشغيل الـ migrations (سيتم تشغيله لاحقًا من لوحة التحكم)
# php artisan migrate --force

# مسح الكاش وتحسين الأداء
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ربط مجلد التخزين
php artisan storage:link
