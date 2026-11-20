<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItinerarySetting extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'itinerary_settings';

    protected $guarded = [];

    protected $casts = [
        'emergency_contacts' => 'array',
        'sic_timings' => 'array',
        'meeting_points' => 'array',
        'dmc_id' => 'integer',
        'itinerary_setting_id' => 'integer',
    ];
}

