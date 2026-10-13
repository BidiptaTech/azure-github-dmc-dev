<?php

namespace App\Services\HotelSuppliers\Tinivia;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin transport for the Tinivia JSON API (fetchHotels, checkRoomAvailability,
 * confirmBookingRequest).
 *
 * Credentials come from Supplier Master, falling back to config/services.php because
 * `env()` returns null once the config cache is built. The Supplier Master code is
 * `tinivia` while the services key is `tiniva`, so both spellings are honoured.
 */
class TiniviaClient
{
    /**
     * @param  array<string, string|null>  $credentials
     */
    public function __construct(private array $credentials = []) {}

    public function credential(string $key, string $default = ''): string
    {
        if (array_key_exists($key, $this->credentials)) {
            $value = trim((string) $this->credentials[$key]);

            return $value !== '' ? $value : $default;
        }

        $value = trim((string) config("services.tiniva.{$key}", ''));

        if ($value === '') {
            $value = trim((string) config("services.tinivia.{$key}", ''));
        }

        return $value !== '' ? $value : $default;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function fetchHotels(array $payload): array
    {
        return $this->post('fetchHotels', $payload);
    }

    /**
     * Live availability for a single property, including the `roomRateKey` needed to book.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function checkRoomAvailability(array $payload): array
    {
        return $this->post('checkRoomAvailability', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function confirmBookingRequest(array $payload): array
    {
        return $this->post('confirmBookingRequest', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array $payload): array
    {
        $baseUrl = rtrim($this->credential('base_url'), '/');
        $apiKey = $this->credential('api_key');

        if ($baseUrl === '' || $apiKey === '') {
            throw new RuntimeException('Tinivia credentials are incomplete (base URL and API key required).');
        }

        $timeout = (int) $this->credential('timeout', '30');

        $response = Http::timeout($timeout > 0 ? $timeout : 30)
            ->withHeaders($this->headers($apiKey))
            ->acceptJson()
            ->asJson()
            ->post($baseUrl . '/api/ext/' . $endpoint, $payload);

        if (! $response->successful()) {
            Log::warning("Tinivia {$endpoint} failed", [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);

            throw new RuntimeException("Tinivia {$endpoint} failed with HTTP " . $response->status());
        }

        $body = $response->json();

        if (! is_array($body)) {
            throw new RuntimeException("Tinivia {$endpoint} returned an invalid response.");
        }

        return $body;
    }

    /**
     * @return array<string, string>
     */
    public function headers(?string $apiKey = null): array
    {
        $headers = [
            'apikey' => $apiKey ?? $this->credential('api_key'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $jwt = $this->credential('jwt');
        if ($jwt !== '') {
            $headers['Jwt'] = $jwt;
        }

        $entityId = $this->credential('entity_id');
        if ($entityId !== '') {
            $headers['entityId'] = $entityId;
        }

        return $headers;
    }

    /**
     * Builds the error text Tinivia returned, or null when the call succeeded.
     *
     * @param  array<string, mixed>  $body
     */
    public function failureReason(array $body): ?string
    {
        foreach (['success', 'status'] as $flag) {
            if (array_key_exists($flag, $body) && is_bool($body[$flag]) && $body[$flag] === false) {
                return $this->errorText($body);
            }
        }

        foreach (['error', 'errorMessage', 'errors'] as $key) {
            if (filled($body[$key] ?? null)) {
                return $this->errorText($body);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function errorText(array $body): string
    {
        foreach (['errorMessage', 'error', 'message', 'msg', 'statusMessage'] as $key) {
            $value = $body[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }

            if (is_array($value)) {
                $flat = array_filter(array_map(
                    fn ($item) => is_string($item) ? trim($item) : null,
                    $value,
                ));

                if ($flat !== []) {
                    return implode('; ', $flat);
                }
            }
        }

        $errors = $body['errors'] ?? null;

        if (is_array($errors)) {
            $messages = [];

            foreach ($errors as $error) {
                if (is_string($error)) {
                    $messages[] = $error;
                } elseif (is_array($error)) {
                    $messages[] = (string) ($error['message'] ?? $error['description'] ?? '');
                }
            }

            $messages = array_filter(array_map('trim', $messages));

            if ($messages !== []) {
                return implode('; ', $messages);
            }
        }

        return 'The supplier rejected the request.';
    }
}
