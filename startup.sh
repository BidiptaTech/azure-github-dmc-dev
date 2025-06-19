#!/bin/bash
echo "🔧 Configuring NGINX for Laravel..."

# Copy our custom NGINX configuration
cp /home/site/wwwroot/default /etc/nginx/sites-available/default
cp /home/site/wwwroot/default /etc/nginx/sites-enabled/default

# Test NGINX configuration
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
