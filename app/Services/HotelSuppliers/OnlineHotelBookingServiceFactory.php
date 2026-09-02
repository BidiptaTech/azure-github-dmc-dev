<?php

namespace App\Services\HotelSuppliers;

use App\Services\HotelSuppliers\Contracts\OnlineHotelBookingService;
use App\Services\HotelSuppliers\MgBedbank\MgBedbankBookingService;
use App\Services\HotelSuppliers\Tinivia\TiniviaBookingService;
use RuntimeException;

class OnlineHotelBookingServiceFactory
{
    public function make(string $supplierCode): OnlineHotelBookingService
    {
        return match (strtolower(trim($supplierCode))) {
            'mg_bedbank' => app(MgBedbankBookingService::class),
            'tinivia', 'tiniva' => app(TiniviaBookingService::class),

            default => throw new RuntimeException(
                'Online supplier "' . $supplierCode . '" is not supported yet.'
            ),
        };
    }

    public function supports(string $supplierCode): bool
    {
        try {
            $this->make($supplierCode);

            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    /**
     * Supplier Master keys the credentials under `tinivia`, so normalise the
     * alternate spelling that shows up in stored order data.
     */
    public function credentialCode(string $supplierCode): string
    {
        $code = strtolower(trim($supplierCode));

        return $code === 'tiniva' ? 'tinivia' : $code;
    }
}
