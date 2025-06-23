<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\EnquiryForm;
use App\Models\User;
use Illuminate\Http\Request;

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

        $user = auth()->user();
        $agent_ids = collect(); // default empty collection

        switch ($user->role_id) {
            case 1: // Admin
            case 2: // Super Admin
            // case 3: // Sales Management Admin
            // case 4: // Sales Management
            case 10: // Master DMC
                // These roles can see all enquiries
                $enquiries = EnquiryForm::whereNull('unique_tour_id')
                    ->with(['agent' => function($query) {
                        $query->select('agent_id', 'name', 'country');
                    }])
                    ->orderBy('enquiry_id', 'desc')
                    ->get();
                return view('enquiry-list.index', compact('enquiries'));

            case 11: // DMC
                // DMC can see all agents' enquiries
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

                $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)
                    ->pluck('agent_id');
                break;

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

                $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)
                    ->pluck('agent_id');
                break;

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

                $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)
                    ->pluck('agent_id');
                break;

            case 38: // Assistant Manager
                $agent_ids = Agent::where('sales_manager_dmc', $user->userId)
                    ->pluck('agent_id');
                break;
        }

        // Fetch enquiries based on agent_ids
        $enquiries = EnquiryForm::whereIn('agent_id', $agent_ids)
            ->whereNull('unique_tour_id')
            ->with(['agent' => function($query) {
                $query->select('agent_id', 'name', 'country');
            }])
            ->orderBy('enquiry_id', 'desc')
            ->get();

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
