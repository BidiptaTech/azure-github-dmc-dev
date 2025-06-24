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
                                       ->where('role_id', 33)
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
                        ->where('role_id', 33)
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
        if (in_array($role_id, [36, 126, 127])) {
            // Finance roles - only see tours with payment details
            $query->whereNotNull('payment_details');
        } elseif (in_array($role_id, [124, 125])) {
            // Specific roles that only see approved tours
            $query->where('is_approve', 1);
        }
        
        // Step 4: Execute the query and get results
        $tours = $query->get();
        
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
                            $salesManagerId = $agent->sales_manager_dmc;
                            $saleshead_dmc = User::where('userId', $salesManagerId)->first();
                            if ($saleshead_dmc) {
                                $dmc_users = User::where('userId', $saleshead_dmc->created_by)->first();
                                if ($dmc_users && $dmc_users->role_id == 11) {
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
                                    if ($dmc_users && $dmc_users->role_id == 11) {
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

        $hotelss = Order::where('type', 'hotel')
                        
                        ->where('bookingType', 'booking')
                        ->whereIn('status', [1, 2, 3])
                        ->whereNotNull('tour_id') // Ensure tour_id exists
                        ->orderBy('created_at', 'desc') // Order by latest created_at
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
            ->whereNotNull('tour_id') // Ensure tour_id exists
            ->orderBy('created_at', 'desc') // Order by latest created_at
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
            ->whereNotNull('tour_id')
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
            ->whereNotNull('tour_id')
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
            ->whereNotNull('tour_id')
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
            ->whereNotNull('tour_id')
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
        ->whereNotNull('tour_id')
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
        $tour = Tour::where('tour_id', $tourId)->first();
        
        if (!$tour) {
            return redirect()->back()->with('error', 'Tour not found!');
        }
        
        // Get currency data from request
        $selectedCurrency = $request->input('selected_currency', 'SGD');
        $exchangeRate = $request->input('exchange_rate', 1);
        $originalAmount = $request->input('original_amount', $request->amount);
        $sgdAmount = $request->input('sgd_amount', $request->amount);
        
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
            'status' => 0,
        ];
        
        // Get existing payment details or initialize empty array
        $paymentDetails = json_decode($tour->payment_details, true) ?: [];
        
        // Add new payment to the array
        $paymentDetails[] = $paymentData;
        
        // Update the payment_details column with the new array
        $tour->payment_details = json_encode($paymentDetails);
        $tour->save();
        
        // Create success message with currency information
        $successMessage = 'Payment of ' . number_format($sgdAmount, 2) . ' SGD';
        if ($selectedCurrency !== 'SGD') {
            $successMessage .= ' (converted from ' . number_format($originalAmount, 2) . ' ' . $selectedCurrency . ')';
        }
        $successMessage .= ' has been successfully added to Tour #' . $tourId;
        
        return redirect()->back()->with('success', $successMessage);
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
            
            // Save updated payment details
            $tour->payment_details = json_encode($paymentDetails);
            $tour->save();
            if($tour->tour_status = "On Hold"){
                $tour_status = Tour::where('tour_id', $tourId)->update([
                            'tour_status' => "Definite",
                        ]);
            }

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
}
