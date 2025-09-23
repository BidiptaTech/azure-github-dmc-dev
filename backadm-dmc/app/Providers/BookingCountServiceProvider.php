<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Tour;
use App\Models\User;

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
            
            // Get current user and determine DMC ID
            $user = Auth::user();
            $dmc_id = null;
            
            if ($user) {
                // Determine DMC ID based on user role
                if ($user->role_id == 11) { // DMC
                    $dmc_id = $user->userId;
                } else if (in_array($user->role_id, [33, 34, 36, 128, 129, 130, 134, 135, 136, 138])) { // Sales Head, Sales Manager, Assistant Sales Manager
                    $dmc_id = $user->created_by;
                }
                elseif($user->role_id == 37 || $user->role_id == 126 || $user->role_id == 124){
                    $sales_manager_id = $user->userId;
                    $sales_head_id = $user->created_by;
                    $sales_head = User::where('userId', $sales_head_id)->first();
                    $dmc_id = $sales_head->created_by;
                }
                elseif($user->role_id == 38 || $user->role_id == 127 || $user->role_id == 125){
                    $assistant_sales_manager_id = $user->userId;
                    $sales_manager_id = $user->created_by;
                    $sales_manager = User::where('userId', $sales_manager_id)->first();
                    $sales_head_id = $sales_manager->created_by;
                    $sales_head = User::where('userId', $sales_head_id)->first();
                    $dmc_id = $sales_head->created_by;
                }
            }
            
            $bookingCounts = [
                'new_enquiries' => $this->getTourCountWithDmcFilter('New Enquiry', $currentMonthStart, $currentMonthEnd, $dmc_id),
                'follow_ups' => $this->getTourCountWithDmcFilter(['Prospect', 'Tentative'], $currentMonthStart, $currentMonthEnd, $dmc_id),
                'confirmed' => $this->getTourCountWithDmcFilter('Confirmed', $currentMonthStart, $currentMonthEnd, $dmc_id),
                'definite' => $this->getTourCountWithDmcFilter('Definite', $currentMonthStart, $currentMonthEnd, $dmc_id),
                'actual' => $this->getTourCountWithDmcFilter('Actual', $currentMonthStart, $currentMonthEnd, $dmc_id),
                'cancelled' => $this->getCancelledTourCount($currentMonthStart, $currentMonthEnd, $dmc_id),
                'refunds' => $this->getTourCountWithDmcFilter(['Refund - Pending', 'Refunded'], $currentMonthStart, $currentMonthEnd, $dmc_id),
            ];

            $view->with('bookingCounts', $bookingCounts);
        });
    }

    /**
     * Get tour count with DMC filter
     */
    private function getTourCountWithDmcFilter($status, $startDate, $endDate, $dmc_id)
    {
        $query = Tour::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        // Apply status filter
        if (is_array($status)) {
            $query->whereIn('tour_status', $status);
        } else {
            $query->where('tour_status', $status);
        }

        // Apply DMC filter if DMC ID is available
        if ($dmc_id) {
            $query->where('dmc_id', $dmc_id);
        }

        return $query->count();
    }

    /**
     * Get cancelled tour count with DMC filter
     */
    private function getCancelledTourCount($startDate, $endDate, $dmc_id)
    {
        $query = Tour::where(function($query) {
                $query->where('tour_status', 'LIKE', 'Cancel%')
                      ->orWhere('tour_status', 'LIKE', '%Cancel%');
            })
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        // Apply DMC filter if DMC ID is available
        if ($dmc_id) {
            $query->where('dmc_id', $dmc_id);
        }

        return $query->count();
    }
}
