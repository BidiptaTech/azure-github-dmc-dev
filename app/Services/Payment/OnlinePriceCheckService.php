<?php

namespace App\Services\Payment;

use App\Http\Controllers\HotelBookingController;
use App\Models\Order;
use App\Models\Tour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Live price check BEFORE an online payment.
 *
 * Supplier prices move between enquiry and payment. Rather than taking the payment and then
 * failing the booking ("price increased — amount released"), every unpaid online service is
 * rechecked first. The payer sees quoted vs current price per service, accepts the change,
 * the order is re-priced and only then is the (new) amount held and the services booked.
 *
 * Only unpaid online services are checked/charged; services paid earlier are never touched.
 */
class OnlinePriceCheckService
{
    private const CACHE_PREFIX = 'online-price-check:';

    private const TTL_MINUTES = 20;

    private const TOLERANCE = 0.009;

    public function __construct(
        protected OrderClassificationService $classification,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function check(Tour $tour): array
    {
        $tourId = (int) ($tour->tour_id ?? $tour->getKey());
        $orders = $this->classification->unpaidOnlineOrders($tour);
        $currency = $this->onlineCurrency($tour);

        $items = [];
        foreach ($orders as $order) {
            $items[] = $this->checkOrder($tourId, $order, $currency);
        }

        $changed = collect($items)->contains(fn ($i) => in_array($i['status'], ['increased', 'decreased'], true));
        $result = [
            'tour_id' => $tourId,
            'currency' => $currency,
            'items' => $items,
            'old_total' => round((float) collect($items)->sum('old_amount'), 2),
            'new_total' => round((float) collect($items)->sum('new_amount'), 2),
            'changed' => $changed,
            'increased' => collect($items)->contains(fn ($i) => $i['status'] === 'increased'),
            'unverified' => collect($items)->contains(fn ($i) => $i['status'] === 'unverified'),
            // Nothing changed → already "accepted"; a change needs the payer's explicit OK.
            'accepted' => ! $changed,
            'checked_at' => now()->toIso8601String(),
            'user_id' => Auth::id(),
        ];
        $result['difference'] = round($result['new_total'] - $result['old_total'], 2);

        $token = Str::random(40);
        Cache::put(self::CACHE_PREFIX.$token, $result, now()->addMinutes(self::TTL_MINUTES));

        return $result + [
            'token' => $token,
            'requires_acceptance' => $changed,
            'expires_in_seconds' => self::TTL_MINUTES * 60,
        ];
    }

    /**
     * Payer accepted the current prices → re-price the orders so the payable becomes the new total.
     *
     * @return array<string, mixed>
     */
    public function accept(Tour $tour, string $token): array
    {
        $entry = $this->entry($tour, $token);
        if (! empty($entry['accepted'])) {
            return $entry;
        }

        DB::transaction(function () use ($entry) {
            foreach ($entry['items'] as $item) {
                if (! in_array($item['status'], ['increased', 'decreased'], true)) {
                    continue;
                }
                $this->applyPrice($item, $entry['currency']);
            }
        });

        $entry['accepted'] = true;
        $entry['accepted_at'] = now()->toIso8601String();
        Cache::put(self::CACHE_PREFIX.$token, $entry, now()->addMinutes(self::TTL_MINUTES));

        Log::info('online_price_change_accepted', [
            'tour_id' => $entry['tour_id'],
            'user_id' => Auth::id(),
            'old_total' => $entry['old_total'],
            'new_total' => $entry['new_total'],
            'currency' => $entry['currency'],
        ]);

        return $entry;
    }

    /**
     * Gate for Pay Now: a fresh, accepted price check covering exactly the services being paid.
     *
     * @return array<string, mixed>|null  Summary stored on the payment attempt (null = nothing to check)
     */
    public function assertConfirmed(Tour $tour, string $token, float $payable): ?array
    {
        if (! config('payment.online_hold.require_price_check', true)) {
            return null;
        }
        $unpaidIds = $this->classification->unpaidOnlineOrders($tour)
            ->map(fn (Order $o) => (int) $o->getKey())->sort()->values()->all();
        if ($unpaidIds === []) {
            return null;
        }

        if (trim($token) === '') {
            throw new RuntimeException('Please check the latest supplier prices before paying. Refresh the page and click Pay Now again.');
        }
        $entry = $this->entry($tour, $token);

        if (empty($entry['accepted'])) {
            throw new RuntimeException('Supplier prices have changed. Please review and accept the new prices before paying.');
        }
        $checkedIds = collect($entry['items'])->pluck('order_id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        if ($checkedIds !== $unpaidIds) {
            throw new RuntimeException('The services on this booking changed after the price check. Please click Pay Now again to re-check prices.');
        }
        if (abs((float) $entry['new_total'] - $payable) > 0.05) {
            throw new RuntimeException('The amount due no longer matches the checked prices. Please click Pay Now again to re-check prices.');
        }

        return [
            'checked_at' => $entry['checked_at'] ?? null,
            'accepted_at' => $entry['accepted_at'] ?? null,
            'currency' => $entry['currency'],
            'old_total' => $entry['old_total'],
            'new_total' => $entry['new_total'],
            'items' => array_map(fn ($i) => array_intersect_key($i, array_flip([
                'order_id', 'name', 'type', 'status', 'old_price', 'new_price', 'old_amount', 'new_amount',
            ])), $entry['items']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function entry(Tour $tour, string $token): array
    {
        $entry = $token !== '' ? Cache::get(self::CACHE_PREFIX.$token) : null;
        if (! is_array($entry) || (int) ($entry['tour_id'] ?? 0) !== (int) ($tour->tour_id ?? $tour->getKey())) {
            throw new RuntimeException('The price check has expired. Please click Pay Now again to re-check the latest prices.');
        }

        return $entry;
    }

    /**
     * @return array<string, mixed>
     */
    protected function checkOrder(int $tourId, Order $order, string $currency): array
    {
        $type = strtolower((string) ($order->type ?? ''));
        $amount = $this->classification->resolveOrderAmount($order);
        $base = [
            'order_id' => (int) $order->getKey(),
            'type' => $type,
            'name' => $this->serviceName($order),
            'currency' => strtoupper((string) ($order->currency ?: $currency)),
            'old_price' => $amount,
            'new_price' => $amount,
            'old_amount' => $amount,
            'new_amount' => $amount,
            'difference' => 0.0,
            'status' => 'unchanged',
            'message' => null,
        ];

        if ($type !== 'hotel') {
            // Online attractions are sold at a fixed rate — booked at the quoted price.
            return $base;
        }

        try {
            $index = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'hotel')
                ->whereNull('deleted_at')
                ->where('id', '<', $order->getKey())
                ->count();
            $response = app(HotelBookingController::class)->recheckOnlineHotelBooking($this->request([
                'tour_id' => $tourId,
                'hotel_order_index' => $index,
                'booking_index' => 0,
            ]));
            $json = $response instanceof JsonResponse ? (array) $response->getData(true) : [];
        } catch (Throwable $e) {
            $json = ['success' => false, 'message' => $e->getMessage()];
        }

        if (empty($json['success'])) {
            return array_merge($base, [
                'status' => 'unverified',
                'message' => 'Could not confirm the live price/availability: '.($json['message'] ?? 'no response from supplier')
                    .'. If it cannot be booked after payment, its amount is released back.',
            ]);
        }

        $data = (array) ($json['data'] ?? []);
        $row = $this->firstRow($order);
        $old = (float) ($data['stored_price'] ?? 0) ?: (float) ($row['totalPrice'] ?? $row['price'] ?? 0);
        $new = $this->currentPrice($data, $row, $old);
        $supplierNow = (float) (($data['supplier_gross_price'] ?? 0) ?: ($data['supplier_net_price'] ?? 0));
        if ($new === null || $old <= 0) {
            return array_merge($base, ['currency' => strtoupper((string) ($data['currency'] ?? $base['currency']))]);
        }

        $diff = round($new - $old, 2);
        $status = abs($diff) <= self::TOLERANCE ? 'unchanged' : ($diff > 0 ? 'increased' : 'decreased');
        if ($status === 'unchanged') {
            $diff = 0.0;
        }

        return array_merge($base, [
            'currency' => strtoupper((string) ($data['currency'] ?? $base['currency'])),
            'old_price' => round($old, 2),
            'new_price' => round($old + $diff, 2),
            'new_amount' => round($amount + $diff, 2),
            'difference' => $diff,
            'status' => $status,
            'supplier_price' => $supplierNow > 0 ? round($supplierNow, 2) : null,
            'check_in' => $data['check_in'] ?? null,
            'check_out' => $data['check_out'] ?? null,
            'room_name' => $data['room_name'] ?? null,
            'message' => match ($status) {
                'increased' => 'The supplier has increased the price for this hotel.',
                'decreased' => 'The supplier has lowered the price for this hotel.',
                default => null,
            },
        ]);
    }

    /**
     * Customer-facing price right now, on the same basis as the quoted one.
     *
     * @param  array<string, mixed>  $data  recheck response data
     * @param  array<string, mixed>  $row  stored booking row
     */
    protected function currentPrice(array $data, array $row, float $old): ?float
    {
        if (! empty($data['markup_applied']) && (float) ($data['customer_price'] ?? 0) > 0) {
            return (float) $data['customer_price'];
        }

        // No markup context: pass the supplier's own change through to the quoted price.
        $supplierNow = (float) (($data['supplier_gross_price'] ?? 0) ?: ($data['supplier_net_price'] ?? 0));
        $revisions = (array) ($row['price_revisions'] ?? []);
        $last = end($revisions);
        $storedSupplier = is_array($last) && (float) ($last['supplier_price'] ?? 0) > 0
            ? (float) $last['supplier_price']
            : (float) ($data['stored_supplier_price'] ?? 0);
        if ($supplierNow > 0 && $storedSupplier > 0) {
            return $old + ($supplierNow - $storedSupplier);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function applyPrice(array $item, string $currency): void
    {
        $order = Order::query()->whereKey((int) $item['order_id'])->lockForUpdate()->first();
        if (! $order) {
            throw new RuntimeException('A service was removed after the price check. Please click Pay Now again.');
        }
        if ((bool) $order->is_approve) {
            throw new RuntimeException('A service was already booked after the price check. Please click Pay Now again.');
        }

        $data = $order->data;
        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }
        $isList = is_array($data) && array_is_list($data) && is_array($data[0] ?? null);
        $row = $isList ? $data[0] : (is_array($data) ? $data : []);

        $current = (float) ($row['totalPrice'] ?? $row['price'] ?? 0);
        if (abs($current - (float) $item['old_price']) > self::TOLERANCE) {
            throw new RuntimeException('The price of '.$item['name'].' changed again. Please click Pay Now again to see the latest price.');
        }

        $new = round((float) $item['new_price'], 2);
        $row['totalPrice'] = $new;
        foreach (['price', 'grand_total'] as $key) {
            if (array_key_exists($key, $row)) {
                $row[$key] = $new;
            }
        }
        foreach (['price_payload', 'helperPriceResult'] as $nest) {
            if (is_array($row[$nest] ?? null) && array_key_exists('grand_total', $row[$nest])) {
                $row[$nest]['grand_total'] = $new;
            }
        }
        $row['price_revisions'] = array_values(array_merge((array) ($row['price_revisions'] ?? []), [[
            'reason' => 'supplier_recheck_before_payment',
            'old_price' => round((float) $item['old_price'], 2),
            'new_price' => $new,
            'difference' => round($new - (float) $item['old_price'], 2),
            'currency' => $item['currency'] ?? $currency,
            'supplier_price' => $item['supplier_price'] ?? null,
            'accepted_by' => Auth::id(),
            'accepted_at' => now()->toIso8601String(),
        ]]));

        if ($isList) {
            $data[0] = $row;
        } else {
            $data = $row;
        }
        $order->data = $data;
        $order->save(); // re-snapshots cost_price from the new stay total
    }

    /**
     * @return array<string, mixed>
     */
    protected function firstRow(Order $order): array
    {
        $data = $order->data;
        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }
        if (! is_array($data)) {
            return [];
        }

        return is_array($data[0] ?? null) ? $data[0] : $data;
    }

    protected function serviceName(Order $order): string
    {
        $row = $this->firstRow($order);
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

    protected function onlineCurrency(Tour $tour): string
    {
        $fallback = $this->classification->resolveTourCurrency($tour);

        return strtoupper(app(AcceptanceFlowService::class)->resolveOnlineCurrency($tour, $fallback));
    }

    /**
     * @param  array<string, mixed>  $input
     */
    protected function request(array $input): Request
    {
        $request = Request::create('/internal/online-price-check', 'POST', $input);
        $request->headers->set('Accept', 'application/json');
        if (Auth::user()) {
            $request->setUserResolver(fn () => Auth::user());
        }

        return $request;
    }
}
