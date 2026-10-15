<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Payment gateway integration for online bookings.
 *
 * Flow: Create Payment → Store pending transaction → Await callback → Settle.
 */
class PaymentGatewayService
{
    public function __construct(
        protected FinanceService $financeService,
    ) {}

    /**
     * Create a payment order with the configured gateway.
     *
     * @param  array<string, mixed>  $bookingData
     * @return array<string, mixed>
     */
    public function createPayment(array $bookingData): array
    {
        $gateway = config('finance.payment_gateways.default', 'razorpay');
        $amount = (int) round(((float) $bookingData['selling_price']) * 100);

        $orderPayload = [
            'amount' => $amount,
            'currency' => $bookingData['currency_code'] ?? 'USD',
            'receipt' => $bookingData['finance_reference'] ?? 'FIN-'.Str::uuid(),
            'notes' => [
                'tour_id' => $bookingData['tour_id'],
                'supplier_api_id' => $bookingData['supplier_api_id'] ?? null,
            ],
        ];

        Log::info('payment_gateway_create', [
            'gateway' => $gateway,
            'tour_id' => $bookingData['tour_id'],
            'amount' => $amount,
        ]);

        if ($gateway === 'razorpay') {
            return $this->createRazorpayOrder($orderPayload);
        }

        throw new RuntimeException("Unsupported payment gateway: {$gateway}");
    }

    /**
     * @param  array<string, mixed>  $orderPayload
     * @return array<string, mixed>
     */
    protected function createRazorpayOrder(array $orderPayload): array
    {
        $keyId = config('finance.payment_gateways.razorpay.key_id');
        $keySecret = config('finance.payment_gateways.razorpay.key_secret');

        if (! $keyId || ! $keySecret) {
            throw new RuntimeException('Razorpay credentials not configured');
        }

        $response = Http::withBasicAuth($keyId, $keySecret)
            ->timeout(30)
            ->post('https://api.razorpay.com/v1/orders', $orderPayload);

        if ($response->failed()) {
            throw new RuntimeException('Failed to create Razorpay order: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Forward verified callback to finance microservice.
     *
     * @param  array<string, mixed>  $callbackPayload
     * @return array<string, mixed>
     */
    public function forwardCallback(array $callbackPayload, ?string $signature = null): array
    {
        $url = rtrim((string) config('finance.base_url'), '/')
            .config('finance.endpoints.payment_callback');

        $request = Http::timeout((int) config('finance.timeout_seconds', 30))
            ->acceptJson();

        if ($signature) {
            $request = $request->withHeaders(['X-Gateway-Signature' => $signature]);
        }

        $response = $request->post($url, $callbackPayload);

        if ($response->failed()) {
            throw new RuntimeException('Finance callback forwarding failed: '.$response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * Initiate gateway refund via finance microservice.
     *
     * @param  array<string, mixed>  $refundData
     * @return array<string, mixed>
     */
    public function refund(array $refundData): array
    {
        return $this->financeService->post(
            config('finance.endpoints.refund'),
            $refundData
        );
    }
}
