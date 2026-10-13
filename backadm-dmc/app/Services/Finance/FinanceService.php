<?php

namespace App\Services\Finance;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * HTTP client facade for Laravel → Azure Finance Function App.
 *
 * When to call: any wallet, settlement, refund, or commission operation.
 * Why: keeps finance domain logic in the Python microservice (single source of truth).
 */
class FinanceService
{
    protected string $baseUrl;

    protected string $bearerToken;

    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('finance.base_url'), '/');
        $this->bearerToken = (string) config('finance.bearer_token');
        $this->timeout = (int) config('finance.timeout_seconds', 30);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array $payload): array
    {
        if (! isset($payload['idempotency_key'])) {
            $payload['idempotency_key'] = (string) Str::uuid();
        }

        $url = $this->baseUrl.$endpoint;

        Log::info('finance_service_request', [
            'url' => $url,
            'idempotency_key' => $payload['idempotency_key'],
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->bearerToken)
                ->acceptJson()
                ->post($url, $payload);

            if ($response->failed()) {
                Log::error('finance_service_error', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                throw new RuntimeException(
                    $response->json('message') ?? 'Finance service request failed',
                    $response->status()
                );
            }

            $body = $response->json();

            if (! ($body['success'] ?? false)) {
                throw new RuntimeException(
                    $body['message'] ?? 'Finance service returned failure',
                    $response->status()
                );
            }

            return $body;
        } catch (ConnectionException $e) {
            Log::error('finance_service_connection_error', ['url' => $url, 'error' => $e->getMessage()]);
            throw new RuntimeException('Finance service unavailable: '.$e->getMessage(), 503, $e);
        } catch (RequestException $e) {
            Log::error('finance_service_request_exception', ['url' => $url, 'error' => $e->getMessage()]);
            throw new RuntimeException('Finance service error: '.$e->getMessage(), 502, $e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $endpoint): array
    {
        $url = $this->baseUrl.$endpoint;

        $response = Http::timeout($this->timeout)
            ->withToken($this->bearerToken)
            ->acceptJson()
            ->get($url);

        return $response->json() ?? [];
    }

    public function healthCheck(): array
    {
        return $this->get(config('finance.endpoints.health'));
    }
}
