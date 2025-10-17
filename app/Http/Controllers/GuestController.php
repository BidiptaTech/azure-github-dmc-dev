<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guest;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Mail;
use App\Mail\DmcMail;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Models\Order;

class GuestController extends Controller
{
    /**
     * Display the guest management page.
     */
    public function index(Request $request)
    {
        try {
            $tourId = Crypt::decrypt($request->query('tour_id'));
            $order = Order::where('tour_id', $tourId)->orderBy('booking_id', 'asc')->first();
            $data = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            $fullName = $data[0]['fullName'] ?? '';
            $email = $data[0]['email'] ?? '';
            $phone = $data[0]['phone'] ?? '';
            $countryCode = $data[0]['countryCode'] ?? '';
            $guests = Guest::where('tour_id', $tourId)->get();
            return view('single-tour-package.guests', compact('tourId', 'fullName', 'email', 'phone', 'guests', 'countryCode'));
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

            // Store plain password for email before hashing
            $plainPassword = $request->app_password;

            $guest = Guest::create([
                'guest_id' => $guestId,
                'tour_id' => $request->tour_id,
                'guest_name' => $request->guest_name,
                'email' => $request->email,
                'country_code' => $request->country_code ?? '+91',
                'contact' => $request->contact,
                'app_password' => $plainPassword ? Hash::make($plainPassword) : null,
            ]);

            // Send credentials email if email is provided
            if ($guest->email && $plainPassword) {
                try {
                    $this->sendGuestCredentialsEmail($guest, $plainPassword);
                } catch (\Exception $e) {
                    Log::warning('Failed to send guest credentials email: ' . $e->getMessage());
                    // Don't fail the request if email sending fails
                }
            }

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
            
            // Prepare update data
            $updateData = [
                'tour_id' => $request->tour_id,
                'guest_name' => $request->guest_name,
                'email' => $request->email,
                'country_code' => $request->country_code ?? '+91',
                'contact' => $request->contact,
            ];
            
            // Hash password if provided
            if ($request->app_password) {
                $updateData['app_password'] = Hash::make($request->app_password);
            }
            
            $guest->update($updateData);

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

    /**
     * Send guest credentials email
     * This method sends a welcome email with login credentials to the newly created guest
     * 
     * @param Guest $guest
     * @param string $plainPassword
     * @return bool
     */
    private function sendGuestCredentialsEmail(Guest $guest, string $plainPassword)
    {
        try {
            // Get company settings for branding
            $logoSetting = Setting::where('name', 'logo')->where('status', 1)->first();
            $nameSetting = Setting::where('name', 'name')->where('status', 1)->first();
            $supportEmailSetting = Setting::where('name', 'support_email')->first();
            $supportPhoneSetting = Setting::where('name', 'support_phone')->first();
            
            $companyLogo = $logoSetting ? $logoSetting->value : null;
            $companyName = $nameSetting ? $nameSetting->value : config('app.name');
            $supportEmail = $supportEmailSetting ? $supportEmailSetting->value : null;
            $supportPhone = $supportPhoneSetting ? $supportPhoneSetting->value : null;
            
            // Prepare email data (use plain password for email, not the hashed one)
            $emailData = [
                'guest_name' => $guest->guest_name,
                'email' => $guest->email,
                'app_password' => $plainPassword,
                'country_code' => $guest->country_code ?? '+91',
                'contact' => $guest->contact,
                'tour_id' => $guest->tour_id,
                'company_name' => $companyName,
                'company_logo' => $companyLogo,
                'support_email' => $supportEmail,
                'support_phone' => $supportPhone,
            ];
            
            // Render the email template
            $html = view('mails.guest_credentials', $emailData)->render();
            
            // Extract styles and email container
            preg_match('/<style>(.*?)<\/style>/s', $html, $styleMatches);
            $styles = !empty($styleMatches[0]) ? $styleMatches[0] : '';
            
            // Extract the email-container div
            preg_match('/<div class="email-container">(.*?)<\/div>\s*<\/body>/s', $html, $matches);
            
            if (!empty($matches[0])) {
                $extractedHtml = $matches[0];
                
                // Build complete email HTML
                $subject = 'Welcome! Your Tour Tracking Credentials';
                $emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . $subject . '</title>' . $styles . '</head><body>' . $extractedHtml . '</body></html>';
                
                // Send the email
                Mail::to($guest->email)->send(new DmcMail($emailHtml, $subject));
                
                Log::info("Guest credentials email sent successfully to: {$guest->email}", [
                    'guest_id' => $guest->guest_id,
                    'guest_name' => $guest->guest_name,
                ]);
                
                return true;
            } else {
                Log::error("Email container div not found in guest credentials template");
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send guest credentials email', [
                'error' => $e->getMessage(),
                'guest_id' => $guest->guest_id ?? null,
                'email' => $guest->email ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
