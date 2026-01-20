<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DefaultValue extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'default_value';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'default_id',
        'dmc_id',
        'name',
        'service_id',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the DMC that owns the default value.
     */
    public function dmc()
    {
        return $this->belongsTo(User::class, 'dmc_id', 'userId');
    }

    /**
     * Get the hotel if the name is hotel.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'service_id', 'hotel_unique_id');
    }

    /**
     * Get the restaurant if the name is restaurant.
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'service_id', 'restaurant_id');
    }

    /**
     * Get the attraction if the name is attraction.
     */
    public function attraction()
    {
        return $this->belongsTo(Attraction::class, 'service_id', 'attraction_id');
    }

    /**
     * Get the vehicle (car) if the name is car_private or car_shared.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'service_id', 'vehicle_id');
    }

    /**
     * Get the port if the name is port.
     */
    public function port()
    {
        return $this->belongsTo(Port::class, 'service_id', 'port_id');
    }

    /**
     * Get the guide if the name is guide.
     */
    public function guide()
    {
        return $this->belongsTo(Guide::class, 'service_id', 'guide_id');
    }

    /**
     * Get the related service based on the name field.
     */
    public function getServiceAttribute()
    {
        switch ($this->name) {
            case 'hotel':
                return $this->hotel;
            case 'restaurant':
                return $this->restaurant;
            case 'attraction':
                return $this->attraction;
            case 'car_private':
            case 'car_shared':
                return $this->vehicle;
            case 'port':
                return $this->port;
            case 'guide':
                return $this->guide;
            default:
                return null;
        }
    }

    /**
     * Get the display name for the service type.
     */
    public function getServiceTypeDisplayName()
    {
        $names = [
            'hotel' => 'Hotel',
            'restaurant' => 'Restaurant',
            'attraction' => 'Attraction',
            'car_private' => 'Car (Private)',
            'car_shared' => 'Car (Shared)',
            'port' => 'Port',
            'guide' => 'Guide',
        ];

        return $names[$this->name] ?? $this->name;
    }
}

