<?php

namespace App\Services\HotelSuppliers;

use App\Models\City;
use App\Models\Country;
use App\Models\SupplierMaster;
use App\Services\ApiEnvironmentResolver;
use App\Services\SupplierConfigResolver;
use RuntimeException;

class HotelSupplierResolver
{
    public function __construct(
        private ApiEnvironmentResolver $apiEnvironment,
        private SupplierConfigResolver $supplierConfig,
    ) {}

    /**
     * `country_id` is the `countries.id` primary key, not `countries.country_id`,
     * because `suppliers_master.country_id` is a foreign key against `countries.id`.
     *
     * @return array{
     *     city: City,
     *     country_id: int,
     *     supplier: SupplierMaster,
     *     credentials: array<string, string|null>,
     *     api_environment: string
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

        $supplier = SupplierMaster::forCountryAndService($countryId, 'hotels');

        if (! $supplier) {
            $countryName = Country::query()->where('id', $countryId)->value('name') ?? 'this country';

            throw new RuntimeException("No active hotel supplier is mapped for {$countryName}. Configure it in Supplier Master.");
        }

        $environment = $this->apiEnvironment->resolve();

        if (! $this->supplierConfig->isConfigured($supplier->code, $environment)) {
            throw new RuntimeException(
                $this->supplierConfig->missingCredentialsMessage($supplier->code, $environment)
            );
        }

        return [
            'city' => $city,
            'country_id' => $countryId,
            'supplier' => $supplier,
            'credentials' => $this->supplierConfig->valuesFor($supplier->code, $environment),
            'api_environment' => $environment,
        ];
    }

    private function findCity(string $cityName): ?City
    {
        $cityName = trim($cityName);

        if ($cityName === '') {
            return null;
        }

        if (ctype_digit($cityName)) {
            return City::query()
                ->where('city_id', (int) $cityName)
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
