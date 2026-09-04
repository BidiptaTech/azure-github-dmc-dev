<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LostFoundResponse extends Model
{
    protected $table = 'lost_found_responses';

    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
    ];

    public function lostFound()
    {
        return $this->belongsTo(LostFound::class, 'lost_found_id');
    }
}
