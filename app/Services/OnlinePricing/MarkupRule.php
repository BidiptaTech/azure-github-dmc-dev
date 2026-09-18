<?php

namespace App\Services\OnlinePricing;

final class MarkupRule
{
    public function __construct(
        public readonly string $type,
        public readonly float $amount,
        public readonly string $source = '',
    ) {}

    public function isPercentage(): bool
    {
        return in_array(strtolower((string) $this->type), ['percentage', 'percent', '1'], true);
    }

    public function isFlat(): bool
    {
        return ! $this->isPercentage();
    }
}
