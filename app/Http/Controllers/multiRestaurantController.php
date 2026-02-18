<?php

namespace App\Http\Controllers;

use App\Models\MultiRestaurant;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\CommonHelper;

class multiRestaurantController extends Controller
{
    /**
     * Show list of multi restaurant products.
     * Visible only to role_id 1, 11, 20.
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->role_id, [1, 11, 20, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138])) {
            abort(403, 'Unauthorized.');
        }

        // Base query for multi restaurant packages
        $multiRestaurantsQuery = MultiRestaurant::orderBy('created_at', 'desc');

        // For DMC users (non-admin), show only their own package (by dmc_id)
        $hasPackageForDmc = false;
        $selectedDmcId = null;
        $companyFilters = collect();

        if ((int) $user->role_id === 1) {
            // Admin: can see all packages, with optional DMC filter
            $selectedDmcId = request()->get('dmc_id');
            if (! empty($selectedDmcId)) {
                $multiRestaurantsQuery->where('dmc_id', (int) $selectedDmcId);
            }

            // Build company filter list directly from User table
            // Get all users with non-zero dmcId and company_name, deduplicated by dmcId
            $companyFilters = User::whereNotNull('dmcId')
                ->where('dmcId', '!=', 0)
                ->whereNotNull('company_name')
                ->where('company_name', '!=', '')
                ->get()
                ->groupBy('dmcId')
                ->map(function ($group) {
                    return $group->first();
                })
                ->values()
                ->sortBy('company_name');
        } else {
            // DMC or other allowed roles: restrict to their own dmc_id
            
            $dmcId = CommonHelper::getDmcId($user);
            
            $multiRestaurantsQuery->where('dmc_id', $dmcId);

            // For a DMC user, enforce single package per dmc_id (used to hide create form)
            if ((int) $user->role_id === 11 && $dmcId) {
                $hasPackageForDmc = MultiRestaurant::where('dmc_id', $dmcId)->exists();
            }
        }

        $multiRestaurants = $multiRestaurantsQuery->get();

        // Fetch restaurants based on user role for the restaurant selector
        $dmc_id_for_restaurants = $user->userId ?? null;
        if ((int) $user->role_id === 11 && $dmc_id_for_restaurants) {
            $restaurants = Restaurant::orderBy('created_at', 'desc')
                ->get()
                ->filter(function ($restaurant) use ($dmc_id_for_restaurants) {
                    return $restaurant->hasSelectedByDmc($dmc_id_for_restaurants)
                        && in_array($restaurant->status, [1, 5]);
                })
                ->values();
        } else {
            $restaurants = Restaurant::orderBy('created_at', 'desc')
                ->get()
                ->filter(fn ($restaurant) => in_array($restaurant->status, [1, 5]))
                ->values();
        }

        return view('multiResturant.list', [
            'multiRestaurants'   => $multiRestaurants,
            'restaurants'        => $restaurants,
            'hasPackageForDmc'   => $hasPackageForDmc,
            'companyFilters'     => $companyFilters,
            'selectedDmcId'      => $selectedDmcId,
        ]);
    }

    /**
     * Show create multi restaurant form.
     * Visible only to role_id 11.
     * Fetches dmc_id from user, then all active restaurants for that DMC.
     */
    public function create()
    {
        $user = Auth::user();

        if (! $user || (int) $user->role_id !== 11 && (int) $user->role_id !== 35 && (int) $user->role_id !== 78 && (int) $user->role_id !== 120 && (int) $user->role_id !== 130 && (int) $user->role_id !== 132 && (int) $user->role_id !== 133 && (int) $user->role_id !== 135 && (int) $user->role_id !== 136 && (int) $user->role_id !== 137 && (int) $user->role_id !== 138) {
            abort(403, 'Unauthorized.');
        }

        $dmc_id = CommonHelper::getDmcId($user);

        $restaurants = Restaurant::orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($restaurant) use ($dmc_id) {
                return $restaurant->hasSelectedByDmc($dmc_id)
                    && in_array($restaurant->status, [1, 5]);
            })
            ->values();

        return view('multiResturant.create', compact('restaurants'));
    }

    /**
     * Store a new multi restaurant package.
     * Saves package_name, restaurants (JSON array), price. package_id is auto-generated unique.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user || (int) $user->role_id !== 11 && (int) $user->role_id !== 35 && (int) $user->role_id !== 78 && (int) $user->role_id !== 120 && (int) $user->role_id !== 130 && (int) $user->role_id !== 132 && (int) $user->role_id !== 133 && (int) $user->role_id !== 135 && (int) $user->role_id !== 136 && (int) $user->role_id !== 137 && (int) $user->role_id !== 138) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'package_name' => 'required|string|max:255',
            'restaurants' => 'required|array',
            'restaurants.*' => 'nullable|numeric',
            // prices
            'price' => 'required|numeric|min:0', // adult price from form
            'child_price' => 'nullable|numeric|min:0',
            // meal toggles + times
            'breakfast_on' => 'required|in:0,1',
            'breakfast_start_time' => 'nullable|string|max:20',
            'breakfast_end_time' => 'nullable|string|max:20',
            'lunch_on' => 'required|in:0,1',
            'lunch_start_time' => 'nullable|string|max:20',
            'lunch_end_time' => 'nullable|string|max:20',
            'dinner_on' => 'required|in:0,1',
            'dinner_start_time' => 'nullable|string|max:20',
            'dinner_end_time' => 'nullable|string|max:20',
            // status
            'status' => 'required|in:0,1',
        ]);

        $restaurantIds = array_values(array_filter(array_map('intval', $request->input('restaurants', []))));
        if (empty($restaurantIds)) {
            return redirect()->back()->withInput()->withErrors(['restaurants' => 'Please select at least one restaurant.']);
        }

        // Build meal time strings (start-end) for varchar columns
        $breakfastTime = null;
        if ((int) $validated['breakfast_on'] === 1) {
            $bStart = $validated['breakfast_start_time'] ?? null;
            $bEnd   = $validated['breakfast_end_time'] ?? null;
            if ($bStart && $bEnd) {
                $breakfastTime = $bStart . '-' . $bEnd;
            }
        }

        $lunchTime = null;
        if ((int) $validated['lunch_on'] === 1) {
            $lStart = $validated['lunch_start_time'] ?? null;
            $lEnd   = $validated['lunch_end_time'] ?? null;
            if ($lStart && $lEnd) {
                $lunchTime = $lStart . '-' . $lEnd;
            }
        }

        $dinnerTime = null;
        if ((int) $validated['dinner_on'] === 1) {
            $dStart = $validated['dinner_start_time'] ?? null;
            $dEnd   = $validated['dinner_end_time'] ?? null;
            if ($dStart && $dEnd) {
                $dinnerTime = $dStart . '-' . $dEnd;
            }
        }

        // Enforce: for every dmc_id only one multi restaurant package can be created
        $dmcId = CommonHelper::getDmcId($user);
        if (MultiRestaurant::where('dmc_id', $dmcId)->exists()) {
            return redirect()
                ->route('multiResturant.index')
                ->with('error', 'You already have a multi restaurant package for this DMC.');
        }

        // Package ID is static MR-1 for every package as per requirement
        $packageId = 'MR-1';

        MultiRestaurant::create([
            'package_id' => $packageId,
            'package_name' => $validated['package_name'],
            'restaurants' => $restaurantIds,
            'adult_price' => (int) round((float) $validated['price']),
            'child_price' => $request->filled('child_price') ? (int) $validated['child_price'] : null,
            'breakfast' => (int) $validated['breakfast_on'],
            'breakfast_time' => $breakfastTime,
            'lunch' => (int) $validated['lunch_on'],
            'lunch_time' => $lunchTime,
            'dinner' => (int) $validated['dinner_on'],
            'dinner_time' => $dinnerTime,
            'status' => (int) $validated['status'],
            'dmc_id' => $dmcId,
        ]);

        return redirect()->route('multiResturant.index')->with('success', 'Multi restaurant package created successfully.');
    }

    /**
     * Show single multi restaurant package.
     */
    public function show($id)
    {
        if (! Auth::user() || ! in_array(Auth::user()->role_id, [1, 11, 20, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138])) {
            abort(403, 'Unauthorized.');
        }

        $id = Crypt::decrypt($id);
        $multiRestaurant = MultiRestaurant::where ('package_unique_id', $id)->first();

        return view('multiResturant.show', compact('multiRestaurant'));
    }

    /**
     * Show edit form for a multi restaurant package.
     */
    public function edit($id)
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role_id, [1, 11, 20, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138])) {
            abort(403, 'Unauthorized.');
        }

        $id = Crypt::decrypt($id);
        $multiRestaurant = MultiRestaurant::findOrFail($id);

        $dmc_id = CommonHelper::getDmcId($user);
        if ((int) $user->role_id === 11 && $dmc_id) {
            $restaurants = Restaurant::orderBy('created_at', 'desc')
                ->get()
                ->filter(function ($restaurant) use ($dmc_id) {
                    return $restaurant->hasSelectedByDmc($dmc_id)
                        && in_array($restaurant->status, [1, 5]);
                })
                ->values();
        } else {
            $restaurants = Restaurant::orderBy('created_at', 'desc')
                ->get()
                ->filter(fn ($restaurant) => in_array($restaurant->status, [1, 5]))
                ->values();
        }

        return view('multiResturant.edit', compact('multiRestaurant', 'restaurants'));
    }

    /**
     * Update a multi restaurant package.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role_id, [1, 11, 20, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138])) {
            abort(403, 'Unauthorized.');
        }

        $id = Crypt::decrypt($id);
        $multiRestaurant = MultiRestaurant::where ('package_unique_id', $id)->first();

        $validated = $request->validate([
            'package_name' => 'required|string|max:255',
            'restaurants' => 'required|array',
            'restaurants.*' => 'nullable|numeric',
            // prices
            'price' => 'required|numeric|min:0', // adult price
            'child_price' => 'nullable|numeric|min:0',
            // meal toggles + times
            'breakfast_on' => 'required|in:0,1',
            'breakfast_start_time' => 'nullable|string|max:20',
            'breakfast_end_time' => 'nullable|string|max:20',
            'lunch_on' => 'required|in:0,1',
            'lunch_start_time' => 'nullable|string|max:20',
            'lunch_end_time' => 'nullable|string|max:20',
            'dinner_on' => 'required|in:0,1',
            'dinner_start_time' => 'nullable|string|max:20',
            'dinner_end_time' => 'nullable|string|max:20',
            // status
            'status' => 'required|in:0,1',
        ]);

        $restaurantIds = array_values(array_filter(array_map('intval', $request->input('restaurants', []))));
        if (empty($restaurantIds)) {
            return redirect()->back()->withInput()->withErrors(['restaurants' => 'Please select at least one restaurant.']);
        }

        // Build meal time strings (start-end)
        $breakfastTime = null;
        if ((int) $validated['breakfast_on'] === 1) {
            $bStart = $validated['breakfast_start_time'] ?? null;
            $bEnd   = $validated['breakfast_end_time'] ?? null;
            if ($bStart && $bEnd) {
                $breakfastTime = $bStart . '-' . $bEnd;
            }
        }

        $lunchTime = null;
        if ((int) $validated['lunch_on'] === 1) {
            $lStart = $validated['lunch_start_time'] ?? null;
            $lEnd   = $validated['lunch_end_time'] ?? null;
            if ($lStart && $lEnd) {
                $lunchTime = $lStart . '-' . $lEnd;
            }
        }

        $dinnerTime = null;
        if ((int) $validated['dinner_on'] === 1) {
            $dStart = $validated['dinner_start_time'] ?? null;
            $dEnd   = $validated['dinner_end_time'] ?? null;
            if ($dStart && $dEnd) {
                $dinnerTime = $dStart . '-' . $dEnd;
            }
        }

        $multiRestaurant->update([
            'package_name' => $validated['package_name'],
            'restaurants' => $restaurantIds,
            'adult_price' => (int) round((float) $validated['price']),
            'child_price' => $request->filled('child_price') ? (int) $validated['child_price'] : null,
            'breakfast' => (int) $validated['breakfast_on'],
            'breakfast_time' => $breakfastTime,
            'lunch' => (int) $validated['lunch_on'],
            'lunch_time' => $lunchTime,
            'dinner' => (int) $validated['dinner_on'],
            'dinner_time' => $dinnerTime,
            'status' => (int) $validated['status'],
        ]);

        return redirect()->route('multiResturant.index')->with('success', 'Multi restaurant package updated successfully.');
    }

    /**
     * Soft delete multi restaurant package.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role_id, [1, 11, 20, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138])) {
            abort(403, 'Unauthorized.');
        }

        $id = Crypt::decrypt($id);
        $multiRestaurant = MultiRestaurant::where ('package_unique_id', $id)->first();
        $multiRestaurant->delete(); // Soft delete (sets deleted_at)

        return redirect()->route('multiResturant.index')->with('success', 'Multi restaurant package deleted.');
    }
}

