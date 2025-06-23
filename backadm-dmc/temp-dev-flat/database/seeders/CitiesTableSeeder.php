<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CitiesImport;
use App\Models\City;
use Illuminate\Support\Str;
class CitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            'Maldives' => [
                'Male', 'Addu City', 'Fuvahmulah', 'Kulhudhuffushi', 'Thinadhoo', 'Hithadhoo', 'Naifaru', 'Dhidhdhoo', 'Eydhafushi', 'Villingili',
                'Funadhoo', 'Thulusdhoo', 'Maafushi', 'Thoddoo', 'Ukulhas', 'Dhigurah', 'Himmafushi', 'Rasdhoo', 'Dhangethi', 'Gan',
                'Komandoo', 'Maradhoo', 'Mahibadhoo', 'Nolhivaranfaru', 'Hinnavaru', 'Madifushi', 'Felidhoo', 'Fehendhoo', 'Omadhoo', 'Mandhoo',
            ],
            'United Arab Emirates' => [
                'Dubai', 'Abu Dhabi', 'Sharjah', 'Al Ain', 'Ajman', 'Ras Al Khaimah', 'Fujairah', 'Umm Al Quwain', 'Khor Fakkan', 'Kalba',
                'Dibba Al-Fujairah', 'Madinat Zayed', 'Liwa Oasis', 'Jebel Ali', 'Ruways', 'Hatta', 'Al Dhaid', 'Ghayathi', 'Al Madam', 'Al Hamriyah',
                'Al Mirfa', 'Masfout', 'Al Rafaah', 'Dhaid', 'Mleiha', 'Al Faqa', 'Al Jazirah Al Hamra', 'Manama', 'Al Halah'],
            'Colombia' => [
                'Bogota', 'Medellin', 'Cali', 'Barranquilla', 'Cartagena', 'Cucuta', 'Bucaramanga', 'Pereira', 'Santa Marta', 'Ibague',
                'Manizales', 'Villavicencio', 'Neiva', 'Pasto', 'Monteria', 'Armenia', 'Sincelejo', 'Popayan', 'Valledupar', 'Riohacha',
                'Tunja', 'Florencia', 'Quibdo', 'Yopal', 'San Jose del Guaviare', 'Leticia', 'Mocoa', 'Inirida', 'Mitu', 'Puerto Carreno',
            ],
            'United States' => [
                'New York', 'Los Angeles', 'Chicago', 'Houston', 'Miami', 'Philadelphia', 'San Antonio', 'San Diego', 'San Jose', 'Austin',
                'Jacksonville', 'San Francisco', 'Columbus', 'Charlotte', 'Seattle', 'Denver', 'Washington', 'Boston', 'Nashville', 'El Paso',
            ],
        ];

        $lastCityId = \App\Models\City::max('city_id');
        $cityId = $lastCityId + 1;
        foreach ($cities as $country => $cityList) {
            foreach ($cityList as $cityName) {
                City::create([
                    'city_id' => $cityId++,
                    'country' => $country,
                    'name'    => $cityName,
                ]);
            }
        }
    }
}
