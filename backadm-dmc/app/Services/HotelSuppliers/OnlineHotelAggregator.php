<?php

namespace App\Services\HotelSuppliers;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class OnlineHotelAggregator
{
    public function __construct(
        private HotelSupplierResolver $resolver,
        private HotelSupplierFactory $factory,
        private HotelResponseNormalizer $normalizer,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function search(string $cityName, string $checkIn, string $checkOut, string $paxInfo): array
    {
        $resolved = $this->resolver->resolveForCityName($cityName);

        $searchRequest = new HotelSearchRequest(
            cityName: $resolved['city']->name,
            checkIn: $checkIn,
            checkOut: $checkOut,
            paxInfo: $paxInfo,
            cityId: isset($resolved['city']->id) ? (int) $resolved['city']->id : null,
            countryId: $resolved['country_id'],
        );

        $adapter = $this->factory->make($resolved['supplier']->code);

        Log::info('Online hotel search', [
            'city' => $searchRequest->cityName,
            'country_id' => $resolved['country_id'],
            'supplier_code' => $resolved['supplier']->code,
            'supplier_name' => $resolved['supplier']->name,
        ]);

        $result = $adapter->fetchHotels($searchRequest, $resolved['credentials']);
        $frontendHotels = $this->normalizer->forFrontend($result['hotels']);

        return [
            'success' => true,
            'hotels' => $frontendHotels,
            'total_hotels' => count($frontendHotels),
            'supplier_code' => $resolved['supplier']->code,
            'supplier_name' => $resolved['supplier']->name,
            'country_id' => $resolved['country_id'],
            'city' => $searchRequest->cityName,
            'request' => $searchRequest->toPayload(),
            'provider' => $result['provider'],
        ];
    }
}
