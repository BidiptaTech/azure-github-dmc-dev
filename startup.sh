#!/bin/bash
echo "🔧 Configuring NGINX and Laravel for Azure App Service..."

# =========================
# (YOUR ORIGINAL COPY LINES — keep these)
# =========================
cp /home/site/wwwroot/default /etc/nginx/sites-available/default
cp /home/site/wwwroot/default /etc/nginx/sites-enabled/default

echo "🌐 Custom NGINX config copied to /etc/nginx/sites-available and sites-enabled."
service nginx reload || echo "service nginx reload failed (will continue and test later)"

# =====================================================
# WRITABLE STORAGE FIX FOR AZURE
# =====================================================
echo "🔧 Fixing Laravel writable directories..."

# Create writable storage under /home
mkdir -p /home/laravel-storage/logs
mkdir -p /home/laravel-storage/framework/{cache,sessions,views}
chmod -R 777 /home/laravel-storage

# Remove old (read-only) storage link if exists and re-link
rm -rf /home/site/wwwroot/backadm-dmc/storage
ln -sfn /home/laravel-storage /home/site/wwwroot/backadm-dmc/storage

echo "✅ Writable storage directory linked successfully."

# =====================================================
# EXISTING LARAVEL SETUP (slightly optimized)
# =====================================================
echo "📁 Ensuring Laravel bootstrap cache exists..."
mkdir -p /home/site/wwwroot/backadm-dmc/bootstrap/cache
chmod -R 777 /home/site/wwwroot/backadm-dmc/bootstrap/cache

cd /home/site/wwwroot/backadm-dmc || echo "❌ Could not cd into backadm-dmc"

echo "🔒 Configuring Laravel for HTTPS..."
if ! grep -q "APP_URL=https://" .env 2>/dev/null; then
    echo "🔧 Setting APP_URL to HTTPS in .env..."
    sed -i 's|APP_URL=.*|APP_URL=https://dev.travclicks.com|g' .env || echo "Could not update APP_URL"
fi

echo "🔧 Ensuring critical environment variables are set..."
if ! grep -q "APP_ENV=" .env 2>/dev/null; then
    echo "APP_ENV=production" >> .env
fi
if ! grep -q "APP_DEBUG=" .env 2>/dev/null; then
    echo "APP_DEBUG=false" >> .env
fi
if ! grep -q "FORCE_HTTPS=" .env 2>/dev/null; then
    echo "FORCE_HTTPS=true" >> .env
fi
if ! grep -q "ASSET_URL=" .env 2>/dev/null; then
    echo "ASSET_URL=https://dev.travclicks.com/backadm-dmc" >> .env
fi

export APP_URL="https://dev.travclicks.com"
export ASSET_URL="https://dev.travclicks.com/backadm-dmc"
export APP_ENV="production"
export FORCE_HTTPS="true"
export HTTPS="on"
export SERVER_PORT="443"

echo "🔑 Checking Laravel application key..."
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "🔑 Generating Laravel application key..."
    php artisan key:generate --force || echo "Key generation failed (may already exist)"
fi

# =====================================================
# CLEAR & OPTIMIZE CACHES
# =====================================================
echo "🧹 Clearing Laravel caches..."
php artisan optimize:clear || echo "Optimize clear failed (read-only system skipped)"

echo "⚡ Optimizing Laravel..."
php artisan config:cache || echo "Config cache failed (OK for development)"

echo "🔍 Checking Laravel routes..."
php artisan route:list --path=login || echo "Route list failed"

# =====================================================
# NGINX VALIDATION
# =====================================================
echo "🔧 Testing NGINX configuration..."
nginx -t
if [ $? -eq 0 ]; then
    echo "✅ NGINX configuration is valid"
    service nginx reload
    echo "✅ NGINX reloaded successfully"
else
    echo "❌ NGINX configuration error - dumping /etc/nginx/sites-available/default for debug"
    cat /etc/nginx/sites-available/default || true
    nginx -t || true
    exit 1
fi

# =====================================================
# REACT BUILD CHECK
# =====================================================
echo "🌐 Verifying React frontend files..."
if [ -f "/home/site/wwwroot/index.html" ]; then
    echo "✅ React index.html found!"
    chmod -R 755 /home/site/wwwroot/*.html || true
    chmod -R 755 /home/site/wwwroot/static || true
else
    echo "❌ React build missing: please ensure React is built into /home/site/wwwroot/"
fi

# =====================================================
# FINAL STATUS
# =====================================================
echo "🎯 Laravel app should now be accessible at /backadm-dmc/"
echo "🎯 React frontend should now load correctly at /"
echo "🔒 HTTPS configuration applied to fix mixed content issues"
echo "📁 Storage linked to writable path: /home/laravel-storage"
echo "🔍 Debug URL: https://dev.travclicks.com/laravel-debug.php"
