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

        $tour->forceFill([
            'order_classification' => $classification,
        ])->save();

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

        $total = round($online + $offline, 4);
        if ($total <= 0 && is_numeric($tour->selling_price ?? null)) {
            $total = round((float) $tour->selling_price, 4);
            if ($offline <= 0 && $online <= 0) {
                $offline = $total;
            }
        }

        return [
            'classification' => $this->classifyFromTypes($orders->pluck('order_type')),
            'online_amount' => round($online, 4),
            'offline_amount' => round($offline, 4),
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

    public function resolvePaymentStage(Tour $tour, ?string $classification = null): string
    {
        $classification ??= (string) ($tour->order_classification ?: $this->classifyTour($tour));
        $status = (string) ($tour->tour_status ?? '');

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
            return round($fromServices, 4);
        }

        if (isset($order->cost_price) && is_numeric($order->cost_price) && (float) $order->cost_price > 0) {
            return round((float) $order->cost_price, 4);
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

            return round($sum, 4);
        }

        foreach (['selling_price', 'totalPrice', 'total_price', 'grand_total', 'price', 'amount', 'net_price'] as $key) {
            if (isset($data[$key]) && is_numeric($data[$key]) && (float) $data[$key] > 0) {
                return round((float) $data[$key], 4);
            }
        }

        return 0.0;
    }
}
