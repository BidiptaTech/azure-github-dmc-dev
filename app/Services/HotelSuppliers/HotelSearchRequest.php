<?php

namespace App\Services\HotelSuppliers;

readonly class HotelSearchRequest
{
    public function __construct(
        public string $cityName,
        public string $checkIn,
        public string $checkOut,
        public string $paxInfo,
        public ?int $cityId = null,
        public ?int $countryId = null,
        /**
         * How many rooms the pax should be spread across. Suppliers that price per
         * room block (MG Bedbank) quote a different total for the same pax depending
         * on this, so it must never be guessed.
         */
        public int $rooms = 1,
    ) {}

    /**
     * Tinivia's request shape; `rooms` is deliberately absent because that API
     * derives occupancy from `paxInfo` alone.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'checkIn' => $this->checkIn,
            'checkOut' => $this->checkOut,
            'city' => $this->cityName,
            'paxInfo' => $this->paxInfo,
        ];
    }

    public function roomCount(): int
    {
        return max(1, $this->rooms);
    }
}
