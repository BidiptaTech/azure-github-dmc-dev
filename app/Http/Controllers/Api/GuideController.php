<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guide;
use App\Models\User;
use App\Models\Agent;
use App\Models\Order;
use App\Models\Country;
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GuideController extends Controller
{
    /*
    * Show Guide Listings.
    * Date 16-01-2025
    */
    public function index(Request $request)
    {
        $location = $request->city;
        $parts = explode(',', $location);
        $city = trim($parts[0]);
        $country = trim($parts[1] ?? '');
        $country = trim($country, '()');
    
        $agentId = auth()->user()->agent_id;
        $dmcId = $request->dmc_id;
        $start = $request->start ?? 0;
        $limit = $request->limit ?? 10;

        if($start < 0){
            return response()->json(['message' => 'Start value cannot be negative'], 400);
        }
        if($limit < 0){
            return response()->json(['message' => 'Limit value cannot be negative'], 400);
        }
        
        if (!$city || !$country) {
            return response()->json(['message' => 'City or Country is missing'], 400);
        }
    
        // Parse the requested date
        $dateRange = json_decode($request->query('date'), true);
        $requestDate = Carbon::parse($dateRange[0]);
        $formattedDate = $requestDate->format('Y-m-d');
        $requestDayOfWeek = strtolower($requestDate->format('l'));
    
        $allGuides = Guide::orderBy('guide_id', 'desc')->where('is_active', 1)
            ->where('country', $country)
            ->where('status', 1)
            ->where('city', $city)
            ->where('dmc_id', $dmcId)
            ->limit($limit)
            ->offset($start)
            ->get();

         // Filter out guides that are not available on the requested date
        $availableGuides = $allGuides->filter(function ($guide) use ($formattedDate, $requestDayOfWeek) {
            // Check for specific dates first
            if (!empty($guide->close_dates)) {
                $closeDates = array_map('trim', explode(',', $guide->close_dates));
                if (in_array($formattedDate, $closeDates)) {
                    return false; // Exclude if the date is in close_dates
                }
            }
            
            // Handle the close_days format ["Sunday"]
            if (!empty($guide->close_days)) {
                try {
                    // Decode the JSON array of close days
                    $closeDaysArray = $guide->close_days;
                    
                    if (is_array($closeDaysArray)) {
                        // Convert all days to lowercase for case-insensitive comparison
                        $closeDaysLower = array_map('strtolower', $closeDaysArray);
                        
                        // Check if the request day is in the close days
                        if (in_array($requestDayOfWeek, $closeDaysLower)) {
                            return false; // Exclude if the day of week is in close_days
                        }
                    }
                } catch (\Exception $e) {
                    // Log the error but continue processing
                    \Log::error("Error processing guide close_days: " . $e->getMessage());
                }
            }
            
            return true; // Guide is available on this date
        });

        if ($availableGuides->isEmpty()) {
            return response()->json(['message' => 'No guides found for the selected city'], 200);
        }
    
        $check_country = Country::whereRaw('LOWER(name) = ?', [strtolower($country)])->first();
        $country_tax = $check_country->tax_percentage ?? 0;
        $agent = Agent::where('agent_id', $agentId)->first();
        
        $dmc_id = null;
        if ($agent) {
            $salesManagerId = $agent->sales_manager_dmc;
            switch ($agent->role_id) {
                case 11: // Agent is a DMC
                    $dmc_id = $agent->sales_manager_dmc; // Assuming `userId` in agent or fallback to agent_id
                    break;
                    case 33: 
                    case 128: 
                    case 129: 
                    case 130: 
                    case 134: 
                    case 135: 
                    case 136: 
                    case 138: // Sales Head
                    $salesManagerId = $agent->sales_manager_dmc;
                         $saleshead_dmc = User::where('userId', $salesManagerId)->first(); // SH
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
            
            if($currentUser->role_id == 33 || $currentUser->role_id == 128 || $currentUser->role_id == 129 || $currentUser->role_id == 130 || $currentUser->role_id == 134 || $currentUser->role_id == 135 || $currentUser->role_id == 136 || $currentUser->role_id == 138){
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
    
        // Group guides by name
        $groupedGuides = $availableGuides->groupBy('name');
        $guideList = [];
    
        foreach ($groupedGuides as $name => $guideCollection) {
            $firstGuide = $guideCollection->first();
            if (!$firstGuide) continue;
    
            // Handle pricing for different DMCs
            if($firstGuide->dmc_id == $dmc_id){
                list($dmc_day_rate, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel($firstGuide->day_rate, $dmc_id, $name, 'guide', $city);
            }else{
                $dmc_day_rate = 0;
                $dmc_dmc_id = 0;
            }
            $travClicks_day_rate = 0;
            $trav_dmc_id = 0;
            if($agent){
                list($total_price, $trav_dmc_id) = CommonHelper::calculateMinPricehotel($firstGuide->day_rate, $dmc_id, $name, 'guide', $city);
                $travClicks_day_rate = $total_price;
            }
            // Availability logic
            $responseData = [];
            $closeDates = array_map('trim', explode(',', $firstGuide->close_dates));
    
            foreach ($dateRange as $date) {
                $formattedDate = Carbon::parse($date)->format('Y-m-d');
                $today = Carbon::today()->format('Y-m-d');
    
                if ($formattedDate < $today || in_array($formattedDate, $closeDates)) {
                    $responseData[$formattedDate] = 'Not Available';
                } else {
                    $responseData[$formattedDate] = 'Available';
                }
            }
    
            // Build the guide response
            $guideList[] = [
                'id' => $firstGuide->guide_id,
                'guide_name' => $name,
                'dmc_day_rate' => round((float)$dmc_day_rate, 2),
                'dmc_id' => $dmc_id ?? 0,
                'dmc_user_name' => User::where('userId', $dmc_id)->value('name') ?? '',
                'travClicks_day_rate' => round((float)$travClicks_day_rate, 2),
                'travclicks_dmc_id' => $trav_dmc_id,
                'city' => $firstGuide->city,
                'country' => $country,
                'contact_no' => $firstGuide->contact_no,
                'email' => $firstGuide->email,
                'languages' => $firstGuide->languages->map(fn($language) => [
                    'language' => $language->language, 
                    'proficiency' => $language->proficiency ?? null, 
                ]),
                'government_license_no' => $firstGuide->government_license_no,
                'license_exp_date' => $firstGuide->license_exp_date,
                'experience_years' => $firstGuide->experience_years,
                'license_image' => $firstGuide->license_image,
                'service_type' => $firstGuide->service_type,
                'description' => $firstGuide->description,
                'guide_image' => $firstGuide->image,
                'availability' => $responseData,
                'tax_percentage' => $country_tax,
                'guide_base_price' => round((float)$dmc_day_rate, 2),
                'created_at' => $firstGuide->created_at,
            ];
        }
        return response()->json($guideList);
    }

    //Guide Details Api
    public function guideDetails(Request $request){
        $mode = $request->mode;
        $guide_id = $request->guide_id;
        $dmc_id = $request->dmc_id;
        $userId = auth()->user()->userId;
        if (!$dmc_id) {
            return response()->json(['error' => 'Dmc Id not found.'], 404);
        }
        if(!$guide_id){
            return response()->json(['error' => 'Guide Id not found.'], 404);
        }
        $guide = Guide::orderBy('guide_id', 'desc')->with('languages')->where('guide_id', $guide_id)->first();
        $country = $guide->country;
        $check_country = Country::whereRaw('LOWER(name) = ?', [strtolower($country)])->first();
        $country_tax = $check_country->tax_percentage ?? 0;

        $date = json_decode($request->query('date'), true);
        $orders = Order::where('type', 'guide')->get();
        $dateArray = json_decode($request->query('date'), true);
        $dateString = $dateArray[0] ?? null;

        if (!$dateString) {
            return response()->json(['error' => 'Invalid date format.'], 400);
        }
        try {
            $formattedDate = Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date input.'], 400);
        }

        $guideBookings = $orders->flatMap(function ($order) use ($guide_id, $formattedDate) {
            // Check if data is already an array or a JSON string
            if (is_string($order->data)) {
                $dataArray = json_decode($order->data, true); // Decode if it's a string
            } else {
                $dataArray = $order->data; // Already an array, use directly
            }
        
            return collect($dataArray)
                ->filter(function ($booking) use ($guide_id, $formattedDate) {
                    return is_array($booking)
                        && isset($booking['guide_id'], $booking['pickupdate']) // Check keys exist
                        && (int)$booking['guide_id'] == (int)$guide_id
                        && $booking['pickupdate'] == $formattedDate;
                })
                ->map(function ($booking) {
                    $entryTime = $booking['entrytime'] ?? '00:00';
                    $hours = (int) filter_var($booking['hours'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
                    
                    return [
                        'date' => $booking['pickupdate'],
                        'start_time' => $entryTime,
                        'hour' => $booking['hours'],
                        'end_time' => Carbon::parse($entryTime)
                            ->addHours($hours)
                            ->format('h:i A'),
                    ];
                });
        });
        $prices = null;

        if($mode == "dmc"){
            list($dmc_hourly_price) = CommonHelper::CalculatePriceDetails($guide->hourly_price, $dmc_id);
            list($dmc_night_surcharge) = CommonHelper::CalculatePriceDetails($guide->night_surcharge, $dmc_id);
            list($dmc_two_hour_price) = CommonHelper::CalculatePriceDetails($guide->two_hour_price, $dmc_id);
            list($dmc_four_hour_price) = CommonHelper::CalculatePriceDetails($guide->four_hour_price, $dmc_id);
            list($dmc_six_hour_price) = CommonHelper::CalculatePriceDetails($guide->six_hour_price, $dmc_id);
            list($dmc_eight_hour_price) = CommonHelper::CalculatePriceDetails($guide->eight_hour_price, $dmc_id);
            list($dmc_ten_hour_price) = CommonHelper::CalculatePriceDetails($guide->ten_hour_price, $dmc_id);
            list($dmc_twelve_hour_price) = CommonHelper::CalculatePriceDetails($guide->twelve_hour_price, $dmc_id);

            $prices = [
                "dmc_hourly_price" => round((float)$dmc_hourly_price,2),
                "dmc_night_surcharge" => round((float)$dmc_night_surcharge,2),
                "dmc_two_hour_price" => round((float)$dmc_two_hour_price,2),
                "dmc_four_hour_price" => round((float)$dmc_four_hour_price,2),
                "dmc_six_hour_price" => round((float)$dmc_six_hour_price,2),
                "dmc_eight_hour_price" => round((float)$dmc_eight_hour_price,2),
                "dmc_ten_hour_price" => round((float)$dmc_ten_hour_price,2),
                "dmc_twelve_hour_price" => round((float)$dmc_twelve_hour_price,2),
                "dmc_id" => $dmc_id, // Storing dmc_id once since it should be the same for all
            ];
        }
        else if($mode == "travclicks"){
            list($travclicks_day_rate) = CommonHelper::CalculatePriceDetails($guide->day_rate, $dmc_id);
            list($travclicks_night_surcharge) = CommonHelper::CalculatePriceDetails($guide->night_surcharge, $dmc_id);
            list($travclicks_hourly_price) = CommonHelper::CalculatePriceDetails($guide->hourly_price, $dmc_id);
            list($travclicks_two_hour_price) = CommonHelper::CalculatePriceDetails($guide->two_hour_price, $dmc_id);
            list($travclicks_four_hour_price) = CommonHelper::CalculatePriceDetails($guide->four_hour_price, $dmc_id);
            list($travclicks_six_hour_price) = CommonHelper::CalculatePriceDetails($guide->six_hour_price, $dmc_id);
            list($travclicks_eight_hour_price) = CommonHelper::CalculatePriceDetails($guide->eight_hour_price, $dmc_id);
            list($travclicks_ten_hour_price) = CommonHelper::CalculatePriceDetails($guide->ten_hour_price, $dmc_id);
            list($travclicks_twelve_hour_price) = CommonHelper::CalculatePriceDetails($guide->twelve_hour_price, $dmc_id);

            $prices = [
                "travclicks_day_rate" => round((float)$travclicks_day_rate,2),
                "travclicks_night_surcharge" => round((float)$travclicks_night_surcharge,2),
                "travclicks_hourly_price" => round((float)$travclicks_hourly_price,2),
                "travclicks_two_hour_price" => round((float)$travclicks_two_hour_price,2),
                "travclicks_four_hour_price" => round((float)$travclicks_four_hour_price,2),
                "travclicks_six_hour_price" => round((float)$travclicks_six_hour_price,2),
                "travclicks_eight_hour_price" => round((float)$travclicks_eight_hour_price,2),
                "travclicks_ten_hour_price" => round((float)$travclicks_ten_hour_price,2),
                "travclicks_twelve_hour_price" => round((float)$travclicks_twelve_hour_price,2),
                "travclicks_id" => $dmc_id, // Storing travclicks_id once assuming it's the same for all
            ];
        }
        else{
            return response()->json(['error' => 'dmc id or travclicks id are not get'], 400);
        }

        if ($guide) {
            $mappedGuide = [
                "id" => $guide->id,
                "guide_id" => $guide->guide_id,
                "name" => $guide->name,
                "email" => $guide->email,
                "contact_no" => $guide->contact_no,
                "description" => $guide->description,
                "image" => $guide->image,
                'languages' => $guide->languages->map(function ($language) {
                    return [
                        'language' => $language->language, 
                        'proficiency' => $language->proficiency ?? null, 
                    ];
                }),
                "is_active" => $guide->is_active,
                "government_license_no" => $guide->government_license_no,
                "license_image" => $guide->license_image,
                "license_exp_date" => $guide->license_exp_date,
                "certified" => $guide->certified,
                "experience_years" => $guide->experience_years,
                // Night Timing (Formatted)
                "night_start_time" => date('h:i A', strtotime($guide->night_start_time)),
                "night_end_time" => date('h:i A', strtotime($guide->night_end_time)),
        
                // Pricing (Both TravClicks & DMC Mode)
                "prices" => $prices,
                // Additional Info
                "rating" => $guide->rating,
                "service_type" => $guide->service_type,
                "approval" => $guide->approval,
                "close_days" => $guide->close_days,
                "close_dates" => $guide->close_dates,
                "salutation" => $guide->salutation,
                'tax_percentage' => $country_tax,
            ];
            
        } else {
            return response()->json(["message" => "Guide not found"], 404);
        }
        $data = [
            'guide' => $mappedGuide,
            'bookingDetails' => $guideBookings,
        ];
        
        return $data;
    }
}
