<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'guests';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'guest_id',
        'tour_id',
        'guest_name',
        'email',
        'country_code',
        'contact',
        'whatsapp_no',
        'app_password',
        'image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tour_id' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    /**
     * Add a tour ID to the tour_id array
     */
    public function addTourId($tourId)
    {
        // Get existing tour_id and ensure it's an array
        $tourIds = $this->tour_id;
        
        // Handle case where tour_id might be a string (old data)
        if (is_string($tourIds)) {
            // If it's a numeric string, convert to integer and wrap in array
            if (is_numeric($tourIds)) {
                $tourIds = [(int)$tourIds];
            } else {
                // Otherwise just wrap in array
                $tourIds = [$tourIds];
            }
        } elseif (!is_array($tourIds)) {
            // If it's null or other type, initialize as empty array
            $tourIds = [];
        }
        
        // Convert to integer if numeric
        $tourId = is_numeric($tourId) ? (int)$tourId : $tourId;
        
        if (!in_array($tourId, $tourIds, true)) {
            $tourIds[] = $tourId;
            $this->tour_id = $tourIds;
            $this->save();
        }
        
        return $this;
    }
    
    /**
     * Remove a tour ID from the tour_id array
     */
    public function removeTourId($tourId)
    {
        // Get existing tour_id and ensure it's an array
        $tourIds = $this->tour_id;
        
        // Handle case where tour_id might be a string (old data)
        if (is_string($tourIds)) {
            if (is_numeric($tourIds)) {
                $tourIds = [(int)$tourIds];
            } else {
                $tourIds = [$tourIds];
            }
        } elseif (!is_array($tourIds)) {
            $tourIds = [];
        }
        
        // Convert to integer if numeric for proper comparison
        $tourId = is_numeric($tourId) ? (int)$tourId : $tourId;
        
        $tourIds = array_values(array_filter($tourIds, function($id) use ($tourId) {
            return $id !== $tourId;
        }));
        
        $this->tour_id = $tourIds;
        $this->save();
        
        return $this;
    }
    
    /**
     * Check if guest has a specific tour ID
     */
    public function hasTourId($tourId)
    {
        // Get existing tour_id and ensure it's an array
        $tourIds = $this->tour_id;
        
        // Handle case where tour_id might be a string (old data)
        if (is_string($tourIds)) {
            if (is_numeric($tourIds)) {
                $tourIds = [(int)$tourIds];
            } else {
                $tourIds = [$tourIds];
            }
        } elseif (!is_array($tourIds)) {
            $tourIds = [];
        }
        
        // Convert to integer if numeric for proper comparison
        $tourId = is_numeric($tourId) ? (int)$tourId : $tourId;
        
        return in_array($tourId, $tourIds, true);
    }
}
