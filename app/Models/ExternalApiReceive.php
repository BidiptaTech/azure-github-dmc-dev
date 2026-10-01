<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalApiReceive extends Model
{
    use HasFactory;

    protected $table = 'external_api_receives';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
    ];
}
