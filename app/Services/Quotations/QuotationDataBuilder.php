<?php

namespace App\Services\Quotations;

/**
 * Shared quotation data preparation.
 *
 * Reuses CommonHelper-prepared payload, then merges Blade-extracted
 * presentation calculations so the existing PDF views can render without
 * recalculating business values.
 */
class QuotationDataBuilder
{
    public function __construct(
        private ItineraryQuotationDataBuilder $itineraryBuilder,
        private DetailedQuotationDataBuilder $detailedBuilder
    ) {
    }

    /**
     * Enrich helper payload for a Blade view name used by downloadTourPdf().
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function forViewName(array $payload, string $viewName): array
    {
        $type = $viewName === 'single-tour-package.detailedqutation'
            ? 'detailed'
            : 'itinerary';

        return $this->mergePreparedViewData($payload, $type);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  string  $quotationType  itinerary|detailed
     * @return array<string, mixed>
     */
    public function mergePreparedViewData(array $payload, string $quotationType): array
    {
        $quotationType = $quotationType === 'detailed' ? 'detailed' : 'itinerary';

        $presentation = $quotationType === 'detailed'
            ? $this->detailedBuilder->build($payload)
            : $this->itineraryBuilder->build($payload);

        $normalized = $this->toNormalizedContract($payload, $presentation, $quotationType);

        $viewData = array_merge($payload, $presentation, [
            'quotationData' => $normalized,
        ]);

        foreach ($this->unusedViewPayloadKeys() as $unusedKey) {
            unset($viewData[$unusedKey]);
        }

        return $viewData;
    }

    /**
     * Assembler internals kept for tests/contract, not required by Blade.
     *
     * @return list<string>
     */
    private function unusedViewPayloadKeys(): array
    {
        return [
            'formatOccupancyHotelCells',
            'extractQuotationOrderAmount',
            'resolveQuotationOrderLabel',
            'quotationOrderRows',
            'otherOrderTotalByBucketKey',
            'formatSelectedPersonsCells',
            'formatAmount',
            'formatMoney',
            'cardCountry',
            'cardCurrency',
            'countryBucketKey',
            'parseYmdFromRange',
            'isUsableCityName',
            'resolveStayForItem',
            'sharingLabel',
            'convertToOverall',
            'overallLine',
        ];
    }

    /**
     * Normalized contract for future DMC templates. Existing Blade variables
     * remain the source of truth for PDF output in this phase.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $presentation
     * @return array<string, mixed>
     */
    public function toNormalizedContract(array $payload, array $presentation, string $quotationType): array
    {
        $tour = $payload['tour'] ?? null;
        $booking = is_array($payload['bookingDetails'] ?? null) ? $payload['bookingDetails'] : [];
        $travel = is_array($payload['travelDetails'] ?? null) ? $payload['travelDetails'] : [];
        $brandingMode = strtolower((string) ($payload['logoType'] ?? $presentation['logoType'] ?? 'dmc'));
        $dmcDetails = is_array($payload['dmcDetails'] ?? null) ? $payload['dmcDetails'] : [];
        $agentDetails = is_array($payload['agentDetails'] ?? null) ? $payload['agentDetails'] : [];
        $agency = $payload['user_agency'] ?? $presentation['user_agency'] ?? null;
        $tourPrices = is_array($payload['tourPrices'] ?? null) ? $payload['tourPrices'] : [];

        $companyName = null;
        $address = null;
        $email = null;
        $phone = null;
        $logo = $payload['dmcLogo'] ?? null;

        if ($brandingMode === 'agency' && $agency) {
            $companyName = $agency->agency_name ?? ($agentDetails['name'] ?? null);
            $address = $agency->address ?? ($agentDetails['address'] ?? null);
            $email = $agency->email ?? ($agentDetails['email'] ?? null);
            $phone = $agency->phone ?? ($agentDetails['phone'] ?? null);
            $logo = $agency->logo ?? null;
        } else {
            $companyName = $payload['dmcCompanyName'] ?? ($dmcDetails['company_name'] ?? ($dmcDetails['name'] ?? null));
            $address = $dmcDetails['address'] ?? null;
            $email = $dmcDetails['email'] ?? null;
            $phone = $dmcDetails['phone'] ?? null;
        }

        return [
            'quotation' => [
                'reference' => $presentation['formattedDisplayId'] ?? ($booking['booking_id'] ?? null),
                'date' => $payload['proposalDetails']['proposal_date'] ?? null,
                'valid_until' => $payload['proposalDetails']['proposal_validity'] ?? null,
                'currency' => $presentation['selectedCurrency'] ?? ($payload['selectedCurrency'] ?? null),
                'base_currency' => $presentation['baseCurrency'] ?? ($payload['baseCurrency'] ?? null),
                'exchange_rate' => $presentation['exchangeRate'] ?? ($payload['exchangeRate'] ?? null),
                'type' => $quotationType,
                'generated_at' => $payload['generatedAt'] ?? null,
            ],
            'branding' => [
                'mode' => $brandingMode,
                'company_name' => $companyName,
                'logo' => $logo,
                'address' => $address,
                'email' => $email,
                'phone' => $phone,
                'dmc_details' => $dmcDetails,
                'agent_details' => $agentDetails,
            ],
            'booking' => [
                'lead_guest_name' => $presentation['leadGuestName'] ?? ($booking['lead_guest_name'] ?? null),
                'email' => $booking['email'] ?? null,
                'phone' => $booking['phone'] ?? null,
                'adults' => $presentation['adults'] ?? ($booking['no_of_adults'] ?? 0),
                'children' => $presentation['children'] ?? ($booking['no_of_children'] ?? 0),
                'infants' => $presentation['infants'] ?? ($booking['no_of_infants'] ?? 0),
                'display_adults' => $presentation['displayAdults'] ?? null,
                'pax_text' => $presentation['paxText'] ?? null,
                'foc_size' => $presentation['focSize'] ?? null,
                'has_foc' => $presentation['hasFoc'] ?? false,
                'passengers' => $booking['passengers'] ?? [],
            ],
            'travel' => [
                'destination' => $travel['destination'] ?? ($tour->destination ?? null),
                'start_date' => $travel['travel_date_from'] ?? null,
                'end_date' => $travel['travel_date_to'] ?? null,
                'duration' => $travel['duration'] ?? null,
                'travelling_date' => $presentation['travellingDate'] ?? null,
                'inclusion_date_range' => $presentation['inclusionDateRange'] ?? null,
                'rooming_text' => $presentation['roomingText'] ?? null,
            ],
            'services' => [
                'by_type' => $payload['servicesByType'] ?? [],
                'by_date' => $payload['servicesByDate'] ?? [],
                'hotels_by_country' => $presentation['hotelsByCountry'] ?? [],
                'other_by_country' => $presentation['otherByCountry'] ?? [],
                'country_keys' => $presentation['allCountryKeys'] ?? [],
                'country_meta' => $presentation['countryMeta'] ?? [],
                'orders' => $payload['orders'] ?? [],
            ],
            'pricing' => $this->normalizedPricing($payload, $presentation, $quotationType, $tourPrices),
            'bank_details' => $payload['bankDetails'] ?? [],
            'terms_and_conditions' => $payload['termsAndConditions'] ?? null,
            'exclusions' => $payload['exclusions'] ?? null,
            'quotation_information' => $payload['quotationInformationHtml']
                ?? ($presentation['quotationInformationHtml'] ?? null),
        ];
    }

    /**
     * Pricing contract for future templates. Blade still uses the flat view-model.
     *
     * Hotel-only occupancy totals are in the same units as CommonHelper tourPrices
     * sharing rates (not converted to the overall display currency).
     * Subtotal, discount, tax, and grand total are in display_currency
     * (overallTargetCurrency / selected quotation currency).
     * Country-sharing amounts stay in each row's native currency.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $presentation
     * @param  array<string, mixed>  $tourPrices
     * @return array<string, mixed>
     */
    private function normalizedPricing(array $payload, array $presentation, string $quotationType, array $tourPrices): array
    {
        $occupancyKey = $presentation['occupancyKey'] ?? null;
        $tripleAvailable = !empty($presentation['tripleOccupancyAvailable']);
        $hotelOnlyTriple = $presentation['hotelOnlyTripleTotal'] ?? null;

        $bookedHotelTotal = null;
        if ($occupancyKey === 'single') {
            $bookedHotelTotal = $presentation['hotelOnlySingleTotal'] ?? null;
        } elseif ($occupancyKey === 'double') {
            $bookedHotelTotal = $presentation['hotelOnlyDoubleTotal'] ?? null;
        } elseif ($occupancyKey === 'triple') {
            $bookedHotelTotal = $tripleAvailable ? $hotelOnlyTriple : null;
        }

        $countrySharing = $presentation['countrySharingRows'] ?? ($tourPrices['country_sharing'] ?? []);
        $combinedCountrySharing = null;
        if ($quotationType === 'detailed' && is_array($countrySharing) && $countrySharing !== []) {
            $combinedCountrySharing = [];
            foreach ($countrySharing as $share) {
                if (!is_array($share)) {
                    continue;
                }
                if (!array_key_exists('combined_single', $share)
                    && !array_key_exists('combined_double', $share)
                    && !array_key_exists('combined_triple', $share)) {
                    continue;
                }
                $combinedCountrySharing[] = [
                    'key' => $share['key'] ?? null,
                    'country' => $share['country'] ?? null,
                    'currency' => $share['currency'] ?? null,
                    'combined_single' => $share['combined_single'] ?? 0.0,
                    'combined_double' => $share['combined_double'] ?? 0.0,
                    'combined_triple' => $share['combined_triple'] ?? 0.0,
                    'child_all' => $share['child_all'] ?? null,
                ];
            }
            if ($combinedCountrySharing === []) {
                $combinedCountrySharing = null;
            }
        }

        return [
            'tour_prices' => $tourPrices,
            'hotel_options' => $payload['hotelOptions'] ?? [],
            'country_sharing' => $countrySharing,
            'country_quotation_groups' => $payload['countryQuotationGroups'] ?? [],
            'display_currency' => $presentation['overallTargetCurrency']
                ?? ($presentation['selectedCurrency'] ?? ($payload['selectedCurrency'] ?? null)),
            // Same units as tourPrices occupancy sharing (hotel minus other-services per occupancy).
            'hotel_only_single_total' => $presentation['hotelOnlySingleTotal'] ?? null,
            'hotel_only_double_total' => $presentation['hotelOnlyDoubleTotal'] ?? null,
            'hotel_only_triple_total' => $tripleAvailable ? $hotelOnlyTriple : null,
            // Hotel-only total for the booked occupancy_key. Null when occupancy is unknown
            // or triple was selected but no triple hotel-only amount exists.
            'hotel_total_booked_occupancy' => $bookedHotelTotal,
            // Other-services per-pax for booked occupancy (double uses other_services_double; otherwise single).
            'other_services_total_booked_occupancy' => $presentation['otherTotalForOccupancy'] ?? null,
            // Packaged combined hotel+other per occupancy, native country currency. Null for itinerary.
            'country_sharing_combined' => $combinedCountrySharing,
            'discounts' => $tourPrices['discounts'] ?? [],
            // Display currency. Already ceiled in the assembler.
            'discount_total' => $presentation['overallDiscountShown'] ?? ($tourPrices['discount_total'] ?? null),
            'tax_total' => $presentation['overallTaxShown'] ?? null,
            'tax_breakdown' => $presentation['overallTaxBreakdown'] ?? [],
            'subtotal' => $presentation['overallLinesSubtotal'] ?? null,
            'grand_total' => $presentation['totalCostAmount'] ?? null,
            'grand_total_label' => $presentation['totalCostLabel'] ?? null,
            'package_rows' => $presentation['overallPackageRows'] ?? [],
            'supplements' => $presentation['supplements'] ?? ($tourPrices['supplements'] ?? []),
            'occupancy_key' => $occupancyKey,
            'order_line_sell_total' => $presentation['overallQuotationTotal'] ?? null,
            'order_line_sell_converted_ok' => $presentation['overallQuotationConvertedOk'] ?? null,
            'payment_terms' => $payload['paymentTerms'] ?? [],
        ];
    }
}
