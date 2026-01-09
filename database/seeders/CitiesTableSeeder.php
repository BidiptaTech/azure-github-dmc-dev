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
            'India' => [
                'Goa', 'Pondicherry', 'Ooty', 'Kodaikanal', 'Munnar',
                'Alleppey', 'Kumarakom', 'Varkala', 'Kovalam', 'Wayanad',
                'Hampi', 'Badami', 'Aihole', 'Pattadakal', 'Belur',
                'Halebidu', 'Chikmagalur', 'Coorg', 'Bandipur', 'Kabini',
                'Srinagar', 'Gulmarg', 'Pahalgam', 'Sonamarg', 'Leh',
                'Ladakh', 'Manali', 'Shimla', 'Dharamshala', 'McLeod Ganj',
                'Kasol', 'Spiti', 'Kinnaur', 'Kullu', 'Bir',
                'Rishikesh', 'Haridwar', 'Nainital', 'Mussoorie', 'Jim Corbett',
                'Ranthambore', 'Pushkar', 'Mount Abu', 'Bikaner', 'Jaisalmer',
                'Darjeeling', 'Gangtok', 'Pelling', 'Kalimpong', 'Dooars',
                'Kaziranga', 'Majuli', 'Tawang', 'Khajuraho', 'Orchha',
                'Sanchi', 'Ujjain', 'Omkareshwar', 'Maheshwar', 'Mandu',
                'Konark', 'Puri', 'Chilika', 'Cuttack', 'Sambalpur',
                'Chittorgarh', 'Kumbhalgarh', 'Nathdwara', 'Ranakpur', 'Bundi',
                'Jammu', 'Kargil', 'Dras', 'Zanskar', 'Nubra Valley',
                'Katra', 'Patnitop', 'Kishtwar', 'Doda', 'Rajouri',
                'Dalhousie', 'Chamba', 'Kangra', 'Palampur', 'Khajjiar',
                'Mandi', 'Kufri', 'Chail', 'Kasauli', 'Solan',
                'Keylong', 'Kaza', 'Kalpa', 'Sangla', 'Chitkul',
                'Bomdila', 'Ziro', 'Aalo', 'Pasighat', 'Naharlagun',
                'Roing', 'Tezu', 'Anini', 'Seppa', 'Khonsa',
                'Shillong', 'Cherrapunji', 'Mawlynnong', 'Dawki', 'Jowai',
                'Tura', 'Williamnagar', 'Baghmara', 'Nongpoh', 'Mairang',
                'Dimapur', 'Wokha', 'Mokokchung', 'Zunheboto', 'Tuensang',
                'Mon', 'Phek', 'Kiphire', 'Longleng', 'Peren',
            ],
            'Australia' => [
                'Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide',
                'Gold Coast', 'Newcastle', 'Canberra', 'Sunshine Coast', 'Wollongong',
                'Hobart', 'Geelong', 'Townsville', 'Cairns', 'Darwin',
                'Toowoomba', 'Ballarat', 'Bendigo', 'Launceston', 'Mackay',
                'Rockhampton', 'Bundaberg', 'Coffs Harbour', 'Wagga Wagga', 'Hervey Bay',
                'Port Macquarie', 'Shepparton', 'Orange', 'Tamworth', 'Dubbo',
                'Albury', 'Mildura', 'Gladstone', 'Geraldton', 'Alice Springs',
                'Bunbury', 'Kalgoorlie', 'Devonport', 'Mount Gambier', 'Warrnambool',
            ],
            'New Zealand' => [
                'Auckland', 'Wellington', 'Christchurch', 'Hamilton', 'Tauranga',
                'Dunedin', 'Palmerston North', 'Rotorua', 'New Plymouth', 'Whangarei',
                'Nelson', 'Hastings', 'Invercargill', 'Napier', 'Upper Hutt',
                'Porirua', 'Wanganui', 'Gisborne', 'Lower Hutt', 'Masterton',
                'Blenheim', 'Timaru', 'Oamaru', 'Queenstown', 'Whakatane',
                'Pukekohe', 'Cambridge', 'Te Awamutu', 'Taupo', 'Levin',
            ]
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
