<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DayLevel;
use App\Models\City;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\Meal;
use App\Models\Guide;
use App\Models\Vehicle;
use App\Models\Room;
use App\Models\Bed;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Port;
use App\Models\DefaultValue;
use App\Helpers\CommonHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DayLevelController extends Controller
{
    private const HOTEL_STAR_RATINGS = [
        '3' => '3 Stars',
        '4' => '4 Stars',
        '5' => '5 Stars',
        '7' => '7 Stars',
    ];

    private const AIRPORT_TYPES = [
        'private' => 'Private',
        'shared'  => 'Shared',
    ];

    private const VEHICLES_STATIC = [
        'sedan'           => 'Sedan',
        'suv'             => 'SUV',
        'minivan'         => 'Minivan',
        'tempo_traveller' => 'Tempo Traveller',
        'bus'             => 'Bus',
        'train'           => 'Train',
    ];

    // =========================================================================
    // Resolve DMC / Master DMC from the logged-in user
    // roles.role_id 10 (and 19) → Master DMC
    // roles.role_id 11 (and 20) → DMC
    // staff (e.g. Sales Head) → walk created_by to the DMC (11), never use staff master_dmc_id
    // =========================================================================
    private function resolveDmcIds(): array
    {
        $context = $this->resolveDmcContext();

        return [
            'dmc_id'        => $context['dmc_id'],
            'master_dmc_id' => $context['master_dmc_id'],
        ];
    }

    private function resolveDmcContext(): array
    {
        $user = Auth::user();
        $roleId = (int) $user->role_id;

        if (in_array($roleId, [10, 19], true)) {
            $masterId   = (int) $user->userId;
            $masterName = $this->userDisplayName($user, 'Master DMC');
            $childDmc   = User::query()
                ->where('master_dmc_id', $masterId)
                ->whereIn('role_id', [11, 20])
                ->whereNull('deleted_at')
                ->orderBy('company_name')
                ->first();

            $dmcUser = $childDmc ?? $user;

            return [
                'dmc_id'                  => (int) $dmcUser->userId,
                'master_dmc_id'           => $masterId,
                'dmc_name'                => $this->userDisplayName($dmcUser, 'DMC'),
                'master_dmc_display_name' => $masterName,
                'dmc_country'             => (string) ($dmcUser->country ?? ''),
            ];
        }

        $dmcUser = $this->findDmcAccount($user);
        if (!$dmcUser) {
            $dmcId = (int) ($user->created_by ?: $user->userId);
            $name  = (string) ($user->company_name ?: $user->name ?: 'DMC');

            return [
                'dmc_id'                  => $dmcId,
                'master_dmc_id'           => (int) ($user->master_dmc_id ?: $dmcId),
                'dmc_name'                => $name,
                'master_dmc_display_name' => $name,
                'dmc_country'             => (string) ($user->country ?? ''),
            ];
        }

        $dmcId       = (int) $dmcUser->userId;
        $dmcName     = $this->userDisplayName($dmcUser, 'DMC');
        $masterDmcId = $this->resolveMasterDmcIdForDmc($dmcUser);
        $masterUser  = User::query()->where('userId', $masterDmcId)->whereNull('deleted_at')->first();

        return [
            'dmc_id'                  => $dmcId,
            'master_dmc_id'           => $masterDmcId,
            'dmc_name'                => $dmcName,
            'master_dmc_display_name' => $this->userDisplayName($masterUser, 'Master DMC'),
            'dmc_country'             => (string) ($dmcUser->country ?? ''),
        ];
    }

    private function userDisplayName(?User $user, string $fallback): string
    {
        if (!$user) {
            return $fallback;
        }

        return (string) ($user->company_name ?: $user->name ?: $fallback);
    }

    private function findDmcAccount(User $user): ?User
    {
        if (in_array((int) $user->role_id, [11, 20], true)) {
            return $user;
        }

        $walker = $user;
        for ($depth = 0; $depth < 12; $depth++) {
            $parentId = (int) ($walker->created_by ?? 0);
            if ($parentId <= 0) {
                break;
            }

            $parent = User::query()
                ->where('userId', $parentId)
                ->whereNull('deleted_at')
                ->first();

            if (!$parent) {
                break;
            }

            if (in_array((int) $parent->role_id, [11, 20], true)) {
                return $parent;
            }

            $walker = $parent;
        }

        return null;
    }

    /**
     * Resolve the Master DMC for a DMC account: sales/staff → DMC (11) → Master DMC (10).
     * Never treat the child DMC userId as the master id.
     */
    private function resolveMasterDmcIdForDmc(User $dmcUser): int
    {
        $parentId = (int) ($dmcUser->master_dmc_id ?? 0);
        if ($parentId > 0) {
            $parent = User::query()
                ->where('userId', $parentId)
                ->whereNull('deleted_at')
                ->first();

            if ($parent && in_array((int) $parent->role_id, [10, 19], true)) {
                return (int) $parent->userId;
            }
        }

        $walker = $dmcUser;
        for ($depth = 0; $depth < 12; $depth++) {
            $walkerParentId = (int) ($walker->created_by ?? 0);
            if ($walkerParentId <= 0) {
                break;
            }

            $parent = User::query()
                ->where('userId', $walkerParentId)
                ->whereNull('deleted_at')
                ->first();

            if (!$parent) {
                break;
            }

            if (in_array((int) $parent->role_id, [10, 19], true)) {
                return (int) $parent->userId;
            }

            $walker = $parent;
        }

        return (int) ($dmcUser->master_dmc_id ?: 0);
    }

    private function resolveMasterDmcIdForDmcUserId(int $dmcUserId): int
    {
        if ($dmcUserId <= 0) {
            return 0;
        }

        $dmcUser = User::query()
            ->where('userId', $dmcUserId)
            ->whereNull('deleted_at')
            ->first();

        return $dmcUser ? $this->resolveMasterDmcIdForDmc($dmcUser) : 0;
    }

    /**
     * Flat export rows must group under the real Master DMC (from DMC chain), not a child DMC id.
     *
     * @param  list<array<string, mixed>>  $payload
     * @return list<array<string, mixed>>
     */
    private function normalizeFlatExportMasterDmcIds(array $payload): array
    {
        foreach ($payload as $i => $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $dmcId = (int) ($entry['DMC_id'] ?? 0);
            $resolvedMaster = $this->resolveMasterDmcIdForDmcUserId($dmcId);
            if ($resolvedMaster > 0) {
                $payload[$i]['Master_DMC_id'] = $resolvedMaster;
            }
        }

        return $payload;
    }

    /**
     * Correct day_levels.master_dmc_id (and inter_city) when a child DMC id was stored as master.
     */
    private function reconcileStoredMasterDmcIds($rows): void
    {
        foreach ($rows as $row) {
            if (! $row instanceof DayLevel) {
                continue;
            }

            $resolved = $this->resolveMasterDmcIdForDmcUserId((int) $row->dmc_id);
            if ($resolved <= 0 || (int) $row->master_dmc_id === $resolved) {
                continue;
            }

            $row->master_dmc_id = $resolved;

            $ic = $row->inter_city;
            if (is_array($ic) && isset($ic['Master_DMC']) && is_array($ic['Master_DMC'])) {
                foreach ($ic['Master_DMC'] as $i => $masterNode) {
                    if (is_array($masterNode)) {
                        $ic['Master_DMC'][$i]['Master_DMC_id'] = $resolved;
                    }
                }
                $row->inter_city = $ic;
            }

            $row->save();
        }
    }

    // =========================================================================
    // CREATE
    // =========================================================================
    public function create()
    {
        $user = Auth::user();
        $allowedRoleIds = [33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138, 37, 38];

        // Check if user has permission to access this page
        if (!in_array($user->role_id, $allowedRoleIds)) {
            return redirect()->route('dashboard')->with('error', 'You have not permission for access this page');
        }

        $context = $this->resolveDmcContext();

        $cities = City::whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'city_id', 'name', 'country']);

        // Unique countries from cities for the country filter
        $countries = $cities->pluck('country')->filter()->unique()->sort()->values();

        return view('day-level.create', [
            'cities'           => $cities,
            'countries'        => $countries,
            'hotelStarRatings' => self::HOTEL_STAR_RATINGS,
            'airportTypes'     => self::AIRPORT_TYPES,
            'interVehicles'    => self::VEHICLES_STATIC,
            'masterDmcId'      => $context['master_dmc_id'],
            'masterDmcName'    => $context['master_dmc_display_name'],
            'defaultDmcId'     => $context['dmc_id'],
            'dmcName'          => $context['dmc_name'],
            'dmcCountry'       => $context['dmc_country'],
        ]);
    }

    // =========================================================================
    // AJAX – cities filtered by country
    // GET /day-level/cities-by-country?country=Singapore
    // =========================================================================
    public function citiesByCountry(Request $request)
    {
        $country = trim($request->input('country', ''));

        $cities = City::whereNull('deleted_at')
            ->when(!blank($country), fn($q) => $q->where('country', 'ilike', "%{$country}%"))
            ->orderBy('name')
            ->get(['id', 'city_id', 'name', 'country']);

        return response()->json($cities);
    }

    // =========================================================================
    // AJAX – load hotels, attractions, guides & vehicles by city
    // GET /day-level/by-city?city_name=Singapore&type=all
    //
    // Column reference (confirmed from SQL dump):
    //   hotels.city          ✓ exists
    //   attractions.location ✓ use this (attractions has NO city column)
    //   guides.city          ✓ exists
    //   vehicles.city        ✓ exists, vehicles.dmc_id ✓ exists
    // =========================================================================
    public function byCity(Request $request)
    {
        $cityName = trim($request->input('city_name', ''));
        $type     = $request->input('type', 'all');
        $dmcId    = (int) ($request->input('dmc_id') ?: $this->resolveDmcIds()['dmc_id']);

        if (blank($cityName)) {
            return response()->json([
                'hotels'      => [],
                'hotels_flat' => [],
                'attractions' => [],
                'restaurants' => [],
                'activities'  => [],
                'guides'      => [],
                'vehicles'    => [],
            ]);
        }
        $result = [];

        // ── Hotels ──────────────────────────────────────────────────
        if (in_array($type, ['all', 'hotels'])) {
            $hotelsQuery = Hotel::whereNull('deleted_at')
                ->where('is_active', 1)
                ->whereIn('hotel_star_rating', array_keys(self::HOTEL_STAR_RATINGS))
                ->where('city', 'ilike', "%{$cityName}%")
                ->orderBy('name');

            $this->applyHotelDmcFilter($hotelsQuery, $dmcId);

            $hotels = $hotelsQuery->get(['id', 'hotel_unique_id', 'name', 'hotel_star_rating', 'city', 'dmc_id']);

            $result['hotels'] = $hotels
                ->groupBy('hotel_star_rating')
                ->map(fn($g) => $g->values())
                ->toArray();

            $result['hotels_flat'] = $hotels->values()->toArray();
        }

        // ── Attractions (DMC-mapped only) ───────────────────────────
        // attractions table has NO city column — use location only
        if (in_array($type, ['all', 'attractions'])) {
            $attractionsQuery = Attraction::whereNull('deleted_at')
                ->where('is_active', 1)
                ->where('location', 'ilike', "%{$cityName}%")
                ->orderBy('name');

            $this->applyServiceDmcFilter($attractionsQuery, $dmcId);

            $attractions = $attractionsQuery
                ->get(['attraction_id', 'name', 'location', 'adult_price'])
                ->values()
                ->toArray();

            $result['attractions'] = $attractions;
            $result['activities'] = array_map(function ($row) {
                return [
                    'activity_id' => (string) ($row['attraction_id'] ?? ''),
                    'name'        => (string) ($row['name'] ?? ''),
                ];
            }, $attractions);
        }

        // ── Restaurants (DMC-mapped only) ─────────────────────────────
        if (in_array($type, ['all', 'restaurants'])) {
            $restaurantsQuery = Restaurant::whereNull('deleted_at')
                ->where('is_active', 1)
                ->where('city', 'ilike', "%{$cityName}%")
                ->orderBy('name');

            $this->applyServiceDmcFilter($restaurantsQuery, $dmcId);

            $result['restaurants'] = $restaurantsQuery
                ->get(['restaurant_id', 'name', 'city'])
                ->values()
                ->toArray();
        }

        // ── Guides ──────────────────────────────────────────────────
        if (in_array($type, ['all', 'guides'])) {
            $result['guides'] = Guide::whereNull('deleted_at')
                ->where('is_active', 1)
                ->where('city', 'ilike', "%{$cityName}%")
                ->orderBy('name')
                ->get(['id', 'guide_id', 'name', 'city', 'day_rate'])
                ->values()
                ->toArray();
        }

        // ── Vehicles (for airport transfer) ─────────────────────────
        // vehicles.city and vehicles.dmc_id exist per SQL dump
        if (in_array($type, ['all', 'vehicles'])) {
            $vehicleQuery = Vehicle::whereNull('deleted_at')
                // Keep legacy rows visible; many vehicle rows use null/0 flags.
                ->where(function ($q) {
                    $q->where('is_available', 1)->orWhereNull('is_available')->orWhere('is_available', 0);
                })
                ->where(function ($q) {
                    $q->where('is_active', 1)->orWhereNull('is_active')->orWhere('is_active', 0);
                })
                ->where('city', 'ilike', "%{$cityName}%")
                ->where(function ($q) use ($dmcId) {
                    // vehicles.dmc_id is plain numeric in this flow
                    $q->where('dmc_id', $dmcId)
                      ->orWhereRaw('CAST(dmc_id AS TEXT) = ?', [(string) $dmcId])
                      ->orWhereRaw('CAST(dmc_id AS TEXT) LIKE ?', ['%' . (string) $dmcId . '%']);
                })
                ->orderBy('vehicle_name');

            $vehicles = $vehicleQuery->get([
                'id', 'vehicle_id', 'vehicle_name', 'vehicle_type',
                'seating_capacity', 'sharable', 'image',
                'base_price', 'cost_per_hour', 'city', 'dmc_id',
            ]);

            // Fallback: if strict dmc mapping has no rows, show city vehicles
            if ($vehicles->isEmpty()) {
                $vehicles = Vehicle::whereNull('deleted_at')
                    ->where('city', 'ilike', "%{$cityName}%")
                    ->orderBy('vehicle_name')
                    ->get([
                    'id', 'vehicle_id', 'vehicle_name', 'vehicle_type',
                    'seating_capacity', 'sharable', 'image',
                    'base_price', 'cost_per_hour', 'city', 'dmc_id',
                ]);
            }

            $result['vehicles'] = $vehicles->values()->toArray();
        }

        return response()->json($result);
    }

    // =========================================================================
    // AJAX – hotels by star rating + city
    // =========================================================================
    public function hotelsByRating(Request $request)
    {
        $rating   = (string) $request->input('rating', '');
        $cityName = trim($request->input('city_name', ''));
        $dmcId    = (int) ($request->input('dmc_id') ?: $this->resolveDmcIds()['dmc_id']);

        abort_unless(array_key_exists($rating, self::HOTEL_STAR_RATINGS), 422, 'Invalid rating.');

        $query = Hotel::whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('hotel_star_rating', (int) $rating)
            ->orderBy('name');

        if (!blank($cityName)) {
            $query->where('city', 'ilike', "%{$cityName}%");
        }

        $this->applyHotelDmcFilter($query, $dmcId);

        $hotels = $query->get(['id', 'hotel_unique_id', 'name', 'city', 'hotel_star_rating', 'dmc_id']);

        return response()->json($hotels);
    }

    // =========================================================================
    // AJAX – available rooms by hotel (active room + at least one active bed)
    // GET /day-level/rooms-by-hotel?hotel_unique_id=H123&dmc_id=4
    // =========================================================================
    public function roomsByHotel(Request $request)
    {
        $dmcId = (int) ($request->input('dmc_id') ?: $this->resolveDmcIds()['dmc_id']);
        $hotelUniqueId = $this->resolveHotelUniqueIdFromRequest($request);

        if ($hotelUniqueId === null) {
            return response()->json([]);
        }

        $priceColumns = [
            'room_id', 'room_type', 'weekday_price', 'weekend_price',
            'double_weekday_price', 'double_weekend_price',
            'breakfast', 'lunch', 'dinner',
            'breakfast_included', 'lunch_included', 'dinner_included',
            'breakfast_price', 'lunch_price', 'dinner_price',
            'created_by', 'dmc_id',
        ];
        $columns = array_values(array_filter($priceColumns, fn ($col) => Schema::hasColumn('rooms', $col)));
        $rooms = $this->queryAvailableRoomsForHotel($hotelUniqueId, $dmcId, $columns);

        return response()->json(
            $rooms->map(function ($room) {
                return [
                    'room_id'              => $room->room_id,
                    'room_type'            => (string) ($room->room_type ?? ''),
                    'weekday_price'        => (float) ($room->weekday_price ?? 0),
                    'weekend_price'        => (float) ($room->weekend_price ?? 0),
                    'double_weekday_price' => (float) ($room->double_weekday_price ?? 0),
                    'double_weekend_price' => (float) ($room->double_weekend_price ?? 0),
                    'breakfast_price'      => (float) ($room->breakfast_price ?? 0),
                    'lunch_price'          => (float) ($room->lunch_price ?? 0),
                    'dinner_price'         => (float) ($room->dinner_price ?? 0),
                    'breakfast'            => $room->breakfast ?? null,
                    'lunch'                => $room->lunch ?? null,
                    'dinner'               => $room->dinner ?? null,
                    'breakfast_included'   => $room->breakfast_included ?? null,
                    'lunch_included'       => $room->lunch_included ?? null,
                    'dinner_included'      => $room->dinner_included ?? null,
                    'created_by'           => $room->created_by ?? null,
                    'dmc_id'               => $room->dmc_id ?? null,
                ];
            })->values()
        );
    }

    // =========================================================================
    // AJAX – available beds by room
    // GET /day-level/beds-by-room?room_id=5&dmc_id=4
    // =========================================================================
    public function bedsByRoom(Request $request)
    {
        $roomId = (int) $request->input('room_id', 0);
        $dmcId = (int) ($request->input('dmc_id') ?: $this->resolveDmcIds()['dmc_id']);

        if ($roomId <= 0) {
            return response()->json([]);
        }

        $baseBedQuery = fn () => Bed::query()
            ->whereNull('deleted_at')
            ->where('room_id', $roomId)
            ->where(function ($q) {
                $q->where('is_active', 1)->orWhereNull('is_active');
            });

        if ($dmcId > 0 && Schema::hasColumn('beds', 'dmc_id')) {
            $scoped = $baseBedQuery()->where('dmc_id', $dmcId)->orderBy('room_type')->get(['bed_id', 'room_type']);
            if ($scoped->isNotEmpty()) {
                return response()->json(
                    $scoped->map(fn ($bed) => [
                        'bed_id'   => $bed->bed_id,
                        'bed_type' => (string) ($bed->room_type ?? ''),
                    ])->values()
                );
            }
        }

        $beds = $baseBedQuery()->orderBy('room_type')->get(['bed_id', 'room_type']);

        return response()->json(
            $beds->map(fn ($bed) => [
                'bed_id'   => $bed->bed_id,
                'bed_type' => (string) ($bed->room_type ?? ''),
            ])->values()
        );
    }

    // =========================================================================
    // AJAX – meal plans by hotel + dmc
    // GET /day-level/meal-plans-by-hotel?hotel_unique_id=H123&dmc_id=4&room_id=3
    // =========================================================================
    public function mealPlansByHotel(Request $request)
    {
        $roomId = (int) $request->input('room_id', 0);
        $dmcId = (int) ($request->input('dmc_id') ?: $this->resolveDmcIds()['dmc_id']);
        $hotelUniqueId = $this->resolveHotelUniqueIdFromRequest($request);

        if ($hotelUniqueId === null) {
            return response()->json([]);
        }

        $mealColumns = $this->getExistingRoomMealColumns();
        $rooms = $this->queryAvailableRoomsForHotel($hotelUniqueId, $dmcId, $mealColumns);

        if ($roomId > 0) {
            $rooms = $rooms->filter(fn ($room) => (int) ($room->room_id ?? 0) === $roomId)->values();
        }

        $plans = $this->buildMealPlanOptionsFromRooms($rooms->toArray());

        return response()->json($plans);
    }

    /**
     * Prefer hotel_unique_id from the request (matches rooms.hotel_id).
     * Falls back to hotels.id via hotel_id for older clients.
     */
    private function resolveHotelUniqueIdFromRequest(Request $request): ?string
    {
        $uniqueId = trim((string) $request->input('hotel_unique_id', ''));
        if ($uniqueId !== '') {
            $hotel = Hotel::query()
                ->whereNull('deleted_at')
                ->where(function ($q) use ($uniqueId) {
                    $q->where('hotel_unique_id', $uniqueId);
                    if (ctype_digit($uniqueId)) {
                        $q->orWhere('id', (int) $uniqueId);
                    }
                })
                ->first(['id', 'hotel_unique_id']);

            if ($hotel !== null) {
                $resolved = trim((string) ($hotel->hotel_unique_id ?? ''));

                return $resolved !== '' ? $resolved : (string) $hotel->id;
            }

            return $uniqueId;
        }

        $legacyHotelId = trim((string) $request->input('hotel_id', ''));
        if ($legacyHotelId === '') {
            return null;
        }

        if (! ctype_digit($legacyHotelId)) {
            return $legacyHotelId;
        }

        return $this->resolveHotelUniqueIdForListId((int) $legacyHotelId);
    }

    /**
     * Resolve hotels.id to rooms.hotel_id (hotel_unique_id).
     */
    private function resolveHotelUniqueIdForListId(int $hotelListId): ?string
    {
        $hotel = Hotel::query()
            ->whereNull('deleted_at')
            ->where('id', $hotelListId)
            ->first(['id', 'hotel_unique_id']);

        if ($hotel === null) {
            return null;
        }

        $uniqueId = trim((string) ($hotel->hotel_unique_id ?? ''));

        return $uniqueId !== '' ? $uniqueId : (string) $hotel->id;
    }

    /**
     * Active rooms for a hotel (DMC-scoped when rows exist, else all hotel rooms).
     *
     * @param  list<string>  $columns
     */
    private function queryAvailableRoomsForHotel(string $hotelUniqueId, int $dmcId, array $columns = ['room_id', 'room_type'])
    {
        $buildQuery = function () use ($hotelUniqueId, $columns) {
            $query = Room::query()
                ->select($columns)
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->where('status', 1)->orWhereNull('status');
                })
                ->whereNotNull('room_type')
                ->where('room_type', '!=', '')
                ->orderBy('room_type');

            $this->applyRoomHotelIdFilter($query, $hotelUniqueId);

            return $query;
        };

        if ($dmcId > 0) {
            $scoped = $buildQuery()->where(function ($q) use ($dmcId) {
                $q->where('created_by', $dmcId)
                    ->orWhere('dmc_id', $dmcId);
            })->get();

            if ($scoped->isNotEmpty()) {
                return $scoped;
            }
        }

        return $buildQuery()->get();
    }

    /**
     * rooms.hotel_id stores hotel_unique_id; match string or numeric forms.
     */
    private function applyRoomHotelIdFilter($query, string $hotelUniqueId): void
    {
        $query->where(function ($q) use ($hotelUniqueId) {
            $q->where('hotel_id', $hotelUniqueId);
            if (ctype_digit($hotelUniqueId)) {
                $q->orWhere('hotel_id', (int) $hotelUniqueId);
            }
        });
    }

    // =========================================================================
    // AJAX – transfer locations + defaults for hotels section
    // GET /day-level/transfer-options?dmc_id=4&city_name=Singapore
    // =========================================================================
    public function transferOptions(Request $request)
    {
        $ids = $this->resolveDmcIds();
        $dmcId = (int) ($request->input('dmc_id') ?: $ids['dmc_id']);
        $masterDmcId = (int) ($request->input('master_dmc_id') ?: $ids['master_dmc_id']);
        $cityName = trim((string) $request->input('city_name', ''));
        $country = $this->resolveTransferCountry($request, $masterDmcId, $dmcId);
        $likeOperator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $defaultPortRow = DefaultValue::query()
            ->where('dmc_id', $dmcId)
            ->where('name', 'port')
            ->where('status', 1)
            ->latest('id')
            ->first();

        $defaultPortId = (string) ($defaultPortRow->service_id ?? '');

        /** @var Port|null */
        $defaultPortRecord = null;
        if ($defaultPortId !== '') {
            $defaultPortRecord = Port::query()
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->where('port_id', $defaultPortId)
                ->first(['port_id', 'port_name']);
        }

        $makePortRow = static function (?Port $p): ?array {
            if ($p === null || $p->port_id === null || (string) $p->port_id === '') {
                return null;
            }
            $pk = (string) $p->port_id;

            return [
                'value' => 'port:' . $pk,
                'label' => (string) $p->port_name,
                'type'  => 'port',
                'id'    => $pk,
            ];
        };

        $mergePortInto = static function (?Port $p, array &$bucket) use ($makePortRow): void {
            $row = $makePortRow($p);
            if ($row === null) {
                return;
            }
            $bucket[(string) $row['id']] = $row;
        };

        /** Arrival pickup: ports under Master DMC country (not limited to itinerary city). */
        $arrivalPickupPortBuckets = [];
        $mergePortInto($defaultPortRecord, $arrivalPickupPortBuckets);
        if ($country !== '') {
            foreach (Port::query()
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->where('country', $likeOperator, "%{$country}%")
                ->orderBy('port_name')
                ->get(['port_id', 'port_name']) as $row) {
                $mergePortInto($row, $arrivalPickupPortBuckets);
            }
        }

        /** General + arrival-drop ports: city (+ country when set). */
        $portBuckets = [];
        $mergePortInto($defaultPortRecord, $portBuckets);
        if ($cityName !== '') {
            $cityFilteredQuery = Port::query()
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->whereHas('city', function ($cityQ) use ($cityName, $likeOperator) {
                    $cityQ->where('name', $likeOperator, "%{$cityName}%");
                });
            if ($country !== '') {
                $cityFilteredQuery->where('country', $likeOperator, "%{$country}%");
            }
            foreach ($cityFilteredQuery->orderBy('port_name')->get(['port_id', 'port_name']) as $row) {
                $mergePortInto($row, $portBuckets);
            }
        } elseif ($country !== '') {
            foreach (Port::query()
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->where('country', $likeOperator, "%{$country}%")
                ->orderBy('port_name')
                ->get(['port_id', 'port_name']) as $row) {
                $mergePortInto($row, $portBuckets);
            }
        }

        $hotelQuery = Hotel::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->orderBy('name');
        if ($cityName !== '') {
            $hotelQuery->where('city', $likeOperator, "%{$cityName}%");
        }
        $this->applyHotelDmcFilter($hotelQuery, $dmcId);
        $hotelsForLocations = $hotelQuery->get(['id', 'hotel_unique_id', 'name', 'city']);

        if ($hotelsForLocations->isEmpty()) {
            $fallbackHotelQuery = Hotel::query()
                ->whereNull('deleted_at')
                ->where('is_active', 1)
                ->orderBy('name');
            if ($cityName !== '') {
                $fallbackHotelQuery->where('city', $likeOperator, "%{$cityName}%");
            }
            $hotelsForLocations = $fallbackHotelQuery->get(['id', 'hotel_unique_id', 'name', 'city']);
        }

        $defaultHotelRow = DefaultValue::query()
            ->where('dmc_id', $dmcId)
            ->where('name', 'hotel')
            ->where('status', 1)
            ->latest('id')
            ->first();

        $defaultHotelServiceId = (string) ($defaultHotelRow->service_id ?? '');
        $defaultHotelMatch = $hotelsForLocations->first(function ($h) use ($defaultHotelServiceId) {
            return $defaultHotelServiceId !== '' && (string) ($h->hotel_unique_id ?? '') === $defaultHotelServiceId;
        });
        $defaultHotelValue = $defaultHotelMatch ? ('hotel:' . (string) $defaultHotelMatch->id) : null;

        $hotelLocations = $hotelsForLocations->map(function ($h) {
            return [
                'value' => 'hotel:' . (string) $h->id,
                'label' => $h->name . ($h->city ? (' - ' . $h->city) : ''),
                'type'  => 'hotel',
                'id'    => (string) $h->id,
            ];
        })->values();

        $attractionLocations = collect();
        $restaurantLocations = collect();
        if ($cityName !== '') {
            $attractionsQuery = Attraction::query()
                ->whereNull('deleted_at')
                ->where('is_active', 1)
                ->where('location', $likeOperator, "%{$cityName}%")
                ->orderBy('name');
            $this->applyServiceDmcFilter($attractionsQuery, $dmcId);
            $attractionLocations = $attractionsQuery
                ->get(['attraction_id', 'name', 'location'])
                ->map(fn ($a) => [
                    'value' => 'attraction:' . (string) $a->attraction_id,
                    'label' => (string) $a->name . (($loc = trim((string) ($a->location ?? ''))) !== '' ? (' - ' . $loc) : ''),
                    'type'  => 'attraction',
                    'id'    => (string) $a->attraction_id,
                ])
                ->values();

            $restaurantsQuery = Restaurant::query()
                ->whereNull('deleted_at')
                ->where('is_active', 1)
                ->where('city', $likeOperator, "%{$cityName}%")
                ->orderBy('name');
            $this->applyServiceDmcFilter($restaurantsQuery, $dmcId);
            $restaurantLocations = $restaurantsQuery
                ->get(['restaurant_id', 'name', 'city'])
                ->map(fn ($r) => [
                    'value' => 'restaurant:' . (string) $r->restaurant_id,
                    'label' => (string) $r->name . (($city = trim((string) ($r->city ?? ''))) !== '' ? (' - ' . $city) : ''),
                    'type'  => 'restaurant',
                    'id'    => (string) $r->restaurant_id,
                ])
                ->values();
        }

        /** When DMC has no DefaultValue port, still resolve a canonical port for arrival/departure defaults. */
        $defaultPortFallback = null;
        if ($defaultPortRecord === null) {
            $defaultPortFallback = Port::query()
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->orderBy('port_name')
                ->first(['port_id', 'port_name']);
            $mergePortInto($defaultPortFallback, $portBuckets);
            $mergePortInto($defaultPortFallback, $arrivalPickupPortBuckets);
        }

        $locationsPorts = collect($portBuckets)->values()->sortBy('label')->values();
        $arrivalPickupPorts = collect($arrivalPickupPortBuckets)->values()->sortBy('label')->values();
        $arrivalDropLocations = $locationsPorts
            ->concat($hotelLocations)
            ->concat($attractionLocations)
            ->concat($restaurantLocations)
            ->values();

        /** Same port drives arrival pickup default and departure drop default — independent of itinerary city row. */
        $defaultPickupValue = $defaultPortRecord
            ? ('port:' . (string) $defaultPortRecord->port_id)
            : ($defaultPortFallback ? ('port:' . (string) $defaultPortFallback->port_id) : null);

        $zoneRows = DayLevel::fetchZoneTransfersForDmc($dmcId);
        if ($cityName !== '') {
            $zoneRows = array_values(array_filter($zoneRows, function ($z) use ($cityName) {
                $zoneCity = strtolower(trim((string) ($z['city'] ?? '')));
                return $zoneCity !== '' && str_contains($zoneCity, strtolower($cityName));
            }));
        }
        if ($zoneRows === []) {
            $zoneRows = DayLevel::fetchZoneTransfersForDmc($dmcId);
        }
        $zones = collect($zoneRows)->map(function ($z) {
            $zoneName = (string) ($z['zone_name'] ?? '');
            $zoneCity = (string) ($z['city'] ?? '');
            return [
                'value' => 'zone:' . (string) ($z['zone_id'] ?? ''),
                'label' => $zoneName . ($zoneCity !== '' ? (' - ' . $zoneCity) : ''),
                'id' => (string) ($z['zone_id'] ?? ''),
                'type' => 'zone',
                'city' => $zoneCity,
            ];
        })->filter(fn ($z) => $z['id'] !== '')->values();

        $serviceTransferLocations = $hotelLocations
            ->concat($attractionLocations)
            ->concat($restaurantLocations)
            ->concat($zones)
            ->values();

        return response()->json([
            'locations' => $locationsPorts->concat($hotelLocations)->values(),
            /** Hotels, attractions, restaurants & zones for per-service transfer pickup/drop. */
            'service_transfer_locations' => $serviceTransferLocations,
            /** Arrival pickup dropdown: Master DMC country ports only. */
            'arrival_pickup_ports' => $arrivalPickupPorts->values(),
            /** Arrival drop: ports (city/country) + DMC hotels, attractions, restaurants. */
            'arrival_drop_locations' => $arrivalDropLocations,
            'zones' => $zones,
            'default_pickup' => $defaultPickupValue,
            /** Explicit alias — front-end uses both for clarity (arrival pickup & departure drop). */
            'default_port_value' => $defaultPickupValue,
            'default_drop_hotel' => $defaultHotelValue,
            'master_dmc_id' => $masterDmcId,
            'country' => $country !== '' ? $country : null,
        ]);
    }

    private function resolveTransferCountry(Request $request, int $masterDmcId, int $dmcId): string
    {
        $country = trim((string) $request->input('country', ''));
        if ($country !== '') {
            return $country;
        }

        if ($masterDmcId > 0) {
            $masterUser = User::query()
                ->where('userId', $masterDmcId)
                ->whereNull('deleted_at')
                ->first();
            if ($masterUser !== null) {
                $masterCountry = trim((string) ($masterUser->country ?? ''));
                if ($masterCountry !== '') {
                    return $masterCountry;
                }
            }
        }

        if ($dmcId > 0) {
            $dmcUser = User::query()
                ->where('userId', $dmcId)
                ->whereNull('deleted_at')
                ->first();
            if ($dmcUser !== null) {
                return trim((string) ($dmcUser->country ?? ''));
            }
        }

        return '';
    }

    // =========================================================================
    // AJAX – meals by restaurant + DMC
    // GET /day-level/meals-by-restaurant?restaurant_id=12&dmc_id=6&meal_period=2
    // =========================================================================
    public function mealsByRestaurant(Request $request)
    {
        $restaurantId = (string) $request->input('restaurant_id', '');
        $mealPeriod = (int) $request->input('meal_period', 0);
        $dmcId = (int) ($request->input('dmc_id') ?: $this->resolveDmcIds()['dmc_id']);

        if ($restaurantId === '' || ! ctype_digit($restaurantId)) {
            return response()->json(['meals' => []]);
        }

        $restaurantQuery = Restaurant::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('restaurant_id', $restaurantId);

        $this->applyServiceDmcFilter($restaurantQuery, $dmcId);

        if ($restaurantQuery->first() === null) {
            return response()->json(['meals' => []]);
        }

        $query = Meal::query()
            ->whereNull('deleted_at')
            ->where('restaurant_id', $restaurantId)
            ->where('dmc_id', $dmcId)
            ->orderBy('name');

        if ($mealPeriod > 0) {
            $query->where('meal_period', $mealPeriod);
        }

        $meals = $query->get(['meal_id', 'name', 'type', 'price', 'adult_price', 'child_price', 'meal_period']);

        return response()->json([
            'meals' => $meals->map(fn ($meal) => [
                'meal_id'            => $meal->meal_id,
                'name'               => (string) ($meal->name ?? ''),
                'type'               => (int) ($meal->type ?? 0),
                'type_label'         => $this->mealTypeLabel((int) ($meal->type ?? 0)),
                'meal_period'        => (int) ($meal->meal_period ?? 0),
                'meal_period_label'  => $this->mealPeriodLabel((int) ($meal->meal_period ?? 0)),
                'price'              => $meal->price,
                'adult_price'        => $meal->adult_price,
                'child_price'        => $meal->child_price,
            ])->values(),
        ]);
    }

    private function mealTypeLabel(int $type): string
    {
        return match ($type) {
            1 => 'Buffet',
            2 => 'Set Menu',
            default => 'Other',
        };
    }

    private function mealPeriodLabel(int $period): string
    {
        return match ($period) {
            1 => 'Breakfast',
            2 => 'Lunch',
            3 => 'Dinner',
            default => '',
        };
    }

    // =========================================================================
    // AJAX – tickets by attraction for day-level page
    // Always returns 200 to keep UI smooth (empty list when unmapped)
    // GET /day-level/tickets-by-attraction?attraction_id=58&dmc_id=18
    // =========================================================================
    public function ticketsByAttraction(Request $request)
    {
        $attractionId = (string) $request->input('attraction_id', '');
        $dmcId = (int) ($request->input('dmc_id') ?: $this->resolveDmcIds()['dmc_id']);

        if ($attractionId === '') {
            return response()->json(['tickets' => []]);
        }

        $attractionQuery = Attraction::query()
            ->where('attraction_id', $attractionId);

        $this->applyServiceDmcFilter($attractionQuery, $dmcId);

        if ($attractionQuery->first() === null) {
            return response()->json(['tickets' => []]);
        }

        $tickets = Ticket::query()
            ->where('attraction_id', $attractionId)
            ->where('dmc_id', $dmcId)
            ->select('ticket_id', 'name', 'adult_price', 'child_price', 'senior_adult_price')
            ->orderBy('name')
            ->get();

        return response()->json([
            'tickets' => $tickets->map(fn ($ticket) => [
                'ticket_id'          => $ticket->ticket_id,
                'name'               => (string) ($ticket->name ?? ''),
                'adult_price'        => (float) ($ticket->adult_price ?? 0),
                'child_price'        => (float) ($ticket->child_price ?? 0),
                'senior_adult_price' => (float) ($ticket->senior_adult_price ?? 0),
            ])->values(),
        ]);
    }

    private function getExistingRoomMealColumns(): array
    {
        $base = ['room_type', 'rooms_only'];
        $optional = [
            'breakfast',
            'lunch',
            'dinner',
            'breakfast_included',
            'lunch_included',
            'dinner_included',
        ];

        $existing = [];
        foreach ($optional as $column) {
            if (Schema::hasColumn('rooms', $column)) {
                $existing[] = $column;
            }
        }

        return array_merge($base, $existing);
    }

    private function applyHotelDmcFilter($query, int $dmcId): void
    {
        $dmcIdText = (string) $dmcId;
        $query->where(function ($q) use ($dmcIdText) {
            // hotels.dmc_id is json with mixed formats; text match is safer.
            $q->whereRaw(
                "COALESCE(dmc_id::text, '') LIKE ?",
                ['%' . $dmcIdText . '%']
            )
            // Some hotels are mapped through zone_assignments only.
            ->orWhereRaw(
                "COALESCE(zone_assignments::text, '') LIKE ? OR COALESCE(zone_assignments::text, '') LIKE ?",
                ['%"dmc_id":' . $dmcIdText . '%', '%"dmc_id":"' . $dmcIdText . '"%']
            );
        });
    }

    /**
     * Attractions / restaurants: only rows mapped to the selected DMC (dmc_id JSON array).
     */
    private function applyServiceDmcFilter($query, int $dmcId): void
    {
        if ($dmcId <= 0) {
            return;
        }

        $dmcIdText = (string) $dmcId;
        $query->where(function ($q) use ($dmcId, $dmcIdText) {
            $q->whereJsonContains('dmc_id', $dmcId)
                ->orWhereRaw(
                    "COALESCE(dmc_id::text, '') LIKE ?",
                    ['%' . $dmcIdText . '%']
                );
        });
    }

    private function buildMealPlanOptionsFromRooms(array $rooms): array
    {
        $set = [];

        foreach ($rooms as $room) {
            $room = is_array($room) ? $room : [];
            $roomText = 'room';

            $hasBreakfast = $this->isTruthyMealFlag($room['breakfast'] ?? null) || $this->isTruthyMealFlag($room['breakfast_included'] ?? null);
            $hasLunch = $this->isTruthyMealFlag($room['lunch'] ?? null) || $this->isTruthyMealFlag($room['lunch_included'] ?? null);
            $hasDinner = $this->isTruthyMealFlag($room['dinner'] ?? null) || $this->isTruthyMealFlag($room['dinner_included'] ?? null);

            $set[$roomText . ' only'] = true;
            if ($hasBreakfast) $set[$roomText . ' with breakfast'] = true;
            if ($hasLunch) $set[$roomText . ' with lunch'] = true;
            if ($hasDinner) $set[$roomText . ' with dinner'] = true;
            if ($hasBreakfast && $hasLunch) $set[$roomText . ' with breakfast + lunch'] = true;
            if ($hasBreakfast && $hasDinner) $set[$roomText . ' with breakfast + dinner'] = true;
            if ($hasLunch && $hasDinner) $set[$roomText . ' with lunch + dinner'] = true;
            if ($hasBreakfast && $hasLunch && $hasDinner) {
                $set[$roomText . ' with all meals (breakfast + lunch + dinner)'] = true;
            }
        }

        if ($set === []) {
            $set = [
                'room only' => true,
                'room with breakfast' => true,
                'room with breakfast + lunch' => true,
                'room with breakfast + dinner' => true,
                'room with all meals (breakfast + lunch + dinner)' => true,
            ];
        }

        $plans = array_keys($set);
        sort($plans, SORT_NATURAL | SORT_FLAG_CASE);

        return array_map(function ($plan) {
            return [
                'value' => $plan,
                'label' => $plan,
            ];
        }, $plans);
    }

    private function isTruthyMealFlag($value): bool
    {
        if (is_bool($value)) return $value;
        if (is_numeric($value)) return (float) $value > 0;
        if (is_string($value)) return trim($value) !== '' && trim($value) !== '0';
        return false;
    }

    // =========================================================================
    // STORE
    // Inserts one row per dmc. If master_dmc_id != dmc_id, inserts for both.
    // =========================================================================
    public function store(Request $request)
    {
        if ($request->boolean('structured_mode') && !$request->filled('payload_json')) {
            return back()->withInput()->with('error', 'Payload generation failed. Please recheck form fields and submit again.');
        }

        if ($request->filled('payload_json')) {
            $payload = json_decode((string) $request->input('payload_json'), true);

            if (!is_array($payload) || !isset($payload['Master_DMC']) || !is_array($payload['Master_DMC'])) {
                return back()->withInput()->with('error', 'Invalid payload_json format.');
            }

            try {
                DB::beginTransaction();
                $savedRows = $this->storeStructuredPayload($payload);
                DB::commit();
                $this->refreshCombinedJsonFile();

                return redirect()
                    ->route('day-level.index')
                    ->with('success', "Day Level saved successfully ({$savedRows} row(s)).");
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('DayLevel structured store failed: ' . $e->getMessage());
                $msg = $e instanceof \RuntimeException
                    ? $e->getMessage()
                    : 'Structured save failed. Please try again.';

                return back()->withInput()->with('error', $msg);
            }
        }

        $validated = $request->validate([
            'city_id'               => ['required', 'integer', 'exists:cities,id'],
            'country'               => ['nullable', 'string', 'max:191'],
            'days'                  => ['required', 'integer', 'min:1', 'max:365'],
            'hotel_category'        => ['nullable', 'string', 'in:' . implode(',', array_keys(self::HOTEL_STAR_RATINGS))],
            'hotels_json'           => ['nullable', 'string'],
            'airport_transfer_type' => ['nullable', 'in:shared,private'],
            'airport_transfer_cost' => ['nullable', 'numeric', 'min:0'],
            'vehicle_id'            => ['nullable', 'integer', 'exists:vehicles,id'],
            'vehicle_service_type'  => ['nullable', 'in:private,shared'],
            'vehicle_passengers'    => ['nullable', 'integer', 'min:1'],
            'activities_json'       => ['nullable', 'string'],
            'guide_id'              => ['nullable', 'integer', 'exists:guides,id'],
            'guide_cost'            => ['nullable', 'numeric', 'min:0'],
            'inter_json'            => ['nullable', 'string'],
        ]);

        $hotelsData     = $this->decodeAndValidateHotels($request->input('hotels_json'));
        $activitiesData = $this->decodeAndValidateActivities($request->input('activities_json'));
        $interData      = $this->decodeAndValidateInter($request->input('inter_json'));

        $ids = $this->resolveDmcIds();

        $payload = [
            'city_id'               => $validated['city_id'],
            'country'               => $validated['country'] ?? null,
            'days'                  => $validated['days'],
            'hotels'                => $hotelsData,
            'airport_transfer_type' => $validated['airport_transfer_type'] ?? null,
            'airport_transfer_cost' => $validated['airport_transfer_cost'] ?? null,
            'vehicle_id'            => $validated['vehicle_id'] ?? null,
            'vehicle_service_type'  => $validated['vehicle_service_type'] ?? null,
            'vehicle_passengers'    => $validated['vehicle_passengers'] ?? null,
            'activities'            => $activitiesData,
            'guide_id'              => $validated['guide_id'] ?? null,
            'guide_cost'            => $validated['guide_cost'] ?? null,
            'inter_city'            => $interData,
            'dmc_id'                => $ids['dmc_id'],
            'master_dmc_id'         => $ids['master_dmc_id'],
        ];

        try {
            DB::beginTransaction();

            // Insert single row for DMC
            DayLevel::create($payload);

            // If master_dmc_id is different, also insert for Master DMC
            // (so master can see all records under them)
            // Uncomment the block below if you want a separate master row:
            // if ($ids['master_dmc_id'] !== $ids['dmc_id']) {
            //     $masterPayload = array_merge($payload, [
            //         'dmc_id'        => $ids['master_dmc_id'],
            //         'master_dmc_id' => $ids['master_dmc_id'],
            //     ]);
            //     DayLevel::create($masterPayload);
            // }

            DB::commit();
            $this->refreshCombinedJsonFile();

            return redirect()
                ->route('day-level.index')
                ->with('success', 'Day Level saved successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('DayLevel store failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Something went wrong. Please try again.');
        }
    }

    // =========================================================================
    // INDEX – scoped to current DMC/master
    // =========================================================================
    public function index()
    {
        $user = Auth::user();
        $allowedRoleIds = [33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138, 37, 38];

        // Check if user has permission to access this page
        if (!in_array($user->role_id, $allowedRoleIds)) {
            return redirect()->route('dashboard')->with('error', 'You have not permission for access this page');
        }

        $ids = $this->resolveDmcIds();

        $dayLevels = DayLevel::with(['city', 'guide', 'dmc', 'masterDmc'])
            ->where(function ($q) use ($ids) {
                $q->where('dmc_id', $ids['dmc_id'])
                  ->orWhere('master_dmc_id', $ids['master_dmc_id']);
            })
            ->whereNull('deleted_at')
            ->latest()
            ->get();

        return view('day-level.index', compact('dayLevels'));
    }

    // =========================================================================
    // API – combined JSON output for all/specific DMC rows
    // GET /api/v1/day-level/combined-json?master_dmc_id=3&dmc_id=4
    // =========================================================================
    public function combinedJsonApi(Request $request)
    {
        $query = DayLevel::query()
            ->with('dmc')
            ->whereNull('deleted_at')
            ->latest();

        if ($request->filled('master_dmc_id')) {
            $query->where('master_dmc_id', (int) $request->query('master_dmc_id'));
        }

        if ($request->filled('dmc_id')) {
            $query->where('dmc_id', (int) $request->query('dmc_id'));
        }

        $rows = $query->get();
        $payload = $this->normalizeFlatExportMasterDmcIds(
            $this->buildFlatDayLevelPackagesPayload($rows)
        );

        if ($request->filled('master_dmc_id')) {
            $filterMaster = (int) $request->query('master_dmc_id');
            $payload = array_values(array_filter(
                $payload,
                fn (array $entry) => (int) ($entry['Master_DMC_id'] ?? 0) === $filterMaster
            ));
        }

        if ($request->filled('dmc_id')) {
            $filterDmc = (int) $request->query('dmc_id');
            $payload = array_values(array_filter(
                $payload,
                fn (array $entry) => (int) ($entry['DMC_id'] ?? 0) === $filterDmc
            ));
        }

        // Sync Azure blobs (raw JSON array only — no API wrapper) when fetching the full catalog.
        if (! ($request->filled('master_dmc_id') || $request->filled('dmc_id'))) {
            $this->refreshCombinedJsonFile();
        }

        // Same shape as day-level-combined.json in Blob Storage: top-level array `[...]`.
        return response()->json($payload);
    }

    // =========================================================================
    // WEB – serve combined JSON file
    // GET /day-level/day-level-combined.json
    // =========================================================================
    public function combinedJsonFile()
    {
        $this->refreshCombinedJsonFile();

        $rows = DayLevel::query()
            ->with('dmc')
            ->whereNull('deleted_at')
            ->latest()
            ->get();

        return response()->json($this->buildFlatDayLevelPackagesPayload($rows));
    }

    // =========================================================================
    // SHOW
    // =========================================================================
    public function show(DayLevel $dayLevel)
    {
        $dayLevel->load('city', 'guide', 'vehicle', 'dmc', 'masterDmc');

        $packageBlocks = $dayLevel->getPackageDisplayBlocks();

        return view('day-level.show', compact('dayLevel', 'packageBlocks'));
    }

    // =========================================================================
    // EDIT
    // =========================================================================
    public function edit(Request $request, DayLevel $dayLevel)
    {
        $user = Auth::user();
        $allowedRoleIds = [33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138, 37, 38];

        // Check if user has permission to access this page
        if (!in_array($user->role_id, $allowedRoleIds)) {
            return redirect()->route('dashboard')->with('error', 'You have not permission for access this page');
        }

        $context = $this->resolveDmcContext();
        $dmcId   = (int) ($dayLevel->dmc_id ?: $context['dmc_id']);
        $masterId = (int) ($dayLevel->master_dmc_id ?: $context['master_dmc_id']);
        $dmcUser = User::query()->where('userId', $dmcId)->whereNull('deleted_at')->first();
        $masterUser = User::query()->where('userId', $masterId)->whereNull('deleted_at')->first();

        $cities = City::whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'city_id', 'name', 'country']);

        $countries = $cities->pluck('country')->filter()->unique()->sort()->values();

        $packageSummaries = $dayLevel->collectPackageSummaries();
        $packageId = trim((string) $request->query('package_id', ''));
        $editingPackageId = '';

        if ($packageId !== '') {
            $found = collect($packageSummaries)->contains(
                fn (array $summary) => $summary['package_id'] === $packageId && $summary['has_stable_id']
            );
            if (! $found) {
                return redirect()
                    ->route('day-level.index')
                    ->with('error', 'Package not found or cannot be edited individually.');
            }
            $editingPackageId = $packageId;
            $editPayload = $dayLevel->filterStructuredPayloadToPackage($packageId);
        } elseif (count($packageSummaries) > 1) {
            return redirect()
                ->route('day-level.index')
                ->with('error', 'This DMC has multiple packages. Choose Edit on the package you want to change.');
        } elseif (count($packageSummaries) === 1 && $packageSummaries[0]['has_stable_id']) {
            $editingPackageId = $packageSummaries[0]['package_id'];
            $editPayload = $dayLevel->filterStructuredPayloadToPackage($editingPackageId);
        } else {
            $editPayload = $dayLevel->structured_payload;
        }

        // Per-package day count for the edit form (row-level `days` is max across all packages).
        $editPackageDays = max(1, (int) ($dayLevel->days ?? 1));
        if ($editingPackageId !== '') {
            foreach ($packageSummaries as $summary) {
                if (($summary['package_id'] ?? '') === $editingPackageId) {
                    $editPackageDays = max(
                        1,
                        (int) ($summary['total_days'] ?: $summary['max_day'] ?: 1)
                    );
                    break;
                }
            }
        }

        return view('day-level.create', [
            'dayLevel'         => $dayLevel,
            'isEditMode'       => true,
            'editPayload'      => $editPayload,
            'editingPackageId' => $editingPackageId,
            'editPackageDays'  => $editPackageDays,
            'cities'           => $cities,
            'countries'        => $countries,
            'hotelStarRatings' => self::HOTEL_STAR_RATINGS,
            'airportTypes'     => self::AIRPORT_TYPES,
            'interVehicles'    => self::VEHICLES_STATIC,
            'masterDmcId'      => $masterId,
            'masterDmcName'    => $this->userDisplayName($masterUser, $context['master_dmc_display_name']),
            'defaultDmcId'     => $dmcId,
            'dmcName'          => $this->userDisplayName($dmcUser, $context['dmc_name']),
            'dmcCountry'       => (string) ($dmcUser?->country ?? $context['dmc_country']),
        ]);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================
    public function update(Request $request, DayLevel $dayLevel)
    {
        if ($request->boolean('structured_mode') && !$request->filled('payload_json')) {
            return back()->withInput()->with('error', 'Payload generation failed. Please recheck form fields and submit again.');
        }

        // Keep edit flow aligned with create flow: allow structured payload updates.
        if ($request->filled('payload_json')) {
            $payload = json_decode((string) $request->input('payload_json'), true);
            if (!is_array($payload) || !isset($payload['Master_DMC']) || !is_array($payload['Master_DMC'])) {
                return back()->withInput()->with('error', 'Invalid payload_json format.');
            }

            try {
                DB::beginTransaction();

                $masterNode = $payload['Master_DMC'][0] ?? null;
                if (!is_array($masterNode)) {
                    throw new \RuntimeException('Missing Master_DMC node in payload.');
                }

                $rawDestinations = is_array($masterNode['destinations'] ?? null) ? $masterNode['destinations'] : [];
                $destinations = [];
                foreach ($rawDestinations as $destination) {
                    $normalizedDestination = $this->unwrapDestinationNode($destination);
                    if (is_array($normalizedDestination)) {
                        $destinations[] = $normalizedDestination;
                    }
                }
                if ($destinations === []) {
                    throw new \RuntimeException('No destinations found in payload.');
                }

                // Prefer destination matching the current row dmc_id, fallback to first destination.
                $selectedDestination = null;
                foreach ($destinations as $destination) {
                    if (!is_array($destination)) {
                        continue;
                    }
                    if ((int) ($destination['DMC_id'] ?? 0) === (int) $dayLevel->dmc_id) {
                        $selectedDestination = $destination;
                        break;
                    }
                }
                if (!is_array($selectedDestination)) {
                    $selectedDestination = is_array($destinations[0] ?? null) ? $destinations[0] : null;
                }
                if (!is_array($selectedDestination)) {
                    throw new \RuntimeException('Unable to resolve destination from payload.');
                }

                $selectedDestination = DayLevel::canonicalizeDestinationsForStorage([$selectedDestination])[0] ?? $selectedDestination;

                $editPackageId = trim((string) $request->input('edit_package_id', ''));
                if ($editPackageId !== '') {
                    $previousDestinations = $this->extractDestinationsFromStoredDayLevel($dayLevel);
                    $previousDestinations = DayLevel::removePackageFromDestinations($previousDestinations, $editPackageId);
                    $mergedDestinations = $this->mergeDestinationsList($previousDestinations, [$selectedDestination]);
                    $mergedDestinations = DayLevel::canonicalizeDestinationsForStorage($mergedDestinations);
                } else {
                    $mergedDestinations = [$selectedDestination];
                }

                $meta = $this->computeDayLevelMetadataFromDestinations($mergedDestinations);
                $services = $this->extractTransferServicesFromDestinations($mergedDestinations);
                $resolvedDmcId = (int) ($mergedDestinations[0]['DMC_id'] ?? $dayLevel->dmc_id);
                $incomingMasterId = $this->resolveMasterDmcIdForDmcUserId($resolvedDmcId);
                if ($incomingMasterId <= 0) {
                    $incomingMasterId = (int) ($masterNode['Master_DMC_id'] ?? $dayLevel->master_dmc_id);
                }
                $country = (string) ($mergedDestinations[0]['country'] ?? $meta['country'] ?? '');
                $country = $country !== '' ? $country : null;

                $firstCityName = (string) ($meta['first_city_name'] ?? '');
                $cityId = null;
                if ($firstCityName !== '') {
                    $cityQuery = City::whereNull('deleted_at')->where('name', 'ilike', $firstCityName);
                    if (! blank($country)) {
                        $cityQuery->where('country', 'ilike', (string) $country);
                    }
                    $cityId = $cityQuery->value('id');
                }

                // Row-level days = max across all packages; per-package total_days lives on each package node.
                $rowDays = max(1, (int) ($meta['max_day_count'] ?? 1));

                $dayLevel->update([
                    'master_dmc_id'         => $incomingMasterId,
                    'dmc_id'                => (int) ($mergedDestinations[0]['DMC_id'] ?? $dayLevel->dmc_id),
                    'city_id'               => $cityId,
                    'country'               => $country,
                    'days'                  => $rowDays,
                    'hotels'                => $meta['hotels_flat'] ?? [],
                    'airport_transfer_type' => $services['airport_transfer']['type'] ?: null,
                    'airport_transfer_cost' => $services['airport_transfer']['cost'],
                    'vehicle_id'            => $services['airport_transfer']['vehicle_id'],
                    'vehicle_service_type'  => $services['airport_transfer']['vehicle_service_type'] ?: null,
                    'vehicle_passengers'    => $services['airport_transfer']['vehicle_passengers'],
                    'activities'            => $mergedDestinations,
                    'inter_city'            => $this->buildPersistedInterCityPayload($incomingMasterId, $mergedDestinations),
                ]);

                DB::commit();
                $this->refreshCombinedJsonFile();

                $successMsg = $editPackageId !== ''
                    ? 'Package updated successfully. Other packages were left unchanged.'
                    : 'Day Level updated successfully.';

                return redirect()
                    ->route('day-level.index')
                    ->with('success', $successMsg);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('DayLevel structured update failed: ' . $e->getMessage());
                return back()->withInput()->with('error', 'Structured update failed. Please try again.');
            }
        }

        $validated = $request->validate([
            'city_id'               => ['required', 'integer', 'exists:cities,id'],
            'country'               => ['nullable', 'string', 'max:191'],
            'days'                  => ['required', 'integer', 'min:1', 'max:365'],
            'hotel_category'        => ['nullable', 'string', 'in:' . implode(',', array_keys(self::HOTEL_STAR_RATINGS))],
            'hotels_json'           => ['nullable', 'string'],
            'airport_transfer_type' => ['nullable', 'in:shared,private'],
            'airport_transfer_cost' => ['nullable', 'numeric', 'min:0'],
            'vehicle_id'            => ['nullable', 'integer', 'exists:vehicles,id'],
            'vehicle_service_type'  => ['nullable', 'in:private,shared'],
            'vehicle_passengers'    => ['nullable', 'integer', 'min:1'],
            'activities_json'       => ['nullable', 'string'],
            'guide_id'              => ['nullable', 'integer', 'exists:guides,id'],
            'guide_cost'            => ['nullable', 'numeric', 'min:0'],
            'inter_json'            => ['nullable', 'string'],
        ]);

        $hotelsData     = $this->decodeAndValidateHotels($request->input('hotels_json'));
        $activitiesData = $this->decodeAndValidateActivities($request->input('activities_json'));
        $interData      = $this->decodeAndValidateInter($request->input('inter_json'));

        try {
            DB::beginTransaction();

            $dayLevel->update([
                'city_id'               => $validated['city_id'],
                'country'               => $validated['country'] ?? null,
                'days'                  => $validated['days'],
                'hotels'                => $hotelsData,
                'airport_transfer_type' => $validated['airport_transfer_type'] ?? null,
                'airport_transfer_cost' => $validated['airport_transfer_cost'] ?? null,
                'vehicle_id'            => $validated['vehicle_id'] ?? null,
                'vehicle_service_type'  => $validated['vehicle_service_type'] ?? null,
                'vehicle_passengers'    => $validated['vehicle_passengers'] ?? null,
                'activities'            => $activitiesData,
                'guide_id'              => $validated['guide_id'] ?? null,
                'guide_cost'            => $validated['guide_cost'] ?? null,
                'inter_city'            => $interData,
            ]);

            DB::commit();
            $this->refreshCombinedJsonFile();

            return redirect()
                ->route('day-level.show', $dayLevel)
                ->with('success', 'Day Level updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('DayLevel update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Update failed. Please try again.');
        }
    }

    // =========================================================================
    // DESTROY
    // =========================================================================
    public function destroy(DayLevel $dayLevel)
    {
        $user = Auth::user();
        $allowedRoleIds = [33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138, 37, 38];

        // Check if user has permission to access this page
        if (!in_array($user->role_id, $allowedRoleIds)) {
            return redirect()->route('dashboard')->with('error', 'You have not permission for access this page');
        }

        $dayLevel->delete();
        $this->refreshCombinedJsonFile();
        return redirect()->route('day-level.index')->with('success', 'Day Level deleted.');
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function decodeAndValidateHotels(?string $json): array
    {
        if (blank($json)) return [];
        $rows = json_decode($json, true);
        if (!is_array($rows)) return [];

        $validHotelIds = [];
        foreach (Hotel::whereNull('deleted_at')->get(['id', 'hotel_unique_id']) as $hotelRow) {
            $validHotelIds[(string) $hotelRow->id] = true;
            $uniqueId = trim((string) ($hotelRow->hotel_unique_id ?? ''));
            if ($uniqueId !== '') {
                $validHotelIds[$uniqueId] = true;
            }
        }
        $catBuckets    = [];
        $catCount      = [];

        foreach ($rows as $row) {
            $cat      = (string) ($row['cv'] ?? $row['cat'] ?? '');
            $hotelId  = trim((string) ($row['hotel_id'] ?? ''));
            $priority = (int) ($row['pri'] ?? 1);

            if (!array_key_exists($cat, self::HOTEL_STAR_RATINGS)) continue;
            if ($hotelId === '' || !isset($validHotelIds[$hotelId])) continue;

            // No limit per category — user may add as many as needed
            $catBuckets[$cat][] = [
                'cat'      => $cat,
                'hotel_id' => $hotelId,
                'priority' => max(1, $priority),
            ];
        }

        $cleaned = [];
        foreach ($catBuckets as $items) {
            usort($items, fn($a, $b) => $a['priority'] <=> $b['priority']);
            $i = 1;
            foreach ($items as $item) {
                $item['priority'] = $i++;
                $cleaned[] = $item;
            }
        }
        return $cleaned;
    }

    private function decodeAndValidateActivities(?string $json): array
    {
        if (blank($json)) return [];
        $rows = json_decode($json, true);
        if (!is_array($rows)) return [];

        $validTypes = ['attraction', 'transfer', 'food'];
        $cleaned    = [];

        foreach ($rows as $row) {
            $type = $row['tab'] ?? $row['type'] ?? '';
            if (!in_array($type, $validTypes, true)) continue;

            $cleaned[] = [
                'type'      => $type,
                'day'       => (int) ($row['day'] ?? 1),
                'detail'    => strip_tags((string) ($row['detail'] ?? '')),
                'detail_id' => isset($row['detail_id']) ? (int) $row['detail_id'] : null,
                'cost'      => (float) ($row['cost'] ?? 0),
            ];
        }
        return $cleaned;
    }

    private function decodeAndValidateInter(?string $json): array
    {
        if (blank($json)) return [];
        $rows = json_decode($json, true);
        if (!is_array($rows)) return [];

        $validCityIds = City::whereNull('deleted_at')->pluck('id')->flip();
        $cleaned      = [];

        foreach ($rows as $row) {
            $cityId    = isset($row['city_id']) ? (int) $row['city_id'] : null;
            $cleaned[] = [
                'city_id'       => ($cityId && isset($validCityIds[$cityId])) ? $cityId : null,
                'city_name'     => strip_tags((string) ($row['city']  ?? '')),
                'transfer_type' => strip_tags((string) ($row['tr']    ?? '')),
                'vehicle'       => strip_tags((string) ($row['veh']   ?? '')),
                'cost'          => (float) ($row['cost'] ?? 0),
                'priority'      => max(1, min(10, (int) ($row['pri'] ?? $row['priority'] ?? 1))),
            ];
        }
        return $cleaned;
    }

    private function storeStructuredPayload(array $payload): int
    {
        $rowsByMasterAndDmc = [];

        foreach ($payload['Master_DMC'] as $masterNode) {
            foreach (($masterNode['destinations'] ?? []) as $destinationNode) {
                $destination = $this->unwrapDestinationNode($destinationNode);
                if (!is_array($destination)) {
                    continue;
                }
                $dmcId = (int) ($destination['DMC_id'] ?? 0);
                if ($dmcId <= 0) {
                    continue;
                }

                $masterId = $this->resolveMasterDmcIdForDmcUserId($dmcId);
                if ($masterId <= 0) {
                    $masterId = (int) ($masterNode['Master_DMC_id'] ?? 0);
                }
                if ($masterId <= 0) {
                    continue;
                }

                $key = $masterId . ':' . $dmcId;
                if (!isset($rowsByMasterAndDmc[$key])) {
                    $rowsByMasterAndDmc[$key] = [
                        'master_dmc_id' => $masterId,
                        'dmc_id'        => $dmcId,
                        'destinations'  => [],
                    ];
                }

                $rowsByMasterAndDmc[$key]['destinations'][] = $destination;
            }
        }

        $saved = 0;

        foreach ($rowsByMasterAndDmc as $row) {
            $masterId   = (int) $row['master_dmc_id'];
            $dmcId      = (int) $row['dmc_id'];
            $incoming   = $row['destinations'];

            $existing = DayLevel::query()
                ->where('master_dmc_id', $masterId)
                ->where('dmc_id', $dmcId)
                ->whereNull('deleted_at')
                ->first();

            $previousDestinations = $this->extractDestinationsFromStoredDayLevel($existing);
            $mergedDestinations  = $this->mergeDestinationsList($previousDestinations, $incoming);
            $mergedDestinations  = DayLevel::canonicalizeDestinationsForStorage($mergedDestinations);

            $meta = $this->computeDayLevelMetadataFromDestinations($mergedDestinations);
            $services = $this->extractTransferServicesFromDestinations($mergedDestinations);
            $country      = $meta['country'] !== '' ? $meta['country'] : null;
            $firstCityName = $meta['first_city_name'];

            $cityId = null;
            if ($firstCityName !== '') {
                $cityQuery = City::whereNull('deleted_at')
                    ->where('name', 'ilike', $firstCityName);
                if ($country !== null && $country !== '') {
                    $cityQuery->where('country', 'ilike', $country);
                }
                $cityId = $cityQuery->value('id');
            }

            DayLevel::updateOrCreate(
                [
                    'master_dmc_id' => $masterId,
                    'dmc_id'        => $dmcId,
                ],
                [
                    'city_id'    => $cityId,
                    'country'    => $country,
                    'days'       => $meta['max_day_count'] > 0 ? $meta['max_day_count'] : 1,
                    'hotels'     => $meta['hotels_flat'],
                    'airport_transfer_type' => $services['airport_transfer']['type'] ?: null,
                    'airport_transfer_cost' => $services['airport_transfer']['cost'],
                    'vehicle_id'            => $services['airport_transfer']['vehicle_id'],
                    'vehicle_service_type'  => $services['airport_transfer']['vehicle_service_type'] ?: null,
                    'vehicle_passengers'    => $services['airport_transfer']['vehicle_passengers'],
                    'activities' => $mergedDestinations,
                    'inter_city'   => $this->buildPersistedInterCityPayload($masterId, $mergedDestinations),
                ]
            );

            $saved++;
        }

        $hadValidMaster = false;
        foreach ($payload['Master_DMC'] as $masterNode) {
            if (is_array($masterNode) && (int) ($masterNode['Master_DMC_id'] ?? 0) > 0) {
                $hadValidMaster = true;
                break;
            }
        }
        if ($hadValidMaster && $saved === 0) {
            throw new \RuntimeException(
                'No Day Level rows were saved. Check Master_DMC_id and each destination DMC_id in payload_json.'
            );
        }

        return $saved;
    }

    /**
     * When saving structured JSON, load any destinations already stored for this
     * DMC (same master) so a new "package" appends to cities[].packages[] instead
     * of replacing the only package.
     */
    private function extractDestinationsFromStoredDayLevel(?DayLevel $row): array
    {
        if (! $row) {
            return [];
        }

        $ic = $row->inter_city;
        if (is_array($ic)) {
            if (isset($ic['Master_DMC']) && is_array($ic['Master_DMC'])) {
                $destinations = [];
                foreach ($ic['Master_DMC'] as $masterNode) {
                    if (! is_array($masterNode)) {
                        continue;
                    }
                    foreach ((array) ($masterNode['destinations'] ?? []) as $destinationNode) {
                        $destination = $this->unwrapDestinationNode($destinationNode);
                        if (is_array($destination)) {
                            $destinations[] = $destination;
                        }
                    }
                }
                if ($destinations !== []) {
                    return $destinations;
                }
            }

            if (isset($ic['destinations']) && is_array($ic['destinations'])) {
                return $ic['destinations'];
            }
        }

        $act = $row->activities;
        if (is_array($act) && $act !== [] && $this->looksLikeDestinationsList($act)) {
            return $act;
        }

        return [];
    }

    /**
     * Persist the full structured JSON envelope (same shape as payload_json / API output).
     *
     * @param  array<int, array<string, mixed>>  $mergedDestinations
     * @return array{Master_DMC: array<int, array<string, mixed>>}
     */
    private function buildPersistedInterCityPayload(int $masterId, array $mergedDestinations): array
    {
        return [
            'Master_DMC' => [
                [
                    'Master_DMC_id' => $masterId,
                    'destinations'  => array_values($mergedDestinations),
                ],
            ],
        ];
    }

    private function looksLikeDestinationsList(array $arr): bool
    {
        if (! array_is_list($arr)) {
            return isset($arr['DMC_id']) || isset($arr['cities']);
        }

        $first = $arr[0] ?? null;

        return is_array($first) && (isset($first['DMC_id']) || isset($first['cities']));
    }

    private function mergeDestinationsList(array $existing, array $incoming): array
    {
        if ($incoming === []) {
            return $existing;
        }
        if ($existing === []) {
            return $incoming;
        }

        $merged = $existing;
        foreach ($incoming as $dIn) {
            if (! is_array($dIn)) {
                continue;
            }
            $dmcIn = (int) ($dIn['DMC_id'] ?? 0);
            $cKey  = $this->normalizeLocationKey($dIn['country'] ?? '');
            $idx   = $this->findDestinationIndex($merged, $dmcIn, $cKey);
            if ($idx === null) {
                $merged[] = $dIn;
            } else {
                $merged[$idx] = $this->mergeCitiesIntoDestination($merged[$idx], $dIn);
            }
        }

        return $merged;
    }

    private function findDestinationIndex(array $destinations, int $dmcId, string $countryKey): ?int
    {
        foreach ($destinations as $i => $d) {
            if (! is_array($d)) {
                continue;
            }
            if ((int) ($d['DMC_id'] ?? 0) !== $dmcId) {
                continue;
            }
            if ($this->normalizeLocationKey($d['country'] ?? '') === $countryKey) {
                return (int) $i;
            }
        }

        return null;
    }

    private function mergeCitiesIntoDestination(array $destExisting, array $destIncoming): array
    {
        $cities         = is_array($destExisting['cities'] ?? null) ? $destExisting['cities'] : [];
        $incomingCities = is_array($destIncoming['cities'] ?? null) ? $destIncoming['cities'] : [];
        foreach ($incomingCities as $cIn) {
            if (! is_array($cIn)) {
                continue;
            }
            $cityKey = $this->normalizeLocationKey($cIn['city'] ?? '');
            $cIdx    = $this->findCityIndex($cities, $cityKey);
            if ($cIdx === null) {
                $cities[] = $cIn;
                continue;
            }
            // Merge city metadata, but treat incoming city packages as source-of-truth snapshot.
            // This prevents stale/old package fragments from being carried forward and split output.
            $prev = $cities[$cIdx];
            $mergedCity = array_merge($prev, $cIn);
            if (array_key_exists('packages', $cIn) && is_array($cIn['packages'])) {
                $incomingPackages = $cIn['packages'];
                $mergedByIdentity = [];
                $existingPackages = is_array($prev['packages'] ?? null) ? $prev['packages'] : [];
                foreach (array_values($existingPackages) as $pkgIdx => $pkg) {
                    if (! is_array($pkg)) {
                        continue;
                    }
                    $mergedByIdentity[$this->inferPackageIdentity($pkg, $pkgIdx)] = $pkg;
                }
                foreach (array_values($incomingPackages) as $pkgIdx => $pkg) {
                    if (! is_array($pkg)) {
                        continue;
                    }
                    $identity = $this->inferPackageIdentity($pkg, $pkgIdx);
                    $mergedByIdentity[$identity] = $pkg;
                }

                $mergedCity['packages'] = array_values($mergedByIdentity);
            }
            $cities[$cIdx] = $mergedCity;
        }

        $out = $destExisting;
        $out['cities'] = $cities;
        if (isset($destIncoming['DMC_id'])) {
            $out['DMC_id'] = (int) $destIncoming['DMC_id'];
        }
        if (array_key_exists('country', $destIncoming)) {
            $out['country'] = (string) $destIncoming['country'];
        }
        if (array_key_exists('service_meta', $destIncoming)) {
            $out['service_meta'] = is_array($destIncoming['service_meta']) ? $destIncoming['service_meta'] : [];
        } elseif (array_key_exists('services', $destIncoming)) {
            // Backward compatibility with older destination-level services shape.
            $out['service_meta'] = is_array($destIncoming['services']) ? $destIncoming['services'] : [];
        }

        // Preserve normalized destination shape when source rows already contain
        // top-level packages/list buckets (not only cities[] packages[]).
        foreach (['packages', 'list_all_services', 'list_all_transport', 'company_name'] as $field) {
            if (array_key_exists($field, $destIncoming)) {
                $out[$field] = $destIncoming[$field];
            }
        }

        return $out;
    }

    private function inferPackageIdentity(array $packageNode, int $fallbackIndex = 0): string
    {
        $packageId = trim((string) ($packageNode['package_id'] ?? $packageNode['packageId'] ?? ''));
        if ($packageId !== '') {
            return 'id:' . $packageId;
        }

        return 'index:' . $fallbackIndex;
    }

    private function findCityIndex(array $cities, string $cityNameKey): ?int
    {
        foreach ($cities as $i => $c) {
            if (! is_array($c)) {
                continue;
            }
            if ($this->normalizeLocationKey($c['city'] ?? '') === $cityNameKey) {
                return (int) $i;
            }
        }

        return null;
    }

    private function normalizeLocationKey($value): string
    {
        return mb_strtolower(trim((string) $value));
    }

    private function unwrapDestinationNode($raw): ?array
    {
        if (!is_array($raw)) {
            return null;
        }

        if (array_key_exists('DMC_id', $raw)) {
            return $raw;
        }

        $dmcList = $raw['DMC'] ?? null;
        if (is_array($dmcList) && isset($dmcList[0]) && is_array($dmcList[0])) {
            return $dmcList[0];
        }

        return null;
    }

    /**
     * Pull services from structured payload:
     * destinations[].services.airport_transfer / departure_transfer
     */
    private function extractTransferServicesFromDestinations(array $destinations): array
    {
        $empty = [
            'type'                 => '',
            'vehicle_id'           => null,
            'vehicle_service_type' => '',
            'vehicle_passengers'   => null,
            'cost'                 => null,
        ];

        $airport = $empty;
        $departure = $empty;

        foreach ($destinations as $destination) {
            if (!is_array($destination)) {
                continue;
            }
            $services = $destination['service_meta'] ?? ($destination['services'] ?? null);
            if (!is_array($services)) {
                continue;
            }

            if ($airport === $empty && is_array($services['airport_transfer'] ?? null)) {
                $airport = $this->normalizeTransferService($services['airport_transfer']);
            }
            if ($departure === $empty && is_array($services['departure_transfer'] ?? null)) {
                $departure = $this->normalizeTransferService($services['departure_transfer']);
            }
        }

        return [
            'airport_transfer'   => $airport,
            'departure_transfer' => $departure,
        ];
    }

    private function normalizeTransferService(array $service): array
    {
        $vehicleId = isset($service['vehicle_id']) && $service['vehicle_id'] !== ''
            ? (int) $service['vehicle_id']
            : null;
        $passengers = isset($service['vehicle_passengers']) && $service['vehicle_passengers'] !== ''
            ? (int) $service['vehicle_passengers']
            : null;
        $cost = isset($service['cost']) && $service['cost'] !== ''
            ? (float) $service['cost']
            : null;

        return [
            'type'                 => (string) ($service['type'] ?? ''),
            'vehicle_id'           => $vehicleId,
            'vehicle_service_type' => (string) ($service['vehicle_service_type'] ?? ''),
            'vehicle_passengers'   => $passengers,
            'cost'                 => $cost,
        ];
    }

    /**
     * @return array{country: string, first_city_name: string, max_day_count: int, hotels_flat: array}
     */
    private function computeDayLevelMetadataFromDestinations(array $destinations): array
    {
        $maxDay        = 0;
        $hotelsFlat    = [];
        $firstCityName = '';
        $country       = '';

        foreach ($destinations as $dest) {
            if (! is_array($dest)) {
                continue;
            }
            if ($country === '' && $dest['country'] !== null && $dest['country'] !== '') {
                $country = (string) $dest['country'];
            }
            foreach ((array) ($dest['cities'] ?? []) as $city) {
                if (! is_array($city)) {
                    continue;
                }
                if ($firstCityName === '' && ($city['city'] ?? '') !== '') {
                    $firstCityName = (string) $city['city'];
                }
                if (isset($city['checkout_day'])) {
                    $co = (int) $city['checkout_day'];
                    if ($co > 0) {
                        $maxDay = max($maxDay, $co);
                    }
                }
                foreach ((array) ($city['packages'] ?? []) as $package) {
                    if (! is_array($package)) {
                        continue;
                    }
                    $daysRaw = $package['days'] ?? [];
                    $daysList = is_array($daysRaw) ? $daysRaw : [];
                    $pkgDayMax = 0;
                    foreach ($daysList as $dayNode) {
                        if (! is_array($dayNode)) {
                            continue;
                        }
                        $dayNum = (int) ($dayNode['day'] ?? 0);
                        if ($dayNum > 0) {
                            $pkgDayMax = max($pkgDayMax, $dayNum);
                        }
                        foreach ((array) ($dayNode['hotels'] ?? []) as $hotelNode) {
                            $hotelsFlat[] = $hotelNode;
                        }
                    }
                    if ($pkgDayMax > 0) {
                        $maxDay = max($maxDay, $pkgDayMax);
                    } elseif ($daysList !== []) {
                        // Legacy: positional day slots without explicit `day` property.
                        $maxDay = max($maxDay, count($daysList));
                    }
                    $pkgTotalDays = (int) ($package['total_days'] ?? $package['totalDays'] ?? 0);
                    if ($pkgTotalDays > 0) {
                        $maxDay = max($maxDay, $pkgTotalDays);
                    }
                }
            }
        }

        if ($maxDay < 1) {
            $maxDay = 1;
        }

        return [
            'country'         => $country,
            'first_city_name' => $firstCityName,
            'max_day_count'   => $maxDay,
            'hotels_flat'     => $hotelsFlat,
        ];
    }

    /**
     * Flat day-level export: one object per package with raw_package / raw_all_services strings.
     *
     * @param  \Illuminate\Support\Collection<int, DayLevel>|iterable  $rows
     * @return list<array<string, mixed>>
     */
    private function buildFlatDayLevelPackagesPayload($rows): array
    {
        return DayLevel::collectFlatPackageExportsFromRows($rows);
    }

    /**
     * Combine all visible day_levels rows into one output JSON:
     * { Master_DMC: [ { Master_DMC_id, destinations: [...] }, ... ] }
     */
    private function buildCombinedStructuredPayload($rows): array
    {
        $masters = [];

        foreach ($rows as $row) {
            $payload = $row->structured_payload;
            $masterNodes = is_array($payload['Master_DMC'] ?? null) ? $payload['Master_DMC'] : [];

            foreach ($masterNodes as $masterNode) {
                if (!is_array($masterNode)) {
                    continue;
                }

                $masterId = (int) ($masterNode['Master_DMC_id'] ?? 0);
                if ($masterId <= 0) {
                    continue;
                }

                if (!isset($masters[$masterId])) {
                    $masters[$masterId] = [
                        'Master_DMC_id' => $masterId,
                        'destinations'  => [],
                    ];
                }

                foreach ((array) ($masterNode['destinations'] ?? []) as $destinationNode) {
                    $destination = $this->unwrapDestinationNode($destinationNode);
                    if (!is_array($destination)) {
                        continue;
                    }
                    $dmcId = (int) ($destination['DMC_id'] ?? 0);
                    if ($dmcId <= 0) {
                        continue;
                    }
                    // Single source of truth per DMC. Avoid duplicate destination blocks
                    // caused by country-key drift / legacy rows.
                    $destKey = (string) $dmcId;

                    if (!isset($masters[$masterId]['destinations'][$destKey])) {
                        $masters[$masterId]['destinations'][$destKey] = $destination;
                        continue;
                    }

                    $masters[$masterId]['destinations'][$destKey] = $this->mergeCitiesIntoDestination(
                        $masters[$masterId]['destinations'][$destKey],
                        $destination
                    );
                }
            }
        }

        // Do not inject placeholder child DMC destinations with empty cities/packages.
        // Combined JSON should contain only persisted package data.

        $masterList = array_values(array_map(function ($master) {
            $normalizedDestinations = [];
            foreach ($master['destinations'] as $destination) {
                if (!is_array($destination) || !$this->destinationHasRealPackageDays($destination)) {
                    continue;
                }
                $normalizedDestinations[] = [
                    'DMC' => [
                        $this->transformDestinationForExternalPayload($destination),
                    ],
                ];
            }
            $master['destinations'] = array_values($normalizedDestinations);
            return $master;
        }, $masters));

        usort($masterList, fn($a, $b) => ((int) $a['Master_DMC_id']) <=> ((int) $b['Master_DMC_id']));

        $dmcIdsForEmail = [];
        foreach ($masterList as $master) {
            foreach ((array) ($master['destinations'] ?? []) as $destWrap) {
                $dmcNode = is_array($destWrap['DMC'][0] ?? null) ? $destWrap['DMC'][0] : null;
                if ($dmcNode === null) {
                    continue;
                }
                $id = (int) ($dmcNode['DMC_id'] ?? 0);
                if ($id > 0) {
                    $dmcIdsForEmail[$id] = true;
                }
            }
        }
        $dmcIdsForEmail = array_keys($dmcIdsForEmail);
        $emailsByDmcId = $dmcIdsForEmail === [] ? collect()
            : User::query()->whereIn('userId', $dmcIdsForEmail)->pluck('email', 'userId');

        foreach ($masterList as $mi => $master) {
            foreach ((array) ($master['destinations'] ?? []) as $di => $destWrap) {
                if (! isset($destWrap['DMC'][0]) || ! is_array($destWrap['DMC'][0])) {
                    continue;
                }
                $dmcId = (int) ($destWrap['DMC'][0]['DMC_id'] ?? 0);
                $node = $masterList[$mi]['destinations'][$di]['DMC'][0];
                $existing = trim((string) ($node['DMC_email'] ?? ''));
                if ($existing === '' && $dmcId > 0) {
                    $fromUser = trim((string) ($emailsByDmcId[$dmcId] ?? ''));
                    if ($fromUser !== '') {
                        $node['DMC_email'] = $fromUser;
                    }
                }
                $masterList[$mi]['destinations'][$di]['DMC'][0] = $this->orderDmcExternalKeys($node);
            }
        }

        return ['Master_DMC' => $masterList];
    }

    private function destinationHasRealPackageDays(array $destination): bool
    {
        // Newer/normalized payload shape: destination.packages[].days
        $destinationPackages = is_array($destination['packages'] ?? null) ? $destination['packages'] : [];
        foreach ($destinationPackages as $package) {
            if (!is_array($package)) {
                continue;
            }
            $days = $package['days'] ?? [];
            if (is_object($days)) {
                $days = (array) $days;
            }
            if (!is_array($days)) {
                continue;
            }
            foreach ($days as $dayNode) {
                if (is_object($dayNode)) {
                    $dayNode = (array) $dayNode;
                }
                if (is_array($dayNode) && !empty($dayNode)) {
                    return true;
                }
            }
        }

        // Backward-compatible shape: destination.cities[].packages[].days
        $cities = is_array($destination['cities'] ?? null) ? $destination['cities'] : [];
        foreach ($cities as $city) {
            if (!is_array($city)) {
                continue;
            }
            foreach ((array) ($city['packages'] ?? []) as $package) {
                if (!is_array($package)) {
                    continue;
                }
                $days = (array) ($package['days'] ?? []);
                foreach ($days as $dayNode) {
                    if (is_array($dayNode) && !empty($dayNode)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Ensure every role-11 child DMC linked to master_dmc_id appears in combined JSON even when no day-level row exists yet.
     */
    private function appendMissingMasterChildDmcs(array &$masterBucket, int $masterId): void
    {
        if ($masterId <= 0) {
            return;
        }

        $presentDmcIds = [];
        foreach ((array) ($masterBucket['destinations'] ?? []) as $existing) {
            if (! is_array($existing)) {
                continue;
            }
            $id = (int) ($existing['DMC_id'] ?? 0);
            if ($id > 0) {
                $presentDmcIds[$id] = true;
            }
        }

        $childDmcs = User::query()
            ->where('role_id', 11)
            ->where('master_dmc_id', $masterId)
            ->orderBy('company_name')
            ->get(['userId', 'company_name', 'country']);

        foreach ($childDmcs as $u) {
            $dmcId = (int) $u->userId;
            if ($dmcId <= 0 || isset($presentDmcIds[$dmcId])) {
                continue;
            }

            $countryRaw = trim((string) ($u->country ?? ''));
            $countryPieces = array_map('trim', explode(',', $countryRaw));
            $country = trim((string) ($countryPieces[0] ?? $countryRaw));
            $countryKey = $this->normalizeLocationKey($country);
            $destKey = $dmcId . '|' . $countryKey;

            if (isset($masterBucket['destinations'][$destKey])) {
                continue;
            }

            $masterBucket['destinations'][$destKey] = [
                'DMC_id'       => $dmcId,
                'country'      => $country !== '' ? $country : $countryRaw,
                'company_name' => trim((string) ($u->company_name ?? '')),
                'cities'       => [],
            ];

            $presentDmcIds[$dmcId] = true;
        }
    }

    /**
     * Put DMC_email immediately after DMC_id so JSON output matches the expected field order.
     */
    private function orderDmcExternalKeys(array $dmc): array
    {
        if (! array_key_exists('DMC_id', $dmc)) {
            return $dmc;
        }

        $out = ['DMC_id' => $dmc['DMC_id']];
        $email = trim((string) ($dmc['DMC_email'] ?? ''));
        if ($email !== '') {
            $out['DMC_email'] = $email;
        }
        foreach ($dmc as $k => $v) {
            if ($k === 'DMC_id' || $k === 'DMC_email') {
                continue;
            }
            $out[$k] = $v;
        }

        return $out;
    }

    private function transformDestinationForExternalPayload(array $destination): array
    {
        // If destination is already in external payload shape (top-level packages
        // and service buckets), keep it as source-of-truth and avoid rebuilding it
        // from cities[] (which may be absent in normalized rows).
        if (
            is_array($destination['packages'] ?? null)
            && !is_array($destination['cities'] ?? null)
        ) {
            $email = trim((string) ($destination['DMC_email'] ?? $destination['email'] ?? ''));
            $destination = DayLevel::transformDestinationAttractionsForApi($destination);
            $destination = DayLevel::transformDestinationLocationsForApi($destination);
            if ($email !== '') {
                return $this->orderDmcExternalKeys(array_merge($destination, ['DMC_email' => $email]));
            }

            return $this->orderDmcExternalKeys($destination);
        }

        $cities = is_array($destination['cities'] ?? null) ? $destination['cities'] : [];
        $days = $this->collectMergedDaysWithCities($cities);
        $packages = $this->collectPackagesWithCities($cities);
        $serviceAndTransportBuckets = $this->buildDmcWideServiceBuckets($days, $destination);
        $normalizedPackages = $packages !== [] ? $packages : [[
            'days' => $this->toIndexedObject($days),
        ]];

        $email = trim((string) ($destination['DMC_email'] ?? $destination['email'] ?? ''));

        $out = ['DMC_id' => (int) ($destination['DMC_id'] ?? 0)];
        if ($email !== '') {
            $out['DMC_email'] = $email;
        }
        $out = array_merge($out, [
            'country' => (string) ($destination['country'] ?? ''),
            'list_all_services' => $serviceAndTransportBuckets['list_all_services'],
            'list_all_transport' => $serviceAndTransportBuckets['list_all_transport'],
            'packages' => $normalizedPackages,
        ], $this->buildNamedPackageBlocks($normalizedPackages));

        $company = trim((string) ($destination['company_name'] ?? ''));
        if ($company !== '') {
            $out['DMC_company_name'] = $company;
        }

        $out = DayLevel::transformDestinationAttractionsForApi($out);

        return $this->orderDmcExternalKeys(DayLevel::transformDestinationLocationsForApi($out));
    }

    private function collectPackagesWithCities(array $cities): array
    {
        $citySummaries = [];
        foreach ($cities as $cityNode) {
            if (!is_array($cityNode)) {
                continue;
            }
            $citySummaries[] = [
                'city' => (string) ($cityNode['city'] ?? ''),
                'city_checkin' => (string) ($cityNode['checkin_day'] ?? ''),
                'city_checkout' => (string) ($cityNode['checkout_day'] ?? ''),
            ];
        }

        /**
         * A "package" is a user-created duration block, not a city bucket.
         * Cities/hotels can vary within the same package, so merge package days across cities.
         */
        $packagesByIdentity = [];
        foreach ($cities as $cityNode) {
            if (!is_array($cityNode)) {
                continue;
            }
            foreach (array_values((array) ($cityNode['packages'] ?? [])) as $pkgIdx => $packageNode) {
                if (!is_array($packageNode)) {
                    continue;
                }
                $dayNodes = (array) ($packageNode['days'] ?? []);
                $identity = $this->inferPackageIdentity($packageNode, $pkgIdx);
                $entry = $packagesByIdentity[$identity] ?? ['meta' => [], 'days' => []];
                $daysByNumber = is_array($entry['days'] ?? null) ? $entry['days'] : [];
                if ($entry['meta'] === []) {
                    $entry['meta'] = $this->extractPackageMeta($packageNode);
                }
                foreach ($dayNodes as $dayIdx => $dayNode) {
                    if (!is_array($dayNode)) {
                        continue;
                    }
                    $normalizedDay = $dayNode;
                    if (!is_array($normalizedDay['cities'] ?? null) && $citySummaries !== []) {
                        $normalizedDay['cities'] = $this->toIndexedObject($citySummaries);
                    }
                    $normalizedDay['hotels'] = $this->normalizeDayHotelsWithNight(
                        is_array($normalizedDay['hotels'] ?? null) ? $normalizedDay['hotels'] : []
                    );
                    $normalizedDay['attractions'] = is_array($normalizedDay['attractions'] ?? null) ? $normalizedDay['attractions'] : [];
                    $normalizedDay['arrivals'] = is_array($normalizedDay['arrivals'] ?? null) ? $normalizedDay['arrivals'] : [];
                    $normalizedDay['departures'] = is_array($normalizedDay['departures'] ?? null) ? $normalizedDay['departures'] : [];
                    $normalizedDay['transfers'] = is_array($normalizedDay['transfers'] ?? null) ? $normalizedDay['transfers'] : [];
                    $normalizedDay['restaurants'] = is_array($normalizedDay['restaurants'] ?? null) ? $normalizedDay['restaurants'] : [];
                    $normalizedDay['Transfer'] = is_array($normalizedDay['Transfer'] ?? null)
                        ? $normalizedDay['Transfer']
                        : $this->extractDayTransfers($normalizedDay);
                    $normalizedDay['Guide'] = is_array($normalizedDay['Guide'] ?? null)
                        ? $normalizedDay['Guide']
                        : $this->extractDayGuides($normalizedDay);
                    $normalizedDay['services'] = is_array($normalizedDay['services'] ?? null) ? $normalizedDay['services'] : [];

                    $dayNumber = (int) ($normalizedDay['day'] ?? 0);
                    if ($dayNumber <= 0) {
                        $dayNumber = is_numeric((string) $dayIdx) ? ((int) $dayIdx + 1) : 0;
                    }
                    if ($dayNumber <= 0) {
                        continue;
                    }

                    if (!isset($daysByNumber[$dayNumber])) {
                        $daysByNumber[$dayNumber] = $normalizedDay;
                    } else {
                        $daysByNumber[$dayNumber] = array_replace($daysByNumber[$dayNumber], $normalizedDay);
                        foreach (['hotels', 'attractions', 'arrivals', 'departures', 'transfers', 'restaurants', 'services', 'Transfer', 'Guide'] as $bucket) {
                            if (!array_key_exists($bucket, $normalizedDay)) {
                                continue;
                            }
                            if (!is_array($normalizedDay[$bucket])) {
                                continue;
                            }
                            $prev = is_array($daysByNumber[$dayNumber][$bucket] ?? null) ? $daysByNumber[$dayNumber][$bucket] : [];
                            $daysByNumber[$dayNumber][$bucket] = array_replace($prev, $normalizedDay[$bucket]);
                        }
                    }
                }
                $entry['days'] = $daysByNumber;
                $packagesByIdentity[$identity] = $entry;
            }
        }

        $packages = [];
        foreach ($packagesByIdentity as $entry) {
            $daysByNumber = is_array($entry['days'] ?? null) ? $entry['days'] : [];
            if (!is_array($daysByNumber) || $daysByNumber === []) {
                continue;
            }
            ksort($daysByNumber);
            $daysIndexed = [];
            foreach (array_values($daysByNumber) as $i => $dayNode) {
                $daysIndexed[(string) $i] = is_array($dayNode) ? $dayNode : [];
            }
            $pkgOut = array_merge(
                is_array($entry['meta'] ?? null) ? $entry['meta'] : [],
                [
                'days' => $this->toIndexedObject($daysIndexed),
                ]
            );
            $packages[] = $pkgOut;
        }

        return $packages;
    }

    private function extractPackageMeta(array $packageNode): array
    {
        $meta = [];
        foreach (['package_id', 'packageId', 'total_days', 'totalDays'] as $field) {
            if (array_key_exists($field, $packageNode)) {
                $meta[$field] = $packageNode[$field];
            }
        }

        return $meta;
    }

    private function buildNamedPackageBlocks(array $packages): array
    {
        $named = [];
        foreach (array_values($packages) as $idx => $package) {
            $key = $idx === 0 ? 'packages' : 'packages ' . ($idx + 1);
            $named[$key] = [is_array($package) ? $package : []];
        }
        return $named;
    }

    private function collectMergedDaysWithCities(array $cities): array
    {
        $allDays = [];
        $citySummaries = [];

        foreach ($cities as $cityIndex => $cityNode) {
            if (!is_array($cityNode)) {
                continue;
            }

            $citySummaries[] = [
                'city' => (string) ($cityNode['city'] ?? ''),
                'city_checkin' => (string) ($cityNode['checkin_day'] ?? ''),
                'city_checkout' => (string) ($cityNode['checkout_day'] ?? ''),
            ];

            $packages = is_array($cityNode['packages'] ?? null) ? $cityNode['packages'] : [];
            foreach ($packages as $packageNode) {
                if (!is_array($packageNode)) {
                    continue;
                }
                $dayNodes = (array) ($packageNode['days'] ?? []);
                foreach ($dayNodes as $dayNode) {
                    if (!is_array($dayNode)) {
                        continue;
                    }
                    $allDays[] = $dayNode;
                }
            }
        }

        usort($allDays, fn ($a, $b) => (int) ($a['day'] ?? 0) <=> (int) ($b['day'] ?? 0));

        $normalized = [];
        foreach ($allDays as $idx => $dayNode) {
            $normalized[(string) $idx] = [
                'day' => (int) ($dayNode['day'] ?? ($idx + 1)),
                'cities' => $this->toIndexedObject($citySummaries),
                'hotels' => $this->normalizeDayHotelsWithNight(
                    is_array($dayNode['hotels'] ?? null) ? $dayNode['hotels'] : []
                ),
                'attractions' => is_array($dayNode['attractions'] ?? null) ? $dayNode['attractions'] : [],
                'arrivals'    => is_array($dayNode['arrivals'] ?? null) ? $dayNode['arrivals'] : [],
                'departures'  => is_array($dayNode['departures'] ?? null) ? $dayNode['departures'] : [],
                'transfers'   => is_array($dayNode['transfers'] ?? null) ? $dayNode['transfers'] : [],
                'restaurants' => is_array($dayNode['restaurants'] ?? null) ? $dayNode['restaurants'] : [],
                'Transfer'    => $this->extractDayTransfers($dayNode),
                'Guide'       => $this->extractDayGuides($dayNode),
                'services'    => is_array($dayNode['services'] ?? null) ? $dayNode['services'] : [],
            ];
        }

        return $normalized;
    }

    private function buildDmcWideServiceBuckets(array $days, array $destination): array
    {
        $hotels = [];
        $attractions = [];
        $restaurants = [];
        $guides = [];
        $zoneTransfers = [];

        $dmcId = (int) ($destination['DMC_id'] ?? 0);
        if ($dmcId > 0) {
            foreach (DayLevel::fetchZoneTransfersForDmc($dmcId) as $zoneRow) {
                if (! is_array($zoneRow)) {
                    continue;
                }
                $zoneRow['itinerary_day'] = 0;
                $zoneTransfers[] = $zoneRow;
            }
        }

        foreach ($days as $dayNode) {
            if (!is_array($dayNode)) {
                continue;
            }
            $hotels = array_merge($hotels, array_values((array) ($dayNode['hotels'] ?? [])));
            $attractions = array_merge($attractions, array_values((array) ($dayNode['attractions'] ?? [])));
            $restaurants = array_merge($restaurants, array_values((array) ($dayNode['restaurants'] ?? [])));
            $guides = array_merge($guides, array_values((array) ($dayNode['Guide'] ?? [])));
        }

        $itineraryTransfers = [];
        foreach ($days as $dayNode) {
            if (! is_array($dayNode)) {
                continue;
            }
            $dayNum = (int) ($dayNode['day'] ?? 0);
            $transferMap = $dayNode['Transfer'] ?? [];
            if (! is_array($transferMap)) {
                continue;
            }
            foreach ($transferMap as $leg) {
                if (! is_array($leg)) {
                    continue;
                }
                $leg['itinerary_day'] = $dayNum > 0 ? $dayNum : 0;
                $itineraryTransfers[] = $leg;
            }
        }

        $serviceMetaTransfers = [];
        $serviceMeta = $destination['service_meta'] ?? ($destination['services'] ?? []);
        if (is_array($serviceMeta)) {
            if (is_array($serviceMeta['guide'] ?? null)) {
                $guides[] = $serviceMeta['guide'];
            }
            if (is_array($serviceMeta['airport_transfer'] ?? null)) {
                $serviceMetaTransfers[] = array_merge(
                    ['type' => 'airport_transfer', 'itinerary_day' => 0],
                    $serviceMeta['airport_transfer']
                );
            }
            if (is_array($serviceMeta['departure_transfer'] ?? null)) {
                $serviceMetaTransfers[] = array_merge(
                    ['type' => 'departure_transfer', 'itinerary_day' => 0],
                    $serviceMeta['departure_transfer']
                );
            }
            if (is_array($serviceMeta['inter_city'] ?? null)) {
                foreach ($serviceMeta['inter_city'] as $interTransfer) {
                    if (is_array($interTransfer)) {
                        $serviceMetaTransfers[] = array_merge(
                            ['type' => 'inter_city', 'itinerary_day' => 0],
                            $interTransfer
                        );
                    }
                }
            }
        }

        $transferDedupe = [
            'type',
            'itinerary_day',
            'zone_id',
            'zone_name',
            'zone_type',
            'required',
            'transfer_type',
            'zone_from_id',
            'zone_to_id',
            'vehicle_id',
            'vehicle_name',
            'private_cost',
            'shared_cost',
            'city',
            'pickup_location',
            'drop_location',
        ];

        $transfers = array_merge($zoneTransfers, $itineraryTransfers, $serviceMetaTransfers);

        return [
            'list_all_services' => [
                'hotels' => $this->toNamedMap($this->uniqueByFields($hotels, ['hotel_id', 'hotel_name']), 'Hotel'),
                'attractions' => $this->toNamedMap($this->uniqueByFields($attractions, ['attraction_id', 'name']), 'Attraction'),
                'restaurants' => $this->toNamedMap($this->uniqueByFields($restaurants, ['restaurant_id', 'name']), 'Restaurant'),
                'guides' => $this->toNamedMap($this->uniqueByFields($guides, ['guide_id', 'guide_name', 'name']), 'Guide'),
                'transfers' => $this->toNamedMap($this->uniqueByFields($transfers, $transferDedupe), 'Transfer'),
            ],
            'list_all_transport' => [
                'zones' => $this->toNamedMap($this->uniqueByFields($zoneTransfers, $transferDedupe), 'Transfer'),
                'itinerary_transfers' => $this->toNamedMap($this->uniqueByFields($itineraryTransfers, $transferDedupe), 'Transfer'),
                'service_meta_transfers' => $this->toNamedMap($this->uniqueByFields($serviceMetaTransfers, $transferDedupe), 'Transfer'),
            ],
        ];
    }

    private function extractDayGuides(array $dayNode): array
    {
        $guides = [];
        foreach ((array) ($dayNode['services'] ?? []) as $service) {
            if (!is_array($service)) {
                continue;
            }
            if (strtolower((string) ($service['service_type'] ?? '')) !== 'guide') {
                continue;
            }
            $guides[] = [
                'guide_id' => (string) ($service['guide_id'] ?? ''),
                'guide_name' => (string) ($service['guide_name'] ?? ''),
                'guide_cost' => (float) ($service['guide_cost'] ?? 0),
                'city' => (string) ($service['city'] ?? ''),
            ];
        }

        return $this->toNamedMap($guides, 'Guide');
    }

    private function extractDayTransfers(array $dayNode): array
    {
        $transfers = [];

        foreach ((array) ($dayNode['hotels'] ?? []) as $hotel) {
            if (!is_array($hotel)) {
                continue;
            }
            if (
                ($hotel['transfer_city'] ?? '') === '' &&
                ($hotel['transfer_pickup'] ?? '') === '' &&
                ($hotel['transfer_drop'] ?? '') === ''
            ) {
                continue;
            }
            $transfers[] = [
                'type' => 'hotel_transfer',
                'city' => (string) ($hotel['transfer_city'] ?? ''),
                'pickup_location' => (string) ($hotel['transfer_pickup'] ?? ''),
                'drop_location' => (string) ($hotel['transfer_drop'] ?? ''),
            ];
        }

        foreach ((array) ($dayNode['attractions'] ?? []) as $attraction) {
            if (! is_array($attraction) || trim((string) ($attraction['attraction_id'] ?? '')) === '') {
                continue;
            }
            if (! is_array($attraction['transfer'] ?? null)) {
                continue;
            }
            $transfer = $attraction['transfer'];
            $transfers[] = [
                'type' => 'attraction_transfer',
                'booked_day' => (int) ($attraction['booked_day'] ?? $dayNode['day'] ?? 0),
                'required' => (string) ($transfer['required'] ?? ''),
                'transfer_type' => (string) ($transfer['transfer_type'] ?? ''),
                'city' => (string) ($transfer['city'] ?? ''),
                'pickup_location' => (string) ($transfer['pickup_location'] ?? ''),
                'drop_location' => (string) ($transfer['drop_location'] ?? ''),
            ];
            foreach ((array) ($transfer['additional_transfers'] ?? []) as $extra) {
                if (!is_array($extra)) {
                    continue;
                }
                if (($extra['pickup_location'] ?? '') === '' && ($extra['drop_location'] ?? '') === '') {
                    continue;
                }
                $transfers[] = [
                    'type' => 'attraction_transfer_additional',
                    'booked_day' => (int) ($attraction['booked_day'] ?? $dayNode['day'] ?? 0),
                    'city' => (string) ($extra['city'] ?? ''),
                    'pickup_location' => (string) ($extra['pickup_location'] ?? ''),
                    'drop_location' => (string) ($extra['drop_location'] ?? ''),
                ];
            }
        }

        foreach ([
            'arrivals' => 'arrival',
            'departures' => 'departure',
            'transfers' => 'attraction_transfer',
        ] as $bucket => $legType) {
            foreach ((array) ($dayNode[$bucket] ?? []) as $leg) {
                if (! is_array($leg) || ! is_array($leg['transfer'] ?? null)) {
                    continue;
                }
                $transfer = $leg['transfer'];
                $transfers[] = [
                    'type' => $legType,
                    'booked_day' => (int) ($leg['booked_day'] ?? $leg['day'] ?? $dayNode['day'] ?? 0),
                    'required' => (string) ($transfer['required'] ?? ''),
                    'transfer_type' => (string) ($transfer['transfer_type'] ?? $legType),
                    'city' => (string) ($transfer['city'] ?? $leg['city'] ?? ''),
                    'pickup_location' => (string) ($transfer['pickup_location'] ?? ''),
                    'drop_location' => (string) ($transfer['drop_location'] ?? ''),
                    'cost' => (float) ($transfer['cost'] ?? $transfer['transfer_price'] ?? $leg['total_price'] ?? 0),
                ];
            }
        }

        foreach ((array) ($dayNode['services'] ?? []) as $service) {
            if (!is_array($service) || !is_array($service['transfer'] ?? null)) {
                continue;
            }
            $transfer = $service['transfer'];
            $serviceType = strtolower((string) ($service['service_type'] ?? ''));
            $transfers[] = [
                'type' => $serviceType === 'restaurant_transfer' ? 'restaurant_transfer' : 'service_transfer',
                'booked_day' => (int) ($service['booked_day'] ?? $service['day'] ?? $dayNode['day'] ?? 0),
                'required' => (string) ($transfer['required'] ?? ''),
                'city' => (string) ($transfer['city'] ?? $service['city'] ?? ''),
                'pickup_location' => (string) ($transfer['pickup_location'] ?? ''),
                'drop_location' => (string) ($transfer['drop_location'] ?? ''),
                'vehicle_id' => (string) ($transfer['vehicle_id'] ?? ''),
                'vehicle_name' => (string) ($transfer['vehicle_name'] ?? ''),
                'cost' => (float) ($transfer['cost'] ?? 0),
            ];
        }

        return $this->toNamedMap($transfers, 'Transfer');
    }

    private function toIndexedObject(array $list): \stdClass
    {
        $obj = new \stdClass();
        foreach (array_values($list) as $index => $value) {
            $obj->{(string) $index} = $value;
        }
        return $obj;
    }

    private function toNamedMap(array $items, string $prefix): array
    {
        $mapped = [];
        foreach (array_values($items) as $index => $item) {
            $mapped[$prefix . ' ' . ($index + 1)] = is_array($item) ? $item : [];
        }
        return $mapped;
    }

    private function normalizeDayHotelsWithNight(array $hotels): array
    {
        $out = [];
        foreach ($hotels as $label => $hotel) {
            if (!is_array($hotel)) {
                $out[$label] = $hotel;
                continue;
            }
            $nights = max(1, (int) ($hotel['night'] ?? 1));
            $hotel['night'] = $nights;
            $perNight = (float) ($hotel['price_per_night'] ?? 0);
            if ($perNight <= 0) {
                $room = (float) ($hotel['room_price'] ?? 0);
                $meals = (float) ($hotel['breakfast_price'] ?? 0)
                    + (float) ($hotel['lunch_price'] ?? 0)
                    + (float) ($hotel['dinner_price'] ?? 0);
                $perNight = $room + $meals;
            }
            if ($perNight <= 0) {
                $storedPrice = (float) ($hotel['price'] ?? 0);
                $perNight = ($storedPrice > 0 && $nights > 1) ? ($storedPrice / $nights) : $storedPrice;
            }
            if ($perNight > 0) {
                $hotel['price_per_night'] = $perNight;
            }
            $total = (float) ($hotel['total_price'] ?? 0);
            if ($total <= 0 && $perNight > 0) {
                $total = $perNight * $nights;
            }
            if ($total > 0) {
                $hotel['total_price'] = $total;
                $hotel['price'] = $total;
            }
            if (!isset($hotel['checkin_day']) && isset($hotel['day'])) {
                $hotel['checkin_day'] = (int) $hotel['day'];
            }
            $checkin = max(1, (int) ($hotel['checkin_day'] ?? $hotel['day'] ?? 1));
            if (!isset($hotel['checkout_day'])) {
                $hotel['checkout_day'] = $checkin + $nights;
            }
            if (!isset($hotel['stay_days']) || !is_array($hotel['stay_days'])) {
                $stayDays = [];
                $checkout = max($checkin, (int) ($hotel['checkout_day'] ?? ($checkin + $nights)));
                for ($d = $checkin; $d <= $checkout; $d++) {
                    $stayDays[] = $d;
                }
                $hotel['stay_days'] = $stayDays;
            }
            $out[$label] = $hotel;
        }
        return $out;
    }

    private function uniqueByFields(array $rows, array $fields): array
    {
        $seen = [];
        $unique = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $keyParts = [];
            foreach ($fields as $field) {
                $keyParts[] = (string) ($row[$field] ?? '');
            }
            $key = implode('|', $keyParts);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $row;
        }
        return $unique;
    }

    private const DAY_LEVEL_JSON_CONTAINER = 'aiuploads';

    /**
     * Upload JSON to Azure via CommonHelper::json_path (aiuploads container).
     */
    private function storeDayLevelJsonOnAzure(string $jsonContent, string $fileName): ?string
    {
        $upload = CommonHelper::json_path(
            'file_storage',
            $jsonContent,
            $fileName,
            self::DAY_LEVEL_JSON_CONTAINER
        );

        return $upload['master_value'] ?? null;
    }

    private function getCombinedJsonUrl(): ?string
    {
        return CommonHelper::json_azure_url('day-level-combined.json', self::DAY_LEVEL_JSON_CONTAINER);
    }

    private function getMasterDmcJsonUrl(int $masterId): ?string
    {
        return CommonHelper::json_azure_url('master-dmc-' . $masterId . '.json', self::DAY_LEVEL_JSON_CONTAINER);
    }

    /**
     * Encode flat package rows for Azure AI Search / Blob Storage.
     * Must be a top-level JSON array (`[...]`) with no Laravel API wrapper.
     *
     * @param  list<array<string, mixed>>  $packages
     */
    private function encodeFlatPackagesForBlobStorage(array $packages): ?string
    {
        $json = json_encode(array_values($packages), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || $json === '' || $json[0] !== '[') {
            return null;
        }

        return $json;
    }

    /**
     * Rebuild combined + per-master JSON blobs after each create/update/delete.
     * Each uploaded file is only the raw package array (starts with `[`, ends with `]`).
     */
    private function refreshCombinedJsonFile(): void
    {
        try {
            $rows = DayLevel::query()
                ->with('dmc')
                ->whereNull('deleted_at')
                ->latest()
                ->get();

            $this->reconcileStoredMasterDmcIds($rows);

            $payload = $this->normalizeFlatExportMasterDmcIds(
                $this->buildFlatDayLevelPackagesPayload($rows)
            );
            $packageIds = array_values(array_filter(array_map(
                fn (array $entry) => trim((string) ($entry['package_id'] ?? $entry['id'] ?? '')),
                array_filter($payload, 'is_array')
            )));
            Log::info('Day-level Azure JSON refresh starting', [
                'day_level_rows' => $rows->count(),
                'package_count'  => count($payload),
                'package_ids'    => $packageIds,
            ]);

            $json = $this->encodeFlatPackagesForBlobStorage($payload);
            if ($json === null) {
                Log::warning('Day-level Azure JSON refresh skipped: payload could not be encoded as a JSON array.');

                return;
            }

            $combinedUrl = $this->storeDayLevelJsonOnAzure($json, 'day-level-combined.json');
            if ($combinedUrl === null) {
                Log::warning('Day-level combined JSON was not uploaded to Azure (check file_storage=azure and AZURE_AI_* credentials).');
            } else {
                Log::info('Day-level combined JSON synced to Azure', [
                    'url'           => $combinedUrl,
                    'package_count' => count($payload),
                    'package_ids'   => $packageIds,
                    'bytes'         => strlen($json),
                ]);
            }

            $masterIds = [];
            foreach ($payload as $entry) {
                if (! is_array($entry)) {
                    continue;
                }
                $masterId = (int) ($entry['Master_DMC_id'] ?? 0);
                if ($masterId > 0) {
                    $masterIds[$masterId] = true;
                }
            }

            foreach (array_keys($masterIds) as $masterId) {
                $masterPackages = array_values(array_filter(
                    $payload,
                    fn (array $entry) => (int) ($entry['Master_DMC_id'] ?? 0) === $masterId
                ));
                $masterJson = $this->encodeFlatPackagesForBlobStorage($masterPackages);
                if ($masterJson !== null) {
                    $masterUrl = $this->storeDayLevelJsonOnAzure($masterJson, 'master-dmc-' . $masterId . '.json');
                    if ($masterUrl !== null) {
                        Log::info('Day-level master DMC JSON synced to Azure', [
                            'master_dmc_id' => $masterId,
                            'url'           => $masterUrl,
                            'package_count' => count($masterPackages),
                            'package_ids'   => array_values(array_filter(array_map(
                                fn (array $entry) => trim((string) ($entry['package_id'] ?? $entry['id'] ?? '')),
                                $masterPackages
                            ))),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to refresh day-level JSON on Azure', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }
}





