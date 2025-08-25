<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\EnquiryForm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnquiryListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // if (!hasPermission('view enquiry')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }

        $user = Auth::user();
        $agent_ids = collect(); // default empty collection

        if($user->role_id == 11 || $user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 37 || $user->role_id == 38 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 131 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            if($user->role_id == 11 || $user->role_id == 20){
                $dmc_id = $user->userId;
            }
            //sales head
            elseif($user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
                $dmc_id = $user->created_by;
            }
            //operational head
            elseif($user->role_id == 34 || $user->role_id == 128 || $user->role_id == 131 || $user->role_id == 132 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 137 || $user->role_id == 138){
                $dmc_id = $user->created_by;
            }
            //finance head
            elseif($user->role_id == 36 || $user->role_id == 129 || $user->role_id == 131 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
                $dmc_id = $user->created_by;
            }
            //sales manager
            elseif($user->role_id == 37){
                $sales_manager_id = $user->userId;
                $sales_head_id = $user->created_by;
                $sales_head = User::where('userId', $sales_head_id)->first();
                $dmc_id = $sales_head->created_by;
            }
            //assistant sales manager
            elseif($user->role_id == 38){
                $assistant_sales_manager_id = $user->userId;
                $sales_manager_id = $user->created_by;
                $sales_manager = User::where('userId', $sales_manager_id)->first();
                $sales_head_id = $sales_manager->created_by;
                $sales_head = User::where('userId', $sales_head_id)->first();
                $dmc_id = $sales_head->created_by;
            }

            // Include the package and agent relationships to access package and agent details
            $enquiries = EnquiryForm::with(['agent' => function($query) use ($dmc_id) {
                $query->where(function($q) use ($dmc_id) {
                    $q->whereRaw("CASE 
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
                    ]);
                });
            }])
            ->where('dmc_id', $dmc_id)
            ->where('unique_tour_id', null)
            ->orderBy('created_at', 'desc')
            ->get();
        }
        elseif($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
            $enquiries = EnquiryForm::with(['agent'])
            ->where('unique_tour_id', null)
            ->orderBy('created_at', 'desc')
            ->get();
        }
        else{
            return "you are not authorized to view this page";
        }

        // For debugging
        Log::info('Enquiry List Query', [
            'role_id' => $user->role_id,
            'user_id' => $user->userId,
            'agent_count' => $agent_ids->count(),
            'enquiry_count' => $enquiries->count(),
            'agent_ids' => $agent_ids->toArray()
        ]);

        return view('enquiry-list.index', compact('enquiries'));
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
}
