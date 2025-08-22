#!/bin/sh
set -e

# Generate key if missing
if [ -z "$APP_KEY" ]; then
  php artisan key:generate --force
fi

# Run migrations
php artisan migrate --force

# Run queues in background
php artisan queue:work --daemon &

# Start Laravel server
exec php artisan serve --host 0.0.0.0 --port 10000
