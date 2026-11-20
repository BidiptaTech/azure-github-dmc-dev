<?php

namespace Tests\Unit\Finance;

use App\Services\Finance\WalletLedgerService;
use Tests\TestCase;

class WalletLedgerServiceTest extends TestCase
{
    public function test_application_fee_uses_markup_when_provided(): void
    {
        $service = new WalletLedgerService;
        $this->assertEquals(25.0, $service->applicationFeeAmount(100, 25));
        $this->assertEquals(100.0, $service->applicationFeeAmount(100, 150));
    }

    public function test_application_fee_percent_fallback(): void
    {
        config(['payment.stripe.application_fee_percent' => 10]);
        $service = new WalletLedgerService;
        $this->assertEquals(10.0, $service->applicationFeeAmount(100, null));
    }

    public function test_calculate_commission_math(): void
    {
        $result = (new WalletLedgerService)->calculateCommission([
            'supplier_cost' => 100,
            'travclicks_markup_percent' => 50,
            'dmc_commission_percent' => 46.67,
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals(150.0, $result['data']['travclicks_price']);
        $this->assertEqualsWithDelta(220.005, $result['data']['selling_price'], 0.02);
    }
}
