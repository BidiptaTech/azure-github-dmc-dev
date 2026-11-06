<?php

namespace App\Services\OnlinePricing;

use App\Helpers\CommonHelper;
use App\Models\User;

class DmcMarkupResolver
{
    /**
     * Resolve DMC markup for online services from users.markup_json
     * (hotel / other). Falls back to legacy scalar columns when json amount is 0.
     *
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
            'hotels' => $this->ruleFromMarkupJson($dmcId, 'hotel', 'dmc:hotel')
                ?? $this->ruleFromLegacyUserFields(
                    (int) ($dmc->markup_type ?? 0),
                    (float) ($dmc->markup_price ?? 0),
                    'dmc:hotel'
                ),
            'attractions' => $this->ruleFromMarkupJson($dmcId, 'other', 'dmc:attraction')
                ?? $this->ruleFromLegacyUserFields(
                    (int) ($dmc->markup_type_attraction ?? 1),
                    (float) ($dmc->markup_price_attraction ?? 0),
                    'dmc:attraction'
                ),
            default => null,
        };
    }

    /**
     * Profile markup_json: hotel | other → MarkupRule.
     *
     * @param  'hotel'|'other'  $kind
     */
    private function ruleFromMarkupJson(int $dmcId, string $kind, string $source): ?MarkupRule
    {
        $config = CommonHelper::getDmcProfileMarkupConfig(null, $dmcId);
        $kind = $kind === 'hotel' ? 'hotel' : 'other';
        $row = $config[$kind] ?? [];

        $amount = (float) ($row['markup_value'] ?? 0);
        if ($amount <= 0) {
            return null;
        }

        $type = strtolower(trim((string) ($row['markup_type'] ?? 'percentage')));
        if ($type === 'fixed') {
            $type = 'flat';
        }
        if (! in_array($type, ['percentage', 'flat'], true)) {
            $type = 'percentage';
        }

        return new MarkupRule(
            type: $type,
            amount: $amount,
            source: $source,
        );
    }

    private function ruleFromLegacyUserFields(int $type, float $amount, string $source): ?MarkupRule
    {
        if ($amount <= 0) {
            return null;
        }

        // Legacy users.markup_type: 0 = flat, 1 = percentage
        $markupType = $type === 1 ? 'percentage' : 'flat';

        return new MarkupRule(
            type: $markupType,
            amount: $amount,
            source: $source,
        );
    }
}
