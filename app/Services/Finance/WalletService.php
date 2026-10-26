<?php

namespace App\Services\Finance;

/**
 * Wallet operations delegated to Azure Finance Functions.
 */
class WalletService
{
    public function __construct(
        protected FinanceService $financeService,
    ) {}

    /**
     * Credit prepaid wallet for DMC/Agent/Supplier.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function topup(array $data): array
    {
        return $this->financeService->post(
            config('finance.endpoints.wallet_topup'),
            $data
        );
    }

    /**
     * Allocate funds: debit allocator wallet and credit recipient wallet.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function allocate(array $data): array
    {
        return $this->financeService->post(
            config('finance.endpoints.wallet_allocate'),
            $data
        );
    }

    /**
     * Deduct wallet and settle booking atomically.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function bookWithWallet(array $data): array
    {
        return $this->financeService->post(
            config('finance.endpoints.wallet_booking'),
            $data
        );
    }

    /**
     * Reserve available balance for a pending booking (DMC-scoped when source_dmc_id set).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function reserve(array $data): array
    {
        return $this->financeService->post(
            config('finance.endpoints.wallet_reserve'),
            $data
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function commitReservation(array $data): array
    {
        return $this->financeService->post(
            config('finance.endpoints.wallet_commit'),
            $data
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function releaseReservation(array $data): array
    {
        return $this->financeService->post(
            config('finance.endpoints.wallet_release'),
            $data
        );
    }
}
