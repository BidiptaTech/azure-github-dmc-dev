<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use App\Services\AttractionSuppliers\OnlineAttractionAggregator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

/**
 * Mobile / SPA API for online (supplier-backed) attractions.
 *
 * Offline catalogue attractions stay on Api\HomeController. This controller only
 * talks to the country-mapped supplier via OnlineAttractionAggregator.
 *
 * List items are shaped like Api\HomeController::attractionListing so the agent
 * SPA can reuse the same list UI.
 */
class OnlineAttractionController extends Controller
{
    use Concerns\ActsAsRequestDmc;

    /**
     * Attraction list for a city / visit date.
     *
     * GET /api/v1/online-attractions
     *
     * Required (agent SPA):
     *   dmc_id          int         Operating DMC. Its Master DMC online_api / live_api
     *                               choose demo vs live credentials; this DMC's markup applies.
     *
     * Optional:
     *   city            string|int  City name or numeric city id
     *   visit_date      date        Y-m-d (also accepts visitDate)
     *   pax_info        string      "adults|children" or "adults|children|ages"
     *   adults          int         Alternative to pax_info
     *   children        int         Optional when using adults
     *   child_ages      array|string Optional ages when children > 0
     *   display_limit   int         Page size (1–500)
     *   current_page    int         Page number (min 1)
     */
    public function lists(Request $request, OnlineAttractionAggregator $aggregator): JsonResponse
    {
        $payload = $this->normalizeInput($request);

        try {
            $validated = validator($payload, [
                'dmc_id' => ['required', 'integer', 'min:1'],
                'city' => ['nullable', 'string', 'max:255'],
                'visit_date' => ['nullable', 'date_format:Y-m-d'],
                'pax_info' => ['nullable', 'string', 'max:50', 'regex:/^\d+\|\d+(\|[\d,\s;:]+)?$/'],
                'display_limit' => ['nullable', 'integer', 'min:1', 'max:500'],
                'current_page' => ['nullable', 'integer', 'min:1'],
            ], [
                'dmc_id.required' => 'dmc_id is required so we can load that DMC\'s online API settings.',
                'pax_info.regex' => 'pax_info must look like "2|0" or "2|1|8" (adults|children|optional ages).',
            ])->validate();
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        $city = isset($validated['city']) ? trim((string) $validated['city']) : '';
        $visitDate = isset($validated['visit_date']) && $validated['visit_date'] !== ''
            ? (string) $validated['visit_date']
            : null;
        $paxInfo = isset($validated['pax_info']) && trim((string) $validated['pax_info']) !== ''
            ? trim((string) $validated['pax_info'])
            : null;
        $displayLimit = isset($validated['display_limit']) ? (int) $validated['display_limit'] : null;
        $currentPage = isset($validated['current_page']) ? (int) $validated['current_page'] : null;
        $dmcId = (int) $validated['dmc_id'];

        try {
            $dmc = $this->resolveRequestDmc($dmcId);
            $result = $this->withDmcAuthContext(
                $dmc,
                fn () => $aggregator->search(
                    $visitDate,
                    $city !== '' ? $city : null,
                    $paxInfo,
                    $displayLimit,
                    $currentPage,
                ),
            );

            $resolvedCity = (string) ($result['city'] ?? ($city !== '' ? $city : ''));
            $countryName = $this->resolveCountryName($result['country_id'] ?? null, $resolvedCity);
            $taxPercentage = $this->resolveTaxPercentage($countryName);

            // Same top-level array shape as GET /api/v1/attraction
            return response()->json($this->presentAttractions(
                $result['attractions'] ?? [],
                $dmc,
                $resolvedCity,
                $countryName,
                $taxPercentage,
                $visitDate,
                (string) ($result['supplier_code'] ?? ''),
                (string) ($result['api_environment'] ?? ''),
            ));
        } catch (RuntimeException $e) {
            Log::warning('API online attraction lists failed', [
                'dmc_id' => $dmcId,
                'city' => $city !== '' ? $city : null,
                'visit_date' => $visitDate,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('API online attraction lists exception', [
                'dmc_id' => $dmcId,
                'city' => $city !== '' ? $city : null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch online attractions right now. Please try again.',
            ], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeInput(Request $request): array
    {
        $city = $request->input('city', $request->input('city_name'));
        $visitDate = $request->input('visit_date', $request->input('visitDate'));
        $displayLimit = $request->input('display_limit', $request->input('displayLimit'));
        $currentPage = $request->input('current_page', $request->input('currentPage'));
        $dmcId = $request->input('dmc_id', $request->input('dmcId'));

        $paxInfo = $request->input('pax_info', $request->input('paxInfo'));

        if ($paxInfo === null || trim((string) $paxInfo) === '') {
            $paxInfo = $this->buildPaxInfoFromParts($request);
        }

        return [
            'dmc_id' => $dmcId,
            'city' => is_scalar($city) ? (string) $city : null,
            'visit_date' => is_scalar($visitDate) ? (string) $visitDate : null,
            'pax_info' => is_scalar($paxInfo) ? trim((string) $paxInfo) : null,
            'display_limit' => $displayLimit,
            'current_page' => $currentPage,
        ];
    }

    private function buildPaxInfoFromParts(Request $request): ?string
    {
        if (! $request->filled('adults') && ! $request->filled('adult')) {
            return null;
        }

        $adults = max(1, (int) $request->input('adults', $request->input('adult', 1)));
        $children = max(0, (int) $request->input('children', $request->input('child', 0)));

        $agesRaw = $request->input('child_ages', $request->input('childAges', []));
        $ages = [];

        if (is_string($agesRaw) && trim($agesRaw) !== '') {
            $ages = preg_split('/[,;:\s]+/', trim($agesRaw)) ?: [];
        } elseif (is_array($agesRaw)) {
            $ages = $agesRaw;
        }

        $ages = array_values(array_filter(
            array_map(static fn ($age) => (int) $age, $ages),
            static fn (int $age) => $age > 0,
        ));

        if ($children <= 0) {
            return $adults . '|0';
        }

        if ($ages === []) {
            return $adults . '|' . $children;
        }

        return $adults . '|' . $children . '|' . implode(',', array_slice($ages, 0, $children));
    }

    /**
     * Map supplier attractions into the offline attractionListing shape.
     *
     * @param  array<int, mixed>  $attractions
     * @return array<int, array<string, mixed>>
     */
    private function presentAttractions(
        array $attractions,
        User $dmc,
        string $city,
        string $country,
        string|float|int $taxPercentage,
        ?string $visitDate,
        string $supplierCode,
        string $apiEnvironment,
    ): array {
        $presented = [];
        $dmcId = (int) $dmc->userId;
        $dmcName = (string) ($dmc->name ?? '');

        foreach ($attractions as $attraction) {
            if (! is_array($attraction)) {
                continue;
            }

            $skuId = trim((string) ($attraction['sku_id'] ?? ''));
            $raw = is_array($attraction['onlineAttractionRaw'] ?? null)
                ? $attraction['onlineAttractionRaw']
                : (is_array($attraction['raw'] ?? null) ? $attraction['raw'] : []);

            $prices = $this->resolveListPrices($attraction);
            $image = $this->firstImage($raw, $attraction);
            $additionalImages = $this->additionalImages($raw, $image);

            $availability = [];
            if ($visitDate) {
                $availability[$visitDate] = 'Available';
            }

            $presented[] = [
                'id' => $skuId !== '' ? $skuId : ($attraction['title'] ?? null),
                'attraction_name' => (string) ($attraction['title'] ?? $attraction['name'] ?? $raw['title'] ?? ''),
                'dmc_adult_price' => $prices['adult'],
                'dmc_senior_price' => $prices['senior'],
                'dmc_child_price' => $prices['child'],
                'dmc_id' => $dmcId,
                'dmc_user_name' => $dmcName,
                'travClicks_adult_price' => 0,
                'travClicks_senior_price' => 0,
                'travClicks_child_price' => 0,
                'travclicks_dmc_id' => null,
                'city' => $city !== '' ? $city : (string) ($raw['city'] ?? $raw['location'] ?? ''),
                'country' => $country !== '' ? $country : (string) ($raw['country'] ?? ''),
                'image' => $image,
                'morning_opening' => (int) ($raw['morning_opening'] ?? 0),
                'additional_images' => $additionalImages,
                'afternoon_opening' => (int) ($raw['afternoon_opening'] ?? 0),
                'evening_opening' => (int) ($raw['evening_opening'] ?? 0),
                'night_opening' => (int) ($raw['night_opening'] ?? 0),
                'availability' => $availability,
                'time_slots' => $this->timeSlots($raw),
                'tax_percentage' => is_numeric($taxPercentage)
                    ? number_format((float) $taxPercentage, 2, '.', '')
                    : (string) $taxPercentage,
                'created_at' => $raw['created_at'] ?? null,
                // Online booking extras (ignored by offline list UI)
                'sku_id' => $skuId !== '' ? $skuId : null,
                'supplier_code' => $attraction['supplier_code'] ?? ($supplierCode !== '' ? $supplierCode : null),
                'api_environment' => $attraction['api_environment'] ?? ($apiEnvironment !== '' ? $apiEnvironment : null),
                'is_online' => true,
                'tickets' => $this->presentTickets(
                    is_array($attraction['tickets'] ?? null) ? $attraction['tickets'] : []
                ),
            ];
        }

        return $presented;
    }

    /**
     * @param  array<string, mixed>  $attraction
     * @return array{adult: float|int, child: float|int, senior: float|int}
     */
    private function resolveListPrices(array $attraction): array
    {
        $adults = [];
        $children = [];
        $seniors = [];

        $tickets = is_array($attraction['tickets'] ?? null) ? $attraction['tickets'] : [];
        foreach ($tickets as $ticket) {
            if (! is_array($ticket)) {
                continue;
            }
            $price = is_array($ticket['price'] ?? null) ? $ticket['price'] : [];
            if (isset($price['adult']) && is_numeric($price['adult']) && (float) $price['adult'] > 0) {
                $adults[] = (float) $price['adult'];
            }
            if (isset($price['child']) && is_numeric($price['child']) && (float) $price['child'] > 0) {
                $children[] = (float) $price['child'];
            }
            if (isset($price['senior']) && is_numeric($price['senior']) && (float) $price['senior'] > 0) {
                $seniors[] = (float) $price['senior'];
            }
        }

        $fallbackAdult = (float) ($attraction['lowest_ticket_price'] ?? $attraction['lowestPrice'] ?? 0);
        $adult = $adults !== [] ? min($adults) : ($fallbackAdult > 0 ? $fallbackAdult : 0);
        $child = $children !== [] ? min($children) : $adult;
        $senior = $seniors !== [] ? min($seniors) : $adult;

        return [
            'adult' => $this->money($adult),
            'child' => $this->money($child),
            'senior' => $this->money($senior),
        ];
    }

    /**
     * @param  array<int, mixed>  $tickets
     * @return array<int, array<string, mixed>>
     */
    private function presentTickets(array $tickets): array
    {
        $presented = [];

        foreach ($tickets as $ticket) {
            if (! is_array($ticket)) {
                continue;
            }

            $price = is_array($ticket['price'] ?? null) ? $ticket['price'] : null;

            $presented[] = [
                'ticket_id' => $ticket['ticket_id'] ?? $ticket['ticketId'] ?? null,
                'sku_id' => $ticket['sku_id'] ?? null,
                'ticket_name' => $ticket['ticketName'] ?? $ticket['ticket_name'] ?? $ticket['name'] ?? null,
                'synthetic' => (bool) ($ticket['synthetic'] ?? false),
                'attraction_sku_id' => $ticket['attraction_sku_id'] ?? null,
                'price' => $price,
            ];
        }

        return $presented;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @param  array<string, mixed>  $attraction
     */
    private function firstImage(array $raw, array $attraction): string
    {
        $candidates = [
            $raw['image'] ?? null,
            $raw['master_image'] ?? null,
            $raw['thumbnail'] ?? null,
            $raw['image_url'] ?? null,
            $attraction['image'] ?? null,
        ];

        if (isset($raw['images']) && is_array($raw['images']) && $raw['images'] !== []) {
            $first = $raw['images'][0];
            $candidates[] = is_array($first) ? ($first['url'] ?? $first['image'] ?? null) : $first;
        }

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<int, string>
     */
    private function additionalImages(array $raw, string $primary): array
    {
        $images = [];

        foreach (['additional_images', 'additional_image', 'gallery', 'images'] as $key) {
            if (! isset($raw[$key])) {
                continue;
            }
            $value = $raw[$key];
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                $value = is_array($decoded) ? $decoded : array_filter([$value]);
            }
            if (! is_array($value)) {
                continue;
            }
            foreach ($value as $item) {
                $url = is_array($item) ? (string) ($item['url'] ?? $item['image'] ?? '') : (string) $item;
                $url = trim($url);
                if ($url !== '' && $url !== $primary) {
                    $images[] = $url;
                }
            }
            break;
        }

        return array_values(array_unique($images));
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<int, string>
     */
    private function timeSlots(array $raw): array
    {
        if (isset($raw['time_slots']) && is_array($raw['time_slots'])) {
            return array_values(array_filter(array_map('strval', $raw['time_slots'])));
        }

        $open = $raw['open_time'] ?? null;
        $close = $raw['close_time'] ?? null;

        if (is_string($open)) {
            $open = json_decode($open, true) ?? [$open];
        }
        if (is_string($close)) {
            $close = json_decode($close, true) ?? [$close];
        }

        if (! is_array($open) || ! is_array($close) || $open === []) {
            return [];
        }

        $slots = [];
        $count = min(count($open), count($close));
        for ($i = 0; $i < $count; $i++) {
            $slots[] = trim((string) $open[$i]) . ' - ' . trim((string) $close[$i]);
        }

        return $slots;
    }

    private function resolveCountryName(mixed $countryId, string $cityName): string
    {
        if ($countryId) {
            $name = Country::query()->where('id', (int) $countryId)->value('name');
            if (is_string($name) && $name !== '') {
                return $name;
            }
        }

        if ($cityName === '') {
            return '';
        }

        $city = \App\Models\City::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($cityName)])
            ->first(['country', 'country_id']);

        if (! $city) {
            return '';
        }

        if (! empty($city->country)) {
            return (string) $city->country;
        }

        if (! empty($city->country_id)) {
            return (string) (Country::query()->where('id', (int) $city->country_id)->value('name') ?? '');
        }

        return '';
    }

    private function resolveTaxPercentage(string $countryName): string|float|int
    {
        if ($countryName === '') {
            return 0;
        }

        $tax = Country::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($countryName)])
            ->value('tax_percentage');

        return $tax ?? 0;
    }

    private function money(float $value): float|int
    {
        if ($value <= 0) {
            return 0;
        }

        return round($value, 2);
    }
}
