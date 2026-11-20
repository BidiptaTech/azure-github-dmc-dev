<?php

namespace App\Services\SupplierCredit;

use App\Models\Country;
use App\Models\SupplierCreditAccount;
use App\Models\SupplierCreditTransaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

/**
 * TravClicks credit held with each API vendor (per supplier code + demo/live).
 *
 *  - Booking with the vendor  → booking_debit  (balance goes down)
 *  - Cancelled with the vendor → cancel_refund (debit − cancellation charge comes back)
 *  - Admin top-up / adjustment → topup / adjustment_*
 *
 * Every change is one row in supplier_credit_transactions with balance before/after,
 * the vendor's own amount + currency and the FX rate used. Money is 2 decimals.
 *
 * Recording methods never throw: once the vendor has confirmed a booking the local
 * flow must continue. Enforcement happens up front in assertSufficient().
 */
class SupplierCreditService
{
    public const ENV_DEMO = 'demo';

    public const ENV_LIVE = 'live';

    public function normalizeCode(?string $code): string
    {
        $code = strtolower(trim((string) $code));

        return match ($code) {
            'tiniva' => 'tinivia',
            'mgbedbank', 'mg' => 'mg_bedbank',
            default => $code,
        };
    }

    public function normalizeEnvironment(?string $env): string
    {
        return strtolower(trim((string) $env)) === self::ENV_LIVE ? self::ENV_LIVE : self::ENV_DEMO;
    }

    public static function money(float|int|string|null $value): float
    {
        return round((float) $value, 2);
    }

    /**
     * What the hotel vendor charges TravClicks: net price (agency cost), gross as fallback.
     *
     * @param  array<string, mixed>  $recheck  recheck result or booking_details['recheck']
     * @return array{amount: float, currency: string, net: ?float, gross: ?float}
     */
    public static function hotelCostFromRecheck(array $recheck): array
    {
        $net = isset($recheck['supplier_net_price']) ? (float) $recheck['supplier_net_price'] : null;
        $gross = isset($recheck['supplier_gross_price']) ? (float) $recheck['supplier_gross_price'] : null;

        return [
            'amount' => self::money(($net ?? 0) > 0 ? $net : ($gross ?? 0)),
            'currency' => strtoupper(trim((string) ($recheck['currency'] ?? ''))) ?: 'SGD',
            'net' => $net,
            'gross' => $gross,
        ];
    }

    /**
     * @param  array<string, mixed>  $details  orders.booking_details from a hotel book call
     */
    public static function hotelSupplierReference(array $details): ?string
    {
        $resp = is_array($details['book_response'] ?? null) ? $details['book_response'] : [];
        $mg = is_array($resp['bookingDetails'] ?? null) ? $resp['bookingDetails'] : [];
        $hb = is_array($resp['booking'] ?? null) ? $resp['booking'] : [];
        $ref = trim((string) ($details['supplier_booking_reference']
            ?? $mg['mgBookingID'] ?? $mg['MGBookingID'] ?? $hb['reference'] ?? ''));

        return $ref !== '' ? $ref : null;
    }

    /**
     * Debit vendor credit for a confirmed hotel booking (orders.booking_details shape).
     *
     * @param  array<string, mixed>  $details
     */
    public function recordHotelBooking(array $details, ?int $tourId, ?int $orderId, ?string $agencyRef, string $channel = 'approve'): ?SupplierCreditTransaction
    {
        $recheck = is_array($details['recheck'] ?? null) ? $details['recheck'] : [];
        $cost = self::hotelCostFromRecheck($recheck);
        $supplierRef = self::hotelSupplierReference($details);
        $agencyRef = trim((string) ($details['agency_booking_id'] ?? $agencyRef ?? '')) ?: null;

        return $this->recordBookingDebit([
            'supplier_code' => (string) ($details['supplier_code'] ?? ''),
            'environment' => (string) ($details['api_environment'] ?? ''),
            'amount' => $cost['amount'],
            'currency' => $cost['currency'],
            'service_type' => 'hotel',
            'tour_id' => $tourId,
            'order_id' => $orderId,
            'supplier_booking_reference' => $supplierRef,
            'agency_reference' => $agencyRef,
            'idempotency_key' => 'booking_debit:hotel:'.($orderId ?: 'api').':'.($supplierRef ?? $agencyRef ?? Str::uuid()),
            'description' => 'Hotel booked with vendor'.($tourId ? ' · Tour #'.$tourId : '').($channel === 'api' ? ' (API)' : ''),
            'metadata' => [
                'channel' => $channel,
                'supplier_net_price' => $cost['net'],
                'supplier_gross_price' => $cost['gross'],
                'hotel_name' => $details['book_payload']['HotelName'] ?? null,
            ],
        ]);
    }

    public function findAccount(string $code, string $environment): ?SupplierCreditAccount
    {
        return SupplierCreditAccount::query()
            ->where('supplier_code', $this->normalizeCode($code))
            ->where('environment', $this->normalizeEnvironment($environment))
            ->first();
    }

    /**
     * Block a vendor booking when an enforced credit account cannot cover it.
     * No account / inactive / not enforced → allowed (recorded later on an auto account).
     *
     * @throws SupplierCreditInsufficientException
     */
    public function assertSufficient(string $code, ?string $environment, float $amount, ?string $currency): void
    {
        $account = $this->findAccount($code, (string) $environment);
        if (! $account || ! $account->isActive() || ! $account->enforce_limit) {
            return;
        }

        $amount = self::money($amount);
        if ($amount <= 0) {
            return;
        }

        $fx = $this->convert($amount, (string) $currency, $account->currency_code);
        if ($fx['amount'] === null) {
            Log::warning('supplier_credit_check_fx_missing', [
                'supplier_code' => $account->supplier_code,
                'environment' => $account->environment,
                'from' => $currency,
                'to' => $account->currency_code,
            ]);

            throw new SupplierCreditInsufficientException(
                'This online service cannot be booked right now: the exchange rate needed to check TravClicks supplier credit is missing. Please contact TravClicks.'
            );
        }

        if ((float) $account->balance + 0.001 < $fx['amount']) {
            Log::warning('supplier_credit_insufficient', [
                'supplier_code' => $account->supplier_code,
                'environment' => $account->environment,
                'required' => $fx['amount'],
                'available' => (float) $account->balance,
                'currency' => $account->currency_code,
            ]);

            // Do not reveal TravClicks' vendor balance to DMC users.
            throw new SupplierCreditInsufficientException(
                'This online service cannot be booked right now because the TravClicks supplier credit limit has been reached. Please contact TravClicks.'
            );
        }
    }

    /**
     * @param  array{
     *   supplier_code: string, environment?: ?string, amount: float, currency: ?string,
     *   service_type?: ?string, tour_id?: ?int, order_id?: ?int,
     *   supplier_booking_reference?: ?string, agency_reference?: ?string,
     *   idempotency_key: string, description?: ?string, metadata?: array<string, mixed>
     * }  $ctx
     */
    public function recordBookingDebit(array $ctx): ?SupplierCreditTransaction
    {
        try {
            $amount = self::money($ctx['amount'] ?? 0);
            if ($amount <= 0) {
                Log::warning('supplier_credit_debit_skipped_zero_amount', array_intersect_key($ctx, array_flip(['supplier_code', 'tour_id', 'order_id'])));

                return null;
            }

            return $this->post(
                code: (string) $ctx['supplier_code'],
                environment: (string) ($ctx['environment'] ?? ''),
                type: 'booking_debit',
                supplierAmount: $amount,
                supplierCurrency: (string) ($ctx['currency'] ?? ''),
                idempotencyKey: (string) $ctx['idempotency_key'],
                attrs: [
                    'service_type' => $ctx['service_type'] ?? null,
                    'tour_id' => $ctx['tour_id'] ?? null,
                    'order_id' => $ctx['order_id'] ?? null,
                    'supplier_booking_reference' => $ctx['supplier_booking_reference'] ?? null,
                    'agency_reference' => $ctx['agency_reference'] ?? null,
                    'description' => $ctx['description'] ?? null,
                    'metadata' => $ctx['metadata'] ?? [],
                ],
            );
        } catch (Throwable $e) {
            Log::error('supplier_credit_debit_failed', [
                'supplier_code' => $ctx['supplier_code'] ?? null,
                'tour_id' => $ctx['tour_id'] ?? null,
                'order_id' => $ctx['order_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Give back what the vendor refunded after a confirmed cancellation:
     * original debit − cancellation charge (charge converted to the account currency).
     *
     * @param  array{
     *   order_id: int, service_type: string, tour_id?: ?int,
     *   cancellation_charge?: ?float, charge_currency?: ?string,
     *   charge_reported?: bool, supplier_booking_reference?: ?string, metadata?: array<string, mixed>
     * }  $ctx
     * @return array<int, SupplierCreditTransaction>
     */
    public function recordCancellationRefund(array $ctx): array
    {
        $created = [];

        try {
            $orderId = (int) ($ctx['order_id'] ?? 0);
            $serviceType = (string) ($ctx['service_type'] ?? '');
            if ($orderId <= 0 || $serviceType === '') {
                return [];
            }

            $debits = SupplierCreditTransaction::query()
                ->where('order_id', $orderId)
                ->where('service_type', $serviceType)
                ->where('txn_type', 'booking_debit')
                ->orderBy('id')
                ->get();

            $chargeLeft = max(0, (float) ($ctx['cancellation_charge'] ?? 0));
            $chargeCurrency = (string) ($ctx['charge_currency'] ?? '');
            $chargeReported = (bool) ($ctx['charge_reported'] ?? false);

            foreach ($debits as $debit) {
                $alreadyRefunded = (float) SupplierCreditTransaction::query()
                    ->where('related_txn_id', $debit->id)
                    ->where('txn_type', 'cancel_refund')
                    ->sum('amount');
                $refundable = self::money((float) $debit->amount - $alreadyRefunded);
                if ($refundable <= 0) {
                    continue;
                }

                $chargeInAccount = 0.0;
                $chargeFx = null;
                if ($chargeLeft > 0) {
                    $fx = $this->convert($chargeLeft, $chargeCurrency !== '' ? $chargeCurrency : $debit->currency_code, $debit->currency_code);
                    $chargeFx = $fx;
                    $chargeInAccount = min($refundable, (float) ($fx['amount'] ?? 0));
                    $chargeLeft = 0; // applied once, to the first open debit
                }

                $refund = self::money($refundable - $chargeInAccount);
                if ($refund <= 0) {
                    continue;
                }

                $txn = $this->post(
                    code: $debit->supplier_code,
                    environment: $debit->environment,
                    type: 'cancel_refund',
                    supplierAmount: $refund,
                    supplierCurrency: $debit->currency_code,
                    idempotencyKey: 'cancel_refund:'.$debit->id,
                    attrs: [
                        'service_type' => $serviceType,
                        'tour_id' => $ctx['tour_id'] ?? $debit->tour_id,
                        'order_id' => $orderId,
                        'related_txn_id' => $debit->id,
                        'supplier_booking_reference' => $ctx['supplier_booking_reference'] ?? $debit->supplier_booking_reference,
                        'agency_reference' => $debit->agency_reference,
                        'cancellation_charge' => $chargeInAccount > 0 ? self::money($chargeInAccount) : 0,
                        // Confirmed vendor cancel → auto-verify (no manual TravClicks review).
                        'reconciliation_status' => 'reconciled',
                        'reconciled_at' => now(),
                        'description' => 'Cancellation refund for '.$serviceType.' order #'.$orderId
                            .($chargeInAccount > 0 ? ' (less cancellation charge '.number_format($chargeInAccount, 2).' '.$debit->currency_code.')' : ''),
                        'metadata' => array_merge($ctx['metadata'] ?? [], [
                            'original_debit' => (float) $debit->amount,
                            'already_refunded' => self::money($alreadyRefunded),
                            'charge_reported_by_supplier' => $chargeReported,
                            'charge_source_amount' => isset($ctx['cancellation_charge']) ? self::money($ctx['cancellation_charge']) : null,
                            'charge_source_currency' => $chargeCurrency ?: null,
                            'charge_fx' => $chargeFx,
                            'auto_verified' => true,
                        ]),
                    ],
                );
                if ($txn) {
                    $created[] = $txn;
                }
            }
        } catch (Throwable $e) {
            Log::error('supplier_credit_refund_failed', [
                'order_id' => $ctx['order_id'] ?? null,
                'service_type' => $ctx['service_type'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        return $created;
    }

    /**
     * Admin: add credit (top-up) or a signed manual adjustment.
     */
    public function adminPost(
        SupplierCreditAccount $account,
        string $type,
        float $amount,
        string $note,
        ?int $userId,
        ?string $ip,
    ): SupplierCreditTransaction {
        if (! in_array($type, ['topup', 'adjustment_credit', 'adjustment_debit'], true)) {
            throw new InvalidArgumentException('Unsupported credit operation.');
        }
        $amount = self::money($amount);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero.');
        }

        $txn = $this->post(
            code: $account->supplier_code,
            environment: $account->environment,
            type: $type,
            supplierAmount: $amount,
            supplierCurrency: $account->currency_code,
            idempotencyKey: $type.':'.(string) Str::uuid(),
            attrs: [
                'description' => $note,
                'created_by' => $userId,
                'created_ip' => $ip,
                'reconciliation_status' => 'reconciled',
                'metadata' => ['entered_by_admin' => true],
            ],
            accountId: $account->id,
        );

        if (! $txn) {
            throw new InvalidArgumentException('Could not post the credit transaction.');
        }

        return $txn;
    }

    /**
     * Convert via countries.current_rate (SGD pivot).
     *
     * @return array{amount: ?float, rate: ?float, from: string, to: string, source: string}
     */
    public function convert(float $amount, string $from, string $to): array
    {
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));
        if ($from === '' || $from === $to) {
            return ['amount' => self::money($amount), 'rate' => 1.0, 'from' => $from ?: $to, 'to' => $to, 'source' => 'same_currency'];
        }

        $rate = Country::exchangeRateBetween($from, $to);
        if ($rate === null || $rate <= 0) {
            return ['amount' => null, 'rate' => null, 'from' => $from, 'to' => $to, 'source' => 'rate_missing'];
        }

        return [
            'amount' => self::money($amount * $rate),
            'rate' => round($rate, 8),
            'from' => $from,
            'to' => $to,
            'source' => 'countries.current_rate',
        ];
    }

    /**
     * Single write path: lock account, idempotency check, convert, insert line, move balance.
     *
     * @param  array<string, mixed>  $attrs
     */
    protected function post(
        string $code,
        string $environment,
        string $type,
        float $supplierAmount,
        string $supplierCurrency,
        string $idempotencyKey,
        array $attrs = [],
        ?int $accountId = null,
    ): ?SupplierCreditTransaction {
        $code = $this->normalizeCode($code);
        $environment = $this->normalizeEnvironment($environment);
        $supplierCurrency = strtoupper(trim($supplierCurrency));
        $idempotencyKey = Str::limit($idempotencyKey, 190, '');

        $existing = SupplierCreditTransaction::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($code, $environment, $type, $supplierAmount, $supplierCurrency, $idempotencyKey, $attrs, $accountId) {
                $account = $accountId
                    ? SupplierCreditAccount::query()->whereKey($accountId)->lockForUpdate()->first()
                    : $this->lockOrCreateAccount($code, $environment, $supplierCurrency);

                if (! $account) {
                    return null;
                }

                $direction = in_array($type, ['booking_debit', 'adjustment_debit'], true) ? 'debit' : 'credit';
                $fx = $this->convert($supplierAmount, $supplierCurrency !== '' ? $supplierCurrency : $account->currency_code, $account->currency_code);
                $amount = $fx['amount'] ?? 0.0;
                $reconciliation = $attrs['reconciliation_status'] ?? 'unreconciled';
                if ($fx['amount'] === null) {
                    // Keep the line (visible in the report) but don't move the balance blindly.
                    $reconciliation = 'needs_review';
                }

                $before = self::money($account->balance);
                $after = self::money($direction === 'debit' ? $before - $amount : $before + $amount);

                $metadata = array_merge($attrs['metadata'] ?? [], ['fx' => $fx]);

                $txn = new SupplierCreditTransaction;
                $txn->forceFill([
                    'txn_uuid' => (string) Str::uuid(),
                    'account_id' => $account->id,
                    'supplier_code' => $account->supplier_code,
                    'environment' => $account->environment,
                    'txn_type' => $type,
                    'direction' => $direction,
                    'currency_code' => $account->currency_code,
                    'amount' => $amount,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'supplier_amount' => self::money($supplierAmount),
                    'supplier_currency' => $supplierCurrency !== '' ? $supplierCurrency : $account->currency_code,
                    'fx_rate' => $fx['rate'],
                    'fx_source' => $fx['source'],
                    'cancellation_charge' => $attrs['cancellation_charge'] ?? null,
                    'service_type' => $attrs['service_type'] ?? null,
                    'tour_id' => $attrs['tour_id'] ?? null,
                    'order_id' => $attrs['order_id'] ?? null,
                    'related_txn_id' => $attrs['related_txn_id'] ?? null,
                    'supplier_booking_reference' => isset($attrs['supplier_booking_reference']) ? Str::limit((string) $attrs['supplier_booking_reference'], 190, '') : null,
                    'agency_reference' => isset($attrs['agency_reference']) ? Str::limit((string) $attrs['agency_reference'], 190, '') : null,
                    'idempotency_key' => $idempotencyKey,
                    'reconciliation_status' => $reconciliation,
                    'reconciled_at' => $attrs['reconciled_at'] ?? ($reconciliation === 'reconciled' ? now() : null),
                    'reconciled_by' => $attrs['reconciled_by'] ?? null,
                    'description' => $attrs['description'] ?? null,
                    'metadata' => $metadata,
                    'created_by' => $attrs['created_by'] ?? (auth()->id() ?: null),
                    'created_ip' => $attrs['created_ip'] ?? (app()->runningInConsole() ? null : request()->ip()),
                ])->save();

                $account->forceFill([
                    'balance' => $after,
                    'total_credit_added' => self::money((float) $account->total_credit_added + ($type === 'topup' ? $amount : 0)),
                    'total_debited' => self::money((float) $account->total_debited + ($type === 'booking_debit' ? $amount : 0)),
                    'total_refunded' => self::money((float) $account->total_refunded + ($type === 'cancel_refund' ? $amount : 0)),
                ])->save();

                if ($account->isLow()) {
                    Log::warning('supplier_credit_low_balance', [
                        'supplier_code' => $account->supplier_code,
                        'environment' => $account->environment,
                        'balance' => (float) $account->balance,
                        'threshold' => (float) $account->low_balance_threshold,
                        'currency' => $account->currency_code,
                    ]);
                }

                return $txn;
            });
        } catch (QueryException $e) {
            // Concurrent request inserted the same idempotency key first.
            $dupe = SupplierCreditTransaction::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($dupe) {
                return $dupe;
            }
            throw $e;
        }
    }

    /**
     * Lock the account row; when TravClicks has not configured one yet, create it with
     * enforcement OFF so the transaction is still captured without blocking bookings.
     */
    protected function lockOrCreateAccount(string $code, string $environment, string $currency): ?SupplierCreditAccount
    {
        $account = SupplierCreditAccount::query()
            ->where('supplier_code', $code)
            ->where('environment', $environment)
            ->lockForUpdate()
            ->first();

        if ($account) {
            return $account;
        }

        $currency = preg_match('/^[A-Z]{3}$/', $currency) ? $currency : 'SGD';

        try {
            DB::statement('SAVEPOINT sca_autocreate');
            $account = new SupplierCreditAccount;
            $account->forceFill([
                'supplier_code' => $code,
                'environment' => $environment,
                'currency_code' => $currency,
                'enforce_limit' => false,
                'status' => 'active',
                'auto_created' => true,
                'notes' => 'Auto-created on first vendor transaction. Set the real credit and enable the limit.',
            ])->save();
            DB::statement('RELEASE SAVEPOINT sca_autocreate');

            Log::warning('supplier_credit_account_auto_created', [
                'supplier_code' => $code,
                'environment' => $environment,
                'currency' => $currency,
            ]);
        } catch (QueryException) {
            DB::statement('ROLLBACK TO SAVEPOINT sca_autocreate');
        }

        return SupplierCreditAccount::query()
            ->where('supplier_code', $code)
            ->where('environment', $environment)
            ->lockForUpdate()
            ->first();
    }
}
