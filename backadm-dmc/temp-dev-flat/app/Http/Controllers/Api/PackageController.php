<?php

namespace App\Http\Controllers\Api;
use App\Models\Package;
use App\Models\Agent;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\Guide;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Auth;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $city = $request->input('city');
        $country = $request->input('country');
        $today = Carbon::today();

        $dmc_id = $this->getDmcIdForCurrentUser();

        if (!$dmc_id) {
            return response()->json(['message' => 'DMC Not Found!'], 400);
        }

        $query = Package::where('status', 1)
            // ->where('created_by', $dmc_id)
            // ->whereDate('start_date', '<=', $today)
            ->whereDate('expire_date', '>=', $today);
        if (!empty($city)) {
            $query->where('city', $city);
        }

        if (!empty($country)) {
            $query->where('destination', $country);
        }

        $packages = $query->select('package_id', 'title', 'destination', 'category', 'duration_days', 'description', 'price_adult', 'max_pax', 'main_image', 'city')->get();
        // Format the response
        return response()->json($packages);
    }

    private function getDmcIdForCurrentUser()
    {
        $user = Auth::user();

        if ($user->agent_id) {
            $agent = Agent::where('agent_id', $user->agent_id)->first();

            if (!$agent) {
                return null;
            }

            switch ($agent->role_id) {
                case 11: // DMC
                    return $agent->sales_manager_dmc;

                case 33: // Sales Head
                    return optional(User::find($agent->sales_manager_dmc))->created_by;

                case 12:
                case 37: // Sales Manager
                    $sm = User::find($agent->sales_manager_dmc);
                    return optional($sm && $sm->created_by ? User::find($sm->created_by) : null)->created_by;

                case 38: // Assistant Manager
                    $am = User::find($agent->sales_manager_dmc);
                    $sm = $am && $am->created_by ? User::find($am->created_by) : null;
                    $sh = $sm && $sm->created_by ? User::find($sm->created_by) : null;
                    return optional($sh)->created_by;
            }
        }

        // If the user is not an agent (e.g., directly SH, SM, AM)
        switch ($user->role_id) {
            case 33: // SH
                return $user->created_by;

            case 37: // SM
                return optional(User::find($user->created_by))->created_by;

            case 38: // AM
                $sm = User::find($user->created_by);
                $sh = $sm && $sm->created_by ? User::find($sm->created_by) : null;
                return optional($sh)->created_by;
        }

        return null;
    }

    public function package_details(Request $request)
    {
        $package_id = $request->input('package_id');
        
        $package = Package::where('package_id', $package_id)
            ->select(
                'package_id', 'title', 'destination', 'category', 'duration_days', 
                'description', 'price_adult', 'price_senior', 'price_child', 
                'max_pax', 'selected_hotels', 'selected_attractions', 'max_hotels', 
                'max_attractions', 'main_image', 'gallery_images', 'inclusions', 
                'exclusions', 'terms_conditions', 'views_count', 
                'rating', 'reviews_count', 'city', 'expire_date', 'start_date', 
                'selected_guide', 'selected_restaurants', 'max_restaurants', 'status'
            )
            ->first();
        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }
        
        // Enhance selected_hotels with image data
        if (!empty($package->selected_hotels)) {
            $hotelIds = collect($package->selected_hotels)->pluck('id')->toArray();
            $hotelNames = collect($package->selected_hotels)->pluck('name')->toArray();
            
            $hotelsData = Hotel::where(function($query) use ($hotelIds) {
                $query->whereIn('hotel_unique_id', $hotelIds);
            })->select('hotel_unique_id', 'name', 'main_image', 'images')->get();
            
            $package->selected_hotels = collect($package->selected_hotels)->map(function($hotel) use ($hotelsData) {
                // Use first with a callback for matching complex conditions
                $hotelData = $hotelsData->first(function($item) use ($hotel) {
                    return $item->hotel_unique_id == $hotel['id'] || 
                           $item->name == $hotel['name'];
                });
                
                if ($hotelData) {
                    $hotel['image'] = $hotelData->main_image 
                        ? (str_starts_with($hotelData->main_image, 'http') 
                            ? $hotelData->main_image 
                            : asset('storage/' . $hotelData->main_image))
                        : null;
                    $hotel['images'] = $hotelData->images 
                        ? collect(json_decode($hotelData->images, true))->map(function($img) {
                            return str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
                        })->toArray()
                        : [];
                }
                
                return $hotel;
            })->toArray();
        }
        
        // Enhance selected_attractions with image data
        if (!empty($package->selected_attractions)) {
            $attractionIds = collect($package->selected_attractions)->pluck('id')->toArray();
            $attractionNames = collect($package->selected_attractions)->pluck('name')->toArray();
            
            $attractionsData = Attraction::where(function($query) use ($attractionIds) {
                $query->whereIn('attraction_id', $attractionIds);
            })->select('attraction_id', 'name', 'master_image', 'additional_image')->get();
            
            $package->selected_attractions = collect($package->selected_attractions)->map(function($attraction) use ($attractionsData) {
                // Use first with a callback for matching complex conditions
                $attractionData = $attractionsData->first(function($item) use ($attraction) {
                    return $item->attraction_id == $attraction['id'] || 
                           $item->name == $attraction['name'];
                });
                
                if ($attractionData) {
                    $attraction['image'] = $attractionData->master_image 
                        ? (str_starts_with($attractionData->master_image, 'http') 
                            ? $attractionData->master_image 
                            : asset('storage/' . $attractionData->master_image))
                        : null;
                    $attraction['images'] = $attractionData->additional_image 
                        ? collect(json_decode($attractionData->additional_image, true))->map(function($img) {
                            return str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
                        })->toArray()
                        : [];
                }
                
                return $attraction;
            })->toArray();
        }
        
        // Enhance selected_guide with image data
        if (!empty($package->selected_guide)) {
            $guideIds = [];
            $guideNames = [];
            
            foreach ($package->selected_guide as $guide) {
                if (isset($guide['id'])) $guideIds[] = $guide['id'];
                if (isset($guide['name'])) $guideNames[] = $guide['name'];
            }
            
            $guidesData = Guide::where(function($query) use ($guideIds) {
                $query->whereIn('guide_id', $guideIds);
            })->select('guide_id', 'name', 'image')->get();
            
            $package->selected_guide = collect($package->selected_guide)->map(function($guide) use ($guidesData) {
                // Use first with a callback for matching complex conditions
                $guideData = $guidesData->first(function($item) use ($guide) {
                    return $item->guide_id == $guide['id'] || 
                           $item->name == $guide['name'];
                });
                
                if ($guideData) {
                    $guide['image'] = $guideData->image 
                        ? (str_starts_with($guideData->image, 'http') 
                            ? $guideData->image 
                            : asset('storage/' . $guideData->image))
                        : null;
                }
                
                return $guide;
            })->toArray();
        }
        
        // Enhance selected_restaurants with image data
        if (!empty($package->selected_restaurants)) {
            $restaurantIds = collect($package->selected_restaurants)->pluck('id')->toArray();
            $restaurantNames = collect($package->selected_restaurants)->pluck('name')->toArray();
            
            $restaurantsData = Restaurant::where(function($query) use ($restaurantIds) {
                $query->whereIn('restaurant_id', $restaurantIds);
            })->select('restaurant_id', 'name', 'master_image', 'images')->get();
            
            $package->selected_restaurants = collect($package->selected_restaurants)->map(function($restaurant) use ($restaurantsData) {
                // Use first with a callback for matching complex conditions
                $restaurantData = $restaurantsData->first(function($item) use ($restaurant) {
                    return $item->restaurant_id == $restaurant['id'] || 
                           $item->name == $restaurant['name'];
                });
                
                if ($restaurantData) {
                    $restaurant['image'] = $restaurantData->master_image 
                        ? (str_starts_with($restaurantData->master_image, 'http') 
                            ? $restaurantData->master_image 
                            : asset('storage/' . $restaurantData->master_image))
                        : null;
                    $restaurant['images'] = $restaurantData->images 
                        ? collect(json_decode($restaurantData->images, true))->map(function($img) {
                            return str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
                        })->toArray()
                        : [];
                }
                
                return $restaurant;
            })->toArray();
        }
        
        return response()->json($package);
    }
}
