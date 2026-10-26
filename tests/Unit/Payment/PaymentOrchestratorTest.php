<?php

namespace Tests\Unit\Payment;

use App\Models\Tour;
use App\Services\Finance\PaymentGatewayService;
use App\Services\Finance\WalletService;
use App\Services\Payment\CurrencySnapshotService;
use App\Services\Payment\OrderClassificationService;
use App\Services\Payment\PaymentEligibilityService;
use App\Services\Payment\PaymentOrchestrator;
use App\Services\Payment\PaymentSettingsService;
use App\Services\Payment\StripeConnectService;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class PaymentOrchestratorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_rejects_tampered_amount_on_supplier_initial(): void
    {
        $eligibility = [
            'order_classification' => OrderClassificationService::ONLINE,
            'payment_stage' => 'supplier_initial',
            'payment_recipient' => 'travclicks',
            'currency' => 'SGD',
            'payable_amount' => 500.0,
            'outstanding_balance' => 500.0,
            'amount_readonly' => true,
            'methods' => [
                [
                    'code' => 'wallet',
                    'available' => true,
                    'reason' => null,
                ],
            ],
        ];

        $eligibilityService = Mockery::mock(PaymentEligibilityService::class);
        $eligibilityService->shouldReceive('forTour')->andReturn($eligibility);

        $settings = Mockery::mock(PaymentSettingsService::class);
        $settings->shouldReceive('assertMethodAllowed')->once();

        $orchestrator = new PaymentOrchestrator(
            Mockery::mock(OrderClassificationService::class),
            $eligibilityService,
            $settings,
            Mockery::mock(CurrencySnapshotService::class),
            Mockery::mock(StripeConnectService::class),
            Mockery::mock(PaymentGatewayService::class),
            Mockery::mock(WalletService::class),
        );

        $tour = new Tour(['tour_id' => 1, 'dmc_id' => 2, 'agent_id' => 3]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Payment amount cannot be modified');
        $orchestrator->initiate($tour, [
            'payment_method' => 'wallet',
            'amount' => 1.0,
            'idempotency_key' => 'test-idempotency-key-001',
        ]);
    }

    public function test_rejects_unavailable_method(): void
    {
        $eligibilityService = Mockery::mock(PaymentEligibilityService::class);
        $eligibilityService->shouldReceive('forTour')->andReturn([
            'order_classification' => OrderClassificationService::OFFLINE,
            'payment_stage' => 'dmc_collection',
            'payment_recipient' => 'dmc',
            'currency' => 'USD',
            'payable_amount' => 100.0,
            'outstanding_balance' => 100.0,
            'amount_readonly' => false,
            'methods' => [
                ['code' => 'wallet', 'available' => false, 'reason' => 'Wallet payments are disabled.'],
            ],
        ]);

        $settings = Mockery::mock(PaymentSettingsService::class);
        $settings->shouldReceive('assertMethodAllowed')->once();

        $orchestrator = new PaymentOrchestrator(
            Mockery::mock(OrderClassificationService::class),
            $eligibilityService,
            $settings,
            Mockery::mock(CurrencySnapshotService::class),
            Mockery::mock(StripeConnectService::class),
            Mockery::mock(PaymentGatewayService::class),
            Mockery::mock(WalletService::class),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Wallet payments are disabled');
        $orchestrator->initiate(new Tour(['tour_id' => 5]), [
            'payment_method' => 'wallet',
            'idempotency_key' => 'test-idempotency-key-002',
        ]);
    }
}
