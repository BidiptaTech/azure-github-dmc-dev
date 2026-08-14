<?php

namespace App\Services\HotelSuppliers;

use App\Services\HotelSuppliers\Contracts\HotelSupplierAdapter;
use App\Services\HotelSuppliers\Contracts\TwoStepHotelSupplierAdapter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\OnlinePricing\OnlinePricingService;
use RuntimeException;

class OnlineHotelAggregator
{
    public function __construct(
        private HotelSupplierResolver $resolver,
        private HotelSupplierFactory $factory,
        private HotelResponseNormalizer $normalizer,
        private OnlinePricingService $onlinePricing,
    ) {}

    /**
     * Lists the hotels for a city. Two-step suppliers return their catalogue without
     * rates here; the caller then asks for `rooms()` once a hotel is picked.
     *
     * @return array<string, mixed>
     */
    public function search(string $cityName, string $checkIn, string $checkOut, string $paxInfo): array
    {
        [$searchRequest, $adapter, $resolved] = $this->prepare($cityName, $checkIn, $checkOut, $paxInfo);
        $twoStep = $adapter instanceof TwoStepHotelSupplierAdapter;

        Log::info('Online hotel search', [
            'city' => $searchRequest->cityName,
            'country_id' => $resolved['country_id'],
            'supplier_code' => $resolved['supplier']->code,
            'supplier_name' => $resolved['supplier']->name,
            'two_step' => $twoStep,
        ]);

        $result = $twoStep
            ? $adapter->listHotels($searchRequest, $resolved['credentials'])
            : $adapter->fetchHotels($searchRequest, $resolved['credentials']);

        $frontendHotels = $this->present($result['hotels'], $resolved);

        return [
            'success' => true,
            'hotels' => $frontendHotels,
            'total_hotels' => count($frontendHotels),
            'two_step' => $twoStep,
            'supplier_code' => $resolved['supplier']->code,
            'supplier_name' => $resolved['supplier']->name,
            'country_id' => $resolved['country_id'],
            'city' => $searchRequest->cityName,
            'request' => $searchRequest->toPayload(),
            'provider' => $result['provider'],
        ];
    }

    /**
     * Live availability for one hotel, including the session and rate keys needed to book it.
     *
     * @return array<string, mixed>
     */
    public function rooms(
        string $cityName,
        string $hotelCode,
        string $checkIn,
        string $checkOut,
        string $paxInfo,
    ): array {
        [$searchRequest, $adapter, $resolved] = $this->prepare($cityName, $checkIn, $checkOut, $paxInfo);

        if (! $adapter instanceof TwoStepHotelSupplierAdapter) {
            throw new RuntimeException("{$resolved['supplier']->name} returns rates with the hotel list; no room lookup is needed.");
        }

        Log::info('Online hotel room search', [
            'city' => $searchRequest->cityName,
            'hotel_code' => $hotelCode,
            'supplier_code' => $resolved['supplier']->code,
        ]);

        $result = $adapter->fetchHotelRooms($searchRequest, $hotelCode, $resolved['credentials']);

        if (! is_array($result['hotel'] ?? null)) {
            return [
                'success' => true,
                'hotel' => null,
                'rooms' => [],
                'total_rooms' => 0,
                'session_id' => $result['session_id'] ?? null,
                'supplier_code' => $resolved['supplier']->code,
                'supplier_name' => $resolved['supplier']->name,
                'message' => 'No rooms are available for this hotel on the selected dates.',
                'provider' => $result['provider'],
            ];
        }

        $hotel = array_values($this->present([$result['hotel']], $resolved))[0] ?? $result['hotel'];

        return [
            'success' => true,
            'hotel' => $hotel,
            'rooms' => $hotel['rooms'] ?? [],
            'total_rooms' => count($hotel['rooms'] ?? []),
            'session_id' => $result['session_id'] ?? null,
            'supplier_code' => $resolved['supplier']->code,
            'supplier_name' => $resolved['supplier']->name,
            'request' => $searchRequest->toPayload(),
            'provider' => $result['provider'],
        ];
    }

    /**
     * @return array{0: HotelSearchRequest, 1: HotelSupplierAdapter, 2: array<string, mixed>}
     */
    private function prepare(string $cityName, string $checkIn, string $checkOut, string $paxInfo): array
    {
        $resolved = $this->resolver->resolveForCityName($cityName);

        $searchRequest = new HotelSearchRequest(
            cityName: $resolved['city']->name,
            checkIn: $checkIn,
            checkOut: $checkOut,
            paxInfo: $paxInfo,
            cityId: isset($resolved['city']->city_id) ? (int) $resolved['city']->city_id : null,
            countryId: $resolved['country_id'],
        );

        return [$searchRequest, $this->factory->make($resolved['supplier']->code), $resolved];
    }

    /**
     * @param  array<int, array<string, mixed>>  $hotels
     * @param  array<string, mixed>  $resolved
     * @return array<int, array<string, mixed>>
     */
    private function present(array $hotels, array $resolved): array
    {
        return $this->onlinePricing->applyHotelMarkups(
            $this->normalizer->forFrontend($hotels),
            $resolved['supplier'],
            Auth::user(),
        );
    }
}
