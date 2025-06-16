<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class RateExchange extends Controller
{
    public function exchangeRate(Request $request)
    {
        $ip = request()->ip() == '::1' ? '8.8.8.8' : request()->ip();
        $position = Location::get($ip);

        if ($position) {
            $country = $position->countryName;
            $currency = $position->currency; // Not always available
            $city = $position->city;
        } else {
            $country = 'Unknown';
            $currency = 'Unknown';
        }

    }
}
