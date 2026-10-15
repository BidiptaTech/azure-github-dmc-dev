<?php

namespace App\Services\HotelSuppliers\Contracts;

/**
 * Confirms an online hotel order with its supplier in two steps: recheck live
 * availability/price, then book the rate the recheck returned.
 *
 * The recheck result is cached between the two HTTP requests so the rate keys the
 * supplier handed back are the exact ones used to book.
 */
interface OnlineHotelBookingService
{
    public function supplierCode(): string;

    /**
     * Implementations must fold in RecheckPriceComparison::compare() so the price shown
     * before approval carries the same admin + DMC markups as the enquiry.
     *
     * @param  array<string, mixed>  $booking  Single hotel booking row from orders.data
     * @param  array<string, string|null>  $credentials
     * @param  array{tour_id?: int|null}  $options
     * @return array<string, mixed>
     */
    public function recheckFromOrderBooking(array $booking, array $credentials, array $options = []): array;

    /**
     * @param  array<string, mixed>  $recheckResult  Output from recheckFromOrderBooking()
     * @param  array<string, mixed>  $booking
     * @param  array<string, string|null>  $credentials
     * @return array<string, mixed>
     */
    public function bookFromRecheckResult(
        array $recheckResult,
        array $booking,
        string $agencyBookingId,
        array $credentials,
    ): array;

    /**
     * @param  array<string, mixed>  $recheckResult
     */
    public function cacheRecheckResult(int $orderId, int $bookingIndex, array $recheckResult): string;

    /**
     * @return array<string, mixed>|null
     */
    public function pullCachedRecheckResult(int $orderId, int $bookingIndex, string $token): ?array;
}
