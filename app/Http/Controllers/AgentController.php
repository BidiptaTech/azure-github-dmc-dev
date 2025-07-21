<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Agent;
use App\Models\City;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!hasPermission('view agent')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $user = auth()->user();
        $agents = collect(); // default empty

        switch ($user->role_id) {
            case 11: // DMC
                $dmc_id = $user->userId;

                $sales_heads = User::where('created_by', $dmc_id)
                    ->whereIn('role_id', [33, 128, 129, 130, 134, 135, 136, 138])
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

                $agents = Agent::whereIn('sales_manager_dmc', $all_ids)->get();
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

                $agents = Agent::whereIn('sales_manager_dmc', $all_ids)->get();
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

                $agents = Agent::whereIn('sales_manager_dmc', $all_ids)->get();
                break;

            case 38: // Assistant Manager
            case 128:
            case 129:
            case 130:
            case 134:
            case 135:
            case 136:
            case 138:
                $agents = Agent::where('sales_manager_dmc', $user->userId)->get();
                break;

            case 1: // Admin
                $agents = Agent::get();
                break;
        }
        return view('agents.index', compact('agents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $masterDmc = null;
        $authUserCountries = [];

        // Traverse up the hierarchy to find master DMC based on user role
        if ($user->role_id == 11) { // DMC
            // Direct access to master_dmc_id
            $masterDmc = User::where('userId', $user->master_dmc_id)->first();
        } 
        else if ($user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138) { // Sales Head
            // Find parent DMC
            $parentDmc = User::where('userId', $user->created_by)
                            ->where('role_id', 11)
                            ->first();
            
            if ($parentDmc) {
                $masterDmc = User::where('userId', $parentDmc->master_dmc_id)->first();
            }
        }
        else if ($user->role_id == 12 || $user->role_id == 37) { // Sales Manager
            // Find parent Sales Head
            $parentSalesHead = User::where('userId', $user->created_by)
                                  ->where('role_id', 33)
                                  ->first();
            
            if ($parentSalesHead) {
                // Find parent DMC of Sales Head
                $parentDmc = User::where('userId', $parentSalesHead->created_by)
                                ->where('role_id', 11)
                                ->first();
                
                if ($parentDmc) {
                    $masterDmc = User::where('userId', $parentDmc->master_dmc_id)->first();
                }
            }
        }
        else if ($user->role_id == 38) { // Assistant Sales Manager
            // Find parent Sales Manager
            $parentSalesManager = User::where('userId', $user->created_by)
                                     ->whereIn('role_id', [12, 37])
                                     ->first();
            
            if ($parentSalesManager) {
                // Find parent Sales Head of Sales Manager
                $parentSalesHead = User::where('userId', $parentSalesManager->created_by)
                                      ->where('role_id', 33)
                                      ->first();
                
                if ($parentSalesHead) {
                    // Find parent DMC of Sales Head
                    $parentDmc = User::where('userId', $parentSalesHead->created_by)
                                    ->where('role_id', 11)
                                    ->first();
                    
                    if ($parentDmc) {
                        $masterDmc = User::where('userId', $parentDmc->master_dmc_id)->first();
                    }
                }
            }
        }

        // Get countries from master DMC if found
        if ($masterDmc) {
            $authUserCountries = explode(',', $masterDmc->country);
        }

        $card = Country::whereIn('name', $authUserCountries)->get(['card_type']);
        $sales_mg = User::where('role_id', 38)->get();
        $cityCountry = Country::get();
        $country = Country::get();
        $countryCodes = Agent::countryCodes();

        return view('agents.add-agent', compact('sales_mg', 'country', 'authUserCountries', 'card', 'cityCountry', 'countryCodes'));
    }


    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'phone' => 'required|numeric',
            // 'country' => 'required|array',                 // 👈 change here
            // 'country.*' => 'string|max:255',               // 👈 each selected country
            'email' => 'required|email',
            // 'country' => 'required|string|max:255',
            'user_country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'code' => 'required|string|max:255',    // 👈 change here
            'id_card' => 'required|string|max:255',
            'card_number' => 'required|string|max:255',
            'image' => 'required|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif|max:2048',
            'agent_image' => 'required|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif|max:2048',
            'password' => 'required|min:8',
        ]);
    
        $validator->after(function ($validator) use ($request) {
            $existingAgent = Agent::where('email', $request->input('email'))->first();
            if ($existingAgent && !$existingAgent->trashed()) {
                $validator->errors()->add('email', 'The email has already been taken.');
            }
        });
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        // Check if agent is soft deleted
        $deletedAgent = Agent::withTrashed()->where('email', $request->input('email'))->first();
    
        // Uploads
        $idProofImage = null;
        if ($request->hasFile('image')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('image'));
            $idProofImage = $pathData['master_value'] ?? null;
        }
    
        $agentImage = null;
        if ($request->hasFile('agent_image')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('agent_image'));
            $agentImage = $pathData['master_value'] ?? null;
        }
    
        if ($deletedAgent && $deletedAgent->trashed()) {
            // Restore and update
            $deletedAgent->restore();
            $deletedAgent->fill([
                'salutation' => $request->input('salutation'),
                'name' => $request->input('name'),
                'company_name' => $request->input('company_name'),
                'phone' => $request->input('phone'),
                'sales_manager_dmc' => auth()->user()->userId,
                'role_id' => auth()->user()->role_id,
                // 'country' => implode(',', $request->input('country')),
                // 'country' => is_array($request->input('country')) ?? implode(',', $request->input('country')),
                'user_country' => $request->input('user_country'),
                'city' => $request->input('city'),
                'code' => $request->input('code'),  
                'country' => is_array($request->input('country')) 
                                                ? implode(',', $request->input('country')) 
                                                : $request->input('country'),

                // 'country' => $request->input('country'),
                'id_cards' => $request->input('id_card'),
                'id_number' => $request->input('card_number'),
                'image' => $idProofImage,
                'agent_image' => $agentImage,
                'password' => bcrypt($request->input('password')),
            ]);
            $deletedAgent->save();
    
            return redirect()->route('agents.index')->with('success', 'Soft-deleted agent restored and updated successfully!');
        }
    
        // Create new agent
        $lastAgent = Agent::withTrashed()->orderBy('created_at', 'desc')->first();
        $agent_max_id = $lastAgent->agent_id ?? 1;
        $agentId = CommonHelper::createId($agent_max_id);
        while (Agent::where('agent_id', $agentId)->exists()) {
            $agentId = CommonHelper::createId($agentId);
        }
    
        $agent = new Agent();
        $agent->agent_id = $agentId;
        $agent->salutation = $request->input('salutation');
        $agent->name = $request->input('name');
        $agent->company_name = $request->input('company_name');
        $agent->phone = $request->input('phone');
        $agent->email = $request->input('email');
        $agent->sales_manager_dmc = auth()->user()->userId;
        $agent->role_id = auth()->user()->role_id;
        // $agent->country = $request->input('country');
        $agent->user_country = $request->input('user_country');
        $agent->city = $request->input('city');
        $agent->code = $request->input('code');
        $agent->country = implode(',', $request->input('country', []));
        $agent->id_cards = $request->input('id_card');
        $agent->id_number = $request->input('card_number');
        $agent->image = $idProofImage;
        $agent->agent_image = $agentImage;
        $agent->password = bcrypt($request->input('password'));
    
        if ($agent->save()) {
            return redirect()->route('agents.index')->with('success', 'Agent details added successfully!');
        } else {
            return redirect()->route('agents.index')->with('error', 'Failed to add agent details.');
        }
    }
    

    /**
    * Show the form for editing the specified resource.
    */
    public function edit(string $agent_id)
    {
        $agent = Agent::where('agent_id', $agent_id)->firstOrFail();
        $sales_mg = User::where('role_id', 38)->get();

        $user = auth()->user();
        $masterDmc = null;
        $authUserCountries = [];

        // Traverse up the hierarchy to find master DMC based on user role
        if ($user->role_id == 11) { // DMC
            // Direct access to master_dmc_id
            $masterDmc = User::where('userId', $user->master_dmc_id)->first();
        } 
        else if ($user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138) { // Sales Head
            // Find parent DMC
            $parentDmc = User::where('userId', $user->created_by)
                            ->where('role_id', 11)
                            ->first();
            
            if ($parentDmc) {
                $masterDmc = User::where('userId', $parentDmc->master_dmc_id)->first(); 
            }
        }
        else if ($user->role_id == 12 || $user->role_id == 37) { // Sales Manager
            // Find parent Sales Head
            $parentSalesHead = User::where('userId', $user->created_by)
                                  ->where('role_id', 33)
                                  ->first();
            
            if ($parentSalesHead) {
                // Find parent DMC of Sales Head
                $parentDmc = User::where('userId', $parentSalesHead->created_by)
                                ->where('role_id', 11)
                                ->first();
                
                if ($parentDmc) {
                    $masterDmc = User::where('userId', $parentDmc->master_dmc_id)->first();
                }
            }
        }
        else if ($user->role_id == 38) { // Assistant Sales Manager
            // Find parent Sales Manager
            $parentSalesManager = User::where('userId', $user->created_by)
                                     ->whereIn('role_id', [12, 37])
                                     ->first();
            
            if ($parentSalesManager) {
                // Find parent Sales Head of Sales Manager
                $parentSalesHead = User::where('userId', $parentSalesManager->created_by)
                                      ->where('role_id', 33)
                                      ->first();
                
                if ($parentSalesHead) {
                    // Find parent DMC of Sales Head
                    $parentDmc = User::where('userId', $parentSalesHead->created_by)
                                    ->where('role_id', 11)
                                    ->first();
                    
                    if ($parentDmc) {
                        $masterDmc = User::where('userId', $parentDmc->master_dmc_id)->first();
                    }
                }
            }
        }

        // Get countries from master DMC if found
        if ($masterDmc) {
            $authUserCountries = explode(',', $masterDmc->country);
        }

        $card = Country::whereIn('name', $authUserCountries)->get(['card_type']);
        $country = Country::all();
        $cityCountry = Country::get();
        $countryCodes = Agent::countryCodes();

        return view('agents.edit-agent', compact('agent', 'sales_mg', 'authUserCountries', 'card', 'country', 'cityCountry', 'countryCodes'));
    }


    /**
    * Show the form for editing the specified resource.
    */
    public function getSalesManagerDetails($userId)
    {
        $manager = User::where('userId', $userId)->first();

        if (!$manager) {
            return response()->json([
                'success' => false,
                'message' => 'Manager not found'
            ]);
        }

        // Fetch country using the name
        $country = Country::where('name', $manager->country)->first();

        if (!$country) {
            return response()->json([
                'success' => false,
                'message' => 'Country not found'
            ]);
        }

        // Fetch ID cards for this country
        $idCards = Country::where('name', $manager->country)->get(['card_type', 'name']);

        return response()->json([
            'success' => true,
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
            ],
            'id_cards' => $idCards,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());
        $agent = Agent::where('agent_id', $id)->firstOrFail();

        // $validated = $request->validate([
        //     'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
        //     'name' => 'required|string|max:255',
        //     'phone' => 'required|numeric',
        //     'email' => 'required|email|unique:agents,email,' . $agent->id,
        //     'sales_mg' => 'required|exists:users,userId',
        //     'country' => 'required|string|max:255',
        //     'id_card' => 'required|string|max:255',
        //     'card_number' => 'required|string|max:255',
        //     'image' => 'nullable|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif|max:2048',
        //     'agent_image' => 'nullable|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif|max:2048',
        //     'password' => 'nullable|min:8',
        // ]);

        try {
            $validated = $request->validate([
                'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
                'name' => 'required|string|max:255',
                'company_name' => 'required|string|max:255',
                'phone' => 'required|numeric',
                'email' => 'required|email|unique:agents,email,' . $agent->id,
                // NEW FIELDS
                'user_country' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'code' => 'required|string|max:255',
                // EXISTING FIELDS
                'country' => 'required|array',
                'country.*' => 'string', // each country must be a string
                'id_card' => 'required|string|max:255',
                'card_number' => 'required|string|max:255',
                'image' => 'nullable|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif',
                'agent_image' => 'nullable|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif',
                'password' => 'nullable|min:8',
            ]);
        
            // dd($validated); // Debugging: Show validated data
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors()); // Debugging: Show validation errors
        }
        

        if ($request->hasFile('image')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('image'));
            $agent->image = $pathData['master_value'] ?? $agent->image;
        }

        if ($request->hasFile('agent_image')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('agent_image'));
            $agent->agent_image = $pathData['master_value'] ?? $agent->agent_image;
        }

        $agent->salutation = $validated['salutation'];
        $agent->name = $validated['name'];
        $agent->company_name = $validated['company_name'];
        $agent->phone = $validated['phone'];
        $agent->email = $validated['email'];
        // NEW FIELDS
        $agent->user_country = $validated['user_country'];
        $agent->city = $validated['city'];
        $agent->code = $validated['code'];
        // EXISTING FIELDS
        $agent->country = implode(',', $validated['country']);
        $agent->id_cards = $validated['id_card'];
        $agent->id_number = $validated['card_number'];

        if (!empty($validated['password'])) {
            $agent->password = bcrypt($validated['password']);
        }

        if ($agent->save()) {
            return redirect()->route('agents.index')->with('success', 'Agent details updated successfully!');
        } else {
            return redirect()->route('agents.index')->with('error', 'Failed to update agent details.');
        }
    }

    public function fetchCitiesByCountry(Request $request) {
        $countryName = $request->input('country');
        
        $cities = City::where('country', $countryName)
                ->select('name', 'city_id')
                ->get();
                 
        return response()->json(['cities' => $cities]);
    }

    public function fetchCountryCode(Request $request) {
        $countryName = $request->input('country');
        
        $country = Country::where('name', $countryName)->first();
        
        if ($country) {
            return response()->json([
                'success' => true,
                'country_code' => $country->country_code
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Country not found'
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $agent = Agent::where('agent_id', $id);
        $agent->delete();

        return redirect()->route('agents.index')->with('success', 'Agent deleted successfully!');
    }
}
