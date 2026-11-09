<?php

namespace Tests\Unit\Payment;

use App\Models\Tour;
use App\Services\Finance\PaymentGatewayService;
use App\Services\Finance\WalletService;
use App\Services\Payment\CurrencySnapshotService;
use App\Services\Payment\FxConversionCharge;
use App\Services\Payment\OrderClassificationService;
use App\Services\Payment\PaymentEligibilityService;
use App\Services\Payment\PaymentOrchestrator;
use App\Services\Payment\PaymentSettingsService;
use App\Services\Payment\StripeConnectService;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

/**
 * Tour #3850: SGD tour (124 SGD) was charged 169.18 SGD because payment_received_currency
 * defaulted to USD, so 124 was treated as USD, converted to SGD and charged a 6.5% FX fee.
 */
class WalletAmountRegressionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function ordersTable(array $currencies, int $tourId = 987001): Tour
    {
        // Temp table shadows the real orders table for this connection only.
        Schema::create('orders', function ($t) {
            $t->temporary();
            $t->id();
            $t->integer('tour_id');
            $t->string('currency')->nullable();
            $t->timestamp('deleted_at')->nullable();
        });
        foreach ($currencies as $c) {
            \DB::table('orders')->insert(['tour_id' => $tourId, 'currency' => $c]);
        }

        return new Tour(['tour_id' => $tourId, 'payment_received_currency' => 'USD']);
    }

    public function test_order_currency_beats_the_usd_column_default(): void
    {
        $tour = $this->ordersTable(['SGD', 'SGD']);

        $this->assertSame('SGD', (new OrderClassificationService)->resolveTourCurrency($tour));
    }

    public function test_falls_back_to_tour_currency_when_orders_missing_or_mixed(): void
    {
        $service = new OrderClassificationService;

        $this->assertSame('USD', $service->resolveTourCurrency($this->ordersTable(['SGD', 'IDR'])));
        $this->assertSame('USD', $service->resolveTourCurrency(new Tour(['tour_id' => 987002, 'payment_received_currency' => 'USD'])));
    }

    public function test_same_currency_applies_no_conversion_or_fee(): void
    {
        $quoted = (new FxConversionCharge)->apply(124.0, 1.0, 6.5, false);

        $this->assertSame(124.0, $quoted['destination_amount']);
        $this->assertSame(0.0, $quoted['fee']);
    }

    public function test_cross_currency_conversion_still_charges_once(): void
    {
        // 124 USD -> SGD at 1.2810801878 plus 6.5%: the figure that was wrongly charged for an SGD tour.
        $quoted = (new FxConversionCharge)->apply(124.0, 1.2810801878, 6.5, true);

        $this->assertEquals(169.1794, $quoted['destination_amount']);
        $this->assertEquals(10.3255, $quoted['fee']);
    }

    protected function orchestrator(?WalletService $wallet = null)
    {
        return Mockery::mock(PaymentOrchestrator::class, [
            Mockery::mock(OrderClassificationService::class),
            Mockery::mock(PaymentEligibilityService::class),
            Mockery::mock(PaymentSettingsService::class),
            Mockery::mock(CurrencySnapshotService::class),
            Mockery::mock(StripeConnectService::class),
            Mockery::mock(PaymentGatewayService::class),
            $wallet ?? Mockery::mock(WalletService::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();
    }

    protected function row(PaymentOrchestrator $o, float $amount, string $currency, array $meta = [], float $requested = 0.0): array
    {
        $m = new \ReflectionMethod($o, 'walletPaymentRow');
        $m->setAccessible(true);

        return $m->invoke($o, $amount, $currency, 'CMT-1', 'uuid-1', $meta, $requested);
    }

    public function test_sgd_payment_records_the_exact_total_with_no_conversion(): void
    {
        $row = $this->row($this->orchestrator(), 124.0, 'SGD', ['order_amount' => 124.0, 'order_currency' => 'SGD', 'exchange_rate' => 1.0], 124.0);

        $this->assertSame(124.0, $row['amount']);
        $this->assertSame('SGD', $row['currency']);
        $this->assertSame(1, $row['exchange_rate']);
        $this->assertArrayNotHasKey('wallet_amount', $row);
    }

    public function test_converted_payment_keeps_order_amount_rate_and_wallet_debit(): void
    {
        $meta = ['order_amount' => 124.0, 'order_currency' => 'USD', 'exchange_rate' => 1.2810801878, 'conversion_charge' => 10.3255];
        $row = $this->row($this->orchestrator(), 169.1794, 'SGD', $meta, 169.1794);

        $this->assertSame(124.0, $row['amount']);          // outstanding is measured in the order currency
        $this->assertSame('USD', $row['currency']);
        $this->assertSame(1.2810801878, $row['exchange_rate']);
        $this->assertSame(169.1794, $row['wallet_amount']);
        $this->assertSame('SGD', $row['wallet_currency']);
        $this->assertSame(10.3255, $row['conversion_charge']);
    }

    public function test_partial_commit_scales_the_order_amount(): void
    {
        $meta = ['order_amount' => 100.0, 'order_currency' => 'USD', 'exchange_rate' => 2.0, 'conversion_charge' => 0.0];
        $row = $this->row($this->orchestrator(), 100.0, 'SGD', $meta, 200.0);

        $this->assertSame(50.0, $row['amount']);
    }

    public function test_duplicate_submission_does_not_reserve_again(): void
    {
        $wallet = Mockery::mock(WalletService::class);
        $wallet->shouldNotReceive('reserve');
        $wallet->shouldNotReceive('commitReservation');

        $orchestrator = $this->orchestrator($wallet);
        $orchestrator->shouldReceive('findAttemptByIdempotency')->with('same-key')->andReturn(['id' => 2, 'status' => 'completed']);

        $result = $orchestrator->initiate(new Tour(['tour_id' => 3850]), ['payment_method' => 'wallet', 'idempotency_key' => 'same-key']);

        $this->assertTrue($result['idempotent_replay']);
    }
}
