<?php

namespace App\Services\HotelSuppliers\Hotelbeds;

use Carbon\Carbon;

/**
 * Map Hotelbeds `{ amount, from }` cancellation rules into the MG-style list
 * consumed by the online modal and approve free-cancel logic.
 */
class HotelbedsCancellationPolicies
{
    /**
     * @param  array<int, mixed>  $rawPolicies
     * @return array<int, array<string, mixed>>
     */
    public static function normalize(
        array $rawPolicies,
        string $checkIn = '',
        string $checkOut = '',
        float $netPrice = 0.0,
    ): array {
        $sorted = [];

        foreach ($rawPolicies as $policy) {
            if (! is_array($policy)) {
                continue;
            }

            $fromDate = self::policyDateYmd($policy['from'] ?? $policy['fromDate'] ?? '');
            if ($fromDate === '') {
                continue;
            }

            $sorted[] = [
                'fromDate' => $fromDate,
                'amount' => $policy['amount'] ?? ($policy['grossAmount'] ?? ''),
            ];
        }

        usort(
            $sorted,
            fn (array $a, array $b) => strcmp((string) $a['fromDate'], (string) $b['fromDate'])
        );

        $normalized = [];

        foreach ($sorted as $index => $policy) {
            $nextFrom = $sorted[$index + 1]['fromDate'] ?? null;
            $toDate = is_string($nextFrom) && $nextFrom !== ''
                ? self::dayBeforeYmd($nextFrom)
                : ($checkIn !== '' ? $checkIn : self::dayBeforeYmd($checkOut));

            $amount = is_numeric($policy['amount'])
                ? (string) $policy['amount']
                : trim((string) $policy['amount']);
            $amountFloat = is_numeric($amount) ? (float) $amount : null;

            $noShow = false;
            $percent = '';
            if (
                $amountFloat !== null
                && $netPrice > 0
                && $amountFloat >= ($netPrice * 0.99)
                && $checkIn !== ''
                && $policy['fromDate'] >= $checkIn
            ) {
                $noShow = true;
                $percent = '100.00';
            }

            $normalized[] = [
                'fromDate' => $policy['fromDate'],
                'toDate' => $toDate,
                'amount' => $amount,
                'b2BMarkup' => '',
                'grossAmount' => $amount,
                'nights' => '',
                'percent' => $percent,
                'noShow' => $noShow,
            ];
        }

        return $normalized;
    }

    public static function policyTypeFromRateClass(string $rateClass): string
    {
        $rateClass = strtoupper(trim($rateClass));

        if ($rateClass === 'NRF') {
            return 'NRF';
        }

        if ($rateClass === 'NOR') {
            return 'Flexi';
        }

        return $rateClass;
    }

    public static function policyDateYmd(mixed $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $raw, $matches)) {
            return $matches[1];
        }

        try {
            return Carbon::parse($raw)->toDateString();
        } catch (\Throwable) {
            return '';
        }
    }

    public static function dayBeforeYmd(string $ymd): string
    {
        $date = self::policyDateYmd($ymd);
        if ($date === '') {
            return '';
        }

        try {
            return Carbon::parse($date)->subDay()->toDateString();
        } catch (\Throwable) {
            return '';
        }
    }
}
