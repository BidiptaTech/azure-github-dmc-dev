<?php

namespace Tests\Unit\Finance;

use App\Services\Finance\FinanceService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class FinanceServiceTest extends TestCase
{
    public function test_post_sends_bearer_token_and_returns_data(): void
    {
        config([
            'finance.base_url' => 'http://finance.test/api',
            'finance.bearer_token' => 'test-token',
            'finance.timeout_seconds' => 10,
        ]);

        Http::fake([
            'http://finance.test/api/wallet/topup' => Http::response([
                'success' => true,
                'message' => 'OK',
                'data' => ['finance_reference' => 'FIN-20260708-100001'],
            ], 200),
        ]);

        $service = new FinanceService;
        $result = $service->post('/wallet/topup', [
            'wallet_owner_type' => 'dmc',
            'wallet_owner_id' => 42,
            'topup_amount' => '100.00',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('FIN-20260708-100001', $result['data']['finance_reference']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer test-token')
                && $request->url() === 'http://finance.test/api/wallet/topup'
                && isset($request['idempotency_key']);
        });
    }

    public function test_post_throws_on_finance_service_failure(): void
    {
        config([
            'finance.base_url' => 'http://finance.test/api',
            'finance.bearer_token' => 'test-token',
        ]);

        Http::fake([
            'http://finance.test/api/wallet/booking' => Http::response([
                'success' => false,
                'message' => 'Insufficient wallet balance',
                'error_code' => 'INSUFFICIENT_BALANCE',
            ], 402),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Insufficient wallet balance');

        (new FinanceService)->post('/wallet/booking', [
            'tour_id' => 1,
            'wallet_owner_type' => 'dmc',
            'wallet_owner_id' => 42,
            'supplier_api_id' => 'hotelbeds',
            'supplier_cost' => 100,
            'travclicks_markup' => 50,
            'dmc_commission' => 70,
            'selling_price' => 220,
        ]);
    }

    public function test_health_check_get_request(): void
    {
        config(['finance.base_url' => 'http://finance.test/api']);

        Http::fake([
            'http://finance.test/api/health' => Http::response([
                'status' => 'healthy',
                'database' => 'connected',
            ], 200),
        ]);

        $result = (new FinanceService)->healthCheck();

        $this->assertEquals('healthy', $result['status']);
    }
}
