<?php
namespace App\Http\Controllers\Api;
use Laravel\Sanctum\PersonalAccessToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Models\Agent;
use App\Models\Country;
use Auth;
use Str;
use App\Models\Setting;
use App\Models\Role;
use App\Services\CurrencyService;
use League\ISO3166\ISO3166;
use NumberFormatter;
use App\Helpers\CountryHelper;
use Illuminate\Support\Facades\Validator;
use App\Helpers\CommonHelper;

class LoginControllerApi extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function getCurrencySymbolByCode($currencyCode) {
        $formatter = new NumberFormatter('en', NumberFormatter::CURRENCY);
        
        try {
            $symbol = $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
            
            // Format 0 amount just to get the symbol from the currency code
            $formatted = $formatter->formatCurrency(0, $currencyCode);
            
            // Extract the symbol from the formatted string
            $symbol = preg_replace('/[0-9,. ]/', '', $formatted);
            
            return $symbol ?: $currencyCode; // Fallback to currency code if symbol not found
        } catch (\Exception $e) {
            return $currencyCode; // Return code if formatter fails
        }
    }
    
    public function login(Request $request)
    {
        function getCurrencySymbolByCode($currencyCode) {
            $formatter = new NumberFormatter('en', NumberFormatter::CURRENCY);
            
            try {
                $symbol = $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
                // Format 0 amount just to get the symbol from the currency code
                $formatted = $formatter->formatCurrency(0, $currencyCode);
                
                // Extract the symbol from the formatted string
                $symbol = preg_replace('/[0-9,. ]/', '', $formatted);
                
                return $symbol ?: $currencyCode; // Fallback to currency code if symbol not found
            } catch (\Exception $e) {
                return $currencyCode; // Return code if formatter fails
            }
        }
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        $email = $request->input('email');
        $password = $request->input('password');
        // $user = User::where('email', $email)->first();
        $user = Agent::where('email', $email)->first();
        $userModel = 'Agent';

        if (!$user) {
            $user = User::where('email', $email)
            ->wherein('role_id', [33,37,38])
            ->first();
            $userModel = 'User';
        }
        $user_role = $user->role_id ?? 0;
        if($userModel == 'User' && in_array($user_role, [33,37,38])){
            $userRoleId = $user->role_id;
            $userRole = Role::where('role_id', $userRoleId)->first()->name; 
            
        }else{
            $userRole = "Agent";
        }
        if(!$user){
            return response()->json(['error' => 'This email is  not registered.'], 401);
        }
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials.'], 401);
        }
        
        // Initialize variables to avoid undefined errors
        $dmc_id = null;
        $dmc_users = null;
        
        if ($user) {
            // Get the appropriate creator ID based on user model
            $creatorId = ($userModel == 'Agent') ? $user->sales_manager_dmc : $user->created_by;
            
            switch ($user->role_id) {
                case 11: // Agent is a DMC
                    $dmc_id = $user->userId; // For DMC, the user itself is the DMC
                    $dmc_users = $user; // DMC is its own reference
                    break;
                    case 33: 
                    case 128: 
                    case 129: 
                    case 130: 
                    case 134: 
                    case 135: 
                    case 136: 
                    case 138: // Sales Head
                    if ($userModel == 'User') {
                        // For SH, creator should be DMC
                        $dmc_users = User::where('userId', $creatorId)->first(); // DMC
                        if ($dmc_users && $dmc_users->role_id == 11) {
                            $dmc_id = $dmc_users->userId;
                        } else {
                            // If creator is not DMC, look for their creator
                            $superiorUser = User::where('userId', $creatorId)->first();
                            if ($superiorUser) {
                                $dmc_users = User::where('userId', $superiorUser->created_by)->first();
                                if ($dmc_users && $dmc_users->role_id == 11) {
                                    $dmc_id = $dmc_users->userId;
                                }
                            }
                        }
                    } else {
                        // Agent case - use sales_manager_dmc reference
                        $saleshead_dmc = User::where('userId', $user->sales_manager_dmc)->first();
                        if ($saleshead_dmc) {
                            $dmc_users = User::where('userId', $saleshead_dmc->created_by)->first();
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                            }
                        }
                    }
                    break;
                case 12:
                case 37: // Sales Manager
                    if ($userModel == 'User') {
                        // First, get Sales Head (creator of Sales Manager)
                        $saleshead = User::where('userId', $creatorId)->first();
                        if ($saleshead) {
                            // Then get DMC (creator of Sales Head)
                            $dmc_users = User::where('userId', $saleshead->created_by)->first();
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                            }
                        }
                    } else {
                        // Agent case - using the original code path
                        $salesmng_dmc = User::where('userId', $user->sales_manager_dmc)->first();
                        if ($salesmng_dmc) {
                            $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first();
                            if ($saleshead_dmc) {
                                $dmc_users = User::where('userId', $saleshead_dmc->created_by)->first();
                                if ($dmc_users && $dmc_users->role_id == 11) {
                                    $dmc_id = $dmc_users->userId;
                                }
                            }
                        }
                    }
                    break;
                case 38: // Assistant Manager
                    if ($userModel == 'User') {
                        // First, get Sales Manager (creator of Asst Manager)
                        $salesManager = User::where('userId', $creatorId)->first();
                        if ($salesManager) {
                            // Then get Sales Head (creator of Sales Manager)
                            $salesHead = User::where('userId', $salesManager->created_by)->first();
                            if ($salesHead) {
                                // Finally get DMC (creator of Sales Head)
                                $dmc_users = User::where('userId', $salesHead->created_by)->first();
                                if ($dmc_users && $dmc_users->role_id == 11) {
                                    $dmc_id = $dmc_users->userId;
                                }
                            }
                        }
                    } else {
                        // Original path for Agent
                        $asmng_dmc = User::where('userId', $user->sales_manager_dmc)->first();
                        if ($asmng_dmc) {
                            $salesmng_dmc = User::where('userId', $asmng_dmc->created_by)->first();
                            if ($salesmng_dmc) {
                                $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first();
                                if ($saleshead_dmc && $saleshead_dmc->role_id == 11) {
                                    $dmc_users = $saleshead_dmc;
                                    $dmc_id = $saleshead_dmc->userId;
                                }
                            }
                        }
                    }
                    break;
            }
            
            // Fallback mechanism for all roles if DMC is still not found
            if (!isset($dmc_users) || !isset($dmc_id)) {
                // Try to find DMC by traversing up the hierarchy
                $currentUser = User::where('userId', $creatorId)->first();
                $maxDepth = 5; // Prevent infinite loops by setting a maximum depth
                $depth = 0;
                
                while ($currentUser && $depth < $maxDepth) {
                    if ($currentUser->role_id == 11) {
                        $dmc_users = $currentUser;
                        $dmc_id = $currentUser->userId;
                        break;
                    }
                    $currentUser = User::where('userId', $currentUser->created_by)->first();
                    $depth++;
                }
                
                // If still not found, try the basic fallback
                if (!isset($dmc_users) || !isset($dmc_id)) {
                    $dmc_users = User::where('userId', $creatorId)->first();
                    if ($dmc_users && $dmc_users->role_id == 11) {
                        $dmc_id = $dmc_users->userId;
                    }
                }
            }
        }
        
        // Ensure $dmc_id and $dmc_users are valid
        if (!$dmc_id) {
            // Default fallback if $dmc_id is still not set
            $dmc_id = $user->userId ?? $user->agent_id ?? null;
        }
        
        $dmc = User::where('userId', $dmc_id)->first(); //For Dmc Company Name
        
        // Handle case where $dmc might be null
        $master_dmc = null;
        if ($dmc) {
            $master_dmc = User::where('userId', $dmc->master_dmc_id)->first(); //For MDMC Logo
        }
        
        $token = $user->createToken('react-login')->plainTextToken;
        $tokenId = explode('|', $token)[0];
        $hashToken = PersonalAccessToken::where('id',$tokenId)->latest()->first();
        try {
            $countryName = $request->header('user-country');
    
            if (!$countryName) {
                return response()->json(['message' => 'Country header is missing'], 400);
            }
            $iso3166 = new ISO3166();
    
            // Search country by name
            $country = collect($iso3166->all())->firstWhere('name', $countryName);
    
            if (!$country) {
                $currencyCode = 'Country not found';
            }

            $currencyCode = $country['currency'][0]; // Some countries have multiple currencies
            $symbol = getCurrencySymbolByCode($currencyCode);
            
        } catch (\Exception $e) {
            $symbol = $e->getMessage();
        }

        $setting = Setting::where('name', 'currency')->where('status', 1)->first();
        $country_wise_rate = $this->currencyService->getExchangeRate('SGD', $currencyCode);

        $inr_rate = $this->currencyService->getExchangeRate('SGD', 'INR');
        $usd_rate = $this->currencyService->getExchangeRate('SGD', 'USD');
        
        if ($country_wise_rate) {
            $exchangeRate = $country_wise_rate;
        } else {
            $exchangeRate= "Exchange rate unavailable";
        }
        $country = $country;
        $userCountry = $user->country;
        $userCountryData = [];

        if ($userCountry) {
            // Split by comma and trim whitespace
            $countries = array_map('trim', explode(',', $userCountry));
            
            foreach ($countries as $countryName) {
                $countryCode = CountryHelper::getCountryCode($countryName);
                if ($countryCode) {
                    $countryInfos = Country::where('name', $countryName)->first();
                    $agent_country_max_length = $countryInfos->max_length ?? 10;  // Default to 10 if null
                    $agent_country_min_length = $countryInfos->min_length ?? 10;  // Default to 10 if null
                    $userCountryData[] = [
                        'name' => $countryName,
                        'code' => $countryCode,
                        'country_code' => $countryInfos->country_code,
                        'contact_max_length' => $agent_country_max_length, // Will be 10 if null in database
                        'contact_min_length' => $agent_country_min_length, // Will be 10 if null in database
                    ];
                }
            }
        }

        $countryInfo = Country::where('name', $country)->first();

        $agent_country_tax = $countryInfo->tax_percentage ?? 0;
        $sgd_tax = Country::where('name', 'Singapore')->first()->tax_percentage ?? 0;
        $usd_tax = Country::where('name', 'United States')->first()->tax_percentage ?? 0;
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'agent_id' => $user->agent_id,
                'agent_company_name' => $user->company_name,
                'name' => $user->name,
                'email' => $user->email,
                'agent_address' => $user->agent_address,
                'profile_picture' => $user->agent_image ?? '',
                'logo' => $master_dmc->logo ?? '', 
                'dmc_name' => $dmc->company_name ?? '', 
                'country' => $country ?? '',
                'user_country' => !empty($userCountryData) ? $userCountryData : [['name' => '', 'code' => '']],
                'token' => $token,
                'current_exchange_rate' => $exchangeRate,
                'current_currency_code' => $currencyCode,
                'current_currency_symbol' => $symbol,
                'inr_exchange_rate' => $inr_rate,
                'inr_currency_code' => 'INR',
                'inr_currency_symbol' => '₹',
                'usd_exchange_rate' => $usd_rate,
                'usd_currency_code' => 'USD',
                'usd_currency_symbol' => '$',
                'usd_tax' => $usd_tax,
                'sgd_tax' => $sgd_tax,
                'agent_country_tax' => $agent_country_tax,
                'phone_no' => $user->phone,
                'price_hide' => $dmc_users->price_hide ?? 0,
                'user_role' => $userRole,
                'zone_on' => $dmc_users->zone_on ?? 0,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error'=> 'User not found'], 404);
        }
        $providedToken = $request->header('Authorization');
        $providedToken = Str::replaceFirst('Bearer ', '', $providedToken);
        $tokenId = Str::before($providedToken, '|');
        $token = PersonalAccessToken::where('id', $tokenId)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        // Basic validation for optional fields
        $validator = Validator::make($request->all(), [
            'phone' => 'nullable',
            'image' => 'nullable',
            'old_password' => 'nullable',
            'new_password' => 'nullable|min:6',
            'confirm_password' => 'nullable|same:new_password',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Validation error', 
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Handle password update only if old_password is provided
        if ($request->has('old_password') && !is_null($request->old_password)) {
            // Additional validation for password fields
            $passwordValidator = Validator::make($request->all(), [
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|same:new_password',
            ]);
            
            if ($passwordValidator->fails()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Password validation error', 
                    'errors' => $passwordValidator->errors()
                ], 422);
            }
            
            // Check if old password is correct
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Old password is incorrect'
                ], 400);
            }
            
            // Update password
            $user->password = Hash::make($request->new_password);
        }
        
        // Update phone if provided
        if ($request->has('phone') && !is_null($request->phone)) {
            $user->phone = $request->phone;
        }
        
        if ($request->has('agent_address') && !is_null($request->agent_address)) {
            $user->agent_address = $request->agent_address;
        }
        
        // Update image if provided
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->agent_image) {
                CommonHelper::deleteAzureImage($user->agent_image);
            }
            
            // Upload new image using CommonHelper
            $pathData = CommonHelper::image_path('file_storage', $request->file('image'));
            if (!empty($pathData['master_value'])) {
                $user->agent_image = $pathData['master_value'];
            }
        }
        
        $user->save();
        
        return response()->json([
            'success' => true, 
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }

    public function registerAgent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'salutation' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:agents',
            'country' => 'required',
            'user_country' => 'required',
            'city' => 'required',
            'agent_address' => 'required',
            'code' => 'required', // Country code
            'phone' => 'required',
            'id_card' => 'required',
            'card_number' => 'required',
            'agent_image' => 'nullable|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif',
            'image' => 'nullable|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Uploads - these can be done outside the transaction
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
        $virtualDmc = User::select('userId')->where('role_id', 20)->first();

        try {
            // Start a database transaction
            \DB::beginTransaction();
            
            // Get virtual DMC inside the transaction
            $virtualDmc = User::select('userId', 'logo', 'company_name', 'email', 'phone')
                ->where('role_id', 20)
                ->first();
                
            if (!$virtualDmc) {
                throw new \Exception('Virtual DMC not found');
            }
            
            // Check if agent is soft deleted - with a lock to prevent race conditions
            $deletedAgent = Agent::withTrashed()
                ->where('email', $request->email)
                ->lockForUpdate()
                ->first();
            
            if ($deletedAgent && $deletedAgent->trashed()) {
                // Restore and update
                $deletedAgent->restore();
                $deletedAgent->fill([
                    'salutation' => $request->salutation,
                    'name' => $request->name,
                    'company_name' => $request->company_name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'user_country' => $request->user_country,
                    'city' => $request->city,
                    'agent_address' => $request->agent_address,
                    'code' => $request->code,
                    'country' => is_array($request->country) ? implode(',', $request->country) : $request->country,
                    'id_cards' => $request->id_card,
                    'id_number' => $request->card_number,
                    'image' => $idProofImage ?? $deletedAgent->image,
                    'agent_image' => $agentImage ?? $deletedAgent->agent_image,
                    'password' => bcrypt($request->password),
                    'sales_manager_dmc' => $virtualDmc->userId,
                    'role_id' => 20,
                ]);
                
                $deletedAgent->save();
                
                // Commit the transaction
                \DB::commit();
                
                // Send email notification
                try {
                    $emailData = [
                        'salutation' => $deletedAgent->salutation,
                        'name' => $deletedAgent->name,
                        'email' => $deletedAgent->email,
                        'phone' => $deletedAgent->phone,
                        'company_name' => $deletedAgent->company_name,
                        'country' => $deletedAgent->user_country,
                        'city' => $deletedAgent->city,
                        'password' => $request->password,
                        'dmc_logo' => $virtualDmc->logo ?? 'NA',
                        'dmc_company' => $virtualDmc->company_name ?? config('app.name'),
                        'dmc_email' => $virtualDmc->email ?? 'NA',
                        'dmc_phone' => $virtualDmc->phone ?? 'NA',
                        'message_type' => 'registered',
                        'mail_settings' => (object)[
                            'support_email' => $virtualDmc->email ?? 'NA',
                            'support_phone' => $virtualDmc->phone ?? 'NA',
                            'facebook_url' => '#',
                            'twitter_url' => '#',
                            'instagram_url' => '#',
                            'linkedin_url' => '#'
                        ]
                    ];
                    
                    CommonHelper::sendEmail(
                        $deletedAgent->email, 
                        'agent_update', 
                        'Your Agent Account Has Been Restored', 
                        'Welcome back! Your agent account has been restored successfully.', 
                        $emailData
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send agent restoration email: ' . $e->getMessage());
                    // Continue with the process even if email fails
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Agent restored and updated successfully',
                    'data' => $deletedAgent
                ], 200);
            } else {
                // Generate unique agent ID with a safer approach
                $lastAgent = Agent::withTrashed()
                    ->orderBy('agent_id', 'desc')
                    ->lockForUpdate()
                    ->first();
                
                $agent_max_id = $lastAgent->agent_id ?? 1;
                $agentId = CommonHelper::createId($agent_max_id);
                
                // Double-check with a lock to ensure uniqueness
                while (Agent::where('agent_id', $agentId)->exists()) {
                    $agent_max_id++;
                    $agentId = CommonHelper::createId($agent_max_id);
                }
                
                $agent = new Agent();
                $agent->agent_id = $agentId;
                $agent->salutation = $request->salutation;
                $agent->name = $request->name;
                $agent->company_name = $request->company_name;
                $agent->phone = $request->phone;
                $agent->email = $request->email;
                $agent->user_country = $request->user_country;
                $agent->city = $request->city;
                $agent->agent_address = $request->agent_address;
                $agent->code = $request->code;
                $agent->country = is_array($request->country) ? implode(',', $request->country) : $request->country;
                $agent->id_cards = $request->id_card;
                $agent->id_number = $request->card_number;
                $agent->image = $idProofImage;
                $agent->agent_image = $agentImage;
                $agent->password = bcrypt($request->password);
                $agent->sales_manager_dmc = $virtualDmc->userId;
                $agent->role_id = 20;
                
                $agent->save();
                
                // Commit the transaction
                \DB::commit();
                
                // Send email notification
                try {
                    $emailData = [
                        'salutation' => $agent->salutation,
                        'name' => $agent->name,
                        'email' => $agent->email,
                        'phone' => $agent->phone,
                        'company_name' => $agent->company_name,
                        'country' => $agent->user_country,
                        'city' => $agent->city,
                        'password' => $request->password,
                        'dmc_logo' => $virtualDmc->logo ?? 'NA',
                        'dmc_company' => $virtualDmc->company_name ?? config('app.name'),
                        'dmc_email' => $virtualDmc->email ?? 'NA',
                        'dmc_phone' => $virtualDmc->phone ?? 'NA',
                        'message_type' => 'registered',
                        'mail_settings' => (object)[
                            'support_email' => $virtualDmc->email ?? 'NA',
                            'support_phone' => $virtualDmc->phone ?? 'NA',
                            'facebook_url' => '#',
                            'twitter_url' => '#',
                            'instagram_url' => '#',
                            'linkedin_url' => '#'
                        ]
                    ];
                    
                    CommonHelper::sendEmail(
                        $agent->email, 
                        'agent_update', 
                        'Your Agent Account Has Been Created', 
                        'Welcome to our platform! Your agent account has been created successfully.', 
                        $emailData
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send agent creation email: ' . $e->getMessage());
                    // Continue with the process even if email fails
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Agent registered successfully',
                    'data' => $agent
                ], 201);
            }
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            \DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

