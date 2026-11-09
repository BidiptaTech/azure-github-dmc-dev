<?php

namespace App\Services\Finance;

/**
 * Settlement operations for online and offline bookings.
 */
class SettlementService
{
    public function __construct(
        protected FinanceService $financeService,
    ) {}

    /**
     * Settle online gateway-captured booking.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function settleOnline(array $data): array
    {
        return $this->financeService->post(
            config('finance.endpoints.online_settlement'),
            $data
        );
    }

    /**
     * Settle offline office payment after finance team confirmation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function settleOffline(array $data): array
    {
        return $this->financeService->post(
            config('finance.endpoints.offline_settlement'),
            $data
        );
    }

    /**
     * Calculate commission breakdown before booking.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function calculateCommission(array $data): array
    {
        return $this->financeService->post(
            config('finance.endpoints.commission_calculate'),
            $data
        );
    }

    /**
     * Credit DMC/Travclicks wallets after agent wallet booking (idempotent repair).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function distributeBookingProceeds(array $data): array
    {
        return $this->financeService->post(
            config('finance.endpoints.wallet_booking_distribute_proceeds'),
            $data
        );
    }
}
