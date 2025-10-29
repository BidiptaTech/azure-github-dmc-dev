<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Agency;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use App\Imports\AgenciesImport;
use Illuminate\Support\Facades\DB;

class AgencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            
            $dmc_id = $this->getDmcIdByUserRole();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        $user = Auth::user();
        // Filter agencies that have the current DMC ID in their dmc_id JSON field
        if($user->role_id == 11 || $user->role_id == 33 || $user->role_id == 37 || $user->role_id == 38 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $agencies = Agency::with(['creator', 'updater'])
                    ->whereJsonContains('dmc_id', $dmc_id)
                    ->orderBy('created_at', 'desc')
                    ->get();
        }
        else{
            $agencies = Agency::with(['creator', 'updater'])
                    ->orderBy('created_at', 'desc')
                    ->get();
        }
                         
        return view('agencies.index', compact('agencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::where('is_active', 1)->get();
        return view('agencies.create', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agency_name' => 'required|string|max:255',
            'email' => 'required|email|unique:agencies,email',
            'phone' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'address' => 'required|string',
            'postal_code' => 'nullable|string|max:20',
            'id_card_type' => 'string|max:255',
            'card_number' => 'string|max:50',
            'agency_logo' => 'required|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'branches' => 'nullable|array',
            'branches.*.email' => 'required_with:branches|email',
            'branches.*.phone' => 'required_with:branches|string|max:20',
            'branches.*.country' => 'required_with:branches|string|max:255',
            'branches.*.city' => 'required_with:branches|string|max:255',
            'branches.*.address' => 'required_with:branches|string',
            'branches.*.postal_code' => 'nullable|string|max:20',
            // 'branches.*.id_card_type' => 'required_with:branches|string|max:255',
            // 'branches.*.card_number' => 'required_with:branches|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if agency email is soft deleted
        $deletedAgency = Agency::withTrashed()->where('email', $request->input('email'))->first();
        $isAdmin = false;
        $authUser = Auth::user();
        // Default DMC id to empty array for admin/virtual roles
        $dmc_id = [];
        if($authUser->role_id == 1 || $authUser->role_id == 2 || $authUser->role_id == 3 || $authUser->role_id == 4 || $authUser->role_id == 19 || $authUser->role_id == 20){
            $isAdmin = true;
        }else{
            $dmc_id = $this->getDmcIdByUserRole();
            $isAdmin = false;
        }

        if ($deletedAgency && $deletedAgency->trashed()) {
            // Handle logo upload for restored agency
            $logoPath = $deletedAgency->logo; // Keep existing logo by default
            if ($request->hasFile('agency_logo')) {
                try {
                    $logoUpload = CommonHelper::image_path('file_storage', $request->file('agency_logo'), 'agencies');
                    $logoPath = $logoUpload['master_value'];
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Failed to upload logo: ' . $e->getMessage());
                }
            }
            
            // Restore and update
            $deletedAgency->restore();
            $deletedAgency->fill([
                'agency_name' => $request->input('agency_name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'country' => $request->input('country'),
                'city' => $request->input('city'),
                'contact_person' => $request->input('contact_person'),
                'address' => $request->input('address'),
                'postal_code' => $request->input('postal_code'),
                'id_card_type' => $request->input('id_card_type'),
                'card_number' => $request->input('card_number'),
                'branches' => $this->normalizeBranches($request->input('branches', [])),
                'logo' => $logoPath,
                'updated_by' => Auth::user()->userId,
            ]);
            $deletedAgency->save();

            return redirect()->route('agencies.index')->with('success', 'Soft-deleted agency restored and updated successfully!');
        }

        // Handle logo upload if provided
        $logoPath = null;
        if ($request->hasFile('agency_logo')) {
            try {
                $logoUpload = CommonHelper::image_path('file_storage', $request->file('agency_logo'));
                $logoPath = $logoUpload['master_value'];
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Failed to upload logo: ' . $e->getMessage());
            }
        }

        // Generate unique agency_id following the same pattern as AgentController
        $lastAgency = Agency::withTrashed()->orderBy('created_at', 'desc')->first();
        $agency_max_id = $lastAgency->agency_id ?? 1;
        $agencyId = CommonHelper::createId($agency_max_id);
        
        while (Agency::where('agency_id', $agencyId)->exists()) {
            $agencyId = CommonHelper::createId($agencyId);
        }

        // Create new agency
        $agency = new Agency();
        $agency->agency_id = $agencyId;
        $agency->agency_name = $request->input('agency_name');
        $agency->email = $request->input('email');
        $agency->phone = $request->input('phone');
        $agency->country = $request->input('country');
        $agency->city = $request->input('city');
        $agency->contact_person = $request->input('contact_person');
        $agency->address = $request->input('address');
        $agency->postal_code = $request->input('postal_code');
        $agency->id_card_type = $request->input('id_card_type');
        $agency->card_number = $request->input('card_number');
        $agency->branches = $this->normalizeBranches($request->input('branches', []));
        $agency->logo = $logoPath;
        $agency->created_by = Auth::user()->userId;
        $agency->dmc_id = is_array($dmc_id) ? $dmc_id : [$dmc_id];

        if ($agency->save()) {
            return redirect()->route('agencies.index')->with('success', 'Agency created successfully!');
        }
        return redirect()->back()->with('error', 'Failed to create agency. Please try again.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $agency = Agency::where('agency_id', Crypt::decrypt($id))->with(['creator', 'updater'])->firstOrFail();
        return view('agencies.show', compact('agency'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $agency = Agency::where('agency_id', Crypt::decrypt($id))->firstOrFail();
        $countries = Country::all();
        return view('agencies.edit', compact('agency', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $agency = Agency::where('agency_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'agency_name' => 'required|string|max:255',
            'email' => 'required|email|unique:agencies,email,' . $agency->id,
            'phone' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'address' => 'required|string',
            'postal_code' => 'nullable|string|max:20',
            'id_card_type' => 'string|max:255',
            'card_number' => 'string|max:50',
            'agency_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'branches' => 'nullable|array',
            'branches.*.email' => 'required_with:branches|email',
            'branches.*.phone' => 'required_with:branches|string|max:20',
            'branches.*.country' => 'required_with:branches|string|max:255',
            'branches.*.city' => 'required_with:branches|string|max:255',
            'branches.*.address' => 'required_with:branches|string',
            'branches.*.postal_code' => 'nullable|string|max:20',
            // 'branches.*.id_card_type' => 'required_with:branches|string|max:255',
            // 'branches.*.card_number' => 'required_with:branches|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Handle logo upload if provided
        if ($request->hasFile('agency_logo')) {
            try {
                $logoUpload = CommonHelper::image_path('file_storage', $request->file('agency_logo'));
                $agency->logo = $logoUpload['master_value'];
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Failed to upload logo: ' . $e->getMessage());
            }
        } elseif ($request->input('clear_logo') == '1') {
            // Clear the logo if requested
            $agency->logo = null;
        }

        $agency->agency_name = $request->input('agency_name');
        $agency->email = $request->input('email');
        $agency->phone = $request->input('phone');
        $agency->country = $request->input('country');
        $agency->city = $request->input('city');
        $agency->contact_person = $request->input('contact_person');
        $agency->address = $request->input('address');
        $agency->postal_code = $request->input('postal_code');
        $agency->id_card_type = $request->input('id_card_type');
        $agency->card_number = $request->input('card_number');
        $agency->branches = $this->normalizeBranches($request->input('branches', []));
        $agency->updated_by = Auth::user()->userId;

        if ($agency->save()) {
            return redirect()->route('agencies.index')->with('success', 'Agency updated successfully!');
        }

        return redirect()->back()->with('error', 'Failed to update agency. Please try again.');
    }

    /**
     * Ensure every branch has a unique, immutable branch_uid
     */
    private function normalizeBranches($branches)
    {
        if (!is_array($branches)) {
            return [];
        }

        $normalized = [];
        foreach ($branches as $branch) {
            if (!is_array($branch)) {
                continue;
            }
            if (empty($branch['branch_uid'])) {
                $branch['branch_uid'] = $this->generateBranchUid();
            }
            $normalized[] = $branch;
        }
        return $normalized;
    }

    private function generateBranchUid(): string
    {
        $timestamp = base_convert(now()->timestamp, 10, 36); // Base36 for shorter timestamp
        $random = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4); // 4 random chars
        return 'BR' . strtoupper($timestamp . $random);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $allowedRoles = [1, 2, 3, 4, 19, 20, 37, 38];
        if (!in_array(Auth::user()->role_id, $allowedRoles)) {
            abort(403, 'You do not have permission to delete this agency.');
        }

        $agency = Agency::where('agency_id', $id)->firstOrFail();
        
        if ($agency->delete()) {
            return redirect()->route('agencies.index')->with('success', 'Agency deleted successfully!');
        }

        return redirect()->back()->with('error', 'Failed to delete agency. Please try again.');
    }

    /**
     * Toggle agency status
     */
    public function toggleStatus(string $id)
    {
        $agency = Agency::where('agency_id', $id)->firstOrFail();
        $agency->status = !$agency->status;
        $agency->updated_by = Auth::user()->userId;
        
        if ($agency->save()) {
            $status = $agency->status ? 'activated' : 'deactivated';
            return redirect()->back()->with('success', "Agency {$status} successfully!");
        }

        return redirect()->back()->with('error', 'Failed to update agency status. Please try again.');
    }

    /**
     * Get cities by country for Ajax
     */
    public function getCitiesByCountry(Request $request)
    {
        $country = $request->input('country');
        
        if (!$country) {
            return response()->json([
                'success' => false,
                'message' => 'Country is required'
            ]);
        }

        $cities = \App\Models\City::where('country', $country)
                                 ->select('city_id', 'name')
                                 ->orderBy('name')
                                 ->get();

        return response()->json([
            'success' => true,
            'cities' => $cities
        ]);
    }

    /**
     * Get card types by country for Ajax
     */
    public function getCardTypesByCountry(Request $request)
    {
        $country = $request->input('country');
        
        if (!$country) {
            return response()->json([
                'success' => false,
                'message' => 'Country is required'
            ]);
        }

        $countryData = Country::where('name', $country)
                             ->select('name', 'card_type')
                             ->first();

        if (!$countryData || !$countryData->card_type) {
            return response()->json([
                'success' => false,
                'message' => 'No card types found for this country'
            ]);
        }

        // Split card types by comma if multiple exist
        $cardTypes = array_map('trim', explode(',', $countryData->card_type));
        
        // Create array with proper structure for select2
        $formattedCardTypes = [];
        foreach ($cardTypes as $cardType) {
            $formattedCardTypes[] = [
                'id' => $cardType,
                'text' => $cardType
            ];
        }

        return response()->json([
            'success' => true,
            'card_types' => $formattedCardTypes
        ]);
    }

    /**
     * Display agency selection page for DMCs
     */
    public function dmcAgenciesSelection(Request $request)
    {
        // Check if user is DMC (role_id = 11) or has allowed roles
        $user = Auth::user();
        $allowedRoles = [11,33, 35, 37, 38, 74, 93, 130, 132, 133, 135, 136, 137, 138];
        if (!in_array($user->role_id, $allowedRoles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        // Determine DMC ID based on user role
        $dmc_id = $this->getDmcIdByUserRole();

        // Get all active agencies
        $allAgencies = Agency::where('status', 1)
                            ->orderBy('created_at', 'desc')
                            ->get();
        
        // Filter agencies that are selected by the current DMC
        $selectedAgencies = $allAgencies->filter(function($agency) use ($dmc_id) {
            return $agency->hasSelectedByDmc($dmc_id);
        });
        
        // Get agencies that are not selected by the current DMC
        $availableAgencies = $allAgencies->filter(function($agency) use ($dmc_id) {
            return !$agency->hasSelectedByDmc($dmc_id);
        });

        return view('services.agencies', compact('availableAgencies', 'selectedAgencies'));
    }

    /**
     * Select Individual Agency for DMC
     * Handle individual agency selection with AJAX
     */
    public function selectAgency(Request $request)
    {
        try {
            $agencyId = $request->input('agency_id');
            $user = Auth::user();

            $allowedRoles = [11,33, 35, 37, 38, 74, 93, 130, 132, 133, 135, 136, 137, 138, 128, 129, 134];
            if (!in_array($user->role_id, $allowedRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to perform this action.'
                ], 403);
            }
            // Determine DMC ID based on user role
            try {
                $dmc_id = $this->getDmcIdByUserRole();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 403);
            }
            
            
            // Find the agency
            $agency = Agency::where('agency_id', $agencyId)->first();
            if (!$agency) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agency not found.'
                ], 404);
            }
            
            // Check if user has admin/virtual DMC roles and agency is already registered with another DMC
            if (in_array($user->role_id, [1, 2, 3, 4, 19, 20])) {
                $existingDmcIds = $agency->getSelectedDmcIds();
                if (!empty($existingDmcIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This agency is already registered with another DMC.'
                    ], 400);
                }
            }
            
            // Add the DMC ID to the agency's dmc_id array
            $agency->addDmcId($dmc_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Agency selected successfully!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Agency selection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while selecting the agency.'
            ], 500);
        }
    }

    /**
     * Remove Individual Agency from DMC Selection
     * Handle individual agency removal with AJAX
     */
    public function removeAgency(Request $request)
    {
        try {
            $agencyId = $request->input('agency_id');
            $user = Auth::user();

            $allowedRoles = [11,33, 35, 37, 38, 74, 93, 130, 132, 133, 135, 136, 137, 138, 128, 129, 134];
            if (!in_array($user->role_id, $allowedRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to perform this action.'
                ], 403);
            }
            // Determine DMC ID based on user role
            try {
                $dmc_id = $this->getDmcIdByUserRole();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 403);
            }
            
            // Find the agency
            $agency = Agency::where('agency_id', $agencyId)->first();
            if (!$agency) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agency not found.'
                ], 404);
            }
            
            // Check if this DMC has selected this agency
            if (!$agency->hasSelectedByDmc($dmc_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agency not selected by you.'
                ], 400);
            }
            
            // Remove the DMC ID from the agency's dmc_id array
            $agency->removeDmcId($dmc_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Agency removed successfully!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Agency removal error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing the agency.'
            ], 500);
        }
    }

    public function getDmcIdByUserRole()
    {
        $user = Auth::user();
        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4  || $user->role_id == 19|| $user->role_id == 20){
            // $virtualDMC = \App\Models\User::where('role_id', 20)->first();
            // if (!$virtualDMC) {
            //     throw new \Exception('Virtual DMC user not found.');
            // }
            $dmc_id = [];
        }
        elseif($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 35 || in_array($user->role_id, [33, 128, 129, 130, 134, 135, 136, 138])){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 74 || $user->role_id == 37){
            $user_product_head = \App\Models\User::where('userId', $user->created_by)->first();
            if (!$user_product_head) {
                throw new \Exception('Product head user not found.');
            }
            $dmc_id = $user_product_head->created_by;
        }else if($user->role_id == 93 || $user->role_id == 38){
            $user_product_manager = \App\Models\User::where('userId', $user->created_by)->first();
            if (!$user_product_manager) {
                throw new \Exception('Product manager user not found.');
            }
            $user_product_head = \App\Models\User::where('userId', $user_product_manager->created_by)->first();
            if (!$user_product_head) {
                throw new \Exception('Product head user not found.');
            }
            $dmc_id = $user_product_head->created_by;
        }
        else{
            throw new \Exception('You do not have permission to access this page.');
        }
        return $dmc_id;
    }

    /**
     * Show the agencies import page
     */
    public function importView()
    {
        $auth_user = Auth::user();
        
        // Get recent upload history for agencies (last 10 uploads)
        $uploadHistory = \App\Models\UploadHistory::where('upload_type', 'agencies')
            ->where('uploaded_by', $auth_user->userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('agencies.import', compact('uploadHistory'));
    }

    /**
     * Handle the agencies CSV import
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
            ]);

            $file = $request->file('file');
            $originalFileName = $file->getClientOriginalName();
            
            // Generate file hash to prevent duplicate uploads
            $fileHash = hash_file('md5', $file->getPathname());
            $auth_user = Auth::user();
            $cacheKey = "agency_upload_{$fileHash}_{$auth_user->userId}";
            
            // Check if this exact file was uploaded recently (within last 30 seconds)
            if (cache()->has($cacheKey)) {
                return redirect()->back()->with('error', 'This file was already uploaded recently. Please wait a moment before uploading again.');
            }

            // Create the import instance
            $import = new AgenciesImport();
            
            // Import the file
            $result = $import->import($file->getPathname());
            
            // Cache the upload to prevent duplicates
            cache()->put($cacheKey, true, 30);
            
            $successCount = $result['success'];
            $errorCount = $result['errors'];
            $errorMessages = $result['error_messages'];
            $totalRecords = $successCount + $errorCount;
            
            // Create upload history record
            \App\Models\UploadHistory::createRecord(
                'agencies',
                $originalFileName,
                $originalFileName,
                $totalRecords,
                $successCount,
                $errorCount,
                $errorMessages,
                $auth_user->userId
            );
            
            if ($errorCount > 0) {
                // Format error summary with better structure
                $errorSummary = '<div class="error-summary-list mt-2">';
                $displayErrors = array_slice($errorMessages, 0, 10);
                
                foreach ($displayErrors as $index => $error) {
                    $errorSummary .= '<div class="error-summary-item mb-1">';
                    $errorSummary .= '<i class="ri-close-circle-line text-danger me-1"></i>';
                    $errorSummary .= '<strong>' . ($index + 1) . '.</strong> ' . $error;
                    $errorSummary .= '</div>';
                }
                
                if (count($errorMessages) > 10) {
                    $errorSummary .= '<div class="error-summary-item mt-2 text-muted">';
                    $errorSummary .= '<i class="ri-more-line me-1"></i>';
                    $errorSummary .= '<em>... and ' . (count($errorMessages) - 10) . ' more errors. View upload history for complete details.</em>';
                    $errorSummary .= '</div>';
                }
                
                $errorSummary .= '</div>';
                
                if ($successCount > 0) {
                    $message = '<div class="mb-2">';
                    $message .= '<i class="ri-information-line me-1"></i>';
                    $message .= '<strong>Import Summary:</strong><br>';
                    $message .= '<span class="text-success"><i class="ri-check-line me-1"></i>' . $successCount . ' agencies imported successfully</span><br>';
                    $message .= '<span class="text-danger"><i class="ri-close-line me-1"></i>' . $errorCount . ' rows failed</span>';
                    $message .= '</div>';
                    $message .= '<div class="mb-2"><strong>Failed Rows:</strong></div>';
                    $message .= $errorSummary;
                    
                    return redirect()->back()->with('warning', $message);
                } else {
                    $message = '<div class="mb-2">';
                    $message .= '<i class="ri-error-warning-line me-1"></i>';
                    $message .= '<strong>No agencies were imported.</strong> All ' . $errorCount . ' rows had errors.';
                    $message .= '</div>';
                    $message .= '<div class="mb-2"><strong>Error Details:</strong></div>';
                    $message .= $errorSummary;
                    
                    return redirect()->back()->with('error', $message);
                }
            }
            
            if ($successCount > 0) {
                return redirect()->back()->with('success', "Successfully imported {$successCount} agencies!");
            } else {
                return redirect()->back()->with('error', 'No agencies were imported. Please check your CSV file.');
            }
            
        } catch (\Exception $e) {
            Log::error('Agency import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to import agencies. Please ensure the file is properly formatted. Error: ' . $e->getMessage());
        }
    }

    /**
     * Download the agencies import template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="agencies_import_template.csv"',
        ];

        $columns = [
            'agency_name',
            'email',
            'phone',
            'country',
            'city',
            'address',
            'postal_code',
            'contact_person'
        ];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            // Write header row
            fputcsv($file, $columns);
            
            // Write sample data row
            fputcsv($file, [
                'ABC Travel Agency',
                'info@abctravel.com',
                '12345678',
                'Singapore',
                'Singapore',
                '123 Main Street, Suite 100',
                '10001',
                'John Doe'
            ]);
            
            // Write another sample row
            fputcsv($file, [
                'XYZ Tours & Travels',
                'contact@xyztours.com',
                '9876543210',
                'India',
                'Delhi',
                '456 High Street, Floor 2',
                '7654212',
                'Kunal Malviya'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 