<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountryShortCodeSeeder extends Seeder
{
    public function run(): void
    {
        $updated = 0;
        $missing = [];

        foreach (Country::shortCodeMap() as $countryName => $shortCode) {
            $count = Country::where('name', $countryName)->update(['short_code' => $shortCode]);

            if ($count > 0) {
                $updated += $count;
                continue;
            }

            $missing[] = $countryName;
        }

        $this->command?->info("Updated short_code for {$updated} country row(s).");

        if (! empty($missing)) {
            $this->command?->warn('No matching countries table row for: ' . implode(', ', $missing));
        }

        $unset = Country::whereNull('short_code')->orWhere('short_code', '')->pluck('name');

        if ($unset->isNotEmpty()) {
            $this->command?->warn('Still without a short_code: ' . $unset->implode(', '));
        }
    }
}
