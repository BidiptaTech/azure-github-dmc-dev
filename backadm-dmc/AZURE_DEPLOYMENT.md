# Azure App Service Deployment Guide

## Overview
This Laravel application is configured for deployment to Azure App Service in a subdirectory structure where:
- Main domain: `https://dev.travclicks.com`
- Laravel backend: `https://dev.travclicks.com/backadm-dmc`
- React frontend: Root directory (`https://dev.travclicks.com`)

## Pre-Deployment Checklist

### 1. Environment Configuration
Create a `.env` file with the following settings:

```env
APP_NAME="DMC Backend"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://dev.travclicks.com/backadm-dmc

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=YOUR_DB_HOST
DB_PORT=3306
DB_DATABASE=YOUR_DB_NAME
DB_USERNAME=YOUR_DB_USER
DB_PASSWORD=YOUR_DB_PASSWORD

# File Storage (Azure Blob Storage recommended)
FILESYSTEM_DISK=azure
AZURE_STORAGE_NAME=YOUR_AZURE_STORAGE_ACCOUNT
AZURE_STORAGE_KEY=YOUR_AZURE_STORAGE_KEY
AZURE_STORAGE_CONTAINER=uploads
AZURE_STORAGE_ENDPOINT=https://YOUR_AZURE_STORAGE_ACCOUNT.blob.core.windows.net

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=YOUR_MAIL_HOST
MAIL_PORT=587
MAIL_USERNAME=YOUR_MAIL_USERNAME
MAIL_PASSWORD=YOUR_MAIL_PASSWORD
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@travclicks.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. Azure App Service Configuration

#### App Settings (Environment Variables)
Configure the following in Azure App Service → Configuration → Application Settings:

- `APP_URL`: `https://dev.travclicks.com/backadm-dmc`
- `APP_ENV`: `production`
- `APP_DEBUG`: `false`
- All database and storage credentials

#### General Settings
- **Runtime stack**: PHP 8.2
- **Platform**: Linux
- **Startup Command**: Leave empty (uses default Laravel configuration)

### 3. Directory Structure After Deployment
```
wwwroot/
├── backadm-dmc/           # Laravel application
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/            # Laravel public files
│   │   ├── index.php      # Main entry point
│   │   ├── .htaccess      # URL rewriting rules
│   │   ├── web.config     # IIS configuration
│   │   └── assets/        # Static assets
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   └── vendor/
└── [React frontend files] # React app files in root
```

### 4. Database Setup
1. Ensure your MySQL database is accessible from Azure
2. Run migrations after deployment:
   ```bash
   php artisan migrate --force
   ```

### 5. File Permissions
The following directories need write permissions:
- `storage/`
- `bootstrap/cache/`

### 6. Storage Link
Create the storage symbolic link:
```bash
php artisan storage:link
```

## Deployment Process

### Method 1: Using CI/CD Pipeline (Recommended)
1. Push to feature branch
2. CI/CD pipeline automatically deploys to staging branch
3. Staging branch deploys to Azure App Service in `/backadm-dmc` folder

### Method 2: Manual Deployment
1. Use the provided `azure-deploy.sh` script
2. Ensure proper environment variables are set
3. Run deployment commands

## Key Files Modified for Subdirectory Deployment

### 1. `app/Providers/AppServiceProvider.php`
- Added URL scheme forcing and subdirectory configuration
- Configures proper HTTPS and subdirectory paths in production

### 2. `app/helpers.php`
- Added `subdirectory_asset()` and `subdirectory_url()` helper functions
- Ensures proper asset and URL generation for subdirectory deployment

### 3. `public/.htaccess`
- Updated rewrite rules for subdirectory handling
- Added redirect rules for proper URL structure

### 4. `public/web.config`
- IIS configuration for Azure App Service compatibility
- Handles URL rewriting and static file serving

### 5. Layout Files
- Updated `layouts/header.blade.php`
- Updated `layouts/footer.blade.php`
- Updated `layouts/layout.blade.php`
- Changed from `env('APP_URL')` to `subdirectory_asset()` function

## URL Structure

### Backend Routes
- Dashboard: `https://dev.travclicks.com/backadm-dmc/dashboard`
- API: `https://dev.travclicks.com/backadm-dmc/api/*`
- Admin: `https://dev.travclicks.com/backadm-dmc/admin/*`

### Static Assets
- CSS: `https://dev.travclicks.com/backadm-dmc/assets/css/*`
- JS: `https://dev.travclicks.com/backadm-dmc/assets/js/*`
- Images: `https://dev.travclicks.com/backadm-dmc/assets/images/*`

### Storage Files
- Public Storage: `https://dev.travclicks.com/backadm-dmc/storage/*`
- Azure Blob Storage: `https://yourstorageaccount.blob.core.windows.net/uploads/*`

## Testing Deployment

### 1. Check Application Access
- Visit: `https://dev.travclicks.com/backadm-dmc`
- Should redirect to login page or dashboard

### 2. Test Asset Loading
- Verify CSS and JS files load correctly
- Check browser developer tools for 404 errors

### 3. Test File Uploads
- Try uploading files through the admin interface
- Verify files are stored in Azure Blob Storage

### 4. Database Connectivity
- Test login functionality
- Verify data is loading from database

## Common Issues and Solutions

### 1. Assets Not Loading (404 Errors)
- Verify `APP_URL` includes `/backadm-dmc`
- Check that `subdirectory_asset()` function is used in views
- Clear cache: `php artisan cache:clear`

### 2. Routes Not Working
- Verify `.htaccess` file is present and correct
- Check if URL rewriting is enabled in Azure App Service
- Clear route cache: `php artisan route:clear`

### 3. Storage Issues
- Verify Azure Blob Storage credentials
- Check if `storage:link` command was run
- Verify file permissions on storage directory

### 4. Database Connection Issues
- Check database credentials in environment variables
- Verify database server allows connections from Azure
- Test connection using Azure's Advanced Tools (Kudu)

## Monitoring and Maintenance

### Log Files
- Laravel logs: `storage/logs/laravel.log`
- Azure App Service logs: Available in Azure Portal

### Performance Optimization
- Use Azure CDN for static assets
- Enable PHP OPcache
- Use Azure Cache for Redis for sessions/cache

### Backup Strategy
- Database: Use Azure Database for MySQL backup features
- Files: Azure Blob Storage provides automatic redundancy
- Code: Git repository serves as code backup

## Support
For deployment issues, check:
1. Azure App Service logs
2. Laravel application logs
3. Browser developer tools for client-side errors
4. Database connection logs 