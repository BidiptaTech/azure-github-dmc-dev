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
