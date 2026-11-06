<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Tour;
use App\Services\OnlinePricing\MarkupRule;

/**
 * Online: API cost + Travclicks commission = price the DMC sees.
 * DMC then adds their own commission = price the agent pays.
 * Offline: Travclicks commission is zero. Only the DMC commission is added.
 */
class CommissionBreakdownService
{
    public function __construct(
        protected OrderClassificationService $classificationService,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $supplierResults  Empty means every item is charged.
     * @return array{currency: string, lines: array<int, array<string, mixed>>, charged_amount: float, api_cost: float, travclicks_commission: float, dmc_commission: float, conversion_charge: float}
     */
    public function forTour(Tour $tour, array $supplierResults = []): array
    {
        $bookedOrderIds = $this->bookedOrderIds($supplierResults);
        $currency = $this->classificationService->resolveTourCurrency($tour);
        $tourId = (int) ($tour->tour_id ?? $tour->getKey());
        $orders = Order::query()->where('tour_id', $tourId)->get();

        $lines = [];
        foreach ($orders as $order) {
            if ($bookedOrderIds !== null && ! in_array((int) $order->getKey(), $bookedOrderIds, true)) {
                continue;
            }
            $line = $this->lineForOrder($order, $tour);
            if ($line['agent_price'] <= 0 && $line['dmc_commission'] <= 0) {
                continue;
            }
            $lines[] = $line;
        }

        return [
            'currency' => $currency,
            'lines' => $lines,
            'charged_amount' => round(array_sum(array_column($lines, 'charged_amount')), 4),
            'api_cost' => round(array_sum(array_map(fn ($l) => (float) ($l['api_cost'] ?? 0), $lines)), 4),
            'travclicks_commission' => round(array_sum(array_column($lines, 'travclicks_commission')), 4),
            'dmc_commission' => round(array_sum(array_column($lines, 'dmc_commission')), 4),
            'conversion_charge' => 0.0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function lineForOrder(Order $order, ?Tour $tour = null): array
    {
        $data = $order->data;
        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }
        if (! is_array($data)) {
            $data = [];
        }

        $channel = strtolower(trim((string) ($order->order_type ?? '')));
        $online = $channel === 'online';
        $agentPrice = $this->agentPrice($order, $data);
        $rules = $this->findMarkupRules($data);
        if (! $online && ! $rules['dmc'] && $tour) {
            $rules['dmc'] = $this->dmcRuleFromTour($tour, $order);
        }
        $split = $this->split($agentPrice, $online, $online ? $rules['admin'] : null, $rules['dmc']);

        return array_merge($split, [
            'order_id' => (int) ($order->booking_id ?? $order->getKey()),
            'channel' => $online ? 'online' : 'offline',
            'supplier_api' => $online ? $this->supplierApi($data) : null,
            'item_label' => $this->itemLabel($order, $data),
            'charged_amount' => $split['agent_price'],
        ]);
    }

    /**
     * @return array{api_cost: float|null, travclicks_commission: float, dmc_visible_price: float, dmc_commission: float, agent_price: float}
     */
    public function split(float $agentPrice, bool $online, ?MarkupRule $adminRule, ?MarkupRule $dmcRule): array
    {
        $agentPrice = round(max(0, $agentPrice), 4);

        if (! $online) {
            $base = $this->reverse($agentPrice, $dmcRule);

            return [
                'api_cost' => null,
                'travclicks_commission' => 0.0,
                'dmc_visible_price' => $base,
                'dmc_commission' => round(max(0, $agentPrice - $base), 4),
                'agent_price' => $agentPrice,
            ];
        }

        $dmcVisible = $this->reverse($agentPrice, $dmcRule);
        $apiCost = $this->reverse($dmcVisible, $adminRule);

        return [
            'api_cost' => $apiCost,
            'travclicks_commission' => round(max(0, $dmcVisible - $apiCost), 4),
            'dmc_visible_price' => $dmcVisible,
            'dmc_commission' => round(max(0, $agentPrice - $dmcVisible), 4),
            'agent_price' => $agentPrice,
        ];
    }

    public function reverse(float $price, ?MarkupRule $rule): float
    {
        if (! $rule instanceof MarkupRule || $rule->amount <= 0 || $price <= 0) {
            return round(max(0, $price), 4);
        }

        if ($rule->isPercentage()) {
            $factor = 1 + ($rule->amount / 100);

            return $factor > 0 ? round($price / $factor, 4) : round($price, 4);
        }

        return round(max(0, $price - $rule->amount), 4);
    }

    protected function dmcRuleFromTour(Tour $tour, Order $order): ?MarkupRule
    {
        $rows = $tour->currency_markups ?? null;
        if (is_string($rows)) {
            $rows = json_decode($rows, true);
        }
        if (! is_array($rows) || $rows === []) {
            return null;
        }

        $row = null;
        foreach ($rows as $candidate) {
            if (is_array($candidate)) {
                $row = $candidate;
                break;
            }
        }
        if ($row === null) {
            return null;
        }

        $isHotel = strtolower((string) ($order->type ?? '')) === 'hotel';
        $amount = (float) ($isHotel
            ? ($row['hotel_markup'] ?? $row['markup_value'] ?? 0)
            : ($row['other_markup'] ?? $row['markup_value'] ?? 0));
        if ($amount <= 0) {
            return null;
        }

        return new MarkupRule((string) ($row['markup_type'] ?? 'percentage'), $amount, 'dmc');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function agentPrice(Order $order, array $data): float
    {
        if (isset($order->cost_price) && is_numeric($order->cost_price) && (float) $order->cost_price > 0) {
            return (float) $order->cost_price;
        }

        foreach (['selling_price', 'total_price', 'agent_price', 'price', 'amount', 'net_price'] as $key) {
            if (isset($data[$key]) && is_numeric($data[$key]) && (float) $data[$key] > 0) {
                return (float) $data[$key];
            }
        }

        return 0.0;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{admin: ?MarkupRule, dmc: ?MarkupRule}
     */
    protected function findMarkupRules(array $data): array
    {
        $found = $this->searchMarkup($data);

        return [
            'admin' => MarkupRule::fromArray(is_array($found['admin_rule'] ?? null) ? $found['admin_rule'] : null),
            'dmc' => MarkupRule::fromArray(is_array($found['dmc_rule'] ?? null) ? $found['dmc_rule'] : null),
        ];
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    protected function searchMarkup(array $node): array
    {
        if (isset($node['admin_rule']) || isset($node['dmc_rule'])) {
            return $node;
        }

        foreach ($node as $value) {
            if (! is_array($value)) {
                continue;
            }
            $found = $this->searchMarkup($value);
            if ($found !== []) {
                return $found;
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function supplierApi(array $data): ?string
    {
        foreach (['supplier_api_id', 'supplier_code', 'supplier', 'api', 'provider'] as $key) {
            if (! empty($data[$key]) && is_string($data[$key])) {
                return $data[$key];
            }
        }

        $markup = $this->searchMarkup($data);

        return isset($markup['supplier_code']) ? (string) $markup['supplier_code'] : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function itemLabel(Order $order, array $data): string
    {
        foreach (['hotel_name', 'name', 'service_name', 'title', 'attraction_name'] as $key) {
            if (! empty($data[$key]) && is_string($data[$key])) {
                return $data[$key];
            }
        }

        $type = trim((string) ($order->type ?? 'Service'));

        return $type !== '' ? ucfirst($type) : 'Service';
    }

    /**
     * @param  array<int, array<string, mixed>>  $supplierResults
     * @return array<int, int>|null  Null means charge every item.
     */
    protected function bookedOrderIds(array $supplierResults): ?array
    {
        if ($supplierResults === []) {
            return null;
        }

        $ids = [];
        foreach ($supplierResults as $row) {
            if (strtolower((string) ($row['status'] ?? '')) !== 'booked') {
                continue;
            }
            if (! empty($row['order_id'])) {
                $ids[] = (int) $row['order_id'];
            }
        }

        return $ids === [] ? null : $ids;
    }
}
