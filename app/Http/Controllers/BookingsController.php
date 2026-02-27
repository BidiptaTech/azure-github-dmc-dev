<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CommonHelper;
use App\Models\Agent;
use App\Models\Country;
use App\Models\Enquiry;
use App\Models\EnquiryForm;
use App\Models\Order;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Crypt;

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
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);
        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
        $tours = Tour::where('tour_status', 'New Enquiry')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
                            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
                            ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.multi_enq_id',
                    'tours.tour_type',
                    'tours.adult',
                    'tours.child',
                    'tours.infant',
                    'tours.hotel',
                    'tours.attraction',
                    'tours.travel',
                    'tours.restaurent',
                    'tours.guide',
                    'tours.port',
                    'tours.destination',
                    'tours.city',
                    'tours.is_pro',
                    'tours.check_in_time',
                    'tours.check_out_time',
                    'tours.tour_status',
                    'tours.created_at',
                    'tours.updated_at',
                    'tours.auto_cancel_date',
                    'tours.agent_id',
                    'tours.created_by',
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name'
                    ])
                ->orderBy('tours.created_at', 'desc')
                ->get();
        }

        
        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
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
            $tours = Tour::where('tour_status', 'New Enquiry')
                ->where('tours.dmc_id', $dmc_id)
                ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
                ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
                ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.multi_enq_id',
                    'tours.tour_type',
                    'tours.adult',
                    'tours.child',
                    'tours.infant',
                    'tours.hotel',
                    'tours.attraction',
                    'tours.travel',
                    'tours.restaurent',
                    'tours.guide',
                    'tours.port',
                    'tours.destination',
                    'tours.city',
                    'tours.is_pro',
                    'tours.check_in_time',
                    'tours.check_out_time',
                    'tours.tour_status',
                    'tours.created_at',
                    'tours.updated_at',
                    'tours.auto_cancel_date',
                    'tours.agent_id',
                    'tours.created_by',
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name'
                    ])
                ->orderBy('tours.created_at', 'desc')
                ->get();
        }

        $enquary_comments = Enquiry::where('dmcId', $dmc_id)->get();
        

        // Get filtered agents based on logged-in DMC user
        $filteredAgents = $this->getFilteredAgents();

        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        return view('bookings.new-enquiries', compact('tours', 'filteredAgents', 'enquary_comments', 'country_tax'));
    }

    public function agentNegotiation(Request $request)
    {
        $validated = $request->validate([
            'tour_id' => 'required|integer|exists:tours,tour_id',
            'action' => 'required|in:negotiate,cancel,confirm',
            'amount' => 'required_if:action,negotiate|numeric|min:0.01',
            'comment' => 'nullable|string|max:1000',
        ], [
            'amount.required_if' => 'Please enter a negotiation amount.',
        ]);

        $tour = Tour::where('tour_id', $validated['tour_id'])->firstOrFail();
        $action = $validated['action'];

        $currentUser = auth()->user();
        $changedByName = $currentUser ? ($currentUser->name ?? '') : null;
        $changedByUserId = $currentUser ? ($currentUser->userId ?? $currentUser->id ?? null) : null;

        $latestEnquiry = Enquiry::where('tour_id', $tour->tour_id)->orderByDesc('created_at')->first();
        $activeEnquiry = Enquiry::where('tour_id', $tour->tour_id)
            ->where('status', 1)
            ->orderByDesc('created_at')
            ->first();

        if ($action === 'negotiate') {
            $actualAmount = (float) $request->input('actual_amount', 0);
            if ($actualAmount <= 0) {
                $actualAmount = $this->calculateOrdersTotalAmount($tour->tour_id);
            }
            $amountOffered = (float) $validated['amount'];

            if ($actualAmount > 0 && $amountOffered > $actualAmount) {
                return back()
                    ->withErrors(['amount' => 'Negotiated amount cannot exceed the current amount.'])
                    ->withInput();
            }

            $lastEnquiryId = Enquiry::latest('created_at')->value('enquiry_id') ?? 1;
            $newEnquiryId = CommonHelper::createId($lastEnquiryId);
            while (Enquiry::where('enquiry_id', $newEnquiryId)->exists()) {
                $newEnquiryId = CommonHelper::createId($newEnquiryId);
            }

            $enquiry = Enquiry::create([
                'tour_id' => $tour->tour_id,
                'status' => 1,
                'dmcId' => $tour->dmc_id,
                'enquiry_id' => $newEnquiryId,
                'sender_id' => $tour->agent_id ?? 0,
                'sender_type' => 'agent',
                'receiver_id' => $latestEnquiry->sender_id ?? 0,
                'receiver_type' => 'OM',
                'current_position' => 'OM',
                'amount' => $amountOffered,
                'actual_amount' => $actualAmount ?: ($latestEnquiry->actual_amount ?? 0),
                'comment' => $validated['comment'] ?? '',
            ]);

            if ($enquiry && $activeEnquiry && $activeEnquiry->id !== $enquiry->id) {
                $activeEnquiry->update(['status' => 0]);
            }

            if ($enquiry) {
                return back()->with('success', 'Agent negotiation submitted successfully!');
            }

            return back()->with('error', 'Unable to submit negotiation. Please try again.');
        }

        if ($action === 'cancel') {
            $oldStatus = $tour->tour_status;

            if ($activeEnquiry) {
                $activeEnquiry->update(['status' => 3]);
            }

            $newStatus = $tour->tour_status === 'Definite'
                ? 'Refund - Pending'
                : 'Cancel - ' . $tour->tour_status;

            // Track status change (e.g. New Enquiry -> Cancel - New Enquiry, Definite -> Refund - Pending)
            \App\Helpers\CommonHelper::appendTourStatusTrackById(
                (int) $tour->tour_id,
                $oldStatus,
                $newStatus,
                null,
                null,
                null,
                null,
                $changedByName,
                $changedByUserId
            );

            $tour->update(['tour_status' => $newStatus]);

            return back()->with('success', 'Tour #' . $tour->tour_id . ' cancelled successfully! Status has been updated to ' . $newStatus . '.');
        }

        if ($action === 'confirm') {
            if ($activeEnquiry) {
                $activeEnquiry->update(['status' => 2]);
            }

            Order::where('tour_id', $tour->tour_id)->update(['bookingType' => 'booking']);

            if ($tour->tour_status !== 'Confirmed') {
                $oldStatus = $tour->tour_status;

                // Actual amount at confirmation (from active enquiry or orders total)
                $actualAmount = $activeEnquiry?->actual_amount ?? 0;
                if (empty($actualAmount) || $actualAmount <= 0) {
                    $actualAmount = $this->calculateOrdersTotalAmount($tour->tour_id);
                }
                $amount = $activeEnquiry?->amount ?? $actualAmount;

                // Track status change (e.g. New Enquiry / Prospect / Tentative -> Confirmed)
                \App\Helpers\CommonHelper::appendTourStatusTrackById(
                    (int) $tour->tour_id,
                    $oldStatus,
                    'Confirmed',
                    null,
                    $amount,
                    $activeEnquiry?->comment ?? null,
                    $actualAmount,
                    $changedByName,
                    $changedByUserId
                );

                $tour->update(['tour_status' => 'Confirmed']);
            }

            if (!empty($tour->multi_enq_id)) {
                $formEnquiry = EnquiryForm::where('multi_enq_id', (string) $tour->multi_enq_id)->first();

                if ($formEnquiry && $formEnquiry->multi_enq_id) {
                    EnquiryForm::where('multi_enq_id', (string) $formEnquiry->multi_enq_id)
                        ->where('enquiry_id', '!=', $formEnquiry->enquiry_id)
                        ->update(['status' => 'cancelled']);
                }

                Tour::where('multi_enq_id', (string) $tour->multi_enq_id)
                    ->where('tour_id', '!=', $tour->tour_id)
                    ->update(['deleted_at' => now()]);
            }

            return back()->with('success', 'Tour #' . $tour->tour_id . ' confirmed successfully! Status has been updated to Confirmed and booking has been finalized.');
        }

        return back()->with('error', 'Unsupported action requested.');
    }

    private function calculateOrdersTotalAmount(int $tourId): float
    {
        $orders = Order::where('tour_id', $tourId)->get();
        $sum = 0;

        foreach ($orders as $order) {
            $payload = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            $sum += $this->extractOrderPayloadTotal($payload);
        }

        return round($sum, 2);
    }

    private function extractOrderPayloadTotal($payload): float
    {
        if (is_object($payload)) {
            $payload = (array) $payload;
        }

        if (!is_array($payload)) {
            return 0;
        }

        $priorityKeys = ['totalPrice', 'total_price', 'price', 'amount'];
        foreach ($priorityKeys as $key) {
            if (isset($payload[$key]) && is_numeric($payload[$key])) {
                return (float) $payload[$key];
            }
        }

        $sum = 0;
        foreach ($payload as $value) {
            if (is_array($value) || is_object($value)) {
                $sum += $this->extractOrderPayloadTotal($value);
            }
        }

        return $sum;
    }

    /**
     * Display Follow Ups (tour_status = 'Prospect' and 'Tentative')
     */
    // public function followUps()
    // {
    //     $user = Auth::user();
    //     $dmc_id = null;
    //     $tours = collect([]);

    //     if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
    //         $tours = Tour::whereIn('tour_status', ['Prospect', 'Tentative'])
    //         ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
    //         ->leftJoin('enquiry_comments', 'tours.tour_id', '=', 'enquiry_comments.tour_id')
    //         ->select([
    //             'tours.tour_id',
    //             'tours.display_id',
    //             'tours.multi_enq_id',
    //             'tours.adult',
    //             'tours.child',
    //             'tours.hotel',
    //             'tours.attraction',
    //             'tours.travel',
    //             'tours.restaurent',
    //             'tours.guide',
    //             'tours.port',
    //             'tours.destination',
    //             'tours.city',
    //             'tours.check_in_time',
    //             'tours.check_out_time',
    //             'tours.tour_status',
    //             'tours.created_at',
    //             'tours.updated_at',
    //             'tours.agent_id',
    //             'agents.name as agent_name',
    //             'enquiry_comments.enquiry_id as enquiry_id',
    //             'enquiry_comments.comment as enquiry_comment',
    //             'enquiry_comments.amount as enquiry_comment_amount',
    //             'enquiry_comments.actual_amount as actual_amount',
    //             'enquiry_comments.created_at as enquiry_comment_created_at',
    //             'enquiry_comments.updated_at as enquiry_comment_updated_at',
    //         ])
    //         ->orderBy('tours.created_at', 'desc')
    //         ->paginate(105);

    //     }
        
    //     if($user->role_id == 11){
    //         $dmc_id = $user->userId;
    //     }else if($user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
    //         $dmc_id = $user->created_by;
    //     }else if($user->role_id == 37){
    //         $sales_head = User::where('userId', $user->created_by)->first();
    //         $dmc_id = $sales_head->created_by;
    //     }else if($user->role_id == 38){
    //         $sales_manager = User::where('userId', $user->created_by)->first();
    //         $sales_head = User::where('userId', $sales_manager->created_by)->first();
    //         $dmc_id = $sales_head->created_by;
    //     }

    //     if($dmc_id){
    //         $tours = Tour::whereIn('tour_status', ['Prospect', 'Tentative'])
    //             ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
    //             ->leftJoin('enquiry_comments', 'tours.tour_id', '=', 'enquiry_comments.tour_id')
    //             ->select([
    //                 'tours.tour_id',
    //                 'tours.display_id',
    //                 'tours.multi_enq_id',
    //                 'tours.adult',
    //                 'tours.child',
    //                 'tours.hotel',
    //                 'tours.attraction',
    //                 'tours.travel',
    //                 'tours.restaurent',
    //                 'tours.guide',
    //                 'tours.port',
    //                 'tours.destination',
    //                 'tours.city',
    //                 'tours.check_in_time',
    //                 'tours.check_out_time',
    //                 'tours.tour_status',
    //                 'tours.created_at',
    //                 'tours.updated_at',
    //                 'tours.agent_id',
    //                 'agents.name as agent_name',
    //                 'enquiry_comments.enquiry_id as enquiry_id',
    //                 'enquiry_comments.comment as enquiry_comment',
    //                 'enquiry_comments.amount as enquiry_comment_amount',
    //                 'enquiry_comments.actual_amount as actual_amount',
    //                 'enquiry_comments.created_at as enquiry_comment_created_at',
    //                 'enquiry_comments.updated_at as enquiry_comment_updated_at',
    //             ])
    //             ->where('tours.dmc_id', $dmc_id)
    //             ->orderBy('tours.created_at', 'desc')
    //             ->paginate(15);

    //     }
    //     return view('bookings.follow-ups', compact('tours'));
    // }

    public function followUps()
    {
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);

        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
            $tours = Tour::whereIn('tour_status', ['Prospect', 'Tentative'])
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('enquiry_comments', function($join) {
                $join->on('tours.tour_id', '=', 'enquiry_comments.tour_id')
                     ->whereRaw('enquiry_comments.enquiry_id = (
                         SELECT MAX(ec2.enquiry_id) 
                         FROM enquiry_comments ec2 
                         WHERE ec2.tour_id = tours.tour_id
                     )');
            })
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
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
                'tours.is_pro',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'tours.created_by',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name',
                'enquiry_comments.enquiry_id as enquiry_id',
                'enquiry_comments.comment as enquiry_comment',
                'enquiry_comments.amount as enquiry_comment_amount',
                'enquiry_comments.actual_amount as actual_amount',
                'enquiry_comments.sender_type as enquiry_comment_sender_type',
                'enquiry_comments.created_at as enquiry_comment_created_at',
                'enquiry_comments.updated_at as enquiry_comment_updated_at',
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
        }
        
        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
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
                ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
                ->leftJoin('enquiry_comments', function($join) {
                    $join->on('tours.tour_id', '=', 'enquiry_comments.tour_id')
                         ->whereRaw('enquiry_comments.enquiry_id = (
                             SELECT MAX(ec2.enquiry_id) 
                             FROM enquiry_comments ec2 
                             WHERE ec2.tour_id = tours.tour_id
                         )');
                })
                ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.multi_enq_id',
                    'tours.tour_type',
                    'tours.adult',
                    'tours.child',
                    'tours.infant',
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
                    'tours.is_pro',
                    'tours.created_at',
                    'tours.updated_at',
                    'tours.auto_cancel_date',
                    'tours.agent_id',
                    'tours.created_by',
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name',
                    'enquiry_comments.enquiry_id as enquiry_id',
                    'enquiry_comments.comment as enquiry_comment',
                    'enquiry_comments.amount as enquiry_comment_amount',
                    'enquiry_comments.actual_amount as actual_amount',
                    'enquiry_comments.sender_type as enquiry_comment_sender_type',
                    'enquiry_comments.created_at as enquiry_comment_created_at',
                    'enquiry_comments.updated_at as enquiry_comment_updated_at',
                ])
                ->where('tours.dmc_id', $dmc_id)
                ->orderBy('tours.created_at', 'desc')
                ->get();
        }
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        return view('bookings.follow-ups', compact('tours', 'country_tax'));
    }

    /**
     * Display Tentative Bookings (tour_status = 'Tentative')
     */
    public function tentative()
    {
        $user = Auth::user();
        $tours = Tour::where('tour_status', 'Tentative')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
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
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->paginate(15);

        $country_tax = Country::where('name', optional($user)->country)->value('tax_percentage');
        return view('bookings.tentative', compact('tours', 'country_tax'));
    }

    /**
     * Display Confirmed Bookings (tour_status = 'Confirmed')
     */
    public function confirmedBookings()
    {
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);

        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->where('bookingType', 'booking');
                }
            ])
            ->where('tour_status', 'Confirmed')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
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
                'tours.taxes',
                'tours.is_pro',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
        }
        
        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 37 || $user->role_id == 126 || $user->role_id == 124){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($user->role_id == 38 || $user->role_id == 127 || $user->role_id == 125){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        if($dmc_id){
            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->where('bookingType', 'booking');
                }
            ])
            ->where('tour_status', 'Confirmed')
            ->where('tours.dmc_id', $dmc_id)
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
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
                'tours.taxes',
                'tours.is_pro',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
        }
        return view('bookings.confirmed', compact('tours'));
    }

    /**
     * Display Definite Bookings (tour_status = 'Definite')
     */
    public function definiteBookings()
    {
        $today = Carbon::today();
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);

        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->where('bookingType', 'booking');
                },
                'agent'
            ])
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
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
                'tours.taxes',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name'
            ])
            ->where(function ($query) use ($today) {
                $query->where('tours.tour_status', 'Definite');
                    // ->orWhereDate('tours.updated_at', $today);
            })
            ->orderBy('tours.created_at', 'desc')
            ->get();
        }
        
        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 37 || $user->role_id == 126 || $user->role_id == 124){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($user->role_id == 38 || $user->role_id == 127 || $user->role_id == 125){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        if($dmc_id){
            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->where('bookingType', 'booking');
                },
                'agent'
            ])
            ->where(function ($query) use ($today) {
                $query->where('tours.tour_status', 'Definite');
                    // ->orWhereDate('tours.updated_at', $today);
            })
            ->where('tours.dmc_id', $dmc_id)
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
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
                'tours.taxes',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
        }

        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        return view('bookings.definite', compact('tours', 'country_tax'));
    }

    /**
     * Display Actual Bookings (tour_status = 'Actual')
     */
    public function actualBookings()
    {
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);

        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
            $tours = Tour::where('tour_status', 'Actual')
                ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
                ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
                ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.multi_enq_id',
                    'tours.tour_type',
                    'tours.adult',
                    'tours.child',
                    'tours.infant',
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
                    'tours.taxes',
                    'tours.created_at',
                    'tours.updated_at',
                    'tours.auto_cancel_date',
                    'tours.agent_id',
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name'
                ])
                ->orderBy('tours.created_at', 'desc')
                ->get();
        }
        
        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 37 || $user->role_id == 126 || $user->role_id == 124){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($user->role_id == 38 || $user->role_id == 127 || $user->role_id == 125){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        if($dmc_id){
            $tours = Tour::where('tour_status', 'Actual')
                ->where('tours.dmc_id', $dmc_id)
                ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
                ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
                ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.multi_enq_id',
                    'tours.tour_type',
                    'tours.adult',
                    'tours.child',
                    'tours.infant',
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
                    'tours.taxes',
                    'tours.created_at',
                    'tours.updated_at',
                    'tours.auto_cancel_date',
                    'tours.agent_id',
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name'
                ])
                ->orderBy('tours.created_at', 'desc')
                ->get();
        }

        // Parse payment details for each tour
        $tours->transform(function ($tour) {
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

        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        return view('bookings.actual', compact('tours', 'country_tax'));
    }

    /**
     * Display Cancelled Bookings (tour_status contains 'Cancel')
     */
    public function cancelledBookings()
    {
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);

        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
        $tours = Tour::where(function($query) {
                $query->where('tour_status', 'LIKE', 'Cancel%')
                      ->orWhere('tour_status', 'LIKE', '%Cancel%');
            })
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
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
                'tours.auto_cancel_date',
                'tours.agent_id',
                'tours.created_by',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
        }

        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 37 || $user->role_id == 126 || $user->role_id == 124){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($user->role_id == 38 || $user->role_id == 127 || $user->role_id == 125){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        if($dmc_id){
            $tours = Tour::where(function($query) {
                $query->where('tour_status', 'LIKE', 'Cancel%')
                      ->orWhere('tour_status', 'LIKE', '%Cancel%');
            })
            ->where('tours.dmc_id', $dmc_id)
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'tours.created_by',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
        }

        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        return view('bookings.cancelled', compact('tours', 'country_tax'));
    }

    /**
     * Display Refunds (tour_status = 'Refund - Pending')
     */
    public function refunds()
    {
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);

        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->where('bookingType', 'booking');
                }
            ])
            ->whereIn('tour_status', ['Refund - Pending', 'Refunded'])
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.infant',
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
                'tours.auto_cancel_date',
                'tours.agent_id',
                'tours.dmc_id',
                'tours.created_by',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
        }
        
        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 37 || $user->role_id == 126 || $user->role_id == 124){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($user->role_id == 38 || $user->role_id == 127 || $user->role_id == 125){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        if($dmc_id){
            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->where('bookingType', 'booking');
                }
            ])
            ->whereIn('tour_status', ['Refund - Pending', 'Refunded'])
            ->where('tours.dmc_id', $dmc_id)
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.infant',
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
                'tours.auto_cancel_date',
                'tours.agent_id',
                'tours.dmc_id',
                'tours.created_by',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
        }
        
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        return view('bookings.refunds', compact('tours', 'country_tax'));
    }

    /**
     * Process refund for a tour (update tour_status from 'Refund - Pending' to 'Refunded')
     */
    public function processRefund(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer|exists:tours,tour_id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid tour ID provided'
                ], 422);
            }

            $tourId = $request->tour_id;
            
            // Find the tour
            $tour = Tour::where('tour_id', $tourId)
                       ->where('tour_status', 'Refund - Pending')
                       ->first();

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found or not eligible for refund processing'
                ], 404);
            }

            // Update tour status to Refunded and track transition
            $oldStatus = $tour->tour_status; // Should be 'Refund - Pending'
            $tour->tour_status = 'Refunded';
            $tour->updated_at = now();

            // Append to track_details history: Refund - Pending -> Refunded
            \App\Helpers\CommonHelper::appendTourStatusTrack(
                $tour,
                $oldStatus,
                $tour->tour_status,
                $tour->updated_at
            );

            $tour->save();

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'tour_id' => $tourId,
                'new_status' => 'Refunded'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the refund: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display Cancellations & Refunds (tour_status = 'Cancelled')
     */
    public function cancellationsRefunds()
    {
        $user = Auth::user();
        $tours = Tour::where('tour_status', 'Cancelled')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();

        $country_tax = Country::where('name', optional($user)->country)->value('tax_percentage');
        return view('bookings.cancellations-refunds', compact('tours', 'country_tax'));
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
    public function viewTour($encryptedId)
    {
        $tourId = Crypt::decrypt($encryptedId);
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
    public function exportTourPDF(Request $request, $tourId)
    {
        try {
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

            // Check if this is a POST request with HTML content (from JavaScript)
            if ($request->isMethod('post') && $request->has('html_content')) {
                // Use the HTML content sent from JavaScript
                $html = $request->input('html_content');
                $tourTitle = $request->input('tour_title', $tour->display_id);
                
                // Try to generate PDF using dompdf (if available)
                if (class_exists('Dompdf\Dompdf')) {
                    $dompdf = new Dompdf([
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true,
                        'chroot' => public_path(),
                        'enable_php' => false
                    ]);
                    
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();
                    
                    $filename = 'Tour_Details_' . preg_replace('/[^a-zA-Z0-9]/', '_', $tourTitle) . '.pdf';
                    
                    return response($dompdf->output())
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                        ->header('Cache-Control', 'no-store, no-cache');
                }
                
                // Fallback: return HTML with PDF-optimized styling
                $filename = 'Tour_Details_' . preg_replace('/[^a-zA-Z0-9]/', '_', $tourTitle) . '.html';
                
                return response($html)
                    ->header('Content-Type', 'text/html; charset=utf-8')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                    ->header('Cache-Control', 'no-store, no-cache');
            }

            // Default behavior: Generate PDF view
            $html = view('bookings.tour-pdf', compact('tour'))->render();
            
            // Try to generate PDF using dompdf (if available)
            if (class_exists('Dompdf\Dompdf')) {
                $dompdf = new Dompdf([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'chroot' => public_path(),
                    'enable_php' => false
                ]);
                
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                
                $filename = 'Tour_Details_' . $tour->display_id . '.pdf';
                
                return response($dompdf->output())
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                    ->header('Cache-Control', 'no-store, no-cache');
            }
            
            // Fallback: return HTML file
            $filename = 'Tour_Details_' . $tour->display_id . '.html';
            
            return response($html)
                ->header('Content-Type', 'text/html; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-store, no-cache');
                
        } catch (\Exception $e) {
            Log::error('PDF Export Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a tour by updating tour_status to 'Cancel'
     */
    public function cancelTour(Request $request, $encryptedId)
    {
        try {
            // Decrypt the tour ID
            $tourId = Crypt::decrypt($encryptedId);
            
            // Find the tour
            $tour = Tour::where('tour_id', $tourId)->first();
            
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }
            
            // Check if tour is already cancelled
            if ($tour->tour_status === 'Cancel') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour is already cancelled'
                ], 400);
            }
            
            // Update tour status to Cancel (and track history)
            $oldStatus = $tour->tour_status;

            if ($tour->tour_status == 'Definite') {
                $tour->tour_status = 'Refund - Pending';
            } else {
                $tour->tour_status = 'Cancel-' . $tour->tour_status;
            }

            // Track status change, e.g. Definite -> Refund - Pending, or X -> Cancel-X
            \App\Helpers\CommonHelper::appendTourStatusTrack(
                $tour,
                $oldStatus,
                $tour->tour_status
            );

            $tour->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Tour cancelled successfully',
                'tour_id' => $tour->display_id,
                'new_status' => $tour->tour_status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel tour: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveQrCode(Request $request, $encryptedId)
    {
        try {
            $qrCode = '';
            if ($request->hasFile('qr_code')) {
                $pathData = CommonHelper::image_path('file_storage', $request->file('qr_code'));
                if (!empty($pathData['master_value'])) {
                    $qrCode = $pathData['master_value'];
                }
            }
            // Allow either an encrypted id or a plain numeric booking_id
            $orderId = is_numeric($encryptedId) ? $encryptedId : Crypt::decrypt($encryptedId);
            $order = Order::where('booking_id', $orderId)->first();
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $order->qr_code = $qrCode;
            $order->save();
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save QR code: ' . $e->getMessage()
            ], 500);
        }
        return response()->json([
            'success' => true,
            'message' => 'QR code saved successfully',
            'order_id' => $order->booking_id,
            'qr_code' => $qrCode
        ]);
    }
}
