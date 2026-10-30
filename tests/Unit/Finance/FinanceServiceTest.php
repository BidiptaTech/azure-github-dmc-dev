<?php

namespace Tests\Unit\Finance;

use App\Services\Finance\FinanceService;
use App\Services\Finance\WalletLedgerService;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class FinanceServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_post_routes_wallet_topup_locally(): void
    {
        $ledger = Mockery::mock(WalletLedgerService::class);
        $ledger->shouldReceive('topup')
            ->once()
            ->with(Mockery::on(fn ($p) => ($p['wallet_owner_id'] ?? null) === 42 && isset($p['idempotency_key'])))
            ->andReturn([
                'success' => true,
                'message' => 'OK',
                'data' => ['finance_reference' => 'TOP-20261014-1'],
            ]);

        $service = new FinanceService($ledger);
        $result = $service->post('/wallet/topup', [
            'wallet_owner_type' => 'dmc',
            'wallet_owner_id' => 42,
            'topup_amount' => '100.00',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('TOP-20261014-1', $result['data']['finance_reference']);
    }

    public function test_post_throws_on_unknown_endpoint(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown finance endpoint');

        (new FinanceService(Mockery::mock(WalletLedgerService::class)))
            ->post('/nope', []);
    }

    public function test_health_check_uses_laravel_engine(): void
    {
        $ledger = Mockery::mock(WalletLedgerService::class);
        $ledger->shouldReceive('healthCheck')->once()->andReturn([
            'status' => 'healthy',
            'database' => 'connected',
            'engine' => 'laravel',
        ]);

        $result = (new FinanceService($ledger))->healthCheck();

        $this->assertEquals('healthy', $result['status']);
        $this->assertEquals('laravel', $result['engine']);
    }

    public function test_commission_calculate_local(): void
    {
        $ledger = new WalletLedgerService;
        $service = new FinanceService($ledger);
        $result = $service->post('/commission/calculate', [
            'supplier_cost' => 100,
            'travclicks_markup_percent' => 50,
            'dmc_commission_percent' => 46.67,
            'currency_code' => 'USD',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals(50.0, $result['data']['travclicks_markup']);
        $this->assertEqualsWithDelta(70.005, $result['data']['dmc_commission'], 0.01);
    }
}
