<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MiscellaneousPrice extends Model
{
    use SoftDeletes;

    protected $table = 'miscellaneous_prices';

    protected $fillable = [
        'mis_id',
        'dmc_id',
        'adult_price',
        'child_price',
        'infant_price',
        'adult_cost',
        'child_cost',
        'infant_cost',
        'status'
    ];

    protected $casts = [
        'adult_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'infant_price' => 'decimal:2',
        'adult_cost' => 'decimal:2',
        'child_cost' => 'decimal:2',
        'infant_cost' => 'decimal:2',
        'status' => 'integer',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the miscellaneous item
     */
    public function item()
    {
        return $this->belongsTo(MiscellaneousItem::class, 'mis_id', 'mis_id');
    }

    /**
     * Scope to get only active prices
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
