<?php

namespace App\Services\Payment;

use App\Helpers\CommonHelper;
use App\Models\Order;
use App\Models\Tour;
use App\Services\Finance\PaymentGatewayService;
use App\Services\Finance\WalletService;
use App\Services\Payment\FxConversionCharge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Recoverable payment orchestration for Online / Offline / Mix stages.
 * Does not pretend Stripe + supplier + ledger are one DB transaction.
 */
class PaymentOrchestrator
{
    public function __construct(
        protected OrderClassificationService $classificationService,
        protected PaymentEligibilityService $eligibilityService,
        protected PaymentSettingsService $settingsService,
        protected CurrencySnapshotService $currencySnapshotService,
        protected StripeConnectService $stripeConnectService,
        protected PaymentGatewayService $paymentGatewayService,
        protected WalletService $walletService,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function initiate(Tour $tour, array $input): array
    {
        $idempotencyKey = (string) ($input['idempotency_key'] ?? Str::uuid());
        if ($existing = $this->findAttemptByIdempotency($idempotencyKey)) {
            return ['attempt' => $existing, 'idempotent_replay' => true];
        }

        // Never take a second payment while this tour's online payment is held / being booked.
        $openHold = DB::table('payment_attempts')
            ->where('tour_id', (int) ($tour->tour_id ?? $tour->getKey()))
            ->where('payment_stage', 'supplier_initial')
            ->where('status', 'authorized')
            ->whereRaw("(metadata::jsonb ->> 'hold_opened_at') IS NOT NULL")
            ->exists();
        if ($openHold) {
            throw new RuntimeException('A payment for this tour is already on hold while its online services are being booked. Please wait for it to finish.');
        }

        // Evaluate wallet/FX against the amount being paid (e.g. 300 SGD), not always full remaining.
        $requestedAmount = isset($input['amount']) && is_numeric($input['amount'])
            ? (float) $input['amount']
            : null;
        $eligibility = $this->eligibilityService->forTour($tour, $requestedAmount);
        $method = strtolower((string) ($input['payment_method'] ?? ''));
        $this->settingsService->assertMethodAllowed($method);

        $methodMeta = collect($eligibility['methods'])->firstWhere('code', $method)
            ?? collect($eligibility['methods'])->first(function ($m) use ($method) {
                return ($m['legacy_value'] ?? null) === $method;
            });

        if (! $methodMeta || empty($methodMeta['available'])) {
            throw new RuntimeException($methodMeta['reason'] ?? 'Selected payment method is not available.');
        }

        $amount = (float) $eligibility['payable_amount'];
        if (! empty($eligibility['amount_readonly'])) {
            // Reject tampered amounts for supplier_initial
            if ($requestedAmount !== null && abs($requestedAmount - $amount) > 0.0001) {
                throw new RuntimeException('Payment amount cannot be modified for this payment stage.');
            }
        } else {
            $amount = $requestedAmount !== null && $requestedAmount > 0 ? $requestedAmount : $amount;
            $outstanding = (float) $eligibility['outstanding_balance'];
            if ($amount <= 0 || $amount - $outstanding > 0.0001) {
                throw new RuntimeException('Payment amount exceeds outstanding balance or is invalid.');
            }
        }

        if ($eligibility['order_classification'] === OrderClassificationService::EMPTY) {
            throw new RuntimeException('Cannot pay an empty order.');
        }

        // Online services are paid at today's supplier price: the payer must have seen the
        // live price (and accepted any change) right before paying.
        $priceCheck = null;
        if (($eligibility['payment_stage'] ?? '') === 'supplier_initial' && in_array($method, ['wallet', 'stripe'], true)) {
            $priceCheck = app(OnlinePriceCheckService::class)->assertConfirmed(
                $tour,
                (string) ($input['price_check_token'] ?? ''),
                (float) $eligibility['payable_amount'],
            );
        }

        $currency = (string) $eligibility['currency'];
        $orderAmount = $amount;
        $orderCurrency = $currency;
        $fxMeta = is_array($eligibility['fx'] ?? null) ? $eligibility['fx'] : [];
        $destinationCurrency = $currency;
        $exchangeRate = null;
        if ($method === 'wallet') {
            $walletCurrency = strtoupper((string) ($fxMeta['wallet_currency'] ?? $currency));
            if ($walletCurrency !== '' && $walletCurrency !== $currency) {
                $destinationCurrency = $walletCurrency;
                $exchangeRate = isset($fxMeta['exchange_rate']) ? (float) $fxMeta['exchange_rate'] : null;
            }
        }
        $fx = $this->currencySnapshotService->createSnapshot([
            'source_currency' => $currency,
            'destination_currency' => $destinationCurrency,
            'source_amount' => $amount,
            'exchange_rate' => $exchangeRate,
            'conversion_charge_percent' => (float) ($fxMeta['charge_percent'] ?? 0),
            'tour_id' => (int) ($tour->tour_id ?? $tour->getKey()),
            'agent_id' => $tour->agent_id,
            'dmc_id' => $tour->dmc_id,
            'metadata' => [
                'context' => 'payment_initiate',
                'method' => $method,
                'order_amount' => $amount,
                'conversion_charge_percent' => (float) ($fxMeta['charge_percent'] ?? 0),
            ],
        ]);
        // Wallet debits the converted amount plus the conversion charge.
        if ($destinationCurrency !== $currency) {
            $amount = (float) ($fx['destination_amount'] ?? $amount);
            $currency = $destinationCurrency;
        }

        $attempt = $this->createAttempt([
            'idempotency_key' => $idempotencyKey,
            'tour_id' => (int) ($tour->tour_id ?? $tour->getKey()),
            'order_classification' => $eligibility['order_classification'],
            'payment_stage' => $eligibility['payment_stage'],
            'payment_method' => $method,
            'payment_recipient' => $eligibility['payment_recipient'],
            'dmc_id' => $tour->dmc_id,
            'agent_id' => $tour->agent_id,
            'currency_code' => $currency,
            'requested_amount' => $amount,
            'status' => 'pending',
            'fx_snapshot_id' => $fx['id'] ?? null,
            'eligibility_snapshot' => $eligibility,
            'metadata' => array_merge($input['metadata'] ?? [], [
                // Original quote in the order currency; the wallet debit is `requested_amount` in `currency_code`.
                'order_amount' => $orderAmount,
                'order_currency' => $orderCurrency,
                'exchange_rate' => $exchangeRate,
                'conversion_charge' => $destinationCurrency !== $orderCurrency ? (float) ($fxMeta['conversion_charge'] ?? 0) : 0.0,
                'conversion_charge_percent' => $destinationCurrency !== $orderCurrency
                    ? (float) ($fxMeta['charge_percent'] ?? app(FxConversionCharge::class)->percent())
                    : 0.0,
                'conversion_charge_currency' => $destinationCurrency !== $orderCurrency ? $destinationCurrency : $orderCurrency,
                'sgd_rate_order' => $fxMeta['sgd_rate_order'] ?? null,
                'sgd_rate_wallet' => $fxMeta['sgd_rate_wallet'] ?? null,
                'payment_for' => $this->classificationService->resolvePaymentFor(
                    (string) ($eligibility['order_classification'] ?? ''),
                    (string) ($eligibility['payment_stage'] ?? ''),
                ),
                'user_remarks' => trim((string) ($input['user_remarks'] ?? $input['remarks'] ?? '')),
                'user_transaction_id' => trim((string) ($input['transaction_id'] ?? '')),
                // What the payer saw: quoted vs live price per service at payment time.
                'price_check' => $priceCheck,
                // Hold full amount → deduct per booked service → capture once / release rest.
                'hold_mode' => (bool) config('payment.online_hold.enabled', true)
                    && ($eligibility['payment_stage'] ?? '') === 'supplier_initial'
                    && in_array($method, ['wallet', 'stripe'], true),
            ]),
        ]);

        $tour->forceFill([
            'order_classification' => $eligibility['order_classification'],
            'payment_stage' => $eligibility['payment_stage'],
        ])->save();

        return match ($method) {
            'wallet' => $this->initiateWallet($tour, $attempt, $eligibility, $amount),
            'stripe' => $this->initiateStripe($tour, $attempt, $eligibility, $amount),
            'cash', 'cheque', 'bank_transfer', 'card_offline', 'online', 'card' => $this->initiateOfflineRecord($tour, $attempt, $input, $amount),
            default => throw new RuntimeException("Unsupported payment method: {$method}"),
        };
    }

    /**
     * Capture/cancel Stripe PaymentIntent after supplier booking results.
     *
     * @param  array<int, array<string, mixed>>  $supplierResults
     * @return array<string, mixed>
     */
    public function completeStripeAfterSupplier(Tour $tour, string $attemptUuid, array $supplierResults = []): array
    {
        $attempt = $this->findAttemptByUuid($attemptUuid);
        if (! $attempt) {
            throw new RuntimeException('Payment attempt not found.');
        }
        if (($attempt['status'] ?? '') === 'completed') {
            return ['attempt' => $attempt, 'confirmed' => true, 'idempotent_replay' => true];
        }
        if (empty($attempt['stripe_payment_intent_id'])) {
            throw new RuntimeException('No Stripe PaymentIntent on this attempt.');
        }

        // Hold flow: card is authorised only; capture happens once services are booked.
        if ($supplierResults === [] && $this->isHoldAttempt($attempt)) {
            $intent = $this->paymentGatewayService->retrieveStripePaymentIntent((string) $attempt['stripe_payment_intent_id']);
            if (($intent['status'] ?? '') !== 'requires_capture') {
                throw new RuntimeException('Card payment is not authorised yet (status: '.($intent['status'] ?? 'unknown').').');
            }
            $held = $this->moneyFromMinor((int) ($intent['amount_capturable'] ?? 0), (string) ($intent['currency'] ?? ''));
            if (abs($held - (float) $attempt['requested_amount']) > 0.01) {
                throw new RuntimeException('Authorised card amount does not match the payment amount.');
            }

            return $this->openHold($tour, $attemptUuid);
        }

        $bookedAmount = $this->persistSupplierOutcomes((int) $attempt['id'], (int) $attempt['tour_id'], $supplierResults);
        $pi = (string) $attempt['stripe_payment_intent_id'];

        if ($supplierResults !== [] && $bookedAmount <= 0 && $attempt['payment_stage'] === 'supplier_initial') {
            $this->paymentGatewayService->cancelStripePaymentIntent($pi);
            $this->updateAttempt((int) $attempt['id'], [
                'status' => 'cancelled',
                'released_amount' => $attempt['requested_amount'],
                'last_error' => 'All supplier bookings failed or unavailable.',
            ]);

            return ['attempt' => $this->findAttemptByUuid($attemptUuid), 'confirmed' => false];
        }

        $finalAmount = $bookedAmount > 0 ? $bookedAmount : (float) $attempt['requested_amount'];
        $captured = $this->paymentGatewayService->captureStripePaymentIntent($pi, $finalAmount);

        $this->updateAttempt((int) $attempt['id'], [
            'status' => 'completed',
            'captured_amount' => $finalAmount,
            'released_amount' => max(0, (float) $attempt['requested_amount'] - $finalAmount),
            'metadata' => array_merge((array) ($attempt['metadata'] ?? []), [
                'stripe_capture' => [
                    'id' => $captured['id'] ?? null,
                    'status' => $captured['status'] ?? null,
                ],
            ]),
        ]);

        $updated = $this->findAttemptByUuid($attemptUuid);
        $this->advanceAcceptance($tour, $updated ?? $attempt);
        $commission = $this->recordCommission($tour, $updated ?? $attempt, $supplierResults);

        return [
            'attempt' => $this->findAttemptByUuid($attemptUuid),
            'confirmed' => true,
            'captured_amount' => $finalAmount,
            'stripe' => $captured,
            'commission' => $commission,
        ];
    }

    /**
     * Complete wallet path after optional supplier booking results.
     *
     * @param  array<int, array<string, mixed>>  $supplierResults
     * @return array<string, mixed>
     */
    public function completeWalletAfterSupplier(Tour $tour, string $attemptUuid, array $supplierResults = []): array
    {
        $attempt = $this->findAttemptByUuid($attemptUuid);
        if (! $attempt) {
            throw new RuntimeException('Payment attempt not found.');
        }
        if (($attempt['status'] ?? '') === 'completed') {
            return ['attempt' => $attempt, 'confirmed' => true, 'idempotent_replay' => true];
        }

        // Hold flow: an empty "complete" (e.g. old page JS) must not commit the whole hold.
        if ($supplierResults === [] && $this->isHoldAttempt($attempt) && ! empty($attempt['wallet_reservation_ref'])) {
            return $this->openHold($tour, $attemptUuid);
        }

        $bookedAmount = $this->persistSupplierOutcomes((int) $attempt['id'], (int) $attempt['tour_id'], $supplierResults);
        if ($supplierResults !== [] && $bookedAmount <= 0 && $attempt['payment_stage'] === 'supplier_initial') {
            if (! empty($attempt['wallet_reservation_ref'])) {
                $this->walletService->releaseReservation([
                    'idempotency_key' => $attempt['idempotency_key'].':release',
                    'reservation_reference' => $attempt['wallet_reservation_ref'],
                ]);
            }
            $this->updateAttempt((int) $attempt['id'], [
                'status' => 'cancelled',
                'released_amount' => $attempt['requested_amount'],
                'last_error' => 'All supplier bookings failed or unavailable.',
            ]);

            return ['attempt' => $this->findAttemptByUuid($attemptUuid), 'confirmed' => false];
        }

        $finalAmount = $bookedAmount > 0 ? $bookedAmount : (float) $attempt['requested_amount'];

        $commit = $this->walletService->commitReservation([
            'idempotency_key' => $attempt['idempotency_key'].':commit',
            'reservation_reference' => $attempt['wallet_reservation_ref'],
            'commit_amount' => $finalAmount,
            'tour_id' => $attempt['tour_id'],
            'dmc_id' => $attempt['dmc_id'],
            'agent_id' => $attempt['agent_id'],
            'source_dmc_id' => $attempt['dmc_id'],
        ]);

        $releaseAmount = max(0, (float) $attempt['requested_amount'] - $finalAmount);
        if ($releaseAmount > 0.0001) {
            $this->walletService->releaseReservation([
                'idempotency_key' => $attempt['idempotency_key'].':release-partial',
                'reservation_reference' => $attempt['wallet_reservation_ref'],
                'release_amount' => $releaseAmount,
            ]);
        }

        $this->updateAttempt((int) $attempt['id'], [
            'status' => 'completed',
            'captured_amount' => $finalAmount,
            'released_amount' => $releaseAmount,
            'metadata' => array_merge((array) ($attempt['metadata'] ?? []), ['commit' => $commit]),
        ]);

        $updated = $this->findAttemptByUuid($attemptUuid);
        $this->advanceAcceptance($tour, $updated ?? $attempt);
        $commission = $this->recordCommission($tour, $updated ?? $attempt, $supplierResults);
        $tour->refresh();

        // Wallet funds are already deducted — record as auto-verified (no manual approve).
        $attemptMeta = (array) ($attempt['metadata'] ?? []);
        $attemptMeta['payment_for'] = $attemptMeta['payment_for']
            ?? $this->classificationService->resolvePaymentFor(
                (string) ($attempt['order_classification'] ?? $tour->order_classification ?? ''),
                (string) ($attempt['payment_stage'] ?? $tour->payment_stage ?? ''),
            );
        $paymentIndex = $this->recordAutoVerifiedWalletPayment(
            $tour,
            $finalAmount,
            (string) ($attempt['currency_code'] ?? 'SGD'),
            $commit['data']['finance_reference'] ?? $commit['finance_reference'] ?? null,
            (string) ($attempt['attempt_uuid'] ?? $attemptUuid),
            $attemptMeta,
            (float) ($attempt['requested_amount'] ?? 0),
        );

        return [
            'attempt' => $this->findAttemptByUuid($attemptUuid),
            'confirmed' => true,
            'captured_amount' => $finalAmount,
            'verified' => true,
            'payment_index' => $paymentIndex,
            'commission' => $commission,
        ];
    }

    /* ------------------------------------------------------------------
     | Online payment hold
     |
     | 1. Pay   → full amount HELD (Stripe manual-capture / wallet reservation).
     |            Tour is unlocked for booking exactly as before.
     | 2. Book  → each online service approved with its vendor takes its share:
     |            wallet commits that share immediately; Stripe accumulates.
     | 3. Settle→ when every service is booked or removed (or the hold is near expiry):
     |            Stripe captures the booked total ONCE, wallet releases the rest.
     |            Unbooked share goes back to the payer.
     * ------------------------------------------------------------------ */

    /**
     * @param  array<string, mixed>  $attempt
     */
    public function isHoldAttempt(array $attempt): bool
    {
        $meta = (array) ($attempt['metadata'] ?? []);

        return ! empty($meta['hold_mode'])
            && ($attempt['payment_stage'] ?? '') === 'supplier_initial'
            && in_array($attempt['payment_method'] ?? '', ['wallet', 'stripe'], true);
    }

    /**
     * Mark the attempt as held, split the hold across the online services, unlock the tour.
     *
     * @return array<string, mixed>
     */
    public function openHold(Tour $tour, string $attemptUuid): array
    {
        $justOpened = false;
        $result = DB::transaction(function () use ($tour, $attemptUuid, &$justOpened) {
            $row = DB::table('payment_attempts')->where('attempt_uuid', $attemptUuid)->lockForUpdate()->first();
            $attempt = $row ? $this->normalizeAttempt((array) $row) : null;
            if (! $attempt) {
                throw new RuntimeException('Payment attempt not found.');
            }
            $meta = (array) ($attempt['metadata'] ?? []);

            if (empty($meta['hold_opened_at'])) {
                $shares = $this->computeHoldShares($tour, $attempt);
                if ($shares === []) {
                    throw new RuntimeException('No online services found to hold this payment against.');
                }

                $meta = array_merge($meta, [
                    'hold_opened_at' => now()->toIso8601String(),
                    'hold_shares' => $shares,
                    'hold_status' => 'held',
                    // Background auto-booking runs as the user who paid (API env / permissions).
                    'paid_by_user_id' => auth()->id(),
                ]);
                $justOpened = true;
                $this->updateAttempt((int) $attempt['id'], [
                    'status' => 'authorized',
                    'authorized_amount' => (float) $attempt['requested_amount'],
                    'metadata' => $meta,
                ]);
                $attempt = $this->findAttemptByUuid($attemptUuid) ?? $attempt;

                // Unlock the tour for booking. No Payment Details row yet: a hold is not a payment.
                // The row is written once, at settlement, with the amount actually charged.
                $this->advanceAcceptance($tour, $attempt);
            }

            return [
                'attempt' => $this->findAttemptByUuid($attemptUuid),
                'confirmed' => true,
                'hold' => true,
                'message' => ($attempt['payment_method'] ?? '') === 'stripe'
                    ? 'Card authorised. The amount is on hold and is charged as each service is booked; anything not booked is released.'
                    : 'Amount held in your wallet. It is deducted as each service is booked; anything not booked is released.',
            ];
        });

        // Hold is committed → book the online services with the vendors (no approve dialog).
        // A failure here never undoes the payment hold; the scheduler retries pending holds.
        if ($justOpened && config('payment.online_hold.auto_book', true)) {
            try {
                \App\Jobs\AutoBookOnlineServicesJob::dispatch(
                    (int) ($tour->tour_id ?? $tour->getKey()),
                    $attemptUuid,
                    auth()->id()
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('online_auto_booking_dispatch_failed', [
                    'attempt' => $attemptUuid,
                    'error' => $e->getMessage(),
                ]);
            }

            $result['attempt'] = $this->findAttemptByUuid($attemptUuid);
            $summary = (array) (($result['attempt']['metadata'] ?? [])['auto_booking'] ?? []);
            $result['auto_booking'] = $summary;
            $result['message'] = $this->autoBookingMessage($result['attempt'] ?? [], $summary);
        }

        $tour->refresh();
        $freshAttempt = (array) ($result['attempt'] ?? []);
        $result['tour_status'] = (string) ($tour->tour_status ?? '');
        $classification = (string) ($freshAttempt['order_classification'] ?? $tour->order_classification ?? '');
        if (in_array($result['tour_status'], ['Confirmed', 'Definite'], true)
            || $classification === OrderClassificationService::ONLINE) {
            $result['redirect_url'] = url('/bookings/definite');
        }

        return $result;
    }

    /**
     * Plain-language result shown after Pay Now.
     *
     * @param  array<string, mixed>  $attempt
     * @param  array<string, mixed>  $summary
     */
    protected function autoBookingMessage(array $attempt, array $summary): string
    {
        if ($summary === []) {
            return 'Payment received and held. Your online services are being booked with the suppliers; '
                .'booked services are charged and anything that cannot be booked is released.';
        }

        $booked = (int) ($summary['booked'] ?? 0);
        $failed = (int) ($summary['failed'] ?? 0);
        $total = $booked + $failed;
        $cur = (string) ($attempt['currency_code'] ?? '');
        $charged = (float) ($attempt['captured_amount'] ?? 0);
        $released = (float) ($attempt['released_amount'] ?? 0);

        if ($failed === 0) {
            return "All {$total} online service(s) booked with the suppliers. Charged {$cur} ".number_format($charged, 2).'.';
        }
        if ($booked === 0) {
            return "None of the {$total} online service(s) could be booked. The full amount ({$cur} "
                .number_format($released, 2).') has been released.';
        }

        return "{$booked} of {$total} online service(s) booked. Charged {$cur} ".number_format($charged, 2)
            ."; {$cur} ".number_format($released, 2).' released for the service(s) that could not be booked.';
    }

    /**
     * Auto-booking could not book this service → record it so its share of the hold is released.
     */
    public function recordHoldServiceFailure(int $attemptId, int $orderId, string $reason): void
    {
        $row = DB::table('payment_attempts')->where('id', $attemptId)->first();
        if (! $row) {
            return;
        }
        $attempt = $this->normalizeAttempt((array) $row);
        $shares = (array) ($attempt['metadata']['hold_shares'] ?? []);
        if (! array_key_exists($orderId, $shares)
            || DB::table('supplier_booking_outcomes')->where('payment_attempt_id', $attemptId)->where('order_id', $orderId)->exists()) {
            return;
        }

        $order = DB::table('orders')->where('id', $orderId)->first();
        $this->insertHoldOutcome($attempt, $orderId, 'failed', (float) $shares[$orderId], $order);
        DB::table('supplier_booking_outcomes')
            ->where('payment_attempt_id', $attemptId)
            ->where('order_id', $orderId)
            ->update(['error_message' => mb_substr($reason, 0, 2000), 'updated_at' => now()]);
    }

    /**
     * Apply booked / removed services to open holds of a tour; settle when nothing is pending.
     *
     * @return array<int, array<string, mixed>>
     */
    public function syncOnlineHold(int $tourId, bool $force = false, string $reason = 'all_services_resolved'): array
    {
        $results = [];
        $attempts = DB::table('payment_attempts')
            ->where('tour_id', $tourId)
            ->where('status', 'authorized')
            ->where('payment_stage', 'supplier_initial')
            ->orderBy('id')
            ->get();

        foreach ($attempts as $row) {
            $attempt = $this->normalizeAttempt((array) $row);
            if (! $this->isHoldAttempt($attempt) || empty($attempt['metadata']['hold_opened_at'])) {
                continue;
            }
            try {
                $results[] = $this->syncHoldAttempt($attempt, $force, $reason);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('online_hold_sync_failed', [
                    'tour_id' => $tourId,
                    'attempt' => $attempt['attempt_uuid'] ?? null,
                    'error' => $e->getMessage(),
                ]);
                $this->updateAttempt((int) $attempt['id'], ['last_error' => $e->getMessage()]);
            }
        }

        return $results;
    }

    /**
     * @param  array<string, mixed>  $attempt
     * @return array<string, mixed>
     */
    protected function syncHoldAttempt(array $attempt, bool $force, string $reason): array
    {
        return DB::transaction(function () use ($attempt, $force, $reason) {
            // Serialize concurrent approvals / scheduler runs on the same hold.
            $locked = DB::table('payment_attempts')->where('id', $attempt['id'])->lockForUpdate()->first();
            $attempt = $this->normalizeAttempt((array) $locked);
            if (($attempt['status'] ?? '') !== 'authorized') {
                return ['attempt_uuid' => $attempt['attempt_uuid'], 'state' => 'already_settled'];
            }

            $meta = (array) ($attempt['metadata'] ?? []);
            $shares = (array) ($meta['hold_shares'] ?? []);
            $isWallet = ($attempt['payment_method'] ?? '') === 'wallet';
            $done = DB::table('supplier_booking_outcomes')
                ->where('payment_attempt_id', $attempt['id'])
                ->pluck('status', 'order_id')
                ->all();

            $pending = 0;
            foreach ($shares as $orderId => $share) {
                $orderId = (int) $orderId;
                if (array_key_exists($orderId, $done)) {
                    continue;
                }
                $order = DB::table('orders')->where('id', $orderId)->first();
                $approved = $order && (bool) $order->is_approve;

                if ($approved) {
                    if ($isWallet && (float) $share > 0) {
                        // Deduct this service's share from the held amount now.
                        $this->walletService->commitReservation([
                            'idempotency_key' => 'hold:'.$attempt['attempt_uuid'].':order:'.$orderId.':commit',
                            'reservation_reference' => $attempt['wallet_reservation_ref'],
                            'commit_amount' => (float) $share,
                            'tour_id' => $attempt['tour_id'],
                            'ledger_description' => 'Held amount deducted · service booked · order #'.$orderId,
                            'metadata' => ['order_id' => $orderId, 'payment_attempt_uuid' => $attempt['attempt_uuid']],
                        ]);
                    }
                    $this->insertHoldOutcome($attempt, $orderId, 'booked', (float) $share, $order);
                    $done[$orderId] = 'booked';
                } elseif (! $order || ! empty($order->deleted_at) || $force) {
                    $this->insertHoldOutcome($attempt, $orderId, $force && $order && empty($order->deleted_at) ? 'expired' : 'failed', (float) $share, $order);
                    $done[$orderId] = 'failed';
                } else {
                    $pending++;
                }
            }

            if ($pending > 0) {
                return ['attempt_uuid' => $attempt['attempt_uuid'], 'state' => 'holding', 'pending_services' => $pending];
            }

            return $this->settleHold($attempt, $force ? $reason : 'all_services_resolved');
        });
    }

    /**
     * @param  array<string, mixed>  $attempt
     */
    protected function insertHoldOutcome(array $attempt, int $orderId, string $status, float $share, ?object $order): void
    {
        $details = $order && is_string($order->booking_details ?? null) ? (json_decode($order->booking_details, true) ?: []) : [];
        DB::table('supplier_booking_outcomes')->insert([
            'outcome_uuid' => (string) Str::uuid(),
            'tour_id' => (int) $attempt['tour_id'],
            'order_id' => $orderId,
            'payment_attempt_id' => (int) $attempt['id'],
            'supplier_api_id' => $details['supplier_code'] ?? null,
            'supplier_booking_reference' => $order->reference_id ?? $order->order_ref_no ?? null,
            'status' => $status,
            'quoted_amount' => round($share, 2),
            'booked_amount' => $status === 'booked' ? round($share, 2) : null,
            'currency_code' => $attempt['currency_code'] ?? null,
            'request_meta' => json_encode(['source' => 'online_hold', 'service_type' => $order->type ?? null]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Capture booked total once (Stripe) / release the remainder (wallet), then finalize records.
     *
     * @param  array<string, mixed>  $attempt
     * @return array<string, mixed>
     */
    protected function settleHold(array $attempt, string $reason): array
    {
        $outcomes = DB::table('supplier_booking_outcomes')
            ->where('payment_attempt_id', $attempt['id'])
            ->get();
        $booked = round((float) $outcomes->where('status', 'booked')->sum('booked_amount'), 2);
        $requested = round((float) $attempt['requested_amount'], 2);
        $booked = min($booked, $requested);
        $release = round(max(0, $requested - $booked), 2);
        $meta = (array) ($attempt['metadata'] ?? []);
        $gateway = [];

        if (($attempt['payment_method'] ?? '') === 'stripe') {
            $pi = (string) $attempt['stripe_payment_intent_id'];
            try {
                $gateway = $booked > 0
                    ? $this->paymentGatewayService->captureStripePaymentIntent($pi, $booked)
                    : $this->paymentGatewayService->cancelStripePaymentIntent($pi);
            } catch (\Throwable $e) {
                $this->updateAttempt((int) $attempt['id'], [
                    'status' => 'capture_failed',
                    'last_error' => $e->getMessage(),
                    'metadata' => array_merge($meta, ['hold_status' => 'capture_failed']),
                ]);
                \Illuminate\Support\Facades\Log::critical('online_hold_capture_failed', [
                    'attempt' => $attempt['attempt_uuid'],
                    'tour_id' => $attempt['tour_id'],
                    'booked' => $booked,
                    'error' => $e->getMessage(),
                ]);

                return ['attempt_uuid' => $attempt['attempt_uuid'], 'state' => 'capture_failed', 'error' => $e->getMessage()];
            }
        } elseif (! empty($attempt['wallet_reservation_ref'])) {
            $gateway = $this->walletService->releaseReservation([
                'idempotency_key' => 'hold:'.$attempt['attempt_uuid'].':release',
                'reservation_reference' => $attempt['wallet_reservation_ref'],
                'tour_id' => $attempt['tour_id'],
                'ledger_description' => 'Held amount released · services not booked',
            ]);
        }

        $holdStatus = $booked <= 0 ? 'released' : ($release > 0 ? 'partially_captured' : 'captured');
        $this->updateAttempt((int) $attempt['id'], [
            'status' => $booked > 0 ? 'completed' : 'cancelled',
            'captured_amount' => $booked,
            'released_amount' => $release,
            'metadata' => array_merge($meta, [
                'hold_status' => $holdStatus,
                'hold_settled_at' => now()->toIso8601String(),
                'hold_settle_reason' => $reason,
                'hold_gateway' => array_intersect_key((array) $gateway, array_flip(['id', 'status', 'amount_received', 'finance_reference', 'released_amount'])),
            ]),
        ]);

        $tour = Tour::query()->where('tour_id', (int) $attempt['tour_id'])->first();
        if ($tour) {
            $this->finalizeHeldPaymentRow($tour, $attempt, $booked, $requested, $holdStatus);
            if ($booked > 0) {
                $supplierResults = $outcomes->map(fn ($o) => [
                    'order_id' => (int) $o->order_id,
                    'status' => (string) $o->status,
                    'booked_amount' => (float) ($o->booked_amount ?? 0),
                ])->all();
                $settled = $this->findAttemptByUuid((string) $attempt['attempt_uuid']) ?? $attempt;
                $settled['authorized_amount'] = $booked; // commission statement: what was actually charged
                $this->recordCommission($tour, $settled, $supplierResults);
            }
        }

        \Illuminate\Support\Facades\Log::info('online_hold_settled', [
            'attempt' => $attempt['attempt_uuid'],
            'tour_id' => $attempt['tour_id'],
            'method' => $attempt['payment_method'],
            'held' => $requested,
            'charged' => $booked,
            'released' => $release,
            'reason' => $reason,
        ]);

        return [
            'attempt_uuid' => $attempt['attempt_uuid'],
            'state' => $holdStatus,
            'held' => $requested,
            'charged' => $booked,
            'released' => $release,
        ];
    }

    /**
     * Payment Details row: on hold → final charged amount (or declined when nothing was booked).
     *
     * @param  array<string, mixed>  $attempt
     */
    protected function finalizeHeldPaymentRow(Tour $tour, array $attempt, float $booked, float $requested, string $holdStatus): void
    {
        $tour->refresh();
        $details = json_decode((string) ($tour->payment_details ?? '[]'), true) ?: [];
        $uuid = (string) $attempt['attempt_uuid'];
        $meta = (array) ($attempt['metadata'] ?? []);
        $isCard = ($attempt['payment_method'] ?? '') === 'stripe';

        $hasRow = false;
        foreach ($details as $row) {
            if (is_array($row) && ($row['payment_attempt_uuid'] ?? null) === $uuid) {
                $hasRow = true;
                break;
            }
        }

        // Normal case: no row during the hold → record the actual charge now (one row only).
        if (! $hasRow) {
            if ($booked <= 0) {
                return; // nothing charged → nothing to show
            }
            $this->recordAutoVerifiedWalletPayment(
                $tour,
                $booked,
                (string) ($attempt['currency_code'] ?? 'SGD'),
                $isCard ? (string) ($attempt['stripe_payment_intent_id'] ?? '') : ($attempt['wallet_reservation_ref'] ?? null),
                $uuid,
                $meta,
                $requested,
                [
                    'payment_type' => $isCard ? 'stripe' : 'wallet',
                    'remarks' => $holdStatus === 'partially_captured'
                        ? ($isCard ? 'Card' : 'Wallet').' payment — charged for booked services only'
                        : ($isCard ? 'Card payment — auto verified' : 'Wallet payment — auto verified'),
                    'charged_amount' => round($booked, 2),
                    '_track_label' => $isCard ? 'Card Payment Auto Verified' : 'Wallet Payment Auto Verified',
                ]
            );

            return;
        }

        // Legacy rows written at hold time (before this change): turn into the final charge.
        foreach ($details as $idx => $row) {
            if (! is_array($row) || ($row['payment_attempt_uuid'] ?? null) !== $uuid) {
                continue;
            }
            $currency = (string) ($attempt['currency_code'] ?? 'SGD');
            $fresh = $booked > 0
                ? $this->walletPaymentRow($booked, $currency, $row['transaction_id'] ?? null, $uuid, $meta, $requested)
                : [];
            $isCard = ($attempt['payment_method'] ?? '') === 'stripe';
            $details[$idx] = array_merge($row, $fresh, [
                'payment_type' => $isCard ? 'stripe' : 'wallet',
                'transaction_id' => $row['transaction_id'] ?? $uuid,
                'payment_hold' => $holdStatus,
                'hold_amount' => round($requested, 2),
                'hold_currency' => $currency,
                'charged_amount' => round($booked, 2),
                'released_amount' => round(max(0, $requested - $booked), 2),
                'status' => $booked > 0 ? 1 : 2,
                'remarks' => match ($holdStatus) {
                    'captured' => ($isCard ? 'Card' : 'Wallet').' charged in full — all services booked',
                    'partially_captured' => ($isCard ? 'Card' : 'Wallet').' charged for booked services only; '
                        .number_format(max(0, $requested - $booked), 2).' '.$currency.' released',
                    default => 'No service could be booked — full hold released',
                },
            ]);
            break;
        }

        $tour->payment_details = json_encode(array_values($details));
        $tour->save();
    }

    /**
     * Split the held amount across the tour's online services by their selling price.
     * Last service takes the rounding remainder so shares always sum to the held amount.
     *
     * @param  array<string, mixed>  $attempt
     * @return array<int, float>  order_id => share (attempt currency)
     */
    protected function computeHoldShares(Tour $tour, array $attempt): array
    {
        // Only services not paid yet: booked / earlier-paid services are never held or charged again.
        $orders = $this->classificationService->unpaidOnlineOrders($tour);
        if ($orders->isEmpty()) {
            return [];
        }

        $breakdown = app(CommissionBreakdownService::class);
        $weights = [];
        foreach ($orders as $order) {
            $line = $breakdown->lineForOrder($order, $tour);
            $weights[(int) $order->getKey()] = max(0.0, (float) ($line['charged_amount'] ?? 0) ?: (float) ($line['agent_price'] ?? 0));
        }
        $total = array_sum($weights);
        $count = count($weights);
        $held = round((float) $attempt['requested_amount'], 2);

        $shares = [];
        $allocated = 0.0;
        $i = 0;
        foreach ($weights as $orderId => $weight) {
            $i++;
            if ($i === $count) {
                $shares[$orderId] = round($held - $allocated, 2);
                break;
            }
            $share = $total > 0 ? round($held * $weight / $total, 2) : round($held / $count, 2);
            $shares[$orderId] = $share;
            $allocated = round($allocated + $share, 2);
        }

        return $shares;
    }

    protected function moneyFromMinor(int $minor, string $currency): float
    {
        return in_array(strtolower($currency), \App\Services\Finance\PaymentGatewayService::STRIPE_ZERO_DECIMAL, true)
            ? (float) $minor
            : round($minor / 100, 2);
    }

    /**
     * @param  array<string, mixed>  $attempt
     * @param  array<int, array<string, mixed>>  $supplierResults
     * @return array<string, mixed>
     */
    protected function recordCommission(Tour $tour, array $attempt, array $supplierResults): array
    {
        try {
            return app(CommissionSettlementService::class)->record($tour, $attempt, $supplierResults);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('commission_settlement_failed', [
                'tour_id' => $tour->tour_id ?? null,
                'error' => $e->getMessage(),
            ]);

            return ['recorded' => false, 'reason' => $e->getMessage()];
        }
    }

    /**
     * Online: full payment moves the tour to Confirmed (no enquiry).
     * Mix: online-service payment unlocks enquiry on the offline total only.
     *
     * @param  array<string, mixed>  $attempt
     */
    protected function advanceAcceptance(Tour $tour, array $attempt): void
    {
        $classification = (string) ($attempt['order_classification'] ?? $tour->order_classification ?? '');
        $stage = (string) ($attempt['payment_stage'] ?? '');

        if ($classification === OrderClassificationService::ONLINE) {
            $this->confirmTour($tour, 'Online services paid in full');

            return;
        }

        if ($classification === OrderClassificationService::MIX && $stage === 'supplier_initial') {
            $status = (string) ($tour->tour_status ?? '');
            if (! in_array($status, ['Confirmed', 'Definite', 'Actual'], true)) {
                $tour->forceFill([
                    'tour_status' => 'New Enquiry',
                    'order_classification' => OrderClassificationService::MIX,
                    'payment_stage' => 'offline_negotiation',
                ])->save();
            }
        }
    }

    protected function confirmTour(Tour $tour, string $note): void
    {
        $tour->refresh();
        $status = (string) ($tour->tour_status ?? '');
        if (in_array($status, ['Confirmed', 'Definite', 'Actual'], true)) {
            return;
        }

        $tourId = (int) ($tour->tour_id ?? $tour->getKey());
        try {
            CommonHelper::appendTourStatusTrackById(
                $tourId,
                $status,
                'Confirmed',
                null,
                null,
                $note,
                null,
                auth()->user()->name ?? null,
                auth()->id(),
            );
        } catch (\Throwable) {
            // Status tracking must not block confirmation after a successful payment.
        }

        Order::query()->where('tour_id', $tourId)->update(['bookingType' => 'booking']);
        $tour->forceFill([
            'tour_status' => 'Confirmed',
            'order_classification' => OrderClassificationService::ONLINE,
            'payment_stage' => 'dmc_collection',
        ])->save();
    }

    /**
     * Persist wallet collection on tour.payment_details with status=1 (verified).
     * Mirrors TourController::verifyPayment side-effects (Confirmed → Definite).
     */
    protected function recordAutoVerifiedWalletPayment(
        Tour $tour,
        float $amount,
        string $currency,
        ?string $financeReference,
        string $attemptUuid,
        array $attemptMeta = [],
        float $requestedAmount = 0.0,
        array $overrides = [],
    ): int {
        $tour->refresh();
        $prevStatus = (string) ($tour->tour_status ?? '');
        $currency = strtoupper(trim($currency)) ?: 'SGD';
        $paymentDate = now()->toDateString();

        $paymentData = array_merge(
            $this->walletPaymentRow($amount, $currency, $financeReference, $attemptUuid, $attemptMeta, $requestedAmount),
            $overrides
        ) + ['date' => now()->format('Y-m-d H:i:s'), 'payment_date' => $paymentDate];
        $trackLabel = (string) ($overrides['_track_label'] ?? 'Wallet Payment Auto Verified');
        $trackType = (string) ($overrides['payment_type'] ?? 'wallet');
        unset($paymentData['_track_label']);

        $paymentDetails = json_decode((string) ($tour->payment_details ?? '[]'), true) ?: [];
        if (! is_array($paymentDetails)) {
            $paymentDetails = [];
        }

        // Idempotent: same attempt must not create duplicate rows.
        foreach ($paymentDetails as $idx => $existing) {
            if (is_array($existing) && ($existing['payment_attempt_uuid'] ?? null) === $attemptUuid) {
                $paymentDetails[$idx] = array_merge($existing, $paymentData, ['status' => 1]);
                $tour->payment_details = json_encode(array_values($paymentDetails));
                if ($tour->tour_status === 'Confirmed') {
                    $tour->tour_status = 'Definite';
                }
                $tour->save();

                return (int) $idx;
            }
        }

        $paymentDetails[] = $paymentData;
        $paymentIndex = count($paymentDetails) - 1;

        $tour->payment_details = json_encode($paymentDetails);
        if ($tour->tour_status === 'Confirmed') {
            $tour->tour_status = 'Definite';
        }
        $tour->save();

        try {
            CommonHelper::appendTourStatusTrackById(
                (int) ($tour->tour_id ?? $tour->getKey()),
                $prevStatus,
                (string) $tour->tour_status,
                null,
                null,
                null,
                null,
                auth()->user()->name ?? null,
                auth()->id(),
                $trackLabel,
                null,
                null,
                null,
                $amount,
                $currency,
                $paymentDate,
                $trackType,
            );
        } catch (\Throwable) {
            // Tracking must not roll back a successful wallet deduct.
        }

        return $paymentIndex;
    }

    /**
     * One payment_details row from the attempt that was debited. `amount` stays in the order
     * currency (what outstanding balance is measured in); the wallet debit is kept alongside it.
     *
     * @param  array<string, mixed>  $attemptMeta
     * @return array<string, mixed>
     */
    protected function walletPaymentRow(
        float $walletAmount,
        string $walletCurrency,
        ?string $financeReference,
        string $attemptUuid,
        array $attemptMeta = [],
        float $requestedAmount = 0.0,
    ): array {
        $orderCurrency = strtoupper((string) ($attemptMeta['order_currency'] ?? $walletCurrency));
        $converted = $orderCurrency !== $walletCurrency && isset($attemptMeta['order_amount']);
        // A supplier outcome can commit less than requested: scale the order-currency quote the same way.
        $ratio = $requestedAmount > 0 ? $walletAmount / $requestedAmount : 1.0;
        $orderAmount = $converted ? (float) $attemptMeta['order_amount'] * $ratio : $walletAmount;

        $paymentFor = strtolower(trim((string) ($attemptMeta['payment_for'] ?? '')));
        if (! in_array($paymentFor, [OrderClassificationService::ONLINE, OrderClassificationService::OFFLINE], true)) {
            $paymentFor = $this->classificationService->resolvePaymentFor(
                (string) ($attemptMeta['order_classification'] ?? ''),
                (string) ($attemptMeta['payment_stage'] ?? ''),
            );
        }

        $userTxn = trim((string) ($attemptMeta['user_transaction_id'] ?? ''));
        $userRemarks = trim((string) ($attemptMeta['user_remarks'] ?? ''));
        $row = [
            'amount' => round($orderAmount, 2),
            'original_amount' => round($orderAmount, 2),
            'currency' => $converted ? $orderCurrency : $walletCurrency,
            'exchange_rate' => $converted ? (float) ($attemptMeta['exchange_rate'] ?? 1) : 1,
            // Prefer cashier-entered reference; fall back to wallet ledger / attempt id.
            'transaction_id' => $userTxn !== '' ? $userTxn : ($financeReference ?: $attemptUuid),
            'remarks' => $userRemarks !== '' ? $userRemarks : 'Wallet payment — auto verified',
            'user_remarks' => $userRemarks,
            'payment_type' => 'wallet',
            // online = online-service payment; offline = offline-service payment
            'payment_for' => $paymentFor,
            'status' => 1, // verified — no manual approve needed
            'auto_verified' => true,
            'payment_attempt_uuid' => $attemptUuid,
        ];
        if ($converted) {
            $row += [
                'wallet_amount' => round($walletAmount, 2),
                'wallet_currency' => $walletCurrency,
                'conversion_charge' => round((float) ($attemptMeta['conversion_charge'] ?? 0) * $ratio, 2),
                // Fee is in wallet/destination currency — keep percent for statement display.
                'conversion_charge_percent' => (float) (
                    $attemptMeta['conversion_charge_percent']
                    ?? $attemptMeta['charge_percent']
                    ?? 0
                ),
                'conversion_charge_currency' => $walletCurrency,
            ];
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $attempt
     * @param  array<string, mixed>  $eligibility
     * @return array<string, mixed>
     */
    protected function initiateWallet(Tour $tour, array $attempt, array $eligibility, float $amount): array
    {
        // Wallet payments are debited from the DMC wallet, not the agent wallet.
        $reserve = $this->walletService->reserve([
            'idempotency_key' => $attempt['idempotency_key'].':reserve',
            'wallet_owner_type' => 'dmc',
            'wallet_owner_id' => (int) $tour->dmc_id,
            'wallet_currency_code' => $attempt['currency_code'],
            'amount' => $amount,
            'tour_id' => $attempt['tour_id'],
        ]);

        $reservationRef = $reserve['data']['reservation_reference']
            ?? $reserve['reservation_reference']
            ?? ($attempt['idempotency_key'].':reserve');

        $this->updateAttempt((int) $attempt['id'], [
            'status' => 'authorized',
            'authorized_amount' => $amount,
            'wallet_reservation_ref' => $reservationRef,
        ]);

        if ($this->isHoldAttempt($attempt)) {
            try {
                return $this->openHold($tour, (string) $attempt['attempt_uuid']);
            } catch (\Throwable $e) {
                // Could not open the hold → give the reservation back.
                try {
                    $this->walletService->releaseReservation([
                        'idempotency_key' => $attempt['idempotency_key'].':rollback',
                        'reservation_reference' => $reservationRef,
                        'release_amount' => $amount,
                    ]);
                } catch (\Throwable $releaseError) {
                    \Illuminate\Support\Facades\Log::error('wallet_rollback_failed', [
                        'attempt' => $attempt['attempt_uuid'],
                        'error' => $releaseError->getMessage(),
                    ]);
                }
                $this->updateAttempt((int) $attempt['id'], ['status' => 'failed', 'last_error' => $e->getMessage()]);

                throw $e;
            }
        }

        // Quoted amount is committed now. An explicit supplier-result payload can
        // still reduce or release funds via completeWalletAfterSupplier.
        try {
            return $this->completeWalletAfterSupplier($tour, (string) $attempt['attempt_uuid'], []);
        } catch (\Throwable $e) {
            // Commit never happened: give the reserved amount back so the DMC wallet is not left locked.
            $current = $this->findAttemptByUuid((string) $attempt['attempt_uuid']);
            if (($current['status'] ?? '') !== 'completed') {
                try {
                    $this->walletService->releaseReservation([
                        'idempotency_key' => $attempt['idempotency_key'].':rollback',
                        'reservation_reference' => $reservationRef,
                        'release_amount' => $amount,
                    ]);
                } catch (\Throwable $releaseError) {
                    \Illuminate\Support\Facades\Log::error('wallet_rollback_failed', [
                        'attempt' => $attempt['attempt_uuid'],
                        'error' => $releaseError->getMessage(),
                    ]);
                }
                $this->updateAttempt((int) $attempt['id'], ['status' => 'failed', 'last_error' => $e->getMessage()]);
            }

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $attempt
     * @param  array<string, mixed>  $eligibility
     * @return array<string, mixed>
     */
    protected function initiateStripe(Tour $tour, array $attempt, array $eligibility, float $amount): array
    {
        $destination = null;
        if ($eligibility['payment_recipient'] === 'dmc') {
            $account = $this->stripeConnectService->getAccountForDmc((int) $tour->dmc_id);
            $destination = $account['stripe_account_id'] ?? null;
            if (! $destination || ! $this->stripeConnectService->isReadyForCharges((int) $tour->dmc_id)) {
                throw new RuntimeException('DMC Stripe account is not ready.');
            }
        }

        $manualCapture = $eligibility['payment_stage'] === 'supplier_initial';

        // Commission is credited once on the wallet ledger. A Stripe application
        // fee would take that same commission a second time on the card charge.
        $applicationFee = 0.0;

        $intent = $this->paymentGatewayService->createStripePaymentIntent([
            'tour_id' => $attempt['tour_id'],
            'amount' => $amount,
            'currency_code' => $attempt['currency_code'],
            'idempotency_key' => $attempt['idempotency_key'],
            'capture_method' => $manualCapture ? 'manual' : 'automatic',
            'destination_account' => $destination,
            'travclicks_markup' => $applicationFee,
            'application_fee_amount' => $applicationFee,
            'payment_attempt_uuid' => $attempt['attempt_uuid'],
            'payment_stage' => $eligibility['payment_stage'],
            'payment_recipient' => $eligibility['payment_recipient'],
            'metadata' => [
                'tour_id' => (string) $attempt['tour_id'],
                'payment_attempt_uuid' => $attempt['attempt_uuid'],
                'order_classification' => $eligibility['order_classification'],
            ],
        ]);

        $this->updateAttempt((int) $attempt['id'], [
            'status' => 'pending',
            'stripe_payment_intent_id' => $intent['id'] ?? null,
            'stripe_account_id' => $destination,
            'authorized_amount' => $manualCapture ? $amount : null,
            'metadata' => array_merge((array) ($attempt['metadata'] ?? []), [
                'stripe_client_secret' => $intent['client_secret'] ?? null,
            ]),
        ]);

        return [
            'attempt' => $this->findAttemptByUuid($attempt['attempt_uuid']),
            'stripe' => [
                'payment_intent_id' => $intent['id'] ?? null,
                'client_secret' => $intent['client_secret'] ?? null,
                'publishable_key' => config('payment.stripe.publishable_key'),
                'destination_account' => $destination,
                'capture_method' => $manualCapture ? 'manual' : 'automatic',
            ],
            'requires_supplier_booking' => $manualCapture,
            'next_step' => $manualCapture
                ? 'confirm_card_then_book_supplier_then_capture'
                : 'confirm_card_payment',
        ];
    }

    /**
     * @param  array<string, mixed>  $attempt
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    protected function initiateOfflineRecord(Tour $tour, array $attempt, array $input, float $amount): array
    {
        // Preserve existing TourController payment_details flow via synthetic request when possible.
        $legacyType = match ($attempt['payment_method']) {
            'bank_transfer' => 'online',
            'card_offline' => 'card',
            default => $attempt['payment_method'],
        };

        $this->updateAttempt((int) $attempt['id'], [
            'status' => 'completed',
            'captured_amount' => $amount,
            'metadata' => array_merge((array) ($attempt['metadata'] ?? []), [
                'legacy_payment_type' => $legacyType,
                'remarks' => $input['remarks'] ?? null,
                'transaction_id' => $input['transaction_id'] ?? null,
                'payment_date' => $input['payment_date'] ?? now()->toDateString(),
            ]),
        ]);

        return [
            'attempt' => $this->findAttemptByUuid($attempt['attempt_uuid']),
            'legacy_payment_type' => $legacyType,
            'record_via' => 'tour.add-payment',
            'confirmed' => false,
            'message' => 'Offline payment attempt recorded. Persist via existing add-payment endpoint for UI parity.',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    protected function persistSupplierOutcomes(int $attemptId, int $tourId, array $results): float
    {
        if ($results === [] || ! Schema::hasTable('supplier_booking_outcomes')) {
            return 0.0;
        }

        $booked = 0.0;
        foreach ($results as $row) {
            $status = strtolower((string) ($row['status'] ?? 'failed'));
            $amount = (float) ($row['booked_amount'] ?? $row['quoted_amount'] ?? 0);
            DB::table('supplier_booking_outcomes')->insert([
                'outcome_uuid' => (string) Str::uuid(),
                'tour_id' => $tourId,
                'order_id' => $row['order_id'] ?? null,
                'payment_attempt_id' => $attemptId,
                'supplier_api_id' => $row['supplier_api_id'] ?? null,
                'supplier_booking_reference' => $row['supplier_booking_reference'] ?? null,
                'status' => $status,
                'quoted_amount' => (float) ($row['quoted_amount'] ?? 0),
                'booked_amount' => $status === 'booked' ? $amount : null,
                'currency_code' => $row['currency_code'] ?? null,
                'request_meta' => isset($row['request_meta']) ? json_encode($row['request_meta']) : null,
                'response_meta' => isset($row['response_meta']) ? json_encode($row['response_meta']) : null,
                'error_message' => $row['error_message'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($status === 'booked') {
                $booked += $amount;
            }
        }

        return round($booked, 2);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function createAttempt(array $data): array
    {
        if (! Schema::hasTable('payment_attempts')) {
            return array_merge($data, [
                'id' => 0,
                'attempt_uuid' => (string) Str::uuid(),
            ]);
        }

        $uuid = (string) Str::uuid();
        $id = DB::table('payment_attempts')->insertGetId([
            'attempt_uuid' => $uuid,
            'idempotency_key' => $data['idempotency_key'],
            'tour_id' => $data['tour_id'],
            'order_classification' => $data['order_classification'],
            'payment_stage' => $data['payment_stage'],
            'payment_method' => $data['payment_method'],
            'payment_recipient' => $data['payment_recipient'],
            'dmc_id' => $data['dmc_id'],
            'agent_id' => $data['agent_id'],
            'currency_code' => $data['currency_code'],
            'requested_amount' => $data['requested_amount'],
            'status' => $data['status'],
            'fx_snapshot_id' => $data['fx_snapshot_id'],
            'eligibility_snapshot' => json_encode($data['eligibility_snapshot'] ?? []),
            'metadata' => json_encode($data['metadata'] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->findAttemptByUuid($uuid) ?? ['id' => $id, 'attempt_uuid' => $uuid];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function updateAttempt(int $id, array $data): void
    {
        if ($id <= 0 || ! Schema::hasTable('payment_attempts')) {
            return;
        }

        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $data['metadata'] = json_encode($data['metadata']);
        }
        if (isset($data['eligibility_snapshot']) && is_array($data['eligibility_snapshot'])) {
            $data['eligibility_snapshot'] = json_encode($data['eligibility_snapshot']);
        }

        $data['updated_at'] = now();
        DB::table('payment_attempts')->where('id', $id)->update($data);
    }

    protected function findAttemptByIdempotency(string $key): ?array
    {
        if (! Schema::hasTable('payment_attempts')) {
            return null;
        }
        $row = DB::table('payment_attempts')->where('idempotency_key', $key)->first();

        return $row ? $this->normalizeAttempt((array) $row) : null;
    }

    protected function findAttemptByUuid(string $uuid): ?array
    {
        if (! Schema::hasTable('payment_attempts')) {
            return null;
        }
        $row = DB::table('payment_attempts')->where('attempt_uuid', $uuid)->first();

        return $row ? $this->normalizeAttempt((array) $row) : null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function normalizeAttempt(array $row): array
    {
        foreach (['metadata', 'eligibility_snapshot'] as $jsonCol) {
            if (isset($row[$jsonCol]) && is_string($row[$jsonCol])) {
                $row[$jsonCol] = json_decode($row[$jsonCol], true) ?: [];
            }
        }

        return $row;
    }
}
