<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tour;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders'; 
    protected $guarded = [];
    protected $casts = [
        'data' => 'json', // Ensures Laravel treats 'data' column as JSON
    ];
    
    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }
}
