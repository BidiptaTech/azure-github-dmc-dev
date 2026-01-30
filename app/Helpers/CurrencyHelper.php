<?php

namespace App\Helpers;

use App\Services\CurrencyService;
use Illuminate\Support\Facades\Log;

class CurrencyHelper
{
    /**
     * Get exchange rate between two currencies.
     *
     * @param string $baseCurrency   The currency you are converting FROM (e.g. 'USD')
     * @param string $targetCurrency The currency you are converting TO (e.g. 'EUR')
     * @return float|null            The exchange rate, or null on failure
     */
    public static function getExchangeRate(string $baseCurrency, string $targetCurrency): ?float
    {
        try {
            $service = new CurrencyService();

            return $service->getExchangeRate(strtoupper($baseCurrency), strtoupper($targetCurrency));
        } catch (\Throwable $e) {
            Log::warning('Failed to fetch exchange rate', [
                'base' => $baseCurrency,
                'target' => $targetCurrency,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Convert an amount from one currency to another.
     *
     * @param float|int $amount          The amount to convert
     * @param string    $currentCurrency The currency the amount is currently in
     * @param string    $targetCurrency  The currency to convert the amount to
     * @return float|null                Converted amount, or null if rate not available
     */
    public static function convertAmount($amount, string $currentCurrency, string $targetCurrency): ?float
    {
        $rate = self::getExchangeRate($currentCurrency, $targetCurrency);

        if ($rate === null) {
            return null;
        }

        return (float) $amount * (float) $rate;
    }
}

