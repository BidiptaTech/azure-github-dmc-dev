<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\User;
use App\Models\Agency;
use App\Models\Country;
use App\Models\Agent;
class EnquiryFormPro extends Controller
{
    public function create()
    {
        $user = auth()->user();
        $destination = $user->country ?? 'Singapore';
        $agencies = Agency::where('status', 1)->where('country', $destination)->get();
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        $agents = []; // Start with empty agents, will be populated via AJAX
        return view('enquiryform_pro.create', compact('destination', 'agents', 'agencies', 'user', 'countries'));
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
