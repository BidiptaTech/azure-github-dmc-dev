<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only statement line for a supplier credit account.
 */
class SupplierCreditTransaction extends Model
{
    protected $table = 'supplier_credit_transactions';

    protected $guarded = ['*'];

    public const TYPE_LABELS = [
        'topup' => 'Credit added',
        'adjustment_credit' => 'Manual adjustment (+)',
        'adjustment_debit' => 'Manual adjustment (−)',
        'booking_debit' => 'Booking charged',
        'cancel_refund' => 'Cancellation refund',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'supplier_amount' => 'decimal:2',
            'cancellation_charge' => 'decimal:2',
            'fx_rate' => 'decimal:8',
            'metadata' => 'array',
            'reconciled_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(SupplierCreditAccount::class, 'account_id');
    }

    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->txn_type] ?? ucfirst(str_replace('_', ' ', (string) $this->txn_type));
    }

    public function signedAmount(): float
    {
        return $this->direction === 'debit' ? -1 * (float) $this->amount : (float) $this->amount;
    }
}
