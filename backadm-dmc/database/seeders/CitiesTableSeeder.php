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
            'Thailand' => [
                'Bangkok', 'Chiang Mai', 'Phuket', 'Pattaya', 'Krabi',
                'Ayutthaya', 'Hua Hin', 'Chiang Rai', 'Kanchanaburi', 'Udon Thani',
                'Nakhon Ratchasima', 'Surat Thani', 'Hat Yai', 'Rayong', 'Trang',
                'Lampang', 'Khon Kaen', 'Nakhon Si Thammarat', 'Phitsanulok', 'Sukhothai',
                'Mae Hong Son', 'Loei', 'Buriram', 'Chonburi', 'Samut Prakan',
                'Nakhon Pathom', 'Ratchaburi', 'Phrae', 'Ubon Ratchathani', 'Yala',
            ],
            'Cambodia' =>[
                'Phnom Penh', 'Siem Reap', 'Battambang', 'Sihanoukville', 'Kampot',
                'Kep', 'Poipet', 'Takeo', 'Kampong Cham', 'Koh Kong',
                'Pursat', 'Svay Rieng', 'Stung Treng', 'Prey Veng', 'Kratie',
                'Kampong Thom', 'Kampong Chhnang', 'Tbong Khmum', 'Banteay Meanchey', 'Oddar Meanchey',
                'Preah Vihear', 'Ratanakiri', 'Mondulkiri', 'Senmonorom', 'Samraong',
                'Pailin', 'Serei Saophoan', 'Takhmao', 'Doun Kaev', 'Baray',
            ],
            'Vietnam' => [
                'Hanoi', 'Ho Chi Minh City', 'Da Nang', 'Hai Phong', 'Nha Trang',
                'Hue', 'Can Tho', 'Vung Tau', 'Dalat', 'Bien Hoa',
                'Buon Ma Thuot', 'Ha Long', 'Quy Nhon', 'Phan Thiet', 'My Tho',
                'Thanh Hoa', 'Thai Nguyen', 'Nam Dinh', 'Long Xuyen', 'Rach Gia',
                'Tuy Hoa', 'Cam Ranh', 'Bac Giang', 'Bac Ninh', 'Lang Son',
                'Cao Bang', 'Tam Ky', 'Ha Tinh', 'Vinh', 'Phu Quoc',
            ],
            'Singapore' =>[
                'Singapore'
            ],
            'Malaysia' =>[
                'Kuala Lumpur', 'George Town', 'Johor Bahru', 'Ipoh', 'Malacca',
                'Kota Kinabalu', 'Kuching', 'Shah Alam', 'Putrajaya', 'Petaling Jaya',
                'Alor Setar', 'Seremban', 'Kuantan', 'Miri', 'Batu Pahat',
                'Sibu', 'Sandakan', 'Taiping', 'Kangar', 'Nilai',
                'Cyberjaya', 'Rawang', 'Bentong', 'Kluang', 'Segamat',
                'Selayang', 'Sepang', 'Lahad Datu', 'Tawau', 'Bintulu',
            ],
            'Indonesia' =>[
                'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang',
                'Bali', 'Denpasar', 'Palembang', 'Tangerang', 'Bekasi',
                'Tangerang Selatan', 'Depok', 'Makassar', 'Kuala Lumpur',
                'Kota Kinabalu', 'Kuching', 'Shah Alam', 'Putrajaya', 'Petaling Jaya',
                'Alor Setar', 'Seremban', 'Kuantan', 'Miri', 'Batu Pahat',
            ],
            'India' => [
                'Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai',
                'Kolkata', 'Pune', 'Ahmedabad', 'Jaipur', 'Surat',
                'Lucknow', 'Kanpur', 'Nagpur', 'Indore', 'Bhopal',
                'Patna', 'Ranchi', 'Bhubaneswar', 'Raipur', 'Chandigarh',
                'Ludhiana', 'Amritsar', 'Jalandhar', 'Dehradun', 'Noida',
                'Gurugram', 'Faridabad', 'Ghaziabad', 'Varanasi', 'Prayagraj',
                'Agra', 'Meerut', 'Gwalior', 'Vadodara', 'Rajkot',
                'Jodhpur', 'Udaipur', 'Ajmer', 'Aligarh', 'Gaya',
                'Howrah', 'Asansol', 'Durgapur', 'Shillong', 'Guwahati',
                'Imphal', 'Aizawl', 'Kohima', 'Itanagar', 'Panaji',
                'Thiruvananthapuram', 'Kochi', 'Coimbatore', 'Madurai', 'Mysore',
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
