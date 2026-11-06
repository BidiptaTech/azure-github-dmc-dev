<?php

namespace App\Services\OnlinePricing;

use App\Models\User;

class DmcMarkupResolver
{
    /**
     * @param  'hotels'|'attractions'|'flights'  $serviceType
     */
    public function forService(?int $dmcId, string $serviceType): ?MarkupRule
    {
        if (! $dmcId) {
            return null;
        }

        $dmc = User::query()->where('userId', $dmcId)->first();

        if (! $dmc) {
            return null;
        }

        return match ($serviceType) {
            'attractions' => $this->ruleFromUserFields(
                (int) ($dmc->markup_type_attraction ?? 1),
                (float) ($dmc->markup_price_attraction ?? 0),
                'dmc:attraction'
            ),
            'hotels' => $this->ruleFromUserFields(
                (int) ($dmc->markup_type ?? 0),
                (float) ($dmc->markup_price ?? 0),
                'dmc:hotel'
            ),
            default => null,
        };
    }

    private function ruleFromUserFields(int $type, float $amount, string $source): ?MarkupRule
    {
        if ($amount <= 0) {
            return null;
        }

        // users.markup_type: 0 = flat, 1 = percentage
        $markupType = $type === 1 ? 'percentage' : 'flat';

        return new MarkupRule(
            type: $markupType,
            amount: $amount,
            source: $source,
        );
    }
}
