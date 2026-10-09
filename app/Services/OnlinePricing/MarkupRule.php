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

    /**
     * @return array{type: string, amount: float, source: string}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'amount' => $this->amount,
            'source' => $this->source,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    public static function fromArray(?array $data): ?self
    {
        if (! is_array($data) || (float) ($data['amount'] ?? 0) <= 0) {
            return null;
        }

        return new self(
            type: (string) ($data['type'] ?? 'percentage'),
            amount: (float) $data['amount'],
            source: (string) ($data['source'] ?? ''),
        );
    }

    public function label(): string
    {
        return $this->isPercentage()
            ? rtrim(rtrim(number_format($this->amount, 2, '.', ''), '0'), '.') . '%'
            : number_format($this->amount, 2, '.', '');
    }
}
