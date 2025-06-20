# Complete .env File for Staging Environment

Copy this content to your `.env` file in the staging branch:

```env
# ============================================================================
# AZURE KEY VAULT CONFIGURATION (Required for authentication)
# ============================================================================
USE_AZURE_KEYVAULT=true
AZURE_KEYVAULT_NAME=dmcKeyVault-dev
AZURE_CLIENT_ID=35889702-d898-43ea-815a-e9cc8c4a8f9e
AZURE_CLIENT_SECRET=2V28Q~lUG6Ew~Q64IyZdkEWLPvq_KeYltj~5xc1D
AZURE_TENANT_ID=b9b2d70c-e02a-4a03-9a14-426ede4914f2

# ============================================================================
# LARAVEL FRAMEWORK CONFIGURATION (Keep these for framework functionality)
# ============================================================================
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# ============================================================================
# CACHE & SESSION CONFIGURATION (Non-sensitive, can stay in .env)
# ============================================================================
BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# ============================================================================
# REDIS CONFIGURATION (If not using Azure Redis Cache)
# ============================================================================
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# ============================================================================
# MEMCACHED CONFIGURATION (If not using Azure Cache)
# ============================================================================
MEMCACHED_HOST=127.0.0.1

# ============================================================================
# AWS CONFIGURATION (If not using AWS services)
# ============================================================================
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# ============================================================================
# PUSHER CONFIGURATION (If not using Pusher)
# ============================================================================
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

# ============================================================================
# VITE CONFIGURATION (References other environment variables)
# ============================================================================
VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# ============================================================================
# APPLICATION THEME CONFIGURATION (UI/UX settings)
# ============================================================================
APP_CAPTION_SHOW='true'
APP_DARK_NAVBAR='false'
APP_PRESET_THEME="preset-1"
APP_DARK_LAYOUT='false'
APP_RTL_LAYOUT='false'
APP_BOX_CONTAINER='false'

# ============================================================================
# THIRD-PARTY API KEYS (Consider moving to Azure Key Vault)
# ============================================================================
FREECURRENCYAPI_KEY='fca_live_RHY03wHdEiYdoOqi9h7dUewwS7oDWA0VxTRaUeb2'
```

## Secrets Automatically Loaded from Azure Key Vault

**DO NOT ADD THESE TO YOUR .env FILE** - They will be automatically loaded:

| Azure Key Vault Secret | Environment Variable | Current Value |
|------------------------|---------------------|---------------|
| `APP-NAME` | `APP_NAME` | `Travclicks` |
| `APP-ENV` | `APP_ENV` | `production` |
| `APP-KEY` | `APP_KEY` | `base64:Tb7XcZnK34NLjyM8ILqL1P6jEWaUmI0KXpTwHMI4Xg4=` |
| `APP-DEBUG` | `APP_DEBUG` | `true` |
| `APP-URL` | `APP_URL` | `https://dev.travclicks.com/backadm-dmc` |
| `DB-CONNECTION` | `DB_CONNECTION` | `pgsql` |
| `DB-HOST` | `DB_HOST` | `173.247.245.74` |
| `DB-PORT` | `DB_PORT` | `5432` |
| `DB-DATABASE` | `DB_DATABASE` | `dmcdemo_nwdbdmc` |
| `DB-USERNAME` | `DB_USERNAME` | `dmcdemo_usnwdmc` |
| `DB-PASSWORD` | `DB_PASSWORD` | `A99FXCU1Mf43` |
| `MAIL-MAILER` | `MAIL_MAILER` | `smtp` |
| `MAIL-HOST` | `MAIL_HOST` | `smtp.hostinger.com` |
| `MAIL-PORT` | `MAIL_PORT` | `465` |
| `MAIL-USERNAME` | `MAIL_USERNAME` | `admin@travclicks.com` |
| `MAIL-PASSWORD` | `MAIL_PASSWORD` | `CoActive@@Trav123#456` |
| `MAIL-ENCRYPTION` | `MAIL_ENCRYPTION` | `SSL` |
| `MAIL-FROM-ADDRESS` | `MAIL_FROM_ADDRESS` | `admin@travclicks.com` |
| `MAIL-FROM-NAME` | `MAIL_FROM_NAME` | `Travclicks` |
| `STORAGE-NAME` | `AZURE_STORAGE_NAME` | `stgdmcappdev` |
| `STORAGE-KEY` | `AZURE_STORAGE_KEY` | `vUzd66ZV5V01BDgKGtgCw4jK/BvymAyW5D4fCQhz4vDNY6viawTnMPDb1R49ozsNcAqqdQKjF17O+AStdqojLA==` |
| `STORAGE-CONTAINER` | `AZURE_STORAGE_CONTAINER` | `uploads` |
| `STORAGE-ENDPOINT` | `AZURE_STORAGE_ENDPOINT` | `https://stgdmcappdev.blob.core.windows.net//` |
| `FILESYSTEM-DISK` | `FILESYSTEM_DISK` | `azure` |

## What You Need to Do

1. **Replace your current `.env` file** with the content above
2. **Ensure your Azure Key Vault** contains all the secrets listed in the table
3. **Test the integration** using: `php artisan azure:test-keyvault load`

## Environment Comparison

| Configuration | Local Development | Staging/Production |
|---------------|------------------|-------------------|
| Environment File | `.env.local` | `.env` |
| Azure Key Vault | Disabled | Enabled |
| Database | XAMPP MySQL | PostgreSQL |
| Storage | Local files | Azure Blob Storage |
| Mail | Mailtrap/Local | SMTP Production |
| App URL | `localhost/dev_dmc/public` | `https://dev.travclicks.com/backadm-dmc` |
``` 