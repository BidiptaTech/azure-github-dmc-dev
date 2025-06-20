<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AzureKeyVaultService;
use Illuminate\Support\Facades\Log;

class TestAzureKeyVault extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'azure:test-keyvault {action=list} {secret?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Azure Key Vault integration (actions: list, get, load)';

    protected $keyVaultService;

    public function __construct(AzureKeyVaultService $keyVaultService)
    {
        parent::__construct();
        $this->keyVaultService = $keyVaultService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $secretName = $this->argument('secret');

        $this->info('Testing Azure Key Vault Integration');
        $this->info('=====================================');

        // Check if Azure Key Vault is enabled
        if (!$this->keyVaultService->isEnabled()) {
            $this->error('Azure Key Vault is not enabled or could not be initialized.');
            $this->info('Please check your configuration:');
            $this->info('- USE_AZURE_KEYVAULT=true');
            $this->info('- AZURE_KEYVAULT_NAME=' . config('services.azure.vault'));
            $this->info('- AZURE_CLIENT_ID=' . config('services.azure.client_id'));
            $this->info('- AZURE_TENANT_ID=' . config('services.azure.tenant_id'));
            return self::FAILURE;
        }

        $this->info('✓ Azure Key Vault is enabled and authenticated');
        $this->info('Vault: ' . config('services.azure.vault'));
        $this->newLine();

        switch ($action) {
            case 'list':
                return $this->listSecrets();
            case 'get':
                return $this->getSecret($secretName);
            case 'load':
                return $this->loadAllSecrets();
            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: list, get, load');
                return self::FAILURE;
        }
    }

    protected function listSecrets()
    {
        $this->info('Listing all secrets from Azure Key Vault...');
        
        $secrets = $this->keyVaultService->listSecrets();
        
        if (empty($secrets)) {
            $this->warn('No secrets found in the Key Vault');
            return self::SUCCESS;
        }

        $this->info('Found ' . count($secrets) . ' secrets:');
        $this->newLine();

        $this->table(['Azure Secret Name', 'Laravel Env Name'], array_map(function ($secret) {
            return [$secret, $this->keyVaultService->convertSecretNameToEnv($secret)];
        }, $secrets));

        return self::SUCCESS;
    }

    protected function getSecret($secretName)
    {
        if (!$secretName) {
            $this->error('Please provide a secret name: azure:test-keyvault get SECRET-NAME');
            return self::FAILURE;
        }

        $this->info("Getting secret: {$secretName}");
        
        $value = $this->keyVaultService->getSecret($secretName);
        
        if ($value === null) {
            $this->error("Secret '{$secretName}' not found or could not be retrieved");
            return self::FAILURE;
        }

        $envName = $this->keyVaultService->convertSecretNameToEnv($secretName);
        
        $this->info("✓ Secret retrieved successfully");
        $this->info("Azure Name: {$secretName}");
        $this->info("Laravel Env: {$envName}");
        $this->info("Value: " . (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value));

        return self::SUCCESS;
    }

    protected function loadAllSecrets()
    {
        $this->info('Loading all secrets from Azure Key Vault...');
        
        $secrets = $this->keyVaultService->loadEnvironmentFromKeyVault();
        
        if (empty($secrets)) {
            $this->warn('No secrets loaded from Key Vault');
            return self::SUCCESS;
        }

        $this->info('✓ Loaded ' . count($secrets) . ' secrets successfully');
        $this->newLine();

        $tableData = [];
        foreach ($secrets as $envName => $value) {
            $azureName = $this->keyVaultService->convertEnvNameToSecret($envName);
            $displayValue = strlen($value) > 30 ? substr($value, 0, 30) . '...' : $value;
            $tableData[] = [$azureName, $envName, $displayValue];
        }

        $this->table(['Azure Secret', 'Laravel Env', 'Value (truncated)'], $tableData);

        return self::SUCCESS;
    }
} 