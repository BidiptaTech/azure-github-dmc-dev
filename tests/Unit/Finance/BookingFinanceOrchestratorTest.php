<?php

namespace Tests\Unit\Finance;

use App\Models\Tour;
use App\Services\Finance\BookingFinanceOrchestrator;
use App\Services\Finance\PaymentGatewayService;
use App\Services\Finance\SettlementService;
use App\Services\Finance\WalletService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class BookingFinanceOrchestratorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_skips_finance_when_payment_mode_not_set(): void
    {
        $orchestrator = new BookingFinanceOrchestrator(
            Mockery::mock(WalletService::class),
            Mockery::mock(SettlementService::class),
            Mockery::mock(PaymentGatewayService::class),
        );

        $tour = new Tour(['id' => 1, 'tour_id' => 100, 'dmc_id' => 42]);
        $request = Request::create('/test', 'POST', ['bookingType' => 'booking']);

        $result = $orchestrator->processAfterBooking($tour, $request, 220.0);

        $this->assertNull($result);
    }

    public function test_wallet_booking_delegates_to_wallet_service(): void
    {
        $walletService = Mockery::mock(WalletService::class);
        $walletService->shouldReceive('bookWithWallet')
            ->once()
            ->with(Mockery::on(function ($payload) {
                return $payload['wallet_owner_type'] === 'dmc'
                    && $payload['wallet_owner_id'] === 42
                    && $payload['selling_price'] === 220.0;
            }))
            ->andReturn([
                'success' => true,
                'data' => [
                    'finance_reference' => 'FIN-20260708-100002',
                    'settlement_status' => 'completed',
                ],
            ]);

        $orchestrator = new BookingFinanceOrchestrator(
            $walletService,
            Mockery::mock(SettlementService::class),
            Mockery::mock(PaymentGatewayService::class),
        );

        $tour = Mockery::mock(Tour::class)->makePartial();
        $tour->id = 1;
        $tour->tour_id = 100;
        $tour->dmc_id = 42;
        $tour->agent_id = 7;
        $tour->shouldReceive('update')->once()->andReturn(true);

        $request = Request::create('/test', 'POST', [
            'payment_mode' => 'wallet',
            'wallet_owner_type' => 'dmc',
            'wallet_owner_id' => 42,
            'supplier_api_id' => 'hotelbeds',
            'supplier_cost' => 100,
            'travclicks_markup' => 50,
            'dmc_commission' => 70,
            'selling_price' => 220,
        ]);

        $result = $orchestrator->processAfterBooking($tour, $request, 220.0);

        $this->assertEquals('FIN-20260708-100002', $result['data']['finance_reference']);
    }
}
