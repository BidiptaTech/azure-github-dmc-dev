<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agency extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'agencies';

    protected $fillable = [
        'agency_id',
        'agency_name',
        'email',
        'phone',
        'country',
        'city',
        'address',
        'postal_code',
        'branches',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'branches' => 'array', // Cast JSON to array
        'status' => 'boolean',
    ];

    protected $dates = [
        'deleted_at',
    ];

    /**
     * Get the user who created this agency
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'userId');
    }

    /**
     * Get the user who last updated this agency
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'userId');
    }

    /**
     * Scope to get only active agencies
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get the total number of branches (including head office)
     */
    public function getTotalBranchesAttribute()
    {
        $branches = $this->branches ?? [];
        return count($branches) + 1; // +1 for head office
    }

    /**
     * Get only the branch data (excluding head office)
     */
    public function getBranchesOnlyAttribute()
    {
        return $this->branches ?? [];
    }

    /**
     * Check if agency has branches
     */
    public function hasBranches()
    {
        return !empty($this->branches);
    }
} 