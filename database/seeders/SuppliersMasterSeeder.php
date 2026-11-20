<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\SupplierMaster;
use Illuminate\Database\Seeder;

class SuppliersMasterSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'India' => ['name' => 'Tinivia', 'code' => 'tinivia'],
            'Singapore' => ['name' => 'MyBeds', 'code' => 'mybeds'],
            'Malaysia' => ['name' => 'MG Bedbank', 'code' => 'mg_bedbank'],
            'Thailand' => ['name' => 'Hotelbeds', 'code' => 'hotelbeds'],
        ];

        foreach ($map as $countryName => $supplier) {
            $country = Country::query()->where('name', $countryName)->first();

            if (! $country) {
                continue;
            }

            SupplierMaster::query()->updateOrCreate(
                ['country_id' => $country->id],
                [
                    'name' => $supplier['name'],
                    'code' => $supplier['code'],
                    'status' => true,
                ]
            );
        }
    }
}
