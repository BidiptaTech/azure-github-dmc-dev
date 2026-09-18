<?php

namespace App\Services\AttractionSuppliers;

readonly class AttractionSearchRequest
{
    public function __construct(
        public ?string $visitDate = null,
        public ?string $cityName = null,
        public ?string $paxInfo = null,
        public ?int $displayLimit = null,
        public ?int $currentPage = null,
        public ?int $cityId = null,
        public ?int $countryId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return array_filter([
            'visitDate' => $this->visitDate,
            'city' => $this->cityName,
            'paxInfo' => $this->paxInfo,
            'display_limit' => $this->displayLimit,
            'current_page' => $this->currentPage,
        ], static fn ($value) => $value !== null && $value !== '');
    }
}
