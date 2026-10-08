<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HotelSuppliers\OnlineHotelAggregator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

/**
 * Mobile / SPA API for online (supplier-backed) hotels.
 *
 * Offline catalogue hotels stay on Api\HotelController. This controller only
 * talks to the country-mapped supplier via OnlineHotelAggregator.
 */
class OnlineHotelController extends Controller
{
    use Concerns\ActsAsRequestDmc;

    /**
     * Hotel list for a city and stay.
     *
     * For two-step suppliers (e.g. MG Bedbank) this returns the catalogue without
     * live rates — call the rooms endpoint later for the selected hotel.
     * For single-step suppliers (e.g. Tinivia) hotels may already include rooms/rates.
     *
     * GET /api/v1/online-hotels
     *
     * Required:
     *   dmc_id      int         Operating DMC. Its Master DMC online_api / live_api
     *                           choose demo vs live credentials; this DMC's markup applies.
     *   city        string|int  City name (case-insensitive) or numeric cities.city_id
     *   check_in    date        Y-m-d (also accepts checkIn)
     *   check_out   date        Y-m-d, after check_in (also accepts checkOut)
     *
     * Occupancy — send either pax_info OR adults:
     *   pax_info    string      "adults|children" or "adults|children|age1,age2"
     *                           e.g. "2|0", "2|1|8", "4|2|5,8"
     *   adults      int         Alternative to pax_info (min 1)
     *   children    int         Optional when using adults (default 0)
     *   child_ages  array|string Optional ages when children > 0, e.g. [5,8] or "5,8"
     *
     * Optional:
     *   rooms       int         Number of rooms to price across (default 1, max 20)
     */
    public function lists(Request $request, OnlineHotelAggregator $aggregator): JsonResponse
    {
        $payload = $this->normalizeInput($request);

        try {
            $validated = validator($payload, [
                'dmc_id' => ['required', 'integer', 'min:1'],
                'city' => ['required', 'string', 'max:255'],
                'check_in' => ['required', 'date_format:Y-m-d'],
                'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
                'pax_info' => ['required', 'string', 'max:50', 'regex:/^\d+\|\d+(\|[\d,\s;:]+)?$/'],
                'rooms' => ['nullable', 'integer', 'min:1', 'max:20'],
            ], [
                'dmc_id.required' => 'dmc_id is required so we can load that DMC\'s online API settings.',
                'pax_info.regex' => 'pax_info must look like "2|0" or "2|1|8" (adults|children|optional ages).',
                'check_out.after' => 'check_out must be after check_in.',
            ])->validate();
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        $city = trim((string) $validated['city']);
        $checkIn = (string) $validated['check_in'];
        $checkOut = (string) $validated['check_out'];
        $paxInfo = (string) $validated['pax_info'];
        $rooms = (int) ($validated['rooms'] ?? 1);
        $dmcId = (int) $validated['dmc_id'];

        try {
            $dmc = $this->resolveRequestDmc($dmcId);
            $result = $this->withDmcAuthContext(
                $dmc,
                fn () => $aggregator->search($city, $checkIn, $checkOut, $paxInfo, $rooms),
            );

            return response()->json([
                'success' => true,
                'message' => 'Hotels fetched successfully.',
                'data' => [
                    'hotels' => $this->presentHotels($result['hotels'] ?? []),
                    'meta' => [
                        'total_hotels' => (int) ($result['total_hotels'] ?? count($result['hotels'] ?? [])),
                        'two_step' => (bool) ($result['two_step'] ?? false),
                        'supplier_code' => $result['supplier_code'] ?? null,
                        'supplier_name' => $result['supplier_name'] ?? null,
                        'city' => $result['city'] ?? $city,
                        'country_id' => $result['country_id'] ?? null,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'pax_info' => $paxInfo,
                        'rooms' => (int) ($result['rooms'] ?? $rooms),
                        'dmc_id' => $dmcId,
                        'api_environment' => $result['api_environment'] ?? null,
                    ],
                ],
            ]);
        } catch (RuntimeException $e) {
            Log::warning('API online hotel lists failed', [
                'dmc_id' => $dmcId,
                'city' => $city,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [
                    'hotels' => [],
                    'meta' => null,
                ],
            ], 422);
        } catch (Throwable $e) {
            Log::error('API online hotel lists exception', [
                'dmc_id' => $dmcId,
                'city' => $city,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch online hotels right now. Please try again.',
                'data' => [
                    'hotels' => [],
                    'meta' => null,
                ],
            ], 500);
        }
    }

    /**
     * Accept camelCase or snake_case, and either pax_info or adults/children.
     *
     * @return array<string, mixed>
     */
    private function normalizeInput(Request $request): array
    {
        $city = $request->input('city', $request->input('city_name'));
        $checkIn = $request->input('check_in', $request->input('checkIn'));
        $checkOut = $request->input('check_out', $request->input('checkOut'));
        $rooms = $request->input('rooms', $request->input('room_count', 1));
        $dmcId = $request->input('dmc_id', $request->input('dmcId'));

        $paxInfo = $request->input('pax_info', $request->input('paxInfo'));

        if ($paxInfo === null || trim((string) $paxInfo) === '') {
            $paxInfo = $this->buildPaxInfoFromParts($request);
        }

        return [
            'dmc_id' => $dmcId,
            'city' => is_scalar($city) ? (string) $city : '',
            'check_in' => is_scalar($checkIn) ? (string) $checkIn : '',
            'check_out' => is_scalar($checkOut) ? (string) $checkOut : '',
            'pax_info' => is_scalar($paxInfo) ? trim((string) $paxInfo) : '',
            'rooms' => $rooms,
        ];
    }

    /**
     * Build supplier pax_info from adults / children / child_ages.
     */
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
     * Stable list payload for the frontend — strips supplier raw dumps.
     *
     * @param  array<int, mixed>  $hotels
     * @return array<int, array<string, mixed>>
     */
    private function presentHotels(array $hotels): array
    {
        $presented = [];

        foreach ($hotels as $hotel) {
            if (! is_array($hotel)) {
                continue;
            }

            $hotelId = $hotel['hotel_id'] ?? $hotel['hotelId'] ?? null;

            $item = [
                'hotel_id' => $hotelId !== null ? (string) $hotelId : null,
                'hotel_name' => $hotel['hotel_name'] ?? $hotel['hotelName'] ?? $hotel['name'] ?? null,
                'star_rating' => $hotel['star_rating'] ?? $hotel['starRating'] ?? null,
                'property_type' => $hotel['property_type'] ?? $hotel['propertyType'] ?? null,
                'address' => $hotel['address'] ?? null,
                'currency' => $hotel['currency'] ?? null,
                'min_rate' => $this->nullableFloat($hotel['min_rate'] ?? $hotel['minRate'] ?? $hotel['lowestPrice'] ?? null),
                'max_rate' => $this->nullableFloat($hotel['max_rate'] ?? $hotel['maxRate'] ?? null),
                'images' => is_array($hotel['images'] ?? null) ? array_values($hotel['images']) : [],
                'description' => $hotel['description'] ?? null,
                'supplier_code' => $hotel['supplier_code'] ?? null,
                'api_environment' => $hotel['api_environment'] ?? null,
            ];

            // Single-step suppliers may already attach priced rooms on the list response.
            if (isset($hotel['rooms']) && is_array($hotel['rooms']) && $hotel['rooms'] !== []) {
                $item['rooms'] = $this->presentRooms($hotel['rooms']);
                $item['total_rooms'] = count($item['rooms']);
            }

            $presented[] = $item;
        }

        return $presented;
    }

    /**
     * @param  array<int, mixed>  $rooms
     * @return array<int, array<string, mixed>>
     */
    private function presentRooms(array $rooms): array
    {
        $presented = [];

        foreach ($rooms as $room) {
            if (! is_array($room)) {
                continue;
            }

            $price = is_array($room['price'] ?? null) ? $room['price'] : null;
            $converted = is_array($room['currencyConvertedPrice'] ?? null)
                ? $room['currencyConvertedPrice']
                : (is_array($room['currency_converted_price'] ?? null) ? $room['currency_converted_price'] : null);

            $presented[] = [
                'room_id' => $room['room_id'] ?? $room['roomId'] ?? null,
                'room_name' => $room['room_name'] ?? $room['roomName'] ?? $room['roomType'] ?? null,
                'rate_plan_id' => $room['rate_plan_id'] ?? $room['ratePlanId'] ?? $room['rateKey'] ?? null,
                'rate_plan_name' => $room['rate_plan_name'] ?? $room['ratePlanName'] ?? null,
                'meal_plan' => $room['meal_plan'] ?? $room['mealPlanName'] ?? null,
                'breakfast_included' => (bool) ($room['breakfast_included'] ?? $room['breakFast'] ?? false),
                'free_cancellation' => (bool) ($room['free_cancellation'] ?? $room['freeCancellation'] ?? false),
                'max_occupancy' => (int) ($room['max_occupancy'] ?? $room['maxOccupancy'] ?? 0),
                'max_adult' => (int) ($room['max_adult'] ?? $room['maxAdult'] ?? 0),
                'max_child' => (int) ($room['max_child'] ?? $room['maxChild'] ?? 0),
                'price' => $price,
                'currency_converted_price' => $converted,
                // Keep booking keys + stamped markup for a future book/recheck flow.
                'booking' => is_array($room['booking'] ?? null) ? $room['booking'] : null,
                'markup' => is_array($room['markup'] ?? null) ? $room['markup'] : null,
            ];
        }

        return $presented;
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $float = (float) $value;

        return $float > 0 ? $float : null;
    }
}
