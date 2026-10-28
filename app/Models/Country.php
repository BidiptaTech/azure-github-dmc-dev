<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    /** Roles that may view/edit per-DMC remittance charge & exchange rate on the countries listing. */
    public const DMC_REMITTANCE_EXCHANGE_ROLE_IDS = [11, 20, 34, 124, 125, 36, 126, 127, 138];

    protected $table = 'countries';
    protected $guarded = [];

    protected $casts = [
        'remitance_charge' => 'array',
        'exchange_rate' => 'array',
    ];

    protected $fillable = [
        'name',
        'country_code',
        'short_code',
        'currency',
        'timezone',
        'is_active',
        'country_image',
        'tax_percentage',
        'gateway_percentage',
        'commission_percentage',
        'remitance_charge',
        'exchange_rate',
        'card_type',
        'card_length',
        'min_length',
        'max_length',
        'header_pdf',
        'footer_pdf'
    ];

    /**
     * ISO 3166-1 alpha-2 codes keyed by countries.name.
     * Dial codes are not unique, so name is the reliable key.
     *
     * @return array<string, string>
     */
    public static function shortCodeMap(): array
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

    public static function resolveShortCode(?string $name, ?string $storedShortCode = null): ?string
    {
        $code = strtoupper(trim((string) $storedShortCode));
        if (strlen($code) === 2) {
            return $code;
        }

        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $mapped = self::shortCodeMap()[$name] ?? null;

        return $mapped ? strtoupper($mapped) : null;
    }

     // Relationship with City
     public function cities()
     {
         return $this->hasMany(City::class, 'country_id', 'id');
     }

     public function getcities()
    {
        return $this->hasMany(City::class);
    }

    /**
     * Normalized remittance charge for a DMC from JSON (supports legacy `value` shape).
     */
    public function remittanceChargeDisplayForDmc(?int $dmcId): string
    {
        return self::readDmcJsonField($this->remitance_charge, $dmcId, 'remitance_charge');
    }

    /**
     * Normalized exchange rate for a DMC from JSON (supports legacy `value` shape).
     */
    public function exchangeRateDisplayForDmc(?int $dmcId): string
    {
        return self::readDmcJsonField($this->exchange_rate, $dmcId, 'exchange_rate');
    }

    /**
     * @param  array<int, mixed>|array<string, mixed>|string|null  $raw
     */
    public static function readDmcJsonField($raw, ?int $dmcId, string $fieldKey): string
    {
        if (! $dmcId) {
            return '';
        }
        $data = $raw;
        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }
        if (! is_array($data)) {
            return '';
        }

        // New format (preferred): list of objects
        // e.g. [ {"dmc_id":4,"exchange_rate":10}, ... ]
        $candidateRows = array_is_list($data) ? $data : null;
        if (is_array($candidateRows)) {
            foreach ($candidateRows as $row) {
                if (! is_array($row)) {
                    continue;
                }
                if ((int) ($row['dmc_id'] ?? 0) !== (int) $dmcId) {
                    continue;
                }
                if (array_key_exists($fieldKey, $row) && $row[$fieldKey] !== null && $row[$fieldKey] !== '') {
                    return (string) $row[$fieldKey];
                }
                if (array_key_exists('value', $row) && $row['value'] !== null && $row['value'] !== '') {
                    return (string) $row['value'];
                }

                return '';
            }
        }

        // Legacy format: keyed by DMC id
        // e.g. { "4": { "value": 12 } }
        $row = $data[(string) $dmcId] ?? $data[$dmcId] ?? null;
        if (is_array($row)) {
            if (array_key_exists($fieldKey, $row) && $row[$fieldKey] !== null && $row[$fieldKey] !== '') {
                return (string) $row[$fieldKey];
            }
            if (array_key_exists('value', $row) && $row['value'] !== null && $row['value'] !== '') {
                return (string) $row['value'];
            }
        }

        return '';
    }

}
