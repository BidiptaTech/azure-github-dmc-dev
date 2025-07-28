<?php

namespace App\Http\Controllers\APi;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\User;
use App\Models\Agent;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getCity(Request $request)
    {
        $countryName = $request->header('country');

        $country = Country::where('name', $countryName)->first();

        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        $cities = City::where('country', $countryName)->pluck('name');

        return response()->json([
            'cities' => $cities
        ]);
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

    public function getDmcs(Request $request)
    {
        $user = auth()->user();
        $agentCreatedBy = $user->sales_manager_dmc;
        $createdByDmc = User::where('userId', $agentCreatedBy)->first();
        $agentDmcIds = $user->dmc_id;
        

        // Handle JSON array or comma-separated string
        if (is_string($agentDmcIds) && strpos($agentDmcIds, '[') === 0) {
            // It's a JSON array, decode it
            $agentDmcIds = json_decode($agentDmcIds, true);
        } else {
            // It's a comma-separated string, explode it
            $agentDmcIds = explode(',', $agentDmcIds);
        }
        
        // Ensure all values are integers
        $agentDmcIds = array_map('intval', array_filter($agentDmcIds));
        
        if (empty($agentDmcIds)) {
            return response()->json(['error' => 'No DMC IDs found'], 404);
        }

        if (count($agentDmcIds) > 1 && $createdByDmc->role_id != 20) {
            $dmcs = User::whereIn('userId', $agentDmcIds)->get();
        } 
        elseif (count($agentDmcIds) == 1 && $createdByDmc->role_id != 20) {
            $dmcs = User::where('userId', $agentDmcIds[0])->first();
        }
        elseif ($createdByDmc->role_id == 20) {
            $dmcs = User::whereIn('role_id', [11, 20])->get();
        }
        else {
            return response()->json(['error' => 'DMC not found'], 404);
        }

        if ($request->has('country')) {
            // Handle country as JSON string, array, or single string
            $countryParam = $request->country;
            
            if (is_string($countryParam) && strpos($countryParam, '[') === 0) {
                // Clean the JSON string - remove extra quotes and fix common issues
                $cleanedParam = str_replace("''", '"', $countryParam);
                $cleanedParam = str_replace("'", '"', $cleanedParam);
                
                // It's a JSON string like ["Singapore", "India"]
                $requestCountries = json_decode($cleanedParam, true);
                
                // Check if JSON decode failed
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'error' => 'Invalid country format',
                        'debug' => [
                            'originalCountryParam' => $countryParam,
                            'cleanedParam' => $cleanedParam,
                            'jsonError' => json_last_error_msg()
                        ]
                    ], 400);
                }
            } elseif (is_array($countryParam)) {
                // It's already an array
                $requestCountries = $countryParam;
            } else {
                // It's a single string
                $requestCountries = [$countryParam];
            }
            
            // Ensure we have a valid array and filter out null values
            if (!is_array($requestCountries)) {
                $requestCountries = [$requestCountries];
            }
            
            // Filter out null values
            $requestCountries = array_filter($requestCountries, function($country) {
                return $country !== null && $country !== '';
            });
            
            // If no valid countries, return error
            if (empty($requestCountries)) {
                return response()->json([
                    'error' => 'No valid countries provided',
                    'debug' => [
                        'originalCountryParam' => $countryParam,
                        'processedCountries' => $requestCountries
                    ]
                ], 400);
            }
            
            // Debug: Let's see what we're comparing
            \Log::info('Country Debug', [
                'originalCountryParam' => $countryParam,
                'requestCountries' => $requestCountries,
                'dmcsCountry' => $dmcs instanceof \Illuminate\Database\Eloquent\Collection ? $dmcs->pluck('country')->toArray() : $dmcs->country,
                'dmcsData' => $dmcs instanceof \Illuminate\Database\Eloquent\Collection ? $dmcs->toArray() : $dmcs->toArray()
            ]);
            
            // Filter DMCs by country
            if ($dmcs instanceof \Illuminate\Database\Eloquent\Collection) {
                $dmcs = $dmcs->whereIn('country', $requestCountries);
            } else {
                // If it's a single model, check if it matches the country
                if (!in_array($dmcs->country, $requestCountries)) {
                    return response()->json([
                        'error' => 'DMC not found for specified country',
                        'debug' => [
                            'dmcCountry' => $dmcs->country,
                            'requestCountries' => $requestCountries,
                            'dmcData' => $dmcs->toArray()
                        ]
                    ], 404);
                }
            }
        }

        return response()->json([
            'dmcs' => $dmcs
        ]);
    }
}
