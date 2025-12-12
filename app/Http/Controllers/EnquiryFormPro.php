<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\User;
use App\Models\Agency;
use App\Models\Country;
use App\Models\Agent;
use App\Models\Port;
use App\Models\Attraction;
use App\Models\Restaurant;
class EnquiryFormPro extends Controller
{
    public function create(Request $request)
    {
        $user = auth()->user();
        $destination = $user->country ?? 'Singapore';
        
        // Get initial data from session if available
        $initialData = $request->session()->get('tour_pro_initial_data', null);
        
        // Get DMC ID based on user role
        $dmc_id = null;
        if ($user->role_id == 11) {
            $dmc_id = $user->userId;
        } elseif (in_array($user->role_id, [33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138])) {
            $dmc_id = $user->created_by;
        } elseif (in_array($user->role_id, [37, 64, 65, 66, 67, 68])) {
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head ? $sales_head->created_by : null;
        } elseif (in_array($user->role_id, [38, 81, 90, 108, 117, 124, 125, 126, 127])) {
            $sales_manager = User::where('userId', $user->created_by)->first();
            if ($sales_manager) {
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                $dmc_id = $sales_head ? $sales_head->created_by : null;
            }
        }
        
        // Load agencies based on destination from popup or user country
        $agencyQuery = Agency::where('status', 1);
        
        // Filter by DMC ID if available
        if ($dmc_id) {
            $agencyQuery->whereJsonContains('dmc_id', (int) $dmc_id);
        }
        
        // If we have initial data, get agencies from that destination, otherwise use user's country
        if ($initialData && isset($initialData['destination_display'])) {
            // Get destination(s) from initial data
            if (isset($initialData['destinations_array'])) {
                $agencyQuery->whereIn('country', $initialData['destinations_array']);
            } else {
                $agencyQuery->where('country', $initialData['destination_display']);
            }
        } else {
            $agencyQuery->where('country', $destination);
        }
        
        $agencies = $agencyQuery->orderBy('agency_name', 'asc')->get();
        
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        $ports = Port::where('status', 1)->orderBy('port_name')->get();
        $destinations = Country::where('is_active', 1)->orderBy('name')->get();
        $attractions = Attraction::where('status', 1)->orderBy('name')->get();
        $restaurants = Restaurant::where('status', 1)->orderBy('name')->get();
        $agents = []; // Start with empty agents, will be populated via AJAX
        
        return view('enquiryform_pro.create', compact('destination', 'agents', 'agencies', 'user', 'countries', 'ports', 'destinations', 'attractions', 'restaurants', 'initialData'));
    }
    
    /**
     * Initialize tour with popup data and redirect to create page
     */
    public function initialize(Request $request)
    {
        $validated = $request->validate([
            'tour_type' => 'required|in:Group,FIT',
            'tour_start_date' => 'required|date|after_or_equal:today',
            'tour_end_date' => 'required|date|after:tour_start_date',
            'adult_count' => 'required|integer|min:0',
            'child_count' => 'nullable|integer|min:0',
            'infant_count' => 'nullable|integer|min:0',
            'agency_id' => 'required|exists:agencies,agency_id',
            'agent_id' => 'required|exists:agents,agent_id',
            'salutation' => 'required|in:Mr,Mrs,Ms,Dr',
            'customer_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'multiple_destination' => 'nullable|boolean',
            'destination_single' => 'nullable|string',
            'destinations' => 'nullable|json',
        ]);
        
        // Get agency and agent details
        $agency = Agency::find($validated['agency_id']);
        $agent = Agent::find($validated['agent_id']);
        
        // Prepare destination data
        if ($request->has('multiple_destination') && $request->multiple_destination) {
            $destinations = json_decode($request->destinations, true);
            $validated['destinations_array'] = $destinations;
            $validated['destination_display'] = implode(', ', $destinations);
        } else {
            $validated['destination_display'] = $request->destination_single;
        }
        
        // Add agency and agent names
        $validated['agency_name'] = $agency->agency_name ?? '';
        $validated['agent_name'] = $agent->name ?? '';
        
        // Store in session
        $request->session()->put('tour_pro_initial_data', $validated);
        
        return redirect()->route('enquiry-form-pro.create');
    }
    
    /**
     * Get agencies for popup (AJAX) - filtered by destination and DMC
     */
    public function getAgencies(Request $request)
    {
        $user = auth()->user();
        
        // Get DMC ID based on user role (following existing pattern)
        $dmc_id = null;
        if ($user->role_id == 11) {
            $dmc_id = $user->userId;
        } elseif (in_array($user->role_id, [33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138])) {
            $dmc_id = $user->created_by;
        } elseif (in_array($user->role_id, [37, 64, 65, 66, 67, 68])) {
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head ? $sales_head->created_by : null;
        } elseif (in_array($user->role_id, [38, 81, 90, 108, 117, 124, 125, 126, 127])) {
            $sales_manager = User::where('userId', $user->created_by)->first();
            if ($sales_manager) {
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                $dmc_id = $sales_head ? $sales_head->created_by : null;
            }
        }
        
        // Get destination(s) from request
        $destination = $request->input('destination');
        $destinations = $request->input('destinations'); // comma-separated
        
        if (!$destination && !$destinations) {
            return response()->json([
                'success' => false,
                'message' => 'Destination is required',
                'agencies' => []
            ]);
        }
        
        // Parse destinations
        $countryArray = [];
        if ($destinations) {
            $countryArray = array_map('trim', explode(',', $destinations));
        } else {
            $countryArray = [$destination];
        }
        
        // Build query for agencies with two-step filtering:
        // Step 1: Get agencies that are in the selected destination(s)
        // Step 2: From those, filter only agencies connected to this DMC
        
        $agencies = Agency::where('status', 1)
            ->whereIn('country', $countryArray); // Step 1: Filter by destination country
        
        // Step 2: Filter by DMC ID - only agencies that have this DMC in their dmc_id JSON array
        if ($dmc_id) {
            $agencies = $agencies->whereJsonContains('dmc_id', (int) $dmc_id);
        }
        
        $agencies = $agencies->orderBy('agency_name', 'asc')
                           ->get(['agency_id', 'agency_name', 'country', 'dmc_id']);
        
        return response()->json([
            'success' => true,
            'agencies' => $agencies,
            'dmc_id' => $dmc_id,
            'count' => $agencies->count()
        ]);
    }
    
    /**
     * Get destinations for popup (AJAX)
     */
    public function getDestinations(Request $request)
    {
        $destinations = Country::where('is_active', 1)
                              ->orderBy('name', 'asc')
                              ->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'destinations' => $destinations
        ]);
    }
    
    // Get agents by agency ID
    public function getAgentsByAgency(Request $request)
    {
        $agencyId = $request->input('agency_id');
        if (!$agencyId) {
            return response()->json([
                'success' => false,
                'message' => 'Agency ID is required'
            ], 400);
        }
        
        // Fetch agents for the selected agency
        $agents = Agent::where('status', 1)
                      ->where('agency_id', $agencyId)
                      ->orderBy('name', 'asc')
                      ->get(['agent_id', 'name', 'email']);
        
        return response()->json([
            'success' => true,
            'agents' => $agents
        ]);
    }
    
    /**
     * Get hotels by destination (AJAX)
     */
    public function getHotelsByDestination(Request $request)
    {
        $user = auth()->user();
        $destination = $request->input('destination');
        
        if (!$destination) {
            return response()->json([
                'success' => false,
                'message' => 'Destination is required'
            ], 400);
        }
        // Get hotels selected by this DMC with rooms for the destination
        $hotels = Hotel::where('status', 1)
                      ->where('is_active', 1)
                      ->where('is_complete', 1)
                      ->where('city', $destination)
                      ->with(['rooms' => function($query) {
                          $query->where('status', 1)
                                ->with(['beds' => function($bedQuery) {
                                    $bedQuery->where('is_active', 1);
                                }])
                                ->orderBy('room_type', 'asc');
                      }])
                    //   ->whereJsonContains('dmc_id', $dmc_id)
                      ->orderBy('name', 'asc')
                      ->get();
        
        // Transform the data to include bed information properly
        $hotels->each(function($hotel) {
            $hotel->rooms->each(function($room) {
                // Attach bed types to each room
                if ($room->beds && $room->beds->isNotEmpty()) {
                    $room->bed_types = $room->beds->map(function($bed) {
                        return [
                            'bed_type_id' => $bed->bed_id,
                            'bed_type' => $bed->room_type ?? 'Standard Bed',
                            'max_occupancy' => $bed->max_occupancy ?? 2,
                            'extra_bed_price' => $bed->extra_bed_price ?? 0,
                            'has_extra_bed' => $bed->extra_bed ? true : false,
                        ];
                    })->toArray();
                } else {
                    // Default bed type if no beds defined
                    $room->bed_types = [
                        [
                            'bed_type_id' => $room->room_id,
                            'bed_type' => 'Standard Bed',
                            'max_occupancy' => 2,
                            'extra_bed_price' => 0,
                            'has_extra_bed' => false,
                        ]
                    ];
                }
                // Remove the beds relation to keep response clean
                unset($room->beds);
            });
        });
        
        return response()->json([
            'success' => true,
            'hotels' => $hotels
        ]);
    }
   
}
