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

    /** Safe default page size for live API list calls (avoids PHP 60s timeouts). */
    public const DEFAULT_PAGE_SIZE = 25;

    public function __construct(
        private AttractionSupplierResolver $resolver,
        private AttractionSupplierFactory $factory,
        private AttractionResponseNormalizer $normalizer,
        private OnlinePricingService $onlinePricing,
        private ApiEnvironmentResolver $apiEnvironment,
        private SupplierConfigResolver $supplierConfig,
    ) {}

    /**
     * Search attractions. Tickets are NOT bulk-fetched (that caused N+1 timeouts).
     * Use fetchTicketsForSku() when the user selects an attraction.
     *
     * @return array<string, mixed>
     */
    public function search(
        ?string $visitDate = null,
        ?string $cityName = null,
        ?string $paxInfo = null,
        ?int $displayLimit = null,
        ?int $currentPage = null,
        bool $includeTickets = false,
    ): array {
        $cityName = trim((string) $cityName);
        $supplier = null;
        $countryId = null;
        $resolvedCityName = $cityName !== '' ? $cityName : null;
        $credentials = [];
        $environment = $this->apiEnvironment->resolve();

        // null / 0 display_limit = full catalog (same as Postman GET /attractions with no paging).
        // Paging is optional for huge catalogs; SG Attractions returns ~150 items in a few seconds.
        $fetchAll = $displayLimit === null || (int) $displayLimit <= 0;
        $page = $fetchAll ? null : max(1, (int) ($currentPage ?: 1));
        $limit = $fetchAll ? null : max(1, min(500, (int) $displayLimit));

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
            displayLimit: $limit,
            currentPage: $page,
            countryId: $countryId,
        );

        $adapter = $this->factory->make($supplierCode);

        Log::info('Online attraction search', [
            'city' => $resolvedCityName,
            'country_id' => $countryId,
            'supplier_code' => $supplierCode,
            'supplier_name' => $supplier?->name,
            'api_environment' => $environment,
            'fetch_all' => $fetchAll,
            'display_limit' => $limit,
            'current_page' => $page,
            'include_tickets' => $includeTickets,
        ]);

        $result = $adapter->fetchAttractions($searchRequest, $credentials);
        $attractions = $result['attractions'] ?? [];
        if (! is_array($attractions)) {
            $attractions = [];
        }

        // Optional ticket enrichment for a small page only (never the full catalog).
        if ($includeTickets && method_exists($adapter, 'fetchTickets')) {
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

        $count = count($frontendAttractions);
        $providerMeta = $this->extractProviderPagination($result['provider'] ?? null);

        return [
            'success' => true,
            'attractions' => $frontendAttractions,
            'total_attractions' => $count,
            'fetch_all' => $fetchAll,
            'page' => $page ?? 1,
            'display_limit' => $limit,
            // Full catalog fetch has no further pages; paged mode continues while page is full.
            'has_more' => $fetchAll ? false : ($limit !== null && $count >= $limit),
            'provider_total' => $providerMeta['total'] ?? $count,
            'provider_pages' => $providerMeta['pages'],
            'supplier_code' => $supplier?->code ?? $supplierCode,
            'supplier_name' => $supplier?->name ?? config('suppliers.' . $supplierCode . '.label', $supplierCode),
            'country_id' => $countryId,
            'city' => $resolvedCityName,
            'api_environment' => $environment,
            'request' => $searchRequest->toPayload(),
            'provider' => $result['provider'] ?? null,
        ];
    }

    /**
     * Lazy-load tickets for one attraction SKU (after user selects it).
     *
     * @return array<string, mixed>
     */
    public function fetchTicketsForSku(
        string $skuId,
        ?string $visitDate = null,
        ?string $cityName = null,
    ): array {
        $skuId = trim($skuId);
        if ($skuId === '') {
            throw new RuntimeException('Attraction SKU is required.');
        }

        $cityName = trim((string) $cityName);
        $supplier = null;
        $credentials = [];
        $environment = $this->apiEnvironment->resolve();
        $supplierCode = self::DEFAULT_SUPPLIER_CODE;

        if ($cityName !== '') {
            $resolved = $this->resolver->resolveForCityName($cityName);
            $supplier = $resolved['supplier'];
            $credentials = $resolved['credentials'];
            $supplierCode = $supplier->code;
            $environment = $resolved['api_environment'] ?? $environment;
        } else {
            if (! $this->supplierConfig->isConfigured($supplierCode, $environment)) {
                throw new RuntimeException(
                    $this->supplierConfig->missingCredentialsMessage($supplierCode, $environment)
                );
            }
            $credentials = $this->supplierConfig->valuesFor($supplierCode, $environment);
        }

        $adapter = $this->factory->make($supplierCode);
        if (! method_exists($adapter, 'fetchTickets')) {
            return [
                'success' => true,
                'tickets' => [],
                'sku_id' => $skuId,
                'api_environment' => $environment,
            ];
        }

        $tickets = $adapter->fetchTickets($skuId, $visitDate, $credentials);
        $wrapped = [[
            'sku_id' => $skuId,
            'title' => $skuId,
            'tickets' => $tickets,
            'lowest_ticket_price' => 0,
            'highest_ticket_price' => 0,
            'currency' => 'SGD',
            'supplier_code' => $supplierCode,
        ]];
        $frontend = $this->normalizer->forFrontend($wrapped);
        $frontend = $this->onlinePricing->applyAttractionMarkups($frontend, $supplier, Auth::user());
        $ticketList = is_array($frontend[0]['tickets'] ?? null) ? $frontend[0]['tickets'] : $tickets;

        return [
            'success' => true,
            'tickets' => array_values(array_filter($ticketList, 'is_array')),
            'sku_id' => $skuId,
            'api_environment' => $environment,
            'supplier_code' => $supplier?->code ?? $supplierCode,
        ];
    }

    /**
     * @return array{total: ?int, pages: ?int}
     */
    private function extractProviderPagination(mixed $provider): array
    {
        $total = null;
        $pages = null;
        if (! is_array($provider)) {
            return ['total' => $total, 'pages' => $pages];
        }

        $candidates = [
            $provider['response'] ?? null,
            $provider['data'] ?? null,
            $provider,
        ];
        foreach ($candidates as $node) {
            if (! is_array($node)) {
                continue;
            }
            foreach (['total', 'total_count', 'totalCount', 'total_records', 'totalRecords'] as $key) {
                if (isset($node[$key]) && is_numeric($node[$key])) {
                    $total = (int) $node[$key];
                    break 2;
                }
            }
            if (isset($node['pagination']) && is_array($node['pagination'])) {
                $p = $node['pagination'];
                if (isset($p['total']) && is_numeric($p['total'])) {
                    $total = (int) $p['total'];
                }
                if (isset($p['total_pages']) && is_numeric($p['total_pages'])) {
                    $pages = (int) $p['total_pages'];
                } elseif (isset($p['last_page']) && is_numeric($p['last_page'])) {
                    $pages = (int) $p['last_page'];
                }
                break;
            }
        }

        return ['total' => $total, 'pages' => $pages];
    }
}
