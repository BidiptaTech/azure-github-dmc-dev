<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RetryFinanceOperationJob;
use App\Services\Finance\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Public webhook endpoint for payment gateway callbacks.
 * Signature-verified before forwarding to Azure Finance Functions.
 */
class FinanceWebhookController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $paymentGatewayService,
    ) {}

    public function paymentCallback(Request $request): JsonResponse
    {
        $signature = $request->header('X-Razorpay-Signature')
            ?? $request->header('X-Gateway-Signature')
            ?? $request->header('X-Webhook-Signature');

        $payload = $request->all();

        Log::info('finance_webhook_received', [
            'gateway' => $payload['gateway_name'] ?? config('finance.payment_gateways.default'),
            'transaction_id' => $payload['transaction_id'] ?? $payload['payload']['payment']['entity']['id'] ?? null,
        ]);

        try {
            $normalized = $this->normalizeCallbackPayload($payload);

            $result = $this->paymentGatewayService->forwardCallback(
                $normalized,
                $signature
            );

            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error('finance_webhook_failed', [
                'error' => $e->getMessage(),
                'payload_keys' => array_keys($payload),
            ]);

            RetryFinanceOperationJob::dispatch('payment_callback', [
                'payload' => $payload,
                'signature' => $signature,
            ])->delay(now()->addSeconds(60));

            return response()->json([
                'success' => false,
                'message' => 'Callback queued for retry',
            ], 202);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function normalizeCallbackPayload(array $payload): array
    {
        if (isset($payload['payload']['payment']['entity'])) {
            $entity = $payload['payload']['payment']['entity'];

            return [
                'gateway_name' => 'razorpay',
                'transaction_id' => $entity['id'] ?? '',
                'status' => $entity['status'] ?? 'captured',
                'amount' => ($entity['amount'] ?? 0) / 100,
                'currency' => $entity['currency'] ?? 'INR',
                'merchant_reference' => $entity['order_id'] ?? '',
                'raw' => $payload,
            ];
        }

        return array_merge([
            'gateway_name' => config('finance.payment_gateways.default'),
        ], $payload);
    }
}
