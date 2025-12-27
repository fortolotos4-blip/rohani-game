#!/bin/bash

echo "🚀 Starting Laravel setup..."

# ❌ JANGAN generate key di Render
echo "🔑 APP_KEY from environment (skip key:generate)"

# migrate (aman)
php artisan migrate --force || true

# seed (kalau sudah idempotent)
php artisan db:seed --force || true

# storage link (PENTING UNTUK GAMBAR)
php artisan storage:link || true

# clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "✅ Laravel ready"

# start apache
apache2-foreground
