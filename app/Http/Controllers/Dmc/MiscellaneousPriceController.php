<?php

namespace App\Http\Controllers\Dmc;

use App\Http\Controllers\Controller;
use App\Models\MiscellaneousItem;
use App\Models\MiscellaneousPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MiscellaneousPriceController extends Controller
{
    /**
     * Display a listing of items with prices for the logged-in DMC
     */
    public function index()
    {
        $dmcId = Auth::user()->dmc_id ?? Auth::id(); // Adjust based on your auth structure
        
        $items = MiscellaneousItem::active()
            ->with(['priceForDmc' => function($query) use ($dmcId) {
                $query->where('dmc_id', $dmcId);
            }])
            ->paginate(20);
        
        return view('dmc.miscellaneous.index', compact('items', 'dmcId'));
    }

    /**
     * Show the form for editing prices
     */
    public function edit(string $id)
    {
        $dmcId = Auth::user()->dmc_id ?? Auth::id();
        $item = MiscellaneousItem::active()->findOrFail($id);
        $price = MiscellaneousPrice::where('mis_id', $id)
            ->where('dmc_id', $dmcId)
            ->first();
        
        return view('dmc.miscellaneous.edit', compact('item', 'price', 'dmcId'));
    }

    /**
     * Update or create pricing for the DMC
     */
    public function update(Request $request, string $id)
    {
        $dmcId = Auth::user()->dmc_id ?? Auth::id();

        $validated = $request->validate([
            'adult_price' => 'required|numeric|min:0',
            'child_price' => 'required|numeric|min:0',
            'infant_price' => 'required|numeric|min:0',
            'adult_cost' => 'nullable|numeric|min:0',
            'child_cost' => 'nullable|numeric|min:0',
            'infant_cost' => 'nullable|numeric|min:0',
            'status' => 'required|boolean'
        ]);

        $validated['mis_id'] = $id;
        $validated['dmc_id'] = $dmcId;

        MiscellaneousPrice::updateOrCreate(
            ['mis_id' => $id, 'dmc_id' => $dmcId],
            $validated
        );

        return redirect()
            ->route('dmc.miscellaneous.index')
            ->with('success', 'Pricing updated successfully!');
    }

    /**
     * Bulk update prices
     */
    public function bulkUpdate(Request $request)
    {
        $dmcId = Auth::user()->dmc_id ?? Auth::id();

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.mis_id' => 'required|exists:miscellaneous_items,mis_id',
            'items.*.adult_price' => 'required|numeric|min:0',
            'items.*.child_price' => 'required|numeric|min:0',
            'items.*.infant_price' => 'required|numeric|min:0',
            'items.*.adult_cost' => 'nullable|numeric|min:0',
            'items.*.child_cost' => 'nullable|numeric|min:0',
            'items.*.infant_cost' => 'nullable|numeric|min:0',
            'items.*.status' => 'required|boolean'
        ]);

        foreach ($validated['items'] as $itemData) {
            $itemData['dmc_id'] = $dmcId;
            MiscellaneousPrice::updateOrCreate(
                ['mis_id' => $itemData['mis_id'], 'dmc_id' => $dmcId],
                $itemData
            );
        }

        return redirect()
            ->route('dmc.miscellaneous.index')
            ->with('success', 'All prices updated successfully!');
    }

    /**
     * Get items with prices for API (for Enquiry Pro form)
     */
    public function getItemsForDmc($dmcId = null)
    {
        if (!$dmcId) {
            $dmcId = Auth::user()->dmc_id ?? Auth::id();
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
                    'infant_price' => $item->priceForDmc->infant_price ?? 0,
                    'adult_cost' => $item->priceForDmc->adult_cost ?? 0,
                    'child_cost' => $item->priceForDmc->child_cost ?? 0,
                    'infant_cost' => $item->priceForDmc->infant_cost ?? 0
                ];
            })
            ->values();

        return response()->json($items);
    }
}
