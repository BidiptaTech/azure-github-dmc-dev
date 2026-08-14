<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Services\HotelSuppliers\MgBedbank\MgBedbankClient;
use App\Services\SupplierEnvService;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * One-time (and thereafter occasional) sync of MG Bedbank's destination catalogue
 * onto our own cities, so hotel searches can look the codes up locally.
 */
class SyncMgBedbankDestinations extends Command
{
    protected $signature = 'mg-bedbank:sync-destinations
                            {--since= : Only pull destinations modified since this datetime (defaults to the full catalogue)}
                            {--continent= : Restrict the pull to a single MG continent code, e.g. AS}
                            {--only-missing : Skip cities that already have MG codes}
                            {--dry-run : Report what would change without writing}';

    protected $description = 'Pull the MG Bedbank destination catalogue and map it onto cities.mg_country_code / mg_city_code';

    /**
     * Our country names that differ from MG's spelling, aliased onto the ISO code
     * because the catalogue is indexed by code as well as by name.
     *
     * @var array<string, string>
     */
    private const COUNTRY_ALIASES = [
        'usa' => 'us',
        'unitedstates' => 'us',
        'unitedstatesofamerica' => 'us',
        'uk' => 'gb',
        'unitedkingdom' => 'gb',
        'greatbritain' => 'gb',
        'uae' => 'ae',
        'unitedarabemirates' => 'ae',
        'southkorea' => 'kr',
        'northkorea' => 'kp',
        'russia' => 'ru',
        'vietnam' => 'vn',
        'laos' => 'la',
        'macau' => 'mo',
        'ivorycoast' => 'ci',
        'czechrepublic' => 'cz',
        'hongkong' => 'hk',
    ];

    public function handle(): int
    {
        $client = new MgBedbankClient(app(SupplierEnvService::class)->valuesFor('mg_bedbank'));
        $since = (string) ($this->option('since') ?: MgBedbankClient::EPOCH);
        $continent = trim((string) $this->option('continent'));
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Pulling MG Bedbank destinations modified since {$since}" . ($continent !== '' ? " for continent {$continent}" : '') . '...');

        try {
            $body = $client->getDestinations($since, $continent);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($reason = $client->failureReason($body)) {
            $this->error('MG Bedbank rejected GetDestinations: ' . $reason);

            return self::FAILURE;
        }

        $catalogue = $this->buildCatalogue($body);

        if ($catalogue['countries'] === []) {
            $this->warn('MG returned no destinations. If you passed --since, try again without it to pull the full catalogue.');

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Received %d countries and %d cities.',
            count($catalogue['countries']),
            $catalogue['city_count'],
        ));

        return $this->mapOntoCities($catalogue, $dryRun);
    }

    /**
     * Flattens MG's continent → country → city tree into lookup tables.
     *
     * @param  array<string, mixed>  $body
     * @return array{countries: array<string, string>, cities: array<string, array<int, array{code: string, name: string}>>, city_count: int}
     */
    private function buildCatalogue(array $body): array
    {
        $countries = [];
        $cities = [];
        $cityCount = 0;

        foreach ($body['continents']['continent'] ?? [] as $continent) {
            foreach ($continent['countries']['country'] ?? [] as $country) {
                $code = strtoupper(trim((string) ($country['code'] ?? '')));
                $name = trim((string) ($country['name'] ?? ''));

                if ($code === '') {
                    continue;
                }

                $countries[$this->normalize($name)] = $code;
                $countries[$this->normalize($code)] = $code;

                foreach ($country['cities']['city'] ?? [] as $city) {
                    $cityCode = trim((string) ($city['code'] ?? ''));
                    $cityName = trim((string) ($city['name'] ?? ''));

                    if ($cityCode === '' || $cityName === '') {
                        continue;
                    }

                    $cities[$code][] = ['code' => $cityCode, 'name' => $cityName];
                    $cityCount++;
                }
            }
        }

        return ['countries' => $countries, 'cities' => $cities, 'city_count' => $cityCount];
    }

    /**
     * @param  array{countries: array<string, string>, cities: array<string, array<int, array{code: string, name: string}>>, city_count: int}  $catalogue
     */
    private function mapOntoCities(array $catalogue, bool $dryRun): int
    {
        $query = City::query();

        if ($this->option('only-missing')) {
            $query->whereNull('mg_city_code');
        }

        $ourCities = $query->get();
        $matched = 0;
        $unchanged = 0;
        $unknownCountry = [];
        $unmatchedCity = [];

        foreach ($ourCities as $city) {
            $countryKey = $this->normalize((string) $city->country);
            $countryCode = $catalogue['countries'][$countryKey]
                ?? $catalogue['countries'][self::COUNTRY_ALIASES[$countryKey] ?? $countryKey]
                ?? null;

            if (! $countryCode) {
                $unknownCountry[(string) $city->country] = ($unknownCountry[(string) $city->country] ?? 0) + 1;
                continue;
            }

            $match = $this->matchCity((string) $city->name, $catalogue['cities'][$countryCode] ?? []);

            if (! $match) {
                $unmatchedCity[] = $city->name . ' (' . $city->country . ')';
                continue;
            }

            if ($city->mg_country_code === $countryCode && $city->mg_city_code === $match['code']) {
                $unchanged++;
                continue;
            }

            $matched++;

            if (! $dryRun) {
                $city->forceFill([
                    'mg_country_code' => $countryCode,
                    'mg_city_code' => $match['code'],
                ])->save();
            }
        }

        $this->newLine();
        $this->info(($dryRun ? '[dry run] ' : '') . "Mapped {$matched} cities, {$unchanged} already correct.");

        if ($unknownCountry !== []) {
            $this->warn('Countries MG does not recognise (cities skipped): ' . collect($unknownCountry)
                ->map(fn (int $count, string $name) => "{$name} x{$count}")
                ->implode(', '));
        }

        if ($unmatchedCity !== []) {
            $this->warn(count($unmatchedCity) . ' cities had no MG equivalent:');
            $this->line('  ' . implode(', ', array_slice($unmatchedCity, 0, 40)));

            if (count($unmatchedCity) > 40) {
                $this->line('  ...and ' . (count($unmatchedCity) - 40) . ' more.');
            }
        }

        return self::SUCCESS;
    }

    /**
     * MG city names carry region suffixes ("Alor Setar, Kedah") and the word "City"
     * ("Singapore City"), and ours sometimes do too ("Ho Chi Minh City"), so compare
     * progressively looser forms of both sides.
     *
     * @param  array<int, array{code: string, name: string}>  $candidates
     * @return array{code: string, name: string}|null
     */
    private function matchCity(string $cityName, array $candidates): ?array
    {
        $needles = array_values(array_unique(array_filter([
            $this->normalize($cityName),
            $this->stripCitySuffix($cityName),
        ])));

        if ($needles === []) {
            return null;
        }

        // Built in ascending priority: later passes overwrite earlier ones on collision.
        $index = [];

        foreach ([
            fn (string $name, string $head) => $this->stripCitySuffix($head),
            fn (string $name, string $head) => $this->stripCitySuffix($name),
            fn (string $name, string $head) => $this->normalize($head),
            fn (string $name, string $head) => $this->normalize($name),
        ] as $keyFor) {
            foreach ($candidates as $candidate) {
                $key = $keyFor($candidate['name'], trim(explode(',', $candidate['name'])[0]));

                if ($key !== '') {
                    $index[$key] = $candidate;
                }
            }
        }

        foreach ($needles as $needle) {
            if (isset($index[$needle])) {
                return $index[$needle];
            }
        }

        return null;
    }

    private function stripCitySuffix(string $name): string
    {
        return $this->normalize(preg_replace('/\s+city$/i', '', trim($name)) ?? $name);
    }

    private function normalize(string $value): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $value) ?? '');
    }
}
