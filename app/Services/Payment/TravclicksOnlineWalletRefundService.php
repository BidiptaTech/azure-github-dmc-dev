<?php

namespace App\Services\Payment;

use App\Helpers\CommonHelper;
use App\Models\Country;
use App\Models\Order;
use App\Models\Tour;
use App\Services\Finance\WalletQueryService;
use App\Services\Finance\WalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

/**
 * TravClicks → DMC wallet credit for cancelled online services.
 *
 * Cancel paths call tryAutoRefund() so TravClicks does not need to manually
 * "Credit DMC Wallet" / verify on the Refunds screen. Manual refund() remains
 * for overrides (custom amount / force after supplier cancel resolve).
 */
class TravclicksOnlineWalletRefundService
{
    public function __construct(
        protected SupplierCancellationStatusService $cancelStatus,
        protected CommissionBreakdownService $commission,
        protected WalletQueryService $walletQuery,
        protected WalletService $wallet,
        protected FxConversionCharge $fx,
    ) {}

    /**
     * Safe cancel-hook entry: credits full max SGD when eligible.
     * Never throws — logs and returns a status payload.
     *
     * @return array{
     *   credited: bool,
     *   skipped?: bool,
     *   reason?: string,
     *   refund_amount?: float,
     *   max_refund_amount?: float,
     *   wallet_credit?: array<string, mixed>,
     *   message?: string
     * }
     */
    /**
     * Call after online supplier cancel passes all scenario checks
     * (cancel confirmed / not required). Queues refund if needed, then
     * credits DMC wallet and marks TravClicks refund verified.
     *
     * @return array<string, mixed>
     */
    public function afterOnlineCancelConfirmed(Order $order, ?Tour $tour = null): array
    {
        return $this->tryAutoRefund($order, $tour, true);
    }

    public function tryAutoRefund(Order $order, ?Tour $tour = null, bool $queueIfNeeded = false): array
    {
        try {
            if ($queueIfNeeded) {
                $tour = $tour ?? Tour::query()->where('tour_id', (int) ($order->tour_id ?? 0))->first();
                if ($tour) {
                    $this->ensureRefundQueued($order, $tour);
                }
            }

            // Single TravClicks step: cancel online supplier API first, then credit DMC wallet.
            $supplierCancel = $this->ensureOnlineSupplierCancelled($order);
            if (is_array($supplierCancel) && ! empty($supplierCancel['soft_failed'])) {
                return [
                    'credited' => false,
                    'skipped' => true,
                    'reason' => 'supplier_cancel_blocking',
                    'message' => (string) ($supplierCancel['message'] ?? 'Supplier cancellation failed — wallet credit blocked.'),
                    'supplier_cancel' => $supplierCancel,
                ];
            }

            $order->refresh();

            return $this->refund($order, $tour, null, false, '', true);
        } catch (Throwable $e) {
            Log::error('travclicks_online_auto_refund_failed', [
                'order_id' => $order->id ?? null,
                'tour_id' => $order->tour_id ?? null,
                'error' => $e->getMessage(),
            ]);

            return [
                'credited' => false,
                'skipped' => false,
                'reason' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * TravClicks-only: call supplier cancel (SG voucher / hotel cancel) when still pending.
     * Wallet credit stays in refund() after this succeeds.
     *
     * @return array<string, mixed>|null
     */
    protected function ensureOnlineSupplierCancelled(Order $order, ?string $cancelReason = null): ?array
    {
        if (strtolower(trim((string) ($order->order_type ?? ''))) !== 'online') {
            return null;
        }

        if ($this->cancelStatus->allowsWalletCredit($order)) {
            return null;
        }

        $type = strtolower(trim((string) ($order->type ?? '')));
        $reason = trim((string) ($cancelReason ?? ''));
        if ($reason === '') {
            $reason = 'TravClicks auto-refund supplier cancellation';
        }

        if ($type === 'attraction') {
            return app(\App\Services\AttractionSuppliers\OnlineAttractionCancellationService::class)
                ->cancelIfApplicable($order, $reason, true);
        }

        if ($type === 'hotel') {
            return app(\App\Services\HotelSuppliers\OnlineHotelCancellationService::class)
                ->cancelIfApplicable($order, true);
        }

        return null;
    }

    /**
     * Mark order for Refunds tab so TravClicks → DMC wallet credit can run.
     */
    public function ensureRefundQueued(Order $order, Tour $tour): void
    {
        if ((bool) ($order->is_refund ?? false)) {
            return;
        }

        if (strtolower(trim((string) ($order->order_type ?? ''))) !== 'online') {
            return;
        }

        $status = (string) ($tour->tour_status ?? '');
        $qualifies = in_array($status, ['Definite', 'Actual', 'Refund - Pending'], true)
            || str_starts_with($status, 'Refund')
            || app(AcceptanceFlowService::class)->onlinePortionSettled($tour);

        if (! $qualifies) {
            return;
        }

        if (($order->bookingType ?? '') !== 'booking') {
            $order->bookingType = 'booking';
        }
        $order->is_refund = 1;
        if (! isset($order->is_verify) || (int) $order->is_verify === 0) {
            $order->is_verify = 0;
        }
        if (! (bool) ($order->refunded ?? false)) {
            $order->refunded = false;
        }
        $order->save();
    }

    /**
     * @return array{
     *   credited: bool,
     *   skipped?: bool,
     *   reason?: string,
     *   refund_amount?: float,
     *   max_refund_amount?: float,
     *   wallet_credit?: array<string, mixed>,
     *   message?: string
     * }
     */
    public function refund(
        Order $order,
        ?Tour $tour = null,
        ?float $amountSgd = null,
        bool $forceWallet = false,
        string $overrideReason = '',
        bool $auto = false,
    ): array {
        if (strtolower(trim((string) ($order->order_type ?? ''))) !== 'online') {
            return $this->skip('not_online', 'Not an online order.');
        }

        if (! (bool) ($order->is_refund ?? false)) {
            return $this->skip('not_queued', 'Order is not queued for refund.');
        }

        if ((bool) ($order->travclicks_refunded ?? false)) {
            return $this->skip('already_refunded', 'TravClicks already refunded this service to the DMC wallet.');
        }

        $tourId = (int) ($order->tour_id ?? 0);
        $tour = $tour ?? Tour::query()->where('tour_id', $tourId)->first();
        if (! $tour) {
            throw new RuntimeException('Tour not found for online refund.');
        }

        if ($this->cancelStatus->isBlockingWalletCredit($order)) {
            if ($forceWallet && trim($overrideReason) !== '') {
                $this->cancelStatus->markResolvedManual(
                    $order,
                    'Wallet credit override: '.$overrideReason
                );
            } else {
                $cancelInfo = $this->cancelStatus->read($order);
                $statusLabel = strtoupper((string) ($cancelInfo['status'] ?? 'pending'));
                $detail = trim((string) ($cancelInfo['last_error'] ?: $cancelInfo['message'] ?? ''));
                $message = 'Supplier cancellation is '.$statusLabel
                    .'. Retry cancel or mark resolved before crediting the DMC wallet'
                    .($detail !== '' ? (' — '.$detail) : '.');

                if ($auto) {
                    return [
                        'credited' => false,
                        'skipped' => true,
                        'reason' => 'supplier_cancel_blocking',
                        'message' => $message,
                        'supplier_cancellation' => $cancelInfo,
                    ];
                }

                throw new RuntimeException($message);
            }
        }

        $max = $this->maxSgdRefundAmount($order, $tour);
        $maxAmount = (float) $max['amount'];
        $refundAmount = $amountSgd === null ? $maxAmount : round(max(0, $amountSgd), 2);

        if ($refundAmount - $maxAmount > 0.009) {
            $maxMessage = ((float) ($max['dmc_markup_native'] ?? 0)) > 0
                ? 'Refund amount cannot be higher than the order price minus DMC markup (SGD '.number_format($maxAmount, 2, '.', '').').'
                : 'Refund amount cannot be higher than the order price with markup (SGD '.number_format($maxAmount, 2, '.', '').').';

            throw new RuntimeException($maxMessage);
        }

        $walletCredit = $this->creditDmcWallet($tour, $order, $refundAmount);

        if (Schema::hasColumn('orders', 'travclicks_refund_amount')) {
            $order->travclicks_refund_amount = $refundAmount;
        }
        if (Schema::hasColumn('orders', 'travclicks_refunded')) {
            $order->travclicks_refunded = true;
        }
        if ($auto && Schema::hasColumn('orders', 'refund_remarks')) {
            $existing = trim((string) ($order->refund_remarks ?? ''));
            if ($existing === '') {
                $order->refund_remarks = 'TravClicks auto-refunded to DMC wallet after supplier cancellation.';
            }
        }
        $order->updated_at = now();
        $order->save();

        $this->appendStatusTrack($tour, $order, $refundAmount, $auto);

        Log::info('travclicks_online_wallet_refund_completed', [
            'order_id' => $order->id,
            'tour_id' => $tour->tour_id ?? null,
            'auto' => $auto,
            'refund_amount_sgd' => $refundAmount,
            'credited' => (bool) ($walletCredit['credited'] ?? false),
        ]);

        return [
            'credited' => (bool) ($walletCredit['credited'] ?? false),
            'refund_amount' => $refundAmount,
            'max_refund_amount' => $maxAmount,
            'wallet_credit' => $walletCredit,
            'message' => (string) ($walletCredit['message'] ?? 'Refunded to DMC wallet. DMC can still refund the agent separately.'),
            'auto' => $auto,
        ];
    }

    /**
     * Full online sell minus DMC markup, converted to SGD (same as Refunds UI max).
     *
     * @return array{amount: float, native_amount: float, native_currency: string, dmc_markup_native: float}
     */
    public function maxSgdRefundAmount(Order $order, Tour $tour): array
    {
        $priced = app(\App\Http\Controllers\BookingsController::class)
            ->resolveOrderRefundMaxForHotel($tour, $order);

        $nativeCurrency = strtoupper(trim((string) ($priced['currency'] ?: $order->currency ?: 'SGD'))) ?: 'SGD';
        $nativeRate = Country::sgdRateFor($nativeCurrency);
        if ($nativeCurrency !== 'SGD' && ($nativeRate === null || $nativeRate <= 0)) {
            throw new RuntimeException(
                "No countries.current_rate for {$nativeCurrency}. Sync currency rates before refunding."
            );
        }

        $nativeMax = (float) $priced['amount'];
        $dmcMarkupNative = $this->onlineOrderDmcMarkup($order, $tour);
        if ($dmcMarkupNative > 0) {
            $nativeMax = max(0, round($nativeMax - $dmcMarkupNative, 2));
        }

        return [
            'amount' => $this->convertAmountToSgd($nativeMax, $nativeCurrency, $nativeRate),
            'native_amount' => $nativeMax,
            'native_currency' => $nativeCurrency,
            'dmc_markup_native' => $dmcMarkupNative,
        ];
    }

    /**
     * @return array{credited: bool, amount: float, currency: string, source_amount: float, source_currency: string, sgd_amount: float, finance_reference: ?string, message: string}
     */
    public function creditDmcWallet(Tour $tour, Order $order, float $amount): array
    {
        $amount = round(max(0, $amount), 2);
        $sourceCurrency = 'SGD';
        $dmcId = (int) ($tour->dmc_id ?? 0);

        if ($amount <= 0) {
            return [
                'credited' => false,
                'amount' => 0.0,
                'currency' => $sourceCurrency,
                'source_amount' => 0.0,
                'source_currency' => $sourceCurrency,
                'sgd_amount' => 0.0,
                'finance_reference' => null,
                'message' => 'Refund amount is zero — no wallet credit.',
            ];
        }

        if ($dmcId <= 0) {
            throw new RuntimeException('Tour has no DMC — cannot credit wallet.');
        }

        $configured = $this->walletQuery->getConfiguredCurrenciesForOwner('dmc', $dmcId);
        $dmcCurrency = strtoupper(trim((string) (
            $configured['default']
            ?: ($configured['currencies'][0] ?? '')
            ?: $this->walletQuery->resolveCurrencyForOwner('dmc', $dmcId)
        )));
        if ($dmcCurrency === '') {
            throw new RuntimeException('DMC wallet currency is not configured.');
        }

        $sourceRate = Country::sgdRateFor($sourceCurrency);
        if ($sourceRate === null || $sourceRate <= 0) {
            throw new RuntimeException(
                "No countries.current_rate for {$sourceCurrency}. Sync currency rates before refunding."
            );
        }
        $sgdAmount = $amount;

        $dmcRate = Country::sgdRateFor($dmcCurrency);
        if ($dmcRate === null || $dmcRate <= 0) {
            throw new RuntimeException(
                "No countries.current_rate for DMC currency {$dmcCurrency}. Sync currency rates before refunding."
            );
        }

        $fxPercent = $this->fx->percent();
        $effectiveDmcRate = $dmcCurrency === 'SGD'
            ? 1.0
            : $this->fx->effectiveRate((float) $dmcRate, $fxPercent);
        if ($dmcCurrency === 'SGD') {
            $creditAmount = round($sgdAmount, 4);
        } elseif ($dmcCurrency === 'IDR') {
            $creditAmount = (float) round($sgdAmount * $effectiveDmcRate);
        } else {
            $creditAmount = round($sgdAmount * $effectiveDmcRate, 4);
        }

        if ($creditAmount <= 0) {
            throw new RuntimeException('Converted DMC credit amount is zero.');
        }

        if (Schema::hasTable('wallet_ledger')) {
            $already = DB::table('wallet_ledger')
                ->where('ledger_entry_type', 'allocation_credit')
                ->where(function ($q) use ($order) {
                    $orderKey = (string) ($order->id ?? '');
                    if (DB::getDriverName() === 'pgsql') {
                        $q->whereRaw("COALESCE(ledger_metadata->>'refund_order_id', '') = ?", [$orderKey]);
                    } else {
                        $q->where('ledger_metadata', 'like', '%"refund_order_id":"'.$orderKey.'"%')
                            ->orWhere('ledger_metadata', 'like', '%"refund_order_id": '.$orderKey.'%');
                    }
                })
                ->exists();
            if ($already) {
                return [
                    'credited' => false,
                    'amount' => $creditAmount,
                    'currency' => $dmcCurrency,
                    'source_amount' => $amount,
                    'source_currency' => $sourceCurrency,
                    'sgd_amount' => $sgdAmount,
                    'finance_reference' => null,
                    'message' => 'DMC wallet already credited for this refund.',
                ];
            }
        }

        $displayId = trim((string) ($tour->display_id ?? ''));
        $tourLabel = $displayId !== '' ? $displayId : ('#'.(int) ($tour->tour_id ?? 0));
        $fxLabel = $fxPercent > 0
            ? (' rate '.$effectiveDmcRate.' incl. FX '.$fxPercent.'%')
            : (' rate '.$effectiveDmcRate);
        $fxSuffix = " ({$amount} {$sourceCurrency} → {$creditAmount} {$dmcCurrency}{$fxLabel})";
        $desc = 'TravClicks online refund → DMC wallet (tour '.$tourLabel.', order '.($order->booking_id ?? $order->id).')'.$fxSuffix;

        $result = $this->wallet->allocate([
            'idempotency_key' => 'travclicks-online-refund-order-'.(int) $order->id,
            'allocator_wallet_owner_type' => 'travclicks',
            'allocator_wallet_owner_id' => 1,
            'recipient_wallet_owner_type' => 'dmc',
            'recipient_wallet_owner_id' => $dmcId,
            'allocation_amount' => number_format($creditAmount, 4, '.', ''),
            'wallet_currency_code' => $dmcCurrency,
            'allocator_currency_code' => 'SGD',
            'recipient_currency_code' => $dmcCurrency,
            'allocator_amount' => number_format($sgdAmount, 4, '.', ''),
            'recipient_amount' => number_format($creditAmount, 4, '.', ''),
            'exchange_rate' => number_format($effectiveDmcRate, 8, '.', ''),
            'platform_spend' => true,
            'ledger_description' => $desc,
            'debit_ledger_description' => $desc,
            'credit_ledger_description' => $desc,
            'metadata' => [
                'transfer' => [
                    'from_type' => 'travclicks',
                    'from_id' => 1,
                    'to_type' => 'dmc',
                    'to_id' => $dmcId,
                ],
                'refund' => true,
                'refund_order_id' => (string) $order->id,
                'tour_id' => (int) ($tour->tour_id ?? 0),
                'order_booking_id' => $order->booking_id ?? null,
                'order_type' => 'online',
                'auto_refund' => true,
                'fx' => [
                    'source_currency' => $sourceCurrency,
                    'source_amount' => number_format($amount, 4, '.', ''),
                    'source_sgd_rate' => $sourceRate,
                    'sgd_amount' => number_format($sgdAmount, 4, '.', ''),
                    'target_currency' => $dmcCurrency,
                    'target_amount' => number_format($creditAmount, 4, '.', ''),
                    'target_sgd_rate' => $dmcRate,
                    'rate_source' => 'countries.current_rate',
                ],
            ],
        ]);

        if (! ($result['success'] ?? false)) {
            throw new RuntimeException((string) ($result['message'] ?? 'Failed to credit DMC wallet.'));
        }

        return [
            'credited' => true,
            'amount' => $creditAmount,
            'currency' => $dmcCurrency,
            'source_amount' => $amount,
            'source_currency' => $sourceCurrency,
            'sgd_amount' => $sgdAmount,
            'finance_reference' => $result['data']['finance_reference'] ?? null,
            'message' => 'Credited '.$dmcCurrency.' '.number_format($creditAmount, 2)
                .' to DMC wallet (from '.$sourceCurrency.' '.number_format($amount, 2).').',
        ];
    }

    protected function onlineOrderDmcMarkup(Order $order, ?Tour $tour = null): float
    {
        if (strtolower(trim((string) ($order->order_type ?? ''))) !== 'online') {
            return 0.0;
        }

        try {
            $line = $this->commission->lineForOrder($order, $tour);
        } catch (Throwable) {
            return 0.0;
        }

        return round(max(0, (float) ($line['dmc_commission'] ?? 0)), 2);
    }

    protected function convertAmountToSgd(float $amount, string $currency, ?float $sgdRate): float
    {
        $amount = max(0, $amount);
        $currency = strtoupper(trim($currency));
        if ($amount <= 0 || $currency === '' || $currency === 'SGD') {
            return round($amount, 2);
        }
        if ($sgdRate === null || $sgdRate <= 0) {
            return round($amount, 2);
        }

        return round($amount / $sgdRate, 2);
    }

    protected function appendStatusTrack(Tour $tour, Order $order, float $refundAmount, bool $auto): void
    {
        $fromStatus = (string) ($tour->tour_status ?? 'Definite');
        $user = Auth::user();
        $changedByName = $user ? ($user->name ?? null) : ($auto ? 'System' : null);
        $changedByUserId = $user ? ($user->userId ?? $user->id ?? null) : null;

        $payload = is_array($order->data) && isset($order->data[0])
            ? $order->data[0]
            : (is_array($order->data) ? $order->data : []);
        $orderType = (string) ($order->type ?? '');
        $serviceId = null;
        $serviceName = null;
        if ($orderType === 'hotel') {
            $serviceId = $payload['hotelDetails']['hotel_id'] ?? null;
            $serviceName = $payload['hotelDetails']['hotel_name'] ?? null;
        } elseif ($orderType === 'attraction') {
            $serviceId = $payload['attraction_id'] ?? null;
            $serviceName = $payload['AttractionName'] ?? $payload['attraction_name'] ?? null;
        }

        $currency = strtoupper(trim((string) (
            $order->currency
            ?: $order->refund_price_currency
            ?: $tour->user_currency
            ?: 'SGD'
        )));

        CommonHelper::appendTourStatusTrack(
            $tour,
            $fromStatus,
            'Refund - Pending',
            now(),
            $refundAmount,
            null,
            null,
            $changedByName,
            $changedByUserId,
            $auto ? 'TravClicks Auto Refunded' : 'TravClicks Refunded',
            $orderType !== '' ? $orderType : null,
            $serviceId ?? (string) $order->id,
            $serviceName,
            $refundAmount,
            $currency !== '' ? $currency : null,
            null,
            null
        );
    }

    /**
     * @return array{credited: bool, skipped: bool, reason: string, message: string}
     */
    protected function skip(string $reason, string $message): array
    {
        return [
            'credited' => false,
            'skipped' => true,
            'reason' => $reason,
            'message' => $message,
        ];
    }
}
