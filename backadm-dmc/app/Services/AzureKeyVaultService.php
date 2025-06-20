<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AzureKeyVaultService
{
    protected $client;
    protected $token;
    protected $vaultBaseUrl;
    protected $isEnabled;

    public function __construct()
    {
        $this->client = new Client();
        $this->isEnabled = env('USE_AZURE_KEYVAULT', false);
        
        if ($this->isEnabled) {
            $vaultName = config('services.azure.vault');
            if (empty($vaultName)) {
                Log::warning('Azure Key Vault name not configured');
                $this->isEnabled = false;
                return;
            }
            
            $this->vaultBaseUrl = 'https://' . $vaultName . '.vault.azure.net';
            $this->authenticate();
        }
    }

    protected function authenticate()
    {
        try {
            $tenantId = config('services.azure.tenant_id');
            $clientId = config('services.azure.client_id');
            $clientSecret = config('services.azure.client_secret');

            if (empty($tenantId) || empty($clientId) || empty($clientSecret)) {
                Log::error('Azure Key Vault authentication credentials not configured');
                $this->isEnabled = false;
                return;
            }

            $response = $this->client->post("https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token", [
                'form_params' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'client_credentials',
                    'scope' => 'https://vault.azure.net/.default',
                ],
                'timeout' => 30,
            ]);

            $body = json_decode($response->getBody(), true);
            $this->token = $body['access_token'] ?? null;

            if (!$this->token) {
                Log::error('Failed to get Azure Key Vault access token');
                $this->isEnabled = false;
            }
        } catch (\Exception $e) {
            Log::error('Azure Key Vault authentication failed: ' . $e->getMessage());
            $this->isEnabled = false;
        }
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function getSecret(string $name): ?string
    {
        if (!$this->isEnabled || !$this->token) {
            return null;
        }

        try {
            $url = $this->vaultBaseUrl . "/secrets/{$name}?api-version=7.4";

            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                'timeout' => 30,
            ]);

            $body = json_decode($response->getBody(), true);
            return $body['value'] ?? null;
        } catch (\Exception $e) {
            Log::error("Failed to get secret '{$name}' from Azure Key Vault: " . $e->getMessage());
            return null;
        }
    }

    public function getAllSecrets(): array
    {
        if (!$this->isEnabled || !$this->token) {
            return [];
        }

        $cacheKey = 'azure_keyvault_secrets';
        
        // Try to get from cache first (cache for 5 minutes)
        $secrets = Cache::get($cacheKey);
        if ($secrets !== null) {
            return $secrets;
        }

        try {
            $secrets = [];
            $url = $this->vaultBaseUrl . "/secrets?api-version=7.4";

            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                'timeout' => 30,
            ]);

            $body = json_decode($response->getBody(), true);
            
            if (isset($body['value'])) {
                foreach ($body['value'] as $secret) {
                    $secretName = basename($secret['id']);
                    $secretValue = $this->getSecret($secretName);
                    
                    if ($secretValue !== null) {
                        $secrets[$secretName] = $secretValue;
                    }
                }
            }

            // Cache the secrets for 5 minutes
            Cache::put($cacheKey, $secrets, 300);
            
            Log::info('Successfully loaded ' . count($secrets) . ' secrets from Azure Key Vault');
            return $secrets;
            
        } catch (\Exception $e) {
            Log::error('Failed to load secrets from Azure Key Vault: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Convert hyphenated Azure Key Vault secret names to Laravel underscore format
     * Example: APP-NAME becomes APP_NAME
     */
    public function convertSecretNameToEnv(string $azureSecretName): string
    {
        return str_replace('-', '_', $azureSecretName);
    }

    /**
     * Convert Laravel underscore environment variable names to Azure Key Vault format
     * Example: APP_NAME becomes APP-NAME
     */
    public function convertEnvNameToSecret(string $envName): string
    {
        return str_replace('_', '-', $envName);
    }

    /**
     * Load all secrets from Azure Key Vault and convert them to Laravel format
     */
    public function loadEnvironmentFromKeyVault(): array
    {
        if (!$this->isEnabled) {
            return [];
        }

        $azureSecrets = $this->getAllSecrets();
        $environmentVars = [];

        foreach ($azureSecrets as $secretName => $secretValue) {
            $envName = $this->convertSecretNameToEnv($secretName);
            $environmentVars[$envName] = $secretValue;
        }

        return $environmentVars;
    }

    public function listSecrets(): array
    {
        if (!$this->isEnabled || !$this->token) {
            return [];
        }

        try {
            $url = $this->vaultBaseUrl . "/secrets?api-version=7.4";

            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                'timeout' => 30,
            ]);

            $body = json_decode($response->getBody(), true);
            $secrets = [];
            
            if (isset($body['value'])) {
                foreach ($body['value'] as $secret) {
                    $secretName = basename($secret['id']);
                    $secrets[] = $secretName;
                }
            }
            
            return $secrets;
        } catch (\Exception $e) {
            Log::error('Failed to list secrets from Azure Key Vault: ' . $e->getMessage());
            return [];
        }
    }
}
