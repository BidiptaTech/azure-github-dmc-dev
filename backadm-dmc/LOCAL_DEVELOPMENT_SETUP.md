# Local Development Setup

This guide explains how to set up the Laravel application for local development using XAMPP, separate from the Azure Key Vault integration used in staging/production.

## Quick Setup

1. **Copy the local environment template:**
   ```bash
   copy env.local.template .env.local
   ```
   
   Or on Linux/Mac:
   ```bash
   cp env.local.template .env.local
   ```

2. **Update database settings in `.env.local`:**
   ```env
   DB_DATABASE=your_local_database_name
   DB_USERNAME=root
   DB_PASSWORD=your_mysql_password
   ```

3. **Create the local database in XAMPP:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database with the name you specified in `DB_DATABASE`

4. **Run Laravel setup commands:**
   ```bash
   php artisan migrate
   php artisan db:seed
   php artisan storage:link
   ```

## Environment File Priority

The application automatically detects which environment file to use:

1. **`.env.local`** - Used for local development (if exists)
2. **`.env`** - Used for staging/production with Azure Key Vault

## Local Development Configuration

### Database (XAMPP MySQL)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dmc_local
DB_USERNAME=root
DB_PASSWORD=
```

### Application URL
```env
APP_URL=http://localhost/dev_dmc/public
```

### Azure Key Vault (Disabled)
```env
USE_AZURE_KEYVAULT=false
```

### File Storage (Local)
```env
FILESYSTEM_DISK=local
```

### Mail Testing (Mailtrap recommended)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

## XAMPP Configuration

### Apache Configuration
Make sure your XAMPP Apache is configured to serve the Laravel application:

1. **Document Root:** Point to `C:/xampp/htdocs/dev_dmc/public`
2. **URL Rewriting:** Ensure `mod_rewrite` is enabled
3. **PHP Version:** Use PHP 8.2 or higher

### MySQL Configuration
1. Start MySQL service in XAMPP
2. Create database via phpMyAdmin
3. Import any existing database dump if available

## Development Workflow

### Starting Development
```bash
# Start XAMPP services (Apache + MySQL)
# Navigate to project directory
cd C:/xampp/htdocs/dev_dmc

# Install dependencies (if first time)
composer install

# Run migrations
php artisan migrate

# Start development
# Access: http://localhost/dev_dmc/public
```

### Testing Azure Key Vault Integration
Even in local development, you can test Azure Key Vault integration:

```bash
# Temporarily enable Azure Key Vault in .env.local
USE_AZURE_KEYVAULT=true

# Add Azure credentials to .env.local
AZURE_KEYVAULT_NAME=dmcKeyVault-dev
AZURE_CLIENT_ID=your-client-id
AZURE_CLIENT_SECRET=your-client-secret
AZURE_TENANT_ID=your-tenant-id

# Test the connection
php artisan azure:test-keyvault list
```

## File Structure

```
dev_dmc/
├── .env                    # Staging/Production (Azure Key Vault)
├── .env.local             # Local Development (Git ignored)
├── env.local.template     # Template for local development
├── bootstrap/app.php      # Auto-detects environment file
└── ...
```

## Git Workflow

### What's Tracked
- `.env` - Staging configuration with Azure Key Vault
- `env.local.template` - Template for local development

### What's Ignored
- `.env.local` - Your personal local configuration

### Switching Between Environments
```bash
# For local development
cp env.local.template .env.local
# Edit .env.local with your local settings

# For staging deployment
# Use .env file (already configured for Azure Key Vault)
```

## Troubleshooting

### Common Issues

**1. Database Connection Failed**
- Check XAMPP MySQL is running
- Verify database name exists in phpMyAdmin
- Check credentials in `.env.local`

**2. Application URL Issues**
- Ensure `APP_URL=http://localhost/dev_dmc/public`
- Check Apache document root configuration
- Verify mod_rewrite is enabled

**3. File Permissions**
- Ensure `storage/` and `bootstrap/cache/` are writable
- Run: `chmod -R 775 storage bootstrap/cache` (Linux/Mac)

**4. Environment File Not Loading**
- Check `.env.local` exists and has correct syntax
- Clear config cache: `php artisan config:clear`
- Check Laravel logs: `storage/logs/laravel.log`

### Debug Commands

```bash
# Check which environment file is being used
php artisan env

# Check current configuration
php artisan config:show

# Clear all caches
php artisan optimize:clear

# Test database connection
php artisan migrate:status
```

## Production Deployment

When deploying to staging/production:

1. **Don't deploy `.env.local`** (it's git ignored)
2. **Use `.env`** with Azure Key Vault configuration
3. **Set `USE_AZURE_KEYVAULT=true`** in production
4. **Configure Azure App Service** with minimal environment variables

The application will automatically use Azure Key Vault in production and local files in development. 