<?php

namespace App\Services\OnlinePricing;

use App\Models\SupplierMaster;
use App\Services\AttractionSuppliers\AttractionSupplierResolver;
use App\Services\OnlinePricing\Appliers\AttractionPriceMarkupApplier;
use App\Services\OnlinePricing\Appliers\HotelPriceMarkupApplier;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class OnlinePricingService
{
    public function __construct(
        private MarkupContextFactory $contextFactory,
        private MarkupCalculator $calculator,
        private HotelPriceMarkupApplier $hotelApplier,
        private AttractionPriceMarkupApplier $attractionApplier,
        private AttractionSupplierResolver $attractionSupplierResolver,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $hotels
     * @return array<int, array<string, mixed>>
     */
    public function applyHotelMarkups(array $hotels, ?SupplierMaster $adminSupplier, ?Authenticatable $user = null): array
    {
        $context = $this->contextFactory->create($user ?? Auth::user(), $adminSupplier, 'hotels');

        if (! $context->adminRule && ! $context->dmcRule) {
            return $hotels;
        }

        return $this->hotelApplier->apply($hotels, $context);
    }

    /**
     * @param  array<int, array<string, mixed>>  $attractions
     * @return array<int, array<string, mixed>>
     */
    public function applyAttractionMarkups(
        array $attractions,
        ?SupplierMaster $adminSupplier,
        ?Authenticatable $user = null,
    ): array {
        $context = $this->contextFactory->create($user ?? Auth::user(), $adminSupplier, 'attractions');

        if (! $context->adminRule && ! $context->dmcRule) {
            return $attractions;
        }

        return $this->attractionApplier->apply($attractions, $context);
    }

    public function applyMarkedUpPrice(float $netPrice, MarkupContext $context): float
    {
        return $this->calculator->applyStack($netPrice, $context->stackedRules());
    }

    /**
     * Apply the same admin+DMC stack used on room rates to absolute cancellation
     * penalties (Hotelbeds `amount` / `grossAmount`). Percent and nights rules are
     * left untouched — they scale with the (already marked-up) stay price.
     *
     * Supplier figures are kept on `supplier_amount` / `supplier_grossAmount` so
     * re-applying this method (search → get-hotel-data → recheck) never double-marks.
     *
     * @param  array<int, mixed>  $policies
     * @return array<int, mixed>
     */
    public function applyMarkupToCancellationPolicies(array $policies, MarkupContext $context): array
    {
        if (! $context->hasRules() || $policies === []) {
            return $policies;
        }

        return array_map(function ($policy) use ($context) {
            if (! is_array($policy)) {
                return $policy;
            }

            foreach (
                [
                    'amount' => 'supplier_amount',
                    'grossAmount' => 'supplier_grossAmount',
                ] as $field => $supplierField
            ) {
                if (! $this->policyAmountFilled($policy[$field] ?? null)) {
                    continue;
                }

                if (! $this->policyAmountFilled($policy[$supplierField] ?? null)) {
                    $policy[$supplierField] = $policy[$field];
                }

                $base = (float) $policy[$supplierField];
                if ($base <= 0) {
                    continue;
                }

                $policy[$field] = $this->applyMarkedUpPrice($base, $context);
            }

            return $policy;
        }, $policies);
    }

    private function policyAmountFilled(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return is_numeric($value) && (float) $value > 0;
    }

    /**
     * The markup stack an online hotel enquiry was priced with, as recorded by
     * HotelPriceMarkupApplier. Null for enquiries taken before it was recorded.
     *
     * @param  array<string, mixed>  $onlineBooking  orders.data[i].onlineHotelBooking
     */
    public function storedHotelMarkupContext(array $onlineBooking): ?MarkupContext
    {
        return MarkupContext::fromArray(
            is_array($onlineBooking['markup'] ?? null) ? $onlineBooking['markup'] : null,
        );
    }

    /**
     * Markup to apply to a live recheck price: the enquiry stamp when present,
     * otherwise the same admin + DMC stack the hotel search uses today.
     *
     * @param  array<string, mixed>  $onlineBooking
     */
    public function hotelMarkupContextForRecheck(
        array $onlineBooking,
        ?Authenticatable $user = null,
        ?int $tourDmcId = null,
    ): ?MarkupContext {
        $stored = $this->storedHotelMarkupContext($onlineBooking);

        if ($stored) {
            return $stored;
        }

        $context = $this->contextFactory->forHotelSupplier(
            $user ?? Auth::user(),
            (string) ($onlineBooking['supplier_code'] ?? ''),
            $tourDmcId,
        );

        return $context->hasRules() ? $context : null;
    }

    /**
     * Resolve admin supplier row for attractions by city name (country-scoped).
     */
    public function resolveAttractionSupplierForCity(?string $cityName): ?SupplierMaster
    {
        return $this->attractionSupplierResolver->tryResolveSupplierForCity($cityName);
    }
}
