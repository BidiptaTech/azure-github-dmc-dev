<?php

namespace Tests\Unit\Payment;

use App\Services\Payment\CurrencySnapshotService;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CurrencySnapshotServiceTest extends TestCase
{
    public function test_same_currency_snapshot_uses_identity_rate(): void
    {
        $service = new CurrencySnapshotService;
        $snap = $service->createSnapshot([
            'source_currency' => 'SGD',
            'destination_currency' => 'SGD',
            'source_amount' => 100,
        ]);

        $this->assertSame(1.0, (float) $snap['exchange_rate']);
        $this->assertSame(100.0, (float) $snap['destination_amount']);
        $this->assertSame('identity', $snap['rate_provider']);
    }

    public function test_explicit_rate_is_persisted_without_live_lookup(): void
    {
        $service = new CurrencySnapshotService;
        $snap = $service->createSnapshot([
            'source_currency' => 'INR',
            'destination_currency' => 'SGD',
            'source_amount' => 1000,
            'exchange_rate' => 0.016,
            'rate_provider' => 'test-fixed',
        ]);

        $this->assertSame(0.0, (float) ($snap['fee_or_spread'] ?? 0));
        $this->assertSame(0.016, (float) $snap['exchange_rate']);
        $this->assertEqualsWithDelta(16.0, (float) $snap['destination_amount'], 0.0001);
        $this->assertSame('test-fixed', $snap['rate_provider']);

        $charged = $service->createSnapshot([
            'source_currency' => 'INR',
            'destination_currency' => 'SGD',
            'source_amount' => 1000,
            'exchange_rate' => 0.016,
            'conversion_charge_percent' => 2.5,
            'rate_provider' => 'test-fixed',
        ]);
        $this->assertEqualsWithDelta(0.4, (float) $charged['fee_or_spread'], 0.0001);
        $this->assertEqualsWithDelta(16.4, (float) $charged['destination_amount'], 0.0001);

        if (! empty($snap['id']) && Schema::hasTable('currency_conversion_snapshots')) {
            $loaded = $service->find((int) $snap['id']);
            $this->assertNotNull($loaded);
            $this->assertEqualsWithDelta(0.016, (float) $loaded['exchange_rate'], 0.0000001);
        }
    }
}
