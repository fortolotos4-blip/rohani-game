#!/bin/bash
set -e

echo "🚀 Starting Laravel (Render-safe)..."

# ❌ JANGAN generate key (Render pakai ENV)
echo "ℹ️ Using APP_KEY from Render Environment"

# migrate & seed aman
php artisan migrate --force || true
php artisan db:seed --force || true

# 🔥 INI YANG PALING PENTING
php artisan storage:link || true

# clear cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "✅ Laravel ready"

apache2-foreground
