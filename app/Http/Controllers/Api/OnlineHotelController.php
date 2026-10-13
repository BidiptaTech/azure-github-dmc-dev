<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApiEnvironmentResolver;
use App\Services\HotelSuppliers\OnlineHotelAggregator;
use App\Services\HotelSuppliers\OnlineHotelBookingServiceFactory;
use App\Services\SupplierConfigResolver;
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
 * talks to the country-mapped supplier via OnlineHotelAggregator /
 * OnlineHotelBookingServiceFactory.
 *
 * Flow (MG Bedbank): GetHotelList → SearchHotel → RecheckHotel → BookHotel.
 */
class OnlineHotelController extends Controller
{
    use Concerns\ActsAsRequestDmc;

    /** Synthetic cache ids for SPA recheck tokens (no tour order yet). */
    private const API_CACHE_ORDER_ID = 0;

    private const API_CACHE_BOOKING_INDEX = 0;

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
     * Live details + rooms for one hotel picked from the list.
     *
     * Two-step suppliers (MG Bedbank): SearchHotel for that hotel code.
     * Single-step suppliers (Tinivia): rates already came with the list, so we
     * re-search the city and return the matching hotel.
     *
     * GET /api/v1/online-hotels/details
     */
    public function details(Request $request, OnlineHotelAggregator $aggregator): JsonResponse
    {
        $payload = $this->normalizeInput($request);

        try {
            $validated = validator($payload, [
                'dmc_id' => ['required', 'integer', 'min:1'],
                'hotel_id' => ['required', 'string', 'max:100'],
                'city' => ['required', 'string', 'max:255'],
                'check_in' => ['required', 'date_format:Y-m-d'],
                'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
                'pax_info' => ['required', 'string', 'max:50', 'regex:/^\d+\|\d+(\|[\d,\s;:]+)?$/'],
                'rooms' => ['nullable', 'integer', 'min:1', 'max:20'],
            ], [
                'dmc_id.required' => 'dmc_id is required so we can load that DMC\'s online API settings.',
                'hotel_id.required' => 'hotel_id is required (use hotel_id from the hotel list).',
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
        $hotelId = trim((string) $validated['hotel_id']);
        $checkIn = (string) $validated['check_in'];
        $checkOut = (string) $validated['check_out'];
        $paxInfo = (string) $validated['pax_info'];
        $rooms = (int) ($validated['rooms'] ?? 1);
        $dmcId = (int) $validated['dmc_id'];

        try {
            $dmc = $this->resolveRequestDmc($dmcId);
            $result = $this->withDmcAuthContext(
                $dmc,
                fn () => $this->fetchHotelDetails($aggregator, $city, $hotelId, $checkIn, $checkOut, $paxInfo, $rooms),
            );

            $hotel = $this->presentHotel(is_array($result['hotel'] ?? null) ? $result['hotel'] : []);
            $roomList = $hotel['rooms'] ?? $this->presentRooms($result['rooms'] ?? []);

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Hotel details fetched successfully.',
                'data' => [
                    'hotel' => $hotel !== [] ? $hotel : null,
                    'rooms' => $roomList,
                    'meta' => [
                        'hotel_id' => $hotelId,
                        'total_rooms' => count($roomList),
                        'session_id' => $result['session_id'] ?? null,
                        'supplier_code' => $result['supplier_code'] ?? null,
                        'supplier_name' => $result['supplier_name'] ?? null,
                        'city' => $result['city'] ?? $city,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'pax_info' => $paxInfo,
                        'rooms' => (int) ($result['room_count'] ?? $rooms),
                        'dmc_id' => $dmcId,
                        'api_environment' => $result['api_environment'] ?? null,
                    ],
                ],
            ]);
        } catch (RuntimeException $e) {
            Log::warning('API online hotel details failed', [
                'dmc_id' => $dmcId,
                'city' => $city,
                'hotel_id' => $hotelId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [
                    'hotel' => null,
                    'rooms' => [],
                    'meta' => null,
                ],
            ], 422);
        } catch (Throwable $e) {
            Log::error('API online hotel details exception', [
                'dmc_id' => $dmcId,
                'city' => $city,
                'hotel_id' => $hotelId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch hotel details right now. Please try again.',
                'data' => [
                    'hotel' => null,
                    'rooms' => [],
                    'meta' => null,
                ],
            ], 500);
        }
    }

    /**
     * Two-step: live SearchHotel for one code. Single-step: city search, then pick the hotel.
     *
     * @return array<string, mixed>
     */
    private function fetchHotelDetails(
        OnlineHotelAggregator $aggregator,
        string $city,
        string $hotelId,
        string $checkIn,
        string $checkOut,
        string $paxInfo,
        int $rooms,
    ): array {
        try {
            return $aggregator->rooms($city, $hotelId, $checkIn, $checkOut, $paxInfo, $rooms);
        } catch (RuntimeException $e) {
            if (! str_contains($e->getMessage(), 'no room lookup is needed')) {
                throw $e;
            }
        }

        $search = $aggregator->search($city, $checkIn, $checkOut, $paxInfo, $rooms);
        $matched = null;

        foreach ($search['hotels'] ?? [] as $hotel) {
            if (! is_array($hotel)) {
                continue;
            }

            $candidate = (string) ($hotel['hotel_id'] ?? $hotel['hotelId'] ?? '');
            if ($candidate !== '' && strcasecmp($candidate, $hotelId) === 0) {
                $matched = $hotel;
                break;
            }
        }

        if (! is_array($matched)) {
            throw new RuntimeException('Hotel [' . $hotelId . '] was not found in the supplier search results.');
        }

        return [
            'success' => true,
            'hotel' => $matched,
            'rooms' => $matched['rooms'] ?? [],
            'session_id' => null,
            'supplier_code' => $search['supplier_code'] ?? null,
            'supplier_name' => $search['supplier_name'] ?? null,
            'city' => $search['city'] ?? $city,
            'room_count' => $search['rooms'] ?? $rooms,
            'api_environment' => $search['api_environment'] ?? null,
        ];
    }

    /**
     * Step after SearchHotel: live RecheckHotel (MG) / availability recheck (Tinivia).
     *
     * Send the `booking` object from online-hotels/details for the selected room.
     *
     * POST /api/v1/online-hotels/recheck
     */
    public function recheck(
        Request $request,
        OnlineHotelBookingServiceFactory $factory,
        SupplierConfigResolver $supplierConfig,
        ApiEnvironmentResolver $apiEnvironment,
    ): JsonResponse {
        try {
            $validated = validator($request->all(), [
                'dmc_id' => ['required', 'integer', 'min:1'],
                'booking' => ['required_without:online_hotel_booking', 'array'],
                'online_hotel_booking' => ['required_without:booking', 'array'],
                'quoted_price' => ['nullable', 'numeric', 'min:0'],
                'city' => ['nullable', 'string', 'max:255'],
                'supplier_code' => ['nullable', 'string', 'max:50'],
            ], [
                'dmc_id.required' => 'dmc_id is required so we can load that DMC\'s online API settings.',
                'booking.required_without' => 'booking is required (use the room.booking object from hotel details).',
            ])->validate();
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        $dmcId = (int) $validated['dmc_id'];
        $online = is_array($request->input('booking'))
            ? $request->input('booking')
            : $request->input('online_hotel_booking');

        try {
            $dmc = $this->resolveRequestDmc($dmcId);

            $result = $this->withDmcAuthContext($dmc, function () use (
                $online,
                $request,
                $factory,
                $supplierConfig,
                $apiEnvironment,
                $validated,
            ) {
                $bookingRow = $this->buildApiBookingRow($online, $request);
                $supplierCode = $this->resolveSupplierCode($bookingRow, $validated['supplier_code'] ?? null);

                if (! $factory->supports($supplierCode)) {
                    throw new RuntimeException('Online supplier "' . $supplierCode . '" is not supported for booking yet.');
                }

                $bookingService = $factory->make($supplierCode);
                $environment = $apiEnvironment->resolve();
                $credentialCode = $factory->credentialCode($supplierCode);

                if (! $supplierConfig->isConfigured($credentialCode, $environment)) {
                    throw new RuntimeException(
                        $supplierConfig->missingCredentialsMessage($credentialCode, $environment)
                    );
                }

                $credentials = $supplierConfig->valuesFor($credentialCode, $environment);
                $recheckResult = $bookingService->recheckFromOrderBooking($bookingRow, $credentials);
                $token = $bookingService->cacheRecheckResult(
                    self::API_CACHE_ORDER_ID,
                    self::API_CACHE_BOOKING_INDEX,
                    $recheckResult,
                );

                return [
                    'token' => $token,
                    'recheck' => $recheckResult,
                    'supplier_code' => $supplierCode,
                ];
            });

            $recheck = $result['recheck'];

            Log::info('API online hotel recheck priced', [
                'dmc_id' => $dmcId,
                'supplier_code' => $result['supplier_code'],
                'customer_price' => $recheck['customer_price'] ?? null,
                'price_changed' => $recheck['price_changed'] ?? null,
                'api_environment' => $recheck['api_environment'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hotel availability rechecked successfully.',
                'recheck_token' => $result['token'],
                'cache_order_id' => self::API_CACHE_ORDER_ID,
                'cache_booking_index' => self::API_CACHE_BOOKING_INDEX,
                'data' => [
                    'supplier_code' => $result['supplier_code'],
                    'supplier_label' => (string) config(
                        'suppliers.' . $factory->credentialCode($result['supplier_code']) . '.label',
                        $result['supplier_code']
                    ),
                    'hotel_name' => $recheck['hotel_name'] ?? null,
                    'room_name' => $recheck['room_name'] ?? null,
                    'meal_plan_name' => $recheck['meal_plan_name'] ?? null,
                    'currency' => $recheck['currency'] ?? null,
                    'check_in' => $recheck['check_in'] ?? null,
                    'check_out' => $recheck['check_out'] ?? null,
                    'stored_price' => $recheck['stored_price'] ?? null,
                    'stored_supplier_price' => $recheck['stored_supplier_price'] ?? null,
                    'supplier_net_price' => $recheck['supplier_net_price'] ?? null,
                    'supplier_gross_price' => $recheck['supplier_gross_price'] ?? null,
                    'customer_price' => $recheck['customer_price'] ?? null,
                    'markup_applied' => $recheck['markup_applied'] ?? false,
                    'markup_rules' => $recheck['markup_rules'] ?? [],
                    'comparison_basis' => $recheck['comparison_basis'] ?? null,
                    'price_changed' => $recheck['price_changed'] ?? false,
                    'session_id' => $recheck['session_id'] ?? null,
                    'api_environment' => $recheck['api_environment'] ?? null,
                ],
            ]);
        } catch (RuntimeException $e) {
            Log::warning('API online hotel recheck failed', [
                'dmc_id' => $dmcId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('API online hotel recheck exception', [
                'dmc_id' => $dmcId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to recheck hotel availability right now. Please try again.',
            ], 500);
        }
    }

    /**
     * Final step: BookHotel / confirmBookingRequest using the cached recheck token.
     *
     * POST /api/v1/online-hotels/book
     */
    public function book(
        Request $request,
        OnlineHotelBookingServiceFactory $factory,
        SupplierConfigResolver $supplierConfig,
        ApiEnvironmentResolver $apiEnvironment,
    ): JsonResponse {
        try {
            $validated = validator($request->all(), [
                'dmc_id' => ['required', 'integer', 'min:1'],
                'recheck_token' => ['required', 'string', 'max:128'],
                'supplier_code' => ['required', 'string', 'max:50'],
                'reference_id' => ['required', 'string', 'max:255'],
                'guest_name' => ['required_without:full_name', 'string', 'max:255'],
                'full_name' => ['required_without:guest_name', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:40'],
                'cache_order_id' => ['nullable', 'integer'],
                'cache_booking_index' => ['nullable', 'integer', 'min:0'],
                'quoted_price' => ['nullable', 'numeric', 'min:0'],
                'booking' => ['nullable', 'array'],
                'online_hotel_booking' => ['nullable', 'array'],
            ], [
                'recheck_token.required' => 'recheck_token from /online-hotels/recheck is required.',
                'reference_id.required' => 'reference_id (agency booking id) is required to confirm with the supplier.',
                'guest_name.required_without' => 'guest_name is required for the primary guest.',
            ])->validate();
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        $dmcId = (int) $validated['dmc_id'];
        $token = trim((string) $validated['recheck_token']);
        $supplierCode = strtolower(trim((string) $validated['supplier_code']));
        $referenceId = trim((string) $validated['reference_id']);
        $cacheOrderId = (int) ($validated['cache_order_id'] ?? self::API_CACHE_ORDER_ID);
        $cacheBookingIndex = (int) ($validated['cache_booking_index'] ?? self::API_CACHE_BOOKING_INDEX);

        try {
            $dmc = $this->resolveRequestDmc($dmcId);

            $bookingDetails = $this->withDmcAuthContext($dmc, function () use (
                $request,
                $factory,
                $supplierConfig,
                $apiEnvironment,
                $supplierCode,
                $token,
                $referenceId,
                $cacheOrderId,
                $cacheBookingIndex,
                $validated,
            ) {
                if (! $factory->supports($supplierCode)) {
                    throw new RuntimeException('Online supplier "' . $supplierCode . '" is not supported for booking yet.');
                }

                $bookingService = $factory->make($supplierCode);
                $recheckResult = $bookingService->pullCachedRecheckResult(
                    $cacheOrderId,
                    $cacheBookingIndex,
                    $token,
                );

                if ($recheckResult === null) {
                    throw new RuntimeException(
                        'The availability check has expired. Please recheck availability and try again.'
                    );
                }

                $environment = $apiEnvironment->normalize((string) ($recheckResult['api_environment'] ?? ''))
                    ?? $apiEnvironment->resolve();
                $credentialCode = $factory->credentialCode($supplierCode);

                if (! $supplierConfig->isConfigured($credentialCode, $environment)) {
                    throw new RuntimeException(
                        $supplierConfig->missingCredentialsMessage($credentialCode, $environment)
                    );
                }

                $credentials = $supplierConfig->valuesFor($credentialCode, $environment);
                $online = is_array($request->input('booking'))
                    ? $request->input('booking')
                    : (is_array($request->input('online_hotel_booking'))
                        ? $request->input('online_hotel_booking')
                        : []);
                $bookingRow = $this->buildApiBookingRow($online !== [] ? $online : [
                    'supplier_code' => $supplierCode,
                    'api_environment' => $environment,
                    'check_in' => $recheckResult['check_in'] ?? null,
                    'check_out' => $recheckResult['check_out'] ?? null,
                    'hotel' => ['code' => $recheckResult['hotel_code'] ?? '', 'name' => $recheckResult['hotel_name'] ?? ''],
                    'room' => [
                        'code' => $recheckResult['room_code'] ?? '',
                        'name' => $recheckResult['room_name'] ?? '',
                    ],
                ], $request);

                $guestName = trim((string) ($validated['guest_name'] ?? $validated['full_name'] ?? ''));
                $bookingRow['fullName'] = $guestName !== '' ? $guestName : 'Guest';
                if (! empty($validated['email'])) {
                    $bookingRow['email'] = (string) $validated['email'];
                }
                if (! empty($validated['phone'])) {
                    $bookingRow['phone'] = (string) $validated['phone'];
                }

                return $bookingService->bookFromRecheckResult(
                    $recheckResult,
                    $bookingRow,
                    $referenceId,
                    $credentials,
                );
            });

            Log::info('API online hotel booked', [
                'dmc_id' => $dmcId,
                'supplier_code' => $bookingDetails['supplier_code'] ?? $supplierCode,
                'agency_booking_id' => $bookingDetails['agency_booking_id'] ?? $referenceId,
                'supplier_booking_reference' => $bookingDetails['supplier_booking_reference'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hotel booked successfully with the supplier.',
                'data' => [
                    'supplier_code' => $bookingDetails['supplier_code'] ?? $supplierCode,
                    'api_environment' => $bookingDetails['api_environment'] ?? null,
                    'agency_booking_id' => $bookingDetails['agency_booking_id'] ?? $referenceId,
                    'supplier_booking_reference' => $bookingDetails['supplier_booking_reference'] ?? null,
                    'booked_at' => $bookingDetails['booked_at'] ?? now()->toIso8601String(),
                    'booking_details' => $bookingDetails,
                ],
            ]);
        } catch (RuntimeException $e) {
            Log::warning('API online hotel book failed', [
                'dmc_id' => $dmcId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'requires_recheck' => str_contains(strtolower($e->getMessage()), 'recheck')
                    || str_contains(strtolower($e->getMessage()), 'expired'),
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('API online hotel book exception', [
                'dmc_id' => $dmcId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to book the hotel right now. Please try again.',
            ], 500);
        }
    }

    /**
     * Build the order-booking row shape expected by OnlineHotelBookingService.
     *
     * @param  array<string, mixed>  $online
     * @return array<string, mixed>
     */
    private function buildApiBookingRow(array $online, Request $request): array
    {
        $checkIn = (string) ($online['check_in'] ?? $request->input('check_in', $request->input('checkIn', '')));
        $checkOut = (string) ($online['check_out'] ?? $request->input('check_out', $request->input('checkOut', '')));
        $city = (string) ($request->input('city', $online['search']['city_name'] ?? ''));
        $quoted = $request->input('quoted_price', $request->input('total_price'));
        $hotel = is_array($online['hotel'] ?? null) ? $online['hotel'] : [];

        if (($online['api_environment'] ?? '') === '' && $request->filled('api_environment')) {
            $online['api_environment'] = (string) $request->input('api_environment');
        }

        return [
            'onlineHotelSource' => (string) ($online['supplier_code'] ?? $request->input('supplier_code', '')),
            'onlineHotelBooking' => $online,
            'hotelDetails' => [
                'hotel_id' => (string) ($hotel['code'] ?? $request->input('hotel_id', '')),
                'hotel_name' => (string) ($hotel['name'] ?? ''),
                'city' => $city,
            ],
            'bookingDate' => array_values(array_filter([$checkIn, $checkOut], static fn ($v) => $v !== '')),
            'city' => $city,
            'currency' => (string) ($online['currency'] ?? $request->input('currency', 'SGD')),
            'totalPrice' => is_numeric($quoted) ? (float) $quoted : (float) ($online['room']['gross_price'] ?? 0),
            'price' => is_numeric($quoted) ? (float) $quoted : (float) ($online['room']['gross_price'] ?? 0),
            'fullName' => (string) ($request->input('guest_name', $request->input('full_name', 'Guest'))),
            'email' => (string) ($request->input('email', '')),
            'phone' => (string) ($request->input('phone', '')),
            'numberOfRooms' => (int) ($online['search']['rooms'] ?? $request->input('rooms', 1)),
        ];
    }

    private function resolveSupplierCode(array $bookingRow, mixed $fallback): string
    {
        $online = is_array($bookingRow['onlineHotelBooking'] ?? null)
            ? $bookingRow['onlineHotelBooking']
            : [];

        $code = strtolower(trim((string) (
            $bookingRow['onlineHotelSource']
            ?? $online['supplier_code']
            ?? $fallback
            ?? ''
        )));

        return $code !== '' ? $code : 'mg_bedbank';
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
        $hotelId = $request->input('hotel_id', $request->input('hotelId', $request->input('hotelCode')));

        $paxInfo = $request->input('pax_info', $request->input('paxInfo'));

        if ($paxInfo === null || trim((string) $paxInfo) === '') {
            $paxInfo = $this->buildPaxInfoFromParts($request);
        }

        return [
            'dmc_id' => $dmcId,
            'hotel_id' => is_scalar($hotelId) ? trim((string) $hotelId) : '',
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

            $item = $this->presentHotel($hotel, includeRooms: false);

            if (isset($hotel['rooms']) && is_array($hotel['rooms']) && $hotel['rooms'] !== []) {
                $item['rooms'] = $this->presentRooms($hotel['rooms']);
                $item['total_rooms'] = count($item['rooms']);
            }

            $presented[] = $item;
        }

        return $presented;
    }

    /**
     * @param  array<string, mixed>  $hotel
     * @return array<string, mixed>
     */
    private function presentHotel(array $hotel, bool $includeRooms = true): array
    {
        if ($hotel === []) {
            return [];
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

        if ($includeRooms && isset($hotel['rooms']) && is_array($hotel['rooms'])) {
            $item['rooms'] = $this->presentRooms($hotel['rooms']);
            $item['total_rooms'] = count($item['rooms']);
        }

        return $item;
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
                'inclusions' => is_array($room['inclusions'] ?? null) ? $room['inclusions'] : [],
                'cancellation_policy' => $room['cancellation_policy'] ?? $room['cancellationPolicy'] ?? [],
                // Pass this booking object to POST /online-hotels/recheck, then book.
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
