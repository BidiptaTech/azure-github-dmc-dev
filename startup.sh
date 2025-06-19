#!/bin/bash
echo "🔧 Configuring NGINX and Laravel for Azure App Service..."

# Copy our custom NGINX configuration
cp /home/site/wwwroot/default /etc/nginx/sites-available/default
cp /home/site/wwwroot/default /etc/nginx/sites-enabled/default

# Set proper Laravel permissions
echo "📁 Setting Laravel permissions..."
chmod -R 777 /home/site/wwwroot/backadm-dmc/storage
chmod -R 777 /home/site/wwwroot/backadm-dmc/bootstrap/cache

# Ensure directories exist
echo "📁 Ensuring Laravel directories exist..."
mkdir -p /home/site/wwwroot/backadm-dmc/storage/framework/views
mkdir -p /home/site/wwwroot/backadm-dmc/storage/framework/cache/data
mkdir -p /home/site/wwwroot/backadm-dmc/storage/framework/sessions
mkdir -p /home/site/wwwroot/backadm-dmc/storage/logs
mkdir -p /home/site/wwwroot/backadm-dmc/bootstrap/cache

# Set permissions again after creating directories
chmod -R 777 /home/site/wwwroot/backadm-dmc/storage
chmod -R 777 /home/site/wwwroot/backadm-dmc/bootstrap/cache

# Navigate to Laravel directory
cd /home/site/wwwroot/backadm-dmc

# Generate application key if not set
echo "🔑 Checking Laravel application key..."
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "🔑 Generating Laravel application key..."
    php artisan key:generate --force || echo "Key generation failed (may already exist)"
fi

# Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
php artisan config:clear || echo "Config clear failed (OK if no config cached)"
php artisan cache:clear || echo "Cache clear failed (OK if no cache)"
php artisan view:clear || echo "View clear failed (OK if no views cached)"
php artisan route:clear || echo "Route clear failed (OK if no routes cached)"

# Try to cache config for better performance
echo "⚡ Optimizing Laravel..."
php artisan config:cache || echo "Config cache failed (OK for development)"

# Test NGINX configuration
echo "🔧 Testing NGINX configuration..."
nginx -t
if [ $? -eq 0 ]; then
    echo "✅ NGINX configuration is valid"
    service nginx reload
    echo "✅ NGINX reloaded successfully"
else
    echo "❌ NGINX configuration error"
    exit 1
fi

echo "🎯 Laravel app should now be accessible at /backadm-dmc/"
echo "📁 Storage permissions: $(ls -la /home/site/wwwroot/backadm-dmc/storage | head -5)"
echo "🔍 Debug URL: https://dev.travclicks.com/laravel-debug.php"
