<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Country;
use App\Models\Tour;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tour::where('status', 1); // Base query for active tours
        $tours = $query->get();

        $managerDetails = [];
        foreach ($tours as $tour) {

            $asmng_dmc = User::where('userId', $tour->sales_manager_dmc)->first();
            $salesmng_dmc = $asmng_dmc ? User::where('userId', $asmng_dmc->created_by)->first() : null;
            $saleshead_dmc = $salesmng_dmc ? User::where('userId', $salesmng_dmc->created_by)->first() : null;
            $dmc_users = $saleshead_dmc ? User::where('userId', $saleshead_dmc->created_by)->first() : null;

            $managerDetails[$tour->id] = array_filter([
                'asmng_dmc' => $asmng_dmc,
                'salesmng_dmc' => $salesmng_dmc,
                'saleshead_dmc' => $saleshead_dmc,
                'dmc_users' => $dmc_users,
            ]);
        }
        $tour_count = Tour::where('status', 1)->count();
        // $tour_price = Tour::with('booking')->where('status', 1)->sum('');
        return view('reports.index', compact('tours'));
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

    public function getMasterDmc()
    {
        // Get all users with role_id 10 (Master DMC)
        $masterDmcs = User::where('role_id', 10)
                         ->select('id', 'name', 'country')
                         ->get();

        return response()->json($masterDmcs);
    }

    // Add new method to get countries for a specific Master DMC
    public function getMasterDmcCountries($masterId)
    {
        $masterDmc = User::where('id', $masterId)
                        ->select('country')
                        ->first();

        if ($masterDmc && $masterDmc->country) {
            // Split the country string if it contains multiple countries
            $countries = array_map('trim', explode(',', $masterDmc->country));
            return response()->json($countries);
        }

        return response()->json([]);
    }

    public function getDmc()
    {
        // Get all users with role_id 10 (Master DMC)
        $Dmcs = User::where('role_id', 11)
                     ->select('id', 'name', 'country')
                     ->get();

        return response()->json($Dmcs);
    }

    public function getDmcList()
    {
        $dmcs = User::where('role_id', 10)
                    ->select('id', 'name', 'country')
                    ->get();

        return response()->json($dmcs);
    }

    // Add this method to handle DMC countries
    public function getDmcCountries($dmcId)
    {
        $dmc = User::where('id', $dmcId)
                  ->select('country')
                  ->first();
                  
        if ($dmc && $dmc->country) {
            return response()->json([
                'status' => 'success',
                'country' => $dmc->country
            ]);
        }
        
        return response()->json([
            'status' => 'error',
            'country' => ''
        ]);
    }

    /**
     * Get active countries.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveCountries()
    {
        try {
            $countries = Country::where('is_active', 1)
                            ->select('id', 'name', 'country_code', 'currency')
                            ->orderBy('name', 'asc')
                            ->get();
            
            return response()->json([
                'success' => true,
                'countries' => $countries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching countries: ' . $e->getMessage()
            ]);
        }
    }

    public function getToursByCountry($country)
    {
        $tours = Tour::where('destination', $country)->where('status', 1)->get();
    
        return response()->json([
            'success' => true,
            'tours' => $tours
        ]);
    }

}
