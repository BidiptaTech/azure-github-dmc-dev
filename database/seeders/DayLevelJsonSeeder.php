<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\DayLevel;
use Illuminate\Database\Seeder;

class DayLevelJsonSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            [
                'master_dmc_id' => 3,
                'dmc_id' => 4,
                'country' => 'Singapore',
                'city' => 'Singapore',
                'services' => [
                    'airport_transfer' => [
                        'type' => 'private',
                        'vehicle_id' => '1',
                        'vehicle_service_type' => 'private',
                        'vehicle_passengers' => 4,
                        'cost' => 55,
                    ],
                    'departure_transfer' => [
                        'type' => 'shared',
                        'vehicle_id' => '2',
                        'vehicle_service_type' => 'shared',
                        'vehicle_passengers' => 2,
                        'cost' => 35,
                    ],
                ],
                'packages' => [
                    [
                        'days' => [
                            '0' => $this->buildDay(
                                1,
                                [['hotel_id' => '9', 'hotel_name' => 'Andaz Singapore - Singapore', 'price' => 0]],
                                [['attraction_id' => '36', 'name' => '4D Adventure Land - Singapore']],
                                [['restaurant_id' => '28', 'name' => 'Burnt Ends - Singapore']],
                                [['activity_id' => '38', 'name' => 'Bungy Jump (AJ HACKETT)']]
                            ),
                            '1' => $this->buildDay(
                                2,
                                [['hotel_id' => '10', 'hotel_name' => 'Crowne Plaza Changi Airport - Singapore', 'price' => 0]],
                                [['attraction_id' => '40', 'name' => 'Adventure Cove Waterpark - Singapore']],
                                [['restaurant_id' => '30', 'name' => 'Cafe Delight - Singapore']],
                                [['activity_id' => '28', 'name' => 'Dolphin Island']]
                            ),
                            '2' => $this->buildDay(
                                3,
                                [['hotel_id' => '44', 'hotel_name' => 'BB Hotel - Singapore', 'price' => 0]],
                                [['attraction_id' => '28', 'name' => 'Dolphin Island - Singapore']],
                                [['restaurant_id' => '40', 'name' => 'ABC Test res - Singapore']],
                                [['activity_id' => '29', 'name' => 'Dolphin Island']]
                            ),
                        ],
                    ],
                    [
                        'days' => [
                            '0' => $this->buildDay(
                                1,
                                [['hotel_id' => '15', 'hotel_name' => 'Grand Hyatt Singapore - Singapore', 'price' => 0]],
                                [['attraction_id' => '1', 'name' => 'Sky Park Observation Deck - Singapore']],
                                [['restaurant_id' => '17', 'name' => 'Taj Resturant - Singapore']],
                                [['activity_id' => '1', 'name' => 'Sky Park Observation Deck']]
                            ),
                            '1' => $this->buildDay(
                                2,
                                [['hotel_id' => '26', 'hotel_name' => 'YOTEL Singapore Orchard Road - Singapore', 'price' => 0]],
                                [['attraction_id' => '41', 'name' => 'Singapore Botanic Gardens + National Orchid Garden - Singapore']],
                                [['restaurant_id' => '26', 'name' => 'Test Restaurant20 - Singapore']],
                                [['activity_id' => '40', 'name' => 'Adventure Cove Waterpark']]
                            ),
                        ],
                    ],
                ],
            ],
            [
                'master_dmc_id' => 3,
                'dmc_id' => 5,
                'country' => 'Thailand',
                'city' => 'Bangkok',
                'services' => [
                    'airport_transfer' => [
                        'type' => 'shared',
                        'vehicle_id' => '3',
                        'vehicle_service_type' => 'shared',
                        'vehicle_passengers' => 3,
                        'cost' => 40,
                    ],
                    'departure_transfer' => [
                        'type' => 'private',
                        'vehicle_id' => '4',
                        'vehicle_service_type' => 'private',
                        'vehicle_passengers' => 3,
                        'cost' => 60,
                    ],
                ],
                'packages' => [
                    [
                        'days' => [
                            '0' => $this->buildDay(
                                1,
                                [['hotel_id' => 'BKK_H01', 'hotel_name' => 'Mandarin Oriental - Bangkok', 'price' => 200]],
                                [['attraction_id' => 'A1', 'name' => 'Grand Palace']],
                                [['restaurant_id' => 'R1', 'name' => 'Gaggan']],
                                [['activity_id' => 'ACT1', 'name' => 'Chao Phraya River Cruise']]
                            ),
                            '1' => $this->buildDay(
                                2,
                                [['hotel_id' => 'BKK_H02', 'hotel_name' => 'Banyan Tree - Bangkok', 'price' => 180]],
                                [['attraction_id' => 'A2', 'name' => 'Wat Arun']],
                                [['restaurant_id' => 'R2', 'name' => 'Nahm']],
                                [['activity_id' => 'ACT2', 'name' => 'Thai Cooking Class']]
                            ),
                        ],
                    ],
                ],
            ],
            [
                'master_dmc_id' => 4,
                'dmc_id' => 8,
                'country' => 'UAE',
                'city' => 'Dubai',
                'services' => [
                    'airport_transfer' => [
                        'type' => 'private',
                        'vehicle_id' => '7',
                        'vehicle_service_type' => 'private',
                        'vehicle_passengers' => 4,
                        'cost' => 120,
                    ],
                    'departure_transfer' => [
                        'type' => 'private',
                        'vehicle_id' => '7',
                        'vehicle_service_type' => 'private',
                        'vehicle_passengers' => 4,
                        'cost' => 120,
                    ],
                ],
                'packages' => [
                    [
                        'days' => [
                            '0' => $this->buildDay(
                                1,
                                [['hotel_id' => 'DXB_H01', 'hotel_name' => 'Burj Al Arab', 'price' => 800]],
                                [['attraction_id' => 'A1', 'name' => 'Burj Khalifa']],
                                [['restaurant_id' => 'R1', 'name' => 'Al Mahara']],
                                [['activity_id' => 'ACT1', 'name' => 'Desert Safari Ride']]
                            ),
                            '1' => $this->buildDay(
                                2,
                                [['hotel_id' => 'DXB_H02', 'hotel_name' => 'Atlantis The Palm', 'price' => 650]],
                                [['attraction_id' => 'A2', 'name' => 'Dubai Mall']],
                                [['restaurant_id' => 'R2', 'name' => 'Nobu Dubai']],
                                [['activity_id' => 'ACT2', 'name' => 'Dhow Cruise Dinner']]
                            ),
                            '2' => $this->buildDay(
                                3,
                                [['hotel_id' => 'DXB_H03', 'hotel_name' => 'Jumeirah Beach Hotel', 'price' => 500]],
                                [['attraction_id' => 'A3', 'name' => 'Desert Safari']],
                                [['restaurant_id' => 'R3', 'name' => 'Pierchic']],
                                [['activity_id' => 'ACT3', 'name' => 'Skydiving Dubai']]
                            ),
                        ],
                    ],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $destination = [
                'DMC_id' => (int) $sample['dmc_id'],
                'country' => (string) $sample['country'],
                'services' => $sample['services'],
                'cities' => [[
                    'city' => (string) $sample['city'],
                    'packages' => $sample['packages'],
                ]],
            ];

            $destinations = DayLevel::canonicalizeDestinationsForStorage([$destination]);
            $cityId = $this->resolveCityId((string) $sample['city'], (string) $sample['country']);
            if (!$cityId) {
                continue;
            }

            $airport = $sample['services']['airport_transfer'] ?? [];
            $daysCount = $this->getMaxDayCount($sample['packages']);

            DayLevel::updateOrCreate(
                [
                    'master_dmc_id' => (int) $sample['master_dmc_id'],
                    'dmc_id' => (int) $sample['dmc_id'],
                ],
                [
                    'city_id' => $cityId,
                    'country' => $sample['country'],
                    'days' => $daysCount,
                    'hotels' => $this->collectHotelsFromPackages($sample['packages']),
                    'airport_transfer_type' => $airport['type'] ?? null,
                    'airport_transfer_cost' => isset($airport['cost']) ? (float) $airport['cost'] : null,
                    'vehicle_id' => isset($airport['vehicle_id']) && $airport['vehicle_id'] !== '' ? (int) $airport['vehicle_id'] : null,
                    'vehicle_service_type' => $airport['vehicle_service_type'] ?? null,
                    'vehicle_passengers' => isset($airport['vehicle_passengers']) ? (int) $airport['vehicle_passengers'] : null,
                    'activities' => $destinations,
                    'inter_city' => [
                        'Master_DMC_id' => (int) $sample['master_dmc_id'],
                        'destinations' => $destinations,
                    ],
                ]
            );
        }
    }

    private function buildDay(
        int $dayNumber,
        array $hotels,
        array $attractions,
        array $restaurants,
        array $activities
    ): array {
        return [
            'day' => $dayNumber,
            'hotels' => $this->namedMap('Hotel', $hotels),
            'attractions' => $this->namedMap('Attraction', $attractions),
            'restaurants' => $this->namedMap('Restaurant', $restaurants),
            'activities' => $this->namedMap('Activity', $activities),
        ];
    }

    private function namedMap(string $prefix, array $rows): array
    {
        $mapped = [];
        foreach (array_values($rows) as $i => $row) {
            $mapped[$prefix . ' ' . ($i + 1)] = $row;
        }
        return $mapped;
    }

    private function collectHotelsFromPackages(array $packages): array
    {
        $hotels = [];
        foreach ($packages as $package) {
            $days = is_array($package['days'] ?? null) ? $package['days'] : [];
            foreach ($days as $dayNode) {
                foreach ((array) ($dayNode['hotels'] ?? []) as $hotelNode) {
                    $hotels[] = $hotelNode;
                }
            }
        }
        return $hotels;
    }

    private function getMaxDayCount(array $packages): int
    {
        $max = 1;
        foreach ($packages as $package) {
            $days = is_array($package['days'] ?? null) ? $package['days'] : [];
            $max = max($max, count($days));
        }
        return $max;
    }

    private function resolveCityId(string $cityName, string $country): ?int
    {
        $cityName = trim($cityName);
        $country = trim($country);

        $id = City::query()
            ->when($cityName !== '', fn ($q) => $q->where('name', 'ilike', $cityName))
            ->when($country !== '', fn ($q) => $q->where('country', 'ilike', $country))
            ->value('id');
        if ($id) {
            return (int) $id;
        }

        $id = City::query()
            ->when($cityName !== '', fn ($q) => $q->where('name', 'ilike', '%' . $cityName . '%'))
            ->when($country !== '', fn ($q) => $q->where('country', 'ilike', '%' . $country . '%'))
            ->value('id');
        if ($id) {
            return (int) $id;
        }

        // Final fallback to satisfy NOT NULL city_id constraint.
        $id = City::query()->value('id');
        return $id ? (int) $id : null;
    }
}

