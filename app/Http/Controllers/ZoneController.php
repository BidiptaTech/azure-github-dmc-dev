<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\City;
use App\Helpers\CommonHelper;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class ZoneController extends Controller
{
    /**
     * Remove a single {dmc_id, zone_id} assignment from a model's zone_assignments array.
     */
    private function removeZoneAssignmentForDmc($model, int $dmcId, $zoneId): void
    {
        $assignments = $model->zone_assignments ?? [];
        $zoneIdStr = (string) $zoneId;

        $assignments = array_values(array_filter($assignments, function ($assignment) use ($dmcId, $zoneIdStr) {
            if (!is_array($assignment)) {
                return true;
            }
            $aDmc = isset($assignment['dmc_id']) ? (int) $assignment['dmc_id'] : null;
            $aZone = isset($assignment['zone_id']) ? (string) $assignment['zone_id'] : '';
            // Keep everything that is NOT the exact pair {dmc_id, zone_id}
            return !($aDmc === $dmcId && $aZone === $zoneIdStr);
        }));

        $model->zone_assignments = $assignments;
        $model->save();
    }

    /**
     * Resolve the DMC ID for the given user based on role hierarchy
     * This mirrors the conditions used in index() for filtering zones.
     */
    private function resolveDmcIdForUser(User $user)
    {
        // Direct DMC roles
        if ($user->role_id == 11 || $user->role_id == 20) {
            return $user->userId;
        }

        // Team roles directly under a DMC
        if ($user->role_id == 35 || in_array($user->role_id, [130, 132, 133, 135, 136, 137, 138])) {
            return $user->created_by;
        }

        // Roles under Product Head
        if ($user->role_id == 76 || $user->role_id == 139) {
            $productHead = User::where('userId', $user->created_by)->first();
            return $productHead ? $productHead->created_by : null;
        }

        // Roles under Product Manager → Product Head
        if ($user->role_id == 111 || $user->role_id == 140) {
            $productManager = User::where('userId', $user->created_by)->first();
            $productHead = $productManager ? User::where('userId', $productManager->created_by)->first() : null;
            return $productHead ? $productHead->created_by : null;
        }

        return null;
    }

    /**
     * City name from the DMC profile used to scope zone lists (e.g. Singapore DMC only sees Singapore zones).
     * Uses the resolved parent DMC when the current user is team/staff; otherwise the logged-in user's city.
     */
    private function dmcHomeCityName(User $user): ?string
    {
        $dmcId = $this->resolveDmcIdForUser($user);
        if ($dmcId) {
            $dmcUser = User::where('userId', $dmcId)->first();
            $name = $dmcUser->city ?? null;
        } else {
            $name = $user->city ?? null;
        }
        $name = is_string($name) ? trim($name) : '';
        return $name !== '' ? $name : null;
    }

    /**
     * Get hotels/attractions/restaurants in a zone for a DMC (uses zone_assignments JSON).
     * Same logic as VehicleController::getZoneItemsForVehicle.
     * zone_assignments format: [{"dmc_id":4,"zone_id":"14"}]
     */
    private function getZoneItemsForZone(Zone $zone, $dmcId, $hotels, $attractions, $restaurants): array
    {
        if (!in_array($zone->zone_type ?? '', ['Hotel', 'Attraction', 'Restaurant'])) {
            return [];
        }
        $effectiveDmcId = $dmcId ?? (is_array($zone->dmc_id) ? ($zone->dmc_id[0] ?? null) : $zone->dmc_id);
        $effectiveDmcId = (int) $effectiveDmcId;
        if (!$effectiveDmcId) {
            return [];
        }
        $zoneIdStr = (string) ($zone->zone_id ?? '');
        if ($zoneIdStr === '') {
            return [];
        }

        $zoneIdMatches = fn ($a, $b) => (string) ($a ?? '') === (string) ($b ?? '') || (int) ($a ?? 0) === (int) ($b ?? 0);

        $hasDmc = fn ($model) => in_array($effectiveDmcId, (array) ($model->dmc_id ?? [])) || in_array((string) $effectiveDmcId, (array) ($model->dmc_id ?? []));

        if ($zone->zone_type == 'Hotel') {
            $items = $hotels->filter(fn ($h) => ($h->status ?? 0) == 1 && $hasDmc($h) && $zoneIdMatches($h->getZoneForDmc($effectiveDmcId), $zoneIdStr));
            return $items->map(fn ($h) => [
                'name' => $h->name ?? '',
                'image' => ($h->main_image ?? '') ? (str_starts_with($h->main_image ?? '', 'http') || str_starts_with($h->main_image ?? '', '/') ? $h->main_image : asset($h->main_image)) : '',
            ])->values()->toArray();
        }
        if ($zone->zone_type == 'Attraction') {
            $items = $attractions->filter(fn ($a) => ($a->status ?? 0) == 1 && $hasDmc($a) && $zoneIdMatches($a->getZoneForDmc($effectiveDmcId), $zoneIdStr));
            return $items->map(fn ($a) => [
                'name' => $a->name ?? '',
                'image' => ($a->master_image ?? '') ? (str_starts_with($a->master_image ?? '', 'http') || str_starts_with($a->master_image ?? '', '/') ? $a->master_image : asset($a->master_image)) : '',
            ])->values()->toArray();
        }
        if ($zone->zone_type == 'Restaurant') {
            $items = $restaurants->filter(fn ($r) => ($r->status ?? 0) == 1 && $hasDmc($r) && $zoneIdMatches($r->getZoneForDmc($effectiveDmcId), $zoneIdStr));
            return $items->map(fn ($r) => [
                'name' => $r->name ?? '',
                'image' => ($r->master_image ?? '') ? (str_starts_with($r->master_image ?? '', 'http') || str_starts_with($r->master_image ?? '', '/') ? $r->master_image : asset($r->master_image)) : '',
            ])->values()->toArray();
        }
        return [];
    }

    /**
     * Display a listing of zones.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Build the base query first (so we can apply filters/sorting consistently).
        $zonesQuery = Zone::query();

        // Admin (userId == 1) sees ONLY admin-created master zones.
        // Everyone else keeps legacy role-based zone filtering.
        if ((int) ($user->userId ?? 0) === 1) {
            $zonesQuery->whereNull('dmc_id');
            if (Schema::hasColumn('zones', 'created_by')) {
                $zonesQuery->where('created_by', 1);
            }
        } else {
            // For all non-admin users:
            // show Master Zones (admin-created global zones) + the user's legacy DMC zones.
            $zonesQuery->where(function ($q) use ($user) {
                // Master zones always visible
                $q->whereNull('dmc_id')->orWhere('dmc_id', 0)->orWhere('dmc_id', '0');

                // Legacy DMC zones (keep existing behavior)
                if ($user->role_id == 4) {
                    $dmc_ids = User::where('assistant_manager_id', $user->userId)->pluck('userId')->toArray();
                    $q->orWhereIn('dmc_id', $dmc_ids);
                } elseif ($user->role_id == 10) {
                    $dmc_ids = User::where('master_dmc_id', $user->userId)->pluck('userId')->toArray();
                    $q->orWhereIn('dmc_id', $dmc_ids);
                } elseif ($user->role_id == 11 || $user->role_id == 20) {
                    $q->orWhere('dmc_id', $user->userId);
                } elseif (in_array($user->role_id, [25, 62, 110], true)) {
                    if($user->role_id == 25){
                        $master_dmc_id = $user->created_by;
                    }
                    elseif($user->role_id == 62){
                        $product_head = User::where('userId', $user->created_by)->first();
                        $master_dmc_id = $product_head->created_by;
                    }
                    else { // 110
                        $product_manager = User::where('userId', $user->created_by)->first();
                        $product_head = User::where('userId', $product_manager->created_by)->first();
                        $master_dmc_id = $product_head->created_by;
                    }

                    $dmc_ids = User::where('master_dmc_id', $master_dmc_id)->pluck('userId')->toArray();
                    $q->orWhereIn('dmc_id', $dmc_ids);
                } elseif (in_array($user->role_id, [35, 130, 132, 133, 135, 136, 137, 138], true)) {
                    $q->orWhere('dmc_id', $user->created_by);
                } elseif ($user->role_id == 76 || $user->role_id == 139) {
                    $product_head = User::where('userId', $user->created_by)->first();
                    $q->orWhere('dmc_id', $product_head->created_by);
                } elseif ($user->role_id == 111 || $user->role_id == 140) {
                    $product_manager = User::where('userId', $user->created_by)->first();
                    $product_head = User::where('userId', $product_manager->created_by)->first();
                    $q->orWhere('dmc_id', $product_head->created_by);
                }
            });
        }

        // Non-admin: only list zones in the DMC's home city (zone.city is city_id; user.city is city name).
        if ((int) ($user->userId ?? 0) !== 1) {
            $homeCityName = $this->dmcHomeCityName($user);
            if ($homeCityName !== null) {
                $cityIds = City::query()
                    ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($homeCityName, 'UTF-8')])
                    ->pluck('city_id');
                if ($cityIds->isNotEmpty()) {
                    $zonesQuery->whereIn('city', $cityIds);
                } else {
                    // Profile city set but not found in cities master — do not show other regions' zones.
                    $zonesQuery->whereRaw('0 = 1');
                }
            }
        }

        // Optional filter by zone type (used by UI tabs: Hotel/Restaurant/Attraction/All).
        $zoneType = trim((string) $request->query('zone_type', ''));
        $allowedTypes = ['Hotel', 'Restaurant', 'Attraction'];
        if ($zoneType !== '' && in_array($zoneType, $allowedTypes, true)) {
            $zonesQuery->where('zone_type', $zoneType);
        }

        // Optional sorting (preserved in UI when switching sort).
        $sort = (string) $request->query('sort', 'updated_at');
        $direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['zone_name', 'zone_type', 'status', 'updated_at'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'updated_at';
        }
        $zones = $zonesQuery->orderBy($sort, $direction)->get();

        $hotels = Hotel::all();
        $attractions = Attraction::all();
        $restaurants = Restaurant::all();
        $dmcId = $this->resolveDmcIdForUser($user);

        // Precompute zone items (hotels/attractions/restaurants) per zone using zone_assignments - same logic as edit-vehicle
        $zoneItemsMap = [];
        foreach ($zones as $zone) {
            $zoneItemsMap[$zone->zone_id] = $this->getZoneItemsForZone($zone, $dmcId, $hotels, $attractions, $restaurants);
        }
        return view('zones.index', compact('zones', 'hotels', 'attractions', 'restaurants', 'dmcId', 'zoneItemsMap'));
    }

    /**
     * Show the form for creating a new zone.
     */
    public function create()
    {
        if ((int) (Auth::user()->userId ?? 0) === 1) {
            $city = City::orderBy('name')->get();
        } else {
            $city = City::where('country', Auth::user()->country)->orderBy('name')->get();
        }
        return view('zones.create', compact('city'));
    }
    

    /**
     * Store a newly created zone in storage.
     */
    public function store(Request $request)
    {
        $statusInput = $request->input('status');
        if (is_array($statusInput)) {
            $statusInput = in_array('1', $statusInput, true) || in_array(1, $statusInput, true) ? 1 : 0;
        }
        $request->merge(['status' => (int) $statusInput]);

        $validator = Validator::make($request->all(), [
            'zone_name' => 'required|string|max:255',
            'zone_type' => 'required|string|in:Hotel,Attraction,Restaurant',
            'vehicle_type' => 'required|string|in:Shared,Private,Both',
            'description' => 'nullable|string',
            'city' => 'required',
            'status' => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        \Log::info('Zone store hit', [
            'time' => now(),
            'user' => Auth::id(),
        ]);
        // $zoneMaxId = Zone::withTrashed()->max('zone_id') ?? 0;
        // $zoneId = CommonHelper::createId($zoneMaxId);



        if ((int) (Auth::user()->userId ?? 0) === 1) {
            $dmcIdForZone = null;
        } else {
            $dmcIdForZone = $this->resolveDmcIdForUser(Auth::user());
            if (!$dmcIdForZone) {
                return redirect()->back()
                    ->withErrors(['dmc_id' => 'DMC ID not found'])
                    ->withInput();
            }
        }

        $zone = Zone::create([
            // 'zone_id' => (string) $zoneId,
            'zone_name' => trim($validated['zone_name']),
            'zone_type' => $validated['zone_type'],
            'vehicle_type' => $validated['vehicle_type'],
            'description' => isset($validated['description']) ? trim($validated['description']) : null,
            'city' => (string) $validated['city'],
            'status' => (int) $validated['status'],
            'dmc_id' => $dmcIdForZone,
            ...(Schema::hasColumn('zones', 'created_by') && (int) (Auth::user()->userId ?? 0) === 1
                ? ['created_by' => Auth::user()->userId]
                : []),
        ]);
        \Log::info('Zone created', [
            'id' => $zone->id,
            'zone_id' => $zone->zone_id,
        ]);
        \Log::info('Zone raw attrs', $zone->getAttributes());
        $zone->refresh();
        $zoneId = $zone->zone_id;

        return redirect()->route('zones.index')
            ->with('success', 'Zone created successfully');
    }

    /**
     * Display the specified zone.
     */
    public function show($id)
    {
        $zoneId = Crypt::decrypt($id);
        $zone = Zone::where('zone_id', $zoneId)->first();
        // Get the city name using the city_id
        $cityName = City::where('city_id', $zone->city)->value('name') ?? $zone->city;
        return view('zones.show', compact('zone', 'cityName'));
    }

    /**
     * Show the form for editing the specified zone.
     */
    public function edit($id)
    {
        $zoneId = Crypt::decrypt($id);
        $zone = Zone::where('zone_id', $zoneId)->first();
        if ((int) (Auth::user()->userId ?? 0) === 1) {
            $city = City::orderBy('name')->get();
        } else {
            $city = City::where('country', Auth::user()->country)->orderBy('name')->get();
        }
        return view('zones.edit', compact('zone', 'city'));
    }

    /**
     * Update the specified zone in storage.
     */
    public function update(Request $request, Zone $zone)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'zone_name' => 'required|string|max:255',
            'zone_type' => 'required|string|max:255',
            'vehicle_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'city' => 'required',
            'status' => 'required|integer',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update zone
        // Update ONLY validated fields; keep existing dmc_id untouched.
        $data = $validator->validated();
        
        $zone->update($data);

        return redirect()->route('zones.index')
            ->with('success', 'Zone updated successfully');
    }

    /**
     * Remove the specified zone from storage.
     */
    public function destroy($id)
    {
        $zoneId = Crypt::decrypt($id);
        $zone = Zone::where('zone_id', $zoneId)->first();
        if (!$zone) {
            return redirect()->route('zones.index')
                ->with('success', 'Zone deleted successfully');
        }

        // Remove assignments for the DMC performing the delete (this is what user expects).
        // Do NOT rely on $zone->dmc_id because it can be stored as JSON/array and cast to 0.
        $currentDmcId = (int) ($this->resolveDmcIdForUser(Auth::user()) ?? 0);
        if (!$currentDmcId) {
            // Fallback: attempt to parse zone dmc_id if present
            $zd = $zone->dmc_id ?? null;
            if (is_array($zd)) {
                $currentDmcId = (int) ($zd[0] ?? 0);
            } elseif (is_string($zd)) {
                $decoded = json_decode($zd, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $currentDmcId = (int) ($decoded[0] ?? 0);
                } elseif (is_numeric($zd)) {
                    $currentDmcId = (int) $zd;
                }
            } elseif (is_numeric($zd)) {
                $currentDmcId = (int) $zd;
            }
        }
        
        // Remove zone assignments from associated hotels, attractions, and restaurants
        // NOTE:
        // In some DB rows zone_assignments JSON can have mixed string/integer types
        // causing whereJsonContains matches to fail. Use model helpers instead.
        if ($currentDmcId && $zone->zone_type == 'Hotel') {
            $hotels = Hotel::whereNotNull('zone_assignments')->get();
            foreach ($hotels as $hotel) {
                $assignedZone = $hotel->getZoneForDmc($currentDmcId);
                if ((string) ($assignedZone ?? '') === (string) $zoneId) {
                    $hotel->setZoneForDmc($currentDmcId, null);
                }
            }
        } elseif ($currentDmcId && $zone->zone_type == 'Attraction') {
            $attractions = Attraction::whereNotNull('zone_assignments')->get();
            foreach ($attractions as $attraction) {
                $assignedZone = $attraction->getZoneForDmc($currentDmcId);
                if ((string) ($assignedZone ?? '') === (string) $zoneId) {
                    $attraction->setZoneForDmc($currentDmcId, null);
                }
            }
        } elseif ($currentDmcId && $zone->zone_type == 'Restaurant') {
            $restaurants = Restaurant::whereNotNull('zone_assignments')->get();
            foreach ($restaurants as $restaurant) {
                $assignedZone = $restaurant->getZoneForDmc($currentDmcId);
                if ((string) ($assignedZone ?? '') === (string) $zoneId) {
                    $restaurant->setZoneForDmc($currentDmcId, null);
                }
            }
        }
        
        // Soft delete the zone (uses deleted_at column)
        $zone->delete();

        return redirect()->route('zones.index')
            ->with('success', 'Zone deleted successfully');
    }
    
    /**
     * Save settings for a zone
     */
    public function saveSettings(Request $request, $zone_id)
    {
        $zone = Zone::where('zone_id', $zone_id)->first();
        
        if (!$zone) {
            return redirect()->route('zones.index')->with('error', 'Zone not found');
        }
        
        // Determine DMC ID using the same conditions as index()
        $currentDmcId = $this->resolveDmcIdForUser(Auth::user());
        
        if (!$currentDmcId) {
            return redirect()->route('zones.index')->with('error', 'Unable to determine your DMC association');
        }
        
        // Handle DMC-specific zone assignments
        if ($zone->zone_type == 'Hotel') {
            // Clear previous assignments for this DMC and zone
            $allHotels = Hotel::whereJsonContains('dmc_id', $currentDmcId)->get();
            foreach ($allHotels as $hotel) {
                if ($hotel->getZoneForDmc($currentDmcId) == $zone_id) {
                    $hotel->setZoneForDmc($currentDmcId, null); // Remove assignment
                }
            }
            
            // Set new assignments for selected hotels
            if ($request->has('hotels')) {
                $selectedHotels = Hotel::whereIn('hotel_unique_id', $request->hotels)->get();
                foreach ($selectedHotels as $hotel) {
                    $hotel->setZoneForDmc($currentDmcId, $zone_id);
                }
            }
        } elseif ($zone->zone_type == 'Attraction') {
            // Clear previous assignments for this DMC and zone
            $allAttractions = Attraction::whereJsonContains('dmc_id', $currentDmcId)->get();
            foreach ($allAttractions as $attraction) {
                if ($attraction->getZoneForDmc($currentDmcId) == $zone_id) {
                    $attraction->setZoneForDmc($currentDmcId, null); // Remove assignment
                }
            }
            
            // Set new assignments for selected attractions
            if ($request->has('attractions')) {
                $selectedAttractions = Attraction::whereIn('attraction_id', $request->attractions)->get();
                foreach ($selectedAttractions as $attraction) {
                    $attraction->setZoneForDmc($currentDmcId, $zone_id);
                }
            }
        } elseif ($zone->zone_type == 'Restaurant') {
            // Clear previous assignments for this DMC and zone
            $allRestaurants = Restaurant::whereJsonContains('dmc_id', $currentDmcId)->get();
            foreach ($allRestaurants as $restaurant) {
                if ($restaurant->getZoneForDmc($currentDmcId) == $zone_id) {
                    $restaurant->setZoneForDmc($currentDmcId, null); // Remove assignment
                }
            }
            
            // Set new assignments for selected restaurants
            if ($request->has('restaurants')) {
                $selectedRestaurants = Restaurant::whereIn('restaurant_id', $request->restaurants)->get();
                foreach ($selectedRestaurants as $restaurant) {
                    $restaurant->setZoneForDmc($currentDmcId, $zone_id);
                }
            }
        }
        
        return redirect()->route('zones.index')->with('success', 'Zone settings updated successfully for your DMC');
    }
}
