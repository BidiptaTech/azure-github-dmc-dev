<?php

namespace App\Jobs;

use App\Services\Payment\OnlineServiceAutoBookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Book the paid online services of a tour with their vendors (one by one) after Pay Now.
 *
 * QUEUE_CONNECTION=sync → runs immediately inside the payment request (result shown at once).
 * Any real queue (database/redis) → runs in the background worker.
 */
class AutoBookOnlineServicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Vendor calls must never be repeated blindly — the service is idempotent, but don't auto-retry. */
    public int $tries = 1;

    public int $timeout = 900;

    public function __construct(
        public int $tourId,
        public string $attemptUuid,
        public ?int $userId = null,
    ) {}

    public function handle(OnlineServiceAutoBookingService $service): void
    {
        $service->run($this->tourId, $this->attemptUuid, $this->userId);
    }
}
