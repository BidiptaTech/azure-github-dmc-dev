<?php

namespace App\Http\Controllers;

use App\Helpers\CountryHelper;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Crypt;
class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $countries = [];
        $countries = Country::orderBy('name', 'asc')->get();
        return view('countries.index', compact('countries'));
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
            'gateway_percentage' => 'required|numeric|min:0',
            'commission_percentage' => 'required|numeric|min:0',
            'card_type' => 'required|string',
            'card_length' => 'required|numeric|min:1',
            'min_length' => 'required|numeric|min:1',
            'max_length' => 'required|numeric|min:1',
        ]);


        $existing_header_pdf = Country::where('name', $request->name)->first();
        if($existing_header_pdf){
            $header_pdf = $existing_header_pdf->header_pdf ?? ''; // Default to existing PDF if no new file
        }
        if ($request->hasFile('header_pdf')) {
            $header_pdfPath = CommonHelper::image_path('file_storage', $request->file('header_pdf'));
            if (!empty($header_pdfPath['master_value'])) {
                $header_pdf = $header_pdfPath['master_value'];
            }
        }
        $existing_footer_pdf = Country::where('name', $request->name)->first();
        if($existing_footer_pdf){
            $footer_pdf = $existing_footer_pdf->footer_pdf ?? ''; // Default to existing PDF if no new file
        }
        if ($request->hasFile('footer_pdf')) {
            $footer_pdfPath = CommonHelper::image_path('file_storage', $request->file('footer_pdf'));
            if (!empty($footer_pdfPath['master_value'])) {
                $footer_pdf = $footer_pdfPath['master_value'];
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
            'header_pdf' =>$request->header_pdf,
            'footer_pdf' => $request->footer_pdf
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
            'commission_percentage' => 'required|numeric|min:0',
            'card_type' => 'required|string',
            'card_length' => 'required|numeric|min:1',
            'min_length' => 'required|numeric|min:1',
            'max_length' => 'required|numeric|min:1',
        ]);
        
        $existing_country = Country::where('name', $request->name)->first();
        if($existing_country){
            
            $header_pdf = $existing_country->header_pdf ?? ''; // Default to existing PDF if no new file
        }
        if ($request->hasFile('header_pdf')) {
            $header_pdfPath = CommonHelper::image_path('file_storage', $request->file('header_pdf'));
            if (!empty($header_pdfPath['master_value'])) {
                $header_pdf = $header_pdfPath['master_value'];
            }
        }
        if($existing_country){
            $footer_pdf = $existing_country->footer_pdf ?? ''; // Default to existing PDF if no new file
        }
        
        if ($request->hasFile('footer_pdf')) {
            $footer_pdfPath = CommonHelper::image_path('file_storage', $request->file('footer_pdf'));
            if (!empty($footer_pdfPath['master_value'])) {
                $footer_pdf = $footer_pdfPath['master_value'];
            }
        }

        // Find the country by ID
        $country = Country::findOrFail($id);
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
            'header_pdf' =>$header_pdf,
            'footer_pdf' => $footer_pdf,
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

}
