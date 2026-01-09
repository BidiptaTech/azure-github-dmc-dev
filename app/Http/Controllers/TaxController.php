<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use App\Models\User;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
    /**
     * Display a listing of taxes
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $dmcId = $this->getDmcId($user);

        if (!$dmcId) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access. DMC ID not found.');
        }

        $query = Tax::where('dmc_id', $dmcId)->orderBy('created_at', 'desc');

        // Filter by search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tax_name', 'like', '%' . $search . '%');
            });
        }

        // Filter by type
        if ($request->has('type') && $request->type != '') {
            $query->where('tax_type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('is_active', $request->status);
        }

        $taxes = $query->paginate(15);
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        
        // Get all taxes for calculate_on dropdown (ordered by creation)
        $allTaxes = Tax::where('dmc_id', $dmcId)->orderBy('created_at', 'asc')->get();

        return view('tax.index', compact('taxes', 'countries', 'dmcId', 'allTaxes'));
    }

    /**
     * Display tax settings
     */
    public function settings()
    {
        $user = Auth::user();
        $dmcId = $this->getDmcId($user);

        if (!$dmcId) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access. DMC ID not found.');
        }

        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        
        // Get all taxes for calculate_on dropdown (ordered by creation)
        $allTaxes = Tax::where('dmc_id', $dmcId)->orderBy('created_at', 'asc')->get();
        
        return view('tax.settings', compact('countries', 'dmcId', 'allTaxes'));
    }

    /**
     * Store a newly created tax
     */
    public function store(Request $request)
    {   
        $user = Auth::user();
        $dmcId = $this->getDmcId($user);

        if (!$dmcId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        // Check if DMC already has 5 taxes
        $taxCount = Tax::where('dmc_id', $dmcId)->count();
        if ($taxCount >= 5) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum 5 taxes allowed per DMC. You have reached the limit.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Maximum 5 taxes allowed per DMC. You have reached the limit.');
        }

        $validated = $request->validate([
            'tax_name' => 'required|string|max:255',
            'tax_type' => 'required|in:percentage,fixed',
            'tax_value' => 'required|numeric|min:0',
            'calculate_on' => ['required', 'string', function ($attribute, $value, $fail) use ($dmcId) {
                // Allow 'total' or numeric tax_id or 'tax_{number}' format
                if ($value === 'total') {
                    return; // Valid
                }
                
                // Check if it's a numeric tax_id or tax_{number} format
                if (is_numeric($value)) {
                    // Verify the tax exists and belongs to this DMC
                    $taxExists = Tax::where('tax_id', $value)
                                   ->where('dmc_id', $dmcId)
                                   ->exists();
                    if (!$taxExists) {
                        $fail('The selected tax does not exist.');
                    }
                } elseif (preg_match('/^tax_(\d+)$/', $value, $matches)) {
                    // Extract tax_id from 'tax_{number}' format
                    $taxId = $matches[1];
                    $taxExists = Tax::where('tax_id', $taxId)
                                   ->where('dmc_id', $dmcId)
                                   ->exists();
                    if (!$taxExists) {
                        $fail('The selected tax does not exist.');
                    }
                } else {
                    $fail('The selected calculate on is invalid.');
                }
            }],
            'description' => 'nullable|string|max:1000',
            'if_fixed' => 'nullable|string|max:1000',
        ]);

        $validated['dmc_id'] = $dmcId;
        $validated['is_active'] = 1;
        
        // Normalize calculate_on: convert 'tax_9' to '9' for consistent storage
        if (isset($validated['calculate_on']) && preg_match('/^tax_(\d+)$/', $validated['calculate_on'], $matches)) {
            $validated['calculate_on'] = $matches[1];
        }

        // Clear if_fixed when tax_type is percentage
        if ($validated['tax_type'] === 'percentage') {
            $validated['if_fixed'] = null;
        }

        $tax = Tax::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tax created successfully!',
                'tax' => $tax
            ]);
        }

        return redirect()->route('tax.index')->with('success', 'Tax created successfully!');
    }

    /**
     * Show the form for editing the specified tax
     */
    public function edit($id)
    {
        $user = Auth::user();
        $dmcId = $this->getDmcId($user);

        if (!$dmcId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $tax = Tax::where('tax_id', $id)->where('dmc_id', $dmcId)->firstOrFail();
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        
        // Get only taxes created before this tax (to prevent circular dependencies)
        $previousTaxes = Tax::where('dmc_id', $dmcId)
            ->where('created_at', '<', $tax->created_at)
            ->orderBy('created_at', 'asc')
            ->get();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'tax' => $tax,
                'countries' => $countries,
                'previousTaxes' => $previousTaxes
            ]);
        }

        return view('tax.edit', compact('tax', 'countries', 'previousTaxes'));
    }

    /**
     * Update the specified tax
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $dmcId = $this->getDmcId($user);

        if (!$dmcId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $tax = Tax::where('tax_id', $id)->where('dmc_id', $dmcId)->firstOrFail();

        $validated = $request->validate([
            'tax_name' => 'required|string|max:255',
            'tax_type' => 'required|in:percentage,fixed',
            'tax_value' => 'required|numeric|min:0',
            'calculate_on' => ['required', 'string', function ($attribute, $value, $fail) use ($dmcId, $id) {
                // Allow 'total' or numeric tax_id or 'tax_{number}' format
                if ($value === 'total') {
                    return; // Valid
                }
                
                // Check if it's a numeric tax_id or tax_{number} format
                if (is_numeric($value)) {
                    // Verify the tax exists and belongs to this DMC (and not the current tax being edited)
                    $taxExists = Tax::where('tax_id', $value)
                                   ->where('dmc_id', $dmcId)
                                   ->where('tax_id', '!=', $id)
                                   ->exists();
                    if (!$taxExists) {
                        $fail('The selected tax does not exist or is invalid.');
                    }
                } elseif (preg_match('/^tax_(\d+)$/', $value, $matches)) {
                    // Extract tax_id from 'tax_{number}' format
                    $taxId = $matches[1];
                    $taxExists = Tax::where('tax_id', $taxId)
                                   ->where('dmc_id', $dmcId)
                                   ->where('tax_id', '!=', $id)
                                   ->exists();
                    if (!$taxExists) {
                        $fail('The selected tax does not exist or is invalid.');
                    }
                } else {
                    $fail('The selected calculate on is invalid.');
                }
            }],
            'description' => 'nullable|string|max:1000',    
            'if_fixed' => 'nullable|string|max:1000',
        ]);
        
        // Normalize calculate_on: convert 'tax_9' to '9' for consistent storage
        if (isset($validated['calculate_on']) && preg_match('/^tax_(\d+)$/', $validated['calculate_on'], $matches)) {
            $validated['calculate_on'] = $matches[1];
        }

        // Clear if_fixed when tax_type is changed to percentage
        if ($validated['tax_type'] === 'percentage') {
            $validated['if_fixed'] = null;
        }

        $tax->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tax updated successfully!',
                'tax' => $tax
            ]);
        }

        return redirect()->route('tax.index')->with('success', 'Tax updated successfully!');
    }

    /**
     * Remove the specified tax
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $dmcId = $this->getDmcId($user);

        if (!$dmcId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $tax = Tax::where('tax_id', $id)->where('dmc_id', $dmcId)->firstOrFail();
        $tax->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tax deleted successfully!'
            ]);
        }

        return redirect()->route('tax.index')->with('success', 'Tax deleted successfully!');
    }

    /**
     * Toggle tax status
     */
    public function toggleStatus($id)
    {
        $user = Auth::user();
        $dmcId = $this->getDmcId($user);

        if (!$dmcId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $tax = Tax::where('tax_id', $id)->where('dmc_id', $dmcId)->firstOrFail();
        $tax->is_active = !$tax->is_active;
        $tax->save();

        return response()->json([
            'success' => true,
            'message' => 'Tax status updated successfully!',
            'is_active' => $tax->is_active
        ]);
    }

    /**
     * Get DMC ID from user
     */
    private function getDmcId($user)
    {
        // Role 11 is direct DMC
        if ($user->role_id == 11) {
            return $user->userId;
        }

        // For other DMC-related roles, traverse the hierarchy
        $dmcRoles = [33, 37, 38, 35, 74, 75, 76, 77, 78, 84, 93, 102, 111, 120, 128, 129, 130, 132, 133, 134, 135, 136, 137, 138, 139, 140];
        
        if (in_array($user->role_id, $dmcRoles)) {
            // Sales Head and similar roles
            if (in_array($user->role_id, [33, 128, 129, 130, 134, 135, 136, 138])) {
                return $user->created_by;
            }
            
            // Sales Manager roles
            if (in_array($user->role_id, [37, 12])) {
                $salesHead = User::where('userId', $user->created_by)->first();
                if ($salesHead) {
                    return $salesHead->created_by;
                }
            }
            
            // Assistant Manager roles
            if ($user->role_id == 38) {
                $salesManager = User::where('userId', $user->created_by)->first();
                if ($salesManager) {
                    $salesHead = User::where('userId', $salesManager->created_by)->first();
                    if ($salesHead) {
                        return $salesHead->created_by;
                    }
                }
            }
            
            // For operational roles
            if (in_array($user->role_id, [35, 74, 75, 76, 77, 78, 84, 93, 102, 111, 120, 132, 133, 139, 140])) {
                // Try to find DMC through created_by chain
                $currentUser = $user;
                for ($i = 0; $i < 5; $i++) {
                    if ($currentUser->created_by) {
                        $parent = User::where('userId', $currentUser->created_by)->first();
                        if ($parent && $parent->role_id == 11) {
                            return $parent->userId;
                        }
                        $currentUser = $parent;
                    }
                }
            }
        }

        return null;
    }
}
