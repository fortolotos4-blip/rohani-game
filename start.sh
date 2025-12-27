#!/bin/bash

echo "🚀 Starting Laravel..."

# generate key kalau belum ada
php artisan key:generate --force || true

# migrate database (AMAN, tidak hapus data)
php artisan migrate --force || true

# buat storage symlink (AMAN kalau sudah ada)
php artisan storage:link || true

# clear & rebuild cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "✅ Laravel ready"

# start apache
apache2-foreground
