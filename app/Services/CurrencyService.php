<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CurrencyService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.freecurrencyapi.key');
    }

    public function getExchangeRate($baseCurrency = 'USD', $targetCurrency = 'EUR')
    {
        $url = "https://api.freecurrencyapi.com/v1/latest?apikey={$this->apiKey}&base_currency={$baseCurrency}";

        // Make the HTTP request
        $response = Http::get($url);

        if ($response->successful() && isset($response->json()['data'][$targetCurrency])) {
            return $response->json()['data'][$targetCurrency];
        }

        return null;
    }
}
