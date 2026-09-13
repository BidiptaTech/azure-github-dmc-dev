<?php

namespace App\Services;

use App\Helpers\CommonHelper;
use App\Models\Country;
use App\Models\Enquiry;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Adds a newly booked service on top of an already negotiated enquiry.
 *
 * A service is priced in the currency of the country it sits in (a Singapore hotel
 * in SGD, an Indian hotel in INR), while an enquiry_comments row is denominated in
 * one currency. The service total is therefore converted before being added to the
 * negotiated amounts. Tours that were never negotiated have no enquiry_comments row
 * and are left completely alone.
 *
 * The orders table is never touched here — it keeps the service's own currency.
 */
class EnquiryAmountTopUpService
{
    public function __construct(private CurrencyService $currency)
    {
    }

    /**
     * @return bool True when a negotiation row was found and topped up.
     */
    public function addOrder(Order $order): bool
    {
        if (empty($order->tour_id)) {
            return false;
        }

        $enquiry = Enquiry::where('tour_id', $order->tour_id)
            ->orderByDesc('enquiry_id')
            ->orderByDesc('created_at')
            ->first();

        if (! $enquiry) {
            return false;
        }

        $amount = CommonHelper::calculateOrderGrossAmount($order);
        if ($amount <= 0) {
            return false;
        }

        $offers = is_array($enquiry->negotiation_details) ? $enquiry->negotiation_details : [];
        $serviceCurrency = $this->serviceCurrency($order);
        $targetCurrency = $this->targetCurrency($enquiry, $offers, $serviceCurrency);

        if ($serviceCurrency === '') {
            $serviceCurrency = $targetCurrency;
        }

        $index = $this->matchOfferIndex($offers, $order, $serviceCurrency);
        $offerCurrency = $index !== null
            ? (strtoupper(trim((string) ($offers[$index]['currency'] ?? ''))) ?: $serviceCurrency)
            : $serviceCurrency;

        // Into the matched offer's currency, then into the row's currency.
        $nativeDelta = $this->convert($amount, $serviceCurrency, $offerCurrency);
        $convertedDelta = $nativeDelta === null
            ? null
            : $this->convert($nativeDelta, $offerCurrency, $targetCurrency);

        if ($nativeDelta === null || $convertedDelta === null) {
            Log::warning('Enquiry top-up skipped: no exchange rate available', [
                'tour_id' => $order->tour_id,
                'booking_id' => $order->booking_id,
                'service_currency' => $serviceCurrency,
                'offer_currency' => $offerCurrency,
                'target_currency' => $targetCurrency,
            ]);

            return false;
        }

        // Only touch negotiation_details when the row actually carries offers; never
        // fabricate a negotiation that did not happen.
        if ($offers !== []) {
            $offers = $this->applyToOffers($offers, $index, $order, $offerCurrency, $targetCurrency, $nativeDelta, $convertedDelta);
            $enquiry->negotiation_details = $offers;
        }

        $enquiry->amount = round((float) ($enquiry->amount ?? 0) + $convertedDelta, 2);
        $enquiry->actual_amount = round((float) ($enquiry->actual_amount ?? 0) + $convertedDelta, 2);
        $enquiry->gross_amount = round((float) ($enquiry->gross_amount ?? 0) + $convertedDelta, 2);
        $enquiry->save();

        return true;
    }

    /**
     * The currency the service was priced in. The order already carries it, resolved
     * from the service's own city/country when the row was created.
     */
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
     * The currency the enquiry row is denominated in. Most rows predate the currency
     * column, so fall back to what the offers were converted into.
     *
     * @param  array<int, mixed>  $offers
     */
    private function targetCurrency(Enquiry $enquiry, array $offers, string $serviceCurrency): string
    {
        $candidates = [$enquiry->currency ?? ''];
        foreach ($offers as $offer) {
            if (is_array($offer)) {
                $candidates[] = $offer['target_currency'] ?? '';
            }
        }
        foreach ($offers as $offer) {
            if (is_array($offer)) {
                $candidates[] = $offer['currency'] ?? '';
            }
        }
        $candidates[] = $serviceCurrency;

        foreach ($candidates as $candidate) {
            $candidate = strtoupper(trim((string) $candidate));
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return '';
    }

    /**
     * Locate the offer this service belongs to. Matches on country first; some older
     * rows store a currency code in the country field, hence the extra passes.
     *
     * @param  array<int, mixed>  $offers
     */
    private function matchOfferIndex(array $offers, Order $order, string $serviceCurrency): ?int
    {
        $country = strtoupper(trim((string) ($order->country ?? '')));

        foreach ([$country, $serviceCurrency] as $needle) {
            if ($needle === '') {
                continue;
            }
            foreach ($offers as $i => $offer) {
                if (! is_array($offer)) {
                    continue;
                }
                if (strtoupper(trim((string) ($offer['country'] ?? ''))) === $needle) {
                    return (int) $i;
                }
            }
        }

        if ($serviceCurrency !== '') {
            foreach ($offers as $i => $offer) {
                if (is_array($offer) && strtoupper(trim((string) ($offer['currency'] ?? ''))) === $serviceCurrency) {
                    return (int) $i;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $offers
     * @return array<int, mixed>
     */
    private function applyToOffers(
        array $offers,
        ?int $index,
        Order $order,
        string $offerCurrency,
        string $targetCurrency,
        float $nativeDelta,
        float $convertedDelta
    ): array {
        $isNew = $index === null;

        $offer = $isNew
            ? [
                'country' => trim((string) ($order->country ?? '')),
                'currency' => $offerCurrency,
                'amount' => 0,
                'actual_amount' => 0,
                'gross' => 0,
            ]
            : $offers[$index];

        // Legacy single-currency rows have no converted_* mirror; leave them that way
        // so the row total keeps matching the sum of the offers.
        $tracksConversion = $isNew
            ? $this->offersTrackConversion($offers)
            : array_key_exists('converted_amount', $offer);

        $offer['currency'] = $offerCurrency;
        foreach (['amount', 'actual_amount', 'gross'] as $field) {
            $offer[$field] = round((float) ($offer[$field] ?? 0) + $nativeDelta, 2);
        }

        if ($tracksConversion) {
            foreach (['converted_amount', 'converted_actual_amount', 'converted_gross'] as $field) {
                $offer[$field] = round((float) ($offer[$field] ?? 0) + $convertedDelta, 2);
            }
            $offer['target_currency'] = $targetCurrency;
            $offer['date_of_conversion'] = now()->toDateTimeString();
            // Effective rate across the negotiated amount plus this top-up, so
            // converted_amount stays exactly amount x conversion_rate.
            $offer['conversion_rate'] = $offer['amount'] > 0
                ? round($offer['converted_amount'] / $offer['amount'], 8)
                : 1;
        }

        if ($isNew) {
            $offers[] = $offer;
        } else {
            $offers[$index] = $offer;
        }

        return array_values($offers);
    }

    /**
     * @param  array<int, mixed>  $offers
     */
    private function offersTrackConversion(array $offers): bool
    {
        foreach ($offers as $offer) {
            if (is_array($offer) && array_key_exists('converted_amount', $offer)) {
                return true;
            }
        }

        return false;
    }

    private function convert(float $amount, string $from, string $to): ?float
    {
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));

        if ($from === '' || $to === '' || $from === $to) {
            return round($amount, 2);
        }

        $rate = $this->currency->getExchangeRate($from, $to);
        if ($rate === null || $rate <= 0) {
            return null;
        }

        return round($amount * $rate, 2);
    }
}
