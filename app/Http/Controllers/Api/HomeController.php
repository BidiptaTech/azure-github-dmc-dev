<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attraction;
use App\Models\User;
use App\Models\Agent;
use App\Models\Country;
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use App\Models\PackagedAttraction;

class HomeController extends Controller
{
   /*
    * Show Attraction Listings.
    * Date 16-01-2025
    */
    public function attractionListing(Request $request)
    {
        $location = $request->city;
        $parts = explode(',', $location);
        $city = trim($parts[0]);
        $country = trim($parts[1] ?? '');
        $country = trim($country, '()');

        $agentId = auth()->user()->agent_id;
        
        if (!$city || !$country) {
            return response()->json(['message' => 'City or Country is missing'], 400);
        }
        $dateRange = json_decode($request->query('date'), true);
        $requestDate = Carbon::parse($dateRange[0]);

        // Now you can use format() since $requestDate is a Carbon object
        $formattedDate = $requestDate->format('Y-m-d');
        $requestDayOfWeek = strtolower($requestDate->format('l'));

        // Query only attractions that are available on the requested date
        $allAttractions = Attraction::where('is_active', 1)
        ->where('country', $country)
        ->where('status', 1)
        ->where('location', $city)
        ->get();

        // Filter out attractions where the date is in close_dates
        $availableAttractions = $allAttractions->filter(function ($attraction) use ($formattedDate, $requestDayOfWeek) {
            // Check for specific dates first
            if (!empty($attraction->close_dates)) {
                $closeDates = array_map('trim', explode(',', $attraction->close_dates));
                if (in_array($requestDate, $closeDates)) {
                    return false; // Exclude if the date is in close_dates
                }
            }
            
            // Then check for closed days of the week
            if (!empty($attraction->close_days)) {
                $closeDays = array_map('trim', explode(',', strtolower($attraction->close_days)));
                if (in_array($requestDayOfWeek, $closeDays)) {
                    return false; // Exclude if the day of week is in close_days
                }
            }
            
            // If passed both checks, the attraction is available
            return true;
        });

        if ($availableAttractions->isEmpty()) {
            return response()->json(['message' => 'No attractions found for the selected city'], 404);
        }

        $agent = Agent::where('agent_id', $agentId)->first();
        
        $dmc_id = null;
        if ($agent) {
            $salesManagerId = $agent->sales_manager_dmc;
            switch ($agent->role_id) {
                case 11: // Agent is a DMC
                    $dmc_id = $salesManagerId; // Assuming `userId` in agent or fallback to agent_id
                    break;
                case 33: // Sales Head
                         $saleshead_dmc = User::where('userId', $salesManagerId)->first(); 
                        if ( $saleshead_dmc) {
                            $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                            }
                        }
                    break;
                case 12:
                case 37: // Sales Manager
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

        // Group attractions by name
        $groupedAttractions = $availableAttractions->groupBy('name');
        $attractionList = [];

        $check_country = Country::whereRaw('LOWER(name) = ?', [strtolower($country)])->first();
        $country_tax = $check_country->tax_percentage ?? 0;

        foreach ($groupedAttractions as $name => $attractionCollection) {
            $firstAttraction = $attractionCollection->first();
            if (!$firstAttraction) continue;

            $time_slots = [];
            $open_times = json_decode($firstAttraction->open_time, true) ?? [];
            $close_times = json_decode($firstAttraction->close_time, true) ?? [];
            $count = min(count($open_times), count($close_times));

            for ($i = 0; $i < $count; $i++) {
                $time_slots[] = "{$open_times[$i]} - {$close_times[$i]}";
            }

            // Handle pricing for different DMCs
            $pricingDetails = [];

            foreach ($attractionCollection as $attraction) {
                $tickets = Ticket::where('attraction_id', $attraction->attraction_id)->get();
                $lowestChildPrice = $tickets->min('child_price');
                $lowestAdultPrice = $tickets->min('adult_price');
                $lowestSeniorAdultPrice = $tickets->min('senior_adult_price');
                if($attraction->dmc_id == $dmc_id){
                    list($dmc_adult_price, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel($lowestAdultPrice, $dmc_id, $name, 'attraction', $city);
                    list($dmc_child_price, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel($lowestChildPrice, $dmc_id, $name, 'attraction', $city);
                    list($dmc_senior_price, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel($lowestSeniorAdultPrice, $dmc_id, $name, 'attraction', $city);
                }else{
                    $dmc_adult_price = 0;
                    $dmc_child_price = 0;
                    $dmc_dmc_id = 0;
                    $dmc_senior_price = 0;
                }
                $travClicks_adult_price = 0;
                $travClicks_child_price = 0;
                $travClicks_senior_price = 0;
                $trav_dmc_id = 0;
                if($agent){ 
                    list($travClicks_adult_price, $trav_dmc_id) = CommonHelper::calculateMinPricehotel($lowestAdultPrice, $dmc_id, $name, 'attraction', $city);
                    list($travClicks_child_price, $trav_dmc_id) = CommonHelper::calculateMinPricehotel($lowestChildPrice, $dmc_id, $name, 'attraction', $city);
                    list($travClicks_senior_price, $trav_dmc_id) = CommonHelper::calculateMinPricehotel($lowestSeniorAdultPrice, $dmc_id, $name, 'attraction', $city);
                    $pricingDetails[] = [
                        
                    ];
                }
            }

            // Availability logic
            $responseData = [];
            $closeDates = array_map('trim', explode(',', $firstAttraction->close_dates));

            foreach ($dateRange as $date) {
                $formattedDate = Carbon::parse($date)->format('Y-m-d');
                $today = Carbon::today()->format('Y-m-d');

                if ($formattedDate < $today || in_array($formattedDate, $closeDates)) {
                    $responseData[$formattedDate] = 'Not Available';
                } else {
                    $responseData[$formattedDate] = 'Available';
                }
            }

            // Build the attraction response
            $attractionList[] = [
                'id' => $firstAttraction->attraction_id,
                'attraction_name' => $name,
                'dmc_adult_price' => round((float)$dmc_adult_price, 2),
                'dmc_senior_price' => round((float)$dmc_senior_price, 2),
                'dmc_child_price' => round((float)$dmc_child_price, 2),
                'dmc_id' => $dmc_dmc_id ?? 0,
                'dmc_user_name' => User::where('userId', $dmc_dmc_id)->value('name') ?? '',
                'travClicks_adult_price' => round((float)$travClicks_adult_price, 2),
                'travClicks_senior_price' => round((float)$travClicks_senior_price, 2),
                
                'travClicks_child_price' => round((float)$travClicks_child_price, 2),
                'travclicks_dmc_id' => $trav_dmc_id,
                'city' => $firstAttraction->location,
                'country' => $country,
                'image' => $firstAttraction->master_image,
                'morning_opening' => $firstAttraction->morning_opening ?? 0,
                'additional_images' => json_decode($firstAttraction->additional_image, true) ?? [],
                'afternoon_opening' => $firstAttraction->afternoon_opening ?? 0,
                'evening_opening' => $firstAttraction->evening_opening ?? 0,
                'night_opening' => $firstAttraction->night_opening ?? 0,
                'availability' => $responseData,
                'time_slots' => $time_slots,
                'tax_percentage' => $country_tax,
            ];
        }
        return response()->json($attractionList);
    }

    public function attractionDetails(Request $request){
        $mode = $request->mode;
        $attractionId = $request->attractionId;
        $get_dmc_id = $request->dmc_id;
        if(!$mode){
            return response()->json(['message' => 'Mode is missing'], 400);
        }
        if(!$attractionId){
            return response()->json(['message' => 'Attraction Id is missing'], 400);
        }
        if(!$get_dmc_id){
            return response()->json(['message' => 'Dmc Id is missing'], 400);
        }
        
        // Then check if any package contains this attraction ID
        $packaged_attractions = PackagedAttraction::whereJsonContains('attractions', $attractionId)->get();
        $packages = [];
        if(!$packaged_attractions->isEmpty()){
            $packages =  $this->formatPackagedAttractionResponse($packaged_attractions);
        }

        $attraction = Attraction::where('attraction_id', $attractionId)->first();
        $country = $attraction->country;
        $check_country = Country::whereRaw('LOWER(name) = ?', [strtolower($country)])->first();
        $country_tax = $check_country->tax_percentage ?? 0;
        if (!$attraction) {
            return response()->json(['message' => 'Attraction not found'], 404);
        }
        $dmcId = User::where('userId', $get_dmc_id)->first();
        if(!$dmcId){
            return response()->json(['message' => 'Dmc Not Found !'], 400);
        }
        list($dmc_adult_price) = CommonHelper::CalculatePriceDetails($attraction->adult_price, $get_dmc_id);
        list($dmc_child_price) = CommonHelper::CalculatePriceDetails($attraction->child_price, $get_dmc_id);
           
        list($dmc_senior_price) = CommonHelper::CalculatePriceDetails($attraction->senior_adult_price, $get_dmc_id);
        list($dmc_shared_price) = CommonHelper::CalculatePriceDetails($attraction->price_shared, $get_dmc_id); 
        // Decode open_time and close_time from JSON
        $open_times = json_decode($attraction->open_time, true) ?? [];
        $close_times = json_decode($attraction->close_time, true) ?? [];
        // Merge open and close times into "open-close" format
        $time_slots = [];
        $count = min(count($open_times), count($close_times)); // Ensure we don't exceed array limits
        for ($i = 0; $i < $count; $i++) {
            $time_slots[] = "{$open_times[$i]}-{$close_times[$i]}";
        }
        $prices = (object) [
            'dmc_adult_price' => round((float)$dmc_adult_price,2),
            'dmc_child_price' => round((float)$dmc_child_price,2),
            'dmc_senior_price' => round((float)$dmc_senior_price,2),
            'dmc_shared_price' => round((float)$dmc_shared_price,2),
            'dmc_id' => $get_dmc_id,
            'mode' => $mode,
        ];
        $ticket = Ticket::where('attraction_id', $attractionId)->get();
        $ticketPrices = [];
        if($ticket){
            foreach($ticket as $t){
                $ticketPrices[] = (object) [
                    'ticket_id' => $t->ticket_id,	
                    'ticket_name' => $t->name,
                    'dmc_adult_price' => $t->adult_price,
                    'dmc_child_price' => $t->child_price,
                    'dmc_senior_price' => $t->senior_adult_price,
                    'dmc_adult_price_nri' => $t->adult_price_nri,
                    'dmc_child_price_nri' => $t->child_price_nri,
                    'dmc_senior_price_nri' => $t->senior_adult_price_nri,
                    'description' => $t->description,
                    'remarks' => $t->remarks,
                    'terms_conditions' => $t->terms_conditions,
                    'dmc_id' => $t->dmc_id,
                ];
            }
        }
        else{
            $ticketPrices = (object) [
                'ticket_id' => 0,
                'ticket_name' => '',
                'dmc_adult_price' => 0,
                'dmc_child_price' => 0,
                'dmc_senior_price' => 0,
                'description' => '',
                'dmc_id' => 0,
            ];
        }

        // $vehicles = Vehicle::select('vehicle_id', 'vehicle_name', 'vehicle_type', 'seating_capacity', 'sharable', 'description', 'attraction_private_transport_price', 'is_available', 'attraction_shared_transport_price')->where('is_available', 1)
        //     ->where('dmc_id', $get_dmc_id)
        //     ->get();
        return response()->json([
            'id' => $attraction->attraction_id,
            'name' => $attraction->name,
            'child_max_age' => $attraction->child_max_age,
            'senior_min_age' => $attraction->senior_min_age,
            'prices' => $prices,
            'location' => $attraction->location,
            'country' => $attraction->country,
            'time_slots' => $time_slots,
            'description' => $attraction->description,
            'remarks' => $attraction->remarks,
            'terms_conditions' => $attraction->terms_conditions,
            'is_active' => $attraction->is_active,
            'master_image' => $attraction->master_image,
            'additional_images' => json_decode($attraction->additional_image, true),
            'tax_percentage' => $country_tax,
            'ticket_prices' => $ticketPrices,
            'packages' => $packages,
            // 'vehicles' => $vehicles,
        ]);
    }
    
    /**
     * Format packaged attractions for response
     * 
     * @param \Illuminate\Database\Eloquent\Collection $packaged_attractions
     * @return array
     */
    private function formatPackagedAttractionResponse($packaged_attractions)
    {
        $formattedPackages = [];
        
        foreach($packaged_attractions as $package) {
            $attractionsList = [];
            $attractionIds = json_decode($package->attractions, true) ?? [];
            
            foreach($attractionIds as $attrId) {
                $attr = Attraction::where('attraction_id', $attrId)
                    ->select('attraction_id', 'name', 'country', 'location', 'master_image', 'description')
                    ->first();
                
                if($attr) {
                    $attractionsList[] = [
                        'attraction_id' => $attr->attraction_id,
                        'name' => $attr->name,
                        'country' => $attr->country,
                        'location' => $attr->location,
                        'master_image' => $attr->master_image,
                        'description' => $attr->description
                    ];
                }
            }
            
            $formattedPackages[] = [
                'id' => $package->id,
                'package_attraction_id' => $package->package_attraction_id,
                'name' => $package->name,
                'senior_citizen_price' => $package->senior_citizen_price,
                'child_price' => $package->child_price,
                'adult_price' => $package->adult_price,
                'image' => $package->image,
                'description' => $package->description,
                'dmc_id' => $package->dmc_id,
                'attractions' => $attractionsList
            ];
        }
        
        return $formattedPackages;
    }
}
