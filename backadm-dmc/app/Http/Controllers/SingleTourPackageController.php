<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\City;
use App\Models\Agent;
use App\Models\SingleTourPackage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SingleTourPackageController extends Controller
{
    /**
     * Display a listing of single tour packages.
     */
    public function index()
    {
        $packages = SingleTourPackage::with(['country', 'city', 'agent'])
            ->where('dmc_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('single-tour-package.index', compact('packages'));
    }

    /**
     * Show the form for creating a new single tour package.
     */
    public function create()
    {
        // Get countries for dropdown
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        
        // Get agents for the current DMC
        $agents = Agent::Where('sales_manager_dmc', Auth::id())
            ->orderBy('name')
            ->get();

        return view('single-tour-package.create', compact('countries', 'agents'));
    }

    /**
     * Store a newly created single tour package in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_country' => 'required|string', // Country name
            'city' => 'required|string', // City name  
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'adults' => 'required|integer|min:1',
            'male' => 'required|integer|min:0',
            'female' => 'required|integer|min:0',
            'children' => 'required|integer|min:0',
            'infants' => 'required|integer|min:0',
            'agent_id' => 'required|exists:agents,agent_id',
            'package_name' => 'required|string|max:255',
            'estimated_budget' => 'nullable|numeric|min:0',
            'package_description' => 'nullable|string',
            'is_premium' => 'boolean'
        ]);

        // Additional validation: male + female should equal adults
        if (($request->male + $request->female) != $request->adults) {
            return back()->withErrors(['adults' => 'Total male and female count must equal total adults.'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Convert country name to country ID
            $country = Country::where('name', $request->user_country)->first();
            if (!$country) {
                throw new \Exception('Invalid country selected');
            }

            // Convert city name to city ID
            $city = City::where('name', $request->city)->where('country', $request->user_country)->first();
            if (!$city) {
                throw new \Exception('Invalid city selected');
            }

            $package = SingleTourPackage::create([
                'dmc_id' => Auth::id(),
                'country_id' => $country->id,
                'city_id' => $city->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'adults' => $request->adults,
                'male' => $request->male,
                'female' => $request->female,
                'children' => $request->children,
                'infants' => $request->infants,
                'agent_id' => $request->agent_id,
                'package_name' => $request->package_name,
                'estimated_budget' => $request->estimated_budget,
                'package_description' => $request->package_description,
                'is_premium' => $request->has('is_premium'),
                'status' => 'draft',
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('single-tour-package.index')
                ->with('success', 'Single tour package created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create tour package. Please try again.');
        }
    }

    /**
     * Display the specified single tour package.
     */
    public function show($id)
    {
        $package = SingleTourPackage::with(['country', 'city', 'agent', 'dmc'])
            ->where('dmc_id', Auth::id())
            ->findOrFail($id);

        return view('single-tour-package.show', compact('package'));
    }

    /**
     * Show the form for editing the specified single tour package.
     */
    public function edit($id)
    {
        $package = SingleTourPackage::where('dmc_id', Auth::id())->findOrFail($id);
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        $cities = City::where('country_id', $package->country_id)->orderBy('name')->get();
        
        $agents = Agent::where('root_dmc_id', Auth::id())
            ->orWhere('sales_manager_dmc', Auth::id())
            ->orderBy('name')
            ->get();

        return view('single-tour-package.edit', compact('package', 'countries', 'cities', 'agents'));
    }

    /**
     * Update the specified single tour package in storage.
     */
    public function update(Request $request, $id)
    {
        $package = SingleTourPackage::where('dmc_id', Auth::id())->findOrFail($id);

        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'infants' => 'required|integer|min:0',
            'agent_id' => 'required|exists:agents,agent_id',
            'package_name' => 'required|string|max:255',
            'estimated_budget' => 'nullable|numeric|min:0',
            'package_description' => 'nullable|string',
            'is_premium' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $package->update([
                'country_id' => $request->country_id,
                'city_id' => $request->city_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'adults' => $request->adults,
                'children' => $request->children,
                'infants' => $request->infants,
                'agent_id' => $request->agent_id,
                'package_name' => $request->package_name,
                'estimated_budget' => $request->estimated_budget,
                'package_description' => $request->package_description,
                'is_premium' => $request->has('is_premium'),
                'updated_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('single-tour-package.index')
                ->with('success', 'Single tour package updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update tour package. Please try again.');
        }
    }

    /**
     * Remove the specified single tour package from storage.
     */
    public function destroy($id)
    {
        try {
            $package = SingleTourPackage::where('dmc_id', Auth::id())->findOrFail($id);
            $package->delete();

            return redirect()->route('single-tour-package.index')
                ->with('success', 'Single tour package deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete tour package. Please try again.');
        }
    }

    /**
     * Fetch cities by country for auto-population (following agent controller pattern)
     */
    public function fetchCitiesByCountry(Request $request) 
    {
        $countryName = $request->input('country');
        
        $cities = City::where('country', $countryName)
                ->select('name', 'city_id', 'id')
                ->get();
                 
        return response()->json(['cities' => $cities]);
    }
} 