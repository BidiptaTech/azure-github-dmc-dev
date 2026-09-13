<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserCurrencySeeder extends Seeder
{
    /**
     * Populate the users.currency column based on each user's country.
     *
     * The users.country column stores country name(s) as stored in countries.name.
     * It may contain a comma-separated list of countries for multi-country users;
     * in that case the currency of the first listed country is used.
     */
    public function run(): void
    {
        // Build a case-insensitive lookup of country name => currency.
        $currencyByCountry = [];
        Country::query()
            ->whereNotNull('currency')
            ->where('currency', '!=', '')
            ->select(['name', 'currency'])
            ->get()
            ->each(function (Country $country) use (&$currencyByCountry) {
                $currencyByCountry[mb_strtolower(trim($country->name))] = $country->currency;
            });

        $updated = 0;
        $skippedNoCountry = 0;
        $unmatched = [];

        User::query()
            ->select(['userId', 'country'])
            ->orderBy('userId')
            ->chunkById(500, function ($users) use ($currencyByCountry, &$updated, &$skippedNoCountry, &$unmatched) {
                foreach ($users as $user) {
                    $rawCountry = trim((string) $user->country);

                    if ($rawCountry === '') {
                        $skippedNoCountry++;
                        continue;
                    }

                    // Use the first country when multiple are stored comma-separated.
                    $firstCountry = trim(explode(',', $rawCountry)[0]);
                    $currency = $currencyByCountry[mb_strtolower($firstCountry)] ?? null;

                    if ($currency === null) {
                        $unmatched[$firstCountry] = ($unmatched[$firstCountry] ?? 0) + 1;
                        continue;
                    }

                    User::where('userId', $user->userId)->update(['currency' => $currency]);
                    $updated++;
                }
            }, 'userId');

        $this->command?->info("Updated currency for {$updated} user row(s).");

        if ($skippedNoCountry > 0) {
            $this->command?->warn("Skipped {$skippedNoCountry} user(s) with no country set.");
        }

        if (!empty($unmatched)) {
            $details = collect($unmatched)
                ->map(fn ($count, $name) => "{$name} ({$count})")
                ->implode(', ');
            $this->command?->warn('No matching countries table currency for: ' . $details);
        }
    }
}
