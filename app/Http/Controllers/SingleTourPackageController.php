<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\City;
use App\Models\Agent;
use App\Models\SingleTourPackage;
use App\Models\Tour;
use App\Models\Attraction;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Guide;
use App\Models\Restaurant;
use App\Models\Meal;
use App\Models\Zone;
use App\Models\Vehicle;
use App\Models\VehicleZoneMapping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
use Carbon\Carbon;

class SingleTourPackageController extends Controller
{
    /**
     * Display a listing of single tour packages.
     */
    public function index()
    {
        $packages = SingleTourPackage::with(['country', 'city', 'agent'])
            ->where('dmc_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('single-tour-package.index', compact('packages'));
    }

    /**
     * Show the form for creating a new single tour package.
     */
    public function create()
    {
        // Get countries for dropdown
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        
        // Get agents for the current DMC
        $agents = Agent::Where('sales_manager_dmc', Auth::id())
            ->orderBy('name')
            ->get();

        return view('single-tour-package.create', compact('countries', 'agents'));
    }

    /**
     * Store a newly created single tour package in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_country' => 'required|string', // Country name
            'city' => 'required|string', // City name  
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'adults' => 'required|integer|min:1',
            'male' => 'required|integer|min:0',
            'female' => 'required|integer|min:0',
            'children' => 'required|integer|min:0',
            'infants' => 'required|integer|min:0',
            'agent_id' => 'required|exists:agents,agent_id',
            'package_name' => 'nullable|string|max:255',
            'estimated_budget' => 'nullable|numeric|min:0',
            'package_description' => 'nullable|string',
            'is_premium' => 'nullable|boolean'
        ]);

        // Additional validation: male + female should equal adults
        if (($request->male + $request->female) != $request->adults) {
            return back()->withErrors(['adults' => 'Total male and female count must equal total adults.'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Parse the dates
            $checkInTime = Carbon::createFromFormat('Y-m-d', $request->start_date);
            $checkOutTime = Carbon::createFromFormat('Y-m-d', $request->end_date);

            // Generate tour ID and save the tour
            $max_tour_id = Tour::max('tour_id') ?? 0;
            $tourId = CommonHelper::createId($max_tour_id);

            $display_id = 'DMC-ORD' . $tourId;

            // Create new tour record following TourController pattern
            $tour = new Tour();
            $tour->destination = $request->user_country;
            $tour->adult = $request->adults;
            $tour->child = $request->children ?? 0;
            $tour->infant = $request->infants ?? 0;
            $tour->agent_id = $request->agent_id;
            $tour->tour_id = $tourId;
            $tour->male_count = $request->male;
            $tour->female_count = $request->female;
            $tour->check_in_time = $checkInTime;
            $tour->check_out_time = $checkOutTime;
            $tour->display_id = $display_id;
            $tour->tour_status = "Pending";
            $tour->city = $request->city;
            $tour->child_ages = null; // You can add this field to the form if needed
            $tour->save();

            DB::commit();

            // Return JSON response for AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tour package created successfully!',
                    'tour_id' => $tourId,
                    'display_id' => $display_id,
                    'tour' => $tour
                ]);
            }

            return redirect()->route('single-tour-package.create')
                ->with('success', 'Tour package created successfully! Tour ID: ' . $display_id);

        } catch (\Exception $e) {
            DB::rollback();
            
            // Return JSON error response for AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create tour package. Error: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create tour package. Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified single tour package.
     */
    public function show($id)
    {
        $package = SingleTourPackage::with(['country', 'city', 'agent', 'dmc'])
            ->where('dmc_id', Auth::id())
            ->findOrFail($id);

        return view('single-tour-package.show', compact('package'));
    }

    /**
     * Show the form for editing the specified single tour package.
     */
    public function edit($id)
    {
        $package = SingleTourPackage::where('dmc_id', Auth::id())->findOrFail($id);
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        $cities = City::where('country_id', $package->country_id)->orderBy('name')->get();
        
        $agents = Agent::where('root_dmc_id', Auth::id())
            ->orWhere('sales_manager_dmc', Auth::id())
            ->orderBy('name')
            ->get();

        return view('single-tour-package.edit', compact('package', 'countries', 'cities', 'agents'));
    }

    /**
     * Update the specified single tour package in storage.
     */
    public function update(Request $request, $id)
    {
        $package = SingleTourPackage::where('dmc_id', Auth::id())->findOrFail($id);

        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'infants' => 'required|integer|min:0',
            'agent_id' => 'required|exists:agents,agent_id',
            'package_name' => 'required|string|max:255',
            'estimated_budget' => 'nullable|numeric|min:0',
            'package_description' => 'nullable|string',
            'is_premium' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $package->update([
                'country_id' => $request->country_id,
                'city_id' => $request->city_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'adults' => $request->adults,
                'children' => $request->children,
                'infants' => $request->infants,
                'agent_id' => $request->agent_id,
                'package_name' => $request->package_name,
                'estimated_budget' => $request->estimated_budget,
                'package_description' => $request->package_description,
                'is_premium' => $request->has('is_premium'),
                'updated_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('single-tour-package.index')
                ->with('success', 'Single tour package updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update tour package. Please try again.');
        }
    }

    /**
     * Remove the specified single tour package from storage.
     */
    public function destroy($id)
    {
        try {
            $package = SingleTourPackage::where('dmc_id', Auth::id())->findOrFail($id);
            $package->delete();

            return redirect()->route('single-tour-package.index')
                ->with('success', 'Single tour package deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete tour package. Please try again.');
        }
    }

    /**
     * Fetch cities by country for auto-population (following agent controller pattern)
     */
    public function fetchCitiesByCountry(Request $request) 
    {
        $countryName = $request->input('country');
        
        $cities = City::where('country', $countryName)
                ->select('name', 'city_id', 'id')
                ->get();
                 
        return response()->json(['cities' => $cities]);
    }

    /**
     * Get DMC ID based on current user hierarchy
     */
    private function getDmcId()
    {
        try {
            $currentUser = Auth::user();
            $dmcId = null;

            // If user has agent_id, get DMC from agent hierarchy
            if (isset($currentUser->agent_id) && $currentUser->agent_id) {
                $agent = Agent::where('agent_id', $currentUser->agent_id)->first();
                if ($agent) {
                    $sales_manager_dmc = $agent->sales_manager_dmc;
                    
                    // Based on role hierarchy, find the DMC
                    if ($agent->role_id == 11) {
                        $dmcId = $sales_manager_dmc;
                    } elseif (in_array($agent->role_id, [33, 128, 129, 130, 134, 135, 136, 138])) {
                        $sales_head = User::where('userId', $sales_manager_dmc)->first();
                        $dmcId = $sales_head ? $sales_head->created_by : null;
                    } elseif ($agent->role_id == 37) {
                        $sales_manager = User::where('userId', $sales_manager_dmc)->first();
                        if ($sales_manager) {
                            $sales_head = User::where('userId', $sales_manager->created_by)->first();
                            $dmcId = $sales_head ? $sales_head->created_by : null;
                        }
                    } elseif ($agent->role_id == 38) {
                        $assistant_sales_manager = User::where('userId', $sales_manager_dmc)->first();
                        if ($assistant_sales_manager) {
                            $sales_manager = User::where('userId', $assistant_sales_manager->created_by)->first();
                            if ($sales_manager) {
                                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                                $dmcId = $sales_head ? $sales_head->created_by : null;
                            }
                        }
                    }
                }
            } elseif (isset($currentUser->userId) && $currentUser->userId) {
                // Direct user - check if they're a DMC or sales head
                $dmcId = $currentUser->userId;
            }

            return $dmcId;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fetch attractions for the current DMC
     */
    public function fetchAttractions(Request $request)
    {
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            // Fetch attractions where dmc_id JSON contains current DMC ID
            // Using LIKE instead of JSON_CONTAINS for better compatibility
            $attractions = Attraction::whereJsonContains('dmc_id', (int) $dmcId)
                        ->select('attraction_id', 'name', 'open_time', 'close_time', 'location')
                        ->get();

            $attractionsData = $attractions->map(function ($attraction) {
                // Parse time slots from JSON
                $openTimes = [];
                $closeTimes = [];
                
                // Handle open_time
                if ($attraction->open_time) {
                    $decoded = json_decode($attraction->open_time, true);
                    if (is_array($decoded)) {
                        $openTimes = $decoded;
                    } elseif (is_string($attraction->open_time)) {
                        // If it's just a string time, wrap it in array
                        $openTimes = [$attraction->open_time];
                    }
                }
                
                // Handle close_time
                if ($attraction->close_time) {
                    $decoded = json_decode($attraction->close_time, true);
                    if (is_array($decoded)) {
                        $closeTimes = $decoded;
                    } elseif (is_string($attraction->close_time)) {
                        // If it's just a string time, wrap it in array
                        $closeTimes = [$attraction->close_time];
                    }
                }
                
                // Generate time slots
                $timeSlots = [];
                if (!empty($openTimes) && !empty($closeTimes) && is_array($openTimes) && is_array($closeTimes)) {
                    foreach ($openTimes as $index => $openTime) {
                        $closeTime = $closeTimes[$index] ?? $closeTimes[0];
                        $timeSlots[] = [
                            'open' => $openTime,
                            'close' => $closeTime,
                            'slot' => $openTime . ' - ' . $closeTime
                        ];
                    }
                }

                return [
                    'attraction_id' => $attraction->attraction_id,
                    'name' => $attraction->name,
                    'location' => $attraction->location,
                    'time_slots' => $timeSlots
                ];
            });

            return response()->json([
                'success' => true,
                'attractions' => $attractionsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attractions: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Fetch tickets for a specific attraction
     */
    public function fetchTickets(Request $request)
    {
        try {
            $attractionId = $request->input('attraction_id');
            
            if (!$attractionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attraction ID is required'
                ], 400);
            }

            $tickets = Ticket::where('attraction_id', $attractionId)
                ->select('ticket_id', 'name')
                ->get();

            return response()->json([
                'success' => true,
                'tickets' => $tickets
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tickets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch hotels for the current DMC by city
     */
    public function fetchHotels(Request $request)
    {
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            $city = $request->input('city');
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            if (!$city) {
                return response()->json([
                    'success' => false,
                    'message' => 'City is required'
                ], 400);
            }

            // Fetch hotels where dmc_id JSON contains current DMC ID and city matches
            $hotels = \App\Models\Hotel::whereJsonContains('dmc_id', (int) $dmcId)
                ->where('status', 1)
                ->where('is_active', 1)
                ->where(function ($q) use ($city) {
                    $q->whereRaw('LOWER(city) = ?', [strtolower($city)]);
                })
                ->select('hotel_unique_id', 'name', 'city', 'main_image', 'hotel_star_rating', 'latitude', 'longitude')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'hotels' => $hotels,
                'total_hotels' => count($hotels),
                'city' => $city
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching hotels: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Fetch rooms for a specific hotel
     */
    public function fetchRooms(Request $request)
    {
        try {
            $hotelId = $request->input('hotel_id');
            
            if (!$hotelId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel ID is required'
                ], 400);
            }

            // Fetch rooms for the selected hotel
            $rooms = \App\Models\Room::where('hotel_id', $hotelId)
                ->where('status', 1)
                ->select('room_id', 'room_type', 'weekday_price', 'weekend_price', 'double_weekday_price', 'double_weekend_price', 
                        'breakfast', 'breakfast_type', 'lunch', 'lunch_type', 'dinner', 'dinner_type',
                        'breakfast_included', 'dimension', 'features', 'master_image')
                ->orderBy('room_type')
                ->get();

            return response()->json([
                'success' => true,
                'rooms' => $rooms,
                'total_rooms' => count($rooms),
                'hotel_id' => $hotelId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching rooms: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Fetch beds for a specific room
     */
    public function fetchBeds(Request $request)
    {
        try {
            $roomId = $request->input('room_id');
            if (!$roomId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room ID is required'
                ], 400);
            }

            // Fetch beds for the selected room
            $beds = \App\Models\Bed::where('room_id', $roomId)
                ->where('is_active', 1)
                ->select('bed_id', 'room_type', 'no_of_rooms', 'max_occupancy', 'adult_count', 'child_count', 
                        'extra_bed', 'extra_bed_price', 'extra_bed_type', 'baby_cot', 'baby_cot_price')
                ->orderBy('room_type')
                ->get();
            return response()->json([
                'success' => true,
                'beds' => $beds,
                'total_beds' => count($beds),
                'room_id' => $roomId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching beds: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Fetch guides for the current DMC
     */
    public function fetchGuides(Request $request)
    {
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            // Fetch guides where dmc_id matches current DMC ID (bigint type)
            $guides = Guide::where('dmc_id', $dmcId)
                ->where('status', 1)
                ->select('guide_id', 'name', 'night_start_time', 'night_end_time', 
                        'day_rate', 'night_surcharge', 'hourly_price', 
                        'two_hour_price', 'four_hour_price', 'six_hour_price', 
                        'eight_hour_price', 'ten_hour_price', 'twelve_hour_price')
                ->get();

            $guidesData = $guides->map(function ($guide) {
                return [
                    'guide_id' => $guide->guide_id,
                    'name' => $guide->name,
                    'night_start_time' => $guide->night_start_time,
                    'night_end_time' => $guide->night_end_time,
                    'day_rate' => $guide->day_rate,
                    'night_surcharge' => $guide->night_surcharge,
                    'hourly_price' => $guide->hourly_price,
                    'two_hour_price' => $guide->two_hour_price,
                    'four_hour_price' => $guide->four_hour_price,
                    'six_hour_price' => $guide->six_hour_price,
                    'eight_hour_price' => $guide->eight_hour_price,
                    'ten_hour_price' => $guide->ten_hour_price,
                    'twelve_hour_price' => $guide->twelve_hour_price,
                ];
            });

            return response()->json([
                'success' => true,
                'guides' => $guidesData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching guides: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Fetch restaurants for the current DMC
     */
    public function fetchRestaurants(Request $request)
    {
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }
            
            // Fetch restaurants where dmc_id JSON contains current DMC ID AND have meals in meals table
            $restaurants = Restaurant::whereJsonContains('dmc_id', (int) $dmcId)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('meals')
                          ->whereRaw('meals.restaurant_id = restaurants.restaurant_id');
                })
                ->select('restaurant_id', 'name', 'breakfast_available', 'lunch_available', 'dinner_available',
                         'opening_time_bf', 'closing_time_bf', 'opening_time_lunch', 'closing_time_lunch',
                         'opening_time_dinner', 'closing_time_dinner')
                ->get();

            $restaurantsData = $restaurants->map(function ($restaurant) {
                $mealTypes = [];
                
                // Add breakfast if available
                if ($restaurant->breakfast_available == 1) {
                    $mealTypes[] = [
                        'type' => 'breakfast',
                        'label' => 'Breakfast',
                        'open_time' => $this->formatTimeTo12Hour($restaurant->opening_time_bf),
                        'close_time' => $this->formatTimeTo12Hour($restaurant->closing_time_bf)
                    ];
                }
                
                // Add lunch if available
                if ($restaurant->lunch_available == 1) {
                    $mealTypes[] = [
                        'type' => 'lunch',
                        'label' => 'Lunch',
                        'open_time' => $this->formatTimeTo12Hour($restaurant->opening_time_lunch),
                        'close_time' => $this->formatTimeTo12Hour($restaurant->closing_time_lunch)
                    ];
                }
                
                // Add dinner if available
                if ($restaurant->dinner_available == 1) {
                    $mealTypes[] = [
                        'type' => 'dinner',
                        'label' => 'Dinner',
                        'open_time' => $this->formatTimeTo12Hour($restaurant->opening_time_dinner),
                        'close_time' => $this->formatTimeTo12Hour($restaurant->closing_time_dinner)
                    ];
                }

                return [
                    'restaurant_id' => $restaurant->restaurant_id,
                    'name' => $restaurant->name,
                    'meal_types' => $mealTypes
                ];
            });

            return response()->json([
                'success' => true,
                'restaurants' => $restaurantsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching restaurants: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Fetch meals/dishes for a specific restaurant
     */
    public function fetchMealsByRestaurant(Request $request)
    {
        try {
            $restaurantId = $request->input('restaurant_id');
            $mealPeriod = $request->input('meal_period'); // 1=Breakfast, 2=Lunch, 3=Dinner
            
            if (!$restaurantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant ID is required'
                ], 400);
            }

            $query = Meal::where('restaurant_id', $restaurantId);
            
            // Filter by meal period if provided
            if ($mealPeriod) {
                $query->where('meal_period', $mealPeriod);
            }

            $meals = $query->select('meal_id', 'name', 'type', 'price', 'adult_price', 'child_price', 'meal_period')
                ->get();

            $mealsData = $meals->map(function ($meal) {
                $dishType = '';
                switch ($meal->type) {
                    case 1:
                        $dishType = 'Buffet';
                        break;
                    case 2:
                        $dishType = 'Set Menu';
                        break;
                    default:
                        $dishType = 'Other';
                }

                $mealPeriodLabel = '';
                switch ($meal->meal_period) {
                    case 1:
                        $mealPeriodLabel = 'Breakfast';
                        break;
                    case 2:
                        $mealPeriodLabel = 'Lunch';
                        break;
                    case 3:
                        $mealPeriodLabel = 'Dinner';
                        break;
                }

                return [
                    'meal_id' => $meal->meal_id,
                    'name' => $meal->name,
                    'type' => $meal->type,
                    'type_label' => $dishType,
                    'meal_period' => $meal->meal_period,
                    'meal_period_label' => $mealPeriodLabel,
                    'price' => $meal->price,
                    'adult_price' => $meal->adult_price,
                    'child_price' => $meal->child_price,
                    'display_name' => $dishType
                ];
            });

            return response()->json([
                'success' => true,
                'meals' => $mealsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching meals: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Format time from 24-hour to 12-hour format
     */
    private function formatTimeTo12Hour($time)
    {
        if (!$time) return '';
        
        try {
            // Parse time (handle both HH:MM:SS and HH:MM formats)
            $carbonTime = \Carbon\Carbon::createFromFormat('H:i:s', $time);
            return $carbonTime->format('g:i A');
        } catch (\Exception $e) {
            try {
                // Try HH:MM format if HH:MM:SS fails
                $carbonTime = \Carbon\Carbon::createFromFormat('H:i', $time);
                return $carbonTime->format('g:i A');
            } catch (\Exception $e2) {
                return $time; // Return original if parsing fails
            }
        }
    }

    /**
     * Fetch zones for transportation dropdowns
     */
    public function fetchZones(Request $request)
    {
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            $city = $request->input('city');
            $city_id = City::where('name', $city)->first()->city_id;
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }
            // Fetch zones for the current DMC and city
            $zones = Zone::where('dmc_id', $dmcId)  
                ->where('status', 1)
                ->where('city', $city_id)
                ->select('zone_id', 'zone_name', 'zone_type', 'city', 'description')
                ->orderBy('zone_name')
                ->get();

            return response()->json([
                'success' => true,
                'zones' => $zones,
                'total_zones' => count($zones)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching zones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch vehicles based on zone mapping
     */
    public function fetchVehiclesByZones(Request $request)
    {
        
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            $fromZoneId = $request->from_zone_id;
            $toZoneId = $request->from_zone_id;
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            if (!$fromZoneId || !$toZoneId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Both pickup and dropoff zones are required'
                ], 400);
            }

            // Fetch vehicles that have zone mappings between the selected zones
            $vehicleMappings = VehicleZoneMapping::where('from_zone_id', $fromZoneId)
                ->where('to_zone_id', $toZoneId)
                ->get();
            // Format the response with vehicle details and pricing
            $vehicles = $vehicleMappings->map(function ($mapping) {
                $zone_vehicles = Vehicle::select('vehicle_id', 'vehicle_name', 'vehicle_type', 'seating_capacity', 'vehicle_model', 'image', 'base_price', 'sharable_base_price', 'service_type')->where('vehicle_id', $mapping->vehicle_id)->first();
                return [
                    'vehicle_id' => $zone_vehicles->vehicle_id,
                    'vehicle_name' => $zone_vehicles->vehicle_name,
                    'vehicle_type' => $zone_vehicles->vehicle_type,
                    'seating_capacity' => $zone_vehicles->seating_capacity,
                    'vehicle_model' => $zone_vehicles->vehicle_model,
                    'image' => $zone_vehicles->image,
                    'private_price' => $zone_vehicles->private_price,
                    'shared_price' => $zone_vehicles->shared_price,
                    'service_type' => $zone_vehicles->service_type,
                    'from_zone' => $mapping->fromZone->zone_name,
                    'to_zone' => $mapping->toZone->zone_name,
                    'mapping_id' => $mapping->mapping_id
                ];
            });

            return response()->json([
                'success' => true,
                'vehicles' => $vehicles,
                'total_vehicles' => count($vehicles),
                'from_zone_id' => $fromZoneId,
                'to_zone_id' => $toZoneId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching vehicles: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }
} 