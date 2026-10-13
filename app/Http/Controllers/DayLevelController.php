<?php

namespace App\Http\Controllers;

use App\Models\DayLevel;
use App\Models\City;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\Guide;
use App\Models\Vehicle;
use App\Models\Room;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Port;
use App\Models\DefaultValue;
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
    // Resolve DMC IDs from the logged-in user
    // users.role_id == 11  → DMC user  (userId = dmc_id, master_dmc_id from master_dmc_id col)
    // other roles          → staff under a DMC (created_by = dmc_id)
    // =========================================================================
    private function resolveDmcIds(): array
    {
        $user = Auth::user();

        // Master DMC role_id = 1 (super-admin / master)
        // DMC role_id = 11
        if ((int) $user->role_id === 11) {
            return [
                'dmc_id'        => (int) $user->userId,
                'master_dmc_id' => (int) ($user->master_dmc_id ?? $user->userId),
            ];
        }

        // Staff under a DMC
        return [
            'dmc_id'        => (int) ($user->created_by ?? $user->userId),
            'master_dmc_id' => (int) ($user->master_dmc_id ?? $user->created_by ?? $user->userId),
        ];
    }

    // =========================================================================
    // CREATE
    // =========================================================================
    public function create()
    {
        $user = Auth::user();
        $allowedRoleIds = [11,33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138, 37, 38];

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

        $dmcUsers = User::query()
            ->where('role_id', 11) // show only DMC users
            ->where(function ($q) use ($ids) {
                $q->where('userId', $ids['dmc_id'])
                    ->orWhere('master_dmc_id', $ids['master_dmc_id']);
            })
            ->orderBy('company_name')
            ->get($hasUserCountry ? ['userId', 'company_name', 'country'] : ['userId', 'company_name'])
            ->unique('userId')
            ->values();

        if ($dmcUsers->isEmpty()) {
            $dmcUsers = collect([
                (object) [
                    'userId'       => $ids['dmc_id'],
                    'company_name' => 'Current DMC',
                    'country'      => '',
                ],
            ]);
        }

        return view('dmc.index', [
            'cities'           => $cities,
            'countries'        => $countries,
            'hotelStarRatings' => self::HOTEL_STAR_RATINGS,
            'airportTypes'     => self::AIRPORT_TYPES,
            'interVehicles'    => self::VEHICLES_STATIC,
            'masterDmcId'      => $ids['master_dmc_id'],
            'defaultDmcId'     => $ids['dmc_id'],
            'dmcUsers'         => $dmcUsers,
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
            $hotels = $this->filterHotelsThatHaveRoomsForDmc($hotels, $dmcId);

            $result['hotels'] = $hotels
                ->groupBy('hotel_star_rating')
                ->map(fn($g) => $g->values())
                ->toArray();

            $result['hotels_flat'] = $hotels->values()->toArray();
        }

        // ── Attractions (DMC-mapped + must have tickets for this DMC) ─
        // attractions table has NO city column — use location only
        if (in_array($type, ['all', 'attractions'])) {
            $attractions = Attraction::whereNull('deleted_at')
                ->where('is_active', 1)
                ->where('location', 'ilike', "%{$cityName}%")
                ->orderBy('name');

            $this->applyServiceDmcFilter($attractionsQuery, $dmcId);
            $this->applyAttractionHasTicketsForDmcFilter($attractionsQuery, $dmcId);

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

        // ── Restaurants (DMC-mapped + must have meals for this DMC) ──
        if (in_array($type, ['all', 'restaurants'])) {
            $result['restaurants'] = Restaurant::whereNull('deleted_at')
                ->where('is_active', 1)
                ->where('city', 'ilike', "%{$cityName}%")
                ->orderBy('name');

            $this->applyServiceDmcFilter($restaurantsQuery, $dmcId);
            $this->applyRestaurantHasMealsForDmcFilter($restaurantsQuery, $dmcId);

            $result['restaurants'] = $restaurantsQuery
                ->get(['restaurant_id', 'name', 'city'])
                ->values()
                ->toArray();
        }

        // ── Guides (DMC-scoped) ─────────────────────────────────────
        if (in_array($type, ['all', 'guides'])) {
            $guidesQuery = Guide::whereNull('deleted_at')
                ->where('is_active', 1)
                ->where('city', 'ilike', "%{$cityName}%")
                ->orderBy('name');

            $this->applyGuideDmcFilter($guidesQuery, $dmcId);

            $result['guides'] = $guidesQuery
                ->get(['id', 'guide_id', 'name', 'city', 'day_rate'])
                ->values()
                ->toArray();
        }

        // ── Vehicles (for airport transfer) — DMC only, no city fallback ─
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
                ->orderBy('vehicle_name');

            $this->applyVehicleDmcFilter($vehicleQuery, $dmcId);

            $result['vehicles'] = $vehicleQuery->get([
                'id', 'vehicle_id', 'vehicle_name', 'vehicle_type',
                'seating_capacity', 'sharable', 'image',
                'base_price', 'cost_per_hour', 'city', 'dmc_id',
            ])->values()->toArray();
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
        $hotels = $this->filterHotelsThatHaveRoomsForDmc($hotels, $dmcId);

        return response()->json($hotels->values());
    }

    // =========================================================================
    // AJAX – available rooms by hotel (active room + at least one active bed)
    // GET /day-level/rooms-by-hotel?hotel_unique_id=H123&dmc_id=4
    // =========================================================================
    public function roomsByHotel(Request $request)
    {
        $dmcId = $this->resolveHotelDmcId($request);
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

        $bedColumns = ['bed_id', 'room_type', 'max_occupancy'];

        if ($dmcId > 0 && Schema::hasColumn('beds', 'dmc_id')) {
            $scoped = $baseBedQuery()->where('dmc_id', $dmcId)->orderBy('room_type')->get($bedColumns);
            if ($scoped->isNotEmpty()) {
                return response()->json(
                    $scoped->map(fn ($bed) => [
                        'bed_id' => $bed->bed_id,
                        'bed_type' => (string) ($bed->room_type ?? ''),
                        'max_occupancy' => (int) ($bed->max_occupancy ?? 0),
                    ])->values()
                );
            }
        }

        $beds = $baseBedQuery()->orderBy('room_type')->get($bedColumns);

        return response()->json(
            $beds->map(fn ($bed) => [
                'bed_id' => $bed->bed_id,
                'bed_type' => (string) ($bed->room_type ?? ''),
                'max_occupancy' => (int) ($bed->max_occupancy ?? 0),
            ])->values()
        );
    }

    // =========================================================================
    // AJAX – meal plans by hotel + dmc
    // GET /day-level/meal-plans-by-hotel?hotel_id=12&dmc_id=4
    // =========================================================================
    public function mealPlansByHotel(Request $request)
    {
        $hotelId = (int) $request->input('hotel_id', 0);
        $dmcId = (int) ($request->input('dmc_id') ?: $this->resolveDmcIds()['dmc_id']);

        if ($hotelId <= 0) {
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
     * Active rooms for a hotel, strictly scoped to the selected DMC.
     * No fallback to other DMCs' rooms — hotels without DMC rooms stay empty.
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

        if ($dmcId <= 0) {
            return $buildQuery()->get();
        }

        return $buildQuery()->where(function ($q) use ($dmcId) {
            $q->where('created_by', $dmcId)
                ->orWhere('dmc_id', $dmcId);
        })->get();
    }

    /**
     * Hotel unique ids that have at least one active room for this DMC.
     *
     * @return array<string, true>
     */
    private function hotelUniqueIdSetWithRoomsForDmc(int $dmcId): array
    {
        if ($dmcId <= 0) {
            return [];
        }

        static $cache = [];
        if (isset($cache[$dmcId])) {
            return $cache[$dmcId];
        }

        $ids = Room::query()
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->whereNotNull('room_type')
            ->where('room_type', '!=', '')
            ->where(function ($q) use ($dmcId) {
                $q->where('created_by', $dmcId)
                    ->orWhere('dmc_id', $dmcId);
            })
            ->distinct()
            ->pluck('hotel_id')
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $set = [];
        foreach ($ids as $id) {
            $set[$id] = true;
            if (ctype_digit($id)) {
                $set[(string) ((int) $id)] = true;
            }
        }

        return $cache[$dmcId] = $set;
    }

    /**
     * Keep only hotels that have bookable rooms for the selected DMC.
     *
     * @param  \Illuminate\Support\Collection<int, mixed>  $hotels
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    private function filterHotelsThatHaveRoomsForDmc($hotels, int $dmcId)
    {
        if ($dmcId <= 0) {
            return $hotels;
        }

        $roomHotelIds = $this->hotelUniqueIdSetWithRoomsForDmc($dmcId);
        if ($roomHotelIds === []) {
            return $hotels->filter(fn () => false)->values();
        }

        return $hotels->filter(function ($hotel) use ($roomHotelIds) {
            $uid = trim((string) (is_array($hotel)
                ? ($hotel['hotel_unique_id'] ?? '')
                : ($hotel->hotel_unique_id ?? '')));
            $listId = trim((string) (is_array($hotel)
                ? ($hotel['id'] ?? '')
                : ($hotel->id ?? '')));

            if ($uid !== '' && isset($roomHotelIds[$uid])) {
                return true;
            }
            if ($listId !== '' && isset($roomHotelIds[$listId])) {
                return true;
            }
            if ($uid !== '' && ctype_digit($uid) && isset($roomHotelIds[(string) ((int) $uid)])) {
                return true;
            }

            return false;
        })->values();
    }

    private function applyAttractionHasTicketsForDmcFilter($query, int $dmcId): void
    {
        if ($dmcId <= 0 || ! Schema::hasTable('tickets')) {
            return;
        }

        $query->whereIn('attraction_id', function ($sub) use ($dmcId) {
            $sub->select('attraction_id')
                ->from('tickets')
                ->where('dmc_id', $dmcId)
                ->whereNotNull('attraction_id');
            if (Schema::hasColumn('tickets', 'deleted_at')) {
                $sub->whereNull('deleted_at');
            }
        });
    }

    private function applyRestaurantHasMealsForDmcFilter($query, int $dmcId): void
    {
        if ($dmcId <= 0 || ! Schema::hasTable('meals')) {
            return;
        }

        $query->whereIn('restaurant_id', function ($sub) use ($dmcId) {
            $sub->select('restaurant_id')
                ->from('meals')
                ->where('dmc_id', $dmcId)
                ->whereNotNull('restaurant_id');
            if (Schema::hasColumn('meals', 'deleted_at')) {
                $sub->whereNull('deleted_at');
            }
        });
    }

    private function applyGuideDmcFilter($query, int $dmcId): void
    {
        if ($dmcId <= 0) {
            return;
        }

        $query->where(function ($q) use ($dmcId) {
            if (Schema::hasColumn('guides', 'dmc_id')) {
                $q->where('dmc_id', $dmcId)
                    ->orWhereRaw("CAST(dmc_id AS TEXT) = ?", [(string) $dmcId])
                    ->orWhereRaw("COALESCE(dmc_id::text, '') LIKE ?", ['%' . $dmcId . '%']);
            }
            if (Schema::hasColumn('guides', 'created_by')) {
                $q->orWhere('created_by', $dmcId);
            }
        });
    }

    private function applyVehicleDmcFilter($query, int $dmcId): void
    {
        if ($dmcId <= 0) {
            return;
        }

        $query->where(function ($q) use ($dmcId) {
            $q->where('dmc_id', $dmcId)
                ->orWhereRaw('CAST(dmc_id AS TEXT) = ?', [(string) $dmcId])
                ->orWhereRaw("COALESCE(dmc_id::text, '') LIKE ?", ['%' . $dmcId . '%']);
        });
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
        $dmcId = (int) ($request->input('dmc_id') ?: $this->resolveDmcIds()['dmc_id']);
        $cityName = trim((string) $request->input('city_name', ''));
        $likeOperator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $defaultPortRow = DefaultValue::query()
            ->where('dmc_id', $dmcId)
            ->where('name', 'port')
            ->where('status', 1)
            ->latest('id')
            ->first();

        $defaultPortId = (string) ($defaultPortRow->service_id ?? '');

        $ports = Port::query()
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->when($cityName !== '', function ($q) use ($cityName) {
                $q->whereHas('city', function ($cityQ) use ($cityName) {
                    $cityQ->where('name', 'like', "%{$cityName}%");
                });
            })
            ->orderBy('port_name')
            ->get(['port_id', 'port_name'])
            ->map(function ($p) use ($defaultPortId) {
                return [
                    'value' => 'port:' . (string) $p->port_id,
                    'label' => $p->port_name,
                    'type'  => 'port',
                    'id'    => (string) $p->port_id,
                    'is_default' => (string) $p->port_id === $defaultPortId,
                ];
            })
            ->values();

        $hotelQuery = Hotel::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->orderBy('name');
        if ($cityName !== '') {
            $hotelQuery->where('city', $likeOperator, "%{$cityName}%");
        }
        $this->applyHotelDmcFilter($hotelQuery, $dmcId);
        $hotelsForLocations = $this->filterHotelsThatHaveRoomsForDmc(
            $hotelQuery->get(['id', 'hotel_unique_id', 'name', 'city']),
            $dmcId
        );

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
            $this->applyAttractionHasTicketsForDmcFilter($attractionsQuery, $dmcId);
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
            $this->applyRestaurantHasMealsForDmcFilter($restaurantsQuery, $dmcId);
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
            'locations' => $ports->concat($hotelLocations)->values(),
            'default_pickup' => $defaultPickupValue,
            'default_drop_hotel' => $defaultHotelValue,
            'master_dmc_id' => $masterDmcId,
            'country' => $country !== '' ? $country : null,
        ]);
    }

    /**
     * Zone-respected transfer price for arrival/departure (and other leg transfers).
     * Uses this DMC's default vehicle + this DMC's zone assignments only (no cross-DMC fallback).
     */
    public function transferZonePrice(Request $request)
    {
        $dmcId = $this->resolveHotelDmcId($request);
        $pickupValue = trim((string) $request->input('pickup_value', ''));
        $dropValue = trim((string) $request->input('drop_value', ''));
        $transferType = strtolower(trim((string) $request->input('transfer_type', 'private')));

        $empty = [
            'success' => true,
            'zone_mapped' => false,
            'price' => 0,
            'private_price' => 0,
            'shared_price' => 0,
            'vehicle_id' => null,
            'vehicle_name' => null,
        ];

        if ($pickupValue === '' || $dropValue === '') {
            return response()->json(array_merge($empty, ['success' => false]));
        }

        $pickup = $this->parseTransferLocationValue($pickupValue);
        $drop = $this->parseTransferLocationValue($dropValue);

        $pickupType = $pickup['type'] === 'zone'
            ? $this->resolveZoneLocationApiType($pickup['id'], $dmcId)
            : $pickup['type'];
        $dropType = $drop['type'] === 'zone'
            ? $this->resolveZoneLocationApiType($drop['id'], $dmcId)
            : $drop['type'];

        $pickupId = $this->resolveServiceIdForZoneLookup($pickup['type'], $pickup['id'], $dmcId);
        $dropId = $this->resolveServiceIdForZoneLookup($drop['type'], $drop['id'], $dmcId);

        // Hotel / attraction / restaurant → only this DMC's assigned zone (no other-DMC fallback).
        // zone:* tokens must keep the zone id (do not re-resolve as hotel/attraction entity).
        $pickupZoneId = $pickup['type'] === 'zone'
            ? ($this->zoneBelongsToDmc((string) $pickup['id'], $dmcId) ? (string) $pickup['id'] : null)
            : $this->resolveDmcStrictZoneId($pickupType, (string) ($pickupId ?? ''), $dmcId);
        $dropZoneId = $drop['type'] === 'zone'
            ? ($this->zoneBelongsToDmc((string) $drop['id'], $dmcId) ? (string) $drop['id'] : null)
            : $this->resolveDmcStrictZoneId($dropType, (string) ($dropId ?? ''), $dmcId);

        if ($pickupZoneId === null || $dropZoneId === null || $pickupType === '' || $dropType === '') {
            return response()->json(array_merge($empty, [
                'message' => 'No DMC zone assignment for selected pickup/drop',
            ]));
        }

        $vehicleId = $this->resolveDefaultTransferVehicleId($dmcId);
        $vehicleName = $vehicleId ? $this->resolveTransferVehicleName($vehicleId, $dmcId) : null;
        if ($vehicleId === null || $vehicleName === null || !$this->vehicleBelongsToDmc($vehicleId, $dmcId)) {
            return response()->json(array_merge($empty, [
                'message' => 'No default transfer vehicle configured for this DMC',
            ]));
        }

        $zoneRequest = Request::create('/', 'GET', [
            'vehicle_id' => $vehicleId,
            // Pass DMC-strict zone IDs so getZonePrices does not fall back to other DMC assignments.
            'pickup_id' => $pickupZoneId,
            'drop_id' => $dropZoneId,
            'pickup_type' => $pickupType,
            'drop_type' => $dropType,
            'dmc_id' => $dmcId,
            'strict_dmc' => 1,
        ]);

        $zoneResponse = app(EnquiryFormPro::class)->getZonePrices($zoneRequest);
        $payload = json_decode($zoneResponse->getContent(), true) ?: [];
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        $private = (float) ($data['private_price'] ?? 0);
        $shared = (float) ($data['shared_price'] ?? 0);
        $hasMapping = !empty($data['mapping_id']) || !empty($data['mapping_row_id']);
        $zoneMapped = $hasMapping || $private > 0 || $shared > 0;

        if ($transferType === 'shared') {
            $price = $shared > 0 ? $shared : $private;
        } else {
            $price = $private > 0 ? $private : $shared;
        }

        return response()->json([
            'success' => true,
            'zone_mapped' => $zoneMapped && $price > 0,
            'price' => $price,
            'private_price' => $private,
            'shared_price' => $shared,
            'vehicle_id' => $vehicleId,
            'vehicle_name' => $vehicleName,
            'message' => $payload['message'] ?? null,
        ]);
    }

    /**
     * Resolve zone id strictly for this DMC (hotel/attraction/restaurant assignments only).
     * Port / already-zone tokens keep their id when they belong to the DMC.
     */
    private function resolveDmcStrictZoneId(string $type, string $serviceId, int $dmcId): ?string
    {
        $type = strtolower(trim($type));
        $serviceId = trim($serviceId);
        if ($type === '' || $serviceId === '' || $dmcId <= 0) {
            return null;
        }

        if ($type === 'port') {
            return $serviceId;
        }

        if ($type === 'zone') {
            return $this->zoneBelongsToDmc($serviceId, $dmcId) ? $serviceId : null;
        }

        if ($type === 'hotel') {
            $hotel = Hotel::query()
                ->whereNull('deleted_at')
                ->where(function ($q) use ($serviceId) {
                    $q->where('hotel_unique_id', $serviceId);
                    if (ctype_digit($serviceId)) {
                        $q->orWhere('id', (int) $serviceId);
                    }
                })
                ->first();
            $zoneId = $hotel?->getZoneForDmc($dmcId);

            return ($zoneId !== null && trim((string) $zoneId) !== '') ? (string) $zoneId : null;
        }

        if ($type === 'attraction') {
            $attraction = Attraction::query()
                ->whereNull('deleted_at')
                ->where(function ($q) use ($serviceId) {
                    $q->where('attraction_id', $serviceId);
                    if (ctype_digit($serviceId)) {
                        $q->orWhere('id', (int) $serviceId);
                    }
                })
                ->first();
            $zoneId = $attraction?->getZoneForDmc($dmcId);

            return ($zoneId !== null && trim((string) $zoneId) !== '') ? (string) $zoneId : null;
        }

        if ($type === 'restaurant') {
            $restaurant = Restaurant::query()
                ->whereNull('deleted_at')
                ->where(function ($q) use ($serviceId) {
                    $q->where('restaurant_id', $serviceId);
                    if (ctype_digit($serviceId)) {
                        $q->orWhere('id', (int) $serviceId);
                    }
                })
                ->first();
            $zoneId = $restaurant?->getZoneForDmc($dmcId);

            return ($zoneId !== null && trim((string) $zoneId) !== '') ? (string) $zoneId : null;
        }

        return null;
    }

    private function zoneBelongsToDmc(string $zoneId, int $dmcId): bool
    {
        if ($zoneId === '' || $dmcId <= 0) {
            return false;
        }

        return Zone::query()
            ->where('zone_id', $zoneId)
            ->where(function ($q) use ($dmcId) {
                $q->where('dmc_id', $dmcId)
                    ->orWhere('dmc_id', (string) $dmcId)
                    ->orWhereRaw('CAST(dmc_id AS TEXT) = ?', [(string) $dmcId]);
            })
            ->exists();
    }

    private function vehicleBelongsToDmc(string $vehicleId, int $dmcId): bool
    {
        $vehicleId = trim($vehicleId);
        if ($vehicleId === '' || $dmcId <= 0) {
            return false;
        }

        $query = Vehicle::query()
            ->whereNull('deleted_at')
            ->where(function ($q) use ($vehicleId) {
                $q->where('vehicle_id', $vehicleId);
                if (ctype_digit($vehicleId)) {
                    $q->orWhere('id', (int) $vehicleId);
                }
            });

        if (Schema::hasColumn('vehicles', 'dmc_id')) {
            $this->applyVehicleDmcFilter($query, $dmcId);
        }

        return $query->exists();
    }

    /** @return array{type: string, id: string} */
    private function parseTransferLocationValue(string $value): array
    {
        $value = trim($value);
        if ($value === '' || !str_contains($value, ':')) {
            return ['type' => '', 'id' => ''];
        }

        [$type, $id] = explode(':', $value, 2);

        return [
            'type' => strtolower(trim($type)),
            'id' => trim($id),
        ];
    }

    private function resolveServiceIdForZoneLookup(string $type, string $id, int $dmcId): ?string
    {
        $id = trim($id);
        if ($id === '' || $type === '') {
            return null;
        }

        if ($type === 'hotel') {
            return $this->resolveHotelUniqueIdForZone($id);
        }

        if ($type === 'zone') {
            return $id;
        }

        return $id;
    }

    private function resolveHotelUniqueIdForZone(string $tokenId): ?string
    {
        $hotel = Hotel::query()
            ->whereNull('deleted_at')
            ->where(function ($q) use ($tokenId) {
                $q->where('hotel_unique_id', $tokenId);
                if (ctype_digit($tokenId)) {
                    $q->orWhere('id', (int) $tokenId);
                }
            })
            ->first(['id', 'hotel_unique_id']);

        if ($hotel === null) {
            return null;
        }

        $uniqueId = trim((string) ($hotel->hotel_unique_id ?? ''));

        return $uniqueId !== '' ? $uniqueId : (string) $hotel->id;
    }

    private function resolveZoneLocationApiType(string $zoneId, int $dmcId): string
    {
        $zone = Zone::query()
            ->where('zone_id', $zoneId)
            ->where(function ($q) use ($dmcId) {
                $q->where('dmc_id', (string) $dmcId)
                    ->orWhereRaw("COALESCE(dmc_id::text, '') LIKE ?", ['%' . $dmcId . '%']);
            })
            ->first(['zone_type']);

        $zoneType = strtolower(trim((string) ($zone->zone_type ?? '')));

        return match ($zoneType) {
            'port' => 'port',
            'attraction' => 'attraction',
            'restaurant' => 'restaurant',
            default => 'hotel',
        };
    }

    private function resolveDefaultTransferVehicleId(int $dmcId, string $prefer = 'private'): ?string
    {
        if ($dmcId <= 0) {
            return null;
        }

        $name = $prefer === 'shared' ? 'car_shared' : 'car_private';
        $row = DefaultValue::query()
            ->where('dmc_id', $dmcId)
            ->where('name', $name)
            ->where('status', 1)
            ->latest('id')
            ->first();

        $vehicleId = trim((string) ($row->service_id ?? ''));
        // Only accept an active (non-deleted) vehicle for this DMC.
        if ($vehicleId !== '' && $this->vehicleBelongsToDmc($vehicleId, $dmcId)) {
            $canonical = $this->resolveCanonicalVehicleId($vehicleId, $dmcId);
            if ($canonical !== null) {
                return $canonical;
            }
        }

        if ($prefer !== 'shared') {
            $shared = $this->resolveDefaultTransferVehicleId($dmcId, 'shared');
            if ($shared !== null) {
                return $shared;
            }
        }

        // Fallback: first active vehicle for this DMC (default may point at a soft-deleted row).
        $fallback = Vehicle::query()
            ->whereNull('deleted_at')
            ->where(function ($q) use ($dmcId) {
                $q->where('dmc_id', $dmcId)
                    ->orWhere('dmc_id', (string) $dmcId)
                    ->orWhereRaw('CAST(dmc_id AS TEXT) = ?', [(string) $dmcId]);
            })
            ->orderBy('id')
            ->first(['vehicle_id', 'id']);

        if ($fallback) {
            $id = trim((string) ($fallback->vehicle_id ?? ''));
            return $id !== '' ? $id : (string) $fallback->id;
        }

        return null;
    }

    /** Canonical active vehicles.vehicle_id for a raw id / vehicle_id token. */
    private function resolveCanonicalVehicleId(string $vehicleId, int $dmcId): ?string
    {
        $vehicleId = trim($vehicleId);
        if ($vehicleId === '') {
            return null;
        }

        $query = Vehicle::query()
            ->whereNull('deleted_at')
            ->where(function ($q) use ($vehicleId) {
                $q->where('vehicle_id', $vehicleId);
                if (ctype_digit($vehicleId)) {
                    $q->orWhere('id', (int) $vehicleId);
                }
            });

        if ($dmcId > 0 && Schema::hasColumn('vehicles', 'dmc_id')) {
            $this->applyVehicleDmcFilter($query, $dmcId);
        }

        $vehicle = $query->first(['vehicle_id', 'id']);
        if (! $vehicle) {
            return null;
        }

        $canonical = trim((string) ($vehicle->vehicle_id ?? ''));

        return $canonical !== '' ? $canonical : (string) $vehicle->id;
    }

    private function resolveTransferVehicleName(string $vehicleId, int $dmcId): ?string
    {
        $vehicleId = trim($vehicleId);
        if ($vehicleId === '') {
            return null;
        }

        // Prefer active vehicle; fall back to soft-deleted so historical IDs still show a name.
        $query = Vehicle::withTrashed()
            ->where(function ($q) use ($vehicleId) {
                $q->where('vehicle_id', $vehicleId);
                if (ctype_digit($vehicleId)) {
                    $q->orWhere('id', (int) $vehicleId);
                }
            })
            ->orderByRaw('CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END');

        if ($dmcId > 0 && Schema::hasColumn('vehicles', 'dmc_id')) {
            $query->where(function ($q) use ($dmcId) {
                $q->where('dmc_id', $dmcId)
                    ->orWhere('dmc_id', (string) $dmcId)
                    ->orWhereRaw("COALESCE(dmc_id::text, '') LIKE ?", ['%' . $dmcId . '%']);
            });
        }

        $vehicle = $query->first(['vehicle_name', 'vehicle_type', 'vehicle_id']);
        if ($vehicle === null) {
            return null;
        }

        $name = trim((string) ($vehicle->vehicle_name ?? ''));
        $type = trim((string) ($vehicle->vehicle_type ?? ''));
        if ($name === '') {
            return $type !== '' ? $type : null;
        }

        if ($type !== '' && strcasecmp($name, $type) !== 0) {
            return $name . ' (' . $type . ')';
        }

        return $name;
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

        $attraction = Attraction::query()
            ->where('attraction_id', $attractionId)
            ->whereJsonContains('dmc_id', $dmcId)
            ->first();

        if (!$attraction) {
            return response()->json(['tickets' => []]);
        }

        $tickets = Ticket::query()
            ->where('attraction_id', $attractionId)
            ->where('dmc_id', $dmcId)
            ->select('ticket_id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['tickets' => $tickets]);
    }

    private function getExistingRoomMealColumns(): array
    {
        $base = ['room_id', 'room_type', 'rooms_only'];
        $optional = [
            'breakfast',
            'lunch',
            'dinner',
            'breakfast_included',
            'lunch_included',
            'dinner_included',
            'breakfast_price',
            'lunch_price',
            'dinner_price',
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

    private function buildMealPlanOptionsFromRooms(array $rooms): array
    {
        $set = [];
        $bestPrices = [
            'breakfast_price' => 0.0,
            'lunch_price' => 0.0,
            'dinner_price' => 0.0,
        ];

        foreach ($rooms as $room) {
            $room = is_array($room) ? $room : (array) $room;
            $roomText = 'room';

            $breakfastPrice = (float) ($room['breakfast_price'] ?? 0);
            $lunchPrice = (float) ($room['lunch_price'] ?? 0);
            $dinnerPrice = (float) ($room['dinner_price'] ?? 0);
            $bestPrices['breakfast_price'] = max($bestPrices['breakfast_price'], $breakfastPrice);
            $bestPrices['lunch_price'] = max($bestPrices['lunch_price'], $lunchPrice);
            $bestPrices['dinner_price'] = max($bestPrices['dinner_price'], $dinnerPrice);

            $hasBreakfast = $this->isTruthyMealFlag($room['breakfast'] ?? null)
                || $this->isTruthyMealFlag($room['breakfast_included'] ?? null)
                || $breakfastPrice > 0;
            $hasLunch = $this->isTruthyMealFlag($room['lunch'] ?? null)
                || $this->isTruthyMealFlag($room['lunch_included'] ?? null)
                || $lunchPrice > 0;
            $hasDinner = $this->isTruthyMealFlag($room['dinner'] ?? null)
                || $this->isTruthyMealFlag($room['dinner_included'] ?? null)
                || $dinnerPrice > 0;

            $set[$roomText . ' only'] = true;
            if ($hasBreakfast) {
                $set[$roomText . ' with breakfast'] = true;
            }
            if ($hasLunch) {
                $set[$roomText . ' with lunch'] = true;
            }
            if ($hasDinner) {
                $set[$roomText . ' with dinner'] = true;
            }
            if ($hasBreakfast && $hasLunch) {
                $set[$roomText . ' with breakfast + lunch'] = true;
            }
            if ($hasBreakfast && $hasDinner) {
                $set[$roomText . ' with breakfast + dinner'] = true;
            }
            if ($hasLunch && $hasDinner) {
                $set[$roomText . ' with lunch + dinner'] = true;
            }
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

        return array_map(function ($plan) use ($bestPrices) {
            $key = strtolower((string) $plan);
            $breakfast = str_contains($key, 'breakfast') ? (float) $bestPrices['breakfast_price'] : 0.0;
            $lunch = str_contains($key, 'lunch') ? (float) $bestPrices['lunch_price'] : 0.0;
            $dinner = str_contains($key, 'dinner') ? (float) $bestPrices['dinner_price'] : 0.0;
            $mealTotal = $breakfast + $lunch + $dinner;

            $label = (string) $plan;
            if ($mealTotal > 0) {
                $parts = [];
                if ($breakfast > 0) {
                    $parts[] = 'B ' . number_format($breakfast, 2, '.', '');
                }
                if ($lunch > 0) {
                    $parts[] = 'L ' . number_format($lunch, 2, '.', '');
                }
                if ($dinner > 0) {
                    $parts[] = 'D ' . number_format($dinner, 2, '.', '');
                }
                if ($parts !== []) {
                    $label .= ' — ' . implode(' + ', $parts);
                }
            }

            return [
                'value' => (string) $plan,
                'label' => $label,
                'breakfast_price' => $breakfast,
                'lunch_price' => $lunch,
                'dinner_price' => $dinner,
                'includes_breakfast' => str_contains($key, 'breakfast'),
                'includes_lunch' => str_contains($key, 'lunch'),
                'includes_dinner' => str_contains($key, 'dinner'),
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
        $allowedRoleIds = [11, 33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138, 37, 38];

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

    public function updateInclusion(Request $request, DayLevel $dayLevel)
    {
        $user = Auth::user();
        $allowedRoleIds = [11, 33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138, 37, 38];

        if (! in_array((int) $user->role_id, $allowedRoleIds, true)) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to update inclusion.'], 403);
        }

        if (! $this->userCanAccessDayLevel($dayLevel)) {
            return response()->json(['success' => false, 'message' => 'This package is not available for your account.'], 403);
        }

        $validated = $request->validate([
            'is_inclusion' => ['required', 'boolean'],
        ]);

        $isInclusion = filter_var($validated['is_inclusion'], FILTER_VALIDATE_BOOLEAN);
        $dayLevel->update(['is_inclusion' => $isInclusion]);

        try {
            $this->refreshCombinedJsonFile();
        } catch (\Throwable $e) {
            Log::warning('Day-level inclusion updated in DB but Azure JSON refresh failed', [
                'day_level_id' => $dayLevel->id,
                'is_inclusion' => $isInclusion ? 1 : 0,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $isInclusion ? 'Package marked as inclusion.' : 'Inclusion removed from package.',
            'is_inclusion' => (bool) $dayLevel->is_inclusion,
        ]);
    }

    private function userCanAccessDayLevel(DayLevel $dayLevel): bool
    {
        $ids = $this->resolveDmcIds();

        return (int) $dayLevel->dmc_id === (int) $ids['dmc_id']
            || (int) $dayLevel->master_dmc_id === (int) $ids['master_dmc_id'];
    }

    // =========================================================================
    // API – combined JSON output for all/specific DMC rows
    // GET /api/v1/day-level/combined-json?master_dmc_id=3&dmc_id=4
    // =========================================================================
    public function combinedJsonApi(Request $request)
    {
        $query = DayLevel::query()
            ->whereNull('deleted_at')
            ->latest();

        if ($request->filled('master_dmc_id')) {
            $query->where('master_dmc_id', (int) $request->query('master_dmc_id'));
        }

        if ($request->filled('dmc_id')) {
            $query->where('dmc_id', (int) $request->query('dmc_id'));
        }

        $rows = $query->get();
        $payload = $this->buildCombinedStructuredPayload($rows);

        // Keep on-disk file synchronized for no-filter requests.
        if (!($request->filled('master_dmc_id') || $request->filled('dmc_id'))) {
            $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if ($json !== false) {
                File::put($this->getCombinedJsonFilePath(), $json);
            }
        }

        return response()->json([
            'success' => true,
            'filters' => [
                'master_dmc_id' => $request->query('master_dmc_id'),
                'dmc_id' => $request->query('dmc_id'),
            ],
            'total_rows' => $rows->count(),
            'json_file_url' => url('/day-level-combined.json'),
            'data' => $payload,
        ]);
    }

    // =========================================================================
    // WEB – serve combined JSON file
    // GET /day-level/day-level-combined.json
    // =========================================================================
    public function combinedJsonFile()
    {
        // Always regenerate before serving to avoid stale/legacy format.
        $this->refreshCombinedJsonFile();
        $filePath = $this->getCombinedJsonFilePath();

        if (!File::exists($filePath)) {
            return response()->json(['Master_DMC' => []]);
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    // =========================================================================
    // SHOW
    // =========================================================================
    public function show(DayLevel $dayLevel)
    {
        $dayLevel->load('city', 'guide', 'vehicle');

        $hotelIds     = collect($dayLevel->hotels ?? [])->pluck('hotel_id')->filter()->unique();
        $hotelMap     = Hotel::whereIn('id', $hotelIds)->pluck('name', 'id');

        $attrIds      = collect($dayLevel->activities ?? [])
            ->where('type', 'attraction')->pluck('detail_id')->filter()->unique();
        $attrMap      = Attraction::whereIn('attraction_id', $attrIds)->pluck('name', 'attraction_id');

        $interCityIds = collect($dayLevel->inter_city ?? [])->pluck('city_id')->filter()->unique();
        $interCityMap = City::whereIn('id', $interCityIds)->pluck('name', 'id');

        return view('day-level.show', compact(
            'dayLevel', 'hotelMap', 'attrMap', 'interCityMap'
        ));
    }

    // =========================================================================
    // EDIT
    // =========================================================================
    public function edit(DayLevel $dayLevel)
    {
        $user = Auth::user();
        $allowedRoleIds = [11, 33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138, 37, 38];

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

        $dmcUsers = User::query()
            ->where('role_id', 11)
            ->where(function ($q) use ($ids) {
                $q->where('userId', $ids['dmc_id'])
                    ->orWhere('master_dmc_id', $ids['master_dmc_id']);
            })
            ->orderBy('company_name')
            ->get($hasUserCountry ? ['userId', 'company_name', 'country'] : ['userId', 'company_name'])
            ->unique('userId')
            ->values();

        if ($dmcUsers->isEmpty()) {
            $dmcUsers = collect([
                (object) [
                    'userId'       => $ids['dmc_id'],
                    'company_name' => 'Current DMC',
                    'country'      => '',
                ],
            ]);
        }

        return view('dmc.index', [
            'dayLevel'         => $dayLevel,
            'isEditMode'       => true,
            'cities'           => $cities,
            'countries'        => $countries,
            'hotelStarRatings' => self::HOTEL_STAR_RATINGS,
            'airportTypes'     => self::AIRPORT_TYPES,
            'interVehicles'    => self::VEHICLES_STATIC,
            'masterDmcId'      => (int) ($dayLevel->master_dmc_id ?: $ids['master_dmc_id']),
            'defaultDmcId'     => (int) ($dayLevel->dmc_id ?: $ids['dmc_id']),
            'dmcUsers'         => $dmcUsers,
        ]);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================
    public function update(Request $request, DayLevel $dayLevel)
    {
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

                $incomingMasterId = (int) ($masterNode['Master_DMC_id'] ?? $dayLevel->master_dmc_id);
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
                $selectedDestinations = [$selectedDestination];

                $meta = $this->computeDayLevelMetadataFromDestinations($selectedDestinations);
                $services = $this->extractTransferServicesFromDestinations($selectedDestinations);
                $country = (string) ($selectedDestination['country'] ?? $meta['country'] ?? '');
                $country = $country !== '' ? $country : null;

                $firstCityName = (string) ($meta['first_city_name'] ?? '');
                $cityId = null;
                if ($firstCityName !== '') {
                    $cityQuery = City::whereNull('deleted_at')->where('name', 'ilike', $firstCityName);
                    if (!blank($country)) {
                        $cityQuery->where('country', 'ilike', (string) $country);
                    }
                    $cityId = $cityQuery->value('id');
                }

                $dayLevel->update([
                    'master_dmc_id'         => $incomingMasterId,
                    'dmc_id'                => (int) ($selectedDestination['DMC_id'] ?? $dayLevel->dmc_id),
                    'city_id'               => $cityId,
                    'country'               => $country,
                    'days'                  => max(1, (int) ($meta['max_day_count'] ?? 1)),
                    'hotels'                => $meta['hotels_flat'] ?? [],
                    'airport_transfer_type' => $services['airport_transfer']['type'] ?: null,
                    'airport_transfer_cost' => $services['airport_transfer']['cost'],
                    'vehicle_id'            => $services['airport_transfer']['vehicle_id'],
                    'vehicle_service_type'  => $services['airport_transfer']['vehicle_service_type'] ?: null,
                    'vehicle_passengers'    => $services['airport_transfer']['vehicle_passengers'],
                    'activities'            => $selectedDestinations,
                    'inter_city'            => [
                        'Master_DMC_id' => $incomingMasterId,
                        'destinations'  => $selectedDestinations,
                    ],
                ]);

                DB::commit();
                $this->refreshCombinedJsonFile();

                return redirect()
                    ->route('day-level.show', $dayLevel)
                    ->with('success', 'Day Level updated successfully.');
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
    // DESTROY – soft-delete day level row + rebuild Azure JSON without it
    // =========================================================================
    public function destroy(DayLevel $dayLevel)
    {
        $user = Auth::user();
        $allowedRoleIds = [11, 33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138, 37, 38];

        if (! in_array((int) $user->role_id, $allowedRoleIds, true)) {
            if ($this->wantsJsonResponse()) {
                return response()->json(['success' => false, 'message' => 'You do not have permission to delete this package.'], 403);
            }

            return redirect()->route('dashboard')->with('error', 'You have not permission for access this page');
        }

        if (! $this->userCanAccessDayLevel($dayLevel)) {
            if ($this->wantsJsonResponse()) {
                return response()->json(['success' => false, 'message' => 'This package is not available for your account.'], 403);
            }

            return redirect()->route('day-level.index')->with('error', 'This package is not available for your account.');
        }

        $dayLevelId = (int) $dayLevel->id;
        $affectedMasterId = (int) ($dayLevel->master_dmc_id ?? 0);
        $deletedPackageIds = [];
        foreach ($dayLevel->collectPackageSummaries() as $summary) {
            $pid = trim((string) ($summary['package_id'] ?? ''));
            if ($pid !== '' && ! empty($summary['has_stable_id'])) {
                $deletedPackageIds[] = $pid;
            }
        }

        $dayLevel->delete();

        $azureSync = ['ok' => false, 'deleted_blobs' => [], 'search' => []];
        try {
            $azureSync = $this->refreshCombinedJsonFile(
                $affectedMasterId > 0 ? [$affectedMasterId] : [],
                $deletedPackageIds
            );
        } catch (\Throwable $e) {
            Log::warning('Day-level soft-deleted in DB but Azure JSON refresh failed', [
                'day_level_id' => $dayLevelId,
                'error' => $e->getMessage(),
            ]);
        }

        if ($this->wantsJsonResponse()) {
            return response()->json([
                'success' => true,
                'message' => ! empty($azureSync['ok'])
                    ? 'Day Level deleted from DB, blob storage, and Azure AI Search (indexer reset).'
                    : 'Day Level soft-deleted. Azure blob/search sync may have failed — check logs.',
                'deleted_day_level_id' => $dayLevelId,
                'deleted_package_ids' => $deletedPackageIds,
                'azure_sync' => $azureSync,
            ]);
        }

        return redirect()->route('day-level.index')->with(
            ! empty($azureSync['ok']) ? 'success' : 'warning',
            ! empty($azureSync['ok'])
                ? 'Day Level deleted from blob storage and Azure AI Search (indexer reset).'
                : 'Day Level soft-deleted. Azure blob/search sync may have failed — check logs.'
        );
    }

    /**
     * Soft-remove one package from a Day Level row.
     * If no packages remain, soft-deletes the whole row. Always rebuilds Azure JSON.
     */
    public function destroyPackage(Request $request, DayLevel $dayLevel, string $packageId)
    {
        $user = Auth::user();
        $allowedRoleIds = [11, 33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138, 37, 38];

        if (! in_array((int) $user->role_id, $allowedRoleIds, true)) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to delete this package.'], 403);
        }

        if (! $this->userCanAccessDayLevel($dayLevel)) {
            return response()->json(['success' => false, 'message' => 'This package is not available for your account.'], 403);
        }

        $packageId = trim($packageId);
        if ($packageId === '') {
            return response()->json(['success' => false, 'message' => 'Package id is required.'], 422);
        }

        $summaries = $dayLevel->collectPackageSummaries();
        $matched = collect($summaries)->first(function ($summary) use ($packageId) {
            return (string) ($summary['package_id'] ?? '') === $packageId
                && ! empty($summary['has_stable_id']);
        });
        if (! $matched) {
            return response()->json(['success' => false, 'message' => 'Package not found on this Day Level row.'], 404);
        }

        $previousDestinations = $this->extractDestinationsFromStoredDayLevel($dayLevel);
        $remainingDestinations = DayLevel::removePackageFromDestinations($previousDestinations, $packageId);
        $remainingDestinations = DayLevel::canonicalizeDestinationsForStorage($remainingDestinations);

        $dayLevelId = (int) $dayLevel->id;
        $affectedMasterIds = array_values(array_unique(array_filter([
            (int) ($dayLevel->master_dmc_id ?? 0),
        ])));
        $rowDeleted = false;

        try {
            DB::beginTransaction();

            if ($remainingDestinations === []) {
                $dayLevel->delete();
                $rowDeleted = true;
            } else {
                $meta = $this->computeDayLevelMetadataFromDestinations($remainingDestinations);
                $services = $this->extractTransferServicesFromDestinations($remainingDestinations);
                $resolvedDmcId = (int) ($remainingDestinations[0]['DMC_id'] ?? $dayLevel->dmc_id);
                $incomingMasterId = $this->resolveMasterDmcIdForDmcUserId($resolvedDmcId);
                if ($incomingMasterId <= 0) {
                    $incomingMasterId = (int) $dayLevel->master_dmc_id;
                }
                if ($incomingMasterId > 0) {
                    $affectedMasterIds[] = $incomingMasterId;
                }
                $country = (string) ($remainingDestinations[0]['country'] ?? $meta['country'] ?? '');
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

                $rowDays = max(1, (int) ($meta['max_day_count'] ?? 1));

                $dayLevel->update([
                    'master_dmc_id' => $incomingMasterId,
                    'dmc_id' => $resolvedDmcId,
                    'city_id' => $cityId,
                    'country' => $country,
                    'days' => $rowDays,
                    'hotels' => $meta['hotels_flat'] ?? [],
                    'airport_transfer_type' => $services['airport_transfer']['type'] ?: null,
                    'airport_transfer_cost' => $services['airport_transfer']['cost'],
                    'vehicle_id' => $services['airport_transfer']['vehicle_id'],
                    'vehicle_service_type' => $services['airport_transfer']['vehicle_service_type'] ?: null,
                    'vehicle_passengers' => $services['airport_transfer']['vehicle_passengers'],
                    'activities' => $remainingDestinations,
                    'inter_city' => $this->buildPersistedInterCityPayload($incomingMasterId, $remainingDestinations),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('DayLevel package delete failed: ' . $e->getMessage(), [
                'day_level_id' => $dayLevelId,
                'package_id' => $packageId,
            ]);

            return response()->json(['success' => false, 'message' => 'Could not delete package. Please try again.'], 500);
        }

        $azureSync = ['ok' => false, 'deleted_blobs' => [], 'search' => []];
        try {
            $azureSync = $this->refreshCombinedJsonFile(
                array_values(array_unique(array_filter($affectedMasterIds))),
                [$packageId]
            );
        } catch (\Throwable $e) {
            Log::warning('Day-level package deleted in DB but Azure JSON refresh failed', [
                'day_level_id' => $dayLevelId,
                'package_id' => $packageId,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => ! empty($azureSync['ok'])
                ? ($rowDeleted
                    ? 'Package deleted from blob storage and Azure AI Search. Day Level row soft-deleted.'
                    : 'Package deleted from blob storage and Azure AI Search (indexer reset).')
                : ($rowDeleted
                    ? 'Package deleted and Day Level soft-deleted. Azure blob/search sync may have failed — check logs.'
                    : 'Package deleted. Azure blob/search sync may have failed — check logs.'),
            'deleted_package_id' => $packageId,
            'row_deleted' => $rowDeleted,
            'azure_sync' => $azureSync,
        ]);
    }

    private function wantsJsonResponse(): bool
    {
        return request()->expectsJson()
            || request()->ajax()
            || str_contains((string) request()->header('Accept', ''), 'application/json');
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function decodeAndValidateHotels(?string $json): array
    {
        if (blank($json)) return [];
        $rows = json_decode($json, true);
        if (!is_array($rows)) return [];

        $validHotelIds = Hotel::whereNull('deleted_at')->pluck('id')->flip();
        $catBuckets    = [];
        $catCount      = [];

        foreach ($rows as $row) {
            $cat      = (string) ($row['cv'] ?? $row['cat'] ?? '');
            $hotelId  = (int) ($row['hotel_id'] ?? 0);
            $priority = (int) ($row['pri'] ?? 1);

            if (!array_key_exists($cat, self::HOTEL_STAR_RATINGS)) continue;
            if (!isset($validHotelIds[$hotelId])) continue;

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
            $masterId = (int) ($masterNode['Master_DMC_id'] ?? 0);
            if ($masterId <= 0) {
                continue;
            }

            foreach (($masterNode['destinations'] ?? []) as $destinationNode) {
                $destination = $this->unwrapDestinationNode($destinationNode);
                if (!is_array($destination)) {
                    continue;
                }
                $dmcId = (int) ($destination['DMC_id'] ?? 0);
                if ($dmcId <= 0) {
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
                    'inter_city' => [
                        'Master_DMC_id' => $masterId,
                        'destinations'  => $mergedDestinations,
                    ],
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
        if (is_array($ic) && isset($ic['destinations']) && is_array($ic['destinations'])) {
            return $ic['destinations'];
        }

        $act = $row->activities;
        if (is_array($act) && $act !== [] && $this->looksLikeDestinationsList($act)) {
            return $act;
        }

        return [];
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
            // Merge city metadata and keep both old + new packages for the same DMC/city.
            // This allows creating another package under the same DMC without losing previous ones.
            $prev = $cities[$cIdx];
            $mergedCity = array_merge($prev, $cIn);
            if (array_key_exists('packages', $cIn) && is_array($cIn['packages'])) {
                $prevPackages = is_array($prev['packages'] ?? null) ? $prev['packages'] : [];
                $incomingPackages = $cIn['packages'];
                $dedupedPackages = [];
                $seenPackageHashes = [];

                foreach (array_merge($prevPackages, $incomingPackages) as $pkg) {
                    if (!is_array($pkg)) {
                        continue;
                    }
                    $hash = md5(json_encode($pkg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
                    if ($hash === '' || isset($seenPackageHashes[$hash])) {
                        continue;
                    }
                    $seenPackageHashes[$hash] = true;
                    $dedupedPackages[] = $pkg;
                }

                $mergedCity['packages'] = $dedupedPackages;
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

        return $out;
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
                    $countryKey = $this->normalizeLocationKey($destination['country'] ?? '');
                    $destKey = $dmcId . '|' . $countryKey;

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

        foreach ($masters as $masterId => &$masterRef) {
            $this->appendMissingMasterChildDmcs($masterRef, (int) $masterId);
        }
        unset($masterRef);

        $masterList = array_values(array_map(function ($master) {
            $master['destinations'] = array_values(array_map(function ($destination) {
                return [
                    'DMC' => [
                        $this->transformDestinationForExternalPayload(
                            is_array($destination) ? $destination : []
                        ),
                    ],
                ];
            }, $master['destinations']));
            return $master;
        }, $masters));

        usort($masterList, fn($a, $b) => ((int) $a['Master_DMC_id']) <=> ((int) $b['Master_DMC_id']));

        return ['Master_DMC' => $masterList];
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

    private function transformDestinationForExternalPayload(array $destination): array
    {
        $cities = is_array($destination['cities'] ?? null) ? $destination['cities'] : [];
        $days = $this->collectMergedDaysWithCities($cities);
        $packages = $this->collectPackagesWithCities($cities);
        $serviceAndTransportBuckets = $this->buildDmcWideServiceBuckets($days, $destination);
        $normalizedPackages = $packages !== [] ? $packages : [[
            'days' => $this->toIndexedObject($days),
        ]];

        $out = array_merge([
            'DMC_id' => (int) ($destination['DMC_id'] ?? 0),
            'country' => (string) ($destination['country'] ?? ''),
            'list_all_services' => $serviceAndTransportBuckets['list_all_services'],
            'list_all_transport' => $serviceAndTransportBuckets['list_all_transport'],
            'packages' => $normalizedPackages,
        ], $this->buildNamedPackageBlocks($normalizedPackages));

        $company = trim((string) ($destination['company_name'] ?? ''));
        if ($company !== '') {
            $out['DMC_company_name'] = $company;
        }

        return $out;
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

        $packages = [];
        foreach ($cities as $cityNode) {
            if (!is_array($cityNode)) {
                continue;
            }
            foreach ((array) ($cityNode['packages'] ?? []) as $packageNode) {
                if (!is_array($packageNode)) {
                    continue;
                }
                $dayNodes = (array) ($packageNode['days'] ?? []);
                $normalizedDays = [];
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
                    $normalizedDay['restaurants'] = is_array($normalizedDay['restaurants'] ?? null) ? $normalizedDay['restaurants'] : [];
                    $normalizedDay['Transfer'] = is_array($normalizedDay['Transfer'] ?? null)
                        ? $normalizedDay['Transfer']
                        : $this->extractDayTransfers($normalizedDay);
                    $normalizedDay['Guide'] = is_array($normalizedDay['Guide'] ?? null)
                        ? $normalizedDay['Guide']
                        : $this->extractDayGuides($normalizedDay);
                    $normalizedDay['services'] = is_array($normalizedDay['services'] ?? null) ? $normalizedDay['services'] : [];
                    $normalizedDays[(string) $dayIdx] = $normalizedDay;
                }
                if ($normalizedDays !== []) {
                    $packages[] = [
                        'days' => $this->toIndexedObject($normalizedDays),
                    ];
                }
            }
        }

        return $packages;
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
                'restaurants' => is_array($dayNode['restaurants'] ?? null) ? $dayNode['restaurants'] : [],
                'Transfer' => $this->extractDayTransfers($dayNode),
                'Guide' => $this->extractDayGuides($dayNode),
                'services' => is_array($dayNode['services'] ?? null) ? $dayNode['services'] : [],
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
            if (!is_array($attraction) || !is_array($attraction['transfer'] ?? null)) {
                continue;
            }
            $transfer = $attraction['transfer'];
            $transfers[] = [
                'type' => 'attraction_transfer',
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
                    'city' => (string) ($extra['city'] ?? ''),
                    'pickup_location' => (string) ($extra['pickup_location'] ?? ''),
                    'drop_location' => (string) ($extra['drop_location'] ?? ''),
                ];
            }
        }

        foreach ((array) ($dayNode['services'] ?? []) as $service) {
            if (!is_array($service) || !is_array($service['transfer'] ?? null)) {
                continue;
            }
            $transfer = $service['transfer'];
            $transfers[] = [
                'type' => 'service_transfer',
                'required' => (string) ($transfer['required'] ?? ''),
                'city' => (string) ($transfer['city'] ?? ''),
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
            $hotel['night'] = (int) ($hotel['night'] ?? 1);
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

    private function getCombinedJsonFilePath(): string
    {
        return public_path('day-level-combined.json');
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
     * Masters that no longer have packages get their blob deleted from Azure.
     * On delete: removes package docs from Azure AI Search, purges orphans, resets+runs indexer.
     *
     * @param  list<int>  $masterIdsPossiblyEmptied  Master IDs that may have zero packages after this change
     * @param  list<string>  $deletedPackageIds  Package ids removed from DB (search key = id)
     * @return array{ok: bool, combined_url: ?string, uploaded_masters: list<int>, deleted_blobs: list<string>, search: array<string, mixed>}
     */
    private function refreshCombinedJsonFile(array $masterIdsPossiblyEmptied = [], array $deletedPackageIds = []): array
    {
        $result = [
            'ok' => false,
            'combined_url' => null,
            'uploaded_masters' => [],
            'deleted_blobs' => [],
            'search' => [],
        ];

        try {
            $rows = DayLevel::query()
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
                'deleted_package_ids' => $deletedPackageIds,
            ]);

            $json = $this->encodeFlatPackagesForBlobStorage($payload);
            if ($json === null) {
                Log::warning('Day-level Azure JSON refresh skipped: payload could not be encoded as a JSON array.');

                return $result;
            }

            $combinedUrl = $this->storeDayLevelJsonOnAzure($json, 'day-level-combined.json');
            $result['combined_url'] = $combinedUrl;
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
                        $result['uploaded_masters'][] = (int) $masterId;
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

            $deletedBlobs = $this->purgeEmptyMasterDmcJsonBlobs(
                array_keys($masterIds),
                $masterIdsPossiblyEmptied
            );
            $result['deleted_blobs'] = $deletedBlobs;
            $result['ok'] = $combinedUrl !== null;

            // Blob indexer does not drop docs when JSON/blobs are deleted — purge by id, then reset+run.
            if ($deletedPackageIds !== [] || $deletedBlobs !== []) {
                $searchSync = CommonHelper::syncAzureSearchAfterDayLevelChange($deletedPackageIds, $packageIds);
                $result['search'] = $searchSync;
                if (! empty($searchSync['skipped'])) {
                    Log::warning('Azure Search sync skipped after day-level change — set AZURE_SEARCH_* in .env', [
                        'deleted_package_ids' => $deletedPackageIds,
                    ]);
                } elseif (empty($searchSync['ok'])) {
                    Log::warning('Azure Search sync incomplete after day-level change', [
                        'search' => $searchSync,
                    ]);
                }
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('Failed to refresh day-level JSON on Azure', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return $result;
        }
    }

    /**
     * Delete master-dmc-{id}.json blobs that no longer have any packages.
     * Uses known emptied IDs first, then lists the container to catch orphans.
     *
     * @param  list<int>  $activeMasterIds
     * @param  list<int>  $masterIdsPossiblyEmptied
     * @return list<string>
     */
    private function purgeEmptyMasterDmcJsonBlobs(array $activeMasterIds, array $masterIdsPossiblyEmptied = []): array
    {
        $active = [];
        foreach ($activeMasterIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $active[$id] = true;
            }
        }

        $candidates = [];
        foreach ($masterIdsPossiblyEmptied as $id) {
            $id = (int) $id;
            if ($id > 0 && ! isset($active[$id])) {
                $candidates['master-dmc-' . $id . '.json'] = true;
            }
        }

        // Also remove any orphan master-dmc-*.json still sitting in the container.
        $listed = CommonHelper::listAzureJsonBlobs('master-dmc-', self::DAY_LEVEL_JSON_CONTAINER);
        foreach ($listed as $blobName) {
            if (! preg_match('/^master-dmc-(\d+)\.json$/i', $blobName, $m)) {
                continue;
            }
            $id = (int) $m[1];
            if ($id > 0 && ! isset($active[$id])) {
                $candidates[$blobName] = true;
            }
        }

        $deleted = [];
        foreach (array_keys($candidates) as $blobName) {
            if (CommonHelper::deleteJsonFromAzure($blobName, self::DAY_LEVEL_JSON_CONTAINER)) {
                $deleted[] = $blobName;
                Log::info('Day-level orphan master JSON deleted from Azure', [
                    'file_name' => $blobName,
                ]);
            }
        }

        return $deleted;
    }
}





