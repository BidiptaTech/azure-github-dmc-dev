<?php

namespace App\Services\OnlinePricing\Appliers;

use App\Services\OnlinePricing\MarkupCalculator;
use App\Services\OnlinePricing\MarkupContext;

class AttractionPriceMarkupApplier
{
    public function __construct(
        private MarkupCalculator $calculator,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $attractions
     * @return array<int, array<string, mixed>>
     */
    public function apply(array $attractions, MarkupContext $context): array
    {
        return array_map(fn (array $item) => $this->applyToAttraction($item, $context), $attractions);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function applyToAttraction(array $item, MarkupContext $context): array
    {
        foreach (['lowest_ticket_price', 'lowestPrice', 'lowest_price', 'highest_ticket_price', 'highestPrice', 'highest_price', 'price', 'amount'] as $key) {
            if (isset($item[$key]) && is_numeric($item[$key]) && (float) $item[$key] > 0) {
                $item[$key] = $this->markUp((float) $item[$key], $context);
            }
        }

        $tickets = $item['tickets'] ?? $item['ticketDetails'] ?? $item['products'] ?? null;
        if (is_array($tickets) && $tickets !== []) {
            $item['tickets'] = array_map(
                fn (array $ticket) => $this->applyToTicket($ticket, $context),
                $tickets,
            );
        }

        $low = (float) ($item['lowest_ticket_price'] ?? 0);
        $high = (float) ($item['highest_ticket_price'] ?? 0);
        if ($low <= 0 || $high <= 0) {
            $this->syncLowHighFromTickets($item);
        }

        return $item;
    }

    /**
     * @param  array<string, mixed>  $ticket
     * @return array<string, mixed>
     */
    private function applyToTicket(array $ticket, MarkupContext $context): array
    {
        $price = $ticket['price'] ?? $ticket['currencyConvertedPrice'] ?? null;
        if (is_array($price)) {
            foreach (['adult', 'child', 'senior', 'actual', 'infant'] as $field) {
                if (isset($price[$field]) && is_numeric($price[$field]) && (float) $price[$field] > 0) {
                    $price[$field] = $this->markUp((float) $price[$field], $context);
                }
            }
            $ticket['price'] = $price;
            if (isset($ticket['currencyConvertedPrice']) && is_array($ticket['currencyConvertedPrice'])) {
                $ticket['currencyConvertedPrice'] = $price;
            }
        }

        foreach (['adult_price', 'adultPrice', 'child_price', 'childPrice', 'senior_adult_price', 'seniorPrice', 'price'] as $scalar) {
            if (isset($ticket[$scalar]) && is_numeric($ticket[$scalar]) && (float) $ticket[$scalar] > 0) {
                $ticket[$scalar] = $this->markUp((float) $ticket[$scalar], $context);
            }
        }

        return $ticket;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function syncLowHighFromTickets(array &$item): void
    {
        $tickets = $item['tickets'] ?? [];
        if (! is_array($tickets) || $tickets === []) {
            return;
        }

        $prices = [];
        foreach ($tickets as $ticket) {
            $p = $ticket['price']['adult'] ?? $ticket['price']['actual'] ?? $ticket['adultPrice'] ?? null;
            if (is_numeric($p) && (float) $p > 0) {
                $prices[] = (float) $p;
            }
        }

        if ($prices === []) {
            return;
        }

        $item['lowest_ticket_price'] = min($prices);
        $item['highest_ticket_price'] = max($prices);
    }

    private function markUp(float $basePrice, MarkupContext $context): float
    {
        return $this->calculator->applyStack($basePrice, $context->stackedRules());
    }
}
