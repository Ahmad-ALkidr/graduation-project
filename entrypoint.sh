#!/bin/sh



# --- Run migrations once ---

php artisan migrate --force



# --- Array of Seeders with corresponding model count checks ---

declare -A SEEDERS

# شكل: ["ModelClass"]="SeederClass"

SEEDERS["\App\Models\University"]="UniversityDataSeeder"

SEEDERS["\App\Models\Category"]="CategorySeeder"

SEEDERS["\App\Models\User"]="UserSeeder"

# أضف أي Seeder جديد هنا بنفس الشكل



# Loop through each seeder

for MODEL in "${!SEEDERS[@]}"; do

COUNT=$(php artisan tinker --execute "echo $MODEL::count();")

if [ "$COUNT" -eq "0" ]; then

php artisan db:seed --class=${SEEDERS[$MODEL]} --force

echo "✅ ${SEEDERS[$MODEL]} executed"

else

echo "⚠️ ${SEEDERS[$MODEL]} skipped (data already exists)"

fi

done



# --- Start queue worker in background ---

php artisan queue:work --tries=3 --timeout=90 &



# --- Start Laravel scheduler in background ---

while true; do

php artisan schedule:run >> /dev/null 2>&1

sleep 60

done &



# --- Start Laravel server ---

php artisan serve --host 0.0.0.0 --port $PORT
