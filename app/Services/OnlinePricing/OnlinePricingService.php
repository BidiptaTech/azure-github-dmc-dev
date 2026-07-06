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
     * Resolve admin supplier row for attractions by city name (country-scoped).
     */
    public function resolveAttractionSupplierForCity(?string $cityName): ?SupplierMaster
    {
        return $this->attractionSupplierResolver->tryResolveSupplierForCity($cityName);
    }
}
