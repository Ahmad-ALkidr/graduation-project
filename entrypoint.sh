#!/bin/sh

# --- تحقق من وجود PORT ---
if [ -z "$PORT" ]; then
  echo "⚠️ متغير PORT غير معرف، سيتم استخدام 10000 كافتراضي"
  PORT=10000
fi

# --- Run migrations once ---
echo "🔄 Running migrations..."
php artisan migrate --force

# --- Seeders check & run without tinker ---
echo "🔄 Checking seeders..."

php artisan tinker --execute "
\$seeders = [
    'App\\Models\\University' => 'UniversityDataSeeder',
    'App\\Models\\Category'   => 'CategorySeeder',
    'App\\Models\\User'       => 'UserSeeder',
];

foreach (\$seeders as \$model => \$seeder) {
    if (\$model::count() === 0) {
        echo '🔹 Seeding ' . \$seeder . \"...\n\";
        \$this->call(\$seeder::class);
        echo '✅ ' . \$seeder . \" executed\n\";
    } else {
        echo '⚠️ ' . \$seeder . \" skipped (data exists)\n\";
    }
}
"

# --- Start queue worker in background ---
echo "🔄 Starting queue worker..."
php artisan queue:work --tries=3 --timeout=90 >> /var/www/html/storage/logs/queue.log 2>&1 &

# --- Start Laravel scheduler in background ---
echo "🔄 Starting scheduler..."
while true; do
  php artisan schedule:run >> /var/www/html/storage/logs/scheduler.log 2>&1
  sleep 60
done &

# --- Start Laravel server ---
echo "🚀 Starting Laravel server on port $PORT..."
php artisan serve --host 0.0.0.0 --port $PORT
