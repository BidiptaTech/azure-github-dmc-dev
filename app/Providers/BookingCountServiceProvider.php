<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Tour;
use App\Models\Order;
use App\Models\User;
use App\Models\PackageBooking;
use Illuminate\Support\Facades\Schema;
use App\Helpers\CommonHelper;

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
                $dmc_id = CommonHelper::getDmcId($user) ?: $dmc_id;
            }
            
            $bookingCounts = [
                'new_enquiries' => $this->getTourCountWithDmcFilter('New Enquiry', $currentMonthStart, $currentMonthEnd, $dmc_id),
                'follow_ups' => $this->getTourCountWithDmcFilter(['Prospect', 'Tentative'], $currentMonthStart, $currentMonthEnd, $dmc_id),
                'confirmed' => $this->getTourCountWithDmcFilter('Confirmed', $currentMonthStart, $currentMonthEnd, $dmc_id),
                'definite' => $this->getTourCountWithDmcFilter('Definite', $currentMonthStart, $currentMonthEnd, $dmc_id),
                'actual' => $this->getTourCountWithDmcFilter('Actual', $currentMonthStart, $currentMonthEnd, $dmc_id),
                'cancelled' => $this->getCancelledTourCount($currentMonthStart, $currentMonthEnd, $dmc_id),
                // Refunds badge: distinct tours that have refund-marked removed services in current month.
                'refunds' => $this->getRefundTourCount($currentMonthStart, $currentMonthEnd, $dmc_id),
            ];

            $packageBookingCounts = $this->getPackageBookingCounts($currentMonthStart, $currentMonthEnd, $dmc_id);

            $view->with('bookingCounts', $bookingCounts);
            $view->with('packageBookingCounts', $packageBookingCounts);
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

    /**
     * Refunds count for sidebar:
     * distinct tour_id from orders where removed/refund service rows are marked is_refund = 1
     * within current month (based on deleted_at, fallback updated_at), optionally filtered by dmc_id.
     */
    private function getRefundTourCount($startDate, $endDate, $dmc_id): int
    {
        $query = Order::withTrashed()
            ->join('tours', 'orders.tour_id', '=', 'tours.tour_id')
            ->where('orders.bookingType', 'booking')
            ->where('orders.is_refund', 1)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('orders.deleted_at', [$startDate, $endDate])
                  ->orWhere(function ($inner) use ($startDate, $endDate) {
                      $inner->whereNull('orders.deleted_at')
                            ->whereBetween('orders.updated_at', [$startDate, $endDate]);
                  });
            });

        if ($dmc_id) {
            $query->where('tours.dmc_id', $dmc_id);
        }

        return (int) $query->select('orders.tour_id')->distinct()->count('orders.tour_id');
    }

    private function getPackageBookingCounts($startDate, $endDate, $dmc_id): array
    {
        $statusColumn = Schema::hasColumn('package_bookings', 'booking_status') ? 'booking_status' : 'status';

        $base = PackageBooking::query()
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($dmc_id && Schema::hasColumn('package_bookings', 'dmc_id')) {
            $base->where('dmc_id', $dmc_id);
        }

        $countFor = function ($statuses) use ($base, $statusColumn) {
            $q = (clone $base);
            if (is_array($statuses)) {
                return $q->whereIn($statusColumn, $statuses)->count();
            }
            return $q->where($statusColumn, $statuses)->count();
        };

        $cancelled = (clone $base)->where(function ($q) use ($statusColumn) {
            $q->where($statusColumn, 'Cancelled')
              ->orWhere($statusColumn, 'like', 'Cancel%');
        })->count();

        return [
            'new_enquiries' => $countFor('New Enquiry'),
            'follow_ups' => $countFor(['Prospect', 'Tentative']),
            'confirmed' => $countFor('Confirmed'),
            'definite' => $countFor('Definite'),
            'actual' => $countFor(['Actual', 'Complete']),
            'cancelled' => $cancelled,
        ];
    }
}
