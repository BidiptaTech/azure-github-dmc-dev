<?php

namespace App\Services\HotelSuppliers\Adapters;

class MyBedsHotelAdapter extends AbstractPlaceholderHotelAdapter
{
    public function code(): string
    {
        return 'mybeds';
    }

    protected function supplierLabel(): string
    {
        return 'MyBeds';
    }
}
