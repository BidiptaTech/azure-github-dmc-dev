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
        'share_contact',
        'passport',
        'passport_exp',
        'salutation',

        // Extended guest-profile fields
        'guest_id2',
        'languages',
        'occupation',
        'address_line1',
        'address_line2',
        'city',
        'state_region',
        'postal_code',
        'country',
        'phone',
        'email2',
        'country_of_residence',
        'name',
        'relationship',
        'phone_number',
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'full_name',
        'date_of_birth',
        'gender',
        'allergies',
        'disabilities',
        'blood_group',
        'passport_number',
        'passport_nationality',
        'passport_issue_date',
        'passport_expiry_date',
        'payment_passport_details',
        'government_approved_id',
        'seat_preference',
        'room_preference',
        'meal_preference',
        'dietary_type',
        'personalization',
        'favorite_place',
        'travel_bucket_list',
        'favourite_cuisine',
        'favourite_colour',
        'dream_destination',
        'travel_mood',
        'favourite_travel_companion',
        'best_travel_memory',
        'uploaded_images',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tour_id' => 'array',
        'passport_exp' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',

        // Extended guest-profile casts
        'date_of_birth' => 'date',
        'passport_issue_date' => 'date',
        'passport_expiry_date' => 'date',
        'payment_passport_details' => 'array',
        'government_approved_id' => 'array',
        'room_preference' => 'array',
        'personalization' => 'array',
        'uploaded_images' => 'array',
    ];

    /**
     * Expire active Sanctum tokens for this guest (e.g. after app password change).
     */
    public function invalidateAccessTokens(): void
    {
        if (!$this->id) {
            return;
        }

        \Laravel\Sanctum\PersonalAccessToken::where('tokenable_type', self::class)
            ->where('tokenable_id', $this->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->update(['expires_at' => now()]);
    }

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
