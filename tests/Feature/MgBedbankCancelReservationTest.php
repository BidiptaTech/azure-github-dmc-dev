<?php

namespace Tests\Feature;

use App\Services\HotelSuppliers\MgBedbank\MgBedbankBookingService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MgBedbankCancelReservationTest extends TestCase
{
    public function test_it_cancels_using_mg_booking_id_from_booking_details(): void
    {
        Http::fake([
            'https://mg.example/CancelReservation' => Http::response([
                'status' => true,
                'responseTime' => 100,
                'sessionID' => 'session-cancel',
                'bookingDetails' => [
                    'mgBookingID' => 'AGSG0537622609054846',
                    'agencyBookingID' => '1234QWERTY',
                    'mgBookingVersionID' => 'HB2609074123',
                    'agencyVoucherNo' => 'VAGHB2609074123',
                    'status' => 'CANCELCONF',
                    'cancelDate' => '2026-09-17',
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

        $result = app(MgBedbankBookingService::class)->cancelFromBookingDetails(
            [
                'supplier_code' => 'mg_bedbank',
                'api_environment' => 'demo',
                'agency_booking_id' => '1234qwerty',
                'book_response' => [
                    'bookingDetails' => [
                        'mgBookingID' => 'AGSG0537622609054846',
                        'agencyBookingID' => '1234QWERTY',
                    ],
                ],
            ],
            [
                'base_url' => 'https://mg.example',
                'agency_code' => 'agency',
                'username' => 'user',
                'password' => 'secret',
                'language' => 'En',
                'api_environment' => 'demo',
            ],
        );

        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return $request->url() === 'https://mg.example/CancelReservation'
                && $payload['MGBookingID'] === 'AGSG0537622609054846'
                && $payload['AgencyBookingID'] === '1234QWERTY'
                && $payload['SimulationFlag'] === false
                && ! array_key_exists('CancelDate', $payload);
        });

        $this->assertSame('mg_bedbank', $result['supplier_code']);
        $this->assertSame('CANCELCONF', $result['status']);
        $this->assertFalse($result['already_cancelled']);
        $this->assertSame(0.0, $result['cancellation_charges']['gross_amount']);
    }

    public function test_already_cancelled_is_treated_as_success(): void
    {
        Http::fake([
            'https://mg.example/CancelReservation' => Http::response([
                'status' => false,
                'errorCode' => 'JRVXML106',
                'errorMessage' => 'Reservation is already cancelled',
            ]),
        ]);

        $result = app(MgBedbankBookingService::class)->cancelFromBookingDetails(
            [
                'supplier_code' => 'mg_bedbank',
                'book_response' => [
                    'bookingDetails' => [
                        'mgBookingID' => 'AGSG0537622609054846',
                    ],
                ],
            ],
            [
                'base_url' => 'https://mg.example',
                'agency_code' => 'agency',
                'username' => 'user',
                'password' => 'secret',
            ],
        );

        $this->assertTrue($result['already_cancelled']);
        $this->assertSame('CANCELCONF', $result['status']);
    }
}
