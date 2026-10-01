<?php

namespace App\Http\Controllers;
use App\Models\Setting;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function showExchangeRate()
    {
        $setting = Setting::where('name', 'currency')->where('status', 1)->first();
        $rate = $this->currencyService->getExchangeRate('USD', $setting->value);
        
        if ($rate) {
            return view('currency.index', ['rate' => $rate]);
        } else {
            return view('currency.index', ['error' => 'Exchange rate unavailable']);
        }
    }

    public function getExchangeRate(Request $request)
    {
        $from = $request->get('from', 'SGD');
        $to = $request->get('to', 'USD');
        try {
            $rate = $this->currencyService->getExchangeRate($from, $to);
            
            if ($rate) {
                return response()->json([
                    'success' => true,
                    'rate' => $rate,
                    'from' => $from,
                    'to' => $to
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Exchange rate not available',
                    'rate' => null
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching exchange rate: ' . $e->getMessage(),
                'rate' => null
            ], 500);
        }
    }
}
