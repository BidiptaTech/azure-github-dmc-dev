<?php

namespace App\Services\OnlinePricing;

class MarkupCalculator
{
    public function apply(float $basePrice, MarkupRule $rule): float
    {
        if ($basePrice <= 0 || $rule->amount <= 0) {
            return max(0, round($basePrice, 2));
        }

        $markup = $rule->isPercentage()
            ? ($basePrice * $rule->amount / 100)
            : $rule->amount;

        return max(0, round($basePrice + $markup, 2));
    }

    /**
     * @param  array<int, MarkupRule|null>  $rules
     */
    public function applyStack(float $basePrice, array $rules): float
    {
        $price = $basePrice;

        foreach ($rules as $rule) {
            if (! $rule instanceof MarkupRule) {
                continue;
            }
            $price = $this->apply($price, $rule);
        }

        return $price;
    }
}
