<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class MultiRestaurant extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'multi_restaurants';

    protected $guarded = [];

    protected $casts = [
        'restaurants' => 'array',
    ];

    /**
     * Get the restaurants as an array (IDs or raw JSON array).
     */
    public function getRestaurantsAsArray(): array
    {
        $restaurants = $this->restaurants;
        if (is_string($restaurants)) {
            $decoded = json_decode($restaurants, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($restaurants) ? $restaurants : [];
    }

    /**
     * Get all Restaurant models related to this package.
     * Expects restaurants JSON to store an array of restaurant IDs.
     */
    public function getRestaurantsList()
    {
        $ids = $this->getRestaurantsAsArray();
        $ids = array_map('intval', array_filter($ids, fn ($id) => is_numeric($id)));
        if (empty($ids)) {
            return collect([]);
        }
        return Restaurant::whereIn('restaurant_id', $ids)
            ->orWhereIn('id', $ids)
            ->get()
            ->unique('restaurant_id')
            ->values();
    }

    /**
     * Find multi restaurants that contain a specific restaurant ID.
     *
     * @param int|string $restaurantId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function containingRestaurantId($restaurantId)
    {
        $restaurantId = (string) $restaurantId;
        return static::whereJsonContains('restaurants', $restaurantId);
    }

    /**
     * Scope: find multi restaurants containing a specific restaurant ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|string $restaurantId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeContainingRestaurant($query, $restaurantId)
    {
        return $query->whereJsonContains('restaurants', (string) $restaurantId);
    }
}
