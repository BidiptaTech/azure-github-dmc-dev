<?php

namespace App\Services\HotelSuppliers\Contracts;

use App\Services\HotelSuppliers\HotelSearchRequest;

interface HotelSupplierAdapter
{
    public function code(): string;

    /**
     * @param  array<string, string|null>  $credentials
     * @return array{hotels: array<int, array<string, mixed>>, provider: mixed}
     */
    public function fetchHotels(HotelSearchRequest $request, array $credentials): array;
}
