<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Country;

class CountriesTableSeeder extends Seeder
{
    public function run()
    {
        $countryCodes = User::countryCodes();

        foreach ($countryCodes as $code => $name) {
            Country::updateOrCreate(
                ['country_code' => $code],
                [
                    'name' => $name,
                    'tax_percentage' => $this->getTaxPercentage($code),
                ]
            );
        }
    }

    private function getTaxPercentage($countryCode)
    {
        $defaultTaxRates = [
            '93' => 5,  // Afghanistan
            '44' => 20, // United Kingdom
            '1' => 10,  // United States
            '65' => 7,  // Singapore
            '91' => 18, // India
            '880' => 15, // Bangladesh
        ];

        return $defaultTaxRates[$countryCode] ?? rand(5, 20); // Fallback to random 5–20%
    }
}
