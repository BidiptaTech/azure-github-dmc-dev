<?php

namespace App\Jobs;

use App\Services\Finance\FinanceService;
use App\Services\Finance\PaymentGatewayService;
use App\Services\Finance\SettlementService;
use App\Services\Finance\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Dead-letter style retry for failed finance operations.
 * Uses exponential backoff from config/finance.php.
 */
class RetryFinanceOperationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    public function __construct(
        public string $operation,
        public array $payload,
    ) {
        $this->tries = (int) config('finance.retry.max_attempts', 3);
    }

    public function backoff(): array
    {
        return config('finance.retry.backoff_seconds', [10, 60, 300]);
    }

    public function handle(
        FinanceService $financeService,
        WalletService $walletService,
        SettlementService $settlementService,
        PaymentGatewayService $paymentGatewayService,
    ): void {
        Log::info('finance_retry_attempt', [
            'operation' => $this->operation,
            'attempt' => $this->attempts(),
            'idempotency_key' => $this->payload['idempotency_key'] ?? null,
        ]);

        match ($this->operation) {
            'wallet_booking' => $walletService->bookWithWallet($this->payload),
            'wallet_topup' => $walletService->topup($this->payload),
            'offline_settlement' => $settlementService->settleOffline($this->payload),
            'online_settlement' => $settlementService->settleOnline($this->payload),
            'refund' => $paymentGatewayService->refund($this->payload),
            'payment_callback' => $paymentGatewayService->forwardCallback(
                $this->payload['payload'] ?? $this->payload,
                $this->payload['signature'] ?? null,
            ),
            default => Log::error('finance_retry_unknown_operation', ['operation' => $this->operation]),
        };
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('finance_retry_exhausted', [
            'operation' => $this->operation,
            'error' => $exception->getMessage(),
            'payload' => $this->payload,
        ]);
    }
}
