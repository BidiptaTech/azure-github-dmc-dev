#!/bin/bash

echo "🚀 Starting Laravel DMC Application on Linux Azure App Service..."

# Navigate to Laravel application directory
cd /home/site/wwwroot/backadm-dmc || {
    echo "❌ Error: Laravel directory not found at /home/site/wwwroot/backadm-dmc"
    exit 1
}

echo "📁 Current directory: $(pwd)"
echo "📋 Directory contents:"
ls -la

# Set proper permissions for Laravel
echo "📁 Setting Laravel permissions..."
chmod -R 755 storage bootstrap/cache 2>/dev/null || true
chmod -R 777 storage/logs storage/framework 2>/dev/null || true

# Create necessary directories if they don't exist
mkdir -p storage/logs storage/framework/sessions storage/framework/views storage/framework/cache 2>/dev/null || true

# Check if .env file exists
if [ -f ".env" ]; then
    echo "✅ .env file found"
else
    echo "⚠️  .env file not found, creating basic .env..."
    cat > .env << 'EOF'
APP_NAME="DMC Travel Admin"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://dev.travclicks.com/backadm-dmc

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
EOF
fi

# Generate app key if not exists
echo "🔑 Checking Laravel app key..."
if grep -q "APP_KEY=$" .env 2>/dev/null || ! grep -q "APP_KEY=" .env 2>/dev/null; then
    echo "🔑 Generating Laravel application key..."
    php artisan key:generate --force --no-interaction
else
    echo "✅ Laravel app key already exists"
fi

# Create storage link if it doesn't exist
echo "🔗 Creating storage link..."
if [ ! -L "public/storage" ]; then
    php artisan storage:link --force
    echo "✅ Storage link created"
else
    echo "✅ Storage link already exists"
fi

# Clear and optimize Laravel (safe for production)
echo "⚙️ Optimizing Laravel for production..."
php artisan config:clear --no-interaction 2>/dev/null || true
php artisan cache:clear --no-interaction 2>/dev/null || true
php artisan route:clear --no-interaction 2>/dev/null || true
php artisan view:clear --no-interaction 2>/dev/null || true

# Cache configuration for production
echo "🚀 Caching Laravel configuration..."
php artisan config:cache --no-interaction 2>/dev/null || true
php artisan route:cache --no-interaction 2>/dev/null || true
php artisan view:cache --no-interaction 2>/dev/null || true

# Check Laravel installation
echo "🔍 Laravel health check..."
if [ -f "public/index.php" ]; then
    echo "✅ Laravel public/index.php found"
else
    echo "❌ Laravel public/index.php not found!"
fi

if [ -f "artisan" ]; then
    echo "✅ Laravel artisan found"
    php artisan --version 2>/dev/null || echo "⚠️ Could not get Laravel version"
else
    echo "❌ Laravel artisan not found!"
fi

# Display environment info
echo "📊 Environment Information:"
echo "   PHP Version: $(php -v | head -n 1)"
echo "   Working Directory: $(pwd)"
echo "   Laravel App URL: ${APP_URL:-https://dev.travclicks.com/backadm-dmc}"
echo "   Environment: $(grep APP_ENV .env | cut -d'=' -f2)"

echo "✅ Laravel DMC startup completed!"
echo "🌐 Application should be available at: https://dev.travclicks.com/backadm-dmc"

# Keep the container running (if needed)
exec "$@" 