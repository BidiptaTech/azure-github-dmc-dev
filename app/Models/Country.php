<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    /** Roles that may view/edit per-DMC remittance charge & exchange rate on the countries listing. */
    public const DMC_REMITTANCE_EXCHANGE_ROLE_IDS = [11, 20, 34, 124, 125, 36, 126, 127];

    protected $table = 'countries';
    protected $guarded = [];

    protected $casts = [
        'remitance_charge' => 'array',
        'exchange_rate' => 'array',
    ];

    protected $fillable = [
        'name',
        'country_code',
        'currency',
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
