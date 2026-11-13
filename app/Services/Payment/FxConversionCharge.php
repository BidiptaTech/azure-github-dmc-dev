<?php

namespace App\Services\Payment;

use App\Models\Country;
use App\Models\Tour;

/**
 * Currency-conversion charge when payment wallet currency differs from order currency.
 *
 * Standard procedure:
 * - Rates use countries.current_rate with SGD as pivot (1 SGD = rate units).
 * - Fee % comes only from PAYMENT_FX_CONVERSION_CHARGE_PERCENT (config).
 * - Display / Paid-in use effective rate X = base + (base × %): e.g. 14,165.74 + 1,558.23.
 * - Same-currency movements: fee = 0.
 * - Do not use countries.gateway_percentage for this fee.
 */
class FxConversionCharge
{
    /**
     * Active FX conversion charge percent (config only).
     */
    public function percent(): float
    {
        $configured = config('payment.fx_conversion_charge_percent');
        if ($configured === null || $configured === '') {
            return 0.0;
        }

        return max(0.0, round((float) $configured, 4));
    }

    /**
     * @deprecated Use percent() — tour destination gateway_percentage is not used.
     */
    public function percentForTour(?Tour $tour): float
    {
        return $this->percent();
    }

    /**
     * Effective rate X = base + (base × percent/100).
     * Example: 14165.74 + 1558.23 = 15723.97.
     */
    public function effectiveRate(float $baseRate, ?float $percent = null): float
    {
        $baseRate = $baseRate > 0 ? $baseRate : 0.0;
        $percent = $percent === null ? $this->percent() : max(0.0, (float) $percent);
        if ($baseRate <= 0) {
            return 0.0;
        }
        if ($percent <= 0) {
            return round($baseRate, 6);
        }

        return round($baseRate * (1 + ($percent / 100)), 6);
    }

    /**
     * Paid-in / debit amount from order amount × effective rate X.
     */
    public function paidInAmount(float $orderAmount, float $baseRate, ?float $percent = null, string $debitCurrency = ''): float
    {
        $effective = $this->effectiveRate($baseRate, $percent);
        if ($orderAmount <= 0 || $effective <= 0) {
            return 0.0;
        }
        $raw = $orderAmount * $effective;
        $debitCurrency = strtoupper(trim($debitCurrency));

        return $debitCurrency === 'IDR' ? round($raw) : round($raw, 2);
    }

    /**
     * Rate + Paid-in display for a payment_details row (online or offline).
     *
     * @param  array<string, mixed>  $payment
     * @return array{
     *   show_rate: bool,
     *   rate_per_sgd: float,
     *   rate_currency: string,
     *   paid_in: float|null,
     *   paid_in_currency: string,
     *   converted: bool,
     * }
     */
    public function paymentDisplay(array $payment, string $tourCurrency = 'SGD'): array
    {
        $tourCurrency = strtoupper(trim($tourCurrency));
        $pct = (float) ($payment['conversion_charge_percent'] ?? $payment['charge_percent'] ?? $this->percent());
        $payCur = strtoupper(trim((string) ($payment['currency'] ?? $tourCurrency)));
        $walletCur = strtoupper(trim((string) ($payment['wallet_currency'] ?? '')));
        $orderCur = strtoupper(trim((string) ($payment['order_currency'] ?? '')));
        $baseRate = (float) ($payment['exchange_rate'] ?? 0);

        // Offline TourController FX rows: amount in tour currency, currency/original_amount = debit currency.
        if ($orderCur === '' && $walletCur === ''
            && isset($payment['original_amount'])
            && $payCur !== ''
            && $payCur !== $tourCurrency
            && $baseRate > 0
            && abs($baseRate - 1.0) > 0.0000001
        ) {
            $orderCur = $tourCurrency;
            $walletCur = $payCur;
        }

        if ($orderCur === '') {
            $orderCur = ($walletCur !== '' && isset($payment['wallet_amount'])) ? $payCur : $tourCurrency;
        }
        if ($orderCur === '') {
            $orderCur = $tourCurrency;
        }

        $debitCur = $walletCur !== '' ? $walletCur : '';
        $converted = $debitCur !== '' && $orderCur !== '' && $debitCur !== $orderCur;

        $sgdBase = 0.0;
        if ($converted && $debitCur !== 'SGD') {
            if ($orderCur === 'SGD' && $baseRate > 0 && abs($baseRate - 1.0) > 0.0000001) {
                $sgdBase = $baseRate;
            } else {
                $sgdBase = (float) (Country::sgdRateFor($debitCur) ?? 0);
                if ($sgdBase <= 0 && $baseRate > 0 && abs($baseRate - 1.0) > 0.0000001) {
                    $sgdBase = $baseRate;
                }
            }
        }

        $effective = $this->effectiveRate($sgdBase, $pct);
        $orderAmount = isset($payment['amount']) ? (float) $payment['amount'] : null;

        $paidIn = null;
        $paidInCur = $debitCur !== '' ? $debitCur : $orderCur;
        if ($converted && $orderCur === 'SGD' && $orderAmount !== null && $effective > 0) {
            $paidIn = $this->paidInAmount($orderAmount, $sgdBase, $pct, $debitCur);
            $paidInCur = $debitCur;
        } elseif ($converted && isset($payment['wallet_amount'])) {
            $paidIn = (float) $payment['wallet_amount'];
            $paidInCur = $debitCur;
        } elseif ($converted && isset($payment['original_amount'])) {
            $paidIn = (float) $payment['original_amount'];
            $paidInCur = $debitCur !== '' ? $debitCur : $payCur;
        } elseif (isset($payment['wallet_amount'])) {
            $paidIn = (float) $payment['wallet_amount'];
            $paidInCur = $debitCur !== '' ? $debitCur : $payCur;
        } elseif (isset($payment['original_amount'])) {
            $paidIn = (float) $payment['original_amount'];
            $paidInCur = $payCur !== '' ? $payCur : $tourCurrency;
        } elseif ($orderAmount !== null) {
            $paidIn = $orderAmount;
            $paidInCur = $orderCur !== '' ? $orderCur : $tourCurrency;
        }

        return [
            'show_rate' => $converted && $effective > 0,
            'rate_per_sgd' => $effective,
            'rate_currency' => $debitCur,
            'paid_in' => $paidIn,
            'paid_in_currency' => $paidInCur,
            'converted' => $converted,
        ];
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
