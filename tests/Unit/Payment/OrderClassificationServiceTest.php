<?php

namespace Tests\Unit\Payment;

use App\Services\Payment\OrderClassificationService;
use Tests\TestCase;

class OrderClassificationServiceTest extends TestCase
{
    protected OrderClassificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderClassificationService;
    }

    public function test_classifies_online_only(): void
    {
        $this->assertSame(
            OrderClassificationService::ONLINE,
            $this->service->classifyFromTypes(['online', 'online'])
        );
    }

    public function test_classifies_offline_only(): void
    {
        $this->assertSame(
            OrderClassificationService::OFFLINE,
            $this->service->classifyFromTypes(['offline', 'offline'])
        );
    }

    public function test_classifies_mix(): void
    {
        $this->assertSame(
            OrderClassificationService::MIX,
            $this->service->classifyFromTypes(['online', 'offline'])
        );
    }

    public function test_empty_orders_are_empty(): void
    {
        $this->assertSame(
            OrderClassificationService::EMPTY,
            $this->service->classifyFromTypes([])
        );
    }

    public function test_online_plus_blank_type_is_mix(): void
    {
        $this->assertSame(
            OrderClassificationService::MIX,
            $this->service->classifyFromTypes(['online', null])
        );
    }

    public function test_null_types_treated_as_offline_compat(): void
    {
        $this->assertSame(
            OrderClassificationService::OFFLINE,
            $this->service->classifyFromTypes([null, '', 'OFFLINE'])
        );
    }

    public function test_service_rows_sum_to_the_agent_total_even_when_cost_price_is_zero(): void
    {
        $this->assertSame(250.0, $this->service->sumServiceSellAmount([
            ['totalPrice' => 200, 'price' => 200],
            ['price' => 50],
        ]));
    }

    public function test_payment_recipient_routing(): void
    {
        $this->assertSame('travclicks', $this->service->resolvePaymentRecipient('supplier_initial'));
        $this->assertSame('dmc', $this->service->resolvePaymentRecipient('dmc_collection'));
    }
}
