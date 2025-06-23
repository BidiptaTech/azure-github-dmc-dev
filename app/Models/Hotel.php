<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'hotels'; 
    protected $guarded = []; 

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'hotel_id', 'hotel_unique_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id', 'category_id');
    }

    public function companyname()
    {
        return $this->belongsTo(User::class, 'dmc_id', 'userId');
    }

    public function hotelPolicy()
    {
        return $this->hasMany(HotelPolicy::class, 'hotel_id', 'hotel_unique_id');
    }

}
