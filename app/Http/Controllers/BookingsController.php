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
     * Format display_id as company_code/user_code/ENQxxxx (strip DMC- prefix).
     * Expects tour items to have created_by_company_code and created_by_user_code, or dmc_company_code and created_by_user_code.
     */
    private function formatToursDisplayId($tours)
    {
        $items = $tours instanceof \Illuminate\Pagination\LengthAwarePaginator ? $tours->getCollection() : $tours;
        foreach ($items as $t) {
            $rest = preg_replace('/^DMC\-/i', '', $t->display_id ?? '');
            $companyCode = $t->dmc_company_code ?? $t->created_by_company_code ?? '';
            $userCode = $t->created_by_user_code ?? '';
            $prefixParts = array_filter([$companyCode, $userCode], 'strlen');
            $t->display_id = $prefixParts ? implode('/', $prefixParts) . '/' . $rest : $rest;
        }
        return $tours;
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
                            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
                            ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.reference_id',
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
                    'tours.mainguest', 
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name',
                    'dmc_user.company_code as dmc_company_code',
                    'created_by_user.user_code as created_by_user_code'
                    ])
                ->orderBy('tours.created_at', 'desc')
                ->get();

            foreach ($tours as $t) {
                $rest = preg_replace('/^DMC\-/i', '', $t->display_id ?? '');
                $prefixParts = array_filter([
                    $t->dmc_company_code ?? '',
                    $t->created_by_user_code ?? ''
                ], 'strlen');
                $t->display_id = $prefixParts ? implode('/', $prefixParts) . '/' . $rest : $rest;
            }

                foreach ($tours as $t) {
                    \Log::info('New Enquiry guest debug', [
                        'tour_id'        => $t->tour_id,
                        'display_id'     => $t->display_id,
                        'mainguest_raw'  => $t->mainguest,
                        'customer_name'  => $t->customer_name ?? null,
                    ]);
                }
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
                ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
                ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.reference_id',
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
                    'tours.mainguest', 
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name',
                    'dmc_user.company_code as dmc_company_code',
                    'created_by_user.user_code as created_by_user_code'
                    ])
                ->orderBy('tours.created_at', 'desc')
                ->get();

            foreach ($tours as $t) {
                $rest = preg_replace('/^DMC\-/i', '', $t->display_id ?? '');
                $prefixParts = array_filter([
                    $t->dmc_company_code ?? '',
                    $t->created_by_user_code ?? ''
                ], 'strlen');
                $t->display_id = $prefixParts ? implode('/', $prefixParts) . '/' . $rest : $rest;
            }

            foreach ($tours as $t) {
                \Log::info('New Enquiry guest debug (DMC scope)', [
                    'tour_id'        => $t->tour_id,
                    'display_id'     => $t->display_id,
                    'mainguest_raw'  => $t->mainguest,
                    'customer_name'  => $t->customer_name ?? null,
                ]);
            }
        }

        $enquary_comments = Enquiry::where('dmcId', $dmc_id)->get();
 
        // Get filtered agents based on logged-in DMC user
        $filteredAgents = $this->getFilteredAgents();
        $currency = Country::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower((string) ($user->country ?? ''))])
            ->value('currency');
        $currency = is_string($currency) && trim($currency) !== '' ? trim($currency) : (CommonHelper::getDmcCurrencyByCountry() ?: 'SGD');
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        return view('bookings.new-enquiries', compact('tours', 'filteredAgents', 'enquary_comments', 'country_tax', 'currency'));
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

            // Include soft-deleted records: enquiry_id is unique, so soft-deleted rows still occupy their ID
            $lastEnquiryId = Enquiry::withTrashed()->max('enquiry_id') ?? 1;
            $newEnquiryId = CommonHelper::createId($lastEnquiryId);
            while (Enquiry::withTrashed()->where('enquiry_id', $newEnquiryId)->exists()) {
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
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
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
                'tours.reference_id',
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
                'tours.mainguest',
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
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code',
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->formatToursDisplayId($tours);
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
                ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
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
                    'tours.reference_id',
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
                    'tours.mainguest',
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
                    'dmc_user.company_code as dmc_company_code',
                    'created_by_user.user_code as created_by_user_code',
                ])
                ->where('tours.dmc_id', $dmc_id)
                ->orderBy('tours.created_at', 'desc')
                ->get();
            $this->formatToursDisplayId($tours);
        }
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        $currency = CommonHelper::getDmcCurrencyByCountry();
        return view('bookings.follow-ups', compact('tours', 'country_tax', 'currency'));
    }

    /**
     * Display Tentative Bookings (tour_status = 'Tentative')
     */
    public function tentative()
    {
        $user = Auth::user();
        $tours = Tour::where('tour_status', 'Tentative')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.reference_id',
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
                'agents.name as agent_name',
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code',
                'created_by_user.company_code as created_by_company_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->paginate(15);
        $this->formatToursDisplayId($tours);
        $currency = CommonHelper::getDmcCurrencyByCountry();
        $country_tax = Country::where('name', optional($user)->country)->value('tax_percentage');
        return view('bookings.tentative', compact('tours', 'country_tax', 'currency'));
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
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.reference_id',
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
                'tours.user_currency',
                'tours.mainguest',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'dmc_user.auto_cancel_date as dmc_auto_cancel_day',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->formatToursDisplayId($tours);
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
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.reference_id',
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
                'tours.user_currency',
                'tours.mainguest',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'dmc_user.auto_cancel_date as dmc_auto_cancel_day',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->formatToursDisplayId($tours);
        }
        $currency = CommonHelper::getDmcCurrencyByCountry();

        // Prepare exchange-rate sources for the "Add Payment" modal:
        // - DMC rate: countries.exchange_rate JSON keyed by tour.dmc_id
        // - Previous rate: last entry in tours.payment_details JSON array
        if ($tours && $tours->count() > 0) {
            // Build a lightweight lookup of countries by normalized name for best-effort matching.
            $destinations = $tours->pluck('destination')
                ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                ->map(fn ($v) => mb_strtolower(trim($v)))
                ->unique()
                ->values()
                ->all();

            $countriesByName = [];
            if (!empty($destinations)) {
                Country::query()
                    ->whereIn(DB::raw('LOWER(name)'), $destinations)
                    ->get()
                    ->each(function ($c) use (&$countriesByName) {
                        $key = is_string($c->name) ? mb_strtolower(trim($c->name)) : '';
                        if ($key !== '') {
                            $countriesByName[$key] = $c;
                        }
                    });
            }
            

            foreach ($tours as $tour) {
                // Previous rate/currency from last payment_details entry (if any).
                $prevRate = null;
                $prevCurrency = null;
                $isDefiniteTour = is_string($tour->tour_status) && strcasecmp($tour->tour_status, 'Definite') === 0;
                if ($isDefiniteTour) {
                    $paymentDetails = $tour->payment_details;
                    if (is_string($paymentDetails) && trim($paymentDetails) !== '') {
                        $decoded = json_decode($paymentDetails, true);
                        $paymentDetails = is_array($decoded) ? $decoded : [];
                    }
                    if (is_array($paymentDetails) && !empty($paymentDetails)) {
                        $last = end($paymentDetails);
                        if (is_array($last)) {
                            $prevRate = $last['exchange_rate'] ?? null;
                            $prevCurrency = $last['currency'] ?? null;
                        }
                    }
                }
                $tour->previous_exchange_rate = is_scalar($prevRate) ? (string) $prevRate : null;
                $tour->previous_payment_currency = is_scalar($prevCurrency) ? (string) $prevCurrency : null;

                // DMC rate lookup: countries.exchange_rate[dmc_id].value
                $dmcRate = null;
                $destKey = is_string($tour->destination) ? mb_strtolower(trim($tour->destination)) : '';
                $country = $destKey !== '' ? ($countriesByName[$destKey] ?? null) : null;
                
                if ($country && !empty($dmc_id)) {
                    
                    // countries.exchange_rate format is an array of objects, e.g.
                    // [
                    //   { "dmc_id": 4, "exchange_rate": 5 }
                    // ]
                    $exchangeRateRaw = $country->exchange_rate;
                    
                    if (is_string($exchangeRateRaw) && trim($exchangeRateRaw) !== '') {
                        $decoded = json_decode($exchangeRateRaw, true);
                        $exchangeRateRaw = is_array($decoded) ? $decoded : [];
                    }
                    $exchangeRateArr = is_array($exchangeRateRaw) ? $exchangeRateRaw : [];
                    
                    $match = collect($exchangeRateArr)->firstWhere('dmc_id', (int) $dmc_id);
                    if (is_array($match)) {
                        $dmcRate = $match['exchange_rate'] ?? null;
                    }
                }
                
                $tour->dmc_exchange_rate_value = is_scalar($dmcRate) ? (string) $dmcRate : null;
            }
        }

        $tourIds = $tours->pluck('tour_id')->toArray();
        $tourNegotiationHistory = [];
        if (!empty($tourIds)) {
            $withComments = DB::table('enquiry_comments')
                ->whereIn('tour_id', $tourIds)
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck('tour_id')
                ->flip()
                ->toArray();
            foreach ($tourIds as $tid) {
                $tourNegotiationHistory[$tid] = isset($withComments[$tid]);
            }
        }

        return view('bookings.confirmed', compact('tours', 'currency', 'tourNegotiationHistory'));
    }

    /**
     * Fetch DMC exchange rate for selected currency (AJAX).
     */
    public function getDmcExchangeRate(Request $request)
    {
        $tourId = $request->query('tour_id');
        $currency = trim((string) $request->query('currency', ''));
        $defaultRate = '1';

        if (empty($tourId) || $currency === '') {
            return response()->json([
                'success' => false,
                'message' => 'tour_id and currency are required.',
            ], 422);
        }

        $tour = Tour::query()
            ->select(['tour_id', 'dmc_id', 'destination'])
            ->where('tour_id', $tourId)
            ->first();

        if (!$tour || empty($tour->dmc_id)) {
            return response()->json([
                'success' => true,
                'dmc_rate' => $defaultRate,
            ]);
        }

        // Primary lookup by currency selected in modal.
        $country = Country::query()
            ->whereRaw('LOWER(currency) = ?', [mb_strtolower($currency)])
            ->first();

        // Fallback to destination-country mapping if no direct currency country found.
        if (!$country && is_string($tour->destination) && trim($tour->destination) !== '') {
            $country = Country::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($tour->destination))])
                ->first();
        }

        if (!$country) {
            return response()->json([
                'success' => true,
                'dmc_rate' => $defaultRate,
            ]);
        }

        $exchangeRateRaw = $country->exchange_rate;
        if (is_string($exchangeRateRaw) && trim($exchangeRateRaw) !== '') {
            $decoded = json_decode($exchangeRateRaw, true);
            $exchangeRateRaw = is_array($decoded) ? $decoded : [];
        }
        $exchangeRateArr = is_array($exchangeRateRaw) ? $exchangeRateRaw : [];

        $match = collect($exchangeRateArr)->firstWhere('dmc_id', (int) $tour->dmc_id);
        $dmcRate = is_array($match) ? ($match['exchange_rate'] ?? null) : null;
        
        return response()->json([
            'success' => true,
            'dmc_rate' => is_scalar($dmcRate) ? (string) $dmcRate : $defaultRate,
        ]);
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
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.reference_id',
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
                'tours.user_currency',
                'tours.mainguest',
                'tours.created_at',
                'tours.created_by',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'dmc_user.auto_cancel_date as dmc_auto_cancel_day',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->where(function ($query) use ($today) {
                $query->where('tours.tour_status', 'Definite');
                    // ->orWhereDate('tours.updated_at', $today);
            })
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->formatToursDisplayId($tours);
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
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.reference_id',
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
                'tours.user_currency',
                'tours.mainguest',
                'tours.created_at',
                'tours.created_by',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'dmc_user.company_code as dmc_company_code',
                'dmc_user.auto_cancel_date as dmc_auto_cancel_day',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->formatToursDisplayId($tours);
        }

        $currency = Country::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower((string) ($user->country ?? ''))])
            ->value('currency');
        $currency = is_string($currency) && trim($currency) !== '' ? trim($currency) : (CommonHelper::getDmcCurrencyByCountry() ?: 'SGD');
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');

        // Prepare exchange-rate sources for the "Add Payment" modal:
        // - DMC rate: countries.exchange_rate JSON array of objects keyed by dmc_id
        // - Previous rate: last entry in tours.payment_details JSON array (Definite tours only)
        if ($tours && $tours->count() > 0) {
            $destinations = $tours->pluck('destination')
                ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                ->map(fn ($v) => mb_strtolower(trim($v)))
                ->unique()
                ->values()
                ->all();

            $countriesByName = [];
            if (!empty($destinations)) {
                Country::query()
                    ->whereIn(DB::raw('LOWER(name)'), $destinations)
                    ->get()
                    ->each(function ($c) use (&$countriesByName) {
                        $key = is_string($c->name) ? mb_strtolower(trim($c->name)) : '';
                        if ($key !== '') {
                            $countriesByName[$key] = $c;
                        }
                    });
            }

            foreach ($tours as $tour) {
                // Previous rate/currency from last payment_details entry (if any).
                $prevRate = null;
                $prevCurrency = null;
                $isDefiniteTour = is_string($tour->tour_status) && strcasecmp($tour->tour_status, 'Definite') === 0;
                if ($isDefiniteTour) {
                    $paymentDetails = $tour->payment_details;
                    if (is_string($paymentDetails) && trim($paymentDetails) !== '') {
                        $decoded = json_decode($paymentDetails, true);
                        $paymentDetails = is_array($decoded) ? $decoded : [];
                    }
                    if (is_array($paymentDetails) && !empty($paymentDetails)) {
                        $last = end($paymentDetails);
                        if (is_array($last)) {
                            $prevRate = $last['exchange_rate'] ?? null;
                            $prevCurrency = $last['currency'] ?? null;
                        }
                    }
                }
                $tour->previous_exchange_rate = is_scalar($prevRate) ? (string) $prevRate : null;
                $tour->previous_payment_currency = is_scalar($prevCurrency) ? (string) $prevCurrency : null;

                // DMC rate lookup: countries.exchange_rate is an array of objects.
                $dmcRate = null;
                $destKey = is_string($tour->destination) ? mb_strtolower(trim($tour->destination)) : '';
                $country = $destKey !== '' ? ($countriesByName[$destKey] ?? null) : null;
                if ($country && $dmc_id) {
                    $exchangeRateRaw = $country->exchange_rate;
                    if (is_string($exchangeRateRaw) && trim($exchangeRateRaw) !== '') {
                        $decoded = json_decode($exchangeRateRaw, true);
                        $exchangeRateRaw = is_array($decoded) ? $decoded : [];
                    }
                    $exchangeRateArr = is_array($exchangeRateRaw) ? $exchangeRateRaw : [];

                    $match = collect($exchangeRateArr)->firstWhere('dmc_id', (int) $dmc_id);
                    if (is_array($match)) {
                        $dmcRate = $match['exchange_rate'] ?? null;
                    }
                }
                $tour->dmc_exchange_rate_value = is_scalar($dmcRate) ? (string) $dmcRate : null;
            
            }
        }

        $tourIds = $tours->pluck('tour_id')->toArray();
        $tourNegotiationHistory = [];
        if (!empty($tourIds)) {
            $withComments = DB::table('enquiry_comments')
                ->whereIn('tour_id', $tourIds)
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck('tour_id')
                ->flip()
                ->toArray();
            foreach ($tourIds as $tid) {
                $tourNegotiationHistory[$tid] = isset($withComments[$tid]);
            }
        }

        return view('bookings.definite', compact('tours', 'country_tax', 'currency', 'tourNegotiationHistory'));
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
            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->where('bookingType', 'booking');
                }
            ])
                ->whereIn('tour_status', ['Actual', 'Complete'])
                ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
                ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
                ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
                ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.reference_id',
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
                    'tours.user_currency',
                    'tours.mainguest',
                    'tours.created_at',
                    'tours.updated_at',
                    'tours.auto_cancel_date',
                    'tours.agent_id',
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name',
                    'dmc_user.company_code as dmc_company_code',
                    'dmc_user.auto_cancel_date as dmc_auto_cancel_day',
                    'created_by_user.user_code as created_by_user_code'
                ])
                ->orderBy('tours.created_at', 'desc')
                ->get();
            $this->formatToursDisplayId($tours);
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
                ->whereIn('tour_status', ['Actual', 'Complete'])
                ->where('tours.dmc_id', $dmc_id)
                ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
                ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
                ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
                ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.reference_id',
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
                    'tours.user_currency',
                    'tours.mainguest',
                    'tours.created_at',
                    'tours.updated_at',
                    'tours.auto_cancel_date',
                    'tours.agent_id',
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name',
                    'dmc_user.company_code as dmc_company_code',
                    'dmc_user.auto_cancel_date as dmc_auto_cancel_day',
                    'created_by_user.user_code as created_by_user_code'
                ])
                ->orderBy('tours.created_at', 'desc')
                ->get();
            $this->formatToursDisplayId($tours);
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

        // Prepare exchange-rate sources for the "Add Payment" modal:
        // - DMC rate: countries.exchange_rate JSON array of objects keyed by dmc_id
        // - Previous rate: last entry in tours.payment_details JSON array
        if ($tours && $tours->count() > 0) {
            $destinations = $tours->pluck('destination')
                ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                ->map(fn ($v) => mb_strtolower(trim($v)))
                ->unique()
                ->values()
                ->all();

            $countriesByName = [];
            if (!empty($destinations)) {
                Country::query()
                    ->whereIn(DB::raw('LOWER(name)'), $destinations)
                    ->get()
                    ->each(function ($c) use (&$countriesByName) {
                        $key = is_string($c->name) ? mb_strtolower(trim($c->name)) : '';
                        if ($key !== '') {
                            $countriesByName[$key] = $c;
                        }
                    });
            }

            foreach ($tours as $tour) {
                $prevRate = null;
                $prevCurrency = null;
                $paymentDetails = $tour->payment_details;
                if (is_string($paymentDetails) && trim($paymentDetails) !== '') {
                    $decoded = json_decode($paymentDetails, true);
                    $paymentDetails = is_array($decoded) ? $decoded : [];
                }
                if (is_array($paymentDetails) && !empty($paymentDetails)) {
                    $last = end($paymentDetails);
                    if (is_array($last)) {
                        $prevRate = $last['exchange_rate'] ?? null;
                        $prevCurrency = $last['currency'] ?? null;
                    }
                }
                $tour->previous_exchange_rate = is_scalar($prevRate) ? (string) $prevRate : null;
                $tour->previous_payment_currency = is_scalar($prevCurrency) ? (string) $prevCurrency : null;

                $dmcRate = null;
                $destKey = is_string($tour->destination) ? mb_strtolower(trim($tour->destination)) : '';
                $country = $destKey !== '' ? ($countriesByName[$destKey] ?? null) : null;
                if ($country && $dmc_id) {
                    $exchangeRateRaw = $country->exchange_rate;
                    if (is_string($exchangeRateRaw) && trim($exchangeRateRaw) !== '') {
                        $decoded = json_decode($exchangeRateRaw, true);
                        $exchangeRateRaw = is_array($decoded) ? $decoded : [];
                    }
                    $exchangeRateArr = is_array($exchangeRateRaw) ? $exchangeRateRaw : [];
                    $match = collect($exchangeRateArr)->firstWhere('dmc_id', $dmc_id);
                    if (is_array($match)) {
                        $dmcRate = $match['exchange_rate'] ?? null;
                    }
                }
                $tour->dmc_exchange_rate_value = is_scalar($dmcRate) ? (string) $dmcRate : null;
            }
        }

        $currency = Country::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower((string) ($user->country ?? ''))])
            ->value('currency');
        $currency = is_string($currency) && trim($currency) !== '' ? trim($currency) : (CommonHelper::getDmcCurrencyByCountry() ?: 'SGD');
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');

        $tourIds = $tours->pluck('tour_id')->toArray();
        $tourNegotiationHistory = [];
        if (!empty($tourIds)) {
            $withComments = DB::table('enquiry_comments')
                ->whereIn('tour_id', $tourIds)
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck('tour_id')
                ->flip()
                ->toArray();
            foreach ($tourIds as $tid) {
                $tourNegotiationHistory[$tid] = isset($withComments[$tid]);
            }
        }

        return view('bookings.actual', compact('tours', 'country_tax', 'currency', 'tourNegotiationHistory'));
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
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.mainguest',
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
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->formatToursDisplayId($tours);
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
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.mainguest',
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
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->formatToursDisplayId($tours);
        }

        $currency = CommonHelper::getDmcCurrencyByCountry();
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        return view('bookings.cancelled', compact('tours', 'country_tax', 'currency'));
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
            $refundTourIds = Order::withTrashed()
                ->where('bookingType', 'booking')
                ->where('is_refund', 1)
                ->pluck('tour_id')
                ->unique()
                ->toArray();

            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->withTrashed()
                          ->where('bookingType', 'booking')
                          ->where('is_refund', 1);
                }
            ])
            ->where(function ($query) use ($refundTourIds) {
                $query->whereIn('tour_status', ['Refund - Pending', 'Refunded']);
                if (!empty($refundTourIds)) {
                    $query->orWhereIn('tours.tour_id', $refundTourIds);
                }
            })
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.hotel',
                'tours.mainguest',
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
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->formatToursDisplayId($tours);
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
            $refundTourIds = Order::withTrashed()
                ->where('bookingType', 'booking')
                ->where('is_refund', 1)
                ->where('tour_id', '>', 0)
                ->pluck('tour_id')
                ->unique()
                ->toArray();

            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->withTrashed()
                          ->where('bookingType', 'booking')
                          ->where('is_refund', 1);
                }
            ])
            ->where(function ($query) use ($refundTourIds) {
                $query->whereIn('tour_status', ['Refund - Pending', 'Refunded']);
                if (!empty($refundTourIds)) {
                    $query->orWhereIn('tours.tour_id', $refundTourIds);
                }
            })
            ->where('tours.dmc_id', $dmc_id)
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.hotel',
                'tours.mainguest',
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
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->formatToursDisplayId($tours);
        }
        
        $currency = CommonHelper::getDmcCurrencyByCountry();
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        return view('bookings.refunds', compact('tours', 'country_tax', 'currency'));
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
     * Mark refund-eligible orders as refunded for a specific tour
     */
    public function processOrderRefund(Request $request)
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

            $tourId = (int) $request->tour_id;
            $roleId = (int) (Auth::user()->role_id ?? 0);
            $holdRoles = [33, 12, 37, 38];
            $financeRoles = [36, 126, 127];

            if (in_array($roleId, $holdRoles, true)) {
                $updated = Order::withTrashed()
                    ->where('tour_id', $tourId)
                    ->where('bookingType', 'booking')
                    ->where('is_refund', 1)
                    ->update([
                        'is_verify' => 2, // hold
                        'updated_at' => now(),
                    ]);

                if ($updated === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No refund-eligible services found for this tour.'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Services moved to hold for finance verification.',
                    'tour_id' => $tourId,
                    'updated_orders' => $updated,
                    'is_verify' => 2
                ]);
            }

            if (in_array($roleId, $financeRoles, true)) {
                $updated = Order::withTrashed()
                    ->where('tour_id', $tourId)
                    ->where('bookingType', 'booking')
                    ->where('is_refund', 1)
                    ->update([
                        'is_verify' => 1, // accepted
                        'refunded' => true,
                        'updated_at' => now(),
                    ]);

                if ($updated === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No refund services found for finance verification.'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Finance verification completed and services marked refunded.',
                    'tour_id' => $tourId,
                    'updated_orders' => $updated,
                    'is_verify' => 1
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to perform this action.'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating refunded status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a single refund-eligible order as refunded (by booking_id/id)
     */
    public function processOrderRefundByOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer|exists:tours,tour_id',
                'order_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input provided'
                ], 422);
            }

            $tourId = (int) $request->tour_id;
            $orderId = (int) $request->order_id;
            $roleId = (int) (Auth::user()->role_id ?? 0);
            $holdRoles = [33, 12, 37, 38];
            $financeRoles = [36, 126, 127];

            $order = Order::withTrashed()
                ->where('tour_id', $tourId)
                ->where('bookingType', 'booking')
                ->where('is_refund', 1)
                ->where(function ($q) use ($orderId) {
                    $q->where('booking_id', $orderId)
                      ->orWhere('id', $orderId);
                })
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refund order not found for this tour.'
                ], 404);
            }

            if (in_array($roleId, $holdRoles, true)) {
                $order->is_verify = 2; // hold
                $order->updated_at = now();
                $order->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Selected service moved to hold for finance verification.',
                    'tour_id' => $tourId,
                    'order_id' => $orderId,
                    'is_verify' => 2
                ]);
            }

            if (in_array($roleId, $financeRoles, true)) {
                $order->is_verify = 1; // accepted
                $order->refunded = true;
                $order->updated_at = now();
                $order->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Finance verification completed and selected service marked refunded.',
                    'tour_id' => $tourId,
                    'order_id' => $orderId,
                    'is_verify' => 1
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to perform this action.'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating refunded status: ' . $e->getMessage()
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
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.reference_id',
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
                'agents.company_name as agent_company_name',
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
        $this->formatToursDisplayId($tours);
        $currency = CommonHelper::getDmcCurrencyByCountry();
        $country_tax = Country::where('name', optional($user)->country)->value('tax_percentage');
        return view('bookings.cancellations-refunds', compact('tours', 'country_tax', 'currency'));
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

    public function confirmationVoucher(Request $request, $tourId)
    {
        try {
            $tourId = Crypt::decrypt($tourId);
        } catch (\Exception $e) {
            abort(404, 'Invalid tour ID');
        }

        $tour = Tour::where('tour_id', $tourId)->first();
        if (!$tour) {
            abort(404, 'Tour not found');
        }

        $orders = Order::where('tour_id', $tourId)
            ->where('bookingType', 'booking')
            ->whereNull('deleted_at')
            ->where('is_approve', 1)
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'No approved services found for this tour.');
        }

        $dmcUser = User::where('userId', $tour->dmc_id)->first();

        $hotels = [];
        $inclusions = [];
        $lowestDueDate = null;
        $totalRooms = 0;
        $confirmationNos = [];
        $allMealPlans = [];
        $childWithBedTotal = 0;
        $childWithoutBedTotal = 0;

        $str = function ($val, $default = '') {
            if (is_array($val) || is_object($val)) return $default;
            return (string) ($val ?? $default);
        };

        foreach ($orders as $order) {
            $data = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            if (!is_array($data)) continue;

            foreach ($data as $booking) {
                if (!is_array($booking)) continue;

                switch ($order->type) {
                    case 'hotel':
                        $hotelName = $str($booking['hotelDetails']['hotel_name'] ?? ($booking['hotel_name'] ?? null), 'Hotel');

                        $bookingDate = $booking['bookingDate'] ?? [];
                        $checkIn = '';
                        $checkOut = '';
                        if (is_array($bookingDate) && count($bookingDate) >= 2) {
                            $checkIn = $str($bookingDate[0]);
                            $checkOut = $str($bookingDate[1]);
                        } else {
                            $checkIn = $str($booking['checkIn'] ?? ($booking['check_in_date'] ?? null));
                            $checkOut = $str($booking['checkOut'] ?? ($booking['check_out_date'] ?? null));
                        }

                        $roomTypes = [];
                        $hotelRooms = 0;
                        $hotelMeals = [];
                        $roomsArr = $booking['rooms'] ?? [];
                        if (is_array($roomsArr)) {
                            foreach ($roomsArr as $room) {
                                if (!is_array($room)) continue;
                                $rt = $str($room['room_type'] ?? null);
                                $nr = (int) ($room['number_of_rooms'] ?? 1);
                                $hotelRooms += $nr;
                                if ($rt) $roomTypes[] = $rt;

                                $beds = $room['beds'] ?? [];
                                if (is_array($beds)) {
                                    foreach ($beds as $bed) {
                                        if (!is_array($bed)) continue;
                                        if (isset($bed['selectedMeals']) && is_array($bed['selectedMeals'])) {
                                            foreach ($bed['selectedMeals'] as $meal) {
                                                if (is_array($meal) && isset($meal['type']) && is_string($meal['type'])) {
                                                    $hotelMeals[] = $meal['type'];
                                                }
                                            }
                                        } elseif (isset($bed['mealTypes']) && is_array($bed['mealTypes'])) {
                                            foreach ($bed['mealTypes'] as $mt) {
                                                if (is_string($mt)) $hotelMeals[] = $mt;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (empty($roomTypes)) {
                            $rt = $str($booking['room_type'] ?? null);
                            if ($rt) $roomTypes[] = $rt;
                        }
                        if ($hotelRooms === 0) {
                            $hotelRooms = (int) ($booking['number_of_rooms'] ?? 1);
                        }

                        $cwb = $booking['child_with_bed'] ?? null;
                        $cnb = $booking['child_without_bed'] ?? null;
                        if (is_array($cwb) && isset($cwb['children'])) {
                            $n = (int) $cwb['children'];
                            if ($n > $childWithBedTotal) $childWithBedTotal = $n;
                        }
                        if (is_array($cnb) && isset($cnb['children'])) {
                            $n = (int) $cnb['children'];
                            if ($n > $childWithoutBedTotal) $childWithoutBedTotal = $n;
                        }

                        $totalRooms += $hotelRooms;
                        $allMealPlans = array_merge($allMealPlans, $hotelMeals);

                        $hotelDueDate = null;
                        if ($order->display_due_date) {
                            try {
                                $hotelDueDate = Carbon::parse($order->display_due_date)->format('d/m/Y');
                            } catch (\Exception $e) {}
                        }

                        $hotelMealFormatted = '';
                        if (!empty($hotelMeals)) {
                            $mealNames = [];
                            foreach (array_unique($hotelMeals) as $mp) {
                                $mp = preg_replace('/^room\s+with\s+/i', '', (string) $mp);
                                $mp = preg_replace('/^room\s+only\s*/i', '', $mp);
                                $mp = trim($mp);
                                if (empty($mp)) continue;
                                $parts = preg_split('/\s*\+\s*|\s+and\s+/i', $mp);
                                foreach ($parts as $p) {
                                    $p = trim($p);
                                    if (!empty($p)) $mealNames[] = ucfirst(strtolower($p));
                                }
                            }
                            $hotelMealFormatted = implode(', ', array_unique($mealNames));
                        }

                        $hotels[] = [
                            'name' => $hotelName,
                            'room_type' => implode(', ', array_filter($roomTypes)),
                            'check_in' => $checkIn,
                            'check_out' => $checkOut,
                            'meal_plan' => $hotelMealFormatted,
                            'rooms' => $hotelRooms,
                            'due_date' => $hotelDueDate,
                            'confirmation_no' => $order->reference_id ? $str($order->reference_id) : '',
                        ];

                        if ($order->reference_id) {
                            $cn = $str($order->reference_id);
                            if ($cn) $confirmationNos[] = $cn;
                        }

                        if ($order->display_due_date) {
                            try {
                                $dueDate = Carbon::parse($order->display_due_date);
                                if (!$lowestDueDate || $dueDate->lt($lowestDueDate)) {
                                    $lowestDueDate = $dueDate;
                                }
                            } catch (\Exception $e) {}
                        }
                        break;

                    case 'attraction':
                        $name = $str($booking['AttractionName'] ?? ($booking['attraction_name'] ?? null), 'Attraction');
                        $ticketName = $str($booking['ticketName'] ?? ($booking['ticket_name'] ?? null));
                        $inclusions[] = $ticketName ? $name . ' (' . $ticketName . ')' : $name;

                        $tf = $booking['transfer_options'] ?? null;
                        if (is_array($tf) && !empty($tf['transfer_required'])) {
                            $tvName = $str($tf['vehicle_name'] ?? ($tf['vehicle_details']['vehicle_name'] ?? null));
                            $tvType = $str($tf['type'] ?? null, 'Private');
                            $tvWay = $str($tf['way'] ?? null, 'One Way');
                            if ($tvName) {
                                $inclusions[] = $name . ' Transfer (' . $tvName . ' - ' . $tvType . ' - ' . $tvWay . ')';
                            }
                        }
                        break;

                    case 'restaurant':
                        $name = $str($booking['restaurantName'] ?? ($booking['restaurant_name'] ?? null), 'Restaurant');
                        $mealType = $str($booking['mealType'] ?? ($booking['meal_type'] ?? null));
                        $inclusions[] = $mealType ? $name . ' (' . $mealType . ')' : $name;

                        $tf = $booking['transfer_options'] ?? null;
                        if (is_array($tf) && !empty($tf['transfer_required'])) {
                            $tvName = $str($tf['vehicle_name'] ?? ($tf['vehicle_details']['vehicle_name'] ?? null));
                            $tvType = $str($tf['type'] ?? null, 'Private');
                            $tvWay = $str($tf['way'] ?? null, 'One Way');
                            if ($tvName) {
                                $inclusions[] = $name . ' Transfer (' . $tvName . ' - ' . $tvType . ' - ' . $tvWay . ')';
                            }
                        }
                        break;

                    case 'guide':
                        $name = $str($booking['guide_name'] ?? null, 'Guide');
                        $hours = $str($booking['hours'] ?? ($booking['service_hours'] ?? null));
                        $inclusions[] = $hours ? $name . ' - ' . $hours . 'H' : $name;
                        break;

                    case 'entry_port':
                        $vehicle = $str($booking['vehicles_name'] ?? ($booking['vehicle_name'] ?? null), 'Transfer');
                        $bType = $str($booking['type'] ?? null, 'Private');
                        $pickup = $str($booking['entrypickup'] ?? null);
                        $dropoff = $str($booking['entrydropoff'] ?? null);
                        $label = 'Arrival Transfer (' . $vehicle . ' - ' . $bType . ' - One Way)';
                        if ($pickup && $dropoff) $label .= "\n" . $pickup . ' to ' . $dropoff;
                        $inclusions[] = $label;
                        break;

                    case 'exit_port':
                        $vehicle = $str($booking['vehicles_name'] ?? ($booking['vehicle_name'] ?? null), 'Transfer');
                        $bType = $str($booking['type'] ?? null, 'Private');
                        $pickup = $str($booking['exitpickup'] ?? null);
                        $dropoff = $str($booking['exitdropoff'] ?? null);
                        $label = 'Departure Transfer (' . $vehicle . ' - ' . $bType . ' - One Way)';
                        if ($pickup && $dropoff) $label .= "\n" . $pickup . ' to ' . $dropoff;
                        $inclusions[] = $label;
                        break;

                    case 'local_transport':
                    case 'travel_hourly':
                    case 'travel_point':
                        $vehicle = $str($booking['vehicles_name'] ?? ($booking['vehicle_name'] ?? null), 'Transport');
                        $bType = $str($booking['type'] ?? null);
                        $pickup = $str($booking['entrypickup'] ?? null);
                        $dropoff = $str($booking['entrydropoff'] ?? ($booking['dropoffLocation'] ?? null));
                        $label = $bType ? $vehicle . ' - ' . $bType : $vehicle;
                        if ($pickup && $dropoff) $label .= "\n" . $pickup . ' to ' . $dropoff;
                        $inclusions[] = $label;
                        break;
                }
            }
        }

        $paxName = '';
        if ($tour->mainguest) {
            $guest = is_string($tour->mainguest) ? json_decode($tour->mainguest, true) : $tour->mainguest;
            if (is_array($guest)) {
                $paxName = $str($guest['salutation'] ?? null) . ' ' . $str($guest['first_name'] ?? null) . ' ' . $str($guest['last_name'] ?? null);
                $paxName = trim($paxName);
            }
        }
        if (empty($paxName)) {
            $paxName = $str($tour->customer_name ?? null, 'Guest');
        }

        $travelDates = '';
        if ($tour->check_in_time && $tour->check_out_time) {
            $travelDates = Carbon::parse($tour->check_in_time)->format('d/m/Y') . ' - ' . Carbon::parse($tour->check_out_time)->format('d/m/Y');
        }

        $adultCount = (int) ($tour->adult ?? 0);
        $childCount = (int) ($tour->child ?? 0);
        $infantCount = (int) ($tour->infant ?? 0);
        $noOfPax = sprintf('%02d', $adultCount) . ' Adults';
        if ($childWithBedTotal > 0 || $childWithoutBedTotal > 0) {
            if ($childWithBedTotal > 0) $noOfPax .= '+' . sprintf('%02d', $childWithBedTotal) . ' cwb';
            if ($childWithoutBedTotal > 0) $noOfPax .= '+' . sprintf('%02d', $childWithoutBedTotal) . ' cnb';
        } elseif ($childCount > 0) {
            $noOfPax .= ', ' . $childCount . ' Children';
        }
        if ($infantCount > 0) $noOfPax .= ', ' . $infantCount . ' Infants';

        $refId = $tour->reference_id ?? $tour->display_id ?? '';
        $referenceId = is_array($refId) ? (string) ($refId[0] ?? '') : (string) $refId;

        $mealPlanSummary = '';
        if (!empty($allMealPlans)) {
            $mealNames = [];
            foreach (array_unique($allMealPlans) as $mp) {
                $mp = preg_replace('/^room\s+with\s+/i', '', (string) $mp);
                $mp = preg_replace('/^room\s+only\s*/i', '', $mp);
                $mp = trim($mp);
                if (empty($mp)) continue;
                $parts = preg_split('/\s*\+\s*|\s+and\s+/i', $mp);
                foreach ($parts as $p) {
                    $p = trim($p);
                    if (!empty($p)) {
                        $mealNames[] = ucfirst(strtolower($p));
                    }
                }
            }
            $mealPlanSummary = implode(', ', array_unique($mealNames));
        }
        $confirmationNo = !empty($confirmationNos) ? implode(', ', $confirmationNos) : 'na';

        $voucherData = [
            'tour' => $tour,
            'dmcUser' => $dmcUser,
            'hotels' => $hotels,
            'inclusions' => $inclusions,
            'lowestDueDate' => $lowestDueDate,
            'paxName' => (string) $paxName,
            'travelDates' => (string) $travelDates,
            'noOfPax' => (string) $noOfPax,
            'referenceId' => $referenceId,
            'totalRooms' => $totalRooms > 0 ? (string) $totalRooms : 'na',
            'confirmationNo' => (string) $confirmationNo,
            'mealPlanSummary' => (string) $mealPlanSummary,
        ];

        $dompdf = new Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $html = view('bookings.voucher-pdf', $voucherData)->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Confirmation_Voucher_' . ($tour->display_id ?? $tourId) . '.pdf';
        return $dompdf->stream($filename, ['Attachment' => true]);
    }
}
