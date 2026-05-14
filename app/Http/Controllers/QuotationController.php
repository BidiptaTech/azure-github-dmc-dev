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
use App\Models\Agent;

class QuotationController extends Controller
{
    /**
     * When the PDF iframe fails, never redirect back to the preview URL (that loads the full app inside the iframe).
     */
    private function itineraryPdfErrorResponse(Request $request, string $message)
    {
        if ($request->boolean('preview', false)) {
            $safe = e($message);

            return response(
                '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Preview unavailable</title></head>'
                . '<body style="margin:0;font-family:system-ui,sans-serif;padding:1.25rem;background:#f8f9fa;color:#333;">'
                . '<p style="margin:0 0 0.5rem;font-weight:600;">Quotation preview could not be loaded</p>'
                . '<p style="margin:0;font-size:0.9rem;">' . $safe . '</p>'
                . '<p style="margin:1rem 0 0;font-size:0.8rem;color:#6c757d;">Use <strong>Download Quotation</strong> on the outer page, or try another currency / company option.</p>'
                . '</body></html>',
                503
            )->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return redirect()->back()->with('error', $message);
    }

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

        $logoType = strtolower((string) $request->query('logo_type', 'dmc'));
        if (!in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }
        $hasAgency = false;
        if (!empty($tour->agent_id)) {
            $agentForPreview = Agent::with('agency')->where('agent_id', $tour->agent_id)->first();
            $hasAgency = $agentForPreview && $agentForPreview->agency;
        }
        if ($logoType === 'agency' && !$hasAgency) {
            $logoType = 'dmc';
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
            'logoType' => $logoType,
            'hasAgency' => $hasAgency,
        ]);
    }

    /**
     * Packaged (detailed) quotation preview page: currency, iframe preview, then download via modal (same flow as itinerary preview).
     */
    public function detailedQuotationPreview($encryptedTourId, Request $request)
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

        $availableCurrencies = [
            'SGD',
            'USD',
            'EUR',
            'GBP',
            'INR',
            'AUD',
            'NZD',
            'CAD',
            'CHF',
            'JPY',
            'CNY',
            'HKD',
            'TWD',
            'KRW',
            'THB',
            'MYR',
            'IDR',
            'PHP',
            'VND',
            'AED',
            'SAR',
            'QAR',
            'KWD',
            'BHD',
            'OMR',
            'ZAR',
            'NGN',
            'EGP',
            'KES',
            'GHS',
            'MAD',
            'BRL',
            'ARS',
            'CLP',
            'COP',
            'PEN',
            'MXN',
            'RUB',
            'UAH',
            'TRY',
            'ILS',
            'PLN',
            'CZK',
            'HUF',
            'RON',
            'SEK',
            'NOK',
            'DKK',
            'ISK',
            'BGN',
            'HRK',
            'PKR',
            'LKR',
            'BDT',
            'MVR',
            'KZT',
            'DOP',
            'JMD',
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

        $logoType = strtolower((string) $request->query('logo_type', 'dmc'));
        if (!in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }
        $hasAgency = false;
        if (!empty($tour->agent_id)) {
            $agentForPreview = Agent::with('agency')->where('agent_id', $tour->agent_id)->first();
            $hasAgency = $agentForPreview && $agentForPreview->agency;
        }
        if ($logoType === 'agency' && !$hasAgency) {
            $logoType = 'dmc';
        }

        $countries = Country::where('is_active', 1)->orderBy('name', 'asc')->get();
        $cities = City::whereNull('deleted_at')->orderBy('name', 'asc')->get(['name', 'country']);
        $citiesByCountry = $cities->groupBy(fn ($c) => (string) ($c->country ?? ''))->map(function ($group) {
            return $group->pluck('name')->values();
        })->toArray();

        return view('single-tour-package.detailed-quotation-preview', [
            'tour' => $tour,
            'selectedCurrency' => $selectedCurrency,
            'availableCurrencies' => $availableCurrencies,
            'countries' => $countries,
            'citiesByCountry' => $citiesByCountry,
            'logoType' => $logoType,
            'hasAgency' => $hasAgency,
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
                return $this->itineraryPdfErrorResponse($request, 'Tour not found.');
            }

            $currency = $request->query('currency'); // target currency selected by user
            $preview = $request->boolean('preview', false);
            $quotationInfoKey = $request->query('quotation_info_key');
            $quotationInformationHtml = $quotationInfoKey ? Cache::get((string) $quotationInfoKey) : null;
            $logoType = strtolower(trim((string) $request->query('logo_type', 'dmc')));
            if (!in_array($logoType, ['dmc', 'agency'], true)) {
                $logoType = 'dmc';
            }

            // If agency-branded PDF fails, fall back to the same quotation with DMC logo and company name only.
            $logoAttempts = $logoType === 'agency' ? ['agency', 'dmc'] : ['dmc'];

            foreach ($logoAttempts as $attemptLogo) {
                try {
                    $pdfResponse = CommonHelper::downloadTourPdf(
                        $tourId,
                        $currency,
                        $preview,
                        $quotationInformationHtml,
                        'single-tour-package.quotation',
                        $attemptLogo
                    );
                    if ($pdfResponse) {
                        return $pdfResponse;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Itinerary PDF generation attempt failed', [
                        'tour_id' => $tourId,
                        'logo_type' => $attemptLogo,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::error('Itinerary PDF generation failed after all logo attempts', [
                'tour_id' => $tourId,
                'requested_logo_type' => $logoType,
            ]);

            return $this->itineraryPdfErrorResponse($request, 'Unable to generate itinerary PDF.');
        } catch (\Exception $e) {
            Log::error('PDF route error: ' . $e->getMessage(), [
                'tour_id' => $tourId,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->itineraryPdfErrorResponse($request, 'Unable to generate itinerary PDF.');
        }
    }

    /**
     * Generate packaged (detailed) quotation PDF for iframe preview and download.
     */
    public function downloadDetailedQuotation($tourId, Request $request)
    {
        try {
            $tour = Tour::where('tour_id', $tourId)->first();
            if (!$tour) {
                return $this->itineraryPdfErrorResponse($request, 'Tour not found.');
            }

            $currency = $request->query('currency');
            $preview = $request->boolean('preview', false);
            $quotationInfoKey = $request->query('quotation_info_key');
            $quotationInformationHtml = $quotationInfoKey ? Cache::get((string) $quotationInfoKey) : null;
            $logoType = strtolower(trim((string) $request->query('logo_type', 'dmc')));
            if (!in_array($logoType, ['dmc', 'agency'], true)) {
                $logoType = 'dmc';
            }

            $logoAttempts = $logoType === 'agency' ? ['agency', 'dmc'] : ['dmc'];

            foreach ($logoAttempts as $attemptLogo) {
                try {
                    $pdfResponse = CommonHelper::downloadTourPdf(
                        $tourId,
                        $currency,
                        $preview,
                        $quotationInformationHtml,
                        'single-tour-package.detailedqutation',
                        $attemptLogo
                    );
                    if ($pdfResponse) {
                        return $pdfResponse;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Packaged quotation PDF generation attempt failed', [
                        'tour_id' => $tourId,
                        'logo_type' => $attemptLogo,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::error('Packaged quotation PDF generation failed after all logo attempts', [
                'tour_id' => $tourId,
                'requested_logo_type' => $logoType,
            ]);

            return $this->itineraryPdfErrorResponse($request, 'Unable to generate packaged quotation PDF.');
        } catch (\Exception $e) {
            Log::error('Packaged quotation PDF route error: ' . $e->getMessage(), [
                'tour_id' => $tourId,
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->itineraryPdfErrorResponse($request, 'Unable to generate packaged quotation PDF.');
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

