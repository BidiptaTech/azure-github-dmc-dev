<?php

namespace App\Services\Quotations;

/**
 * Prepares view-model data for the detailed (Packaged) quotation PDF.
 *
 * Shared pricing, occupancy, and country grouping come from the assembler.
 * This builder adds Packaged-only share-row cells: hotel occupancy + other
 * services are combined per column, and child = child-bed + other-services child.
 */
class DetailedQuotationDataBuilder
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
        return $this->assembler->presentDetailedShareRows(
            $this->assembler->assemble($payload, 'detailed')
        );
    }
}
