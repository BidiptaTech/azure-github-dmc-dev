<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class DayLevel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'day_levels';

    protected $guarded = [];

    protected $casts = [
        'hotels'     => 'array',
        'activities' => 'array',
        'inter_city' => 'array',
        'is_inclusion' => 'boolean',
    ];

    protected $attributes = [
        'hotels'     => '[]',
        'activities' => '[]',
        'inter_city' => '[]',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function guide()
    {
        return $this->belongsTo(Guide::class, 'guide_id', 'id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    public function dmc()
    {
        return $this->belongsTo(\App\Models\User::class, 'dmc_id', 'userId');
    }

    public function masterDmc()
    {
        return $this->belongsTo(\App\Models\User::class, 'master_dmc_id', 'userId');
    }

    public function getStructuredPayloadAttribute(): array
    {
        $payload = $this->inter_city;
        if (!is_array($payload) || empty($payload)) {
            // Fallback only when activities actually stores destination nodes.
            // Do NOT treat day activity rows (type/day/detail...) as destinations.
            $activities = $this->activities;
            if ($this->looksLikeDestinationsList($activities)) {
                return [
                    'Master_DMC' => [
                        $this->normalizeMasterNode(
                            [
                                'Master_DMC_id' => (int) $this->master_dmc_id,
                                'destinations'  => $activities,
                            ],
                            (int) $this->master_dmc_id
                        ),
                    ],
                ];
            }

            return [];
        }

        // Already in target root shape.
        if (isset($payload['Master_DMC']) && is_array($payload['Master_DMC'])) {
            return [
                'Master_DMC' => array_map(function ($masterNode) {
                    return $this->normalizeMasterNode(
                        is_array($masterNode) ? $masterNode : [],
                        (int) $this->master_dmc_id
                    );
                }, $payload['Master_DMC']),
            ];
        }

        // Legacy shape from DB: { Master_DMC_id, destinations }
        if (isset($payload['Master_DMC_id']) || isset($payload['destinations'])) {
            return [
                'Master_DMC' => [
                    $this->normalizeMasterNode($payload, (int) $this->master_dmc_id),
                ],
            ];
        }

        // Final fallback: wrap whatever is present.
        return [
            'Master_DMC' => [
                $this->normalizeMasterNode(
                    [
                        'Master_DMC_id' => (int) $this->master_dmc_id,
                        'destinations'  => is_array($this->activities) ? $this->activities : [],
                    ],
                    (int) $this->master_dmc_id
                ),
            ],
        ];
    }

    /**
     * Raw destination nodes as saved in inter_city / activities (with cities[].packages[]).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStoredDestinations(): array
    {
        $ic = $this->inter_city;
        if (is_array($ic) && isset($ic['destinations']) && is_array($ic['destinations'])) {
            return $ic['destinations'];
        }

        $activities = $this->activities;
        if (is_array($activities) && $this->looksLikeDestinationsList($activities)) {
            return $activities;
        }

        return [];
    }

    /**
     * Build form/API payload wrapper from raw stored destination nodes.
     */
    public function buildEditPayloadFromStoredDestinations(array $destinations): array
    {
        return [
            'Master_DMC' => [
                [
                    'Master_DMC_id' => (int) $this->master_dmc_id,
                    'destinations'  => array_values($destinations),
                ],
            ],
        ];
    }

    /**
     * Summaries of distinct packages stored on this row (grouped by package_id).
     *
     * @return array<int, array{package_id: string, cities: array<int, string>, total_days: int, max_day: int, has_stable_id: bool}>
     */
    public function collectPackageSummaries(): array
    {
        $byId = [];

        foreach ($this->getStoredDestinations() as $dest) {
            if (! is_array($dest)) {
                continue;
            }
            foreach ((array) ($dest['cities'] ?? []) as $city) {
                if (! is_array($city)) {
                    continue;
                }
                foreach (array_values((array) ($city['packages'] ?? [])) as $pkgIdx => $pkg) {
                    if (! is_array($pkg)) {
                        continue;
                    }
                    $id = self::packageNodeId($pkg, $pkgIdx);
                    if (! isset($byId[$id])) {
                        $byId[$id] = [
                            'package_id'    => $id,
                            'cities'        => [],
                            'total_days'    => (int) ($pkg['total_days'] ?? $pkg['totalDays'] ?? 0),
                            'max_day'       => 0,
                            'has_stable_id' => self::packageHasStableId($pkg),
                        ];
                    }
                    $cityName = trim((string) ($city['city'] ?? ''));
                    if ($cityName !== '' && ! in_array($cityName, $byId[$id]['cities'], true)) {
                        $byId[$id]['cities'][] = $cityName;
                    }
                    foreach ((array) ($pkg['days'] ?? []) as $dayNode) {
                        if (! is_array($dayNode)) {
                            continue;
                        }
                        $dayNum = (int) ($dayNode['day'] ?? 0);
                        if ($dayNum > $byId[$id]['max_day']) {
                            $byId[$id]['max_day'] = $dayNum;
                        }
                    }
                    $totalDays = (int) ($pkg['total_days'] ?? $pkg['totalDays'] ?? 0);
                    if ($totalDays > $byId[$id]['total_days']) {
                        $byId[$id]['total_days'] = $totalDays;
                    }
                }
            }
        }

        return array_values($byId);
    }

    /**
     * Structured blocks for the show/detail UI (one entry per package).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPackageDisplayBlocks(): array
    {
        $blocks = [];

        foreach ($this->collectPackageSummaries() as $summary) {
            $packageId = (string) ($summary['package_id'] ?? '');
            $cityPlans = [];
            $daysByNumber = [];

            foreach ($this->getStoredDestinations() as $dest) {
                if (! is_array($dest)) {
                    continue;
                }
                foreach ((array) ($dest['cities'] ?? []) as $city) {
                    if (! is_array($city)) {
                        continue;
                    }
                    foreach (array_values((array) ($city['packages'] ?? [])) as $pkgIdx => $pkg) {
                        if (! is_array($pkg)) {
                            continue;
                        }
                        if (self::packageNodeId($pkg, $pkgIdx) !== $packageId) {
                            continue;
                        }

                        $checkIn = (int) ($city['checkin_day'] ?? 0);
                        $checkOut = (int) ($city['checkout_day'] ?? 0);
                        if ($checkIn > 0 && $checkOut >= $checkIn) {
                            $cityPlans[] = [
                                'city'     => (string) ($city['city'] ?? ''),
                                'checkin'  => $checkIn,
                                'checkout' => $checkOut,
                            ];
                        }

                        foreach ((array) ($pkg['days'] ?? []) as $dayNode) {
                            if (! is_array($dayNode)) {
                                continue;
                            }
                            $dayNum = (int) ($dayNode['day'] ?? 0);
                            if ($dayNum <= 0) {
                                continue;
                            }
                            if (! isset($daysByNumber[$dayNum])) {
                                $daysByNumber[$dayNum] = $dayNode;
                            } else {
                                $daysByNumber[$dayNum] = array_replace($daysByNumber[$dayNum], $dayNode);
                            }
                        }
                    }
                }
            }

            ksort($daysByNumber);

            $blocks[] = array_merge($summary, [
                'city_plans' => $cityPlans,
                'days'       => array_values($daysByNumber),
            ]);
        }

        return $blocks;
    }

    public function filterStructuredPayloadToPackage(string $packageId): array
    {
        $packageId = trim($packageId);
        $destinations = $this->getStoredDestinations();

        if ($packageId === '') {
            return $this->buildEditPayloadFromStoredDestinations($destinations);
        }

        $filtered = [];
        foreach ($destinations as $dest) {
            if (! is_array($dest)) {
                continue;
            }
            $filteredDest = self::filterDestinationToPackage($dest, $packageId);
            if ($filteredDest !== null) {
                $filtered[] = $filteredDest;
            }
        }

        return $this->buildEditPayloadFromStoredDestinations($filtered);
    }

    /**
     * @param  mixed  $raw
     */
    public static function unwrapDestinationNode($raw): ?array
    {
        if (! is_array($raw)) {
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

    public static function packageNodeId(array $pkg, int $fallbackIndex = 0): string
    {
        $id = trim((string) ($pkg['package_id'] ?? $pkg['packageId'] ?? ''));

        return $id !== '' ? $id : 'legacy-index-' . $fallbackIndex;
    }

    public static function packageHasStableId(array $pkg): bool
    {
        return trim((string) ($pkg['package_id'] ?? $pkg['packageId'] ?? '')) !== '';
    }

    public static function packageNodeMatchesId(array $pkg, string $packageId): bool
    {
        $id = trim((string) ($pkg['package_id'] ?? $pkg['packageId'] ?? ''));

        return $id !== '' && $id === trim($packageId);
    }

    /**
     * Remove all package nodes matching package_id; drop cities/destinations that become empty.
     */
    public static function removePackageFromDestinations(array $destinations, string $packageId): array
    {
        $packageId = trim($packageId);
        if ($packageId === '') {
            return $destinations;
        }

        $out = [];
        foreach ($destinations as $dest) {
            if (! is_array($dest)) {
                continue;
            }
            $cities = [];
            foreach ((array) ($dest['cities'] ?? []) as $city) {
                if (! is_array($city)) {
                    continue;
                }
                $pkgs = array_values(array_filter(
                    (array) ($city['packages'] ?? []),
                    fn ($pkg) => is_array($pkg) && ! self::packageNodeMatchesId($pkg, $packageId)
                ));
                if ($pkgs === []) {
                    continue;
                }
                $cities[] = array_replace($city, ['packages' => $pkgs]);
            }
            if ($cities === []) {
                continue;
            }
            $destOut = $dest;
            $destOut['cities'] = $cities;
            $out[] = $destOut;
        }

        return $out;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function filterDestinationToPackage(array $dest, string $packageId): ?array
    {
        $cities = [];
        foreach ((array) ($dest['cities'] ?? []) as $city) {
            if (! is_array($city)) {
                continue;
            }
            $pkgs = array_values(array_filter(
                (array) ($city['packages'] ?? []),
                fn ($pkg) => is_array($pkg) && self::packageNodeMatchesId($pkg, $packageId)
            ));
            if ($pkgs === []) {
                continue;
            }
            $cities[] = array_replace($city, ['packages' => $pkgs]);
        }

        if ($cities === []) {
            return null;
        }

        $out = $dest;
        $out['cities'] = $cities;

        return $out;
    }

    /**
     * Match the reference JSON: each package has "days" as an object with "0", "1", "2" keys,
     * not a JSON array (PHP list arrays would encode as [...] and break the contract).
     */
    public static function canonicalizeDestinationsForStorage(array $destinations): array
    {
        return array_map(function ($dest) {
            if (! is_array($dest)) {
                return $dest;
            }
            $out    = $dest;
            $cities = [];
            foreach ((array) ($dest['cities'] ?? []) as $city) {
                if (! is_array($city)) {
                    $cities[] = $city;
                    continue;
                }
                $pkgs = [];
                foreach ((array) ($city['packages'] ?? []) as $pkg) {
                    if (! is_array($pkg)) {
                        $pkgs[] = $pkg;
                        continue;
                    }
                    $pkg2         = $pkg;
                    $pkg2['days'] = self::canonicalizeDaysObjectForStorage($pkg['days'] ?? []);
                    $pkgs[] = $pkg2;
                }
                $cities[] = array_replace($city, ['packages' => $pkgs]);
            }
            $out['cities'] = $cities;

            return $out;
        }, $destinations);
    }

    /**
     * @param  mixed  $days
     */
    public static function canonicalizeDaysObjectForStorage($days): array
    {
        if (! is_array($days) || $days === []) {
            return [];
        }

        $out = [];
        if (array_is_list($days)) {
            foreach ($days as $i => $dayNode) {
                $out[(string) (int) $i] = is_array($dayNode) ? $dayNode : [];
            }
        } else {
            foreach ($days as $k => $dayNode) {
                $out[(string) $k] = is_array($dayNode) ? $dayNode : [];
            }
        }
        uksort($out, fn ($a, $b) => (int) $a <=> (int) $b);

        return $out;
    }

    private function normalizeMasterNode(array $masterNode, int $fallbackMasterId): array
    {
        $masterId = (int) ($masterNode['Master_DMC_id'] ?? $fallbackMasterId);
        $destinations = is_array($masterNode['destinations'] ?? null) ? $masterNode['destinations'] : [];

        return [
            'Master_DMC_id' => $masterId,
            'destinations'  => array_map(function ($destination) {
                return [
                    'DMC' => [
                        $this->normalizeDestinationNode(is_array($destination) ? $destination : []),
                    ],
                ];
            }, $destinations),
        ];
    }

    /**
     * @param mixed $value
     */
    private function looksLikeDestinationsList($value): bool
    {
        if (!is_array($value) || $value === []) {
            return false;
        }

        if (!array_is_list($value)) {
            return isset($value['DMC_id']) || isset($value['cities']);
        }

        $first = $value[0] ?? null;
        return is_array($first) && (isset($first['DMC_id']) || isset($first['cities']));
    }

    private function normalizeDestinationNode(array $destination): array
    {
        $cities = is_array($destination['cities'] ?? null) ? $destination['cities'] : [];
        $dmcId = (int) ($destination['DMC_id'] ?? $this->dmc_id);
        $normalizedCities = array_map(function ($city) {
            return $this->normalizeCityNode(is_array($city) ? $city : []);
        }, $cities);
        $days = $this->collectMergedDaysWithCities($normalizedCities);
        $packages = $this->collectPackagesWithCities($normalizedCities);

        $serviceAndTransportBuckets = $this->buildDmcWideServiceBuckets($days, $destination, $dmcId);
        $normalizedPackages = $packages !== [] ? $packages : [[
            'days' => self::daysMapToJsonObject($days),
        ]];

        $email = trim((string) ($destination['DMC_email'] ?? $destination['email'] ?? ''));
        if ($email === '' && $this->relationLoaded('dmc') && $this->dmc
            && (int) $this->dmc->userId === $dmcId) {
            $email = trim((string) ($this->dmc->email ?? ''));
        }

        $out = ['DMC_id' => $dmcId];
        if ($email !== '') {
            $out['DMC_email'] = $email;
        }
        $out = array_merge($out, [
            'country' => (string) ($destination['country'] ?? ($this->country ?? '')),
            'list_all_services' => $serviceAndTransportBuckets['list_all_services'],
            'list_all_transport' => $serviceAndTransportBuckets['list_all_transport'],
            'packages' => $normalizedPackages,
        ], $this->buildNamedPackageBlocks($normalizedPackages));

        return $this->orderDmcPayloadKeys($out);
    }

    /**
     * Keep DMC_email immediately after DMC_id (JSON key order / readability).
     */
    private function orderDmcPayloadKeys(array $dmc): array
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
         * IMPORTANT:
         * A "package" is a user-created block (duration), not a city bucket.
         * Cities/hotels can vary inside the same package. So we must merge package days
         * across cities instead of creating a new package per city.
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
                $daysObj = (array) ($packageNode['days'] ?? []);
                $identity = $this->inferPackageIdentity($packageNode, $pkgIdx);
                $entry = $packagesByIdentity[$identity] ?? ['meta' => [], 'days' => []];
                $daysByNumber = is_array($entry['days'] ?? null) ? $entry['days'] : [];
                if ($entry['meta'] === []) {
                    $entry['meta'] = $this->extractPackageMeta($packageNode);
                }

                foreach ($daysObj as $dayIdx => $dayNode) {
                    if (!is_array($dayNode)) {
                        continue;
                    }
                    $normalizedDay = $dayNode;
                    if (!is_array($normalizedDay['cities'] ?? null) && $citySummaries !== []) {
                        $normalizedDay['cities'] = self::daysMapToJsonObject($this->indexList($citySummaries));
                    }
                    $normalizedDay['hotels'] = is_array($normalizedDay['hotels'] ?? null) ? $normalizedDay['hotels'] : [];
                    $normalizedDay['attractions'] = is_array($normalizedDay['attractions'] ?? null) ? $normalizedDay['attractions'] : [];
                    $normalizedDay['restaurants'] = is_array($normalizedDay['restaurants'] ?? null) ? $normalizedDay['restaurants'] : [];

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
                        // Merge duplicate day numbers (can happen when city buckets split the same package).
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
                'days' => self::daysMapToJsonObject($daysIndexed),
                ]
            );
            $packages[] = $pkgOut;
        }

        return $packages;
    }

    private function inferPackageIdentity(array $packageNode, int $fallbackIndex = 0): string
    {
        $packageId = trim((string) ($packageNode['package_id'] ?? $packageNode['packageId'] ?? ''));
        if ($packageId !== '') {
            return 'id:' . $packageId;
        }

        return 'index:' . $fallbackIndex;
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

    private function getAllHotelsForDmc(int $dmcId): array
    {
        if ($dmcId <= 0) {
            return [];
        }

        $hotelQuery = Hotel::query()
            ->whereNull('deleted_at');
        $this->applyDmcMappingFilter($hotelQuery, $dmcId);

        $hotels = $hotelQuery
            ->orderBy('name')
            ->get([
                'id',
                'hotel_unique_id',
                'name',
                'city',
                'country',
                'hotel_star_rating',
                'is_active',
                'dmc_id',
            ]);

        $mealMetaByHotel = $this->getHotelMealMetaMap($hotels);

        return $hotels->map(function ($h) use ($mealMetaByHotel) {
                $uniqueId = trim((string) ($h->hotel_unique_id ?? ''));
                $exportHotelId = $uniqueId !== '' ? $uniqueId : (string) ((int) ($h->id ?? 0));
                $meta = $mealMetaByHotel[$exportHotelId]
                    ?? $mealMetaByHotel[(int) ($h->id ?? 0)]
                    ?? ['meal_types' => [], 'dishes' => []];

                return [
                    'hotel_id' => $exportHotelId,
                    'hotel_name' => (string) ($h->name ?? ''),
                    'city' => (string) ($h->city ?? ''),
                    'country' => (string) ($h->country ?? ''),
                    'hotel_star_rating' => (string) ($h->hotel_star_rating ?? ''),
                    'is_active' => (int) ($h->is_active ?? 0),
                    'dmc_id' => $h->dmc_id,
                    'meal_type' => implode(', ', $meta['meal_types']),
                    'meal_types' => $meta['meal_types'],
                    'dish' => implode(', ', $meta['dishes']),
                    'dishes' => $meta['dishes'],
                ];
            })
            ->values()
            ->toArray();
    }

    private function normalizeCityNode(array $city): array
    {
        $packages = is_array($city['packages'] ?? null) ? $city['packages'] : [];
        $out = [
            'city'     => (string) ($city['city'] ?? optional($this->city)->name ?? ''),
            'packages' => array_map(function ($package) {
                return $this->normalizePackageNode(is_array($package) ? $package : []);
            }, $packages),
        ];

        // Keep multi-city day ranges when present in source payload.
        if (array_key_exists('checkin_day', $city)) {
            $out['checkin_day'] = (int) ($city['checkin_day'] ?? 0);
        }
        if (array_key_exists('checkout_day', $city)) {
            $out['checkout_day'] = (int) ($city['checkout_day'] ?? 0);
        }

        return $out;
    }

    private function collectMergedDaysWithCities(array $cities): array
    {
        $allDays = [];
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

            foreach ((array) ($cityNode['packages'] ?? []) as $packageNode) {
                if (!is_array($packageNode)) {
                    continue;
                }
                foreach ((array) ($packageNode['days'] ?? []) as $dayNode) {
                    if (is_array($dayNode)) {
                        $allDays[] = $dayNode;
                    }
                }
            }
        }

        usort($allDays, fn ($a, $b) => (int) ($a['day'] ?? 0) <=> (int) ($b['day'] ?? 0));

        $normalized = [];
        foreach ($allDays as $idx => $dayNode) {
            $normalized[(string) $idx] = [
                'day' => (int) ($dayNode['day'] ?? ($idx + 1)),
                'cities' => self::daysMapToJsonObject($this->indexList($citySummaries)),
                'hotels' => is_array($dayNode['hotels'] ?? null) ? $dayNode['hotels'] : [],
                'attractions' => is_array($dayNode['attractions'] ?? null) ? $dayNode['attractions'] : [],
                'arrivals'    => is_array($dayNode['arrivals'] ?? null) ? $dayNode['arrivals'] : [],
                'departures'  => is_array($dayNode['departures'] ?? null) ? $dayNode['departures'] : [],
                'transfers'   => is_array($dayNode['transfers'] ?? null) ? $dayNode['transfers'] : [],
                'restaurants' => is_array($dayNode['restaurants'] ?? null) ? $dayNode['restaurants'] : [],
                'services'    => is_array($dayNode['services'] ?? null) ? $dayNode['services'] : [],
                'Transfer'    => $this->extractDayTransfers($dayNode),
                'Guide'       => $this->extractDayGuides($dayNode),
            ];
        }

        return $normalized;
    }

    private function buildDmcWideServiceBuckets(array $days, array $destination, int $dmcId): array
    {
        $country = (string) ($destination['country'] ?? ($this->country ?? ''));
        $cities = [];
        foreach ((array) ($destination['cities'] ?? []) as $cityNode) {
            if (is_array($cityNode) && ($cityNode['city'] ?? '') !== '') {
                $cities[] = (string) $cityNode['city'];
            }
        }

        $hotels = $this->getAllHotelsForDmc($dmcId);
        $attractions = $this->getAllAttractionsForDmc($dmcId, $country, $cities);
        $restaurants = $this->getAllRestaurantsForDmc($dmcId, $country, $cities);
        $guides = $this->getAllGuidesForDmc($dmcId, $country, $cities);
        $zoneTransfers = array_values(array_map(function ($zoneRow) {
            if (! is_array($zoneRow)) {
                return $zoneRow;
            }
            $zoneRow['itinerary_day'] = 0;

            return $zoneRow;
        }, $this->getAllTransfersForDmc($dmcId, $country, $cities)));

        // Keep explicit day-level guide data.
        foreach ($days as $dayNode) {
            if (!is_array($dayNode)) {
                continue;
            }
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

    private function getAllAttractionsForDmc(int $dmcId, string $country, array $cities): array
    {
        $query = Attraction::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1);
        $this->applyDmcMappingFilter($query, $dmcId);

        if ($cities !== []) {
            $query->where(function ($q) use ($cities) {
                foreach ($cities as $city) {
                    $q->orWhere('location', 'ilike', '%' . $city . '%');
                }
            });
        } elseif ($country !== '') {
            $query->where('location', 'ilike', '%' . $country . '%');
        }

        $attractions = $query->orderBy('name')->get(['attraction_id', 'name', 'location']);
        $tickets = Ticket::query()
            ->whereNull('deleted_at')
            ->where('dmc_id', $dmcId)
            ->whereIn('attraction_id', $attractions->pluck('attraction_id')->values())
            ->orderBy('name')
            ->get(['ticket_id', 'name', 'attraction_id', 'adult_price']);
        $ticketMap = [];
        foreach ($tickets as $ticket) {
            $attrId = (string) ($ticket->attraction_id ?? '');
            if ($attrId === '') {
                continue;
            }
            $ticketMap[$attrId]['tickets'][] = [
                'ticket_id' => (string) ($ticket->ticket_id ?? ''),
                'ticket_name' => (string) ($ticket->name ?? ''),
                'price' => (float) ($ticket->adult_price ?? 0),
            ];
        }

        return $attractions->map(function ($a) use ($ticketMap) {
            $attrId = (string) ($a->attraction_id ?? '');
            $tickets = array_values(array_filter(
                $ticketMap[$attrId]['tickets'] ?? [],
                fn ($t) => is_array($t) && ($t['ticket_id'] ?? '') !== ''
            ));

            return [
                'attraction_id' => $attrId,
                'name' => (string) ($a->name ?? ''),
                'city' => (string) ($a->location ?? ''),
                'ticket_mapping' => self::buildTicketMappingFromTickets($tickets),
            ];
        })->values()->toArray();
    }

    private function getAllRestaurantsForDmc(int $dmcId, string $country, array $cities): array
    {
        $query = Restaurant::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1);
        $this->applyDmcMappingFilter($query, $dmcId);

        if ($cities !== []) {
            $query->whereIn('city', $cities);
        } elseif ($country !== '') {
            $query->where('country', 'ilike', '%' . $country . '%');
        }

        return $query->orderBy('name')
            ->get(['restaurant_id', 'name', 'city'])
            ->map(fn ($r) => [
                'restaurant_id' => (string) ($r->restaurant_id ?? ''),
                'name' => (string) ($r->name ?? ''),
                'city' => (string) ($r->city ?? ''),
            ])
            ->values()
            ->toArray();
    }

    private function getAllGuidesForDmc(int $dmcId, string $country, array $cities): array
    {
        $query = Guide::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1);
        $this->applyDmcMappingFilter($query, $dmcId, 'dmc_id', null);

        if ($cities !== []) {
            $query->whereIn('city', $cities);
        } elseif ($country !== '') {
            $query->where('country', 'ilike', '%' . $country . '%');
        }

        return $query->orderBy('name')
            ->get(['guide_id', 'name', 'city', 'day_rate'])
            ->map(fn ($g) => [
                'guide_id' => (string) ($g->guide_id ?? ''),
                'guide_name' => (string) ($g->name ?? ''),
                'city' => (string) ($g->city ?? ''),
                'guide_cost' => (float) ($g->day_rate ?? 0),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Zone rows from DB for a DMC (no itinerary city filter). Used when building external payloads outside {@see getStructuredPayloadAttribute}.
     */
    public static function fetchZoneTransfersForDmc(int $dmcId): array
    {
        if ($dmcId <= 0) {
            return [];
        }

        return (new static)->getAllTransfersForDmc($dmcId, '', []);
    }

    private function getAllTransfersForDmc(int $dmcId, string $country, array $cities): array
    {
        $zonesQuery = Zone::query()
            ->where('status', 1);
        $this->applyDmcMappingFilter($zonesQuery, $dmcId, 'dmc_id', null);
        return $zonesQuery
            ->orderBy('zone_id')
            ->get(['zone_id', 'zone_name', 'zone_type', 'city', 'description', 'dmc_id'])
            ->map(function ($zone) {
                return [
                    'type' => 'zone',
                    'zone_id' => (string) ($zone->zone_id ?? ''),
                    'zone_name' => (string) ($zone->zone_name ?? ''),
                    'zone_type' => (string) ($zone->zone_type ?? ''),
                    'city' => (string) ($zone->city ?? ''),
                    'description' => is_string($zone->description ?? null) ? trim(strip_tags((string) $zone->description)) : '',
                    'dmc_id' => (string) ($zone->dmc_id ?? ''),
                ];
            })
            ->values()
            ->toArray();
    }

    private function applyDmcMappingFilter(
        Builder $query,
        int $dmcId,
        string $dmcColumn = 'dmc_id',
        ?string $zoneAssignmentsColumn = 'zone_assignments'
    ): void {
        $id = (string) $dmcId;
        $query->where(function ($q) use ($id, $dmcColumn, $zoneAssignmentsColumn) {
            $q->whereRaw("COALESCE({$dmcColumn}::text, '') LIKE ?", ['%' . $id . '%']);
            if ($zoneAssignmentsColumn !== null) {
                $q->orWhereRaw(
                    "COALESCE({$zoneAssignmentsColumn}::text, '') LIKE ? OR COALESCE({$zoneAssignmentsColumn}::text, '') LIKE ?",
                    ['%"dmc_id":' . $id . '%', '%"dmc_id":"' . $id . '"%']
                );
            }
        });
    }

    private function getHotelMealMetaMap($hotels): array
    {
        $hotelUniqueIds = $hotels->pluck('hotel_unique_id')->filter()->values();
        if ($hotelUniqueIds->isEmpty()) {
            return [];
        }

        $mealCols = ['hotel_id'];
        foreach ([
            'breakfast_included', 'lunch_included', 'dinner_included',
            'breakfast', 'lunch', 'dinner',
            'breakfast_type', 'lunch_type', 'dinner_type',
        ] as $col) {
            if (Schema::hasColumn('rooms', $col)) {
                $mealCols[] = $col;
            }
        }
        if (count($mealCols) === 1) {
            return [];
        }

        $rows = Room::query()
            ->whereIn('hotel_id', $hotelUniqueIds)
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->get($mealCols);

        $byUnique = [];
        foreach ($rows as $room) {
            $u = (string) ($room->hotel_id ?? '');
            if ($u === '') {
                continue;
            }
            $meals = $byUnique[$u]['meal_types'] ?? [];
            $dishes = $byUnique[$u]['dishes'] ?? [];
            if ($this->truthyMeal($room->breakfast_included ?? null) || $this->truthyMeal($room->breakfast ?? null)) {
                $meals['Breakfast'] = true;
            }
            if ($this->truthyMeal($room->lunch_included ?? null) || $this->truthyMeal($room->lunch ?? null)) {
                $meals['Lunch'] = true;
            }
            if ($this->truthyMeal($room->dinner_included ?? null) || $this->truthyMeal($room->dinner ?? null)) {
                $meals['Dinner'] = true;
            }
            if ($this->truthyMeal($room->breakfast_type ?? null)) {
                $meals['Breakfast'] = true;
                $dishes[trim((string) $room->breakfast_type)] = true;
            }
            if ($this->truthyMeal($room->lunch_type ?? null)) {
                $meals['Lunch'] = true;
                $dishes[trim((string) $room->lunch_type)] = true;
            }
            if ($this->truthyMeal($room->dinner_type ?? null)) {
                $meals['Dinner'] = true;
                $dishes[trim((string) $room->dinner_type)] = true;
            }
            $byUnique[$u] = [
                'meal_types' => $meals,
                'dishes' => $dishes,
            ];
        }

        $mapped = [];
        foreach ($hotels as $hotel) {
            $uniqueId = trim((string) ($hotel->hotel_unique_id ?? ''));
            $exportKey = $uniqueId !== '' ? $uniqueId : (string) ((int) ($hotel->id ?? 0));
            $meta = $byUnique[$uniqueId] ?? ['meal_types' => [], 'dishes' => []];
            $mapped[$exportKey] = [
                'meal_types' => array_values(array_keys($meta['meal_types'] ?? [])),
                'dishes' => array_values(array_keys($meta['dishes'] ?? [])),
            ];
        }

        return $mapped;
    }

    private function truthyMeal($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (float) $value > 0;
        }
        if (is_string($value)) {
            $v = strtolower(trim($value));
            return $v !== '' && $v !== '0' && $v !== 'no' && $v !== 'false';
        }
        return false;
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

    private function toNamedMap(array $items, string $prefix): array
    {
        $mapped = [];
        foreach (array_values($items) as $index => $item) {
            $mapped[$prefix . ' ' . ($index + 1)] = is_array($item) ? $item : [];
        }
        return $mapped;
    }

    private function indexList(array $items): array
    {
        $indexed = [];
        foreach (array_values($items) as $index => $item) {
            $indexed[(string) $index] = $item;
        }
        return $indexed;
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

    private function normalizePackageNode(array $package): array
    {
        $daysRaw = $package['days'] ?? [];
        $daysArray = is_array($daysRaw) ? $daysRaw : [];
        $daysAssoc = [];

        if (array_is_list($daysArray)) {
            foreach ($daysArray as $i => $dayNode) {
                $daysAssoc[(string) (int) $i] = $this->normalizeDayNode(
                    is_array($dayNode) ? $dayNode : [],
                    (int) $i + 1
                );
            }
        } else {
            // Object-style days: { "0": {...}, "1": {...} } per canonical JSON
            foreach ($daysArray as $k => $dayNode) {
                $kStr     = (string) $k;
                $fallback = is_numeric($kStr) ? (int) $kStr + 1 : 1;
                $daysAssoc[$kStr] = $this->normalizeDayNode(
                    is_array($dayNode) ? $dayNode : [],
                    $fallback
                );
            }
        }

        if ($daysAssoc !== []) {
            uksort($daysAssoc, fn ($a, $b) => (int) $a <=> (int) $b);
        }

        // PHP merges keys 0 and "0"; json_encode() would turn consecutive int keys into a JSON
        // array. Use stdClass with explicit "0","1",… property names so JSON is always an object.
        $out = ['days' => self::daysMapToJsonObject($daysAssoc)];
        foreach (['package_id', 'packageId', 'total_days', 'totalDays'] as $field) {
            if (array_key_exists($field, $package)) {
                $out[$field] = $package[$field];
            }
        }

        return $out;
    }

    /**
     * @param  array<string|int, mixed>  $dayMap
     */
    private static function daysMapToJsonObject(array $dayMap): \stdClass
    {
        $o = new \stdClass;
        foreach ($dayMap as $k => $dayNode) {
            $kStr       = (string) $k;
            $o->{$kStr} = is_array($dayNode) ? $dayNode : [];
        }

        return $o;
    }

    private function normalizeDayNode(array $dayNode, int $fallbackDay): array
    {
        $servicesRaw = $dayNode['services'] ?? [];
        $services = is_array($servicesRaw) ? $servicesRaw : [];

        // Backward compatibility: older payloads used "activities" on day node.
        if ($services === [] && is_array($dayNode['activities'] ?? null)) {
            $services = $this->normalizeNamedMap($dayNode['activities'] ?? [], 'Activity', 'activity_id', 'name');
        }

        return [
            'day'         => (int) ($dayNode['day'] ?? $fallbackDay),
            'hotels'      => $this->normalizeNamedMap($dayNode['hotels'] ?? [], 'Hotel', 'hotel_id', 'hotel_name', true),
            'attractions' => $this->normalizeNamedMap($dayNode['attractions'] ?? [], 'Attraction', 'attraction_id', 'name'),
            'arrivals'    => is_array($dayNode['arrivals'] ?? null) ? $dayNode['arrivals'] : [],
            'departures'  => is_array($dayNode['departures'] ?? null) ? $dayNode['departures'] : [],
            'transfers'   => is_array($dayNode['transfers'] ?? null) ? $dayNode['transfers'] : [],
            'restaurants' => $this->normalizeNamedMap($dayNode['restaurants'] ?? [], 'Restaurant', 'restaurant_id', 'name'),
            'services'    => $this->normalizeDayServices($services),
        ];
    }

    private function normalizeDayServices(array $services): array
    {
        if ($services === []) {
            return [];
        }

        if (array_is_list($services)) {
            $mapped = [];
            foreach ($services as $i => $row) {
                $row = is_array($row) ? $row : [];
                $mapped['Service ' . ($i + 1)] = $row;
            }
            return $mapped;
        }

        return $services;
    }

    private function normalizeNamedMap($raw, string $prefix, string $idField, string $nameField, bool $includePrice = false): array
    {
        if (! is_array($raw) || $raw === []) {
            return [];
        }

        if (array_is_list($raw)) {
            $mapped = [];
            foreach ($raw as $i => $row) {
                $row              = is_array($row) ? $row : [];
                $entry            = $this->orderNamedEntry($row, $idField, $nameField, $includePrice, $prefix);
                $mapped[$prefix . ' ' . ($i + 1)] = $entry;
            }

            return $mapped;
        }

        $out = [];
        foreach ($raw as $label => $row) {
            if (! is_array($row)) {
                $out[$label] = $row;
                continue;
            }
            $out[$label] = $this->orderNamedEntry($row, $idField, $nameField, $includePrice, $prefix);
        }

        return $out;
    }

    /**
     * Enforce the reference JSON field order: hotel_id, hotel_name, price; *_id then name;
     * never price-first from legacy JS or the DB.
     */
    private function orderNamedEntry(array $row, string $idField, string $nameField, bool $includePrice, string $prefix): array
    {
        if (str_starts_with($prefix, 'Hotel')) {
            return self::normalizeRawHotelRow($row, $includePrice);
        }

        if (str_starts_with($prefix, 'Attraction')) {
            $entry = [
                'attraction_id' => (string) ($row['attraction_id'] ?? ''),
                'name'          => (string) ($row['name'] ?? ''),
                'ticket_mapping' => self::buildTicketMapping($row),
            ];
            if (array_key_exists('city', $row)) {
                $entry['city'] = strip_tags((string) ($row['city'] ?? ''));
            }
            if (array_key_exists('transfer', $row) && is_array($row['transfer'])) {
                $entry['transfer'] = $this->sanitizeStructuredLeafValues($row['transfer']);
            }

            return $entry;
        }

        if (str_starts_with($prefix, 'Restaurant')) {
            return [
                'restaurant_id' => (string) ($row['restaurant_id'] ?? ''),
                'name'          => (string) ($row['name'] ?? ''),
            ];
        }

        if (str_starts_with($prefix, 'Activity')) {
            return [
                'activity_id' => (string) ($row['activity_id'] ?? ''),
                'name'        => (string) ($row['name'] ?? ''),
            ];
        }

        // Fallback: id field then name field then price
        $base = [
            $idField   => (string) ($row[$idField] ?? ''),
            $nameField => (string) ($row[$nameField] ?? ''),
        ];
        if ($includePrice) {
            $base['price'] = (float) ($row['price'] ?? 0);
        }

        return $base;
    }

    /**
     * Normalize attraction ticket fields for API / combined JSON output.
     *
     * @param  array<string, mixed>  $attraction
     * @return array<string, mixed>
     */
    public static function normalizeAttractionForResponse(array $attraction): array
    {
        $mapping = self::buildTicketMapping($attraction);
        unset(
            $attraction['ticket_ids'],
            $attraction['ticket_names'],
            $attraction['ticket_id'],
            $attraction['ticket_name'],
            $attraction['ticket_prices']
        );
        $attraction['ticket_mapping'] = $mapping;

        return $attraction;
    }

    /**
     * Apply ticket_mapping normalization to list_all_services and package day attractions.
     *
     * @param  array<string, mixed>  $destination
     * @return array<string, mixed>
     */
    public static function transformDestinationAttractionsForApi(array $destination): array
    {
        if (isset($destination['list_all_services']['attractions'])
            && is_array($destination['list_all_services']['attractions'])) {
            $destination['list_all_services']['attractions'] = self::normalizeAttractionsMap(
                $destination['list_all_services']['attractions']
            );
        }

        self::walkDestinationPackageDays($destination, function (string $pkgKey, int|string $pkgIdx, int|string $dayIdx, array &$day) {
            if (! isset($day['attractions']) || ! is_array($day['attractions'])) {
                return;
            }
            $day['attractions'] = self::normalizeAttractionsMap($day['attractions']);
        });

        return $destination;
    }

    /**
     * Add human-readable labels for pickup/drop location references (e.g. port:42 → port name).
     *
     * @param  array<string, mixed>  $destination
     * @return array<string, mixed>
     */
    public static function transformDestinationLocationsForApi(array $destination): array
    {
        $dmcId = (int) ($destination['DMC_id'] ?? 0);
        $refs = self::collectLocationReferencesFromDestination($destination);
        if ($refs === []) {
            return $destination;
        }

        $labels = self::resolveLocationLabels($refs, $dmcId, $destination);

        if (isset($destination['list_all_services']['transfers']) && is_array($destination['list_all_services']['transfers'])) {
            $destination['list_all_services']['transfers'] = self::enrichTransferLocationLabelsInMap(
                $destination['list_all_services']['transfers'],
                $labels
            );
        }

        if (isset($destination['list_all_transport']) && is_array($destination['list_all_transport'])) {
            foreach ($destination['list_all_transport'] as $bucketKey => $transfers) {
                if (! is_array($transfers)) {
                    continue;
                }
                $destination['list_all_transport'][$bucketKey] = self::enrichTransferLocationLabelsInMap(
                    $transfers,
                    $labels
                );
            }
        }

        self::walkDestinationPackageDays($destination, function (string $pkgKey, int|string $pkgIdx, int|string $dayIdx, array &$day) use ($labels) {
            if (isset($day['hotels']) && is_array($day['hotels'])) {
                foreach ($day['hotels'] as $hotelKey => $hotel) {
                    if (! is_array($hotel)) {
                        continue;
                    }
                    $day['hotels'][$hotelKey] = self::enrichHotelTransferLocationLabels(
                        self::normalizeRawHotelRow($hotel),
                        $labels
                    );
                }
            }

            if (isset($day['attractions']) && is_array($day['attractions'])) {
                foreach ($day['attractions'] as $attrKey => $attraction) {
                    if (! is_array($attraction) || ! isset($attraction['transfer']) || ! is_array($attraction['transfer'])) {
                        continue;
                    }
                    $day['attractions'][$attrKey]['transfer'] = self::enrichTransferLocationLabels(
                        $attraction['transfer'],
                        $labels
                    );
                }
            }

            if (isset($day['services']) && is_array($day['services'])) {
                foreach ($day['services'] as $serviceKey => $service) {
                    if (! is_array($service) || ! isset($service['transfer']) || ! is_array($service['transfer'])) {
                        continue;
                    }
                    $day['services'][$serviceKey]['transfer'] = self::enrichTransferLocationLabels(
                        $service['transfer'],
                        $labels
                    );
                }
            }

            foreach (['arrivals', 'departures', 'transfers'] as $transferBucket) {
                if (! isset($day[$transferBucket]) || ! is_array($day[$transferBucket])) {
                    continue;
                }
                foreach ($day[$transferBucket] as $legKey => $leg) {
                    if (! is_array($leg) || ! isset($leg['transfer']) || ! is_array($leg['transfer'])) {
                        continue;
                    }
                    $day[$transferBucket][$legKey]['transfer'] = self::enrichTransferLocationLabels(
                        $leg['transfer'],
                        $labels
                    );
                }
            }
        });

        return $destination;
    }

    /**
     * @param  array<string, mixed>  $destination
     * @param  callable(string, int|string, int|string, array): void  $callback
     */
    private static function walkDestinationPackageDays(array &$destination, callable $callback): void
    {
        foreach ($destination as $pkgKey => $packages) {
            if ($pkgKey !== 'packages' && ! str_starts_with((string) $pkgKey, 'packages')) {
                continue;
            }
            if (! is_array($packages)) {
                continue;
            }
            foreach ($packages as $pkgIdx => $package) {
                if (! is_array($package) || ! isset($package['days'])) {
                    continue;
                }

                $days = $package['days'];
                $daysIsObject = $days instanceof \stdClass;
                $daysList = $daysIsObject
                    ? json_decode(json_encode($days), true)
                    : $days;

                if (! is_array($daysList)) {
                    continue;
                }

                foreach ($daysList as $dayIdx => $day) {
                    if ($day instanceof \stdClass) {
                        $day = json_decode(json_encode($day), true);
                    }
                    if (! is_array($day)) {
                        continue;
                    }
                    $callback((string) $pkgKey, $pkgIdx, $dayIdx, $day);
                    $daysList[$dayIdx] = $day;
                }

                $destination[$pkgKey][$pkgIdx]['days'] = $daysIsObject
                    ? json_decode(json_encode($daysList))
                    : $daysList;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $destination
     * @return list<string>
     */
    private static function collectLocationReferencesFromDestination(array $destination): array
    {
        $refs = [];

        $collect = static function ($value) use (&$collect, &$refs): void {
            if (! is_array($value)) {
                if (is_string($value) && self::isLocationReference($value)) {
                    $refs[$value] = true;
                }

                return;
            }
            foreach ($value as $k => $v) {
                if (in_array($k, [
                    'pickup_location',
                    'drop_location',
                    'transfer_pickup',
                    'transfer_drop',
                    'pickup_location_id',
                ], true) && is_string($v) && self::isLocationReference($v)) {
                    $refs[$v] = true;
                }
                $collect($v);
            }
        };

        if (isset($destination['list_all_services']) && is_array($destination['list_all_services'])) {
            $collect($destination['list_all_services']);
        }
        if (isset($destination['list_all_transport']) && is_array($destination['list_all_transport'])) {
            $collect($destination['list_all_transport']);
        }
        self::walkDestinationPackageDays($destination, static function (string $pkgKey, int|string $pkgIdx, int|string $dayIdx, array &$day) use ($collect): void {
            $collect($day);
        });

        return array_keys($refs);
    }

    private static function isLocationReference(string $value): bool
    {
        return (bool) preg_match('/^(port|hotel|zone|attraction|restaurant):.+$/', trim($value));
    }

    /**
     * @param  list<string>  $refs
     * @param  array<string, mixed>  $destination
     * @return array<string, string>
     */
    private static function resolveLocationLabels(array $refs, int $dmcId, array $destination): array
    {
        $labels = [];
        self::seedLocationLabelsFromDestinationCatalog($destination, $labels);

        $byType = [
            'port' => [],
            'hotel' => [],
            'zone' => [],
            'attraction' => [],
            'restaurant' => [],
        ];

        foreach ($refs as $ref) {
            if (isset($labels[$ref])) {
                continue;
            }
            if (! preg_match('/^(port|hotel|zone|attraction|restaurant):(.+)$/', $ref, $matches)) {
                continue;
            }
            $byType[$matches[1]][$matches[2]] = true;
        }

        if ($byType['port'] !== []) {
            $ports = Port::query()
                ->whereNull('deleted_at')
                ->whereIn('port_id', array_keys($byType['port']))
                ->get(['port_id', 'port_name']);
            foreach ($ports as $port) {
                $labels['port:' . (string) $port->port_id] = (string) ($port->port_name ?? '');
            }
        }

        if ($byType['hotel'] !== []) {
            $hotelIds = array_map('strval', array_keys($byType['hotel']));
            $numericHotelIds = array_values(array_filter(
                $hotelIds,
                static fn (string $id) => $id !== '' && ctype_digit($id)
            ));
            $uniqueHotelIds = array_values(array_diff($hotelIds, $numericHotelIds));

            $hotels = Hotel::query()
                ->whereNull('deleted_at')
                ->where(function ($q) use ($numericHotelIds, $uniqueHotelIds) {
                    if ($numericHotelIds !== []) {
                        $q->whereIn('id', array_map('intval', $numericHotelIds));
                    }
                    if ($uniqueHotelIds !== []) {
                        if ($numericHotelIds !== []) {
                            $q->orWhereIn('hotel_unique_id', $uniqueHotelIds);
                        } else {
                            $q->whereIn('hotel_unique_id', $uniqueHotelIds);
                        }
                    }
                })
                ->get(['id', 'hotel_unique_id', 'name', 'city']);
            foreach ($hotels as $hotel) {
                $label = (string) ($hotel->name ?? '');
                $city = trim((string) ($hotel->city ?? ''));
                if ($city !== '') {
                    $label .= ' - ' . $city;
                }
                $labels['hotel:' . (string) $hotel->id] = $label;
                $uniqueId = trim((string) ($hotel->hotel_unique_id ?? ''));
                if ($uniqueId !== '' && $uniqueId !== '0') {
                    $labels['hotel:' . $uniqueId] = $label;
                }
            }
        }

        if ($byType['zone'] !== []) {
            $zoneQuery = Zone::query()->whereIn('zone_id', array_keys($byType['zone']));
            if ($dmcId > 0) {
                $zoneQuery->where('dmc_id', $dmcId);
            }
            foreach ($zoneQuery->get(['zone_id', 'zone_name', 'city']) as $zone) {
                $zoneName = (string) ($zone->zone_name ?? '');
                $zoneCity = trim((string) ($zone->city ?? ''));
                $labels['zone:' . (string) $zone->zone_id] = $zoneCity !== ''
                    ? $zoneName . ' - ' . $zoneCity
                    : $zoneName;
            }
        }

        if ($byType['attraction'] !== []) {
            $attractions = Attraction::query()
                ->whereNull('deleted_at')
                ->whereIn('attraction_id', array_keys($byType['attraction']))
                ->get(['attraction_id', 'name', 'location']);
            foreach ($attractions as $attraction) {
                $name = (string) ($attraction->name ?? '');
                $city = trim((string) ($attraction->location ?? ''));
                $labels['attraction:' . (string) $attraction->attraction_id] = $city !== ''
                    ? $name . ' - ' . $city
                    : $name;
            }
        }

        if ($byType['restaurant'] !== []) {
            $restaurants = Restaurant::query()
                ->whereNull('deleted_at')
                ->whereIn('restaurant_id', array_keys($byType['restaurant']))
                ->get(['restaurant_id', 'name', 'city']);
            foreach ($restaurants as $restaurant) {
                $name = (string) ($restaurant->name ?? '');
                $city = trim((string) ($restaurant->city ?? ''));
                $labels['restaurant:' . (string) $restaurant->restaurant_id] = $city !== ''
                    ? $name . ' - ' . $city
                    : $name;
            }
        }

        return array_filter($labels, fn ($label) => trim((string) $label) !== '');
    }

    /**
     * @param  array<string, mixed>  $destination
     * @param  array<string, string>  $labels
     */
    private static function seedLocationLabelsFromDestinationCatalog(array $destination, array &$labels): void
    {
        $services = is_array($destination['list_all_services'] ?? null) ? $destination['list_all_services'] : [];

        foreach ((array) ($services['hotels'] ?? []) as $hotel) {
            if (! is_array($hotel)) {
                continue;
            }
            $hotelId = self::resolveHotelUniqueId((string) ($hotel['hotel_id'] ?? ''));
            $name = (string) ($hotel['hotel_name'] ?? $hotel['name'] ?? '');
            if ($hotelId !== '' && $name !== '') {
                $labels['hotel:' . $hotelId] = $name;
            }
        }

        foreach ((array) ($services['attractions'] ?? []) as $attraction) {
            if (! is_array($attraction)) {
                continue;
            }
            $attrId = (string) ($attraction['attraction_id'] ?? '');
            $name = (string) ($attraction['name'] ?? '');
            if ($attrId !== '' && $name !== '') {
                $labels['attraction:' . $attrId] = $name;
            }
        }

        foreach ((array) ($services['restaurants'] ?? []) as $restaurant) {
            if (! is_array($restaurant)) {
                continue;
            }
            $restaurantId = (string) ($restaurant['restaurant_id'] ?? '');
            $name = (string) ($restaurant['name'] ?? '');
            if ($restaurantId !== '' && $name !== '') {
                $labels['restaurant:' . $restaurantId] = $name;
            }
        }

        foreach ((array) ($services['transfers'] ?? []) as $transfer) {
            if (! is_array($transfer) || ($transfer['type'] ?? '') !== 'zone') {
                continue;
            }
            $zoneId = (string) ($transfer['zone_id'] ?? '');
            $zoneName = (string) ($transfer['zone_name'] ?? '');
            if ($zoneId !== '' && $zoneName !== '') {
                $labels['zone:' . $zoneId] = $zoneName;
            }
        }

        if (isset($destination['list_all_transport']['zones']) && is_array($destination['list_all_transport']['zones'])) {
            foreach ($destination['list_all_transport']['zones'] as $transfer) {
                if (! is_array($transfer)) {
                    continue;
                }
                $zoneId = (string) ($transfer['zone_id'] ?? '');
                $zoneName = (string) ($transfer['zone_name'] ?? '');
                if ($zoneId !== '' && $zoneName !== '') {
                    $labels['zone:' . $zoneId] = $zoneName;
                }
            }
        }
    }

    /**
     * @param  array<string|int, mixed>  $transfers
     * @param  array<string, string>  $labels
     * @return array<string|int, mixed>
     */
    private static function enrichTransferLocationLabelsInMap(array $transfers, array $labels): array
    {
        $out = [];
        foreach ($transfers as $key => $transfer) {
            $out[$key] = is_array($transfer)
                ? self::enrichTransferLocationLabels($transfer, $labels)
                : $transfer;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $transfer
     * @param  array<string, string>  $labels
     * @return array<string, mixed>
     */
    private static function enrichTransferLocationLabels(array $transfer, array $labels): array
    {
        foreach (['pickup_location', 'drop_location'] as $field) {
            $ref = trim((string) ($transfer[$field] ?? ''));
            if ($ref === '') {
                continue;
            }
            $labelField = $field . '_label';
            if (trim((string) ($transfer[$labelField] ?? '')) !== '') {
                continue;
            }
            if (isset($labels[$ref])) {
                $transfer[$labelField] = $labels[$ref];
            }
        }

        if (isset($transfer['additional_transfers']) && is_array($transfer['additional_transfers'])) {
            foreach ($transfer['additional_transfers'] as $idx => $extra) {
                if (is_array($extra)) {
                    $transfer['additional_transfers'][$idx] = self::enrichTransferLocationLabels($extra, $labels);
                }
            }
        }

        return $transfer;
    }

    /**
     * @param  array<string, mixed>  $hotel
     * @param  array<string, string>  $labels
     * @return array<string, mixed>
     */
    private static function enrichHotelTransferLocationLabels(array $hotel, array $labels): array
    {
        foreach (['transfer_pickup' => 'transfer_pickup_label', 'transfer_drop' => 'transfer_drop_label'] as $refField => $labelField) {
            $ref = trim((string) ($hotel[$refField] ?? ''));
            if ($ref === '') {
                continue;
            }
            if (trim((string) ($hotel[$labelField] ?? '')) !== '') {
                continue;
            }
            if (isset($labels[$ref])) {
                $hotel[$labelField] = $labels[$ref];
            }
        }

        return $hotel;
    }

    /**
     * @param  array<string|int, mixed>  $attractions
     * @return array<string|int, mixed>
     */
    private static function normalizeAttractionsMap(array $attractions): array
    {
        $out = [];
        foreach ($attractions as $key => $attraction) {
            $out[$key] = is_array($attraction)
                ? self::normalizeAttractionForResponse($attraction)
                : $attraction;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<array{ticket_id: string, ticket_name: string, price?: float}>
     */
    public static function buildTicketMapping(array $row): array
    {
        if (isset($row['ticket_mapping']) && is_array($row['ticket_mapping'])) {
            if (array_is_list($row['ticket_mapping'])) {
                return self::sanitizeTicketMappingList($row['ticket_mapping']);
            }

            $mapping = [];
            foreach ($row['ticket_mapping'] as $ticketId => $ticketName) {
                if ($ticketId === '' || $ticketId === null) {
                    continue;
                }
                $mapping[] = [
                    'ticket_id' => (string) $ticketId,
                    'ticket_name' => is_array($ticketName)
                        ? (string) ($ticketName['ticket_name'] ?? $ticketName['name'] ?? '')
                        : (string) $ticketName,
                ];
            }

            return self::sanitizeTicketMappingList($mapping);
        }

        $ids = [];
        $names = [];

        if (! empty($row['ticket_ids']) && is_array($row['ticket_ids'])) {
            $ids = array_values(array_filter(
                array_map(fn ($v) => trim((string) $v), $row['ticket_ids']),
                fn ($v) => $v !== ''
            ));
        } elseif (! empty($row['ticket_id'])) {
            $ids = array_values(array_filter(
                array_map('trim', explode(',', (string) $row['ticket_id'])),
                fn ($v) => $v !== ''
            ));
        }

        if (! empty($row['ticket_names']) && is_array($row['ticket_names'])) {
            $names = array_map(fn ($v) => trim((string) $v), $row['ticket_names']);
        } elseif (! empty($row['ticket_name'])) {
            $ticketName = trim((string) $row['ticket_name']);
            $names = count($ids) > 1
                ? array_map('trim', explode(',', $ticketName))
                : [$ticketName];
        }

        $tickets = [];
        foreach ($ids as $index => $ticketId) {
            $tickets[] = [
                'ticket_id' => $ticketId,
                'ticket_name' => (string) ($names[$index] ?? ''),
                'price' => 0.0,
            ];
        }

        $mapping = self::buildTicketMappingFromTickets($tickets);

        if (count($mapping) === 1 && array_key_exists('price', $row)) {
            $price = (float) ($row['price'] ?? 0);
            if ($price > 0 && ! isset($mapping[0]['price'])) {
                $mapping[0]['price'] = $price;
            }
        }

        return $mapping;
    }

    /**
     * @param  list<array{ticket_id?: string, ticket_name?: string, price?: float}>  $tickets
     * @return list<array{ticket_id: string, ticket_name: string, price?: float}>
     */
    private static function buildTicketMappingFromTickets(array $tickets): array
    {
        $mapping = [];
        foreach ($tickets as $ticket) {
            if (! is_array($ticket)) {
                continue;
            }
            $ticketId = trim((string) ($ticket['ticket_id'] ?? ''));
            if ($ticketId === '') {
                continue;
            }
            $entry = [
                'ticket_id' => $ticketId,
                'ticket_name' => (string) ($ticket['ticket_name'] ?? $ticket['name'] ?? ''),
            ];
            $price = (float) ($ticket['price'] ?? $ticket['adult_price'] ?? 0);
            if ($price > 0) {
                $entry['price'] = $price;
            }
            $mapping[] = $entry;
        }

        return $mapping;
    }

    /**
     * @param  list<mixed>  $list
     * @return list<array{ticket_id: string, ticket_name: string, price?: float}>
     */
    private static function sanitizeTicketMappingList(array $list): array
    {
        $out = [];
        foreach ($list as $item) {
            if (! is_array($item)) {
                continue;
            }
            $ticketId = trim((string) ($item['ticket_id'] ?? ''));
            if ($ticketId === '') {
                continue;
            }
            $entry = [
                'ticket_id' => $ticketId,
                'ticket_name' => (string) ($item['ticket_name'] ?? $item['name'] ?? ''),
            ];
            if (array_key_exists('price', $item)) {
                $price = (float) $item['price'];
                if ($price > 0) {
                    $entry['price'] = $price;
                }
            }
            $out[] = $entry;
        }

        return $out;
    }

    /**
     * Recursively strip HTML from string leaves so nested payloads (e.g. transfers) round-trip safely.
     *
     * @param  mixed  $value
     * @return mixed
     */
    private function sanitizeStructuredLeafValues($value)
    {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $this->sanitizeStructuredLeafValues($v);
            }

            return $out;
        }
        if (is_string($value)) {
            return strip_tags($value);
        }

        return $value;
    }

    /**
     * Flat day-level JSON rows: one entry per package (see day-level-combined.json contract).
     *
     * @param  iterable<int, DayLevel>  $rows
     * @return list<array<string, mixed>>
     */
    public static function collectFlatPackageExportsFromRows(iterable $rows): array
    {
        $byPackageId = [];

        foreach ($rows as $row) {
            if (! $row instanceof self) {
                continue;
            }
            foreach ($row->buildFlatPackageExports() as $entry) {
                $packageId = trim((string) ($entry['package_id'] ?? ''));
                if ($packageId !== '') {
                    $byPackageId[$packageId] = $entry;
                } else {
                    $byPackageId[] = $entry;
                }
            }
        }

        $list = array_values($byPackageId);
        usort($list, fn ($a, $b) => strcmp((string) ($b['package_id'] ?? ''), (string) ($a['package_id'] ?? '')));

        return self::hydrateFlatExportDmcEmails($list);
    }

    /**
     * @param  list<array<string, mixed>>  $entries
     * @return list<array<string, mixed>>
     */
    private static function hydrateFlatExportDmcEmails(array $entries): array
    {
        $missingDmcIds = [];
        foreach ($entries as $entry) {
            if (trim((string) ($entry['DMC_email'] ?? '')) !== '') {
                continue;
            }
            $dmcId = (int) ($entry['DMC_id'] ?? 0);
            if ($dmcId > 0) {
                $missingDmcIds[$dmcId] = true;
            }
        }

        if ($missingDmcIds === []) {
            return $entries;
        }

        $emailsByDmcId = \App\Models\User::query()
            ->whereIn('userId', array_keys($missingDmcIds))
            ->pluck('email', 'userId');

        foreach ($entries as $i => $entry) {
            if (trim((string) ($entry['DMC_email'] ?? '')) !== '') {
                continue;
            }
            $dmcId = (int) ($entry['DMC_id'] ?? 0);
            $email = trim((string) ($emailsByDmcId[$dmcId] ?? ''));
            if ($email === '') {
                continue;
            }
            $entry['DMC_email'] = $email;
            $entries[$i] = self::orderFlatPackageExportKeys($entry);
        }

        return $entries;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function buildFlatPackageExports(): array
    {
        $payload = $this->structured_payload;
        $masterNodes = is_array($payload['Master_DMC'] ?? null) ? $payload['Master_DMC'] : [];
        if ($masterNodes === []) {
            return [];
        }

        $exports = [];
        foreach ($masterNodes as $masterNode) {
            if (! is_array($masterNode)) {
                continue;
            }
            $masterId = (int) ($masterNode['Master_DMC_id'] ?? $this->master_dmc_id);
            foreach ((array) ($masterNode['destinations'] ?? []) as $destWrap) {
                $destination = self::unwrapDestinationNode($destWrap);
                if (! is_array($destination) || (int) ($destination['DMC_id'] ?? 0) <= 0) {
                    continue;
                }
                $exports = array_merge(
                    $exports,
                    $this->buildFlatExportsForDestination($destination, $masterId)
                );
            }
        }

        return $exports;
    }

    /**
     * @param  array<string, mixed>  $destination
     * @return list<array<string, mixed>>
     */
    private function buildFlatExportsForDestination(array $destination, int $masterId): array
    {
        $dmcId = (int) ($destination['DMC_id'] ?? $this->dmc_id);
        if ($dmcId <= 0) {
            return [];
        }

        $email = trim((string) ($destination['DMC_email'] ?? $destination['email'] ?? ''));
        if ($email === '' && $this->relationLoaded('dmc') && $this->dmc && (int) $this->dmc->userId === $dmcId) {
            $email = trim((string) ($this->dmc->email ?? ''));
        }

        $country = (string) ($destination['country'] ?? ($this->country ?? ''));
        $citySummaries = self::filterCitySummaries(self::extractCitySummariesFromDestination($destination));

        $destination = self::transformDestinationAttractionsForApi($destination);
        $destination = self::transformDestinationLocationsForApi($destination);

        $packages = $this->resolvePackagesForExport($destination, $citySummaries);
        if ($citySummaries === [] && $packages !== []) {
            $citySummaries = self::filterCitySummaries(
                self::extractCitySummariesFromPackage($packages[0])
            );
        }

        $cityNames = [];
        foreach ($citySummaries as $summary) {
            $name = trim((string) ($summary['city'] ?? ''));
            if ($name !== '' && ! in_array($name, $cityNames, true)) {
                $cityNames[] = $name;
            }
        }
        if ($cityNames === []) {
            foreach ($this->collectPackageSummaries() as $summary) {
                foreach ((array) ($summary['cities'] ?? []) as $name) {
                    $name = trim((string) $name);
                    if ($name !== '' && ! in_array($name, $cityNames, true)) {
                        $cityNames[] = $name;
                    }
                }
            }
        }
        if ($packages === []) {
            return [];
        }

        $rawAllServices = $this->buildRawAllServicesCatalog($dmcId, $country, $cityNames);
        $rawAllServicesJson = json_encode($rawAllServices, JSON_UNESCAPED_SLASHES);
        if ($rawAllServicesJson === false) {
            $rawAllServicesJson = '{}';
        }

        $entries = [];
        foreach ($packages as $package) {
            if (! is_array($package)) {
                continue;
            }
            $packageId = trim((string) ($package['package_id'] ?? $package['packageId'] ?? ''));
            if ($packageId === '') {
                continue;
            }

            $totalDays = (int) ($package['total_days'] ?? $package['totalDays'] ?? 0);
            if ($totalDays <= 0) {
                $totalDays = self::inferTotalDaysFromPackageDays($package);
            }

            $rawPackage = self::buildRawPackagePayload($package, $citySummaries);
            $rawPackageJson = json_encode($rawPackage, JSON_UNESCAPED_SLASHES);
            if ($rawPackageJson === false) {
                continue;
            }

            $entry = [
                'id'               => $packageId,
                'country'          => $country,
                'city'             => $cityNames,
                'total_days'       => $totalDays,
                'Master_DMC_id'    => $masterId,
                'DMC_id'           => $dmcId,
                'package_id'       => $packageId,
                'raw_package'      => $rawPackageJson,
                'raw_all_services' => $rawAllServicesJson,
            ];
            if ($email !== '') {
                $entry['DMC_email'] = $email;
            }

            $entries[] = self::orderFlatPackageExportKeys($entry);
        }

        return $entries;
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    private static function orderFlatPackageExportKeys(array $entry): array
    {
        $ordered = [];
        foreach (['id', 'DMC_email', 'country', 'city', 'total_days', 'Master_DMC_id', 'DMC_id', 'package_id', 'raw_package', 'raw_all_services'] as $key) {
            if (array_key_exists($key, $entry)) {
                $ordered[$key] = $entry[$key];
            }
        }
        foreach ($entry as $key => $value) {
            if (! array_key_exists($key, $ordered)) {
                $ordered[$key] = $value;
            }
        }

        return $ordered;
    }

    /**
     * @param  array<string, mixed>  $destination
     * @return list<array{city: string, city_checkin: string, city_checkout: string}>
     */
    public static function extractCitySummariesFromDestination(array $destination): array
    {
        $summaries = [];
        foreach ((array) ($destination['cities'] ?? []) as $cityNode) {
            if (! is_array($cityNode)) {
                continue;
            }
            $summaries[] = [
                'city'          => (string) ($cityNode['city'] ?? ''),
                'city_checkin'  => (string) ($cityNode['checkin_day'] ?? $cityNode['city_checkin'] ?? ''),
                'city_checkout' => (string) ($cityNode['checkout_day'] ?? $cityNode['city_checkout'] ?? ''),
            ];
        }

        return $summaries;
    }

    /**
     * @param  list<array{city: string, city_checkin: string, city_checkout: string}>  $summaries
     * @return list<array{city: string, city_checkin: string, city_checkout: string}>
     */
    private static function filterCitySummaries(array $summaries): array
    {
        return array_values(array_filter($summaries, function ($row) {
            if (! is_array($row)) {
                return false;
            }
            $city = trim((string) ($row['city'] ?? ''));

            return $city !== '' && strcasecmp($city, 'Unknown') !== 0;
        }));
    }

    /**
     * @param  array<string, mixed>  $package
     * @return list<array{city: string, city_checkin: string, city_checkout: string}>
     */
    private static function extractCitySummariesFromPackage(array $package): array
    {
        foreach (self::flattenPackageDayNodes($package['days'] ?? []) as $dayNode) {
            if (! is_array($dayNode)) {
                continue;
            }
            $list = self::rawCitiesListFromDayNode($dayNode, []);

            return $list;
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $destination
     * @param  list<array{city: string, city_checkin: string, city_checkout: string}>  $citySummaries
     * @return list<array<string, mixed>>
     */
    private function resolvePackagesForExport(array $destination, array $citySummaries): array
    {
        $cities = is_array($destination['cities'] ?? null) ? $destination['cities'] : [];
        if ($cities !== []) {
            return $this->collectPackagesWithCities($cities);
        }

        return self::unwrapPackagesList($destination);
    }

    /**
     * @param  array<string, mixed>  $destination
     * @return list<array<string, mixed>>
     */
    public static function unwrapPackagesList(array $destination): array
    {
        $found = [];
        $main = $destination['packages'] ?? null;
        if (is_array($main) && $main !== []) {
            if (isset($main['days']) || isset($main['package_id']) || isset($main['packageId'])) {
                $found[] = $main;
            } else {
                foreach ($main as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    if (isset($item['days']) || isset($item['package_id']) || isset($item['packageId'])) {
                        $found[] = $item;
                        continue;
                    }
                    if (isset($item[0]) && is_array($item[0])) {
                        $found[] = $item[0];
                    }
                }
            }
        }

        foreach ($destination as $key => $value) {
            if (! is_string($key) || ! preg_match('/^packages\s+\d+$/', $key) || ! is_array($value)) {
                continue;
            }
            foreach ($value as $item) {
                if (! is_array($item)) {
                    continue;
                }
                if (isset($item['days']) || isset($item['package_id']) || isset($item['packageId'])) {
                    $found[] = $item;
                    continue;
                }
                if (isset($item[0]) && is_array($item[0])) {
                    $found[] = $item[0];
                }
            }
        }

        return $found;
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  list<array{city: string, city_checkin: string, city_checkout: string}>  $citySummaries
     * @return array{package_id: string, total_days: int, days: list<array<string, mixed>>}
     */
    public static function buildRawPackagePayload(array $package, array $citySummaries): array
    {
        $packageId = trim((string) ($package['package_id'] ?? $package['packageId'] ?? ''));
        $totalDays = (int) ($package['total_days'] ?? $package['totalDays'] ?? 0);
        if ($totalDays <= 0) {
            $totalDays = self::inferTotalDaysFromPackageDays($package);
        }

        $daysList = [];
        foreach (self::flattenPackageDayNodes($package['days'] ?? []) as $dayNode) {
            if (! is_array($dayNode)) {
                continue;
            }
            $daysList[] = self::formatDayForRawPackageExport($dayNode, $citySummaries);
        }

        return [
            'package_id' => $packageId,
            'total_days' => $totalDays,
            'days'       => $daysList,
        ];
    }

    /**
     * Deep-cast JSON object graphs (stdClass) into plain arrays.
     *
     * @return array<string, mixed>
     */
    public static function castMixedToArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_object($value)) {
            $decoded = json_decode(json_encode($value), true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * @param  mixed  $daysRaw
     * @return list<array<string, mixed>>
     */
    public static function flattenPackageDayNodes($daysRaw): array
    {
        $daysRaw = self::castMixedToArray($daysRaw);
        if ($daysRaw === []) {
            return [];
        }

        $nodes = [];
        if (array_is_list($daysRaw)) {
            foreach ($daysRaw as $dayNode) {
                $dayNode = self::castMixedToArray($dayNode);
                if ($dayNode !== []) {
                    $nodes[] = $dayNode;
                }
            }
        } else {
            $keys = array_keys($daysRaw);
            usort($keys, fn ($a, $b) => (int) $a <=> (int) $b);
            foreach ($keys as $key) {
                $dayNode = self::castMixedToArray($daysRaw[$key] ?? null);
                if ($dayNode !== []) {
                    $nodes[] = $dayNode;
                }
            }
        }

        usort($nodes, fn ($a, $b) => (int) ($a['day'] ?? 0) <=> (int) ($b['day'] ?? 0));

        return $nodes;
    }

    /**
     * @param  array<string, mixed>  $dayNode
     * @param  list<array{city: string, city_checkin: string, city_checkout: string}>  $citySummaries
     * @return array<string, mixed>
     */
    public static function formatDayForRawPackageExport(array $dayNode, array $citySummaries): array
    {
        $dayNode = self::castMixedToArray($dayNode);
        $day = (int) ($dayNode['day'] ?? 0);
        $hotels = self::formatRawHotelsMap(self::castMixedToArray($dayNode['hotels'] ?? []), true);
        $attractions = is_array($dayNode['attractions'] ?? null)
            ? self::normalizeAttractionsMap($dayNode['attractions'])
            : [];
        $restaurants = is_array($dayNode['restaurants'] ?? null) ? $dayNode['restaurants'] : [];
        $arrivals = is_array($dayNode['arrivals'] ?? null) ? $dayNode['arrivals'] : [];
        $departures = is_array($dayNode['departures'] ?? null) ? $dayNode['departures'] : [];
        $transfers = is_array($dayNode['transfers'] ?? null) ? $dayNode['transfers'] : [];
        $services = is_array($dayNode['services'] ?? null) ? $dayNode['services'] : [];

        $cities = self::rawCitiesListFromDayNode($dayNode, $citySummaries);

        return [
            'day'         => $day,
            'hotels'      => $hotels,
            'attractions' => $attractions,
            'arrivals'    => $arrivals,
            'departures'  => $departures,
            'transfers'   => $transfers,
            'restaurants' => $restaurants,
            'services'    => $services,
            'cities'      => $cities,
        ];
    }

    /**
     * @param  array<string, mixed>  $hotels
     * @return array<string, mixed>
     */
    /**
     * Map legacy hotels.id to rooms.hotel_id (hotel_unique_id) for JSON export.
     */
    public static function resolveHotelUniqueId(string $hotelId): string
    {
        $hotelId = trim($hotelId);
        if ($hotelId === '') {
            return '';
        }
        if (! ctype_digit($hotelId)) {
            return $hotelId;
        }

        static $byNumericId = null;
        if ($byNumericId === null) {
            $byNumericId = [];
            foreach (Hotel::query()->whereNull('deleted_at')->get(['id', 'hotel_unique_id']) as $hotel) {
                $unique = trim((string) ($hotel->hotel_unique_id ?? ''));
                $byNumericId[(string) ((int) ($hotel->id ?? 0))] = $unique !== '' ? $unique : (string) ((int) ($hotel->id ?? 0));
            }
        }

        return $byNumericId[$hotelId] ?? $hotelId;
    }

    private static function normalizeTransferLocationToken(string $value): string
    {
        $value = trim(strip_tags($value));
        if ($value === '' || ! str_starts_with($value, 'hotel:')) {
            return $value;
        }

        $id = substr($value, 6);

        return 'hotel:' . self::resolveHotelUniqueId($id);
    }

    /**
     * Canonical hotel row for structured + Azure raw_package JSON.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function normalizeRawHotelRow(array $row, bool $includePrice = true): array
    {
        $hotelId = self::resolveHotelUniqueId((string) ($row['hotel_id'] ?? ''));
        $city = (string) ($row['city'] ?? $row['city_name'] ?? '');

        $bookedDay = (int) ($row['booked_day'] ?? $row['checkin_day'] ?? 0);

        $starRating = trim((string) ($row['hotel_star_rating'] ?? $row['cat'] ?? ''));
        $entry = [
            'hotel_id'   => $hotelId,
            'hotel_name' => (string) ($row['hotel_name'] ?? ''),
            'city'       => $city,
            'room_id'    => (string) ($row['room_id'] ?? ''),
            'room_type'  => (string) ($row['room_type'] ?? ''),
            'bed_id'     => (string) ($row['bed_id'] ?? ''),
            'bed_type'   => (string) ($row['bed_type'] ?? ''),
            'meal_plan'  => (string) ($row['meal_plan'] ?? ''),
        ];
        if ($starRating !== '') {
            $entry['hotel_star_rating'] = $starRating;
        } else {
            $entry = self::enrichHotelRowStarRating($entry);
        }
        if ($includePrice) {
            $entry['price'] = (float) ($row['price'] ?? 0);
        }
        $entry['night'] = (int) ($row['night'] ?? 1);
        $entry['meal_type'] = (string) ($row['meal_type'] ?? '');
        $entry['guide_required'] = (string) ($row['guide_required'] ?? 'No');
        $entry['arrival_departure'] = (string) ($row['arrival_departure'] ?? 'No');
        $entry['arrival_departure_type'] = (string) ($row['arrival_departure_type'] ?? '');
        $entry['priority'] = (int) ($row['priority'] ?? 1);
        if ($bookedDay > 0) {
            $entry['booked_day'] = $bookedDay;
        }

        foreach ([
            'transfer_city',
            'transfer_pickup',
            'transfer_drop',
            'transfer_pickup_label',
            'transfer_drop_label',
        ] as $field) {
            if (! array_key_exists($field, $row)) {
                continue;
            }
            $value = (string) ($row[$field] ?? '');
            if ($field === 'transfer_pickup' || $field === 'transfer_drop') {
                $value = self::normalizeTransferLocationToken($value);
            } else {
                $value = strip_tags($value);
            }
            $entry[$field] = $value;
        }

        return $entry;
    }

    /**
     * Ensure hotel_star_rating is present on a hotel row (payload / Azure JSON export).
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function enrichHotelRowStarRating(array $row): array
    {
        $existing = trim((string) ($row['hotel_star_rating'] ?? $row['cat'] ?? ''));
        if ($existing !== '') {
            $row['hotel_star_rating'] = $existing;

            return $row;
        }

        $hotelId = self::resolveHotelUniqueId((string) ($row['hotel_id'] ?? ''));
        if ($hotelId === '') {
            return $row;
        }

        static $ratingByHotelId = null;
        if ($ratingByHotelId === null) {
            $ratingByHotelId = [];
            foreach (Hotel::query()->whereNull('deleted_at')->get(['id', 'hotel_unique_id', 'hotel_star_rating']) as $hotel) {
                $rating = trim((string) ($hotel->hotel_star_rating ?? ''));
                if ($rating === '') {
                    continue;
                }
                $uniqueId = trim((string) ($hotel->hotel_unique_id ?? ''));
                if ($uniqueId !== '') {
                    $ratingByHotelId[$uniqueId] = $rating;
                }
                $ratingByHotelId[(string) ((int) ($hotel->id ?? 0))] = $rating;
            }
        }

        if (isset($ratingByHotelId[$hotelId]) && $ratingByHotelId[$hotelId] !== '') {
            $row['hotel_star_rating'] = $ratingByHotelId[$hotelId];
        }

        return $row;
    }

    private static function formatRawHotelsMap(array $hotels, bool $preserveFullRow = false): array
    {
        $out = [];
        foreach ($hotels as $label => $row) {
            if (! is_array($row) && ! is_object($row)) {
                $out[$label] = $row;
                continue;
            }
            $row = self::castMixedToArray($row);
            if ($row === []) {
                continue;
            }
            if ($preserveFullRow) {
                if (isset($row['hotel_id'])) {
                    $row['hotel_id'] = self::resolveHotelUniqueId((string) $row['hotel_id']);
                }
                foreach (['transfer_pickup', 'transfer_drop'] as $field) {
                    if (array_key_exists($field, $row)) {
                        $row[$field] = self::normalizeTransferLocationToken((string) ($row[$field] ?? ''));
                    }
                }
                $out[$label] = self::enrichHotelRowStarRating($row);
                continue;
            }
            $out[$label] = self::normalizeRawHotelRow($row);
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $dayNode
     * @param  list<array{city: string, city_checkin: string, city_checkout: string}>  $citySummaries
     * @return list<array{city: string, city_checkin: string, city_checkout: string}>
     */
    private static function rawCitiesListFromDayNode(array $dayNode, array $citySummaries): array
    {
        $citiesRaw = $dayNode['cities'] ?? null;
        if ($citiesRaw instanceof \stdClass) {
            $citiesRaw = (array) $citiesRaw;
        }

        $list = [];
        if (is_array($citiesRaw) && $citiesRaw !== []) {
            $rows = array_is_list($citiesRaw) ? $citiesRaw : array_values($citiesRaw);
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $list[] = [
                    'city'          => (string) ($row['city'] ?? ''),
                    'city_checkin'  => (string) ($row['city_checkin'] ?? $row['checkin_day'] ?? ''),
                    'city_checkout' => (string) ($row['city_checkout'] ?? $row['checkout_day'] ?? ''),
                ];
            }
        }

        if ($list !== []) {
            return self::filterCitySummaries($list);
        }

        return self::citySummariesToRawCitiesList($citySummaries);
    }

    /**
     * @param  list<array{city: string, city_checkin: string, city_checkout: string}>  $citySummaries
     * @return list<array{city: string, city_checkin: string, city_checkout: string}>
     */
    private static function citySummariesToRawCitiesList(array $citySummaries): array
    {
        $list = [];
        foreach ($citySummaries as $row) {
            if (! is_array($row)) {
                continue;
            }
            $list[] = [
                'city'          => (string) ($row['city'] ?? ''),
                'city_checkin'  => (string) ($row['city_checkin'] ?? $row['checkin_day'] ?? ''),
                'city_checkout' => (string) ($row['city_checkout'] ?? $row['checkout_day'] ?? ''),
            ];
        }

        return $list;
    }

    /**
     * @param  array<string, mixed>  $package
     */
    public static function inferTotalDaysFromPackageDays(array $package): int
    {
        $max = 0;
        foreach (self::flattenPackageDayNodes($package['days'] ?? []) as $dayNode) {
            if (! is_array($dayNode)) {
                continue;
            }
            $max = max($max, (int) ($dayNode['day'] ?? 0));
        }

        return $max > 0 ? $max : 1;
    }

    /**
     * Catalog buckets for raw_all_services (hotels, attractions, restaurants, guides only).
     *
     * @param  list<string>  $cityNames
     * @return array<string, mixed>
     */
    public function buildRawAllServicesCatalog(int $dmcId, string $country, array $cityNames): array
    {
        $cities = [];
        foreach ($cityNames as $name) {
            $name = trim($name);
            if ($name !== '') {
                $cities[] = ['city' => $name];
            }
        }

        $destination = [
            'country' => $country,
            'cities'  => $cities,
        ];

        $buckets = $this->buildDmcWideServiceBuckets([], $destination, $dmcId);
        $services = is_array($buckets['list_all_services'] ?? null) ? $buckets['list_all_services'] : [];
        unset($services['transfers']);

        if (isset($services['attractions']) && is_array($services['attractions'])) {
            $services['attractions'] = self::normalizeAttractionsMap($services['attractions']);
        }

        $out = [];
        foreach (['hotels', 'attractions', 'restaurants', 'guides'] as $bucket) {
            if (! isset($services[$bucket]) || ! is_array($services[$bucket]) || $services[$bucket] === []) {
                continue;
            }
            $out[$bucket] = $services[$bucket];
        }

        return $out;
    }
}
