#!/bin/bash
echo "🔧 Configuring NGINX and Laravel for Azure App Service..."

# =====================================================
# Copy NGINX configuration
# =====================================================
if [ -f /home/site/wwwroot/default ]; then
  cp /home/site/wwwroot/default /etc/nginx/sites-available/default
  cp /home/site/wwwroot/default /etc/nginx/sites-enabled/default
  echo "🌐 Custom NGINX config copied successfully."
else
  echo "⚠️ default NGINX config not found in /home/site/wwwroot/"
fi

service nginx reload || echo "⚠️ NGINX reload failed (may continue)"

# =====================================================
# WRITABLE STORAGE FIX FOR AZURE
# =====================================================
echo "🔧 Fixing Laravel writable directories..."

# Create persistent writable storage
mkdir -p /home/laravel-storage/logs
mkdir -p /home/laravel-storage/framework/{cache,sessions,views}
chmod -R 777 /home/laravel-storage

# Re-link Laravel storage if wwwroot is writable
if [ -w "/home/site/wwwroot/backadm-dmc" ]; then
  echo "🔁 Relinking Laravel storage..."
  rm -rf /home/site/wwwroot/backadm-dmc/storage
  ln -sfn /home/laravel-storage /home/site/wwwroot/backadm-dmc/storage
else
  echo "⚠️ /home/site/wwwroot/backadm-dmc is read-only; skipping storage symlink"
fi

# =====================================================
# LARAVEL SETUP
# =====================================================
echo "📁 Ensuring Laravel bootstrap cache exists..."
mkdir -p /home/site/wwwroot/backadm-dmc/bootstrap/cache
chmod -R 777 /home/site/wwwroot/backadm-dmc/bootstrap/cache

cd /home/site/wwwroot/backadm-dmc || {
  echo "❌ Could not cd into backadm-dmc"
  exit 1
}

# Set environment variables
echo "🔧 Ensuring environment configuration..."
export APP_URL="https://dev.travclicks.com"
export ASSET_URL="https://dev.travclicks.com/backadm-dmc"
export APP_ENV="production"
export FORCE_HTTPS="true"
export HTTPS="on"
export SERVER_PORT="443"

# Ensure .env contains defaults
if [ ! -f .env ]; then
  echo "⚙️ Creating missing .env file..."
  cp .env.example .env || touch .env
fi

sed -i 's|^APP_URL=.*|APP_URL=https://dev.travclicks.com|g' .env
sed -i 's|^ASSET_URL=.*|ASSET_URL=https://dev.travclicks.com/backadm-dmc|g' .env

# Laravel key check
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
  echo "🔑 Generating Laravel key..."
  php artisan key:generate --force || echo "⚠️ Key generation failed"
fi

# Clear and optimize caches
echo "🧹 Clearing Laravel caches..."
php artisan optimize:clear || echo "⚠️ Failed to clear cache"

echo "⚡ Optimizing Laravel..."
php artisan config:cache || echo "⚠️ Failed to cache config"

# =====================================================
# NGINX VALIDATION
# =====================================================
echo "🔧 Testing NGINX configuration..."
nginx -t
if [ $? -eq 0 ]; then
  echo "✅ NGINX configuration valid"
  service nginx reload
else
  echo "❌ NGINX configuration invalid!"
  nginx -t || true
fi

# =====================================================
# REACT BUILD CHECK
# =====================================================
if [ -f "/home/site/wwwroot/index.html" ]; then
  echo "✅ React index.html found"
else
  echo "⚠️ React build not found at /home/site/wwwroot/"
fi

echo "✅ Startup completed. Laravel storage path: /home/laravel-storage"
