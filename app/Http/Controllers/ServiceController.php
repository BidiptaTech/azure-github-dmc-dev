<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ServiceController extends Controller
{
    public function getServices(Request $request)
    {
        $request->validate([
            'city' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        // This project may not have a unified `services` table.
        // Avoid 500 errors and return an empty grid instead.
        if (!Schema::hasTable('services')) {
            $services = collect();
            return view('partials.service-grid', compact('services'))->render();
        }

        $services = Service::query()
            ->where('city_id', $request->city)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->orderBy('date')
            ->get();

        return view('partials.service-grid', compact('services'))->render();
    }
}

