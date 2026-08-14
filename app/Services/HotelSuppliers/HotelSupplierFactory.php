<?php

namespace App\Services\HotelSuppliers;

use App\Services\HotelSuppliers\Adapters\HotelbedsHotelAdapter;
use App\Services\HotelSuppliers\Adapters\MgBedbankHotelAdapter;
use App\Services\HotelSuppliers\Adapters\MyBedsHotelAdapter;
use App\Services\HotelSuppliers\Adapters\TiniviaHotelAdapter;
use App\Services\HotelSuppliers\Contracts\HotelSupplierAdapter;
use InvalidArgumentException;

class HotelSupplierFactory
{
    public function make(string $code): HotelSupplierAdapter
    {
        return match ($code) {
            'tinivia' => app(TiniviaHotelAdapter::class),
            'mybeds' => app(MyBedsHotelAdapter::class),
            'mg_bedbank' => app(MgBedbankHotelAdapter::class),
            'hotelbeds' => app(HotelbedsHotelAdapter::class),
            
            default => throw new InvalidArgumentException("Unsupported hotel supplier [{$code}]."),
        };
    }
}
