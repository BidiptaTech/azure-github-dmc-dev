#!/bin/bash
echo "🔧 Configuring NGINX and Laravel for Azure App Service..."

# Copy our custom NGINX configuration
cp /home/site/wwwroot/default /etc/nginx/sites-available/default
cp /home/site/wwwroot/default /etc/nginx/sites-enabled/default

# Set proper Laravel permissions
echo "📁 Setting Laravel permissions..."
chmod -R 777 /home/site/wwwroot/backadm-dmc/storage
chmod -R 777 /home/site/wwwroot/backadm-dmc/bootstrap/cache

# Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
cd /home/site/wwwroot/backadm-dmc
php artisan config:clear || echo "Config clear failed (OK if no config cached)"
php artisan cache:clear || echo "Cache clear failed (OK if no cache)"
php artisan view:clear || echo "View clear failed (OK if no views cached)"
php artisan route:clear || echo "Route clear failed (OK if no routes cached)"

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
echo "📁 Storage permissions: $(ls -la /home/site/wwwroot/backadm-dmc/storage)"
