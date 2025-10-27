<?php

namespace App\Imports;

use App\Models\Agent;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\CommonHelper;

class AgentsImport
{
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $errorMessages = [];
    protected $dmcId = null;

    public function __construct()
    {
        // Get DMC ID based on current user role
        $currentUser = Auth::user();
        $this->dmcId = $this->determineDmcId($currentUser);
    }

    /**
     * Determine DMC ID based on user role hierarchy
     */
    private function determineDmcId($user)
    {
        if (!$user) {
            return null;
        }

        switch ($user->role_id) {
            case 11: // DMC
                return $user->userId;
                
            case 33: // Sales Head
            case 128:
            case 129:
            case 130:
            case 134:
            case 135:
            case 136:
            case 138:
                // Direct parent is DMC
                return $user->created_by;
                
            case 12: // Sales Manager
            case 37:
                // Parent is Sales Head, grandparent is DMC
                $salesHead = User::where('userId', $user->created_by)->first();
                return $salesHead ? $salesHead->created_by : null;
                
            case 38: // Assistant Sales Manager
                // Parent is Sales Manager, grandparent is Sales Head, great-grandparent is DMC
                $salesManager = User::where('userId', $user->created_by)->first();
                if ($salesManager) {
                    $salesHead = User::where('userId', $salesManager->created_by)->first();
                    return $salesHead ? $salesHead->created_by : null;
                }
                return null;
                
            case 1: // Admin
            case 2:
            case 3:
            case 4:
            case 19:
                // Admin users - get virtual DMC
                $virtualDmc = User::where('role_id', 20)->first();
                return $virtualDmc ? $virtualDmc->userId : null;
                
            case 20: // Virtual DMC
                return $user->userId;
                
            default:
                return null;
        }
    }

    /**
     * Import agents from CSV file
     */
    public function import($filePath)
    {
        if (!$this->dmcId) {
            $this->errorMessages[] = "Unable to determine DMC ID for your role.";
            $this->errorCount++;
            return $this->getResult();
        }

        // Read CSV file
        $file = fopen($filePath, 'r');
        
        if (!$file) {
            $this->errorMessages[] = "Unable to open file.";
            $this->errorCount++;
            return $this->getResult();
        }

        // Get header row
        $header = fgetcsv($file);
        
        if (!$header) {
            $this->errorMessages[] = "CSV file is empty or invalid.";
            $this->errorCount++;
            fclose($file);
            return $this->getResult();
        }

        // Normalize headers
        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);

        $rowNumber = 1; // Start from row 1 (header is row 0)

        // Process each row
        while (($row = fgetcsv($file)) !== false) {
            $rowNumber++;
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Combine header with row data
            $data = array_combine($header, $row);
            
            // Process the row
            $this->processRow($data, $rowNumber);
        }

        fclose($file);
        
        return $this->getResult();
    }

    /**
     * Process a single row
     */
    private function processRow($data, $rowNumber)
    {
        // Validate required fields
        $validator = Validator::make($data, [
            'agency_name' => 'required|string',
            'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|numeric',
            'designation' => 'required|string|max:255',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $this->errorMessages[] = "Row {$rowNumber}: " . implode(', ', $errors);
            $this->errorCount++;
            return;
        }

        try {
            // Find agency by name
            $agencyName = trim($data['agency_name']);
            $agency = Agency::where('agency_name', 'ILIKE', $agencyName)
                           ->where('status', 1)
                           ->whereRaw('dmc_id::jsonb @> ?', [json_encode([$this->dmcId])])
                           ->first();

            if (!$agency) {
                $this->errorMessages[] = "Row {$rowNumber}: Agency '{$agencyName}' not found or not accessible by your DMC.";
                $this->errorCount++;
                return;
            }

            // Check if email already exists (not soft-deleted)
            $existingAgent = Agent::where('email', trim($data['email']))->first();
            
            if ($existingAgent && !$existingAgent->trashed()) {
                $this->errorMessages[] = "Row {$rowNumber}: Email '{$data['email']}' already exists.";
                $this->errorCount++;
                return;
            }

            // Check if agent is soft deleted - restore and update
            $deletedAgent = Agent::withTrashed()->where('email', trim($data['email']))->first();

            if ($deletedAgent && $deletedAgent->trashed()) {
                $this->restoreAndUpdateAgent($deletedAgent, $data, $agency, $rowNumber);
                return;
            }

            // Create new agent
            $this->createNewAgent($data, $agency, $rowNumber);

        } catch (\Exception $e) {
            Log::error("Agent import error on row {$rowNumber}: " . $e->getMessage());
            $this->errorMessages[] = "Row {$rowNumber}: " . $e->getMessage();
            $this->errorCount++;
        }
    }

    /**
     * Restore and update soft-deleted agent
     */
    private function restoreAndUpdateAgent($agent, $data, $agency, $rowNumber)
    {
        try {
            $agent->restore();

            // Get existing DMC IDs
            $existingDmcIds = [];
            if ($agent->dmc_id) {
                if (is_string($agent->dmc_id)) {
                    try {
                        $existingDmcIds = json_decode($agent->dmc_id, true) ?? [];
                    } catch (\Exception $e) {
                        $existingDmcIds = [];
                    }
                }
            }

            // Add current DMC ID if not exists
            if (!in_array($this->dmcId, $existingDmcIds)) {
                $existingDmcIds[] = $this->dmcId;
            }

            $agent->fill([
                'salutation' => trim($data['salutation']),
                'name' => trim($data['name']),
                'company_name' => $agency->agency_name,
                'agency_id' => $agency->agency_id,
                'phone' => trim($data['phone']),
                'designation' => trim($data['designation']),
                'sales_manager_dmc' => Auth::user()->userId,
                'role_id' => Auth::user()->role_id,
                'created_by' => Auth::user()->userId,
                'dmc_id' => json_encode($existingDmcIds),
                'password' => bcrypt(trim($data['password'])),
            ]);
            
            $agent->save();

            // Send email
            $this->sendAgentEmail($agent, $data, $agency, 'restored');

            $this->successCount++;
            
        } catch (\Exception $e) {
            Log::error("Error restoring agent on row {$rowNumber}: " . $e->getMessage());
            $this->errorMessages[] = "Row {$rowNumber}: Failed to restore agent - " . $e->getMessage();
            $this->errorCount++;
        }
    }

    /**
     * Create new agent
     */
    private function createNewAgent($data, $agency, $rowNumber)
    {
        try {
            // Generate unique agent ID
            $lastAgent = Agent::withTrashed()->orderBy('created_at', 'desc')->first();
            $agent_max_id = $lastAgent ? $lastAgent->agent_id : 1;
            $agentId = CommonHelper::createId($agent_max_id);
            
            while (Agent::where('agent_id', $agentId)->exists()) {
                $agentId = CommonHelper::createId($agentId);
            }

            $agent = new Agent();
            $agent->agent_id = $agentId;
            $agent->salutation = trim($data['salutation']);
            $agent->name = trim($data['name']);
            $agent->company_name = $agency->agency_name;
            $agent->agency_id = $agency->agency_id;
            $agent->phone = trim($data['phone']);
            $agent->designation = trim($data['designation']);
            $agent->email = trim($data['email']);
            $agent->sales_manager_dmc = Auth::user()->userId;
            $agent->role_id = Auth::user()->role_id;
            $agent->password = bcrypt(trim($data['password']));
            $agent->created_by = Auth::user()->userId;
            $agent->dmc_id = json_encode([$this->dmcId]);
            $agent->status = 1;

            $agent->save();

            // Send email
            $this->sendAgentEmail($agent, $data, $agency, 'created');

            $this->successCount++;
            
        } catch (\Exception $e) {
            Log::error("Error creating agent on row {$rowNumber}: " . $e->getMessage());
            $this->errorMessages[] = "Row {$rowNumber}: Failed to create agent - " . $e->getMessage();
            $this->errorCount++;
        }
    }

    /**
     * Send email to agent
     */
    private function sendAgentEmail($agent, $data, $agency, $type = 'created')
    {
        try {
            $dmc_user = User::where('userId', $this->dmcId)->first();

            $emailData = [
                'salutation' => $agent->salutation,
                'name' => $agent->name,
                'email' => $agent->email,
                'phone' => $agent->phone,
                'company_name' => $agency->agency_name,
                'country' => $agent->user_country ?? 'N/A',
                'city' => $agent->city ?? 'N/A',
                'password' => trim($data['password']),
                'dmc_logo' => $dmc_user->logo ?? 'NA',
                'dmc_company' => $dmc_user->company_name ?? config('app.name'),
                'dmc_email' => $dmc_user->email ?? 'NA',
                'dmc_phone' => $dmc_user->phone ?? 'NA',
                'mail_settings' => (object)[
                    'support_email' => $dmc_user->email ?? 'NA',
                    'support_phone' => $dmc_user->phone ?? 'NA',
                    'facebook_url' => '#',
                    'twitter_url' => '#',
                    'instagram_url' => '#',
                    'linkedin_url' => '#'
                ]
            ];

            if ($type === 'created') {
                CommonHelper::sendEmail(
                    $agent->email,
                    'agent_creation',
                    'Your Agent Account Has Been Created',
                    'Welcome to our platform! Your agent account has been created successfully.',
                    $emailData
                );
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to send agent creation email: ' . $e->getMessage());
            // Don't fail the import if email fails
        }
    }

    /**
     * Get import results
     */
    private function getResult()
    {
        return [
            'success' => $this->successCount,
            'errors' => $this->errorCount,
            'error_messages' => $this->errorMessages
        ];
    }
}

