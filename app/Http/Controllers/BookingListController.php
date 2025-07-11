<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use App\Models\Order;
use App\Models\Tour;
use App\Helpers\CommonHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $agent_ids = collect(); // for filtering bookings
        switch ($user->role_id) {
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

        // **FIX: Create a separate query just for getting unique tour_ids**
        $uniqueTourIdsQuery = DB::table('orders')
            ->select('orders.tour_id') // Only select tour_id, not the JSON data column
            ->where('orders.bookingType', '=', 'booking')
            ->where('orders.status', 1)
            ->when($agent_ids->isNotEmpty(), function ($query) use ($agent_ids) {
                $query->whereIn('orders.agent_id', $agent_ids);
            });

        // Now get unique tour IDs - this will work because we're not selecting JSON columns
        $uniqueTourIds = $uniqueTourIdsQuery->distinct()->pluck('tour_id');
        
        // Paginate the tour IDs (10 tours per page)
        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $paginatedTourIds = $uniqueTourIds->forPage($currentPage, $perPage);
        
        // Now get all bookings for the current page's tour IDs
        $bookings = DB::table('orders')
            ->select([
                'orders.id',
                'orders.tour_id',
                'orders.booking_id',
                'orders.agent_id',
                'orders.type',
                'orders.data',
                'orders.bookingType',
                'agents.name as agent_name',
                'orders.voucher_image'
            ])
            ->leftJoin('agents', 'orders.agent_id', '=', 'agents.agent_id')
            ->where('orders.bookingType', '=', 'booking')
            ->where('orders.status', 1)
            ->whereIn('orders.tour_id', $paginatedTourIds)
            ->when($agent_ids->isNotEmpty(), function ($query) use ($agent_ids) {
                $query->whereIn('orders.agent_id', $agent_ids);
            })
            ->orderBy('orders.id', 'desc')
            ->get();

        // Format and decode bookings
        $bookings = $bookings->map(function ($booking) {
            $types = [
                'hotel' => 'Hotel',
                'attraction' => 'Attraction',
                'guide' => 'Guide',
                'restaurant' => 'Restaurant',
                'travel_point' => 'Travel Point',
                'travel_hourly' => 'Travel Hourly',
                'exit_port' => 'Exit Port',
                'entry_port' => 'Entry Port'
            ];

            // Decode the JSON data
            $data = is_string($booking->data) ? json_decode($booking->data, true) : $booking->data;
            
            // Handle the data decoding more carefully
            if (is_array($data)) {
                // If it's a direct array of items
                if (isset($data[0])) {
                    $booking->data_decoded = $data;
                } else {
                    // If it's a single item
                    $booking->data_decoded = [$data];
                }
            } else {
                $booking->data_decoded = [];
            }

            // Process each item in data_decoded to ensure dates are properly formatted
            $booking->data_decoded = array_map(function($item) {
                // Handle booking dates
                if (isset($item['bookingDate'])) {
                    if (is_array($item['bookingDate'])) {
                        // For date ranges (like hotel bookings)
                        $item['bookingDate'] = array_map(function($date) {
                            return date('Y-m-d', strtotime($date));
                        }, $item['bookingDate']);
                    } else {
                        // For single dates
                        $item['bookingDate'] = date('Y-m-d', strtotime($item['bookingDate']));
                    }
                }

                // Handle other date fields
                if (isset($item['pickupdate'])) {
                    $item['pickupdate'] = date('Y-m-d', strtotime($item['pickupdate']));
                }
                if (isset($item['exitpickupdate'])) {
                    $item['exitpickupdate'] = date('Y-m-d', strtotime($item['exitpickupdate']));
                }

                return $item;
            }, $booking->data_decoded);

            // Set the type
            $booking->type = $types[$booking->type] ?? $booking->type;

            // Add DMC and Master DMC information
            if ($booking->agent_id) {
                $agent = Agent::where('agent_id', $booking->agent_id)->first();
                
                // Initialize DMC information
                $dmc_id = null;
                $dmc_name = 'N/A';
                $dmc_company = 'N/A';
                $master_dmc_name = 'N/A';
                $master_dmc_company = 'N/A';
                
                if ($agent) {
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
                }
                
                // Add the company information to the booking
                $booking->dmc_name = $dmc_name;
                $booking->dmc_company = $dmc_company;
                $booking->master_dmc_name = $master_dmc_name;
                $booking->master_dmc_company = $master_dmc_company;
            }

            return $booking;
        });

        // Create pagination manually
        $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
            $bookings, // Current page items
            $uniqueTourIds->count(), // Total items count
            $perPage, // Items per page
            $currentPage, // Current page
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        $pagination->appends($request->except('page'));

        // For use in JavaScript, etc.
        $dmcUsers = DB::table('users')
            ->select('userId', 'name', 'company_name')
            ->get()
            ->keyBy('userId')
            ->toArray();

        return view('bookingList.index', compact('bookings', 'dmcUsers', 'pagination'));
    }

    public function enquiry(Request $request)
    {
        $user = auth()->user();
        $agent_ids = collect(); // for filtering bookings
        switch ($user->role_id) {
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

        // **FIX: Create a separate query just for getting unique tour_ids**
        $uniqueTourIdsQuery = DB::table('orders')
            ->select('orders.tour_id') // Only select tour_id, not the JSON data column
            ->where('orders.bookingType', '=', 'enquiry')
            ->where('orders.status', 1)
            ->when($agent_ids->isNotEmpty(), function ($query) use ($agent_ids) {
                $query->whereIn('orders.agent_id', $agent_ids);
            });

        // Now get unique tour IDs - this will work because we're not selecting JSON columns
        $uniqueTourIds = $uniqueTourIdsQuery->distinct()->pluck('tour_id');
        
        // Paginate the tour IDs (10 tours per page)
        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $paginatedTourIds = $uniqueTourIds->forPage($currentPage, $perPage);
        
        // Now get all bookings for the current page's tour IDs
        $bookings = DB::table('orders')
            ->select([
                'orders.id',
                'orders.tour_id',
                'orders.booking_id',
                'orders.agent_id',
                'orders.type',
                'orders.data',
                'orders.bookingType',
                'agents.name as agent_name'
            ])
            ->leftJoin('agents', 'orders.agent_id', '=', 'agents.agent_id')
            ->where('orders.bookingType', '=', 'enquiry')
            ->where('orders.status', 1)
            ->whereIn('orders.tour_id', $paginatedTourIds)
            ->when($agent_ids->isNotEmpty(), function ($query) use ($agent_ids) {
                $query->whereIn('orders.agent_id', $agent_ids);
            })
            ->orderBy('orders.id', 'desc')
            ->get();

        // Format and decode bookings
        $bookings = $bookings->map(function ($booking) {
            $types = [
                'hotel' => 'Hotel',
                'attraction' => 'Attraction',
                'guide' => 'Guide',
                'restaurant' => 'Restaurant',
                'travel_point' => 'Travel Point',
                'travel_hourly' => 'Travel Hourly',
                'exit_port' => 'Exit Port',
                'entry_port' => 'Entry Port'
            ];

            // Decode the JSON data
            $data = is_string($booking->data) ? json_decode($booking->data, true) : $booking->data;
            
            // Handle the data decoding more carefully
            if (is_array($data)) {
                // If it's a direct array of items
                if (isset($data[0])) {
                    $booking->data_decoded = $data;
                } else {
                    // If it's a single item
                    $booking->data_decoded = [$data];
                }
            } else {
                $booking->data_decoded = [];
            }

            // Process each item in data_decoded to ensure dates are properly formatted
            $booking->data_decoded = array_map(function($item) {
                // Handle booking dates
                if (isset($item['bookingDate'])) {
                    if (is_array($item['bookingDate'])) {
                        // For date ranges (like hotel bookings)
                        $item['bookingDate'] = array_map(function($date) {
                            return date('Y-m-d', strtotime($date));
                        }, $item['bookingDate']);
                    } else {
                        // For single dates
                        $item['bookingDate'] = date('Y-m-d', strtotime($item['bookingDate']));
                    }
                }

                // Handle other date fields
                if (isset($item['pickupdate'])) {
                    $item['pickupdate'] = date('Y-m-d', strtotime($item['pickupdate']));
                }
                if (isset($item['exitpickupdate'])) {
                    $item['exitpickupdate'] = date('Y-m-d', strtotime($item['exitpickupdate']));
                }

                return $item;
            }, $booking->data_decoded);

            // Set the type
            $booking->type = $types[$booking->type] ?? $booking->type;

            // Add DMC and Master DMC information
            if ($booking->agent_id) {
                $agent = Agent::where('agent_id', $booking->agent_id)->first();
                
                // Initialize DMC information
                $dmc_id = null;
                $dmc_name = 'N/A';
                $dmc_company = 'N/A';
                $master_dmc_name = 'N/A';
                $master_dmc_company = 'N/A';
                
                if ($agent) {
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
                }
                
                // Add the company information to the booking
                $booking->dmc_name = $dmc_name;
                $booking->dmc_company = $dmc_company;
                $booking->master_dmc_name = $master_dmc_name;
                $booking->master_dmc_company = $master_dmc_company;
            }

            return $booking;
        });

        // Create pagination manually
        $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
            $bookings, // Current page items
            $uniqueTourIds->count(), // Total items count
            $perPage, // Items per page
            $currentPage, // Current page
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        $pagination->appends($request->except('page'));

        // For use in JavaScript, etc.
        $dmcUsers = DB::table('users')
            ->select('userId', 'name', 'company_name')
            ->get()
            ->keyBy('userId')
            ->toArray();

        return view('bookingList.enquiry', compact('bookings', 'dmcUsers', 'pagination'));
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

    public function cancelBooking(Request $request)
    {
        $booking_id = $request->booking_id;
        $order = Order::where('booking_id', $booking_id)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }
        $order->status = 4; // Canceled
        $order->cancel_reason = $request->cancel_reason;
        $order->save();
        return redirect()->back()->with('success', 'Booking canceled successfully.');
    }

    public function approveBooking(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:orders,booking_id',
            'reference_id' => 'required|string|max:255',
            'actual_due_date' => 'required|date',
            'display_due_date' => 'nullable|date',
            'reference_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png'
        ]);

        $order = Order::where('booking_id', $request->booking_id)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $order->is_approve = 1;
        $order->status = 3;
        $order->approval_id = $request->reference_id;

        if ($request->hasFile('reference_file')) {
            $referenceFile = $request->file('reference_file');
            $pathData = CommonHelper::image_path('approval_file', $referenceFile);
            $order->approval_file = $pathData['master_value'] ?? null;
        }

        $order->actual_due_date = $request->actual_due_date;

        // Use frontend-calculated value, fallback to actual_due_date if empty
        $order->display_due_date = $request->filled('display_due_date')
            ? $request->display_due_date
            : $request->actual_due_date;

        $order->save();

        return redirect()->back()->with('success', 'Booking approved successfully.');
    }

    /**
     * Update booking dates in the JSON data
     */
    public function updateBookingDates(Request $request)
    {
        $booking_id = $request->booking_id;
        $booking_type = $request->booking_type;
        
        // Find the order by booking ID
        $order = Order::where('booking_id', $booking_id)->first();
        
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Booking not found'], 404);
        }
        
        // Get the data - Laravel may already have cast this to an array due to the $casts in the model
        $data = $order->data;
        
        // If data is still a JSON string (not already decoded), decode it
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Invalid booking data'], 400);
        }

        // Check if data is an array without a bookingDate key (indicating it's an array of items)
        $isArrayOfItems = is_array($data) && !isset($data['bookingDate']);
        
        // Update the booking dates based on the booking type
        if ($booking_type == 'hotel') {
            // For hotel bookings, update the date range
            if (isset($request->check_in_date) && isset($request->check_out_date)) {
                // If data is an array of bookings
                if ($isArrayOfItems) {
                    foreach ($data as &$item) {
                        if (isset($item['bookingDate'])) {
                            $item['bookingDate'] = [$request->check_in_date, $request->check_out_date];
                        }
                    }
                } else {
                    // If data is a single booking
                    if (isset($data['bookingDate'])) {
                        $data['bookingDate'] = [$request->check_in_date, $request->check_out_date];
                    }
                }
            }
        } else {
            // For other booking types, update the single date
            if (isset($request->booking_date)) {
                // If data is an array of bookings
                if ($isArrayOfItems) {
                    foreach ($data as &$item) {
                        if (isset($item['bookingDate'])) {
                            // If bookingDate is an array, update the first value
                            if (is_array($item['bookingDate'])) {
                                $item['bookingDate'][0] = $request->booking_date;
                            } else {
                                $item['bookingDate'] = $request->booking_date;
                            }
                        }
                    }
                } else {
                    // If data is a single booking
                    if (isset($data['bookingDate'])) {
                        // If bookingDate is an array, update the first value
                        if (is_array($data['bookingDate'])) {
                            $data['bookingDate'][0] = $request->booking_date;
                        } else {
                            $data['bookingDate'] = $request->booking_date;
                        }
                    }
                }
            }
            
            // Check for additional fields to update based on booking type
            
            // Update visitTime for restaurant and attraction types
            if ($request->has_visit_time == '1' && $request->has('visit_time')) {
                // Format the visit time based on the booking type
                $formattedVisitTime = $request->visit_time;
                
                // For attraction, check if it's a range format or needs conversion
                if ($booking_type == 'attraction') {
                    // Check if the current format already has a range (contains a dash)
                    $existingVisitTime = '';
                    if ($isArrayOfItems && isset($data[0]['visitTime'])) {
                        $existingVisitTime = $data[0]['visitTime'];
                    } elseif (!$isArrayOfItems && isset($data['visitTime'])) {
                        $existingVisitTime = $data['visitTime'];
                    }
                    
                    // If current format has a range and the new value doesn't, we need to preserve the range
                    if (strpos($existingVisitTime, '-') !== false && strpos($formattedVisitTime, '-') === false) {
                        // Extract the time parts
                        $parts = explode('-', $existingVisitTime);
                        if (count($parts) == 2) {
                            // Just replace the start time, keep the end time
                            $formattedVisitTime = $formattedVisitTime . '-' . $parts[1];
                        }
                    }
                } else {
                    // For non-attraction types, convert 24-hour time to 12-hour format with AM/PM
                    if ($formattedVisitTime) {
                        // Parse the time
                        $timeParts = explode(':', $formattedVisitTime);
                        if (count($timeParts) == 2) {
                            $hour = (int)$timeParts[0];
                            $minute = $timeParts[1];
                            
                            $ampm = ($hour >= 12) ? 'PM' : 'AM';
                            $hour = $hour % 12;
                            if ($hour === 0) $hour = 12;
                            
                            $formattedVisitTime = $hour . ':' . $minute . ' ' . $ampm;
                        }
                    }
                }
                
                if ($isArrayOfItems) {
                    foreach ($data as &$item) {
                        $item['visitTime'] = $formattedVisitTime;
                    }
                } else {
                    $data['visitTime'] = $formattedVisitTime;
                }
            }
            
            // Update guide_name for guide type
            if ($request->has_guide_name == '1' && $request->has('guide_name')) {
                if ($isArrayOfItems) {
                    foreach ($data as &$item) {
                        $item['guide_name'] = $request->guide_name;
                    }
                } else {
                    $data['guide_name'] = $request->guide_name;
                }
            }
            
            // Update pickupdate for guide and entry_port types
            if ($request->has_pickup_date == '1' && $request->has('pickupdate')) {
                if ($isArrayOfItems) {
                    foreach ($data as &$item) {
                        $item['pickupdate'] = $request->pickupdate;
                    }
                } else {
                    $data['pickupdate'] = $request->pickupdate;
                }
            }
            
            // Update entrytime for guide and entry_port types
            if ($request->has_entry_time == '1' && $request->has('entrytime')) {
                // Format the entry time to make sure it's in 24-hour format
                $entrytime = $request->entrytime;
                
                if ($isArrayOfItems) {
                    foreach ($data as &$item) {
                        $item['entrytime'] = $entrytime;
                    }
                } else {
                    $data['entrytime'] = $entrytime;
                }
            }
        }
        
        // Update the order with the modified JSON data
        $order->data = $data; // Laravel will automatically encode this back to JSON
        $order->save();
        
        return redirect()->back()->with('success', 'Booking details updated successfully');
    }

    /**
     * Show the tour itinerary on a separate page.
     */
    public function showItinerary(string $tourId)
    {
        $user = auth()->user();
        $agent_ids = collect();
        
        // Similar logic to index method for filtering bookings based on user role
        switch ($user->role_id) {
            case 10: // Master DMC
                // Master DMC can see all DMCs' bookings
                $dmc_ids = User::where('master_dmc_id', $user->userId)
                                ->where('role_id', 11)
                                ->pluck('userId');
                $agent_ids = Agent::whereIn('sales_manager_dmc', $dmc_ids)->pluck('agent_id');
                break;
            case 11: // DMC
                $agent_ids = Agent::where('sales_manager_dmc', $user->userId)->pluck('agent_id');
                break;
            // Add other roles as needed
        }
        
        // Fetch all bookings for the specified tour
        $bookings = Order::with(['tour'])
        ->where('tour_id', $tourId)
        ->where('bookingType', 'booking')
        ->where('status', 1)
        ->when($agent_ids->isNotEmpty(), function ($query) use ($agent_ids) {
            $query->whereIn('agent_id', $agent_ids);
        })
        ->orderByDesc('booking_id')
        ->get();

        // Fetch tour details
        // $tourDetails = DB::table('tours')->where('tour_id', $tourId)->first();
        $tourDetails = Tour::with(['booking' => function ($query) {
        $query->where(function ($q) {
            $q->where('type', '!=', 'hotel')
            ->orWhere('status', '!=', 2);
        });
        }])->where('tour_id', $tourId)->first();

        if (!$bookings->count()) {
            return redirect()->back()->with('error', 'No itinerary found for this tour.');
        }
        
        // Format the bookings data
        $bookings = $this->formatBookings($bookings);
        
        // Group bookings by date for itinerary display
        $itineraryByDate = [];
        foreach ($bookings as $booking) {
            $data = $booking->data_decoded;
            
            // Extract date from the booking data
            $date = null;
            if (isset($data[0]['bookingDate'])) {
                if (is_array($data[0]['bookingDate'])) {
                    $date = $data[0]['bookingDate'][0] ?? null;
                } else {
                    $date = $data[0]['bookingDate'] ?? null;
                }
            } elseif (isset($data[0]['pickupdate'])) {
                $date = $data[0]['pickupdate'];
            } elseif (isset($data[0]['exitpickupdate'])) {
                $date = $data[0]['exitpickupdate'];
            }
            
            if ($date) {
                if (!isset($itineraryByDate[$date])) {
                    $itineraryByDate[$date] = [];
                }
                $itineraryByDate[$date][] = $booking;
            }
        }
        
        // Sort dates
        ksort($itineraryByDate);
        
        return view('bookingList.itinerary', [
            'tourId' => $tourId,
            'itineraryByDate' => $itineraryByDate,
            'tourDetails' => $tourDetails
        ]);
    }
    
    /**
     * Helper method to format bookings data
     */
    private function formatBookings($bookings)
    {
        return $bookings->map(function ($booking) {
            $types = [
                'hotel' => 'Hotel',
                'attraction' => 'Attraction',
                'guide' => 'Guide',
                'restaurant' => 'Restaurant',
                'travel_point' => 'Travel Point',
                'travel_hourly' => 'Travel Hourly',
                'exit_port' => 'Exit Port',
                'entry_port' => 'Entry Port'
            ];

            // Decode the JSON data
            $data = is_string($booking->data) ? json_decode($booking->data, true) : $booking->data;
            
            // Handle the data decoding more carefully
            if (is_array($data)) {
                // If it's a direct array of items
                if (isset($data[0])) {
                    $booking->data_decoded = $data;
                } else {
                    // If it's a single item
                    $booking->data_decoded = [$data];
                }
            } else {
                $booking->data_decoded = [];
            }

            // Process each item in data_decoded to ensure dates are properly formatted
            $booking->data_decoded = array_map(function($item) {
                // Handle booking dates
                if (isset($item['bookingDate'])) {
                    if (is_array($item['bookingDate'])) {
                        // For date ranges (like hotel bookings)
                        $item['bookingDate'] = array_map(function($date) {
                            return date('Y-m-d', strtotime($date));
                        }, $item['bookingDate']);
                    } else {
                        // For single dates
                        $item['bookingDate'] = date('Y-m-d', strtotime($item['bookingDate']));
                    }
                }

                // Handle other date fields
                if (isset($item['pickupdate'])) {
                    $item['pickupdate'] = date('Y-m-d', strtotime($item['pickupdate']));
                }
                if (isset($item['exitpickupdate'])) {
                    $item['exitpickupdate'] = date('Y-m-d', strtotime($item['exitpickupdate']));
                }

                return $item;
            }, $booking->data_decoded);

            // Set the type
            $booking->type = $types[$booking->type] ?? $booking->type;
            
            // Add DMC information (simplified for this method)
            $booking->dmc_company = 'N/A';
            if ($booking->agent_id) {
                $agent = Agent::where('agent_id', $booking->agent_id)->first();
                if ($agent && $agent->sales_manager_dmc) {
                    $dmc = User::where('userId', $agent->sales_manager_dmc)->first();
                    if ($dmc) {
                        $booking->dmc_company = $dmc->company_name ?? 'N/A';
                    }
                }
            }

            return $booking;
        });
    }

    /**
     * Update booking date via drag and drop
     */
    public function updateDate(Request $request)
    {
        try {
            $bookingId = $request->input('booking_id');
            $newDate = $request->input('new_date');
            $oldDate = $request->input('old_date');

            // Validate input
            if (empty($bookingId) || empty($newDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required parameters'
                ], 400);
            }

            // Find the booking
            $booking = Order::where('booking_id', $bookingId)->first();
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            // Check if the booking type is allowed to be moved
            $nonDraggableTypes = ['hotel', 'entry_port', 'exit_port'];
            if (in_array(strtolower($booking->type), $nonDraggableTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking type cannot be moved'
                ], 403);
            }

            // Get the current data
            $data = is_string($booking->data) ? json_decode($booking->data, true) : $booking->data;
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid booking data'
                ], 400);
            }

            // Determine if data is an array of items or a single item
            $isArrayOfItems = is_array($data) && !isset($data['bookingDate']);
            // Update the booking date
            if ($isArrayOfItems) {
                // Handle array of items
                foreach ($data as &$item) {
                    if (isset($item['bookingDate'])) {
                        if (is_array($item['bookingDate'])) {
                            // For date ranges, update the first date
                            $item['bookingDate'][0] = $newDate;
                        } else {
                            // For single dates
                            $item['bookingDate'] = $newDate;
                        }
                    }
                    
                    // Also update other date fields that might be present
                    if (isset($item['pickupdate'])) {
                        $item['pickupdate'] = $newDate;
                    }
                    if (isset($item['exitpickupdate'])) {
                        $item['exitpickupdate'] = $newDate;
                    }
                }
            } else {
                // Handle single item
                if (isset($data['bookingDate'])) {
                    if (is_array($data['bookingDate'])) {
                        // For date ranges, update the first date
                        $data['bookingDate'][0] = $newDate;
                    } else {
                        // For single dates
                        $data['bookingDate'] = $newDate;
                    }
                }
                
                // Also update other date fields that might be present
                if (isset($data['pickupdate'])) {
                    $data['pickupdate'] = $newDate;
                }
                if (isset($data['exitpickupdate'])) {
                    $data['exitpickupdate'] = $newDate;
                }
            }
            // Save the updated data
            $booking->data = $data;
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Booking date updated successfully',
                'booking_id' => $bookingId,
                'old_date' => $oldDate,
                'new_date' => $newDate
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating booking date: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the booking date'
            ], 500);
        }
    }
}