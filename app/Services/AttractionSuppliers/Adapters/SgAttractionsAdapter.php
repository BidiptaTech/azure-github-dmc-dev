<?php

namespace App\Services\AttractionSuppliers\Adapters;

use App\Services\AttractionSuppliers\AttractionSearchRequest;
use App\Services\AttractionSuppliers\Contracts\AttractionSupplierAdapter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SgAttractionsAdapter implements AttractionSupplierAdapter
{
    public function code(): string
    {
        return 'sg_attractions';
    }

    /**
     * @param  array<string, string|null>  $credentials
     * @return array{attractions: array<int, array<string, mixed>>, provider: mixed}
     */
    public function fetchAttractions(AttractionSearchRequest $request, array $credentials): array
    {
        $ctx = $this->authenticatedContext($credentials);
        if (! $ctx['success']) {
            throw new RuntimeException($ctx['message'] ?? 'Failed to authenticate with SG Attractions API.');
        }

        $query = array_filter([
            'display_limit' => $request->displayLimit,
            'current_page' => $request->currentPage,
            'visit_date' => $request->visitDate,
            'city' => $request->cityName,
        ], static fn ($value) => $value !== null && $value !== '');

        $response = Http::timeout($ctx['timeout'])
            ->withHeaders($this->headers($ctx['token']))
            ->acceptJson()
            ->get($ctx['base_url'] . '/attractions', $query);

        if (! $response->successful()) {
            Log::warning('SG Attractions list failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'query' => $query,
            ]);

            throw new RuntimeException('Failed to fetch online attractions from provider (HTTP ' . $response->status() . ').');
        }

        $body = $response->json();
        if ((int) ($body['status'] ?? 0) !== 1000) {
            throw new RuntimeException($body['message'] ?? 'SG Attractions API returned an error.');
        }

        $rawAttractions = $body['response']['data'] ?? [];
        if (! is_array($rawAttractions)) {
            $rawAttractions = [];
        }

        return [
            'attractions' => array_map(fn (array $item) => $this->normalizeAttraction($item), $rawAttractions),
            'provider' => $body,
        ];
    }

    /**
     * POST /order/create
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, string|null>  $credentials
     * @return array{success: bool, order_ref_id: ?string, external_status: ?string, message: ?string, provider: mixed}
     */
    public function createOrder(array $payload, array $credentials): array
    {
        $ctx = $this->authenticatedContext($credentials);
        if (! $ctx['success']) {
            Log::warning('SG Attractions create-order auth failed', [
                'message' => $ctx['message'] ?? null,
            ]);

            return [
                'success' => false,
                'order_ref_id' => null,
                'external_status' => null,
                'message' => $ctx['message'] ?? 'SG Attractions authentication failed',
                'provider' => null,
            ];
        }

        try {
            $response = $this->postCreateOrder($ctx, $payload);
            $parsed = $this->parseOrderResponse($response->json(), $response->status());
        } catch (\Throwable $e) {
            Log::error('SG Attractions create-order request exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'order_ref_id' => null,
                'external_status' => null,
                'message' => $e->getMessage(),
                'provider' => null,
            ];
        }

        $orderRefId = $parsed['order_ref_id'];
        // Working API returns HTTP 200 + data.order_ref_id (status 1000 optional).
        $accepted = $response->successful() && $orderRefId !== null
            && in_array($parsed['provider_status'], [1000, null], true);

        if (! $accepted) {
            Log::warning('SG Attractions create-order failed', [
                'http_status' => $response->status(),
                'provider_status' => $parsed['provider_status'],
                'message' => $parsed['message'],
                'has_order_ref_id' => $orderRefId !== null,
                'payload_keys' => array_keys($payload),
            ]);

            return [
                'success' => false,
                'order_ref_id' => $orderRefId,
                'external_status' => $parsed['external_status'],
                'message' => $parsed['message'] ?? ('SG Attractions create-order failed (HTTP ' . $response->status() . ')'),
                'provider' => $parsed['provider'],
            ];
        }

        Log::info('SG Attractions create-order succeeded', [
            'order_ref_id' => $orderRefId,
            'external_status' => $parsed['external_status'],
            'provider_status' => $parsed['provider_status'],
        ]);

        return [
            'success' => true,
            'order_ref_id' => $orderRefId,
            'external_status' => $parsed['external_status'],
            'message' => $parsed['message'] ?? 'OK',
            'provider' => $parsed['provider'],
        ];
    }

    /**
     * POST /order/update (e.g. action=pay)
     *
     * @param  array<string, string|null>  $credentials
     * @return array{success: bool, provider_status: ?int, message: ?string, provider: mixed}
     */
    public function updateOrder(string $orderRefId, string $action, array $credentials): array
    {
        $ctx = $this->authenticatedContext($credentials);
        if (! $ctx['success']) {
            Log::warning('SG Attractions order-update auth failed', [
                'message' => $ctx['message'] ?? null,
            ]);

            return [
                'success' => false,
                'provider_status' => null,
                'message' => $ctx['message'] ?? 'SG Attractions authentication failed',
                'provider' => null,
            ];
        }

        try {
            $response = Http::timeout($ctx['timeout'])
                ->withHeaders($this->headers($ctx['token']))
                ->acceptJson()
                ->asForm()
                ->post($ctx['base_url'] . '/order/update', [
                    'order_ref_id' => $orderRefId,
                    'action' => $action,
                ]);
        } catch (\Throwable $e) {
            Log::error('SG Attractions order-update request exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'provider_status' => null,
                'message' => $e->getMessage(),
                'provider' => null,
            ];
        }

        $parsed = $this->parseOrderResponse($response->json(), $response->status());
        $success = $response->successful() && $parsed['provider_status'] === 1000;

        Log::info($success ? 'SG Attractions order-update succeeded' : 'SG Attractions order-update failed', [
            'http_status' => $response->status(),
            'provider_status' => $parsed['provider_status'],
            'message' => $parsed['message'],
            'action' => $action,
            'order_ref_id' => $orderRefId,
        ]);

        return [
            'success' => $success,
            'provider_status' => $parsed['provider_status'],
            'message' => $parsed['message'],
            'provider' => $parsed['provider'],
        ];
    }

    /**
     * GET /credits — account credit balance. Used on Confirmed approve, not on create.
     *
     * @param  array<string, string|null>  $credentials
     * @return array{success: bool, credits_balance: ?float, message: ?string}
     */
    public function fetchCredits(array $credentials): array
    {
        $ctx = $this->authenticatedContext($credentials);
        if (! $ctx['success']) {
            return [
                'success' => false,
                'credits_balance' => null,
                'message' => $ctx['message'] ?? 'SG Attractions authentication failed',
            ];
        }

        try {
            $response = Http::timeout($ctx['timeout'])
                ->withHeaders($this->headers($ctx['token']))
                ->acceptJson()
                ->get($ctx['base_url'] . '/credits');
        } catch (\Throwable $e) {
            Log::warning('SG Attractions credits request exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'credits_balance' => null,
                'message' => $e->getMessage(),
            ];
        }

        $body = $response->json();
        $data = [];
        if (is_array($body)) {
            if (isset($body['response']['data']) && is_array($body['response']['data'])) {
                $data = $body['response']['data'];
            } elseif (isset($body['data']) && is_array($body['data'])) {
                $data = $body['data'];
            } elseif (isset($body['response']) && is_array($body['response'])) {
                $data = $body['response'];
            }
        }

        $balance = $data['credits_balance']
            ?? $data['credit_balance']
            ?? $data['balance']
            ?? $data['credits']
            ?? (is_array($body) ? ($body['credits_balance'] ?? $body['credit_balance'] ?? null) : null);
        $balance = is_numeric($balance) ? (float) $balance : null;

        Log::info('SG Attractions credits fetched', [
            'http_status' => $response->status(),
            'provider_status' => is_array($body) ? ($body['status'] ?? null) : null,
            'has_balance' => $balance !== null,
        ]);

        return [
            'success' => $response->successful() && (int) ($body['status'] ?? 0) === 1000,
            'credits_balance' => $balance,
            'message' => is_array($body) ? ($body['message'] ?? null) : null,
        ];
    }

    /**
     * Load orderable package/ticket SKUs (e.g. REGDAY). Catalog attraction SKUs are not orderable.
     *
     * @param  array<string, string|null>  $credentials
     * @return array<int, array<string, mixed>>
     */
    public function fetchTickets(string $skuId, ?string $visitDate, array $credentials): array
    {
        $skuId = trim($skuId);
        if ($skuId === '') {
            return [];
        }

        $cacheKey = 'sg_attractions_tickets_' . md5($skuId . '|' . (string) $visitDate);
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $ctx = $this->authenticatedContext($credentials);
        if (! $ctx['success']) {
            Log::warning('SG Attractions tickets auth failed', [
                'message' => $ctx['message'] ?? null,
            ]);

            return [];
        }

        $query = array_filter([
            'sku_id' => $skuId,
        ], static fn ($value) => $value !== null && $value !== '');

        $attempts = [
            ['method' => 'GET', 'path' => '/attraction/details', 'query' => $query],
        ];

        foreach ($attempts as $attempt) {
            $rows = $this->requestTicketRows($ctx, $attempt['method'], $attempt['path'], $attempt['query'], $skuId);
            $normalized = [];
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $ticket = $this->normalizeTicket($row, $skuId);
                if (($ticket['sku_id'] ?? '') === '') {
                    continue;
                }
                // Catalog attraction SKU is not orderable; keep distinct package/ticket SKUs.
                if (strcasecmp((string) $ticket['sku_id'], $skuId) === 0) {
                    continue;
                }
                $normalized[] = $ticket;
            }
            if ($normalized !== []) {
                Cache::put($cacheKey, $normalized, now()->addMinutes(10));

                Log::info('SG Attractions tickets fetched', [
                    'sku_id' => $skuId,
                    'path' => $attempt['path'],
                    'method' => $attempt['method'],
                    'count' => count($normalized),
                ]);

                return $normalized;
            }
        }

        Log::info('SG Attractions tickets fetched', [
            'sku_id' => $skuId,
            'count' => 0,
        ]);

        Cache::put($cacheKey, [], now()->addMinutes(15));

        return [];
    }

    /**
     * @param  array{base_url: string, timeout: int, token: string}  $ctx
     * @param  array<string, mixed>  $query
     * @return array<int, array<string, mixed>>
     */
    private function requestTicketRows(array $ctx, string $method, string $path, array $query, string $skuId): array
    {
        try {
            $request = Http::timeout($ctx['timeout'])
                ->withHeaders($this->headers($ctx['token']))
                ->acceptJson();

            if ($method === 'POST') {
                $response = $request->asForm()->post($ctx['base_url'] . $path, $query);
            } else {
                $response = $request->get($ctx['base_url'] . $path, $query);
            }
        } catch (\Throwable $e) {
            Log::warning('SG Attractions tickets request exception', [
                'sku_id' => $skuId,
                'path' => $path,
                'message' => $e->getMessage(),
            ]);

            return [];
        }

        $body = $response->json();
        $rows = is_array($body) ? $this->extractTicketRows($body, $skuId) : [];

        Log::info('SG Attractions tickets lookup', [
            'sku_id' => $skuId,
            'path' => $path,
            'method' => $method,
            'http_status' => $response->status(),
            'provider_status' => is_array($body) ? ($body['status'] ?? null) : null,
            'count' => count($rows),
        ]);

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<int, array<string, mixed>>
     */
    private function extractTicketRows(array $body, string $skuId): array
    {
        $data = $body['response']['data'] ?? $body['data'] ?? null;
        if (! is_array($data)) {
            return [];
        }

        if ($this->looksLikeTicketList($data)) {
            return array_values(array_filter($data, 'is_array'));
        }

        if (isset($data[0]) && is_array($data[0]) && (isset($data[0]['sku_id']) || isset($data[0]['sku']))) {
            return array_values(array_filter($data, 'is_array'));
        }

        foreach (['tickets', 'ticket_list', 'ticket_types', 'items', 'products', 'packages', 'availabilities', 'package_list'] as $nestedKey) {
            if (isset($data[$nestedKey]) && $this->looksLikeTicketList($data[$nestedKey])) {
                return array_values(array_filter($data[$nestedKey], 'is_array'));
            }
        }

        if ($this->looksLikeTicket($data)) {
            return [$data];
        }

        // Attraction detail / list payload: pick matching attraction.tickets.
        $attractions = isset($data[0]) && is_array($data[0]) ? $data : [$data];
        foreach ($attractions as $attraction) {
            if (! is_array($attraction)) {
                continue;
            }
            $rowSku = trim((string) ($attraction['sku_id'] ?? $attraction['sku'] ?? ''));
            if ($rowSku !== '' && strcasecmp($rowSku, $skuId) !== 0) {
                continue;
            }
            foreach (['tickets', 'ticket_list', 'ticket_types', 'items', 'packages', 'availabilities'] as $nestedKey) {
                if (isset($attraction[$nestedKey]) && $this->looksLikeTicketList($attraction[$nestedKey])) {
                    return array_values(array_filter($attraction[$nestedKey], 'is_array'));
                }
            }
        }

        return [];
    }

    /**
     * @param  mixed  $value
     */
    private function looksLikeTicketList(mixed $value): bool
    {
        if (! is_array($value) || $value === [] || ! isset($value[0]) || ! is_array($value[0])) {
            return false;
        }

        return $this->looksLikeTicket($value[0]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function looksLikeTicket(array $row): bool
    {
        foreach (['ticket_id', 'ticketId', 'ticket_sku_id', 'item_sku_id', 'ticket_sku'] as $key) {
            if (! empty($row[$key])) {
                return true;
            }
        }

        $sku = trim((string) ($row['sku_id'] ?? $row['sku'] ?? ''));

        return $sku !== '' && (
            isset($row['adult_price'])
            || isset($row['price'])
            || isset($row['ticket_name'])
            || isset($row['ticketName'])
            || isset($row['title'])
            || isset($row['name'])
            || isset($row['package_name'])
            || isset($row['lowest_ticket_price'])
        );
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeTicket(array $row, string $attractionSku): array
    {
        $ticketSku = trim((string) (
            $row['ticket_sku_id']
            ?? $row['item_sku_id']
            ?? $row['ticket_sku']
            ?? $row['sku_id']
            ?? $row['sku']
            ?? ''
        ));
        $ticketId = trim((string) ($row['ticket_id'] ?? $row['ticketId'] ?? $row['id'] ?? ''));
        $rawPrice = $row['price'] ?? null;
        if (is_numeric($rawPrice)) {
            $adult = (float) $rawPrice;
        } elseif (is_array($rawPrice)) {
            $adult = (float) ($rawPrice['adult'] ?? $rawPrice['price'] ?? 0);
        } else {
            $adult = (float) (
                $row['adult_price']
                ?? $row['adult']
                ?? $row['original_price']
                ?? $row['retail_price']
                ?? $row['lowest_ticket_price']
                ?? 0
            );
        }
        $child = is_array($rawPrice)
            ? (float) ($rawPrice['child'] ?? $row['child_price'] ?? $row['child'] ?? $adult)
            : (float) ($row['child_price'] ?? $row['child'] ?? $adult);

        $ticketName = trim((string) ($row['type'] ?? ''));
        if ($ticketName === '') {
            $ticketName = (string) ($row['ticket_name'] ?? $row['ticketName'] ?? $row['title'] ?? $row['name'] ?? 'Ticket');
        }

        return [
            'ticket_id' => $ticketId,
            'ticketId' => $ticketId !== '' ? $ticketId : $ticketSku,
            'sku_id' => $ticketSku !== '' ? $ticketSku : $ticketId,
            'ticketName' => $ticketName,
            'price' => [
                'adult' => $adult,
                'child' => $child,
            ],
            'attraction_sku_id' => $attractionSku,
            'synthetic' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function normalizeAttraction(array $item): array
    {
        $tickets = [];
        foreach (['tickets', 'ticket_list', 'ticket_types', 'packages'] as $nestedKey) {
            if (isset($item[$nestedKey]) && is_array($item[$nestedKey]) && $item[$nestedKey] !== []) {
                $skuId = (string) ($item['sku_id'] ?? '');
                $tickets = array_values(array_filter(
                    array_map(fn (array $row) => $this->normalizeTicket($row, $skuId), array_filter($item[$nestedKey], 'is_array')),
                    static fn (array $row) => ($row['sku_id'] ?? '') !== '' || ($row['ticket_id'] ?? '') !== ''
                ));
                break;
            }
        }

        return [
            'sku_id' => (string) ($item['sku_id'] ?? ''),
            'title' => (string) ($item['title'] ?? ''),
            'lowest_ticket_price' => (float) ($item['lowest_ticket_price'] ?? 0),
            'highest_ticket_price' => (float) ($item['highest_ticket_price'] ?? 0),
            'currency' => 'SGD',
            'supplier_code' => $this->code(),
            'tickets' => $tickets,
            'raw' => $item,
        ];
    }

    /**
     * @param  array<string, string|null>  $credentials
     * @return array{success: bool, base_url?: string, timeout?: int, token?: string, message?: string}
     */
    private function authenticatedContext(array $credentials): array
    {
        $baseUrl = rtrim((string) ($credentials['base_url'] ?? ''), '/');
        $apiKey = trim((string) ($credentials['api_key'] ?? ''));
        $secretKey = trim((string) ($credentials['secret_key'] ?? ''));
        $staticBearer = trim((string) ($credentials['bearer_token'] ?? ''));

        if ($baseUrl === '') {
            $baseUrl = rtrim((string) config('services.sg_attractions.base_url', ''), '/');
        }
        if ($apiKey === '') {
            $apiKey = trim((string) config('services.sg_attractions.api_key', ''));
        }
        if ($secretKey === '') {
            $secretKey = trim((string) config('services.sg_attractions.secret_key', ''));
        }
        if ($staticBearer === '') {
            $staticBearer = trim((string) config('services.sg_attractions.bearer_token', ''));
        }

        if ($baseUrl === '') {
            return [
                'success' => false,
                'message' => 'SG Attractions API is not configured. Set SG_ATTRACTIONS_API_BASE_URL in .env',
            ];
        }

        if ($apiKey === '' && $staticBearer === '') {
            return [
                'success' => false,
                'message' => 'SG Attractions API key is not configured. Set SG_ATTRACTIONS_API_KEY in .env',
            ];
        }

        if ($secretKey === '' && $staticBearer === '') {
            return [
                'success' => false,
                'message' => 'Set SG_ATTRACTIONS_SECRET_KEY in .env (reseller secret from SG Attractions).',
            ];
        }

        $timeout = (int) ($credentials['timeout'] ?? config('services.sg_attractions.timeout', 60));
        $timeout = $timeout > 0 ? $timeout : 60;

        $auth = $this->getBearerToken($baseUrl, $apiKey, $secretKey, $staticBearer, $timeout);
        if (! $auth['success']) {
            return [
                'success' => false,
                'message' => $auth['message'] ?? 'Failed to authenticate with SG Attractions API.',
            ];
        }

        return [
            'success' => true,
            'base_url' => $baseUrl,
            'timeout' => $timeout,
            'token' => $auth['token'],
        ];
    }

    /**
     * @return array{provider_status: ?int, order_ref_id: ?string, external_status: ?string, message: ?string, provider: mixed}
     */
    private function parseOrderResponse(mixed $body, int $httpStatus): array
    {
        if (! is_array($body)) {
            $body = [];
        }

        $orderData = [];
        if (isset($body['data']) && is_array($body['data'])) {
            $orderData = $body['data'];
        } elseif (isset($body['response']['data']) && is_array($body['response']['data'])) {
            $orderData = $body['response']['data'];
        }

        $orderRefId = trim((string) (
            $orderData['order_ref_id']
            ?? $body['order_ref_id']
            ?? ($body['response']['order_ref_id'] ?? '')
        ));
        if ($orderRefId === '') {
            $orderRefId = $this->findOrderRefId($body);
        }
        $message = (string) ($body['message'] ?? '');
        $fieldErrors = $this->extractFieldErrors($body);
        if ($fieldErrors !== '') {
            $message = trim($message) !== '' ? ($message . ': ' . $fieldErrors) : $fieldErrors;
        }

        return [
            'provider_status' => isset($body['status']) ? (int) $body['status'] : null,
            'order_ref_id' => $orderRefId !== '' ? $orderRefId : null,
            'external_status' => isset($orderData['status']) ? (string) $orderData['status'] : null,
            'message' => $message !== '' ? $message : ('HTTP ' . $httpStatus),
            'provider' => $body,
        ];
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function findOrderRefId(array $node): string
    {
        foreach (['order_ref_id', 'order_ref_no', 'orderRefId'] as $key) {
            $value = $node[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        foreach ($node as $value) {
            if (! is_array($value)) {
                continue;
            }
            $found = $this->findOrderRefId($value);
            if ($found !== '') {
                return $found;
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function extractFieldErrors(array $body): string
    {
        $error = $body['response']['data']['error'] ?? $body['data']['error'] ?? null;
        if (! is_array($error) || $error === []) {
            return '';
        }

        $parts = [];
        foreach ($error as $field => $detail) {
            $parts[] = $field . ': ' . (is_string($detail) ? $detail : json_encode($detail));
        }

        return implode(', ', $parts);
    }

    /**
     * TDP /order/create reads application/x-www-form-urlencoded body fields
     * (customer_name, item_sku_id_1, …). JSON bodies are treated as empty → 1001.
     *
     * @param  array{base_url: string, timeout: int, token: string}  $ctx
     * @param  array<string, mixed>  $payload
     */
    private function postCreateOrder(array $ctx, array $payload): \Illuminate\Http\Client\Response
    {
        return Http::timeout($ctx['timeout'])
            ->withHeaders($this->headers($ctx['token']))
            ->acceptJson()
            ->asForm()
            ->post($ctx['base_url'] . '/order/create', $payload);
    }

    /**
     * @return array{success:bool,token?:string,message?:string}
     */
    private function getBearerToken(
        string $baseUrl,
        string $apiKey,
        string $secretKey,
        string $staticBearer,
        int $timeout,
    ): array {
        if ($staticBearer !== '') {
            return ['success' => true, 'token' => $staticBearer];
        }

        $cacheKey = 'sg_attractions_bearer_token_' . md5($apiKey);
        $cachedToken = Cache::get($cacheKey);
        if (is_string($cachedToken) && $cachedToken !== '') {
            return ['success' => true, 'token' => $cachedToken];
        }

        $sessionResponse = Http::timeout($timeout > 0 ? $timeout : 60)
            ->asForm()
            ->withHeaders($this->headers())
            ->post($baseUrl . '/reseller_auth/session', [
                'apikey' => $apiKey,
            ]);

        if (! $sessionResponse->successful()) {
            return [
                'success' => false,
                'message' => 'Failed to request SG Attractions session.',
            ];
        }

        $sessionBody = $sessionResponse->json();
        if ((int) ($sessionBody['status'] ?? 0) !== 1000) {
            return [
                'success' => false,
                'message' => $sessionBody['message'] ?? 'SG Attractions session request failed.',
            ];
        }

        $sessionKey = (string) ($sessionBody['response']['data']['session_key'] ?? '');
        if ($sessionKey === '') {
            return [
                'success' => false,
                'message' => 'SG Attractions session key missing in provider response.',
            ];
        }

        $authKey = md5($sessionKey . $secretKey);

        $tokenResponse = Http::timeout($timeout > 0 ? $timeout : 60)
            ->asForm()
            ->withHeaders($this->headers())
            ->post($baseUrl . '/reseller_auth/token', [
                'session_key' => $sessionKey,
                'auth_key' => $authKey,
            ]);

        if (! $tokenResponse->successful()) {
            return [
                'success' => false,
                'message' => 'Failed to request SG Attractions auth token.',
            ];
        }

        $tokenBody = $tokenResponse->json();
        if ((int) ($tokenBody['status'] ?? 0) !== 1000) {
            return [
                'success' => false,
                'message' => $tokenBody['message'] ?? 'SG Attractions token request failed.',
            ];
        }

        $authToken = (string) ($tokenBody['response']['data']['auth_token'] ?? '');
        if ($authToken === '') {
            return [
                'success' => false,
                'message' => 'SG Attractions auth token missing in provider response.',
            ];
        }

        $expiresIn = $tokenBody['response']['data']['expires_in'] ?? null;
        $ttlSeconds = 3600;
        if (is_string($expiresIn) && $expiresIn !== '') {
            try {
                $ttlSeconds = max(60, Carbon::parse($expiresIn)->diffInSeconds(now()) - 60);
            } catch (\Throwable) {
                $ttlSeconds = 3600;
            }
        }

        Cache::put($cacheKey, $authToken, $ttlSeconds);

        return ['success' => true, 'token' => $authToken];
    }

    /**
     * @return array<string, string>
     */
    private function headers(?string $bearerToken = null): array
    {
        $headers = [
            'Accept' => 'application/json',
            'X-API-Version' => (string) config('services.sg_attractions.api_version', 'v1.10'),
        ];

        if ($bearerToken) {
            $headers['Authorization'] = 'BEARER ' . $bearerToken;
        }

        return $headers;
    }
}
