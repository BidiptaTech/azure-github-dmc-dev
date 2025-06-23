<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Meal;
use App\Models\User;
use App\Models\Agent;
use App\Models\Country;
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller
{
    /*
    * Show Restaurant Listings.
    * Date 16-01-2025
    */
    public function index(Request $request)
    {
        $location = $request->city;
        $parts = explode(',', $location);
        $city = trim($parts[0]); // it gives "Kolkata"
        $country = trim($parts[1] ?? '');
        $country = trim($country, '()'); //it gives "India"

        $agentId = auth()->user()->agent_id;
        
        if (!$city || !$country) {
            return response()->json(['message' => 'City or Country name is missing'], 400);
        }
        
        $restaurants = Restaurant::where('is_active', 1)
            ->where('country', $country)
            ->where('city', $city)
            ->get();
        
        if ($restaurants->isEmpty()) {
            return response()->json(['message' => 'No restaurants found for the selected city'], 404);
        }

        $agent = Agent::where('agent_id', $agentId)->first();
        
        $dmc_id = null;
        if ($agent) {
            $salesManagerId = $agent->sales_manager_dmc;
            switch ($agent->role_id) {
                case 11: // Agent is a DMC
                    $dmc_id = $agent->sales_manager_dmc; // Assuming `userId` in agent or fallback to agent_id
                    break;
                case 33: // Sales Head
                    $salesManagerId = $agent->sales_manager_dmc;
                         $saleshead_dmc = User::where('userId', $agent->sales_manager_dmc)->first(); // SH
                        if ( $saleshead_dmc) {
                            $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                            }
                        }
                    break;
                case 12:
                case 37: // Sales Manager
                    $salesManagerId = $agent->sales_manager_dmc;
                    $salesmng_dmc= User::where('userId', $agent->sales_manager_dmc)->first(); // SM
                    
                    if ($salesmng_dmc) {
                         $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first(); // SH
                        if ( $saleshead_dmc) {
                            $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                            }
                        }
                    }
                    break;
                case 38: // Assistant Manager
                    $salesManagerId = $agent->sales_manager_dmc;
                    $asmng_dmc = User::where('userId', $agent->sales_manager_dmc)->first(); // SM
                    if($asmng_dmc){
                        $salesmng_dmc = User::where('userId', $asmng_dmc->created_by)->first(); // SH
                    }
                    if ($salesmng_dmc) {
                         $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first(); // SH
                        if ( $saleshead_dmc) {
                            $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                            }
                        }
                    }
                    break;
            }
        }
        elseif(Auth::user()->userId){
            $currentUser = Auth::user();
            
            if($currentUser->role_id == 33){
                $dmc_id = $currentUser->created_by;
            }
            elseif($currentUser->role_id == 37){
                $sales_head_id = $currentUser->created_by;
                $sales_head = User::where('userId', $sales_head_id)->first();
                $dmc_id = $sales_head->created_by;
            }
            elseif($currentUser->role_id == 38){
                $sales_manager_id = $currentUser->created_by;
                $sales_manager = User::where('userId', $sales_manager_id)->first();
                $sales_head_id = $sales_manager->created_by;
                $sales_head = User::where('userId', $sales_head_id)->first();
                $dmc_id = $sales_head->created_by;
            }
        }
        
        if (!$dmc_id) {
            return response()->json(['message' => 'DMC Not Found!'], 400);
        }

        $dateRange = json_decode($request->query('date'), true);

        $groupedRestaurants = [];

        $check_country = Country::whereRaw('LOWER(name) = ?', [strtolower($country)])->first();
        $country_tax = $check_country->tax_percentage ?? 0;
        foreach ($restaurants as $restaurant) {
            if (!$dmc_id) {
                return response()->json(['message' => 'Unable to fetch this restaurant details!'], 404);
            }

            $meals = Meal::where('restaurant_id', $restaurant->restaurant_id)->get();

            $minBreakfast = $meals->filter(fn($meal) => $meal->meal_period == 1)
            ->map(fn($meal) => $meal->adult_price !== null ? $meal->adult_price : $meal->price)
            ->filter(fn($price) => $price !== null)
            ->min();

        $minLunch = $meals->filter(fn($meal) => $meal->meal_period == 2)
            ->map(fn($meal) => $meal->adult_price !== null ? $meal->adult_price : $meal->price)
            ->filter(fn($price) => $price !== null)
            ->min();

        $minDinner = $meals->filter(fn($meal) => $meal->meal_period == 3)
            ->map(fn($meal) => $meal->adult_price !== null ? $meal->adult_price : $meal->price)
            ->filter(fn($price) => $price !== null)
            ->min();
            list($dmc_breakfast_price, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel($minBreakfast, $dmc_id, $restaurant->name, 'restaurant', $restaurant->city);
            list($dmc_lunch_price, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel($minLunch, $dmc_id, $restaurant->name, 'restaurant', $restaurant->city);
            
            list($dmc_dinner_price, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel($minDinner, $dmc_id, $restaurant->name, 'restaurant', $restaurant->city);
            $travClicks_breakfast_price = 0;
            $travClicks_lunch_price = 0;
            $travClicks_dinner_price = 0;
            $travclicks_dmc_id = 0;
            if($agent){
                list($travClicks_breakfast_price, $travclicks_dmc_id) = CommonHelper::calculateMinPricehotel($minBreakfast, $dmc_id, $restaurant->name, 'restaurant', $restaurant->city);
                list($travClicks_lunch_price, $travclicks_dmc_id) = CommonHelper::calculateMinPricehotel($minLunch, $dmc_id, $restaurant->name, 'restaurant', $restaurant->city);
                list($travClicks_dinner_price, $travclicks_dmc_id) = CommonHelper::calculateMinPricehotel($minDinner, $dmc_id, $restaurant->name, 'restaurant', $restaurant->city);
            }

            $responseData = [];
            $closeDates = array_map('trim', explode(',', $restaurant->close_dates));
            
            foreach ($dateRange as $date) {
                $formattedDate = Carbon::parse($date)->format('Y-m-d');
                $today = Carbon::today()->format('Y-m-d');
                
                $responseData[$formattedDate] = ($formattedDate < $today || in_array($formattedDate, $closeDates)) ? 'Not Available' : 'Available';
            }

            $restaurantKey = $restaurant->name;
            if (!isset($groupedRestaurants[$restaurantKey])) {
                $groupedRestaurants[$restaurantKey] = [
                    'id' => $restaurant->restaurant_id,
                    'restaurant_name' => $restaurant->name,
                    'cuisine' => $restaurant->cuisine,
                    'breakfast_available' => $restaurant->breakfast_available,
                    'breakfast_open_time' => CommonHelper::TimeFormat($restaurant->opening_time_bf),
                    'breakfast_close_time' => CommonHelper::TimeFormat($restaurant->closing_time_bf),
                    'lunch_available' => $restaurant->lunch_available,
                    'lunch_open_time' => CommonHelper::TimeFormat($restaurant->opening_time_lunch),
                    'lunch_close_time' => CommonHelper::TimeFormat($restaurant->closing_time_lunch),
                    'dinner_available' => $restaurant->dinner_available,
                    'dinner_open_time' => CommonHelper::TimeFormat($restaurant->opening_time_dinner),
                    'dinner_close_time' => CommonHelper::TimeFormat($restaurant->closing_time_dinner),
                    'city' => $restaurant->city,
                    'country' => $country,
                    'image' => $restaurant->master_image,
                    'site_images' => json_decode($restaurant->images, true) ?? [],
                    'closeDates' => $responseData,
                    'dmc_id' => $dmc_dmc_id ?? '',
                    'travclicks_dmc_id' => $travclicks_dmc_id ?? '',
                    'dmc_breakfast_price' => round((float)$dmc_breakfast_price, 2),
                    'travClicks_breakfast_price' => round((float)$travClicks_breakfast_price, 2),
                    'dmc_lunch_price' => round((float)$dmc_lunch_price, 2),
                    'travClicks_lunch_price' => round((float)$travClicks_lunch_price, 2),
                    'dmc_dinner_price' => round((float)$dmc_dinner_price, 2),
                    'travClicks_dinner_price' => round((float)$travClicks_dinner_price, 2),
                    'tax_percentage' => $country_tax,
                ];
            }
        }

        return response()->json(array_values($groupedRestaurants));
    }

    public function restaurantDetails(Request $request)
    {
        $dmcId = $request->dmc_id;
        $get_dmc_id = $dmcId;
        if(!$dmcId){
            return response()->json(['message' => 'Dmc not found'], 404);
        }
        $mode = $request->mode;
        $restaurantId = $request->restaurantId;
        if (!$restaurantId) {
            return response()->json(['message' => 'Restaurant ID is missing'], 400);
        }
        $restaurant = Restaurant::where('restaurant_id', $restaurantId)->first();
        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }
        
        $country = $restaurant->country;
        $check_country = Country::whereRaw('LOWER(name) = ?', [strtolower($country)])->first();
        $country_tax = $check_country->tax_percentage ?? 0;
        $meals = Meal::where('restaurant_id', $restaurantId)
        ->get(['meal_id', 'name', 'type', 'is_active', 'item_description', 'restaurant_id', 'meal_period', 'price', 'adult_price', 'child_price', 'category', 'item_type'])
        ->map(function ($meal) use ($dmcId, $mode, $get_dmc_id) {
            $prices = null;
            $adult_prices = null;
            $child_prices = null;
            $price_type = null;
            list($prices) = CommonHelper::CalculatePriceDetails($meal->price, $get_dmc_id);
            list($adult_prices) = CommonHelper::CalculatePriceDetails($meal->adult_price, $get_dmc_id);
            list($child_prices) = CommonHelper::CalculatePriceDetails($meal->child_price, $get_dmc_id);
            return [
                'meal_id' => $meal->meal_id,
                'name' => $meal->name,
                'type' => match ($meal->type) {
                    "1" => 'Buffet',
                    "2" => 'Set Menu',
                    "3" => 'A la carte',
                    default => 'Unknown'
                },
                'is_active' => $meal->is_active,
                'item_description' => $meal->item_description,
                'restaurant_id' => $meal->restaurant_id,
                'meal_period' => match ($meal->meal_period) {
                    1 => 'Breakfast',
                    2 => 'Lunch',
                    3 => 'Dinner',
                    default => 'Unknown'
                },
                'category' => $meal->category == 1 ? "Alcoholic" : "Non Alcoholic",
                'item_type' => $meal->item_type == 1 ? "Veg" : "Non Veg",
                'price' => round((float)$prices,2),
                'adult_price' => round((float)$adult_prices,2),
                'child_price' => round((float)$child_prices,2),
                'price_type' => $mode,
                'dmc_id' => $get_dmc_id,
            ];
        });
        // $vehicle_details = Vehicle::select('vehicle_id', 'vehicle_name', 'vehicle_type', 'seating_capacity', 'sharable', 'description', 'restaurant_private_transport_price', 'is_available', 'restaurant_shared_transport_price')->where('dmc_id', $get_dmc_id)->where('is_available', 1)->get();
        
        return response()->json([
            'id' => $restaurant->restaurant_id,
            'name' => $restaurant->name,
            'city' => $restaurant->city,
            'country' => $restaurant->country,
            'cuisine' => $restaurant->cuisine,
            'breakfast_available' => $restaurant->breakfast_available,
            'opening_time_bf' => CommonHelper::TimeFormat($restaurant->opening_time_bf),
            'closing_time_bf' => CommonHelper::TimeFormat($restaurant->closing_time_bf),
            'lunch_available' => $restaurant->lunch_available,
            'opening_time_lunch' => CommonHelper::TimeFormat($restaurant->opening_time_lunch),
            'closing_time_lunch' => CommonHelper::TimeFormat($restaurant->closing_time_lunch),
            'dinner_available' => $restaurant->dinner_available,
            'opening_time_dinner' => CommonHelper::TimeFormat($restaurant->opening_time_dinner),
            'closing_time_dinner' => CommonHelper::TimeFormat($restaurant->closing_time_dinner),
            'owned_by' => $restaurant->owned_by,
            'property' => $restaurant->property,
            'is_active' => $restaurant->is_active,
            'description' => $restaurant->description,
            'remarks' => $restaurant->remarks,
            'terms_conditions' => $restaurant->terms_conditions,
            'master_image' => $restaurant->master_image,
            'additional_images' => json_decode($restaurant->images, true),
            'meals' => $meals,
            'tax_percentage' => $country_tax,
            // 'vehicles' => $vehicle_details,
        ]);
    }
}
