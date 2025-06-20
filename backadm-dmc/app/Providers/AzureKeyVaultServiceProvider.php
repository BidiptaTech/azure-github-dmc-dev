<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AzureKeyVaultService;
use Illuminate\Support\Facades\Log;

class AzureKeyVaultServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AzureKeyVaultService::class, function ($app) {
            return new AzureKeyVaultService();
        });

        // Load secrets from Azure Key Vault early in the application lifecycle
        $this->loadSecretsFromKeyVault();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Load secrets from Azure Key Vault and set them as environment variables
     */
    protected function loadSecretsFromKeyVault(): void
    {
        try {
            // Check if Azure Key Vault is enabled
            if (!env('USE_AZURE_KEYVAULT', false)) {
                Log::info('Azure Key Vault is disabled, using .env file only');
                return;
            }

            $keyVaultService = new AzureKeyVaultService();
            
            if (!$keyVaultService->isEnabled()) {
                Log::warning('Azure Key Vault could not be initialized, falling back to .env file');
                return;
            }

            $secrets = $keyVaultService->loadEnvironmentFromKeyVault();
            
            if (empty($secrets)) {
                Log::warning('No secrets loaded from Azure Key Vault');
                return;
            }

            // Set each secret as an environment variable
            foreach ($secrets as $envName => $value) {
                // Only set if not already defined in .env file (allow .env to override for local development)
                if (env($envName) === null) {
                    putenv("{$envName}={$value}");
                    $_ENV[$envName] = $value;
                    $_SERVER[$envName] = $value;
                }
            }

            Log::info('Successfully loaded ' . count($secrets) . ' secrets from Azure Key Vault');

        } catch (\Exception $e) {
            Log::error('Failed to load secrets from Azure Key Vault: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [AzureKeyVaultService::class];
    }
} 