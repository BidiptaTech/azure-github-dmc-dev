<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\MiscellaneousItem;
use App\Models\MiscellaneousPrice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\CommonHelper;
class MiscellaneousItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = MiscellaneousItem::withCount('prices')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.miscellaneous.index', compact('items'));
    }

    /**
     * Countries + cities for admin create/edit dropdowns.
     */
    private function getAdminCountriesAndCities(): array
    {
        $countryNames = City::query()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country')
            ->map(fn ($c) => trim((string) $c))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $citiesByCountry = $this->getCitiesGroupedByCountry($countryNames);

        return [$countryNames, $citiesByCountry];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        [$countryNames, $citiesByCountry] = $this->getAdminCountriesAndCities();

        return view('admin.miscellaneous.create', compact('countryNames', 'citiesByCountry'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'country' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|boolean'
        ]);

        // Handle image upload (like AttractionController - using CommonHelper)
        if ($request->hasFile('image')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('image'));
            if (!empty($pathData['master_value'])) {
                $validated['image'] = $pathData['master_value'];
            }
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
        $item = MiscellaneousItem::with('prices')->where('mis_id', Crypt::decrypt($id))->firstOrFail();
        return view('admin.miscellaneous.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = MiscellaneousItem::where('mis_id', Crypt::decrypt($id))->firstOrFail();
        [$countryNames, $citiesByCountry] = $this->getAdminCountriesAndCities();

        return view('admin.miscellaneous.edit', compact('item', 'countryNames', 'citiesByCountry'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = MiscellaneousItem::where('mis_id', Crypt::decrypt($id))->firstOrFail();

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'country' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_image' => 'nullable|in:0,1',
            'status' => 'required|boolean'
        ]);

        // Handle remove image (cross button clicked)
        if ($request->input('remove_image') == '1') {
            if ($item->image) {
                CommonHelper::deleteAzureImage($item->image);
            }
            $validated['image'] = null;
        }
        // Handle image upload (like AttractionController - using CommonHelper)
        elseif ($request->hasFile('image')) {
            // Delete old image from Azure before uploading new one
            if ($item->image) {
                CommonHelper::deleteAzureImage($item->image);
            }

            $pathData = CommonHelper::image_path('file_storage', $request->file('image'));
            if (!empty($pathData['master_value'])) {
                $validated['image'] = $pathData['master_value'];
            }
        }

        unset($validated['remove_image']);
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
        $item = MiscellaneousItem::where('mis_id', Crypt::decrypt($id))->firstOrFail();

        // Delete image from Azure if exists
        if ($item->image) {
            CommonHelper::deleteAzureImage($item->image);
        }

        $item->delete();

        return redirect()
            ->route('miscellaneous.index')
            ->with('success', 'Miscellaneous item deleted successfully!');
    }

    private function resolveDmcIdForUser($user): ?int
    {
        if (!$user) {
            return null;
        }
        if ((int) $user->role_id === 11) {
            return (int) $user->userId;
        }
        if (in_array((int) $user->role_id, [35, 77, 78, 84, 130, 132, 133, 135, 136, 137, 138], true)) {
            return (int) ($user->created_by ?: $user->userId);
        }
        return (int) $user->userId;
    }

    private function getAccessibleCountryNames(int $dmcId, \App\Models\User $user): array
    {
        $dmcUser = \App\Models\User::where('userId', $dmcId)->first();
        if ($dmcUser) {
            $masterDmcId = $dmcUser->master_dmc_id ?? null;
            if (empty($masterDmcId)) {
                $visited = [];
                $candidateId = $dmcUser->created_by ?? null;
                $safety = 0;
                while (!empty($candidateId) && $safety < 8 && !in_array($candidateId, $visited, true)) {
                    $visited[] = $candidateId;
                    $candidate = \App\Models\User::where('userId', $candidateId)->first();
                    if (!$candidate) {
                        break;
                    }
                    if ((int) ($candidate->role_id ?? 0) === 3) {
                        $masterDmcId = $candidate->userId;
                        break;
                    }
                    $candidateId = $candidate->created_by ?? null;
                    $safety++;
                }
            }

            $masterDmc = \App\Models\User::where('userId', $masterDmcId ?: $dmcId)->first();
            if ($masterDmc && !empty($masterDmc->country)) {
                return array_values(array_filter(array_map(
                    static fn ($c) => trim($c),
                    preg_split('/\s*,\s*/', (string) $masterDmc->country)
                )));
            }

            if (!empty($dmcUser->country)) {
                return array_values(array_filter(array_map(
                    static fn ($c) => trim($c),
                    preg_split('/\s*,\s*/', (string) $dmcUser->country)
                )));
            }
        }

        if (!empty($user->country)) {
            return array_values(array_filter(array_map(
                static fn ($c) => trim($c),
                preg_split('/\s*,\s*/', (string) $user->country)
            )));
        }

        return [];
    }

    private function getCitiesGroupedByCountry(array $countryNames): array
    {
        if (empty($countryNames)) {
            return [];
        }

        $cities = \App\Models\City::whereIn('country', $countryNames)
            ->orderBy('name')
            ->get(['name', 'country', 'city_id']);

        $grouped = [];
        foreach ($cities as $city) {
            $grouped[$city->country][] = [
                'name' => $city->name,
                'city_id' => $city->city_id,
            ];
        }

        return $grouped;
    }

    private function normalizeLocation(?string $value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function upsertLocationPrice(int $itemId, int $dmcId, array $location): MiscellaneousPrice
    {
        $country = $this->normalizeLocation($location['country'] ?? '');
        $city = $this->normalizeLocation($location['city'] ?? '');

        $payload = [
            'country' => $country,
            'city' => $city,
            'adult_price' => $location['adult_price'] ?? 0,
            'child_price' => $location['child_price'] ?? 0,
            'infant_price' => $location['infant_price'] ?? 0,
            'adult_cost' => $location['adult_cost'] ?? 0,
            'child_cost' => $location['child_cost'] ?? 0,
            'infant_cost' => $location['infant_cost'] ?? 0,
            'status' => 1,
        ];

        $existing = null;
        $priceId = $location['price_id'] ?? null;
        if ($priceId !== null && $priceId !== '') {
            $existing = MiscellaneousPrice::withTrashed()
                ->where('id', $priceId)
                ->where('mis_id', $itemId)
                ->where('dmc_id', $dmcId)
                ->first();
        }

        $byLocation = MiscellaneousPrice::withTrashed()
            ->where('mis_id', $itemId)
            ->where('dmc_id', $dmcId)
            ->where('country', $country)
            ->where('city', $city)
            ->first();

        if ($byLocation && $existing && (int) $byLocation->id !== (int) $existing->id) {
            $existing = $byLocation;
        } elseif (!$existing && $byLocation) {
            $existing = $byLocation;
        }

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update($payload);
            return $existing;
        }

        return MiscellaneousPrice::create(array_merge($payload, [
            'mis_id' => $itemId,
            'dmc_id' => $dmcId,
        ]));
    }

    private function formatLocationPayload(MiscellaneousPrice $price): array
    {
        return [
            'price_id' => $price->id,
            'country' => $price->country ?? '',
            'city' => $price->city ?? '',
            'adult_price' => (float) ($price->adult_price ?? 0),
            'child_price' => (float) ($price->child_price ?? 0),
            'infant_price' => (float) ($price->infant_price ?? 0),
            'adult_cost' => (float) ($price->adult_cost ?? 0),
            'child_cost' => (float) ($price->child_cost ?? 0),
            'infant_cost' => (float) ($price->infant_cost ?? 0),
        ];
    }

    /**
     * DMC Miscellaneous Selection Page
     */
    public function dmcMiscellaneousSelection(Request $request)
    {
        $user = auth()->user();
        $allowedRoles = [11, 35, 77, 78, 84, 120, 130, 132, 133, 135, 136, 137, 138, 139, 140];

        if (!in_array($user->role_id, $allowedRoles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        $dmc_id = $this->resolveDmcIdForUser($user);
        $countryNames = $this->getAccessibleCountryNames((int) $dmc_id, $user);
        $citiesByCountry = $this->getCitiesGroupedByCountry($countryNames);

        $priceRows = MiscellaneousPrice::where('dmc_id', $dmc_id)
            ->where('status', 1)
            ->with('item')
            ->orderBy('id')
            ->get()
            ->filter(fn ($price) => $price->item !== null);

        $selectedItems = $priceRows
            ->groupBy('mis_id')
            ->map(function ($prices) {
                $item = $prices->first()->item;
                $item->locations = $prices->map(function ($price) {
                    return (object) $this->formatLocationPayload($price);
                })->values();
                return $item;
            })
            ->values();

        $selectedItemIds = $selectedItems->pluck('mis_id')->toArray();
        $availableItems = MiscellaneousItem::active()
            ->whereNotIn('mis_id', $selectedItemIds)
            ->orderBy('item_name', 'asc')
            ->get();

        // Ensure item countries appear in dropdowns even if outside master-DMC list
        $extraCountries = $selectedItems->pluck('country')
            ->merge($availableItems->pluck('country'))
            ->map(fn ($c) => trim((string) $c))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $countryNames = array_values(array_unique(array_merge($countryNames, $extraCountries)));
        sort($countryNames);
        $citiesByCountry = $this->getCitiesGroupedByCountry($countryNames);

        return view('services.miscellaneous', compact(
            'availableItems',
            'selectedItems',
            'dmc_id',
            'countryNames',
            'citiesByCountry'
        ));
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

        $dmc_id = $this->resolveDmcIdForUser($user);

        if ($request->has('selected_items')) {
            foreach ($request->selected_items as $itemId => $data) {
                $locations = $data['locations'] ?? [];
                if (!is_array($locations)) {
                    continue;
                }

                $keptPriceIds = [];
                foreach ($locations as $location) {
                    if (!is_array($location)) {
                        continue;
                    }
                    $country = $this->normalizeLocation($location['country'] ?? '');
                    $city = $this->normalizeLocation($location['city'] ?? '');
                    if ($country === '' || $city === '') {
                        continue;
                    }
                    $price = $this->upsertLocationPrice((int) $itemId, (int) $dmc_id, $location);
                    $keptPriceIds[] = $price->id;
                }

                $query = MiscellaneousPrice::where('mis_id', $itemId)->where('dmc_id', $dmc_id);
                if (!empty($keptPriceIds)) {
                    $query->whereNotIn('id', $keptPriceIds)->delete();
                } else {
                    $query->delete();
                }
            }
        }

        if ($request->has('removed_items')) {
            MiscellaneousPrice::where('dmc_id', $dmc_id)
                ->whereIn('mis_id', $request->removed_items)
                ->update(['status' => 0]);

            MiscellaneousPrice::where('dmc_id', $dmc_id)
                ->whereIn('mis_id', $request->removed_items)
                ->delete();
        }

        return redirect()->back()->with('success', 'Miscellaneous items updated successfully!');
    }

    /**
     * Select / upsert Miscellaneous Item location price (AJAX)
     */
    public function selectMiscellaneous(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
            }

            $allowedRoles = [11, 35, 77, 78, 84, 120, 130, 132, 133, 135, 136, 137, 138, 139, 140];
            if (!in_array($user->role_id, $allowedRoles)) {
                return response()->json(['success' => false, 'message' => 'You do not have permission to perform this action.'], 403);
            }

            $dmc_id = $this->resolveDmcIdForUser($user);
            $itemId = $request->input('item_id');
            if (!$itemId) {
                return response()->json(['success' => false, 'message' => 'Item ID is required'], 400);
            }

            $item = MiscellaneousItem::find($itemId);
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item not found'], 404);
            }

            $locationsPayload = $request->input('locations');
            if ($request->filled('locations_json')) {
                $decoded = json_decode($request->input('locations_json'), true);
                if (is_array($decoded)) {
                    $locationsPayload = $decoded;
                }
            }

            $wantsLocationSave = $request->has('locations')
                || $request->filled('locations_json')
                || is_array($locationsPayload);

            if ($wantsLocationSave) {
                if (!is_array($locationsPayload)) {
                    return response()->json(['success' => false, 'message' => 'Invalid locations payload.'], 422);
                }

                $saved = \DB::transaction(function () use ($locationsPayload, $itemId, $dmc_id) {
                    $saved = [];
                    $keptIds = [];
                    foreach ($locationsPayload as $location) {
                        if (!is_array($location)) {
                            continue;
                        }
                        $country = $this->normalizeLocation($location['country'] ?? '');
                        $city = $this->normalizeLocation($location['city'] ?? '');
                        if ($country === '' || $city === '') {
                            continue;
                        }
                        $price = $this->upsertLocationPrice((int) $itemId, (int) $dmc_id, $location);
                        $keptIds[] = (int) $price->id;
                        $saved[] = $this->formatLocationPayload($price);
                    }

                    if (empty($saved)) {
                        throw new \RuntimeException('Please select country and city for every location before saving.');
                    }

                    MiscellaneousPrice::where('mis_id', $itemId)
                        ->where('dmc_id', $dmc_id)
                        ->whereNotIn('id', array_values(array_unique($keptIds)))
                        ->update(['status' => 0]);

                    MiscellaneousPrice::where('mis_id', $itemId)
                        ->where('dmc_id', $dmc_id)
                        ->whereNotIn('id', array_values(array_unique($keptIds)))
                        ->delete();

                    return $saved;
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Location prices saved successfully!',
                    'action' => 'updated',
                    'locations' => $saved,
                ]);
            }

            // Add item with required country + city (first location)
            // Prefer request values; fall back to item master country/city from admin create
            $country = $this->normalizeLocation($request->input('country'));
            $city = $this->normalizeLocation($request->input('city'));
            if ($country === '') {
                $country = $this->normalizeLocation($item->country ?? '');
            }
            if ($city === '') {
                $city = $this->normalizeLocation($item->city ?? '');
            }
            if ($country === '' || $city === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Country and city are required. Set them on the item (admin) or when adding.',
                ], 422);
            }

            $price = $this->upsertLocationPrice((int) $itemId, (int) $dmc_id, [
                'price_id' => $request->input('price_id'),
                'country' => $country,
                'city' => $city,
                'adult_price' => $request->adult_price ?? 0,
                'child_price' => $request->child_price ?? 0,
                'infant_price' => $request->infant_price ?? 0,
                'adult_cost' => $request->adult_cost ?? 0,
                'child_cost' => $request->child_cost ?? 0,
                'infant_cost' => $request->infant_cost ?? 0,
            ]);

            $locations = MiscellaneousPrice::where('mis_id', $itemId)
                ->where('dmc_id', $dmc_id)
                ->where('status', 1)
                ->orderBy('id')
                ->get()
                ->map(fn ($p) => $this->formatLocationPayload($p))
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Miscellaneous item added for ' . $city . '. Set cost & sell, then Save.',
                'action' => 'added',
                'locations' => $locations,
                'prices' => $this->formatLocationPayload($price),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in selectMiscellaneous', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error saving item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove Miscellaneous Item or a single location price (AJAX)
     */
    public function removeMiscellaneous(Request $request)
    {
        try {
            $user = auth()->user();
            $allowedRoles = [11, 35, 77, 78, 84, 120, 130, 132, 133, 135, 136, 137, 138, 139, 140];
            if (!in_array($user->role_id, $allowedRoles)) {
                abort(403, 'You do not have permission to perform this action.');
            }

            $dmc_id = $this->resolveDmcIdForUser($user);
            $priceId = $request->input('price_id');
            $itemId = $request->input('item_id');

            if ($priceId) {
                $price = MiscellaneousPrice::where('id', $priceId)->where('dmc_id', $dmc_id)->first();
                if (!$price) {
                    return response()->json(['success' => false, 'message' => 'Location price not found.'], 404);
                }
                $itemId = $price->mis_id;
                $price->update(['status' => 0]);
                $price->delete();

                $remaining = MiscellaneousPrice::where('mis_id', $itemId)
                    ->where('dmc_id', $dmc_id)
                    ->where('status', 1)
                    ->count();

                return response()->json([
                    'success' => true,
                    'message' => 'Location price removed.',
                    'item_removed' => $remaining === 0,
                    'item_id' => $itemId,
                    'remaining_locations' => $remaining,
                ]);
            }

            if (!$itemId) {
                return response()->json(['success' => false, 'message' => 'Item ID is required.'], 400);
            }

            MiscellaneousPrice::where('mis_id', $itemId)->where('dmc_id', $dmc_id)->update(['status' => 0]);
            MiscellaneousPrice::where('mis_id', $itemId)->where('dmc_id', $dmc_id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Miscellaneous item removed successfully.',
                'item_removed' => true,
                'item_id' => $itemId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Items for DMC (API for Enquiry Pro Form)
     */
    public function getItemsForDmc($dmcId = null)
    {
        if (!$dmcId) {
            $dmcId = $this->resolveDmcIdForUser(auth()->user());
        }

        $city = request()->input('city') ?? request()->input('destination');
        $country = request()->input('country');

        $items = MiscellaneousItem::active()
            ->whereHas('pricesForDmc', function ($query) use ($dmcId, $city, $country) {
                $query->where('dmc_id', $dmcId)->where('status', 1);
                if ($city) {
                    $cityLower = strtolower(trim($city));
                    $query->where(function ($q) use ($cityLower) {
                        $q->whereRaw("LOWER(TRIM(COALESCE(city, ''))) = ?", [$cityLower])
                            ->orWhereNull('city')
                            ->orWhere('city', '');
                    });
                }
                if ($country) {
                    $countryLower = strtolower(trim($country));
                    $query->where(function ($q) use ($countryLower) {
                        $q->whereRaw("LOWER(TRIM(COALESCE(country, ''))) = ?", [$countryLower])
                            ->orWhereNull('country')
                            ->orWhere('country', '');
                    });
                }
            })
            ->with(['pricesForDmc' => function ($query) use ($dmcId, $city, $country) {
                $query->where('dmc_id', $dmcId)->where('status', 1);
                if ($city) {
                    $cityLower = strtolower(trim($city));
                    $query->where(function ($q) use ($cityLower) {
                        $q->whereRaw("LOWER(TRIM(COALESCE(city, ''))) = ?", [$cityLower])
                            ->orWhereNull('city')
                            ->orWhere('city', '');
                    });
                }
                if ($country) {
                    $countryLower = strtolower(trim($country));
                    $query->where(function ($q) use ($countryLower) {
                        $q->whereRaw("LOWER(TRIM(COALESCE(country, ''))) = ?", [$countryLower])
                            ->orWhereNull('country')
                            ->orWhere('country', '');
                    });
                }
                $query->orderByRaw(
                    "CASE WHEN LOWER(TRIM(COALESCE(city, ''))) = ? THEN 0 WHEN COALESCE(TRIM(city), '') = '' THEN 1 ELSE 2 END",
                    [strtolower(trim((string) $city))]
                );
            }])
            ->get()
            ->filter(fn ($item) => $item->pricesForDmc->isNotEmpty())
            ->map(function ($item) use ($city) {
                $exact = $city
                    ? $item->pricesForDmc->first(fn ($p) => strcasecmp(trim((string) ($p->city ?? '')), trim($city)) === 0)
                    : null;
                $price = $exact ?: $item->pricesForDmc->first();
                return [
                    'mis_id' => $item->mis_id,
                    'item_name' => $item->item_name,
                    'description' => $item->description,
                    'image' => $item->image ? ((str_starts_with($item->image, 'http') || str_starts_with($item->image, '/')) ? $item->image : asset('storage/' . $item->image)) : null,
                    'country' => $price->country ?? '',
                    'city' => $price->city ?? '',
                    'adult_price' => $price->adult_price ?? 0,
                    'child_price' => $price->child_price ?? 0,
                    'infant_price' => $price->infant_price ?? 0,
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
            'active_items_list' => $activeItems,
        ]);
    }
}
