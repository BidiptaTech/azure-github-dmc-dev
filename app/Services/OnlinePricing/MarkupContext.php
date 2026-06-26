<?php

namespace App\Services\OnlinePricing;

final class MarkupContext
{
    public function __construct(
        public readonly ?MarkupRule $adminRule = null,
        public readonly ?MarkupRule $dmcRule = null,
        public readonly ?int $dmcId = null,
        public readonly ?string $supplierCode = null,
        public readonly string $serviceType = 'hotels',
    ) {}

    /**
     * @return array<int, MarkupRule|null>
     */
    public function stackedRules(): array
    {
        return [$this->adminRule, $this->dmcRule];
    }
}
