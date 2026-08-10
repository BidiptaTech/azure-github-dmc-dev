<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\City;
use App\Models\Country;
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
     * Resolve the owning DMC userId for the given user.
     * Product / multi-role / team users under a DMC all resolve to that DMC.
     */
    private function resolveDmcIdForUser(User $user)
    {
        if ((int) ($user->userId ?? 0) === 1 || (int) ($user->role_id ?? 0) === 1) {
            return null;
        }

        // Shared hierarchy helper (covers most sales/ops/product/multi-role trees).
        $fromHelper = CommonHelper::getDmcId($user);
        if (!empty($fromHelper)) {
            return (int) $fromHelper;
        }

        $roleId = (int) ($user->role_id ?? 0);

        // Direct DMC roles
        if ($roleId === 11 || $roleId === 20) {
            return (int) $user->userId;
        }

        // Walk created_by until we find a DMC (role 11/20).
        // Covers Product Head/Manager, Multi Role (172–182), and other DMC team roles
        // that may not be listed in CommonHelper::getDmcId.
        $visited = [];
        $candidateId = $user->created_by ?? null;
        $safety = 0;
        while (!empty($candidateId) && $safety < 10 && !in_array($candidateId, $visited, true)) {
            $visited[] = $candidateId;
            $candidate = User::where('userId', $candidateId)->first();
            if (!$candidate) {
                break;
            }
            $candidateRole = (int) ($candidate->role_id ?? 0);
            if ($candidateRole === 11 || $candidateRole === 20) {
                return (int) $candidate->userId;
            }
            $candidateId = $candidate->created_by ?? null;
            $safety++;
        }

        return null;
    }

    /**
     * Normalize zone.dmc_id (int|string|array|null) to a comparable int or null.
     */
    private function normalizeZoneDmcId($dmcId): ?int
    {
        if (is_array($dmcId)) {
            $dmcId = $dmcId[0] ?? null;
        }
        if ($dmcId === null || $dmcId === '' || $dmcId === false) {
            return null;
        }
        $n = (int) $dmcId;
        return $n > 0 ? $n : null;
    }

    /**
     * Whether the current user may edit/delete this zone (own DMC zones only).
     */
    private function canManageZone(User $user, Zone $zone): bool
    {
        if ((int) ($user->userId ?? 0) === 1 || (int) ($user->role_id ?? 0) === 1) {
            // Admin manages master zones only (handled separately in callers when needed).
            return $this->normalizeZoneDmcId($zone->dmc_id) === null;
        }
        $dmcId = $this->resolveDmcIdForUser($user);
        $zoneDmcId = $this->normalizeZoneDmcId($zone->dmc_id);
        return $dmcId && $zoneDmcId && (int) $dmcId === (int) $zoneDmcId;
    }

    /**
     * If zone_id is present in hotel/attraction/restaurant zone_assignments
     * (for the zone's dmc_id when set), return a block message; otherwise null.
     */
    private function getZoneMappedDeleteBlockMessage(Zone $zone): ?string
    {
        $zoneId = $zone->zone_id ?? null;
        if ($zoneId === null || $zoneId === '') {
            return null;
        }

        $zoneType = trim((string) ($zone->zone_type ?? ''));
        $zoneDmcId = $this->normalizeZoneDmcId($zone->dmc_id);
        $zoneIdStr = (string) $zoneId;

        $models = match ($zoneType) {
            'Hotel' => Hotel::whereNotNull('zone_assignments')->get(),
            'Attraction' => Attraction::whereNotNull('zone_assignments')->get(),
            'Restaurant' => Restaurant::whereNotNull('zone_assignments')->get(),
            default => collect(),
        };

        if ($models->isEmpty()) {
            return null;
        }

        $mappedDmcId = null;
        foreach ($models as $model) {
            // When zone belongs to a DMC, only check products under that DMC.
            if ($zoneDmcId) {
                $modelDmcIds = (array) ($model->dmc_id ?? []);
                $belongsToZoneDmc = in_array($zoneDmcId, $modelDmcIds, true)
                    || in_array((string) $zoneDmcId, $modelDmcIds, true)
                    || in_array((int) $zoneDmcId, array_map('intval', $modelDmcIds), true);
                if (!$belongsToZoneDmc) {
                    continue;
                }
            }

            foreach ((array) ($model->zone_assignments ?? []) as $assignment) {
                if (!is_array($assignment)) {
                    continue;
                }
                $assignmentZoneId = isset($assignment['zone_id']) ? (string) $assignment['zone_id'] : '';
                if ($assignmentZoneId === '' || $assignmentZoneId !== $zoneIdStr) {
                    continue;
                }

                $assignmentDmcId = isset($assignment['dmc_id']) ? (int) $assignment['dmc_id'] : 0;
                if ($zoneDmcId && $assignmentDmcId > 0 && $assignmentDmcId !== $zoneDmcId) {
                    continue;
                }

                $mappedDmcId = $zoneDmcId ?: ($assignmentDmcId > 0 ? $assignmentDmcId : null);
                break 2;
            }
        }

        if (!$mappedDmcId) {
            return null;
        }

        $dmcUser = User::where('role_id', 11)
            ->where('userId', $mappedDmcId)
            ->first();
        $dmcName = $dmcUser
            ? trim((string) ($dmcUser->name ?? $dmcUser->company_name ?? ''))
            : '';

        if ($dmcName === '') {
            $dmcName = 'DMC #' . $mappedDmcId;
        }

        return 'This zone is mapped by DMC "' . $dmcName . '"';
    }

    /**
     * Country names from the master DMC profile (comma-separated on user.country).
     */
    private function getMasterDmcCountryNamesForDmc(int $dmcId): array
    {
        $dmcUser = User::where('userId', $dmcId)->first();
        if (!$dmcUser) {
            return [];
        }

        $masterDmcId = $dmcUser->master_dmc_id ?? null;
        if (empty($masterDmcId)) {
            $visited = [];
            $candidateId = $dmcUser->created_by ?? null;
            $safety = 0;
            while (!empty($candidateId) && $safety < 8 && !in_array($candidateId, $visited, true)) {
                $visited[] = $candidateId;
                $candidate = User::where('userId', $candidateId)->first();
                if (!$candidate) {
                    break;
                }
                if ((int) ($candidate->role_id ?? 0) === 3) {
                    $masterDmcId = $candidate->userId;
                    break;
                }
                $candidateId = $candidate->created_by ?? null;
                $safety++;
            }
        }

        $masterDmc = User::where('userId', $masterDmcId ?: $dmcId)->first();
        if ($masterDmc && !empty($masterDmc->country)) {
            return array_values(array_filter(array_map(
                static fn ($c) => trim($c),
                preg_split('/\s*,\s*/', (string) $masterDmc->country)
            )));
        }

        if (!empty($dmcUser->country)) {
            return array_values(array_filter(array_map(
                static fn ($c) => trim($c),
                preg_split('/\s*,\s*/', (string) $dmcUser->country)
            )));
        }

        return [];
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
        $isAdmin = (int) ($user->userId ?? 0) === 1;
        $dmcId = $this->resolveDmcIdForUser($user);
        $roleId = (int) ($user->role_id ?? 0);

        // Build the base query first (so we can apply filters/sorting consistently).
        $zonesQuery = Zone::query();

        // Admin (userId == 1) sees ONLY admin-created master zones.
        // Everyone else: master zones + their DMC's zones (product / multi-role included).
        if ($isAdmin) {
            $zonesQuery->whereNull('dmc_id');
            if (Schema::hasColumn('zones', 'created_by')) {
                $zonesQuery->where('created_by', 1);
            }
        } else {
            $zonesQuery->where(function ($q) use ($user, $dmcId, $roleId) {
                // Master zones always visible
                $q->whereNull('dmc_id')->orWhere('dmc_id', 0)->orWhere('dmc_id', '0');

                if ($roleId === 4) {
                    $dmc_ids = User::where('assistant_manager_id', $user->userId)->pluck('userId')->toArray();
                    $q->orWhereIn('dmc_id', $dmc_ids);
                } elseif ($roleId === 10) {
                    $dmc_ids = User::where('master_dmc_id', $user->userId)->pluck('userId')->toArray();
                    $q->orWhereIn('dmc_id', $dmc_ids);
                } elseif (in_array($roleId, [25, 62, 110], true)) {
                    // Product team under Master DMC — see all child DMC zones
                    if ($roleId === 25) {
                        $master_dmc_id = $user->created_by;
                    } elseif ($roleId === 62) {
                        $product_head = User::where('userId', $user->created_by)->first();
                        $master_dmc_id = $product_head ? $product_head->created_by : null;
                    } else {
                        $product_manager = User::where('userId', $user->created_by)->first();
                        $product_head = $product_manager
                            ? User::where('userId', $product_manager->created_by)->first()
                            : null;
                        $master_dmc_id = $product_head ? $product_head->created_by : null;
                    }

                    if ($master_dmc_id) {
                        $dmc_ids = User::where('master_dmc_id', $master_dmc_id)->pluck('userId')->toArray();
                        $q->orWhereIn('dmc_id', $dmc_ids);
                    }
                } elseif (!empty($dmcId)) {
                    // DMC, Product, Multi Role, and other team under that DMC
                    $q->orWhere('dmc_id', $dmcId);
                }
            });
        }

        $masterDmcCountryNames = (!$isAdmin && $dmcId)
            ? $this->getMasterDmcCountryNamesForDmc((int) $dmcId)
            : [];

        // Non-admin: scope zones to master DMC countries.
        if (!$isAdmin && !empty($masterDmcCountryNames)) {
            $scopedCityIds = City::whereIn('country', $masterDmcCountryNames)->pluck('city_id');
            if ($scopedCityIds->isNotEmpty()) {
                $zonesQuery->whereIn('city', $scopedCityIds);
            } else {
                $zonesQuery->whereRaw('0 = 1');
            }
        }

        $filterCountry = trim((string) $request->query('country', ''));
        $filterCityId = trim((string) $request->query('city', ''));

        if ($filterCountry !== '') {
            $countryCityIds = City::where('country', $filterCountry)->pluck('city_id');
            if ($countryCityIds->isNotEmpty()) {
                $zonesQuery->whereIn('city', $countryCityIds);
            } else {
                $zonesQuery->whereRaw('0 = 1');
            }
        }

        if ($filterCityId !== '') {
            $zonesQuery->where('city', $filterCityId);
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
        $zones = $zonesQuery->with('cities')->orderBy($sort, $direction)->get();

        $countriesQuery = Country::where('is_active', 1);
        if (!$isAdmin && !empty($masterDmcCountryNames)) {
            $countriesQuery->whereIn('name', $masterDmcCountryNames);
        }
        $countries = $countriesQuery->orderBy('name')->get();

        $filterCities = $filterCountry !== ''
            ? City::where('country', $filterCountry)->orderBy('name')->get()
            : collect();

        $hotels = Hotel::all();
        $attractions = Attraction::all();
        $restaurants = Restaurant::all();

        // Precompute zone items (hotels/attractions/restaurants) per zone using zone_assignments - same logic as edit-vehicle
        $zoneItemsMap = [];
        foreach ($zones as $zone) {
            $zoneItemsMap[$zone->zone_id] = $this->getZoneItemsForZone($zone, $dmcId, $hotels, $attractions, $restaurants);
        }
        return view('zones.index', compact(
            'zones',
            'hotels',
            'attractions',
            'restaurants',
            'dmcId',
            'zoneItemsMap',
            'countries',
            'filterCountry',
            'filterCityId',
            'filterCities'
        ));
    }

    /**
     * Show the form for creating a new zone.
     */
    public function create()
    {
        $user = Auth::user();
        $isAdmin = (int) ($user->role_id ?? 0) === 1
            || (int) ($user->userId ?? 0) === 1;

        $dmcId = $this->resolveDmcIdForUser($user);
        $masterNames = $dmcId ? $this->getMasterDmcCountryNamesForDmc((int) $dmcId) : [];

        $countriesQuery = Country::where('is_active', 1)->orderBy('name');
        if (!$isAdmin && !empty($masterNames)) {
            $countriesQuery->whereIn('name', $masterNames);
        }
        $countries = $countriesQuery->get();

        $selectedCountry = old('country', $masterNames[0] ?? ($countries->first()->name ?? null));
        $city = $selectedCountry
            ? City::where('country', $selectedCountry)->orderBy('name')->get()
            : collect();

        return view('zones.create', compact('city', 'countries', 'isAdmin', 'selectedCountry'));
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
            'zone_type' => 'required|array|min:1',
            'zone_type.*' => 'required|string|in:Hotel,Attraction,Restaurant',
            'vehicle_type' => 'required|string|in:Shared,Private,Both',
            'description' => 'nullable|string',
            'country' => 'required|string|max:255',
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



        $authUser = Auth::user();
        if ((int) ($authUser->userId ?? 0) === 1) {
            // Admin-created master zone
            $dmcIdForZone = null;
        } else {
            // Always store parent DMC userId (never the product/multi-role userId)
            $dmcIdForZone = $this->resolveDmcIdForUser($authUser);
            if (!$dmcIdForZone) {
                return redirect()->back()
                    ->withErrors(['dmc_id' => 'DMC ID not found'])
                    ->withInput();
            }
            $dmcIdForZone = (int) $dmcIdForZone;
        }

        $zoneTypes = $validated['zone_type'];
        $createdCount = 0;
        $lastZone = null;

        foreach ($zoneTypes as $zoneType) {
            $payload = [
                'zone_name' => trim($validated['zone_name']),
                'zone_type' => $zoneType,
                'vehicle_type' => $validated['vehicle_type'],
                'description' => isset($validated['description']) ? trim($validated['description']) : null,
                'city' => (string) $validated['city'],
                'status' => (int) $validated['status'],
                'dmc_id' => $dmcIdForZone,
            ];
            if (Schema::hasColumn('zones', 'created_by')) {
                $payload['created_by'] = $authUser->userId;
            }
            $zone = Zone::create($payload);

            $createdCount++;
            $lastZone = $zone;
            \Log::info('Zone created', [
                'zone_type' => $zoneType,
                'zone_id' => $zone->zone_id,
                'id' => $zone->id,
            ]);
        }

        return redirect()->route('zones.index')
            ->with('success', "Zone created successfully ({$createdCount})");
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
        $user = Auth::user();
        $isAdmin = (int) ($user->role_id ?? 0) === 1
            || (int) ($user->userId ?? 0) === 1;

        $dmcId = $this->resolveDmcIdForUser($user);
        if (!$isAdmin && !$this->canManageZone($user, $zone)) {
            return redirect()->route('zones.index')
                ->with('error', 'You are not authorized to edit this zone');
        }

        $masterNames = $dmcId ? $this->getMasterDmcCountryNamesForDmc((int) $dmcId) : [];
        $countriesQuery = Country::where('is_active', 1)->orderBy('name');
        if (!$isAdmin && !empty($masterNames)) {
            $countriesQuery->whereIn('name', $masterNames);
        }
        $countries = $countriesQuery->get();

        $zoneCountry = City::where('city_id', $zone->city)->value('country');
        $selectedCountry = old('country', $zoneCountry ?: ($masterNames[0] ?? ($countries->first()->name ?? null)));
        $city = $selectedCountry
            ? City::where('country', $selectedCountry)->orderBy('name')->get()
            : collect();

        return view('zones.edit', compact('zone', 'city', 'countries', 'isAdmin', 'zoneCountry', 'selectedCountry'));
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
            'country' => 'required|string|max:255',
            'city' => 'required',
            'status' => 'required|integer',
        ]);
        $isAdmin = (int) (Auth::user()->role_id ?? 0) === 1
            || (int) (Auth::user()->userId ?? 0) === 1;
        if (!$isAdmin && !$this->canManageZone(Auth::user(), $zone)) {
            return redirect()->route('zones.index')
                ->with('error', 'You are not authorized to edit this zone');
        }
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update zone
        // Update ONLY validated fields; keep existing dmc_id untouched.
        $data = $validator->validated();
        unset($data['country']); // country is UI-only for city filtering
        
        $zone->update($data);

        return redirect()->route('zones.index')
            ->with('success', 'Zone updated successfully');
    }

    /**
     * AJAX: whether the zone can be deleted (admin mapping check).
     */
    public function checkDelete($id)
    {
        try {
            $zoneId = Crypt::decrypt($id);
        } catch (\Throwable $e) {
            return response()->json([
                'can_delete' => false,
                'message' => 'Invalid zone reference.',
            ], 400);
        }

        $zone = Zone::where('zone_id', $zoneId)->first();
        if (!$zone) {
            return response()->json([
                'can_delete' => false,
                'message' => 'Zone not found.',
            ], 404);
        }

        $authUser = Auth::user();
        if (!$this->canManageZone($authUser, $zone)) {
            return response()->json([
                'can_delete' => false,
                'message' => 'You are not authorized to delete this zone.',
            ], 403);
        }

        $isAdminUser = (int) ($authUser->userId ?? 0) === 1 || (int) ($authUser->role_id ?? 0) === 1;
        if ($isAdminUser) {
            $mappedMessage = $this->getZoneMappedDeleteBlockMessage($zone);
            if ($mappedMessage) {
                return response()->json([
                    'can_delete' => false,
                    'message' => $mappedMessage,
                ]);
            }
        }

        return response()->json([
            'can_delete' => true,
            'message' => null,
        ]);
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
                ->with('error', 'Zone not found');
        }
        if (!$this->canManageZone(Auth::user(), $zone)) {
            return redirect()->route('zones.index')
                ->with('error', 'You are not authorized to delete this zone');
        }

        // Admin / userId=1: block delete when this zone is still mapped on products.
        $authUser = Auth::user();
        $isAdminUser = (int) ($authUser->userId ?? 0) === 1 || (int) ($authUser->role_id ?? 0) === 1;
        if ($isAdminUser) {
            $mappedMessage = $this->getZoneMappedDeleteBlockMessage($zone);
            if ($mappedMessage) {
                return redirect()->route('zones.index')
                    ->with('error', $mappedMessage);
            }
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
