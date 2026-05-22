<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'zone_name',
        'zone_type',
        'vehicle_type',
        'description',
        'city',
        'status',
        'dmc_id',
        'created_by',
        'deleted_at',
    ];

    public function cities()
    {
        return $this->belongsTo(City::class, 'city', 'city_id');
    }
}
