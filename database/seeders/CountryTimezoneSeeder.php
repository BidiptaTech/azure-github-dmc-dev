<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountryTimezoneSeeder extends Seeder
{
    /**
     * IANA timezone identifiers keyed by country name (as stored in countries.name).
     * For multi-timezone countries, the capital / primary zone is used.
     */
    private function countryTimezoneMap(): array
    {
        return [
            'Afghanistan' => 'Asia/Kabul',
            'Albania' => 'Europe/Tirane',
            'Algeria' => 'Africa/Algiers',
            'Andorra' => 'Europe/Andorra',
            'Angola' => 'Africa/Luanda',
            'Argentina' => 'America/Argentina/Buenos_Aires',
            'Armenia' => 'Asia/Yerevan',
            'Australia' => 'Australia/Sydney',
            'Austria' => 'Europe/Vienna',
            'Azerbaijan' => 'Asia/Baku',
            'Bahamas' => 'America/Nassau',
            'Bahrain' => 'Asia/Bahrain',
            'Bangladesh' => 'Asia/Dhaka',
            'Barbados' => 'America/Barbados',
            'Belarus' => 'Europe/Minsk',
            'Belgium' => 'Europe/Brussels',
            'Belize' => 'America/Belize',
            'Benin' => 'Africa/Porto-Novo',
            'Bhutan' => 'Asia/Thimphu',
            'Bolivia' => 'America/La_Paz',
            'Bosnia and Herzegovina' => 'Europe/Sarajevo',
            'Botswana' => 'Africa/Gaborone',
            'Brazil' => 'America/Sao_Paulo',
            'Brunei' => 'Asia/Brunei',
            'Bulgaria' => 'Europe/Sofia',
            'Burkina Faso' => 'Africa/Ouagadougou',
            'Burundi' => 'Africa/Bujumbura',
            'Cambodia' => 'Asia/Phnom_Penh',
            'Cameroon' => 'Africa/Douala',
            'United States' => 'America/New_York',
            'Cape Verde' => 'Atlantic/Cape_Verde',
            'Central African Republic' => 'Africa/Bangui',
            'Chad' => 'Africa/Ndjamena',
            'Chile' => 'America/Santiago',
            'China' => 'Asia/Shanghai',
            'Colombia' => 'America/Bogota',
            'Costa Rica' => 'America/Costa_Rica',
            'Croatia' => 'Europe/Zagreb',
            'Cyprus' => 'Asia/Nicosia',
            'Czech Republic' => 'Europe/Prague',
            'Denmark' => 'Europe/Copenhagen',
            'Dominican Republic' => 'America/Santo_Domingo',
            'Ecuador' => 'America/Guayaquil',
            'Egypt' => 'Africa/Cairo',
            'El Salvador' => 'America/El_Salvador',
            'Estonia' => 'Europe/Tallinn',
            'Eswatini' => 'Africa/Mbabane',
            'Ethiopia' => 'Africa/Addis_Ababa',
            'Fiji' => 'Pacific/Fiji',
            'Finland' => 'Europe/Helsinki',
            'France' => 'Europe/Paris',
            'Germany' => 'Europe/Berlin',
            'Ghana' => 'Africa/Accra',
            'Greece' => 'Europe/Athens',
            'Guatemala' => 'America/Guatemala',
            'Haiti' => 'America/Port-au-Prince',
            'Honduras' => 'America/Tegucigalpa',
            'Hungary' => 'Europe/Budapest',
            'Iceland' => 'Atlantic/Reykjavik',
            'India' => 'Asia/Kolkata',
            'Indonesia' => 'Asia/Jakarta',
            'Iran' => 'Asia/Tehran',
            'Iraq' => 'Asia/Baghdad',
            'Ireland' => 'Europe/Dublin',
            'Israel' => 'Asia/Jerusalem',
            'Italy' => 'Europe/Rome',
            'Jamaica' => 'America/Jamaica',
            'Japan' => 'Asia/Tokyo',
            'Jordan' => 'Asia/Amman',
            'Russia' => 'Europe/Moscow',
            'Kenya' => 'Africa/Nairobi',
            'Kuwait' => 'Asia/Kuwait',
            'Kyrgyzstan' => 'Asia/Bishkek',
            'Laos' => 'Asia/Vientiane',
            'Latvia' => 'Europe/Riga',
            'Lebanon' => 'Asia/Beirut',
            'Liberia' => 'Africa/Monrovia',
            'Libya' => 'Africa/Tripoli',
            'Lithuania' => 'Europe/Vilnius',
            'Luxembourg' => 'Europe/Luxembourg',
            'Malaysia' => 'Asia/Kuala_Lumpur',
            'Malta' => 'Europe/Malta',
            'Mexico' => 'America/Mexico_City',
            'Moldova' => 'Europe/Chisinau',
            'Monaco' => 'Europe/Monaco',
            'Morocco' => 'Africa/Casablanca',
            'Nepal' => 'Asia/Kathmandu',
            'Netherlands' => 'Europe/Amsterdam',
            'New Zealand' => 'Pacific/Auckland',
            'Nicaragua' => 'America/Managua',
            'Nigeria' => 'Africa/Lagos',
            'Norway' => 'Europe/Oslo',
            'Pakistan' => 'Asia/Karachi',
            'Peru' => 'America/Lima',
            'Philippines' => 'Asia/Manila',
            'Poland' => 'Europe/Warsaw',
            'Portugal' => 'Europe/Lisbon',
            'Qatar' => 'Asia/Qatar',
            'Romania' => 'Europe/Bucharest',
            'Saudi Arabia' => 'Asia/Riyadh',
            'Singapore' => 'Asia/Singapore',
            'South Africa' => 'Africa/Johannesburg',
            'South Korea' => 'Asia/Seoul',
            'Spain' => 'Europe/Madrid',
            'Sri Lanka' => 'Asia/Colombo',
            'Sweden' => 'Europe/Stockholm',
            'Switzerland' => 'Europe/Zurich',
            'Taiwan' => 'Asia/Taipei',
            'Thailand' => 'Asia/Bangkok',
            'Turkey' => 'Europe/Istanbul',
            'Ukraine' => 'Europe/Kyiv',
            'United Arab Emirates' => 'Asia/Dubai',
            'United Kingdom' => 'Europe/London',
            'Uruguay' => 'America/Montevideo',
            'Venezuela' => 'America/Caracas',
            'Vietnam' => 'Asia/Ho_Chi_Minh',
            'Maldives' => 'Indian/Maldives',
        ];
    }

    public function run(): void
    {
        $updated = 0;
        $missing = [];

        foreach ($this->countryTimezoneMap() as $countryName => $timezone) {
            $count = Country::where('name', $countryName)->update(['timezone' => $timezone]);

            if ($count > 0) {
                $updated += $count;
                continue;
            }

            $missing[] = $countryName;
        }

        $this->command?->info("Updated timezone for {$updated} country row(s).");

        if (!empty($missing)) {
            $this->command?->warn('No matching countries table row for: ' . implode(', ', $missing));
        }
    }
}
