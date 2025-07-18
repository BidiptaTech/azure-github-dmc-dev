<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
                    $enquiries = Enquiry::where('dmcId', $dmc_id)->get();
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
                                $enquiries = Enquiry::where('dmcId', $dmc_id)->get();
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
                                $enquiries = Enquiry::where('dmcId', $dmc_id)->get();
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
                                $enquiries = Enquiry::where('dmcId', $dmc_id)->get();
                            }
                        }
                    }
                    break;
            }
        }
        $enquiries = Enquiry::with(['display'])->where('status', 1)->orderBy('created_at', 'desc')->get();
        // dd($enquiries);
        return view('enquiry.enquiry',compact('enquiries','currentUser', ));
    }

    public function update(Request $request)
    {
        $currentEnquiry = Enquiry::where('enquiry_id',$request->enquiry_id)->first();
        if(!$currentEnquiry){
            return back()->with('error', 'Enquiry Not found!');
        }
        $tour = $currentEnquiry ? Tour::where('tour_id', $currentEnquiry->tour_id)->first() : '';
        if(!$tour){
            return back()->with('error', 'Tour Not found!');
        }
        $currentUser = auth()->user();
        $lastEnquiry = Enquiry::orderBy('created_at', 'desc')->first();
        $enq_max_id = $lastEnquiry->enquiry_id ?? 1;
        $enqId = CommonHelper::createId($enq_max_id);
        while (Enquiry::where('enquiry_id', $enqId)->exists()) {
            $enqId = CommonHelper::createId($enqId);
        }
        $request->validate([
            'enquiry_id' => 'required|integer',
            'price' => 'required|numeric|min:0',
            'comment' => 'required|string|max:1000',
        ]);

        // if($currentUser->role_id == 125){
        //     $currentEnquiry->sender_id = $currentUser->userId;
        //     $currentEnquiry->sender_type = 'AOM';
        //     $currentEnquiry->receiver_id = $tour->agent_id;
        //     $currentEnquiry->receiver_type = 'Agent';
        //     $currentEnquiry->current_position = 'Agent';
        //     $currentEnquiry->actual_amount = $currentEnquiry->actual_amount ?? 0;
        //     $currentEnquiry->amount = $request->price;
        //     $currentEnquiry->comment = $request->comment;
        // }
        // else{
            $currentEnquiry->sender_id = $currentUser->userId;
            $currentEnquiry->sender_type = 'OM';
            $currentEnquiry->receiver_id = 0;
            $currentEnquiry->receiver_type = '';
            $currentEnquiry->current_position = '';
            $currentEnquiry->actual_amount = $currentEnquiry->actual_amount ?? 0;
            $currentEnquiry->amount = $request->price;
            $currentEnquiry->comment = $request->comment;
        // }
        $tourStatus = Tour::where('tour_id', $currentEnquiry->tour_id)->value('tour_status');
        if($tourStatus == "New Enquiry"){
            $tour = Tour::where('tour_id', $currentEnquiry->tour_id)->update([
                'tour_status' => "Prospect",
            ]);
        }elseif($tourStatus == "Prospect"){
            $tour = Tour::where('tour_id', $currentEnquiry->tour_id)->update([
                'tour_status' => "Tentative",
            ]);
        }
        if ($currentEnquiry->save()) {
            return back()->with('success', 'Price updated successfully!');
        } else {
            return back()->with('error', 'Error while updating the price!');
        }
    }

    public function assignManager(Request $request)
    {
        $currentEnquiry = Enquiry::where('enquiry_id',$request->enquiry_id)->first();
        $receiver_id = $request->aom_id;
        $currentUser = auth()->user();
        $lastEnquiry = Enquiry::orderBy('created_at', 'desc')->first();
        $enq_max_id = $lastEnquiry->enquiry_id ?? 1;
        $enqId = CommonHelper::createId($enq_max_id);
        while (Enquiry::where('enquiry_id', $enqId)->exists()) {
            $enqId = CommonHelper::createId($enqId);
        }

        $currentEnquiry->sender_id = $currentUser->userId;
        $currentEnquiry->sender_type = 'OM';
        $currentEnquiry->receiver_id = $receiver_id;
        $currentEnquiry->assigned_to = $receiver_id;
        $currentEnquiry->receiver_type = 'AOM';
        $currentEnquiry->current_position = 'AOM';
        $currentEnquiry->status = 1;
        if ($currentEnquiry->save()) {
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
