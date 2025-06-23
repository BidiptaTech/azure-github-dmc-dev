<?php

namespace App\Http\Controllers;
use App\Models\Setting;
use App\Services\CurrencyService;

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
}
