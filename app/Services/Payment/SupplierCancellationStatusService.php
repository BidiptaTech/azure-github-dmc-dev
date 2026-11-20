<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Supplier cancel lifecycle for online refunds.
 *
 * Statuses:
 * - not_required: no supplier booking to cancel
 * - pending: cancel requested / awaiting confirmation
 * - failed: supplier API failed (no wallet credit until fixed)
 * - confirmed: supplier confirmed cancellation
 * - resolved_manual: admin force-resolved with audit reason (allows wallet credit)
 */
class SupplierCancellationStatusService
{
    public const NOT_REQUIRED = 'not_required';

    public const PENDING = 'pending';

    public const FAILED = 'failed';

    public const CONFIRMED = 'confirmed';

    public const RESOLVED_MANUAL = 'resolved_manual';

    /**
     * @return array{
     *   status: string,
     *   message: string,
     *   supplier_code: string,
     *   updated_at: string|null,
     *   resolved_reason: string|null,
     *   resolved_by: string|null,
     *   resolved_by_user_id: int|null,
     *   attempts: int,
     *   last_error: string|null
     * }
     */
    public function read(Order $order): array
    {
        $stored = $this->extractBlock($order);

        return [
            'status' => (string) ($stored['status'] ?? self::NOT_REQUIRED),
            'message' => (string) ($stored['message'] ?? ''),
            'supplier_code' => (string) ($stored['supplier_code'] ?? ''),
            'updated_at' => isset($stored['updated_at']) ? (string) $stored['updated_at'] : null,
            'resolved_reason' => isset($stored['resolved_reason']) ? (string) $stored['resolved_reason'] : null,
            'resolved_by' => isset($stored['resolved_by']) ? (string) $stored['resolved_by'] : null,
            'resolved_by_user_id' => isset($stored['resolved_by_user_id']) ? (int) $stored['resolved_by_user_id'] : null,
            'attempts' => (int) ($stored['attempts'] ?? 0),
            'last_error' => isset($stored['last_error']) ? (string) $stored['last_error'] : null,
        ];
    }

    public function statusOf(Order $order): string
    {
        $status = strtolower(trim($this->read($order)['status']));

        return $status !== '' ? $status : self::NOT_REQUIRED;
    }

    public function allowsWalletCredit(Order $order): bool
    {
        if (strtolower(trim((string) ($order->order_type ?? ''))) !== 'online') {
            return true;
        }

        return in_array($this->statusOf($order), [
            self::NOT_REQUIRED,
            self::CONFIRMED,
            self::RESOLVED_MANUAL,
        ], true);
    }

    public function isBlockingWalletCredit(Order $order): bool
    {
        return ! $this->allowsWalletCredit($order);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    public function markNotRequired(Order $order, string $message = '', array $extra = []): void
    {
        $this->write($order, array_merge($extra, [
            'status' => self::NOT_REQUIRED,
            'message' => $message !== '' ? $message : 'Supplier cancellation not required.',
        ]));
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    public function markPending(Order $order, string $message = '', array $extra = []): void
    {
        $this->write($order, array_merge($extra, [
            'status' => self::PENDING,
            'message' => $message !== '' ? $message : 'Supplier cancellation pending.',
        ], [
            'attempts' => $this->nextAttemptCount($order),
        ]));
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    public function markConfirmed(Order $order, string $message = '', array $extra = []): void
    {
        $this->write($order, array_merge($extra, [
            'status' => self::CONFIRMED,
            'message' => $message !== '' ? $message : 'Supplier cancellation confirmed.',
            'last_error' => null,
        ], [
            'attempts' => $this->nextAttemptCount($order),
        ]));
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    public function markFailed(Order $order, string $error, array $extra = []): void
    {
        $this->write($order, array_merge($extra, [
            'status' => self::FAILED,
            'message' => 'Supplier cancellation failed.',
            'last_error' => $error,
        ], [
            'attempts' => $this->nextAttemptCount($order),
        ]));
    }

    public function markResolvedManual(Order $order, string $reason): void
    {
        $reason = trim($reason);
        $user = Auth::user();

        $this->write($order, [
            'status' => self::RESOLVED_MANUAL,
            'message' => 'Admin marked supplier cancellation as resolved.',
            'resolved_reason' => $reason,
            'resolved_by' => $user ? (string) ($user->name ?? '') : null,
            'resolved_by_user_id' => $user ? (int) ($user->userId ?? $user->id ?? 0) : null,
            'last_error' => $this->read($order)['last_error'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $block
     */
    public function write(Order $order, array $block): void
    {
        $existing = $this->extractBlock($order);
        $merged = array_merge($existing, $block, [
            'updated_at' => now()->toIso8601String(),
        ]);

        $type = strtolower(trim((string) ($order->type ?? '')));
        if ($type === 'attraction') {
            $this->writeAttractionBlock($order, $merged);
        } else {
            $this->writeBookingDetailsBlock($order, $merged);
        }

        Log::info('Supplier cancellation status updated', [
            'order_id' => $order->id,
            'booking_id' => $order->booking_id,
            'type' => $order->type ?? null,
            'status' => $merged['status'] ?? null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractBlock(Order $order): array
    {
        $type = strtolower(trim((string) ($order->type ?? '')));
        if ($type === 'attraction') {
            $payload = $this->primaryAttractionPayload($order);
            $block = is_array($payload['supplier_cancellation'] ?? null)
                ? $payload['supplier_cancellation']
                : [];

            return is_array($block) ? $block : [];
        }

        $details = $this->decodeBookingDetails($order->booking_details);
        $block = is_array($details['supplier_cancellation'] ?? null)
            ? $details['supplier_cancellation']
            : [];

        // Legacy: cancelled_at without explicit status ⇒ confirmed
        if ($block === [] && (! empty($details['cancelled_at']) || ! empty($details['cancellation']['cancelled_at']))) {
            return [
                'status' => self::CONFIRMED,
                'message' => 'Supplier cancellation confirmed.',
                'updated_at' => (string) ($details['cancelled_at'] ?? $details['cancellation']['cancelled_at'] ?? ''),
            ];
        }

        return is_array($block) ? $block : [];
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected function writeBookingDetailsBlock(Order $order, array $block): void
    {
        $details = $this->decodeBookingDetails($order->booking_details);
        $details['supplier_cancellation'] = $block;
        $encoded = json_encode($details);

        $persisted = false;
        if (! empty($order->id)) {
            $persisted = DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'booking_details' => $encoded,
                    'updated_at' => now(),
                ]) > 0;
        }

        $order->booking_details = $encoded;
        if (! $persisted) {
            $order->save();
        }
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected function writeAttractionBlock(Order $order, array $block): void
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
            $data[0]['supplier_cancellation'] = $block;
            // Keep legacy voucher flags in sync when confirmed/resolved
            if (in_array(($block['status'] ?? ''), [self::CONFIRMED, self::RESOLVED_MANUAL], true)) {
                $data[0]['voucher_cancelled'] = true;
                if (empty($data[0]['voucher_cancelled_at'])) {
                    $data[0]['voucher_cancelled_at'] = $block['updated_at'] ?? now()->toIso8601String();
                }
            }
        } else {
            $data['supplier_cancellation'] = $block;
            if (in_array(($block['status'] ?? ''), [self::CONFIRMED, self::RESOLVED_MANUAL], true)) {
                $data['voucher_cancelled'] = true;
            }
        }

        $order->data = $data;
        $order->save();
    }

    protected function nextAttemptCount(Order $order): int
    {
        return max(0, (int) ($this->read($order)['attempts'] ?? 0)) + 1;
    }

    /**
     * @return array<string, mixed>
     */
    protected function primaryAttractionPayload(Order $order): array
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
     * @return array<string, mixed>
     */
    protected function decodeBookingDetails(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
