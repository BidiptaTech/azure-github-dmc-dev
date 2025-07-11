<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\Guide;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Tour;
use App\Models\Order;
use App\Models\Enquiry;
use App\Models\EnquiryForm;
use App\Models\User;
use App\Models\Agent;
use App\Models\Facility;
use App\Models\Category;
use App\Models\Zone;
use App\Models\Port;  
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'today'); // Change default from 'total' to 'today'
        
        // Get date ranges
        $dateRanges = $this->getDateRanges($period);
        
        // Get counts for all entities
        $counts = $this->getAllCounts($dateRanges);
        
        // Get user permissions for view filtering
        $userPermissions = $this->getUserPermissions();
        
        return view('index', compact('counts', 'period', 'userPermissions'));
    }
    
    /**
     * Get dynamic counts via AJAX
     */
    public function getCounts(Request $request)
    {
        $period = $request->get('period', 'today'); // Change default from 'total' to 'today'
        $dateRanges = $this->getDateRanges($period);
        $counts = $this->getAllCounts($dateRanges);
        $userPermissions = $this->getUserPermissions();
        
        return response()->json([
            'success' => true,
            'counts' => $counts,
            'period' => $period,
            'userPermissions' => $userPermissions
        ]);
    }
    
    /**
     * Get user permissions for view filtering
     */
    private function getUserPermissions()
    {
        $user = Auth::user();
        
        return [
            'canViewAllProducts' => $this->canViewAllProducts($user),
            'canViewHotels' => $this->canViewHotels($user),
            'canViewAttractions' => $this->canViewAttractions($user),
            'canViewRestaurants' => $this->canViewRestaurants($user),
            'canViewGuides' => $this->canViewGuides($user),
            'canViewDrivers' => $this->canViewDrivers($user),
            'canViewVehicles' => $this->canViewVehicles($user),
            'canViewBusinessMetrics' => $this->canViewBusinessMetrics($user),
            'canViewEnquiries' => $this->canViewEnquiries($user),
            'canViewProductAnalytics' => $this->canViewProductAnalytics($user),
            'canViewZones' => $this->canViewZones($user),
            'canViewAgents' => $this->canViewAgents($user),
            'canViewPorts' => $this->canViewPorts($user),
            'isProductManager' => $this->isProductManager($user)
        ];
    }
    
    /**
     * Check if user can view all products
     */
    private function canViewAllProducts($user)
    {
        return in_array($user->role_id, [1, 2, 10, 11, 19, 20, 35]); // Admin, Super Admin, Master DMC, DMC, Virtual Master DMC, Virtual DMC, Product Head
    }
    
    /**
     * Check if user can view product analytics (for chart display)
     */
    private function canViewProductAnalytics($user)
    {
        return $this->canViewAllProducts($user) || $this->isProductManager($user);
    }
    
    /**
     * Check if user can view zones (only DMC and upper levels)
     */
    private function canViewZones($user)
    {
        return in_array($user->role_id, [1, 2, 10, 11, 19, 20]); // Admin, Super Admin, Master DMC, DMC, Virtual Master DMC, Virtual DMC only
    }
    
    /**
     * Check if user can view ports (only Admin and Super Admin)
     */
    private function canViewPorts($user)
    {
        return in_array($user->role_id, [1, 2]); // Admin, Super Admin only
    }
    
    
    /**
     * Check if user can view hotels
     */
    private function canViewHotels($user)
    {
        return $this->canViewAllProducts($user) || in_array($user->role_id, [77, 84]); // PM Hotel, Asst PM Hotel
    }
    
    /**
     * Check if user can view attractions
     */
    private function canViewAttractions($user)
    {
        return $this->canViewAllProducts($user) || in_array($user->role_id, [74, 93]); // PM Attraction, Asst PM Attraction
    }
    
    /**
     * Check if user can view restaurants
     */
    private function canViewRestaurants($user)
    {
        return $this->canViewAllProducts($user) || in_array($user->role_id, [78, 120]); // PM Restaurant, Asst PM Restaurant
    }
    
    /**
     * Check if user can view guides
     */
    private function canViewGuides($user)
    {
        return $this->canViewAllProducts($user) || in_array($user->role_id, [75, 102]); // PM Guide, Asst PM Guide
    }
    
    /**
     * Check if user can view drivers
     */
    private function canViewDrivers($user)
    {
        return $this->canViewAllProducts($user) || in_array($user->role_id, [76, 111]); // PM Driver, Asst PM Driver
    }
    
    /**
     * Check if user can view vehicles
     */
    private function canViewVehicles($user)
    {
        return $this->canViewDrivers($user); // Same as drivers
    }
    
    /**
     * Check if user can view business metrics (enquiries, bookings, tours)
     */
    private function canViewBusinessMetrics($user)
    {
        // Product managers and product head cannot see business metrics, only sales and upper roles can
        return in_array($user->role_id, [1, 2, 10, 11, 19, 20, 33, 12, 37, 38]); // Exclude product managers and product head
    }
    
    /**
     * Check if user can view enquiries specifically
     */
    private function canViewEnquiries($user)
    {
        // Exclude product head and all product manager roles from seeing enquiries
        $excludedRoles = [35, 74, 75, 76, 77, 78, 84, 93, 102, 111, 120];
        return !in_array($user->role_id, $excludedRoles) && $this->canViewBusinessMetrics($user);
    }
    
    /**
     * Check if user can view agents
     */
    private function canViewAgents($user)
    {
        return in_array($user->role_id, [1, 2, 10, 11, 19, 20, 33, 12, 37, 38]); // Sales hierarchy only
    }
    
    /**
     * Check if user is a product manager
     */
    private function isProductManager($user)
    {
        return in_array($user->role_id, [74, 75, 76, 77, 78, 84, 93, 102, 111, 120]);
    }
    
    /**
     * Get date ranges based on period
     */
    private function getDateRanges($period)
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            default: // 'total'
                return null;
        }
    }
    
    /**
     * Get all entity counts
     */
    private function getAllCounts($dateRanges)
    {
        $user = Auth::user();
        
        $counts = [];
        
        // Core entities - only include if user has permission
        if ($this->canViewHotels($user)) {
            $counts['hotels'] = $this->getHotelCounts($dateRanges, $user);
        }
        
        if ($this->canViewAttractions($user)) {
            $counts['attractions'] = $this->getAttractionCounts($dateRanges, $user);
        }
        
        if ($this->canViewRestaurants($user)) {
            $counts['restaurants'] = $this->getRestaurantCounts($dateRanges, $user);
        }
        
        if ($this->canViewGuides($user)) {
            $counts['guides'] = $this->getGuideCounts($dateRanges, $user);
        }
        
        if ($this->canViewDrivers($user)) {
            $counts['drivers'] = $this->getDriverCounts($dateRanges, $user);
        }
        
        if ($this->canViewVehicles($user)) {
            $counts['vehicles'] = $this->getVehicleCounts($dateRanges, $user);
        }
        
        // Business entities - only for non-product managers
        if ($this->canViewBusinessMetrics($user)) {
            $counts['tours'] = $this->getTourCounts($dateRanges, $user);
            $counts['bookings'] = $this->getBookingCounts($dateRanges, $user);
            $counts['orders'] = $this->getOrderCounts($dateRanges, $user);
        }
        
        // Enquiries - separate check with additional role restrictions
        if ($this->canViewEnquiries($user)) {
            $counts['enquiries'] = $this->getEnquiryCounts($dateRanges, $user);
        }
        
        // User management
        if ($this->canViewAgents($user)) {
            $counts['agents'] = $this->getAgentCounts($dateRanges, $user);
        }
        
        if (in_array($user->role_id, [1, 2, 10, 11, 19, 20])) { // Only higher level roles can see users
            $counts['users'] = $this->getUserCounts($dateRanges, $user);
        }
        
        // Configuration entities - for higher level roles and product managers
        if (in_array($user->role_id, [1, 2, 10, 11, 19, 20, 35]) || $this->isProductManager($user)) {
            $counts['facilities'] = $this->getFacilityCounts($dateRanges, $user);
            $counts['categories'] = $this->getCategoryCounts($dateRanges, $user);
        }
        
        // Ports - only for Admin and Super Admin
        if ($this->canViewPorts($user)) {
            $counts['ports'] = $this->getPortCounts($dateRanges, $user);
        }
        
        // Zones - only for DMC and upper levels (Admin, Super Admin, Master DMC, DMC)
        if ($this->canViewZones($user)) {
            $counts['zones'] = $this->getZoneCounts($dateRanges, $user);
        }
        
        return $counts;
    }
    
    /**
     * Get hotel counts with role-based filtering
     */
    private function getHotelCounts($dateRanges, $user)
    {
        // Return empty if user can't view hotels
        if (!$this->canViewHotels($user)) {
            return ['total' => 0, 'active' => 0, 'recent' => 0];
        }
        
        $query = Hotel::where('status', 1);
        
        // Apply role-based filtering
        $query = $this->applyProductRoleBasedFiltering($query, $user, 'dmc_id');
        
        $totalQuery = clone $query;
        $activeQuery = clone $query;
        $recentQuery = clone $query;
        
        if ($dateRanges) {
            $totalQuery->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
        }
        
        // Active now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        return [
            'total' => $totalQuery->count(),
            'active' => $activeQuery->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count(),
            'recent' => $recentQuery->where('created_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }
    
    /**
     * Get attraction counts
     */
    private function getAttractionCounts($dateRanges, $user)
    {
        if (!$this->canViewAttractions($user)) {
            return ['total' => 0, 'active' => 0, 'recent' => 0];
        }
        
        $query = Attraction::where('status', 1);
        $query = $this->applyProductRoleBasedFiltering($query, $user, 'dmc_id');
        
        $totalQuery = clone $query;
        $activeQuery = clone $query;
        $recentQuery = clone $query;
        
        if ($dateRanges) {
            $totalQuery->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
        }
        
        // Active now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        return [
            'total' => $totalQuery->count(),
            'active' => $activeQuery->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count(),
            'recent' => $recentQuery->where('created_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }
    
    /**
     * Get restaurant counts
     */
    private function getRestaurantCounts($dateRanges, $user)
    {
        if (!$this->canViewRestaurants($user)) {
            return ['total' => 0, 'active' => 0, 'recent' => 0];
        }
        
        $query = Restaurant::where('status', 1);
        $query = $this->applyProductRoleBasedFiltering($query, $user, 'dmc_id');
        
        $totalQuery = clone $query;
        $activeQuery = clone $query;
        $recentQuery = clone $query;
        
        if ($dateRanges) {
            $totalQuery->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
        }
        
        // Active now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        return [
            'total' => $totalQuery->count(),
            'active' => $activeQuery->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count(),
            'recent' => $recentQuery->where('created_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }
    
    /**
     * Get guide counts
     */
    private function getGuideCounts($dateRanges, $user)
    {
        if (!$this->canViewGuides($user)) {
            return ['total' => 0, 'available' => 0, 'recent' => 0];
        }
        
        $query = Guide::where('status', 1);
        $query = $this->applyProductRoleBasedFiltering($query, $user, 'dmc_id');
        
        $totalQuery = clone $query;
        $availableQuery = clone $query;
        $recentQuery = clone $query;
        
        if ($dateRanges) {
            $totalQuery->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
        }
        
        // Available now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        return [
            'total' => $totalQuery->count(),
            'available' => $availableQuery->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count(),
            'recent' => $recentQuery->where('created_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }
    
    /**
     * Get driver counts
     */
    private function getDriverCounts($dateRanges, $user)
    {
        if (!$this->canViewDrivers($user)) {
            return ['total' => 0, 'available' => 0, 'recent' => 0];
        }
        
        $query = Driver::where('status', 1);
        $query = $this->applyProductRoleBasedFiltering($query, $user, 'dmc_id');
        
        $totalQuery = clone $query;
        $availableQuery = clone $query;
        $recentQuery = clone $query;
        
        if ($dateRanges) {
            $totalQuery->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
        }
        
        // Available now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        return [
            'total' => $totalQuery->count(),
            'available' => $availableQuery->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count(),
            'recent' => $recentQuery->where('created_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }
    
    /**
     * Get vehicle counts
     */
    private function getVehicleCounts($dateRanges, $user)
    {
        if (!$this->canViewVehicles($user)) {
            return ['total' => 0, 'available' => 0, 'recent' => 0];
        }
        
        $query = Vehicle::where('is_available', 1);
        $query = $this->applyProductRoleBasedFiltering($query, $user, 'dmc_id');
        
        $totalQuery = clone $query;
        $availableQuery = clone $query;
        $recentQuery = clone $query;
        
        if ($dateRanges) {
            $totalQuery->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
        }
        
        // Available now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        return [
            'total' => $totalQuery->count(),
            'available' => $availableQuery->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count(),
            'recent' => $recentQuery->where('created_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }
    
    /**
     * Get tour counts
     */
    private function getTourCounts($dateRanges, $user)
    {
        $query = Tour::where('status', 1);
        
        // Apply role-based filtering for tours
        if (in_array($user->role_id, [11, 20, 33, 12, 37, 38])) {
            $agentIds = $this->getAgentIdsByUserRole($user);
            if ($agentIds->isNotEmpty()) {
                $query->whereIn('agent_id', $agentIds);
            }
        }
        
        // Get total counts based on period
        $total = $dateRanges ? (clone $query)
            ->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']])
            ->count() : $query->count();

        // Active now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        // Get today's tours
        $today = (clone $query)
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        // Get this month's tours
        $thisMonth = (clone $query)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        
        // Get active and completed counts for this month
        $active = (clone $query)
            ->whereNotIn('tour_status', ['Cancelled', 'Closed'])
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->count();
        
        $completed = (clone $query)
            ->where('tour_status', 'Confirmed')
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->count();

        return [
            'total' => $total,
            'today' => $today,
            'this_month' => $thisMonth,
            'active' => $active,
            'completed' => $completed
        ];
    }
    
    /**
     * Get booking counts (Orders with booking type)
     */
    private function getBookingCounts($dateRanges, $user)
    {
        $query = Order::where('bookingType', 'booking')->where('status', '!=', 4);
        
        // Get total counts based on period
        $total = $dateRanges ? (clone $query)
            ->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']])
            ->count() : $query->count();

        // Active now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        // Get today's bookings
        $today = (clone $query)
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        // Get this month's bookings
        $thisMonth = (clone $query)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        
        // Get confirmed and pending counts
        $confirmed = (clone $query)
            ->where('status', 1)
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->count();
        $pending = (clone $query)
            ->where('status', 2)
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->count();

        return [
            'total' => $total,
            'today' => $today,
            'this_month' => $thisMonth,
            'confirmed' => $confirmed,
            'pending' => $pending
        ];
    }
    
    /**
     * Get enquiry counts
     */
    private function getEnquiryCounts($dateRanges, $user)
    {
        $query = EnquiryForm::whereNull('unique_tour_id');

        // Apply role-based filtering
        switch ($user->role_id) {
            case 1: // Admin
            case 2: // Super Admin
            case 10: // Master DMC
            case 19: // Virtual Master DMC
                // These roles can see all enquiries
                break;

            case 11: // DMC
            case 20: // Virtual DMC
                // DMC can see all agents' enquiries
                $dmc_id = $user->userId;

                $sales_heads = User::where('created_by', $dmc_id)
                    ->where('role_id', 33)
                    ->pluck('userId');

                $sales_managers = User::whereIn('created_by', $sales_heads)
                    ->whereIn('role_id', [12, 37])
                    ->pluck('userId');

                $assistant_managers = User::whereIn('created_by', $sales_managers)
                    ->where('role_id', 38)
                    ->pluck('userId');

                $all_ids = collect([$dmc_id])
                    ->merge($sales_heads)
                    ->merge($sales_managers)
                    ->merge($assistant_managers);

                $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)
                    ->pluck('agent_id');

                $query->whereIn('agent_id', $agent_ids);
                break;

            case 33: // Sales Head
                $sales_head_id = $user->userId;

                $sales_managers = User::where('created_by', $sales_head_id)
                    ->whereIn('role_id', [12, 37])
                    ->pluck('userId');

                $assistant_managers = User::whereIn('created_by', $sales_managers)
                    ->where('role_id', 38)
                    ->pluck('userId');

                $all_ids = collect([$sales_head_id])
                    ->merge($sales_managers)
                    ->merge($assistant_managers);

                $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)
                    ->pluck('agent_id');

                $query->whereIn('agent_id', $agent_ids);
                break;

            case 12: // Sales Manager
            case 37: // Sales Manager
            case 38: // Assistant Manager
                $manager_id = $user->userId;

                $assistant_managers = User::where('created_by', $manager_id)
                    ->where('role_id', 38)
                    ->pluck('userId');

                $all_ids = collect([$manager_id])
                    ->merge($assistant_managers);

                $agent_ids = Agent::whereIn('sales_manager_dmc', $all_ids)
                    ->pluck('agent_id');

                $query->whereIn('agent_id', $agent_ids);
                break;

            default:
                // For other roles, only show their own enquiries
                $agent_ids = Agent::where('sales_manager_dmc', $user->userId)
                    ->pluck('agent_id');
                $query->whereIn('agent_id', $agent_ids);
        }

        // Get total counts based on period
        $total = $dateRanges ? (clone $query)
            ->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']])
            ->count() : $query->count();
        
        // Get today's enquiries
        $today = (clone $query)
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        // Get this month's enquiries
        $thisMonth = (clone $query)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        return [
            'total' => $total,
            'today' => $today,
            'this_month' => $thisMonth,
            'new' => $today // For compatibility with existing code
        ];
    }
    
    /**
     * Get order counts
     */
    private function getOrderCounts($dateRanges, $user)
    {
        $query = Order::where('status', '!=', 4);
        
        $totalQuery = clone $query;
        $bookingsQuery = clone $query;
        $enquiriesQuery = clone $query;
        $recentQuery = clone $query;
        
        if ($dateRanges) {
            $totalQuery->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
        }
        
        return [
            'total' => $totalQuery->count(),
            'bookings' => $bookingsQuery->where('bookingType', 'booking')->count(),
            'enquiries' => $enquiriesQuery->where('bookingType', 'enquiry')->count(),
            'recent' => $recentQuery->where('created_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }
    
    /**
     * Get agent counts
     */
    private function getAgentCounts($dateRanges, $user)
    {
        $query = Agent::query();
        
        // Apply role-based filtering
        if (in_array($user->role_id, [10, 11, 19, 20, 33, 12, 37, 38])) {
            $allIds = $this->getAllRelatedUserIds($user);
            if ($allIds->isNotEmpty()) {
                $query->whereIn('sales_manager_dmc', $allIds);
            }
        }
        
        $totalQuery = clone $query;
        $activeQuery = clone $query;
        $recentQuery = clone $query;
        
        if ($dateRanges) {
            $totalQuery->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
        }
        
        // Active now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        return [
            'total' => $totalQuery->count(),
            'active' => $activeQuery->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count(),
            'recent' => $recentQuery->where('created_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }
    
    /**
     * Get user counts
     */
    private function getUserCounts($dateRanges, $user)
    {
        $query = User::where('user_type', '>', 0);
        
        $totalQuery = clone $query;
        $activeQuery = clone $query;
        $recentQuery = clone $query;
        
        if ($dateRanges) {
            $totalQuery->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
        }
        
        // Active now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        return [
            'total' => $totalQuery->count(),
            'active' => $activeQuery->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count(),
            'recent' => $recentQuery->where('created_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }
    
    /**
     * Get facility counts
     */
    private function getFacilityCounts($dateRanges, $user)
    {
        $query = Facility::where('status', 1);
        
        // Apply role-based filtering for product managers if they have a dmc_id field
        if ($this->isProductManager($user) && Schema::hasColumn('facilities', 'dmc_id')) {
            $query = $this->applyProductRoleBasedFiltering($query, $user, 'dmc_id');
        }
        
        $totalQuery = clone $query;
        $activeQuery = clone $query;
        $recentQuery = clone $query;
        
        if ($dateRanges) {
            $totalQuery->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
        }
        
        // Active now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        return [
            'total' => $totalQuery->count(),
            'active' => $activeQuery->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count(),
            'recent' => $recentQuery->where('created_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }
    
    /**
     * Get category counts
     */
    private function getCategoryCounts($dateRanges, $user)
    {
        $query = Category::where('status', 1);
        
        // Apply role-based filtering for product managers if they have a dmc_id field
        if ($this->isProductManager($user) && Schema::hasColumn('categories', 'dmc_id')) {
            $query = $this->applyProductRoleBasedFiltering($query, $user, 'dmc_id');
        }
        
        $totalQuery = clone $query;
        $activeQuery = clone $query;
        $recentQuery = clone $query;
        
        if ($dateRanges) {
            $totalQuery->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
        }
        
        // Active now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        return [
            'total' => $totalQuery->count(),
            'active' => $activeQuery->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count(),
            'recent' => $recentQuery->where('created_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }
    
    /**
     * Get zone counts
     */
    private function getZoneCounts($dateRanges, $user)
    {
        $query = Zone::query();
        $query = $this->applyRoleBasedFiltering($query, $user, 'dmc_id');
        
        $totalQuery = clone $query;
        $activeQuery = clone $query;
        $recentQuery = clone $query;
        
        if ($dateRanges) {
            $totalQuery->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
        }
        
        // Active now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        return [
            'total' => $totalQuery->count(),
            'active' => $activeQuery->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count(),
            'recent' => $recentQuery->where('created_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }
    
    /**
     * Get port counts
     */
    private function getPortCounts($dateRanges, $user)
    {
        $query = Port::where('status', 1);
        
        // Apply role-based filtering for product managers if they have a dmc_id field
        if ($this->isProductManager($user) && Schema::hasColumn('ports', 'dmc_id')) {
            $query = $this->applyProductRoleBasedFiltering($query, $user, 'dmc_id');
        }
        
        $totalQuery = clone $query;
        $activeQuery = clone $query;
        $recentQuery = clone $query;
        
        if ($dateRanges) {
            $totalQuery->whereBetween('created_at', [$dateRanges['start'], $dateRanges['end']]);
        }
        
        // Active now shows records created this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        
        return [
            'total' => $totalQuery->count(),
            'active' => $activeQuery->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count(),
            'recent' => $recentQuery->where('created_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }
    
    /**
     * Apply role-based filtering for product managers
     */
    private function applyProductRoleBasedFiltering($query, $user, $dmcField)
    {
        // For product managers, find their DMC and filter accordingly
        if ($this->isProductManager($user)) {
            $dmcId = $this->getDmcIdForProductManager($user);
            if ($dmcId) {
                $query->where($dmcField, $dmcId);
            } else {
                // If no DMC found, return empty results to avoid showing all data
                $query->where($dmcField, -1);
            }
        } else {
            // Use existing filtering for other roles
            $query = $this->applyRoleBasedFiltering($query, $user, $dmcField);
        }
        
        return $query;
    }
    
    /**
     * Get DMC ID for product manager - Enhanced to handle all product manager types
     */
    private function getDmcIdForProductManager($user)
    {
        // Check if this is a Product Head (role_id = 35)
        if ($user->role_id == 35) {
            // Product Head is created by DMC (role_id = 11)
            return $user->created_by;
        }
        
        // Check if this is a Product Manager (PM Hotel: 77, PM Attraction: 74, PM Restaurant: 78, PM Guide: 75, PM Driver: 76)
        if (in_array($user->role_id, [74, 75, 76, 77, 78])) {
            // Product managers are created by Product Head (role_id = 35)
            $productHead = User::where('userId', $user->created_by)->first();
            if ($productHead && $productHead->role_id == 35) {
                // Product Head is created by DMC (role_id = 11)
                return $productHead->created_by;
            }
        }
        
        // Check if this is an Assistant Product Manager (Asst PM Hotel: 84, Asst PM Attraction: 93, Asst PM Restaurant: 120, Asst PM Guide: 102, Asst PM Driver: 111)
        if (in_array($user->role_id, [84, 93, 102, 111, 120])) {
            // Assistant product managers are created by Product Manager
            $productManager = User::where('userId', $user->created_by)->first();
            if ($productManager && in_array($productManager->role_id, [74, 75, 76, 77, 78])) {
                // Product Manager is created by Product Head (role_id = 35)
                $productHead = User::where('userId', $productManager->created_by)->first();
                if ($productHead && $productHead->role_id == 35) {
                    // Product Head is created by DMC (role_id = 11)
                    return $productHead->created_by;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Apply role-based filtering to queries
     */
    private function applyRoleBasedFiltering($query, $user, $dmcField)
    {
        switch ($user->role_id) {
            case 10: // Master DMC
                $dmcIds = User::where('master_dmc_id', $user->userId)
                             ->where('role_id', 11)
                             ->pluck('userId');
                if ($dmcIds->isNotEmpty()) {
                    $query->whereIn($dmcField, $dmcIds);
                }
                break;
                
            case 19: // Virtual Master DMC
                $dmcIds = User::where('master_dmc_id', $user->userId)
                             ->whereIn('role_id', [11, 20]) // Include both regular DMC and Virtual DMC
                             ->pluck('userId');
                if ($dmcIds->isNotEmpty()) {
                    $query->whereIn($dmcField, $dmcIds);
                }
                break;
                
            case 11: // DMC
                $query->where($dmcField, $user->userId);
                break;
                
            case 20: // Virtual DMC
                $query->where($dmcField, $user->userId);
                break;
                
            case 33: // Sales Head
                $dmcUser = User::where('userId', $user->created_by)->first();
                if ($dmcUser && $dmcUser->role_id == 11) {
                    $query->where($dmcField, $dmcUser->userId);
                }
                break;
                
            case 12:
            case 37: // Sales Manager
                $salesHead = User::where('userId', $user->created_by)->first();
                if ($salesHead) {
                    $dmcUser = User::where('userId', $salesHead->created_by)->first();
                    if ($dmcUser && $dmcUser->role_id == 11) {
                        $query->where($dmcField, $dmcUser->userId);
                    }
                }
                break;
                
            case 38: // Assistant Sales Manager
                $salesManager = User::where('userId', $user->created_by)->first();
                if ($salesManager) {
                    $salesHead = User::where('userId', $salesManager->created_by)->first();
                    if ($salesHead) {
                        $dmcUser = User::where('userId', $salesHead->created_by)->first();
                        if ($dmcUser && $dmcUser->role_id == 11) {
                            $query->where($dmcField, $dmcUser->userId);
                        }
                    }
                }
                break;
        }
        
        return $query;
    }
    
    /**
     * Get agent IDs based on user role
     */
    private function getAgentIdsByUserRole($user)
    {
        $allIds = $this->getAllRelatedUserIds($user);
        
        if ($allIds->isNotEmpty()) {
            return Agent::whereIn('sales_manager_dmc', $allIds)->pluck('agent_id');
        }
        
        return collect();
    }
    
    /**
     * Get DMC ID based on user role
     */
    private function getDmcIdByUserRole($user)
    {
        switch ($user->role_id) {
            case 11: // DMC
            case 20: // Virtual DMC
                return $user->userId;
                
            case 33: // Sales Head
                $dmcUser = User::where('userId', $user->created_by)->first();
                return ($dmcUser && in_array($dmcUser->role_id, [11, 20])) ? $dmcUser->userId : null;
                
            case 12:
            case 37: // Sales Manager
                $salesHead = User::where('userId', $user->created_by)->first();
                if ($salesHead) {
                    $dmcUser = User::where('userId', $salesHead->created_by)->first();
                    return ($dmcUser && in_array($dmcUser->role_id, [11, 20])) ? $dmcUser->userId : null;
                }
                break;
                
            case 38: // Assistant Sales Manager
                $salesManager = User::where('userId', $user->created_by)->first();
                if ($salesManager) {
                    $salesHead = User::where('userId', $salesManager->created_by)->first();
                    if ($salesHead) {
                        $dmcUser = User::where('userId', $salesHead->created_by)->first();
                        return ($dmcUser && in_array($dmcUser->role_id, [11, 20])) ? $dmcUser->userId : null;
                    }
                }
                break;
        }
        
        return null;
    }
    
    /**
     * Get all related user IDs for hierarchy filtering
     */
    private function getAllRelatedUserIds($user)
    {
        // Add debugging
        Log::info("DEBUG: getAllRelatedUserIds called for user", [
            'userId' => $user->userId,
            'role_id' => $user->role_id,
            'name' => $user->name
        ]);

        switch ($user->role_id) {
            case 10: // Master DMC
                $masterDmcId = $user->userId;
                
                // Get all DMCs under this Master DMC
                $dmcs = User::where('master_dmc_id', $masterDmcId)
                           ->where('role_id', 11)
                           ->pluck('userId');
                
                Log::info("DEBUG: Master DMC found DMCs", [
                    'master_dmc_id' => $masterDmcId,
                    'dmcs_found' => $dmcs->toArray()
                ]);
                
                // Get all sales heads under these DMCs
                $salesHeads = User::whereIn('created_by', $dmcs)
                                 ->where('role_id', 33)
                                 ->pluck('userId');
                
                Log::info("DEBUG: Sales heads found", [
                    'sales_heads' => $salesHeads->toArray()
                ]);
                
                // Continue with other hierarchy levels...
                $salesManagers = User::whereIn('created_by', $salesHeads)
                                   ->whereIn('role_id', [12, 37])
                                   ->pluck('userId');
                
                $assistantManagers = User::whereIn('created_by', $salesManagers)
                                        ->where('role_id', 38)
                                        ->pluck('userId');
                
                $allIds = collect([$masterDmcId])
                    ->merge($dmcs)
                    ->merge($salesHeads)
                    ->merge($salesManagers)
                    ->merge($assistantManagers)
                    ->unique()
                    ->filter();
                
                Log::info("DEBUG: Final all IDs", [
                    'all_ids' => $allIds->toArray()
                ]);
                
                // Check how many agents this returns
                $agentCount = Agent::whereIn('sales_manager_dmc', $allIds)->count();
                Log::info("DEBUG: Agent count for these IDs", [
                    'agent_count' => $agentCount
                ]);
                
                return $allIds;
                
            case 19: // Virtual Master DMC
                $virtualMasterDmcId = $user->userId;
                
                // Get all DMCs under this Virtual Master DMC (include both regular DMC and Virtual DMC)
                $dmcs = User::where('master_dmc_id', $virtualMasterDmcId)
                           ->whereIn('role_id', [11, 20])
                           ->pluck('userId');
                
                Log::info("DEBUG: Virtual Master DMC found DMCs", [
                    'virtual_master_dmc_id' => $virtualMasterDmcId,
                    'dmcs_found' => $dmcs->toArray()
                ]);
                
                // Get all sales heads under these DMCs
                $salesHeads = User::whereIn('created_by', $dmcs)
                                 ->where('role_id', 33)
                                 ->pluck('userId');
                
                Log::info("DEBUG: Sales heads found for Virtual Master DMC", [
                    'sales_heads' => $salesHeads->toArray()
                ]);
                
                // Continue with other hierarchy levels...
                $salesManagers = User::whereIn('created_by', $salesHeads)
                                   ->whereIn('role_id', [12, 37])
                                   ->pluck('userId');
                
                $assistantManagers = User::whereIn('created_by', $salesManagers)
                                        ->where('role_id', 38)
                                        ->pluck('userId');
                
                $allIds = collect([$virtualMasterDmcId])
                    ->merge($dmcs)
                    ->merge($salesHeads)
                    ->merge($salesManagers)
                    ->merge($assistantManagers)
                    ->unique()
                    ->filter();
                
                Log::info("DEBUG: Final all IDs for Virtual Master DMC", [
                    'all_ids' => $allIds->toArray()
                ]);
                
                // Check how many agents this returns
                $agentCount = Agent::whereIn('sales_manager_dmc', $allIds)->count();
                Log::info("DEBUG: Agent count for Virtual Master DMC", [
                    'agent_count' => $agentCount
                ]);
                
                return $allIds;
                
            case 11: // DMC
                $dmcId = $user->userId;
                
                $salesHeads = User::where('created_by', $dmcId)
                                 ->where('role_id', 33)
                                 ->pluck('userId');
                
                $salesManagers = User::whereIn('created_by', $salesHeads)
                                   ->whereIn('role_id', [12, 37])
                                   ->pluck('userId');
                
                $assistantManagers = User::whereIn('created_by', $salesManagers)
                                        ->where('role_id', 38)
                                        ->pluck('userId');
                
                return collect([$dmcId])
                    ->merge($salesHeads)
                    ->merge($salesManagers)
                    ->merge($assistantManagers)
                    ->unique()
                    ->filter();
                    
            case 20: // Virtual DMC
                $virtualDmcId = $user->userId;
                
                $salesHeads = User::where('created_by', $virtualDmcId)
                                 ->where('role_id', 33)
                                 ->pluck('userId');
                
                $salesManagers = User::whereIn('created_by', $salesHeads)
                                   ->whereIn('role_id', [12, 37])
                                   ->pluck('userId');
                
                $assistantManagers = User::whereIn('created_by', $salesManagers)
                                        ->where('role_id', 38)
                                        ->pluck('userId');
                
                return collect([$virtualDmcId])
                    ->merge($salesHeads)
                    ->merge($salesManagers)
                    ->merge($assistantManagers)
                    ->unique()
                    ->filter();
                    
            case 33: // Sales Head
                $salesHeadId = $user->userId;
                
                $salesManagers = User::where('created_by', $salesHeadId)
                                   ->whereIn('role_id', [12, 37])
                                   ->pluck('userId');
                
                $assistantManagers = User::whereIn('created_by', $salesManagers)
                                        ->where('role_id', 38)
                                        ->pluck('userId');
                
                return collect([$salesHeadId])
                    ->merge($salesManagers)
                    ->merge($assistantManagers)
                    ->unique()
                    ->filter();
                
            case 12:
            case 37: // Sales Manager
                $salesManagerId = $user->userId;
                
                $assistantManagers = User::where('created_by', $salesManagerId)
                                        ->where('role_id', 38)
                                        ->pluck('userId');
                
                return collect([$salesManagerId])
                    ->merge($assistantManagers)
                    ->unique()
                    ->filter();
                
            case 38: // Assistant Sales Manager
                return collect([$user->userId]);
        }
        
        return collect();
    }
}