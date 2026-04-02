<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Agent;
use App\Models\Package;
use App\Models\PackageBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Agency;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Guide;
use App\Models\Restaurant;

class PackageBookingController extends Controller
{
    public function create($package_id = null)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $dmcId = CommonHelper::getDmcId($user);
        $prefilledPackageId = null;
        if (!empty($package_id)) {
            try {
                $prefilledPackageId = Crypt::decrypt($package_id);
            } catch (\Throwable $e) {
                $prefilledPackageId = $package_id;
            }
        }
        $agencies = Agency::whereJsonContains('dmc_id', (int)$dmcId)->get();

        $agents = Agent::whereIn('agency_id', $agencies->pluck('agency_id'))
            ->orderBy('name')
            ->select('agent_id', 'name', 'company_name')
            ->get();

        return view('package.package-booking', [
            'agencies' => $agencies,
            'prefilledPackageId' => $prefilledPackageId,
        ]);
    }

    public function getAgentsByAgency(Request $request)
    {
        try {
            $agencyId = $request->query('agency_id');
            if (!$agencyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agency ID is required',
                ], 400);
            }
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
            $dmcId = CommonHelper::getDmcId($user);
            
            $agents = Agent::where('agency_id', $agencyId)
                ->orderBy('name')
                ->select('agent_id', 'name', 'company_name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Agents fetched successfully',
                'agents' => $agents,
            ]);
        } catch (\Throwable $e) {
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching agents',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function filterPackages(Request $request)
    {
        $validated = $request->validate([
            'travel_start_date' => 'required|date',
            'travel_end_date' => 'required|date|after_or_equal:travel_start_date',
            'adult_count' => 'required|integer|min:1',
            'child_count' => 'nullable|integer|min:0',
        ]);

        $startDate = Carbon::parse($validated['travel_start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['travel_end_date'])->startOfDay();
        $durationDays = $startDate->diffInDays($endDate) + 1;
        $totalPax = (int) $validated['adult_count'] + (int) ($validated['child_count'] ?? 0);

        $packages = Package::query()
            ->whereDate('start_date', '<=', $startDate->toDateString())
            ->whereDate('expire_date', '>=', $endDate->toDateString())
            ->orderBy('title')
            ->get(['package_id', 'title', 'destination', 'city', 'duration_days', 'max_pax', 'start_date', 'expire_date'])
            ->map(function ($package) {
                return [
                    'package_id' => $package->package_id,
                    'title' => (string) $package->title,
                    'destination' => (string) $package->destination,
                    'city' => (string) $package->city,
                    'duration_days' => (int) ($package->duration_days ?? 0),
                    'max_pax' => (int) ($package->max_pax ?? 0),
                    'start_date' => (string) $package->start_date,
                    'expire_date' => (string) $package->expire_date,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'duration_days' => $durationDays,
            'total_pax' => $totalPax,
            'packages' => $packages,
        ]);
    }

    public function packageDetails($packageId)
    {
        $package = Package::where('package_id', $packageId)->first();
        if (!$package) {
            return response()->json(['success' => false, 'message' => 'Package not found.'], 404);
        }

        $user = Auth::user();
        $dmcId = CommonHelper::getDmcId($user);
        
        $country =  $package->destination;
        $city = $package->city;

        $hotels = Hotel::where('country', $country)->where('city', $city)->whereJsonContains('dmc_id', $dmcId)->get();
        $attractions = Attraction::where('country', $country)->where('location', $city)->whereJsonContains('dmc_id', $dmcId)->get();
        $guides = Guide::where('country', $country)->where('city', $city)->where('dmc_id', $dmcId)->get();
        $restaurants = Restaurant::where('country', $country)->where('city', $city)->whereJsonContains('dmc_id', $dmcId)->get();

        return response()->json([
            'success' => true,
            'package' => [
                'package_id' => $package->package_id,
                'title' => (string) $package->title,
                'destination' => (string) $package->destination,
                'city' => (string) $package->city,
                'selected_hotels' => $this->parseJsonField($package->selected_hotels),
                'selected_attractions' => $this->parseJsonField($package->selected_attractions),
                'selected_guides' => $this->parseJsonField($package->selected_guide),
                'selected_restaurants' => $this->parseJsonField($package->selected_restaurants),
                'arrival_data' => $this->parseJsonField($package->arrival_data),
                'departure_data' => $this->parseJsonField($package->departure_data),
                'transfer_data' => $this->parseJsonField($package->transfer_data),
                'price_adult' => (float) ($package->price_adult ?? 0),
                'price_child' => (float) ($package->price_child ?? 0),
                'duration_days' => (int) ($package->duration_days ?? 1),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|string',
            'travel_start_date' => 'required|date',
            'travel_end_date' => 'required|date|after_or_equal:travel_start_date',
            'adult_count' => 'required|integer|min:1',
            'child_count' => 'nullable|integer|min:0',
            'agent_id' => 'nullable',
            'selected_hotels' => 'nullable|string',
            'selected_attractions' => 'nullable|string',
            'selected_guides' => 'nullable|string',
            'selected_restaurants' => 'nullable|string',
            'arrival_data' => 'nullable|string',
            'departure_data' => 'nullable|string',
            'transfer_data' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $package = Package::where('package_id', $validated['package_id'])->first();
        if (!$package) {
            return back()->withInput()->with('error', 'Selected package not found.');
        }

        $selectedHotels = $this->parseJsonField($validated['selected_hotels'] ?? '[]');
        $selectedAttractions = $this->parseJsonField($validated['selected_attractions'] ?? '[]');
        $selectedGuides = $this->parseJsonField($validated['selected_guides'] ?? '[]');
        $selectedRestaurants = $this->parseJsonField($validated['selected_restaurants'] ?? '[]');
        $arrivalData = $this->parseJsonField($validated['arrival_data'] ?? '{}');
        $departureData = $this->parseJsonField($validated['departure_data'] ?? '{}');
        $transferData = $this->parseJsonField($validated['transfer_data'] ?? '[]');

        $adultCount = (int) ($validated['adult_count'] ?? 0);
        $childCount = (int) ($validated['child_count'] ?? 0);
        $totalPax = $adultCount + $childCount;

        $startDate = Carbon::parse($validated['travel_start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['travel_end_date'])->startOfDay();
        $duration = $startDate->diffInDays($endDate) + 1;
        if ((int) ($package->duration_days ?? 0) !== $duration) {
            return back()->withInput()->with('error', 'Selected package does not match the chosen date duration.');
        }

        $totalPrice = ($adultCount * (float) ($package->price_adult ?? 0))
            + ($childCount * (float) ($package->price_child ?? 0));

        $itineraryDates = [];
        for ($i = 0; $i < $duration; $i++) {
            $itineraryDates[] = [
                'day' => $i + 1,
                'date' => $startDate->copy()->addDays($i)->format('Y-m-d'),
            ];
        }

        try {
            DB::beginTransaction();

            $lastBooking = PackageBooking::withTrashed()->orderBy('id', 'desc')->first();
            $bookingIdRaw = (string) ($lastBooking->booking_id ?? '');
            $bookingNumeric = (int) preg_replace('/\D+/', '', $bookingIdRaw);
            $nextNumeric = CommonHelper::createId($bookingNumeric);
            $bookingId = 'PB' . str_pad((string) $nextNumeric, 5, '0', STR_PAD_LEFT);

            $user = Auth::user();
            $dmcId = null;
            if ($user) {
                $resolvedDmc = CommonHelper::getDmcId($user);
                $dmcId = $resolvedDmc ?: $user->userId;
            }

            $bookingDetails = [
                'adult_count' => $adultCount,
                'child_count' => $childCount,
                'total_pax' => $totalPax,
                'total_price' => $totalPrice,
                'currency' => 'SGD',
                'itinerary' => $itineraryDates,
                'notes' => $validated['notes'] ?? '',
                'arrival_data' => $arrivalData,
                'departure_data' => $departureData,
                'transfer_data' => $transferData,
            ];

            $travelDates = [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'duration_days' => $duration,
            ];

            PackageBooking::create([
                'booking_id' => $bookingId,
                'package_id' => $package->package_id,
                'booking_details' => $bookingDetails,
                'travel_dates' => $travelDates,
                'selected_hotels' => $selectedHotels,
                'selected_attractions' => $selectedAttractions,
                'selected_guides' => $selectedGuides,
                'selected_restaurants' => $selectedRestaurants,
                'status' => '1',
                'booked_by' => $user?->userId,
                'agent_id' => $validated['agent_id'] ?: null,
                'dmc_id' => $dmcId,
                'package' => [
                    'package_id' => $package->package_id,
                    'title' => $package->title,
                    'destination' => $package->destination,
                    'city' => $package->city,
                ],
                'user_info' => $user ? [
                    'user_id' => $user->userId,
                    'name' => $user->name ?? '',
                    'email' => $user->email ?? '',
                ] : null,
                'taxes' => [],
                'payment_details' => [],
            ]);

            DB::commit();
            return redirect()->route('predefined.package.booking.list')
                ->with('success', 'Package booking created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Package booking create failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Failed to create package booking.');
        }
    }

    private function parseJsonField($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if (!is_string($value) || trim($value) === '') {
            return [];
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }
}
