<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Agency;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AgencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $agencies = Agency::with(['creator', 'updater'])->orderBy('created_at', 'desc')->get();
        return view('agencies.index', compact('agencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::where('is_active', 1)->get();
        return view('agencies.create', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agency_name' => 'required|string|max:255',
            'email' => 'required|email|unique:agencies,email',
            'phone' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string',
            'postal_code' => 'nullable|string|max:20',
            'branches' => 'nullable|array',
            'branches.*.email' => 'required_with:branches|email',
            'branches.*.phone' => 'required_with:branches|string|max:20',
            'branches.*.country' => 'required_with:branches|string|max:255',
            'branches.*.city' => 'required_with:branches|string|max:255',
            'branches.*.address' => 'required_with:branches|string',
            'branches.*.postal_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if agency email is soft deleted
        $deletedAgency = Agency::withTrashed()->where('email', $request->input('email'))->first();

        if ($deletedAgency && $deletedAgency->trashed()) {
            // Restore and update
            $deletedAgency->restore();
            $deletedAgency->fill([
                'agency_name' => $request->input('agency_name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'country' => $request->input('country'),
                'city' => $request->input('city'),
                'address' => $request->input('address'),
                'postal_code' => $request->input('postal_code'),
                'branches' => $request->input('branches', []),
                'updated_by' => Auth::user()->userId,
            ]);
            $deletedAgency->save();

            return redirect()->route('agencies.index')->with('success', 'Soft-deleted agency restored and updated successfully!');
        }

        // Generate unique agency_id following the same pattern as AgentController
        $lastAgency = Agency::withTrashed()->orderBy('created_at', 'desc')->first();
        $agency_max_id = $lastAgency->agency_id ?? 1;
        $agencyId = CommonHelper::createId($agency_max_id);
        
        while (Agency::where('agency_id', $agencyId)->exists()) {
            $agencyId = CommonHelper::createId($agencyId);
        }

        // Create new agency
        $agency = new Agency();
        $agency->agency_id = $agencyId;
        $agency->agency_name = $request->input('agency_name');
        $agency->email = $request->input('email');
        $agency->phone = $request->input('phone');
        $agency->country = $request->input('country');
        $agency->city = $request->input('city');
        $agency->address = $request->input('address');
        $agency->postal_code = $request->input('postal_code');
        $agency->branches = $request->input('branches', []);
        $agency->created_by = Auth::user()->userId;

        if ($agency->save()) {
            return redirect()->route('agencies.index')->with('success', 'Agency created successfully!');
        }

        return redirect()->back()->with('error', 'Failed to create agency. Please try again.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $agency = Agency::where('agency_id', $id)->with(['creator', 'updater'])->firstOrFail();
        return view('agencies.show', compact('agency'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $agency = Agency::where('agency_id', $id)->firstOrFail();
        $countries = Country::all();
        return view('agencies.edit', compact('agency', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $agency = Agency::where('agency_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'agency_name' => 'required|string|max:255',
            'email' => 'required|email|unique:agencies,email,' . $agency->id,
            'phone' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string',
            'postal_code' => 'nullable|string|max:20',
            'branches' => 'nullable|array',
            'branches.*.email' => 'required_with:branches|email',
            'branches.*.phone' => 'required_with:branches|string|max:20',
            'branches.*.country' => 'required_with:branches|string|max:255',
            'branches.*.city' => 'required_with:branches|string|max:255',
            'branches.*.address' => 'required_with:branches|string',
            'branches.*.postal_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $agency->agency_name = $request->input('agency_name');
        $agency->email = $request->input('email');
        $agency->phone = $request->input('phone');
        $agency->country = $request->input('country');
        $agency->city = $request->input('city');
        $agency->address = $request->input('address');
        $agency->postal_code = $request->input('postal_code');
        $agency->branches = $request->input('branches', []);
        $agency->updated_by = Auth::user()->userId;

        if ($agency->save()) {
            return redirect()->route('agencies.index')->with('success', 'Agency updated successfully!');
        }

        return redirect()->back()->with('error', 'Failed to update agency. Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $agency = Agency::where('agency_id', $id)->firstOrFail();
        
        if ($agency->delete()) {
            return redirect()->route('agencies.index')->with('success', 'Agency deleted successfully!');
        }

        return redirect()->back()->with('error', 'Failed to delete agency. Please try again.');
    }

    /**
     * Toggle agency status
     */
    public function toggleStatus(string $id)
    {
        $agency = Agency::where('agency_id', $id)->firstOrFail();
        $agency->status = !$agency->status;
        $agency->updated_by = Auth::user()->userId;
        
        if ($agency->save()) {
            $status = $agency->status ? 'activated' : 'deactivated';
            return redirect()->back()->with('success', "Agency {$status} successfully!");
        }

        return redirect()->back()->with('error', 'Failed to update agency status. Please try again.');
    }

    /**
     * Get cities by country for Ajax
     */
    public function getCitiesByCountry(Request $request)
    {
        $country = $request->input('country');
        
        if (!$country) {
            return response()->json([
                'success' => false,
                'message' => 'Country is required'
            ]);
        }

        $cities = \App\Models\City::where('country', $country)
                                 ->select('city_id', 'name')
                                 ->orderBy('name')
                                 ->get();

        return response()->json([
            'success' => true,
            'cities' => $cities
        ]);
    }
} 