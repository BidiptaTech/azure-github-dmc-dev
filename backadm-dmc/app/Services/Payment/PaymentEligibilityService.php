<?php

namespace App\Services\Payment;

use App\Helpers\CurrencyHelper;
use App\Helpers\TaxHelper;
use App\Models\Agent;
use App\Models\Enquiry;
use App\Models\Order;
use App\Models\Tour;
use App\Services\Finance\WalletQueryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Central server-side payment method eligibility for a tour + stage.
 * Frontend must render this response; backend re-enforces on initiate.
 */
class PaymentEligibilityService
{
    public function __construct(
        protected OrderClassificationService $classificationService,
        protected PaymentSettingsService $settingsService,
        protected StripeConnectService $stripeConnectService,
        protected WalletQueryService $walletQueryService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forTour(Tour $tour, ?float $requestedAmount = null): array
    {
        $classification = $this->classificationService->classifyTour($tour);
        if ($classification === OrderClassificationService::EMPTY || $classification === '') {
            $classification = (string) ($tour->order_classification ?: OrderClassificationService::EMPTY);
        }

        $stage = $this->classificationService->resolvePaymentStage($tour, $classification);
        $recipient = $this->classificationService->resolvePaymentRecipient($stage);
        $breakdown = $this->classificationService->amountBreakdown($tour);
        $settings = $this->settingsService->get();

        $collectionDue = $this->collectionOutstanding($tour, (float) $breakdown['total_amount']);
        $payable = $this->resolvePayableAmount($tour, $classification, $stage, $breakdown, $collectionDue);
        $negotiationOnly = $stage === 'offline_negotiation';
        if ($requestedAmount !== null && $stage === 'supplier_initial') {
            // Ignore client amount for supplier_initial — always server payable.
            $requestedAmount = $payable;
        }

        // Wallet / method checks use the amount being collected (form amount when provided).
        $amount = $requestedAmount !== null && $requestedAmount > 0
            ? (float) $requestedAmount
            : $payable;
        $currency = strtoupper((string) (
            $breakdown['currency']
            ?? $this->classificationService->resolveTourCurrency($tour)
        ));
        $dmcId = (int) ($tour->dmc_id ?? 0);
        $agentId = (int) ($tour->agent_id ?? 0);

        $methods = [];

        // Offline methods — always available when stage allows collection
        foreach ([
            ['code' => 'cash', 'label' => 'Cash'],
            ['code' => 'cheque', 'label' => 'Cheque'],
            ['code' => 'bank_transfer', 'label' => 'Bank Transfer', 'legacy_value' => 'online'],
        ] as $offline) {
            $allowed = $stage === 'dmc_collection' || $classification === OrderClassificationService::OFFLINE;
            // Online/Mix supplier_initial: offline methods not for initial supplier payment.
            // Mix offline negotiation is not a collection stage.
            if ($stage === 'supplier_initial' || $negotiationOnly) {
                $allowed = false;
            }
            $methods[] = [
                'code' => $offline['code'],
                'label' => $offline['label'],
                'legacy_value' => $offline['legacy_value'] ?? $offline['code'],
                'available' => $allowed,
                'reason' => $allowed ? null : 'Offline methods are available after supplier-initial payment / for offline collection.',
                'recipient' => 'dmc',
                'amount_readonly' => false,
            ];
        }

        // Legacy "card" offline recording (not Stripe)
        $methods[] = [
            'code' => 'card_offline',
            'label' => 'Card (offline record)',
            'legacy_value' => 'card',
            'available' => ! $negotiationOnly && ($stage === 'dmc_collection' || $classification === OrderClassificationService::OFFLINE),
            'reason' => $negotiationOnly
                ? 'Offline services are negotiated before collection.'
                : ($stage === 'supplier_initial'
                    ? 'Use Online card for supplier-initial payment.'
                    : null),
            'recipient' => 'dmc',
            'amount_readonly' => false,
        ];

        $fx = $this->fxQuote($tour, $amount, $currency, $agentId, $dmcId);
        $walletAmount = (float) ($fx['wallet_payable_amount'] ?? $amount);
        $walletCurrency = (string) ($fx['wallet_currency'] ?? $currency);

        // Wallet balance is checked in the wallet currency, including any conversion charge.
        $walletEval = $this->evaluateWallet($agentId, $dmcId, $walletAmount, $walletCurrency, $settings);
        if ($negotiationOnly) {
            $walletEval['available'] = false;
            $walletEval['reason'] = 'Pay online services first, then negotiate the offline total.';
        }
        $methods[] = [
            'code' => 'wallet',
            'label' => 'Wallet',
            'available' => $walletEval['available'],
            'reason' => $walletEval['reason'],
            'recipient' => $recipient,
            'amount_readonly' => $stage === 'supplier_initial',
            'wallet' => $walletEval,
        ];

        // Stripe Online card
        $stripeEval = $this->evaluateStripe($recipient, $dmcId, $settings);
        if ($negotiationOnly) {
            $stripeEval['available'] = false;
            $stripeEval['reason'] = 'Card payment for offline services starts after negotiation and confirmation.';
        }
        $methods[] = [
            'code' => 'stripe',
            'label' => 'Online Card',
            'available' => $stripeEval['available'],
            'reason' => $stripeEval['reason'],
            'recipient' => $recipient,
            'amount_readonly' => $stage === 'supplier_initial',
            'stripe' => $stripeEval,
        ];

        return [
            'tour_id' => (int) ($tour->tour_id ?? $tour->getKey()),
            'tour_status' => $tour->tour_status,
            'order_classification' => $classification,
            'payment_stage' => $stage,
            'payment_recipient' => $recipient,
            'currency' => $currency,
            'payable_amount' => round($payable, 4),
            'amount_readonly' => $stage === 'supplier_initial',
            'acceptance' => app(AcceptanceFlowService::class)->describe(
                $classification,
                $stage === 'offline_negotiation' || ($classification === OrderClassificationService::ONLINE && $stage !== 'supplier_initial'),
                (float) ($breakdown['online_amount'] ?? 0),
                (float) ($breakdown['offline_amount'] ?? 0),
                $currency,
            ),
            'fx' => $fx,
            'breakdown' => $breakdown,
            'outstanding_balance' => $stage === 'dmc_collection'
                ? $collectionDue
                : $this->outstandingBalance($tour, (float) $breakdown['total_amount']),
            'settings' => [
                'offline_enabled' => true,
                'wallet_enabled' => $settings['wallet_enabled'],
                'online_enabled' => $settings['online_enabled'],
                'stripe_mode' => $settings['stripe_mode'],
            ],
            'methods' => $methods,
        ];
    }

    /**
     * @param  array<string, mixed>  $breakdown
     */
    protected function resolvePayableAmount(Tour $tour, string $classification, string $stage, array $breakdown, float $collectionDue): float
    {
        if ($stage === 'offline_negotiation') {
            return 0.0;
        }

        if ($stage === 'supplier_initial') {
            if ($classification === OrderClassificationService::MIX) {
                return (float) $breakdown['online_amount'];
            }

            return (float) ($breakdown['total_amount'] > 0
                ? $breakdown['total_amount']
                : ($tour->selling_price ?? 0));
        }

        return $collectionDue;
    }

    /**
     * Tour markup is the DMC commission, shown only after the tour is fully paid.
     *
     * @return array{tour_id: int, currency_code: string, charged_amount: float, dmc_commission: float, markup_label: string, base_amount: float}|null
     */
    public function fullyPaidMarkup(Tour $tour): ?array
    {
        $breakdown = $this->classificationService->amountBreakdown($tour);
        $base = (float) ($breakdown['total_amount'] ?? 0);
        if ($this->hasPendingPayment($tour)) {
            return null;
        }

        $paid = $this->verifiedPaid($tour);
        $due = $this->collectionOutstanding($tour, $base);
        if ($paid <= 0.009 || $due > 0.05) {
            return null;
        }

        $markup = $this->markupCommission($tour, $base);
        if ($markup['amount'] <= 0) {
            return null;
        }

        return [
            'tour_id' => (int) ($tour->tour_id ?? $tour->getKey()),
            'currency_code' => (string) ($breakdown['currency'] ?? $this->classificationService->resolveTourCurrency($tour)),
            'charged_amount' => round($paid, 2),
            'dmc_commission' => $markup['amount'],
            'markup_label' => $markup['label'],
            'base_amount' => round($base, 2),
            'travclicks_commission' => 0.0,
            'api_cost' => 0.0,
            'conversion_charge' => 0.0,
        ];
    }

    /**
     * @return array{amount: float, label: string}
     */
    protected function markupCommission(Tour $tour, float $base): array
    {
        $fromRows = $this->markupFromCurrencyRows($tour);
        if ($fromRows['amount'] > 0) {
            return $fromRows;
        }

        $flag = $tour->getAttributes()['markup'] ?? 0;
        $raw = (float) ($tour->getAttributes()['markup_amount'] ?? 0);
        $type = strtolower((string) ($tour->markup_type ?? ''));
        $on = ((int) $flag === 1 || $flag === true) && $raw > 0;
        if (! $on) {
            return ['amount' => 0.0, 'label' => '—'];
        }

        if ($type === 'percentage') {
            return [
                'amount' => round($base * $raw / 100, 2),
                'label' => $this->percentLabel($raw),
            ];
        }

        return ['amount' => round($raw, 2), 'label' => 'Flat'];
    }

    /**
     * Hotel markup applies to hotel services. Other markup applies to the rest.
     *
     * @return array{amount: float, label: string}
     */
    protected function markupFromCurrencyRows(Tour $tour): array
    {
        $rows = $tour->currency_markups;
        if (! is_array($rows) || $rows === []) {
            return ['amount' => 0.0, 'label' => '—'];
        }

        $row = null;
        foreach ($rows as $candidate) {
            if (is_array($candidate)) {
                $row = $candidate;
                break;
            }
        }
        if ($row === null) {
            return ['amount' => 0.0, 'label' => '—'];
        }

        $type = strtolower((string) ($row['markup_type'] ?? ''));
        if ($type === '') {
            $type = strtolower((string) ($tour->markup_type ?? 'percentage'));
        }
        if ($type === '' || $type === 'percent') {
            $type = 'percentage';
        }
        if ($type === 'fixed') {
            $type = 'flat';
        }
        $hotelRate = (float) ($row['hotel_markup'] ?? $row['markup_value'] ?? 0);
        $otherRate = (float) ($row['other_markup'] ?? 0);
        if ($hotelRate <= 0 && $otherRate <= 0) {
            return ['amount' => 0.0, 'label' => '—'];
        }

        $tourId = (int) ($tour->tour_id ?? $tour->getKey());
        $hotelBase = 0.0;
        $otherBase = 0.0;
        if ($tourId > 0) {
            foreach (Order::query()->where('tour_id', $tourId)->get() as $order) {
                $price = $this->classificationService->resolveOrderAmount($order);
                if (strtolower((string) ($order->type ?? '')) === 'hotel') {
                    $hotelBase += $price;
                } else {
                    $otherBase += $price;
                }
            }
        }
        if ($type === 'percentage') {
            $amount = ($hotelBase * $hotelRate / 100) + ($otherBase * $otherRate / 100);
            $label = $hotelRate === $otherRate
                ? $this->percentLabel($hotelRate)
                : 'Hotel '.$this->percentLabel($hotelRate).' · Other '.$this->percentLabel($otherRate);
        } else {
            $amount = ($hotelBase > 0 ? $hotelRate : 0) + ($otherBase > 0 ? $otherRate : 0);
            $label = 'Flat';
        }

        return ['amount' => round(max(0, $amount), 2), 'label' => $label];
    }

    protected function percentLabel(float $rate): string
    {
        $text = rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');

        return ($text === '' ? '0' : $text).'%';
    }

    protected function verifiedPaid(Tour $tour): float
    {
        $paid = 0.0;
        foreach ($this->paymentRows($tour) as $payment) {
            if ((int) ($payment['status'] ?? 0) === 1) {
                $paid += (float) ($payment['amount'] ?? 0);
            }
        }

        return round($paid, 4);
    }

    protected function hasPendingPayment(Tour $tour): bool
    {
        foreach ($this->paymentRows($tour) as $payment) {
            if ((int) ($payment['status'] ?? 0) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function paymentRows(Tour $tour): array
    {
        $details = json_decode((string) ($tour->payment_details ?? '[]'), true) ?: [];

        return is_array($details) ? array_values(array_filter($details, 'is_array')) : [];
    }

    /**
     * Confirmed collection uses the negotiated amount plus tour tax, which is
     * the remaining figure shown on Add Payment.
     */
    protected function collectionOutstanding(Tour $tour, float $orderTotal): float
    {
        $base = $orderTotal;
        $tourId = (int) ($tour->tour_id ?? 0);
        if ($tourId > 0) {
            $negotiated = Enquiry::query()
                ->where('tour_id', $tourId)
                ->where('status', 2)
                ->orderByDesc('enquiry_id')
                ->value('amount');
            if (! is_numeric($negotiated) || (float) $negotiated <= 0) {
                $negotiated = Enquiry::query()
                    ->where('tour_id', $tourId)
                    ->orderByDesc('enquiry_id')
                    ->value('amount');
            }
            if (is_numeric($negotiated) && (float) $negotiated > 0) {
                $base = (float) ceil((float) $negotiated);
            }
        }

        $persons = (int) ($tour->adult ?? 0) + (int) ($tour->child ?? 0);
        $days = TaxHelper::calculateDays($tour->check_in_time ?? null, $tour->check_out_time ?? null);
        $tax = TaxHelper::calculateTourTaxes($base, $tour->taxes ?? null, $persons, $days);
        $final = $base + (float) ($tax['total_tax'] ?? 0);

        return $this->outstandingBalance($tour, $final);
    }

    /**
     * @return array<string, mixed>
     */
    protected function fxQuote(Tour $tour, float $amount, string $orderCurrency, int $agentId, int $dmcId): array
    {
        $orderCurrency = strtoupper($orderCurrency);
        $percent = 0.0;
        try {
            $percent = app(FxConversionCharge::class)->percentForTour($tour);
        } catch (\Throwable $e) {
            Log::debug('fx_charge_percent_unavailable', ['error' => $e->getMessage()]);
        }

        $walletCurrency = $orderCurrency;
        try {
            if ($agentId > 0 && Schema::hasTable('wallet')) {
                $query = DB::table('wallet')
                    ->where('wallet_owner_type', 'agent')
                    ->whereIn('wallet_owner_id', $this->agencyAgentIds($agentId))
                    ->whereNull('deleted_at');
                if (Schema::hasColumn('wallet', 'source_dmc_id') && $dmcId > 0) {
                    $query->where(function ($q) use ($dmcId) {
                        $q->where('source_dmc_id', $dmcId);
                        if (config('payment.wallet_legacy_unscoped_agent', true)) {
                            $q->orWhereNull('source_dmc_id');
                        }
                    });
                }
                $code = $query->orderByDesc('id')->value('wallet_currency_code');
                if (is_string($code) && $code !== '') {
                    $walletCurrency = CurrencyHelper::toIsoCode($code, $orderCurrency);
                }
            }
        } catch (\Throwable) {
            $walletCurrency = $orderCurrency;
        }

        $differ = $walletCurrency !== $orderCurrency;
        $rate = 1.0;
        if ($differ) {
            $rate = (float) (CurrencyHelper::getExchangeRate($orderCurrency, $walletCurrency) ?? 0);
            if ($rate <= 0) {
                $rate = 1.0;
                $differ = false;
            }
        }

        $quoted = app(FxConversionCharge::class)->apply($amount, $rate, $percent, $differ);

        return [
            'charge_percent' => $percent,
            'order_currency' => $orderCurrency,
            'wallet_currency' => $walletCurrency,
            'exchange_rate' => $quoted['exchange_rate'],
            'conversion_charge' => $quoted['fee'],
            'wallet_payable_amount' => $quoted['destination_amount'],
        ];
    }

    protected function outstandingBalance(Tour $tour, float $total): float
    {
        // Add Payment UI records collections in payment_details. Do not treat
        // finance payment_status=captured / selling_price alone as paid — that
        // zeros payable while Cheque/Bank Transfer stay selectable.
        $paid = 0.0;
        $details = json_decode((string) ($tour->payment_details ?? '[]'), true) ?: [];
        if (! is_array($details)) {
            $details = [];
        }

        foreach ($details as $p) {
            if (! is_array($p)) {
                continue;
            }
            if ((int) ($p['status'] ?? 0) === 2) {
                continue; // declined
            }
            $paid += (float) ($p['amount'] ?? 0);
        }

        return max(0, round($total - $paid, 4));
    }

    /**
     * @return array<string, mixed>
     */
    protected function evaluateWallet(int $agentId, int $dmcId, float $amount, string $currency, array $settings): array
    {
        if (! $settings['wallet_enabled']) {
            return ['available' => false, 'reason' => 'Wallet payments are disabled.', 'available_balance' => 0];
        }

        if ($agentId <= 0 || $dmcId <= 0) {
            return ['available' => false, 'reason' => 'Wallet requires an agent and DMC on the tour.', 'available_balance' => 0];
        }

        if (! Schema::hasTable('wallet')) {
            return ['available' => false, 'reason' => 'Wallet ledger is not available.', 'available_balance' => 0];
        }

        $currency = CurrencyHelper::toIsoCode($currency, 'SGD');
        $balance = $this->dmcScopedAgentBalance($agentId, $dmcId, $currency);

        if ($amount <= 0) {
            return [
                'available' => false,
                'reason' => 'No payable amount on this booking.',
                'available_balance' => round($balance, 4),
                'required_amount' => 0.0,
                'currency' => $currency,
                'source_dmc_id' => $dmcId,
            ];
        }

        $sufficient = $balance >= $amount;

        return [
            'available' => $sufficient,
            'reason' => $sufficient
                ? null
                : ($balance <= 0
                    ? "No {$currency} wallet balance for this agency under the tour DMC."
                    : "Insufficient wallet balance ({$currency} {$balance} available, {$amount} required)."),
            'available_balance' => round($balance, 4),
            'required_amount' => round($amount, 4),
            'currency' => $currency,
            'source_dmc_id' => $dmcId,
        ];
    }

    /**
     * DMC allocations are stored against one agent in the agency.
     * Any colleague on the same agency can spend that balance.
     *
     * @return array<int, int>
     */
    public function agencyAgentIds(int $agentId): array
    {
        if ($agentId <= 0) {
            return [];
        }

        $agencyId = Agent::query()->where('agent_id', $agentId)->value('agency_id');
        if (! $agencyId) {
            return [$agentId];
        }

        $ids = Agent::query()
            ->where('agency_id', $agencyId)
            ->pluck('agent_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $ids !== [] ? $ids : [$agentId];
    }

    /**
     * Owner id of the agency wallet this DMC funded. Falls back to the tour agent.
     */
    public function resolveAgencyWalletOwnerId(int $agentId, int $dmcId, string $currency): int
    {
        $currency = CurrencyHelper::toIsoCode($currency, strtoupper($currency));
        $agentIds = $this->agencyAgentIds($agentId);
        if ($agentIds === []) {
            return $agentId;
        }

        $query = DB::table('wallet')
            ->where('wallet_owner_type', 'agent')
            ->whereIn('wallet_owner_id', $agentIds)
            ->whereRaw('UPPER(wallet_currency_code) = ?', [$currency])
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('wallet_status', 'active')->orWhereNull('wallet_status');
            });

        $this->scopeWalletToDmc($query, $dmcId);

        $ownerId = $query->orderByDesc('available_balance')->value('wallet_owner_id');

        return $ownerId ? (int) $ownerId : $agentId;
    }

    public function dmcScopedAgentBalance(int $agentId, int $dmcId, string $currency): float
    {
        $currency = CurrencyHelper::toIsoCode($currency, strtoupper($currency));
        $agentIds = $this->agencyAgentIds($agentId);
        if ($agentIds === []) {
            return 0.0;
        }

        $query = DB::table('wallet')
            ->where('wallet_owner_type', 'agent')
            ->whereIn('wallet_owner_id', $agentIds)
            ->whereRaw('UPPER(wallet_currency_code) = ?', [$currency])
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('wallet_status', 'active')->orWhereNull('wallet_status');
            });

        $this->scopeWalletToDmc($query, $dmcId);

        return (float) $query->sum('available_balance');
    }

    protected function scopeWalletToDmc($query, int $dmcId): void
    {
        if (! Schema::hasColumn('wallet', 'source_dmc_id')) {
            return;
        }

        $query->where(function ($q) use ($dmcId) {
            $q->where('source_dmc_id', $dmcId);
            if (config('payment.wallet_legacy_unscoped_agent', true)) {
                $q->orWhereNull('source_dmc_id');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function evaluateStripe(string $recipient, int $dmcId, array $settings): array
    {
        if (! $settings['online_enabled']) {
            return ['available' => false, 'reason' => 'Online card payments are disabled.', 'publishable_key' => null];
        }

        if (! $settings['stripe_configured']) {
            return ['available' => false, 'reason' => 'Stripe is not configured for the active mode.', 'publishable_key' => null];
        }

        $publishable = config('payment.stripe.publishable_key');

        if ($recipient === 'travclicks') {
            return [
                'available' => true,
                'reason' => null,
                'publishable_key' => $publishable,
                'connected_account_id' => null,
                'charge_model' => 'platform',
            ];
        }

        if ($dmcId <= 0 || ! $this->stripeConnectService->isReadyForCharges($dmcId)) {
            return [
                'available' => false,
                'reason' => 'DMC Stripe Connect account is not ready for charges.',
                'publishable_key' => $publishable,
                'connected_account_id' => null,
                'charge_model' => 'destination',
            ];
        }

        $account = $this->stripeConnectService->getAccountForDmc($dmcId);

        return [
            'available' => true,
            'reason' => null,
            'publishable_key' => $publishable,
            'connected_account_id' => $account['stripe_account_id'] ?? null,
            'charge_model' => 'destination',
        ];
    }
}
