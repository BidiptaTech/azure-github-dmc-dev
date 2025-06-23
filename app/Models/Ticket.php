<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tickets';
    
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'ticket_id',
        'name',
        'description',
        'child_price',
        'adult_price',
        'senior_adult_price',
        'status',
        'created_by',
        'updated_by'
    ];

    // Cast decimal fields to float
    protected $casts = [
        'child_price' => 'float',
        'adult_price' => 'float',
        'senior_adult_price' => 'float',
        'status' => 'integer',
    ];
}
