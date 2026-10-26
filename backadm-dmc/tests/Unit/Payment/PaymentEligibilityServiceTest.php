<?php

namespace Tests\Unit\Payment;

use App\Models\Tour;
use App\Services\Finance\WalletQueryService;
use App\Services\Payment\OrderClassificationService;
use App\Services\Payment\PaymentEligibilityService;
use App\Services\Payment\PaymentSettingsService;
use App\Services\Payment\StripeConnectService;
use Mockery;
use Tests\TestCase;

class PaymentEligibilityServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_supplier_initial_hides_offline_methods_for_online_order(): void
    {
        $classification = Mockery::mock(OrderClassificationService::class);
        $classification->shouldReceive('classifyTour')->andReturn(OrderClassificationService::ONLINE);
        $classification->shouldReceive('resolvePaymentStage')->andReturn('supplier_initial');
        $classification->shouldReceive('resolvePaymentRecipient')->andReturn('travclicks');
        $classification->shouldReceive('amountBreakdown')->andReturn([
            'classification' => OrderClassificationService::ONLINE,
            'online_amount' => 500.0,
            'offline_amount' => 0.0,
            'total_amount' => 500.0,
            'currency' => 'SGD',
        ]);

        $settings = Mockery::mock(PaymentSettingsService::class);
        $settings->shouldReceive('get')->andReturn([
            'offline_enabled' => true,
            'wallet_enabled' => true,
            'online_enabled' => true,
            'stripe_mode' => 'test',
            'stripe_configured' => true,
        ]);

        $stripe = Mockery::mock(StripeConnectService::class);
        $walletQuery = Mockery::mock(WalletQueryService::class);

        $service = Mockery::mock(PaymentEligibilityService::class, [
            $classification,
            $settings,
            $stripe,
            $walletQuery,
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('dmcScopedAgentBalance')->andReturn(1000.0);

        $tour = new Tour([
            'tour_id' => 99,
            'tour_status' => 'Tentative',
            'order_classification' => OrderClassificationService::ONLINE,
            'dmc_id' => 10,
            'agent_id' => 20,
            'payment_received_currency' => 'SGD',
            'payment_details' => '[]',
        ]);

        $result = $service->forTour($tour);

        $this->assertSame('supplier_initial', $result['payment_stage']);
        $this->assertSame('travclicks', $result['payment_recipient']);
        $this->assertTrue($result['amount_readonly']);
        $this->assertEquals(500.0, $result['payable_amount']);

        $cash = collect($result['methods'])->firstWhere('code', 'cash');
        $this->assertFalse($cash['available']);

        $wallet = collect($result['methods'])->firstWhere('code', 'wallet');
        $this->assertTrue($wallet['available']);

        $stripeMethod = collect($result['methods'])->firstWhere('code', 'stripe');
        $this->assertTrue($stripeMethod['available']);
    }

    public function test_wallet_unavailable_when_dmc_allocation_insufficient(): void
    {
        $classification = Mockery::mock(OrderClassificationService::class);
        $classification->shouldReceive('classifyTour')->andReturn(OrderClassificationService::OFFLINE);
        $classification->shouldReceive('resolvePaymentStage')->andReturn('dmc_collection');
        $classification->shouldReceive('resolvePaymentRecipient')->andReturn('dmc');
        $classification->shouldReceive('amountBreakdown')->andReturn([
            'classification' => OrderClassificationService::OFFLINE,
            'online_amount' => 0.0,
            'offline_amount' => 200.0,
            'total_amount' => 200.0,
            'currency' => 'USD',
        ]);

        $settings = Mockery::mock(PaymentSettingsService::class);
        $settings->shouldReceive('get')->andReturn([
            'offline_enabled' => true,
            'wallet_enabled' => true,
            'online_enabled' => false,
            'stripe_mode' => 'test',
            'stripe_configured' => false,
        ]);

        $stripe = Mockery::mock(StripeConnectService::class);
        $stripe->shouldReceive('isReadyForCharges')->andReturn(false);

        $service = Mockery::mock(PaymentEligibilityService::class, [
            $classification,
            $settings,
            $stripe,
            Mockery::mock(WalletQueryService::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('dmcScopedAgentBalance')->andReturn(50.0);

        $tour = new Tour([
            'tour_id' => 11,
            'tour_status' => 'Confirmed',
            'order_classification' => OrderClassificationService::OFFLINE,
            'dmc_id' => 10,
            'agent_id' => 20,
            'payment_received_currency' => 'USD',
            'payment_details' => '[]',
        ]);

        $result = $service->forTour($tour);
        $wallet = collect($result['methods'])->firstWhere('code', 'wallet');

        $this->assertFalse($wallet['available']);
        $this->assertStringContainsString('Insufficient', (string) $wallet['reason']);
    }

    public function test_wallet_available_when_balance_covers_outstanding_despite_finance_captured_flag(): void
    {
        $classification = Mockery::mock(OrderClassificationService::class);
        $classification->shouldReceive('classifyTour')->andReturn(OrderClassificationService::OFFLINE);
        $classification->shouldReceive('resolvePaymentStage')->andReturn('dmc_collection');
        $classification->shouldReceive('resolvePaymentRecipient')->andReturn('dmc');
        $classification->shouldReceive('amountBreakdown')->andReturn([
            'classification' => OrderClassificationService::OFFLINE,
            'online_amount' => 0.0,
            'offline_amount' => 2300.0,
            'total_amount' => 2300.0,
            'currency' => 'SGD',
        ]);
        $classification->shouldReceive('resolveTourCurrency')->andReturn('SGD');

        $settings = Mockery::mock(PaymentSettingsService::class);
        $settings->shouldReceive('get')->andReturn([
            'offline_enabled' => true,
            'wallet_enabled' => true,
            'online_enabled' => false,
            'stripe_mode' => 'test',
            'stripe_configured' => false,
        ]);

        $stripe = Mockery::mock(StripeConnectService::class);
        $stripe->shouldReceive('isReadyForCharges')->andReturn(false);

        $service = Mockery::mock(PaymentEligibilityService::class, [
            $classification,
            $settings,
            $stripe,
            Mockery::mock(WalletQueryService::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('dmcScopedAgentBalance')->andReturn(2700.0);

        $tour = new Tour([
            'tour_id' => 4301,
            'tour_status' => 'Confirmed',
            'order_classification' => OrderClassificationService::OFFLINE,
            'dmc_id' => 4,
            'agent_id' => 72,
            'selling_price' => 2300,
            'payment_status' => 'captured',
            'payment_received_amount' => 2300,
            'payment_received_currency' => 'SGD',
            'payment_details' => null,
        ]);

        $result = $service->forTour($tour);
        $wallet = collect($result['methods'])->firstWhere('code', 'wallet');

        $this->assertEquals(2300.0, $result['payable_amount']);
        $this->assertTrue($wallet['available']);
        $this->assertEquals(2700.0, $wallet['wallet']['available_balance']);
    }

    public function test_wallet_uses_requested_amount_when_provided(): void
    {
        $classification = Mockery::mock(OrderClassificationService::class);
        $classification->shouldReceive('classifyTour')->andReturn(OrderClassificationService::OFFLINE);
        $classification->shouldReceive('resolvePaymentStage')->andReturn('dmc_collection');
        $classification->shouldReceive('resolvePaymentRecipient')->andReturn('dmc');
        $classification->shouldReceive('amountBreakdown')->andReturn([
            'classification' => OrderClassificationService::OFFLINE,
            'online_amount' => 0.0,
            'offline_amount' => 2300.0,
            'total_amount' => 2300.0,
            'currency' => 'SGD',
        ]);

        $settings = Mockery::mock(PaymentSettingsService::class);
        $settings->shouldReceive('get')->andReturn([
            'offline_enabled' => true,
            'wallet_enabled' => true,
            'online_enabled' => false,
            'stripe_mode' => 'test',
            'stripe_configured' => false,
        ]);

        $service = Mockery::mock(PaymentEligibilityService::class, [
            $classification,
            $settings,
            Mockery::mock(StripeConnectService::class),
            Mockery::mock(WalletQueryService::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('dmcScopedAgentBalance')->andReturn(500.0);

        $tour = new Tour([
            'tour_id' => 12,
            'tour_status' => 'Confirmed',
            'order_classification' => OrderClassificationService::OFFLINE,
            'dmc_id' => 4,
            'agent_id' => 72,
            'payment_received_currency' => 'SGD',
            'payment_details' => '[]',
        ]);

        $ok = $service->forTour($tour, 400.0);
        $this->assertTrue(collect($ok['methods'])->firstWhere('code', 'wallet')['available']);

        $short = $service->forTour($tour, 600.0);
        $this->assertFalse(collect($short['methods'])->firstWhere('code', 'wallet')['available']);
    }

    public function test_commission_is_markup_only_after_the_tour_is_fully_paid(): void
    {
        $classification = Mockery::mock(OrderClassificationService::class);
        $classification->shouldReceive('amountBreakdown')->andReturn([
            'total_amount' => 1000.0,
            'currency' => 'SGD',
        ]);
        $classification->shouldReceive('resolveTourCurrency')->andReturn('SGD');

        $service = new PaymentEligibilityService(
            $classification,
            Mockery::mock(PaymentSettingsService::class),
            Mockery::mock(StripeConnectService::class),
            Mockery::mock(WalletQueryService::class),
        );

        $partial = new Tour([
            'tour_id' => 0,
            'markup' => 1,
            'markup_type' => 'percentage',
            'markup_amount' => 10,
            'payment_details' => json_encode([['status' => 1, 'amount' => 400]]),
        ]);
        $this->assertNull($service->fullyPaidMarkup($partial));

        $pending = new Tour([
            'tour_id' => 0,
            'markup' => 1,
            'markup_type' => 'percentage',
            'markup_amount' => 10,
            'payment_details' => json_encode([['status' => 0, 'amount' => 1000]]),
        ]);
        $this->assertNull($service->fullyPaidMarkup($pending));

        $paid = new Tour([
            'tour_id' => 0,
            'markup' => 1,
            'markup_type' => 'percentage',
            'markup_amount' => 10,
            'payment_details' => json_encode([['status' => 1, 'amount' => 1000]]),
        ]);
        $row = $service->fullyPaidMarkup($paid);

        $this->assertNotNull($row);
        $this->assertSame(100.0, $row['dmc_commission']);
        $this->assertSame('10%', $row['markup_label']);
        $this->assertSame(1000.0, $row['charged_amount']);
    }
}
