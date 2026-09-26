<?php

namespace App\Services\AttractionSuppliers;

use App\Services\OnlinePricing\OnlinePricingService;
use App\Services\SupplierEnvService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OnlineAttractionAggregator
{
    private const DEFAULT_SUPPLIER_CODE = 'sg_attractions';

    public function __construct(
        private AttractionSupplierResolver $resolver,
        private AttractionSupplierFactory $factory,
        private AttractionResponseNormalizer $normalizer,
        private OnlinePricingService $onlinePricing,
        private SupplierEnvService $supplierEnv,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function search(
        ?string $visitDate = null,
        ?string $cityName = null,
        ?string $paxInfo = null,
        ?int $displayLimit = null,
        ?int $currentPage = null,
    ): array {
        $cityName = trim((string) $cityName);
        $supplier = null;
        $countryId = null;
        $resolvedCityName = $cityName !== '' ? $cityName : null;
        $credentials = [];

        if ($cityName !== '') {
            $resolved = $this->resolver->resolveForCityName($cityName);
            $supplier = $resolved['supplier'];
            $countryId = $resolved['country_id'];
            $resolvedCityName = $resolved['city']->name;
            $credentials = $resolved['credentials'];
            $supplierCode = $supplier->code;
        } else {
            $supplierCode = self::DEFAULT_SUPPLIER_CODE;

            if (! $this->supplierEnv->isConfigured($supplierCode)) {
                throw new RuntimeException('SG Attractions API credentials are not configured. Set them in Supplier Master → API Credentials or .env.');
            }

            $credentials = $this->supplierEnv->valuesFor($supplierCode);
        }

        $searchRequest = new AttractionSearchRequest(
            visitDate: $visitDate,
            cityName: $resolvedCityName,
            paxInfo: $paxInfo,
            displayLimit: $displayLimit,
            currentPage: $currentPage,
            countryId: $countryId,
        );

        $adapter = $this->factory->make($supplierCode);

        Log::info('Online attraction search', [
            'city' => $resolvedCityName,
            'country_id' => $countryId,
            'supplier_code' => $supplierCode,
            'supplier_name' => $supplier?->name,
        ]);

        $result = $adapter->fetchAttractions($searchRequest, $credentials);
        $frontendAttractions = $this->normalizer->forFrontend($result['attractions']);
        $frontendAttractions = $this->onlinePricing->applyAttractionMarkups(
            $frontendAttractions,
            $supplier,
            Auth::user(),
        );

        return [
            'success' => true,
            'attractions' => $frontendAttractions,
            'total_attractions' => count($frontendAttractions),
            'supplier_code' => $supplier?->code ?? $supplierCode,
            'supplier_name' => $supplier?->name ?? config('suppliers.' . $supplierCode . '.label', $supplierCode),
            'country_id' => $countryId,
            'city' => $resolvedCityName,
            'request' => $searchRequest->toPayload(),
            'provider' => $result['provider'],
        ];
    }
}
