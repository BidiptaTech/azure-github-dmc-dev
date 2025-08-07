<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tour;
use Illuminate\Support\Facades\DB;

class BookingsController extends Controller
{
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

        return view('bookings.new-enquiries', compact('tours'));
    }

    /**
     * Display Follow Ups (tour_status = 'Prospect' and 'Tentative')
     */
    public function followUps()
    {
        $tours = Tour::whereIn('tour_status', ['Prospect', 'Tentative'])
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
                'tours.created_at',
                'tours.updated_at',
                'tours.agent_id',
                'agents.name as agent_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->paginate(15);

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
        $tours = Tour::where('tour_status', 'Confirmed')
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
            'confirmed' => Tour::where('tour_status', 'Confirmed')->count(),
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
}
