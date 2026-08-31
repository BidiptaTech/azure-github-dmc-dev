<?php

namespace App\Services\HotelSuppliers\MgBedbank;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin transport for the MG Bedbank JSON API (GetDestinations, GetHotelList, SearchHotel).
 *
 * Credentials come from Supplier Master, falling back to config/services.php because
 * `env()` returns null once the config cache is built.
 */
class MgBedbankClient
{
    /**
     * MG treats FromDateTime as an incremental "modified since" filter, so a full
     * catalogue pull needs a date old enough to predate every record.
     */
    public const EPOCH = '2019-05-16 00:00:00';

    /**
     * @param  array<string, string|null>  $credentials
     */
    public function __construct(private array $credentials = []) {}

    public function credential(string $key, string $default = ''): string
    {
        $value = trim((string) ($this->credentials[$key] ?? config("services.mg_bedbank.{$key}", $default)));

        return $value !== '' ? $value : $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDestinations(string $fromDateTime = self::EPOCH, string $continent = ''): array
    {
        $payload = [
            'Language' => $this->credential('language', 'En'),
            'FromDateTime' => $fromDateTime,
        ];

        if ($continent !== '') {
            $payload['Continent'] = $continent;
        }

        return $this->post('GetDestinations', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function getHotelList(string $countryCode, string $cityCode, string $fromDateTime = self::EPOCH): array
    {
        return $this->post('GetHotelList', [
            'Country' => $countryCode,
            'City' => $cityCode,
            'Hotels' => ['Code' => ['']],
            'FromDateTime' => $fromDateTime,
            'DetailLevel' => 'full',
            'Xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            'Xsd' => 'http://www.w3.org/2001/XMLSchema',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function searchHotel(array $payload): array
    {
        return $this->post('SearchHotel', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function recheckHotel(array $payload): array
    {
        return $this->post('RecheckHotel', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function bookHotel(array $payload): array
    {
        return $this->post('BookHotel', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array $payload): array
    {
        $baseUrl = rtrim($this->credential('base_url'), '/');
        $agencyCode = $this->credential('agency_code');
        $username = $this->credential('username');
        $password = $this->credential('password');

        if ($baseUrl === '' || $agencyCode === '' || $username === '' || $password === '') {
            throw new RuntimeException(
                'MG Bedbank credentials are incomplete (base URL, agency code, username and password required).'
            );
        }

        $payload = array_merge([
            'Login' => [
                'AgencyCode' => $agencyCode,
                'Username' => $username,
                'Password' => $password,
            ],
        ], $payload);

        $timeout = (int) $this->credential('timeout', '30');

        $response = Http::timeout($timeout > 0 ? $timeout : 30)
            ->acceptJson()
            ->asJson()
            ->post($baseUrl . '/' . $endpoint, $payload);

        if (! $response->successful()) {
            Log::warning("MG Bedbank {$endpoint} failed", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException("MG Bedbank {$endpoint} failed with HTTP " . $response->status());
        }

        $body = $response->json();

        if (! is_array($body)) {
            throw new RuntimeException("MG Bedbank {$endpoint} returned an invalid response.");
        }

        return $body;
    }

    /**
     * Builds the error text MG returned, or null when the call succeeded.
     */
    public function failureReason(array $body): ?string
    {
        if (($body['status'] ?? false) === true) {
            return null;
        }

        $message = trim((string) ($body['errorMessage'] ?? ''));
        $errorCode = trim((string) ($body['errorCode'] ?? ''));
        $detail = $message !== '' ? $message : 'The supplier rejected the request.';

        return $errorCode !== '' ? "{$detail} [{$errorCode}]" : $detail;
    }
}
