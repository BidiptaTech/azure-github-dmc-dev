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
    ];
    use SoftDeletes;
    
    // Automatically load the tour relationship when retrieving orders
    protected $with = ['tour'];

    protected static function booted()
    {
        static::saving(function (Order $order) {
            if (empty($order->country)) {
                $inferred = \App\Helpers\CommonHelper::inferCountryFromOrderPayload($order->data ?? null);
                if (empty($inferred) && !empty($order->tour_id)) {
                    $destination = Tour::where('tour_id', $order->tour_id)->value('destination');
                    $countries = \App\Helpers\CommonHelper::resolveDestinationPartsToCountries((string) $destination);
                    if (count($countries) === 1) {
                        $inferred = $countries[0];
                    }
                }
                if (!empty($inferred)) {
                    $order->country = $inferred;
                }
            }

            if (empty($order->currency) && !empty($order->country) && !str_contains((string) $order->country, ',')) {
                $currency = Country::where('name', $order->country)->value('currency');
                if (!empty($currency)) {
                    $order->currency = strtoupper(trim((string) $currency));
                }
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
