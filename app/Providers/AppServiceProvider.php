<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use Illuminate\Database\Eloquent\Model;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        Storage::extend('azure', function($app, $config) {
            try {
                // Format the connection string properly
                $connectionString = sprintf(
                    'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;EndpointSuffix=core.windows.net',
                    $config['name'],
                    $config['key']
                );

                $blobClient = BlobRestProxy::createBlobService($connectionString);
                
                // Use default container from config, but allow dynamic override
                $defaultContainer = $config['container'] ?? 'uploads';
                
                return new Filesystem(
                    new AzureBlobStorageAdapter(
                        $blobClient,
                        $defaultContainer
                    )
                );
            } catch (\Exception $e) {
                Log::error('Azure storage configuration error:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }
}
