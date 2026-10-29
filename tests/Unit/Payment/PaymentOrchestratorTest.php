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

    protected function walletOrchestrator(WalletService $wallet, array $currentAttempt)
    {
        $orchestrator = Mockery::mock(PaymentOrchestrator::class, [
            Mockery::mock(OrderClassificationService::class),
            Mockery::mock(PaymentEligibilityService::class),
            Mockery::mock(PaymentSettingsService::class),
            Mockery::mock(CurrencySnapshotService::class),
            Mockery::mock(StripeConnectService::class),
            Mockery::mock(PaymentGatewayService::class),
            $wallet,
        ])->makePartial()->shouldAllowMockingProtectedMethods();
        $orchestrator->shouldReceive('updateAttempt')->andReturnNull();
        $orchestrator->shouldReceive('findAttemptByUuid')->andReturn($currentAttempt);

        return $orchestrator;
    }

    protected function callInitiateWallet(PaymentOrchestrator $orchestrator): array
    {
        $attempt = ['id' => 1, 'attempt_uuid' => 'u-1', 'idempotency_key' => 'k1', 'currency_code' => 'SGD', 'tour_id' => 5];
        $method = new \ReflectionMethod($orchestrator, 'initiateWallet');
        $method->setAccessible(true);

        return $method->invoke($orchestrator, new Tour(['tour_id' => 5, 'dmc_id' => 2, 'agent_id' => 3]), $attempt, [], 100.0);
    }

    public function test_wallet_payment_reserves_from_the_dmc_wallet_not_the_agent(): void
    {
        $wallet = Mockery::mock(WalletService::class);
        $wallet->shouldReceive('reserve')->once()->withArgs(function (array $d) {
            return $d['wallet_owner_type'] === 'dmc' && $d['wallet_owner_id'] === 2
                && $d['amount'] === 100.0 && ! isset($d['source_dmc_id']);
        })->andReturn(['data' => ['reservation_reference' => 'RSV1']]);
        $wallet->shouldNotReceive('releaseReservation');

        $orchestrator = $this->walletOrchestrator($wallet, ['status' => 'authorized']);
        $orchestrator->shouldReceive('completeWalletAfterSupplier')->once()->andReturn(['confirmed' => true]);

        $this->assertTrue($this->callInitiateWallet($orchestrator)['confirmed']);
    }

    public function test_failed_wallet_commit_releases_the_reservation_and_rethrows(): void
    {
        $wallet = Mockery::mock(WalletService::class);
        $wallet->shouldReceive('reserve')->once()->andReturn(['data' => ['reservation_reference' => 'RSV1']]);
        $wallet->shouldReceive('releaseReservation')->once()->withArgs(
            fn (array $d) => $d['reservation_reference'] === 'RSV1' && $d['release_amount'] === 100.0
        );

        $orchestrator = $this->walletOrchestrator($wallet, ['status' => 'authorized']);
        $orchestrator->shouldReceive('completeWalletAfterSupplier')->andThrow(new RuntimeException('commit failed'));

        $this->expectExceptionMessage('commit failed');
        $this->callInitiateWallet($orchestrator);
    }

    public function test_completed_attempt_is_not_released_on_late_failure(): void
    {
        $wallet = Mockery::mock(WalletService::class);
        $wallet->shouldReceive('reserve')->once()->andReturn(['data' => ['reservation_reference' => 'RSV1']]);
        $wallet->shouldNotReceive('releaseReservation'); // funds were already committed

        $orchestrator = $this->walletOrchestrator($wallet, ['status' => 'completed']);
        $orchestrator->shouldReceive('completeWalletAfterSupplier')->andThrow(new RuntimeException('late failure'));

        $this->expectExceptionMessage('late failure');
        $this->callInitiateWallet($orchestrator);
    }
}
