<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MiscellaneousItem;
use App\Models\MiscellaneousPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MiscellaneousItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = MiscellaneousItem::withCount('prices')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.miscellaneous.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.miscellaneous.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|boolean'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'misc_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('miscellaneous', $imageName, 'public');
            $validated['image'] = $imagePath;
        }

        $item = MiscellaneousItem::create($validated);

        return redirect()
            ->route('miscellaneous.index')
            ->with('success', 'Miscellaneous item created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = MiscellaneousItem::with('prices')->findOrFail($id);
        return view('admin.miscellaneous.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = MiscellaneousItem::findOrFail($id);
        return view('admin.miscellaneous.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = MiscellaneousItem::findOrFail($id);

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|boolean'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            $image = $request->file('image');
            $imageName = 'misc_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('miscellaneous', $imageName, 'public');
            $validated['image'] = $imagePath;
        }

        $item->update($validated);

        return redirect()
            ->route('miscellaneous.index')
            ->with('success', 'Miscellaneous item updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = MiscellaneousItem::findOrFail($id);
        
        // Delete image if exists
        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return redirect()
            ->route('miscellaneous.index')
            ->with('success', 'Miscellaneous item deleted successfully!');
    }

    /**
     * DMC Miscellaneous Selection Page (Like Restaurant Selection)
     */
    public function dmcMiscellaneousSelection(Request $request)
    {
        $user = auth()->user();
        $allowedRoles = [11, 35, 77, 78, 84, 120, 130, 132, 133, 135, 136, 137, 138, 139, 140];
        
        if (!in_array($user->role_id, $allowedRoles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        // Get DMC ID based on role
        if ($user->role_id == 11) {
            $dmc_id = $user->userId;
        } else if (in_array($user->role_id, [35, 77, 78, 84, 130, 132, 133, 135, 136, 137, 138])) {
            // For sub-users, get the DMC ID from created_by
            $dmc_id = $user->created_by;
        } else {
            $dmc_id = $user->userId;
        }

        \Log::info('dmcMiscellaneousSelection - DMC ID', [
            'dmc_id' => $dmc_id,
            'user_id' => $user->userId,
            'role_id' => $user->role_id
        ]);

        // Get all selected items for this DMC
        $selectedItems = MiscellaneousPrice::where('dmc_id', $dmc_id)
            ->where('status', 1)
            ->with('item')
            ->get()
            ->filter(function($price) {
                return $price->item !== null; // Filter out prices with deleted items
            })
            ->map(function($price) {
                $item = $price->item;
                $item->adult_price = $price->adult_price;
                $item->child_price = $price->child_price;
                $item->infant_price = $price->infant_price;
                $item->price_id = $price->id;
                return $item;
            });

        // Get available items (not selected by this DMC)
        $selectedItemIds = $selectedItems->pluck('mis_id')->toArray();
        $availableItems = MiscellaneousItem::active()
            ->whereNotIn('mis_id', $selectedItemIds)
            ->orderBy('item_name', 'asc')
            ->get();

        return view('services.miscellaneous', compact('availableItems', 'selectedItems', 'dmc_id'));
    }

    /**
     * Update DMC Miscellaneous Selection
     */
    public function updateDmcMiscellaneous(Request $request)
    {
        $user = auth()->user();
        $allowedRoles = [11, 35, 77, 78, 84, 120, 130, 132, 133, 135, 136, 137, 138, 139, 140];
        
        if (!in_array($user->role_id, $allowedRoles)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        // Get DMC ID
        if ($user->role_id == 11) {
            $dmc_id = $user->userId;
        } else if (in_array($user->role_id, [35, 77, 78, 84, 130, 132, 133, 135, 136, 137, 138])) {
            // For sub-users, get the DMC ID from created_by
            $dmc_id = $user->created_by;
        } else {
            $dmc_id = $user->userId;
        }

        // Handle selected items and prices
        if ($request->has('selected_items')) {
            foreach ($request->selected_items as $itemId => $data) {
                MiscellaneousPrice::updateOrCreate(
                    ['mis_id' => $itemId, 'dmc_id' => $dmc_id],
                    [
                        'adult_price' => $data['adult_price'] ?? 0,
                        'child_price' => $data['child_price'] ?? 0,
                        'infant_price' => $data['infant_price'] ?? 0,
                        'adult_cost' => 0,  // Cost fields not required
                        'child_cost' => 0,
                        'infant_cost' => 0,
                        'status' => 1
                    ]
                );
            }
        }

        // Remove unselected items
        if ($request->has('removed_items')) {
            MiscellaneousPrice::where('dmc_id', $dmc_id)
                ->whereIn('mis_id', $request->removed_items)
                ->delete();
        }

        return redirect()->back()->with('success', 'Miscellaneous items updated successfully!');
    }

    /**
     * Select Individual Miscellaneous Item (AJAX)
     */
    public function selectMiscellaneous(Request $request)
    {
        try {
            \Log::info('selectMiscellaneous called', [
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            $user = auth()->user();
            
            if (!$user) {
                \Log::error('User not authenticated');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $allowedRoles = [11, 35, 77, 78, 84, 120, 130, 132, 133, 135, 136, 137, 138, 139, 140];
            
            if (!in_array($user->role_id, $allowedRoles)) {
                \Log::error('User does not have permission', ['role_id' => $user->role_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to perform this action.'
                ], 403);
            }

            // Get DMC ID
            if ($user->role_id == 11) {
                $dmc_id = $user->userId;
            } else if (in_array($user->role_id, [35, 77, 78, 84, 130, 132, 133, 135, 136, 137, 138])) {
                // For sub-users, get the DMC ID from created_by
                $dmc_id = $user->created_by;
            } else {
                $dmc_id = $user->userId;
            }

            \Log::info('DMC ID determined', [
                'dmc_id' => $dmc_id,
                'user_id' => $user->userId,
                'role_id' => $user->role_id,
                'created_by' => $user->created_by ?? 'null'
            ]);

            $itemId = $request->input('item_id');
            
            if (!$itemId) {
                \Log::error('Item ID not provided');
                return response()->json([
                    'success' => false,
                    'message' => 'Item ID is required'
                ], 400);
            }

            // Check if item exists
            $item = MiscellaneousItem::find($itemId);
            if (!$item) {
                \Log::error('Item not found', ['item_id' => $itemId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }
            
            $price = MiscellaneousPrice::updateOrCreate(
                ['mis_id' => $itemId, 'dmc_id' => $dmc_id],
                [
                    'adult_price' => $request->adult_price ?? 0,
                    'child_price' => $request->child_price ?? 0,
                    'infant_price' => $request->infant_price ?? 0,
                    'adult_cost' => 0,  // Cost fields not required
                    'child_cost' => 0,
                    'infant_cost' => 0,
                    'status' => 1
                ]
            );

            \Log::info('Item added successfully', [
                'item_id' => $itemId,
                'dmc_id' => $dmc_id,
                'price_id' => $price->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Miscellaneous item added successfully!',
                'data' => [
                    'item_id' => $itemId,
                    'dmc_id' => $dmc_id,
                    'price_id' => $price->id
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in selectMiscellaneous', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error adding item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove Miscellaneous Item (AJAX)
     */
    public function removeMiscellaneous(Request $request)
    {
        try {
            $user = auth()->user();
            $allowedRoles = [11, 35, 77, 78, 84, 120, 130, 132, 133, 135, 136, 137, 138, 139, 140];
            
            if (!in_array($user->role_id, $allowedRoles)) {
                abort(403, 'You do not have permission to perform this action.');
            }

            // Get DMC ID
            if ($user->role_id == 11) {
                $dmc_id = $user->userId;
            } else if (in_array($user->role_id, [35, 77, 78, 84, 130, 132, 133, 135, 136, 137, 138])) {
                // For sub-users, get the DMC ID from created_by
                $dmc_id = $user->created_by;
            } else {
                $dmc_id = $user->userId;
            }

            $itemId = $request->input('item_id');
            
            MiscellaneousPrice::where('mis_id', $itemId)
                ->where('dmc_id', $dmc_id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Miscellaneous item removed successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Items for DMC (API for Enquiry Pro Form)
     */
    public function getItemsForDmc($dmcId = null)
    {
        if (!$dmcId) {
            $user = auth()->user();
            if ($user->role_id == 11) {
                $dmcId = $user->userId;
            } else if (in_array($user->role_id, [35, 77, 78, 84, 130, 132, 133, 135, 136, 137, 138])) {
                // For sub-users, get the DMC ID from created_by
                $dmcId = $user->created_by;
            } else {
                $dmcId = $user->userId;
            }
        }

        $items = MiscellaneousItem::active()
            ->with(['priceForDmc' => function($query) use ($dmcId) {
                $query->where('dmc_id', $dmcId)->where('status', 1);
            }])
            ->get()
            ->filter(function($item) {
                return $item->priceForDmc !== null;
            })
            ->map(function($item) {
                return [
                    'mis_id' => $item->mis_id,
                    'item_name' => $item->item_name,
                    'description' => $item->description,
                    'image' => $item->image ? asset('storage/' . $item->image) : null,
                    'adult_price' => $item->priceForDmc->adult_price ?? 0,
                    'child_price' => $item->priceForDmc->child_price ?? 0,
                    'infant_price' => $item->priceForDmc->infant_price ?? 0
                ];
            })
            ->values();

        return response()->json($items);
    }

    /**
     * Debug method to check all items
     */
    public function debugItems()
    {
        $allItems = MiscellaneousItem::all();
        $activeItems = MiscellaneousItem::active()->get();
        
        return response()->json([
            'total_items' => $allItems->count(),
            'active_items' => $activeItems->count(),
            'all_items' => $allItems,
            'active_items_list' => $activeItems
        ]);
    }
}
