<?php

namespace App\Services\HotelSuppliers\Contracts;

use App\Services\HotelSuppliers\HotelSearchRequest;

/**
 * For suppliers that separate the hotel catalogue from live availability, so the
 * UI can list every hotel up front and only price the one the user picks.
 */
interface TwoStepHotelSupplierAdapter extends HotelSupplierAdapter
{
    /**
     * Every hotel in the destination, without rates.
     *
     * @param  array<string, string|null>  $credentials
     * @return array{hotels: array<int, array<string, mixed>>, provider: mixed}
     */
    public function listHotels(HotelSearchRequest $request, array $credentials): array;

    /**
     * Live availability for a single hotel code.
     *
     * @param  array<string, string|null>  $credentials
     * @return array{hotel: ?array<string, mixed>, session_id: ?string, provider: mixed}
     */
    public function fetchHotelRooms(HotelSearchRequest $request, string $hotelCode, array $credentials): array;
}
