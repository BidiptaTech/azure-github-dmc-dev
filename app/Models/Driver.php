<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'drivers'; 
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'driver_id', 'driver_id');
    }

    protected $casts = [
        'close_days' => 'array',
        // 'close_dates' => 'array',
    ];
}
