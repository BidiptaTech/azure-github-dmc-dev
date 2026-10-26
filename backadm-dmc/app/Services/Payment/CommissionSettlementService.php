<?php

namespace App\Services\Payment;

use App\Models\Tour;
use App\Services\Finance\PaymentGatewayService;
use App\Services\Finance\WalletLedgerService;
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
        $currency = strtoupper((string) ($attempt['currency_code'] ?? $breakdown['currency']));
        $method = (string) ($attempt['payment_method'] ?? '');
        $stage = (string) ($attempt['payment_stage'] ?? '');
        $tourId = (int) ($tour->tour_id ?? $tour->getKey());
        $dmcId = (int) ($tour->dmc_id ?? 0);
        $ref = (string) ($attempt['wallet_reservation_ref'] ?? ('COM-'.$tourId));

        $transferId = null;
        $transferStatus = null;
        if ($method === 'stripe' && $stage === 'supplier_initial' && $breakdown['dmc_commission'] > 0 && $dmcId > 0) {
            [$transferId, $transferStatus] = $this->transferDmcCommission($tour, $breakdown['dmc_commission'], $currency, $attemptUuid);
        }

        if ($method === 'wallet') {
            $this->creditWallets($tour, $breakdown, $currency, $ref);
            $transferStatus = $transferStatus ?? 'wallet_credit';
        }

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
                'currency_code' => $currency,
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
                'metadata' => json_encode([
                    'payment_stage' => $stage,
                    'payment_recipient' => $attempt['payment_recipient'] ?? null,
                ]),
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
                'currency_code' => $currency,
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
                'metadata' => json_encode(['fx_snapshot_id' => $attempt['fx_snapshot_id'] ?? null]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'recorded' => true,
            'dmc_commission' => $breakdown['dmc_commission'],
            'travclicks_commission' => $breakdown['travclicks_commission'],
            'api_cost' => $breakdown['api_cost'],
            'charged_amount' => $breakdown['charged_amount'],
            'conversion_charge' => $conversion,
            'stripe_transfer_id' => $transferId,
            'transfer_status' => $transferStatus,
        ];
    }

    /**
     * @param  array<string, mixed>  $breakdown
     */
    protected function creditWallets(Tour $tour, array $breakdown, string $currency, string $financeRef): void
    {
        $tourId = (int) ($tour->tour_id ?? $tour->getKey());
        $dmcId = (int) ($tour->dmc_id ?? 0);

        if ($breakdown['dmc_commission'] > 0 && $dmcId > 0 && ! $this->ledger->tourCreditExists($tourId, 'dmc', $dmcId)) {
            $this->ledger->creditCommission([
                'wallet_owner_type' => 'dmc',
                'wallet_owner_id' => $dmcId,
                'wallet_currency_code' => $currency,
                'amount' => $breakdown['dmc_commission'],
                'finance_reference' => $financeRef,
                'tour_id' => $tourId,
                'description' => "DMC commission credit · Tour #{$tourId}",
            ]);
        }

        if ($breakdown['travclicks_commission'] > 0 && ! $this->ledger->tourCreditExists($tourId, 'travclicks', 1)) {
            $this->ledger->creditCommission([
                'wallet_owner_type' => 'travclicks',
                'wallet_owner_id' => 1,
                'wallet_currency_code' => $currency,
                'amount' => $breakdown['travclicks_commission'],
                'finance_reference' => $financeRef,
                'tour_id' => $tourId,
                'description' => "Travclicks online commission · Tour #{$tourId}",
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
