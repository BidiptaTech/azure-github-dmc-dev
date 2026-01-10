<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MiscellaneousItem extends Model
{
    use SoftDeletes;

    protected $table = 'miscellaneous_items';
    protected $primaryKey = 'mis_id';

    protected $fillable = [
        'item_name',
        'description',
        'image',
        'status'
    ];

    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get all prices for this item across different DMCs
     */
    public function prices()
    {
        return $this->hasMany(MiscellaneousPrice::class, 'mis_id', 'mis_id');
    }

    /**
     * Get price for a specific DMC (relationship)
     */
    public function priceForDmc()
    {
        return $this->hasOne(MiscellaneousPrice::class, 'mis_id', 'mis_id');
    }

    /**
     * Scope to get only active items
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get items with prices for a specific DMC
     */
    public static function getItemsForDmc($dmcId)
    {
        return self::active()
            ->with(['priceForDmc' => function($query) use ($dmcId) {
                $query->where('dmc_id', $dmcId)->where('status', 1);
            }])
            ->get();
    }
}
