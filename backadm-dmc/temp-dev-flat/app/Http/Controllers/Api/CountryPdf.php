<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;

class CountryPdf extends Controller
{
    public function GetPdf(Request $request)
    {
        $country = $request->header('country');
        $country_pdf = Country::select('header_pdf', 'footer_pdf')->where('name', $country)->first();
        if (!$country_pdf) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        return response()->json([
            'header_pdf' => $country_pdf->header_pdf,
            'footer_pdf' => $country_pdf->footer_pdf
        ]);
    }
}
