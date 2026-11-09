<?php

namespace App\Services\HotelSuppliers\Hotelbeds;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin transport for Hotelbeds Booking API (Availability, CheckRate, Bookings).
 *
 * Credentials come from Supplier Master / config/services.php. rateKey is always
 * treated as an opaque string — never parsed.
 */
class HotelbedsClient
{
    /**
     * @param  array<string, string|null>  $credentials
     */
    public function __construct(private array $credentials = []) {}

    public function baseUrl(): string
    {
        $base = rtrim((string) ($this->credentials['base_url'] ?? ''), '/');

        if ($base === '' && ! array_key_exists('base_url', $this->credentials)) {
            $base = rtrim((string) config('services.hotelbeds.base_url', ''), '/');
        }

        return $base;
    }

    public function apiKey(): string
    {
        $key = trim((string) ($this->credentials['api_key'] ?? ''));

        if ($key === '' && ! array_key_exists('api_key', $this->credentials)) {
            $key = trim((string) config('services.hotelbeds.api_key', ''));
        }

        return $key;
    }

    public function apiSecret(): string
    {
        $secret = trim((string) ($this->credentials['api_secret'] ?? ''));

        if ($secret === '' && ! array_key_exists('api_secret', $this->credentials)) {
            $secret = trim((string) config('services.hotelbeds.api_secret', ''));
        }

        return $secret;
    }

    public function timeout(int $fallback = 30): int
    {
        $timeout = (int) ($this->credentials['timeout'] ?? config('services.hotelbeds.timeout', $fallback));

        return $timeout > 0 ? $timeout : $fallback;
    }

    public function assertCredentials(): void
    {
        if ($this->baseUrl() === '' || $this->apiKey() === '' || $this->apiSecret() === '') {
            throw new RuntimeException('Hotelbeds credentials are incomplete (base_url, api_key and api_secret required).');
        }
    }

    /**
     * @return array<string, string>
     */
    public function authHeaders(): array
    {
        $apiKey = $this->apiKey();
        $secret = $this->apiSecret();
        $timestamp = (string) time();
        $signature = hash('sha256', $apiKey . $secret . $timestamp);

        return [
            'Api-key' => $apiKey,
            'X-Signature' => $signature,
            'Accept' => 'application/json',
            'Accept-Encoding' => 'gzip',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Availability for specific hotel code(s) — used when a stored rateKey is stale.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function availability(array $payload): array
    {
        return $this->postJson('/hotel-api/1.0/hotels', $payload, $this->timeout(30), 'availability');
    }

    /**
     * CheckRate for a single opaque rateKey (always one key per best practices).
     *
     * @return array<string, mixed>
     */
    public function checkRate(string $rateKey): array
    {
        $rateKey = trim($rateKey);

        if ($rateKey === '') {
            throw new RuntimeException('Hotelbeds rateKey is missing for CheckRate.');
        }

        return $this->postJson('/hotel-api/1.0/checkrates', [
            'rooms' => [
                ['rateKey' => $rateKey],
            ],
        ], $this->timeout(30), 'checkrates');
    }

    /**
     * Booking confirmation — Hotelbeds requires ≥ 60s timeout.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function book(array $payload): array
    {
        $timeout = max(60, $this->timeout(60));
        return $this->postJson('/hotel-api/1.0/bookings', $payload, $timeout, 'bookings');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function postJson(string $path, array $payload, int $timeout, string $operation): array
    {
        $this->assertCredentials();

        $url = $this->baseUrl() . $path;

        $response = Http::timeout($timeout)
            ->withHeaders($this->authHeaders())
            ->acceptJson()
            ->asJson()
            ->post($url, $payload);

        if (! $response->successful()) {
            $body = $response->json();
            $message = $this->errorMessage($body) ?: ('Hotelbeds ' . $operation . ' failed with HTTP ' . $response->status());

            Log::warning('Hotelbeds ' . $operation . ' failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $this->safePayloadLog($payload, $operation),
            ]);

            throw new RuntimeException($message);
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    /**
     * @param  mixed  $body
     */
    public function errorMessage(mixed $body): string
    {
        if (! is_array($body)) {
            return '';
        }

        $error = $body['error'] ?? null;

        if (is_array($error)) {
            $code = trim((string) ($error['code'] ?? ''));
            $message = trim((string) ($error['message'] ?? ''));

            if ($code !== '' && $message !== '') {
                return $message . ' (' . $code . ')';
            }

            return $message !== '' ? $message : $code;
        }

        return trim((string) ($body['message'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function safePayloadLog(array $payload, string $operation): array
    {
        if ($operation === 'checkrates' || $operation === 'bookings') {
            // rateKey is opaque and long — keep presence only in logs.
            $copy = $payload;
            if (isset($copy['rooms']) && is_array($copy['rooms'])) {
                foreach ($copy['rooms'] as $i => $room) {
                    if (is_array($room) && isset($room['rateKey'])) {
                        $copy['rooms'][$i]['rateKey'] = '[redacted:' . strlen((string) $room['rateKey']) . ']';
                    }
                }
            }

            return $copy;
        }

        return $payload;
    }
}
