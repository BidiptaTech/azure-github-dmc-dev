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
    ) {}

    /**
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
}
