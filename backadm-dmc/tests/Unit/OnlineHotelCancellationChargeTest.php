<?php

namespace Tests\Unit;

use App\Helpers\CommonHelper;
use Carbon\Carbon;
use Tests\TestCase;

class OnlineHotelCancellationChargeTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_free_cancellation_before_policy_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-11 10:00:00'));

        $online = [
            'currency' => 'SGD',
            'check_in' => '2026-12-01',
            'hotel' => ['name' => 'Amara Singapore', 'check_in_time' => '14:00:00'],
            'room' => [
                'cancellation_policy_type' => 'Flexi',
                'cancellation_policies' => [
                    [
                        'fromDate' => '2026-11-29',
                        'toDate' => '2026-11-30',
                        'percent' => '100.00',
                        'noShow' => false,
                    ],
                    [
                        'fromDate' => '2026-12-01',
                        'toDate' => '2026-12-01',
                        'percent' => '100.00',
                        'noShow' => true,
                    ],
                ],
            ],
        ];

        $calc = CommonHelper::calculateOnlineHotelCancellationCharge($online, 319.0, 'SGD', 'Amara Singapore');

        $this->assertTrue($calc['free']);
        $this->assertSame(0.0, $calc['cancellation_charge']);
        $this->assertEqualsWithDelta(319.0, $calc['suggested_refund'], 0.01);
        $this->assertStringContainsString('Free cancellation until', $calc['summary']);
        $this->assertStringContainsString('Flexible', $calc['refund_policy']);
        $this->assertStringContainsString('100% of stay', $calc['refund_policy']);
    }

    public function test_full_charge_inside_percent_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-29 12:00:00'));

        $online = [
            'currency' => 'SGD',
            'check_in' => '2026-12-01',
            'hotel' => ['name' => 'Amara Singapore'],
            'room' => [
                'cancellation_policy_type' => 'Flexi',
                'cancellation_policies' => [
                    [
                        'fromDate' => '2026-11-29',
                        'toDate' => '2026-11-30',
                        'percent' => '100.00',
                        'noShow' => false,
                    ],
                ],
            ],
        ];

        $calc = CommonHelper::calculateOnlineHotelCancellationCharge($online, 319.0, 'SGD');

        $this->assertFalse($calc['free']);
        $this->assertEqualsWithDelta(319.0, $calc['cancellation_charge'], 0.01);
        $this->assertSame(0.0, $calc['suggested_refund']);
    }

    public function test_order_payload_routes_to_online_policy_calc(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-11 10:00:00'));

        $order = (object) [
            'currency' => 'SGD',
            'type' => 'hotel',
            'data' => [[
                'isOnlineHotel' => true,
                'totalPrice' => 319,
                'currency' => 'SGD',
                'hotel_name' => 'Amara Singapore',
                'onlineHotelBooking' => [
                    'currency' => 'SGD',
                    'check_in' => '2026-12-01',
                    'hotel' => ['name' => 'Amara Singapore'],
                    'room' => [
                        'cancellation_policy_type' => 'Flexi',
                        'cancellation_policies' => [
                            [
                                'fromDate' => '2026-11-29',
                                'toDate' => '2026-11-30',
                                'percent' => '100.00',
                                'noShow' => false,
                            ],
                        ],
                    ],
                    'markup' => [
                        'dmc_rule' => ['type' => 'percentage', 'amount' => 10],
                    ],
                ],
            ]],
        ];

        $calc = CommonHelper::calculateHotelCancellationCharge($order, 319.0);

        $this->assertTrue($calc['applicable']);
        $this->assertTrue($calc['free']);
        $this->assertNotSame('', $calc['refund_policy']);
        $this->assertSame('Amara Singapore', $calc['hotel_name']);
    }
}
