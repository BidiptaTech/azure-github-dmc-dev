<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'invoice_items';
    
    protected $fillable = [
        'invoice_id',
        'item_type',
        'description',
        'service_details',
        'quantity_adults',
        'quantity_children',
        'quantity_infants',
        'unit_price',
        'total_price',
        'display_order',
    ];

    protected $casts = [
        'service_details' => 'array',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }
}
