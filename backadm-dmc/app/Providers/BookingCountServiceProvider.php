<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Tour;

class BookingCountServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share booking counts with all views
        View::composer('layouts.sidebar', function ($view) {
            $bookingCounts = [
                'new_enquiries' => Tour::where('tour_status', 'New Enquiry')->count(),
                'follow_ups' => Tour::whereIn('tour_status', ['Prospect', 'Tentative'])->count(),
                'confirmed' => Tour::where('tour_status', 'Confirmed')->count(),
                'definite' => Tour::where('tour_status', 'Definite')->count(),
                'actual' => Tour::where('tour_status', 'Actual')->count(),
                'cancelled' => Tour::where(function($query) {
                    $query->where('tour_status', 'LIKE', 'Cancel%')
                          ->orWhere('tour_status', 'LIKE', '%Cancel%');
                })->count(),
                'refunds' => 0, // Placeholder since refunds section is not implemented yet
            ];

            $view->with('bookingCounts', $bookingCounts);
        });
    }
}
