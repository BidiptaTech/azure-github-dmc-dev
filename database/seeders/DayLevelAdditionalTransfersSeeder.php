<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\DayLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sample day_levels row with attraction.transfer.additional_transfers for QA / API testing.
 */
class DayLevelAdditionalTransfersSeeder extends Seeder
{
    public function run(): void
    {
        $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $cityId = City::query()
            ->where('name', $likeOp, '%Batam%')
            ->where('country', $likeOp, '%Indonesia%')
            ->value('id');

        if (!$cityId) {
            $cityId = City::query()->where('name', $likeOp, '%Batam%')->value('id');
        }
        if (!$cityId) {
            $cityId = City::query()->value('id');
        }

        if (!$cityId) {
            $this->command?->warn('DayLevelAdditionalTransfersSeeder: no cities row found; skipped.');

            return;
        }

        $destination = [
            'DMC_id' => 18,
            'country' => 'Indonesia',
            'service_meta' => [
                'airport_transfer' => [
                    'type' => '',
                    'vehicle_id' => '',
                    'vehicle_service_type' => '',
                    'vehicle_passengers' => 0,
                    'cost' => 0,
                ],
                'departure_transfer' => [
                    'type' => '',
                    'vehicle_id' => '',
                    'vehicle_service_type' => '',
                    'vehicle_passengers' => 0,
                    'cost' => 0,
                ],
                'guide' => [
                    'guide_id' => '',
                    'guide_name' => '',
                    'guide_cost' => 0,
                ],
                'inter_city' => [],
            ],
            'cities' => [[
                'city' => 'Batam',
                'checkin_day' => 1,
                'checkout_day' => 3,
                'packages' => [[
                    'days' => [
                        '0' => [
                            'day' => 2,
                            'hotels' => [],
                            'attractions' => [
                                'Attraction 1' => [
                                    'attraction_id' => '41',
                                    'name' => 'Batam Adventure Park - Batam',
                                    'ticket_id' => '10000092',
                                    'ticket_name' => 'Go Kart',
                                    'transfer' => [
                                        'required' => 'Yes',
                                        'transfer_type' => 'Transfer',
                                        'city' => 'Batam',
                                        'pickup_location' => 'hotel:291',
                                        'drop_location' => 'hotel:294',
                                        'additional_transfers' => [
                                            [
                                                'city' => 'Batam',
                                                'pickup_location' => 'hotel:291',
                                                'drop_location' => 'hotel:289',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'restaurants' => [],
                            'services' => [],
                        ],
                    ],
                ]],
            ]],
        ];

        $stored = DayLevel::canonicalizeDestinationsForStorage([$destination]);

        DayLevel::updateOrCreate(
            [
                'master_dmc_id' => 16,
                'dmc_id' => 18,
            ],
            [
                'city_id' => (int) $cityId,
                'country' => 'Indonesia',
                'days' => 3,
                'hotels' => [],
                'activities' => $stored,
                'inter_city' => [
                    'Master_DMC_id' => 16,
                    'destinations' => $stored,
                ],
            ]
        );

        $this->command?->info('DayLevelAdditionalTransfersSeeder: seeded master_dmc_id=16, dmc_id=18 with additional_transfers.');
    }
}
