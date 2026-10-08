<?php

namespace App\Services\AttractionSuppliers;

use App\Services\ApiEnvironmentResolver;
use App\Services\OnlinePricing\OnlinePricingService;
use App\Services\SupplierConfigResolver;
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
        private ApiEnvironmentResolver $apiEnvironment,
        private SupplierConfigResolver $supplierConfig,
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
        $environment = $this->apiEnvironment->resolve();

        if ($cityName !== '') {
            $resolved = $this->resolver->resolveForCityName($cityName);
            $supplier = $resolved['supplier'];
            $countryId = $resolved['country_id'];
            $resolvedCityName = $resolved['city']->name;
            $credentials = $resolved['credentials'];
            $supplierCode = $supplier->code;
            $environment = $resolved['api_environment'] ?? $environment;
        } else {
            $supplierCode = self::DEFAULT_SUPPLIER_CODE;

            if (! $this->supplierConfig->isConfigured($supplierCode, $environment)) {
                throw new RuntimeException(
                    $this->supplierConfig->missingCredentialsMessage($supplierCode, $environment)
                );
            }

            $credentials = $this->supplierConfig->valuesFor($supplierCode, $environment);
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
            'api_environment' => $environment,
        ]);

        $result = $adapter->fetchAttractions($searchRequest, $credentials);
        $attractions = $result['attractions'] ?? [];
        if (method_exists($adapter, 'fetchTickets')) {
            foreach ($attractions as $index => $attraction) {
                if (! is_array($attraction)) {
                    continue;
                }
                $existingTickets = $attraction['tickets'] ?? [];
                if (is_array($existingTickets) && $existingTickets !== []) {
                    continue;
                }
                $sku = trim((string) ($attraction['sku_id'] ?? ''));
                if ($sku === '') {
                    continue;
                }
                $tickets = $adapter->fetchTickets($sku, $visitDate, $credentials);
                if ($tickets !== []) {
                    $attractions[$index]['tickets'] = $tickets;
                }
            }
        }

        $frontendAttractions = $this->normalizer->forFrontend($attractions);
        $frontendAttractions = $this->onlinePricing->applyAttractionMarkups(
            $frontendAttractions,
            $supplier,
            Auth::user(),
        );

        $frontendAttractions = array_map(function ($attraction) use ($environment) {
            if (! is_array($attraction)) {
                return $attraction;
            }

            $attraction['api_environment'] = $environment;

            return $attraction;
        }, $frontendAttractions);

        return [
            'success' => true,
            'attractions' => $frontendAttractions,
            'total_attractions' => count($frontendAttractions),
            'supplier_code' => $supplier?->code ?? $supplierCode,
            'supplier_name' => $supplier?->name ?? config('suppliers.' . $supplierCode . '.label', $supplierCode),
            'country_id' => $countryId,
            'city' => $resolvedCityName,
            'api_environment' => $environment,
            'request' => $searchRequest->toPayload(),
            'provider' => $result['provider'],
        ];
    }
}
