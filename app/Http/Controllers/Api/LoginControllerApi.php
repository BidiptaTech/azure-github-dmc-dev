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
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use App\Models\Role;
use App\Services\CurrencyService;
use League\ISO3166\ISO3166;
use NumberFormatter;
use App\Helpers\CountryHelper;
use Illuminate\Support\Facades\Validator;
use App\Helpers\CommonHelper;
use App\Models\Agency;

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
    
    // public function login(Request $request)
    // {
    //     function getCurrencySymbolByCode($currencyCode) {
    //         $formatter = new NumberFormatter('en', NumberFormatter::CURRENCY);
            
    //         try {
    //             $symbol = $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
    //             // Format 0 amount just to get the symbol from the currency code
    //             $formatted = $formatter->formatCurrency(0, $currencyCode);
                
    //             // Extract the symbol from the formatted string
    //             $symbol = preg_replace('/[0-9,. ]/', '', $formatted);
                
    //             return $symbol ?: $currencyCode; // Fallback to currency code if symbol not found
    //         } catch (\Exception $e) {
    //             return $currencyCode; // Return code if formatter fails
    //         }
    //     }
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required|string|min:6',
    //     ]);
    //     $email = $request->input('email');
    //     $password = $request->input('password');
    //     // $user = User::where('email', $email)->first();
    //     $user = Agent::where('email', $email)->first();
    //     $userModel = 'Agent';

    //     if (!$user) {
    //         $user = User::where('email', $email)
    //         ->wherein('role_id', [33,37,38])
    //         ->first();
    //         $userModel = 'User';
    //     }
    //     $user_role = $user->role_id ?? 0;
    //     if($userModel == 'User' && in_array($user_role, [33,37,38])){
    //         $userRoleId = $user->role_id;
    //         $userRole = Role::where('role_id', $userRoleId)->first()->name; 
            
    //     }else{
    //         $userRole = "Agent";
    //     }
    //     if(!$user){
    //         return response()->json(['error' => 'This email is  not registered.'], 401);
    //     }

    //     if($user->role_id == 20 && $user->status == 2){
    //         return response()->json(['error' => 'Your account is not verified.'], 401);
    //     }
    //     if (!$user || !Hash::check($password, $user->password)) {
    //         return response()->json(['error' => 'Invalid credentials.'], 401);
    //     }
        
    //     // Initialize variables to avoid undefined errors
    //     $dmc_id = null;
    //     $dmc_users = null;
        
    //     if ($user) {
    //         // Get the appropriate creator ID based on user model
    //         $creatorId = ($userModel == 'Agent') ? $user->sales_manager_dmc : $user->created_by;
            
    //         switch ($user->role_id) {
    //             case 11: // Agent is a DMC
    //                 $dmc_id = $user->userId; // For DMC, the user itself is the DMC
    //                 $dmc_users = $user; // DMC is its own reference
    //                 $dmc_logo = $user->logo;
    //                 break;
    //                 case 33: 
    //                 case 128: 
    //                 case 129: 
    //                 case 130: 
    //                 case 134: 
    //                 case 135: 
    //                 case 136: 
    //                 case 138: // Sales Head
    //                 if ($userModel == 'User') {
    //                     // For SH, creator should be DMC
    //                     $dmc_users = User::where('userId', $creatorId)->first(); // DMC
    //                     if ($dmc_users && $dmc_users->role_id == 11) {
    //                         $dmc_id = $dmc_users->userId;
    //                         $dmc_logo = $dmc_users->logo;
    //                     } else {
    //                         // If creator is not DMC, look for their creator
    //                         $superiorUser = User::where('userId', $creatorId)->first();
    //                         if ($superiorUser) {
    //                             $dmc_users = User::where('userId', $superiorUser->created_by)->first();
    //                             if ($dmc_users && $dmc_users->role_id == 11) {
    //                                 $dmc_id = $dmc_users->userId;
    //                                 $dmc_logo = $dmc_users->logo;
    //                             }
    //                         }
    //                     }
    //                 } else {
    //                     // Agent case - use sales_manager_dmc reference
    //                     $saleshead_dmc = User::where('userId', $user->sales_manager_dmc)->first();
    //                     if ($saleshead_dmc) {
    //                         $dmc_users = User::where('userId', $saleshead_dmc->created_by)->first();
    //                         if ($dmc_users && $dmc_users->role_id == 11) {
    //                             $dmc_id = $dmc_users->userId;
    //                             $dmc_logo = $dmc_users->logo;
    //                         }
    //                     }
    //                 }
    //                 break;
    //             case 12:
    //             case 37: // Sales Manager
    //                 if ($userModel == 'User') {
    //                     // First, get Sales Head (creator of Sales Manager)
    //                     $saleshead = User::where('userId', $creatorId)->first();
    //                     if ($saleshead) {
    //                         // Then get DMC (creator of Sales Head)
    //                         $dmc_users = User::where('userId', $saleshead->created_by)->first();
    //                         if ($dmc_users && $dmc_users->role_id == 11) {
    //                             $dmc_id = $dmc_users->userId;
    //                             $dmc_logo = $dmc_users->logo;
    //                         }
    //                     }
    //                 } else {
    //                     // Agent case - using the original code path
    //                     $salesmng_dmc = User::where('userId', $user->sales_manager_dmc)->first();
    //                     if ($salesmng_dmc) {
    //                         $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first();
    //                         if ($saleshead_dmc) {
    //                             $dmc_users = User::where('userId', $saleshead_dmc->created_by)->first();
    //                             if ($dmc_users && $dmc_users->role_id == 11) {
    //                                 $dmc_id = $dmc_users->userId;
    //                                 $dmc_logo = $dmc_users->logo;
    //                             }
    //                         }
    //                     }
    //                 }
    //                 break;
    //             case 38: // Assistant Manager
    //                 if ($userModel == 'User') {
    //                     // First, get Sales Manager (creator of Asst Manager)
    //                     $salesManager = User::where('userId', $creatorId)->first();
    //                     if ($salesManager) {
    //                         // Then get Sales Head (creator of Sales Manager)
    //                         $salesHead = User::where('userId', $salesManager->created_by)->first();
    //                         if ($salesHead) {
    //                             // Finally get DMC (creator of Sales Head)
    //                             $dmc_users = User::where('userId', $salesHead->created_by)->first();
    //                             if ($dmc_users && $dmc_users->role_id == 11) {
    //                                 $dmc_id = $dmc_users->userId;
    //                                 $dmc_logo = $dmc_users->logo;
    //                             }
    //                         }
    //                     }
    //                 } else {
    //                     // Original path for Agent
    //                     $asmng_dmc = User::where('userId', $user->sales_manager_dmc)->first();
    //                     if ($asmng_dmc) {
    //                         $salesmng_dmc = User::where('userId', $asmng_dmc->created_by)->first();
    //                         if ($salesmng_dmc) {
    //                             $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first();
    //                             if ($saleshead_dmc && $saleshead_dmc->role_id == 11) {
    //                                 $dmc_users = $saleshead_dmc;
    //                                 $dmc_id = $saleshead_dmc->userId;
    //                                 $dmc_logo = $saleshead_dmc->logo;
    //                             }
    //                         }
    //                     }
    //                 }
    //                 break;
    //         }
            
    //         // Fallback mechanism for all roles if DMC is still not found
    //         if (!isset($dmc_users) || !isset($dmc_id)) {
    //             // Try to find DMC by traversing up the hierarchy
    //             $currentUser = User::where('userId', $creatorId)->first();
    //             $maxDepth = 5; // Prevent infinite loops by setting a maximum depth
    //             $depth = 0;
                
    //             while ($currentUser && $depth < $maxDepth) {
    //                 if ($currentUser->role_id == 11) {
    //                     $dmc_users = $currentUser;
    //                     $dmc_id = $currentUser->userId;
    //                     $dmc_logo = $currentUser->logo;
    //                     break;
    //                 }
    //                 $currentUser = User::where('userId', $currentUser->created_by)->first();
    //                 $depth++;
    //             }
                
    //             // If still not found, try the basic fallback
    //             if (!isset($dmc_users) || !isset($dmc_id)) {
    //                 $dmc_users = User::where('userId', $creatorId)->first();
    //                 if ($dmc_users && $dmc_users->role_id == 11) {
    //                     $dmc_id = $dmc_users->userId;
    //                     $dmc_logo = $dmc_users->logo;
    //                 }
    //             }
    //         }
    //     }
        
    //     // Ensure $dmc_id and $dmc_users are valid
    //     if (!$dmc_id) {
    //         // Default fallback if $dmc_id is still not set
    //         $dmc_id = $user->userId ?? $user->agent_id ?? null;
    //         $dmc_logo = $user->logo;
    //     }
        
    //     $dmc = User::where('userId', $dmc_id)->first(); //For Dmc Company Name
        
    //     // Handle case where $dmc might be null
    //     $master_dmc = null;
    //     if ($dmc) {
    //         $master_dmc = User::where('userId', $dmc->master_dmc_id)->first(); //For MDMC Logo
    //     }
        
    //     $token = $user->createToken('react-login')->plainTextToken;
    //     $tokenId = explode('|', $token)[0];
    //     $hashToken = PersonalAccessToken::where('id',$tokenId)->latest()->first();
    //     try {
    //         $countryName = $request->header('user-country');
    
    //         if (!$countryName) {
    //             return response()->json(['message' => 'Country header is missing'], 400);
    //         }
    //         $iso3166 = new ISO3166();
    
    //         // Search country by name
    //         $country = collect($iso3166->all())->firstWhere('name', $countryName);
    
    //         if (!$country) {
    //             $currencyCode = 'Country not found';
    //         }

    //         $currencyCode = $country['currency'][0]; // Some countries have multiple currencies
    //         $symbol = getCurrencySymbolByCode($currencyCode);
            
    //     } catch (\Exception $e) {
    //         $symbol = $e->getMessage();
    //     }

    //     $setting = Setting::where('name', 'currency')->where('status', 1)->first();
    //     $country_wise_rate = $this->currencyService->getExchangeRate('SGD', $currencyCode);

    //     $inr_rate = $this->currencyService->getExchangeRate('SGD', 'INR');
    //     $usd_rate = $this->currencyService->getExchangeRate('SGD', 'USD');
        
    //     if ($country_wise_rate) {
    //         $exchangeRate = $country_wise_rate;
    //     } else {
    //         $exchangeRate= "Exchange rate unavailable";
    //     }
    //     $country = $country;
    //     $userCountry = $user->country;
    //     $userCountryData = [];

    //     if ($userCountry) {
    //         // Split by comma and trim whitespace
    //         $countries = array_map('trim', explode(',', $userCountry));
            
    //         foreach ($countries as $countryName) {
    //             $countryCode = CountryHelper::getCountryCode($countryName);
    //             if ($countryCode) {
    //                 $countryInfos = Country::where('name', $countryName)->first();
    //                 $agent_country_max_length = $countryInfos->max_length ?? 10;  // Default to 10 if null
    //                 $agent_country_min_length = $countryInfos->min_length ?? 10;  // Default to 10 if null
    //                 $userCountryData[] = [
    //                     'name' => $countryName,
    //                     'code' => $countryCode,
    //                     'country_code' => $countryInfos->country_code,
    //                     'contact_max_length' => $agent_country_max_length, // Will be 10 if null in database
    //                     'contact_min_length' => $agent_country_min_length, // Will be 10 if null in database
    //                 ];
    //             }
    //         }
    //     }

    //     $countryInfo = Country::where('name', $country)->first();
        
    //     // Handle multiple DMC IDs and company names
    //     $dmc_ids = [];
    //     $dmc_company_names = [];
        
    //     if ($userModel == 'Agent' && $user->dmc_id) {
    //         // Parse the JSON array of DMC IDs from agent table
    //         // $agentDmcIds = is_string($user->dmc_id) ? json_decode($user->dmc_id, true) : $user->dmc_id;
    //         $agency = Agency::where('agency_id', $user->agency_id)->first();
    //         $agentDmcIds = $agency->dmc_id;
            
    //         if (is_array($agentDmcIds)) {
    //             $dmc_ids = $agentDmcIds;
                
    //             // Fetch company names for all DMC IDs
    //             $dmcCompanies = User::whereIn('userId', $agentDmcIds)
    //                 ->where('role_id', 11) // DMC role
    //                 ->select('userId', 'company_name')
    //                 ->get();
                
    //             foreach ($dmcCompanies as $dmcCompany) {
    //                 $dmc_company_names[] = [
    //                     'dmc_id' => $dmcCompany->userId,
    //                     'company_name' => $dmcCompany->company_name,
    //                     'logo' => $dmcCompany->logo,
    //                     'price_hide' => $dmcCompany->price_hide,
    //                     'zone_on' => $dmcCompany->zone_on,
    //                 ];
    //             }
    //         }
    //     } else {
    //         // For User model or single DMC case, use existing logic
    //         if($dmc_id){
    //             $dmc_ids = [$dmc_id];
    //             $dmc_company_names = [[
    //                 'dmc_id' => $dmc_id,
    //                 'company_name' => $dmc->company_name ?? ''
    //             ]];
    //         }
    //     }
        
    //     $agent_country_tax = $countryInfo->tax_percentage ?? 0;
    //     $sgd_tax = Country::where('name', 'Singapore')->first()->tax_percentage ?? 0;
    //     $usd_tax = Country::where('name', 'United States')->first()->tax_percentage ?? 0;
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Login successful',
    //         'user' => [
    //             'agent_id' => $user->agent_id,
    //             'agent_company_name' => $user->company_name,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //             'agent_address' => $user->agent_address,
    //             'profile_picture' => $user->image ?? '',
    //             'logo' => $master_dmc->logo ?? '', 
    //             'dmc_logo' => $dmc_logo ?? '',
    //             'agency_logo' => $user->agent_image ?? '',
    //             'dmc_name' => $dmc->company_name ?? '', 
    //             'country' => $country ?? '',
    //             'user_country' => !empty($userCountryData) ? $userCountryData : [['name' => '', 'code' => '']],
    //             'token' => $token,
    //             'current_exchange_rate' => round($exchangeRate, 2),
    //             'current_currency_code' => $currencyCode,
    //             'current_currency_symbol' => $symbol,
    //             'inr_exchange_rate' => round($inr_rate, 2),
    //             'inr_currency_code' => 'INR',
    //             'inr_currency_symbol' => '₹',
    //             'usd_exchange_rate' => round($usd_rate, 2),
    //             'usd_currency_code' => 'USD',
    //             'usd_currency_symbol' => '$',
    //             'usd_tax' => round($usd_tax, 2),
    //             'sgd_tax' => round($sgd_tax, 2),
    //             'agent_country_tax' => round($agent_country_tax, 2),
    //             'phone_no' => $user->phone,
    //             'price_hide' => $dmc_users->price_hide ?? 0,
    //             'user_role' => $userRole,
    //             'dmc_id' => $dmc_id ?? '',
    //             'dmc_company_name' => $dmc->company_name ?? '',
    //             'dmc_ids' => $dmc_ids,
    //             'dmc_companies' => $dmc_company_names,
    //             'zone_on' => $dmc_users->zone_on ?? 0,
    //         ],
    //     ]);
    // }

    public function login(Request $request)
    {
        function getCurrencySymbolByCode($currencyCode) {
            $formatter = new NumberFormatter('en', NumberFormatter::CURRENCY);
            
            try {
                $symbol = $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
                $formatted = $formatter->formatCurrency(0, $currencyCode);
                $symbol = preg_replace('/[0-9,. ]/', '', $formatted);
                return $symbol ?: $currencyCode;
            } catch (\Exception $e) {
                return $currencyCode;
            }
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        $user = Agent::where('email', $email)->first();
        $userModel = 'Agent';

        if (!$user) {
            $user = User::where('email', $email)
                ->whereIn('role_id', [33,37,38,34,124,125]) // added new roles
                ->first();
            $userModel = 'User';
        }

        $user_role = $user->role_id ?? 0;
        if ($userModel == 'User' && in_array($user_role, [33,37,38,34,124,125])) {
            $userRoleId = $user->role_id;
            $userRole = Role::where('role_id', $userRoleId)->first()->name; 
        } else {
            $userRole = "Agent";
        }

        if (!$user) {
            return response()->json(['error' => 'This email is not registered.'], 401);
        }

        if ($user->role_id == 20 && $user->status == 2) {
            return response()->json(['error' => 'Your account is not verified.'], 401);
        }
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials.'], 401);
        }

        $dmc_id = null;
        $dmc_users = null;

        if ($user) {
            $creatorId = ($userModel == 'Agent') ? $user->sales_manager_dmc : $user->created_by;

            switch ($user->role_id) {
                case 11: // DMC
                    $dmc_id = $user->userId;
                    $dmc_users = $user;
                    $dmc_logo = $user->logo;
                    break;

                case 33: case 128: case 129: case 130: 
                case 134: case 135: case 136: case 138: // Sales Head
                    if ($userModel == 'User') {
                        $dmc_users = User::where('userId', $creatorId)->first();
                        if ($dmc_users && $dmc_users->role_id == 11) {
                            $dmc_id = $dmc_users->userId;
                            $dmc_logo = $dmc_users->logo;
                        } else {
                            $superiorUser = User::where('userId', $creatorId)->first();
                            if ($superiorUser) {
                                $dmc_users = User::where('userId', $superiorUser->created_by)->first();
                                if ($dmc_users && $dmc_users->role_id == 11) {
                                    $dmc_id = $dmc_users->userId;
                                    $dmc_logo = $dmc_users->logo;
                                }
                            }
                        }
                    } else {
                        $saleshead_dmc = User::where('userId', $user->sales_manager_dmc)->first();
                        if ($saleshead_dmc) {
                            $dmc_users = User::where('userId', $saleshead_dmc->created_by)->first();
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                                $dmc_logo = $dmc_users->logo;
                            }
                        }
                    }
                    break;

                case 12: case 37: // Sales Manager
                case 34: case 124: case 125: // Operational Manager roles
                    if ($userModel == 'User') {
                        $saleshead = User::where('userId', $creatorId)->first();
                        if ($saleshead) {
                            $dmc_users = User::where('userId', $saleshead->created_by)->first();
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                                $dmc_logo = $dmc_users->logo;
                            }
                        }
                    } else {
                        $salesmng_dmc = User::where('userId', $user->sales_manager_dmc)->first();
                        if ($salesmng_dmc) {
                            $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first();
                            if ($saleshead_dmc) {
                                $dmc_users = User::where('userId', $saleshead_dmc->created_by)->first();
                                if ($dmc_users && $dmc_users->role_id == 11) {
                                    $dmc_id = $dmc_users->userId;
                                    $dmc_logo = $dmc_users->logo;
                                }
                            }
                        }
                    }
                    break;

                case 38: // Assistant Manager
                    if ($userModel == 'User') {
                        $salesManager = User::where('userId', $creatorId)->first();
                        if ($salesManager) {
                            $salesHead = User::where('userId', $salesManager->created_by)->first();
                            if ($salesHead) {
                                $dmc_users = User::where('userId', $salesHead->created_by)->first();
                                if ($dmc_users && $dmc_users->role_id == 11) {
                                    $dmc_id = $dmc_users->userId;
                                    $dmc_logo = $dmc_users->logo;
                                }
                            }
                        }
                    } else {
                        $asmng_dmc = User::where('userId', $user->sales_manager_dmc)->first();
                        if ($asmng_dmc) {
                            $salesmng_dmc = User::where('userId', $asmng_dmc->created_by)->first();
                            if ($salesmng_dmc) {
                                $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first();
                                if ($saleshead_dmc && $saleshead_dmc->role_id == 11) {
                                    $dmc_users = $saleshead_dmc;
                                    $dmc_id = $saleshead_dmc->userId;
                                    $dmc_logo = $saleshead_dmc->logo;
                                }
                            }
                        }
                    }
                    break;
            }

            // Fallback DMC traversal
            if (!isset($dmc_users) || !isset($dmc_id)) {
                $currentUser = User::where('userId', $creatorId)->first();
                $maxDepth = 5; $depth = 0;
                while ($currentUser && $depth < $maxDepth) {
                    if ($currentUser->role_id == 11) {
                        $dmc_users = $currentUser;
                        $dmc_id = $currentUser->userId;
                        $dmc_logo = $currentUser->logo;
                        break;
                    }
                    $currentUser = User::where('userId', $currentUser->created_by)->first();
                    $depth++;
                }
            }
        }

        if (!$dmc_id) {
            $dmc_id = $user->userId ?? $user->agent_id ?? null;
            $dmc_logo = $user->logo;
        }

        $dmc = User::where('userId', $dmc_id)->first();
        $master_dmc = $dmc ? User::where('userId', $dmc->master_dmc_id)->first() : null;

        $token = $user->createToken('react-login')->plainTextToken;
        $tokenId = explode('|', $token)[0];
        $hashToken = PersonalAccessToken::where('id',$tokenId)->latest()->first();

        try {
            $countryName = $request->header('user-country');
            if (!$countryName) {
                return response()->json(['message' => 'Country header is missing'], 400);
            }
            $iso3166 = new ISO3166();
            $country = collect($iso3166->all())->firstWhere('name', $countryName);
            if (!$country) {
                $currencyCode = 'Country not found';
            }
            $currencyCode = $country['currency'][0];
            $symbol = getCurrencySymbolByCode($currencyCode);
        } catch (\Exception $e) {
            $symbol = $e->getMessage();
        }

        $setting = Setting::where('name', 'currency')->where('status', 1)->first();
        $country_wise_rate = $this->currencyService->getExchangeRate('SGD', $currencyCode);
        $inr_rate = $this->currencyService->getExchangeRate('SGD', 'INR');
        $usd_rate = $this->currencyService->getExchangeRate('SGD', 'USD');
        $exchangeRate = $country_wise_rate ?: "Exchange rate unavailable";

        $userCountry = $user->country;
        $userCountryData = [];
        if ($userCountry) {
            $countries = array_map('trim', explode(',', $userCountry));
            foreach ($countries as $countryName) {
                $countryCode = CountryHelper::getCountryCode($countryName);
                if ($countryCode) {
                    $countryInfos = Country::where('name', $countryName)->first();
                    $agent_country_max_length = $countryInfos->max_length ?? 10;
                    $agent_country_min_length = $countryInfos->min_length ?? 10;
                    $userCountryData[] = [
                        'name' => $countryName,
                        'code' => $countryCode,
                        'country_code' => $countryInfos->country_code,
                        'contact_max_length' => $agent_country_max_length,
                        'contact_min_length' => $agent_country_min_length,
                    ];
                }
            }
        }

        $countryInfo = Country::where('name', $country)->first();

        $dmc_ids = [];
        $dmc_company_names = [];
        if ($userModel == 'Agent' && $user->dmc_id) {
            $agency = Agency::where('agency_id', $user->agency_id)->first();
            $agentDmcIds = $agency->dmc_id;
            if (is_array($agentDmcIds)) {
                $dmc_ids = $agentDmcIds;
                $dmcCompanies = User::whereIn('userId', $agentDmcIds)
                    ->where('role_id', 11)
                    ->select('userId', 'company_name', 'logo', 'price_hide', 'zone_on')
                    ->get();
                foreach ($dmcCompanies as $dmcCompany) {
                    $dmc_company_names[] = [
                        'dmc_id' => $dmcCompany->userId,
                        'company_name' => $dmcCompany->company_name,
                        'logo' => $dmcCompany->logo,
                        'price_hide' => $dmcCompany->price_hide,
                        'zone_on' => $dmcCompany->zone_on,
                    ];
                }
            }
        } else {
            if ($dmc_id) {
                $dmc_ids = [$dmc_id];
                $dmc_company_names = [[
                    'dmc_id' => $dmc_id,
                    'company_name' => $dmc->company_name ?? ''
                ]];
            }
        }

        $agent_country_tax = $countryInfo->tax_percentage ?? 0;
        $sgd_tax = Country::where('name', 'Singapore')->first()->tax_percentage ?? 0;
        $usd_tax = Country::where('name', 'United States')->first()->tax_percentage ?? 0;

        $agency = Agency::where('agency_id', $user->agency_id)->first();
        $agency_logo = $agency->logo ?? '';


        $global_countries = Country::select([
            'id',
            'name',
            'country_code', 
            'currency',
            'card_type',
            'min_length',
            'max_length',
            'header_pdf',  
            'footer_pdf',
            'is_active',
            'tax_percentage'
        ])->where('is_active', 1)->get();
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'agent_id' => $user->agent_id,
                'agent_company_name' => $user->company_name,
                'name' => $user->name,
                'email' => $user->email,
                'agent_address' => $user->agent_address,
                'profile_picture' => $user->image ?? '',
                'logo' => $master_dmc->logo ?? '',
                'dmc_logo' => $dmc_logo ?? '',
                'agency_logo' => $agency_logo ?? '',
                'dmc_name' => $dmc->company_name ?? '',
                'country' => $country ?? '',
                'user_country' => !empty($userCountryData) ? $userCountryData : [['name' => '', 'code' => '']],
                'token' => $token,
                'current_exchange_rate' => round($exchangeRate, 2),
                'current_currency_code' => $currencyCode,
                'current_currency_symbol' => $symbol,
                'inr_exchange_rate' => round($inr_rate, 2),
                'inr_currency_code' => 'INR',
                'inr_currency_symbol' => '₹',
                'usd_exchange_rate' => round($usd_rate, 2),
                'usd_currency_code' => 'USD',
                'usd_currency_symbol' => '$',
                'usd_tax' => round($usd_tax, 2),
                'sgd_tax' => round($sgd_tax, 2),
                'agent_country_tax' => round($agent_country_tax, 2),
                'phone_no' => $user->phone,
                'price_hide' => $dmc_users->price_hide ?? 0,
                'user_role' => $userRole,
                'dmc_id' => $dmc_id ?? '',
                'dmc_company_name' => $dmc->company_name ?? '',
                'dmc_ids' => $dmc_ids,
                'dmc_companies' => $dmc_company_names,
                'zone_on' => $dmc_users->zone_on ?? 0,
                'global_countries' => $global_countries,
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
            if ($user->image) {
                CommonHelper::deleteAzureImage($user->image);
            }
            
            // Upload new image using CommonHelper
            $pathData = CommonHelper::image_path('file_storage', $request->file('image'));
            if (!empty($pathData['master_value'])) {
                $user->image = $pathData['master_value'];
            }
        }
        
        $user->save();
        
        return response()->json([
            'success' => true, 
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }

    public function sendOtpRegistration(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'salutation' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:agents',
            'country' => 'required',
            'user_country' => 'required',
            'city' => 'required',
            'agent_address' => 'required',
            'code' => 'required',
            'phone' => 'required',
            'id_card' => 'required',
            'card_number' => 'required',
            'password' => 'required|min:8',
            'image' => 'nullable|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif',
            'agent_image' => 'nullable|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Get all registration data
        $registrationData = $request->all();
        
        // Process image files and generate Azure URLs
        // Process ID proof image
        if ($request->hasFile('image')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('image'));
            if (!empty($pathData['master_value'])) {
                $registrationData['image'] = $pathData['master_value'];
            }
        }
        
        // Process agent image
        if ($request->hasFile('agent_image')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('agent_image'));
            if (!empty($pathData['master_value'])) {
                $registrationData['agent_image'] = $pathData['master_value'];
            }
        }

        // Generate OTP and store data in database with processed image URLs
        $otpVerification = OtpVerification::generateFor(
            $request->email, 
            $registrationData, 
            10 // expire after 10 minutes
        );
        
        // Prepare email data
        $emailData = [
            'salutation' => $request->salutation,
            'name' => $request->name,
            'email' => $request->email,
            'otp' => $otpVerification->otp,
            'message_type' => 'otp',
            'mail_settings' => (object)[
                'support_email' => 'support@example.com',
                'support_phone' => '+123456789',
                'facebook_url' => '#',
                'twitter_url' => '#',
                'instagram_url' => '#',
                'linkedin_url' => '#'
            ]
        ];
        
        // Send OTP via email
        try {
            $sendEmail = CommonHelper::sendEmail(
                $request->email, 
                'otp_verification', 
                'Your OTP for Registration', 
                'Your OTP code is: ' . $otpVerification->otp, 
                $emailData
            );
            
            return response()->json([
                'success' => true, 
                'message' => 'OTP sent successfully to your email',
                'email' => $request->email
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send OTP email: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|numeric|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $email = $request->email;
        $submittedOtp = $request->otp;
        
        // Verify OTP using our model
        $verification = OtpVerification::verify($email, $submittedOtp);
        
        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP or OTP expired. Please request a new OTP.'
            ], 400);
        }
        
        // Get registration data from verification record
        $registrationData = $verification->registration_data;
        
        // Handle JSON fields like dmc_id to ensure proper format
        if (isset($registrationData['dmc_id'])) {
            if (is_string($registrationData['dmc_id'])) {
                // Attempt to decode if it's a JSON string
                $decoded = json_decode($registrationData['dmc_id'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $registrationData['dmc_id'] = $decoded;
                }
            }
        }
        
        // Create a new request with the registration data and include a flag for OTP verification
        $registrationRequest = new Request($registrationData);
        $registrationRequest->setMethod('POST');
        $registrationRequest->headers->set('Content-Type', 'application/json');
        
        // Add a flag to indicate this request is coming from OTP verification
        $registrationRequest->merge(['from_otp_verification' => true]);
        
        // Forward to registerAgent method
        return $this->registerAgent($registrationRequest);
    }

    public function registerAgent(Request $request)
    {
        // Check if request is coming from OTP verification
        $fromOtpVerification = $request->has('from_otp_verification') && $request->from_otp_verification === true;
        
        // First check if email exists in agents table (including soft deleted records)
        $existingAgent = Agent::withTrashed()
            ->where('email', $request->email)
            ->first();

        if ($existingAgent) {
            // Clean up any OTP verifications for this email
            \DB::table('otp_verifications')->where('email', $request->email)->delete();
            
            return response()->json([
                'success' => false,
                'message' => 'This email is already registered with us. Please use a different email address.',
                'is_deleted' => $existingAgent->trashed()
            ], 422);
        }
        
        // Define validation rules
        $validationRules = [
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
            'password' => 'required|min:8',
        ];
        
        // Only require file uploads if not coming from OTP verification
        if (!$fromOtpVerification) {
            $validationRules['id_card'] = 'required';
            $validationRules['card_number'] = 'required';
            $validationRules['agent_image'] = 'nullable|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif';
            $validationRules['image'] = 'nullable|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif';
        } else {
            // For OTP verification, make file fields optional
            $validationRules['id_card'] = 'nullable';
            $validationRules['card_number'] = 'nullable';
        }
        
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Uploads - these can be done outside the transaction
        // $idProofImage = null;
        // if ($request->hasFile('image')) {
        //     $pathData = CommonHelper::image_path('file_storage', $request->file('image'));
        //     $idProofImage = $pathData['master_value'] ?? null;
        // }
            
        //     $agentImage = null;
        //     if ($request->hasFile('agent_image')) {
        //         $pathData = CommonHelper::image_path('file_storage', $request->file('agent_image'));
        //         $agentImage = $pathData['master_value'] ?? null;
        //     }
        $virtualDmc = User::select('userId')->where('role_id', 20)->first();

        try {
            // Start a database transaction
            DB::beginTransaction();
            
            // Get virtual DMC inside the transaction
            $virtualDmc = User::select('userId', 'logo', 'company_name', 'email', 'phone')
                ->where('role_id', 20)
                ->first();
                
                if (!$virtualDmc) {
                    throw new \Exception('Virtual DMC not found. Please contact to admin!');
                }
            
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
            $agent->image = $request->image;
            $agent->agent_image = $request->agent_image;
            $agent->password = bcrypt($request->password);
            $agent->sales_manager_dmc = $virtualDmc->userId;
            $agent->role_id = 20;
            $agent->dmc_id = json_encode([$virtualDmc->userId]);
            $agent->status = 2;
            
            $agent->save();
            
            // Clean up any OTP verifications for this email
            DB::table('otp_verifications')->where('email', $request->email)->delete();
            
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

