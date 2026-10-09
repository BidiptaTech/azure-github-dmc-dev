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

    public function hasRules(): bool
    {
        return $this->adminRule instanceof MarkupRule || $this->dmcRule instanceof MarkupRule;
    }

    /**
     * Recorded on an enquiry so a later recheck can re-apply the very same stack.
     *
     * Re-deriving the rules from the approver's session would not work: the DMC rule
     * depends on who was logged in when the enquiry was priced, and markup settings
     * can change between enquiry and approval.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'admin_rule' => $this->adminRule?->toArray(),
            'dmc_rule' => $this->dmcRule?->toArray(),
            'dmc_id' => $this->dmcId,
            'supplier_code' => $this->supplierCode,
            'service_type' => $this->serviceType,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    public static function fromArray(?array $data): ?self
    {
        if (! is_array($data)) {
            return null;
        }

        $context = new self(
            adminRule: MarkupRule::fromArray($data['admin_rule'] ?? null),
            dmcRule: MarkupRule::fromArray($data['dmc_rule'] ?? null),
            dmcId: isset($data['dmc_id']) ? (int) $data['dmc_id'] : null,
            supplierCode: isset($data['supplier_code']) ? (string) $data['supplier_code'] : null,
            serviceType: (string) ($data['service_type'] ?? 'hotels'),
        );

        return $context->hasRules() ? $context : null;
    }

    /**
     * @return array<int, string>
     */
    public function ruleLabels(): array
    {
        $labels = [];

        foreach ([$this->adminRule, $this->dmcRule] as $rule) {
            if ($rule instanceof MarkupRule) {
                $labels[] = $rule->source . ' ' . $rule->label();
            }
        }

        return $labels;
    }
}
