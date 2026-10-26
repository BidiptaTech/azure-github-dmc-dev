<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Laravel-native wallet ledger, payments, and settlements.
 * Replaces the former Python Azure Functions finance microservice.
 */
class WalletLedgerService
{
    public function generateFinanceReference(string $prefix = 'FIN'): string
    {
        $seq = null;
        try {
            if (DB::getDriverName() === 'pgsql') {
                $seq = DB::selectOne("SELECT nextval('finance_reference_seq') AS n");
            }
        } catch (\Throwable) {
            $seq = null;
        }

        $n = $seq->n ?? (time() % 100000000);
        $date = now()->format('Ymd');

        return sprintf('%s-%s-%d', $prefix, $date, $n);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function topup(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $amount = $this->money($data['topup_amount'] ?? 0);
            if ($amount <= 0) {
                throw new RuntimeException('topup_amount must be positive');
            }

            $wallet = $this->getOrCreateWallet(
                (string) $data['wallet_owner_type'],
                (int) $data['wallet_owner_id'],
                strtoupper((string) ($data['wallet_currency_code'] ?? 'USD')),
                $data['supplier_api_id'] ?? null,
                isset($data['source_dmc_id']) ? (int) $data['source_dmc_id'] : null,
            );

            $ref = $this->generateFinanceReference('TOP');
            $ledger = $this->creditWallet(
                $wallet,
                $amount,
                $ref,
                'topup',
                (string) ($data['ledger_description'] ?? 'Wallet top-up'),
                metadata: $data['metadata'] ?? null,
            );

            return $this->ok([
                'finance_reference' => $ref,
                'wallet_uuid' => $wallet->wallet_uuid,
                'available_balance' => (string) ((float) $wallet->available_balance + $amount),
                'ledger_uuid' => $ledger->ledger_uuid,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function allocate(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $allocatorCurrency = strtoupper((string) ($data['allocator_currency_code'] ?? $data['wallet_currency_code'] ?? 'USD'));
            $recipientCurrency = strtoupper((string) ($data['recipient_currency_code'] ?? $data['wallet_currency_code'] ?? 'USD'));
            $debitAmount = $this->money($data['allocator_amount'] ?? $data['allocation_amount'] ?? 0);
            $creditAmount = $this->money($data['recipient_amount'] ?? $data['allocation_amount'] ?? 0);

            if ($debitAmount <= 0 || $creditAmount <= 0) {
                throw new RuntimeException('allocation amounts must be positive');
            }

            $allocator = $this->getOrCreateWallet(
                (string) $data['allocator_wallet_owner_type'],
                (int) $data['allocator_wallet_owner_id'],
                $allocatorCurrency,
                $data['supplier_api_id'] ?? null,
            );

            $recipientSourceDmc = null;
            if (
                ($data['recipient_wallet_owner_type'] ?? '') === 'agent'
                && ($data['allocator_wallet_owner_type'] ?? '') === 'dmc'
            ) {
                $recipientSourceDmc = (int) $data['allocator_wallet_owner_id'];
            }

            $recipient = $this->getOrCreateWallet(
                (string) $data['recipient_wallet_owner_type'],
                (int) $data['recipient_wallet_owner_id'],
                $recipientCurrency,
                $data['supplier_api_id'] ?? null,
                $recipientSourceDmc,
            );

            $ref = $this->generateFinanceReference('ALLOC');
            $meta = array_merge($data['metadata'] ?? [], [
                'fx' => [
                    'allocator_currency' => $allocatorCurrency,
                    'recipient_currency' => $recipientCurrency,
                    'allocator_amount' => (string) $debitAmount,
                    'recipient_amount' => (string) $creditAmount,
                    'exchange_rate' => isset($data['exchange_rate']) ? (string) $data['exchange_rate'] : null,
                ],
            ]);

            // Travclicks platform funding: no incoming balance — record spend and credit DMC
            // without requiring available funds on the Travclicks wallet.
            $platformSpend = (bool) ($data['platform_spend'] ?? false)
                || (($data['allocator_wallet_owner_type'] ?? '') === 'travclicks');

            $debitLedger = $this->debitWallet(
                $allocator,
                $debitAmount,
                $ref,
                'allocation_debit',
                (string) ($data['debit_ledger_description'] ?? 'Wallet allocation debit'),
                metadata: array_merge($meta, [
                    'side' => 'debit',
                    'platform_spend' => $platformSpend,
                ]),
                allowOverdraft: $platformSpend,
            );
            $creditLedger = $this->creditWallet(
                $recipient,
                $creditAmount,
                $ref,
                'allocation_credit',
                (string) ($data['credit_ledger_description'] ?? 'Wallet allocation credit'),
                metadata: array_merge($meta, ['side' => 'credit']),
            );

            return $this->ok([
                'finance_reference' => $ref,
                'allocator_wallet_uuid' => $allocator->wallet_uuid,
                'recipient_wallet_uuid' => $recipient->wallet_uuid,
                'allocator_currency_code' => $allocatorCurrency,
                'recipient_currency_code' => $recipientCurrency,
                'allocator_amount' => (string) $debitAmount,
                'recipient_amount' => (string) $creditAmount,
                'exchange_rate' => isset($data['exchange_rate']) ? (string) $data['exchange_rate'] : null,
                'debit_ledger_uuid' => $debitLedger->ledger_uuid,
                'credit_ledger_uuid' => $creditLedger->ledger_uuid,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function reserve(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $amount = $this->money($data['amount'] ?? 0);
            if ($amount <= 0) {
                throw new RuntimeException('reserve amount must be positive');
            }

            $currency = strtoupper((string) ($data['wallet_currency_code'] ?? 'USD'));
            $ownerType = (string) $data['wallet_owner_type'];
            $ownerId = (int) $data['wallet_owner_id'];
            $sourceDmcId = isset($data['source_dmc_id']) ? (int) $data['source_dmc_id'] : null;

            if ($ownerType === 'agent' && $sourceDmcId) {
                $wallet = $this->findAgentWalletForDmc(
                    $ownerId,
                    $currency,
                    $sourceDmcId,
                    (bool) ($data['allow_legacy_unscoped'] ?? true),
                );
            } else {
                $wallet = $this->getOrCreateWallet(
                    $ownerType,
                    $ownerId,
                    $currency,
                    $data['supplier_api_id'] ?? null,
                    $sourceDmcId,
                );
            }

            if ((float) $wallet->available_balance < $amount) {
                throw new RuntimeException(
                    "Insufficient wallet balance: available={$wallet->available_balance}, required={$amount}",
                    402
                );
            }

            $ref = $this->generateFinanceReference('RSV');
            $available = (float) $wallet->available_balance;
            $reserved = (float) ($wallet->reserved_balance ?? 0);

            DB::table('wallet')->where('id', $wallet->id)->update([
                'available_balance' => $available - $amount,
                'reserved_balance' => $reserved + $amount,
                'updated_at' => now(),
            ]);

            $ledger = $this->insertLedger(
                $wallet->id,
                $ref,
                'reserve',
                $amount,
                $amount,
                0,
                $available,
                $available - $amount,
                $currency,
                (string) ($data['ledger_description'] ?? 'Wallet reservation'),
                $data['tour_id'] ?? null,
                metadata: array_merge($data['metadata'] ?? [], [
                    'reservation_reference' => $ref,
                    'source_dmc_id' => $sourceDmcId,
                ]),
            );

            return $this->ok([
                'finance_reference' => $ref,
                'reservation_reference' => $ref,
                'wallet_uuid' => $wallet->wallet_uuid,
                'available_balance' => (string) ($available - $amount),
                'reserved_balance' => (string) ($reserved + $amount),
                'ledger_uuid' => $ledger->ledger_uuid,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function commitReservation(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $reservationRef = (string) ($data['reservation_reference'] ?? '');
            $amount = $this->money($data['commit_amount'] ?? 0);
            if ($reservationRef === '' || $amount <= 0) {
                throw new RuntimeException('reservation_reference and commit_amount are required');
            }

            $wallet = $this->walletFromReservation($reservationRef);
            $reserved = (float) ($wallet->reserved_balance ?? 0);
            if ($reserved < $amount) {
                throw new RuntimeException("Insufficient reserved balance: reserved={$reserved}, required={$amount}", 402);
            }

            $ref = $this->generateFinanceReference('CMT');
            $available = (float) $wallet->available_balance;

            DB::table('wallet')->where('id', $wallet->id)->update([
                'reserved_balance' => $reserved - $amount,
                'total_debited' => (float) $wallet->total_debited + $amount,
                'updated_at' => now(),
            ]);

            $ledger = $this->insertLedger(
                $wallet->id,
                $ref,
                'commit',
                $amount,
                $amount,
                0,
                $available,
                $available,
                (string) $wallet->wallet_currency_code,
                (string) ($data['ledger_description'] ?? 'Wallet reservation commit'),
                $data['tour_id'] ?? null,
                metadata: array_merge($data['metadata'] ?? [], [
                    'reservation_reference' => $reservationRef,
                ]),
            );

            return $this->ok([
                'finance_reference' => $ref,
                'reservation_reference' => $reservationRef,
                'committed_amount' => (string) $amount,
                'reserved_balance' => (string) ($reserved - $amount),
                'ledger_uuid' => $ledger->ledger_uuid,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function releaseReservation(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $reservationRef = (string) ($data['reservation_reference'] ?? '');
            if ($reservationRef === '') {
                throw new RuntimeException('reservation_reference is required');
            }

            $wallet = $this->walletFromReservation($reservationRef);
            $reserved = (float) ($wallet->reserved_balance ?? 0);
            $releaseAmount = isset($data['release_amount'])
                ? $this->money($data['release_amount'])
                : $reserved;

            if ($releaseAmount <= 0) {
                return $this->ok([
                    'finance_reference' => $this->generateFinanceReference('REL'),
                    'reservation_reference' => $reservationRef,
                    'released_amount' => '0',
                    'reserved_balance' => (string) $reserved,
                ]);
            }

            if ($reserved < $releaseAmount) {
                throw new RuntimeException("Insufficient reserved balance: reserved={$reserved}, required={$releaseAmount}", 402);
            }

            $ref = $this->generateFinanceReference('REL');
            $available = (float) $wallet->available_balance;

            DB::table('wallet')->where('id', $wallet->id)->update([
                'available_balance' => $available + $releaseAmount,
                'reserved_balance' => $reserved - $releaseAmount,
                'updated_at' => now(),
            ]);

            $ledger = $this->insertLedger(
                $wallet->id,
                $ref,
                'release',
                $releaseAmount,
                0,
                $releaseAmount,
                $available,
                $available + $releaseAmount,
                (string) $wallet->wallet_currency_code,
                (string) ($data['ledger_description'] ?? 'Wallet reservation release'),
                $data['tour_id'] ?? null,
                metadata: ['reservation_reference' => $reservationRef],
            );

            return $this->ok([
                'finance_reference' => $ref,
                'reservation_reference' => $reservationRef,
                'released_amount' => (string) $releaseAmount,
                'available_balance' => (string) ($available + $releaseAmount),
                'reserved_balance' => (string) ($reserved - $releaseAmount),
                'ledger_uuid' => $ledger->ledger_uuid,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function bookWithWallet(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $idempotencyKey = (string) ($data['idempotency_key'] ?? Str::uuid());
            $existing = DB::table('payment_transactions')->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                $settlement = DB::table('settlements')->where('payment_transaction_id', $existing->id)->orderByDesc('id')->first();

                return $this->ok([
                    'finance_reference' => $existing->finance_reference,
                    'payment_transaction_uuid' => $existing->payment_transaction_uuid,
                    'settlement_uuid' => $settlement->settlement_uuid ?? null,
                    'settlement_status' => $settlement->settlement_status ?? 'completed',
                    'selling_price' => $existing->selling_price,
                    'supplier_cost' => $existing->supplier_cost,
                    'travclicks_markup' => $existing->travclicks_markup,
                    'dmc_commission' => $existing->dmc_commission,
                ]);
            }

            $selling = $this->money($data['selling_price'] ?? 0);
            $supplierCost = $this->money($data['supplier_cost'] ?? 0);
            $markup = $this->money($data['travclicks_markup'] ?? 0);
            $commission = $this->money($data['dmc_commission'] ?? 0);
            $currency = strtoupper((string) ($data['wallet_currency_code'] ?? 'USD'));
            $tourId = (int) $data['tour_id'];
            $sourceDmcId = isset($data['source_dmc_id'])
                ? (int) $data['source_dmc_id']
                : (isset($data['dmc_id']) ? (int) $data['dmc_id'] : null);

            $ownerType = (string) ($data['wallet_owner_type'] ?? 'agent');
            $ownerId = (int) ($data['wallet_owner_id'] ?? 0);

            if ($ownerType === 'agent' && $sourceDmcId) {
                $wallet = $this->findAgentWalletForDmc(
                    $ownerId,
                    $currency,
                    $sourceDmcId,
                    (bool) ($data['allow_legacy_unscoped'] ?? config('payment.wallet_legacy_unscoped_agent', true)),
                );
            } else {
                $wallet = $this->getOrCreateWallet($ownerType, $ownerId, $currency, null, $sourceDmcId);
            }

            $ref = $this->generateFinanceReference('FIN');
            $paymentId = DB::table('payment_transactions')->insertGetId([
                'payment_transaction_uuid' => (string) Str::uuid(),
                'finance_reference' => $ref,
                'tour_id' => $tourId,
                'payment_mode' => 'wallet',
                'payment_status' => 'captured',
                'supplier_api_id' => $data['supplier_api_id'] ?? 'offline',
                'supplier_booking_reference' => $data['supplier_booking_reference'] ?? null,
                'supplier_cost' => $supplierCost,
                'travclicks_markup' => $markup,
                'dmc_commission' => $commission,
                'selling_price' => $selling,
                'payment_received_amount' => $selling,
                'payment_received_currency' => $currency,
                'payment_received_datetime' => now(),
                'wallet_id' => $wallet->id,
                'idempotency_key' => $idempotencyKey,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->debitWallet(
                $wallet,
                $selling,
                $ref,
                'booking_debit',
                "Wallet booking debit for tour {$tourId}",
                tourId: $tourId,
                paymentTransactionId: $paymentId,
            );

            $settlementRef = 'SET-'.$ref;
            $settlementId = $this->createSettlement([
                'settlement_reference' => $settlementRef,
                'tour_id' => $tourId,
                'payment_transaction_id' => $paymentId,
                'settlement_type' => 'wallet_booking',
                'supplier_api_id' => $data['supplier_api_id'] ?? 'offline',
                'supplier_booking_reference' => $data['supplier_booking_reference'] ?? null,
                'supplier_cost' => $supplierCost,
                'travclicks_markup' => $markup,
                'dmc_commission' => $commission,
                'selling_price' => $selling,
                'wallet_debit_amount' => $selling,
                'idempotency_key' => $idempotencyKey.'-settlement',
            ]);

            $this->deductSupplierWallet(
                (string) ($data['supplier_api_id'] ?? 'offline'),
                $supplierCost,
                $settlementRef,
                $tourId,
                $settlementId,
                $currency,
            );

            $this->distributeProceeds(
                $paymentId,
                $settlementId,
                $markup,
                $commission,
                $currency,
                isset($data['dmc_id']) ? (int) $data['dmc_id'] : null,
                $tourId,
                $settlementRef,
            );

            $this->updateTourFinance($tourId, [
                'finance_reference' => $ref,
                'payment_transaction_id' => $paymentId,
                'settlement_id' => $settlementId,
                'payment_mode' => 'wallet',
                'payment_status' => 'captured',
                'supplier_cost' => $supplierCost,
                'travclicks_markup' => $markup,
                'dmc_commission' => $commission,
                'selling_price' => $selling,
                'supplier_api_id' => $data['supplier_api_id'] ?? 'offline',
                'finance_status' => 'settled',
                'payment_received_amount' => $selling,
                'payment_received_currency' => $currency,
            ]);

            $payment = DB::table('payment_transactions')->where('id', $paymentId)->first();
            $settlement = DB::table('settlements')->where('id', $settlementId)->first();

            return $this->ok([
                'finance_reference' => $ref,
                'payment_transaction_uuid' => $payment->payment_transaction_uuid,
                'settlement_uuid' => $settlement->settlement_uuid,
                'settlement_status' => $settlement->settlement_status,
                'selling_price' => $selling,
                'supplier_cost' => $supplierCost,
                'travclicks_markup' => $markup,
                'dmc_commission' => $commission,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function settleOffline(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $idempotencyKey = (string) ($data['idempotency_key'] ?? Str::uuid());
            $existing = DB::table('settlements')->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $this->ok([
                    'settlement_uuid' => $existing->settlement_uuid,
                    'settlement_reference' => $existing->settlement_reference,
                    'settlement_status' => $existing->settlement_status,
                ]);
            }

            $ref = $this->generateFinanceReference('OFF');
            $tourId = (int) $data['tour_id'];
            $currency = strtoupper((string) ($data['payment_received_currency'] ?? $data['wallet_currency_code'] ?? 'USD'));
            $selling = $this->money($data['selling_price'] ?? 0);
            $supplierCost = $this->money($data['supplier_cost'] ?? 0);
            $markup = $this->money($data['travclicks_markup'] ?? 0);
            $commission = $this->money($data['dmc_commission'] ?? 0);
            $received = $this->money($data['payment_received_amount'] ?? $selling);

            $paymentId = DB::table('payment_transactions')->insertGetId([
                'payment_transaction_uuid' => (string) Str::uuid(),
                'finance_reference' => $ref,
                'tour_id' => $tourId,
                'payment_mode' => 'offline_office',
                'payment_status' => 'captured',
                'supplier_api_id' => $data['supplier_api_id'] ?? 'offline',
                'supplier_booking_reference' => $data['supplier_booking_reference'] ?? null,
                'supplier_cost' => $supplierCost,
                'travclicks_markup' => $markup,
                'dmc_commission' => $commission,
                'selling_price' => $selling,
                'payment_received_amount' => $received,
                'payment_received_currency' => $currency,
                'payment_received_datetime' => now(),
                'idempotency_key' => $idempotencyKey,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $settlementRef = 'SET-'.$ref;
            $settlementId = $this->createSettlement([
                'settlement_reference' => $settlementRef,
                'tour_id' => $tourId,
                'payment_transaction_id' => $paymentId,
                'settlement_type' => 'offline_payment',
                'supplier_api_id' => $data['supplier_api_id'] ?? 'offline',
                'supplier_booking_reference' => $data['supplier_booking_reference'] ?? null,
                'supplier_cost' => $supplierCost,
                'travclicks_markup' => $markup,
                'dmc_commission' => $commission,
                'selling_price' => $selling,
                'wallet_credit_amount' => $received,
                'idempotency_key' => $idempotencyKey,
                'metadata' => [
                    'finance_confirmed_by' => $data['finance_confirmed_by'] ?? null,
                    'office_receipt_reference' => $data['office_receipt_reference'] ?? null,
                ],
            ]);

            $this->deductSupplierWallet(
                (string) ($data['supplier_api_id'] ?? 'offline'),
                $supplierCost,
                $settlementRef,
                $tourId,
                $settlementId,
                $currency,
            );

            $this->distributeProceeds(
                $paymentId,
                $settlementId,
                $markup,
                $commission,
                $currency,
                isset($data['dmc_id']) ? (int) $data['dmc_id'] : null,
                $tourId,
                $settlementRef,
            );

            $this->updateTourFinance($tourId, [
                'finance_reference' => $ref,
                'payment_transaction_id' => $paymentId,
                'settlement_id' => $settlementId,
                'payment_mode' => 'offline_office',
                'payment_status' => 'captured',
                'finance_status' => 'settled',
                'supplier_cost' => $supplierCost,
                'travclicks_markup' => $markup,
                'dmc_commission' => $commission,
                'selling_price' => $selling,
                'payment_received_amount' => $received,
                'payment_received_currency' => $currency,
            ]);

            $settlement = DB::table('settlements')->where('id', $settlementId)->first();

            return $this->ok([
                'settlement_uuid' => $settlement->settlement_uuid,
                'settlement_reference' => $settlement->settlement_reference,
                'settlement_status' => $settlement->settlement_status,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function settleOnline(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $idempotencyKey = (string) ($data['idempotency_key'] ?? Str::uuid());
            $existing = DB::table('settlements')->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $this->ok([
                    'settlement_uuid' => $existing->settlement_uuid,
                    'settlement_reference' => $existing->settlement_reference,
                    'settlement_status' => $existing->settlement_status,
                    'travclicks_profit' => $existing->travclicks_profit,
                    'dmc_profit' => $existing->dmc_profit,
                ]);
            }

            $paymentId = (int) ($data['payment_transaction_id'] ?? 0);
            $payment = DB::table('payment_transactions')->where('id', $paymentId)->lockForUpdate()->first();
            if (! $payment) {
                throw new RuntimeException('Payment transaction not found');
            }

            DB::table('payment_transactions')->where('id', $paymentId)->update([
                'payment_status' => 'captured',
                'gateway_transaction_reference' => $data['gateway_transaction_reference'] ?? $payment->gateway_transaction_reference,
                'payment_gateway_name' => $data['payment_gateway_name'] ?? 'stripe',
                'payment_received_amount' => $this->money($data['payment_received_amount'] ?? $payment->selling_price),
                'payment_received_currency' => strtoupper((string) ($data['payment_received_currency'] ?? 'USD')),
                'payment_received_datetime' => $data['payment_received_datetime'] ?? now(),
                'gateway_response_json' => isset($data['gateway_response_json'])
                    ? json_encode($data['gateway_response_json'])
                    : $payment->gateway_response_json,
                'updated_at' => now(),
            ]);

            $settlementRef = 'SET-'.($data['gateway_transaction_reference'] ?? $payment->finance_reference);
            $settlementId = $this->createSettlement([
                'settlement_reference' => $settlementRef,
                'tour_id' => (int) $data['tour_id'],
                'payment_transaction_id' => $paymentId,
                'settlement_type' => 'online_payment',
                'supplier_api_id' => $data['supplier_api_id'] ?? $payment->supplier_api_id,
                'supplier_booking_reference' => $data['supplier_booking_reference'] ?? null,
                'supplier_cost' => $this->money($data['supplier_cost'] ?? $payment->supplier_cost),
                'travclicks_markup' => $this->money($data['travclicks_markup'] ?? $payment->travclicks_markup),
                'dmc_commission' => $this->money($data['dmc_commission'] ?? $payment->dmc_commission),
                'selling_price' => $this->money($data['selling_price'] ?? $payment->selling_price),
                'wallet_credit_amount' => $this->money($data['payment_received_amount'] ?? $payment->selling_price),
                'idempotency_key' => $idempotencyKey,
            ]);

            $currency = strtoupper((string) ($data['payment_received_currency'] ?? 'USD'));
            $this->deductSupplierWallet(
                (string) ($data['supplier_api_id'] ?? $payment->supplier_api_id ?? 'offline'),
                $this->money($data['supplier_cost'] ?? $payment->supplier_cost),
                $settlementRef,
                (int) $data['tour_id'],
                $settlementId,
                $currency,
            );

            $this->distributeProceeds(
                $paymentId,
                $settlementId,
                $this->money($data['travclicks_markup'] ?? $payment->travclicks_markup),
                $this->money($data['dmc_commission'] ?? $payment->dmc_commission),
                $currency,
                isset($data['dmc_id']) ? (int) $data['dmc_id'] : null,
                (int) $data['tour_id'],
                $settlementRef,
            );

            $this->updateTourFinance((int) $data['tour_id'], [
                'settlement_id' => $settlementId,
                'finance_status' => 'settled',
                'payment_status' => 'captured',
                'payment_mode' => 'online_gateway',
                'gateway_transaction_reference' => $data['gateway_transaction_reference'] ?? null,
                'payment_gateway_name' => $data['payment_gateway_name'] ?? 'stripe',
                'payment_received_amount' => $this->money($data['payment_received_amount'] ?? $payment->selling_price),
                'payment_received_currency' => $currency,
            ]);

            $settlement = DB::table('settlements')->where('id', $settlementId)->first();

            return $this->ok([
                'settlement_uuid' => $settlement->settlement_uuid,
                'settlement_reference' => $settlement->settlement_reference,
                'settlement_status' => $settlement->settlement_status,
                'travclicks_profit' => $settlement->travclicks_profit,
                'dmc_profit' => $settlement->dmc_profit,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function calculateCommission(array $data): array
    {
        $supplierCost = $this->money($data['supplier_cost'] ?? 0);
        $markupPct = (float) ($data['travclicks_markup_percent'] ?? 0);
        $dmcPct = (float) ($data['dmc_commission_percent'] ?? 0);
        $agentPct = (float) ($data['agent_markup_percent'] ?? 0);

        $travclicksMarkup = round($supplierCost * $markupPct / 100, 4);
        $travclicksPrice = $supplierCost + $travclicksMarkup;
        $dmcCommission = round($travclicksPrice * $dmcPct / 100, 4);
        $agentMarkup = round($travclicksPrice * $agentPct / 100, 4);
        $sellingPrice = $travclicksPrice + $dmcCommission + $agentMarkup;

        return $this->ok([
            'supplier_cost' => $supplierCost,
            'travclicks_markup' => $travclicksMarkup,
            'travclicks_price' => $travclicksPrice,
            'dmc_commission' => $dmcCommission,
            'selling_price' => $sellingPrice,
            'travclicks_profit' => $travclicksMarkup,
            'dmc_profit' => $dmcCommission,
            'currency_code' => strtoupper((string) ($data['currency_code'] ?? 'USD')),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function refund(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $paymentId = (int) ($data['payment_transaction_id'] ?? 0);
            $payment = DB::table('payment_transactions')->where('id', $paymentId)->lockForUpdate()->first();
            if (! $payment) {
                throw new RuntimeException('Payment transaction not found');
            }
            if ($payment->payment_status === 'refunded') {
                throw new RuntimeException('Payment already fully refunded');
            }

            $refundAmount = $this->money($data['refund_amount'] ?? 0);
            if ($refundAmount <= 0 || $refundAmount > (float) $payment->selling_price) {
                throw new RuntimeException('Invalid refund amount');
            }

            $ref = $this->generateFinanceReference('REF');
            $settlementId = $this->createSettlement([
                'settlement_reference' => $ref,
                'tour_id' => (int) $data['tour_id'],
                'payment_transaction_id' => $paymentId,
                'settlement_type' => 'refund',
                'supplier_api_id' => $payment->supplier_api_id ?? 'offline',
                'supplier_cost' => 0,
                'travclicks_markup' => 0,
                'dmc_commission' => 0,
                'selling_price' => $refundAmount,
                'wallet_credit_amount' => $refundAmount,
                'idempotency_key' => (string) ($data['idempotency_key'] ?? Str::uuid()),
                'metadata' => ['refund_reason' => $data['refund_reason'] ?? null],
                'finance_status' => 'refunded',
            ]);

            if (($data['refund_type'] ?? 'wallet') === 'wallet'
                && ! empty($data['wallet_owner_type'])
                && ! empty($data['wallet_owner_id'])
            ) {
                $wallet = $this->getOrCreateWallet(
                    (string) $data['wallet_owner_type'],
                    (int) $data['wallet_owner_id'],
                    strtoupper((string) ($data['refund_currency_code'] ?? 'USD')),
                );
                $this->creditWallet(
                    $wallet,
                    $refundAmount,
                    $ref,
                    'refund_credit',
                    'Refund credit',
                    tourId: (int) $data['tour_id'],
                    paymentTransactionId: $paymentId,
                    settlementId: $settlementId,
                );
            }

            DB::table('payment_transactions')->where('id', $paymentId)->update([
                'payment_status' => 'refunded',
                'updated_at' => now(),
            ]);

            return $this->ok([
                'settlement_reference' => $ref,
                'refund_amount' => $refundAmount,
                'payment_status' => 'refunded',
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function distributeBookingProceeds(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $paymentId = (int) $data['payment_transaction_id'];
            $payment = DB::table('payment_transactions')->where('id', $paymentId)->first();
            if (! $payment) {
                throw new RuntimeException('Payment not found');
            }
            $settlement = DB::table('settlements')
                ->where('payment_transaction_id', $paymentId)
                ->orderByDesc('id')
                ->first();
            if (! $settlement) {
                throw new RuntimeException('Settlement not found');
            }

            $this->distributeProceeds(
                $paymentId,
                (int) $settlement->id,
                (float) $payment->travclicks_markup,
                (float) $payment->dmc_commission,
                (string) ($payment->payment_received_currency ?? 'USD'),
                isset($data['dmc_id']) ? (int) $data['dmc_id'] : null,
                (int) $payment->tour_id,
                (string) $settlement->settlement_reference,
            );

            return $this->ok([
                'settlement_reference' => $settlement->settlement_reference,
                'wallet_credit_amount' => (string) ((float) $payment->travclicks_markup + (float) $payment->dmc_commission),
            ]);
        });
    }

    public function healthCheck(): array
    {
        try {
            DB::select('SELECT 1');

            return ['status' => 'healthy', 'database' => 'connected', 'engine' => 'laravel'];
        } catch (\Throwable $e) {
            return ['status' => 'unhealthy', 'database' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Platform application fee in major currency units for Stripe destination charges.
     */
    public function applicationFeeAmount(float $chargeAmount, ?float $travclicksMarkup = null): float
    {
        if ($travclicksMarkup !== null && $travclicksMarkup > 0) {
            return round(min($travclicksMarkup, $chargeAmount), 4);
        }

        $percent = (float) config('payment.stripe.application_fee_percent', 0);
        if ($percent <= 0) {
            return 0.0;
        }

        return round($chargeAmount * $percent / 100, 4);
    }

    protected function getOrCreateWallet(
        string $ownerType,
        int $ownerId,
        string $currency,
        ?string $supplierApiId = null,
        ?int $sourceDmcId = null,
    ): object {
        $query = DB::table('wallet')
            ->where('wallet_owner_type', $ownerType)
            ->where('wallet_owner_id', $ownerId)
            ->where('wallet_currency_code', $currency)
            ->whereNull('deleted_at');

        if ($supplierApiId === null) {
            $query->whereNull('supplier_api_id');
        } else {
            $query->where('supplier_api_id', $supplierApiId);
        }

        if (Schema::hasColumn('wallet', 'source_dmc_id')) {
            if ($sourceDmcId === null) {
                $query->whereNull('source_dmc_id');
            } else {
                $query->where('source_dmc_id', $sourceDmcId);
            }
        }

        $wallet = $query->lockForUpdate()->first();
        if ($wallet) {
            return $wallet;
        }

        $payload = [
            'wallet_uuid' => (string) Str::uuid(),
            'wallet_owner_type' => $ownerType,
            'wallet_owner_id' => $ownerId,
            'supplier_api_id' => $supplierApiId,
            'wallet_currency_code' => $currency,
            'available_balance' => 0,
            'reserved_balance' => 0,
            'total_credited' => 0,
            'total_debited' => 0,
            'wallet_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('wallet', 'source_dmc_id')) {
            $payload['source_dmc_id'] = $sourceDmcId;
        }

        $id = DB::table('wallet')->insertGetId($payload);

        return DB::table('wallet')->where('id', $id)->lockForUpdate()->first();
    }

    protected function findAgentWalletForDmc(
        int $agentId,
        string $currency,
        int $sourceDmcId,
        bool $allowLegacyUnscoped,
    ): object {
        $scoped = DB::table('wallet')
            ->where('wallet_owner_type', 'agent')
            ->where('wallet_owner_id', $agentId)
            ->where('wallet_currency_code', $currency)
            ->whereNull('deleted_at')
            ->when(Schema::hasColumn('wallet', 'source_dmc_id'), fn ($q) => $q->where('source_dmc_id', $sourceDmcId))
            ->lockForUpdate()
            ->first();

        if ($scoped) {
            return $scoped;
        }

        if ($allowLegacyUnscoped && Schema::hasColumn('wallet', 'source_dmc_id')) {
            $legacy = DB::table('wallet')
                ->where('wallet_owner_type', 'agent')
                ->where('wallet_owner_id', $agentId)
                ->where('wallet_currency_code', $currency)
                ->whereNull('source_dmc_id')
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();
            if ($legacy) {
                return $legacy;
            }
        }

        throw new RuntimeException(
            "No DMC-scoped agent wallet for agent={$agentId} dmc={$sourceDmcId} currency={$currency}",
            402
        );
    }

    protected function walletFromReservation(string $reservationRef): object
    {
        $ledger = DB::table('wallet_ledger')
            ->where('finance_reference', $reservationRef)
            ->where('ledger_entry_type', 'reserve')
            ->first();

        if (! $ledger) {
            throw new RuntimeException("Reservation not found: {$reservationRef}");
        }

        $wallet = DB::table('wallet')->where('id', $ledger->wallet_id)->lockForUpdate()->first();
        if (! $wallet) {
            throw new RuntimeException("Wallet for reservation not found: {$reservationRef}");
        }

        return $wallet;
    }

    protected function creditWallet(
        object $wallet,
        float $amount,
        string $financeRef,
        string $entryType,
        string $description,
        ?int $tourId = null,
        ?int $paymentTransactionId = null,
        ?int $settlementId = null,
        ?array $metadata = null,
    ): object {
        $before = (float) $wallet->available_balance;
        $after = $before + $amount;

        DB::table('wallet')->where('id', $wallet->id)->update([
            'available_balance' => $after,
            'total_credited' => (float) $wallet->total_credited + $amount,
            'updated_at' => now(),
        ]);
        $wallet->available_balance = $after;

        return $this->insertLedger(
            $wallet->id,
            $financeRef,
            $entryType,
            $amount,
            0,
            $amount,
            $before,
            $after,
            (string) $wallet->wallet_currency_code,
            $description,
            $tourId,
            $paymentTransactionId,
            $settlementId,
            $metadata,
        );
    }

    protected function debitWallet(
        object $wallet,
        float $amount,
        string $financeRef,
        string $entryType,
        string $description,
        ?int $tourId = null,
        ?int $paymentTransactionId = null,
        ?int $settlementId = null,
        ?array $metadata = null,
        bool $allowOverdraft = false,
    ): object {
        $before = (float) $wallet->available_balance;
        if (! $allowOverdraft && $before < $amount) {
            throw new RuntimeException(
                "Insufficient wallet balance: available={$before}, required={$amount}",
                402
            );
        }

        // Platform spend (Travclicks→DMC): track total_debited only; keep available at 0.
        if ($allowOverdraft && ($wallet->wallet_owner_type ?? '') === 'travclicks') {
            $after = $before;
            DB::table('wallet')->where('id', $wallet->id)->update([
                'total_debited' => (float) $wallet->total_debited + $amount,
                'updated_at' => now(),
            ]);
        } else {
            $after = $before - $amount;
            DB::table('wallet')->where('id', $wallet->id)->update([
                'available_balance' => $after,
                'total_debited' => (float) $wallet->total_debited + $amount,
                'updated_at' => now(),
            ]);
            $wallet->available_balance = $after;
        }

        return $this->insertLedger(
            $wallet->id,
            $financeRef,
            $entryType,
            $amount,
            $amount,
            0,
            $before,
            $after,
            (string) $wallet->wallet_currency_code,
            $description,
            $tourId,
            $paymentTransactionId,
            $settlementId,
            $metadata,
        );
    }

    protected function insertLedger(
        int $walletId,
        string $financeRef,
        string $entryType,
        float $amount,
        float $debit,
        float $credit,
        float $balanceBefore,
        float $balanceAfter,
        string $currency,
        string $description,
        ?int $tourId = null,
        ?int $paymentTransactionId = null,
        ?int $settlementId = null,
        ?array $metadata = null,
    ): object {
        $payload = [
            'ledger_uuid' => (string) Str::uuid(),
            'wallet_id' => $walletId,
            'finance_reference' => $financeRef,
            'ledger_entry_type' => $entryType,
            'debit_amount' => $debit,
            'credit_amount' => $credit,
            'balance_after' => $balanceAfter,
            'tour_id' => $tourId,
            'payment_transaction_id' => $paymentTransactionId,
            'settlement_id' => $settlementId,
            'ledger_description' => $description,
            'ledger_metadata' => json_encode($metadata ?? []),
            'created_at' => now(),
        ];

        // Legacy compat columns used by earlier schema patches.
        if (Schema::hasColumn('wallet_ledger', 'entry_type')) {
            $payload['entry_type'] = $credit > 0 ? 'credit' : 'debit';
            $payload['amount'] = $amount;
            $payload['balance_before'] = $balanceBefore;
            $payload['currency_code'] = $currency;
            $payload['description'] = $description;
        }

        $id = DB::table('wallet_ledger')->insertGetId($payload);

        return DB::table('wallet_ledger')->where('id', $id)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function createSettlement(array $data): int
    {
        return DB::table('settlements')->insertGetId([
            'settlement_uuid' => (string) Str::uuid(),
            'settlement_reference' => $data['settlement_reference'],
            'tour_id' => $data['tour_id'],
            'payment_transaction_id' => $data['payment_transaction_id'],
            'settlement_type' => $data['settlement_type'],
            'settlement_status' => 'completed',
            'supplier_api_id' => $data['supplier_api_id'],
            'supplier_booking_reference' => $data['supplier_booking_reference'] ?? null,
            'supplier_cost' => $data['supplier_cost'],
            'travclicks_markup' => $data['travclicks_markup'],
            'travclicks_profit' => $data['travclicks_markup'],
            'dmc_commission' => $data['dmc_commission'],
            'dmc_profit' => $data['dmc_commission'],
            'selling_price' => $data['selling_price'],
            'wallet_debit_amount' => $data['wallet_debit_amount'] ?? 0,
            'wallet_credit_amount' => $data['wallet_credit_amount'] ?? 0,
            'finance_status' => $data['finance_status'] ?? 'settled',
            'settled_at' => now(),
            'settlement_metadata' => json_encode($data['metadata'] ?? []),
            'idempotency_key' => $data['idempotency_key'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function deductSupplierWallet(
        string $supplierApiId,
        float $supplierCost,
        string $financeRef,
        int $tourId,
        int $settlementId,
        string $currency,
    ): void {
        if ($supplierCost <= 0 || in_array($supplierApiId, ['offline', 'none', ''], true)) {
            return;
        }

        $wallet = $this->getOrCreateWallet('supplier', 0, $currency, $supplierApiId);
        try {
            $this->debitWallet(
                $wallet,
                $supplierCost,
                $financeRef,
                'booking_debit',
                "Supplier API deduction for {$supplierApiId}",
                tourId: $tourId,
                settlementId: $settlementId,
            );
        } catch (RuntimeException) {
            // Supplier prepaid wallet may be empty for offline-style suppliers; do not fail booking.
        }
    }

    protected function distributeProceeds(
        int $paymentId,
        int $settlementId,
        float $markup,
        float $commission,
        string $currency,
        ?int $dmcId,
        int $tourId,
        string $financeRef,
    ): void {
        if ($markup > 0 && ! $this->tourCreditExists($tourId, 'travclicks', 1)) {
            $travclicks = $this->getOrCreateWallet('travclicks', 1, $currency);
            if (! $this->bookingCreditExists($settlementId, 'travclicks', 1)) {
                $this->creditWallet(
                    $travclicks,
                    $markup,
                    $financeRef,
                    'booking_credit',
                    'Travclicks markup credit',
                    tourId: $tourId,
                    paymentTransactionId: $paymentId,
                    settlementId: $settlementId,
                );
            }
        }

        if ($commission > 0 && $dmcId && ! $this->tourCreditExists($tourId, 'dmc', $dmcId)) {
            $dmcWallet = $this->getOrCreateWallet('dmc', $dmcId, $currency);
            if (! $this->bookingCreditExists($settlementId, 'dmc', $dmcId)) {
                $this->creditWallet(
                    $dmcWallet,
                    $commission,
                    $financeRef,
                    'booking_credit',
                    'DMC commission credit',
                    tourId: $tourId,
                    paymentTransactionId: $paymentId,
                    settlementId: $settlementId,
                );
            }
        }
    }

    /**
     * Commission and markup are credited once per tour. A later settlement must
     * not credit them again (wallet debit already collected the selling price).
     */
    public function tourCreditExists(int $tourId, string $ownerType, int $ownerId): bool
    {
        if ($tourId <= 0 || ! Schema::hasTable('wallet_ledger')) {
            return false;
        }

        return DB::table('wallet_ledger as l')
            ->join('wallet as w', 'w.id', '=', 'l.wallet_id')
            ->where('l.tour_id', $tourId)
            ->where('l.ledger_entry_type', 'booking_credit')
            ->where('w.wallet_owner_type', $ownerType)
            ->where('w.wallet_owner_id', $ownerId)
            ->exists();
    }

    /**
     * Credit one owner's commission without debiting the payer again.
     *
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    public function creditCommission(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $amount = $this->money($data['amount'] ?? 0);
            if ($amount <= 0) {
                throw new RuntimeException('commission amount must be positive');
            }

            $wallet = $this->getOrCreateWallet(
                (string) $data['wallet_owner_type'],
                (int) $data['wallet_owner_id'],
                strtoupper((string) ($data['wallet_currency_code'] ?? 'USD')),
            );
            $ref = (string) ($data['finance_reference'] ?? $this->generateFinanceReference('COM'));
            $ledger = $this->creditWallet(
                $wallet,
                $amount,
                $ref,
                'booking_credit',
                (string) ($data['description'] ?? 'Commission credit'),
                tourId: isset($data['tour_id']) ? (int) $data['tour_id'] : null,
                metadata: [
                    'tour_id' => $data['tour_id'] ?? null,
                    'purpose' => 'commission',
                    'order_reference' => isset($data['tour_id']) ? 'Tour #'.$data['tour_id'] : null,
                ],
            );

            return $this->ok([
                'finance_reference' => $ref,
                'ledger_uuid' => $ledger->ledger_uuid,
                'amount' => (string) $amount,
            ]);
        });
    }

    public function tourHasBookingDebit(int $tourId): bool
    {
        if ($tourId <= 0 || ! Schema::hasTable('wallet_ledger')) {
            return false;
        }

        return DB::table('wallet_ledger')
            ->where('tour_id', $tourId)
            ->whereIn('ledger_entry_type', ['booking_debit', 'commit'])
            ->exists();
    }

    protected function bookingCreditExists(int $settlementId, string $ownerType, int $ownerId): bool
    {
        return DB::table('wallet_ledger as l')
            ->join('wallet as w', 'w.id', '=', 'l.wallet_id')
            ->where('l.settlement_id', $settlementId)
            ->where('l.ledger_entry_type', 'booking_credit')
            ->where('w.wallet_owner_type', $ownerType)
            ->where('w.wallet_owner_id', $ownerId)
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    protected function updateTourFinance(int $tourId, array $fields): void
    {
        // tours may use tour_id as business key
        $query = DB::table('tours')->where('tour_id', $tourId);
        if (! $query->exists()) {
            $query = DB::table('tours')->where('id', $tourId);
        }

        $fields['updated_at'] = now();
        $query->update($fields);
    }

    protected function money(mixed $value): float
    {
        return round((float) $value, 4);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, data: array<string, mixed>}
     */
    protected function ok(array $data): array
    {
        return ['success' => true, 'message' => 'OK', 'data' => $data];
    }
}
