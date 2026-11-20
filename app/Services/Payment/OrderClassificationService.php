<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Tour;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Server-side Online / Offline / Mix classification from persisted order_type.
 */
class OrderClassificationService
{
    public const ONLINE = 'online';

    public const OFFLINE = 'offline';

    public const MIX = 'mix';

    public const EMPTY = 'empty';

    /**
     * Classify a tour from trusted orders.order_type values.
     *
     * Null/blank order_type is treated as offline for backward compatibility.
     */
    public function classifyTour(Tour|int $tour): string
    {
        $tourId = $tour instanceof Tour
            ? (int) ($tour->tour_id ?? $tour->getKey())
            : (int) $tour;

        $types = Order::query()
            ->where('tour_id', $tourId)
            ->pluck('order_type');

        return $this->classifyFromTypes($types);
    }

    /**
     * @param  Collection<int, mixed>|array<int, mixed>  $types
     */
    public function classifyFromTypes(Collection|array $types): string
    {
        $raw = collect($types);
        if ($raw->isEmpty()) {
            return self::EMPTY;
        }

        // Blank order_type is offline. An online row plus a blank row is a mix,
        // so the blank service stays in negotiation and is not paid as online.
        $normalized = $raw->map(fn ($t) => $this->normalizeType($t));
        $hasOnline = $normalized->contains(self::ONLINE);
        $hasOffline = $normalized->contains(fn ($t) => $t !== self::ONLINE);

        if ($hasOnline && $hasOffline) {
            return self::MIX;
        }

        if ($hasOnline) {
            return self::ONLINE;
        }

        return self::OFFLINE;
    }

    public function persistClassification(Tour $tour): string
    {
        $classification = $this->classifyTour($tour);
        $tourId = (int) ($tour->tour_id ?? $tour->getKey());

        // Targeted update only — avoid $tour->save() which can write dirty attrs
        // for columns missing locally (e.g. previous_exchange_rate).
        if ($tourId > 0) {
            Tour::query()
                ->where('tour_id', $tourId)
                ->update(['order_classification' => $classification]);
        }

        $tour->setAttribute('order_classification', $classification);
        $tour->syncOriginalAttribute('order_classification');

        return $classification;
    }

    /**
     * Sum quoted amounts for online vs offline items using order cost fields when present.
     *
     * @return array{classification: string, online_amount: float, offline_amount: float, total_amount: float, currency: string|null}
     */
    public function amountBreakdown(Tour $tour): array
    {
        $tourId = (int) ($tour->tour_id ?? $tour->getKey());
        $orders = Order::query()->where('tour_id', $tourId)->get();

        $online = 0.0;
        $offline = 0.0;

        foreach ($orders as $order) {
            $amount = $this->resolveOrderAmount($order);
            $type = $this->normalizeType($order->order_type) ?? self::OFFLINE;
            if ($type === self::ONLINE) {
                $online += $amount;
            } else {
                $offline += $amount;
            }
        }

        $total = round($online + $offline, 2);
        if ($total <= 0 && is_numeric($tour->selling_price ?? null)) {
            $total = round((float) $tour->selling_price, 2);
            if ($offline <= 0 && $online <= 0) {
                $offline = $total;
            }
        }

        return [
            'classification' => $this->classifyFromTypes($orders->pluck('order_type')),
            'online_amount' => round($online, 2),
            'offline_amount' => round($offline, 2),
            'total_amount' => $total,
            'currency' => $this->resolveTourCurrency($tour),
        ];
    }

    /**
     * The tour is priced in its orders' currency. tours.payment_received_currency defaults to
     * 'USD' in the database, so it is only a fallback (using it first converted SGD tours as USD).
     */
    public function resolveTourCurrency(Tour $tour): string
    {
        $tourId = (int) ($tour->tour_id ?? 0);
        if ($tourId > 0) {
            $orderCurrencies = Order::query()->where('tour_id', $tourId)->pluck('currency')
                ->map(fn ($c) => strtoupper(trim((string) $c)))->filter()->unique();
            if ($orderCurrencies->count() === 1 && preg_match('/^[A-Z]{3}$/', (string) $orderCurrencies->first())) {
                return (string) $orderCurrencies->first();
            }
        }

        foreach ([
            $tour->payment_received_currency ?? null,
            $tour->user_currency ?? null,
            $tour->currency ?? null,
        ] as $candidate) {
            $code = strtoupper(trim((string) $candidate));
            if (preg_match('/^[A-Z]{3}$/', $code)) {
                return $code;
            }
        }

        return 'SGD';
    }

    /**
     * Online services that still have to be paid for.
     *
     * Paid = booked (is_approve), on an open payment hold, or covered by an online payment
     * taken before the hold flow existed (order created before that payment). A service whose
     * hold share was released (could not be booked) is unpaid again. Services added to a tour
     * that was already paid are unpaid → only they are charged on the next Pay Now.
     *
     * @return Collection<int, Order>
     */
    public function unpaidOnlineOrders(Tour|int $tour): Collection
    {
        $tourId = $tour instanceof Tour ? (int) ($tour->tour_id ?? $tour->getKey()) : (int) $tour;
        if ($tourId <= 0) {
            return collect();
        }

        $orders = Order::query()
            ->where('tour_id', $tourId)
            ->whereRaw("LOWER(TRIM(COALESCE(order_type, ''))) = 'online'")
            ->where(fn ($q) => $q->whereNull('is_approve')->orWhere('is_approve', false))
            ->orderBy('id')
            ->get();
        if ($orders->isEmpty() || ! Schema::hasTable('payment_attempts')) {
            return $orders;
        }

        $onHold = [];
        $legacyPaidAt = null;
        $holdUuids = [];
        $attempts = DB::table('payment_attempts')
            ->where('tour_id', $tourId)
            ->where('payment_stage', 'supplier_initial')
            ->whereIn('status', ['authorized', 'completed'])
            ->get(['attempt_uuid', 'status', 'metadata', 'created_at']);
        foreach ($attempts as $attempt) {
            $meta = json_decode((string) $attempt->metadata, true) ?: [];
            $isHold = ! empty($meta['hold_opened_at']) || ! empty($meta['hold_mode']);
            if ($isHold) {
                $holdUuids[(string) $attempt->attempt_uuid] = true;
                if ($attempt->status === 'authorized' && empty($meta['hold_settled_at'])) {
                    foreach (array_keys((array) ($meta['hold_shares'] ?? [])) as $orderId) {
                        $onHold[(int) $orderId] = true;
                    }
                }

                continue;
            }
            if ($attempt->status === 'completed') {
                $legacyPaidAt = max($legacyPaidAt ?? '', (string) $attempt->created_at);
            }
        }

        // Verified online payments recorded outside the hold flow (older tours).
        $tourRow = $tour instanceof Tour ? $tour : Tour::query()->where('tour_id', $tourId)->first(['payment_details']);
        $details = json_decode((string) ($tourRow->payment_details ?? '[]'), true) ?: [];
        foreach (is_array($details) ? $details : [] as $row) {
            if (! is_array($row) || (int) ($row['status'] ?? 0) !== 1
                || strtolower(trim((string) ($row['payment_for'] ?? ''))) !== self::ONLINE) {
                continue;
            }
            if (isset($holdUuids[(string) ($row['payment_attempt_uuid'] ?? '')])) {
                continue;
            }
            $raw = (string) ($row['date'] ?? $row['payment_date'] ?? '');
            $at = $raw !== '' ? rescue(fn () => \Carbon\Carbon::parse($raw), null, false) : null;
            if ($at) {
                // Date-only rows cover the whole day.
                $date = strlen(trim($raw)) <= 10 ? $at->endOfDay() : $at;
                $legacyPaidAt = max($legacyPaidAt ?? '', $date->format('Y-m-d H:i:s'));
            }
        }

        return $orders->reject(function (Order $order) use ($onHold, $legacyPaidAt) {
            if (isset($onHold[(int) $order->getKey()])) {
                return true;
            }

            return $legacyPaidAt !== null && $order->created_at !== null
                && $order->created_at->format('Y-m-d H:i:s') <= substr($legacyPaidAt, 0, 19);
        })->values();
    }

    public function unpaidOnlineAmount(Tour|int $tour): float
    {
        return round((float) $this->unpaidOnlineOrders($tour)->sum(fn (Order $o) => $this->resolveOrderAmount($o)), 2);
    }

    public function resolvePaymentStage(Tour $tour, ?string $classification = null): string
    {
        $classification ??= (string) ($tour->order_classification ?: $this->classifyTour($tour));
        $status = (string) ($tour->tour_status ?? '');

        // Online services added after an earlier payment (or released when they could not be
        // booked) are paid on their own before anything else — even on a confirmed tour.
        if (in_array($classification, [self::ONLINE, self::MIX], true) && $this->unpaidOnlineAmount($tour) > 0.009) {
            return 'supplier_initial';
        }

        $confirmedLike = in_array($status, ['Confirmed', 'Definite', 'Actual'], true);

        if ($classification === self::ONLINE) {
            return $confirmedLike ? 'dmc_collection' : 'supplier_initial';
        }

        if ($classification === self::MIX) {
            if ($confirmedLike) {
                return 'dmc_collection';
            }
            $stored = (string) ($tour->payment_stage ?? '');
            if ($stored === 'offline_negotiation' || $this->supplierInitialCompleted($tour)) {
                return 'offline_negotiation';
            }

            return 'supplier_initial';
        }

        // Offline: collection is always DMC-directed once payment is taken
        return 'dmc_collection';
    }

    public function resolvePaymentRecipient(string $stage): string
    {
        if ($stage === 'offline_negotiation') {
            return 'none';
        }

        return $stage === 'supplier_initial'
            ? (string) config('payment.recipients.travclicks', 'travclicks')
            : (string) config('payment.recipients.dmc', 'dmc');
    }

    /**
     * What a payment covers on the booking: online services or offline services.
     * Mix: supplier_initial pays online first; later dmc_collection pays offline.
     */
    public function resolvePaymentFor(?string $classification, ?string $stage): string
    {
        $classification = strtolower(trim((string) $classification));
        $stage = strtolower(trim((string) $stage));

        if ($stage === 'supplier_initial' || $classification === self::ONLINE) {
            return self::ONLINE;
        }

        return self::OFFLINE;
    }

    public function supplierInitialCompleted(Tour $tour): bool
    {
        if ((string) ($tour->payment_stage ?? '') === 'offline_negotiation') {
            return true;
        }

        $tourId = (int) ($tour->tour_id ?? $tour->getKey());
        if ($tourId <= 0 || ! Schema::hasTable('payment_attempts')) {
            return false;
        }

        return DB::table('payment_attempts')
            ->where('tour_id', $tourId)
            ->where('payment_stage', 'supplier_initial')
            ->where('status', 'completed')
            ->exists();
    }

    protected function normalizeType(mixed $type): ?string
    {
        $value = strtolower(trim((string) $type));
        if ($value === '') {
            return null;
        }

        return in_array($value, [self::ONLINE, self::OFFLINE], true) ? $value : null;
    }

    /**
     * Agent-facing sell total. Order rows store this on each service as totalPrice.
     * cost_price is often 0 and must not hide that total.
     */
    public function resolveOrderAmount(Order $order): float
    {
        $data = $order->data;
        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }

        $fromServices = is_array($data) ? $this->sumServiceSellAmount($data) : 0.0;
        if ($fromServices > 0) {
            return round($fromServices, 2);
        }

        if (isset($order->cost_price) && is_numeric($order->cost_price) && (float) $order->cost_price > 0) {
            return round((float) $order->cost_price, 2);
        }

        return 0.0;
    }

    /**
     * @param  array<string|int, mixed>  $data
     */
    public function sumServiceSellAmount(array $data): float
    {
        if ($data === []) {
            return 0.0;
        }

        $isList = array_is_list($data);
        if ($isList) {
            $sum = 0.0;
            foreach ($data as $item) {
                if (! is_array($item)) {
                    continue;
                }
                if (isset($item['totalPrice']) && is_numeric($item['totalPrice']) && (float) $item['totalPrice'] > 0) {
                    $sum += (float) $item['totalPrice'];
                } elseif (isset($item['price']) && is_numeric($item['price']) && (float) $item['price'] > 0) {
                    $sum += (float) $item['price'];
                } elseif (isset($item['grand_total']) && is_numeric($item['grand_total']) && (float) $item['grand_total'] > 0) {
                    $sum += (float) $item['grand_total'];
                }
                if (isset($item['transfer_options']) && is_array($item['transfer_options'])) {
                    $sum += (float) ($item['transfer_options']['totalPrice'] ?? $item['transfer_options']['cost'] ?? 0);
                }
                if (isset($item['guide_options']) && is_array($item['guide_options'])) {
                    $sum += (float) ($item['guide_options']['sell'] ?? $item['guide_options']['total_price'] ?? 0);
                }
            }

            return round($sum, 2);
        }

        foreach (['selling_price', 'totalPrice', 'total_price', 'grand_total', 'price', 'amount', 'net_price'] as $key) {
            if (isset($data[$key]) && is_numeric($data[$key]) && (float) $data[$key] > 0) {
                return round((float) $data[$key], 2);
            }
        }

        return 0.0;
    }
}
