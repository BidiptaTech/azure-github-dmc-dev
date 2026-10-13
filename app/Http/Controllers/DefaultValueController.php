<?php

namespace App\Http\Controllers;

use App\Models\DefaultValue;
use App\Models\Hotel;
use App\Models\Restaurant;
use App\Models\Attraction;
use App\Models\Vehicle;
use App\Models\Port;
use App\Models\Guide;
use App\Models\User;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;

class DefaultValueController extends Controller
{
    private array $allTypes = ['hotel', 'restaurant', 'attraction', 'car_private', 'car_shared', 'port', 'guide'];

    private function resolveDmcIdForUser(User $user)
    {
        if ($user->role_id == 11 || $user->role_id == 20) {
            return $user->userId;
        }

        if ($user->role_id == 35 || in_array($user->role_id, [130, 132, 133, 135, 136, 137, 138])) {
            return $user->created_by;
        }

        if ($user->role_id == 76 || $user->role_id == 139) {
            $productHead = User::where('userId', $user->created_by)->first();
            return $productHead ? $productHead->created_by : null;
        }

        if ($user->role_id == 111 || $user->role_id == 140) {
            $productManager = User::where('userId', $user->created_by)->first();
            $productHead = $productManager ? User::where('userId', $productManager->created_by)->first() : null;
            return $productHead ? $productHead->created_by : null;
        }

        return null;
    }

    /**
     * Master-DMC / DMC country list (same idea as EnquiryFormPro).
     */
    private function getAccessibleCountryNames($dmcId, User $user): array
    {
        $dmcUser = User::where('userId', $dmcId)->first();
        if ($dmcUser) {
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
        }

        if (!empty($user->country)) {
            return array_values(array_filter(array_map(
                static fn ($c) => trim($c),
                preg_split('/\s*,\s*/', (string) $user->country)
            )));
        }

        return [];
    }

    private function getCitiesGroupedByCountry(array $countryNames): array
    {
        if (empty($countryNames)) {
            return [];
        }

        $cities = City::whereIn('country', $countryNames)
            ->orderBy('name')
            ->get(['name', 'country', 'city_id']);

        $grouped = [];
        foreach ($cities as $city) {
            $grouped[$city->country][] = [
                'name' => $city->name,
                'city_id' => $city->city_id,
            ];
        }

        return $grouped;
    }

    private function loadServiceRelation(DefaultValue $value): void
    {
        switch ($value->name) {
            case 'hotel':
                $value->load('hotel');
                break;
            case 'restaurant':
                $value->load('restaurant');
                break;
            case 'attraction':
                $value->load('attraction');
                break;
            case 'car_private':
            case 'car_shared':
                $value->load('vehicle');
                break;
            case 'port':
                $value->load('port');
                break;
            case 'guide':
                $value->load('guide');
                break;
        }
    }

    /**
     * Fetch services filtered by DMC + country + city.
     */
    private function fetchServicesForType($dmcId, string $type, ?string $country = null, ?string $city = null)
    {
        $country = $country !== null ? trim($country) : null;
        $city = $city !== null ? trim($city) : null;

        switch ($type) {
            case 'hotel':
                $q = Hotel::whereJsonContains('dmc_id', (int) $dmcId)
                    ->where('status', 1)
                    ->where('is_active', 1);
                if ($country) {
                    $q->where('country', $country);
                }
                if ($city) {
                    $q->where('city', $city);
                }
                return $q->select('hotel_unique_id', 'name', 'city', 'country')
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($h) => [
                        'id' => $h->hotel_unique_id,
                        'name' => $h->name . ($h->city ? ' (' . $h->city . ')' : ''),
                    ]);

            case 'restaurant':
                $q = Restaurant::whereJsonContains('dmc_id', (int) $dmcId)
                    ->where('status', 1)
                    ->where('is_active', 1);
                if ($country) {
                    $q->where('country', $country);
                }
                if ($city) {
                    $q->where('city', $city);
                }
                return $q->select('restaurant_id', 'name', 'city', 'country')
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($r) => [
                        'id' => $r->restaurant_id,
                        'name' => $r->name . ($r->city ? ' (' . $r->city . ')' : ''),
                    ]);

            case 'attraction':
                $q = Attraction::whereJsonContains('dmc_id', (int) $dmcId)
                    ->where('status', 1)
                    ->where('is_active', 1);
                if ($country) {
                    $q->where('country', $country);
                }
                if ($city) {
                    $q->where('location', $city);
                }
                return $q->select('attraction_id', 'name', 'location', 'country')
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($a) => [
                        'id' => $a->attraction_id,
                        'name' => $a->name . ($a->location ? ' (' . $a->location . ')' : ''),
                    ]);

            case 'car_private':
            case 'car_shared':
                $sharable = $type === 'car_private' ? [1, 3] : [2, 3];
                $q = Vehicle::where('dmc_id', $dmcId)
                    ->where('is_available', 1)
                    ->whereIn('sharable', $sharable);
                if ($city) {
                    $q->where(function ($qq) use ($city) {
                        $qq->where('city', $city)
                            ->orWhere('city', 'like', '%' . $city . '%')
                            ->orWhereNull('city')
                            ->orWhere('city', '');
                    });
                }
                return $q->select('vehicle_id', 'vehicle_name', 'vehicle_type', 'sharable', 'city')
                    ->orderBy('vehicle_name')
                    ->get()
                    ->map(function ($vehicle) {
                        $sharableLabel = match ((int) $vehicle->sharable) {
                            1 => 'Private',
                            2 => 'Shared',
                            3 => 'Both',
                            default => 'Unknown'
                        };
                        return [
                            'id' => $vehicle->vehicle_id,
                            'name' => $vehicle->vehicle_name . ' (' . ucfirst((string) $vehicle->vehicle_type) . ' - ' . $sharableLabel . ')',
                        ];
                    });

            case 'port':
                $q = Port::where('status', 1);
                if ($country) {
                    $q->where('country', $country);
                }
                if ($city) {
                    $cityId = City::where('name', $city)->value('city_id');
                    if ($cityId) {
                        $q->where('city_id', $cityId);
                    }
                }
                return $q->select('port_id', 'port_name', 'country', 'city_id')
                    ->orderBy('port_name')
                    ->get()
                    ->map(fn ($p) => [
                        'id' => $p->port_id,
                        'name' => $p->port_name . ($p->country ? ' - ' . $p->country : ''),
                    ]);

            case 'guide':
                $q = Guide::where('dmc_id', $dmcId)->whereIn('status', [1, 3]);
                if ($country) {
                    $q->where(function ($qq) use ($country) {
                        $qq->where('country', $country)->orWhereNull('country')->orWhere('country', '');
                    });
                }
                if ($city) {
                    $q->where(function ($qq) use ($city) {
                        $qq->where('city', $city)->orWhereNull('city')->orWhere('city', '');
                    });
                }
                return $q->select('guide_id', 'name', 'email', 'city', 'country')
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($g) => [
                        'id' => $g->guide_id,
                        'name' => $g->name . ($g->email ? ' - ' . $g->email : ''),
                    ]);
        }

        return collect();
    }

    public function index()
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);

        if (!$dmcId) {
            return redirect()->route('dashboard')->with('error', 'Access denied. DMC ID not found.');
        }

        $defaultValues = DefaultValue::where('dmc_id', $dmcId)
            ->orderBy('country')
            ->orderBy('city')
            ->orderBy('name')
            ->get();

        foreach ($defaultValues as $value) {
            $this->loadServiceRelation($value);
        }

        // Always allow adding more — uniqueness is per country+city+type
        $availableTypes = $this->allTypes;

        return view('default-values.index', compact('defaultValues', 'availableTypes', 'dmcId'));
    }

    public function create()
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);

        if (!$dmcId) {
            return redirect()->route('dashboard')->with('error', 'Access denied. DMC ID not found.');
        }

        $availableTypes = $this->allTypes;
        $countryNames = $this->getAccessibleCountryNames($dmcId, $user);
        $countries = !empty($countryNames)
            ? Country::whereIn('name', $countryNames)->orderBy('name')->get(['name', 'country_id'])
            : collect();
        $citiesByCountry = $this->getCitiesGroupedByCountry($countryNames);

        $existingDefaults = DefaultValue::where('dmc_id', $dmcId)
            ->get(['name', 'country', 'city'])
            ->map(fn ($row) => [
                'name' => $row->name,
                'country' => (string) ($row->country ?? ''),
                'city' => (string) ($row->city ?? ''),
            ])
            ->values()
            ->toArray();

        return view('default-values.create', compact(
            'availableTypes',
            'countries',
            'citiesByCountry',
            'existingDefaults',
            'dmcId'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);

        if (!$dmcId) {
            return redirect()->back()->with('error', 'DMC ID not found.')->withInput();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|in:hotel,restaurant,attraction,car_private,car_shared,port,guide',
            'service_id' => 'required|string',
            'status' => 'required|integer|in:0,1',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $country = trim($request->country);
        $city = trim($request->city);

        $existingDefault = DefaultValue::where('dmc_id', $dmcId)
            ->where('name', $request->name)
            ->where('country', $country)
            ->where('city', $city)
            ->first();

        if ($existingDefault) {
            return redirect()->back()
                ->with('error', 'Default ' . $request->name . ' already exists for ' . $city . ' (' . $country . '). Edit the existing one.')
                ->withInput();
        }

        $trashedSameService = DefaultValue::onlyTrashed()
            ->where('dmc_id', $dmcId)
            ->where('name', $request->name)
            ->where('country', $country)
            ->where('city', $city)
            ->where('service_id', $request->service_id)
            ->first();

        if ($trashedSameService) {
            $trashedSameService->restore();
            $trashedSameService->update([
                'service_id' => $request->service_id,
                'status' => $request->status,
                'country' => $country,
                'city' => $city,
            ]);

            return redirect()->route('default-values.index')
                ->with('success', 'Default value restored successfully.');
        }

        DefaultValue::create([
            'dmc_id' => $dmcId,
            'country' => $country,
            'city' => $city,
            'name' => $request->name,
            'service_id' => $request->service_id,
            'status' => $request->status,
        ]);

        return redirect()->route('default-values.index')
            ->with('success', 'Default value created successfully for ' . $city . ' (' . $country . ').');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);

        if (!$dmcId) {
            return redirect()->route('dashboard')->with('error', 'Access denied. DMC ID not found.');
        }

        $defaultValueId = Crypt::decrypt($id);
        $defaultValue = DefaultValue::where('id', $defaultValueId)
            ->where('dmc_id', $dmcId)
            ->firstOrFail();

        $countryNames = $this->getAccessibleCountryNames($dmcId, $user);
        $countries = !empty($countryNames)
            ? Country::whereIn('name', $countryNames)->orderBy('name')->get(['name', 'country_id'])
            : collect();
        $citiesByCountry = $this->getCitiesGroupedByCountry($countryNames);

        $services = $this->fetchServicesForType(
            $dmcId,
            $defaultValue->name,
            $defaultValue->country,
            $defaultValue->city
        );

        return view('default-values.edit', compact(
            'defaultValue',
            'countries',
            'citiesByCountry',
            'services',
            'dmcId'
        ));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);

        if (!$dmcId) {
            return redirect()->back()->with('error', 'DMC ID not found.')->withInput();
        }

        $defaultValueId = Crypt::decrypt($id);
        $defaultValue = DefaultValue::where('id', $defaultValueId)
            ->where('dmc_id', $dmcId)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|string',
            'status' => 'required|integer|in:0,1',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $country = trim($request->country);
        $city = trim($request->city);

        $duplicate = DefaultValue::where('dmc_id', $dmcId)
            ->where('name', $defaultValue->name)
            ->where('country', $country)
            ->where('city', $city)
            ->where('id', '!=', $defaultValue->id)
            ->first();

        if ($duplicate) {
            return redirect()->back()
                ->with('error', 'Another default ' . $defaultValue->name . ' already exists for ' . $city . ' (' . $country . ').')
                ->withInput();
        }

        $defaultValue->update([
            'country' => $country,
            'city' => $city,
            'service_id' => $request->service_id,
            'status' => $request->status,
        ]);

        return redirect()->route('default-values.index')
            ->with('success', 'Default value updated successfully.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);

        if (!$dmcId) {
            return redirect()->back()->with('error', 'Access denied. DMC ID not found.');
        }

        $defaultValueId = Crypt::decrypt($id);
        $defaultValue = DefaultValue::where('id', $defaultValueId)
            ->where('dmc_id', $dmcId)
            ->firstOrFail();

        $defaultValue->delete();

        return redirect()->route('default-values.index')
            ->with('success', 'Default value deleted successfully.');
    }

    /**
     * AJAX: services for type + country + city
     */
    public function getServices(Request $request)
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);
        $type = $request->type;
        $country = $request->input('country');
        $city = $request->input('city');

        if (!$dmcId || !$type) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        if (!in_array($type, $this->allTypes, true)) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $services = $this->fetchServicesForType($dmcId, $type, $country, $city);

        return response()->json($services->values());
    }
}
