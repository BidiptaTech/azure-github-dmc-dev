# Azure Key Vault Integration

This Laravel application includes comprehensive Azure Key Vault integration to securely manage application secrets and configuration values.

## Overview

The integration automatically loads secrets from Azure Key Vault and makes them available as environment variables in your Laravel application. It converts between Azure Key Vault's hyphenated naming convention (e.g., `APP-NAME`) and Laravel's underscore convention (e.g., `APP_NAME`).

## Features

- **Automatic Secret Loading**: Secrets are loaded at application startup
- **Name Convention Conversion**: Converts between Azure hyphenated names and Laravel underscore names
- **Caching**: Secrets are cached for 5 minutes to improve performance
- **Fallback Support**: Falls back to `.env` file if Key Vault is unavailable
- **Error Handling**: Comprehensive error handling and logging
- **Testing Commands**: Artisan commands for testing and debugging

## Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
# Enable Azure Key Vault (set to true for production)
USE_AZURE_KEYVAULT=true

# Azure Key Vault Configuration
AZURE_KEYVAULT_NAME=dmcKeyVault-dev
AZURE_CLIENT_ID=your-client-id
AZURE_CLIENT_SECRET=your-client-secret
AZURE_TENANT_ID=your-tenant-id
```

### Azure Key Vault Setup

Your Azure Key Vault should contain secrets with hyphenated names:

- `APP-NAME` → becomes `APP_NAME` in Laravel
- `DB-HOST` → becomes `DB_HOST` in Laravel
- `DB-PASSWORD` → becomes `DB_PASSWORD` in Laravel
- etc.

### Required Azure Permissions

The service principal needs the following permissions on the Key Vault:
- `Get` (to retrieve individual secrets)
- `List` (to list all secrets)

## Secret Name Mapping

| Azure Key Vault Secret | Laravel Environment Variable |
|------------------------|------------------------------|
| `APP-NAME` | `APP_NAME` |
| `APP-ENV` | `APP_ENV` |
| `APP-DEBUG` | `APP_DEBUG` |
| `APP-URL` | `APP_URL` |
| `DB-CONNECTION` | `DB_CONNECTION` |
| `DB-HOST` | `DB_HOST` |
| `DB-PORT` | `DB_PORT` |
| `DB-DATABASE` | `DB_DATABASE` |
| `DB-USERNAME` | `DB_USERNAME` |
| `DB-PASSWORD` | `DB_PASSWORD` |
| `MAIL-MAILER` | `MAIL_MAILER` |
| `MAIL-HOST` | `MAIL_HOST` |
| `MAIL-PORT` | `MAIL_PORT` |
| `MAIL-USERNAME` | `MAIL_USERNAME` |
| `MAIL-PASSWORD` | `MAIL_PASSWORD` |
| `MAIL-ENCRYPTION` | `MAIL_ENCRYPTION` |
| `MAIL-FROM-ADDRESS` | `MAIL_FROM_ADDRESS` |
| `MAIL-FROM-NAME` | `MAIL_FROM_NAME` |
| `STORAGE-NAME` | `STORAGE_NAME` |
| `STORAGE-KEY` | `STORAGE_KEY` |
| `STORAGE-CONTAINER` | `STORAGE_CONTAINER` |
| `STORAGE-ENDPOINT` | `STORAGE_ENDPOINT` |
| `FILESYSTEM-DISK` | `FILESYSTEM_DISK` |

## Testing Commands

Use these Artisan commands to test and debug the Azure Key Vault integration:

### List All Secrets
```bash
php artisan azure:test-keyvault list
```

### Get a Specific Secret
```bash
php artisan azure:test-keyvault get APP-NAME
```

### Load All Secrets (Test Environment Loading)
```bash
php artisan azure:test-keyvault load
```

## How It Works

1. **Application Startup**: The `AzureKeyVaultServiceProvider` is registered early in the bootstrap process
2. **Secret Loading**: If `USE_AZURE_KEYVAULT=true`, the provider connects to Azure Key Vault
3. **Authentication**: Uses client credentials flow with the provided Azure service principal
4. **Secret Retrieval**: Fetches all secrets from the Key Vault
5. **Name Conversion**: Converts hyphenated names to underscore format
6. **Environment Setting**: Sets secrets as environment variables
7. **Caching**: Caches secrets for 5 minutes to improve performance

## Error Handling

The integration includes comprehensive error handling:

- **Authentication Failures**: Logged with details about missing credentials
- **Network Issues**: Timeouts and connection errors are handled gracefully
- **Missing Secrets**: Individual secret retrieval failures don't break the application
- **Fallback**: Always falls back to `.env` file values if Key Vault is unavailable

## Development vs Production

### Development (Local)
- Set `USE_AZURE_KEYVAULT=false` or omit it
- Use local `.env` file for all configuration
- Azure Key Vault is bypassed completely

### Production (Azure)
- Set `USE_AZURE_KEYVAULT=true`
- Minimal `.env` file with only Azure Key Vault connection details
- All other secrets loaded from Azure Key Vault

## Environment Variables Priority

1. **Local .env file**: Always takes priority (allows local overrides)
2. **Azure Key Vault**: Used if not defined in .env
3. **System Environment**: Laravel's standard fallback

This means you can override any Key Vault secret locally by defining it in your `.env` file.

## Logging

The integration logs important events:

- **Info**: Successful secret loading, number of secrets loaded
- **Warning**: Key Vault disabled, empty secret lists, authentication issues
- **Error**: Authentication failures, network errors, configuration problems

Check `storage/logs/laravel.log` for integration status and troubleshooting information.

## Security Considerations

1. **Service Principal**: Use a dedicated service principal with minimal required permissions
2. **Secret Rotation**: Regularly rotate the client secret used for authentication
3. **Network Security**: Consider using Azure Private Endpoints for Key Vault access
4. **Audit Logging**: Enable Key Vault audit logging to track secret access
5. **Environment Isolation**: Use separate Key Vaults for different environments

## Troubleshooting

### Common Issues

**1. Authentication Failed**
- Verify client credentials are correct
- Check service principal has proper permissions
- Ensure tenant ID is correct

**2. No Secrets Loaded**
- Verify Key Vault name is correct
- Check secrets exist in the Key Vault
- Verify secrets are enabled (not disabled)

**3. Specific Secret Not Found**
- Check secret name spelling (case sensitive)
- Verify secret exists and is enabled
- Check secret hasn't expired

**4. Performance Issues**
- Caching is enabled by default (5 minutes)
- Consider increasing cache time if needed
- Monitor Key Vault throttling limits

### Debug Commands

```bash
# Test connection and list secrets
php artisan azure:test-keyvault list

# Test specific secret retrieval
php artisan azure:test-keyvault get APP-NAME

# Test full environment loading
php artisan azure:test-keyvault load

# Clear application cache
php artisan cache:clear
```

## Architecture

```
Laravel Application
├── AzureKeyVaultServiceProvider (Early Registration)
│   ├── Checks USE_AZURE_KEYVAULT flag
│   ├── Initializes AzureKeyVaultService
│   └── Loads secrets as environment variables
├── AzureKeyVaultService
│   ├── Authenticates with Azure
│   ├── Retrieves secrets from Key Vault
│   ├── Converts naming conventions
│   └── Caches results
└── TestAzureKeyVault Command
    ├── Lists available secrets
    ├── Tests individual secret retrieval
    └── Validates full integration
```

## Migration Guide

If you're migrating from `.env` file to Azure Key Vault:

1. **Create secrets in Azure Key Vault** with hyphenated names
2. **Set `USE_AZURE_KEYVAULT=true`** in your production environment
3. **Keep local .env file** for development with `USE_AZURE_KEYVAULT=false`
4. **Test the integration** using the provided commands
5. **Deploy and monitor** logs for any issues

The integration is designed to be backward compatible and won't break existing functionality. 