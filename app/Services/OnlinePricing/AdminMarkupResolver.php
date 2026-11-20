<?php

namespace App\Services\OnlinePricing;

use App\Models\SupplierMaster;

class AdminMarkupResolver
{
    public function fromSupplier(?SupplierMaster $supplier): ?MarkupRule
    {
        if (! $supplier) {
            return null;
        }

        $amount = (float) ($supplier->amount ?? 0);

        if ($amount <= 0) {
            return null;
        }

        return new MarkupRule(
            type: (string) ($supplier->markup_type ?? 'percentage'),
            amount: $amount,
            source: 'admin:supplier:' . ($supplier->code ?? $supplier->id),
        );
    }
}
