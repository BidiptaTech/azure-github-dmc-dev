<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $table = 'taxes';
    
    protected $primaryKey = 'tax_id';

    protected $fillable = [
        'tax_name',
        'tax_type',
        'tax_value',
        'calculate_on',
        'country',
        'city',
        'dmc_id',
        'is_active',
        'description',
        'if_fixed'
    ];

    protected $casts = [
        'tax_value' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the DMC user that owns the tax
     */
    public function dmc()
    {
        return $this->belongsTo(User::class, 'dmc_id', 'userId');
    }
}

