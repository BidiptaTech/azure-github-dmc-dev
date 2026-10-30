<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountryCurrencySeeder extends Seeder
{
    /**
     * ISO 4217 currency codes keyed by country name (as stored in countries.name).
     */
    private function countryCurrencyMap(): array
    {
        return [
            'Afghanistan' => 'AFN',
            'Albania' => 'ALL',
            'Algeria' => 'DZD',
            'Andorra' => 'EUR',
            'Angola' => 'AOA',
            'Argentina' => 'ARS',
            'Armenia' => 'AMD',
            'Australia' => 'AUD',
            'Austria' => 'EUR',
            'Azerbaijan' => 'AZN',
            'Bahamas' => 'BSD',
            'Bahrain' => 'BHD',
            'Bangladesh' => 'BDT',
            'Barbados' => 'BBD',
            'Belarus' => 'BYN',
            'Belgium' => 'EUR',
            'Belize' => 'BZD',
            'Benin' => 'XOF',
            'Bhutan' => 'BTN',
            'Bolivia' => 'BOB',
            'Bosnia and Herzegovina' => 'BAM',
            'Botswana' => 'BWP',
            'Brazil' => 'BRL',
            'Brunei' => 'BND',
            'Bulgaria' => 'BGN',
            'Burkina Faso' => 'XOF',
            'Burundi' => 'BIF',
            'Cambodia' => 'KHR',
            'Cameroon' => 'XAF',
            'United States' => 'USD',
            'Cape Verde' => 'CVE',
            'Central African Republic' => 'XAF',
            'Chad' => 'XAF',
            'Chile' => 'CLP',
            'China' => 'CNY',
            'Colombia' => 'COP',
            'Costa Rica' => 'CRC',
            'Croatia' => 'EUR',
            'Cyprus' => 'EUR',
            'Czech Republic' => 'CZK',
            'Denmark' => 'DKK',
            'Dominican Republic' => 'DOP',
            'Ecuador' => 'USD',
            'Egypt' => 'EGP',
            'El Salvador' => 'USD',
            'Estonia' => 'EUR',
            'Eswatini' => 'SZL',
            'Ethiopia' => 'ETB',
            'Fiji' => 'FJD',
            'Finland' => 'EUR',
            'France' => 'EUR',
            'Germany' => 'EUR',
            'Ghana' => 'GHS',
            'Greece' => 'EUR',
            'Guatemala' => 'GTQ',
            'Haiti' => 'HTG',
            'Honduras' => 'HNL',
            'Hungary' => 'HUF',
            'Iceland' => 'ISK',
            'India' => 'INR',
            'Indonesia' => 'IDR',
            'Iran' => 'IRR',
            'Iraq' => 'IQD',
            'Ireland' => 'EUR',
            'Israel' => 'ILS',
            'Italy' => 'EUR',
            'Jamaica' => 'JMD',
            'Japan' => 'JPY',
            'Jordan' => 'JOD',
            'Russia' => 'RUB',
            'Kenya' => 'KES',
            'Kuwait' => 'KWD',
            'Kyrgyzstan' => 'KGS',
            'Laos' => 'LAK',
            'Latvia' => 'EUR',
            'Lebanon' => 'LBP',
            'Liberia' => 'LRD',
            'Libya' => 'LYD',
            'Lithuania' => 'EUR',
            'Luxembourg' => 'EUR',
            'Malaysia' => 'MYR',
            'Malta' => 'EUR',
            'Mexico' => 'MXN',
            'Moldova' => 'MDL',
            'Monaco' => 'EUR',
            'Morocco' => 'MAD',
            'Nepal' => 'NPR',
            'Netherlands' => 'EUR',
            'New Zealand' => 'NZD',
            'Nicaragua' => 'NIO',
            'Nigeria' => 'NGN',
            'Norway' => 'NOK',
            'Pakistan' => 'PKR',
            'Peru' => 'PEN',
            'Philippines' => 'PHP',
            'Poland' => 'PLN',
            'Portugal' => 'EUR',
            'Qatar' => 'QAR',
            'Romania' => 'RON',
            'Saudi Arabia' => 'SAR',
            'Singapore' => 'SGD',
            'South Africa' => 'ZAR',
            'South Korea' => 'KRW',
            'Spain' => 'EUR',
            'Sri Lanka' => 'LKR',
            'Sweden' => 'SEK',
            'Switzerland' => 'CHF',
            'Taiwan' => 'TWD',
            'Thailand' => 'THB',
            'Turkey' => 'TRY',
            'Ukraine' => 'UAH',
            'United Arab Emirates' => 'AED',
            'United Kingdom' => 'GBP',
            'Uruguay' => 'UYU',
            'Venezuela' => 'VES',
            'Vietnam' => 'VND',
            'Maldives' => 'MVR',
        ];
    }

    public function run(): void
    {
        $updated = 0;
        $missing = [];

        foreach ($this->countryCurrencyMap() as $countryName => $currencyCode) {
            $count = Country::where('name', $countryName)->update(['currency' => $currencyCode]);

            if ($count > 0) {
                $updated += $count;
                continue;
            }

            $missing[] = $countryName;
        }

        $this->command?->info("Updated currency for {$updated} country row(s).");

        if (!empty($missing)) {
            $this->command?->warn('No matching countries table row for: ' . implode(', ', $missing));
        }
    }
}
