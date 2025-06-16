<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attraction extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'attractions'; 
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }

    public function companyname()
    {
        return $this->belongsTo(User::class, 'dmc_id', 'userId');
    }
}
