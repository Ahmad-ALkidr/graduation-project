#!/bin/bash
set -e

# تنظيف وتجهيز Laravel
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force

# تشغيل Supervisor لإدارة كل الخدمات
exec /usr/bin/supervisord -c /etc/supervisord.conf
