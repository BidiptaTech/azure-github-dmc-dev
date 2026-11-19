<?php

namespace App\Services\Payment;

use App\Http\Controllers\HotelBookingController;
use App\Models\User;
use App\Services\AttractionSuppliers\OnlineAttractionOrderService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * After an online payment is HELD (wallet reservation / Stripe authorisation), book every paid
 * online service with its vendor one by one — no manual "Approve" dialog.
 *
 *  - Hotel: live recheck → book only if the price is not higher than quoted → auto-approved with
 *           free-cancel date (day before the supplier's first penalty date) and display due D-N.
 *  - Attraction: create/pay with the vendor → auto-approved (free-cancel = tour auto-cancel date).
 *  - A service that cannot be booked is recorded as failed so its share of the hold is released.
 *  - Finally the hold settles: booked services are charged, the rest is released.
 *
 * Booking reuses HotelBookingController's existing approve actions in-process, so vendor booking,
 * commission credit, vendor-credit debit, hotel e-mail and hold deduction are exactly the same as a
 * manual approve. Runs as the user who paid (API environment / permissions follow that user).
 */
class OnlineServiceAutoBookingService
{
    public function __construct(
        protected PaymentOrchestrator $orchestrator,
    ) {}

    /**
     * @return array{state: string, booked: int, failed: int, results: array<int, array<string, mixed>>}
     */
    public function run(int $tourId, string $attemptUuid, ?int $userId = null): array
    {
        $empty = ['state' => 'skipped', 'booked' => 0, 'failed' => 0, 'results' => []];
        if (! config('payment.online_hold.auto_book', true)) {
            return $empty;
        }

        @set_time_limit(900);
        $lock = Cache::lock('online-auto-book:'.$tourId, 900);
        if (! $lock->get()) {
            return array_merge($empty, ['state' => 'already_running']);
        }

        $previousUser = Auth::user();
        try {
            $attempt = DB::table('payment_attempts')->where('attempt_uuid', $attemptUuid)->first();
            if (! $attempt || (int) $attempt->tour_id !== $tourId) {
                return $empty;
            }
            $meta = json_decode((string) $attempt->metadata, true) ?: [];
            $shares = (array) ($meta['hold_shares'] ?? []);
            if ($attempt->status !== 'authorized' || $shares === []) {
                return $empty;
            }

            $user = User::query()->where('userId', $userId ?: (int) ($meta['paid_by_user_id'] ?? 0))->first()
                ?? User::query()->where('userId', (int) $attempt->dmc_id)->first();
            if ($user) {
                Auth::setUser($user);
            }

            $results = [];
            foreach (array_keys($shares) as $orderId) {
                $orderId = (int) $orderId;
                $results[$orderId] = $this->bookOne($tourId, $orderId, (int) $attempt->id);
            }

            $booked = count(array_filter($results, fn ($r) => $r['status'] === 'booked'));
            $failed = count(array_filter($results, fn ($r) => $r['status'] === 'failed'));

            $fresh = DB::table('payment_attempts')->where('attempt_uuid', $attemptUuid)->value('metadata');
            $freshMeta = json_decode((string) $fresh, true) ?: [];
            $freshMeta['auto_booking'] = [
                'finished_at' => now()->toIso8601String(),
                'booked' => $booked,
                'failed' => $failed,
                'results' => $results,
            ];
            DB::table('payment_attempts')->where('attempt_uuid', $attemptUuid)->update([
                'metadata' => json_encode($freshMeta),
                'updated_at' => now(),
            ]);

            // Every service is now booked or failed → charge booked, release the rest.
            $settle = $this->orchestrator->syncOnlineHold($tourId);

            Log::info('online_auto_booking_finished', [
                'tour_id' => $tourId,
                'attempt' => $attemptUuid,
                'booked' => $booked,
                'failed' => $failed,
                'settle' => $settle,
            ]);

            return ['state' => 'done', 'booked' => $booked, 'failed' => $failed, 'results' => array_values($results), 'settle' => $settle];
        } catch (Throwable $e) {
            Log::error('online_auto_booking_failed', ['tour_id' => $tourId, 'attempt' => $attemptUuid, 'error' => $e->getMessage()]);

            return array_merge($empty, ['state' => 'error', 'error' => $e->getMessage()]);
        } finally {
            if ($previousUser) {
                Auth::setUser($previousUser);
            }
            $lock->release();
        }
    }

    /**
     * @return array{order_id: int, type: string, status: string, message: string}
     */
    protected function bookOne(int $tourId, int $orderId, int $attemptId): array
    {
        $order = DB::table('orders')->where('id', $orderId)->first();
        $type = strtolower((string) ($order->type ?? ''));
        $name = $this->serviceName($order);
        $out = fn (string $status, string $message) => ['order_id' => $orderId, 'type' => $type, 'name' => $name, 'status' => $status, 'message' => $message];

        if (! $order || ! empty($order->deleted_at)) {
            return $out('removed', 'Service was removed before booking.');
        }
        if ((bool) $order->is_approve) {
            return $out('booked', 'Already booked.');
        }
        if (DB::table('supplier_booking_outcomes')->where('payment_attempt_id', $attemptId)->where('order_id', $orderId)->exists()) {
            return $out('failed', 'Already processed for this payment.');
        }

        try {
            $result = match ($type) {
                'hotel' => $this->bookHotel($tourId, $order),
                'attraction' => $this->bookAttraction($tourId, $order),
                default => ['ok' => false, 'message' => 'Automatic booking is not available for this service type.'],
            };
        } catch (Throwable $e) {
            $result = ['ok' => false, 'message' => $e->getMessage()];
        }

        if ($result['ok']) {
            // Same references the manual approve stores — shown in the result window.
            $fresh = DB::table('orders')->where('id', $orderId)->first();
            $details = json_decode((string) ($fresh->booking_details ?? ''), true) ?: [];
            $supplierRef = \App\Services\SupplierCredit\SupplierCreditService::hotelSupplierReference($details)
                ?? (($fresh->order_ref_no ?? '') !== '' && ($fresh->order_ref_no ?? '') !== OnlineAttractionOrderService::PLACEHOLDER_ORDER_REF ? $fresh->order_ref_no : null);

            return $out('booked', $result['message'] ?? 'Booked with supplier.') + [
                'reference_id' => $fresh->reference_id ?? null,
                'supplier_reference' => $supplierRef,
                'actual_due_date' => $fresh->actual_due_date ?? null,
                'display_due_date' => $fresh->display_due_date ?? null,
            ];
        }

        $this->orchestrator->recordHoldServiceFailure($attemptId, $orderId, (string) $result['message']);
        Log::warning('online_auto_booking_service_failed', [
            'tour_id' => $tourId,
            'order_id' => $orderId,
            'type' => $type,
            'reason' => $result['message'],
        ]);

        return $out('failed', (string) $result['message']);
    }

    /**
     * @return array{ok: bool, message: string}
     */
    protected function bookHotel(int $tourId, object $order): array
    {
        $hotelOrderIndex = DB::table('orders')
            ->where('tour_id', $tourId)
            ->where('type', 'hotel')
            ->whereNull('deleted_at')
            ->where('id', '<', $order->id)
            ->count();
        $controller = app(HotelBookingController::class);

        $recheck = $this->json($controller->recheckOnlineHotelBooking($this->request([
            'tour_id' => $tourId,
            'hotel_order_index' => $hotelOrderIndex,
            'booking_index' => 0,
        ])));
        if (empty($recheck['success']) || empty($recheck['recheck_token'])) {
            return ['ok' => false, 'message' => 'Availability check failed: '.($recheck['message'] ?? 'no response')];
        }
        $data = (array) ($recheck['data'] ?? []);
        if (! empty($data['price_increased'])) {
            return ['ok' => false, 'message' => sprintf(
                'Supplier price increased (quoted %s %.2f) — not booked; amount released.',
                $data['currency'] ?? '',
                (float) ($data['stored_price'] ?? 0)
            )];
        }

        $freeCancel = $this->freeCancelFromPolicies((array) ($data['cancellation_policies'] ?? []))
            ?? $this->tourAutoCancelDate($tourId)
            ?? (! empty($data['check_in']) ? CarbonImmutable::parse($data['check_in'])->subDay() : null);
        if (! $freeCancel) {
            return ['ok' => false, 'message' => 'No free-cancellation date available for this hotel.'];
        }
        [$days, $display] = $this->displayDue($freeCancel, $tourId);

        $approve = $this->json($controller->approveHotelBooking($this->request([
            'tour_id' => $tourId,
            'hotel_order_index' => $hotelOrderIndex,
            'booking_index' => 0,
            'actual_due_date' => $freeCancel->format('Y-m-d'),
            'display_due_date_days' => $days,
            'display_due_date' => $display,
            'confirm_online_booking' => 1,
            'recheck_token' => (string) $recheck['recheck_token'],
        ])));

        return [
            'ok' => ! empty($approve['success']),
            'message' => (string) ($approve['message'] ?? 'Hotel booking failed.'),
        ];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    protected function bookAttraction(int $tourId, object $order): array
    {
        $attractionOrderIndex = DB::table('orders')
            ->where('tour_id', $tourId)
            ->where('type', 'attraction')
            ->whereNull('deleted_at')
            ->where('id', '<', $order->id)
            ->count();

        $data = json_decode((string) $order->data, true) ?: [];
        $first = is_array($data[0] ?? null) ? $data[0] : (is_array($data) ? $data : []);
        $visit = (string) ($first['date'] ?? $first['bookingDate'] ?? $first['booking_date'] ?? '');

        $freeCancel = $this->tourAutoCancelDate($tourId)
            ?? ($visit !== '' ? rescue(fn () => CarbonImmutable::parse(substr($visit, 0, 10))->subDay(), null, false) : null)
            ?? CarbonImmutable::today();
        [$days, $display] = $this->displayDue($freeCancel, $tourId);

        $approve = $this->json(app(HotelBookingController::class)->approveAttractionBooking($this->request([
            'tour_id' => $tourId,
            'attraction_order_index' => $attractionOrderIndex,
            'attraction_order_id' => (int) $order->id,
            'booking_index' => 0,
            'actual_due_date' => $freeCancel->format('Y-m-d'),
            'display_due_date_days' => $days,
            'display_due_date' => $display,
        ])));

        return [
            'ok' => ! empty($approve['success']),
            'message' => (string) ($approve['message'] ?? 'Attraction booking failed.'),
        ];
    }

    /**
     * Human label for the result window (hotel / attraction name).
     */
    protected function serviceName(?object $order): string
    {
        if (! $order) {
            return 'Service';
        }
        $data = json_decode((string) ($order->data ?? ''), true) ?: [];
        $row = is_array($data[0] ?? null) ? $data[0] : (is_array($data) ? $data : []);
        $name = $row['hotelDetails']['hotel_name']
            ?? $row['onlineHotelBooking']['hotel']['name']
            ?? $row['hotel_name']
            ?? $row['attractionName']
            ?? $row['attraction_name']
            ?? $row['name']
            ?? '';
        $name = trim(preg_replace('/\s*\(.*$/', '', (string) $name));

        return $name !== '' ? $name : ucfirst((string) ($order->type ?? 'Service'));
    }

    /**
     * Same rule as the approve dialog: free cancel = day before the earliest policy fromDate.
     *
     * @param  array<int, mixed>  $policies
     */
    protected function freeCancelFromPolicies(array $policies): ?CarbonImmutable
    {
        $dates = [];
        foreach ($policies as $p) {
            $d = substr((string) (is_array($p) ? ($p['fromDate'] ?? '') : ''), 0, 10);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                $dates[] = $d;
            }
        }
        if ($dates === []) {
            return null;
        }
        sort($dates);

        return CarbonImmutable::parse($dates[0])->subDay();
    }

    protected function tourAutoCancelDate(int $tourId): ?CarbonImmutable
    {
        $d = DB::table('tours')->where('tour_id', $tourId)->value('auto_cancel_date');

        return $d ? CarbonImmutable::parse($d) : null;
    }

    /**
     * D-N display due date (dd-mm-YYYY, same format as the approve dialog).
     *
     * @return array{0: int, 1: string}
     */
    protected function displayDue(CarbonImmutable $freeCancel, int $tourId = 0): array
    {
        $days = $this->dmcDisplayDueDays($tourId);

        return [$days, $freeCancel->subDays($days)->format('d-m-Y')];
    }

    /**
     * D-N chosen by the DMC in its profile (users.markup_json.online_display_due_days):
     * tour DMC → its master DMC → config default (1).
     */
    protected function dmcDisplayDueDays(int $tourId): int
    {
        $tour = $tourId > 0 ? DB::table('tours')->where('tour_id', $tourId)->first(['dmc_id', 'master_dmc_id']) : null;
        foreach ([$tour->dmc_id ?? null, $tour->master_dmc_id ?? null] as $dmcId) {
            if (! $dmcId) {
                continue;
            }
            $json = json_decode((string) DB::table('users')->where('userId', (int) $dmcId)->value('markup_json'), true) ?: [];
            $days = (int) ($json['online_display_due_days'] ?? 0);
            if ($days >= 1) {
                return $days;
            }
        }

        return max(1, (int) config('payment.online_hold.display_due_days_before', 1));
    }

    /**
     * @param  array<string, mixed>  $input
     */
    protected function request(array $input): Request
    {
        $request = Request::create('/internal/online-auto-book', 'POST', $input);
        $request->headers->set('Accept', 'application/json');
        if (Auth::user()) {
            $request->setUserResolver(fn () => Auth::user());
        }

        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    protected function json(mixed $response): array
    {
        if ($response instanceof JsonResponse) {
            return (array) $response->getData(true);
        }

        return [];
    }
}
