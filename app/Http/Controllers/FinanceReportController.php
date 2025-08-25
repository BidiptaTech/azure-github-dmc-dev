<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FinanceReportController extends Controller
{
    public function salesRevenue(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $groupBy = $request->input('group_by', 'monthly');

        // Get agent IDs based on user role (using pluck instead of get)
        $agentIds = $this->getAccessibleAgentIds($user);

        // Call the stored function with agent filtering
        $rawResults = DB::select(
            "SELECT * FROM get_sales_revenue_reports(?, ?, ?)",
            [$startDate, $endDate, $groupBy]
        );

        // Filter results by agent names if not admin (since function returns agent_name, not agent_id)
        if ($user->role_id !== 1) {
            if (!empty($agentIds)) {
                // User has accessible agents - filter by them
                $agentNames = Agent::whereIn('agent_id', $agentIds)->pluck('name')->toArray();
                
                $rawResults = collect($rawResults)->filter(function ($row) use ($agentNames) {
                    return in_array($row->agent_name, $agentNames);
                });
            } else {
                // User has no accessible agents - show no results
                $rawResults = collect(); // Empty collection
            }
        }

        // Group results by period and agent
        $groupedResults = collect($rawResults)->groupBy(function ($item) {
            return $item->period . '|' . $item->agent_name;
        })->map(function ($group) {
            $firstItem = $group->first();
            $totalRevenue = $group->sum('total_revenue');
            $services = $group->map(function ($item) {
                return [
                    'service_type' => $item->service_type,
                    'revenue' => $item->total_revenue
                ];
            })->toArray();
            
            return [
                'period' => $firstItem->period,
                'agent_name' => $firstItem->agent_name,
                'total_revenue' => $totalRevenue,
                'services' => $services,
                'service_count' => count($services)
            ];
        })->values();

        return view('reports.sales-revenue', compact('groupedResults', 'startDate', 'endDate', 'groupBy'));
    }

    public function ledger(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $agentId = $request->input('agent_id', null);
        $serviceType = $request->input('service_type', null);
        $masterDmcId = $request->input('master_dmc_id', null);
        $dmcId = $request->input('dmc_id', null);

        // Auto-select user's own Master DMC or DMC if not already selected
        // Only applies to Admin users now since others don't have these dropdowns
        // Master DMC and DMC users will work with their hierarchy automatically

        // Get accessible agent IDs
        $agentIds = $this->getAccessibleAgentIds($user);

        // If Master DMC or DMC is selected, filter agents accordingly
        if ($masterDmcId || $dmcId) {
            $filteredAgentIds = [];
            
            if ($dmcId) {
                // Get agents under specific DMC and its entire hierarchy
                $dmcHierarchyIds = $this->getDmcHierarchyIds($dmcId);
                $filteredAgentIds = Agent::whereIn('sales_manager_dmc', $dmcHierarchyIds)
                    ->pluck('agent_id')
                    ->toArray();
            } elseif ($masterDmcId) {
                // Get all DMCs under Master DMC, then get their agents
                $dmcIds = User::where('role_id', 11)
                    ->where('master_dmc_id', $masterDmcId)
                    ->pluck('userId')
                    ->toArray();
                
                if (!empty($dmcIds)) {
                    $allHierarchyIds = [];
                    foreach ($dmcIds as $dmcId) {
                        $allHierarchyIds = array_merge($allHierarchyIds, $this->getDmcHierarchyIds($dmcId));
                    }
                    $filteredAgentIds = Agent::whereIn('sales_manager_dmc', array_unique($allHierarchyIds))
                        ->pluck('agent_id')
                        ->toArray();
                }
            }
            
            // Intersect with accessible agent IDs to maintain security
            if ($user->role_id !== 1) {
                $agentIds = array_intersect($agentIds, $filteredAgentIds);
            } else {
                $agentIds = $filteredAgentIds;
            }
        }

                // Build the query based on filters - handle both JSON objects and arrays
                $query = "SELECT 
                o.id,
                o.booking_id,
                o.agent_id,
                o.type as service_type,
                o.status,
                o.created_at,
                a.name as agent_name,
                a.company_name,
                COALESCE(
                    (o.data->>'totalPrice')::NUMERIC,
                    (o.data->0->>'totalPrice')::NUMERIC,
                    0
                ) as amount,
                COALESCE(
                    o.data->>'fullName',
                    o.data->0->>'fullName',
                    'N/A'
                ) as customer_name,
                COALESCE(
                    o.data->>'email',
                    o.data->0->>'email',
                    'N/A'
                ) as customer_email
                  FROM orders o
                  LEFT JOIN agents a ON o.agent_id = a.agent_id
                  WHERE o.status = 1
                    AND o.type IN ('hotel', 'attraction', 'guide', 'driver', 'entry_port', 'exit_port', 'travel_point', 'travel_hourly')
                    AND o.created_at BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        // Filter by accessible agents if not admin
        if ($user->role_id !== 1) {
            if (!empty($agentIds)) {
                // User has accessible agents - filter by them
                $query .= " AND o.agent_id = ANY(?)";
                $params[] = '{' . implode(',', $agentIds) . '}';
            } else {
                // User has no accessible agents - show no results
                $query .= " AND o.agent_id = -1"; // This will match no records
            }
        }

        if ($agentId) {
            $query .= " AND o.agent_id = ?";
            $params[] = $agentId;
        }

        if ($serviceType) {
            $query .= " AND o.type = ?";
            $params[] = $serviceType;
        }

        $query .= " ORDER BY o.created_at DESC";

        $results = DB::select($query, $params);
        
        // Ensure $results is always an array
        if (!$results) {
            $results = [];
        }

        // Debug: Log service types for debugging
        $serviceTypes = array_unique(array_column($results, 'service_type'));
        Log::info('Service types found in ledger query:', $serviceTypes);

        // Get agents for filter dropdown (only accessible agents) - using pluck
        $agentsForDropdown = $this->getAccessibleAgentsForDropdown($user);
        
        // Ensure $agentsForDropdown is always a collection
        if (!$agentsForDropdown) {
            $agentsForDropdown = collect();
        }

        // DMC relationship data is already included in the agent selection

        // Get Master DMCs for dropdown (for admin only)
        $masterDmcsForDropdown = collect();
        if ($user->role_id == 1 || $user->role_id == 2) { // Admin and Super Admin - can see all Master DMCs
            $masterDmcsForDropdown = User::where('role_id', 10)
                ->select('userId', 'name', 'company_name')
                ->orderBy('company_name', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        }
        // Master DMC users and below don't get Master DMC dropdown

        // Get DMCs for dropdown based on user role
        $dmcsForDropdown = collect();
        if ($user->role_id == 1 || $user->role_id == 2) { // Admin and Super Admin - can see all DMCs
            $dmcsForDropdown = User::where('role_id', 11)
                ->select('userId', 'name', 'company_name', 'master_dmc_id')
                ->orderBy('company_name', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        } elseif ($user->role_id == 10) { // Master DMC - can see their DMCs
            $dmcsForDropdown = User::where('role_id', 11)
                ->where('master_dmc_id', $user->userId)
                ->select('userId', 'name', 'company_name', 'master_dmc_id')
                ->orderBy('company_name', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        }
        // DMC users (role_id = 11) and below don't get DMC dropdown

        return view('reports.ledger', compact(
            'results', 
            'startDate', 
            'endDate', 
            'agentId', 
            'serviceType', 
            'agentsForDropdown',
            'masterDmcsForDropdown',
            'dmcsForDropdown',
            'masterDmcId',
            'dmcId'
        ));
    }

    /**
     * Get all user IDs in DMC hierarchy (DMC -> Sales Head -> Sales Manager -> Assistant Manager)
     */
    private function getDmcHierarchyIds($dmcId)
    {
        // Start with the DMC itself
        $hierarchyIds = collect([$dmcId]);
        
        // Get all sales heads under this DMC
        $salesHeads = User::where('created_by', $dmcId)
            ->whereIn('role_id', [33, 37, 128, 129, 130, 134, 135, 136, 138])
            ->pluck('userId');
        
        $hierarchyIds = $hierarchyIds->merge($salesHeads);
        
        // Get all sales managers under these sales heads
        $salesManagers = User::whereIn('created_by', $salesHeads)
            ->whereIn('role_id', [12, 37])
            ->pluck('userId');
        
        $hierarchyIds = $hierarchyIds->merge($salesManagers);
        
        // Get all assistant managers under these sales managers
        $assistantManagers = User::whereIn('created_by', $salesManagers)
            ->where('role_id', 38)
            ->pluck('userId');
        
        $hierarchyIds = $hierarchyIds->merge($assistantManagers);
        
        return $hierarchyIds->unique()->filter()->toArray();
    }

    /**
     * Get accessible agent IDs based on user role
     */
    private function getAccessibleAgentIds($user)
    {
        switch ($user->role_id) {
            case 1: // Admin
            case 2: // Super Admin
                // Admin can see all active agents
                return Agent::where('status', 1)->pluck('agent_id')->toArray();

            case 10: // Master DMC
                $masterDmcId = $user->userId;
                
                // Get all DMCs under this Master DMC
                $dmcs = User::where('master_dmc_id', $masterDmcId)
                           ->where('role_id', 11)
                           ->pluck('userId');
                
                $allHierarchyIds = collect([$masterDmcId]);
                
                // Get hierarchy for each DMC
                foreach ($dmcs as $dmcId) {
                    $dmcHierarchy = $this->getDmcHierarchyIds($dmcId);
                    $allHierarchyIds = $allHierarchyIds->merge($dmcHierarchy);
                }

                return Agent::where('status', 1)
                           ->whereIn('sales_manager_dmc', $allHierarchyIds->unique())
                           ->pluck('agent_id')
                           ->toArray();

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
                return Agent::where('status', 1)
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
                           })->pluck('agent_id')->toArray();

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
                    return Agent::where('status', 1)
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
                               })->pluck('agent_id')->toArray();
                }
                return [];

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
                    return Agent::where('status', 1)
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
                               })->pluck('agent_id')->toArray();
                }
                return [];

            case 38: // Assistant Sales Manager
                // Assistant Sales Manager can only see agents that he created directly
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
                    return Agent::where('status', 1)
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
                               })->pluck('agent_id')->toArray();
                }
                return [];

            default:
                // For all other roles, get the parent DMC's agents
                $parentUser = User::where('userId', $user->created_by)->first();
                while ($parentUser && !in_array($parentUser->role_id, [11])) {
                    $parentUser = User::where('userId', $parentUser->created_by)->first();
                }

                if ($parentUser && $parentUser->role_id == 11) {
                    $dmc_id = $parentUser->userId;
                    return Agent::where('status', 1)->where(function($query) use ($dmc_id) {
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
                    })->pluck('agent_id')->toArray();
                }
                return []; // No access 
        }
    }

    /**
     * Get accessible agents for dropdown (only agent_id and name)
     */
    private function getAccessibleAgentsForDropdown($user)
    {
        switch ($user->role_id) {
            case 1: // Admin
            case 2: // Super Admin
                // Admin can see all active agents
                $agents = Agent::where('status', 1)
                    ->select('agent_id', 'name', 'sales_manager_dmc')
                    ->get();
                
                // Add root DMC ID for each agent
                return $agents->map(function ($agent) {
                    $agent->root_dmc_id = $this->findRootDmcForAgent($agent->sales_manager_dmc);
                    return $agent;
                });

            case 10: // Master DMC
                $masterDmcId = $user->userId;
                
                // Get all DMCs under this Master DMC
                $dmcs = User::where('master_dmc_id', $masterDmcId)
                           ->where('role_id', 11)
                           ->pluck('userId');
                
                $allHierarchyIds = collect([$masterDmcId]);
                
                // Get hierarchy for each DMC
                foreach ($dmcs as $dmcId) {
                    $dmcHierarchy = $this->getDmcHierarchyIds($dmcId);
                    $allHierarchyIds = $allHierarchyIds->merge($dmcHierarchy);
                }

                $agents = Agent::where('status', 1)
                    ->whereIn('sales_manager_dmc', $allHierarchyIds->unique())
                    ->select('agent_id', 'name', 'sales_manager_dmc')
                    ->get();
                
                // Add root DMC ID for each agent
                return $agents->map(function ($agent) {
                    $agent->root_dmc_id = $this->findRootDmcForAgent($agent->sales_manager_dmc);
                    return $agent;
                });

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
                $agents = Agent::where('status', 1)
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
                             })
                             ->select('agent_id', 'name', 'sales_manager_dmc')
                             ->get();
                
                // Add root DMC ID for each agent
                return $agents->map(function ($agent) use ($user) {
                    $agent->root_dmc_id = $user->userId; // For DMC users, they are the root
                    return $agent;
                });

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
                    $agents = Agent::where('status', 1)
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
                                 })
                                 ->select('agent_id', 'name', 'sales_manager_dmc')
                                 ->get();
                    
                    // Add root DMC ID for each agent
                    return $agents->map(function ($agent) {
                        $agent->root_dmc_id = $this->findRootDmcForAgent($agent->sales_manager_dmc);
                        return $agent;
                    });
                }
                return collect();

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
                    $agents = Agent::where('status', 1)
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
                               })
                               ->select('agent_id', 'name', 'sales_manager_dmc')
                               ->get();
                    
                    // Add root DMC ID for each agent
                    return $agents->map(function ($agent) {
                        $agent->root_dmc_id = $this->findRootDmcForAgent($agent->sales_manager_dmc);
                        return $agent;
                    });
                }
                return collect();

            case 38: // Assistant Sales Manager
                // Assistant Sales Manager can only see agents that he created directly
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
                    $agents = Agent::where('status', 1)
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
                               })
                               ->select('agent_id', 'name', 'sales_manager_dmc')
                               ->get();
                    
                    // Add root DMC ID for each agent
                    return $agents->map(function ($agent) {
                        $agent->root_dmc_id = $this->findRootDmcForAgent($agent->sales_manager_dmc);
                        return $agent;
                    });
                }
                return collect();

            default:
                // For all other roles, get the parent DMC's agents
                $parentUser = User::where('userId', $user->created_by)->first();
                while ($parentUser && !in_array($parentUser->role_id, [11])) {
                    $parentUser = User::where('userId', $parentUser->created_by)->first();
                }

                if ($parentUser && $parentUser->role_id == 11) {
                    $dmc_id = $parentUser->userId;
                    $agents = Agent::where('status', 1)->where(function($query) use ($dmc_id) {
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
                    })
                    ->select('agent_id', 'name', 'sales_manager_dmc')
                    ->get();
                    
                    // Add root DMC ID for each agent
                    return $agents->map(function ($agent) {
                        $agent->root_dmc_id = $this->findRootDmcForAgent($agent->sales_manager_dmc);
                        return $agent;
                    });
                }
                return collect(); // No access - return empty collection
        }
    }

    /**
     * Find the root DMC for a given user ID by traversing up the hierarchy
     */
    private function findRootDmcForAgent($userId)
    {
        $user = User::where('userId', $userId)->first();
        
        if (!$user) {
            return null;
        }
        
        // If the user is already a DMC, return their ID
        if ($user->role_id == 11) {
            return $user->userId;
        }
        
        // Traverse up the hierarchy to find the DMC
        $currentUser = $user;
        $maxDepth = 10; // Prevent infinite loops
        $depth = 0;
        
        while ($currentUser && $currentUser->role_id != 11 && $depth < $maxDepth) {
            $parentUser = User::where('userId', $currentUser->created_by)->first();
            if (!$parentUser) {
                break;
            }
            $currentUser = $parentUser;
            $depth++;
        }
        
        // Return the DMC ID if found, otherwise return the original userId for fallback
        return ($currentUser && $currentUser->role_id == 11) ? $currentUser->userId : $userId;
    }

    /**
     * Balance Sheet Report with day-wise profit/loss analysis
     */
    public function balanceSheet(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $agentId = $request->input('agent_id', null);
        $viewType = $request->input('view_type', 'daily'); // daily, weekly, monthly

        // Get accessible agent IDs
        $agentIds = $this->getAccessibleAgentIds($user);

        // Build comprehensive balance sheet query
        $query = "
            WITH daily_transactions AS (
                SELECT 
                    DATE(o.created_at) as transaction_date,
                    o.agent_id,
                    a.name as agent_name,
                    o.type as service_type,
                    COUNT(o.id) as transaction_count,
                    COALESCE(
                        SUM((o.data->>'totalPrice')::NUMERIC),
                        SUM((o.data->0->>'totalPrice')::NUMERIC),
                        0
                    ) as gross_revenue,
                    COALESCE(
                        SUM((o.data->>'commission')::NUMERIC),
                        SUM((o.data->0->>'commission')::NUMERIC),
                        SUM((o.data->>'totalPrice')::NUMERIC * 0.1),
                        0
                    ) as commission_earned,
                    COALESCE(
                        SUM((o.data->>'operationalCost')::NUMERIC),
                        SUM((o.data->0->>'operationalCost')::NUMERIC),
                        SUM((o.data->>'totalPrice')::NUMERIC * 0.7),
                        0
                    ) as operational_costs,
                    COALESCE(
                        SUM((o.data->>'taxes')::NUMERIC),
                        SUM((o.data->0->>'taxes')::NUMERIC),
                        SUM((o.data->>'totalPrice')::NUMERIC * 0.18),
                        0
                    ) as taxes_paid
                FROM orders o
                LEFT JOIN agents a ON o.agent_id = a.agent_id
                WHERE o.status = 1
                    AND o.type IN ('hotel', 'attraction', 'guide', 'driver', 'entry_port', 'exit_port', 'travel_point', 'travel_hourly')
                    AND o.created_at BETWEEN ? AND ?
        ";

        $params = [$startDate, $endDate];

        // Filter by accessible agents if not admin
        if ($user->role_id !== 1) {
            if (!empty($agentIds)) {
                $query .= " AND o.agent_id = ANY(?)";
                $params[] = '{' . implode(',', $agentIds) . '}';
            } else {
                $query .= " AND o.agent_id = -1";
            }
        }

        if ($agentId) {
            $query .= " AND o.agent_id = ?";
            $params[] = $agentId;
        }

        $query .= "
                GROUP BY DATE(o.created_at), o.agent_id, a.name, o.type
            ),
            balance_calculations AS (
                SELECT 
                    transaction_date,
                    agent_id,
                    agent_name,
                    service_type,
                    transaction_count,
                    gross_revenue,
                    commission_earned,
                    operational_costs,
                    taxes_paid,
                    (gross_revenue - operational_costs - taxes_paid + commission_earned) as net_profit,
                    CASE 
                        WHEN gross_revenue > 0 
                        THEN ((gross_revenue - operational_costs - taxes_paid + commission_earned) / gross_revenue) * 100
                        ELSE 0 
                    END as profit_margin_percentage
                FROM daily_transactions
            )
            SELECT * FROM balance_calculations
            ORDER BY transaction_date DESC, agent_name, service_type
        ";

        $rawResults = DB::select($query, $params);

        // Process results based on view type
        $processedResults = $this->processBalanceSheetResults($rawResults, $viewType);

        // Calculate summary statistics
        $summaryStats = $this->calculateBalanceSheetSummary($rawResults);

        // Calculate traditional balance sheet
        $traditionalBalanceSheet = $this->calculateTraditionalBalanceSheet($user, $startDate, $endDate, $agentIds);

        // Get agents for filter dropdown
        $agentsForDropdown = $this->getAccessibleAgentsForDropdown($user);

        // Get service types for filtering
        $serviceTypes = collect($rawResults)->pluck('service_type')->unique()->sort()->values();

        return view('reports.balance-sheet', compact(
            'processedResults', 
            'summaryStats',
            'traditionalBalanceSheet',
            'startDate', 
            'endDate', 
            'agentId', 
            'viewType',
            'agentsForDropdown',
            'serviceTypes'
        ));
    }

    /**
     * Get transaction details for modal
     */
    public function getTransactionDetails($transactionId)
    {
        $user = Auth::user();
        $agentIds = $this->getAccessibleAgentIds($user);

        // Get transaction details
        $query = "SELECT 
            o.id,
            o.booking_id,
            o.agent_id,
            o.type as service_type,
            o.status,
            o.created_at,
            a.name as agent_name,
            COALESCE(
                (o.data->>'totalPrice')::NUMERIC,
                (o.data->0->>'totalPrice')::NUMERIC,
                0
            ) as amount,
            COALESCE(
                o.data->>'fullName',
                o.data->0->>'fullName',
                'N/A'
            ) as customer_name,
            COALESCE(
                o.data->>'email',
                o.data->0->>'email',
                'N/A'
            ) as customer_email,
            o.data
        FROM orders o
        LEFT JOIN agents a ON o.agent_id = a.agent_id
        WHERE o.id = ?";

        $params = [$transactionId];

        // Add security check - only allow access to user's accessible agents
        if ($user->role_id !== 1) {
            if (!empty($agentIds)) {
                $query .= " AND o.agent_id = ANY(?)";
                $params[] = '{' . implode(',', $agentIds) . '}';
            } else {
                return response()->json(['success' => false, 'message' => 'Access denied']);
            }
        }

        $transaction = DB::select($query, $params);

        if (empty($transaction)) {
            return response()->json(['success' => false, 'message' => 'Transaction not found']);
        }

        return response()->json([
            'success' => true,
            'transaction' => $transaction[0]
        ]);
    }

    /**
     * Get balance history for an agent
     */
    public function getBalanceHistory($agentId)
    {
        $user = Auth::user();
        $agentIds = $this->getAccessibleAgentIds($user);

        // Check if user has access to this agent
        if ($user->role_id !== 1 && !in_array($agentId, $agentIds)) {
            return response()->json(['success' => false, 'message' => 'Access denied']);
        }

        // Get agent details
        $agent = Agent::where('agent_id', $agentId)->first();
        if (!$agent) {
            return response()->json(['success' => false, 'message' => 'Agent not found']);
        }

        // Get transaction history for this agent
        $query = "SELECT 
            o.id,
            o.booking_id,
            o.agent_id,
            o.type as service_type,
            o.status,
            o.created_at,
            COALESCE(
                (o.data->>'totalPrice')::NUMERIC,
                (o.data->0->>'totalPrice')::NUMERIC,
                0
            ) as amount
        FROM orders o
        WHERE o.agent_id = ? AND o.status = 1
        ORDER BY o.created_at ASC";

        $history = DB::select($query, [$agentId]);

        return response()->json([
            'success' => true,
            'agent' => [
                'agent_id' => $agent->agent_id,
                'name' => $agent->name
            ],
            'history' => $history
        ]);
    }

    /**
     * Export single transaction as PDF
     */
    public function exportTransaction($transactionId)
    {
        $user = Auth::user();
        $agentIds = $this->getAccessibleAgentIds($user);

        // Get transaction details (reuse the same query)
        $query = "SELECT 
            o.id,
            o.booking_id,
            o.agent_id,
            o.type as service_type,
            o.status,
            o.created_at,
            a.name as agent_name,
            COALESCE(
                (o.data->>'totalPrice')::NUMERIC,
                (o.data->0->>'totalPrice')::NUMERIC,
                0
            ) as amount,
            COALESCE(
                o.data->>'fullName',
                o.data->0->>'fullName',
                'N/A'
            ) as customer_name,
            COALESCE(
                o.data->>'email',
                o.data->0->>'email',
                'N/A'
            ) as customer_email
        FROM orders o
        LEFT JOIN agents a ON o.agent_id = a.agent_id
        WHERE o.id = ?";

        $params = [$transactionId];

        // Add security check
        if ($user->role_id !== 1) {
            if (!empty($agentIds)) {
                $query .= " AND o.agent_id = ANY(?)";
                $params[] = '{' . implode(',', $agentIds) . '}';
            } else {
                return response()->json(['success' => false, 'message' => 'Access denied']);
            }
        }

        $transaction = DB::select($query, $params);

        if (empty($transaction)) {
            return response()->json(['success' => false, 'message' => 'Transaction not found']);
        }

        $transaction = $transaction[0];

        // Generate CSV content (simpler than PDF for now)
        $csvContent = "Transaction Details\n\n";
        $csvContent .= "Transaction ID,{$transaction->id}\n";
        $csvContent .= "Booking ID,{$transaction->booking_id}\n";
        $csvContent .= "Service Type,{$transaction->service_type}\n";
        $csvContent .= "Agent Name,{$transaction->agent_name}\n";
        $csvContent .= "Agent ID,{$transaction->agent_id}\n";
        $csvContent .= "Customer Name,{$transaction->customer_name}\n";
        $csvContent .= "Customer Email,{$transaction->customer_email}\n";
        $csvContent .= "Amount,{$transaction->amount}\n";
        $csvContent .= "Date,{$transaction->created_at}\n";
        $csvContent .= "Status," . ($transaction->status == 1 ? 'Active' : 'Inactive') . "\n";

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="transaction_' . $transactionId . '.csv"');
    }

    /**
     * Export balance history as CSV
     */
    public function exportBalanceHistory($agentId)
    {
        $user = Auth::user();
        $agentIds = $this->getAccessibleAgentIds($user);

        // Check access
        if ($user->role_id !== 1 && !in_array($agentId, $agentIds)) {
            return response()->json(['success' => false, 'message' => 'Access denied']);
        }

        // Get agent and history
        $agent = Agent::where('agent_id', $agentId)->first();
        if (!$agent) {
            return response()->json(['success' => false, 'message' => 'Agent not found']);
        }

        $query = "SELECT 
            o.id,
            o.booking_id,
            o.type as service_type,
            o.created_at,
            COALESCE(
                (o.data->>'totalPrice')::NUMERIC,
                (o.data->0->>'totalPrice')::NUMERIC,
                0
            ) as amount
        FROM orders o
        WHERE o.agent_id = ? AND o.status = 1
        ORDER BY o.created_at ASC";

        $history = DB::select($query, [$agentId]);

        // Generate CSV
        $csvContent = "Balance History for Agent: {$agent->name} (ID: {$agent->agent_id})\n\n";
        $csvContent .= "Date,Booking ID,Service Type,Opening Balance,Transaction Amount,Closing Balance\n";

        $runningBalance = 0;
        foreach ($history as $item) {
            $openingBalance = $runningBalance;
            $transactionAmount = floatval($item->amount);
            $runningBalance += $transactionAmount;

            $csvContent .= implode(',', [
                date('Y-m-d', strtotime($item->created_at)),
                $item->booking_id,
                $item->service_type,
                number_format($openingBalance, 2),
                number_format($transactionAmount, 2),
                number_format($runningBalance, 2)
            ]) . "\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="balance_history_' . $agentId . '.csv"');
    }

    /**
     * Process balance sheet results based on view type
     */
    private function processBalanceSheetResults($rawResults, $viewType)
    {
        $collection = collect($rawResults);

        switch ($viewType) {
            case 'weekly':
                return $collection->groupBy(function ($item) {
                    $date = \Carbon\Carbon::parse($item->transaction_date);
                    return $date->format('Y-W') . '|' . $item->agent_name;
                })->map(function ($group) {
                    return $this->aggregateBalanceSheetGroup($group, 'weekly');
                });

            case 'monthly':
                return $collection->groupBy(function ($item) {
                    $date = \Carbon\Carbon::parse($item->transaction_date);
                    return $date->format('Y-m') . '|' . $item->agent_name;
                })->map(function ($group) {
                    return $this->aggregateBalanceSheetGroup($group, 'monthly');
                });

            default: // daily
                return $collection->groupBy(function ($item) {
                    return $item->transaction_date . '|' . $item->agent_name;
                })->map(function ($group) {
                    return $this->aggregateBalanceSheetGroup($group, 'daily');
                });
        }
    }

    /**
     * Aggregate balance sheet group data
     */
    private function aggregateBalanceSheetGroup($group, $periodType)
    {
        $first = $group->first();
        $services = $group->groupBy('service_type')->map(function ($serviceGroup) {
            return [
                'service_type' => $serviceGroup->first()->service_type,
                'transaction_count' => $serviceGroup->sum('transaction_count'),
                'gross_revenue' => $serviceGroup->sum('gross_revenue'),
                'commission_earned' => $serviceGroup->sum('commission_earned'),
                'operational_costs' => $serviceGroup->sum('operational_costs'),
                'taxes_paid' => $serviceGroup->sum('taxes_paid'),
                'net_profit' => $serviceGroup->sum('net_profit'),
            ];
        })->values();

        $totalGrossRevenue = $group->sum('gross_revenue');
        $totalNetProfit = $group->sum('net_profit');

        return [
            'period' => $this->formatPeriod($first->transaction_date, $periodType),
            'agent_name' => $first->agent_name,
            'agent_id' => $first->agent_id,
            'transaction_count' => $group->sum('transaction_count'),
            'gross_revenue' => $totalGrossRevenue,
            'commission_earned' => $group->sum('commission_earned'),
            'operational_costs' => $group->sum('operational_costs'),
            'taxes_paid' => $group->sum('taxes_paid'),
            'net_profit' => $totalNetProfit,
            'profit_margin_percentage' => $totalGrossRevenue > 0 ? ($totalNetProfit / $totalGrossRevenue) * 100 : 0,
            'services' => $services,
            'service_count' => $services->count(),
            'profitability_status' => $totalNetProfit > 0 ? 'profitable' : ($totalNetProfit < 0 ? 'loss' : 'breakeven')
        ];
    }

    /**
     * Format period based on type
     */
    private function formatPeriod($date, $periodType)
    {
        $carbon = \Carbon\Carbon::parse($date);
        
        switch ($periodType) {
            case 'weekly':
                return 'Week ' . $carbon->week . ', ' . $carbon->year;
            case 'monthly':
                return $carbon->format('F Y');
            default:
                return $carbon->format('M d, Y');
        }
    }

    /**
     * Calculate balance sheet summary statistics
     */
    private function calculateBalanceSheetSummary($rawResults)
    {
        $collection = collect($rawResults);
        
        $totalRevenue = $collection->sum('gross_revenue');
        $totalCosts = $collection->sum('operational_costs');
        $totalTaxes = $collection->sum('taxes_paid');
        $totalCommission = $collection->sum('commission_earned');
        $totalProfit = $collection->sum('net_profit');
        
        $profitableDays = $collection->filter(function ($item) {
            return $item->net_profit > 0;
        })->count();
        
        $lossDays = $collection->filter(function ($item) {
            return $item->net_profit < 0;
        })->count();

        $avgDailyProfit = $collection->groupBy('transaction_date')->map(function ($dayGroup) {
            return $dayGroup->sum('net_profit');
        })->avg();

        return [
            'total_revenue' => $totalRevenue,
            'total_costs' => $totalCosts,
            'total_taxes' => $totalTaxes,
            'total_commission' => $totalCommission,
            'total_profit' => $totalProfit,
            'profit_margin' => $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0,
            'profitable_days' => $profitableDays,
            'loss_days' => $lossDays,
            'total_days' => $collection->pluck('transaction_date')->unique()->count(),
            'avg_daily_profit' => $avgDailyProfit ?? 0,
            'total_transactions' => $collection->sum('transaction_count'),
            'unique_agents' => $collection->pluck('agent_id')->unique()->count(),
            'service_breakdown' => $collection->groupBy('service_type')->map(function ($serviceGroup, $service) {
                return [
                    'service' => $service,
                    'revenue' => $serviceGroup->sum('gross_revenue'),
                    'profit' => $serviceGroup->sum('net_profit'),
                    'transactions' => $serviceGroup->sum('transaction_count')
                ];
            })->values()
        ];
    }

    /**
     * Calculate traditional balance sheet components (Assets, Liabilities, Equity)
     */
    private function calculateTraditionalBalanceSheet($user, $startDate, $endDate, $agentIds)
    {
        // Get accessible agent IDs filter
        $agentFilter = "";
        $params = [$startDate, $endDate];
        
        if ($user->role_id !== 1) {
            if (!empty($agentIds)) {
                $agentFilter = " AND o.agent_id = ANY(?)";
                $params[] = '{' . implode(',', $agentIds) . '}';
            } else {
                $agentFilter = " AND o.agent_id = -1";
            }
        }

        // Calculate Current Assets
        $currentAssetsQuery = "
            SELECT 
                SUM(COALESCE((o.data->>'totalPrice')::NUMERIC, (o.data->0->>'totalPrice')::NUMERIC, 0)) as cash_equivalent,
                SUM(COALESCE((o.data->>'pendingAmount')::NUMERIC, (o.data->0->>'pendingAmount')::NUMERIC, 0)) as accounts_receivable,
                SUM(COALESCE((o.data->>'advanceAmount')::NUMERIC, (o.data->0->>'advanceAmount')::NUMERIC, 0)) as inventory_bookings
            FROM orders o
            WHERE o.status = 1 
                AND o.created_at BETWEEN ? AND ?
                {$agentFilter}
        ";

        $currentAssets = DB::select($currentAssetsQuery, $params)[0];

        // Calculate Non-Current Assets (Real amounts from different service types)
        $nonCurrentAssetsQuery = "
            SELECT 
                SUM(CASE WHEN o.type = 'guide' THEN COALESCE((o.data->>'totalPrice')::NUMERIC, (o.data->0->>'totalPrice')::NUMERIC, 0) ELSE 0 END) as equipment_guides,
                SUM(CASE WHEN o.type = 'driver' THEN COALESCE((o.data->>'totalPrice')::NUMERIC, (o.data->0->>'totalPrice')::NUMERIC, 0) ELSE 0 END) as vehicles_drivers,
                SUM(CASE WHEN o.type = 'hotel' THEN COALESCE((o.data->>'totalPrice')::NUMERIC, (o.data->0->>'totalPrice')::NUMERIC, 0) ELSE 0 END) as property_partnerships,
                SUM(CASE WHEN o.type = 'attraction' THEN COALESCE((o.data->>'totalPrice')::NUMERIC, (o.data->0->>'totalPrice')::NUMERIC, 0) ELSE 0 END) as attraction_assets,
                SUM(CASE WHEN o.type IN ('entry_port', 'exit_port', 'travel_point', 'travel_hourly') THEN COALESCE((o.data->>'totalPrice')::NUMERIC, (o.data->0->>'totalPrice')::NUMERIC, 0) ELSE 0 END) as transport_assets
            FROM orders o
            WHERE o.status = 1 
                AND o.created_at BETWEEN ? AND ?
                {$agentFilter}
        ";

        $nonCurrentAssets = DB::select($nonCurrentAssetsQuery, $params)[0];

        // Calculate Current Liabilities
        $currentLiabilitiesQuery = "
            SELECT 
                SUM(COALESCE((o.data->>'operationalCost')::NUMERIC, (o.data->0->>'operationalCost')::NUMERIC, 0)) as accounts_payable,
                SUM(COALESCE((o.data->>'taxes')::NUMERIC, (o.data->0->>'taxes')::NUMERIC, 0)) as tax_payable,
                SUM(COALESCE((o.data->>'commission')::NUMERIC, (o.data->0->>'commission')::NUMERIC, 0)) as commission_payable
            FROM orders o
            WHERE o.status = 1 
                AND o.created_at BETWEEN ? AND ?
                {$agentFilter}
        ";

        $currentLiabilities = DB::select($currentLiabilitiesQuery, $params)[0];

        // Calculate Owner's Equity
        $equityQuery = "
            SELECT 
                SUM(COALESCE((o.data->>'totalPrice')::NUMERIC, (o.data->0->>'totalPrice')::NUMERIC, 0)) as total_revenue,
                SUM(COALESCE((o.data->>'operationalCost')::NUMERIC, (o.data->0->>'operationalCost')::NUMERIC, 0)) as total_costs,
                SUM(COALESCE((o.data->>'taxes')::NUMERIC, (o.data->0->>'taxes')::NUMERIC, 0)) as total_taxes,
                SUM(COALESCE((o.data->>'commission')::NUMERIC, (o.data->0->>'commission')::NUMERIC, 0)) as total_commission
            FROM orders o
            WHERE o.status = 1 
                AND o.created_at BETWEEN ? AND ?
                {$agentFilter}
        ";

        $equity = DB::select($equityQuery, $params)[0];

        // Calculate totals
        $cashAndEquivalents = $currentAssets->cash_equivalent ?? 0;
        $accountsReceivable = $currentAssets->accounts_receivable ?? 0;
        $inventoryValue = $currentAssets->inventory_bookings ?? 0;
        $totalCurrentAssets = $cashAndEquivalents + $accountsReceivable + $inventoryValue;

        $equipmentValue = $nonCurrentAssets->equipment_guides ?? 0;
        $vehiclesValue = $nonCurrentAssets->vehicles_drivers ?? 0;
        $propertyValue = $nonCurrentAssets->property_partnerships ?? 0;
        $attractionValue = $nonCurrentAssets->attraction_assets ?? 0;
        $transportValue = $nonCurrentAssets->transport_assets ?? 0;
        $totalNonCurrentAssets = $equipmentValue + $vehiclesValue + $propertyValue + $attractionValue + $transportValue;

        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;

        $accountsPayable = $currentLiabilities->accounts_payable ?? 0;
        $taxPayable = $currentLiabilities->tax_payable ?? 0;
        $commissionPayable = $currentLiabilities->commission_payable ?? 0;
        $totalCurrentLiabilities = $accountsPayable + $taxPayable + $commissionPayable;

        // Estimate long-term liabilities (20% of total revenue)
        $longTermLoan = ($equity->total_revenue ?? 0) * 0.2;
        $totalLiabilities = $totalCurrentLiabilities + $longTermLoan;

        // Calculate retained earnings
        $netIncome = ($equity->total_revenue ?? 0) - ($equity->total_costs ?? 0) - ($equity->total_taxes ?? 0) + ($equity->total_commission ?? 0);
        $initialCapital = max(100000, $totalAssets * 0.4); // Minimum capital or 40% of assets
        $retainedEarnings = $netIncome;
        $totalEquity = $initialCapital + $retainedEarnings;

        return [
            'current_assets' => [
                'cash_and_equivalents' => $cashAndEquivalents,
                'accounts_receivable' => $accountsReceivable,
                'inventory' => $inventoryValue,
                'total' => $totalCurrentAssets
            ],
            'non_current_assets' => [
                'equipment' => $equipmentValue,
                'vehicles' => $vehiclesValue,
                'property_partnerships' => $propertyValue,
                'attraction_assets' => $attractionValue,
                'transport_assets' => $transportValue,
                'total' => $totalNonCurrentAssets
            ],
            'total_assets' => $totalAssets,
            'current_liabilities' => [
                'accounts_payable' => $accountsPayable,
                'tax_payable' => $taxPayable,
                'commission_payable' => $commissionPayable,
                'total' => $totalCurrentLiabilities
            ],
            'non_current_liabilities' => [
                'long_term_loan' => $longTermLoan,
                'total' => $longTermLoan
            ],
            'total_liabilities' => $totalLiabilities,
            'equity' => [
                'capital' => $initialCapital,
                'retained_earnings' => $retainedEarnings,
                'total' => $totalEquity
            ],
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
            'balance_check' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 1000 // Allow small rounding differences
        ];
    }
}
