<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'invoices';
    
    protected $fillable = [
        'invoice_id',
        'tour_id',
        'dmc_id',
        'agent_id',
        'invoice_type',
        'status',
        'invoice_number',
        'proforma_number',
        'invoice_date',
        'due_date',
        'validity_date',
        'client_details',
        'travel_company_details',
        'destination',
        'travel_from_date',
        'travel_to_date',
        'duration_days',
        'no_of_adults',
        'no_of_children',
        'no_of_infants',
        'base_currency',
        'subtotal',
        'gst_amount',
        'service_charge',
        'tourist_tax',
        'total_amount',
        'payment_received',
        'outstanding_balance',
        'currency_conversion',
        'payment_terms',
        'bank_details',
        'notes',
        'terms_and_conditions',
        'sent_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'client_details' => 'array',
        'travel_company_details' => 'array',
        'currency_conversion' => 'array',
        'payment_terms' => 'array',
        'bank_details' => 'array',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'validity_date' => 'date',
        'travel_from_date' => 'date',
        'travel_to_date' => 'date',
        'subtotal' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'tourist_tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payment_received' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
    ];

    /**
     * Get the primary key for the model.
     */
    public function getKeyName()
    {
        return 'invoice_id';
    }

    /**
     * Relationships
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }

    public function dmc()
    {
        return $this->belongsTo(User::class, 'dmc_id', 'userId');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id', 'agent_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id', 'invoice_id');
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class, 'invoice_id', 'invoice_id');
    }

    /**
     * Scope for proforma invoices
     */
    public function scopeProforma($query)
    {
        return $query->where('invoice_type', 'proforma');
    }

    /**
     * Scope for final invoices
     */
    public function scopeFinal($query)
    {
        return $query->where('invoice_type', 'final');
    }

    /**
     * Check if invoice is editable (only proforma invoices)
     */
    public function isEditable()
    {
        return $this->invoice_type === 'proforma' && $this->status !== 'cancelled';
    }

    /**
     * Check if invoice is final (tax invoice)
     */
    public function isFinal()
    {
        return $this->invoice_type === 'final';
    }

    /**
     * Get last negotiated amount from enquiry_comments
     */
    public function getNegotiatedAmount()
    {
        $lastEnquiry = \App\Models\Enquiry::where('tour_id', $this->tour_id)
            ->whereNotNull('amount')
            ->orderBy('enquiry_id', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        
        return $lastEnquiry ? $lastEnquiry->amount : null;
    }
}
