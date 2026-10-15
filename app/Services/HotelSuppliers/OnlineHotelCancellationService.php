<?php

namespace App\Services\HotelSuppliers;

use App\Models\Order;
use App\Services\ApiEnvironmentResolver;
use App\Services\SupplierConfigResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Cancels a confirmed online hotel with its supplier when the order is removed
 * from the tour. Dispatches by `orders.booking_details.supplier_code` so each
 * supplier's cancel API can be added independently (MG today; Hotelbeds / Tiniva later).
 *
 * User-facing messages intentionally omit supplier product names.
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

        $details = $this->decodeBookingDetails($order->booking_details);

        if ($details === []) {
            // Enquiry marked online but never confirmed with the supplier.
            Log::info('Online hotel remove skipped supplier cancel (empty booking_details)', [
                'order_id' => $order->id,
                'booking_id' => $order->booking_id,
                'order_type' => $order->order_type ?? null,
            ]);

            return null;
        }

        $orderTypeOnline = strtolower(trim((string) ($order->order_type ?? ''))) === 'online';
        $detailsLookOnline = $this->looksLikeOnlineBookingDetails($details);

        // Prefer order_type, but still cancel when booking_details proves a supplier book
        // (avoids silent skip if order_type was never set to online).
        if (! $orderTypeOnline && ! $detailsLookOnline) {
            Log::info('Online hotel remove skipped supplier cancel (not an online order)', [
                'order_id' => $order->id,
                'booking_id' => $order->booking_id,
                'order_type' => $order->order_type ?? null,
            ]);

            return null;
        }

        if (! empty($details['cancelled_at']) || ! empty($details['cancellation']['cancelled_at'])) {
            return is_array($details['cancellation'] ?? null) ? $details['cancellation'] : null;
        }

        if (! $this->wasConfirmedWithSupplier($details)) {
            Log::info('Online hotel remove skipped supplier cancel (booking not confirmed)', [
                'order_id' => $order->id,
                'booking_id' => $order->booking_id,
                'order_type' => $order->order_type ?? null,
                'supplier_code' => $details['supplier_code'] ?? null,
            ]);

            return null;
        }

        $supplierCode = strtolower(trim((string) ($details['supplier_code'] ?? '')));

        if ($supplierCode === '') {
            Log::warning('Online hotel remove skipped supplier cancel (missing supplier_code)', [
                'order_id' => $order->id,
                'booking_id' => $order->booking_id,
            ]);

            return null;
        }

        if (! $this->factory->supportsCancellation($supplierCode)) {
            Log::warning('Online hotel cancel blocked: supplier cancel not wired yet', [
                'order_id' => $order->id,
                'booking_id' => $order->booking_id,
                'supplier_code' => $supplierCode,
            ]);

            throw new RuntimeException(
                'This online hotel reservation cannot be cancelled automatically yet. '
                . 'The hotel was not removed so the reservation is not left open.'
            );
        }

        try {
            $credentialCode = $this->factory->credentialCode($supplierCode);
            $environment = $this->apiEnvironment->resolveForRecord($details);
            $credentials = $this->credentialsFor($credentialCode, $environment);

            $result = $this->factory
                ->makeCancellable($supplierCode)
                ->cancelFromBookingDetails($details, $credentials);
        } catch (Throwable $e) {
            Log::error('Online hotel supplier cancellation failed', [
                'order_id' => $order->id,
                'booking_id' => $order->booking_id,
                'supplier_code' => $supplierCode,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                'Failed to cancel the online hotel reservation with the supplier. The hotel was not removed.',
                0,
                $e
            );
        }

        $updated = array_merge($details, [
            'cancellation' => $result,
            'cancelled_at' => $result['cancelled_at'] ?? now()->toIso8601String(),
        ]);

        // Persist like approveHotelBooking (query builder) so model events cannot
        // drop the cancel payload before soft-delete. Fall back to Eloquent save
        // for in-memory doubles used in tests.
        $encoded = json_encode($updated);
        $persisted = false;

        if (! empty($order->id)) {
            $persisted = DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'booking_details' => $encoded,
                    'updated_at' => now(),
                ]) > 0;
        }

        $order->booking_details = $encoded;

        if (! $persisted) {
            $persisted = (bool) $order->save();
        }

        if (! $persisted) {
            Log::error('Online hotel cancel response could not be stored on order', [
                'order_id' => $order->id,
                'booking_id' => $order->booking_id,
                'supplier_code' => $supplierCode,
            ]);

            throw new RuntimeException(
                'Failed to store the online hotel cancellation result. The hotel was not removed.'
            );
        }

        Log::info('Online hotel cancelled with supplier', [
            'order_id' => $order->id,
            'booking_id' => $order->booking_id,
            'supplier_code' => $result['supplier_code'] ?? $supplierCode,
            'status' => $result['status'] ?? null,
            'already_cancelled' => $result['already_cancelled'] ?? false,
            'api_environment' => $result['api_environment'] ?? $environment ?? null,
            'cancel_status' => $result['status'] ?? null,
        ]);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function looksLikeOnlineBookingDetails(array $details): bool
    {
        $supplierCode = strtolower(trim((string) ($details['supplier_code'] ?? '')));

        return $supplierCode !== '' && $this->factory->supports($supplierCode);
    }

    /**
     * True when booking_details shows a successful supplier confirmation
     * (IDs / references from a completed BookHotel / book call).
     *
     * @param  array<string, mixed>  $details
     */
    private function wasConfirmedWithSupplier(array $details): bool
    {
        $bookResponse = is_array($details['book_response'] ?? null)
            ? $details['book_response']
            : [];

        if ($bookResponse === []) {
            return false;
        }

        // Explicit failed book stored on the order — do not call cancel.
        if (array_key_exists('status', $bookResponse) && $bookResponse['status'] === false) {
            return false;
        }

        $mgDetails = is_array($bookResponse['bookingDetails'] ?? null)
            ? $bookResponse['bookingDetails']
            : [];
        $hbBooking = is_array($bookResponse['booking'] ?? null)
            ? $bookResponse['booking']
            : [];

        $mgBookingId = trim((string) (
            $mgDetails['mgBookingID']
            ?? $mgDetails['MGBookingID']
            ?? $details['mg_booking_id']
            ?? ''
        ));

        $hotelbedsRef = trim((string) (
            $details['supplier_booking_reference']
            ?? $hbBooking['reference']
            ?? ''
        ));

        if ($mgBookingId !== '' || $hotelbedsRef !== '') {
            return true;
        }

        $agencyBookingId = trim((string) (
            $mgDetails['agencyBookingID']
            ?? $mgDetails['AgencyBookingID']
            ?? $details['agency_booking_id']
            ?? $hbBooking['clientReference']
            ?? ''
        ));

        if ($agencyBookingId !== '' && (($bookResponse['status'] ?? false) === true)) {
            return true;
        }

        $confirmStatus = strtoupper(trim((string) (
            $mgDetails['status']
            ?? $hbBooking['status']
            ?? ''
        )));

        return $agencyBookingId !== ''
            && in_array($confirmStatus, ['CONF', 'CONFIRMED'], true);
    }

    /**
     * @return array<string, string|null>
     */
    private function credentialsFor(string $credentialCode, string $environment): array
    {
        if (! $this->supplierConfig->isConfigured($credentialCode, $environment)) {
            Log::error('Online hotel cancel blocked: missing supplier credentials', [
                'credential_code' => $credentialCode,
                'environment' => $environment,
                'detail' => $this->supplierConfig->missingCredentialsMessage($credentialCode, $environment),
            ]);

            throw new RuntimeException(
                'Online hotel supplier credentials are not configured. The hotel was not removed.'
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
