<?php

namespace App\Services\HotelSuppliers\Adapters;

use App\Services\HotelSuppliers\Contracts\HotelSupplierAdapter;
use App\Services\HotelSuppliers\HotelSearchRequest;
use RuntimeException;

abstract class AbstractPlaceholderHotelAdapter implements HotelSupplierAdapter
{
    abstract protected function supplierLabel(): string;

    /**
     * @param  array<string, string|null>  $credentials
     * @return array{hotels: array<int, array<string, mixed>>, provider: mixed}
     */
    public function fetchHotels(HotelSearchRequest $request, array $credentials): array
    {
        throw new RuntimeException($this->supplierLabel() . ' hotel search adapter is not implemented yet.');
    }
}
