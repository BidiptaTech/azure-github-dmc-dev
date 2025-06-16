<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'restaurants'; 
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }

    public function companyname()
    {
        return $this->belongsTo(User::class, 'dmc_id', 'userId');
    }

    public function hotel(){
        return $this->belongsTo(Hotel::class, 'hotel_id', 'hotel_unique_id');
    }

    public function meals()
    {
        return $this->hasMany(Meal::class, 'restaurant_id', 'restaurant_id');
    }

    protected $casts = [
        'close_days' => 'array',
        'close_dates' => 'array',
    ];
}
