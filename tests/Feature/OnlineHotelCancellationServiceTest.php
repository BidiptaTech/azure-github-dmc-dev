<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\HotelSuppliers\OnlineHotelCancellationService;
use App\Services\SupplierConfigResolver;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class OnlineHotelCancellationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_skips_when_booking_details_empty(): void
    {
        $order = $this->makeOrder([
            'type' => 'hotel',
            'order_type' => 'online',
            'booking_details' => null,
        ]);

        $result = app(OnlineHotelCancellationService::class)->cancelIfApplicable($order);

        $this->assertNull($result);
        Http::assertNothingSent();
    }

    public function test_it_skips_when_book_response_was_not_successful(): void
    {
        $order = $this->makeOrder([
            'type' => 'hotel',
            'order_type' => 'online',
            'booking_details' => json_encode([
                'supplier_code' => 'mg_bedbank',
                'agency_booking_id' => 'ONLINE-1',
                'book_response' => [
                    'status' => false,
                    'errorMessage' => 'Booking failed',
                ],
            ]),
        ]);

        $result = app(OnlineHotelCancellationService::class)->cancelIfApplicable($order);

        $this->assertNull($result);
        Http::assertNothingSent();
    }

    public function test_it_cancels_confirmed_mg_bedbank_booking(): void
    {
        Http::fake([
            'https://mg.example/CancelReservation' => Http::response([
                'status' => true,
                'responseTime' => 100,
                'sessionID' => 'session-cancel',
                'bookingDetails' => [
                    'mgBookingID' => 'AGSG0537622610055198',
                    'agencyBookingID' => 'ONLINE-4276-9129-0',
                    'mgBookingVersionID' => 'HB2610074652',
                    'agencyVoucherNo' => 'VAGHB2610074652',
                    'status' => 'CANCELCONF',
                    'cancelDate' => '2026-10-15',
                    'cancellationPolicies' => [
                        'cancellationCharges' => [
                            'amount' => '0.00',
                            'b2BMarkup' => '0.00',
                            'grossAmount' => '0.00',
                            'currency' => 'SGD',
                        ],
                    ],
                ],
            ]),
        ]);

        $this->app->instance(SupplierConfigResolver::class, $this->fakeSupplierConfig());

        $order = $this->makeOrder([
            'id' => 99,
            'booking_id' => 9129,
            'type' => 'hotel',
            'order_type' => 'online',
            'booking_details' => json_encode([
                'supplier_code' => 'mg_bedbank',
                'api_environment' => 'demo',
                'agency_booking_id' => 'ONLINE-4276-9129-0',
                'book_response' => [
                    'status' => true,
                    'bookingDetails' => [
                        'mgBookingID' => 'AGSG0537622610055198',
                        'agencyBookingID' => 'ONLINE-4276-9129-0',
                        'status' => 'CONF',
                    ],
                ],
            ]),
        ]);

        $result = app(OnlineHotelCancellationService::class)->cancelIfApplicable($order);

        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return str_ends_with($request->url(), '/CancelReservation')
                && $payload['MGBookingID'] === 'AGSG0537622610055198'
                && $payload['AgencyBookingID'] === 'ONLINE-4276-9129-0'
                && $payload['SimulationFlag'] === false
                && ! array_key_exists('CancelDate', $payload);
        });

        $this->assertSame('CANCELCONF', $result['status']);
        $this->assertFalse($result['already_cancelled']);

        $stored = json_decode((string) $order->booking_details, true);
        $this->assertNotEmpty($stored['cancelled_at'] ?? null);
        $this->assertSame('CANCELCONF', $stored['cancellation']['status'] ?? null);
        $this->assertArrayHasKey('cancel_response', $stored['cancellation'] ?? []);
    }

    public function test_unsupported_supplier_message_omits_supplier_code(): void
    {
        $order = $this->makeOrder([
            'type' => 'hotel',
            'order_type' => 'online',
            'booking_details' => json_encode([
                'supplier_code' => 'hotelbeds',
                'api_environment' => 'demo',
                'supplier_booking_reference' => '322-4697792',
                'book_response' => [
                    'booking' => [
                        'reference' => '322-4697792',
                        'status' => 'CONFIRMED',
                    ],
                ],
            ]),
        ]);

        try {
            app(OnlineHotelCancellationService::class)->cancelIfApplicable($order);
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringNotContainsString('hotelbeds', strtolower($e->getMessage()));
            $this->assertStringContainsString('cannot be cancelled automatically yet', $e->getMessage());
        }

        Http::assertNothingSent();
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function makeOrder(array $attrs): Order
    {
        $order = new class extends Order
        {
            public bool $wasSaved = false;

            /** @var array<int, string> */
            protected $with = [];

            public function save(array $options = [])
            {
                $this->wasSaved = true;

                return true;
            }
        };

        foreach ($attrs as $key => $value) {
            $order->setAttribute($key, $value);
        }

        return $order;
    }

    private function fakeSupplierConfig(): SupplierConfigResolver
    {
        $resolver = Mockery::mock(SupplierConfigResolver::class);
        $resolver->shouldReceive('isConfigured')->andReturn(true);
        $resolver->shouldReceive('valuesFor')->andReturn([
            'base_url' => 'https://mg.example',
            'agency_code' => 'agency',
            'username' => 'user',
            'password' => 'secret',
            'language' => 'EN',
            'api_environment' => 'demo',
        ]);
        $resolver->shouldReceive('missingCredentialsMessage')->andReturn('missing');

        return $resolver;
    }
}
