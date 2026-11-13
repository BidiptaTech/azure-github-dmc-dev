<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DmcInvoice extends Model
{
    protected $table = 'dmc_invoice';

    public const CURRENCY = 'SGD';

    protected $fillable = [
        'dmc_invoice_id',
        'adult_price',
        'child_price',
        'infant_price',
        'currency',
        'created_by',
    ];

    protected $casts = [
        'adult_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'infant_price' => 'decimal:2',
        'dmc_invoice_id' => 'integer',
        'created_by' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->currency = self::CURRENCY;
        });

        static::updating(function (self $model) {
            $model->currency = self::CURRENCY;
        });

        static::created(function (self $model) {
            if ($model->dmc_invoice_id === null) {
                $model->forceFill(['dmc_invoice_id' => $model->id])->saveQuietly();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'userId');
    }

    /**
     * Latest Travclicks-level base prices (fallback when no offer applies).
     */
    public static function current(): ?self
    {
        return static::query()->latest('id')->first();
    }
}
