<?php

namespace App\Services\Finance;

use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Orchestrates finance microservice calls after a booking order is saved.
 * Keeps TourController thin — all payment routing logic lives here.
 */
class BookingFinanceOrchestrator
{
    public function __construct(
        protected WalletService $walletService,
        protected SettlementService $settlementService,
        protected PaymentGatewayService $paymentGatewayService,
    ) {}

    /**
     * Process finance for a confirmed booking (not enquiry).
     *
     * @return array<string, mixed>|null Finance result data, or null if finance skipped.
     */
    public function processAfterBooking(Tour $tour, Request $request, float $sellingPrice): ?array
    {
        $paymentMode = $request->input('payment_mode');

        if (! $paymentMode) {
            return null;
        }

        $supplierCost = (float) $request->input('supplier_cost', 0);
        $travclicksMarkup = (float) $request->input('travclicks_markup', 0);
        $dmcCommission = (float) $request->input('dmc_commission', 0);
        $supplierApiId = (string) $request->input('supplier_api_id', '');

        if ($supplierCost <= 0 && ($travclicksMarkup <= 0 && $dmcCommission <= 0)) {
            $commission = $this->settlementService->calculateCommission([
                'supplier_cost' => $supplierCost > 0 ? $supplierCost : $sellingPrice * 0.45,
                'travclicks_markup_percent' => (float) $request->input('travclicks_markup_percent', 50),
                'dmc_commission_percent' => (float) $request->input('dmc_commission_percent', 46.67),
                'currency_code' => $request->input('wallet_currency_code', 'USD'),
            ]);
            $breakdown = $commission['data'] ?? $commission;
            $supplierCost = (float) ($breakdown['supplier_cost'] ?? $supplierCost);
            $travclicksMarkup = (float) ($breakdown['travclicks_markup'] ?? $travclicksMarkup);
            $dmcCommission = (float) ($breakdown['dmc_commission'] ?? $dmcCommission);
            $sellingPrice = (float) ($breakdown['selling_price'] ?? $sellingPrice);
        }

        if ($supplierApiId === '') {
            $supplierApiId = (string) $request->input('supplier_api_id', 'hotelbeds');
        }

        $basePayload = [
            'idempotency_key' => $request->input('idempotency_key', (string) Str::uuid()),
            'tour_id' => (int) ($tour->getKey() ?? $tour->id ?? $tour->tour_id),
            'supplier_api_id' => $supplierApiId,
            'supplier_booking_reference' => $request->input('supplier_booking_reference'),
            'supplier_cost' => $supplierCost,
            'travclicks_markup' => $travclicksMarkup,
            'dmc_commission' => $dmcCommission,
            'selling_price' => $sellingPrice,
            'wallet_currency_code' => $request->input('wallet_currency_code', 'USD'),
        ];

        try {
            return match ($paymentMode) {
                'wallet' => $this->processWalletBooking($tour, $request, $basePayload),
                'online_gateway' => $this->processOnlinePayment($tour, $request, $basePayload),
                'offline_office' => $this->processOfflineSettlement($tour, $request, $basePayload),
                default => null,
            };
        } catch (RuntimeException $e) {
            Log::error('booking_finance_failed', [
                'tour_id' => $tour->id,
                'payment_mode' => $paymentMode,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Run finance microservice when a tour is confirmed via web UI.
     *
     * @return array<string, mixed>|null
     */
    public function processOnTourConfirmation(Tour $tour, float $sellingPrice): ?array
    {
        if ($sellingPrice <= 0) {
            return null;
        }

        if (in_array((string) ($tour->finance_status ?? ''), ['settled', 'processing'], true)) {
            return null;
        }

        if ($tour->payment_mode && (string) ($tour->payment_status ?? '') === 'captured') {
            return null;
        }

        $paymentMode = (string) config('finance.default_payment_mode_on_confirm', 'wallet');
        if ($paymentMode === '') {
            return null;
        }

        $walletOwnerType = $tour->agent_id ? 'agent' : 'dmc';
        $walletOwnerId = $tour->agent_id
            ? (int) $tour->agent_id
            : (int) ($tour->dmc_id ?? 0);

        if ($walletOwnerId <= 0) {
            return null;
        }

        $walletQuery = app(WalletQueryService::class);
        $currency = $walletQuery->resolveCurrencyForOwner($walletOwnerType, $walletOwnerId);
        $markup = round($sellingPrice / 2, 4);
        $commission = round($sellingPrice - $markup, 4);

        $request = Request::create('/', 'POST', [
            'payment_mode' => $paymentMode,
            'wallet_owner_type' => $walletOwnerType,
            'wallet_owner_id' => $walletOwnerId,
            'wallet_currency_code' => $currency,
            'selling_price' => $sellingPrice,
            'supplier_api_id' => 'offline',
            'supplier_cost' => 0,
            'travclicks_markup' => $markup,
            'dmc_commission' => $commission,
            'idempotency_key' => 'web-confirm-'.$tour->tour_id.'-'.Str::uuid(),
        ]);

        return $this->processAfterBooking($tour, $request, $sellingPrice);
    }

    /**
     * @param  array<string, mixed>  $basePayload
     * @return array<string, mixed>
     */
    protected function processWalletBooking(Tour $tour, Request $request, array $basePayload): array
    {
        $walletOwnerType = $request->input('wallet_owner_type', 'dmc');
        $walletOwnerId = (int) $request->input('wallet_owner_id', $tour->dmc_id ?? 0);

        if ($walletOwnerId <= 0) {
            throw new RuntimeException('wallet_owner_id is required for wallet bookings');
        }

        $result = $this->walletService->bookWithWallet(array_merge($basePayload, [
            'wallet_owner_type' => $walletOwnerType,
            'wallet_owner_id' => $walletOwnerId,
            'dmc_id' => $tour->dmc_id,
            'agent_id' => $tour->agent_id,
            'master_dmc_id' => $this->resolveMasterDmcId($tour),
        ]));

        $this->syncTourFromFinanceResponse($tour, $result, 'wallet');

        return $result;
    }

    /**
     * @param  array<string, mixed>  $basePayload
     * @return array<string, mixed>
     */
    protected function processOnlinePayment(Tour $tour, Request $request, array $basePayload): array
    {
        $gatewayOrder = $this->paymentGatewayService->createPayment([
            'tour_id' => $tour->id,
            'selling_price' => $basePayload['selling_price'],
            'currency_code' => $basePayload['wallet_currency_code'],
            'supplier_api_id' => $basePayload['supplier_api_id'],
            'finance_reference' => $tour->finance_reference,
        ]);

        $tour->update([
            'payment_mode' => 'online_gateway',
            'payment_status' => 'pending',
            'gateway_response_json' => $gatewayOrder,
            'payment_gateway_name' => config('finance.payment_gateways.default'),
            'supplier_cost' => $basePayload['supplier_cost'],
            'travclicks_markup' => $basePayload['travclicks_markup'],
            'dmc_commission' => $basePayload['dmc_commission'],
            'selling_price' => $basePayload['selling_price'],
            'supplier_api_id' => $basePayload['supplier_api_id'],
            'finance_status' => 'pending',
        ]);

        return [
            'payment_mode' => 'online_gateway',
            'gateway_order' => $gatewayOrder,
        ];
    }

    /**
     * @param  array<string, mixed>  $basePayload
     * @return array<string, mixed>
     */
    protected function processOfflineSettlement(Tour $tour, Request $request, array $basePayload): array
    {
        $result = $this->settlementService->settleOffline(array_merge($basePayload, [
            'finance_confirmed_by' => $request->input('finance_confirmed_by', auth()->user()?->email ?? 'system'),
            'payment_received_amount' => $request->input('payment_received_amount', $basePayload['selling_price']),
            'payment_received_currency' => $basePayload['wallet_currency_code'],
            'office_receipt_reference' => $request->input('office_receipt_reference'),
        ]));

        $this->syncTourFromFinanceResponse($tour, $result, 'offline_office');

        return $result;
    }

    protected function resolveMasterDmcId(Tour $tour): ?int
    {
        if ($tour->master_dmc_id) {
            return (int) $tour->master_dmc_id;
        }

        if ($tour->dmc_id) {
            $dmc = User::where('userId', $tour->dmc_id)->first();

            return $dmc?->master_dmc_id ? (int) $dmc->master_dmc_id : null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function syncTourFromFinanceResponse(Tour $tour, array $result, string $paymentMode): void
    {
        $data = $result['data'] ?? $result;

        $tour->update([
            'finance_reference' => $data['finance_reference'] ?? $tour->finance_reference,
            'payment_mode' => $paymentMode,
            'payment_status' => $paymentMode === 'wallet' ? 'captured' : $tour->payment_status,
            'finance_status' => isset($data['settlement_status']) && in_array($data['settlement_status'], ['completed', 'settled'], true)
                ? 'settled'
                : ($tour->finance_status ?? 'pending'),
            'selling_price' => $data['selling_price'] ?? $tour->selling_price,
            'supplier_cost' => $data['supplier_cost'] ?? $tour->supplier_cost,
            'travclicks_markup' => $data['travclicks_markup'] ?? $tour->travclicks_markup,
            'dmc_commission' => $data['dmc_commission'] ?? $tour->dmc_commission,
        ]);
    }
}
