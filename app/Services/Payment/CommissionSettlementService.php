<?php

namespace App\Services\Payment;

use App\Models\Country;
use App\Models\Tour;
use App\Services\Finance\PaymentGatewayService;
use App\Services\Finance\WalletLedgerService;
use App\Services\Finance\WalletQueryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * After a collected payment:
 * - Agent wallet debit is already the full agent price (done by the orchestrator).
 * - DMC wallet is credited only the DMC commission.
 * - Travclicks wallet is credited only Travclicks' online commission.
 * - Card payments collected by Travclicks transfer the DMC commission to the DMC Stripe account.
 */
class CommissionSettlementService
{
    public function __construct(
        protected CommissionBreakdownService $breakdownService,
        protected WalletLedgerService $ledger,
        protected PaymentGatewayService $paymentGatewayService,
        protected StripeConnectService $stripeConnectService,
        protected WalletQueryService $walletQuery,
    ) {}

    /**
     * @param  array<string, mixed>  $attempt
     * @param  array<int, array<string, mixed>>  $supplierResults
     * @return array<string, mixed>
     */
    public function record(Tour $tour, array $attempt, array $supplierResults = []): array
    {
        if (! Schema::hasTable('order_commission_lines')) {
            return ['recorded' => false, 'reason' => 'commission table missing'];
        }

        $attemptUuid = (string) ($attempt['attempt_uuid'] ?? '');
        if ($attemptUuid !== '' && DB::table('order_commission_lines')->where('payment_attempt_uuid', $attemptUuid)->exists()) {
            return ['recorded' => true, 'idempotent_replay' => true];
        }

        $breakdown = $this->breakdownService->forTour($tour, $supplierResults);
        $conversion = $this->conversionCharge($attempt);
        $breakdown['conversion_charge'] = $conversion;

        $attemptMeta = $this->attemptMeta($attempt);
        // Commission figures stay in order currency; wallet debit may be a different currency (e.g. IDR).
        $orderCurrency = strtoupper((string) (
            $attemptMeta['order_currency']
            ?? $breakdown['currency']
            ?? 'SGD'
        ));
        $walletCurrency = strtoupper((string) ($attempt['currency_code'] ?? $orderCurrency));
        $walletCharged = round((float) (
            $attempt['authorized_amount']
            ?? $attempt['requested_amount']
            ?? $attemptMeta['wallet_payable_amount']
            ?? 0
        ), 4);
        if ($walletCharged <= 0 && Schema::hasTable('currency_conversion_snapshots')) {
            $snapId = (int) ($attempt['fx_snapshot_id'] ?? 0);
            if ($snapId > 0) {
                $walletCharged = round((float) DB::table('currency_conversion_snapshots')
                    ->where('id', $snapId)
                    ->value('destination_amount'), 4);
            }
        }
        if ($walletCharged <= 0) {
            $walletCharged = round((float) $breakdown['charged_amount'] + $conversion, 4);
        }

        $method = (string) ($attempt['payment_method'] ?? '');
        $stage = (string) ($attempt['payment_stage'] ?? '');
        $tourId = (int) ($tour->tour_id ?? $tour->getKey());
        $dmcId = (int) ($tour->dmc_id ?? 0);
        $ref = (string) ($attempt['wallet_reservation_ref'] ?? ('COM-'.$tourId));

        $transferId = null;
        $transferStatus = null;
        if ($method === 'stripe' && $stage === 'supplier_initial' && $breakdown['dmc_commission'] > 0 && $dmcId > 0) {
            [$transferId, $transferStatus] = $this->transferDmcCommission($tour, $breakdown['dmc_commission'], $orderCurrency, $attemptUuid);
        }

        if ($method === 'wallet') {
            // Commission credit uses DMC home wallet currency (not payment debit currency).
            $dmcHomeCurrency = $dmcId > 0
                ? $this->walletQuery->resolveDmcWalletCurrency($dmcId)
                : $walletCurrency;
            $this->creditWallets($tour, $breakdown, $orderCurrency, $dmcHomeCurrency, $ref);
            $transferStatus = $transferStatus ?? 'wallet_credit';
        }

        $lineMetaBase = [
            'payment_stage' => $stage,
            'payment_recipient' => $attempt['payment_recipient'] ?? null,
            'order_currency' => $orderCurrency,
            'wallet_currency' => $walletCurrency,
            'wallet_charged_amount' => $walletCharged,
            'paid_currency' => $walletCurrency,
            'paid_amount' => $walletCharged,
        ];

        foreach ($breakdown['lines'] as $line) {
            DB::table('order_commission_lines')->insert([
                'line_uuid' => (string) Str::uuid(),
                'tour_id' => $tourId,
                'order_id' => $line['order_id'] ?? null,
                'payment_attempt_uuid' => $attemptUuid !== '' ? $attemptUuid : null,
                'payment_method' => $method,
                'channel' => $line['channel'],
                'supplier_api' => $line['supplier_api'] ?? null,
                'item_label' => $line['item_label'] ?? null,
                'currency_code' => $orderCurrency,
                'api_cost' => $line['api_cost'],
                'travclicks_commission' => $line['travclicks_commission'],
                'dmc_visible_price' => $line['dmc_visible_price'],
                'dmc_commission' => $line['dmc_commission'],
                'agent_price' => $line['agent_price'],
                'charged_amount' => $line['charged_amount'],
                'conversion_charge' => 0,
                'agent_entry' => 'debit',
                'dmc_entry' => $line['dmc_commission'] > 0 ? 'credit' : 'none',
                'stripe_transfer_id' => $transferId,
                'transfer_status' => $transferStatus,
                'metadata' => json_encode($lineMetaBase),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($conversion > 0 && $breakdown['lines'] !== []) {
            DB::table('order_commission_lines')->insert([
                'line_uuid' => (string) Str::uuid(),
                'tour_id' => $tourId,
                'order_id' => null,
                'payment_attempt_uuid' => $attemptUuid !== '' ? $attemptUuid : null,
                'payment_method' => $method,
                'channel' => 'fx',
                'supplier_api' => null,
                'item_label' => 'Currency conversion charge',
                'currency_code' => $walletCurrency,
                'api_cost' => null,
                'travclicks_commission' => 0,
                'dmc_visible_price' => 0,
                'dmc_commission' => 0,
                'agent_price' => $conversion,
                'charged_amount' => $conversion,
                'conversion_charge' => $conversion,
                'agent_entry' => 'debit',
                'dmc_entry' => 'none',
                'stripe_transfer_id' => null,
                'transfer_status' => null,
                'metadata' => json_encode(array_merge($lineMetaBase, [
                    'fx_snapshot_id' => $attempt['fx_snapshot_id'] ?? null,
                ])),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'recorded' => true,
            'dmc_commission' => $breakdown['dmc_commission'],
            'travclicks_commission' => $breakdown['travclicks_commission'],
            'api_cost' => $breakdown['api_cost'],
            'charged_amount' => $walletCharged > 0 ? $walletCharged : $breakdown['charged_amount'],
            'charged_currency' => $walletCurrency,
            'order_currency' => $orderCurrency,
            'conversion_charge' => $conversion,
            'stripe_transfer_id' => $transferId,
            'transfer_status' => $transferStatus,
        ];
    }

    /**
     * @param  array<string, mixed>  $attempt
     * @return array<string, mixed>
     */
    protected function attemptMeta(array $attempt): array
    {
        $meta = $attempt['metadata'] ?? [];
        if (is_string($meta)) {
            $meta = json_decode($meta, true) ?: [];
        }

        return is_array($meta) ? $meta : [];
    }

    /**
     * @param  array<string, mixed>  $breakdown
     */
    /**
     * Credit remaining DMC commission into the DMC's home wallet currency
     * (Indonesia → IDR, Singapore → SGD), converting via countries.current_rate.
     *
     * @param  array<string, mixed>  $breakdown
     */
    protected function creditWallets(
        Tour $tour,
        array $breakdown,
        string $orderCurrency,
        string $dmcHomeCurrency,
        string $financeRef
    ): void {
        $tourId = (int) ($tour->tour_id ?? $tour->getKey());
        $dmcId = (int) ($tour->dmc_id ?? 0);
        $orderCurrency = strtoupper($orderCurrency);
        $dmcHomeCurrency = strtoupper($dmcHomeCurrency !== '' ? $dmcHomeCurrency : $orderCurrency);

        // Online approve may already have credited DMC markup — only top up the remainder.
        // Travclicks/admin commission stays on Commission statement (order_commission_lines), not wallet.
        $alreadyDmc = $dmcId > 0
            ? $this->ledger->sumTourCommissionCredits($tourId, 'dmc', $dmcId, 'online_markup_commission')
            : 0.0;

        // alreadyDmc is in DMC home currency when online_approve converted; due starts in order currency.
        $dmcDueOrder = round((float) $breakdown['dmc_commission'], 4);
        if ($alreadyDmc > 0) {
            $alreadyInOrder = $orderCurrency === $dmcHomeCurrency
                ? $alreadyDmc
                : (Country::convertAmount($alreadyDmc, $dmcHomeCurrency, $orderCurrency) ?? $alreadyDmc);
            $dmcDueOrder = round(max(0, $dmcDueOrder - (float) $alreadyInOrder), 4);
        }

        if ($dmcDueOrder > 0 && $dmcId > 0) {
            $legacyFull = $alreadyDmc <= 0 && $this->ledger->tourCreditExists($tourId, 'dmc', $dmcId);
            if (! $legacyFull) {
                $creditAmount = $dmcDueOrder;
                $creditCurrency = $dmcHomeCurrency;
                if ($dmcHomeCurrency !== $orderCurrency) {
                    $converted = Country::convertAmount($dmcDueOrder, $orderCurrency, $dmcHomeCurrency);
                    if ($converted !== null && $converted > 0) {
                        $creditAmount = round((float) $converted, 4);
                    } else {
                        // Keep credit in order currency only if FX rate is missing
                        $creditCurrency = $orderCurrency;
                    }
                }

                $this->ledger->creditCommission([
                    'wallet_owner_type' => 'dmc',
                    'wallet_owner_id' => $dmcId,
                    'wallet_currency_code' => $creditCurrency,
                    'amount' => $creditAmount,
                    'finance_reference' => $financeRef.($alreadyDmc > 0 ? '-DMC-BAL' : ''),
                    'tour_id' => $tourId,
                    'description' => ($alreadyDmc > 0 ? 'DMC commission balance' : 'DMC commission credit')." · Tour #{$tourId}",
                    'metadata' => [
                        'purpose' => $alreadyDmc > 0 ? 'commission_balance' : 'commission',
                        'already_online_markup' => $alreadyDmc,
                        'source_amount' => $dmcDueOrder,
                        'source_currency' => $orderCurrency,
                        'credited_amount' => $creditAmount,
                        'credited_currency' => $creditCurrency,
                        'dmc_home_currency' => $dmcHomeCurrency,
                        'rate_source' => 'countries.current_rate',
                    ],
                ]);

                $this->stampWalletCreditOnLines($tourId, $creditAmount, $creditCurrency, $dmcDueOrder, $orderCurrency);
            }
        }
    }

    /**
     * Persist converted wallet credit on commission statement lines so UI never shows SGD in an IDR wallet.
     */
    protected function stampWalletCreditOnLines(
        int $tourId,
        float $creditedAmount,
        string $creditedCurrency,
        float $sourceAmount,
        string $sourceCurrency
    ): void {
        if ($tourId <= 0 || $creditedAmount <= 0 || ! Schema::hasTable('order_commission_lines')) {
            return;
        }

        $rows = DB::table('order_commission_lines')
            ->where('tour_id', $tourId)
            ->where(function ($q) {
                $q->where('payment_method', 'online_approve')
                    ->orWhere(function ($q2) {
                        $q2->where('payment_method', 'wallet')
                            ->where('channel', '!=', 'fx')
                            ->where('dmc_commission', '>', 0);
                    });
            })
            ->get(['id', 'metadata']);

        foreach ($rows as $row) {
            $meta = is_string($row->metadata ?? null)
                ? (json_decode((string) $row->metadata, true) ?: [])
                : (is_array($row->metadata ?? null) ? $row->metadata : []);
            if (! is_array($meta)) {
                $meta = [];
            }
            if (isset($meta['wallet_credited_amount']) && (float) $meta['wallet_credited_amount'] > 0) {
                continue;
            }
            $meta = array_merge($meta, [
                'wallet_credited_amount' => $creditedAmount,
                'wallet_credited_currency' => strtoupper($creditedCurrency),
                'wallet_source_amount' => $sourceAmount,
                'wallet_source_currency' => strtoupper($sourceCurrency),
                'dmc_destination' => 'wallet',
                'rate_source' => 'countries.current_rate',
            ]);
            DB::table('order_commission_lines')->where('id', $row->id)->update([
                'metadata' => json_encode($meta),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @return array{0: ?string, 1: string}
     */
    protected function transferDmcCommission(Tour $tour, float $amount, string $currency, string $attemptUuid): array
    {
        $dmcId = (int) ($tour->dmc_id ?? 0);
        if (! $this->stripeConnectService->isReadyForCharges($dmcId)) {
            return [null, 'pending_connect'];
        }

        $account = $this->stripeConnectService->getAccountForDmc($dmcId);
        $destination = (string) ($account['stripe_account_id'] ?? '');
        if ($destination === '') {
            return [null, 'pending_connect'];
        }

        try {
            $transfer = $this->paymentGatewayService->transferToConnectedAccount(
                $destination,
                $amount,
                $currency,
                [
                    'tour_id' => (string) ($tour->tour_id ?? $tour->getKey()),
                    'purpose' => 'dmc_online_commission',
                ],
                'dmc-commission-'.$attemptUuid,
            );

            return [(string) ($transfer['id'] ?? ''), 'transferred'];
        } catch (\Throwable $e) {
            Log::warning('dmc_commission_stripe_transfer_failed', [
                'tour_id' => $tour->tour_id ?? null,
                'error' => $e->getMessage(),
            ]);

            return [null, 'transfer_failed'];
        }
    }

    /**
     * @param  array<string, mixed>  $attempt
     */
    protected function conversionCharge(array $attempt): float
    {
        $snapshotId = (int) ($attempt['fx_snapshot_id'] ?? 0);
        if ($snapshotId <= 0 || ! Schema::hasTable('currency_conversion_snapshots')) {
            return 0.0;
        }

        $fee = DB::table('currency_conversion_snapshots')->where('id', $snapshotId)->value('fee_or_spread');

        return round((float) $fee, 4);
    }
}
