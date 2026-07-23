<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Guide;
use App\Models\Order;
use App\Models\Tour;
use App\Models\Driver;
use App\Models\User;
use App\Models\Enquiry;
use App\Helpers\CommonHelper;

class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $tours = [];
        $hotels = []; 
        $attractions = [];
        $currentUser = auth()->user();
        $user = $currentUser;
        $enquiries = [];

        if ($user) {
            switch ($user->role_id) {
                case 11: // Agent is a DMC
                    $dmc_id = $user->userId; // Assuming `userId` in agent or fallback to agent_id
                    $dmc_users = User::where('userId', $dmc_id)->first();
                    $enquiries = Enquiry::where('dmcId', $dmc_id)->orderBy('created_at', 'desc')->get();
                    break;
                case 33: // Sales Head
                case 128: // Sales Head
                case 129: // Sales Head
                case 130: // Sales Head
                case 134: // Sales Head
                case 135: // Sales Head
                case 136: // Sales Head
                case 138: // Sales Head
                    $salesManagerId = $user->userId;
                        $saleshead_dmc = User::where('userId',$user->userId)->first(); // SH
                        if ( $saleshead_dmc) {
                            $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                                $enquiries = Enquiry::where('dmcId', $dmc_id)->orderBy('created_at', 'desc')->get();
                            }
                        }
                        
                    break;
                case 12:
                case 37: // Sales Manager
                    $salesManagerId = $user->userId;
                    $salesmng_dmc= User::where('userId', $user->userId)->first(); // SM
                    
                    if ($salesmng_dmc) {
                        $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first(); // SH
                        if ( $saleshead_dmc) {
                            $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                                $enquiries = Enquiry::where('dmcId', $dmc_id)->orderBy('created_at', 'desc')->get();
                            }
                        }
                    }
                    
                    break;
                case 38: // Assistant Manager
                    $salesManagerId = $user->userId;
                    $asmng_dmc = User::where('userId', $user->userId)->first(); // SM
                    if($asmng_dmc){
                        $salesmng_dmc = User::where('userId', $asmng_dmc->created_by)->first(); // SH
                    }
                    if ($salesmng_dmc) {
                        $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first(); // SH
                        if ( $saleshead_dmc) {
                            $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                                $enquiries = Enquiry::where('dmcId', $dmc_id)->orderBy('created_at', 'desc')->get();
                            }
                        }
                    }
                    break;
                default:
                    $enquiries = Enquiry::with(['display'])->where('status', 1)->orderBy('created_at', 'desc')->get();
                    break;
            }
        }
        // else{
        //     $enquiries = Enquiry::with(['display'])->where('status', 1)->orderBy('created_at', 'desc')->get();
        // }
        return view('enquiry.enquiry',compact('enquiries','currentUser', ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'enquiry_id' => 'nullable|integer',
            'tour_id' => 'nullable|integer|exists:tours,tour_id',
            'price' => 'nullable|numeric|min:0',
            'comment' => 'required|string|max:1000',
            'actual_amount' => 'nullable|numeric|min:0',
            'offers' => 'nullable|array|min:1',
            'offers.*.country' => 'required_with:offers|string|max:255',
            'offers.*.currency' => 'required_with:offers|string|max:10',
            'offers.*.amount' => 'required_with:offers|numeric|min:0.01',
            'offers.*.actual_amount' => 'required_with:offers|numeric|min:0',
            'offers.*.gross' => 'nullable|numeric|min:0',
        ]);

        if (! $request->filled('enquiry_id') && ! $request->filled('tour_id')) {
            return back()->with('error', 'Enquiry or tour reference is required.');
        }

        $offers = [];
        if ($request->filled('offers') && is_array($request->input('offers'))) {
            $offers = array_values(array_map(function ($offer) {
                return [
                    'country' => trim((string) ($offer['country'] ?? '')),
                    'currency' => strtoupper(trim((string) ($offer['currency'] ?? ''))),
                    'amount' => round((float) ($offer['amount'] ?? 0), 2),
                    'actual_amount' => round((float) ($offer['actual_amount'] ?? 0), 2),
                    'gross' => round((float) ($offer['gross'] ?? 0), 2),
                ];
            }, $request->input('offers')));

            foreach ($offers as $offer) {
                if ($offer['actual_amount'] > 0 && $offer['amount'] > $offer['actual_amount']) {
                    return back()
                        ->withErrors([
                            'price' => 'Counter price for ' . ($offer['country'] ?: $offer['currency'])
                                . ' cannot exceed the payable amount.',
                        ])
                        ->withInput();
                }
            }
        }

        $primaryOffer = $offers[0] ?? null;
        $price = $primaryOffer
            ? (float) $primaryOffer['amount']
            : (float) $request->input('price', 0);

        if ($price <= 0) {
            return back()->withErrors(['price' => 'Please enter a counter price for each country.'])->withInput();
        }

        $currentUser = auth()->user();
        $currentEnquiry = null;
        $tour = null;

        if ($request->filled('enquiry_id')) {
            $currentEnquiry = Enquiry::where('enquiry_id', $request->enquiry_id)->first();
            if (! $currentEnquiry) {
                return back()->with('error', 'Enquiry Not found!');
            }
        }

        if (! $currentEnquiry && $request->filled('tour_id')) {
            $tour = Tour::where('tour_id', $request->tour_id)->first();
            if (! $tour) {
                return back()->with('error', 'Tour Not found!');
            }
            $currentEnquiry = Enquiry::where('tour_id', $tour->tour_id)
                ->orderByDesc('created_at')
                ->first();
        }

        if (! $currentEnquiry && $tour) {
            $actualForRow = $primaryOffer
                ? (float) $primaryOffer['actual_amount']
                : (float) ($request->input('actual_amount', 0));

            $currentEnquiry = Enquiry::create([
                'tour_id' => $tour->tour_id,
                'status' => 1,
                'dmcId' => $tour->dmc_id,
                'sender_id' => $currentUser->userId,
                'sender_type' => 'OM',
                'receiver_id' => 0,
                'receiver_type' => '',
                'current_position' => '',
                'amount' => 0,
                'actual_amount' => $actualForRow,
                'gross_amount' => \App\Helpers\CommonHelper::calculateTourGrossAmount($tour),
                'comment' => '',
                'negotiation_details' => $offers ?: null,
            ]);
            $currentEnquiry->refresh();
        }

        if (! $currentEnquiry) {
            return back()->with('error', 'Enquiry Not found!');
        }

        $tour = $tour ?? Tour::where('tour_id', $currentEnquiry->tour_id)->first();
        if (! $tour) {
            return back()->with('error', 'Tour Not found!');
        }

        // Read before updating: amount = incoming (what came to me), actualAmount = outgoing (what I am sending)
        $amount = $currentEnquiry->amount ?? 0;
        $comment = $request->comment ?? '';
        $actualAmount = $price;

        $currentEnquiry->sender_id = $currentUser->userId;
        $currentEnquiry->sender_type = 'OM';
        $currentEnquiry->receiver_id = 0;
        $currentEnquiry->receiver_type = '';
        $currentEnquiry->current_position = '';
        $currentEnquiry->actual_amount = $currentEnquiry->actual_amount ?? 0;
        // Reset the gross baseline to the current gross for this negotiation round so that
        // services added afterwards are added on top of this newly agreed amount.
        if ($primaryOffer && (float) ($primaryOffer['gross'] ?? 0) > 0) {
            $currentEnquiry->gross_amount = (float) $primaryOffer['gross'];
        } else {
            $currentEnquiry->gross_amount = \App\Helpers\CommonHelper::calculateTourGrossAmount($tour);
        }
        $currentEnquiry->amount = $price;
        $currentEnquiry->comment = $request->comment;
        if (!empty($offers)) {
            $currentEnquiry->negotiation_details = $offers;
        }

        $tourStatus = Tour::where('tour_id', $currentEnquiry->tour_id)->value('tour_status');
        $oldStatus = $tourStatus;
        $newStatus = $tourStatus;

        $changedByName = $currentUser ? ($currentUser->name ?? '') : null;
        $changedByUserId = $currentUser ? ($currentUser->userId ?? $currentUser->id ?? null) : null;

        if ($tourStatus == "New Enquiry") {
            \App\Helpers\CommonHelper::appendTourStatusTrackById(
                (int) $currentEnquiry->tour_id,
                $tourStatus,
                "Prospect",
                null,
                $amount,
                $comment,
                $actualAmount,
                $changedByName,
                $changedByUserId
            );

            Tour::where('tour_id', $currentEnquiry->tour_id)->update([
                'tour_status' => "Prospect",
            ]);
            $newStatus = "Prospect";
            $tour = Tour::where('tour_id', $currentEnquiry->tour_id)->first();
        } elseif ($tourStatus == "Prospect") {
            \App\Helpers\CommonHelper::appendTourStatusTrackById(
                (int) $currentEnquiry->tour_id,
                $tourStatus,
                "Tentative",
                null,
                $amount,
                $comment,
                $actualAmount,
                $changedByName,
                $changedByUserId
            );

            Tour::where('tour_id', $currentEnquiry->tour_id)->update([
                'tour_status' => "Tentative",
            ]);
            $newStatus = "Tentative";
            $tour = Tour::where('tour_id', $currentEnquiry->tour_id)->first();
        } else {
            // Tour status not changing (Tentative, Confirmed, etc.) - still append track for enquiry update
            \App\Helpers\CommonHelper::appendTourStatusTrackById(
                (int) $currentEnquiry->tour_id,
                $tourStatus,
                $tourStatus,
                null,
                $amount,    
                $comment,
                $actualAmount,
                $changedByName,
                $changedByUserId
            );
            $tour = Tour::where('tour_id', $currentEnquiry->tour_id)->first();
        }

        if ($currentEnquiry->save()) {
            // Send negotiation email to agent
            if ($tour && $tour->agent_id) {
                try {
                    // Get the actual amount (original price) and negotiated amount
                    $actualAmount = $currentEnquiry->actual_amount ?? 0;
                    $negotiatedAmount = $price;
                    
                    $negotiationData = [
                        'tour' => $tour,
                        'dmc_id' => $tour->dmc_id,
                        'actual_amount' => $actualAmount,
                        'negotiated_amount' => $negotiatedAmount,
                        'previous_negotiated_amount' => null, // DMC doesn't have previous offers in this flow
                        'comment' => $request->comment,
                        'currency' => '$', // You can customize this
                        'offers' => $offers,
                    ];
                    
                    $emailResult = CommonHelper::sendNegotiationEmail(
                        $tour->agent_id,
                        $tour->tour_id,
                        $tour->display_id ?? 'TOUR-' . $tour->tour_id,
                        $negotiationData
                    );
                    
                    if ($emailResult !== true) {
                        Log::warning("Negotiation email could not be sent to agent", [
                            'tour_id' => $tour->tour_id,
                            'agent_id' => $tour->agent_id,
                            'error' => $emailResult
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Error sending negotiation email to agent", [
                        'tour_id' => $tour->tour_id,
                        'agent_id' => $tour->agent_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Build success message with status update info
            $successMessage = 'Price updated successfully! The negotiation has been sent to the agent via email.';
            if ($oldStatus !== $newStatus) {
                $successMessage .= ' Tour status has been updated from "' . $oldStatus . '" to "' . $newStatus . '".';
            }
            
            return back()->with('success', $successMessage);
        } else {
            return back()->with('error', 'Error while updating the price!');
        }
    }

    public function assignManager(Request $request)
    {
        $currentEnquiry = Enquiry::where('enquiry_id',$request->enquiry_id)->first();
        $receiver_id = $request->aom_id;
        $currentUser = auth()->user();
        // $lastEnquiry = Enquiry::orderBy('created_at', 'desc')->first();
        // $enq_max_id = $lastEnquiry->enquiry_id ?? 1;
        // $enqId = CommonHelper::createId($enq_max_id);
        // while (Enquiry::where('enquiry_id', $enqId)->exists()) {
        //     $enqId = CommonHelper::createId($enqId);
        // }

        $currentEnquiry->sender_id = $currentUser->userId;
        $currentEnquiry->sender_type = 'OM';
        $currentEnquiry->receiver_id = $receiver_id;
        $currentEnquiry->assigned_to = $receiver_id;
        $currentEnquiry->receiver_type = 'AOM';
        $currentEnquiry->current_position = 'AOM';
        $currentEnquiry->status = 1;
        $isSaved = $currentEnquiry->save();
        $currentEnquiry->refresh();
        if ($isSaved) {
            return response()->json([
                'status' => 'success', 
                'message' => 'Asst. Manager assigned successfully!'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Tour not found!',
            ]);
        }
    }

    public function removeManager(Request $request)
    {
        $enquiry = Enquiry::where('enquiry_id',$request->enquiry_id)->first();

        if (!$enquiry) {
            return response()->json(['status' => 'error', 'message' => 'enquiry not found']);
        }

        $enquiry->receiver_id = 0; // Remove the assigned manager
        $enquiry->save();

        return response()->json(['status' => 'success', 'message' => 'Asst. Manager removed successfully']);
    }
}
