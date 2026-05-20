<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LostFound extends Model
{
    protected $table = 'lost_found';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'resolved' => 'boolean',
        'images' => 'array',
        'guest_images' => 'array',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }
}
