<?php

namespace App\Services;

use GuzzleHttp\Client;

class AzureKeyVaultService
{
    protected $client;
    protected $token;
    protected $vaultBaseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->vaultBaseUrl = 'https://' . config('services.azure.vault') . '.vault.azure.net';
        $this->authenticate();
    }

    protected function authenticate()
    {
        $response = $this->client->post("https://login.microsoftonline.com/" . config('services.azure.tenant_id') . "/oauth2/v2.0/token", [
            'form_params' => [
                'client_id' => config('services.azure.client_id'),
                'client_secret' => config('services.azure.client_secret'),
                'grant_type' => 'client_credentials',
                'scope' => 'https://vault.azure.net/.default',
            ],
        ]);

        $body = json_decode($response->getBody(), true);
        $this->token = $body['access_token'] ?? null;
    }

    public function getSecret(string $name)
    {
        $url = $this->vaultBaseUrl . "/secrets/{$name}?api-version=7.4";

        $response = $this->client->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $body = json_decode($response->getBody(), true);
        return $body['value'] ?? null;
    }

    public function listSecrets()
    {
        $url = $this->vaultBaseUrl . "/secrets?api-version=7.4";

        $response = $this->client->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $body = json_decode($response->getBody(), true);
        $secrets = [];
        
        if (isset($body['value'])) {
            foreach ($body['value'] as $secret) {
                // Extract secret name from the ID
                $secretName = basename($secret['id']);
                $secrets[] = $secretName;
            }
        }
        
        return $secrets;
    }
}
