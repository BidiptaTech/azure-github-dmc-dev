# Agency Controller Sequence Diagram

This diagram illustrates the complete workflow of the `AgencyController`, covering agency management, creation, editing, DMC selection, branch management, status toggling, and CSV import operations.

```mermaid
sequenceDiagram
    participant User
    participant Browser
    participant BladeView
    participant AgencyController
    participant Auth
    participant Agency
    participant Country
    participant City
    participant CommonHelper
    participant EmailService
    participant Database
    participant Cache
    participant AgenciesImport

    Note over User,AgenciesImport: 📋 PHASE 1: Agency Listing & Display

    User->>Browser: Navigate to agencies index
    Browser->>AgencyController: GET /agencies
    AgencyController->>AgencyController: getDmcIdByUserRole()
    
    Note over AgencyController: Determine DMC ID based on role:<br/>- Admin/Virtual DMC: Empty array<br/>- DMC (11): user->userId<br/>- Sales Head (33, 128-138): user->created_by<br/>- Product Head (35): user->created_by<br/>- Sales Manager (37, 74): parent->created_by<br/>- Product Manager (38, 93): grandparent->created_by
    
    AgencyController-->>AgencyController: DMC ID or empty array
    AgencyController->>Auth: Get authenticated user
    Auth-->>AgencyController: User object
    
    alt User is DMC/Sales Head/Sales Manager/Product roles
        AgencyController->>Agency: whereJsonContains('dmc_id', dmc_id)->get()
        Agency-->>AgencyController: Filtered agencies
    else User is Admin/Virtual DMC
        AgencyController->>Agency: Get all agencies
        Agency-->>AgencyController: All agencies
    end
    
    AgencyController->>Agency: Load creator and updater relationships
    Agency-->>AgencyController: Agencies with relationships
    AgencyController->>BladeView: Return agencies.index view
    BladeView-->>Browser: Render HTML
    Browser-->>User: Display agencies list

    Note over User,AgenciesImport: 🆕 PHASE 2: Agency Creation Form

    User->>Browser: Click "Add Agency"
    Browser->>AgencyController: GET /agencies/create
    AgencyController->>Country: Get active countries
    Country-->>AgencyController: Countries collection
    AgencyController->>BladeView: Return agencies.create view
    BladeView-->>Browser: Render form
    Browser-->>User: Display creation form

    Note over User,AgenciesImport: 💾 PHASE 3: Agency Creation (Store)

    User->>Browser: Submit agency form
    Browser->>AgencyController: POST /agencies
    AgencyController->>AgencyController: Validate request data
    
    Note over AgencyController: Validation: agency_name, email (unique),<br/>phone, wp_number, country, city, contact_person,<br/>address, postal_code, id_card_type, card_number,<br/>agency_logo (image, max 2MB), branches (array)
    
    alt Validation fails
        AgencyController-->>Browser: Return validation errors
        Browser-->>User: Show error message
    else Validation passes
        AgencyController->>Agency: Check if soft-deleted agency exists (by email)
        Agency-->>AgencyController: Deleted agency or null
        
        alt Soft-deleted agency found
            Note over AgencyController: Restore and update existing agency
            AgencyController->>Agency: Restore agency
            AgencyController->>CommonHelper: Upload logo (if provided)
            CommonHelper-->>AgencyController: Logo path
            AgencyController->>AgencyController: normalizeBranches()
            AgencyController->>Agency: Fill and save agency data
            Agency->>Database: Update agency
            Database-->>Agency: Success
            Agency-->>AgencyController: Success
            AgencyController-->>Browser: Redirect with success message
        else Create new agency
            AgencyController->>Auth: Get authenticated user
            Auth-->>AgentController: User object
            AgencyController->>AgencyController: getDmcIdByUserRole()
            AgencyController-->>AgencyController: DMC ID or empty array
            
            AgencyController->>CommonHelper: Upload logo (if provided)
            CommonHelper-->>AgencyController: Logo path
            
            AgencyController->>Agency: Get last agency ID
            Agency-->>AgencyController: Last agency ID
            AgencyController->>CommonHelper: Generate unique agency ID
            CommonHelper-->>AgencyController: New agency ID
            
            AgencyController->>AgencyController: normalizeBranches()
            Note over AgencyController: For each branch:<br/>- Generate unique branch_uid if missing<br/>- Format: BR + timestamp + random chars
            
            AgencyController->>Agency: Create new agency record
            Note over Agency: Set fields: agency_id, agency_name, email,<br/>phone, wp_number, country, city, contact_person,<br/>address, postal_code, id_card_type, card_number,<br/>branches (normalized), logo, created_by, dmc_id
            Agency->>Database: Insert agency
            Database-->>Agency: Success
            Agency-->>AgencyController: Agency object
            
            AgencyController-->>Browser: Redirect with success message
        end
        
        Browser-->>User: Show success message
    end

    Note over User,AgenciesImport: 👁️ PHASE 4: View Agency Details

    User->>Browser: Click on agency
    Browser->>AgencyController: GET /agencies/{encrypted_id}
    AgencyController->>AgencyController: Decrypt agency ID
    AgencyController->>Agency: Find agency with creator/updater
    Agency-->>AgencyController: Agency object
    AgencyController->>BladeView: Return agencies.show view
    BladeView-->>Browser: Render HTML
    Browser-->>User: Display agency details

    Note over User,AgenciesImport: ✏️ PHASE 5: Edit Agency Form

    User->>Browser: Click "Edit Agency"
    Browser->>AgencyController: GET /agencies/{encrypted_id}/edit
    AgencyController->>AgencyController: Decrypt agency ID
    AgencyController->>Agency: Find agency by ID
    Agency-->>AgentController: Agency object
    AgencyController->>Country: Get all countries
    Country-->>AgentController: Countries
    AgencyController->>BladeView: Return agencies.edit view
    BladeView-->>Browser: Render form
    Browser-->>User: Display edit form

    Note over User,AgenciesImport: 💾 PHASE 6: Update Agency

    User->>Browser: Submit edit form
    Browser->>AgencyController: PUT /agencies/{id}
    AgencyController->>Agency: Find agency by ID
    Agency-->>AgencyController: Agency object
    AgencyController->>AgencyController: Validate request data
    
    Note over AgencyController: Validation: Same as create, but email<br/>unique check excludes current agency
    
    alt Validation fails
        AgencyController-->>Browser: Return validation errors
        Browser-->>User: Show error message
    else Validation passes
        alt Logo file provided
            AgencyController->>CommonHelper: Upload new logo
            CommonHelper-->>AgencyController: Logo path
            AgencyController->>Agency: Set logo path
        else Clear logo requested
            AgencyController->>Agency: Set logo to null
        end
        
        AgencyController->>AgencyController: normalizeBranches()
        AgencyController->>Agency: Update all fields
        AgencyController->>Agency: Set updated_by = current user
        AgencyController->>Agency: Save agency
        Agency->>Database: Update agency
        Database-->>Agency: Success
        Agency-->>AgencyController: Success
        
        AgencyController-->>Browser: Redirect with success message
        Browser-->>User: Show success message
    end

    Note over User,AgenciesImport: 🗑️ PHASE 7: Delete Agency

    User->>Browser: Click "Delete Agency"
    Browser->>AgencyController: DELETE /agencies/{id}
    AgencyController->>Auth: Get authenticated user
    Auth-->>AgencyController: User object
    AgencyController->>AgencyController: Check allowed roles (1, 2, 3, 4, 19, 20, 37, 38)
    
    alt User not allowed
        AgencyController-->>Browser: 403 Forbidden
    else User allowed
        AgencyController->>Agency: Find agency by ID
        Agency-->>AgencyController: Agency object
        AgencyController->>Agency: Get dmc_id field
        Agency-->>AgencyController: DMC IDs (JSON or array)
        AgencyController->>AgencyController: Parse DMC IDs
        
        alt Agency assigned to DMCs
            AgencyController-->>Browser: Return error (cannot delete)
            Browser-->>User: Show error message
        else Agency not assigned
            AgencyController->>Agency: Soft delete agency
            Agency->>Database: Update deleted_at
            Database-->>Agency: Success
            Agency-->>AgencyController: Success
            AgencyController-->>Browser: Redirect with success message
            Browser-->>User: Show success message
        end
    end

    Note over User,AgenciesImport: 🔄 PHASE 8: Toggle Agency Status

    User->>Browser: Click "Toggle Status"
    Browser->>AgencyController: POST /agencies/{id}/toggle-status
    AgencyController->>Agency: Find agency by ID
    Agency-->>AgencyController: Agency object
    AgencyController->>Agency: Toggle status (!status)
    AgencyController->>Agency: Set updated_by = current user
    AgencyController->>Agency: Save agency
    Agency->>Database: Update status
    Database-->>Agency: Success
    Agency-->>AgencyController: Success
    AgencyController-->>Browser: Redirect with success message
    Browser-->>User: Show status change message

    Note over User,AgenciesImport: 🔍 PHASE 9: AJAX Helper Endpoints

    rect rgb(240, 248, 255)
        Note over User,AgenciesImport: Get Cities by Country
        Browser->>AgencyController: GET /agencies/cities?country={name}
        AgencyController->>AgencyController: Validate country parameter
        
        alt Country not provided
            AgencyController-->>Browser: JSON error response
        else Country provided
            AgencyController->>City: Get cities by country
            City-->>AgencyController: Cities collection
            AgencyController-->>Browser: JSON with cities array
        end
    end

    rect rgb(240, 248, 255)
        Note over User,AgenciesImport: Get Card Types by Country
        Browser->>AgencyController: GET /agencies/card-types?country={name}
        AgencyController->>AgencyController: Validate country parameter
        
        alt Country not provided
            AgencyController-->>Browser: JSON error response
        else Country provided
            AgencyController->>Country: Get country by name
            Country-->>AgencyController: Country or null
            
            alt Country not found or no card types
                AgencyController-->>Browser: JSON error response
            else Country found with card types
                AgencyController->>AgencyController: Split card types by comma
                AgencyController->>AgencyController: Format for Select2 (id, text)
                AgencyController-->>Browser: JSON with formatted card types
            end
        end
    end

    Note over User,AgenciesImport: 🏢 PHASE 10: DMC Agency Selection - View Page

    User->>Browser: Navigate to DMC agencies selection
    Browser->>AgencyController: GET /agencies/dmc-selection
    AgencyController->>Auth: Get authenticated user
    Auth-->>AgencyController: User object
    AgencyController->>AgencyController: Check allowed roles (11, 33, 35, 37, 38, 74, 93, 130-138)
    
    alt User not allowed
        AgencyController-->>Browser: 403 Forbidden
    else User allowed
        AgencyController->>AgencyController: getDmcIdByUserRole()
        AgencyController-->>AgencyController: DMC ID
        AgencyController->>Agency: Get all active agencies
        Agency-->>AgencyController: All agencies
        
        Note over AgencyController: Filter agencies into two groups:<br/>1. Selected by current DMC<br/>2. Available (not selected)
        
        AgencyController->>AgencyController: Filter selected agencies
        AgencyController->>AgencyController: Filter available agencies
        AgencyController->>BladeView: Return services.agencies view
        BladeView-->>Browser: Render selection page
        Browser-->>User: Display agencies selection interface
    end

    Note over User,AgenciesImport: ✅ PHASE 11: Select Agency for DMC (AJAX)

    User->>Browser: Click "Select Agency"
    Browser->>AgencyController: POST /agencies/select
    AgencyController->>Auth: Get authenticated user
    Auth-->>AgencyController: User object
    AgencyController->>AgencyController: Check allowed roles
    
    alt User not allowed
        AgencyController-->>Browser: JSON error (403)
    else User allowed
        AgencyController->>AgencyController: getDmcIdByUserRole()
        AgencyController-->>AgencyController: DMC ID
        AgencyController->>Agency: Find agency by ID
        Agency-->>AgencyController: Agency or null
        
        alt Agency not found
            AgencyController-->>Browser: JSON error (404)
        else Agency found
            alt User is Admin/Virtual DMC and agency already registered
                AgencyController-->>Browser: JSON error (400 - already registered)
            else Proceed with selection
                AgencyController->>Agency: Get existing DMC IDs
                Agency-->>AgencyController: Existing DMC IDs
                AgencyController->>AgencyController: Check if first DMC selecting
                AgencyController->>Agency: Add DMC ID to agency
                Agency->>Database: Update dmc_id JSON field
                Database-->>Agency: Success
                Agency-->>AgencyController: Success
                
                Note over AgencyController: Send appropriate emails
                alt First DMC selecting
                    AgencyController->>EmailService: Send welcome email
                    EmailService-->>AgencyController: Result
                    AgencyController->>EmailService: Send partnership email
                    EmailService-->>AgencyController: Result
                else Additional DMC selecting
                    AgencyController->>EmailService: Send partnership email
                    EmailService-->>AgencyController: Result
                end
                
                AgencyController-->>Browser: JSON success response
            end
        end
    end

    Note over User,AgenciesImport: ❌ PHASE 12: Remove Agency from DMC (AJAX)

    User->>Browser: Click "Remove Agency"
    Browser->>AgencyController: POST /agencies/remove
    AgencyController->>Auth: Get authenticated user
    Auth-->>AgencyController: User object
    AgencyController->>AgencyController: Check allowed roles
    
    alt User not allowed
        AgencyController-->>Browser: JSON error (403)
    else User allowed
        AgencyController->>AgencyController: getDmcIdByUserRole()
        AgencyController-->>AgencyController: DMC ID
        AgencyController->>Agency: Find agency by ID
        Agency-->>AgencyController: Agency or null
        
        alt Agency not found
            AgencyController-->>Browser: JSON error (404)
        else Agency found
            AgencyController->>Agency: Check if DMC has selected agency
            Agency-->>AgencyController: Boolean
            
            alt Agency not selected by DMC
                AgencyController-->>Browser: JSON error (400)
            else Agency selected
                AgencyController->>Agency: Remove DMC ID from agency
                Agency->>Database: Update dmc_id JSON field
                Database-->>Agency: Success
                Agency-->>AgencyController: Success
                AgencyController-->>Browser: JSON success response
            end
        end
    end

    Note over User,AgenciesImport: 📥 PHASE 13: CSV Import - View & Download

    User->>Browser: Navigate to import page
    Browser->>AgencyController: GET /agencies/import
    AgencyController->>Auth: Get authenticated user
    Auth-->>AgencyController: User object
    AgencyController->>Database: Get upload history (last 10, type: agencies)
    Database-->>AgencyController: Upload history
    AgencyController->>BladeView: Return agencies.import view
    BladeView-->>Browser: Render import page
    Browser-->>User: Display import form & history

    User->>Browser: Click "Download Template"
    Browser->>AgencyController: GET /agencies/import/template
    AgencyController->>AgencyController: Generate CSV headers
    Note over AgencyController: Headers: agency_name, email, phone,<br/>country, city, address, postal_code, contact_person
    AgencyController->>AgencyController: Write sample data rows (2 examples)
    AgencyController-->>Browser: CSV file download
    Browser-->>User: Download template file

    Note over User,AgenciesImport: 📤 PHASE 14: CSV Import - Process File

    User->>Browser: Upload CSV file
    Browser->>AgencyController: POST /agencies/import
    AgencyController->>AgencyController: Validate file (CSV, max 10MB)
    
    alt Validation fails
        AgencyController-->>Browser: Return error
        Browser-->>User: Show error message
    else Validation passes
        AgencyController->>AgencyController: Generate file hash (MD5)
        AgencyController->>Cache: Check if file uploaded recently
        Cache-->>AgencyController: Cached or not
        
        alt File uploaded recently (within 30 seconds)
            AgencyController-->>Browser: Return duplicate upload error
            Browser-->>User: Show error message
        else Process import
            AgencyController->>AgenciesImport: Create import instance
            AgencyController->>AgenciesImport: Import file
            AgenciesImport->>Database: Process each row
            Database-->>AgenciesImport: Success/error per row
            AgenciesImport-->>AgencyController: Result (success count, error count, messages)
            
            AgencyController->>Cache: Store upload hash (30 seconds)
            AgencyController->>Database: Create upload history record
            Database-->>AgencyController: History saved
            
            alt Errors occurred
                AgencyController->>AgencyController: Format error summary (max 10 displayed)
                alt Some successful
                    AgencyController-->>Browser: Return warning with summary
                else All failed
                    AgencyController-->>Browser: Return error with summary
                end
            else All successful
                AgencyController-->>Browser: Return success message
            end
            
            Browser-->>User: Show import results
        end
    end
```

## Key Features

### 🔐 **Role-Based Access Control**
- **Admin/Virtual DMC**: Can view all agencies, create agencies without DMC assignment
- **DMC**: Can view and manage agencies assigned to them
- **Sales Head/Product Head**: Can view agencies from parent DMC
- **Sales Manager/Product Manager**: Can view agencies from grandparent DMC

### 🏗️ **Branch Management**
- **Branch Normalization**: Ensures each branch has a unique `branch_uid`
- **UID Generation**: Format `BR + timestamp (base36) + random (4 chars)`
- **Immutable UIDs**: Existing branches keep their UIDs, new branches get generated ones
- **Branch Structure**: Each branch has email, phone, country, city, address, postal_code

### 📧 **Email Notifications**
- **Welcome Email**: Sent when first DMC selects an agency
- **Partnership Email**: Sent when DMC selects/partners with agency
- **Email Logic**: First DMC gets both welcome + partnership, subsequent DMCs get only partnership

### 🔄 **Soft Delete Handling**
- Checks for soft-deleted agencies with same email
- Restores and updates instead of creating duplicate
- Preserves existing data

### 🖼️ **Logo Upload**
- Logo upload via `CommonHelper::image_path()`
- Optional logo (can be null)
- Logo clearing option in edit form
- Images stored in Azure Blob Storage

### 🏢 **DMC Assignment**
- Agencies can be assigned to multiple DMCs (JSON array in `dmc_id` field)
- `selectAgency()` allows DMCs to select agencies
- `removeAgency()` allows DMCs to remove agencies
- Admin/Virtual DMC restrictions: Cannot select agencies already registered with other DMCs
- Deletion prevention: Cannot delete agency if assigned to any DMC

### 🔄 **Status Management**
- `toggleStatus()` toggles agency active/inactive status
- Tracks `updated_by` for audit purposes

### 📥 **CSV Import**
- Duplicate upload prevention (30-second cache)
- Upload history tracking
- Detailed error reporting (max 10 errors displayed)
- Success/error count summary
- Template download with sample data

### 🆔 **Unique ID Generation**
- Uses `CommonHelper::createId()` to generate unique agency IDs
- Checks for existing IDs to prevent collisions
- Increments until unique ID found

### 🔍 **AJAX Endpoints**
- **getCitiesByCountry()**: Returns cities for a given country
- **getCardTypesByCountry()**: Returns ID card types for a given country (formatted for Select2)

## Main Actors

- **User**: The person interacting with the system
- **Browser**: Web browser making HTTP requests
- **BladeView**: Laravel Blade template rendering
- **AgencyController**: Main controller handling all agency operations
- **Auth**: Authentication service
- **Agency**: Agency model
- **Country**: Country model
- **City**: City model
- **CommonHelper**: Helper class for utilities
- **EmailService**: Email sending service
- **Database**: Database layer
- **Cache**: Cache service for duplicate prevention
- **AgenciesImport**: CSV import class

## Special Notes

1. **DMC ID Resolution**: The `getDmcIdByUserRole()` method determines the correct DMC ID based on user role and hierarchy
2. **Permission Checks**: Multiple permission checks throughout (delete, DMC selection)
3. **Email Failures**: Email sending failures are logged but don't fail the operation
4. **Image Paths**: Logos are uploaded to Azure Blob Storage via `CommonHelper::image_path()`
5. **JSON Fields**: DMC IDs are stored as JSON arrays in the `dmc_id` field
6. **Soft Deletes**: Uses Laravel's soft delete feature for data recovery
7. **Validation**: Comprehensive validation with custom rules (email uniqueness, file types, branch structure)
8. **AJAX Endpoints**: Multiple AJAX endpoints for dynamic form updates and DMC selection
9. **Branch UID**: Unique identifier for each branch ensures data integrity across updates
10. **Deletion Protection**: Agencies cannot be deleted if assigned to any DMC, preventing data loss
