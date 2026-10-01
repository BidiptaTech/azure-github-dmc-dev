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
            throw new RuntimeException('SG Attractions API is not configured. Set SG_ATTRACTIONS_API_BASE_URL in .env');
        }

        if ($apiKey === '' && $staticBearer === '') {
            throw new RuntimeException('SG Attractions API key is not configured. Set SG_ATTRACTIONS_API_KEY in .env');
        }

        if ($secretKey === '' && $staticBearer === '') {
            throw new RuntimeException('Set SG_ATTRACTIONS_SECRET_KEY in .env (reseller secret from SG Attractions). For quick testing, paste a token from Postman into SG_ATTRACTIONS_BEARER_TOKEN.');
        }

        $timeout = (int) ($credentials['timeout'] ?? config('services.sg_attractions.timeout', 60));

        $auth = $this->getBearerToken($baseUrl, $apiKey, $secretKey, $staticBearer, $timeout);
        if (! $auth['success']) {
            throw new RuntimeException($auth['message'] ?? 'Failed to authenticate with SG Attractions API.');
        }

        $query = array_filter([
            'display_limit' => $request->displayLimit,
            'current_page' => $request->currentPage,
        ], static fn ($value) => $value !== null && $value !== '');

        $response = Http::timeout($timeout > 0 ? $timeout : 60)
            ->withHeaders($this->headers($auth['token']))
            ->acceptJson()
            ->get($baseUrl . '/attractions', $query);

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
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function normalizeAttraction(array $item): array
    {
        return [
            'sku_id' => (string) ($item['sku_id'] ?? ''),
            'title' => (string) ($item['title'] ?? ''),
            'lowest_ticket_price' => (float) ($item['lowest_ticket_price'] ?? 0),
            'highest_ticket_price' => (float) ($item['highest_ticket_price'] ?? 0),
            'currency' => 'SGD',
            'supplier_code' => $this->code(),
            'raw' => $item,
        ];
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
