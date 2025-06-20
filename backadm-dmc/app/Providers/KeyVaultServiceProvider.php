<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AzureKeyVaultService;

class KeyVaultServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (env('USE_AZURE_KEYVAULT', false)) {
            $vault = new AzureKeyVaultService();

            $keys = [
                'API_URL',
                'APP_DEBUG',
                'APP_ENV',
                'APP_KEY',
                'APP_NAME',
                'APP_URL',
                'AWS_ACCESS_KEY_ID',
                'AWS_BUCKET',
                'AWS_DEFAULT_REGION',
                'AWS_ENDPOINT',
                'AWS_SECRET_ACCESS_KEY',
                'AWS_URL',
                'AZUR_STORAGE_CONTAINER',
                'AZUR_STORAGE_ENDPOINT',
                'AZUR_STORAGE_KEY',
                'AZUR_STORAGE_NAME',
                'DB_CONNECTION',
                'DB_DATABASE',
                'DB_HOST',
                'DB_PASSWORD',
                'DB_PORT',
                'DB_USERNAME',
                'FILESYSTEM_DISK',
                'MAIL_ENCRYPTION',
                'MAIL_FROM_ADDRESS',
                'MAIL_FROM_NAME',
                'MAIL_HOST',
                'MAIL_MAILER',
                'MAIL_PASSWORD',
                'MAIL_PORT',
                'MAIL_USERNAME',
            ];

            foreach ($keys as $key) {
                $value = $vault->getSecret($key);
                if ($value !== null) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                    config([$this->mapKeyToConfig($key) => $value]);
                }
            }
        }
    }

    private function mapKeyToConfig($key)
    {
        return match ($key) {
            'APP_KEY' => 'app.key',
            'APP_NAME' => 'app.name',
            'APP_ENV' => 'app.env',
            'APP_DEBUG' => 'app.debug',
            'APP_URL' => 'app.url',
            'DB_CONNECTION' => 'database.default',
            'DB_HOST' => 'database.connections.mysql.host',
            'DB_PORT' => 'database.connections.mysql.port',
            'DB_DATABASE' => 'database.connections.mysql.database',
            'DB_USERNAME' => 'database.connections.mysql.username',
            'DB_PASSWORD' => 'database.connections.mysql.password',
            'MAIL_MAILER' => 'mail.mailer',
            'MAIL_HOST' => 'mail.host',
            'MAIL_PORT' => 'mail.port',
            'MAIL_USERNAME' => 'mail.username',
            'MAIL_PASSWORD' => 'mail.password',
            'MAIL_ENCRYPTION' => 'mail.encryption',
            'MAIL_FROM_ADDRESS' => 'mail.from.address',
            'MAIL_FROM_NAME' => 'mail.from.name',
            'FILESYSTEM_DISK' => 'filesystems.default',
            'AWS_ACCESS_KEY_ID' => 'filesystems.disks.s3.key',
            'AWS_SECRET_ACCESS_KEY' => 'filesystems.disks.s3.secret',
            'AWS_BUCKET' => 'filesystems.disks.s3.bucket',
            'AWS_DEFAULT_REGION' => 'filesystems.disks.s3.region',
            'AWS_URL' => 'filesystems.disks.s3.url',
            'AWS_ENDPOINT' => 'filesystems.disks.s3.endpoint',
            'AZUR_STORAGE_KEY' => 'filesystems.disks.azure.key',
            'AZUR_STORAGE_NAME' => 'filesystems.disks.azure.name',
            'AZUR_STORAGE_CONTAINER' => 'filesystems.disks.azure.container',
            'AZUR_STORAGE_ENDPOINT' => 'filesystems.disks.azure.endpoint',
            default => '',
        };
    }
}
