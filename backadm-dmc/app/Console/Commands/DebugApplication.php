<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AzureKeyVaultService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DebugApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug {--component=all : Component to debug (all, env, db, azure, storage)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug application components to identify 500 errors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $component = $this->option('component');

        $this->info('🔍 Application Debug Report');
        $this->info('==========================');

        if ($component === 'all' || $component === 'env') {
            $this->debugEnvironment();
        }

        if ($component === 'all' || $component === 'azure') {
            $this->debugAzureKeyVault();
        }

        if ($component === 'all' || $component === 'db') {
            $this->debugDatabase();
        }

        if ($component === 'all' || $component === 'storage') {
            $this->debugStorage();
        }

        $this->newLine();
        $this->info('✅ Debug report completed. Check storage/logs/laravel.log for detailed error logs.');

        return self::SUCCESS;
    }

    protected function debugEnvironment()
    {
        $this->newLine();
        $this->info('🌍 Environment Configuration');
        $this->info('----------------------------');

        $this->table(['Setting', 'Value'], [
            ['APP_ENV', config('app.env')],
            ['APP_DEBUG', config('app.debug') ? 'true' : 'false'],
            ['APP_URL', config('app.url')],
            ['LOG_LEVEL', config('logging.level', 'Not set')],
            ['USE_AZURE_KEYVAULT', env('USE_AZURE_KEYVAULT') ? 'true' : 'false'],
        ]);

        // Check for required environment variables
        $required = ['APP_KEY', 'APP_URL'];
        $missing = [];

        foreach ($required as $var) {
            if (empty(env($var))) {
                $missing[] = $var;
            }
        }

        if (!empty($missing)) {
            $this->error('❌ Missing required environment variables: ' . implode(', ', $missing));
        } else {
            $this->info('✅ All required environment variables are set');
        }
    }

    protected function debugAzureKeyVault()
    {
        $this->newLine();
        $this->info('🔐 Azure Key Vault Status');
        $this->info('-------------------------');

        try {
            if (!env('USE_AZURE_KEYVAULT', false)) {
                $this->warn('⚠️  Azure Key Vault is disabled');
                return;
            }

            $keyVaultService = app(AzureKeyVaultService::class);
            
            if (!$keyVaultService->isEnabled()) {
                $this->error('❌ Azure Key Vault is not enabled or failed to initialize');
                $this->info('Check the following:');
                $this->info('- AZURE_KEYVAULT_NAME: ' . (config('services.azure.vault') ?: 'Not set'));
                $this->info('- AZURE_CLIENT_ID: ' . (config('services.azure.client_id') ? 'Set' : 'Not set'));
                $this->info('- AZURE_CLIENT_SECRET: ' . (config('services.azure.client_secret') ? 'Set' : 'Not set'));
                $this->info('- AZURE_TENANT_ID: ' . (config('services.azure.tenant_id') ?: 'Not set'));
                return;
            }

            $this->info('✅ Azure Key Vault is enabled and authenticated');
            
            // Test secret loading
            $secrets = $keyVaultService->loadEnvironmentFromKeyVault();
            $this->info("📊 Loaded {count($secrets)} secrets from Azure Key Vault");

            if (count($secrets) > 0) {
                $this->table(['Secret Name (Azure)', 'Environment Variable', 'Status'], 
                    collect($secrets)->take(5)->map(function ($value, $envName) use ($keyVaultService) {
                        $azureName = $keyVaultService->convertEnvNameToSecret($envName);
                        return [
                            $azureName,
                            $envName,
                            !empty($value) ? '✅ Loaded' : '❌ Empty'
                        ];
                    })->toArray()
                );
            }

        } catch (\Exception $e) {
            $this->error('❌ Azure Key Vault Error: ' . $e->getMessage());
            Log::error('Azure Key Vault Debug Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function debugDatabase()
    {
        $this->newLine();
        $this->info('🗄️  Database Connection');
        $this->info('----------------------');

        try {
            $connection = config('database.default');
            $this->info("Default Connection: {$connection}");

            $dbConfig = config("database.connections.{$connection}");
            $this->table(['Setting', 'Value'], [
                ['Driver', $dbConfig['driver'] ?? 'Not set'],
                ['Host', $dbConfig['host'] ?? 'Not set'],
                ['Port', $dbConfig['port'] ?? 'Not set'],
                ['Database', $dbConfig['database'] ?? 'Not set'],
                ['Username', $dbConfig['username'] ?? 'Not set'],
                ['Password', !empty($dbConfig['password']) ? 'Set' : 'Not set'],
            ]);

            // Test database connection
            DB::connection()->getPdo();
            $this->info('✅ Database connection successful');

            // Test a simple query
            $result = DB::select('SELECT 1 as test');
            if ($result) {
                $this->info('✅ Database query test successful');
            }

        } catch (\Exception $e) {
            $this->error('❌ Database Error: ' . $e->getMessage());
            Log::error('Database Debug Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function debugStorage()
    {
        $this->newLine();
        $this->info('💾 Storage Configuration');
        $this->info('-----------------------');

        try {
            $defaultDisk = config('filesystems.default');
            $this->info("Default Disk: {$defaultDisk}");

            // Test local storage
            try {
                Storage::disk('local')->put('test.txt', 'test content');
                Storage::disk('local')->delete('test.txt');
                $this->info('✅ Local storage working');
            } catch (\Exception $e) {
                $this->error('❌ Local storage error: ' . $e->getMessage());
            }

            // Test Azure storage if configured
            if ($defaultDisk === 'azure' || config('filesystems.disks.azure')) {
                try {
                    $azureConfig = config('filesystems.disks.azure');
                    $this->table(['Azure Setting', 'Status'], [
                        ['Driver', $azureConfig['driver'] ?? 'Not set'],
                        ['Name', !empty($azureConfig['name']) ? 'Set' : 'Not set'],
                        ['Key', !empty($azureConfig['key']) ? 'Set' : 'Not set'],
                        ['Container', $azureConfig['container'] ?? 'Not set'],
                        ['Endpoint', $azureConfig['url'] ?? 'Not set'],
                    ]);

                    // Test Azure connection
                    Storage::disk('azure')->put('debug-test.txt', 'debug test');
                    Storage::disk('azure')->delete('debug-test.txt');
                    $this->info('✅ Azure storage working');

                } catch (\Exception $e) {
                    $this->error('❌ Azure storage error: ' . $e->getMessage());
                    Log::error('Azure Storage Debug Error', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ Storage Error: ' . $e->getMessage());
            Log::error('Storage Debug Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 