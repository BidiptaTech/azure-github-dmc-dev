<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Credit TravClicks holds with one API vendor (supplier code) in one environment.
 */
class SupplierCreditAccount extends Model
{
    use SoftDeletes;

    protected $table = 'supplier_credit_accounts';

    /** Balances are only ever changed by SupplierCreditService under a row lock. */
    protected $fillable = [
        'supplier_code',
        'environment',
        'currency_code',
        'low_balance_threshold',
        'enforce_limit',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'total_credit_added' => 'decimal:2',
            'balance' => 'decimal:2',
            'total_debited' => 'decimal:2',
            'total_refunded' => 'decimal:2',
            'low_balance_threshold' => 'decimal:2',
            'enforce_limit' => 'boolean',
            'auto_created' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SupplierCreditTransaction::class, 'account_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isLow(): bool
    {
        return $this->low_balance_threshold !== null
            && (float) $this->balance <= (float) $this->low_balance_threshold;
    }

    public function environmentLabel(): string
    {
        return $this->environment === 'live' ? 'Live' : 'Sandbox';
    }
}
