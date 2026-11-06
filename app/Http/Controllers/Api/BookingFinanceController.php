<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessFinanceSettlementJob;
use App\Jobs\RetryFinanceOperationJob;
use App\Models\Tour;
use App\Services\Finance\PaymentGatewayService;
use App\Services\Finance\SettlementService;
use App\Services\Finance\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Booking finance endpoints — Laravel proxy to Azure Finance Functions.
 */
class BookingFinanceController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected SettlementService $settlementService,
        protected PaymentGatewayService $paymentGatewayService,
    ) {}

    public function walletTopup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wallet_owner_type' => 'required|in:travclicks,master_dmc,dmc,agent,supplier',
            'wallet_owner_id' => 'required|integer|min:1',
            'topup_amount' => 'required|numeric|min:0.01',
            'wallet_currency_code' => 'nullable|string|size:3',
            'supplier_api_id' => 'nullable|string|max:64',
            'ledger_description' => 'nullable|string|max:255',
            'idempotency_key' => 'nullable|string|max:128',
        ]);

        $validated['idempotency_key'] ??= (string) Str::uuid();

        try {
            $result = $this->walletService->topup($validated);

            return response()->json($result);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() >= 400 ? $e->getCode() : 500);
        }
    }

    public function walletBooking(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tour_id' => 'required|integer|exists:tours,id',
            'wallet_owner_type' => 'required|in:dmc,agent,travclicks',
            'wallet_owner_id' => 'required|integer|min:1',
            'supplier_api_id' => 'required|string|max:64',
            'supplier_booking_reference' => 'nullable|string|max:128',
            'supplier_cost' => 'required|numeric|min:0',
            'travclicks_markup' => 'required|numeric|min:0',
            'dmc_commission' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0.01',
            'wallet_currency_code' => 'nullable|string|size:3',
            'idempotency_key' => 'nullable|string|max:128',
        ]);

        $validated['idempotency_key'] ??= (string) Str::uuid();

        try {
            $result = $this->walletService->bookWithWallet($validated);

            return response()->json($result);
        } catch (RuntimeException $e) {
            if ($e->getCode() === 402) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 402);
            }

            RetryFinanceOperationJob::dispatch('wallet_booking', $validated)
                ->delay(now()->addSeconds(30));

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() >= 400 ? $e->getCode() : 500);
        }
    }

    public function calculateCommission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_cost' => 'required|numeric|min:0.01',
            'travclicks_markup_percent' => 'required|numeric|min:0',
            'dmc_commission_percent' => 'required|numeric|min:0',
            'agent_markup_percent' => 'nullable|numeric|min:0',
            'currency_code' => 'nullable|string|size:3',
        ]);

        try {
            $result = $this->settlementService->calculateCommission($validated);

            return response()->json($result);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function offlineSettlement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tour_id' => 'required|integer|exists:tours,id',
            'finance_confirmed_by' => 'required|string|max:128',
            'supplier_api_id' => 'required|string|max:64',
            'supplier_booking_reference' => 'nullable|string|max:128',
            'supplier_cost' => 'required|numeric|min:0',
            'travclicks_markup' => 'required|numeric|min:0',
            'dmc_commission' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0.01',
            'payment_received_amount' => 'required|numeric|min:0.01',
            'payment_received_currency' => 'nullable|string|size:3',
            'office_receipt_reference' => 'nullable|string|max:128',
            'idempotency_key' => 'nullable|string|max:128',
        ]);

        $validated['idempotency_key'] ??= (string) Str::uuid();

        try {
            $result = $this->settlementService->settleOffline($validated);

            return response()->json($result);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function refund(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tour_id' => 'required|integer|exists:tours,id',
            'payment_transaction_id' => 'required|integer|min:1',
            'refund_type' => 'required|in:wallet,gateway,supplier,full',
            'refund_amount' => 'required|numeric|min:0.01',
            'refund_currency_code' => 'nullable|string|size:3',
            'refund_reason' => 'nullable|string|max:500',
            'wallet_owner_type' => 'nullable|in:dmc,agent,travclicks',
            'wallet_owner_id' => 'nullable|integer|min:1',
            'idempotency_key' => 'nullable|string|max:128',
        ]);

        $validated['idempotency_key'] ??= (string) Str::uuid();

        try {
            $result = $this->paymentGatewayService->refund($validated);

            return response()->json($result);
        } catch (RuntimeException $e) {
            RetryFinanceOperationJob::dispatch('refund', $validated)
                ->delay(now()->addMinutes(1));

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Initiate online payment for a tour booking.
     */
    public function createOnlinePayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tour_id' => 'required|integer|exists:tours,id',
            'selling_price' => 'required|numeric|min:0.01',
            'currency_code' => 'nullable|string|size:3',
            'supplier_api_id' => 'required|string|max:64',
        ]);

        $tour = Tour::findOrFail($validated['tour_id']);

        try {
            $gatewayOrder = $this->paymentGatewayService->createPayment([
                'tour_id' => $tour->id,
                'selling_price' => $validated['selling_price'],
                'currency_code' => $validated['currency_code'] ?? 'USD',
                'supplier_api_id' => $validated['supplier_api_id'],
                'finance_reference' => $tour->finance_reference,
            ]);

            $tour->update([
                'payment_mode' => 'online_gateway',
                'payment_status' => 'pending',
                'gateway_response_json' => $gatewayOrder,
                'payment_gateway_name' => config('finance.payment_gateways.default'),
                'selling_price' => $validated['selling_price'],
                'supplier_api_id' => $validated['supplier_api_id'],
            ]);

            ProcessFinanceSettlementJob::dispatch($tour->id, 'online_pending')
                ->delay(now()->addMinutes(15));

            return response()->json([
                'success' => true,
                'data' => $gatewayOrder,
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
