<?php

namespace Tests\Unit\Payment;

use App\Services\OnlinePricing\MarkupRule;
use App\Services\Payment\CommissionBreakdownService;
use App\Services\Payment\OrderClassificationService;
use Tests\TestCase;

class CommissionBreakdownServiceTest extends TestCase
{
    public function test_online_splits_api_cost_travclicks_commission_and_dmc_commission(): void
    {
        $service = new CommissionBreakdownService(new OrderClassificationService);
        $admin = new MarkupRule('percentage', 10, 'admin');
        $dmc = new MarkupRule('percentage', 20, 'dmc');

        // API 100 + 10% Travclicks = 110 shown to DMC. DMC + 20% = 132 agent price.
        $split = $service->split(132, true, $admin, $dmc);

        $this->assertEqualsWithDelta(100.0, $split['api_cost'], 0.02);
        $this->assertEqualsWithDelta(10.0, $split['travclicks_commission'], 0.02);
        $this->assertEqualsWithDelta(110.0, $split['dmc_visible_price'], 0.02);
        $this->assertEqualsWithDelta(22.0, $split['dmc_commission'], 0.02);
        $this->assertSame(132.0, $split['agent_price']);
    }

    public function test_offline_has_no_travclicks_commission_or_api_cost(): void
    {
        $service = new CommissionBreakdownService(new OrderClassificationService);
        $dmc = new MarkupRule('percentage', 25, 'dmc');

        $split = $service->split(125, false, new MarkupRule('percentage', 50, 'admin'), $dmc);

        $this->assertNull($split['api_cost']);
        $this->assertSame(0.0, $split['travclicks_commission']);
        $this->assertEqualsWithDelta(100.0, $split['dmc_visible_price'], 0.02);
        $this->assertEqualsWithDelta(25.0, $split['dmc_commission'], 0.02);
    }
}
