<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\City;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
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
        
        $user = Auth::user();
        $agents = collect(); // default empty
        $dmc_id = null;

        switch ($user->role_id) {
            case 1: // Admin
            case 20: // Virtual DMC
                $agents = Agent::where('status', 1)->get();
                break;

            case 11: // DMC
                // Step 1: Get all Sales Heads under this DMC
               
                
                // Step 4: Collect all user IDs in the hierarchy
                $dmc_id = $user->userId;
                break;

            case 33: // Sales Head
            case 128: // Sales Head
            case 129: // Sales Head
            case 130: // Sales Head
            case 134: // Sales Head
            case 135: // Sales Head
            case 136: // Sales Head
            case 138: // Sales Head
                
                $dmc_id = $user->created_by;
                break;    
            case 37: // Sales Manager
            
                $sales_head = User::where('userId', $user->created_by)->first();
                $dmc_id = $sales_head->created_by;
                break;
            case 38: // Assistant Sales Manager
                $sales_manager = User::where('userId', $user->created_by)->first();
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                $dmc_id = $sales_head->created_by;
                break;                    
            default:
                // For all other roles, get the parent DMC's agents
                $dmc_id = null;
                break;
        }

        if($dmc_id){
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
            })->get();
        }

        // For debugging
        Log::info('Agents Query', [
            'role_id' => $user->role_id,
            'user_id' => $user->userId,
            'agent_count' => $agents->count(),
            'agents' => $agents->pluck('dmc_id', 'agent_id')
        ]);

        return view('agents.index', compact('agents', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
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
        $agency = Agency::get();
        $cityCountry = Country::get();
        $country = Country::get();
        $countryCodes = Agent::countryCodes();

        return view('agents.add-agent', compact('sales_mg', 'country', 'authUserCountries', 'card', 'cityCountry', 'countryCodes', 'agency'));
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
            'email' => 'required|email',
            'user_country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'agent_address' => 'required|string',
            'code' => 'required|string|max:255',
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

        // Get current DMC ID based on role
        $currentUser = Auth::user();
        $dmc_id = null;

        if ($currentUser->role_id == 11) { // If user is DMC
            $dmc_id = $currentUser->userId;
        } else {
            // Find parent DMC
            $parentUser = User::where('userId', $currentUser->created_by)->first();
            while ($parentUser && !in_array($parentUser->role_id, [11])) {
                $parentUser = User::where('userId', $parentUser->created_by)->first();
            }
            if ($parentUser && $parentUser->role_id == 11) {
                $dmc_id = $parentUser->userId;
            }
        }
        if($currentUser->role_id == 1 || $currentUser->role_id == 2 || $currentUser->role_id == 3 || $currentUser->role_id == 4 || $currentUser->role_id == 19){
            $dmc = User::where('role_id', 20)->first();
            $dmc_id = $dmc->userId;
        }
        elseif($currentUser->role_id == 20){
            $dmc_id = $dmc->userId;
        }

        if (!$dmc_id) {
            return redirect()->back()->with('error', 'Could not determine DMC ID.');
        }

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

            // Get existing DMC IDs if any
            $existingDmcIds = [];
            if ($deletedAgent->dmc_id) {
                if (is_string($deletedAgent->dmc_id)) {
                    try {
                        $existingDmcIds = json_decode($deletedAgent->dmc_id, true) ?? [];
                    } catch (\Exception $e) {
                        $existingDmcIds = [];
                    }
                }
            }

            // Add current DMC ID if not exists
            if (!in_array($dmc_id, $existingDmcIds)) {
                $existingDmcIds[] = $dmc_id;
            }

            $deletedAgent->fill([
                'salutation' => $request->input('salutation'),
                'name' => $request->input('name'),
                'company_name' => $request->input('company_name'),
                'phone' => $request->input('phone'),
                'sales_manager_dmc' => Auth::user()->userId,
                'role_id' => Auth::user()->role_id,
                'created_by' => Auth::user()->userId,
                'dmc_id' => json_encode($existingDmcIds),
                'user_country' => $request->input('user_country'),
                'city' => $request->input('city'),
                'agent_address' => $request->input('agent_address'),
                'code' => $request->input('code'),
                'country' => is_array($request->input('country')) 
                    ? implode(',', $request->input('country')) 
                    : $request->input('country'),
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
        $agent->sales_manager_dmc = Auth::user()->userId;
        $agent->role_id = Auth::user()->role_id;
        $agent->user_country = $request->input('user_country');
        $agent->city = $request->input('city');
        $agent->agent_address = $request->input('agent_address');
        $agent->code = $request->input('code');
        $agent->country = implode(',', $request->input('country', []));
        $agent->id_cards = $request->input('id_card');
        $agent->id_number = $request->input('card_number');
        $agent->image = $idProofImage;
        $agent->agent_image = $agentImage;
        $agent->password = bcrypt($request->input('password'));
        $agent->created_by = Auth::user()->userId;
        $agent->dmc_id = json_encode([$dmc_id]); // Store as JSON array
        $agent->status = 1;
        if ($agent->save()) {
            // Send email to the agent
            try {
                $dmc_user = User::where('userId', $dmc_id)->first();

                $emailData = [
                    'salutation' => $agent->salutation,
                    'name' => $agent->name,
                    'email' => $agent->email,
                    'phone' => $agent->phone,
                    'company_name' => $agent->company_name,
                    'country' => $agent->user_country,
                    'city' => $agent->city,
                    'password' => $request->input('password'),
                    'dmc_logo' => $dmc_user->logo ?? 'NA',
                    'dmc_company' => $dmc_user->company_name ?? config('app.name'),
                    'dmc_email' => $dmc_user->email ?? 'NA',
                    'dmc_phone' => $dmc_user->phone ?? 'NA',
                    'mail_settings' => (object)[
                        'support_email' => $dmc_user->email ?? 'NA',
                        'support_phone' => $dmc_user->phone ?? 'NA',
                        'facebook_url' => '#',
                        'twitter_url' => '#',
                        'instagram_url' => '#',
                        'linkedin_url' => '#'
                    ]
                ];
                
                $result = \App\Helpers\CommonHelper::sendEmail(
                    $agent->email, 
                    'agent_creation', 
                    'Your Agent Account Has Been Created', 
                    'Welcome to our platform! Your agent account has been created successfully.', 
                    $emailData
                );
                
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send agent creation email: ' . $e->getMessage());
                // Continue with the process even if email fails
            }
            
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
        $agent_id = Crypt::decrypt($agent_id);
        $agent = Agent::where('agent_id', $agent_id)->firstOrFail();
        $sales_mg = User::where('role_id', 38)->get();

        $user = Auth::user();
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
        $agency = Agency::get();
        $country = Country::all();
        $cityCountry = Country::get();
        $countryCodes = Agent::countryCodes();

        return view('agents.edit-agent', compact('agent', 'sales_mg', 'authUserCountries', 'card', 'country', 'cityCountry', 'countryCodes', 'agency'));
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
                'agent_address' => 'required|string',
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
            return redirect()->route('agents.index')->with('error', 'Validation error: ' . $e->getMessage()); // Debugging: Show validation errors
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
        $agent->agent_address = $validated['agent_address'];
        $agent->code = $validated['code'];
        // EXISTING FIELDS
        $agent->country = implode(',', $validated['country']);
        $agent->id_cards = $validated['id_card'];
        $agent->id_number = $validated['card_number'];

        if (!empty($validated['password'])) {
            $agent->password = bcrypt($validated['password']);
        }

        if ($agent->save()) {
            try {
                $dmc_id = CommonHelper::getDmcId(Auth::user());
                $dmc_user = User::where('userId', $dmc_id)->first();

                $emailData = [
                    'salutation' => $agent->salutation,
                    'name' => $agent->name,
                    'email' => $agent->email,
                    'phone' => $agent->phone,
                    'company_name' => $agent->company_name,
                    'country' => $agent->user_country,
                    'city' => $agent->city,
                    'password' => $request->input('password'),
                    'dmc_logo' => $dmc_user->logo ?? 'NA',
                    'dmc_company' => $dmc_user->company_name ?? config('app.name'),
                    'dmc_email' => $dmc_user->email ?? 'NA',
                    'dmc_phone' => $dmc_user->phone ?? 'NA',
                    'mail_settings' => (object)[
                        'support_email' => $dmc_user->email ?? 'NA',
                        'support_phone' => $dmc_user->phone ?? 'NA',
                        'facebook_url' => '#',
                        'twitter_url' => '#',
                        'instagram_url' => '#',
                        'linkedin_url' => '#'
                    ],
                    "message_type" => "updated",
                ];
                
                $result = \App\Helpers\CommonHelper::sendEmail(
                    $agent->email, 
                    'agent_update', 
                    'Your Agent Account Has Been Updated', 
                    'Your agent account has been updated successfully!', 
                    $emailData
                );
                
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send agent creation email: ' . $e->getMessage());
                // Continue with the process even if email fails
            }
            return redirect()->route('agents.index')->with('success', 'Agent details updated successfully!');
        } else {
            return redirect()->route('agents.index')->with('error', 'Failed to update agent details.');
        }
    }

    public function updateDmcId(Request $request)
    {
        try {
            // Find the agent by ID
            $agent = Agent::where('agent_id', $request->agent_id)->first();
            
            // Initialize DMC IDs array
            $dmcIds = [];
            
            // Get current DMC IDs if they exist
            if ($agent->dmc_id) {
                // If it's a string (JSON or comma-separated), try to decode it
                if (is_string($agent->dmc_id)) {
                    try {
                        $dmcIds = json_decode($agent->dmc_id, true) ?? [];
                        if (!is_array($dmcIds)) {
                            $dmcIds = explode(',', $agent->dmc_id);
                        }
                    } catch (\Exception $e) {
                        $dmcIds = explode(',', $agent->dmc_id);
                    }
                }
                // If it's already an array
                elseif (is_array($agent->dmc_id)) {
                    $dmcIds = $agent->dmc_id;
                }
            }
            
            // Clean up the array - remove empty values and duplicates
            $dmcIds = array_filter(array_unique(array_map('trim', $dmcIds)));
            
            // Add current DMC ID if not already present
            $currentDmcId = Auth::user()->userId;
            if (!in_array($currentDmcId, $dmcIds)) {
                $dmcIds[] = $currentDmcId;
            }
            
            // Update the agent
            $agent->dmc_id = json_encode(array_values($dmcIds));
            $agent->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Agent selected successfully',
                'dmc_ids' => $dmcIds
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating agent DMC ID: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to select agent: ' . $e->getMessage()
            ], 500);
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

    public function searchAgents(Request $request)
    {
        $user = Auth::user();

        // Only DMC role can search and select agents
        if ($user->role_id != 11) {
            return response()->json([
                'success' => false,
                'message' => 'Only DMC can search and select agents',
                'agents' => []
            ]);
        }

        $query = Agent::where(function($q) use ($request) {
            if ($request->filled('agency_name')) {
                $q->where('name', 'like', '%' . $request->agency_name . '%');
            }
            if ($request->filled('agent_name')) {
                $q->where('company_name', 'like', '%' . $request->agent_name . '%');
            }
            if ($request->filled('country')) {
                $q->where('user_country', $request->country);
            }
            if ($request->filled('city')) {
                $q->where('city', $request->city);
            }
        });

        // Get agents that are not already selected by this DMC
        $agents = $query->select(
            'agent_id',
            'name',
            'company_name',
            'user_country',
            'city',
            'agent_address',
            'dmc_id'
        )->get();

        // Filter out agents that are already selected by current DMC
        $agents = $agents->filter(function($agent) use ($user) {
            $dmcIds = [];
            if ($agent->dmc_id) {
                if (is_string($agent->dmc_id)) {
                    try {
                        $dmcIds = json_decode($agent->dmc_id, true) ?? [];
                        if (!is_array($dmcIds)) {
                            $dmcIds = explode(',', $agent->dmc_id);
                        }
                    } catch (\Exception $e) {
                        $dmcIds = explode(',', $agent->dmc_id);
                    }
                } elseif (is_array($agent->dmc_id)) {
                    $dmcIds = $agent->dmc_id;
                }
            }
            return !in_array($user->userId, $dmcIds);
        })->values();

        return response()->json([
            'success' => true,
            'agents' => $agents,
            'is_dmc' => true
        ]);
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
