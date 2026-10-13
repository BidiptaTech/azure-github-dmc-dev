<?php

namespace App\Services\HotelSuppliers\Contracts;

/**
 * Cancels a supplier booking that was previously confirmed and stored on
 * `orders.booking_details`. Each supplier implements its own cancel API behind
 * this interface so new suppliers can be added without touching the remove flow.
 */
interface OnlineHotelCancellable
{
    public function supplierCode(): string;

    /**
     * @param  array<string, mixed>  $bookingDetails  Decoded `orders.booking_details`
     * @param  array<string, string|null>  $credentials
     * @param  array{simulate?: bool, cancel_date?: string|null}  $options
     * @return array<string, mixed>
     */
    public function cancelFromBookingDetails(
        array $bookingDetails,
        array $credentials,
        array $options = [],
    ): array;
}
