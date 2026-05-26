<?php

namespace App\Helpers;

use App\Services\CurrencyService;
use Illuminate\Support\Facades\Log;

class CurrencyHelper
{
    /**
     * Map of full currency names (lowercase) to ISO 4217 codes.
     * Used when Country.currency is stored as full name (e.g. "Vietnamese Dong") instead of code (e.g. "VND").
     */
    protected static $currencyNameToCode = [
        'singapore dollar' => 'SGD',
        'us dollar' => 'USD',
        'euro' => 'EUR',
        'british pound' => 'GBP',
        'indian rupee' => 'INR',
        'australian dollar' => 'AUD',
        'new zealand dollar' => 'NZD',
        'canadian dollar' => 'CAD',
        'swiss franc' => 'CHF',
        'japanese yen' => 'JPY',
        'chinese yuan' => 'CNY',
        'hong kong dollar' => 'HKD',
        'new taiwan dollar' => 'TWD',
        'taiwan dollar' => 'TWD',
        'south korean won' => 'KRW',
        'thai baht' => 'THB',
        'malaysian ringgit' => 'MYR',
        'indonesian rupiah' => 'IDR',
        'philippine peso' => 'PHP',
        'vietnamese dong' => 'VND',
        'uae dirham' => 'AED',
        'saudi riyal' => 'SAR',
        'qatari riyal' => 'QAR',
        'kuwaiti dinar' => 'KWD',
        'bahraini dinar' => 'BHD',
        'omani rial' => 'OMR',
        'south african rand' => 'ZAR',
        'nigerian naira' => 'NGN',
        'egyptian pound' => 'EGP',
        'kenyan shilling' => 'KES',
        'ghanaian cedi' => 'GHS',
        'moroccan dirham' => 'MAD',
        'brazilian real' => 'BRL',
        'argentine peso' => 'ARS',
        'chilean peso' => 'CLP',
        'colombian peso' => 'COP',
        'peruvian sol' => 'PEN',
        'mexican peso' => 'MXN',
        'russian ruble' => 'RUB',
        'ukrainian hryvnia' => 'UAH',
        'turkish lira' => 'TRY',
        'israeli new shekel' => 'ILS',
        'polish zloty' => 'PLN',
        'czech koruna' => 'CZK',
        'hungarian forint' => 'HUF',
        'romanian leu' => 'RON',
        'swedish krona' => 'SEK',
        'norwegian krone' => 'NOK',
        'danish krone' => 'DKK',
        'icelandic krona' => 'ISK',
        'bulgarian lev' => 'BGN',
        'croatian kuna' => 'HRK',
        'pakistani rupee' => 'PKR',
        'sri lankan rupee' => 'LKR',
        'bangladeshi taka' => 'BDT',
        'maldivian rufiyaa' => 'MVR',
        'kazakhstani tenge' => 'KZT',
        'dominican peso' => 'DOP',
        'jamaican dollar' => 'JMD',
        'afghan afghani' => 'AFN',
    ];

    /**
     * Normalize a currency value from the database to an ISO 4217 code.
     * Handles both short codes (e.g. AFN, VND) and full names (e.g. Vietnamese Dong).
     *
     * @param string|null $currencyRaw   Value from Country.currency (code or full name)
     * @param array       $allowedCodes  List of valid 3-letter codes to accept
     * @param string      $fallback      Code to return when unknown or not in allowed list
     * @return string 3-letter currency code
     */
    public static function normalizeCurrencyToCode(?string $currencyRaw, array $allowedCodes, string $fallback = 'SGD'): string
    {
        $raw = trim((string) $currencyRaw);
        if ($raw === '') {
            return $fallback;
        }

        $upper = strtoupper($raw);

        // Already a 3-letter code
        if (strlen($upper) === 3 && in_array($upper, $allowedCodes, true)) {
            return $upper;
        }

        // Look up full name (case-insensitive)
        $key = mb_strtolower($raw);
        if (isset(self::$currencyNameToCode[$key])) {
            $code = self::$currencyNameToCode[$key];
            return in_array($code, $allowedCodes, true) ? $code : $fallback;
        }

        return $fallback;
    }

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

