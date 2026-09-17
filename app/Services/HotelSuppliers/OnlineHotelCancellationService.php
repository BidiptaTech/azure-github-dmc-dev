<?php

namespace App\Services\HotelSuppliers;

use App\Models\Order;
use App\Services\ApiEnvironmentResolver;
use App\Services\SupplierConfigResolver;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Cancels a confirmed online hotel with its supplier when the order is removed
 * from the tour. Dispatches by `orders.booking_details.supplier_code` so each
 * supplier's cancel API can be added independently.
 */
class OnlineHotelCancellationService
{
    public function __construct(
        private OnlineHotelBookingServiceFactory $factory,
        private SupplierConfigResolver $supplierConfig,
        private ApiEnvironmentResolver $apiEnvironment,
    ) {}

    /**
     * Call the supplier cancel API when this is an online hotel that was booked.
     * Returns null when no supplier cancel is needed (offline / not yet booked).
     *
     * @return array<string, mixed>|null
     */
    public function cancelIfApplicable(Order $order): ?array
    {
        if (($order->type ?? '') !== 'hotel') {
            return null;
        }

        if (strtolower(trim((string) ($order->order_type ?? ''))) !== 'online') {
            return null;
        }

        $details = $this->decodeBookingDetails($order->booking_details);

        if ($details === []) {
            // Enquiry marked online but never confirmed with the supplier.
            return null;
        }

        if (! empty($details['cancelled_at']) || ! empty($details['cancellation']['cancelled_at'])) {
            return is_array($details['cancellation'] ?? null) ? $details['cancellation'] : null;
        }

        $supplierCode = strtolower(trim((string) ($details['supplier_code'] ?? '')));

        if ($supplierCode === '') {
            return null;
        }

        if (! $this->factory->supportsCancellation($supplierCode)) {
            throw new RuntimeException(
                'This online hotel was booked with "' . $supplierCode
                . '", but supplier cancellation is not supported yet. Remove was blocked so the reservation is not left open.'
            );
        }

        $credentialCode = $this->factory->credentialCode($supplierCode);
        $environment = $this->apiEnvironment->resolveForRecord($details);
        $credentials = $this->credentialsFor($credentialCode, $environment);

        $result = $this->factory
            ->makeCancellable($supplierCode)
            ->cancelFromBookingDetails($details, $credentials);

        $updated = array_merge($details, [
            'cancellation' => $result,
            'cancelled_at' => $result['cancelled_at'] ?? now()->toIso8601String(),
        ]);

        $order->booking_details = json_encode($updated);
        $order->save();

        Log::info('Online hotel cancelled with supplier', [
            'order_id' => $order->id,
            'booking_id' => $order->booking_id,
            'supplier_code' => $result['supplier_code'] ?? $supplierCode,
            'status' => $result['status'] ?? null,
            'already_cancelled' => $result['already_cancelled'] ?? false,
            'api_environment' => $result['api_environment'] ?? $environment,
        ]);

        return $result;
    }

    /**
     * @return array<string, string|null>
     */
    private function credentialsFor(string $credentialCode, string $environment): array
    {
        if (! $this->supplierConfig->isConfigured($credentialCode, $environment)) {
            throw new RuntimeException(
                $this->supplierConfig->missingCredentialsMessage($credentialCode, $environment)
            );
        }

        $values = $this->supplierConfig->valuesFor($credentialCode, $environment);
        $values['api_environment'] = $environment;

        return $values;
    }

    /**
     * @param  mixed  $raw
     * @return array<string, mixed>
     */
    private function decodeBookingDetails(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
