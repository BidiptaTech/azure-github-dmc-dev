<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Helpers\CurrencyHelper;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Models\Country;
use App\Models\City;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class QuotationController extends Controller
{
    /**
     * Show itinerary / quotation preview with currency dropdown and embedded PDF.
     */
    public function itineraryPreview($encryptedTourId, Request $request)
    {
        try {
            $tourId = Crypt::decrypt($encryptedTourId);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Invalid tour reference.');
        }

        $tour = Tour::where('tour_id', $tourId)->first();
        if (!$tour) {
            return redirect()->back()->with('error', 'Tour not found.');
        }

        // Available currencies for selection (can be moved to config later)
        // At least ~50 important world currencies including key tourism markets
        $availableCurrencies = [
            'SGD', // Singapore Dollar (default base)
            'USD', // US Dollar
            'EUR', // Euro
            'GBP', // British Pound
            'INR', // Indian Rupee
            'AUD', // Australian Dollar
            'NZD', // New Zealand Dollar
            'CAD', // Canadian Dollar
            'CHF', // Swiss Franc
            'JPY', // Japanese Yen
            'CNY', // Chinese Yuan
            'HKD', // Hong Kong Dollar
            'TWD', // New Taiwan Dollar
            'KRW', // South Korean Won
            'THB', // Thai Baht
            'MYR', // Malaysian Ringgit
            'IDR', // Indonesian Rupiah
            'PHP', // Philippine Peso
            'VND', // Vietnamese Dong
            'AED', // UAE Dirham
            'SAR', // Saudi Riyal
            'QAR', // Qatari Riyal
            'KWD', // Kuwaiti Dinar
            'BHD', // Bahraini Dinar
            'OMR', // Omani Rial
            // Africa & Middle East tourism destinations
            'ZAR', // South African Rand
            'NGN', // Nigerian Naira
            'EGP', // Egyptian Pound
            'KES', // Kenyan Shilling
            'GHS', // Ghanaian Cedi
            'MAD', // Moroccan Dirham
            // Americas tourism destinations
            'BRL', // Brazilian Real
            'ARS', // Argentine Peso
            'CLP', // Chilean Peso
            'COP', // Colombian Peso
            'PEN', // Peruvian Sol
            'MXN', // Mexican Peso
            // Europe tourism destinations
            'RUB', // Russian Ruble
            'UAH', // Ukrainian Hryvnia
            'TRY', // Turkish Lira
            'ILS', // Israeli New Shekel
            'PLN', // Polish Zloty
            'CZK', // Czech Koruna
            'HUF', // Hungarian Forint
            'RON', // Romanian Leu
            'SEK', // Swedish Krona
            'NOK', // Norwegian Krone
            'DKK', // Danish Krone
            'ISK', // Icelandic Krona
            'BGN', // Bulgarian Lev
            'HRK', // Croatian Kuna
            // South Asia & Indian Ocean key tourism markets
            'PKR', // Pakistani Rupee
            'LKR', // Sri Lankan Rupee
            'BDT', // Bangladeshi Taka
            'MVR', // Maldivian Rufiyaa (Maldives)
            // A few extra common tourism currencies
            'KZT', // Kazakhstani Tenge
            'DOP', // Dominican Peso
            'JMD', // Jamaican Dollar
        ];
        $currentUser = Auth::user();
        $dmcId = CommonHelper::getDmcId($currentUser);
        $dmc = User::select('country')->where('userId', $dmcId)->first();
        $country = $dmc ? Country::select('currency')->where('name', $dmc->country)->first() : null;
        $currencyRaw = $country ? $country->currency : null;
        $defaultCurrency = CurrencyHelper::normalizeCurrencyToCode($currencyRaw, $availableCurrencies, 'SGD');
        $selectedCurrency = strtoupper($request->query('currency', $defaultCurrency));

        if (!in_array($selectedCurrency, $availableCurrencies, true)) {
            $selectedCurrency = $defaultCurrency;
        }

        // For the “Quotation Information” modal (country/city selection)
        $countries = Country::where('is_active', 1)->orderBy('name', 'asc')->get();
        $cities = City::whereNull('deleted_at')->orderBy('name', 'asc')->get(['name', 'country']);
        $citiesByCountry = $cities->groupBy(fn ($c) => (string) ($c->country ?? ''))->map(function ($group) {
            return $group->pluck('name')->values();
        })->toArray();

        return view('single-tour-package.itinerary-preview', [
            'tour' => $tour,
            'selectedCurrency' => $selectedCurrency,
            'availableCurrencies' => $availableCurrencies,
            'countries' => $countries,
            'citiesByCountry' => $citiesByCountry,
        ]);
    }

    /**
     * Generate itinerary PDF (used by preview iframe and direct download).
     */
    public function downloadItinerary($tourId, Request $request)
    {
        try {
            $tour = Tour::where('tour_id', $tourId)->first();
            if (!$tour) {
                return redirect()->back()->with('error', 'Tour not found.');
            }

            $currency = $request->query('currency'); // target currency selected by user
            $preview = $request->boolean('preview', false);
            $quotationInfoKey = $request->query('quotation_info_key');
            $quotationInformationHtml = $quotationInfoKey ? Cache::get((string) $quotationInfoKey) : null;

            try {
                $pdfResponse = CommonHelper::downloadTourPdf(
                    $tourId,
                    $currency,
                    $preview,
                    $quotationInformationHtml
                );
                if ($pdfResponse) {
                    return $pdfResponse;
                }
            } catch (\Exception $e) {
                Log::error('PDF generation error: ' . $e->getMessage(), [
                    'tour_id' => $tourId,
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return redirect()->back()->with('error', 'Unable to generate itinerary PDF.');
        } catch (\Exception $e) {
            Log::error('PDF route error: ' . $e->getMessage(), [
                'tour_id' => $tourId,
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Unable to generate itinerary PDF.');
        }
    }

    /**
     * Store edited quotation info temporarily for preview + PDF generation.
     * Uses cache so we can pass a short key via querystring.
     */
    public function storeQuotationInfo($tourId, Request $request)
    {
        $tour = Tour::where('tour_id', $tourId)->first();
        if (!$tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found.'], 404);
        }

        try {
            $validated = $request->validate([
                'country' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'quotation_information' => 'nullable|string|max:200000',
            ]);

            $quotationInformationHtml = (string) ($validated['quotation_information'] ?? '');

            $key = 'quotation_info_' . Str::random(40);
            Cache::put($key, $quotationInformationHtml, now()->addMinutes(10));

            return response()->json([
                'success' => true,
                'quotation_info_key' => $key,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Invalid input.'], 422);
        } catch (\Throwable $e) {
            Log::error('storeQuotationInfo failed', [
                'tour_id' => $tourId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Unable to store quotation info.'], 500);
        }
    }

}

