<?php

namespace App\Services\Quotations;

/**
 * Prepares view-model data for the itinerary (Acco + Service) quotation PDF.
 *
 * Shared pricing, occupancy, and country grouping come from the assembler.
 * This builder adds itinerary-only share-row cells: hotel occupancy stays
 * separate from other-services, and child-with-bed is shown under the
 * booked hotel occupancy column only.
 */
class ItineraryQuotationDataBuilder
{
    public function __construct(private QuotationPresentationAssembler $assembler)
    {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function build(array $payload): array
    {
        return $this->assembler->presentItineraryShareRows(
            $this->assembler->assemble($payload, 'itinerary')
        );
    }
}
