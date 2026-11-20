<?php

namespace Tests\Unit\Payment;

use App\Services\Payment\PaymentSettingsService;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class PaymentSettingsServiceTest extends TestCase
{
    public function test_offline_always_enabled_even_when_row_missing(): void
    {
        $service = new PaymentSettingsService;
        $settings = $service->get();

        $this->assertTrue($settings['offline_enabled']);
    }

    public function test_cannot_disable_offline(): void
    {
        if (! Schema::hasTable('payment_platform_settings')) {
            $this->markTestSkipped('payment_platform_settings not migrated');
        }

        $service = new PaymentSettingsService;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Offline payments cannot be disabled');
        $service->update(['offline_enabled' => false], 'test@example.com');
    }

    public function test_wallet_method_rejected_when_disabled(): void
    {
        $service = new class extends PaymentSettingsService
        {
            public function get(): array
            {
                return [
                    'offline_enabled' => true,
                    'wallet_enabled' => false,
                    'online_enabled' => false,
                    'stripe_mode' => 'test',
                    'stripe_configured' => false,
                ];
            }
        };

        $this->expectException(RuntimeException::class);
        $service->assertMethodAllowed('wallet');
    }

    public function test_stripe_mode_mismatch_not_configured(): void
    {
        config([
            'payment.stripe.secret_key' => 'sk_live_abc',
            'payment.stripe.publishable_key' => 'pk_live_abc',
        ]);

        $service = new PaymentSettingsService;
        $this->assertFalse($service->isStripeConfigured('test'));
    }
}
