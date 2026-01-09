<?php

namespace App\Imports;

use App\Models\Agency;
use App\Models\Country;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AgenciesImport
{
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $dmc_id;

    public function __construct()
    {
        $authUser = Auth::user();
        
        // Determine DMC ID based on user role (same logic as AgencyController)
        if(in_array($authUser->role_id, [1, 2, 3, 4, 19, 20])){
            $this->dmc_id = [];
        } elseif($authUser->role_id == 11){  
            $this->dmc_id = $authUser->userId;
        } elseif($authUser->role_id == 35 || in_array($authUser->role_id, [33, 128, 129, 130, 134, 135, 136, 138])){
            $this->dmc_id = $authUser->created_by;
        } elseif($authUser->role_id == 74 || $authUser->role_id == 37){
            $user_product_head = \App\Models\User::where('userId', $authUser->created_by)->first();
            $this->dmc_id = $user_product_head ? $user_product_head->created_by : [];
        } elseif($authUser->role_id == 93 || $authUser->role_id == 38){
            $user_product_manager = \App\Models\User::where('userId', $authUser->created_by)->first();
            if ($user_product_manager) {
                $user_product_head = \App\Models\User::where('userId', $user_product_manager->created_by)->first();
                $this->dmc_id = $user_product_head ? $user_product_head->created_by : [];
            } else {
                $this->dmc_id = [];
            }
        } else {
            $this->dmc_id = [];
        }
    }

    /**
     * Process CSV file and import agencies
     */
    public function import($filePath)
    {
        try {
            $csvData = $this->readCsvFile($filePath);
            
            if (empty($csvData)) {
                throw new \Exception('The CSV file is empty or invalid.');
            }

            // Get header row
            $headers = array_shift($csvData);
            $headers = array_map('strtolower', array_map('trim', $headers));

            // Process each row
            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 2; // +2 because we removed header and rows start at 1
                
                try {
                    // Skip empty rows
                    if (empty(array_filter($row, function($cell) { 
                        return !empty(trim($cell ?? '')); 
                    }))) {
                        continue;
                    }

                    // Map row data to associative array
                    $data = [];
                    foreach ($headers as $i => $header) {
                        $data[$header] = isset($row[$i]) ? trim($row[$i]) : '';
                    }

                    // Process the row
                    $this->processRow($data, $rowNumber);
                    
                } catch (\Exception $e) {
                    $this->errorCount++;
                    $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    Log::error("Agency import error at row {$rowNumber}: " . $e->getMessage(), ['data' => $row]);
                }
            }

            return [
                'success' => $this->successCount,
                'errors' => $this->errorCount,
                'error_messages' => $this->errors
            ];
            
        } catch (\Exception $e) {
            Log::error('Agency import failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process a single row of data
     */
    protected function processRow($data, $rowNumber)
    {
        // Extract and trim data
        $agencyName = $data['agency_name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $country = $data['country'] ?? '';
        $city = $data['city'] ?? '';
        $address = $data['address'] ?? '';
        $postalCode = $data['postal_code'] ?? '';
        $contactPerson = $data['contact_person'] ?? $agencyName;

        // Validate required fields
        $validator = Validator::make([
            'agency_name' => $agencyName,
            'email' => $email,
            'country' => $country,
            'city' => $city,
        ], [
            'agency_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            throw new \Exception(implode(', ', $errors));
        }

        // Check if agency with this email already exists (including soft deleted)
        $existingAgency = Agency::withTrashed()->where('email', $email)->first();

        // Auto-populate id_card_type based on country
        $countryData = Country::where('name', $country)->first();
        $idCardType = null;
        
        if ($countryData && !empty($countryData->card_type)) {
            // Get the first card type if multiple exist
            $cardTypes = array_map('trim', explode(',', $countryData->card_type));
            $idCardType = $cardTypes[0] ?? null;
        }

        if ($existingAgency && $existingAgency->trashed()) {
            // Restore soft-deleted agency and update it
            $existingAgency->restore();
            $existingAgency->fill([
                'agency_name' => $agencyName,
                'email' => $email,
                'phone' => $phone,
                'country' => $country,
                'city' => $city,
                'contact_person' => $contactPerson,
                'address' => $address,
                'postal_code' => $postalCode,
                'id_card_type' => $idCardType,
                'card_number' => null,
                'branches' => [],
                'updated_by' => Auth::user()->userId,
            ]);
            $existingAgency->save();
            $this->successCount++;
            
        } elseif ($existingAgency) {
            // Agency exists and is not deleted - skip
            $this->errorCount++;
            $this->errors[] = "Row {$rowNumber}: Email '{$email}' already exists and agency is active. Skipped.";
            
        } else {
            // Generate unique agency_id
            $lastAgency = Agency::withTrashed()->orderBy('created_at', 'desc')->first();
            $agency_max_id = $lastAgency->agency_id ?? 1;
            $agencyId = CommonHelper::createId($agency_max_id);
            
            while (Agency::where('agency_id', $agencyId)->exists()) {
                $agencyId = CommonHelper::createId($agencyId);
            }

            // Create new agency
            $agency = new Agency([
                'agency_id' => $agencyId,
                'agency_name' => $agencyName,
                'email' => $email,
                'phone' => $phone,
                'country' => $country,
                'city' => $city,
                'contact_person' => $contactPerson,
                'address' => $address,
                'postal_code' => $postalCode,
                'id_card_type' => $idCardType,
                'card_number' => null,
                'branches' => [],
                'logo' => null,
                'status' => 1,
                'created_by' => Auth::user()->userId,
                'dmc_id' => is_array($this->dmc_id) ? $this->dmc_id : [$this->dmc_id],
            ]);

            $agency->save();
            $this->successCount++;
        }
    }

    /**
     * Read CSV file
     */
    protected function readCsvFile($filePath)
    {
        $data = [];
        
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle, 10000, ',')) !== false) {
                // Normalize encoding to UTF-8 (Excel/Windows often saves CSV as Windows-1252)
                $data[] = array_map(function ($cell) {
                    if ($cell === null) return '';
                    if (!is_string($cell)) return $cell;
                    $converted = @mb_convert_encoding($cell, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
                    if ($converted === false) {
                        $converted = $cell;
                    }
                    $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $converted);
                    return $clean === false ? '' : $clean;
                }, $row);
            }
            fclose($handle);
        }
        
        return $data;
    }

    /**
     * Get the success count
     */
    public function getSuccessCount()
    {
        return $this->successCount;
    }

    /**
     * Get the error count
     */
    public function getErrorCount()
    {
        return $this->errorCount;
    }

    /**
     * Get the errors
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
