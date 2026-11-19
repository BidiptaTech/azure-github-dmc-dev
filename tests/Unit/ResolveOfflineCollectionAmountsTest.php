<?php

namespace Tests\Unit;

use App\Helpers\CommonHelper;
use Tests\TestCase;

class ResolveOfflineCollectionAmountsTest extends TestCase
{
    public function test_mix_tour_does_not_subtract_online_paid_from_offline_invoice(): void
    {
        $payments = [
            [
                'status' => 1,
                'amount' => 319.0,
                'payment_for' => 'online',
            ],
        ];

        // Offline services 517.30 (+ ceil) with offline tax 52 → invoice ~570.
        $result = CommonHelper::resolveOfflineCollectionAmounts(
            518.0,
            52.0,
            570.0,
            $payments,
            'mix',
            false,
            true,
            518.0
        );

        $this->assertEqualsWithDelta(518.0, $result['base_amount'], 0.01);
        $this->assertEqualsWithDelta(52.0, $result['tax_amount'], 0.01);
        $this->assertEqualsWithDelta(570.0, $result['final_amount'], 0.01);
        $this->assertEqualsWithDelta(0.0, $result['total_paid'], 0.01);
        $this->assertEqualsWithDelta(570.0, $result['remaining_amount'], 0.01);
        $this->assertEqualsWithDelta(319.0, $result['online_paid'], 0.01);
    }

    public function test_mix_uses_taxable_offline_base_when_tour_base_includes_online(): void
    {
        $result = CommonHelper::resolveOfflineCollectionAmounts(
            836.0,
            52.0,
            888.0,
            [
                ['status' => 1, 'amount' => 319.0, 'payment_for' => 'online'],
            ],
            'mix',
            false,
            true,
            517.0
        );

        $this->assertEqualsWithDelta(517.0, $result['base_amount'], 0.01);
        $this->assertEqualsWithDelta(569.0, $result['final_amount'], 0.01);
        $this->assertEqualsWithDelta(569.0, $result['remaining_amount'], 0.01);
    }
}
