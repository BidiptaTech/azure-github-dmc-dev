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

        $agentDmcIds = $this->parseIdList($agency->dmc_id);
        $candidates = $this->getAgencyDmcUsers($agentDmcIds, true);

        $selectedCities = $this->resolveSelectedCities($request);
        $requestCountries = $this->countriesFromSelectedCities($selectedCities);

        $matchingUserIds = [];
        $masterCountriesByUserId = [];
        foreach ($candidates as $dmcUser) {
            if ($this->isThirdPartyDmc($dmcUser)) {
                continue;
            }

            $dmcCountries = $this->getMasterDmcCountryNamesForDmc((int) $dmcUser->userId);
            $masterCountriesByUserId[(int) $dmcUser->userId] = $dmcCountries;

            if (!empty($requestCountries) && !$this->masterDmcCoversAllCountries($dmcCountries, $requestCountries)) {
                continue;
            }

            $matchingUserIds[] = (int) $dmcUser->userId;
        }
        $matchingUserIds = array_values(array_unique($matchingUserIds));

        $dmcColumns = [
            'userId', 'dmcId', 'salutation', 'name', 'company_name', 'email', 'phone',
            'country', 'logo', 'address', 'zone_on', 'price_hide', 'thirdparty', 'thirdparty_enabled',
        ];
        $dmcsQuery = User::select($dmcColumns)
            ->where('role_id', 11)
            ->whereIn('userId', $matchingUserIds ?: [0]);

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

        $dmcs->getCollection()->transform(function ($dmc) use ($masterCountriesByUserId, $selectedCities) {
            $dmc->zone_on = $dmc->zone_on ?? false;
            $dmc->price_hide = $dmc->price_hide ?? false;
            $dmc->thirdparty = strtolower(trim((string) ($dmc->thirdparty ?? 'no'))) === 'yes' ? 'yes' : 'no';
            $dmc->thirdparty_enabled = strtolower(trim((string) ($dmc->thirdparty_enabled ?? 'no'))) === 'yes' ? 'yes' : 'no';
            $dmc->is_third_party = $dmc->thirdparty === 'yes';
            $dmc->master_countries = $masterCountriesByUserId[(int) $dmc->userId] ?? [];
            $dmc->selected_cities = $selectedCities;
            return $dmc;
        });

        $payload = $dmcs->toArray();
        $payload['selected_cities'] = $selectedCities;
        $payload['countries'] = $requestCountries;

        return response()->json($payload);
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
        $dmcUsers = $this->getAgencyDmcUsers($agentDmcIds, true);

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
     * Same rule as Single Tour Lite create form: Multi City is blocked when thirdparty=yes.
     */
    private function isThirdPartyDmc(?User $dmcUser): bool
    {
        if (!$dmcUser) {
            return false;
        }

        return strtolower(trim((string) ($dmcUser->thirdparty ?? 'no'))) === 'yes';
    }

    /**
     * @param  array<int, int>  $agentDmcIds
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function getAgencyDmcUsers(array $agentDmcIds, bool $excludeThirdParty = false)
    {
        if (empty($agentDmcIds)) {
            return collect();
        }

        $query = User::where('role_id', 11)
            ->where(function ($q) use ($agentDmcIds) {
                $q->whereIn('userId', $agentDmcIds)
                    ->orWhereIn('dmcId', $agentDmcIds);
            });

        if ($excludeThirdParty) {
            $query->where(function ($q) {
                $q->whereNull('thirdparty')
                    ->orWhereRaw("LOWER(TRIM(thirdparty::text)) <> ?", ['yes']);
            });
        }

        return $query->get();
    }

    /**
     * Resolve frontend-chosen cities into city + country rows for DMC mapping.
     * Accepts city-country search results, city names, "City (Country)", or city_id.
     *
     * @return array<int, array{city_id: mixed, city: string, country: string, formatted: string}>
     */
    private function resolveSelectedCities(Request $request): array
    {
        $selected = [];
        $seen = [];

        $add = function (array $row) use (&$selected, &$seen) {
            $city = trim((string) ($row['city'] ?? ''));
            $country = trim((string) ($row['country'] ?? ''));
            $cityId = $row['city_id'] ?? null;
            if ($city === '' && $country === '' && empty($cityId)) {
                return;
            }

            $key = mb_strtolower($city . '|' . $country . '|' . (string) ($cityId ?? ''), 'UTF-8');
            if (isset($seen[$key])) {
                return;
            }
            $seen[$key] = true;

            $selected[] = [
                'city_id' => $cityId,
                'city' => $city,
                'country' => $country,
                'formatted' => $country !== ''
                    ? ($city !== '' ? ($city . ', ' . $country) : $country)
                    : $city,
            ];
        };

        $cityIds = array_merge(
            $this->parseIdList($request->input('city_id')),
            $this->parseIdList($request->input('city_ids'))
        );
        if (!empty($cityIds)) {
            foreach (City::whereIn('city_id', $cityIds)->get(['city_id', 'name', 'country']) as $city) {
                $attrs = $city->getAttributes();
                $add([
                    'city_id' => $city->city_id,
                    'city' => (string) $city->name,
                    'country' => (string) ($attrs['country'] ?? ''),
                ]);
            }
        }

        foreach (['cities', 'city'] as $key) {
            if (!$request->filled($key)) {
                continue;
            }
            foreach ($this->normalizeCityInput($request->input($key)) as $item) {
                if (is_array($item)) {
                    $cityName = trim((string) ($item['city'] ?? $item['name'] ?? ''));
                    $countryName = trim((string) ($item['country'] ?? ''));
                    $text = trim((string) ($item['text'] ?? $item['formatted'] ?? ''));
                    $itemCityId = $item['city_id'] ?? $item['id'] ?? null;

                    if ($cityName === '' && $text !== '') {
                        $parsed = $this->parseCityCountryLabel($text);
                        $cityName = $parsed['city'];
                        $countryName = $countryName !== '' ? $countryName : $parsed['country'];
                    } elseif ($cityName !== '' && $countryName === '') {
                        $parsed = $this->parseCityCountryLabel($cityName);
                        $cityName = $parsed['city'];
                        $countryName = $parsed['country'];
                    }

                    if (!empty($itemCityId) && (int) $itemCityId > 0) {
                        $fromId = $this->findCityRowById((int) $itemCityId);
                        if ($fromId) {
                            $add($fromId);
                            continue;
                        }
                    }

                    $fromName = $this->findCityRowByName($cityName, $countryName);
                    $add($fromName ?: [
                        'city_id' => null,
                        'city' => $cityName,
                        'country' => $countryName,
                    ]);
                    continue;
                }

                $parsed = $this->parseCityCountryLabel(trim((string) $item));
                $fromName = $this->findCityRowByName($parsed['city'], $parsed['country']);
                if ($fromName) {
                    $add($fromName);
                    continue;
                }

                $country = $parsed['country'];
                if ($country === '' && $parsed['city'] !== '') {
                    $country = (string) (City::whereRaw('LOWER(name) = ?', [mb_strtolower($parsed['city'], 'UTF-8')])->value('country') ?? '');
                    if ($country === '') {
                        $country = $parsed['city'];
                    }
                }
                $add([
                    'city_id' => null,
                    'city' => $parsed['city'],
                    'country' => $country,
                ]);
            }
        }

        if (empty($selected)) {
            foreach (['country', 'countries'] as $key) {
                if (!$request->filled($key)) {
                    continue;
                }
                foreach ($this->parseFlexibleList($request->input($key)) as $label) {
                    $fromCity = $this->findCityRowByName($label, '');
                    if ($fromCity) {
                        $add($fromCity);
                        continue;
                    }
                    $add([
                        'city_id' => null,
                        'city' => '',
                        'country' => $label,
                    ]);
                }
            }
        }

        return array_values($selected);
    }

    /**
     * @param  array<int, array{country?: string}>  $selectedCities
     * @return array<int, string>
     */
    private function countriesFromSelectedCities(array $selectedCities): array
    {
        $countries = [];
        foreach ($selectedCities as $row) {
            $country = trim((string) ($row['country'] ?? ''));
            if ($country !== '') {
                $countries[] = $country;
            }
        }

        return array_values(array_unique($countries));
    }

    /**
     * @param  mixed  $value
     * @return array<int, mixed>
     */
    private function normalizeCityInput($value): array
    {
        if (is_string($value) && str_starts_with(trim($value), '[')) {
            $cleaned = str_replace(["''", "'"], '"', $value);
            $decoded = json_decode($cleaned, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return array_values($decoded);
            }
        }

        if (is_array($value)) {
            return array_values($value);
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $labels = $this->expandCityLabels(trim($value));
        return array_values($labels);
    }

    /**
     * @return array<int, string>
     */
    private function expandCityLabels(string $value): array
    {
        if (preg_match('/^.+\(.+\)$/', $value)) {
            return [$value];
        }

        $parts = array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/', $value) ?: [])));
        if (count($parts) === 2) {
            $maybeCountry = mb_strtolower($parts[1], 'UTF-8');
            $isCountry = Country::whereRaw('LOWER(name) = ?', [$maybeCountry])->exists()
                || City::whereRaw('LOWER(country) = ?', [$maybeCountry])->exists();
            if ($isCountry) {
                return [$value];
            }
        }

        return $parts;
    }

    /**
     * @return array{city: string, country: string}
     */
    private function parseCityCountryLabel(string $label): array
    {
        $label = trim($label);
        if (preg_match('/^(.+?)\s*\((.+)\)$/', $label, $m)) {
            return ['city' => trim($m[1]), 'country' => trim($m[2])];
        }
        if (preg_match('/^(.+?)\s*,\s*(.+)$/', $label, $m)) {
            return ['city' => trim($m[1]), 'country' => trim($m[2])];
        }

        return ['city' => $label, 'country' => ''];
    }

    /**
     * @return array{city_id: mixed, city: string, country: string}|null
     */
    private function findCityRowById(int $cityId): ?array
    {
        $city = City::where('city_id', $cityId)->first(['city_id', 'name', 'country']);
        if (!$city) {
            return null;
        }

        $attrs = $city->getAttributes();
        return [
            'city_id' => $city->city_id,
            'city' => (string) $city->name,
            'country' => (string) ($attrs['country'] ?? ''),
        ];
    }

    /**
     * @return array{city_id: mixed, city: string, country: string}|null
     */
    private function findCityRowByName(string $cityName, string $countryName = ''): ?array
    {
        $cityName = trim($cityName);
        if ($cityName === '') {
            return null;
        }

        $query = City::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($cityName, 'UTF-8')]);
        if ($countryName !== '') {
            $query->whereRaw('LOWER(country) = ?', [mb_strtolower($countryName, 'UTF-8')]);
        }

        $city = $query->first(['city_id', 'name', 'country']);
        if (!$city && $countryName !== '') {
            $city = City::whereRaw('LOWER(name) = ?', [mb_strtolower($cityName, 'UTF-8')])
                ->first(['city_id', 'name', 'country']);
        }
        if (!$city) {
            return null;
        }

        $attrs = $city->getAttributes();
        return [
            'city_id' => $city->city_id,
            'city' => (string) $city->name,
            'country' => (string) ($attrs['country'] ?? ''),
        ];
    }

    /**
     * @param  mixed  $value
     * @return array<int, string>
     */
    private function parseFlexibleList($value): array
    {
        if (is_string($value) && str_starts_with(trim($value), '[')) {
            $cleaned = str_replace(["''", "'"], '"', $value);
            $decoded = json_decode($cleaned, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        } elseif (is_string($value)) {
            $value = preg_split('/\s*,\s*/', $value) ?: [];
        }

        if (!is_array($value)) {
            $value = $value !== null && $value !== '' ? [$value] : [];
        }

        return array_values(array_filter(array_map(static function ($item) {
            return trim((string) $item);
        }, $value), static fn ($item) => $item !== ''));
    }

    /**
     * True when every searched city-country exists on this master DMC's country list.
     *
     * Example: MDMC1 = Australia,Indonesia,India,Singapore
     *          MDMC2 = Malaysia,Singapore,Indonesia,India
     * - Kolkata(India) + Singapore → both MDMCs (common countries)
     * - Kolkata(India) + Sydney(Australia) → MDMC1 only
     * - Sydney(Australia) + Malacca(Malaysia) → none (split across MDMCs)
     *
     * @param  array<int, string>  $masterCountries
     * @param  array<int, string>  $requestedCountries
     */
    private function masterDmcCoversAllCountries(array $masterCountries, array $requestedCountries): bool
    {
        if (empty($requestedCountries)) {
            return true;
        }

        if (empty($masterCountries)) {
            return false;
        }

        $masterNorm = array_values(array_unique(array_filter(array_map(
            static fn ($c) => mb_strtolower(trim((string) $c), 'UTF-8'),
            $masterCountries
        ), static fn ($c) => $c !== '')));

        foreach ($requestedCountries as $country) {
            $needle = mb_strtolower(trim((string) $country), 'UTF-8');
            if ($needle === '') {
                continue;
            }
            if (!in_array($needle, $masterNorm, true)) {
                return false;
            }
        }

        return true;
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
