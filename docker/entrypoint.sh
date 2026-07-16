#!/bin/bash
set -e

echo "🚀 Starting Diagram Coffee Backend..."

# Cache configuration for performance
echo "📦 Caching config & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Ensure storage directories exist and are writable
echo "📁 Setting up storage..."
php artisan storage:link 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Start PHP-FPM in background
echo "⚙️ Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground (keeps container alive)
echo "🌐 Starting Nginx on port 10000..."
nginx -g "daemon off;"
