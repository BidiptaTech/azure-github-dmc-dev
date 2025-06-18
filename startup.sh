#!/bin/bash

echo "🚀 Starting Laravel DMC Application..."

# Set proper permissions
echo "📁 Setting permissions..."
chmod -R 755 /home/site/wwwroot/backadm-dmc/storage
chmod -R 755 /home/site/wwwroot/backadm-dmc/bootstrap/cache

# Create storage link if it doesn't exist
echo "🔗 Creating storage link..."
cd /home/site/wwwroot/backadm-dmc
if [ ! -L "public/storage" ]; then
    php artisan storage:link --force
fi

# Clear and optimize Laravel
echo "⚙️ Optimizing Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Generate app key if not exists
echo "🔑 Checking app key..."
if grep -q "APP_KEY=$" .env 2>/dev/null; then
    php artisan key:generate --force
fi

echo "✅ Laravel startup completed!"

# Start the default startup process
exec "$@" 