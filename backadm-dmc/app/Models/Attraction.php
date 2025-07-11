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

    protected $casts = [
        'dmc_id' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }

    public function companyname()
    {
        return $this->belongsTo(User::class, 'dmc_id', 'userId');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'attraction_id', 'attraction_id');
    }

    /**
     * Add a DMC ID to the dmc_id array
     */
    public function addDmcId($dmcId)
    {
        $dmcIds = $this->getDmcIdsArray();
        if (!in_array($dmcId, $dmcIds)) {
            $dmcIds[] = $dmcId;
            $this->dmc_id = $dmcIds;
            $this->save();
        }
        return $this;
    }

    /**
     * Remove a DMC ID from the dmc_id array
     */
    public function removeDmcId($dmcId)
    {
        $dmcIds = $this->getDmcIdsArray();
        $dmcIds = array_values(array_filter($dmcIds, function($id) use ($dmcId) {
            return $id != $dmcId;
        }));
        $this->dmc_id = $dmcIds;
        $this->save();
        return $this;
    }

    /**
     * Check if a DMC has selected this attraction
     */
    public function hasSelectedByDmc($dmcId)
    {
        $dmcIds = $this->getDmcIdsArray();
        return in_array($dmcId, $dmcIds);
    }

    /**
     * Get all DMC IDs that have selected this attraction
     */
    public function getSelectedDmcIds()
    {
        return $this->getDmcIdsArray();
    }

    /**
     * Helper method to get dmc_id as array, handling both integer and array formats
     */
    private function getDmcIdsArray()
    {
        $dmcId = $this->dmc_id;
        
        if (is_null($dmcId)) {
            return [];
        }
        
        if (is_array($dmcId)) {
            return $dmcId;
        }
        
        if (is_string($dmcId)) {
            $decoded = json_decode($dmcId, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return is_array($decoded) ? $decoded : [$decoded];
            }
        }
        
        if (is_numeric($dmcId)) {
            return [$dmcId];
        }
        
        return [];
    }
}
