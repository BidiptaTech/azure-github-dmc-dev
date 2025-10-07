#!/bin/bash
echo "🔧 Configuring NGINX and Laravel for Azure App Service..."

# =========================
# COPY CUSTOM NGINX CONFIG
# =========================
cp /home/site/wwwroot/default /etc/nginx/sites-available/default
cp /home/site/wwwroot/default /etc/nginx/sites-enabled/default
echo "🌐 NGINX config copied."
service nginx reload || echo "service nginx reload failed (continuing...)"

# =========================
# WRITABLE STORAGE (hybrid fix)
# =========================
echo "📁 Ensuring Laravel writable storage..."

mkdir -p /home/site/wwwroot/backadm-dmc/storage/framework/{cache,sessions,views}
mkdir -p /home/site/wwwroot/backadm-dmc/storage/logs
chmod -R 777 /home/site/wwwroot/backadm-dmc/storage
chmod -R 777 /home/site/wwwroot/backadm-dmc/bootstrap/cache

echo "✅ Writable storage fixed locally (no external symlink)."

# =========================
# LARAVEL CONFIG
# =========================
cd /home/site/wwwroot/backadm-dmc || echo "❌ Could not cd into backadm-dmc"

echo "🔒 Setting Laravel environment variables..."
sed -i 's|APP_URL=.*|APP_URL=https://dev.travclicks.com|g' .env || echo "APP_URL update failed"
sed -i 's|ASSET_URL=.*|ASSET_URL=https://dev.travclicks.com/backadm-dmc|g' .env || echo "ASSET_URL update failed"

export APP_URL="https://dev.travclicks.com"
export ASSET_URL="https://dev.travclicks.com/backadm-dmc"
export APP_ENV="production"
export APP_DEBUG="false"
export FORCE_HTTPS="true"
export HTTPS="on"
export SERVER_PORT="443"

echo "🔑 Checking APP_KEY..."
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    php artisan key:generate --force || echo "APP_KEY already exists."
fi

# =========================
# OPTIMIZE & CLEAR CACHE
# =========================
echo "🧹 Clearing and caching Laravel..."
php artisan optimize:clear || echo "optimize:clear failed"
php artisan config:cache || echo "config:cache failed"
php artisan route:clear || true
php artisan view:clear || true

# =========================
# VERIFY REACT BUILD
# =========================
echo "🌐 Checking React build..."
if [ -f "/home/site/wwwroot/index.html" ]; then
    echo "✅ index.html found"
    chmod -R 755 /home/site/wwwroot
else
    echo "❌ React build missing under /home/site/wwwroot/"
fi

# =========================
# NGINX VALIDATION
# =========================
echo "🔧 Validating NGINX config..."
nginx -t && service nginx reload && echo "✅ NGINX OK" || echo "❌ NGINX validation failed"

echo "🎯 Deployment completed."
echo "🔍 Debug: https://dev.travclicks.com/laravel-debug.php"
