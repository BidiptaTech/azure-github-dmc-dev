<?php

namespace App\Http\Controllers;

use App\Models\DefaultValue;
use App\Models\Hotel;
use App\Models\Restaurant;
use App\Models\Attraction;
use App\Models\Vehicle;
use App\Models\Port;
use App\Models\Guide;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\CommonHelper;

class DefaultValueController extends Controller
{
    /**
     * Resolve the DMC ID for the given user based on role hierarchy
     * This mirrors the Zone controller logic.
     */
    private function resolveDmcIdForUser(User $user)
    {
        // Direct DMC roles
        if ($user->role_id == 11 || $user->role_id == 20) {
            return $user->userId;
        }

        // Team roles directly under a DMC
        if ($user->role_id == 35 || in_array($user->role_id, [130, 132, 133, 135, 136, 137, 138])) {
            return $user->created_by;
        }

        // Roles under Product Head
        if ($user->role_id == 76 || $user->role_id == 139) {
            $productHead = User::where('userId', $user->created_by)->first();
            return $productHead ? $productHead->created_by : null;
        }

        // Roles under Product Manager → Product Head
        if ($user->role_id == 111 || $user->role_id == 140) {
            $productManager = User::where('userId', $user->created_by)->first();
            $productHead = $productManager ? User::where('userId', $productManager->created_by)->first() : null;
            return $productHead ? $productHead->created_by : null;
        }

        return null;
    }

    /**
     * Display a listing of default values.
     */
    public function index()
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);

        if (!$dmcId) {
            return redirect()->route('dashboard')->with('error', 'Access denied. DMC ID not found.');
        }

        // Get all default values for this DMC
        $defaultValues = DefaultValue::where('dmc_id', $dmcId)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Load relationships dynamically
        foreach ($defaultValues as $value) {
            switch ($value->name) {
                case 'hotel':
                    $value->load('hotel');
                    break;
                case 'restaurant':
                    $value->load('restaurant');
                    break;
                case 'attraction':
                    $value->load('attraction');
                    break;
                case 'car_private':
                case 'car_shared':
                    $value->load('vehicle');
                    break;
                case 'port':
                    $value->load('port');
                    break;
                case 'guide':
                    $value->load('guide');
                    break;
            }
        }

        // Get available types (types that haven't been set yet)
        $existingTypes = $defaultValues->pluck('name')->toArray();
        $allTypes = ['hotel', 'restaurant', 'attraction', 'car_private', 'car_shared', 'port', 'guide'];
        $availableTypes = array_diff($allTypes, $existingTypes);

        return view('default-values.index', compact('defaultValues', 'availableTypes', 'dmcId'));
    }

    /**
     * Show the form for creating a new default value.
     */
    public function create()
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);

        if (!$dmcId) {
            return redirect()->route('dashboard')->with('error', 'Access denied. DMC ID not found.');
        }

        // Get existing types for this DMC
        $existingTypes = DefaultValue::where('dmc_id', $dmcId)->pluck('name')->toArray();
        $allTypes = ['hotel', 'restaurant', 'attraction', 'car_private', 'car_shared', 'port', 'guide'];
        $availableTypes = array_diff($allTypes, $existingTypes);

        if (empty($availableTypes)) {
            return redirect()->route('default-values.index')
                ->with('error', 'All default values have been configured. You can edit existing ones.');
        }

        // Get available services based on DMC (following EnquiryFormPro pattern)
        $hotels = Hotel::whereJsonContains('dmc_id', (int) $dmcId)
            ->where('status', 1)
            ->where('is_active', 1)
            ->select('hotel_unique_id', 'name')
            ->orderBy('name')
            ->get();
            
        $restaurants = Restaurant::whereJsonContains('dmc_id', (int) $dmcId)
            ->where('status', 1)
            ->where('is_active', 1)
            ->select('restaurant_id', 'name')
            ->orderBy('name')
            ->get();
            
        $attractions = Attraction::whereJsonContains('dmc_id', (int) $dmcId)
            ->where('status', 1)
            ->where('is_active', 1)
            ->select('attraction_id', 'name')
            ->orderBy('name')
            ->get();
        
        // Get vehicles with private or both option
        // sharable: 1 = Private, 2 = Shared, 3 = Both
        $privateVehicles = Vehicle::where('dmc_id', $dmcId)
            ->where('is_available', 1)
            ->whereIn('sharable', [1, 3]) // Private or Both
            ->select('vehicle_id', 'vehicle_name', 'vehicle_type', 'sharable')
            ->orderBy('vehicle_name')
            ->get();
        
        // Get vehicles with shared or both option
        $sharedVehicles = Vehicle::where('dmc_id', $dmcId)
            ->where('is_available', 1)
            ->whereIn('sharable', [2, 3]) // Shared or Both
            ->select('vehicle_id', 'vehicle_name', 'vehicle_type', 'sharable')
            ->orderBy('vehicle_name')
            ->get();
        
        // Log vehicle counts for debugging
        \Log::info('DefaultValueController create() - Vehicles loaded', [
            'dmc_id' => $dmcId,
            'private_vehicles_count' => $privateVehicles->count(),
            'shared_vehicles_count' => $sharedVehicles->count(),
            'private_vehicles' => $privateVehicles->pluck('vehicle_name', 'vehicle_id')->toArray(),
            'shared_vehicles' => $sharedVehicles->pluck('vehicle_name', 'vehicle_id')->toArray()
        ]);
        
        // Get ports (following EnquiryFormPro pattern)
        $ports = Port::where('status', 1)
            ->select('port_id', 'port_name', 'country')
            ->orderBy('port_name')
            ->get();
        
        // Get guides for this DMC
        $guides = Guide::where('dmc_id', $dmcId)
            ->whereIn('status', [1, 3]) // Active guides
            ->select('guide_id', 'name', 'email', 'contact_no')
            ->orderBy('name')
            ->get();

        return view('default-values.create', compact(
            'availableTypes',
            'hotels',
            'restaurants',
            'attractions',
            'privateVehicles',
            'sharedVehicles',
            'ports',
            'guides',
            'dmcId'
        ));
    }

    /**
     * Store a newly created default value in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);

        if (!$dmcId) {
            return redirect()->back()
                ->with('error', 'DMC ID not found.')
                ->withInput();
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|in:hotel,restaurant,attraction,car_private,car_shared,port,guide',
            'service_id' => 'required|string',
            'status' => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if this type already exists for this DMC (active row)
        $existingDefault = DefaultValue::where('dmc_id', $dmcId)
            ->where('name', $request->name)
            ->first();

        if ($existingDefault) {
            return redirect()->back()
                ->with('error', 'Default value for ' . $request->name . ' already exists. Please edit the existing one.')
                ->withInput();
        }

        // If same service was soft-deleted, restore it and update instead of creating new
        $trashedSameService = DefaultValue::onlyTrashed()
            ->where('dmc_id', $dmcId)
            ->where('name', $request->name)
            ->where('service_id', $request->service_id)
            ->first();

        if ($trashedSameService) {
            $trashedSameService->restore();
            $trashedSameService->update([
                'service_id' => $request->service_id,
                'status' => $request->status,
            ]);

            return redirect()->route('default-values.index')
                ->with('success', 'Default value restored successfully.');
        }

        // Generate unique default_id (include soft-deleted rows so we never reuse an existing default_id)
        $maxDefaultId = DefaultValue::withTrashed()->max('default_id') ?? 0;
        $defaultId = CommonHelper::createId($maxDefaultId);

        // Create default value
        DefaultValue::create([
            'default_id' => $defaultId,
            'dmc_id' => $dmcId,
            'name' => $request->name,
            'service_id' => $request->service_id,
            'status' => $request->status,
        ]);

        return redirect()->route('default-values.index')
            ->with('success', 'Default value created successfully.');
    }

    /**
     * Show the form for editing the specified default value.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);

        if (!$dmcId) {
            return redirect()->route('dashboard')->with('error', 'Access denied. DMC ID not found.');
        }

        $defaultValueId = Crypt::decrypt($id);
        $defaultValue = DefaultValue::where('id', $defaultValueId)
            ->where('dmc_id', $dmcId)
            ->firstOrFail();

        // Get available services based on DMC and type (following EnquiryFormPro pattern)
        $hotels = Hotel::whereJsonContains('dmc_id', (int) $dmcId)
            ->where('status', 1)
            ->where('is_active', 1)
            ->select('hotel_unique_id', 'name')
            ->orderBy('name')
            ->get();
            
        $restaurants = Restaurant::whereJsonContains('dmc_id', (int) $dmcId)
            ->where('status', 1)
            ->where('is_active', 1)
            ->select('restaurant_id', 'name')
            ->orderBy('name')
            ->get();
            
        $attractions = Attraction::whereJsonContains('dmc_id', (int) $dmcId)
            ->where('status', 1)
            ->where('is_active', 1)
            ->select('attraction_id', 'name')
            ->orderBy('name')
            ->get();
        
        // Get vehicles with private or both option
        // sharable: 1 = Private, 2 = Shared, 3 = Both
        $privateVehicles = Vehicle::where('dmc_id', $dmcId)
            ->where('is_available', 1)
            ->whereIn('sharable', [1, 3]) // Private or Both
            ->select('vehicle_id', 'vehicle_name', 'vehicle_type', 'sharable')
            ->orderBy('vehicle_name')
            ->get();
        
        // Get vehicles with shared or both option
        $sharedVehicles = Vehicle::where('dmc_id', $dmcId)
            ->where('is_available', 1)
            ->whereIn('sharable', [2, 3]) // Shared or Both
            ->select('vehicle_id', 'vehicle_name', 'vehicle_type', 'sharable')
            ->orderBy('vehicle_name')
            ->get();
        
        // Log vehicle counts for debugging
        \Log::info('DefaultValueController edit() - Vehicles loaded', [
            'dmc_id' => $dmcId,
            'private_vehicles_count' => $privateVehicles->count(),
            'shared_vehicles_count' => $sharedVehicles->count()
        ]);
        
        // Get ports (following EnquiryFormPro pattern)
        $ports = Port::where('status', 1)
            ->select('port_id', 'port_name', 'country')
            ->orderBy('port_name')
            ->get();
        
        // Get guides for this DMC
        $guides = Guide::where('dmc_id', $dmcId)
            ->whereIn('status', [1, 3]) // Active guides
            ->select('guide_id', 'name', 'email', 'contact_no')
            ->orderBy('name')
            ->get();

        return view('default-values.edit', compact(
            'defaultValue',
            'hotels',
            'restaurants',
            'attractions',
            'privateVehicles',
            'sharedVehicles',
            'ports',
            'guides',
            'dmcId'
        ));
    }

    /**
     * Update the specified default value in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);

        if (!$dmcId) {
            return redirect()->back()
                ->with('error', 'DMC ID not found.')
                ->withInput();
        }

        $defaultValueId = Crypt::decrypt($id);
        $defaultValue = DefaultValue::where('id', $defaultValueId)
            ->where('dmc_id', $dmcId)
            ->firstOrFail();

        // Validate input
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|string',
            'status' => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update default value
        $defaultValue->update([
            'service_id' => $request->service_id,
            'status' => $request->status,
        ]);

        return redirect()->route('default-values.index')
            ->with('success', 'Default value updated successfully.');
    }

    /**
     * Remove the specified default value from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);

        if (!$dmcId) {
            return redirect()->back()->with('error', 'Access denied. DMC ID not found.');
        }

        $defaultValueId = Crypt::decrypt($id);
        $defaultValue = DefaultValue::where('id', $defaultValueId)
            ->where('dmc_id', $dmcId)
            ->firstOrFail();

        $defaultValue->delete();

        return redirect()->route('default-values.index')
            ->with('success', 'Default value deleted successfully.');
    }

    /**
     * Get services for AJAX requests based on type
     */
    public function getServices(Request $request)
    {
        $user = Auth::user();
        $dmcId = $this->resolveDmcIdForUser($user);
        $type = $request->type;

        if (!$dmcId || !$type) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $services = collect();

        switch ($type) {
            case 'hotel':
                $services = Hotel::whereJsonContains('dmc_id', (int) $dmcId)
                    ->where('status', 1)
                    ->where('is_active', 1)
                    ->select('hotel_unique_id', 'name')
                    ->orderBy('name')
                    ->get()
                    ->map(function($hotel) {
                        return [
                            'id' => $hotel->hotel_unique_id,
                            'name' => $hotel->name
                        ];
                    });
                break;
            case 'restaurant':
                $services = Restaurant::whereJsonContains('dmc_id', (int) $dmcId)
                    ->where('status', 1)
                    ->where('is_active', 1)
                    ->select('restaurant_id', 'name')
                    ->orderBy('name')
                    ->get()
                    ->map(function($restaurant) {
                        return [
                            'id' => $restaurant->restaurant_id,
                            'name' => $restaurant->name
                        ];
                    });
                break;
            case 'attraction':
                $services = Attraction::whereJsonContains('dmc_id', (int) $dmcId)
                    ->where('status', 1)
                    ->where('is_active', 1)
                    ->select('attraction_id', 'name')
                    ->orderBy('name')
                    ->get()
                    ->map(function($attraction) {
                        return [
                            'id' => $attraction->attraction_id,
                            'name' => $attraction->name
                        ];
                    });
                break;
            case 'car_private':
                // sharable: 1 = Private, 2 = Shared, 3 = Both
                $services = Vehicle::where('dmc_id', $dmcId)
                    ->where('is_available', 1)
                    ->whereIn('sharable', [1, 3]) // Private or Both
                    ->select('vehicle_id', 'vehicle_name', 'vehicle_type', 'sharable')
                    ->orderBy('vehicle_name')
                    ->get()
                    ->map(function($vehicle) {
                        $sharableLabel = match($vehicle->sharable) {
                            1 => 'Private',
                            2 => 'Shared',
                            3 => 'Both',
                            default => 'Unknown'
                        };
                        return [
                            'id' => $vehicle->vehicle_id,
                            'name' => $vehicle->vehicle_name . ' (' . ucfirst($vehicle->vehicle_type) . ' - ' . $sharableLabel . ')'
                        ];
                    });
                break;
            case 'car_shared':
                // sharable: 1 = Private, 2 = Shared, 3 = Both
                $services = Vehicle::where('dmc_id', $dmcId)
                    ->where('is_available', 1)
                    ->whereIn('sharable', [2, 3]) // Shared or Both
                    ->select('vehicle_id', 'vehicle_name', 'vehicle_type', 'sharable')
                    ->orderBy('vehicle_name')
                    ->get()
                    ->map(function($vehicle) {
                        $sharableLabel = match($vehicle->sharable) {
                            1 => 'Private',
                            2 => 'Shared',
                            3 => 'Both',
                            default => 'Unknown'
                        };
                        return [
                            'id' => $vehicle->vehicle_id,
                            'name' => $vehicle->vehicle_name . ' (' . ucfirst($vehicle->vehicle_type) . ' - ' . $sharableLabel . ')'
                        ];
                    });
                break;
            case 'port':
                $services = Port::where('status', 1)
                    ->select('port_id', 'port_name', 'country')
                    ->orderBy('port_name')
                    ->get()
                    ->map(function($port) {
                        return [
                            'id' => $port->port_id,
                            'name' => $port->port_name . ($port->country ? ' - ' . $port->country : '')
                        ];
                    });
                break;
            case 'guide':
                $services = Guide::where('dmc_id', $dmcId)
                    ->whereIn('status', [1, 3]) // Active guides
                    ->select('guide_id', 'name', 'email', 'contact_no')
                    ->orderBy('name')
                    ->get()
                    ->map(function($guide) {
                        return [
                            'id' => $guide->guide_id,
                            'name' => $guide->name . ($guide->email ? ' - ' . $guide->email : '')
                        ];
                    });
                break;
        }

        return response()->json($services);
    }
}

