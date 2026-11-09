<?php

namespace App\Services\Payment;

use App\Models\Country;
use App\Models\Tour;

/**
 * Currency-conversion charge applied when a payment moves between currencies.
 * Percent comes from PAYMENT_FX_CONVERSION_CHARGE_PERCENT, otherwise the
 * destination country's gateway_percentage. Same-currency movements are 0.
 */
class FxConversionCharge
{
    public function percentForTour(?Tour $tour): float
    {
        $configured = config('payment.fx_conversion_charge_percent');
        if (is_numeric($configured) && (float) $configured > 0) {
            return round((float) $configured, 4);
        }

        if (! $tour) {
            return 0.0;
        }

        $destination = trim((string) ($tour->destination ?? ''));
        if ($destination === '') {
            return 0.0;
        }

        $name = trim(explode(',', $destination)[0]);
        if ($name === '') {
            return 0.0;
        }

        $country = Country::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        return round((float) ($country->gateway_percentage ?? 0), 4);
    }

    /**
     * @return array{destination_amount: float, fee: float, exchange_rate: float}
     */
    public function apply(float $sourceAmount, float $rate, float $percent, bool $currenciesDiffer): array
    {
        $rate = $rate > 0 ? $rate : 1.0;
        $converted = round($sourceAmount * $rate, 4);
        $fee = 0.0;

        if ($currenciesDiffer && $percent > 0 && $converted > 0) {
            $fee = round($converted * ($percent / 100), 4);
            $converted = round($converted + $fee, 4);
        }

        return [
            'destination_amount' => $converted,
            'fee' => $fee,
            'exchange_rate' => $rate,
        ];
    }
}
