<?php

namespace App\Services\Finance;

use Illuminate\Support\Str;
use RuntimeException;

/**
 * Local Laravel finance facade (replaces former Python Azure Functions HTTP client).
 *
 * WalletService / SettlementService keep calling post($endpoint, $payload);
 * this class routes those endpoints to WalletLedgerService.
 */
class FinanceService
{
    public function __construct(
        protected WalletLedgerService $ledger,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array $payload): array
    {
        if (! isset($payload['idempotency_key'])) {
            $payload['idempotency_key'] = (string) Str::uuid();
        }

        $path = trim($endpoint, '/');

        return match ($path) {
            'wallet/topup' => $this->ledger->topup($payload),
            'wallet/allocate' => $this->ledger->allocate($payload),
            'wallet/booking' => $this->ledger->bookWithWallet($payload),
            'wallet/booking/distribute-proceeds' => $this->ledger->distributeBookingProceeds($payload),
            'wallet/reserve' => $this->ledger->reserve($payload),
            'wallet/commit' => $this->ledger->commitReservation($payload),
            'wallet/release' => $this->ledger->releaseReservation($payload),
            'settlement/online' => $this->ledger->settleOnline($payload),
            'settlement/offline' => $this->ledger->settleOffline($payload),
            'commission/calculate' => $this->ledger->calculateCommission($payload),
            'refund' => $this->ledger->refund($payload),
            'payment/callback' => $this->handlePaymentCallback($payload),
            default => throw new RuntimeException("Unknown finance endpoint: {$path}"),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $endpoint): array
    {
        $path = trim($endpoint, '/');
        if ($path === 'health') {
            return $this->ledger->healthCheck();
        }

        return [];
    }

    public function healthCheck(): array
    {
        return $this->ledger->healthCheck();
    }

    /**
     * Normalize Stripe (or legacy) gateway callbacks into online settlement.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function handlePaymentCallback(array $payload): array
    {
        $status = strtolower((string) ($payload['status'] ?? $payload['payment_status'] ?? ''));
        if (in_array($status, ['failed', 'failure', 'canceled', 'cancelled'], true)) {
            return [
                'success' => true,
                'message' => 'Payment failure recorded',
                'data' => ['status' => 'failed'],
            ];
        }

        if (empty($payload['payment_transaction_id']) && empty($payload['tour_id'])) {
            throw new RuntimeException('payment_transaction_id or tour_id required for callback settlement');
        }

        return $this->ledger->settleOnline([
            'idempotency_key' => $payload['idempotency_key'] ?? ('cb-'.($payload['transaction_id'] ?? Str::uuid())),
            'tour_id' => (int) ($payload['tour_id'] ?? 0),
            'payment_transaction_id' => (int) ($payload['payment_transaction_id'] ?? 0),
            'gateway_transaction_reference' => (string) ($payload['transaction_id'] ?? $payload['gateway_transaction_reference'] ?? ''),
            'payment_gateway_name' => (string) ($payload['gateway_name'] ?? 'stripe'),
            'supplier_api_id' => (string) ($payload['supplier_api_id'] ?? 'offline'),
            'supplier_booking_reference' => $payload['supplier_booking_reference'] ?? null,
            'supplier_cost' => $payload['supplier_cost'] ?? 0,
            'travclicks_markup' => $payload['travclicks_markup'] ?? 0,
            'dmc_commission' => $payload['dmc_commission'] ?? 0,
            'selling_price' => $payload['selling_price'] ?? $payload['amount'] ?? 0,
            'payment_received_amount' => $payload['amount'] ?? $payload['payment_received_amount'] ?? 0,
            'payment_received_currency' => $payload['currency'] ?? $payload['payment_received_currency'] ?? 'USD',
            'gateway_response_json' => $payload['raw'] ?? $payload,
            'dmc_id' => $payload['dmc_id'] ?? null,
        ]);
    }
}
