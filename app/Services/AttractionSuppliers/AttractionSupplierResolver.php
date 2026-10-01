<?php

namespace App\Services\AttractionSuppliers;

use App\Models\City;
use App\Models\Country;
use App\Models\SupplierMaster;
use App\Services\SupplierEnvService;
use RuntimeException;

class AttractionSupplierResolver
{
    public function __construct(
        private SupplierEnvService $supplierEnv,
    ) {}

    /**
     * @return array{
     *     city: City,
     *     country_id: int,
     *     supplier: SupplierMaster,
     *     credentials: array<string, string|null>
     * }
     */
    public function resolveForCityName(string $cityName): array
    {
        $city = $this->findCity($cityName);

        if (! $city) {
            throw new RuntimeException("City [{$cityName}] was not found.");
        }

        $countryId = $this->resolveCountryId($city);

        if (! $countryId) {
            throw new RuntimeException("Could not determine country for city [{$city->name}].");
        }

        $supplier = SupplierMaster::forCountryAndService($countryId, 'attractions');

        if (! $supplier) {
            $countryName = Country::query()->where('id', $countryId)->value('name') ?? 'this country';

            throw new RuntimeException("No active attraction supplier is mapped for {$countryName}. Configure it in Supplier Master.");
        }

        if (! $this->supplierEnv->isConfigured($supplier->code)) {
            $label = config("suppliers.{$supplier->code}.label", $supplier->code);

            throw new RuntimeException("{$label} API credentials are not configured. Set them in Supplier Master → API Credentials.");
        }

        return [
            'city' => $city,
            'country_id' => $countryId,
            'supplier' => $supplier,
            'credentials' => $this->supplierEnv->valuesFor($supplier->code),
        ];
    }

    public function tryResolveSupplierForCity(?string $cityName): ?SupplierMaster
    {
        $cityName = trim((string) $cityName);

        if ($cityName === '') {
            return null;
        }

        try {
            return $this->resolveForCityName($cityName)['supplier'];
        } catch (RuntimeException) {
            return null;
        }
    }

    private function findCity(string $cityName): ?City
    {
        $cityName = trim($cityName);

        if ($cityName === '') {
            return null;
        }

        if (ctype_digit($cityName)) {
            return City::query()
                ->where('id', (int) $cityName)
                ->orWhere('city_id', (int) $cityName)
                ->first();
        }

        return City::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($cityName)])
            ->first();
    }

    private function resolveCountryId(City $city): ?int
    {
        if (! empty($city->country_id)) {
            return (int) $city->country_id;
        }

        if (! empty($city->country)) {
            $countryId = Country::query()
                ->whereRaw('LOWER(name) = ?', [strtolower((string) $city->country)])
                ->value('id');

            if ($countryId) {
                return (int) $countryId;
            }
        }

        return null;
    }
}
