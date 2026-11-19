<?php

namespace Tests\Unit\Payment;

use App\Models\Tour;
use App\Services\Payment\FxConversionCharge;
use Tests\TestCase;

class FxConversionChargeTest extends TestCase
{
    public function test_percent_comes_from_config_only(): void
    {
        config(['payment.fx_conversion_charge_percent' => 11]);

        $service = new FxConversionCharge();
        $this->assertSame(11.0, $service->percent());
        $this->assertSame(11.0, $service->percentForTour(new Tour(['destination' => 'Singapore'])));
    }

    public function test_empty_config_disables_fee_without_gateway_fallback(): void
    {
        config(['payment.fx_conversion_charge_percent' => null]);

        $this->assertSame(0.0, (new FxConversionCharge())->percent());
    }

    public function test_effective_rate_is_base_plus_percent_fee(): void
    {
        config(['payment.fx_conversion_charge_percent' => 11]);

        $service = new FxConversionCharge();
        // 14165.74 + (14165.74 * 11%) = 15723.9714
        $this->assertEqualsWithDelta(15723.9714, $service->effectiveRate(14165.74, 11), 0.0001);
        $effective = $service->effectiveRate(14165.74, 11);
        $this->assertSame((float) round(9.91 * $effective), $service->paidInAmount(9.91, 14165.74, 11, 'IDR'));
    }

    public function test_payment_display_uses_effective_rate_for_paid_in(): void
    {
        config(['payment.fx_conversion_charge_percent' => 11]);

        $service = new FxConversionCharge();
        $row = $service->paymentDisplay([
            'amount' => 10.0,
            'currency' => 'SGD',
            'wallet_amount' => 1.0, // ignored when SGD × effective can be computed
            'wallet_currency' => 'IDR',
            'exchange_rate' => 14165.74,
            'conversion_charge_percent' => 11,
        ], 'SGD');

        $this->assertTrue($row['show_rate']);
        $this->assertSame(15723.9714, $row['rate_per_sgd']);
        $this->assertSame('IDR', $row['rate_currency']);
        $this->assertSame(157240.0, $row['paid_in']);
        $this->assertSame('IDR', $row['paid_in_currency']);
    }
}
