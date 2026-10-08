<?php

namespace Tests\Feature;

use App\Services\HotelSuppliers\Adapters\MgBedbankHotelAdapter;
use App\Services\HotelSuppliers\HotelSearchRequest;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MgBedbankHotelAdapterTest extends TestCase
{
    public function test_it_searches_and_normalizes_mg_bedbank_hotels(): void
    {
        Http::fake([
            'https://mg.example/GetHotelList' => Http::response([
                'status' => true,
                'hotels' => [
                    'hotel' => [
                        ['code' => 'SG10000002', 'name' => 'Amara Singapore'],
                    ],
                ],
            ]),
            'https://mg.example/SearchHotel' => Http::response([
                'status' => true,
                'sessionID' => 'session-1',
                'currency' => 'SGD',
                'hotels' => [
                    'hotel' => [
                        [
                            'code' => 'SG10000002',
                            'name' => 'Amara Singapore',
                            'rating' => '5',
                            'latitude' => '1.275335',
                            'longitude' => '103.843576',
                            'roomDetails' => [
                                [
                                    'code' => 'RM_1511_0',
                                    'name' => 'DELUXE',
                                    'mealPlan' => 'BDBF',
                                    'mealPlanName' => 'Breakfast',
                                    'netPrice' => 168.97,
                                    'grossPrice' => 168.97,
                                    'avgNightPrice' => 84.49,
                                    'canHold' => true,
                                    'packageRate' => false,
                                    'cancellationPolicies' => [
                                        'policy' => [
                                            ['percent' => '100.00', 'noShow' => false],
                                        ],
                                    ],
                                    'rooms' => [
                                        'room' => [
                                            [
                                                'rateKey' => 'rate-key-1',
                                                'noOfAdults' => 2,
                                                'noOfChild' => 0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $result = (new MgBedbankHotelAdapter())->fetchHotels(
            new HotelSearchRequest(
                cityName: 'Singapore',
                checkIn: '2026-08-01',
                checkOut: '2026-08-03',
                paxInfo: '2|0',
            ),
            [
                'base_url' => 'https://mg.example',
                'agency_code' => 'agency',
                'username' => 'user',
                'password' => 'secret',
                'nationality' => 'SG',
                'country_code' => 'SG',
                'destination_map' => '{"Singapore":"SG-SIN"}',
                'hotel_codes' => 'SG10000002',
                'currency' => 'INR',
                'language' => 'En',
                'detail_level' => 'Basic',
                'timeout' => '30',
                'hotel_list_ttl' => '0',
                'api_environment' => 'demo',
            ],
        );

        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return $request->url() === 'https://mg.example/SearchHotel'
                && $payload['Login']['AgencyCode'] === 'agency'
                && $payload['City'] === 'SG-SIN'
                && $payload['Hotels']['Code'] === ['SG10000002']
                && $payload['Rooms']['Room'][0]['NoOfAdults'] === '2'
                && $payload['Rooms']['Room'][0]['NoOfChild'] === '';
        });

        $this->assertCount(1, $result['hotels']);
        $this->assertSame('SG10000002', $result['hotels'][0]['hotel_id']);
        $this->assertSame('Amara Singapore', $result['hotels'][0]['hotel_name']);
        $this->assertSame(168.97, $result['hotels'][0]['min_rate']);
        $this->assertSame('rate-key-1', $result['hotels'][0]['rooms'][0]['rate_plan_id']);
        $this->assertSame('Breakfast', $result['hotels'][0]['rooms'][0]['meal_plan']);
        $this->assertTrue($result['hotels'][0]['rooms'][0]['breakfast_included']);
    }

    public function test_it_keeps_all_adults_in_one_room_when_one_room_is_requested(): void
    {
        $this->fakeEmptySearch();

        $this->search(paxInfo: '4|0', rooms: 1);

        $this->assertSearchRooms(function (array $rooms): bool {
            return count($rooms) === 1
                && $rooms[0]['NoOfAdults'] === '4'
                && $rooms[0]['NoOfChild'] === '';
        });
    }

    public function test_it_spreads_pax_evenly_across_the_requested_rooms(): void
    {
        $this->fakeEmptySearch();

        $this->search(paxInfo: '5|3', rooms: 3);

        $this->assertSearchRooms(function (array $rooms): bool {
            return count($rooms) === 3
                && $rooms[0]['NoOfAdults'] === '2' && $rooms[0]['NoOfChild'] === '2'
                && $rooms[1]['NoOfAdults'] === '2' && $rooms[1]['NoOfChild'] === '1'
                && $rooms[2]['NoOfAdults'] === '1' && $rooms[2]['NoOfChild'] === ''
                && $rooms[2]['RoomNo'] === '3';
        });
    }

    public function test_it_adds_rooms_when_children_exceed_two_per_room(): void
    {
        $this->fakeEmptySearch();

        // MG's payload only carries Child1Age/Child2Age, so three children need two rooms.
        $this->search(paxInfo: '2|3', rooms: 1);

        $this->assertSearchRooms(function (array $rooms): bool {
            return count($rooms) === 2
                && $rooms[0]['NoOfAdults'] === '1' && $rooms[0]['NoOfChild'] === '2'
                && $rooms[1]['NoOfAdults'] === '1' && $rooms[1]['NoOfChild'] === '1';
        });
    }

    public function test_it_never_requests_more_rooms_than_there_are_adults(): void
    {
        $this->fakeEmptySearch();

        $this->search(paxInfo: '2|0', rooms: 4);

        $this->assertSearchRooms(function (array $rooms): bool {
            return count($rooms) === 2
                && $rooms[0]['NoOfAdults'] === '1'
                && $rooms[1]['NoOfAdults'] === '1';
        });
    }

    private function fakeEmptySearch(): void
    {
        Http::fake([
            'https://mg.example/GetHotelList' => Http::response([
                'status' => true,
                'hotels' => [
                    'hotel' => [
                        ['code' => 'SG10000002', 'name' => 'Amara Singapore'],
                    ],
                ],
            ]),
            'https://mg.example/SearchHotel' => Http::response([
                'status' => true,
                'currency' => 'SGD',
                'hotels' => ['hotel' => []],
            ]),
        ]);
    }

    private function search(string $paxInfo, int $rooms): void
    {
        (new MgBedbankHotelAdapter())->fetchHotels(
            new HotelSearchRequest(
                cityName: 'Singapore',
                checkIn: '2026-08-01',
                checkOut: '2026-08-04',
                paxInfo: $paxInfo,
                rooms: $rooms,
            ),
            [
                'base_url' => 'https://mg.example',
                'agency_code' => 'agency',
                'username' => 'user',
                'password' => 'secret',
                'country_code' => 'SG',
                'city_code' => 'SG-SIN',
                'hotel_list_ttl' => '0',
                'api_environment' => 'demo',
            ],
        );
    }

    public function test_no_availability_error_returns_empty_hotel_list(): void
    {
        Http::fake([
            'https://mg.example/GetHotelList' => Http::response([
                'status' => true,
                'hotels' => [
                    'hotel' => [
                        ['code' => 'SG10000002', 'name' => 'Amara Singapore'],
                    ],
                ],
            ]),
            'https://mg.example/SearchHotel' => Http::response([
                'status' => false,
                'errorCode' => 'JRVXML060',
                'errorMessage' => 'No data matching with requested criteria',
            ]),
        ]);

        $result = (new MgBedbankHotelAdapter())->fetchHotels(
            new HotelSearchRequest(
                cityName: 'Singapore',
                checkIn: '2026-07-21',
                checkOut: '2026-07-25',
                paxInfo: '4|0',
            ),
            [
                'base_url' => 'https://mg.example',
                'agency_code' => 'agency',
                'username' => 'user',
                'password' => 'secret',
                'country_code' => 'SG',
                'city_code' => 'SG-SIN',
                'hotel_list_ttl' => '0',
                'api_environment' => 'demo',
            ],
        );

        $this->assertSame([], $result['hotels']);
    }

    /**
     * @param  callable(array<int, array<string, mixed>>): bool  $callback
     */
    private function assertSearchRooms(callable $callback): void
    {
        Http::assertSent(function (Request $request) use ($callback): bool {
            if (! str_ends_with($request->url(), '/SearchHotel')) {
                return false;
            }

            return $callback($request->data()['Rooms']['Room'] ?? []);
        });
    }
}
