#!/bin/bash
echo "🔧 Configuring NGINX and Laravel for Azure App Service..."

# ✅ Add this block to increase upload limits for NGINX
echo "🔧 Setting NGINX client_max_body_size to 100M"
echo "client_max_body_size 100M;" >> /etc/nginx/nginx.conf

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

# Configure Laravel for HTTPS (fix mixed content issues)
echo "🔒 Configuring Laravel for HTTPS..."

# Update .env for HTTPS if APP_URL is not set to HTTPS
if ! grep -q "APP_URL=https://" .env 2>/dev/null; then
    echo "🔧 Setting APP_URL to HTTPS in .env..."
    sed -i 's|APP_URL=.*|APP_URL=https://uat.travclicks.com|g' .env || echo "Could not update APP_URL"
fi

# Ensure critical environment variables are set
echo "🔧 Ensuring critical environment variables are set..."
if ! grep -q "APP_ENV=" .env 2>/dev/null; then
    echo "APP_ENV=production" >> .env
fi
if ! grep -q "APP_DEBUG=" .env 2>/dev/null; then
    echo "APP_DEBUG=false" >> .env
fi

# Add HTTPS enforcement variables
if ! grep -q "FORCE_HTTPS=" .env 2>/dev/null; then
    echo "FORCE_HTTPS=true" >> .env
fi
if ! grep -q "ASSET_URL=" .env 2>/dev/null; then
    echo "ASSET_URL=https://uat.travclicks.com/backadm-dmc" >> .env
fi

# Force HTTPS in Laravel configuration
export APP_URL="https://uat.travclicks.com"
export ASSET_URL="https://uat.travclicks.com/backadm-dmc"
export APP_ENV="production"
export FORCE_HTTPS="true"
export HTTPS="on"
export SERVER_PORT="443"

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

# Debug Laravel routes
echo "🔍 Checking Laravel routes..."
php artisan route:list --path=login || echo "Route list failed"

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
echo "🔒 HTTPS configuration applied to fix mixed content issues"
echo "📁 Storage permissions: $(ls -la /home/site/wwwroot/backadm-dmc/storage | head -5)"
echo "🔍 Debug URL: https://uat.travclicks.com/laravel-debug.php"
