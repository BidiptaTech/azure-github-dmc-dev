<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountryShortCodeSeeder extends Seeder
{
    /**
     * ISO 3166-1 alpha-2 codes keyed by country name (as stored in countries.name).
     *
     * Keyed by name rather than dial code because dial codes are not unique
     * (Russia and Kazakhstan share 7, the US and Canada share 1).
     */
    private function countryShortCodeMap(): array
    {
        return [
            'Afghanistan' => 'AF',
            'Albania' => 'AL',
            'Algeria' => 'DZ',
            'Andorra' => 'AD',
            'Angola' => 'AO',
            'Argentina' => 'AR',
            'Armenia' => 'AM',
            'Australia' => 'AU',
            'Austria' => 'AT',
            'Azerbaijan' => 'AZ',
            'Bahamas' => 'BS',
            'Bahrain' => 'BH',
            'Bangladesh' => 'BD',
            'Barbados' => 'BB',
            'Belarus' => 'BY',
            'Belgium' => 'BE',
            'Belize' => 'BZ',
            'Benin' => 'BJ',
            'Bhutan' => 'BT',
            'Bolivia' => 'BO',
            'Bosnia and Herzegovina' => 'BA',
            'Botswana' => 'BW',
            'Brazil' => 'BR',
            'Brunei' => 'BN',
            'Bulgaria' => 'BG',
            'Burkina Faso' => 'BF',
            'Burundi' => 'BI',
            'Cambodia' => 'KH',
            'Cameroon' => 'CM',
            'Canada' => 'CA',
            'Cape Verde' => 'CV',
            'Central African Republic' => 'CF',
            'Chad' => 'TD',
            'Chile' => 'CL',
            'China' => 'CN',
            'Colombia' => 'CO',
            'Costa Rica' => 'CR',
            'Croatia' => 'HR',
            'Cyprus' => 'CY',
            'Czech Republic' => 'CZ',
            'Denmark' => 'DK',
            'Dominican Republic' => 'DO',
            'Ecuador' => 'EC',
            'Egypt' => 'EG',
            'El Salvador' => 'SV',
            'Estonia' => 'EE',
            'Eswatini' => 'SZ',
            'Ethiopia' => 'ET',
            'Fiji' => 'FJ',
            'Finland' => 'FI',
            'France' => 'FR',
            'Germany' => 'DE',
            'Ghana' => 'GH',
            'Greece' => 'GR',
            'Guatemala' => 'GT',
            'Haiti' => 'HT',
            'Honduras' => 'HN',
            'Hungary' => 'HU',
            'Iceland' => 'IS',
            'India' => 'IN',
            'Indonesia' => 'ID',
            'Iran' => 'IR',
            'Iraq' => 'IQ',
            'Ireland' => 'IE',
            'Israel' => 'IL',
            'Italy' => 'IT',
            'Jamaica' => 'JM',
            'Japan' => 'JP',
            'Jordan' => 'JO',
            'Kazakhstan' => 'KZ',
            'Kenya' => 'KE',
            'Kuwait' => 'KW',
            'Kyrgyzstan' => 'KG',
            'Laos' => 'LA',
            'Latvia' => 'LV',
            'Lebanon' => 'LB',
            'Liberia' => 'LR',
            'Libya' => 'LY',
            'Lithuania' => 'LT',
            'Luxembourg' => 'LU',
            'Malaysia' => 'MY',
            'Maldives' => 'MV',
            'Malta' => 'MT',
            'Mexico' => 'MX',
            'Moldova' => 'MD',
            'Monaco' => 'MC',
            'Morocco' => 'MA',
            'Nepal' => 'NP',
            'Netherlands' => 'NL',
            'New Zealand' => 'NZ',
            'Nicaragua' => 'NI',
            'Nigeria' => 'NG',
            'Norway' => 'NO',
            'Pakistan' => 'PK',
            'Peru' => 'PE',
            'Philippines' => 'PH',
            'Poland' => 'PL',
            'Portugal' => 'PT',
            'Qatar' => 'QA',
            'Romania' => 'RO',
            'Russia' => 'RU',
            'Saudi Arabia' => 'SA',
            'Singapore' => 'SG',
            'South Africa' => 'ZA',
            'South Korea' => 'KR',
            'Spain' => 'ES',
            'Sri Lanka' => 'LK',
            'Sweden' => 'SE',
            'Switzerland' => 'CH',
            'Taiwan' => 'TW',
            'Thailand' => 'TH',
            'Turkey' => 'TR',
            'Ukraine' => 'UA',
            'United Arab Emirates' => 'AE',
            'United Kingdom' => 'GB',
            'United States' => 'US',
            'Uruguay' => 'UY',
            'Venezuela' => 'VE',
            'Vietnam' => 'VN',
        ];
    }

    public function run(): void
    {
        $updated = 0;
        $missing = [];

        foreach ($this->countryShortCodeMap() as $countryName => $shortCode) {
            $count = Country::where('name', $countryName)->update(['short_code' => $shortCode]);

            if ($count > 0) {
                $updated += $count;
                continue;
            }

            $missing[] = $countryName;
        }

        $this->command?->info("Updated short_code for {$updated} country row(s).");

        if (!empty($missing)) {
            $this->command?->warn('No matching countries table row for: ' . implode(', ', $missing));
        }

        $unset = Country::whereNull('short_code')->orWhere('short_code', '')->pluck('name');

        if ($unset->isNotEmpty()) {
            $this->command?->warn('Still without a short_code: ' . $unset->implode(', '));
        }
    }
}
