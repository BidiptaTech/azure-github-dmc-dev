<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CommonHelper;
class CheckCurrencyController extends Controller
{
    public function checkCurrency()
    {
        $currency = CommonHelper::getDmcCurrencyByCountry();
        return response()->json([
            'currency' => $currency
        ]);
    }
}
