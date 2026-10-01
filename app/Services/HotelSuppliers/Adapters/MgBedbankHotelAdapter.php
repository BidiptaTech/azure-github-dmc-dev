<?php

namespace App\Services\HotelSuppliers\Adapters;

class MgBedbankHotelAdapter extends AbstractPlaceholderHotelAdapter
{
    public function code(): string
    {
        return 'mg_bedbank';
    }

    protected function supplierLabel(): string
    {
        return 'MG Bedbank';
    }
}
