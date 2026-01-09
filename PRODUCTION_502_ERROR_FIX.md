# Production 502 Bad Gateway Error - Fix Guide

## Problem Summary
Your production server is returning **502 Bad Gateway** errors when the JavaScript tries to fetch data from API endpoints. This causes the browser to receive HTML error pages instead of JSON, resulting in parsing errors.

### Error Messages You're Seeing:
```
Error fetching vehicle details: SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
Failed to load resource: the server responded with a status of 502 (Bad Gateway)
Error: SyntaxError: Unexpected token '<', "<html>..."... is not valid JSON
```

## What I Fixed in the Code (Already Done ✅)
- ✅ Added content-type checking before parsing JSON responses
- ✅ Added graceful error handling for non-JSON responses
- ✅ Updated `fetchVehicleDetails()` function
- ✅ Updated miscellaneous items fetch
- ✅ Updated zone prices fetch

## What Causes 502 Bad Gateway?

A 502 error means your web server (Nginx/Apache) cannot get a valid response from PHP-FPM. Common causes:

### 1. **PHP-FPM Crashed or Not Running**
```bash
# Check if PHP-FPM is running
sudo systemctl status php8.1-fpm
# or
sudo systemctl status php-fpm

# If not running, start it
sudo systemctl start php8.1-fpm
sudo systemctl restart php8.1-fpm
```

### 2. **PHP-FPM Timeout Issues**
The script takes too long to execute and PHP-FPM times out.

**Fix:** Edit your PHP-FPM configuration
```bash
# Edit PHP-FPM pool config (path may vary)
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

Add or update these values:
```ini
request_terminate_timeout = 300
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

### 3. **Nginx/Apache Timeout**
The web server times out waiting for PHP-FPM.

**For Nginx:**
```bash
sudo nano /etc/nginx/sites-available/your-site.conf
```

Add these inside the `server {}` or `location ~ \.php$ {}` block:
```nginx
fastcgi_read_timeout 300;
fastcgi_send_timeout 300;
proxy_read_timeout 300;
proxy_connect_timeout 300;
proxy_send_timeout 300;
```

**For Apache with mod_fcgid:**
```bash
sudo nano /etc/apache2/conf-available/fcgid.conf
```

Add:
```apache
FcgidIOTimeout 300
FcgidConnectTimeout 300
FcgidBusyTimeout 300
```

### 4. **Memory Limit Exceeded**
PHP runs out of memory.

**Fix:** Edit php.ini
```bash
sudo nano /etc/php/8.1/fpm/php.ini
```

Update:
```ini
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
```

### 5. **Laravel Queue Worker Issues**
If you're using queues, workers might be stuck.

```bash
# Restart queue workers
php artisan queue:restart

# Or in supervisor
sudo supervisorctl restart all
```

### 6. **Database Connection Issues**
Check if your database is accessible and not timing out.

```bash
# Test database connection from production server
php artisan tinker
# Then run: DB::connection()->getPdo();
```

## Quick Fix Steps (Do These First)

### Step 1: Restart All Services
```bash
# Restart PHP-FPM
sudo systemctl restart php8.1-fpm

# Restart Web Server
sudo systemctl restart nginx
# OR
sudo systemctl restart apache2

# Clear Laravel caches
cd /path/to/your/project
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 2: Check Error Logs
```bash
# Check PHP-FPM error log
sudo tail -f /var/log/php8.1-fpm.log

# Check Nginx error log
sudo tail -f /var/log/nginx/error.log

# Check Laravel log
tail -f storage/logs/laravel.log
```

### Step 3: Check Permissions
```bash
# Ensure proper ownership
cd /path/to/your/project
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Step 4: Test API Endpoints Manually
```bash
# Test from production server itself
curl -v https://your-domain.com/api/vehicle-details?vehicle_id=1&dmc_id=1&mode=dmc
curl -v https://your-domain.com/api/miscellaneous/dmc/1
```

## Monitoring & Prevention

### 1. **Enable PHP Slow Log** (to find slow queries/code)
```bash
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

Add:
```ini
request_slowlog_timeout = 10s
slowlog = /var/log/php-fpm/slow.log
```

### 2. **Monitor Resources**
```bash
# Check server resources
htop

# Check disk space
df -h

# Check memory
free -h
```

### 3. **Use Laravel Telescope** (for debugging in production)
```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

## After Fixing

1. **Deploy the updated JavaScript** (with the error handling I added)
2. **Clear all caches** on production
3. **Monitor the logs** for any remaining issues
4. **Test the enquiry form** thoroughly

## Contact Checklist
If the issue persists, check with your hosting provider about:
- [ ] PHP-FPM configuration limits
- [ ] Server resource limits (RAM, CPU)
- [ ] Firewall rules blocking internal communication
- [ ] Load balancer timeout settings (if using one)
- [ ] CDN/proxy timeout settings (if using Cloudflare, etc.)

## Emergency Temporary Fix
If you need the site working immediately while debugging:

1. Increase all timeouts to very high values (5 minutes)
2. Restart all services
3. Monitor which specific API endpoint is slow
4. Optimize that specific endpoint's query/logic

---

**Note:** The JavaScript fixes I made will prevent the error from breaking the UI, but you still need to fix the root cause (502 error) on the server side.

