<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tour;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders'; 
    protected $guarded = [];
    protected $casts = [
        'data' => 'json', // Ensures Laravel treats 'data' column as JSON
        'cost_price' => 'array',
    ];
    use SoftDeletes;
    
    // Automatically load the tour relationship when retrieving orders
    protected $with = ['tour'];

    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            // Freeze cost_price at booking time. Only (re)snapshot when:
            // - creating a new order, or
            // - service data/type changed (rebook / edit services).
            // Never overwrite an existing snapshot just because master prices changed.
            $serviceChanged = $order->isDirty('data') || $order->isDirty('type');
            $isCreate = ! $order->exists;

            if (! $isCreate && ! $serviceChanged) {
                return;
            }

            try {
                \App\Helpers\OrderCostPriceHelper::snapshotOntoOrder($order);
            } catch (\Throwable $e) {
                \Log::warning('Failed to build order cost_price', [
                    'booking_id' => $order->booking_id ?? null,
                    'tour_id' => $order->tour_id ?? null,
                    'type' => $order->type ?? null,
                    'error' => $e->getMessage(),
                    'sql_state' => $e->getCode(),
                ]);
            }
        });
    }
    
    public function toArray()
    {
        $array = parent::toArray();
        
        // Get tour display_id and replace tour_id in the response only
        if ($this->tour && $this->tour->display_id) {
            $array['tour_id'] = $this->tour->display_id;
        }
        
        return $array;
    }
    
    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }

    /**
     * Check if booking date has passed and status is not complete/confirmed
     */
    public function isExpiredAndNotConfirmed()
    {
        // If already complete (1) or cancelled (4), don't show as expired
        if (in_array($this->status, [1, 4])) {
            return false;
        }

        $data = is_string($this->data) ? json_decode($this->data, true) : $this->data;
        
        if (!$data) {
            return false;
        }

        // Handle array of bookings
        if (isset($data[0]) && is_array($data[0])) {
            foreach ($data as $item) {
                if (isset($item['bookingDate'])) {
                    $bookingDate = $item['bookingDate'];
                    if (Carbon::parse($bookingDate)->lt(Carbon::now())) {
                        return true;
                    }
                }
            }
        } else {
            // Single booking
            if (isset($data['bookingDate'])) {
                $bookingDate = $data['bookingDate'];
                if (Carbon::parse($bookingDate)->lt(Carbon::now())) {
                    return true;
                }
            }
        }

        return false;
    }
}
