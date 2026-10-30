<?php

namespace App\Services\Payment;

use App\Helpers\CurrencyHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Immutable FX snapshots for financial events. Never recalculate history with live rates.
 */
class CurrencySnapshotService
{
    /**
     * @param  array{
     *   source_currency: string,
     *   destination_currency: string,
     *   source_amount: float|int|string,
     *   tour_id?: int|null,
     *   payment_transaction_id?: int|null,
     *   wallet_ledger_id?: int|null,
     *   agent_id?: int|null,
     *   dmc_id?: int|null,
     *   stripe_reference?: string|null,
     *   metadata?: array<string, mixed>|null,
     *   exchange_rate?: float|null,
     *   rate_provider?: string|null,
     *   conversion_charge_percent?: float|null,
     * }  $input
     * @return array<string, mixed>
     */
    public function createSnapshot(array $input): array
    {
        $source = strtoupper(trim((string) $input['source_currency']));
        $destination = strtoupper(trim((string) $input['destination_currency']));
        $sourceAmount = round((float) $input['source_amount'], 4);

        if ($source === '' || $destination === '') {
            throw new RuntimeException('Source and destination currencies are required.');
        }

        if ($source === $destination) {
            $rate = 1.0;
            $destinationAmount = $sourceAmount;
            $provider = $input['rate_provider'] ?? 'identity';
            $rateTs = now();
        } else {
            $rate = isset($input['exchange_rate']) && $input['exchange_rate'] !== null
                ? (float) $input['exchange_rate']
                : $this->fetchAuthoritativeRate($source, $destination);

            if ($rate <= 0) {
                throw new RuntimeException("Exchange rate unavailable for {$source}→{$destination}.");
            }

            $destinationAmount = round($sourceAmount * $rate, 4);
            $provider = $input['rate_provider'] ?? 'CurrencyService';
            $rateTs = now();
        }

        $percent = (float) ($input['conversion_charge_percent'] ?? 0);
        $quoted = app(FxConversionCharge::class)->apply(
            $sourceAmount,
            $rate,
            $percent,
            $source !== $destination,
        );
        $destinationAmount = $quoted['destination_amount'];
        $fee = $quoted['fee'];

        $payload = [
            'snapshot_uuid' => (string) Str::uuid(),
            'source_currency' => $source,
            'destination_currency' => $destination,
            'source_amount' => $sourceAmount,
            'destination_amount' => $destinationAmount,
            'exchange_rate' => $rate,
            'rate_direction' => 'source_to_destination',
            'rate_provider' => $provider,
            'rate_timestamp' => $rateTs,
            'conversion_timestamp' => now(),
            'decimal_precision' => 4,
            'rounding_policy' => 'half_up',
            'fee_or_spread' => $input['fee_or_spread'] ?? $fee,
            'tour_id' => $input['tour_id'] ?? null,
            'payment_transaction_id' => $input['payment_transaction_id'] ?? null,
            'wallet_ledger_id' => $input['wallet_ledger_id'] ?? null,
            'agent_id' => $input['agent_id'] ?? null,
            'dmc_id' => $input['dmc_id'] ?? null,
            'stripe_reference' => $input['stripe_reference'] ?? null,
            'metadata' => isset($input['metadata']) ? json_encode($input['metadata']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (! Schema::hasTable('currency_conversion_snapshots')) {
            return array_merge($payload, ['id' => null, 'persisted' => false]);
        }

        $id = DB::table('currency_conversion_snapshots')->insertGetId($payload);
        $payload['id'] = $id;
        $payload['persisted'] = true;
        $payload['metadata'] = $input['metadata'] ?? null;

        return $payload;
    }

    public function find(int $id): ?array
    {
        if (! Schema::hasTable('currency_conversion_snapshots')) {
            return null;
        }

        $row = DB::table('currency_conversion_snapshots')->where('id', $id)->first();

        return $row ? (array) $row : null;
    }

    protected function fetchAuthoritativeRate(string $source, string $destination): float
    {
        try {
            if (method_exists(CurrencyHelper::class, 'getExchangeRate')) {
                $rate = CurrencyHelper::getExchangeRate($source, $destination);
                if (is_numeric($rate) && (float) $rate > 0) {
                    return (float) $rate;
                }
            }
        } catch (\Throwable) {
            // fall through
        }

        throw new RuntimeException("Exchange rate unavailable for {$source}→{$destination}.");
    }
}
