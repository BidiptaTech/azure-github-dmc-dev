<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmartAppNtf extends Model
{
    protected $table = 'smartApp_Ntf';

    protected $guarded = [];

    protected $casts = [
        'receiver' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
