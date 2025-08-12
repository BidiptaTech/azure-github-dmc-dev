<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tour;
use App\Models\User;
use App\Models\Agent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookingsController extends Controller
{
    /**
     * Get filtered agents based on logged-in DMC user
     */
    private function getFilteredAgents()
    {
        $user = Auth::user();
        
        // If no user or not a DMC role, return all agents
        if (!$user || !in_array($user->role_id, [11, 33, 37, 38])) {
            return Agent::where('status', 1)->get();
        }
        
        $agents = collect();
        $dmc_id = null;
        
        switch ($user->role_id) {
            case 11: // DMC
                $dmc_id = $user->userId;
                break;
                
            case 33: // Sales Head
                $dmc_id = $user->created_by;
                break;
                
            case 37: // Sales Manager
                // Get parent DMC ID by traversing up the hierarchy
                $parentUser = User::where('userId', $user->created_by)->first();
                while ($parentUser && !in_array($parentUser->role_id, [11])) {
                    $parentUser = User::where('userId', $parentUser->created_by)->first();
                }
                if ($parentUser && $parentUser->role_id == 11) {
                    $dmc_id = $parentUser->userId;
                }
                break;
                
            case 38: // Assistant Sales Manager
                // Get parent DMC ID by traversing up the hierarchy
                $parentUser = User::where('userId', $user->created_by)->first();
                while ($parentUser && !in_array($parentUser->role_id, [11])) {
                    $parentUser = User::where('userId', $parentUser->created_by)->first();
                }
                if ($parentUser && $parentUser->role_id == 11) {
                    $dmc_id = $parentUser->userId;
                }
                break;
        }
        
        if ($dmc_id) {
            // Get agents that have this DMC ID in their dmc_id field
            $agents = Agent::where('status', 1)
                ->whereRaw("CASE 
                    WHEN dmc_id IS NOT NULL 
                    THEN (
                        CASE 
                            WHEN dmc_id::text ~ '^\\[.*\\]$' 
                            THEN dmc_id::jsonb @> ?::jsonb
                            WHEN dmc_id::text ~ '^\\{.*\\}$'
                            THEN dmc_id::jsonb @> ?::jsonb
                            ELSE dmc_id::text LIKE ?
                        END
                    )
                    ELSE false
                END", [
                    json_encode([$dmc_id]),
                    json_encode([$dmc_id]),
                    "%{$dmc_id}%"
                ])
                ->get();
        }
        
        return $agents;
    }

    /**
     * Display New Enquiries (tour_status = 'New Enquiry')
     */
    public function newEnquiries()
    {
        $tours = Tour::where('tour_status', 'New Enquiry')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                // 'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.created_at',
                'tours.updated_at',
                'tours.agent_id',
                'agents.name as agent_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->paginate(15);

        // Get filtered agents based on logged-in DMC user
        $filteredAgents = $this->getFilteredAgents();

        return view('bookings.new-enquiries', compact('tours', 'filteredAgents'));
    }

    /**
     * Display Follow Ups (tour_status = 'Prospect' and 'Tentative')
     */
    public function followUps()
    {
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);

        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
            $tours = Tour::whereIn('tour_status', ['Prospect', 'Tentative'])
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('enquiry_comments', 'tours.tour_id', '=', 'enquiry_comments.tour_id')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.created_at',
                'tours.updated_at',
                'tours.agent_id',
                'agents.name as agent_name',
                'enquiry_comments.enquiry_id as enquiry_id',
                'enquiry_comments.comment as enquiry_comment',
                'enquiry_comments.amount as enquiry_comment_amount',
                'enquiry_comments.actual_amount as actual_amount',
                'enquiry_comments.created_at as enquiry_comment_created_at',
                'enquiry_comments.updated_at as enquiry_comment_updated_at',
            ])
            ->orderBy('tours.created_at', 'desc')
            ->paginate(105);

        }
        
        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 37){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($user->role_id == 38){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        if($dmc_id){
            $tours = Tour::whereIn('tour_status', ['Prospect', 'Tentative'])
                ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
                ->leftJoin('enquiry_comments', 'tours.tour_id', '=', 'enquiry_comments.tour_id')
                ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.multi_enq_id',
                    'tours.adult',
                    'tours.child',
                    'tours.hotel',
                    'tours.attraction',
                    'tours.travel',
                    'tours.restaurent',
                    'tours.guide',
                    'tours.port',
                    'tours.destination',
                    'tours.city',
                    'tours.check_in_time',
                    'tours.check_out_time',
                    'tours.tour_status',
                    'tours.created_at',
                    'tours.updated_at',
                    'tours.agent_id',
                    'agents.name as agent_name',
                    'enquiry_comments.enquiry_id as enquiry_id',
                    'enquiry_comments.comment as enquiry_comment',
                    'enquiry_comments.amount as enquiry_comment_amount',
                    'enquiry_comments.actual_amount as actual_amount',
                    'enquiry_comments.created_at as enquiry_comment_created_at',
                    'enquiry_comments.updated_at as enquiry_comment_updated_at',
                ])
                ->where('tours.dmc_id', $dmc_id)
                ->orderBy('tours.created_at', 'desc')
                ->paginate(15);
        }
        return view('bookings.follow-ups', compact('tours'));
    }

    /**
     * Display Tentative Bookings (tour_status = 'Tentative')
     */
    public function tentative()
    {
        $tours = Tour::where('tour_status', 'Tentative')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.created_at',
                'tours.updated_at',
                'tours.agent_id',
                'agents.name as agent_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->paginate(15);

        return view('bookings.tentative', compact('tours'));
    }

    /**
     * Display Confirmed Bookings (tour_status = 'Confirmed')
     */
    public function confirmedBookings()
    {
        $tours = Tour::where('tour_status', 'On Hold')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.created_at',
                'tours.updated_at',
                'tours.agent_id',
                'agents.name as agent_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->paginate(15);

        return view('bookings.confirmed', compact('tours'));
    }

    /**
     * Display Definite Bookings (tour_status = 'Definite')
     */
    public function definiteBookings()
    {
        $tours = Tour::where('tour_status', 'Definite')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.created_at',
                'tours.updated_at',
                'tours.agent_id',
                'agents.name as agent_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->paginate(15);

        return view('bookings.definite', compact('tours'));
    }

    /**
     * Display Actual Bookings (tour_status = 'Actual')
     */
    public function actualBookings()
    {
        $tours = Tour::where('tour_status', 'Actual')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.created_at',
                'tours.updated_at',
                'tours.agent_id',
                'agents.name as agent_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->paginate(15);

        // Parse payment details for each tour
        $tours->getCollection()->transform(function ($tour) {
            if ($tour->payment_details) {
                try {
                    $tour->parsed_payment_details = json_decode($tour->payment_details, true);
                } catch (\Exception $e) {
                    $tour->parsed_payment_details = [];
                }
            } else {
                $tour->parsed_payment_details = [];
            }
            return $tour;
        });

        return view('bookings.actual', compact('tours'));
    }

    /**
     * Display Cancelled Bookings (tour_status contains 'Cancel')
     */
    public function cancelledBookings()
    {
        $tours = Tour::where(function($query) {
                $query->where('tour_status', 'LIKE', 'Cancel%')
                      ->orWhere('tour_status', 'LIKE', '%Cancel%');
            })
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.created_at',
                'tours.updated_at',
                'tours.agent_id',
                'agents.name as agent_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->paginate(15);

        return view('bookings.cancelled', compact('tours'));
    }

    /**
     * Display Refunds (placeholder for future implementation)
     */
    public function refunds()
    {
        // For now, return empty data as refunds section is not needed yet
        $tours = collect([]);
        
        return view('bookings.refunds', compact('tours'));
    }

    /**
     * Display Cancellations & Refunds (tour_status = 'Cancelled')
     */
    public function cancellationsRefunds()
    {
        $tours = Tour::where('tour_status', 'Cancelled')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.created_at',
                'tours.updated_at',
                'tours.agent_id',
                'agents.name as agent_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->paginate(15);

        return view('bookings.cancellations-refunds', compact('tours'));
    }

    /**
     * Get booking statistics for dashboard
     */
    public function getBookingStats()
    {
        $stats = [
            'new_enquiries' => Tour::where('tour_status', 'New Enquiry')->count(),
            'follow_ups' => Tour::where('tour_status', 'Prospect')->count(),
            'tentative' => Tour::where('tour_status', 'Tentative')->count(),
            'confirmed' => Tour::where('tour_status', 'On Hold')->count(),
            'definite' => Tour::where('tour_status', 'Definite')->count(),
            'actual' => Tour::where('tour_status', 'Actual')->count(),
            'cancelled' => Tour::where('tour_status', 'Cancelled')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * View specific tour details
     */
    public function viewTour($tourId)
    {
        $tour = Tour::where('tour_id', $tourId)->firstOrFail();
        
        // Parse payment details if exists
        if ($tour->payment_details) {
            try {
                $tour->parsed_payment_details = json_decode($tour->payment_details, true);
            } catch (\Exception $e) {
                $tour->parsed_payment_details = [];
            }
        } else {
            $tour->parsed_payment_details = [];
        }

        return view('bookings.view-tour', compact('tour'));
    }

    /**
     * Export tour details as PDF
     */
    public function exportTourPDF($tourId)
    {
        $tour = Tour::where('tour_id', $tourId)
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->select([
                'tours.*',
                'agents.name as agent_name'
            ])
            ->firstOrFail();
        
        // Parse payment details if exists
        if ($tour->payment_details) {
            try {
                $tour->parsed_payment_details = json_decode($tour->payment_details, true);
            } catch (\Exception $e) {
                $tour->parsed_payment_details = [];
            }
        } else {
            $tour->parsed_payment_details = [];
        }

        // Return the PDF view for download
        $html = view('bookings.tour-pdf', compact('tour'))->render();
        
        // Set proper headers for PDF download
        $filename = 'tour-details-' . $tour->display_id . '.html';
        
        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-store, no-cache');
    }
}
