<?php

namespace App\Services;

use App\Helpers\CommonHelper;
use App\Helpers\CurrencyHelper;
use App\Models\Country;
use App\Models\Enquiry;
use App\Models\Order;
use App\Models\Tour;
use Illuminate\Support\Facades\Log;

/**
 * When a service is added to a Confirmed, Definite, or Actual tour, append that
 * service's price to the existing enquiry_comments.negotiation_details JSON.
 *
 * The enquiry row is updated in place. No new enquiry_comments row is created,
 * and amount / actual_amount / gross_amount are left unchanged so markup,
 * discount, wallet, commission, and refund totals keep using the negotiated figure.
 *
 * Currency:
 * - Service amounts stay in orders.currency.
 * - enquiry.currency is the negotiation currency those amounts are converted into.
 * - country.current_rate is 1 SGD in that currency. The service is converted
 *   into the enquiry currency (order → enquiry), which is the direction the
 *   stored converted_* fields already use.
 */
class EnquiryAmountTopUpService
{
    /**
     * @return bool True when the existing JSON already contains this service or was updated.
     */
    public function addOrder(Order $order): bool
    {
        if (empty($order->tour_id) || empty($order->booking_id)) {
            return false;
        }

        $tourStatus = strtolower(trim((string) Tour::where('tour_id', $order->tour_id)->value('tour_status')));
        if (! in_array($tourStatus, ['confirmed', 'definite', 'actual'], true)) {
            return false;
        }

        $enquiry = $this->existingEnquiry((int) $order->tour_id);
        if (! $enquiry) {
            return false;
        }

        $offers = is_array($enquiry->negotiation_details) ? $enquiry->negotiation_details : [];
        if ($offers === []) {
            return false;
        }

        $bookingId = (int) $order->booking_id;
        if ($this->alreadyRecorded($offers, $bookingId)) {
            return true;
        }

        $native = round(CommonHelper::calculateOrderGrossAmount($order), 2);
        if ($native <= 0) {
            return false;
        }

        $orderCurrency = $this->serviceCurrency($order);
        $enquiryCurrency = $this->enquiryCurrency($enquiry, $offers);
        if ($orderCurrency === '' || $enquiryCurrency === '') {
            return false;
        }

        $rate = $this->orderToEnquiryRate($orderCurrency, $enquiryCurrency, $offers);
        if ($rate === null) {
            Log::warning('Enquiry negotiation update skipped: country.current_rate missing', [
                'tour_id' => $order->tour_id,
                'booking_id' => $bookingId,
                'order_currency' => $orderCurrency,
                'enquiry_currency' => $enquiryCurrency,
            ]);

            return false;
        }

        $converted = round($native * $rate, 2);

        // Same keys as a confirmed negotiation row. booking_id stops a second save
        // of this service from appending again. Previous rows are not rewritten.
        $offers[] = [
            'country' => trim((string) ($order->country ?? '')),
            'currency' => $orderCurrency,
            'amount' => $native,
            'actual_amount' => $native,
            'gross' => $native,
            'conversion_rate' => round($rate, 8),
            'date_of_conversion' => now()->toDateTimeString(),
            'converted_amount' => $converted,
            'converted_actual_amount' => $converted,
            'converted_gross' => $converted,
            'target_currency' => $enquiryCurrency,
            'booking_id' => $bookingId,
            'service_type' => (string) ($order->type ?? ''),
        ];

        $enquiry->negotiation_details = array_values($offers);
        $enquiry->save();

        return true;
    }

    private function existingEnquiry(int $tourId): ?Enquiry
    {
        $confirmed = Enquiry::where('tour_id', $tourId)
            ->where('status', 2)
            ->whereNotNull('negotiation_details')
            ->orderByDesc('enquiry_id')
            ->first();

        if ($confirmed && is_array($confirmed->negotiation_details) && $confirmed->negotiation_details !== []) {
            return $confirmed;
        }

        $latest = Enquiry::where('tour_id', $tourId)
            ->whereNotNull('negotiation_details')
            ->orderByDesc('enquiry_id')
            ->first();

        if ($latest && is_array($latest->negotiation_details) && $latest->negotiation_details !== []) {
            return $latest;
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $offers
     */
    private function alreadyRecorded(array $offers, int $bookingId): bool
    {
        foreach ($offers as $offer) {
            if (is_array($offer) && (int) ($offer['booking_id'] ?? 0) === $bookingId) {
                return true;
            }
        }

        return false;
    }

    private function serviceCurrency(Order $order): string
    {
        $currency = strtoupper(trim((string) ($order->currency ?? '')));
        if ($currency !== '') {
            return $currency;
        }

        $country = trim((string) ($order->country ?? ''));
        if ($country === '') {
            return '';
        }

        return strtoupper(trim((string) (Country::where('name', $country)->value('currency') ?? '')));
    }

    /**
     * Negotiation currency. The column set at confirm wins; older rows only
     * stored it as target_currency on each offer.
     *
     * @param  array<int, mixed>  $offers
     */
    private function enquiryCurrency(Enquiry $enquiry, array $offers): string
    {
        $currency = strtoupper(trim((string) ($enquiry->currency ?? '')));
        if ($currency !== '') {
            return $currency;
        }

        foreach ($offers as $offer) {
            if (! is_array($offer)) {
                continue;
            }
            $target = strtoupper(trim((string) ($offer['target_currency'] ?? '')));
            if ($target !== '') {
                return $target;
            }
        }

        return '';
    }

    /**
     * Multiplier that turns an orders.currency amount into enquiry.currency.
     *
     * country.current_rate is 1 SGD in that currency, so
     * enquiry = order * (enquiryRate / orderRate). When that rate is not stored
     * yet, reuse the rate already saved on this negotiation for the same pair
     * (the SGD settlement), then the live rate. The IDR row is still written.
     *
     * @param  array<int, mixed>  $offers
     */
    private function orderToEnquiryRate(string $orderCurrency, string $enquiryCurrency, array $offers): ?float
    {
        if ($orderCurrency === $enquiryCurrency) {
            return 1.0;
        }

        $orderPerSgd = Country::sgdRateFor($orderCurrency);
        $enquiryPerSgd = Country::sgdRateFor($enquiryCurrency);
        if ($orderPerSgd !== null && $enquiryPerSgd !== null && $orderPerSgd > 0) {
            return $enquiryPerSgd / $orderPerSgd;
        }

        foreach ($offers as $offer) {
            if (! is_array($offer)) {
                continue;
            }
            $from = strtoupper(trim((string) ($offer['currency'] ?? '')));
            $to = strtoupper(trim((string) ($offer['target_currency'] ?? '')));
            $rate = (float) ($offer['conversion_rate'] ?? 0);
            if ($from === $orderCurrency && $to === $enquiryCurrency && $rate > 0) {
                return $rate;
            }
        }

        $live = CurrencyHelper::getExchangeRate($orderCurrency, $enquiryCurrency);

        return ($live !== null && $live > 0) ? (float) $live : null;
    }
}
