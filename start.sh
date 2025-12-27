#!/bin/bash
set -e

echo "🚀 Starting Laravel setup..."

# Generate APP_KEY hanya jika kosong
if [ -z "$APP_KEY" ]; then
  echo "🔑 Generating APP_KEY..."
  php artisan key:generate --force
else
  echo "🔑 APP_KEY already set"
fi

# Permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Migrate database
echo "🗄️ Running migrations..."
php artisan migrate --force

# Seed database
echo "🌱 Seeding database..."
php artisan db:seed --force

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "✅ Laravel ready"

# Start Apache
apache2-foreground
