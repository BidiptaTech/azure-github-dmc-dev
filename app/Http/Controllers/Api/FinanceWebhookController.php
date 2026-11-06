<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RetryFinanceOperationJob;
use App\Services\Finance\PaymentGatewayService;
use App\Services\Payment\StripeConnectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

/**
 * Stripe webhook endpoint (Razorpay removed). Handled entirely in Laravel.
 */
class FinanceWebhookController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $paymentGatewayService,
        protected StripeConnectService $stripeConnectService,
    ) {}

    public function paymentCallback(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $secret = (string) config('payment.stripe.webhook_secret', '');
            if ($secret !== '' && $signature) {
                $event = Webhook::constructEvent($payload, $signature, $secret);
                $normalized = $this->normalizeStripeEvent($event->toArray());
            } else {
                // Allow local/dev JSON posts without signature when secret empty.
                $normalized = $this->normalizeCallbackPayload($request->all());
            }

            if (($normalized['type'] ?? '') === 'account.updated') {
                $this->stripeConnectService->handleWebhook($payload, $signature);

                return response()->json(['success' => true, 'handled' => 'account.updated']);
            }

            $result = $this->paymentGatewayService->forwardCallback($normalized, $signature);

            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error('finance_webhook_failed', [
                'error' => $e->getMessage(),
            ]);

            RetryFinanceOperationJob::dispatch('payment_callback', [
                'payload' => $request->all(),
                'signature' => $signature,
            ])->delay(now()->addSeconds(60));

            return response()->json([
                'success' => false,
                'message' => 'Callback queued for retry',
            ], 202);
        }
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    protected function normalizeStripeEvent(array $event): array
    {
        $type = (string) ($event['type'] ?? '');
        $object = $event['data']['object'] ?? [];

        if ($type === 'account.updated') {
            return ['type' => 'account.updated', 'raw' => $event];
        }

        $piId = $object['id'] ?? null;
        $meta = $object['metadata'] ?? [];
        $tourId = (int) ($meta['tour_id'] ?? 0);
        $attemptUuid = $meta['payment_attempt_uuid'] ?? null;

        $paymentTransactionId = null;
        if ($attemptUuid) {
            $attempt = DB::table('payment_attempts')->where('attempt_uuid', $attemptUuid)->first();
            $paymentTransactionId = $attempt->payment_transaction_id ?? null;
            $tourId = $tourId ?: (int) ($attempt->tour_id ?? 0);
        }

        $status = match ($type) {
            'payment_intent.succeeded', 'charge.succeeded' => 'captured',
            'payment_intent.payment_failed' => 'failed',
            'payment_intent.canceled' => 'cancelled',
            default => $object['status'] ?? $type,
        };

        return [
            'type' => $type,
            'gateway_name' => 'stripe',
            'transaction_id' => $piId,
            'status' => $status,
            'amount' => isset($object['amount_received'])
                ? ((float) $object['amount_received']) / 100
                : ((float) ($object['amount'] ?? 0)) / 100,
            'currency' => strtoupper((string) ($object['currency'] ?? 'USD')),
            'tour_id' => $tourId,
            'payment_transaction_id' => $paymentTransactionId,
            'merchant_reference' => $attemptUuid,
            'raw' => $event,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function normalizeCallbackPayload(array $payload): array
    {
        if (isset($payload['type']) && str_starts_with((string) $payload['type'], 'payment_intent')) {
            return $this->normalizeStripeEvent($payload);
        }

        return array_merge([
            'gateway_name' => 'stripe',
        ], $payload);
    }
}
