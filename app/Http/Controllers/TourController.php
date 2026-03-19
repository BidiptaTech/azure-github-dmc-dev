<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use \Illuminate\Support\Facades\Auth;
use App\Models\Guide;
use App\Models\Order;
use App\Models\User;
use App\Models\Agent;
use App\Models\Tour;
use Illuminate\Http\Request;
use Ramsey\Uuid\Guid\Guid;
use App\Models\City;
use App\Models\EnquiryForm;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\Package;
use App\Models\PackagedAttraction;


class TourController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!hasPermission('view tour')) {
            abort(403, 'You do not have permission to access this page.');
        }
        
        $user = auth()->user();
        $role_id = $user->role_id;
        $agent_ids = collect();
        
        // Step 1: Get the list of agent_ids based on user hierarchy
        if (in_array($role_id, [1, 2, 24])) {
            // Admin, Super Admin, or other top-level roles - can see all tours
            $agent_ids = null; // No agent_id filtering needed
        } else {
            // For all other roles, build hierarchical agent access
            switch ($role_id) {
                case 10: // Master DMC
                    $dmc_ids = User::where('master_dmc_id', $user->userId)
                                   ->where('role_id', 11) // DMCs
                                   ->pluck('userId');
            
                    $sales_heads = User::whereIn('created_by', $dmc_ids)
                                       ->whereIn('role_id', [33, 128, 129, 130, 134, 135, 136, 138])
                                       ->pluck('userId');
            
                    $sales_managers = User::whereIn('created_by', $sales_heads)
                                          ->whereIn('role_id', [12, 37])
                                          ->pluck('userId');
            
                    $assistant_managers = User::whereIn('created_by', $sales_managers)
                                              ->where('role_id', 38)
                                              ->pluck('userId');
            
                    $all_ids = collect($dmc_ids)
                        ->merge($sales_heads)
                        ->merge($sales_managers)
                        ->merge($assistant_managers)
                        ->unique()
                        ->filter();
            
                    $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)->pluck('agent_id');
                    break;
                    
                case 11: // DMC
                    $dmc_id = $user->userId;
                    $sales_heads = User::where('created_by', $dmc_id)
                        ->whereIn('role_id', [33, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138])
                        ->pluck('userId');

                    $sales_managers = User::whereIn('created_by', $sales_heads)
                        ->whereIn('role_id', [12, 37])
                        ->pluck('userId');

                    $assistant_managers = User::whereIn('created_by', $sales_managers)
                        ->where('role_id', 38)
                        ->pluck('userId');

                    $all_ids = collect([$dmc_id])
                        ->merge($sales_heads)
                        ->merge($sales_managers)
                        ->merge($assistant_managers)
                        ->unique()
                        ->filter();

                    $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)->pluck('agent_id');
                    break;

                case 33: // Sales Head
                case 128:
                case 129:
                case 130:
                case 134:
                case 135:
                case 136:
                case 138:
                    $sh_id = $user->userId;

                    $sales_managers = User::where('created_by', $sh_id)
                        ->whereIn('role_id', [12, 37])
                        ->pluck('userId');

                    $assistant_managers = User::whereIn('created_by', $sales_managers)
                        ->where('role_id', 38)
                        ->pluck('userId');

                    $all_ids = collect([$sh_id])
                        ->merge($sales_managers)
                        ->merge($assistant_managers)
                        ->unique()
                        ->filter();

                    $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)->pluck('agent_id');
                    break;

                case 12: // Sales Manager
                case 37:
                    $sm_id = $user->userId;

                    $assistant_managers = User::where('created_by', $sm_id)
                        ->where('role_id', 38)
                        ->pluck('userId');

                    $all_ids = collect([$sm_id])
                        ->merge($assistant_managers)
                        ->unique()
                        ->filter();

                    $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)->pluck('agent_id');
                    break;

                case 38: // Assistant Manager
                    $agent_ids = Agent::where('sales_manager_dmc', $user->userId)->pluck('agent_id');
                    break;
            }
        }
        
        // Step 2: Build the base query
        $query = Tour::with([
            'booking' => function ($query) {
                $query->where('bookingType', 'booking');
            },
            'agent'
        ])->orderBy('created_at', 'desc');
        
        // Step 3: Apply role-specific filters
        if ($agent_ids !== null && $agent_ids->isNotEmpty()) {
            // Filter by agent IDs if we have a list
            $query->whereIn('agent_id', $agent_ids);
        }
        
        // Apply additional role-specific filtering
        if (in_array($role_id, [36, 126, 127, 129, 131, 133, 134, 136, 137, 138])) {
            // Finance roles - only see tours with payment details
            $query->whereNotNull('payment_details');
        } elseif (in_array($role_id, [124, 125])) {
            // Specific roles that only see approved tours
            $query->whereIn('tour_status', ['Confirmed', 'Definite','Actual']);
        }
        
        // Step 4: Execute the query with pagination
        $tours = $query->paginate(10);
        
        // Step 5: Enrich tour data with additional info
        $toursQuery = [];
        foreach ($tours as $tour) {
            // Get agent information
            if ($tour->agent_id) {
                $agent = Agent::where('agent_id', $tour->agent_id)->first();
                
                if ($agent) {
                    // Initialize DMC information
                    $dmc_id = null;
                    $dmc_name = 'N/A';
                    $dmc_company = 'N/A';
                    $master_dmc_name = 'N/A';
                    $master_dmc_company = 'N/A';
                    
                    // Determine DMC ID based on agent role
                    switch ($agent->role_id) {
                        case 11: // Agent is a DMC
                            $dmc_id = $agent->userId ?? $agent->agent_id;
                            break;
                            
                        case 33: // Sales Head
                        case 128:
                        case 129:
                        case 130:
                        case 134:
                        case 135:
                        case 136:
                        case 138:
                            $salesManagerId = $agent->sales_manager_dmc;
                            $saleshead_dmc = User::where('userId', $salesManagerId)->first();
                            if ($saleshead_dmc) {
                                $dmc_users = User::where('userId', $saleshead_dmc->created_by)->first();
                                if ($dmc_users && $dmc_users->role_id == 11 || $dmc_users->role_id == 20) {
                                    $dmc_id = $dmc_users->userId;
                                }
                            }
                            break;
                            
                        case 12:
                        case 37: // Sales Manager
                            $salesManagerId = $agent->sales_manager_dmc;
                            $salesmng_dmc = User::where('userId', $salesManagerId)->first();
                            
                            if ($salesmng_dmc) {
                                $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first();
                                if ($saleshead_dmc) {
                                    $dmc_users = User::where('userId', $saleshead_dmc->created_by)->first();
                                    if ($dmc_users && $dmc_users->role_id == 11 || $dmc_users->role_id == 20) {
                                        $dmc_id = $dmc_users->userId;
                                    }
                                }
                            }
                            break;
                            
                        case 38: // Assistant Manager
                            $salesManagerId = $agent->sales_manager_dmc;
                            $asmng_dmc = User::where('userId', $salesManagerId)->first();
                            if ($asmng_dmc) {
                                $salesmng_dmc = User::where('userId', $asmng_dmc->created_by)->first();
                                if ($salesmng_dmc) {
                                    $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first();
                                    if ($saleshead_dmc) {
                                        $dmc_users = User::where('userId', $saleshead_dmc->created_by)->first();
                                        if ($dmc_users && $dmc_users->role_id == 11) {
                                            $dmc_id = $dmc_users->userId;
                                        }
                                    }
                                }
                            }
                            break;
                    }
                    
                    // Get DMC and Master DMC information if DMC ID is available
                    if ($dmc_id) {
                        $dmc = User::where('userId', $dmc_id)->first();
                        if ($dmc) {
                            $dmc_name = $dmc->name;
                            $dmc_company = $dmc->company_name;
                            
                            // Get Master DMC information if available
                            if ($dmc->master_dmc_id) {
                                $master_dmc = User::where('userId', $dmc->master_dmc_id)->first();
                                if ($master_dmc) {
                                    $master_dmc_name = $master_dmc->name;
                                    $master_dmc_company = $master_dmc->company_name;
                                }
                            }
                        }
                    }
                    
                    // Add the company information to the tour
                    $tour->dmc_name = $dmc_name;
                    $tour->dmc_company = $dmc_company;
                    $tour->master_dmc_name = $master_dmc_name;
                    $tour->master_dmc_company = $master_dmc_company;
                    $tour->agent_name = $agent->name;
                }
            }
            
            $toursQuery[] = $tour;
        }

        $guides = Guide::where('status', 1)->get();
        $drivers = Driver::where('status', 1)->get();

        // Get only the tour IDs from the current page
        $currentPageTourIds = $tours->pluck('tour_id')->toArray();
        
        // If no tours on current page, set empty array to avoid unnecessary queries
        if (empty($currentPageTourIds)) {
            $currentPageTourIds = [0]; // Use 0 to ensure empty results
        }

        $hotelss = Order::where('type', 'hotel')
                        ->where('bookingType', 'booking')
                        ->whereIn('status', [1, 2, 3])
                        ->whereIn('tour_id', $currentPageTourIds) // Only load for current page tours
                        ->orderBy('created_at', 'desc')
                        ->get();
        $hotels = [];

        foreach ($hotelss as $hotel) {
            // Decode only if it's a string
            $data = is_string($hotel->data) ? json_decode($hotel->data, true) : $hotel->data;

            if (!empty($data)) {
                $hotels[$hotel->tour_id][] = [
                    'id' => $hotel->id,
                    'tour_id' => $hotel->tour_id,
                    'booking_id' => $hotel->booking_id,
                    'user_id' => $hotel->user_id,
                    'amount' => $hotel->amount,
                    'status' => $hotel->status,
                    'hotel_details' => $data,
                ];
            }
        }

        //Attractions Details Fetch From Orders Table
        $attractionss = Order::where('type', 'attraction')
            ->where('bookingType', 'booking')
            ->whereIn('status', [1, 2, 3])
            ->whereIn('tour_id', $currentPageTourIds) // Only load for current page tours
            ->orderBy('created_at', 'desc')
            ->get();

        $attractions = [];

        foreach ($attractionss as $attraction) {
            // Decode only if it's a string
            $data = is_string($attraction->data) ? json_decode($attraction->data, true) : $attraction->data;

            if (!empty($data) && is_array($data)) {
                $attractions[$attraction->tour_id][] = [
                    'id' => $attraction->id,
                    'tour_id' => $attraction->tour_id,
                    'booking_id' => $attraction->booking_id,
                    'user_id' => $attraction->user_id,
                    'amount' => $attraction->amount,
                    'status' => $attraction->status,
                    'attraction_details' => $data,
                ];
            }
        }
        // dd($attractions);

        //Guides Details Fetch From Orders Table
        $guidess = Order::where('type', 'guide')
            ->where('bookingType', 'booking')
            ->whereIn('status', [1,2, 3])
            ->whereIn('tour_id', $currentPageTourIds) // Only load for current page tours
            ->get();

        $tour_guides = [];

        foreach ($guidess as $tour_guide) {
            // Decode only if it's a string
            $data = is_string($tour_guide->data) ? json_decode($tour_guide->data, true) : $tour_guide->data;

            if (!empty($data) && is_array($data)) {
                $tour_guides[$tour_guide->tour_id][] = [
                    'id' => $tour_guide->id,
                    'tour_id' => $tour_guide->tour_id,
                    'booking_id' => $tour_guide->booking_id,
                    'user_id' => $tour_guide->user_id,
                    'amount' => $tour_guide->amount,
                    'status' => $tour_guide->status,
                    'guide_details' => $data,
                ];
            }
        }
        // dd($tour_guides);

        //Restaurants Details Fetch From Orders Table
        $restaurantss = Order::where('type', 'restaurant')
            ->where('bookingType', 'booking')
            ->whereIn('status', [1, 2,3])
            ->whereIn('tour_id', $currentPageTourIds) // Only load for current page tours
            ->get();

        $restaurants = [];

        foreach ($restaurantss as $restaurant) {
            // Decode only if it's a string
            $data = is_string($restaurant->data) ? json_decode($restaurant->data, true) : $restaurant->data;

            if (!empty($data) && is_array($data)) {
                $restaurants[$restaurant->tour_id][] = [
                    'id' => $restaurant->id,
                    'tour_id' => $restaurant->tour_id,
                    'booking_id' => $restaurant->booking_id,
                    'user_id' => $restaurant->user_id,
                    'amount' => $restaurant->amount,
                    'status' => $restaurant->status,
                    'restaurant_details' => $data,
                ];
            }
        }
        // dd($restaurants);

        // Travels Details Fetch From Orders Table
        $travelss = Order::whereIn('type', ['travel_hourly', 'travel_point'])
            ->where('bookingType', 'booking')
            ->whereIn('status', [1,2, 3])
            ->whereIn('tour_id', $currentPageTourIds) // Only load for current page tours
            ->get()
            ->groupBy('tour_id'); // Group orders by 'tour_id'

        $travels = [];

        foreach ($travelss as $tour_id => $travelGroup) { // Loop through grouped results
            foreach ($travelGroup as $travel) { // Loop through each order in the group
                // Decode only if it's a string
                $data = is_string($travel->data) ? json_decode($travel->data, true) : $travel->data;

                if (!empty($data) && is_array($data)) {
                    $travels[$tour_id][] = [ // Use $tour_id from grouping
                        'id' => $travel->id,
                        'tour_id' => $tour_id, // Use the correct grouped tour_id
                        'booking_id' => $travel->booking_id,
                        'user_id' => $travel->user_id,
                        'amount' => $travel->amount,
                        'status' => $travel->status,
                        'travel_details' => $data,
                    ];
                }
            }
        }
        // dd($travels);

        // Pick Up & Drop Details Fetch From Orders Table
        $entrypickupss = Order::where('type', 'entry_port')
            ->where('bookingType', 'booking')
            ->whereIn('status', [1,2, 3])
            ->whereIn('tour_id', $currentPageTourIds) // Only load for current page tours
            ->get();

        $entrypickups = [];

        foreach ($entrypickupss as $entrypickup) {
            // Decode only if it's a string
            $data = is_string($entrypickup->data) ? json_decode($entrypickup->data, true) : $entrypickup->data;

            if (!empty($data) && is_array($data)) {
                $entrypickups[$entrypickup->tour_id][] = [
                    'id' => $entrypickup->id,
                    'tour_id' => $entrypickup->tour_id,
                    'booking_id' => $entrypickup->booking_id,
                    'user_id' => $entrypickup->user_id,
                    'amount' => $entrypickup->amount,
                    'status' => $entrypickup->status,
                    'entrypickup_details' => $data,
                ];
            }
        }
        // dd($entrypickups);

       $exitdropoffss = Order::where('type', 'exit_port')
        ->where('bookingType', 'booking')
        ->whereIn('status', [1, 2, 3])
        ->whereIn('tour_id', $currentPageTourIds) // Only load for current page tours
        ->get();

        $exitdropoffs = [];

        foreach ($exitdropoffss as $exitdropoff) {
            // Decode only if it's a string
            $data = is_string($exitdropoff->data) ? json_decode($exitdropoff->data, true) : $exitdropoff->data;

            if (!empty($data) && is_array($data)) {
                $exitdropoffs[$exitdropoff->tour_id][] = [
                    'id' => $exitdropoff->id,
                    'tour_id' => $exitdropoff->tour_id,
                    'booking_id' => $exitdropoff->booking_id,
                    'user_id' => $exitdropoff->user_id,
                    'amount' => $exitdropoff->amount,
                    'status' => $exitdropoff->status,
                    'exitdropoff_details' => $data,
                ];
            }
        }

        return view('tour.tour',compact('tours', 'guides', 'drivers', 'hotels', 'attractions', 'tour_guides', 'restaurants', 'travels', 'entrypickups', 'exitdropoffs'));
    }

    /**
     * Get tour prices (single sharing and double sharing)
     * 
     * @param int $tourId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTourPrices($tourId)
    {
        
        try {
            $prices = CommonHelper::calculateTourPrices($tourId);
            
            // Format segregated prices (per‑service contribution like hotel/attraction/restaurant, etc.)
            $segregatedFormatted = [];
            if (isset($prices['segregated']) && is_array($prices['segregated'])) {
                foreach ($prices['segregated'] as $serviceType => $servicePrices) {
                    $segregatedFormatted[$serviceType] = [
                        'single' => $servicePrices['single'] ?? 0,
                        'double' => $servicePrices['double'] ?? 0,
                        'single_formatted' => '₹' . number_format($servicePrices['single'] ?? 0, 2),
                        'double_formatted' => '₹' . number_format($servicePrices['double'] ?? 0, 2),
                    ];

                    if (isset($servicePrices['triple'])) {
                        $segregatedFormatted[$serviceType]['triple'] = $servicePrices['triple'];
                        $segregatedFormatted[$serviceType]['triple_formatted'] = '₹' . number_format($servicePrices['triple'], 2);
                    }

                    if (isset($servicePrices['baby_cot'])) {
                        $segregatedFormatted[$serviceType]['baby_cot'] = $servicePrices['baby_cot'];
                        $segregatedFormatted[$serviceType]['baby_cot_formatted'] = '₹' . number_format($servicePrices['baby_cot'], 2);
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'tour_id' => $tourId,
                'prices' => [
                    'single_sharing' => $prices['single_sharing'],
                    'double_sharing' => $prices['double_sharing'],
                    'triple_sharing' => $prices['triple_sharing'] ?? 0,
                    // Effective per-child sharing price built from attraction/restaurant child_price where available
                    'child_sharing' => $prices['child_sharing'] ?? 0,
                    'supplement' => $prices['single_sharing'] - $prices['double_sharing'],
                    // Per-service supplements list (items marked with `supplement: true`)
                    'supplements' => $prices['supplements'] ?? [],
                    // Backwards-compat alias (common misspelling in some frontends)
                    'supplyments' => $prices['supplements'] ?? [],
                    // 'single_sharing_formatted' => '₹' . number_format($prices['single_sharing'], 2),
                    // 'double_sharing_formatted' => '₹' . number_format($prices['double_sharing'], 2),
                    // 'triple_sharing_formatted' => '₹' . number_format($prices['triple_sharing'] ?? 0, 2),
                    'segregated' => $segregatedFormatted,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating prices: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assignGuide(Request $request)
    {
        $tour = Tour::where('tour_id',$request->tour_id)->first();
        $tour->assign_guide_id = $request->guide_id;
        $tour->save();

        if ($tour) {
            return response()->json([
                'status' => 'success', 
                'message' => 'Guide assigned successfully!'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Tour not found!',
            ]);
        }
    }

    public function removeGuide(Request $request)
    {
        $tour = Tour::where('tour_id',$request->tour_id)->first();

        if (!$tour) {
            return response()->json(['status' => 'error', 'message' => 'Tour not found']);
        }

        $tour->assign_guide_id = null; // Remove the assigned guide
        $tour->save();

        return response()->json(['status' => 'success', 'message' => 'Guide removed successfully']);
    }

    public function searchGuide(Request $request)
    {
        $search = $request->input('search');
        $guides = Guide::where('name', 'LIKE', "%{$search}%")->select('id', 'name')->get();

        return response()->json(['guides' => $guides]);
    }

    public function assignDriver(Request $request)
    {
        $tour = Tour::where('tour_id',$request->tour_id)->first();
        $tour->assign_driver_id = $request->driver_id;
        $tour->save();

        if ($tour) {
            return response()->json([
                'status' => 'success', 
                'message' => 'Driver assigned successfully!'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Tour not found!',
            ]);
        }
    }

    public function removeDriver(Request $request)
    {
        $tour = Tour::where('tour_id',$request->tour_id)->first();

        if (!$tour) {
            return response()->json(['status' => 'error', 'message' => 'Tour not found']);
        }

        $tour->assign_driver_id = null; // Remove the assigned guide
        $tour->save();

        return response()->json(['status' => 'success', 'message' => 'Driver removed successfully']);
    }

    public function searchDriver(Request $request)
    {
        $search = $request->input('search');
        $drivers = Driver::where('name', 'LIKE', "%{$search}%")->select('id', 'name')->get();

        return response()->json(['drivers' => $drivers]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function addPayment(Request $request, $tourId)
    {
        try {
            $tour = Tour::where('tour_id', $tourId)->first();
            
            if (!$tour) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tour not found!'
                    ], 404);
                }
                return redirect()->back()->with('error', 'Tour not found!');
            }
            
            // Validate the request
            $request->validate([
                'payment_amount' => 'required|numeric|min:0.01',
                'currency' => 'required|string',
                'payment_date' => 'required|date',
                'payment_type' => 'required|string',
                'auto_verify' => 'nullable|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
        
        try {
            // Get currency data from request
            $selectedCurrency = $request->input('currency', 'SGD');
            $exchangeRate = $request->input('exchange_rate', 1);
            $originalAmount = $request->input('payment_amount'); // The amount user entered
            
            // Calculate SGD amount based on currency
            if ($selectedCurrency === 'SGD') {
                $sgdAmount = $originalAmount;
            } else {
                // Convert foreign currency to SGD
                $sgdAmount = $originalAmount / $exchangeRate;
            }
        
        $paymentData = [
            'amount' => $sgdAmount, // Store SGD converted amount
            'original_amount' => $originalAmount, // Store original amount in selected currency
            'currency' => $selectedCurrency, // Store selected currency
            'exchange_rate' => $exchangeRate, // Store exchange rate used
            'transaction_id' => $request->transaction_id,
            'remarks' => $request->remarks,
            'date' => now()->format('Y-m-d H:i:s'),
            'payment_date' => $request->payment_date,
            'payment_type' => $request->payment_type,
            // status: 0 = unverified, 1 = verified
            'status' => $request->boolean('auto_verify') ? 1 : 0,
        ];
        
        // Get existing payment details or initialize empty array
        $paymentDetails = json_decode($tour->payment_details, true) ?: [];
        
        // Add new payment to the array
        $paymentDetails[] = $paymentData;
        $paymentIndex = count($paymentDetails) - 1;
        
        // Update the payment_details column with the new array
        $tour->payment_details = json_encode($paymentDetails);
        $tour->save();
        
        // Create success message with currency information
        $successMessage = 'Payment of ' . number_format($sgdAmount, 2) . ' SGD';
        if ($selectedCurrency !== 'SGD') {
            $successMessage .= ' (converted from ' . number_format($originalAmount, 2) . ' ' . $selectedCurrency . ')';
        }
        $successMessage .= ' has been successfully added to Tour #' . $tourId;
        
        // Check if request is AJAX and return JSON response
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'payment_index' => $paymentIndex,
                'verified' => (bool) ($request->boolean('auto_verify')),
            ]);
        }
        
            return redirect()->back()->with('success', $successMessage);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add payment: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to add payment: ' . $e->getMessage());
        }
    }

    public function approveBooking(Request $request, $tourId)
    {
        $tour = Tour::where('tour_id', $tourId)->first();
        if (!$tour) {
            return redirect()->back()->with('error', 'Tour not found!');
        }
        $tour->is_approve = 1;
        $tour->save();
        if($tour->tour_status == "Definite"){
            $tour = Tour::where('tour_id', $tourId)->update([
                'tour_status' => "Actual",
            ]);
        }
        return redirect()->back()->with('success', 'Tour has been approved successfully!');
    }

    public function verifyPayment(Request $request, $tourId)
    {
        try {
            // Find the tour by tour_id or return a 404 response if not found
            $tour = Tour::where('tour_id', $tourId)->firstOrFail();
            
            // Get payment index from request
            $paymentIndex = $request->input('payment_index');
            
            // Decode payment details
            $paymentDetails = json_decode($tour->payment_details, true);
            
            // Check if payment index exists
            if (!isset($paymentDetails[$paymentIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }
            
            // Update payment status to verified (1)
            $paymentDetails[$paymentIndex]['status'] = 1;
            
            // Update payment details on the tour model
            $tour->payment_details = json_encode($paymentDetails);
            
            // Only change status from Confirmed to Definite, NOT from Actual
            // Do NOT change status if tour is already in Actual or Definite status
            if ($tour->tour_status === "Confirmed") {
                // Track status change Confirmed -> Definite in track_details JSON
                \App\Helpers\CommonHelper::appendTourStatusTrackById(
                    (int) $tour->tour_id,
                    "Confirmed",
                    "Definite",
                    null,
                    null,
                    "Payment Verified",
                    null,
                    auth()->user() ? auth()->user()->name : null,
                    auth()->user() ? auth()->user()->id : null
                );

                // Update in‑memory status; will be persisted with the save below
                $tour->tour_status = "Definite";
            }

            // Save updated payment details (and possibly updated status)
            $tour->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error verifying payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function declinePayment(Request $request, $tourId)
    {
        try {
            // Find the tour by tour_id or return a 404 response if not found
            $tour = Tour::where('tour_id', $tourId)->firstOrFail();
            
            // Get payment index from request
            $paymentIndex = $request->input('payment_index');
            
            // Decode payment details
            $paymentDetails = json_decode($tour->payment_details, true);
            
            // Check if payment index exists
            if (!isset($paymentDetails[$paymentIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }
            
            // Update payment status to declined (2)
            $paymentDetails[$paymentIndex]['status'] = 2;
            
            // Save updated payment details
            $tour->payment_details = json_encode($paymentDetails);
            $tour->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment declined successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error declining payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deletePayment(Request $request, $tourId)
    {
        try {
            $tour = Tour::where('tour_id', $tourId)->firstOrFail();
            $paymentIndex = (int) $request->input('payment_index');

            $paymentDetails = json_decode($tour->payment_details, true) ?: [];
            if (!isset($paymentDetails[$paymentIndex])) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            array_splice($paymentDetails, $paymentIndex, 1);
            $tour->payment_details = json_encode(array_values($paymentDetails));
            $tour->save();

            return response()->json(['success' => true, 'message' => 'Payment removed successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Tour not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function updatePayment(Request $request, $tourId)
    {
        try {
            $tour = Tour::where('tour_id', $tourId)->firstOrFail();
            $paymentIndex = (int) $request->input('payment_index');

            $request->validate([
                'payment_amount' => 'required|numeric|min:0.01',
                'currency' => 'required|string',
                'payment_date' => 'required|date',
                'payment_type' => 'required|string'
            ]);

            $selectedCurrency = $request->input('currency', 'SGD');
            $exchangeRate = (float) $request->input('exchange_rate', 1);
            $originalAmount = (float) $request->input('payment_amount');

            $sgdAmount = $selectedCurrency === 'SGD' ? $originalAmount : $originalAmount / $exchangeRate;

            $paymentDetails = json_decode($tour->payment_details, true) ?: [];
            if (!isset($paymentDetails[$paymentIndex])) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            $paymentDetails[$paymentIndex] = [
                'amount' => $sgdAmount,
                'original_amount' => $originalAmount,
                'currency' => $selectedCurrency,
                'exchange_rate' => $exchangeRate,
                'transaction_id' => $request->input('transaction_id'),
                'remarks' => $request->input('remarks'),
                'date' => $paymentDetails[$paymentIndex]['date'] ?? now()->format('Y-m-d H:i:s'),
                'payment_date' => $request->payment_date,
                'payment_type' => $request->payment_type,
                'status' => $paymentDetails[$paymentIndex]['status'] ?? 0,
            ];

            $tour->payment_details = json_encode($paymentDetails);
            $tour->save();

            return response()->json(['success' => true, 'message' => 'Payment updated successfully']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Tour not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function createTour(Request $request)
    {
        $user = auth()->user();
        $role_id = $user->role_id;
        if(!in_array($role_id, [1, 2, 3, 4, 10, 11, 25, 33, 37, 38, 128, 129, 130, 134, 135, 136, 138])){
            return response()->json([
                'message' => 'You are not authorized to create a tour',
            ], 403);
        }
        $validatedData = $request->validate([
            'destination' => 'required|string|max:255',
            'adult' => 'required|integer|min:0',
            'child' => 'nullable|integer|min:0',
            'infant' => 'nullable|integer|min:0',
            'check_in' => 'required|date_format:d/m/Y',
            'check_out' => 'required|date_format:d/m/Y|after_or_equal:check_in',
            'male' => 'required|integer|min:0',
            'female' => 'required|integer|min:0',
            'children_ages' => 'nullable|string',
        ]);
        $countryNames = request()->input('destination');
        $agent_id = request()->header('agent-id') ?? request()->header('agent_id');
        $enquiryId = $request->enquiry_id;
        $countryArray = array_map('trim', explode(',', $countryNames));
        $cities = City::whereIn('country', $countryArray)
              ->select('name', 'country')
              ->get()
              ->map(fn($city) => "{$city->name}, ({$city->country})")
              ->toArray();
        // $user = Agent::where('agent_id',);
        // $agent_id = $user->agent_id;

        try {
            // Parse the dates
            $checkInTime = Carbon::createFromFormat('d/m/Y', $request->check_in);
            $checkOutTime = Carbon::createFromFormat('d/m/Y', $request->check_out);

            // Generate tour ID and save the tour
            $max_tour_id = Tour::max('tour_id') ?? 0;
            $tourId = CommonHelper::createId($max_tour_id);

            $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT); 
            $display_id = 'DMC-ORD'. $tourId;
            $formEnquiry = null;
            if($enquiryId){
            $formEnquiry = EnquiryForm::where('enquiry_id', $enquiryId)
                                    //   ->where('agent_id', $agent_id)
                                    //   ->whereNull('unique_tour_id')
                                      ->first();
            $multi_enq_id = $formEnquiry->multi_enq_id ?? '';
            }
            $tour = new Tour();
            $tour->destination = $validatedData['destination'];
            $tour->adult = $validatedData['adult'];
            $tour->child = $validatedData['child'] ?? 0;
            $tour->infant = $validatedData['infant'] ?? 0;
            $tour->agent_id = $agent_id;
            $tour->tour_id = $tourId;
            $tour->male_count = $validatedData['male'];
            $tour->female_count = $validatedData['female'];
            $tour->check_in_time = $checkInTime;
            $tour->check_out_time = $checkOutTime;
            $tour->display_id = $display_id;
            $tour->tour_status = "New Enquiry";
            $tour->city = $request->city;
            $tour->dmc_id = $request->dmc_id;
            $tour->reference_id = $request->reference_id ?? null;
            $tour->multi_enq_id = $multi_enq_id ?? '';
            $tour->child_ages = $validatedData['children_ages'] ?? null;
            
            // Check if service IDs are present and set 1 or 0 in tours table
            $tour->hotel = $this->checkServicePresent($request->hotel_ids);
            $tour->attraction = $this->checkServicePresent($request->attraction_ids);
            $tour->restaurent = $this->checkServicePresent($request->restaurant_ids);
            $tour->guide = $this->checkServicePresent($request->guide_ids);
            
            $tour->save();
            $tour->refresh();
            if($formEnquiry){
                $formEnquiry->unique_tour_id = $tour->unique_tour_id;
                $formEnquiry->save();
            }

            $service = CommonHelper::CommonResponse($agent_id, $tour->tour_id);
            // LogActivityService::log('create_tour', 'App\Models\Tour', $tourId, $tour);

            // Always define as empty collections/arrays
            $hotels = [];
            $attraction = [];
            $restaurant = [];
            $guide = [];
            $drivers = [];
            $ports = [];
            $packagedAttractions = [];
            $port_details = [];
            $dropoff_details = [];
            $name = null;
            $exit_name = null;
            $id = null;
            $exit_id = null;

            if ($formEnquiry) {
                // Get hotel details
                if (!empty($formEnquiry->hotel_ids)) {
                    $hotelIds = json_decode($formEnquiry->hotel_ids, true);
                    $hotels = Hotel::select('hotel_unique_id', 'name', 'main_image')->whereIn('hotel_unique_id', $hotelIds)->get();
                }

                // Get attraction details
                if (!empty($formEnquiry->attraction_ids)) {
                    $attractionIds = json_decode($formEnquiry->attraction_ids, true);
                    $attraction = Attraction::select('attraction_id', 'name', 'master_image')->whereIn('attraction_id', $attractionIds)->get();
                }

                // Get restaurant details
                if (!empty($formEnquiry->restaurant_ids)) {
                    $restaurantIds = json_decode($formEnquiry->restaurant_ids, true);
                    $restaurant = Restaurant::select('restaurant_id', 'name', 'master_image')->whereIn('restaurant_id', $restaurantIds)->get();
                }

                // Get guide details
                if (!empty($formEnquiry->guide_ids)) {
                    $guideIds = json_decode($formEnquiry->guide_ids, true);
                    $guide = Guide::select('guide_id', 'name', 'image')->whereIn('guide_id', $guideIds)->get();
                }

                // Get driver details
                if (!empty($formEnquiry->local_transport_vehicle_ids)) {
                    $driverIds = json_decode($formEnquiry->local_transport_vehicle_ids, true);
                    $drivers = \App\Models\Vehicle::select('vehicle_id', 'vehicle_name', 'vehicle_type', 'vehicle_model','image')->whereIn('vehicle_id', $driverIds)->get();
                }

                // Get port details
                if (!empty($formEnquiry->port_ids)) {
                    $portIds = json_decode($formEnquiry->port_ids, true);
                    $ports = \App\Models\Port::select('port_id', 'port_name', 'type', 'country', 'city_id')->whereIn('port_id', $portIds)->get();
                }

                // Get packaged attraction details
                if (!empty($formEnquiry->packaged_attraction_ids)) {
                    $packagedAttractionIds = json_decode($formEnquiry->packaged_attraction_ids, true);
                    $packagedAttractions = PackagedAttraction::select('package_attraction_id', 'name', 'image')->whereIn('package_attraction_id', $packagedAttractionIds)->get();
                }

                // Handle entry dropoff
                if (!empty($formEnquiry->entry_dropoff_type) && !empty($formEnquiry->entry_dropoff_location_id)) {
                    $id = $formEnquiry->entry_dropoff_location_id;
                    
                    if ($formEnquiry->entry_dropoff_type === 'hotel') {
                        $hotel = \App\Models\Hotel::where('hotel_unique_id', $id)->first();
                        $name = $hotel ? $hotel->name : null;
                    } elseif ($formEnquiry->entry_dropoff_type === 'attraction') {
                        $attraction = \App\Models\Attraction::where('attraction_id', $id)->first();
                        $name = $attraction ? $attraction->name : null;
                    } elseif ($formEnquiry->entry_dropoff_type === 'restaurant') {
                        $restaurant = \App\Models\Restaurant::where('restaurant_id', $id)->first();
                        $name = $restaurant ? $restaurant->name : null;
                    }
                }

                // Handle exit pickup
                if (!empty($formEnquiry->exit_pickup_type) && !empty($formEnquiry->exit_pickup_location_id)) {
                    $exit_id = $formEnquiry->exit_pickup_location_id;
                    
                    if ($formEnquiry->exit_pickup_type === 'hotel') {
                        $hotel = \App\Models\Hotel::where('hotel_unique_id', $exit_id)->first();
                        $exit_name = $hotel ? $hotel->name : null;
                    } elseif ($formEnquiry->exit_pickup_type === 'attraction') {
                        $attraction = \App\Models\Attraction::where('attraction_id', $exit_id)->first();
                        $exit_name = $attraction ? $attraction->name : null;
                    } elseif ($formEnquiry->exit_pickup_type === 'restaurant') {
                        $restaurant = \App\Models\Restaurant::where('restaurant_id', $exit_id)->first();
                        $exit_name = $restaurant ? $restaurant->name : null;
                    }
                }

                // Combine port and location details
                if (!empty($formEnquiry->entry_port_address) || !empty($formEnquiry->exit_port_address)) {
                    $port_details = [
                        [
                            'type' => 'entry',
                            'port_address' => $formEnquiry->entry_port_address,
                            'location_type' => $formEnquiry->entry_dropoff_type,
                            'location_id' => $id,
                            'dropoff_name' => $name
                        ],
                        [
                            'type' => 'exit',
                            'port_address' => $formEnquiry->exit_port_address,
                            'location_type' => $formEnquiry->exit_pickup_type,
                            'location_id' => $exit_id,
                            'dropoff_name' => $exit_name
                        ]
                    ];
                }
            }

            return response()->json([
                'message' => 'Tour created successfully',
                'tour_id' => $tour->unique_tour_id,
                
            ], 201);
        } catch (\Exception $e) {
            // LogActivityService::log('create_tour_failed', 'App\Models\Tour', $tourId ?? null, json_encode($e->getMessage()));
            return response()->json([
                'message' => 'An error occurred while creating the tour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if service IDs are present
     * 
     * @param string|array|null $serviceIds
     * @return int (1 if present, 0 if not present)
     */
    private function checkServicePresent($serviceIds)
    {
        if (empty($serviceIds)) {
            return 0;
        }

        // If already an array, check if it has items
        if (is_array($serviceIds)) {
            return count($serviceIds) > 0 ? 1 : 2;
        }

        // If string, decode JSON and check if it has items
        if (is_string($serviceIds)) {
            $idsArray = json_decode($serviceIds, true);
            return (is_array($idsArray) && count($idsArray) > 0) ? 1 : 2;
        }

        return 0;
    }
}
