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

        switch ($user->role_id) {
            case 1: // Admin
            case 2: // Super Admin
            case 10: // Master DMC
                // These roles can see all enquiries from active agents
                $agent_ids = Agent::where('status', 1)->pluck('agent_id');
                break;

            case 11: // DMC
                // Step 1: Get all Sales Heads under this DMC
                $sales_heads = User::where('created_by', $user->userId)
                                 ->where('role_id', 33)
                                 ->pluck('userId');
                
                // Step 2: Get all Sales Managers under those Sales Heads
                $sales_managers = User::whereIn('created_by', $sales_heads)
                                    ->whereIn('role_id', [12, 37])
                                    ->pluck('userId');
                
                // Step 3: Get all Assistant Managers under those Sales Managers
                $assistant_managers = User::whereIn('created_by', $sales_managers)
                                        ->where('role_id', 38)
                                        ->pluck('userId');
                
                // Step 4: Collect all user IDs in the hierarchy
                $all_user_ids = collect([$user->userId])
                              ->merge($sales_heads)
                              ->merge($sales_managers)
                              ->merge($assistant_managers)
                              ->unique()
                              ->filter();
                
                // Step 5: Get agents created by anyone in the hierarchy OR associated with this DMC
                $agent_ids = Agent::where('status', 1)
                                 ->where(function($query) use ($user, $all_user_ids) {
                                     $query->whereIn('sales_manager_dmc', $all_user_ids)
                                           ->orWhere(function($subQuery) use ($user) {
                                               $subQuery->whereRaw("CASE 
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
                                                   json_encode([$user->userId]),
                                                   json_encode([$user->userId]),
                                                   "%{$user->userId}%"
                                               ]);
                                           });
                                 })->pluck('agent_id');
                break;

            case 33: // Sales Head
            case 128: // Sales Head
            case 129: // Sales Head
            case 130: // Sales Head
            case 134: // Sales Head
            case 135: // Sales Head
            case 136: // Sales Head
            case 138: // Sales Head
                // Step 1: Get all Sales Managers under this Sales Head
                $sales_managers = User::where('created_by', $user->userId)
                                    ->whereIn('role_id', [12, 37])
                                    ->pluck('userId');
                
                // Step 2: Get all Assistant Managers under those Sales Managers
                $assistant_managers = User::whereIn('created_by', $sales_managers)
                                        ->where('role_id', 38)
                                        ->pluck('userId');
                
                // Step 3: Collect all user IDs in the hierarchy
                $all_user_ids = collect([$user->userId])
                              ->merge($sales_managers)
                              ->merge($assistant_managers)
                              ->unique()
                              ->filter();
                
                // Step 4: Get parent DMC
                $dmc_id = User::where('userId', $user->created_by)
                             ->where('role_id', 11)
                             ->value('userId');
                
                // Step 5: Get agents created by anyone in the hierarchy under this DMC
                if ($dmc_id) {
                    $agent_ids = Agent::where('status', 1)
                                     ->whereIn('sales_manager_dmc', $all_user_ids)
                                     ->where(function($query) use ($dmc_id) {
                                         $query->whereRaw("CASE 
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
                                     })->pluck('agent_id');
                }
                break;

            case 12: // Sales Manager
            case 37: // Sales Manager
                // Step 1: Get Assistant Managers under this Sales Manager
                $assistant_managers = User::where('created_by', $user->userId)
                                        ->where('role_id', 38)
                                        ->pluck('userId');
                
                // Step 2: Collect all user IDs (Sales Manager + Assistant Managers)
                $all_user_ids = collect([$user->userId])
                              ->merge($assistant_managers)
                              ->unique()
                              ->filter();
                
                // Step 3: Get the Sales Head who created this Sales Manager (to find DMC)
                $sales_head = User::where('userId', $user->created_by)
                                    ->where('role_id', 33)
                                    ->first();                
                // Step 4: Get the DMC who created the Sales Head
                $dmc_id = null;
                if ($sales_head) {
                    $dmc_id = User::where('userId', $sales_head->created_by)
                                    ->where('role_id', 11)
                                    ->value('userId');
                }
                
                // Step 5: Fetch Agents created by Sales Manager or Assistant Managers under DMC
                if ($dmc_id) {
                    $agent_ids = Agent::where('status', 1)
                                 ->whereIn('sales_manager_dmc', $all_user_ids)
                                 ->where(function($query) use ($dmc_id) {
                                     $query->whereRaw("CASE 
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
                                 })->pluck('agent_id');
                }
                break;

            case 38: // Assistant Sales Manager
                // Assistant Sales Manager can only see enquiries from agents that he created directly
                // Step 1: Get Sales Manager (who created this Assistant Sales Manager)
                $sales_mg = User::where('userId', $user->created_by)
                                ->whereIn('role_id', [12, 37]) // Allow both sales manager roles
                                ->first();
                // Step 2: Get Sales Head (who created the Sales Manager)
                $sales_head = null;
                if ($sales_mg) {
                    $sales_head = User::where('userId', $sales_mg->created_by)
                                      ->where('role_id', 33)
                                      ->first();
                }
                // Step 3: Get DMC (who created the Sales Head)
                $dmc_id = null;
                if ($sales_head) {
                    $dmc_id = User::where('userId', $sales_head->created_by)
                                  ->where('role_id', 11)
                                  ->value('userId');
                }
            
                // Step 4: Fetch only Agents created by this Assistant Sales Manager under DMC
                if ($dmc_id) {
                    $agent_ids = Agent::where('status', 1)
                                     ->where('sales_manager_dmc', $user->userId) // Only agents created by this Assistant Manager
                                     ->where(function($query) use ($dmc_id) {
                                         $query->whereRaw("CASE 
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
                                     })->pluck('agent_id');
                }
                break;

            default:
                // For all other roles, get the parent DMC's agents
                $parentUser = User::where('userId', $user->created_by)->first();
                while ($parentUser && !in_array($parentUser->role_id, [11])) {
                    $parentUser = User::where('userId', $parentUser->created_by)->first();
                }

                if ($parentUser && $parentUser->role_id == 11) {
                    $dmc_id = $parentUser->userId;
                    $agent_ids = Agent::where('status', 1)->where(function($query) use ($dmc_id) {
                        $query->whereRaw("CASE 
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
                    })->pluck('agent_id');
                }
                break;
        }

        // Fetch enquiries based on agent_ids
        if ($agent_ids->isEmpty()) {
            $enquiries = collect(); // Empty collection if no agents found
        } else {
            $enquiries = EnquiryForm::whereIn('agent_id', $agent_ids)
                ->whereNull('unique_tour_id')
                ->with(['agent' => function($query) {
                    $query->select('agent_id', 'name', 'country');
                }])
                ->orderBy('enquiry_id', 'desc')
                ->get();
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
