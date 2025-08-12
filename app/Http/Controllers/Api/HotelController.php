<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Category;
use App\Models\Facility;
use App\Models\User;
use App\Models\Room;
use App\Models\Rate;
use App\Models\Tour;
use App\Models\Bed;
use App\Models\Agent;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use App\Helpers\CommonHelper;
use App\Models\HotelPolicy;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;

class HotelController extends Controller
{
    /*
    * Show Hotel & Feature Category.
    * Date 07-11-2024
    */
    public function category()
    {
        $categories = Category::where('status', 1)->where('category_type', 1)->get();
        if ($categories->isEmpty()) {
            return response()->json([
                'message' => 'No category found'
            ], 404);
        }
        $category_list = $categories->map(function ($category) {
            return [
                'id' => $category->category_id,
                'category_name' => $category->name,
                'status' => $category->status,
            ];
        });
        return response()->json($category_list);
    }

    /*
    * Show Facilities.
    * Date 07-11-2024
    */
    public function facilities(Request $request)
    {
        $categories = Category::where('status', 1)
            ->where('category_type', 2)
            ->with(['facilities' => function ($query) {
                $query->where('status', 1); 
            }])
            ->get();
            
        if ($categories->isEmpty()) {
            return response()->json([
                'message' => 'No Categories with facilities found'
            ], 404);
        }
        $category_facilities = $categories->map(function ($category) {
            return [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'facilities' => $category->facilities->map(function ($facility) {
                    return [
                        'id' => $facility->id,
                        'facility_name' => $facility->name,
                        'status' => $facility->status,
                    ];
                }),
            ];
        });
        return response()->json($category_facilities);
    }

    /*
    * Show Hotel Listings.
    * Date 06-11-2024
    */
    public function index(Request $request)
    {
        $location = $request->input('location'); 
        $cat_id = $request->input('category_id');
        $start = $request->input('start', 0);
        $limit = $request->input('limit', 9);
        
        // Handle both JSON string and array inputs for dmc_ids
        $request_dmc_ids = $request->input('dmc_id');
        if (is_string($request_dmc_ids)) {
            $request_dmc_ids = json_decode($request_dmc_ids, true) ?? [];
        } elseif (!is_array($request_dmc_ids)) {
            $request_dmc_ids = [];
        }

        if (!$location) {
            return response()->json(['message' => 'City not found'], 404);
        }

        if (empty($request_dmc_ids)) {
            return response()->json(['message' => 'DMC IDs are required'], 400);
        }

        $query = Hotel::with('category', 'rooms')
            ->where('status', 1)
            ->where('is_active', 1)
            ->where('is_complete', 1);

        if ($location) {
            $query->where(function ($q) use ($location) {
                $q->whereRaw('LOWER(address) = ?', [strtolower($location)])
                    ->orWhereRaw('LOWER(city) = ?', [strtolower($location)]);
            });
        }

        if ($cat_id) {
            $query->where('cat_id', $cat_id);
        }

        $hotels = $query->orderBy('id', 'desc')->get();
        
        // Group hotels by name to handle duplicates
        $hotelsByName = [];
        
        foreach ($hotels as $hotel) {
            $hotel_dmc_ids = $hotel->dmc_id;
            if (is_string($hotel_dmc_ids)) {
                $hotel_dmc_ids = json_decode($hotel_dmc_ids, true) ?? [];
            } elseif (!is_array($hotel_dmc_ids)) {
                $hotel_dmc_ids = [];
            }

            $matching_dmc_ids = array_intersect($request_dmc_ids, $hotel_dmc_ids);

            if (empty($matching_dmc_ids)) {
                continue;
            }

            $city = $hotel->city ?? '';
            $country = $hotel->country ?? '';
            $check_country = Country::whereRaw('LOWER(name) = ?', [strtolower($country)])->first();
            $country_tax = $check_country->tax_percentage ?? 0;

            $base_price = PHP_INT_MAX;
            $base_room_price = 0;
            $weekend_days = json_decode($hotel->weekend_days, true) ?? [];
            $today = Carbon::now()->format('l');

            foreach ($hotel->rooms as $room) {
                if (!in_array($room->created_by, $matching_dmc_ids)) {
                    continue;
                }

                $price = in_array($today, $weekend_days) ? $room->weekend_price : $room->weekday_price;
                $base_price = min($base_price, $price);
                
                if ($room->base_room == 1) {
                    $base_room_price = $room->weekday_price;
                }
            }
            $base_price = ($base_price === PHP_INT_MAX) ? 0 : $base_price;

            // Calculate prices for each matching DMC
            foreach ($matching_dmc_ids as $current_dmc_id) {
                $dmcResult = CommonHelper::calculateDmcModePricehotel(
                    $base_price, $current_dmc_id, $hotel->name, 'hotel', $city
                );
                
                $current_price = $dmcResult[0] ?? 0;
                
                // Skip if price is 0
                if ($current_price == 0) {
                    continue;
                }

                // Calculate TravClicks price
                $travResult = CommonHelper::calculateMinPricehotel(
                    $base_price, $current_dmc_id, $hotel->name, 'hotel', $city
                );

                $hotelData = [
                    'id' => $hotel->hotel_unique_id ?? null,
                    'hotel_name' => $hotel->name,
                    'category' => $hotel->category->name ?? '',
                    'location' => $hotel->address ?? '',
                    'dmc_price' => (int) $current_price,
                    'dmc_tax_amount' => ((int) $current_price * ($country_tax ?? 0) / 100),
                    'dmc_id' => $dmcResult[1] ?? $current_dmc_id,
                    'travclicks_price' => $travResult[0] ?? 0,
                    'travclicks_tax_amount' => ((int)($travResult[0] ?? 0) * ($country_tax ?? 0) / 100),
                    'travclicks_id' => $travResult[1] ?? null,
                    'image' => $hotel->main_image ?? '',
                    'site_image' => json_decode($hotel->images, true) ?? [],
                    'room_image' => collect($hotel->rooms)
                        ->pluck('images')
                        ->map(fn($img) => json_decode($img, true))
                        ->filter()
                        ->values()
                        ->all(),
                    'cancellation' => $hotel->cancellation_type ?? '',
                    'cancellation_charge' => json_decode($hotel->cancellation_data) ?? [],
                    'facilities' => $this->getFacilitiesByCategory($hotel),
                    'description' => strip_tags($hotel->description ?? ''),
                    'status' => $hotel->status ?? 0,
                    'base_hotel_price' => $base_room_price,
                ];

                // Store hotel data with its price for comparison
                if (!isset($hotelsByName[$hotel->name]) || $current_price < $hotelsByName[$hotel->name]['dmc_price']) {
                    $hotelsByName[$hotel->name] = $hotelData;
                }
            }
        }

        // Convert to array and apply pagination
        $hotel_list = array_values(array_slice($hotelsByName, $start, $limit));

        if (!empty($hotel_list)) {
            return response()->json($hotel_list);
        }

        return response()->json(['message' => 'No hotels found'], 404);
    }
    
    /*
    * Show Hotel Details.
    * Date 07-11-2024
    */
    public function details(Request $request)
    {
        $get_tour_id = $request->query('tour-id');
        $dmcs_id = $request->query('dmc_id');
        $price_mode = $request->query('price-mode');
        if(!$get_tour_id){
            return response()->json(['message' => 'Tour Id required'], 404);
        }
        if(!$dmcs_id){
            return response()->json(['message' => 'Dmc Id required'], 404);
        }
        
        if(!$price_mode){
            return response()->json(['message' => 'Price Mode required'], 404);
        }
        $agent_id = Auth::user()->id;
        $id = $request->query('id');
        // Fetch the hotel with its relationships, filtering rooms by DMC ID
        $hotel = Hotel::with([
            'hotelPolicy',
            'category', 
            'rooms' => function($query) use ($dmcs_id) {
                $query->where('created_by', $dmcs_id);
            },
            'rooms.beds'
        ])->where('hotel_unique_id', $id)
        ->first();
        if (!$hotel) {
            return response()->json(['message' => 'No hotels found'], 404);
        }
        // Country Tax Calculation
        $country_tax = 0;
        if (!empty($hotel->country)) {
            $check_country = Country::whereRaw('LOWER(name) = ?', [strtolower($hotel->country)])->first();
            $country_tax = $check_country->tax_percentage ?? 0;
        }


        // Hotel Images
        $site_image = json_decode($hotel->images, true) ?? [];

        // Facilities
        $facility_ids = json_decode($hotel->facilities, true) ?? [];
        $facilities = Facility::with('categories')->whereIn('facilityId', $facility_ids)->get();

        $categorized_facilities = [];
        foreach ($facilities as $facility) {
            $category_name = $facility->categories ? $facility->categories->name : 'Uncategorized';
            $category_id = $facility->categories ? $facility->categories->category_id : 0;
            
            if (!isset($categorized_facilities[$category_id])) {
                $categorized_facilities[$category_id] = [
                    'category_name' => $category_name,
                    'facilities' => []
                ];
            }
            
            $categorized_facilities[$category_id]['facilities'][] = $facility->name;
        }

        // Convert to array format for JSON response
        $facilities_by_category = [];
        foreach ($categorized_facilities as $cat) {
            $facilities_by_category[] = [
                'category' => $cat['category_name'],
                'facilities' => $cat['facilities']
            ];
        }

        // Fetch applicable rates
        $currentDate = Carbon::today();

        $rate = Rate::where('hotel_id', $id)->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->orderByRaw("
                CASE
                    WHEN event_type = 'Blackout Date' THEN 1
                    WHEN event_type = 'Fair Date' THEN 2
                    WHEN event_type = 'Season' THEN 3
                    ELSE 4 
                END
            ")->first();

        $base_price = PHP_INT_MAX;
        $double_base_price = PHP_INT_MAX;
        $room_data = [];
        $weekend_days = json_decode($hotel->weekend_days, true) ?? [];
        $today = Carbon::now()->format('l');

        // $restaurant = [];
        // $restaurant = Restaurant::where('owned_by', $hotel->hotel_unique_id)->first();
        // $bf_price = $restaurant ? $restaurant->bf_price : 0; // Fetch breakfast price
        // $lunch_price = $restaurant ? $restaurant->lunch_price : 0; // Fetch lunch price
        // $dinner_price = $restaurant ? $restaurant->dinner_price : 0; // Fetch dinner price

        // Apply DMC markup to breakfast price if applicable
        // $users = User::where('userId', $dmcs_id)->first();
        // $dmc = User::where('userId', $users->dmcId)->first();
        // if ($users) {
        //     if ($dmc) {
        //         $bf_price += ($dmc->markup_type == 0) 
        //         ? $dmc->markup_price 
        //         : ($bf_price * $dmc->markup_price / 100);

        //         $lunch_price += ($dmc->markup_type == 0) 
        //         ? $dmc->markup_price 
        //         : ($lunch_price * $dmc->markup_price / 100);

        //         $dinner_price += ($dmc->markup_type == 0) 
        //         ? $dmc->markup_price 
        //         : ($dinner_price * $dmc->markup_price / 100);
        //     }
        // }

        // Separate array for breakfast price
        // $breakfast_data = [
        //     'bf_price' => $bf_price
        // ];
        
        // Iterate through rooms
        foreach ($hotel->rooms as $room) {
            $bed_data = [];
            $price = in_array($today, $weekend_days) ? $room->weekend_price : $room->weekday_price;
            $double_price = in_array($today, $weekend_days) ? $room->double_weekend_price : $room->double_weekday_price;
            if ($rate) {
                if($rate->event_type == "Blackout Date"){
                    $price = $rate->price + $room->varient_price;
                    $double_price = $rate->price + $room->varient_price;
                }elseif($rate->event_type == "Fair Date"){
                    $price += ($rate->price + $room->varient_price);
                    $double_price += ($rate->price + $room->varient_price);
                }elseif($rate->event_type == "Season"){
                    $price = in_array($today, $weekend_days) ? $rate->weekend_price : $rate->weekday_price;
                    $double_price = in_array($today, $weekend_days) ? $rate->double_weekend_price : $rate->double_weekday_price;
                }
            }
            // $users = User::where('userId', $dmcs_id)->first();
            // if ($users) {
            //     $dmc = User::where('userId', $users->dmcId)->first();
            //     if ($dmc) {
            //         // Add DMC markup to room prices
            //         $price += ($dmc->markup_type == 0)
            //             ? $dmc->markup_price
            //             : ($price * $dmc->markup_price / 100);
            //         $double_price += ($dmc->markup_type == 0)
            //             ? $dmc->markup_price
            //             : ($double_price * $dmc->markup_price / 100);
                        
            //         // Add DMC markup to meal prices
            //         $room->breakfast_price += ($dmc->markup_type == 0)
            //             ? $dmc->markup_price
            //             : ($room->breakfast_price * $dmc->markup_price / 100);
            //         $room->lunch_price += ($dmc->markup_type == 0)
            //             ? $dmc->markup_price
            //             : ($room->lunch_price * $dmc->markup_price / 100);
            //         $room->dinner_price += ($dmc->markup_type == 0)
            //             ? $dmc->markup_price
            //             : ($room->dinner_price * $dmc->markup_price / 100);
            //     }
            // }
            $base_price = min($base_price, $price);
            $double_base_price = min($double_base_price, $double_price);
            $beds = Bed::where('room_id', $room->room_id)->get();
            $extra_bed_age_limit = $hotel->extra_bed_age_limit;
            $extra_child_age_limit = $hotel->child_age_limit;
            $check_in_time = $hotel->check_in_time;
            $check_out_time = $hotel->check_out_time;
            $tours = Tour::where('tour_id', $get_tour_id)->first();
            if ($tours && $tours->child_ages) {
                $child_ages_array = explode(',', $tours->child_ages);
                $filtered_ages = array_filter($child_ages_array, function ($age) use ($extra_bed_age_limit) {
                    return $age <= $extra_bed_age_limit;
                });

                $filtered_ages_above = array_filter($child_ages_array, function ($age) use ($extra_bed_age_limit) {
                    return $age > $extra_bed_age_limit;
                });

                //For Caluculate Child Age

                $filtered_child_ages = array_filter($child_ages_array, function ($age) use ($extra_child_age_limit) {
                    return $age <= $extra_child_age_limit;
                });

                $filtered_child_ages_above = array_filter($child_ages_array, function ($age) use ($extra_child_age_limit) {
                    return $age > $extra_child_age_limit;
                });

                $filtered_count = count($filtered_ages);
                $filtered_count_above = count($filtered_ages_above);

                $filtered_child_count = count($filtered_child_ages);
                $filtered_child_count_above = count($filtered_child_ages_above);

                $total_count = $tours->adult - $filtered_count;
            } else {
                $filtered_count = 0;
                $filtered_count_above = 0;

                $filtered_child_count = 0;
                $filtered_child_count_above = 0;
                $total_count = 0;
            }
            $tour_adult = ($tours->adult + $filtered_count_above) ?? 0;
            // $tour_adult = $tours->adult + $tours->child + $tours->infant;

            // Process beds
            foreach ($beds as $bed) {
                $extra_bed_price = $bed->extra_bed_price;
                $baby_cot_price = $bed->baby_cot_price;
                
                // if ($users) {
                //     if ($dmc) {
                //         $extra_bed_price += ($dmc->markup_type == 0)
                //             ? $dmc->markup_price
                //             : ($bed->extra_bed_price * $dmc->markup_price / 100);
                //         $baby_cot_price += ($dmc->markup_type == 0)
                //             ? $dmc->markup_price
                //             : ($bed->baby_cot_price * $dmc->markup_price / 100);
                //     }
                // }
                
                $bed_data[] = [
                    'bed_id' => $bed->bed_id,
                    'room_id' => $room->room_id,
                    'bed_type' => $bed->room_type,
                    'max_occupancy' => $bed->max_occupancy,
                    'adult_count' => $bed->adult_count,
                    'child_count' => $bed->child_count,
                    'extra_bed' => $bed->extra_bed,
                    'extra_bed_price' => $extra_bed_price,
                    'baby_cot' => $bed->baby_cot,
                    'baby_cot_price' => $baby_cot_price,
                ];
            }

            if ($base_price === PHP_INT_MAX) {
                $base_price = 0;
            }
            if ($double_base_price === PHP_INT_MAX) {
                $double_base_price = 0;
            }
            $room_data[] = [
                'room_id' => $room->room_id,
                'room_type' => $room->room_type,
                'room_image' => json_decode($room->images, true) ?? [],
                'number_of_room' => $room->no_of_room ?? '',
                'variant_price' => $room->varient_price ?? 0,
                'complemenatry_breakfast_included' =>$room->breakfast_included,
                'breakfast' => $room->breakfast,
                'breakfast_type' => $room->breakfast_type ?? '',
                'breakfast_price' => $room->breakfast_price ?? 0,
                'lunch' => $room->lunch,
                'lunch_type' => $room->lunch_type ?? '',
                'lunch_price' => $room->lunch_price,
                'dinner' => $room->dinner,
                'dinner_type' => $room->dinner_type ?? '',
                'dinner_price' => $room->dinner_price ?? 0,
                'tax_percentage' => $country_tax,
                'single_price' => $base_price ?? 0,
                'double_price' => $double_base_price ?? 0,
                'child_extraBed_person_count' => $filtered_count,
                'bed_details' => $bed_data,
                'rooms_only' => $room->rooms_only,
            ];
        }
        $hotelPolicy = $hotel->hotelPolicy->first();
        $policy = $hotel->first();

        $hotel_policy[] = [
            'property_policy' => $hotelPolicy ? [
                // 'id' => $hotelPolicy->hotel_id,
                'name' => $hotelPolicy->name,
                'policy' => strip_tags($hotelPolicy->policy),
                'file' => $hotelPolicy->file ? asset('storage/' . $hotelPolicy->file) : '',
                // 'hotel_id' => $hotelPolicy->hotel_id,
                // 'check_in_time' => CommonHelper::DateFormat($hotelPolicy->check_in_time),
                // 'check_in_until' => CommonHelper::DateFormat($hotelPolicy->check_in_until),
                // 'check_out_time' => CommonHelper::DateFormat($hotelPolicy->check_out_time),
                // 'check_out_until' => CommonHelper::DateFormat($hotelPolicy->check_out_until),
                'extras' => $hotelPolicy->extras,
                'property' => $hotelPolicy->property
            ] : null,

            'cancellation_policy' => $hotel ? [
                // 'id' => $policy->id,
                // 'name' => $policy->name,
                'policy' => strip_tags($hotel->policy),
                'cancellation_pdf' => $hotel->cancellation_pdf ? asset('storage/' . $hotel->cancellation_pdf) : '',
                // 'cancel_policy' => $hotel->cancel_policy,
                'cancellation_type' => $hotel->cancellation_type,
                //'cancellation_data' => json_decode($policy->cancellation_data, true) ?? [],
                ] : null,

            'refund_policy' => $hotel ? [
                // 'name' => $hotel->name,
                'refundpolicy' => strip_tags($hotel->refundpolicy),
                'refundpolicy_pdf' => $hotel->refundpolicy_pdf ? asset('storage/' . $hotel->refundpolicy_pdf) : '',
                ] : null,

            'child_policy' => $hotel ? [
                // 'name' => $hotel->name,
                'childpolicy' => strip_tags($hotel->childpolicy),
                'childpolicy_pdf' => $hotel->childpolicy_pdf ? asset('storage/' . $hotel->childpolicy_pdf) : '',
                ] : null,

            'pet_policy' => $hotel ? [
                // 'name' => $hotel->name,
                'petpolicy' => strip_tags($hotel->petpolicy),
                'pet_allowed' => (bool) $hotel->pet_allowed, // force boolean true/false
                'petpolicy_pdf' => $hotel->petpolicy_pdf ? asset('storage/' . $hotel->petpolicy_pdf) : '',
                ] : null,

            'terms_policy' => $hotel ? [
                // 'name' => $hotel->name,
                'termspolicy' => strip_tags($hotel->termspolicy),
                //'pet_allowed' => (bool) $hotel->pet_allowed, // force boolean true/false
                'termspolicy_pdf' => $hotel->termspolicy_pdf ? asset('storage/' . $hotel->termspolicy_pdf) : '',
                ] : null
        ];

        // $policy[] = [
        //     'hotel_policy' => $hotel_policy,
        // ]; 
        // return response()->json($policy); 
        $hotel_list = [
            'id' => $hotel->hotel_unique_id,
            'hotel_name' => $hotel->name,
            'category' => $hotel->category->name ?? 'N/A',
            'image' => $hotel->main_image ?? '',
            'tour_male' => $tours->male_count ?? 0,
            'tour_female' => $tours->female_count ?? 0,
            'tour_adult' => $tours->adult + $tours->child ?? 0,
            'all_pax'=> $tours->adult + $tours->child + $tours->infant ?? 0,
            'tour_child'=> ($tours->child - $filtered_child_count_above) ?? 0,
            'tour_child_adult' => $filtered_child_count_above ?? 0,
            'check_in_time' => $check_in_time,
            'check_out_time' => $check_out_time,
            'tour_infant'=> $tours->infant ?? 0,
            'price_mode' => $price_mode,
            'latitude' => $hotel->latitude ?? '',
            'longitude' => $hotel->longitude ?? '',
            'zipcode' => $hotel->zipcode ?? '',
            'address' => $hotel->address ?? '',
            'city' => $hotel->city ?? '',
            'state' => $hotel->state ?? '',
            'country' => $hotel->country ?? '',
            'hotel_owner_company_name' => $hotel->hotel_owner_company_name ?? '',
            'entry_port' => json_decode($hotel->port_of_entry) ?? '',
            'exit_port' => json_decode($hotel->port_of_exit) ?? '',
            'other_port' => json_decode($hotel->others) ?? '',
            'policy' => $hotel_policy,

            'room_data' => $room_data,
            'facilities_by_category' => $facilities_by_category,
            'description' => strip_tags($hotel->description),
            
            // 'breakfast' => $breakfast_data,
        ];
        return response()->json($hotel_list);
    }
    
    /**
     * Check if a DMC ID matches the stored dmc_id field (handles both integer and JSON array formats)
     * 
     * @param mixed $storedDmcId The dmc_id field from database (can be integer or JSON array)
     * @param int $targetDmcId The DMC ID to check for
     * @return bool
     */
    private function isDmcIdMatch($storedDmcId, $targetDmcId)
    {
        // Handle both old integer format and new JSON array format for dmc_id
        $dmcIds = [];
        
        if (is_string($storedDmcId)) {
            // Try to decode as JSON first
            $decoded = json_decode($storedDmcId, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $dmcIds = array_map('intval', $decoded);
            } else {
                // If not valid JSON, treat as single integer string
                $dmcIds = [(int)$storedDmcId];
            }
        } elseif (is_array($storedDmcId)) {
            // If it's already an array (Laravel auto-cast)
            $dmcIds = array_map('intval', $storedDmcId);
        } else {
            // If it's already an integer
            $dmcIds = [(int)$storedDmcId];
        }
        
        return in_array((int)$targetDmcId, $dmcIds);
    }

    /**
     * Get facilities grouped by category
     */
    private function getFacilitiesByCategory($hotel)
    {
        $facility_ids = json_decode($hotel->facilities, true) ?? [];
        $facilities = Facility::with('categories')->whereIn('facilityId', $facility_ids)->get();
        
        $categorized_facilities = [];
        foreach ($facilities as $facility) {
            $category_name = $facility->categories ? $facility->categories->name : 'Uncategorized';
            $category_id = $facility->categories ? $facility->categories->category_id : 0;
            
            if (!isset($categorized_facilities[$category_id])) {
                $categorized_facilities[$category_id] = [
                    'category_name' => $category_name,
                    'facilities' => []
                ];
            }
            
            $categorized_facilities[$category_id]['facilities'][] = $facility->name;
        }

        $facilities_by_category = [];
        foreach ($categorized_facilities as $cat) {
            $facilities_by_category[] = [
                'category' => $cat['category_name'],
                'facilities' => $cat['facilities']
            ];
        }

        return $facilities_by_category;
    }
}