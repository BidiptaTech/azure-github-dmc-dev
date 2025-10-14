<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guest;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class GuestController extends Controller
{
    /**
     * Display the guest management page.
     */
    public function index(Request $request)
    {
        try {
            $tourId = $request->query('tour_id');
            return view('single-tour-package.guests', compact('tourId'));
        } catch (\Exception $e) {
            Log::error('Error loading guests page: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading guests page.');
        }
    }

    /**
     * Get guests data for DataTables.
     */
    public function getGuests(Request $request)
    {
        try {
            $query = Guest::select('id', 'guest_id', 'tour_id', 'guest_name', 'email', 'contact', 'created_at', 'updated_at');
            
            // Filter by tour_id if provided
            if ($request->has('tour_id') && $request->tour_id) {
                $query->where('tour_id', $request->tour_id);
            }
            
            $guests = $query->orderBy('created_at', 'desc');

            return DataTables::of($guests)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-primary edit-guest" 
                        data-guest-id="' . $row->guest_id . '" 
                        data-tour-id="' . ($row->tour_id ?? '') . '" 
                        data-guest-name="' . htmlspecialchars($row->guest_name) . '" 
                        data-email="' . ($row->email ?? '') . '" 
                        data-country-code="' . ($row->country_code ?? '+91') . '" 
                        data-contact="' . ($row->contact ?? '') . '" 
                        data-app-password="' . ($row->app_password ?? '') . '" 
                        title="Edit">
                        <i class="ri-edit-line"></i>
                    </button>';
                    
                    $deleteBtn = '<button type="button" class="btn btn-sm btn-icon btn-danger delete-guest" 
                        data-guest-id="' . $row->guest_id . '" 
                        title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </button>';
                    
                    return '<div class="d-flex gap-2">' . $editBtn . ' ' . $deleteBtn . '</div>';
                })
                ->addColumn('created_at_formatted', function ($row) {
                    return $row->created_at ? $row->created_at->format('M d, Y H:i A') : 'N/A';
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error fetching guests: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error fetching guests: ' . $e->getMessage()
            ], 200);
        }
    }

    /**
     * Store a new guest.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'guest_name' => 'required|string|max:255',
                'tour_id' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'country_code' => 'nullable|string|max:10',
                'contact' => 'nullable|string|max:255',
                'app_password' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate unique guest_id
            $lastGuest = Guest::withTrashed()->orderBy('created_at', 'desc')->first();
            $lastGuestId = $lastGuest->guest_id ?? 0;
            $guestId = CommonHelper::createId($lastGuestId);
            
            // Ensure uniqueness
            while (Guest::where('guest_id', $guestId)->exists()) {
                $guestId = CommonHelper::createId($guestId);
            }

            $guest = Guest::create([
                'guest_id' => $guestId,
                'tour_id' => $request->tour_id,
                'guest_name' => $request->guest_name,
                'email' => $request->email,
                'country_code' => $request->country_code ?? '+91',
                'contact' => $request->contact,
                'app_password' => $request->app_password,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Guest created successfully',
                'data' => $guest
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating guest: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating guest: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing guest.
     */
    public function update(Request $request, $guestId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'guest_name' => 'required|string|max:255',
                'tour_id' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'country_code' => 'nullable|string|max:10',
                'contact' => 'nullable|string|max:255',
                'app_password' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Try to find by guest_id first, if not found, try by id (auto-increment)
            $guest = Guest::where('guest_id', $guestId)->first();
            if (!$guest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guest not found'
                ], 404);
            }
            
            $guest->update([
                'tour_id' => $request->tour_id,
                'guest_name' => $request->guest_name,
                'email' => $request->email,
                'country_code' => $request->country_code ?? '+91',
                'contact' => $request->contact,
                'app_password' => $request->app_password,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Guest updated successfully',
                'data' => $guest
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating guest: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating guest: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a guest (soft delete).
     */
    public function destroy($guestId)
    {
        try {
            // Try to find by guest_id first, if not found, try by id (auto-increment)
            $guest = Guest::where('guest_id', $guestId)->first();
            if (!$guest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guest not found'
                ], 404);
                // $guest = Guest::findOrFail($guestId);
            }
            $guest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Guest deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting guest: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting guest: ' . $e->getMessage()
            ], 500);
        }
    }
}
