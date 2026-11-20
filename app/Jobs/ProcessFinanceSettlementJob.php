<?php

namespace App\Jobs;

use App\Models\Tour;
use App\Services\Finance\FinanceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Background settlement check for pending online payments.
 * Dispatched with delay after payment creation; handles timeout scenarios.
 */
class ProcessFinanceSettlementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $tourId,
        public string $context = 'online_pending',
    ) {}

    public function handle(FinanceService $financeService): void
    {
        $tour = Tour::find($this->tourId);
        if (! $tour) {
            return;
        }

        if ($tour->payment_status === 'captured' && $tour->finance_status === 'settled') {
            return;
        }

        if ($tour->payment_status === 'pending' && $this->context === 'online_pending') {
            Log::warning('finance_settlement_timeout', [
                'tour_id' => $this->tourId,
                'payment_status' => $tour->payment_status,
            ]);

            $tour->update([
                'payment_status' => 'failed',
                'finance_status' => 'failed',
            ]);
        }
    }
}
