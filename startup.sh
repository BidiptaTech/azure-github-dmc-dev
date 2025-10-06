#!/bin/bash
echo "🔧 Configuring NGINX and Laravel for Azure App Service..."

# =========================
# (YOUR ORIGINAL COPY LINES — keep these)
# =========================
# Copy our custom NGINX configuration (your existing commands are retained)
cp /home/site/wwwroot/default /etc/nginx/sites-available/default
cp /home/site/wwwroot/default /etc/nginx/sites-enabled/default

# === ADDED FOR REACT: ensure config copied early and nginx gently reloaded ===
echo "🌐 Custom NGINX config copied to /etc/nginx/sites-available and sites-enabled."
service nginx reload || echo "service nginx reload failed (will continue and test later)"

# =====================================================
# EXISTING LARAVEL SETUP (unchanged — your original script)
# =====================================================
echo "📁 Setting Laravel permissions..."
chmod -R 777 /home/site/wwwroot/backadm-dmc/storage
chmod -R 777 /home/site/wwwroot/backadm-dmc/bootstrap/cache

echo "📁 Ensuring Laravel directories exist..."
mkdir -p /home/site/wwwroot/backadm-dmc/storage/framework/views
mkdir -p /home/site/wwwroot/backadm-dmc/storage/framework/cache/data
mkdir -p /home/site/wwwroot/backadm-dmc/storage/framework/sessions
mkdir -p /home/site/wwwroot/backadm-dmc/storage/logs
mkdir -p /home/site/wwwroot/backadm-dmc/bootstrap/cache

chmod -R 777 /home/site/wwwroot/backadm-dmc/storage
chmod -R 777 /home/site/wwwroot/backadm-dmc/bootstrap/cache

cd /home/site/wwwroot/backadm-dmc || echo "Could not cd into backadm-dmc"

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

echo "🧹 Clearing Laravel caches..."
php artisan config:clear || echo "Config clear failed (OK if no config cached)"
php artisan cache:clear || echo "Cache clear failed (OK if no cache)"
php artisan view:clear || echo "View clear failed (OK if no views cached)"
php artisan route:clear || echo "Route clear failed (OK if no routes cached)"

echo "⚡ Optimizing Laravel..."
php artisan config:cache || echo "Config cache failed (OK for development)"

echo "🔍 Checking Laravel routes..."
php artisan route:list --path=login || echo "Route list failed"

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

# === ADDED FOR REACT: verify SPA files and set permissions for static files ===
echo "🌐 Verifying React frontend files..."
if [ -f "/home/site/wwwroot/index.html" ]; then
    echo "✅ React index.html found!"
    # set permissive read for nginx to serve; keep 777 for Laravel as before
    chmod -R 755 /home/site/wwwroot/*.html || true
    chmod -R 755 /home/site/wwwroot/static || true
else
    echo "❌ React build missing: please ensure React is built into /home/site/wwwroot/ (index.html + static/)"
fi

echo "🎯 Laravel app should now be accessible at /backadm-dmc/"
echo "🎯 React frontend should now load correctly at /"
echo "🔒 HTTPS configuration applied to fix mixed content issues"
echo "📁 Storage permissions: $(ls -la /home/site/wwwroot/backadm-dmc/storage | head -5)"
echo "🔍 Debug URL: https://dev.travclicks.com/laravel-debug.php"
