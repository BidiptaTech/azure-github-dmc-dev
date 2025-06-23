<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Package;
use App\Models\User;

class PackageBooking extends Model
{
    use HasFactory;
    
    protected $table = 'package_bookings';
    protected $guarded = [];
    
    /**
     * Get the package associated with the booking
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'package_id');
    }
    
    /**
     * Get the user who made the booking
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Get the total number of travelers
     */
    public function getTotalTravelersAttribute()
    {
        return $this->adult_count + $this->child_count + $this->senior_count;
    }
}
