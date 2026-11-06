<?php

namespace App\Observers;

use App\Models\Tour;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Log;

/**
 * When a tour moves past Prospect into Tentative/Confirmed/Definite/Actual,
 * ensure a proforma or final invoice exists. DMC custom templates apply at PDF render.
 */
class TourInvoiceObserver
{
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {
    }

    public function updated(Tour $tour): void
    {
        if (! $tour->wasChanged('tour_status')) {
            return;
        }

        $status = (string) ($tour->tour_status ?? '');
        if (! in_array($status, InvoiceService::invoiceEligibleStatuses(), true)) {
            return;
        }

        $tourId = (int) ($tour->tour_id ?? 0);
        if ($tourId < 1) {
            return;
        }

        try {
            $this->invoiceService->ensureInvoiceForTour($tourId);
        } catch (\Throwable $e) {
            Log::warning('Auto invoice ensure failed after tour status change', [
                'tour_id' => $tourId,
                'tour_status' => $status,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
