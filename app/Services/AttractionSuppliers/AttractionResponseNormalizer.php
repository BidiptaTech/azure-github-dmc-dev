<?php

namespace App\Services\AttractionSuppliers;

/**
 * Maps unified internal attraction shape to frontend-compatible payload.
 */
class AttractionResponseNormalizer
{
    /**
     * @param  array<int, array<string, mixed>>  $unifiedAttractions
     * @return array<int, array<string, mixed>>
     */
    public function forFrontend(array $unifiedAttractions): array
    {
        return array_map(fn (array $item) => $this->mapAttraction($item), $unifiedAttractions);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function mapAttraction(array $item): array
    {
        $skuId = (string) ($item['sku_id'] ?? '');
        $low = (float) ($item['lowest_ticket_price'] ?? 0);
        $high = (float) ($item['highest_ticket_price'] ?? 0);
        $tickets = $item['tickets'] ?? null;

        if (! is_array($tickets) || $tickets === []) {
            $tickets = $this->buildTicketsFromPrices($skuId, $low, $high);
        } else {
            $tickets = array_values(array_filter($tickets, 'is_array'));
        }

        $legacy = [
            'sku_id' => $skuId,
            'title' => (string) ($item['title'] ?? ''),
            'tickets' => $tickets,
            'currency' => $item['currency'] ?? 'SGD',
            'lowest_ticket_price' => $low > 0 ? $low : null,
            'highest_ticket_price' => $high > 0 ? $high : null,
            'lowestPrice' => $low > 0 ? $low : null,
            'supplier_code' => $item['supplier_code'] ?? null,
            'onlineAttractionRaw' => $item['raw'] ?? null,
        ];

        return array_merge($item, $legacy);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTicketsFromPrices(string $skuId, float $low, float $high): array
    {
        $tickets = [];

        if ($low > 0) {
            $tickets[] = [
                'ticketId' => $skuId . '-standard',
                'ticketName' => 'Standard Ticket',
                'sku_id' => '',
                'synthetic' => true,
                'price' => [
                    'adult' => $low,
                    'child' => $low,
                ],
            ];
        }

        if ($high > 0 && abs($high - $low) > 0.0001) {
            $tickets[] = [
                'ticketId' => $skuId . '-premium',
                'ticketName' => 'Premium Ticket',
                'sku_id' => '',
                'synthetic' => true,
                'price' => [
                    'adult' => $high,
                    'child' => $high,
                ],
            ];
        }

        if ($tickets === [] && $skuId !== '') {
            $tickets[] = [
                'ticketId' => $skuId . '-default',
                'ticketName' => 'General Admission',
                'sku_id' => '',
                'synthetic' => true,
                'price' => [
                    'adult' => 0,
                    'child' => 0,
                ],
            ];
        }

        return $tickets;
    }
}
