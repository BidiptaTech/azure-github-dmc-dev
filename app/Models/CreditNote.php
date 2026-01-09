<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'credit_notes';
    
    protected $fillable = [
        'credit_note_id',
        'invoice_id',
        'tour_id',
        'dmc_id',
        'credit_note_number',
        'credit_note_date',
        'reason',
        'reason_description',
        'currency',
        'credit_amount',
        'gst_amount',
        'total_credit',
        'refund_status',
        'refund_date',
        'refund_details',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'credit_note_date' => 'date',
        'refund_date' => 'date',
        'credit_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    /**
     * Get the primary key for the model.
     */
    public function getKeyName()
    {
        return 'credit_note_id';
    }

    /**
     * Relationships
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }

    public function dmc()
    {
        return $this->belongsTo(User::class, 'dmc_id', 'userId');
    }
}
