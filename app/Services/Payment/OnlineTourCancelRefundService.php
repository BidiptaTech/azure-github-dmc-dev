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
use RuntimeException;
use Throwable;

/**
 * Full-tour cancel: paid online services enter the same TravClicks → DMC
 * refund pipeline as per-service cancel (supplier cancel → is_refund → soft delete).
 *
 * Mix flow often pays online while tour is still New Enquiry / Confirmed and
 * online orders may still be bookingType=enquiry — those must still refund.
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
     * Queue paid online services for refund before tour_status changes.
     *
     * @return array{queued: int, skipped: int}
     */
    public function enqueuePaidOnlineServices(Tour $tour): array
    {
        $status = (string) ($tour->tour_status ?? '');
        $tourId = (int) ($tour->tour_id ?? 0);
        if ($tourId <= 0) {
            return ['queued' => 0, 'skipped' => 0];
        }

        if (! Schema::hasColumn('orders', 'order_type')) {
            return ['queued' => 0, 'skipped' => 0];
        }

        $onlinePaid = $this->tourHasPaidOnlinePortion($tour);
        $isDefiniteOrActual = in_array($status, ['Definite', 'Actual'], true);

        // No paid online + not Definite/Actual → nothing to put on Refunds tab
        if (! $onlinePaid && ! $isDefiniteOrActual) {
            return ['queued' => 0, 'skipped' => 0];
        }

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

        // Supplier cancel first (cannot roll back). Fail the whole tour cancel if any fail.
        foreach ($orders as $order) {
            if (! $this->orderLooksPaidOnline($order, $onlinePaid)) {
                $skipped++;
                continue;
            }

            try {
                $this->hotelCancellation->cancelIfApplicable($order);
                $this->attractionCancellation->cancelIfApplicable($order, 'Tour cancelled');
            } catch (Throwable $e) {
                Log::error('Online supplier cancellation failed during tour cancel', [
                    'tour_id' => $tourId,
                    'order_id' => $order->id,
                    'booking_id' => $order->booking_id,
                    'type' => $order->type ?? null,
                    'error' => $e->getMessage(),
                ]);

                $label = (($order->type ?? '') === 'attraction')
                    ? 'online attraction voucher'
                    : 'online hotel';

                throw new RuntimeException(
                    $e instanceof RuntimeException
                        ? $e->getMessage()
                        : 'Failed to cancel the '.$label.' with the supplier. Tour was not cancelled.',
                    0,
                    $e
                );
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

        Log::info('Tour cancel queued paid online services for TravClicks/DMC refund flow', [
            'tour_id' => $tourId,
            'tour_status' => $status,
            'online_paid' => $onlinePaid,
            'queued' => $queued,
            'skipped' => $skipped,
        ]);

        return ['queued' => $queued, 'skipped' => $skipped];
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
