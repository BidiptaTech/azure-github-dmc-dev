<?php

namespace Tests\Unit\Payment;

use App\Services\Payment\AcceptanceFlowService;
use App\Services\Payment\OrderClassificationService;
use Tests\TestCase;

class AcceptanceFlowServiceTest extends TestCase
{
    public function test_online_skips_enquiry_and_requires_full_payment(): void
    {
        $flow = (new AcceptanceFlowService(new OrderClassificationService))->describe(
            OrderClassificationService::ONLINE,
            false,
            800,
            0,
            'SGD',
        );

        $this->assertFalse($flow['allow_enquiry']);
        $this->assertFalse($flow['allow_negotiate']);
        $this->assertSame('full_payment', $flow['next_action']);
        $this->assertSame(0.0, $flow['negotiable_amount']);
        $this->assertSame(800.0, $flow['payable_now']);
    }

    public function test_mix_pays_online_before_offline_negotiation(): void
    {
        $service = new AcceptanceFlowService(new OrderClassificationService);

        $before = $service->describe(OrderClassificationService::MIX, false, 300, 700, 'SGD');
        $this->assertFalse($before['allow_negotiate']);
        $this->assertSame('pay_online', $before['next_action']);
        $this->assertSame(300.0, $before['payable_now']);
        $this->assertSame(700.0, $before['negotiable_amount']);
        $this->assertTrue($before['exclude_online_from_negotiation']);

        $after = $service->describe(OrderClassificationService::MIX, true, 300, 700, 'SGD');
        $this->assertTrue($after['allow_enquiry']);
        $this->assertTrue($after['allow_negotiate']);
        $this->assertSame('negotiate_offline', $after['next_action']);
        $this->assertSame(700.0, $after['negotiable_amount']);
    }

    public function test_offline_keeps_negotiation(): void
    {
        $flow = (new AcceptanceFlowService(new OrderClassificationService))->describe(
            OrderClassificationService::OFFLINE,
            false,
            0,
            450,
            'USD',
        );

        $this->assertTrue($flow['allow_negotiate']);
        $this->assertSame('negotiate', $flow['next_action']);
        $this->assertSame(450.0, $flow['negotiable_amount']);
    }
}
