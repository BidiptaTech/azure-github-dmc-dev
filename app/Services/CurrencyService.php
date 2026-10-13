<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.freecurrencyapi.key');
    }

    /**
     * Fetch latest rates for a base currency (cached).
     *
     * @return array<string, float>|null  Target currency code => units per 1 base unit
     */
    public function getLatestRates(string $baseCurrency = 'USD'): ?array
    {
        $baseCurrency = strtoupper(trim($baseCurrency));
        if ($baseCurrency === '') {
            return null;
        }

        $cacheKey = 'currency_rates_' . $baseCurrency;

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($baseCurrency) {
            $rates = $this->fetchFreeCurrencyApiRates($baseCurrency);
            if (is_array($rates) && $rates !== []) {
                return $rates;
            }

            return $this->fetchOpenExchangeRates($baseCurrency);
        });
    }

    /**
     * Exchange rate: how many target units per 1 base unit.
     */
    public function getExchangeRate($baseCurrency = 'USD', $targetCurrency = 'EUR'): ?float
    {
        $baseCurrency = strtoupper(trim((string) $baseCurrency));
        $targetCurrency = strtoupper(trim((string) $targetCurrency));

        if ($baseCurrency === '' || $targetCurrency === '') {
            return null;
        }

        if ($baseCurrency === $targetCurrency) {
            return 1.0;
        }

        $directRates = $this->getLatestRates($baseCurrency);
        if (is_array($directRates) && isset($directRates[$targetCurrency])) {
            $rate = (float) $directRates[$targetCurrency];

            return $rate > 0 ? $rate : null;
        }

        $usdRates = $this->getLatestRates('USD');
        if (! is_array($usdRates)) {
            return null;
        }

        if ($baseCurrency === 'USD') {
            if (! isset($usdRates[$targetCurrency])) {
                return null;
            }
            $rate = (float) $usdRates[$targetCurrency];

            return $rate > 0 ? $rate : null;
        }

        if ($targetCurrency === 'USD') {
            if (! isset($usdRates[$baseCurrency]) || (float) $usdRates[$baseCurrency] <= 0) {
                return null;
            }

            return 1.0 / (float) $usdRates[$baseCurrency];
        }

        if (! isset($usdRates[$baseCurrency], $usdRates[$targetCurrency])) {
            return null;
        }

        $basePerUsd = (float) $usdRates[$baseCurrency];
        $targetPerUsd = (float) $usdRates[$targetCurrency];

        if ($basePerUsd <= 0 || $targetPerUsd <= 0) {
            return null;
        }

        return $targetPerUsd / $basePerUsd;
    }

    /**
     * @return array<string, float>|null
     */
    protected function fetchFreeCurrencyApiRates(string $baseCurrency): ?array
    {
        if (empty($this->apiKey)) {
            return null;
        }

        $url = 'https://api.freecurrencyapi.com/v1/latest?apikey=' . urlencode($this->apiKey)
            . '&base_currency=' . urlencode($baseCurrency);

        try {
            $response = Http::timeout(10)->get($url);

            if ($response->successful() && is_array($response->json('data'))) {
                return $response->json('data');
            }
        } catch (\Throwable $e) {
            Log::warning('freecurrencyapi request failed', [
                'base' => $baseCurrency,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Fallback provider — supports VND and 160+ currencies, no API key required.
     *
     * @return array<string, float>|null
     */
    protected function fetchOpenExchangeRates(string $baseCurrency): ?array
    {
        $url = 'https://open.er-api.com/v6/latest/' . urlencode($baseCurrency);

        try {
            $response = Http::timeout(10)->get($url);
            $json = $response->json();

            if (
                $response->successful()
                && ($json['result'] ?? '') === 'success'
                && is_array($json['rates'] ?? null)
            ) {
                return $json['rates'];
            }

            Log::warning('open.er-api response invalid', [
                'base' => $baseCurrency,
                'status' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('open.er-api request failed', [
                'base' => $baseCurrency,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }
}
