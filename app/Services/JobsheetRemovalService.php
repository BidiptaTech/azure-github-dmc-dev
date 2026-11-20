<?php

namespace App\Services;

use App\Models\Jobsheet;
use App\Models\Tour;
use Illuminate\Support\Facades\Log;

/**
 * Soft-deletes jobsheet rows that belong to a service that is actually being removed.
 *
 * Standalone transport and guide orders always own a jobsheet.
 * Attraction and restaurant orders own one only when the booking includes a transfer and/or a guide.
 * Call this only for a real removal. Edit-save recreates kept services with a new booking id
 * and must not pass those orders in.
 *
 * Scope follows tours.tour_status (orders.tour_id → tours.tour_id):
 * Confirmed removes every jobsheet for that tour.
 * Definite and Actual remove jobsheets for this order only.
 * Any other status also removes this order only.
 */
class JobsheetRemovalService
{
    /** @var list<string> */
    private const TRANSPORT_TYPES = [
        'travel_point',
        'travel_hourly',
        'local_transport',
        'local_transfer',
        'entry_port',
        'exit_port',
    ];

    /** @var list<string> */
    private const GUIDE_TYPES = [
        'guide',
        'dayguide',
        'halfguide',
    ];

    /**
     * @param  object|array<string, mixed>  $order
     */
    public function deleteForOrder($order): int
    {
        try {
            if ($this->tourStatus($order) === 'confirmed') {
                return $this->deleteAllForTour($order);
            }

            return $this->deleteForThisOrder($order);
        } catch (\Throwable $e) {
            Log::warning('Jobsheet cleanup failed after service removal', [
                'booking_id' => is_array($order) ? ($order['booking_id'] ?? null) : ($order->booking_id ?? null),
                'tour_id' => $this->tourId($order),
                'type' => is_array($order) ? ($order['type'] ?? null) : ($order->type ?? null),
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Confirmed tours drop every jobsheet on the tour, not only the removed order.
     *
     * @param  object|array<string, mixed>  $order
     */
    private function deleteAllForTour($order): int
    {
        $tourId = $this->tourId($order);
        $ids = $this->idVariants($tourId);
        if ($ids === []) {
            return $this->deleteForThisOrder($order);
        }

        $deleted = (int) Jobsheet::query()->whereIn('tour_id', $ids)->delete();

        if ($deleted > 0) {
            Log::info('Removed jobsheets for confirmed tour', [
                'tour_id' => $tourId,
                'booking_id' => is_array($order) ? ($order['booking_id'] ?? null) : ($order->booking_id ?? null),
                'type' => is_array($order) ? ($order['type'] ?? null) : ($order->type ?? null),
                'jobsheets' => $deleted,
            ]);
        }

        return $deleted;
    }

    /**
     * Definite, Actual, and every other status: this order only, and only when it owns a jobsheet.
     *
     * @param  object|array<string, mixed>  $order
     */
    private function deleteForThisOrder($order): int
    {
        $type = strtolower(trim((string) (is_array($order) ? ($order['type'] ?? '') : ($order->type ?? ''))));
        $payload = $this->firstPayload($order);

        if (! $this->shouldRemove($type, $payload)) {
            return 0;
        }

        $bookingId = is_array($order) ? ($order['booking_id'] ?? null) : ($order->booking_id ?? null);
        $ids = $this->idVariants($bookingId);
        if ($ids === []) {
            return 0;
        }

        $deleted = (int) Jobsheet::query()->whereIn('order_id', $ids)->delete();

        if ($deleted > 0) {
            Log::info('Removed jobsheets for deleted service', [
                'booking_id' => $bookingId,
                'tour_id' => $this->tourId($order),
                'tour_status' => $this->tourStatus($order),
                'type' => $type,
                'jobsheets' => $deleted,
            ]);
        }

        return $deleted;
    }

    /**
     * @param  object|array<string, mixed>  $order
     */
    private function tourStatus($order): ?string
    {
        $status = null;

        if (is_object($order) && method_exists($order, 'relationLoaded') && $order->relationLoaded('tour')) {
            $tour = $order->getRelation('tour');
            if ($tour) {
                $status = $tour->tour_status ?? null;
            }
        }

        if ($status === null || $status === '') {
            $tourId = $this->tourId($order);
            if ($tourId === null || $tourId === '') {
                return null;
            }

            $status = Tour::withTrashed()->where('tour_id', $tourId)->value('tour_status');
        }

        if ($status === null || $status === '') {
            return null;
        }

        return strtolower(trim((string) $status));
    }

    /**
     * @param  object|array<string, mixed>  $order
     */
    private function tourId($order)
    {
        if (is_object($order)) {
            if (method_exists($order, 'relationLoaded') && $order->relationLoaded('tour')) {
                $tour = $order->getRelation('tour');
                $fromTour = $tour->tour_id ?? null;
                if ($fromTour !== null && $fromTour !== '') {
                    return $fromTour;
                }
            }

            return $order->tour_id ?? null;
        }

        if (is_array($order)) {
            return $order['tour_id'] ?? null;
        }

        return null;
    }

    /**
     * @return list<int|string>
     */
    private function idVariants($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return array_values(array_unique(array_filter([
            $value,
            (string) $value,
            is_numeric($value) ? (int) $value : null,
        ], static fn ($item) => $item !== null && $item !== '')));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function shouldRemove(string $type, array $payload): bool
    {
        if (in_array($type, self::TRANSPORT_TYPES, true) || in_array($type, self::GUIDE_TYPES, true)) {
            return true;
        }

        if (! in_array($type, ['attraction', 'restaurant'], true)) {
            return false;
        }

        return $this->hasNestedTransfer($payload) || $this->hasNestedGuide($payload);
    }

    /**
     * @param  object|array<string, mixed>  $order
     * @return array<string, mixed>
     */
    private function firstPayload($order): array
    {
        $data = is_array($order) ? ($order['data'] ?? null) : ($order->data ?? null);
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($data)) {
            return [];
        }
        if (isset($data[0]) && is_array($data[0])) {
            return $data[0];
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function hasNestedTransfer(array $payload): bool
    {
        $transfer = $payload['transfer_options'] ?? null;
        if (! is_array($transfer)) {
            return false;
        }

        if ($this->isYes($transfer['transfer_required'] ?? null)) {
            return true;
        }

        if (! empty($transfer['vehicle_id']) || ! empty($transfer['vehicles_id'])) {
            return true;
        }

        return $this->positiveAmount($transfer['cost'] ?? null)
            || $this->positiveAmount($transfer['total_price'] ?? null)
            || $this->positiveAmount($transfer['base_cost'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function hasNestedGuide(array $payload): bool
    {
        $guide = $payload['guide_options'] ?? null;
        if (! is_array($guide)) {
            return false;
        }

        if ($this->isYes($guide['guide_required'] ?? null)) {
            return true;
        }

        $guideId = $guide['guide_id'] ?? null;
        if ($guideId !== null && $guideId !== '' && (string) $guideId !== '0') {
            return true;
        }

        return $this->positiveAmount($guide['total_price'] ?? null)
            || $this->positiveAmount($guide['price'] ?? null);
    }

    private function positiveAmount($value): bool
    {
        return is_numeric($value) && (float) $value > 0;
    }

    private function isYes($value): bool
    {
        if ($value === true || $value === 1 || $value === '1') {
            return true;
        }

        return is_string($value) && strtolower(trim($value)) === 'yes';
    }
}
