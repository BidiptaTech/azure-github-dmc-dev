<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bank_details';

    protected $fillable = [
        'bank_detail_id',
        'dmc_id',
        'terms_and_conditions',
        'payment_terms',
        'account_name',
        'account_number',
        'bank_address',
        'ifsc',
        'swift_bic_iban',
        'bank_code',
        'branch_code',
        'aba_routing',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payment_terms' => 'array', // Cast JSON to array
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'deleted_at',
    ];

    /**
     * Get the user who created this bank detail
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'userId');
    }

    /**
     * Get the user who updated this bank detail
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'userId');
    }

    /**
     * Get the DMC user
     */
    public function dmc()
    {
        return $this->belongsTo(User::class, 'dmc_id', 'userId');
    }
}
