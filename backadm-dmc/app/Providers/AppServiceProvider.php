<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use Illuminate\Database\Eloquent\Model;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

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
            
            // Force HTTPS URLs everywhere
            if (config("app.env") === "production" || isset($_SERVER["HTTPS"]) || request()->header("x-forwarded-proto") == "https") {
                URL::forceScheme("https");
                URL::forceRootUrl(config("app.url"));
            }
            
            // Set asset URL for subdirectory
            if (config("app.asset_url")) {
                URL::asset("/");
            }
            
            // Force HTTPS for all requests
            if (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] === "https") {
                request()->server->set("HTTPS", "on");
                request()->server->set("SERVER_PORT", 443);
            }
    }
}
