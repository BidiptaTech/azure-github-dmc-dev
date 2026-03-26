<?php

namespace App\Http\Controllers;

use App\Helpers\CountryHelper;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $countriesAll = Country::orderBy('name', 'asc')->get();

        $user = Auth::user();
        $roleId = (int) ($user->role_id ?? 0);
        $showRemitanceAndExchange = in_array($roleId, Country::DMC_REMITTANCE_EXCHANGE_ROLE_IDS, true);
        $currentDmcId = $showRemitanceAndExchange ? (int) $this->resolveDmcIdForUser($user) : 0;

        // For DMC view: show only countries that already have a value for this DMC
        // (still allow setting new values via the top picker which uses $countriesAll).
        if ($showRemitanceAndExchange && $currentDmcId > 0) {
            $countries = $countriesAll->filter(function (Country $c) use ($currentDmcId) {
                $hasRem = $c->remittanceChargeDisplayForDmc($currentDmcId) !== '';
                $hasEx = $c->exchangeRateDisplayForDmc($currentDmcId) !== '';

                return $hasRem || $hasEx;
            })->values();
        } else {
            $countries = $countriesAll;
        }

        return view('countries.index', compact('countries', 'countriesAll'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = CountryHelper::getAllCountries();
        return view('countries.add-country', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd($request->all());
        // Validate the request
        // $request->validate([
        //     'name' => 'required|string|unique:countries,name',
        //     'country_code' => 'required|string|unique:countries,country_code',
        //     'tax_percentage' => 'required|numeric|min:0',
        //     'currency' => 'required|string',
        //     'gateway_percentage' => 'required|numeric|min:0',
        //     'commission_percentage' => 'required|numeric|min:0',
        // ]);

        // // Create and store the new country
        // Country::create([
        //     'name' => $request->name,
        //     'country_code' => $request->country_code,
        //     'tax_percentage' => $request->tax_percentage,
        //     'currency' => $request->currency,
        //     'gateway_percentage' => $request->gateway_percentage,
        //     'commission_percentage' => $request->commission_percentage,
        // ]);

        // // Redirect with success message
        // return redirect()->route('countries.index')->with('success', 'Country added successfully!');

        $request->validate([
            'name' => 'required|string|unique:countries,name',
            'country_code' => 'required|string|unique:countries,country_code',
            'tax_percentage' => 'required|numeric|min:0',
            'currency' => 'required|string',
            'country_image' => 'nullable',
            'gateway_percentage' => 'required|numeric|min:0',
            'commission_percentage' => 'required|numeric|min:0',
            'card_type' => 'required|string',
            'card_length' => 'required|numeric|min:1',
            'min_length' => 'required|numeric|min:1',
            'max_length' => 'required|numeric|min:1',
        ]);


        // Handle header PDF
        $header_pdf = null;
        if ($request->hasFile('header_pdf')) {
            $header_pdfPath = CommonHelper::image_path('file_storage', $request->file('header_pdf'));
            if (!empty($header_pdfPath['master_value'])) {
                $header_pdf = $header_pdfPath['master_value'];
            }
        }
        
        // Handle footer PDF
        $footer_pdf = null;
        if ($request->hasFile('footer_pdf')) {
            $footer_pdfPath = CommonHelper::image_path('file_storage', $request->file('footer_pdf'));
            if (!empty($footer_pdfPath['master_value'])) {
                $footer_pdf = $footer_pdfPath['master_value'];
            }
        }

        // Handle country image
        $country_image = null;
        if ($request->hasFile('country_image')) {
            $country_imagePath = CommonHelper::image_path('file_storage', $request->file('country_image'));
            if (!empty($country_imagePath['master_value'])) {
                $country_image = $country_imagePath['master_value'];
            }
        } else {
            // Use default image if no file is uploaded
            $defaultImagePath = base_path('travel-2081174_1280.jpg');
            if (file_exists($defaultImagePath)) {
                $defaultImageFile = new \Illuminate\Http\UploadedFile(
                    $defaultImagePath,
                    'travel-2081174_1280.jpg',
                    mime_content_type($defaultImagePath),
                    null,
                    true
                );
                $defaultImageUpload = CommonHelper::image_path('file_storage', $defaultImageFile);
                if (!empty($defaultImageUpload['master_value'])) {
                    $country_image = $defaultImageUpload['master_value'];
                }
            }
        }
        
        // Create and store the new country
        Country::create([
            'name' => $request->name,
            'country_code' => $request->country_code,
            'tax_percentage' => $request->tax_percentage,
            'currency' => $request->currency,
            'gateway_percentage' => $request->gateway_percentage,
            'commission_percentage' => $request->commission_percentage,
            'card_type' => $request->card_type,
            'card_length' => $request->card_length,
            'min_length' => $request->min_length,
            'max_length' => $request->max_length,
            'header_pdf' => $header_pdf,
            'footer_pdf' => $footer_pdf,
            'country_image' => $country_image,
        ]);

        // Redirect with success message
        return redirect()->route('countries.index')->with('success', 'Country added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // $country = Country::find($id);

        // if (!$country) {
        //     return redirect()->route('countries.index')->with('error', 'Country not found.');
        // }

        // $countries = Country::all(); // Get all countries for dropdown

        // return view('countries.edit-country', compact('country', 'countries'));
        $id = Crypt::decrypt($id);
        $country = Country::findOrFail($id);

        if (!$country) {
            return redirect()->route('countries.index')->with('error', 'Country not found.');
        }

        // Get the list of 195 countries from CountryHelper
        $countries = CountryHelper::getAllCountries();

        return view('countries.edit-country', compact('country', 'countries'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //dd($id, $request->all());
        // Validate the request
        $request->validate([
            'name' => 'required|string|unique:countries,name,' . $id,
            // 'country_code' => 'required|string|unique:countries,country_code,' . $id,
            'tax_percentage' => 'required|numeric|min:0',
            'gateway_percentage' => 'required|numeric|min:0',
            'country_image' => 'nullable',
            'commission_percentage' => 'required|numeric|min:0',
            'card_type' => 'required|string',
            'card_length' => 'required|numeric|min:1',
            'min_length' => 'required|numeric|min:1',
            'max_length' => 'required|numeric|min:1',
        ]);
        
        // Find the country by ID
        $country = Country::findOrFail($id);
        
        // Handle header PDF
        $header_pdf = $country->header_pdf; // Keep existing PDF by default
        
        // Check if user wants to remove header PDF
        if ($request->input('remove_header_pdf') == '1') {
            // Delete the existing file if it exists
            if ($country->header_pdf && file_exists(public_path($country->header_pdf))) {
                unlink(public_path($country->header_pdf));
            }
            $header_pdf = null; // Set to null to remove from database
        }
        // Check if new header PDF is uploaded
        elseif ($request->hasFile('header_pdf')) {
            // Delete old file if exists
            if ($country->header_pdf && file_exists(public_path($country->header_pdf))) {
                unlink(public_path($country->header_pdf));
            }
            // Upload new file
            $header_pdfPath = CommonHelper::image_path('file_storage', $request->file('header_pdf'));
            if (!empty($header_pdfPath['master_value'])) {
                $header_pdf = $header_pdfPath['master_value'];
            }
        }
        
        // Handle footer PDF
        $footer_pdf = $country->footer_pdf; // Keep existing PDF by default
        
        // Check if user wants to remove footer PDF
        if ($request->input('remove_footer_pdf') == '1') {
            // Delete the existing file if it exists
            if ($country->footer_pdf && file_exists(public_path($country->footer_pdf))) {
                unlink(public_path($country->footer_pdf));
            }
            $footer_pdf = null; // Set to null to remove from database
        }
        // Check if new footer PDF is uploaded
        elseif ($request->hasFile('footer_pdf')) {
            // Delete old file if exists
            if ($country->footer_pdf && file_exists(public_path($country->footer_pdf))) {
                unlink(public_path($country->footer_pdf));
            }
            // Upload new file
            $footer_pdfPath = CommonHelper::image_path('file_storage', $request->file('footer_pdf'));
            if (!empty($footer_pdfPath['master_value'])) {
                $footer_pdf = $footer_pdfPath['master_value'];
            }
        }

        // Handle country image
        $country_image = $country->country_image; // Keep existing image by default
        
        // Check if user wants to remove country image
        if ($request->input('remove_country_image') == '1') {
            // Delete the existing file if it exists
            if ($country->country_image && file_exists(public_path($country->country_image))) {
                unlink(public_path($country->country_image));
            }
            $country_image = null; // Set to null to remove from database
        }
        // Check if new country image is uploaded
        elseif ($request->hasFile('country_image')) {
            // Delete old file if exists
            if ($country->country_image && file_exists(public_path($country->country_image))) {
                unlink(public_path($country->country_image));
            }
            // Upload new file
            $country_imagePath = CommonHelper::image_path('file_storage', $request->file('country_image'));
            if (!empty($country_imagePath['master_value'])) {
                $country_image = $country_imagePath['master_value'];
            }
        }
        // If no existing image and no new image uploaded, use default image
        elseif (empty($country->country_image)) {
            $defaultImagePath = base_path('travel-2081174_1280.jpg');
            if (file_exists($defaultImagePath)) {
                $defaultImageFile = new \Illuminate\Http\UploadedFile(
                    $defaultImagePath,
                    'travel-2081174_1280.jpg',
                    mime_content_type($defaultImagePath),
                    null,
                    true
                );
                $defaultImageUpload = CommonHelper::image_path('file_storage', $defaultImageFile);
                if (!empty($defaultImageUpload['master_value'])) {
                    $country_image = $defaultImageUpload['master_value'];
                }
            }
        }

        // Update the country data
        $country->update([
            'name' => $request->name,
            'country_code' => $request->country_code,
            'tax_percentage' => $request->tax_percentage,
            'currency' => $request->currency,
            'gateway_percentage' => $request->gateway_percentage,
            'commission_percentage' => $request->commission_percentage,
            'card_type' => $request->card_type,
            'card_length' => $request->card_length,
            'min_length' => $request->min_length,
            'max_length' => $request->max_length,
            'header_pdf' => $header_pdf,
            'footer_pdf' => $footer_pdf,
            'country_image' => $country_image,
        ]);

        // Redirect with success message
        return redirect()->route('countries.index')->with('success', 'Country updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Toggle the status of a country.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(Request $request)
    {
        try {
            $country = Country::findOrFail($request->id);
            $country->is_active = $request->is_active;
            $country->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Country status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating country status: ' . $e->getMessage()
            ]);
        }
    }

    public function updateRemitanceAndExchange(Request $request)
    {
        $user = Auth::user();
        $userRoleId = (int) ($user->role_id ?? 0);
        if (! $user || ! in_array($userRoleId, Country::DMC_REMITTANCE_EXCHANGE_ROLE_IDS, true)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to save remittance charge or exchange rate. If this is a mistake, ask an administrator to check your account role.',
            ], 403);
        }

        $resolvedDmcId = (int) $this->resolveDmcIdForUser($user);
        if ($resolvedDmcId < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Your user profile has no linked DMC account. Remittance and exchange values can only be saved for a DMC-linked user.',
            ], 422);
        }

        $payload = $request->all();
        if (! array_key_exists('remitance_charge', $payload) || ! array_key_exists('exchange_rate', $payload)) {
            return response()->json([
                'success' => false,
                'message' => 'Please submit both remittance charge and exchange rate fields (leave empty to clear a value).',
            ], 422);
        }

        $normalizeInt = static function ($v): ?int {
            if ($v === null || $v === '') {
                return null;
            }

            return (int) $v;
        };

        $request->merge([
            'remitance_charge' => $normalizeInt($payload['remitance_charge']),
            'exchange_rate' => $normalizeInt($payload['exchange_rate']),
        ]);

        try {
            $validated = $request->validate([
                'id' => ['required', 'integer', 'exists:countries,id'],
                'remitance_charge' => ['nullable', 'integer', 'min:0'],
                'exchange_rate' => ['nullable', 'integer', 'min:0'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $first = $e->errors();
            $flat = [];
            foreach ($first as $msgs) {
                foreach ($msgs as $m) {
                    $flat[] = $m;
                }
            }

            return response()->json([
                'success' => false,
                'message' => $flat[0] ?? 'Please check the values you entered (whole numbers ≥ 0, or leave blank to clear).',
                'errors' => $e->errors(),
            ], 422);
        }

        $country = Country::findOrFail($validated['id']);

        $remVal = $validated['remitance_charge'];
        $exVal = $validated['exchange_rate'];

        $remExisting = $this->normalizeCountryJsonColumn($country->remitance_charge);
        $exExisting = $this->normalizeCountryJsonColumn($country->exchange_rate);

        $remExisting = $this->mergeDmcCountryJsonEntry($remExisting, $resolvedDmcId, 'remitance_charge', $remVal);
        $exExisting = $this->mergeDmcCountryJsonEntry($exExisting, $resolvedDmcId, 'exchange_rate', $exVal);

        $country->remitance_charge = $remExisting;
        $country->exchange_rate = $exExisting;
        $country->save();

        $parts = [];
        if ($remVal !== null) {
            $parts[] = 'remittance charge '.$remVal;
        } else {
            $parts[] = 'remittance charge cleared';
        }
        if ($exVal !== null) {
            $parts[] = 'exchange rate '.$exVal;
        } else {
            $parts[] = 'exchange rate cleared';
        }

        return response()->json([
            'success' => true,
            'message' => 'Saved for '.$country->name.': '.implode(', ', $parts).'.',
            'id' => $country->id,
            'dmcId' => $resolvedDmcId,
            'remitance_charge' => $remVal,
            'exchange_rate' => $exVal,
        ]);
    }

    /**
     * @param  mixed  $raw
     * @return array<string, array<string, mixed>>
     */
    private function normalizeCountryJsonColumn($raw): array
    {
        if (is_string($raw)) {
            $raw = json_decode($raw, true) ?: [];
        }
        if (! is_array($raw)) {
            return [];
        }

        return $raw;
    }

    /**
     * Per-DMC JSON slice stored as a list (no keyed object):
     * [ {"dmc_id": 4, "remitance_charge": 12}, ... ] or [ {"dmc_id": 4, "exchange_rate": 10}, ... ].
     * Null $value removes this DMC's entry for that column.
     *
     * @param  array<int, mixed>  $existing
     * @return array<int, array<string, mixed>>
     */
    private function mergeDmcCountryJsonEntry(array $existing, int $dmcId, string $payloadKey, ?int $value): array
    {
        // If legacy keyed format exists, convert it to list first.
        if (! array_is_list($existing)) {
            $asList = [];
            foreach ($existing as $k => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $rowDmc = (int) ($row['dmc_id'] ?? $k ?? 0);
                if ($rowDmc < 1) {
                    continue;
                }
                $asList[] = ['dmc_id' => $rowDmc] + $row;
            }
            $existing = $asList;
        }

        $out = [];
        $found = false;
        foreach ($existing as $row) {
            if (! is_array($row)) {
                continue;
            }
            $rowDmc = (int) ($row['dmc_id'] ?? 0);
            if ($rowDmc !== $dmcId) {
                $out[] = $row;
                continue;
            }

            $found = true;
            if ($value === null) {
                // remove entry
                continue;
            }
            $out[] = [
                'dmc_id' => $dmcId,
                $payloadKey => $value,
            ];
        }

        if (! $found && $value !== null) {
            $out[] = [
                'dmc_id' => $dmcId,
                $payloadKey => $value,
            ];
        }

        return array_values($out);
    }

    private function resolveDmcIdForUser($user)
    {
        $roleId = (int) ($user->role_id ?? 0);

        // Direct DMC roles
        if ($roleId === 11 || $roleId === 20) {
            return $user->userId ?? null;
        }

        // Operational + Finance team roles (and similar) are created under a DMC userId
        if (in_array($roleId, [34, 124, 125, 36, 126, 127], true)) {
            return $user->created_by ?? null;
        }

        // Fallback: if this user is created under a DMC
        return $user->created_by ?? null;
    }

}
