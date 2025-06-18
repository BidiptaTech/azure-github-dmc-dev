<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Country;
use App\Models\Tour;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Fetch all active tours with their related enquiries and bookings
        $tours = Tour::with(['enquiries', 'booking'])->where('status', 1)->get();

        // Process tours for summary
        $alltours = $tours->map(function ($tour) {
            $totalAmount = $tour->booking->sum(function ($order) {
                return collect($order->data)->sum(fn($item) => (float) data_get($item, 'totalPrice', 0));
            });

            // Calculate total discount from enquiries
            $totalDiscount = $tour->enquiries->sum('discount_amount'); // Assuming `discount_amount` exists

            return [
                'tour_id' => $tour->tour_id,
                'name' => $tour->name,
                'destination' => $tour->destination,
                'total_tours' => $tour->booking->count(), // Count of bookings per tour
                'tour_price' => $totalAmount, // Total price
                'tour_discount' => $totalDiscount, // Discount from enquiries
                'final_amount' => $totalAmount - $totalDiscount, // Final amount after discount
            ];
        })->toArray(); // Convert to an array for better compatibility in Blade

        // Compute summary
        $summary = [
            'total_tours' => count($alltours), // Number of unique tours
            'sum_total_amount' => array_sum(array_column($alltours, 'tour_price')), // Total revenue
        ];

        return view('reports.index', compact('alltours', 'summary'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getMasterDmc()
    {
        // Get all users with role_id 10 (Master DMC)
        $masterDmcs = User::where('role_id', 10)
                         ->select('id', 'name', 'country')
                         ->get();

        return response()->json($masterDmcs);
    }

    // Add new method to get countries for a specific Master DMC
    public function getMasterDmcCountries($masterId)
    {
        $masterDmc = User::where('id', $masterId)
                        ->select('country')
                        ->first();

        if ($masterDmc && $masterDmc->country) {
            // Split the country string if it contains multiple countries
            $countries = array_map('trim', explode(',', $masterDmc->country));
            return response()->json($countries);
        }

        return response()->json([]);
    }

    public function getDmc()
    {
        // Get all users with role_id 10 (Master DMC)
        $Dmcs = User::where('role_id', 11)
                     ->select('id', 'name', 'country')
                     ->get();

        return response()->json($Dmcs);
    }

    public function getDmcList()
    {
        $dmcs = User::where('role_id', 10)
                    ->select('id', 'name', 'country')
                    ->get();

        return response()->json($dmcs);
    }

    // Add this method to handle DMC countries
    public function getDmcCountries($dmcId)
    {
        $dmc = User::where('id', $dmcId)
                  ->select('country')
                  ->first();
                  
        if ($dmc && $dmc->country) {
            return response()->json([
                'status' => 'success',
                'country' => $dmc->country
            ]);
        }
        
        return response()->json([
            'status' => 'error',
            'country' => ''
        ]);
    }

    /**
     * Get active countries.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveCountries()
    {
        try {
            $countries = Country::where('is_active', 1)
                            ->select('id', 'name', 'country_code', 'currency')
                            ->orderBy('name', 'asc')
                            ->get();
            
            return response()->json([
                'success' => true,
                'countries' => $countries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching countries: ' . $e->getMessage()
            ]);
        }
    }

    public function getToursByCountry($country)
    {
        try {
            $tours = Tour::where('destination', $country)
                        ->where('status', 1)
                        ->with('booking')
                        ->get();

            $alltours = $tours->map(function ($tour) {
                $totalAmount = $tour->booking->sum(function ($order) {
                    return collect($order->data)
                        ->sum(fn($item) => (float) data_get($item, 'totalPrice', 0));
                });

                return [
                    'tour_id' => $tour->tour_id,
                    'name' => $tour->name,
                    'destination' => $tour->destination,
                    'total_tours' => $tour->booking->count(),
                    'tour_price' => $totalAmount,
                    'tour_discount' => 0,
                    'final_amount' => $totalAmount,
                ];
            });

            $summary = [
                'total_tours' => $alltours->count(),
                'sum_total_amount' => $alltours->sum('tour_price'),
            ];

            return response()->json([
                'success' => true,
                'tours' => $alltours,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tours: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get filtered data for reports.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilteredData(Request $request)
    {
        try {
            // Get filter parameters
            $dateRange = $request->input('dateRange');
            $role = $request->input('role');
            $userType = $request->input('userType');
            $country = $request->input('country');
            $status = $request->input('status'); // Added status parameter
            
            // Parse date range if provided
            $startDate = null;
            $endDate = null;
            
            if (!empty($dateRange)) {
                $dates = explode(' - ', $dateRange);
                if (count($dates) == 2) {
                    $startDate = $dates[0];
                    $endDate = $dates[1];
                }
            }
            
            // Base queries for all tour types
            $completedToursQuery = Tour::where('status', 1)->with('booking');
            $progressToursQuery = Tour::where('status', 2)->with('booking');
            $cancelToursQuery = Tour::where('status', 3)->with('booking');
            $enquiredToursQuery = Tour::where('status', 4)->with('booking');
            
            // Apply date range filter if provided
            if ($startDate && $endDate) {
                $dateFilter = function ($query) use ($startDate, $endDate) {
                    $query->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('check_in_time', [$startDate, $endDate])
                          ->orWhereBetween('check_out_time', [$startDate, $endDate])
                          ->orWhere(function ($q2) use ($startDate, $endDate) {
                              $q2->where('check_in_time', '<=', $startDate)
                                 ->where('check_out_time', '>=', $endDate);
                          });
                    });
                };
                
                $completedToursQuery->where($dateFilter);
                $progressToursQuery->where($dateFilter);
                $cancelToursQuery->where($dateFilter);
                $enquiredToursQuery->where($dateFilter);
            }
            
            // Apply country filter if provided
            if (!empty($country)) {
                $completedToursQuery->where('destination', $country);
                $progressToursQuery->where('destination', $country);
                $cancelToursQuery->where('destination', $country);
                $enquiredToursQuery->where('destination', $country);
            }
            
            // Apply role and userType filters if provided
            if (!empty($role) && !empty($userType)) {
                if ($role == '1') { // Master DMC
                    // If country was already specified through the country dropdown, no additional filtering needed
                } else if ($role == '2') { // DMC
                    $completedToursQuery->where('master_dmc_id', $userType);
                    $progressToursQuery->where('master_dmc_id', $userType);
                    $cancelToursQuery->where('master_dmc_id', $userType);
                    $enquiredToursQuery->where('master_dmc_id', $userType);
                } else if ($role == '3') { // Agent
                    $completedToursQuery->where('dmc_id', $userType);
                    $progressToursQuery->where('dmc_id', $userType);
                    $cancelToursQuery->where('dmc_id', $userType);
                    $enquiredToursQuery->where('dmc_id', $userType);
                }
            }
            
            // Get filtered results
            $completedTours = $completedToursQuery->get();
            $progressTours = $progressToursQuery->get();
            $cancelTours = $cancelToursQuery->get();
            $enquiredTours = $enquiredToursQuery->get();
            
            // Process all tour types
            $completedToursData = $this->processTours($completedTours);
            $progressToursData = $this->processTours($progressTours);
            $cancelToursData = $this->processTours($cancelTours);
            $enquiredToursData = $this->processTours($enquiredTours);
            
            // Calculate overall summary
            $totalTours = $completedToursData->count() + $progressToursData->count() + $cancelToursData->count() + $enquiredToursData->count();
            $totalAmount = $completedToursData->sum('final_amount') + $progressToursData->sum('final_amount') + $cancelToursData->sum('final_amount') + $enquiredToursData->sum('final_amount');
            
            return response()->json([
                'success' => true,
                'completedTours' => $completedToursData,
                'progressTours' => $progressToursData,
                'cancelTours' => $cancelToursData,
                'enquiredTours' => $enquiredToursData,
                'summary' => [
                    'total_tours' => $totalTours,
                    'sum_total_amount' => $totalAmount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching filtered data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process tour data for display
     *
     * @param \Illuminate\Database\Eloquent\Collection $tours
     * @return \Illuminate\Support\Collection
     */
    private function processTours($tours)
    {
        return $tours->map(function ($tour) {
            $totalAmount = $tour->booking->sum(function ($order) {
                return collect($order->data)
                    ->sum(fn($item) => (float) data_get($item, 'totalPrice', 0));
            });

            // Example calculation for discount (customize based on your business logic)
            $discount = $tour->discount ?? 0;
            $finalAmount = $totalAmount - $discount;

            return [
                'tour_id' => $tour->tour_id,
                'name' => $tour->name,
                'destination' => $tour->destination,
                'total_tour' => $tour->booking->count(), // Count of orders per tour
                'tour_price' => $totalAmount, // Total price before discount
                'tour_discount' => $discount, // Discount amount
                'final_amount' => $finalAmount, // Final amount after discount
            ];
        });
    }

    public function getToursByStatus(Request $request)
    {
        try {
            $status = $request->input('status');
            $tours = Tour::where('status', $status)->with('booking')->get();

            $alltours = $tours->map(function ($tour) {
                $totalAmount = $tour->booking->sum(function ($order) {
                    return collect($order->data)
                        ->sum(fn($item) => (float) data_get($item, 'totalPrice', 0));
                });

                return [
                    'tour_id' => $tour->tour_id,
                    'name' => $tour->name,
                    'destination' => $tour->destination,
                    'total_tours' => $tour->booking->count(),
                    'tour_price' => $totalAmount,
                    'tour_discount' => 0,
                    'final_amount' => $totalAmount,
                ];
            });

            $summary = [
                'total_tours' => $alltours->count(),
                'sum_total_amount' => $alltours->sum('tour_price'),
            ];

            return response()->json([
                'success' => true,
                'alltours' => $alltours,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tours: ' . $e->getMessage()
            ]);
        }
    }
    
}
