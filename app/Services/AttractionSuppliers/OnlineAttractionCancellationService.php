<?php

namespace App\Services\AttractionSuppliers;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Cancels an approved online attraction voucher with the supplier when the order
 * is removed from Lite/Pro edit (or booking reject). Credits are refunded by the
 * supplier cancel-voucher API — same path as the booking Cancel Voucher modal.
 */
class OnlineAttractionCancellationService
{
    public function __construct(
        private OnlineAttractionOrderService $orderService,
    ) {}

    /**
     * Call the supplier cancel-voucher API when this is an online attraction with a voucher.
     * Returns null when no supplier cancel is needed (offline / not yet paid).
     *
     * @return array{success: bool, message: ?string, provider_status: ?int, provider: mixed, voucher_code: ?string}|null
     */
    public function cancelIfApplicable(Order $order, ?string $cancelReason = null): ?array
    {
        if (($order->type ?? '') !== 'attraction') {
            return null;
        }

        $booking = $this->primaryBookingPayload($order);
        $isOnline = strtolower(trim((string) ($order->order_type ?? ''))) === 'online'
            || OnlineAttractionOrderService::isOnlineAttraction($booking);

        if (! $isOnline) {
            return null;
        }

        if (! empty($booking['voucher_cancelled'])) {
            return [
                'success' => true,
                'message' => 'Voucher was already cancelled.',
                'provider_status' => null,
                'provider' => null,
                'voucher_code' => OnlineAttractionOrderService::extractPrimaryVoucherCode($booking),
            ];
        }

        $voucherCode = OnlineAttractionOrderService::extractPrimaryVoucherCode($booking);
        if ($voucherCode === null || $voucherCode === '') {
            // Online but not yet paid / no voucher — local soft-delete only.
            return null;
        }

        $reason = trim((string) ($cancelReason ?? ''));
        if ($reason === '') {
            $reason = 'Removed from tour edit form';
        }

        $result = $this->orderService->cancelVoucher($voucherCode, $reason, $booking);

        if (empty($result['success'])) {
            throw new RuntimeException(
                $result['message'] ?: 'Failed to cancel the online attraction voucher with the supplier.'
            );
        }

        $booking['voucher_cancelled'] = true;
        $booking['voucher_cancel_reason'] = $reason;
        $booking['voucher_cancelled_at'] = now()->toIso8601String();
        $booking['voucher_cancel_response'] = [
            'message' => $result['message'] ?? null,
            'provider_status' => $result['provider_status'] ?? null,
        ];

        $this->writeBookingPayload($order, $booking);

        Log::info('Online attraction voucher cancelled with supplier (edit remove)', [
            'order_id' => $order->id,
            'booking_id' => $order->booking_id,
            'voucher_code' => $voucherCode,
            'message' => $result['message'] ?? null,
        ]);

        return array_merge($result, ['voucher_code' => $voucherCode]);
    }

    /**
     * @return array<string, mixed>
     */
    private function primaryBookingPayload(Order $order): array
    {
        $data = $order->data ?? null;
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
     * @param  array<string, mixed>  $booking
     */
    private function writeBookingPayload(Order $order, array $booking): void
    {
        $data = $order->data ?? null;
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($data)) {
            $data = [];
        }

        if (isset($data[0]) && is_array($data[0])) {
            $data[0] = $booking;
        } else {
            $data = [$booking];
        }

        $order->data = $data;
        $order->save();
    }
}
