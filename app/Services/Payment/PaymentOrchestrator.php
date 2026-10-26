<?php

namespace App\Services\Payment;

use App\Helpers\CommonHelper;
use App\Models\Order;
use App\Models\Tour;
use App\Services\Finance\PaymentGatewayService;
use App\Services\Finance\WalletService;
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

        $eligibility = $this->eligibilityService->forTour($tour);
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
            if (isset($input['amount']) && abs((float) $input['amount'] - $amount) > 0.0001) {
                throw new RuntimeException('Payment amount cannot be modified for this payment stage.');
            }
        } else {
            $amount = isset($input['amount']) ? (float) $input['amount'] : $amount;
            $outstanding = (float) $eligibility['outstanding_balance'];
            if ($amount <= 0 || $amount - $outstanding > 0.0001) {
                throw new RuntimeException('Payment amount exceeds outstanding balance or is invalid.');
            }
        }

        if ($eligibility['order_classification'] === OrderClassificationService::EMPTY) {
            throw new RuntimeException('Cannot pay an empty order.');
        }

        $currency = (string) $eligibility['currency'];
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
            'metadata' => $input['metadata'] ?? [],
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
        $paymentIndex = $this->recordAutoVerifiedWalletPayment(
            $tour,
            $finalAmount,
            (string) ($attempt['currency_code'] ?? 'SGD'),
            $commit['data']['finance_reference'] ?? $commit['finance_reference'] ?? null,
            (string) ($attempt['attempt_uuid'] ?? $attemptUuid),
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
    ): int {
        $tour->refresh();
        $prevStatus = (string) ($tour->tour_status ?? '');
        $currency = strtoupper(trim($currency)) ?: 'SGD';
        $paymentDate = now()->toDateString();

        $paymentData = [
            'amount' => round($amount, 4),
            'original_amount' => round($amount, 4),
            'currency' => $currency,
            'exchange_rate' => 1,
            'transaction_id' => $financeReference ?: $attemptUuid,
            'remarks' => 'Wallet payment — auto verified',
            'date' => now()->format('Y-m-d H:i:s'),
            'payment_date' => $paymentDate,
            'payment_type' => 'wallet',
            'status' => 1, // verified — no manual approve needed
            'auto_verified' => true,
            'payment_attempt_uuid' => $attemptUuid,
        ];

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
                'Wallet Payment Auto Verified',
                null,
                null,
                null,
                $amount,
                $currency,
                $paymentDate,
                'wallet',
            );
        } catch (\Throwable) {
            // Tracking must not roll back a successful wallet deduct.
        }

        return $paymentIndex;
    }

    /**
     * @param  array<string, mixed>  $attempt
     * @param  array<string, mixed>  $eligibility
     * @return array<string, mixed>
     */
    protected function initiateWallet(Tour $tour, array $attempt, array $eligibility, float $amount): array
    {
        $walletOwnerId = $this->eligibilityService->resolveAgencyWalletOwnerId(
            (int) $tour->agent_id,
            (int) $tour->dmc_id,
            (string) $attempt['currency_code'],
        );

        $reserve = $this->walletService->reserve([
            'idempotency_key' => $attempt['idempotency_key'].':reserve',
            'wallet_owner_type' => 'agent',
            'wallet_owner_id' => $walletOwnerId,
            'source_dmc_id' => (int) $tour->dmc_id,
            'wallet_currency_code' => $attempt['currency_code'],
            'amount' => $amount,
            'tour_id' => $attempt['tour_id'],
            'allow_legacy_unscoped' => (bool) config('payment.wallet_legacy_unscoped_agent', true),
        ]);

        $reservationRef = $reserve['data']['reservation_reference']
            ?? $reserve['reservation_reference']
            ?? ($attempt['idempotency_key'].':reserve');

        $this->updateAttempt((int) $attempt['id'], [
            'status' => 'authorized',
            'authorized_amount' => $amount,
            'wallet_reservation_ref' => $reservationRef,
        ]);

        // Quoted amount is committed now. An explicit supplier-result payload can
        // still reduce or release funds via completeWalletAfterSupplier.
        return $this->completeWalletAfterSupplier($tour, (string) $attempt['attempt_uuid'], []);
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

        return round($booked, 4);
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
