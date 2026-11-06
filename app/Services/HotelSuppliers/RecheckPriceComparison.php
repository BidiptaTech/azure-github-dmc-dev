<?php

namespace App\Services\HotelSuppliers;

use App\Models\Tour;
use App\Services\OnlinePricing\OnlinePricingService;
use Illuminate\Support\Facades\Auth;

/**
 * Works out whether an online hotel got more expensive between enquiry and approval.
 *
 * The stored price is what the customer was quoted, so it already carries the admin and
 * DMC markups; a recheck returns the supplier's bare price. Comparing the two directly
 * flags every booking as "price changed", so the same markup stack is applied to the
 * fresh supplier price and the two customer-facing figures are compared instead.
 */
class RecheckPriceComparison
{
    private const TOLERANCE = 0.01;

    public function __construct(
        private OnlinePricingService $pricing,
    ) {}

    /**
     * @param  array<string, mixed>  $booking  Single hotel booking row from orders.data
     * @param  float  $supplierPrice  What the supplier is charging right now
     * @param  float|null  $storedSupplierPrice  What it charged when the enquiry was taken
     * @param  array{tour_id?: int|null}  $options
     * @return array<string, mixed>
     */
    public function compare(
        array $booking,
        float $supplierPrice,
        ?float $storedSupplierPrice = null,
        array $options = [],
    ): array {
        $online = is_array($booking['onlineHotelBooking'] ?? null) ? $booking['onlineHotelBooking'] : [];

        if (($online['supplier_code'] ?? '') === '') {
            $online['supplier_code'] = (string) ($booking['onlineHotelSource'] ?? '');
        }
        $storedPrice = (float) ($booking['totalPrice'] ?? $booking['price'] ?? 0);
        $context = $this->pricing->hotelMarkupContextForRecheck(
            $online,
            Auth::user(),
            $this->tourDmcId($options['tour_id'] ?? null),
        );

        $customerPrice = null;
        $markupRules = [];

        if ($context && $supplierPrice > 0) {
            $customerPrice = $this->pricing->applyMarkedUpPrice($supplierPrice, $context);
            $markupRules = $context->ruleLabels();
        }

        if ($customerPrice !== null && $storedPrice > 0) {
            $basis = 'customer';
            $now = $customerPrice;
            $stored = $storedPrice;
        } elseif ($storedSupplierPrice !== null && $storedSupplierPrice > 0) {
            $basis = 'supplier';
            $now = $supplierPrice;
            $stored = $storedSupplierPrice;
        } else {
            $basis = 'none';
            $now = null;
            $stored = null;
        }

        return [
            'stored_price' => $storedPrice,
            'stored_supplier_price' => $storedSupplierPrice,
            'customer_price' => $customerPrice,
            'markup_applied' => $customerPrice !== null,
            'markup_rules' => $markupRules,
            'comparison_basis' => $basis,
            'compare_now' => $now,
            'compare_stored' => $stored,
            'price_changed' => $now !== null && abs($now - $stored) > self::TOLERANCE,
        ];
    }

    private function tourDmcId(mixed $tourId): ?int
    {
        $tourId = (int) $tourId;

        if ($tourId <= 0) {
            return null;
        }

        $tour = Tour::query()
            ->where(function ($query) use ($tourId) {
                $query->where('tour_id', $tourId)->orWhere('id', $tourId);
            })
            ->first(['dmc_id']);

        return $tour && (int) $tour->dmc_id > 0 ? (int) $tour->dmc_id : null;
    }
}
