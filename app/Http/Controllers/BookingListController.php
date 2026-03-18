<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\BankDetail;
use App\Models\User;
use App\Models\Order;
use App\Models\Tour;
use App\Helpers\CommonHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;

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
                    ->whereIn('role_id', [33, 128, 129, 130, 134, 135, 136, 138])
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
            ->orderBy('orders.updated_at', 'desc')
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
                'orders.voucher_image',
                'agents.name as agent_name'
            ])
            ->leftJoin('agents', 'orders.agent_id', '=', 'agents.agent_id')
            ->where('orders.bookingType', '=', 'enquiry')
            ->where('orders.status', 1)
            ->whereIn('orders.tour_id', $paginatedTourIds)
            ->when($agent_ids->isNotEmpty(), function ($query) use ($agent_ids) {
                $query->whereIn('orders.agent_id', $agent_ids);
            })
            ->orderBy('orders.updated_at', 'desc')
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
    public function showItinerary($tourId)
    {
        $user = auth()->user();
        $agent_ids = collect();
        $tourId = Crypt::decrypt($tourId);
        // Similar logic to index method for filtering bookings based on user role
        // switch ($user->role_id) {
        //     case 10: // Master DMC
        //         $dmc_ids = User::where('master_dmc_id', $user->userId)
        //                         ->where('role_id', 11) // DMCs
        //                         ->pluck('userId');
        
        //         $sales_heads = User::whereIn('created_by', $dmc_ids)
        //                             ->whereIn('role_id', [33, 128, 129, 130, 134, 135, 136, 138])
        //                             ->pluck('userId');
        
        //         $sales_managers = User::whereIn('created_by', $sales_heads)
        //                                 ->whereIn('role_id', [12, 37])
        //                                 ->pluck('userId');
        
        //         $assistant_managers = User::whereIn('created_by', $sales_managers)
        //                                     ->where('role_id', 38)
        //                                     ->pluck('userId');
        
        //         $all_ids = collect($dmc_ids)
        //             ->merge($sales_heads)
        //             ->merge($sales_managers)
        //             ->merge($assistant_managers)
        //             ->unique()
        //             ->filter();
        
        //         $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)->pluck('agent_id');
        //         break;
                
        //     case 11: // DMC
        //         $dmc_id = $user->userId;

        //         $sales_heads = User::where('created_by', $dmc_id)
        //             ->whereIn('role_id', [33, 128, 129, 130, 134, 135, 136, 138])
        //             ->pluck('userId');

        //         $sales_managers = User::whereIn('created_by', $sales_heads)
        //             ->whereIn('role_id', [12, 37])
        //             ->pluck('userId');

        //         $assistant_managers = User::whereIn('created_by', $sales_managers)
        //             ->where('role_id', 38)
        //             ->pluck('userId');

        //         $all_ids = collect([$dmc_id])
        //             ->merge($sales_heads)
        //             ->merge($sales_managers)
        //             ->merge($assistant_managers)
        //             ->unique()
        //             ->filter();

        //         $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)->pluck('agent_id');
        //         break;

        //     case 33: // Sales Head
        //     case 128:
        //     case 129:
        //     case 130:
        //     case 134:
        //     case 135:
        //     case 136:
        //     case 138:
        //         $sh_id = $user->userId;

        //         $sales_managers = User::where('created_by', $sh_id)
        //             ->whereIn('role_id', [12, 37])
        //             ->pluck('userId');

        //         $assistant_managers = User::whereIn('created_by', $sales_managers)
        //             ->where('role_id', 38)
        //             ->pluck('userId');

        //         $all_ids = collect([$sh_id])
        //             ->merge($sales_managers)
        //             ->merge($assistant_managers)
        //             ->unique()
        //             ->filter();

        //         $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)->pluck('agent_id');
        //         break;
        //         dd($agent_ids);

        //     case 12: // Sales Manager
        //     case 37:
        //         $sm_id = $user->userId;

        //         $assistant_managers = User::where('created_by', $sm_id)
        //             ->where('role_id', 38)
        //             ->pluck('userId');

        //         $all_ids = collect([$sm_id])
        //             ->merge($assistant_managers)
        //             ->unique()
        //             ->filter();

        //         $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)->pluck('agent_id');
        //         break;

        //     case 38: // Assistant Manager
        //         $agent_ids = Agent::where('sales_manager_dmc', $user->userId)->pluck('agent_id');
        //         break;
        // }
        
        // Fetch all bookings for the specified tour
        $tour = Tour::where('tour_id', $tourId)->first();
        
        // If tour doesn't exist, redirect back
        if (!$tour) {
            return redirect()->back()->with('error', 'Tour not found.');
        }
        
        $user_dmc = null;
        if ($tour && $tour->dmc_id) {
            $user_dmc = User::select('name', 'email', 'phone', 'company_name','logo', 'country', 'city', 'address')->where('userId', $tour->dmc_id)->first();
            
            // Convert logo URL to base64 data URL to avoid CORS issues
            if ($user_dmc && $user_dmc->logo) {
                try {
                    $logoUrl = $user_dmc->logo;
                    // Check if it's already a data URL
                    if (!str_starts_with($logoUrl, 'data:image')) {
                        // Fetch image content server-side
                        $context = stream_context_create([
                            'http' => [
                                'method' => 'GET',
                                'header' => [
                                    'User-Agent: Mozilla/5.0',
                                    'Accept: image/png,image/jpeg,image/*,*/*'
                                ],
                                'timeout' => 10,
                                'ignore_errors' => true
                            ]
                        ]);
                        
                        $imageContent = @file_get_contents($logoUrl, false, $context);
                        if ($imageContent !== false) {
                            // Determine image type
                            $imageInfo = @getimagesizefromstring($imageContent);
                            if ($imageInfo !== false) {
                                $mimeType = $imageInfo['mime'];
                                $base64 = base64_encode($imageContent);
                                $user_dmc->logo = 'data:' . $mimeType . ';base64,' . $base64;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // If conversion fails, keep original URL
                    \Log::warning('Failed to convert logo to base64: ' . $e->getMessage());
                }
            }
        }
        
        $bookings = Order::with(['tour'])
        ->where('tour_id', $tourId)
        ->where('bookingType', 'booking')
        ->where('status', 1)
        ->when($agent_ids->isNotEmpty(), function ($query) use ($agent_ids) {
            $query->whereIn('agent_id', $agent_ids);
        })
        ->orderByDesc('booking_id')
        ->get();
        
        // Get agent/agency information from tour or first booking
        $agent_info = null;
        $agent_id = null;
        
        // First try to get from tour
        if ($tour && $tour->agent_id) {
            $agent_id = $tour->agent_id;
        }
        // If not in tour, try from first booking
        elseif ($bookings->count() > 0 && $bookings->first()->agent_id) {
            $agent_id = $bookings->first()->agent_id;
        }
        
        // Fetch agent details if we have an agent_id
        if ($agent_id) {
            $agent = \App\Models\Agent::with('agency')->where('agent_id', $agent_id)->first();
            if ($agent) {
                $agency = $agent->agency;
                
                // Use agency data if available, otherwise fall back to agent data
                $agent_info = [
                    'name' => $agent->name ?? '',
                    'company_name' => ($agency && $agency->agency_name) ? $agency->agency_name : '',
                    'address' => ($agency && $agency->address) ? $agency->address : '',
                    'contact_person' => ($agency && $agency->contact_person) ? $agency->contact_person : ($agent->name ?? ''),
                    'phone' => ($agency && $agency->phone) ? $agency->phone : ($agent->phone ?? ''),
                    'email' => ($agency && $agency->email) ? $agency->email : ($agent->email ?? ''),
                    'agent_name' => ($agent->name ?? ''),
                    'agent_phone' => ($agent->phone ?? ''),
                    'agent_email' => ($agent->email ?? ''),
                    'agent_address' => ($agent->address ?? ''),
                ];
            }
        }

        // Fetch tour details
        // $tourDetails = DB::table('tours')->where('tour_id', $tourId)->first();
        $tourDetails = Tour::with(['booking' => function ($query) {
        $query->where(function ($q) {
            $q->where('type', '!=', 'hotel')
            ->orWhere('status', '!=', 2);
        });
        }])->where('tour_id', $tourId)->first();

        // Initialize itineraryByDate as empty array
        $itineraryByDate = [];
        
        // Only process bookings if they exist
        if ($bookings->count() > 0) {
            // Format the bookings data
            $bookings = $this->formatBookings($bookings);
            
            // Group bookings by date for itinerary display
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
        }
        
        // Get DMC price_hide setting based on user hierarchy
        $priceHide = 1; // Default to show prices
        $currentUser = auth()->user();
        
        if ($currentUser) {
            $dmcId = $this->getDmcIdByUserRole($currentUser);
            if ($dmcId) {
                $dmc = \App\Models\User::where('userId', $dmcId)->first();
                if ($dmc) {
                    $priceHide = $dmc->price_hide ?? 0; // Default to 0 (show prices) if not set
                }
            }
        }
        
        // Extract passengers from tour's mainguest and additionalguest columns
        $allPassengers = [];
        
        // Extract main guest from mainguest column
        if (!empty($tour->mainguest)) {
            try {
                $mainguestData = is_string($tour->mainguest) ? json_decode($tour->mainguest, true) : $tour->mainguest;
                if (is_array($mainguestData) && !empty($mainguestData)) {
                    $mainGuest = [
                        'salutation' => $mainguestData['salutation'] ?? 'Mr',
                        'first_name' => $mainguestData['full_name'] ?? '',
                        'name' => $mainguestData['full_name'] ?? '',
                        'passenger_type' => $mainguestData['passenger_type'] ?? 'Adult',
                        'gender' => $mainguestData['gender'] ?? 'M',
                        'mobile_phone' => $mainguestData['phone'] ?? '',
                        'phone' => $mainguestData['phone'] ?? '',
                        'email' => $mainguestData['email'] ?? '',
                    ];
                    // Add country code to phone if available
                    if (!empty($mainguestData['country_code']) && !empty($mainGuest['phone'])) {
                        $mainGuest['mobile_phone'] = '+' . $mainguestData['country_code'] . ' ' . $mainGuest['phone'];
                        $mainGuest['phone'] = '+' . $mainguestData['country_code'] . ' ' . $mainGuest['phone'];
                    }
                    $allPassengers[] = $mainGuest;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to parse mainguest data from tour', [
                    'tour_id' => $tourId,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Extract additional guests from additionalguest column
        if (!empty($tour->additionalguest)) {
            try {
                $additionalGuestsData = is_string($tour->additionalguest) ? json_decode($tour->additionalguest, true) : $tour->additionalguest;
                if (is_array($additionalGuestsData)) {
                    // Check if it's an array of guests or a single guest object
                    if (isset($additionalGuestsData[0]) && is_array($additionalGuestsData[0])) {
                        // Array of guests
                        foreach ($additionalGuestsData as $guestData) {
                            if (is_array($guestData) && !empty($guestData)) {
                                $additionalGuest = [
                                    'salutation' => $guestData['salutation'] ?? 'Mr',
                                    'first_name' => $guestData['full_name'] ?? $guestData['name'] ?? '',
                                    'name' => $guestData['full_name'] ?? $guestData['name'] ?? '',
                                    'passenger_type' => $guestData['passenger_type'] ?? 'Adult',
                                    'gender' => $guestData['gender'] ?? 'M',
                                    'mobile_phone' => $guestData['phone'] ?? '',
                                    'phone' => $guestData['phone'] ?? '',
                                    'email' => $guestData['email'] ?? '',
                                ];
                                // Add country code to phone if available
                                if (!empty($guestData['country_code']) && !empty($additionalGuest['phone'])) {
                                    $additionalGuest['mobile_phone'] = '+' . $guestData['country_code'] . ' ' . $additionalGuest['phone'];
                                    $additionalGuest['phone'] = '+' . $guestData['country_code'] . ' ' . $additionalGuest['phone'];
                                }
                                $allPassengers[] = $additionalGuest;
                            }
                        }
                    } else {
                        // Single guest object
                        if (is_array($additionalGuestsData) && !empty($additionalGuestsData)) {
                            $additionalGuest = [
                                'salutation' => $additionalGuestsData['salutation'] ?? 'Mr',
                                'first_name' => $additionalGuestsData['full_name'] ?? $additionalGuestsData['name'] ?? '',
                                'name' => $additionalGuestsData['full_name'] ?? $additionalGuestsData['name'] ?? '',
                                'passenger_type' => $additionalGuestsData['passenger_type'] ?? 'Adult',
                                'gender' => $additionalGuestsData['gender'] ?? 'M',
                                'mobile_phone' => $additionalGuestsData['phone'] ?? '',
                                'phone' => $additionalGuestsData['phone'] ?? '',
                                'email' => $additionalGuestsData['email'] ?? '',
                            ];
                            // Add country code to phone if available
                            if (!empty($additionalGuestsData['country_code']) && !empty($additionalGuest['phone'])) {
                                $additionalGuest['mobile_phone'] = '+' . $additionalGuestsData['country_code'] . ' ' . $additionalGuest['phone'];
                                $additionalGuest['phone'] = '+' . $additionalGuestsData['country_code'] . ' ' . $additionalGuest['phone'];
                            }
                            $allPassengers[] = $additionalGuest;
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to parse additionalguest data from tour', [
                    'tour_id' => $tourId,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return view('bookingList.itinerary', [
            'tourId' => $tourId,
            'itineraryByDate' => $itineraryByDate,
            'tourDetails' => $tourDetails,
            'priceHide' => $priceHide,
            'user_dmc' => $user_dmc,
            'agent_info' => $agent_info,
            'allPassengers' => $allPassengers
        ]);
    }

    /**
     * Download itinerary as PDF in the formatted layout (company header, hotel table, daily breakdown).
     * Uses HTML template rendered server-side with DomPDF.
     */
    public function downloadItineraryFormattedPdf(Request $request, $tourId)
    {
        try {
            $tourId = Crypt::decrypt($tourId);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid tour.');
        }

        $data = $this->buildItineraryPdfData($tourId);
        if (!$data) {
            return redirect()->back()->with('error', 'Tour not found or no itinerary data.');
        }

        $data['emergency_contact'] = $request->input('emergency_contact', '');
        $data['sic_timing'] = $request->input('sic_timing', '');
        $data['meeting_points'] = $request->input('meeting_points', '');

        $pdf = Pdf::loadView('bookingList.itinerary-pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'Itinerary_' . ($data['display_id'] ?? $tourId) . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Download Handover Acknowledgement Checklist as PDF.
     * Route passes tour_id in encrypted form.
     */
    public function downloadHandoverChecklistPdf(Request $request, $tour_id)
    {
        try {
            $tourId = Crypt::decrypt($tour_id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid tour.');
        }

        $data = $this->buildHandoverChecklistData($tourId);
        if (!$data) {
            return redirect()->back()->with('error', 'Tour not found or no data for handover checklist.');
        }

        try {
            $pdf = Pdf::loadView('bookingList.handover-checklist-pdf', $data)
                ->setPaper('a4', 'portrait');
            $filename = 'Handover_Checklist_' . ($data['display_id'] ?? $tourId) . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Handover Checklist PDF error: ' . $e->getMessage(), ['tour_id' => $tourId]);
            return redirect()->back()->with('error', 'Failed to generate PDF.');
        }
    }

    /**
     * Build data for the handover acknowledgement checklist PDF.
     */
    private function buildHandoverChecklistData($tourId)
    {
        $tour = Tour::where('tour_id', $tourId)->first();
        if (!$tour) {
            return null;
        }

        $dmcId = CommonHelper::getDmcId(auth()->user());
        if ($dmcId === null) {
            $dmcId = $tour->dmc_id;
        }

        $dmc = new \stdClass();
        $dmc->company_name = config('app.name');
        $dmc->name = '';
        $dmc->address = $dmc->phone = $dmc->tel = $dmc->fax = $dmc->email = $dmc->website = $dmc->logo = null;

        if ($dmcId) {
            $userDmc = User::select('name', 'email', 'phone', 'company_name', 'logo', 'country', 'city', 'address')
                ->where('userId', $dmcId)->first();
            if ($userDmc) {
                $dmc->company_name = $userDmc->company_name ?? $userDmc->name ?? config('app.name');
                $dmc->name = $userDmc->name ?? '';
                $dmc->address = $userDmc->address ?? null;
                $dmc->phone = $userDmc->phone ?? null;
                $dmc->tel = $userDmc->tel ?? $userDmc->phone ?? null;
                $dmc->fax = $userDmc->fax ?? null;
                $dmc->email = $userDmc->email ?? null;
                $dmc->website = $userDmc->website ?? null;
                $dmc->logo = $userDmc->logo ?? null;
                if ($dmc->logo && !str_starts_with($dmc->logo, 'data:image')) {
                    try {
                        $context = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 10, 'ignore_errors' => true]]);
                        $imageContent = @file_get_contents($dmc->logo, false, $context);
                        if ($imageContent !== false) {
                            $imageInfo = @getimagesizefromstring($imageContent);
                            if ($imageInfo !== false) {
                                $dmc->logo = 'data:' . $imageInfo['mime'] . ';base64,' . base64_encode($imageContent);
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Handover checklist DMC logo base64 failed: ' . $e->getMessage());
                    }
                }
            }
        }

        $guest_name = 'N/A';
        $nationality = 'N/A';
        if (!empty($tour->mainguest)) {
            try {
                $mainguest = is_string($tour->mainguest) ? json_decode($tour->mainguest, true) : $tour->mainguest;
                if (is_array($mainguest) && !empty($mainguest)) {
                    $guest_name = $mainguest['full_name'] ?? $mainguest['name'] ?? 'N/A';
                    $nationality = $mainguest['nationality'] ?? 'N/A';
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        $destination = $tour->destination ?? '';
        if ($destination === '' && $tour->tour_id) {
            $firstOrder = Order::where('tour_id', $tourId)->where('bookingType', 'booking')->where('status', 1)->first();
            if ($firstOrder && $firstOrder->data) {
                $decoded = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                $first = is_array($decoded) && isset($decoded[0]) ? $decoded[0] : $decoded;
                if (is_array($first)) {
                    $destination = $first['destination'] ?? $first['hotelDetails']['location'] ?? '';
                }
            }
        }

        $adults = (int) ($tour->adult ?? 0);
        $cwb = 0;
        $cnb = 0;

        $hotelOrders = Order::where('tour_id', $tourId)
            ->where('bookingType', 'booking')
            ->where('type', 'hotel')
            ->where('status', 1)
            ->get();

        foreach ($hotelOrders as $order) {
            $data = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            if (!is_array($data)) {
                continue;
            }
            $first = isset($data[0]) ? $data[0] : $data;
            if (!is_array($first)) {
                continue;
            }
            if (isset($first['child_with_bed']['children'])) {
                $cwb += (int) $first['child_with_bed']['children'];
            }
            if (isset($first['child_without_bed']['children'])) {
                $cnb += (int) $first['child_without_bed']['children'];
            }
        }

        $getScalarString = function ($value, string $default = ''): string {
            if (is_array($value)) {
                $value = $value[0] ?? null;
            }
            if (is_object($value)) {
                return $default;
            }
            return (string)($value ?? $default);
        };

        $parseServiceDate = function ($value): ?string {
            if (is_array($value)) {
                $value = $value[0] ?? null;
            }
            if (empty($value) || is_array($value) || is_object($value)) {
                return null;
            }
            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        };

        $getServiceTypeLabel = function (string $orderType): string {
            return match ($orderType) {
                'attraction' => 'Sightseeing',
                'restaurant' => 'Meal Voucher',
                'hotel' => 'Hotel',
                'guide' => 'Guide',
                'entry_port' => 'Airport Arrival',
                'exit_port' => 'Airport Departure',
                'travel_hourly' => 'Travel Hourly',
                'travel_point' => 'Transfer',
                'local_transport', 'local_transfer' => 'Point to Point',
                'miscellaneous' => 'Miscellaneous',
                default => ucfirst(str_replace('_', ' ', $orderType)),
            };
        };

        $extractOrderServiceDate = function (string $orderType, array $booking) use ($parseServiceDate): ?string {
            return match ($orderType) {
                'hotel' => $parseServiceDate($booking['bookingDate'][0] ?? ($booking['checkIn'] ?? ($booking['check_in_date'] ?? null))),
                'exit_port' => $parseServiceDate($booking['exitpickupdate'] ?? ($booking['pickupdate'] ?? ($booking['bookingDate'] ?? null))),
                'entry_port', 'travel_hourly', 'travel_point', 'local_transport', 'local_transfer', 'guide'
                    => $parseServiceDate($booking['pickupdate'] ?? ($booking['bookingDate'] ?? null)),
                'attraction', 'restaurant'
                    => $parseServiceDate($booking['bookingDate'] ?? ($booking['date'] ?? null)),
                default => $parseServiceDate($booking['bookingDate'] ?? ($booking['date'] ?? ($booking['pickupdate'] ?? null))),
            };
        };

        $buildServiceMainRow = function (string $orderType, array $booking) use ($getScalarString): string {
            return match ($orderType) {
                'attraction' => $getScalarString($booking['AttractionName'] ?? ($booking['attractionName'] ?? ($booking['attraction_name'] ?? null)), 'Attraction'),
                'restaurant' => (function () use ($booking, $getScalarString) {
                    $adultCount = (int)($booking['adultCount'] ?? ($booking['adults'] ?? 0));
                    $childCount = (int)($booking['childCount'] ?? ($booking['children'] ?? 0));
                    $pax = $adultCount + $childCount;
                    $mealType = $getScalarString($booking['mealType'] ?? ($booking['mealSpecificType'] ?? ($booking['meal_type'] ?? null)), 'Meal');
                    $restaurantName = $getScalarString($booking['restaurantName'] ?? ($booking['restaurant_name'] ?? ($booking['name'] ?? null)), 'Restaurant');
                    return $mealType . ' for ' . $pax . ' members @ ' . $restaurantName;
                })(),
                'hotel' => (function () use ($booking, $getScalarString) {
                    $hotelName = $getScalarString($booking['hotelDetails']['hotel_name'] ?? ($booking['hotel_name'] ?? null), 'Hotel');
                    $bookingDates = $booking['bookingDate'] ?? [];
                    if (!is_array($bookingDates)) {
                        $bookingDates = [$bookingDates];
                    }
                    $checkIn = $bookingDates[0] ?? ($booking['checkIn'] ?? ($booking['check_in_date'] ?? null));
                    $checkOut = $bookingDates[1] ?? ($booking['checkOut'] ?? ($booking['check_out_date'] ?? null));
                    if (!empty($checkIn) && !empty($checkOut)) {
                        return $hotelName . ' (' . $checkIn . ' - ' . $checkOut . ')';
                    }
                    return $hotelName;
                })(),
                'guide' => (function () use ($booking, $getScalarString) {
                    $guideName = $getScalarString($booking['guide_name'] ?? ($booking['name'] ?? null), 'Guide');
                    $hours = $getScalarString($booking['hours'] ?? ($booking['service_hours'] ?? null));
                    return $hours !== '' ? ($guideName . ' - ' . $hours . 'H') : $guideName;
                })(),
                'entry_port' => (function () use ($booking, $getScalarString) {
                    $vehicle = $getScalarString($booking['vehicles_name'] ?? ($booking['vehicles_name'] ?? ($booking['vehicle_name'] ?? null)), 'Transfer');
                    $bookingType = $getScalarString($booking['type'] ?? null, 'Private');
                    $pickup = $getScalarString($booking['entrypickup'] ?? null);
                    $dropoff = $getScalarString($booking['entrydropoff'] ?? null);
                    $service = 'Arrival Transfer (' . $vehicle . ' - ' . $bookingType . ' - One Way)';
                    if ($pickup !== '' && $dropoff !== '') {
                        $service .= '<br>' . e($pickup) . ' to ' . e($dropoff);
                    }
                    return $service;
                })(),
                'exit_port' => (function () use ($booking, $getScalarString) {
                    $vehicle = $getScalarString($booking['vehicles_name'] ?? ($booking['vehicles_name'] ?? ($booking['vehicle_name'] ?? null)), 'Transfer');
                    $bookingType = $getScalarString($booking['type'] ?? null, 'Private');
                    $pickup = $getScalarString($booking['exitpickup'] ?? null);
                    $dropoff = $getScalarString($booking['exitdropoff'] ?? null);
                    $service = 'Departure Transfer (' . $vehicle . ' - ' . $bookingType . ' - One Way)';
                    if ($pickup !== '' && $dropoff !== '') {
                        $service .= '<br>' . e($pickup) . ' to ' . e($dropoff);
                    }
                    return $service;
                })(),
                'local_transport', 'local_transfer', 'travel_hourly', 'travel_point' => (function () use ($booking, $getScalarString) {
                    $vehicle = $getScalarString($booking['vehicles_name'] ?? ($booking['vehicle_name'] ?? null), 'Transport');
                    $bookingType = $getScalarString($booking['type'] ?? null);
                    $pickup = $getScalarString($booking['entrypickup'] ?? null);
                    $dropoff = $getScalarString($booking['entrydropoff'] ?? ($booking['dropoffLocation'] ?? null));
                    $service = $bookingType !== '' ? ($vehicle . ' - ' . $bookingType) : $vehicle;
                    if ($pickup !== '' && $dropoff !== '') {
                        $service .= '<br>' . e($pickup) . ' to ' . e($dropoff);
                    }
                    return $service;
                })(),
                default => $getScalarString($booking['name'] ?? null, 'Service'),
            };
        };

        $buildServiceSubrows = function (string $orderType, array $booking) use ($getScalarString): array {
            if ($orderType !== 'attraction') {
                return [];
            }

            $subrows = [];
            $ticketName = $getScalarString($booking['ticketName'] ?? ($booking['ticket_name'] ?? null));
            if ($ticketName !== '') {
                $subrows[] = '# ' . $ticketName;
            }

            $ticketDetails = $booking['ticket_details'] ?? null;
            if (is_array($ticketDetails)) {
                $ticketLabel = $getScalarString($ticketDetails['ticket_name'] ?? ($ticketDetails['name'] ?? null));
                if ($ticketLabel !== '' && $ticketLabel !== $ticketName) {
                    $subrows[] = '# ' . $ticketLabel;
                }
            }

            $includedTickets = $booking['includedTickets'] ?? ($booking['included_tickets'] ?? null);
            if (is_array($includedTickets)) {
                foreach ($includedTickets as $includedTicket) {
                    if (!is_array($includedTicket)) {
                        continue;
                    }
                    $includedTicketName = $getScalarString($includedTicket['ticketName'] ?? ($includedTicket['name'] ?? null));
                    if ($includedTicketName !== '') {
                        $subrows[] = '# ' . $includedTicketName;
                    }
                }
            }

            return array_values(array_unique($subrows));
        };

        $ticketCoupons = [];
        $serviceOrders = Order::where('tour_id', $tourId)
            ->where('bookingType', 'booking')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->orderBy('booking_id')
            ->get();

        foreach ($serviceOrders as $order) {
            $decodedData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            if (!is_array($decodedData) || empty($decodedData)) {
                continue;
            }

            $bookings = (isset($decodedData[0]) && is_array($decodedData[0])) ? $decodedData : [$decodedData];
            foreach ($bookings as $booking) {
                if (!is_array($booking)) {
                    continue;
                }

                $serviceDate = $extractOrderServiceDate((string)$order->type, $booking);
                if (!$serviceDate) {
                    continue;
                }

                $serviceTypeLabel = $getServiceTypeLabel((string)$order->type);
                $serviceMainHtml = $buildServiceMainRow((string)$order->type, $booking);

                $ticketCoupons[] = [
                    'service_date' => $serviceDate,
                    'is_approve' => (bool) ($order->is_approve ?? false),
                    'service_type' => $serviceTypeLabel,
                    'service' => $serviceMainHtml,
                    'is_subrow' => false,
                ];

                foreach ($buildServiceSubrows((string)$order->type, $booking) as $subrowText) {
                    $ticketCoupons[] = [
                        'service_date' => $serviceDate,
                        'is_approve' => (bool) ($order->is_approve ?? false),
                        'service_type' => '',
                        'service' => e($subrowText),
                        'is_subrow' => true,
                    ];
                }
            }
        }

        usort($ticketCoupons, function ($a, $b) {
            $dA = $a['service_date'] ?? '';
            $dB = $b['service_date'] ?? '';
            return strcmp($dA, $dB);
        });

        return [
            'tourId' => $tourId,
            'tourDetails' => $tour,
            'display_id' => $tour->display_id ?? $tourId,
            'dmc' => $dmc,
            'guest_name' => $guest_name,
            'nationality' => $nationality,
            'destination' => $destination,
            'arrival_date' => $tour->check_in_time ?? null,
            'adults' => $adults,
            'cwb' => $cwb,
            'cnb' => $cnb,
            'ticketCoupons' => $ticketCoupons,
        ];
    }

    /**
     * Build data for the formatted itinerary PDF (hotels, days with time/description/type rows).
     */
    private function buildItineraryPdfData($tourId)
    {
        $tour = Tour::where('tour_id', $tourId)->first();
        if (!$tour) {
            return null;
        }

        $user_dmc = null;
        if ($tour->dmc_id) {
            $user_dmc = User::select('name', 'email', 'phone', 'company_name', 'logo', 'country', 'city', 'address')
                ->where('userId', $tour->dmc_id)->first();
            if ($user_dmc && $user_dmc->logo && !str_starts_with($user_dmc->logo, 'data:image')) {
                try {
                    $context = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 10, 'ignore_errors' => true]]);
                    $imageContent = @file_get_contents($user_dmc->logo, false, $context);
                    if ($imageContent !== false) {
                        $imageInfo = @getimagesizefromstring($imageContent);
                        if ($imageInfo !== false) {
                            $user_dmc->logo = 'data:' . $imageInfo['mime'] . ';base64,' . base64_encode($imageContent);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Logo base64 failed: ' . $e->getMessage());
                }
            }
        }

        $agent_info = null;
        $agent_id = $tour->agent_id ?? (Order::where('tour_id', $tourId)->where('bookingType', 'booking')->value('agent_id'));
        if ($agent_id) {
            $agent = Agent::with('agency')->where('agent_id', $agent_id)->first();
            if ($agent) {
                $agency = $agent->agency;
                $agent_info = [
                    'company_name' => ($agency && $agency->agency_name) ? $agency->agency_name : '',
                    'agent_name' => $agent->name ?? '',
                ];
            }
        }

        $tourDetails = Tour::with(['booking' => function ($q) {
            $q->where(function ($q2) {
                $q2->where('type', '!=', 'hotel')->orWhere('status', '!=', 2);
            });
        }])->where('tour_id', $tourId)->first();

        $bookings = Order::with('tour')
            ->where('tour_id', $tourId)
            ->where('bookingType', 'booking')
            ->where('status', 1)
            ->orderByDesc('booking_id')
            ->get();

        if ($bookings->isEmpty()) {
            $pdfHotels = [];
            $pdfDays = [];
        } else {
            $bookings = $this->formatBookings($bookings);
            $itineraryByDate = [];
            foreach ($bookings as $booking) {
                $data = $booking->data_decoded;
                $first = $data[0] ?? [];
                $date = null;
                if (isset($first['bookingDate'])) {
                    $date = is_array($first['bookingDate']) ? ($first['bookingDate'][0] ?? null) : $first['bookingDate'];
                } elseif (isset($first['pickupdate'])) {
                    $date = $first['pickupdate'];
                } elseif (isset($first['exitpickupdate'])) {
                    $date = $first['exitpickupdate'];
                }
                if ($date) {
                    $dateStr = \Carbon\Carbon::parse($date)->format('Y-m-d');
                    if (!isset($itineraryByDate[$dateStr])) {
                        $itineraryByDate[$dateStr] = [];
                    }
                    $itineraryByDate[$dateStr][] = $booking;
                }
                // Hotel: expand by check-in/check-out range
                if (strtolower($booking->type ?? '') === 'hotel' && isset($first['bookingDate']) && is_array($first['bookingDate']) && count($first['bookingDate']) >= 2) {
                    $checkIn = \Carbon\Carbon::parse($first['bookingDate'][0]);
                    $checkOut = \Carbon\Carbon::parse($first['bookingDate'][1]);
                    $current = $checkIn->copy();
                    while ($current->lt($checkOut)) {
                        $d = $current->format('Y-m-d');
                        if (!isset($itineraryByDate[$d])) {
                            $itineraryByDate[$d] = [];
                        }
                        $itineraryByDate[$d][] = $booking;
                        $current->addDay();
                    }
                }
            }
            ksort($itineraryByDate);

            $pdfHotels = $this->buildPdfHotels($bookings);
            $pdfDays = $this->buildPdfDays($itineraryByDate, $tourDetails);
        }

        $startDate = $tourDetails && $tourDetails->check_in_time ? \Carbon\Carbon::parse($tourDetails->check_in_time) : null;
        $endDate = $tourDetails && $tourDetails->check_out_time ? \Carbon\Carbon::parse($tourDetails->check_out_time) : null;
        if (!$startDate && !empty($pdfDays)) {
            $firstKey = array_key_first($pdfDays);
            $startDate = \Carbon\Carbon::parse($firstKey);
        }
        if (!$endDate && !empty($pdfDays)) {
            $lastKey = array_key_last($pdfDays);
            $endDate = \Carbon\Carbon::parse($lastKey);
        }

        $dmcId = CommonHelper::getDmcId(auth()->user());
        if ($dmcId === null && $tour) {
            $dmcId = $tour->dmc_id;
        }
        $terms_and_conditions = '';
        if ($dmcId) {
            $bankDetail = BankDetail::where('dmc_id', $dmcId)->where('is_active', 1)->first();
            
            if ($bankDetail && !empty($bankDetail->terms_and_conditions)) {
                $terms_and_conditions = $bankDetail->terms_and_conditions;
            }
        }

        return [
            'tourId' => $tourId,
            'tourDetails' => $tourDetails,
            'display_id' => $tourDetails->display_id ?? $tourId,
            'user_dmc' => $user_dmc,
            'agent_info' => $agent_info ?? [],
            'adults' => $tourDetails->adult ?? 0,
            'pdfHotels' => $pdfHotels ?? [],
            'pdfDays' => $pdfDays ?? [],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'terms_and_conditions' => $terms_and_conditions,
        ];
    }

    private function buildPdfHotels($bookings)
    {
        $hotels = [];
        $seen = [];
        foreach ($bookings as $booking) {
            if (strtolower($booking->type ?? '') !== 'hotel') {
                continue;
            }
            $data = $booking->data_decoded[0] ?? [];
            $name = $data['hotelDetails']['hotel_name'] ?? $data['hotelname'] ?? $data['name'] ?? null;
            if (!$name) {
                continue;
            }
            $bookingDate = $data['bookingDate'] ?? null;
            $checkIn = $checkOut = null;
            if (is_array($bookingDate) && count($bookingDate) >= 2) {
                $checkIn = $bookingDate[0];
                $checkOut = $bookingDate[1];
            } elseif (is_array($bookingDate) && count($bookingDate) === 1) {
                $checkIn = $bookingDate[0];
                $checkOut = $bookingDate[0];
            } else {
                $checkIn = $bookingDate;
                $checkOut = $bookingDate;
            }
            $key = $name . '|' . $checkIn;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $nights = 1;
            if ($checkIn && $checkOut) {
                $nights = \Carbon\Carbon::parse($checkIn)->diffInDays(\Carbon\Carbon::parse($checkOut));
            }
            $hotels[] = [
                'name' => $name,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'nights' => $nights,
            ];
        }
        usort($hotels, function ($a, $b) {
            return strcmp($a['check_in'] ?? '', $b['check_in'] ?? '');
        });
        return $hotels;
    }

    private function buildPdfDays($itineraryByDate, $tourDetails)
    {
        $days = [];
        foreach ($itineraryByDate as $dateStr => $dayBookings) {
            $rows = [];
            $entryPorts = [];
            $exitPorts = [];
            $hotels = [];
            $regular = [];

            foreach ($dayBookings as $booking) {
                $data = $booking->data_decoded[0] ?? [];
                $type = strtolower($booking->type ?? '');
                if ($type === 'hotel') {
                    $bdate = $data['bookingDate'] ?? null;
                    $checkOut = is_array($bdate) && isset($bdate[1]) ? $bdate[1] : $bdate;
                    if ($checkOut && \Carbon\Carbon::parse($checkOut)->format('Y-m-d') === $dateStr) {
                        continue; // skip checkout-only day
                    }
                    if (($data['stay_type'] ?? '') === 'checkout') {
                        continue;
                    }
                }

                $timeSlot = $data['timeslot'] ?? $data['time'] ?? $data['pickuptime'] ?? $data['exitpickuptime'] ?? $data['visitTime'] ?? $data['entrytime'] ?? '00:00';
                $sortTime = $timeSlot;
                if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)?/i', $timeSlot, $m)) {
                    $h = (int)$m[1];
                    $min = (int)($m[2] ?? 0);
                    if (!empty($m[3]) && strtoupper($m[3]) === 'PM' && $h < 12) {
                        $h += 12;
                    }
                    if (!empty($m[3]) && strtoupper($m[3]) === 'AM' && $h === 12) {
                        $h = 0;
                    }
                    $sortTime = sprintf('%02d:%02d', $h, $min);
                }

                $row = $this->bookingToPdfRow($booking, $data, $type, $dateStr);
                if (!$row) {
                    continue;
                }
                $row['sort_time'] = $sortTime;
                if ($type === 'entry port' || $type === 'entry_port') {
                    $entryPorts[] = $row;
                } elseif ($type === 'exit port' || $type === 'exit_port') {
                    $exitPorts[] = $row;
                } elseif ($type === 'hotel') {
                    $hotels[] = $row;
                } else {
                    $regular[] = $row;
                }
            }

            $cmp = function ($a, $b) {
                return strcmp($a['sort_time'], $b['sort_time']);
            };
            usort($entryPorts, $cmp);
            usort($exitPorts, $cmp);
            usort($hotels, $cmp);
            usort($regular, $cmp);

            $isFirst = empty($days);
            $isLast = ($dateStr === array_key_last($itineraryByDate));
            $all = [];
            if ($isFirst) {
                $all = array_merge($all, $entryPorts);
            }
            // Hotels are shown only in the hotel table at the top, not in the day-wise itinerary
            $all = array_merge($all, $regular);
            if ($isLast) {
                $all = array_merge($all, $exitPorts);
            }

            foreach ($all as $r) {
                unset($r['sort_time']);
                $rows[] = $r;
            }

            $days[$dateStr] = [
                'date_label' => \Carbon\Carbon::parse($dateStr)->format('d M Y, l'),
                'rows' => $rows,
            ];
        }
        return $days;
    }

    private function bookingToPdfRow($booking, $data, $type, $dateStr)
    {
        $time = $data['timeslot'] ?? $data['time'] ?? $data['pickuptime'] ?? $data['exitpickuptime'] ?? $data['visitTime'] ?? $data['entrytime'] ?? '00:00';
        if (preg_match('/^(\d{1,2}):(\d{2})/', $time, $m)) {
            $time = $m[1] . ':' . ($m[2] ?? '00');
        }

        $transferType = ucfirst(strtolower($data['type'] ?? $data['transfer_options']['type'] ?? 'Private'));
        $description = '';
        $note = null;
        $wrap = function ($s) {
            return '<span class="loc">' . e($s) . '</span>';
        };

        if ($type === 'entry port' || $type === 'entry_port') {
            $pickup = $data['entrypickup'] ?? $data['entry_pickup'] ?? $data['pickup'] ?? '';
            $dropoff = $data['entrydropoff'] ?? $data['entry_dropoff'] ?? $data['dropoff'] ?? '';
            $description = 'Arrive at ' . $wrap($pickup) . ' and Proceed to ' . $wrap($dropoff);
            $note = $data['remark'] ?? $data['remarks'] ?? $data['specialRequests'] ?? null;
            if ($data['originFlightNumber'] ?? $data['arrivalFlightNumber'] ?? null) {
                $note = ($data['originFlightNumber'] ?? '') . ' ' . ($data['arrivalFlightNumber'] ?? '') . ' ' . ($note ?? '');
            }
        } elseif ($type === 'exit port' || $type === 'exit_port') {
            $pickup = $data['exitpickup'] ?? $data['exit_pickup'] ?? $data['pickup'] ?? '';
            $dropoff = $data['exitdropoff'] ?? $data['exit_dropoff'] ?? $data['dropoff'] ?? '';
            $description = 'Transfer from ' . $wrap($pickup) . ' to ' . $wrap($dropoff);
            $note = $data['remark'] ?? $data['remarks'] ?? $data['specialRequests'] ?? null;
        } elseif ($type === 'hotel') {
            $name = $data['hotelDetails']['hotel_name'] ?? $data['hotelname'] ?? $data['name'] ?? 'Hotel';
            $stayType = $data['stay_type'] ?? '';
            if ($stayType === 'checkout') {
                return null;
            }
            $description = 'Check-in at ' . $name;
            if ($stayType === 'stay') {
                $description = 'Stay at ' . $name;
            }
        } elseif (strpos($type, 'travel') !== false || $type === 'travel_point' || $type === 'travel_hourly' || $type === 'point to point' || $type === 'hourly' || $type === 'local_transport') {
            $pickup = $data['entrypickup'] ?? $data['pickup'] ?? '';
            $dropoff = $data['entrydropoff'] ?? $data['dropoff'] ?? $data['dropoffLocation'] ?? '';
            $description = 'Transfer by ' . e($transferType) . ' Vehicle from ' . $wrap($pickup) . ' to ' . $wrap($dropoff);
            $note = $data['remark'] ?? $data['remarks'] ?? $data['specialRequests'] ?? null;
        } else                if ($type === 'attraction') {
            $name = $data['AttractionName'] ?? $data['name'] ?? 'Attraction';
            $hasTransfer = isset($data['transfer_options']['transfer_required']) && $data['transfer_options']['transfer_required'];
            if ($hasTransfer) {
                $pickup = $data['transfer_options']['pickup_location_name'] ?? '';
                $description = 'Transfer by ' . e($transferType) . ' Vehicle from ' . $wrap($pickup) . ' to ' . $wrap($name);
                $activity = 'Visit ' . $name;
                if (!empty($data['visitTime'])) {
                    $activity .= ' (' . $data['visitTime'] . ')';
                }
                return ['time' => $time, 'description' => $description, 'type' => $transferType, 'note' => null, 'activity' => $activity];
            }
            $description = 'Visit ' . $name;
            $note = $data['specialRequests'] ?? null;
        } elseif ($type === 'restaurant') {
            $name = $data['restaurantName'] ?? $data['name'] ?? 'Restaurant';
            $hasTransfer = isset($data['transfer_options']['transfer_required']) && $data['transfer_options']['transfer_required'];
            if ($hasTransfer) {
                $pickup = $data['transfer_options']['pickup_location_name'] ?? '';
                $description = 'Transfer by ' . e($transferType) . ' Vehicle from ' . $wrap($pickup) . ' to ' . $wrap($name);
                $mealType = $data['mealType'] ?? $data['mealSpecificType'] ?? '';
                $pax = ($data['adultCount'] ?? $data['adult_count'] ?? 0) + ($data['childCount'] ?? $data['child_count'] ?? 0);
                $activity = ($mealType ? $mealType . ' for ' : 'Meal for ') . $pax . ' members';
                return ['time' => $time, 'description' => $description, 'type' => $transferType, 'note' => null, 'activity' => $activity];
            }
            $description = ($data['mealType'] ?? 'Meal') . ' at ' . $name;
            $note = $data['specialRequests'] ?? null;
        } elseif ($type === 'guide') {
            $name = $data['guide_name'] ?? $data['guideName'] ?? $data['name'] ?? 'Guide';
            $description = 'Guide service - ' . $name;
            $note = $data['remark'] ?? $data['specialRequests'] ?? null;
        } else {
            $description = $data['name'] ?? ucfirst($type);
            $note = $data['remark'] ?? $data['specialRequests'] ?? null;
        }

        return ['time' => $time, 'description' => $description, 'type' => $transferType, 'note' => $note];
    }
    
    /**
     * Get DMC ID based on user role hierarchy
     */
    private function getDmcIdByUserRole($user)
    {
        switch ($user->role_id) {
            case 11: // DMC
                return $user->userId;
                
            case 33: // Sales Head
            case 128:
            case 129:
            case 130:
            case 134:
            case 135:
            case 136:
            case 138:
                $dmcUser = \App\Models\User::where('userId', $user->created_by)->first();
                return ($dmcUser && $dmcUser->role_id == 11) ? $dmcUser->userId : null;
                
            case 37: // Sales Manager
                $salesHead = \App\Models\User::where('userId', $user->created_by)->first();
                if ($salesHead) {
                    $dmcUser = \App\Models\User::where('userId', $salesHead->created_by)->first();
                    return ($dmcUser && $dmcUser->role_id == 11) ? $dmcUser->userId : null;
                }
                break;
                
            case 38: // Assistant Sales Manager
                $salesManager = \App\Models\User::where('userId', $user->created_by)->first();
                if ($salesManager) {
                    $salesHead = \App\Models\User::where('userId', $salesManager->created_by)->first();
                    if ($salesHead) {
                        $dmcUser = \App\Models\User::where('userId', $salesHead->created_by)->first();
                        return ($dmcUser && $dmcUser->role_id == 11) ? $dmcUser->userId : null;
                    }
                }
                break;
        }
        
        return null;
    }
    
    /**
     * API endpoint to check current price_hide value for the DMC
     */
    public function checkPriceHide()
    {
        try {
            $currentUser = auth()->user();
            $priceHide = 0; // Default to show prices (0 = show, 1 = hide)
            $dmcId = null;
            
            if ($currentUser) {
                $dmcId = $this->getDmcIdByUserRole($currentUser);
                if ($dmcId) {
                    $dmc = \App\Models\User::where('userId', $dmcId)->first();
                    if ($dmc) {
                        // Ensure price_hide is returned as integer (0 or 1)
                        // 0 = show prices, 1 = hide prices
                        $priceHide = (int)($dmc->price_hide ?? 0);
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'price_hide' => $priceHide, // Return as integer: 0 or 1
                'dmc_id' => $dmcId,
                'timestamp' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in checkPriceHide: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to check price hide status',
                'price_hide' => 0 // Default to show prices on error (0 = show, 1 = hide)
            ], 500);
        }
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
            
            // Fetch vehicle and driver data for entry_port and attraction bookings
            // Get booking_id - try booking_id first, then id
            $bookingId = $booking->booking_id ?? $booking->id ?? null;
            
            if ($bookingId) {
                $bookingTypeLower = strtolower($booking->type);
                if ($bookingTypeLower === 'entry port') {
                    $vehicleDriverData = $this->getVehicleDriverData($bookingId, $booking->data_decoded);
                    // Set as both property and in attributes array to ensure it's accessible
                    $booking->vehicle_driver_data = $vehicleDriverData;
                    $booking->setAttribute('vehicle_driver_data', $vehicleDriverData);
                } elseif ($bookingTypeLower === 'exit port') {
                    $vehicleDriverData = $this->getVehicleDriverData($bookingId, $booking->data_decoded);
                    // Set as both property and in attributes array to ensure it's accessible
                    $booking->vehicle_driver_data = $vehicleDriverData;
                    $booking->setAttribute('vehicle_driver_data', $vehicleDriverData);
                } elseif ($bookingTypeLower === 'attraction') {
                    $vehicleDriverData = $this->getVehicleDriverDataForAttraction($bookingId, $booking->data_decoded);
                    // Set as both property and in attributes array to ensure it's accessible
                    $booking->vehicle_driver_data = $vehicleDriverData;
                    $booking->setAttribute('vehicle_driver_data', $vehicleDriverData);
                } elseif ($bookingTypeLower === 'restaurant') {
                    $vehicleDriverData = $this->getVehicleDriverDataForRestaurant($bookingId, $booking->data_decoded);
                    // Set as both property and in attributes array to ensure it's accessible
                    $booking->vehicle_driver_data = $vehicleDriverData;
                    $booking->setAttribute('vehicle_driver_data', $vehicleDriverData);
                } else {
                    $booking->vehicle_driver_data = null;
                    $booking->setAttribute('vehicle_driver_data', null);
                }
            } else {
                $booking->vehicle_driver_data = null;
                $booking->setAttribute('vehicle_driver_data', null);
            }

            return $booking;
        });
    }

    /**
     * Get vehicle and driver data for a booking
     * Checks jobsheet first, then falls back to vehicles_id from order data
     */
    private function getVehicleDriverData($bookingId, $dataDecoded)
    {
        // Initialize return values
        $vehicleNumber = 'N/A';
        $vehicleName = 'N/A';
        $maxPassengerCapacity = 'N/A';
        $driverName = 'N/A';
        $driverPhone = 'N/A';
        
        // Get first item from data_decoded to access vehicles_id
        $firstItem = is_array($dataDecoded) && !empty($dataDecoded) ? $dataDecoded[0] : null;
        
        // Check jobsheet first using booking_id as order_id
        $vehicleId = null;
        $driverId = null;
        
        $jobsheet = \App\Models\Jobsheet::where('order_id', $bookingId)->first();
        if ($jobsheet) {
            $vehicleId = $jobsheet->vehicle_id;
            $driverId = $jobsheet->driver_id;
        }
        
        // If no jobsheet, use vehicles_id from order data
        if (!$vehicleId && $firstItem && isset($firstItem['vehicles_id'])) {
            $vehicleId = $firstItem['vehicles_id'];
            
        }
        
        // Fetch vehicle details
        if ($vehicleId) {
            $vehicle = \App\Models\Vehicle::where('vehicle_id', $vehicleId)->first();
            if ($vehicle) {
                $vehicleNumber = $vehicle->vehicle_plate_no ?? 'N/A';
                $vehicleName = $vehicle->vehicle_name ?? 'N/A';
                $maxPassengerCapacity = $vehicle->seating_capacity ?? $vehicle->max_passenger_capacity ?? 'N/A';
                
                // If driver_id not from jobsheet, get from vehicle
                if (!$driverId) {
                    $driverId = $vehicle->driver_id;
                }
            }
        }
        
        // Fetch driver details
        if ($driverId) {
            $driver = \App\Models\Driver::where('driver_id', $driverId)->first();
            if ($driver) {
                $driverName = $driver->name ?? 'N/A';
                $driverPhone = $driver->phone ?? 'N/A';
            }
            
        }
        
        return [
            'vehicleNumber' => $vehicleNumber,
            'vehicleName' => $vehicleName,
            'maxPassengerCapacity' => $maxPassengerCapacity,
            'driverName' => $driverName,
            'driverPhone' => $driverPhone
        ];
    }

    /**
     * Get vehicle and driver data for an attraction booking
     * Checks jobsheet first, then falls back to vehicle_id from transfer_options
     */
    private function getVehicleDriverDataForAttraction($bookingId, $dataDecoded)
    {
        // Initialize return values
        $vehicleNumber = 'N/A';
        $vehicleName = 'N/A';
        $maxPassengerCapacity = 'N/A';
        $driverName = 'N/A';
        $driverPhone = 'N/A';
        
        // Get first item from data_decoded to access transfer_options
        $firstItem = is_array($dataDecoded) && !empty($dataDecoded) ? $dataDecoded[0] : null;
        
        if (!$firstItem) {
            return [
                'vehicleNumber' => $vehicleNumber,
                'vehicleName' => $vehicleName,
                'maxPassengerCapacity' => $maxPassengerCapacity,
                'driverName' => $driverName,
                'driverPhone' => $driverPhone
            ];
        }
        
        // Check if transfer is required
        $transferRequired = $firstItem['transfer_options']['transfer_required'] ?? false;
        if (!$transferRequired) {
            return [
                'vehicleNumber' => $vehicleNumber,
                'vehicleName' => $vehicleName,
                'maxPassengerCapacity' => $maxPassengerCapacity,
                'driverName' => $driverName,
                'driverPhone' => $driverPhone
            ];
        }
        
        // Check jobsheet first using booking_id as order_id
        $vehicleId = null;
        $driverId = null;
        
        $jobsheet = \App\Models\Jobsheet::where('order_id', $bookingId)->first();
        if ($jobsheet) {
            $vehicleId = $jobsheet->vehicle_id;
            $driverId = $jobsheet->driver_id;
        }
        
        // If no jobsheet, use vehicle_id from transfer_options
        if (!$vehicleId && isset($firstItem['transfer_options']['vehicle_id'])) {
            $vehicleId = $firstItem['transfer_options']['vehicle_id'];
        }
        
        // Fetch vehicle details
        if ($vehicleId) {
            $vehicle = \App\Models\Vehicle::where('vehicle_id', $vehicleId)->first();
            if ($vehicle) {
                $vehicleNumber = $vehicle->vehicle_plate_no ?? 'N/A';
                $vehicleName = $vehicle->vehicle_name ?? 'N/A';
                $maxPassengerCapacity = $vehicle->seating_capacity ?? $vehicle->max_passenger_capacity ?? 'N/A';
                
                // If driver_id not from jobsheet, get from vehicle
                if (!$driverId) {
                    $driverId = $vehicle->driver_id;
                }
            }
        }
        
        // Fetch driver details
        if ($driverId) {
            $driver = \App\Models\Driver::where('driver_id', $driverId)->first();
            if ($driver) {
                $driverName = $driver->name ?? 'N/A';
                $driverPhone = $driver->phone ?? 'N/A';
            }
        }
        
        return [
            'vehicleNumber' => $vehicleNumber,
            'vehicleName' => $vehicleName,
            'maxPassengerCapacity' => $maxPassengerCapacity,
            'driverName' => $driverName,
            'driverPhone' => $driverPhone
        ];
    }

    /**
     * Get vehicle and driver data for a restaurant booking
     * Checks jobsheet first, then falls back to vehicle_id from transfer_options
     */
    private function getVehicleDriverDataForRestaurant($bookingId, $dataDecoded)
    {
        // Initialize return values
        $vehicleNumber = 'N/A';
        $vehicleName = 'N/A';
        $maxPassengerCapacity = 'N/A';
        $driverName = 'N/A';
        $driverPhone = 'N/A';
        
        // Get first item from data_decoded to access transfer_options
        $firstItem = is_array($dataDecoded) && !empty($dataDecoded) ? $dataDecoded[0] : null;
        
        if (!$firstItem) {
            return [
                'vehicleNumber' => $vehicleNumber,
                'vehicleName' => $vehicleName,
                'maxPassengerCapacity' => $maxPassengerCapacity,
                'driverName' => $driverName,
                'driverPhone' => $driverPhone
            ];
        }
        
        // Check if transfer is required
        $transferRequired = $firstItem['transfer_options']['transfer_required'] ?? false;
        if (!$transferRequired) {
            return [
                'vehicleNumber' => $vehicleNumber,
                'vehicleName' => $vehicleName,
                'maxPassengerCapacity' => $maxPassengerCapacity,
                'driverName' => $driverName,
                'driverPhone' => $driverPhone
            ];
        }
        
        // Check jobsheet first using booking_id as order_id
        $vehicleId = null;
        $driverId = null;
        
        $jobsheet = \App\Models\Jobsheet::where('order_id', $bookingId)->first();
        if ($jobsheet) {
            $vehicleId = $jobsheet->vehicle_id;
            $driverId = $jobsheet->driver_id;
        }
        
        // If no jobsheet, use vehicle_id from transfer_options
        if (!$vehicleId && isset($firstItem['transfer_options']['vehicle_id'])) {
            $vehicleId = $firstItem['transfer_options']['vehicle_id'];
        }
        
        // Fetch vehicle details
        if ($vehicleId) {
            $vehicle = \App\Models\Vehicle::where('vehicle_id', $vehicleId)->first();
            if ($vehicle) {
                $vehicleNumber = $vehicle->vehicle_plate_no ?? 'N/A';
                $vehicleName = $vehicle->vehicle_name ?? 'N/A';
                $maxPassengerCapacity = $vehicle->seating_capacity ?? $vehicle->max_passenger_capacity ?? 'N/A';
                
                // If driver_id not from jobsheet, get from vehicle
                if (!$driverId) {
                    $driverId = $vehicle->driver_id;
                }
            }
        }
        
        // Fetch driver details
        if ($driverId) {
            $driver = \App\Models\Driver::where('driver_id', $driverId)->first();
            if ($driver) {
                $driverName = $driver->name ?? 'N/A';
                $driverPhone = $driver->phone ?? 'N/A';
            }
        }
        
        return [
            'vehicleNumber' => $vehicleNumber,
            'vehicleName' => $vehicleName,
            'maxPassengerCapacity' => $maxPassengerCapacity,
            'driverName' => $driverName,
            'driverPhone' => $driverPhone
        ];
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