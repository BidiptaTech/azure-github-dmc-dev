<?php

namespace App\Services\Payment;

use App\Helpers\CommonHelper;
use App\Models\Order;
use App\Models\Tour;
use App\Services\AttractionSuppliers\OnlineAttractionCancellationService;
use App\Services\HotelSuppliers\OnlineHotelCancellationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Full-tour cancel:
 * - Paid online services → supplier cancel + is_refund soft-delete (TravClicks wallet credit only)
 * - Offline booking services → soft-delete without is_refund (DMC refunds the tour amount, not per-service)
 *
 * DMC Refunds UI uses tour_status Refund - Pending for the full tour amount.
 * Mix flow often pays online while tour is still New Enquiry / Confirmed and
 * online orders may still be bookingType=enquiry — those must still cancel with supplier.
 */
class OnlineTourCancelRefundService
{
    public function __construct(
        protected OnlineHotelCancellationService $hotelCancellation,
        protected OnlineAttractionCancellationService $attractionCancellation,
        protected AcceptanceFlowService $acceptanceFlow,
    ) {}

    /**
     * Whether this tour already collected online payment (mix / online flows).
     */
    public function tourHasPaidOnlinePortion(Tour $tour): bool
    {
        if ($this->acceptanceFlow->onlinePortionSettled($tour)) {
            return true;
        }

        $stage = strtolower(trim((string) ($tour->payment_stage ?? '')));

        return in_array($stage, ['offline_negotiation', 'dmc_collection'], true);
    }

    /**
     * Queue refund services before tour_status changes.
     *
     * @return array{queued: int, skipped: int, online_queued: int, offline_queued: int}
     */
    public function enqueuePaidOnlineServices(Tour $tour): array
    {
        $status = (string) ($tour->tour_status ?? '');
        $tourId = (int) ($tour->tour_id ?? 0);
        $empty = ['queued' => 0, 'skipped' => 0, 'online_queued' => 0, 'offline_queued' => 0];
        if ($tourId <= 0) {
            return $empty;
        }

        $onlinePaid = $this->tourHasPaidOnlinePortion($tour);
        $isDefiniteOrActual = in_array($status, ['Definite', 'Actual'], true);

        // No paid online + not Definite/Actual → nothing to put on Refunds tab
        if (! $onlinePaid && ! $isDefiniteOrActual) {
            return $empty;
        }

        $onlineResult = ['queued' => 0, 'skipped' => 0];
        if (Schema::hasColumn('orders', 'order_type')) {
            $onlineResult = $this->enqueueOnlineServices($tour, $status, $onlinePaid);
        }

        $offlineQueued = 0;
        // Full tour cancel: remove remaining offline booking services without per-service refund rows.
        // DMC refunds the tour paid amount via processRefund when tour_status is Refund - Pending.
        if ($isDefiniteOrActual) {
            $offlineQueued = $this->enqueueOfflineBookingServices($tour, $status);
        }

        $queued = (int) $onlineResult['queued'] + $offlineQueued;

        Log::info('Tour cancel queued services for TravClicks/DMC refund flow', [
            'tour_id' => $tourId,
            'tour_status' => $status,
            'online_paid' => $onlinePaid,
            'online_queued' => (int) $onlineResult['queued'],
            'offline_queued' => $offlineQueued,
            'queued' => $queued,
            'skipped' => (int) $onlineResult['skipped'],
        ]);

        return [
            'queued' => $queued,
            'skipped' => (int) $onlineResult['skipped'],
            'online_queued' => (int) $onlineResult['queued'],
            'offline_queued' => $offlineQueued,
        ];
    }

    /**
     * @return array{queued: int, skipped: int}
     */
    protected function enqueueOnlineServices(Tour $tour, string $status, bool $onlinePaid): array
    {
        $tourId = (int) ($tour->tour_id ?? 0);

        $query = Order::query()
            ->where('tour_id', $tourId)
            ->whereRaw("LOWER(TRIM(COALESCE(order_type, ''))) = ?", ['online']);

        // After online pay, mix tours often still have bookingType=enquiry
        if ($onlinePaid) {
            $query->whereIn('bookingType', ['booking', 'enquiry']);
        } else {
            $query->where('bookingType', 'booking');
        }

        $orders = $query->get();
        if ($orders->isEmpty()) {
            return ['queued' => 0, 'skipped' => 0];
        }

        $queued = 0;
        $skipped = 0;

        // Defer supplier cancel + wallet to TravClicks auto-refund (Refunds page).
        foreach ($orders as $order) {
            if (! $this->orderLooksPaidOnline($order, $onlinePaid)) {
                $skipped++;
                continue;
            }

            try {
                app(SupplierCancellationStatusService::class)->markPending(
                    $order,
                    'Queued for TravClicks auto-refund: supplier cancel + wallet credit pending.'
                );
            } catch (Throwable $e) {
                Log::warning('Could not mark supplier cancellation pending during tour cancel', [
                    'tour_id' => $tourId,
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        DB::transaction(function () use ($orders, $tourId, $status, $onlinePaid, &$queued) {
            $currentUser = Auth::user();
            $changedByName = $currentUser ? ($currentUser->name ?? null) : null;
            $changedByUserId = $currentUser ? ($currentUser->userId ?? $currentUser->id ?? null) : null;
            $jobsheets = app(\App\Services\JobsheetRemovalService::class);

            foreach ($orders as $order) {
                if (! $this->orderLooksPaidOnline($order, $onlinePaid)) {
                    continue;
                }

                // Promote to booking so Refunds queries (bookingType=booking) pick it up
                if (($order->bookingType ?? '') !== 'booking') {
                    $order->bookingType = 'booking';
                }

                $order->is_refund = 1;
                $order->is_verify = 0;
                $order->refunded = false;
                if (Schema::hasColumn('orders', 'travclicks_refunded')) {
                    $order->travclicks_refunded = false;
                }
                $order->save();

                $payload = is_array($order->data) && isset($order->data[0])
                    ? $order->data[0]
                    : (is_array($order->data) ? $order->data : []);
                $orderType = (string) ($order->type ?? '');
                [$serviceId, $serviceName] = $this->resolveServiceMeta($orderType, $payload);

                CommonHelper::appendTourStatusTrackById(
                    $tourId,
                    $status,
                    $status,
                    null,
                    null,
                    null,
                    null,
                    $changedByName,
                    $changedByUserId,
                    'deleted',
                    $orderType !== '' ? $orderType : null,
                    $serviceId,
                    $serviceName
                );

                $jobsheets->deleteForOrder($order);
                $order->delete();
                $queued++;
            }
        });

        // TravClicks Refunds page auto-refund will cancel supplier API + credit wallet.
        return ['queued' => $queued, 'skipped' => $skipped];
    }

    /**
     * Definite/Actual full-tour cancel: soft-delete remaining offline booking services.
     * Does not set is_refund — DMC refunds the tour amount, not each service.
     * Skips online rows (already handled) and enquiry rows.
     */
    protected function enqueueOfflineBookingServices(Tour $tour, string $status): int
    {
        $tourId = (int) ($tour->tour_id ?? 0);
        if ($tourId <= 0) {
            return 0;
        }

        $query = Order::query()
            ->where('tour_id', $tourId)
            ->where('bookingType', 'booking')
            ->where(function ($q) {
                $q->whereNull('is_refund')
                    ->orWhere('is_refund', 0)
                    ->orWhere('is_refund', false);
            });

        if (Schema::hasColumn('orders', 'order_type')) {
            $query->where(function ($q) {
                $q->whereNull('order_type')
                    ->orWhereRaw("LOWER(TRIM(COALESCE(order_type, ''))) = ?", [''])
                    ->orWhereRaw("LOWER(TRIM(COALESCE(order_type, ''))) <> ?", ['online']);
            });
        }

        $orders = $query->get();
        if ($orders->isEmpty()) {
            return 0;
        }

        $queued = 0;

        DB::transaction(function () use ($orders, $tourId, $status, &$queued) {
            $currentUser = Auth::user();
            $changedByName = $currentUser ? ($currentUser->name ?? null) : null;
            $changedByUserId = $currentUser ? ($currentUser->userId ?? $currentUser->id ?? null) : null;
            $jobsheets = app(\App\Services\JobsheetRemovalService::class);

            foreach ($orders as $order) {
                // Tour-level refund: do not queue per-service refund rows for DMC.
                $payload = is_array($order->data) && isset($order->data[0])
                    ? $order->data[0]
                    : (is_array($order->data) ? $order->data : []);
                $orderType = (string) ($order->type ?? '');
                [$serviceId, $serviceName] = $this->resolveServiceMeta($orderType, $payload);

                CommonHelper::appendTourStatusTrackById(
                    $tourId,
                    $status,
                    $status,
                    null,
                    null,
                    null,
                    null,
                    $changedByName,
                    $changedByUserId,
                    'deleted',
                    $orderType !== '' ? $orderType : null,
                    $serviceId,
                    $serviceName
                );

                $jobsheets->deleteForOrder($order);
                $order->delete();
                $queued++;
            }
        });

        return $queued;
    }

    /**
     * Paid online = tour online portion settled, or this order has supplier booking evidence.
     */
    protected function orderLooksPaidOnline(Order $order, bool $tourOnlinePaid): bool
    {
        if ($tourOnlinePaid) {
            return true;
        }

        $details = $order->booking_details;
        if (is_string($details) && $details !== '') {
            $decoded = json_decode($details, true);
            $details = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($details) || $details === []) {
            return false;
        }

        if (! empty($details['cancelled_at']) || ! empty($details['cancellation']['cancelled_at'])) {
            return true;
        }

        foreach (['booking_id', 'bookingId', 'confirmation_number', 'confirmationNumber', 'mg_booking_id', 'supplier_booking_id'] as $key) {
            if (! empty($details[$key])) {
                return true;
            }
        }

        $status = strtolower(trim((string) ($details['status'] ?? $details['booking_status'] ?? '')));

        return in_array($status, ['confirmed', 'booked', 'success', 'ok'], true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{0: mixed, 1: mixed}
     */
    protected function resolveServiceMeta(string $orderType, array $payload): array
    {
        if ($orderType === 'hotel') {
            return [
                $payload['hotelDetails']['hotel_id'] ?? null,
                $payload['hotelDetails']['hotel_name'] ?? null,
            ];
        }
        if ($orderType === 'attraction') {
            return [
                $payload['attraction_id'] ?? null,
                $payload['AttractionName'] ?? $payload['attraction_name'] ?? null,
            ];
        }

        return [null, null];
    }
}
