<?php

namespace App\Services\Payment;

use App\Models\Country;
use App\Models\Order;
use App\Models\Tour;
use App\Services\Finance\WalletLedgerService;
use App\Services\Finance\WalletQueryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * When an online hotel/attraction is approved (supplier booked):
 * - Travclicks/admin markup → Commission statement only (order_commission_lines), NOT wallet
 * - DMC markup → DMC wallet in that DMC's home currency (IDR / SGD / …), ledger carries tour_id
 */
class OnlineServiceCommissionCreditService
{
    public function __construct(
        protected CommissionBreakdownService $breakdownService,
        protected WalletLedgerService $ledger,
        protected WalletQueryService $walletQuery,
    ) {}

    /**
     * @param  object|Order  $orderRow  Eloquent Order or stdClass from DB::table('orders')
     * @return array<string, mixed>
     */
    public function creditOnApprove(object $orderRow, ?Tour $tour = null): array
    {
        try {
            $order = $orderRow instanceof Order
                ? $orderRow
                : Order::query()->find((int) ($orderRow->id ?? 0));

            if (! $order) {
                return ['credited' => false, 'reason' => 'order_not_found'];
            }

            if (strtolower(trim((string) ($order->order_type ?? ''))) !== 'online') {
                return ['credited' => false, 'reason' => 'not_online'];
            }

            $type = strtolower(trim((string) ($order->type ?? '')));
            if (! in_array($type, ['hotel', 'attraction'], true)) {
                return ['credited' => false, 'reason' => 'unsupported_type'];
            }

            $tourId = (int) ($order->tour_id ?? 0);
            $tour = $tour ?? Tour::query()->where('tour_id', $tourId)->first();
            if (! $tour) {
                return ['credited' => false, 'reason' => 'tour_not_found'];
            }

            $line = $this->breakdownService->lineForOrder($order, $tour);
            $adminAmt = round((float) ($line['travclicks_commission'] ?? 0), 4);
            $dmcAmt = round((float) ($line['dmc_commission'] ?? 0), 4);

            if ($adminAmt <= 0 && $dmcAmt <= 0) {
                return [
                    'credited' => false,
                    'reason' => 'zero_commission',
                    'line' => $line,
                ];
            }

            $sourceCurrency = strtoupper(trim((string) (
                $order->currency
                ?? $tour->currency
                ?? 'SGD'
            )));
            if ($sourceCurrency === '') {
                $sourceCurrency = 'SGD';
            }

            $dmcId = (int) ($tour->dmc_id ?? 0);
            if ($dmcId <= 0) {
                // Fallback: stamped markup.dmc_id inside order data
                $data = $order->data;
                if (is_string($data)) {
                    $data = json_decode($data, true) ?: [];
                }
                if (is_array($data)) {
                    $dmcId = (int) ($this->findNested($data, 'dmc_id') ?? 0);
                }
            }

            // Payment settlement already credited DMC commission (legacy/full) — don't double on approve.
            $paymentSettledDmc = $dmcId > 0
                && $this->ledger->tourCreditExists($tourId, 'dmc', $dmcId)
                && $this->ledger->sumTourCommissionCredits($tourId, 'dmc', $dmcId, 'online_markup_commission') <= 0;

            $orderPk = (int) $order->getKey();
            $bookingOrderId = (int) ($order->booking_id ?? $orderPk);
            $ref = 'COM-ONLINE-'.$orderPk;
            $results = [
                'credited' => true,
                'tour_id' => $tourId,
                'order_id' => $orderPk,
                'source_currency' => $sourceCurrency,
                // Travclicks/admin → commission statement only (order_commission_lines)
                'admin' => $adminAmt > 0
                    ? ['statement_only' => true, 'amount' => $adminAmt, 'currency' => $sourceCurrency]
                    : null,
                'dmc' => null,
            ];

            $walletStamp = null;

            // DMC — always credit that DMC's home wallet currency (IDR / SGD / …), not tour currency
            if ($dmcAmt > 0 && $dmcId > 0 && $paymentSettledDmc) {
                // Payment settlement already posted the wallet credit — still stamp converted amount for the statement.
                $quoted = $this->quoteDmcCommissionCredit($dmcId, $dmcAmt, $sourceCurrency);
                $fromLedger = $this->ledger->tourCommissionWalletCredit($tourId, 'dmc', $dmcId);
                $walletStamp = [
                    'wallet_credited_amount' => $fromLedger['amount'] > 0 ? $fromLedger['amount'] : $quoted['amount'],
                    'wallet_credited_currency' => $fromLedger['currency'] ?: $quoted['currency'],
                    'wallet_source_amount' => $dmcAmt,
                    'wallet_source_currency' => $sourceCurrency,
                    'fx' => $quoted['fx'],
                    'skipped_credit' => 'payment_already_settled',
                ];
                $results['dmc'] = ['skipped' => true, 'reason' => 'payment_already_settled', 'wallet_stamp' => $walletStamp];
            } elseif ($dmcAmt > 0 && $dmcId > 0 && ! $this->ledger->orderCommissionCreditExists($orderPk, 'dmc', $dmcId)) {
                $quoted = $this->quoteDmcCommissionCredit($dmcId, $dmcAmt, $sourceCurrency);
                $dmcCurrency = $quoted['currency'];
                $creditAmount = $quoted['amount'];
                $fxMeta = $quoted['fx'];

                $results['dmc'] = $this->ledger->creditCommission([
                    'wallet_owner_type' => 'dmc',
                    'wallet_owner_id' => $dmcId,
                    'wallet_currency_code' => $dmcCurrency,
                    'amount' => $creditAmount,
                    'finance_reference' => $ref.'-DMC',
                    'tour_id' => $tourId,
                    'description' => 'DMC online markup · Tour #'.$tourId.' · '.$type,
                    'metadata' => [
                        'purpose' => 'online_markup_commission',
                        'owner_role' => 'dmc',
                        'tour_id' => $tourId,
                        'order_id' => $orderPk,
                        'booking_id' => $bookingOrderId,
                        'service_type' => $type,
                        'source_amount' => $dmcAmt,
                        'source_currency' => $sourceCurrency,
                        'credited_amount' => $creditAmount,
                        'credited_currency' => $dmcCurrency,
                        'fx' => $fxMeta,
                        'agent_price' => $line['agent_price'] ?? null,
                    ],
                ]);

                $walletStamp = [
                    'wallet_credited_amount' => $creditAmount,
                    'wallet_credited_currency' => $dmcCurrency,
                    'wallet_source_amount' => $dmcAmt,
                    'wallet_source_currency' => $sourceCurrency,
                    'finance_reference' => $ref.'-DMC',
                    'fx' => $fxMeta,
                ];
            } elseif ($dmcAmt > 0) {
                $results['dmc'] = $dmcId > 0
                    ? ['idempotent' => true]
                    : ['skipped' => true, 'reason' => 'missing_dmc_id'];
                if ($dmcId > 0) {
                    // Already credited — show wallet amount in DMC home currency when convertible
                    $quoted = $this->quoteDmcCommissionCredit($dmcId, $dmcAmt, $sourceCurrency);
                    $walletStamp = [
                        'wallet_credited_amount' => $quoted['amount'],
                        'wallet_credited_currency' => $quoted['currency'],
                        'wallet_source_amount' => $dmcAmt,
                        'wallet_source_currency' => $sourceCurrency,
                        'idempotent' => true,
                        'fx' => $quoted['fx'],
                    ];
                }
            }

            $this->recordCommissionLine($tour, $order, $line, $sourceCurrency, $type, $walletStamp);

            return $results;
        } catch (\Throwable $e) {
            Log::warning('online_service_commission_credit_failed', [
                'order_id' => $orderRow->id ?? null,
                'tour_id' => $orderRow->tour_id ?? null,
                'error' => $e->getMessage(),
            ]);

            return [
                'credited' => false,
                'reason' => 'exception',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $line
     * @param  array<string, mixed>|null  $walletStamp  Actual DMC wallet credit (converted amount)
     */
    protected function recordCommissionLine(
        Tour $tour,
        Order $order,
        array $line,
        string $currency,
        string $type,
        ?array $walletStamp = null
    ): void {
        if (! Schema::hasTable('order_commission_lines')) {
            return;
        }

        $tourId = (int) ($tour->tour_id ?? $tour->getKey());
        $orderId = (int) ($order->booking_id ?? $order->getKey());

        $exists = DB::table('order_commission_lines')
            ->where('tour_id', $tourId)
            ->where('order_id', $orderId)
            ->where('payment_method', 'online_approve')
            ->exists();
        if ($exists) {
            // Keep statement in sync if wallet credit landed after a prior empty stamp
            if (is_array($walletStamp) && ! empty($walletStamp['wallet_credited_amount'])) {
                $existing = DB::table('order_commission_lines')
                    ->where('tour_id', $tourId)
                    ->where('order_id', $orderId)
                    ->where('payment_method', 'online_approve')
                    ->first();
                if ($existing) {
                    $meta = [];
                    if (is_string($existing->metadata ?? null)) {
                        $meta = json_decode((string) $existing->metadata, true) ?: [];
                    } elseif (is_array($existing->metadata ?? null)) {
                        $meta = $existing->metadata;
                    }
                    if (empty($meta['wallet_credited_amount'])) {
                        DB::table('order_commission_lines')->where('id', $existing->id)->update([
                            'metadata' => json_encode(array_merge($meta, $walletStamp, [
                                'dmc_destination' => 'wallet',
                            ])),
                            'transfer_status' => 'dmc_wallet_and_statement',
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            return;
        }

        $meta = [
            'trigger' => 'online_service_approve',
            'service_type' => $type,
            'order_pk' => $order->getKey(),
            'travclicks_destination' => 'commission_statement',
            'dmc_destination' => (is_array($walletStamp) && (float) ($walletStamp['wallet_credited_amount'] ?? 0) > 0)
                ? 'wallet'
                : (((float) ($line['dmc_commission'] ?? 0) > 0) ? 'wallet' : 'none'),
        ];
        if (is_array($walletStamp)) {
            $meta = array_merge($meta, $walletStamp);
        }

        DB::table('order_commission_lines')->insert([
            'line_uuid' => (string) Str::uuid(),
            'tour_id' => $tourId,
            'order_id' => $orderId,
            'payment_attempt_uuid' => null,
            'payment_method' => 'online_approve',
            'channel' => 'online',
            'supplier_api' => $line['supplier_api'] ?? null,
            'item_label' => $line['item_label'] ?? $type,
            'currency_code' => $currency,
            'api_cost' => $line['api_cost'] ?? null,
            'travclicks_commission' => $line['travclicks_commission'] ?? 0,
            'dmc_visible_price' => $line['dmc_visible_price'] ?? 0,
            'dmc_commission' => $line['dmc_commission'] ?? 0,
            'agent_price' => $line['agent_price'] ?? 0,
            'charged_amount' => $line['charged_amount'] ?? 0,
            'conversion_charge' => 0,
            'agent_entry' => 'none',
            'dmc_entry' => ((float) ($line['dmc_commission'] ?? 0) > 0) ? 'credit' : 'none',
            'stripe_transfer_id' => null,
            'transfer_status' => ((float) ($line['dmc_commission'] ?? 0) > 0) ? 'dmc_wallet_and_statement' : 'commission_statement',
            'metadata' => json_encode($meta),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Convert commission into the DMC's configured wallet currency via countries.current_rate.
     *
     * @return array{amount: float, currency: string, fx: array<string, mixed>}
     */
    protected function quoteDmcCommissionCredit(int $dmcId, float $sourceAmount, string $sourceCurrency): array
    {
        $sourceCurrency = strtoupper(trim($sourceCurrency));
        $dmcCurrency = strtoupper($this->walletQuery->resolveDmcWalletCurrency($dmcId));
        $fx = [
            'converted' => false,
            'from' => $sourceCurrency,
            'to' => $dmcCurrency,
            'rate_source' => 'countries.current_rate',
        ];

        if ($sourceAmount <= 0) {
            return ['amount' => 0.0, 'currency' => $dmcCurrency, 'fx' => $fx];
        }

        if ($dmcCurrency === $sourceCurrency || $dmcCurrency === '') {
            return [
                'amount' => round($sourceAmount, 4),
                'currency' => $dmcCurrency !== '' ? $dmcCurrency : $sourceCurrency,
                'fx' => $fx,
            ];
        }

        $converted = Country::convertAmount($sourceAmount, $sourceCurrency, $dmcCurrency);
        if ($converted !== null && $converted > 0) {
            $fx['converted'] = true;
            $fx['exchange_rate'] = $sourceAmount > 0 ? round($converted / $sourceAmount, 8) : 1.0;

            return [
                'amount' => round((float) $converted, 4),
                'currency' => $dmcCurrency,
                'fx' => $fx,
            ];
        }

        // Rate missing — still target DMC currency wallet; credit source amount only if same wallet exists,
        // otherwise keep source currency so the credit is not lost.
        $fx['converted'] = false;
        $fx['fallback'] = 'countries.current_rate_unavailable';

        return [
            'amount' => round($sourceAmount, 4),
            'currency' => $sourceCurrency,
            'fx' => $fx,
        ];
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected function findNested(array $node, string $key): mixed
    {
        if (array_key_exists($key, $node) && $node[$key] !== null && $node[$key] !== '') {
            return $node[$key];
        }
        foreach ($node as $value) {
            if (! is_array($value)) {
                continue;
            }
            $found = $this->findNested($value, $key);
            if ($found !== null && $found !== '') {
                return $found;
            }
        }

        return null;
    }
}
