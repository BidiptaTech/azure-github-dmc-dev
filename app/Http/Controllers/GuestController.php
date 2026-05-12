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
use App\Models\Tour;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Models\Order;
use App\Models\User;

class GuestController extends Controller
{
    private function normalizeTourIds($tourIds): array
    {
        if (is_null($tourIds) || $tourIds === '') {
            return [];
        }

        if (!is_array($tourIds)) {
            $tourIds = strpos((string) $tourIds, ',') !== false
                ? explode(',', (string) $tourIds)
                : [$tourIds];
        }

        $normalized = [];

        foreach ($tourIds as $tourId) {
            $tourId = is_string($tourId) ? trim($tourId) : $tourId;

            if ($tourId === '' || is_null($tourId)) {
                continue;
            }

            $normalized[] = is_numeric($tourId) ? (int) $tourId : $tourId;
        }

        return array_values(array_unique($normalized, SORT_REGULAR));
    }

    private function syncGuestIdsToFirebase(array $tourIds, $guestId): array
    {
        $results = [];

        foreach ($this->normalizeTourIds($tourIds) as $tourId) {
            $tour = Tour::query()
                ->select(['tour_id', 'dmc_id'])
                ->where('tour_id', $tourId)
                ->first();

            if (!$tour || empty($tour->dmc_id)) {
                Log::warning('Skipping Firebase guest sync due to missing tour or DMC ID', [
                    'tour_id' => $tourId,
                    'guest_id' => $guestId,
                ]);
                continue;
            }

            try {
                $results[] = app(FirebaseService::class)->upsertChatGuest(
                    (int) $tour->tour_id,
                    (int) $tour->dmc_id,
                    (int) $guestId
                );
            } catch (\Throwable $e) {
                report($e);

                Log::error('Firebase guest sync failed', [
                    'tour_id' => $tourId,
                    'guest_id' => $guestId,
                    'error' => $e->getMessage(),
                ]);

                $results[] = [
                    'success' => false,
                    'tour_id' => $tourId,
                    'guest_id' => (int) $guestId,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    private function removeGuestIdsFromFirebase(array $tourIds, $guestId): array
    {
        $results = [];

        foreach ($this->normalizeTourIds($tourIds) as $tourId) {
            try {
                $results[] = app(FirebaseService::class)->removeChatGuest(
                    $tourId,
                    (int) $guestId
                );
            } catch (\Throwable $e) {
                report($e);

                Log::error('Firebase guest removal failed', [
                    'tour_id' => $tourId,
                    'guest_id' => $guestId,
                    'error' => $e->getMessage(),
                ]);

                $results[] = [
                    'success' => false,
                    'tour_id' => $tourId,
                    'guest_id' => (int) $guestId,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Display the guest management page.
     */
    public function index(Request $request)
    {
        try {
            $tourId = Crypt::decrypt($request->query('tour_id'));
            
            // Convert to integer if numeric
            $tourIdInt = is_numeric($tourId) ? (int)$tourId : $tourId;
            
            $order = Order::where('tour_id', $tourId)->orderBy('booking_id', 'asc')->first();
            $data = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            $fullName = $data[0]['fullName'] ?? '';
            $email = $data[0]['email'] ?? '';
            $phone = $data[0]['phone'] ?? '';
            $countryCode = $data[0]['countryCode'] ?? '';
            
            // Query guests using JSON contains for array field
            $guests = Guest::whereJsonContains('tour_id', $tourIdInt)->get();
            
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
            $query = Guest::select('id', 'guest_id', 'tour_id', 'guest_name', 'email', 'contact', 'country_code', 'whatsapp_no', 'created_at', 'updated_at');
            
            // Filter by tour_id if provided (using JSON contains for array field)
            if ($request->has('tour_id') && $request->tour_id) {
                $tourId = is_numeric($request->tour_id) ? (int)$request->tour_id : $request->tour_id;
                $query->whereJsonContains('tour_id', $tourId);
            }
            
            $guests = $query->orderBy('created_at', 'desc');

            return DataTables::of($guests)
                ->addIndexColumn()
                ->addColumn('tour_id', function ($row) {
                    // Format tour_id array as comma-separated string or badges
                    $tourIds = $row->tour_id ?? [];
                    if (empty($tourIds)) {
                        return '<span class="text-muted">N/A</span>';
                    }
                    
                    $badges = array_map(function($id) {
                        return '<span class="badge bg-success me-1">' . htmlspecialchars($id) . '</span>';
                    }, $tourIds);
                    
                    return implode('', $badges);
                })
                ->addColumn('action', function ($row) {
                    // Convert tour_id array to JSON string for data attribute
                    $tourIdJson = json_encode($row->tour_id ?? []);
                    
                    $editBtn = '<button type="button" class="btn btn-sm btn-icon btn-primary edit-guest" 
                        data-guest-id="' . $row->guest_id . '" 
                        data-tour-id=\'' . htmlspecialchars($tourIdJson, ENT_QUOTES) . '\' 
                        data-guest-name="' . htmlspecialchars($row->guest_name) . '" 
                        data-email="' . ($row->email ?? '') . '" 
                        data-country-code="' . ($row->country_code ?? '+91') . '" 
                        data-contact="' . ($row->contact ?? '') . '" 
                        data-whatsapp-no="' . ($row->whatsapp_no ?? '') . '" 
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
                ->addColumn('whatsapp_no', function ($row) {
                    return $row->whatsapp_no ? htmlspecialchars($row->whatsapp_no) : '<span class="text-muted">N/A</span>';
                })
                ->addColumn('created_at_formatted', function ($row) {
                    return $row->created_at ? $row->created_at->format('M d, Y H:i A') : 'N/A';
                })
                ->rawColumns(['action', 'tour_id', 'whatsapp_no'])
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
                'whatsapp_no' => 'nullable|string|max:255',
                'app_password' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if email already exists
            $existingGuest = null;
            if ($request->email) {
                $existingGuest = Guest::where('email', $request->email)->first();
            }
            
            // Store plain password for email before hashing
            $plainPassword = $request->app_password;
            
            // If guest exists with this email
            if ($existingGuest) {
                $firebaseSync = [];

                // If tour_id is provided and different, add it to the array
                if ($request->tour_id) {
                    $tourId = is_numeric($request->tour_id) ? (int)$request->tour_id : $request->tour_id;
                    
                    if (!$existingGuest->hasTourId($tourId)) {
                        $existingGuest->addTourId($tourId);
                        
                        Log::info('Tour ID added to existing guest', [
                            'guest_id' => $existingGuest->guest_id,
                            'email' => $existingGuest->email,
                            'new_tour_id' => $tourId,
                            'all_tour_ids' => $existingGuest->tour_id
                        ]);
                    }

                    $firebaseSync = $this->syncGuestIdsToFirebase([$tourId], $existingGuest->guest_id);
                }
                
                // Update password if provided
                if ($plainPassword) {
                    $existingGuest->app_password = Hash::make($plainPassword);
                    $existingGuest->save();
                    
                    Log::info('Guest password updated', [
                        'guest_id' => $existingGuest->guest_id,
                        'email' => $existingGuest->email
                    ]);
                }
                
                // Send credentials email with updated information
                if ($existingGuest->email && $plainPassword) {
                    try {
                        // Resolve display_id for the current tour context if provided
                        $currentTourDisplayId = null;
                        if ($request->tour_id) {
                            $tourIdForEmail = is_numeric($request->tour_id) ? (int)$request->tour_id : $request->tour_id;
                            $currentTourDisplayId = Tour::where('tour_id', $tourIdForEmail)->value('display_id');
                        }

                        $this->sendGuestCredentialsEmail($existingGuest, $plainPassword, $currentTourDisplayId);
                        Log::info('Credentials email sent to existing guest', [
                            'guest_id' => $existingGuest->guest_id,
                            'email' => $existingGuest->email
                        ]);
                    } catch (\Exception $e) {
                        Log::warning('Failed to send credentials email to existing guest: ' . $e->getMessage());
                        // Don't fail the request if email sending fails
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Guest already exists. Tour ID added and credentials updated.',
                    'data' => $existingGuest,
                    'existing_guest' => true,
                    'firebase_sync' => $firebaseSync,
                ], 200);
            }

            // Generate unique guest_id for new guest
            $lastGuest = Guest::withTrashed()->orderBy('created_at', 'desc')->first();
            $lastGuestId = $lastGuest->guest_id ?? 0;
            $guestId = CommonHelper::createId($lastGuestId);
            
            // Ensure uniqueness
            while (Guest::where('guest_id', $guestId)->exists()) {
                $guestId = CommonHelper::createId($guestId);
            }

            // Use default avatar image from project root (deployed with code)
            $defaultAvatarPath = base_path('avatar-1577909_1280.png');
            $imagePath = null;
            
            if (file_exists($defaultAvatarPath)) {
                try {
                    // Create a file object from the default avatar
                    $imageFile = new \Illuminate\Http\UploadedFile(
                        $defaultAvatarPath,
                        'avatar-1577909_1280.png',
                        'image/png',
                        null,
                        true
                    );
                    
                    // Upload to Azure Storage using CommonHelper
                    $uploadResult = CommonHelper::image_path('file_storage', $imageFile);
                    if (!empty($uploadResult['master_value'])) {
                        $imagePath = $uploadResult['master_value'];
                        Log::info('Guest default avatar uploaded to Azure successfully', ['path' => $imagePath]);
                    } else {
                        Log::warning('Default avatar upload returned empty result');
                    }
                } catch (\Exception $e) {
                    Log::error('Error uploading guest default avatar: ' . $e->getMessage());
                    // Continue with guest creation even if image upload fails
                }
            } else {
                Log::warning('Default avatar file not found at: ' . $defaultAvatarPath);
            }

            // Prepare tour_id as integer array
            $tourIds = [];
            if ($request->tour_id) {
                $tourId = is_numeric($request->tour_id) ? (int)$request->tour_id : $request->tour_id;
                $tourIds = [$tourId];
            }

            $guest = Guest::create([
                'guest_id' => $guestId,
                'tour_id' => $tourIds,
                'guest_name' => $request->guest_name,
                'email' => $request->email,
                'country_code' => $request->country_code ?? '+91',
                'contact' => $request->contact,
                'whatsapp_no' => $request->whatsapp_no,
                'app_password' => $plainPassword ? Hash::make($plainPassword) : null,
                'image' => $imagePath,
            ]);

            $firebaseSync = $this->syncGuestIdsToFirebase($tourIds, $guest->guest_id);

            // Send credentials email if email is provided
            if ($guest->email && $plainPassword) {
                try {
                    // Resolve display_id for the current tour context if available
                    $currentTourDisplayId = null;
                    if (!empty($tourIds)) {
                        $tourIdForEmail = end($tourIds);
                        $currentTourDisplayId = Tour::where('tour_id', $tourIdForEmail)->value('display_id');
                    }

                    $this->sendGuestCredentialsEmail($guest, $plainPassword, $currentTourDisplayId);
                } catch (\Exception $e) {
                    Log::warning('Failed to send guest credentials email: ' . $e->getMessage());
                    // Don't fail the request if email sending fails
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Guest created successfully',
                'data' => $guest,
                'existing_guest' => false,
                'firebase_sync' => $firebaseSync,
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
                'whatsapp_no' => 'nullable|string|max:255',
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

            $previousTourIds = $this->normalizeTourIds($guest->tour_id);
            
            // Store plain password for email before hashing
            $plainPassword = $request->app_password;
            
            // Prepare update data (excluding tour_id for now)
            $updateData = [
                'guest_name' => $request->guest_name,
                'email' => $request->email,
                'country_code' => $request->country_code ?? '+91',
                'contact' => $request->contact,
                'whatsapp_no' => $request->whatsapp_no,
            ];
            
            // Hash password if provided
            if ($plainPassword) {
                $updateData['app_password'] = Hash::make($plainPassword);
            }
            
            // Handle tour_id separately - it could be comma-separated or single value
            if ($request->has('tour_id') && $request->tour_id) {
                // Split by comma if multiple tour IDs are provided
                $tourIdInput = $request->tour_id;
                $tourIdArray = [];
                
                // Check if it contains commas (multiple tour IDs)
                if (strpos($tourIdInput, ',') !== false) {
                    // Split and trim
                    $tourIdParts = explode(',', $tourIdInput);
                    foreach ($tourIdParts as $part) {
                        $part = trim($part);
                        if (!empty($part)) {
                            $tourIdArray[] = is_numeric($part) ? (int)$part : $part;
                        }
                    }
                } else {
                    // Single tour ID
                    $tourIdInput = trim($tourIdInput);
                    if (!empty($tourIdInput)) {
                        $tourIdArray[] = is_numeric($tourIdInput) ? (int)$tourIdInput : $tourIdInput;
                    }
                }
                
                $updateData['tour_id'] = $tourIdArray;
            }
            
            $guest->update($updateData);
            $guest->refresh();

            $currentTourIds = $this->normalizeTourIds($guest->tour_id);
            $removedTourIds = array_values(array_diff($previousTourIds, $currentTourIds));
            $firebaseSync = $this->syncGuestIdsToFirebase($currentTourIds, $guest->guest_id);
            $firebaseRemoved = $this->removeGuestIdsFromFirebase($removedTourIds, $guest->guest_id);

            // Send update notification email if email and password are provided
            if ($guest->email && $plainPassword) {
                try {
                    $this->sendGuestUpdateEmail($guest, $plainPassword);
                    Log::info('Update email sent to guest', [
                        'guest_id' => $guest->guest_id,
                        'email' => $guest->email
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to send update email to guest: ' . $e->getMessage());
                    // Don't fail the request if email sending fails
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Guest updated successfully',
                'data' => $guest,
                'firebase_sync' => $firebaseSync,
                'firebase_removed' => $firebaseRemoved,
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

            $tourIds = $this->normalizeTourIds($guest->tour_id);
            $guest->delete();
            $firebaseRemoved = $this->removeGuestIdsFromFirebase($tourIds, $guest->guest_id);

            return response()->json([
                'success' => true,
                'message' => 'Guest deleted successfully',
                'firebase_removed' => $firebaseRemoved,
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
     * This method sends a welcome email with login credentials to the newly created guest.
     * Optionally accepts the current tour display ID to avoid ambiguity when a guest
     * is linked to multiple tours.
     * 
     * @param Guest $guest
     * @param string $plainPassword
     * @param string|null $currentTourDisplayId
     * @return bool
     */
    private function sendGuestCredentialsEmail(Guest $guest, string $plainPassword, ?string $currentTourDisplayId = null)
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
            
            // Get DMC company name (the company that initiated the invitation)
            $dmcId = CommonHelper::getDmcId(auth()->user());
            $dmc = User::where('userId', $dmcId)->first();
            $dmcCompanyName = $dmc->company_name ?? null;

            // Prefer explicitly provided tour display ID (from the current context)
            $tourDisplayId = $currentTourDisplayId;

            // Fallback: derive display_id from guest->tour_id if not provided
            if ($tourDisplayId === null && !empty($guest->tour_id)) {
                $tourIdValue = $guest->tour_id;
                // tour_id may be stored as an array of IDs
                if (is_array($tourIdValue)) {
                    $tourIdValue = end($tourIdValue);
                }
                $tourDisplayId = Tour::where('tour_id', $tourIdValue)->value('display_id');
            }

            // Prepare email data (use plain password for email, not the hashed one)
            $emailData = [
                'guest_name' => $guest->guest_name,
                'email' => $guest->email,
                'app_password' => $plainPassword,
                'country_code' => $guest->country_code ?? '+91',
                'contact' => $guest->contact,
                'tour_id' => $tourDisplayId,
                'company_name' => $companyName,
                'company_logo' => $companyLogo,
                'support_email' => $supportEmail,
                'support_phone' => $supportPhone,
                'dmc_company_name' => $dmcCompanyName,
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

    /**
     * Send guest update email
     * This method sends an update notification email with updated credentials
     * 
     * @param Guest $guest
     * @param string $plainPassword
     * @return bool
     */
    private function sendGuestUpdateEmail(Guest $guest, string $plainPassword)
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
            
            // Get DMC company name (the company that initiated the invitation)
            $dmcId = CommonHelper::getDmcId(auth()->user());
            $dmc = User::where('userId', $dmcId)->first();
            $dmcCompanyName = $dmc->company_name ?? null;
            
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
                'dmc_company_name' => $dmcCompanyName,
            ];
            
            // Render the email template
            $html = view('mails.guest_update', $emailData)->render();
            
            // Extract styles and email container
            preg_match('/<style>(.*?)<\/style>/s', $html, $styleMatches);
            $styles = !empty($styleMatches[0]) ? $styleMatches[0] : '';
            
            // Extract the email-container div
            preg_match('/<div class="email-container">(.*?)<\/div>\s*<\/body>/s', $html, $matches);
            
            if (!empty($matches[0])) {
                $extractedHtml = $matches[0];
                
                // Build complete email HTML
                $subject = 'Your Tour Tracking Credentials Have Been Updated';
                $emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . $subject . '</title>' . $styles . '</head><body>' . $extractedHtml . '</body></html>';
                
                // Send the email
                Mail::to($guest->email)->send(new DmcMail($emailHtml, $subject));
                
                Log::info("Guest update email sent successfully to: {$guest->email}", [
                    'guest_id' => $guest->guest_id,
                    'guest_name' => $guest->guest_name,
                ]);
                
                return true;
            } else {
                Log::error("Email container div not found in guest update template");
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send guest update email', [
                'error' => $e->getMessage(),
                'guest_id' => $guest->guest_id ?? null,
                'email' => $guest->email ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
