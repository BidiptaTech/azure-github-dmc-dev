<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\Payment\SupplierCancellationStatusService;
use Tests\TestCase;

class SupplierCancellationStatusServiceTest extends TestCase
{
    public function test_allows_wallet_for_offline_orders(): void
    {
        $order = $this->makeOrder([
            'order_type' => 'offline',
            'type' => 'hotel',
            'booking_details' => json_encode([]),
        ]);

        $service = new SupplierCancellationStatusService();

        $this->assertTrue($service->allowsWalletCredit($order));
        $this->assertFalse($service->isBlockingWalletCredit($order));
    }

    public function test_blocks_wallet_while_pending_or_failed(): void
    {
        $order = $this->makeOrder([
            'order_type' => 'online',
            'type' => 'hotel',
            'booking_details' => json_encode([]),
        ]);

        $service = new SupplierCancellationStatusService();
        $service->markPending($order, 'Waiting on supplier');
        $this->assertSame(SupplierCancellationStatusService::PENDING, $service->statusOf($order));
        $this->assertFalse($service->allowsWalletCredit($order));

        $service->markFailed($order, 'API timeout');
        $this->assertSame(SupplierCancellationStatusService::FAILED, $service->statusOf($order));
        $this->assertFalse($service->allowsWalletCredit($order));
        $this->assertSame('API timeout', $service->read($order)['last_error']);
    }

    public function test_confirmed_and_manual_resolve_allow_wallet(): void
    {
        $order = $this->makeOrder([
            'order_type' => 'online',
            'type' => 'hotel',
            'booking_details' => json_encode([]),
        ]);

        $service = new SupplierCancellationStatusService();
        $service->markConfirmed($order, 'Cancelled');
        $this->assertTrue($service->allowsWalletCredit($order));

        $service->markFailed($order, 'Later failure');
        $this->assertFalse($service->allowsWalletCredit($order));

        $service->markResolvedManual($order, 'Supplier confirmed offline via email');
        $this->assertSame(SupplierCancellationStatusService::RESOLVED_MANUAL, $service->statusOf($order));
        $this->assertTrue($service->allowsWalletCredit($order));
        $this->assertSame('Supplier confirmed offline via email', $service->read($order)['resolved_reason']);
    }

    public function test_legacy_cancelled_at_is_treated_as_confirmed(): void
    {
        $order = $this->makeOrder([
            'order_type' => 'online',
            'type' => 'hotel',
            'booking_details' => json_encode([
                'cancelled_at' => '2026-10-01T00:00:00+00:00',
            ]),
        ]);

        $service = new SupplierCancellationStatusService();
        $this->assertSame(SupplierCancellationStatusService::CONFIRMED, $service->statusOf($order));
        $this->assertTrue($service->allowsWalletCredit($order));
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function makeOrder(array $attrs): Order
    {
        $order = new class extends Order
        {
            /** @var array<int, string> */
            protected $with = [];

            public function save(array $options = [])
            {
                return true;
            }
        };

        foreach ($attrs as $key => $value) {
            $order->setAttribute($key, $value);
        }

        return $order;
    }
}
