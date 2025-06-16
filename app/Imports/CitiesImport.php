<?php

namespace App\Imports;

use App\Models\City;
use Maatwebsite\Excel\Concerns\ToModel;

class CitiesImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    private static $cityId = 0; // Initialize the counter

    public function __construct()
    {
        // Get the max existing city_id from the database
        self::$cityId = City::max('city_id') ?? 0;
    }

    public function model(array $row)
    {
        self::$cityId++; // Increment city_id for each row

        return new City([
            'city_id' => self::$cityId,
            
            'name'    => $row['city_name'], 
            'country' => $row['country'] ?? 'India', // Default to India if not present 
        ]);
    }
}
