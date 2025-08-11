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
            $currentMonthStart = now()->startOfMonth();
            $currentMonthEnd = now()->endOfMonth();
            
            $bookingCounts = [
                'new_enquiries' => Tour::where('tour_status', 'New Enquiry')
                    ->where('created_at', '>=', $currentMonthStart)
                    ->where('created_at', '<=', $currentMonthEnd)
                    ->count(),
                'follow_ups' => Tour::whereIn('tour_status', ['Prospect', 'Tentative'])
                    ->where('created_at', '>=', $currentMonthStart)
                    ->where('created_at', '<=', $currentMonthEnd)
                    ->count(),
                'confirmed' => Tour::where('tour_status', 'On Hold')
                    ->where('created_at', '>=', $currentMonthStart)
                    ->where('created_at', '<=', $currentMonthEnd)
                    ->count(),
                'definite' => Tour::where('tour_status', 'Definite')
                    ->where('created_at', '>=', $currentMonthStart)
                    ->where('created_at', '<=', $currentMonthEnd)
                    ->count(),
                'actual' => Tour::where('tour_status', 'Actual')
                    ->where('created_at', '>=', $currentMonthStart)
                    ->where('created_at', '<=', $currentMonthEnd)
                    ->count(),
                'cancelled' => Tour::where(function($query) {
                    $query->where('tour_status', 'LIKE', 'Cancel%')
                          ->orWhere('tour_status', 'LIKE', '%Cancel%');
                })->where('created_at', '>=', $currentMonthStart)
                  ->where('created_at', '<=', $currentMonthEnd)
                  ->count(),
                'refunds' => 0, // Placeholder since refunds section is not implemented yet
            ];

            $view->with('bookingCounts', $bookingCounts);
        });
    }
}
