<?php

namespace App\Http\Controllers\APi;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\User;
use App\Models\Agent;
use Illuminate\Http\Request;
use App\Models\Agency;
use App\Helpers\CountryHelper;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getCity(Request $request)
    {
        $countryName = $request->header('country');

        $country = Country::where('name', $countryName)->first();

        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        $cities = City::where('country', $countryName)->pluck('name');

        return response()->json([
            'cities' => $cities
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getDmcs(Request $request)
    {
        $agentId = $request->input('agent_id');
        if (!$agentId) {
            return response()->json(['error' => 'agent_id is required'], 400);
        }

        $agent = Agent::where('agent_id', $agentId)->first();
        if (!$agent) {
            return response()->json(['error' => 'Agent not found'], 404);
        }

        if (!$agent->agency_id) {
            return response()->json(['error' => 'Agency not found'], 404);
        }

        $agency = Agency::where('agency_id', $agent->agency_id)->first();
        if (!$agency) {
            return response()->json(['error' => 'Agency not found'], 404);
        }

        $agentDmcIds = $agency->dmc_id;

        if (is_string($agentDmcIds) && str_starts_with(trim($agentDmcIds), '[')) {
            $agentDmcIds = json_decode($agentDmcIds, true);
        } elseif (is_string($agentDmcIds)) {
            $agentDmcIds = explode(',', $agentDmcIds);
        }

        if (!is_array($agentDmcIds)) {
            $agentDmcIds = $agentDmcIds ? [$agentDmcIds] : [];
        }

        $agentDmcIds = array_values(array_unique(array_map('intval', array_filter($agentDmcIds, function ($id) {
            return $id !== null && $id !== '' && (int) $id > 0;
        }))));

        $dmcColumns = ['userId', 'salutation', 'name', 'company_name', 'email', 'phone', 'country', 'logo', 'address', 'zone_on', 'price_hide'];
        $dmcsQuery = User::select($dmcColumns)
            ->where('role_id', 11)
            ->whereIn('dmcId', $agentDmcIds ?: [0]);

        $requestCountries = [];
        if ($request->filled('country')) {
            $countryParam = $request->country;

            if (is_string($countryParam) && str_starts_with(trim($countryParam), '[')) {
                $cleanedParam = str_replace(["''", "'"], '"', $countryParam);
                $requestCountries = json_decode($cleanedParam, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['error' => 'Invalid country format'], 400);
                }
            } elseif (is_array($countryParam)) {
                $requestCountries = $countryParam;
            } else {
                $requestCountries = [$countryParam];
            }

            if (!is_array($requestCountries)) {
                $requestCountries = [$requestCountries];
            }

            $requestCountries = array_values(array_filter($requestCountries, function ($country) {
                return $country !== null && trim((string) $country) !== '';
            }));

            if (empty($requestCountries)) {
                return response()->json(['error' => 'No valid countries provided'], 400);
            }

            $normalizedCountries = array_map(function ($country) {
                return preg_replace('/\s+/', '', (string) $country);
            }, $requestCountries);

            $quoted = array_map(function ($country) {
                return '"' . str_replace('"', '\\"', $country) . '"';
            }, $normalizedCountries);

            $dmcsQuery->whereRaw(
                "string_to_array(regexp_replace(country, '\\s+', '', 'g'), ',') && ?",
                ['{' . implode(',', $quoted) . '}']
            );
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $dmcsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);
        $dmcs = $dmcsQuery->paginate($perPage, ['*'], 'page', $page);

        $dmcs->getCollection()->transform(function ($dmc) {
            $dmc->zone_on = $dmc->zone_on ?? false;
            $dmc->price_hide = $dmc->price_hide ?? false;
            return $dmc;
        });

        return response()->json($dmcs);
    }

    public function dmcCount(Request $request){
        $agentId = $request->input('agent_id');
        if (!$agentId) {
            return response()->json(['error' => 'agent_id is required'], 400);
        }

        $agent = Agent::where('agent_id', $agentId)->first();
        if (!$agent) {
            return response()->json(['error' => 'Agent not found'], 404);
        }

        $agency = Agency::where('agency_id', $agent->agency_id)->first();
        if (!$agency) {
            return response()->json(['error' => 'Agency not found'], 404);
        }

        $agentDmcIds = $agency->dmc_id;

        // Handle JSON array or comma-separated string
        if (is_string($agentDmcIds) && strpos($agentDmcIds, '[') === 0) {
            $agentDmcIds = json_decode($agentDmcIds, true);
        } else if (is_string($agentDmcIds)) {
            $agentDmcIds = explode(',', $agentDmcIds);
        }

        if (!is_array($agentDmcIds)) {
            $agentDmcIds = $agentDmcIds ? [$agentDmcIds] : [];
        }

        $agentDmcIds = array_values(array_map('intval', array_filter($agentDmcIds)));

        $dmcs = [];
        $dmcCount = 1;
        foreach ($agentDmcIds as $dmcId) {
            $dmcUsers = User::where('dmcId', $dmcId)->where('role_id', 11)->get();
            foreach ($dmcUsers as $dmcUser) {
                $dmcs[] = [
                    'dmc_count' => $dmcCount++,
                    'dmc_id' => $dmcUser->dmcId,
                    'dmc_logo' => $dmcUser->logo,
                    'dmc_company_name' => $dmcUser->company_name,
                    'dmc_name' => $dmcUser->name,
                    'dmc_country' => $dmcUser->country,
                ];
            }
        }

        return response()->json($dmcs);
    }

    /**
     * City autocomplete for Pro/Lite multi-city: agent → agency DMCs → master DMC countries → cities.
     */
    public function cityCountry(Request $request)
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));
        $agentId = $request->input('agent_id');

        if (!$agentId) {
            return response()->json(['error' => 'agent_id is required'], 400);
        }

        if (mb_strlen($search) < 3) {
            return response()->json(['error' => 'Please provide at least 3 characters'], 400);
        }

        $agent = Agent::where('agent_id', $agentId)->first();
        if (!$agent) {
            return response()->json(['error' => 'Agent not found'], 404);
        }

        if (!$agent->agency_id) {
            return response()->json(['error' => 'Agency not found'], 404);
        }


        $agency = Agency::where('agency_id', $agent->agency_id)->first();
        if (!$agency) {
            return response()->json(['error' => 'Agency not found'], 404);
        }

        $agentDmcIds = $this->parseIdList($agency->dmc_id);
        $dmcUsers = collect();
        if (!empty($agentDmcIds)) {
            $dmcUsers = User::where('role_id', 11)
                ->where(function ($q) use ($agentDmcIds) {
                    $q->whereIn('userId', $agentDmcIds)
                        ->orWhereIn('dmcId', $agentDmcIds);
                })
                ->get();
        }

        $countryNames = [];
        foreach ($dmcUsers as $dmcUser) {
            $countryNames = array_merge(
                $countryNames,
                $this->getMasterDmcCountryNamesForDmc((int) $dmcUser->userId)
            );
        }
        $countryNames = array_values(array_unique(array_filter(array_map('trim', $countryNames))));

        if (empty($countryNames)) {
            return response()->json([
                'agency' => [
                    'agency_id' => $agency->agency_id,
                    'agency_name' => $agency->agency_name ?? '',
                ],
                'countries' => [],
                'results' => [],
                'count' => 0,
            ]);
        }

        $needle = mb_strtolower($search, 'UTF-8');
        $cities = City::query()
            ->whereIn('country', $countryNames)
            ->whereRaw('LOWER(name) LIKE ?', [$needle . '%'])
            ->orderBy('name')
            ->limit(100)
            ->get(['city_id', 'name', 'country']);

        $formattedResults = $cities->map(function ($city) {
            $attrs = $city->getAttributes();
            $countryName = (string) ($attrs['country'] ?? '');
            $cityName = (string) ($city->name ?? '');
            $cityId = (string) ($city->city_id ?? $city->id ?? '');

            return [
                'id' => $cityId,
                'city_id' => $city->city_id ?? $city->id,
                'city' => $cityName,
                'country' => $countryName,
                'country_code' => CountryHelper::getCountryCode($countryName),
                'formatted' => $countryName !== '' ? ($cityName . ', ' . $countryName) : $cityName,
                'text' => $countryName !== '' ? ($cityName . ' (' . $countryName . ')') : $cityName,
            ];
        })->values();

        return response()->json([
            'agency' => [
                'agency_id' => $agency->agency_id,
                'agency_name' => $agency->agency_name ?? '',
            ],
            'countries' => $countryNames,
            'results' => $formattedResults,
            'count' => $formattedResults->count(),
        ]);
    }

    /**
     * @param  mixed  $value
     * @return array<int, int>
     */
    private function parseIdList($value): array
    {
        if (is_string($value) && str_starts_with(trim($value), '[')) {
            $value = json_decode($value, true);
        } elseif (is_string($value)) {
            $value = explode(',', $value);
        }

        if (!is_array($value)) {
            $value = $value ? [$value] : [];
        }

        return array_values(array_unique(array_map('intval', array_filter($value, function ($id) {
            return $id !== null && $id !== '' && (int) $id > 0;
        }))));
    }

    /**
     * Country names from the DMC's master DMC profile (comma-separated on users.country).
     * Same rule as Pro form and Single Tour Lite city search.
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

    public function getCountry(){
        $country = Country::select('name', 'country_code')
            ->orderBy('name')
            ->get();

        return response()->json($country);
    }
}
