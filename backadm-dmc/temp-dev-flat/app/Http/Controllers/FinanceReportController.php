<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceReportController extends Controller
{
    public function salesRevenue(Request $request)
    {
        $user = auth()->user();
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
        if ($user->role_id !== 1 && !empty($agentIds)) {
            // Get agent names for the accessible agent IDs
            $agentNames = Agent::whereIn('agent_id', $agentIds)->pluck('name')->toArray();
            
            $rawResults = collect($rawResults)->filter(function ($row) use ($agentNames) {
                return in_array($row->agent_name, $agentNames);
            });
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
        $user = auth()->user();
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $agentId = $request->input('agent_id', null);
        $serviceType = $request->input('service_type', null);

        // Get accessible agent IDs
        $agentIds = $this->getAccessibleAgentIds($user);

        // Build the query based on filters
        $query = "SELECT 
                    o.id,
                    o.booking_id,
                    o.agent_id,
                    o.type as service_type,
                    o.status,
                    o.created_at,
                    a.name as agent_name,
                    (elem->>'totalPrice')::NUMERIC as amount,
                    (elem->>'fullName') as customer_name,
                    (elem->>'email') as customer_email
                  FROM orders o
                  LEFT JOIN agents a ON o.agent_id = a.agent_id,
                  LATERAL jsonb_array_elements(o.data::jsonb) AS elem
                  WHERE o.status = 1
                    AND o.type IN ('hotel', 'attraction', 'guide', 'driver', 'entry_port', 'exit_port', 'travel_point', 'travel_hourly')
                    AND o.created_at BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        // Filter by accessible agents if not admin
        if ($user->role_id !== 1 && !empty($agentIds)) {
            $query .= " AND o.agent_id = ANY(?)";
            $params[] = '{' . implode(',', $agentIds) . '}';
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

        // Get agents for filter dropdown (only accessible agents) - using pluck
        $agentsForDropdown = $this->getAccessibleAgentsForDropdown($user);

        return view('reports.ledger', compact('results', 'startDate', 'endDate', 'agentId', 'serviceType', 'agentsForDropdown'));
    }

    /**
     * Get accessible agent IDs based on user role
     */
    private function getAccessibleAgentIds($user)
    {
        switch ($user->role_id) {
            case 11: // DMC
                $dmc_id = $user->userId;

                $sales_heads = User::where('created_by', $dmc_id)
                    ->where('role_id', 33)
                    ->pluck('userId');

                $sales_managers = User::whereIn('created_by', $sales_heads)
                    ->whereIn('role_id', [12, 37])
                    ->pluck('userId');

                $assistant_managers = User::whereIn('created_by', $sales_managers)
                    ->where('role_id', 38)
                    ->pluck('userId');

                $all_ids = collect([$dmc_id])
                    ->merge($sales_heads)
                    ->merge($sales_managers)
                    ->merge($assistant_managers)
                    ->unique()
                    ->filter();

                return Agent::whereIn('sales_manager_dmc', $all_ids)->pluck('agent_id')->toArray();

            case 33: // Sales Head
                $sh_id = $user->userId;

                $sales_managers = User::where('created_by', $sh_id)
                    ->whereIn('role_id', [12, 37])
                    ->pluck('userId');

                $assistant_managers = User::whereIn('created_by', $sales_managers)
                    ->where('role_id', 38)
                    ->pluck('userId');

                $all_ids = collect([$sh_id])
                    ->merge($sales_managers)
                    ->merge($assistant_managers)
                    ->unique()
                    ->filter();

                return Agent::whereIn('sales_manager_dmc', $all_ids)->pluck('agent_id')->toArray();

            case 12: // Sales Manager
            case 37:
                $sm_id = $user->userId;

                $assistant_managers = User::where('created_by', $sm_id)
                    ->where('role_id', 38)
                    ->pluck('userId');

                $all_ids = collect([$sm_id])
                    ->merge($assistant_managers)
                    ->unique()
                    ->filter();

                return Agent::whereIn('sales_manager_dmc', $all_ids)->pluck('agent_id')->toArray();

            case 38: // Assistant Manager
                return Agent::where('sales_manager_dmc', $user->userId)->pluck('agent_id')->toArray();

            case 1: // Admin
                return Agent::pluck('agent_id')->toArray();

            default:
                return []; // No access 
        }
    }

    /**
     * Get accessible agents for dropdown (only agent_id and name)
     */
    private function getAccessibleAgentsForDropdown($user)
    {
        switch ($user->role_id) {
            case 11: // DMC
                $dmc_id = $user->userId;

                $sales_heads = User::where('created_by', $dmc_id)
                    ->where('role_id', 33)
                    ->pluck('userId');

                $sales_managers = User::whereIn('created_by', $sales_heads)
                    ->whereIn('role_id', [12, 37])
                    ->pluck('userId');

                $assistant_managers = User::whereIn('created_by', $sales_managers)
                    ->where('role_id', 38)
                    ->pluck('userId');

                $all_ids = collect([$dmc_id])
                    ->merge($sales_heads)
                    ->merge($sales_managers)
                    ->merge($assistant_managers)
                    ->unique()
                    ->filter();

                return Agent::whereIn('sales_manager_dmc', $all_ids)
                    ->select('agent_id', 'name')
                    ->get();

            case 33: // Sales Head
                $sh_id = $user->userId;

                $sales_managers = User::where('created_by', $sh_id)
                    ->whereIn('role_id', [12, 37])
                    ->pluck('userId');

                $assistant_managers = User::whereIn('created_by', $sales_managers)
                    ->where('role_id', 38)
                    ->pluck('userId');

                $all_ids = collect([$sh_id])
                    ->merge($sales_managers)
                    ->merge($assistant_managers)
                    ->unique()
                    ->filter();

                return Agent::whereIn('sales_manager_dmc', $all_ids)
                    ->select('agent_id', 'name')
                    ->get();

            case 12: // Sales Manager
            case 37:
                $sm_id = $user->userId;

                $assistant_managers = User::where('created_by', $sm_id)
                    ->where('role_id', 38)
                    ->pluck('userId');

                $all_ids = collect([$sm_id])
                    ->merge($assistant_managers)
                    ->unique()
                    ->filter();

                return Agent::whereIn('sales_manager_dmc', $all_ids)
                    ->select('agent_id', 'name')
                    ->get();

            case 38: // Assistant Manager
                return Agent::where('sales_manager_dmc', $user->userId)
                    ->select('agent_id', 'name')
                    ->get();

            case 1: // Admin
                return Agent::select('agent_id', 'name')->get();

            default:
                return collect(); // No access
        }
    }
}
