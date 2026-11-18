<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stripe-only payment gateway (Razorpay removed).
 *
 * - Travclicks supplier_initial: platform PaymentIntent (no destination).
 * - DMC collection: destination charge + application_fee_amount.
 */
class PaymentGatewayService
{
    public function __construct(
        protected FinanceService $financeService,
        protected WalletLedgerService $ledger,
    ) {}

    /**
     * @param  array<string, mixed>  $bookingData
     * @return array<string, mixed>
     */
    public function createPayment(array $bookingData): array
    {
        return $this->createStripePaymentIntent([
            'tour_id' => $bookingData['tour_id'],
            'amount' => (float) $bookingData['selling_price'],
            'currency_code' => $bookingData['currency_code'] ?? 'USD',
            'idempotency_key' => $bookingData['idempotency_key'] ?? (string) Str::uuid(),
            'capture_method' => $bookingData['capture_method'] ?? 'automatic',
            'destination_account' => $bookingData['destination_account'] ?? null,
            'travclicks_markup' => $bookingData['travclicks_markup'] ?? null,
            'application_fee_amount' => $bookingData['application_fee_amount'] ?? null,
            'metadata' => [
                'tour_id' => (string) ($bookingData['tour_id'] ?? ''),
                'supplier_api_id' => (string) ($bookingData['supplier_api_id'] ?? ''),
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createStripePaymentIntent(array $data): array
    {
        $secret = (string) config('payment.stripe.secret_key', '');
        if ($secret === '') {
            throw new RuntimeException('Stripe secret key not configured');
        }

        \Stripe\Stripe::setApiKey($secret);

        $currency = strtolower((string) ($data['currency_code'] ?? 'usd'));
        $amountMinor = self::toMinorUnits((float) $data['amount'], $currency);
        if ($amountMinor <= 0) {
            throw new RuntimeException('Stripe amount must be positive');
        }

        $params = [
            'amount' => $amountMinor,
            'currency' => $currency,
            'capture_method' => $data['capture_method'] ?? 'automatic',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => array_merge(
                ['tour_id' => (string) ($data['tour_id'] ?? ''), 'platform' => 'travclicks'],
                is_array($data['metadata'] ?? null) ? $data['metadata'] : []
            ),
        ];

        if (! empty($data['destination_account'])) {
            $params['transfer_data'] = [
                'destination' => $data['destination_account'],
            ];

            $feeMajor = isset($data['application_fee_amount'])
                ? (float) $data['application_fee_amount']
                : $this->ledger->applicationFeeAmount(
                    (float) $data['amount'],
                    isset($data['travclicks_markup']) ? (float) $data['travclicks_markup'] : null
                );

            $feeMinor = self::toMinorUnits($feeMajor, $currency);
            if ($feeMinor > 0 && $feeMinor < $amountMinor) {
                $params['application_fee_amount'] = $feeMinor;
            }
        }

        $opts = [];
        if (! empty($data['idempotency_key'])) {
            $opts['idempotency_key'] = (string) $data['idempotency_key'];
        }

        Log::info('payment_gateway_stripe_intent', [
            'tour_id' => $data['tour_id'] ?? null,
            'amount' => $amountMinor,
            'currency' => $currency,
            'destination' => $data['destination_account'] ?? null,
            'application_fee_amount' => $params['application_fee_amount'] ?? null,
            'capture_method' => $params['capture_method'],
        ]);

        return \Stripe\PaymentIntent::create($params, $opts)->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function captureStripePaymentIntent(string $paymentIntentId, ?float $amount = null): array
    {
        $this->configureStripe();
        $intent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
        $params = [];
        if ($amount !== null) {
            $params['amount_to_capture'] = self::toMinorUnits($amount, (string) $intent->currency);
        }

        return $intent->capture($params)->toArray();
    }

    /**
     * Server-side read of a PaymentIntent (verify an authorisation instead of trusting the browser).
     *
     * @return array<string, mixed>
     */
    public function retrieveStripePaymentIntent(string $paymentIntentId): array
    {
        $this->configureStripe();

        return \Stripe\PaymentIntent::retrieve($paymentIntentId)->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function cancelStripePaymentIntent(string $paymentIntentId): array
    {
        $this->configureStripe();

        return \Stripe\PaymentIntent::retrieve($paymentIntentId)->cancel()->toArray();
    }

    /**
     * Process Stripe webhook / callback locally (no Python forward).
     *
     * @param  array<string, mixed>  $callbackPayload
     * @return array<string, mixed>
     */
    public function forwardCallback(array $callbackPayload, ?string $signature = null): array
    {
        return $this->financeService->post(
            config('finance.endpoints.payment_callback'),
            array_merge($callbackPayload, [
                'idempotency_key' => $callbackPayload['idempotency_key']
                    ?? ('stripe-cb-'.($callbackPayload['transaction_id'] ?? Str::uuid())),
            ])
        );
    }

    /**
     * @param  array<string, mixed>  $refundData
     * @return array<string, mixed>
     */
    public function refund(array $refundData): array
    {
        if (! empty($refundData['stripe_payment_intent_id']) || ! empty($refundData['gateway_refund_reference'])) {
            $this->configureStripe();
            $pi = (string) ($refundData['stripe_payment_intent_id'] ?? $refundData['gateway_refund_reference']);
            $params = [];
            if (! empty($refundData['refund_amount'])) {
                $refundCurrency = (string) ($refundData['currency'] ?? $refundData['currency_code'] ?? '');
                if ($refundCurrency === '') {
                    try {
                        $refundCurrency = (string) \Stripe\PaymentIntent::retrieve($pi)->currency;
                    } catch (\Throwable) {
                        $refundCurrency = '';
                    }
                }
                $params['amount'] = self::toMinorUnits((float) $refundData['refund_amount'], $refundCurrency);
            }
            try {
                \Stripe\Refund::create(array_merge(['payment_intent' => $pi], $params));
            } catch (\Throwable $e) {
                Log::warning('stripe_refund_api_failed', ['error' => $e->getMessage(), 'pi' => $pi]);
            }
        }

        return $this->financeService->post(
            config('finance.endpoints.refund'),
            $refundData
        );
    }

    /**
     * Send the DMC's own commission on online items to the DMC connected account.
     * Travclicks keeps the API cost and Travclicks commission on the platform charge.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function transferToConnectedAccount(
        string $destinationAccountId,
        float $amount,
        string $currency,
        array $metadata,
        string $idempotencyKey,
    ): array {
        $this->configureStripe();
        $minor = self::toMinorUnits($amount, $currency);
        if ($minor <= 0) {
            throw new RuntimeException('Transfer amount must be positive');
        }

        return \Stripe\Transfer::create([
            'amount' => $minor,
            'currency' => strtolower($currency),
            'destination' => $destinationAccountId,
            'metadata' => $metadata,
        ], ['idempotency_key' => $idempotencyKey])->toArray();
    }

    /**
     * Stripe zero-decimal currencies (amount is sent as-is, not ×100).
     * https://docs.stripe.com/currencies#zero-decimal
     */
    public const STRIPE_ZERO_DECIMAL = [
        'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga',
        'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf',
    ];

    /**
     * Major amount (e.g. 123.45) → Stripe minor units for the currency.
     */
    public static function toMinorUnits(float $amount, ?string $currency): int
    {
        $cur = strtolower(trim((string) $currency));
        if (in_array($cur, self::STRIPE_ZERO_DECIMAL, true)) {
            return (int) round($amount);
        }

        return (int) round(round($amount, 2) * 100);
    }

    protected function configureStripe(): void
    {
        $secret = (string) config('payment.stripe.secret_key', '');
        if ($secret === '') {
            throw new RuntimeException('Stripe secret key not configured');
        }
        \Stripe\Stripe::setApiKey($secret);
    }
}
