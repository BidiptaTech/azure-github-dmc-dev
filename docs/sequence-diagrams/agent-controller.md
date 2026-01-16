# Agent Controller Sequence Diagram

This diagram illustrates the complete workflow of the `AgentController`, covering agent management, creation, editing, DMC assignment, search functionality, and CSV import operations.

```mermaid
sequenceDiagram
    participant User
    participant Browser
    participant BladeView
    participant AgentController
    participant Auth
    participant Agent
    participant Agency
    participant UserModel
    participant Country
    participant City
    participant CommonHelper
    participant EmailService
    participant Database
    participant Cache
    participant AgentsImport

    Note over User,AgentsImport: 📋 PHASE 1: Agent Listing & Display

    User->>Browser: Navigate to agents index
    Browser->>AgentController: GET /agents
    AgentController->>Auth: Get authenticated user
    Auth-->>AgentController: User object
    
    Note over AgentController: Check permission: 'view agent'
    
    alt User is Admin (role_id: 1) or Virtual DMC (role_id: 20)
        AgentController->>Agent: where('status', 1)->get()
        Agent-->>AgentController: All active agents
    else User is DMC (role_id: 11)
        AgentController->>AgentController: Set dmc_id = user->userId
    else User is Sales Head (role_id: 33, 128-138)
        AgentController->>AgentController: Set dmc_id = user->created_by
    else User is Sales Manager (role_id: 37)
        AgentController->>UserModel: Find parent Sales Head
        UserModel-->>AgentController: Sales Head
        AgentController->>AgentController: Set dmc_id = sales_head->created_by
    else User is Assistant Sales Manager (role_id: 38)
        AgentController->>UserModel: Find parent Sales Manager
        UserModel-->>AgentController: Sales Manager
        AgentController->>UserModel: Find parent Sales Head
        UserModel-->>AgentController: Sales Head
        AgentController->>AgentController: Set dmc_id = sales_head->created_by
    end
    
    alt dmc_id exists
        AgentController->>Agency: Get agencies for DMC (JSON contains)
        Agency-->>AgentController: Agencies collection
        AgentController->>Agent: Get agents for these agencies
        Agent-->>AgentController: Filtered agents
    end
    
    AgentController->>AgentController: Log query details
    AgentController->>BladeView: Return agents.index view
    BladeView-->>Browser: Render HTML
    Browser-->>User: Display agents list

    Note over User,AgentsImport: 🆕 PHASE 2: Agent Creation Form

    User->>Browser: Click "Add Agent"
    Browser->>AgentController: GET /agents/create
    AgentController->>Auth: Get authenticated user
    Auth-->>AgentController: User object
    
    Note over AgentController: Traverse hierarchy to find master DMC
    
    alt User is DMC (role_id: 11)
        AgentController->>UserModel: Get master DMC
        UserModel-->>AgentController: Master DMC
    else User is Sales Head
        AgentController->>UserModel: Find parent DMC
        UserModel-->>AgentController: Parent DMC
        AgentController->>UserModel: Get master DMC
        UserModel-->>AgentController: Master DMC
    else User is Sales Manager
        AgentController->>UserModel: Find parent Sales Head
        UserModel-->>AgentController: Sales Head
        AgentController->>UserModel: Find parent DMC
        UserModel-->>AgentController: Parent DMC
        AgentController->>UserModel: Get master DMC
        UserModel-->>AgentController: Master DMC
    else User is Assistant Sales Manager
        AgentController->>UserModel: Find parent Sales Manager
        UserModel-->>AgentController: Sales Manager
        AgentController->>UserModel: Find parent Sales Head
        UserModel-->>AgentController: Sales Head
        AgentController->>UserModel: Find parent DMC
        UserModel-->>AgentController: Parent DMC
        AgentController->>UserModel: Get master DMC
        UserModel-->>AgentController: Master DMC
    end
    
    AgentController->>AgentController: Extract countries from master DMC
    AgentController->>Country: Get card types for countries
    Country-->>AgentController: Card types
    AgentController->>UserModel: Get Assistant Sales Managers
    UserModel-->>AgentController: Sales managers
    AgentController->>Agency: Get agencies based on user role
    Agency-->>AgentController: Agencies
    AgentController->>Country: Get all countries
    Country-->>AgentController: Countries
    AgentController->>Agent: Get country codes
    Agent-->>AgentController: Country codes
    AgentController->>BladeView: Return agents.add-agent view
    BladeView-->>Browser: Render form
    Browser-->>User: Display creation form

    Note over User,AgentsImport: 💾 PHASE 3: Agent Creation (Store)

    User->>Browser: Submit agent form
    Browser->>AgentController: POST /agents
    AgentController->>AgentController: Validate request data
    
    Note over AgentController: Validation: salutation, name, agency_id,<br/>phone, designation, email, password, images
    
    AgentController->>Agent: Check if email exists (not trashed)
    Agent-->>AgentController: Existing agent or null
    
    alt Email already exists (not trashed)
        AgentController-->>Browser: Return validation errors
        Browser-->>User: Show error message
    else Validation passes
        AgentController->>Agent: Check if soft-deleted agent exists
        Agent-->>AgentController: Deleted agent or null
        
        alt Soft-deleted agent found
            Note over AgentController: Restore and update existing agent
            AgentController->>Agent: Restore agent
            AgentController->>Agent: Get existing DMC IDs
            Agent-->>AgentController: DMC IDs array
            AgentController->>AgentController: Add current DMC ID if not exists
            AgentController->>CommonHelper: Upload ID proof image (if provided)
            CommonHelper-->>AgentController: Image path
            AgentController->>CommonHelper: Upload agent image (if provided)
            CommonHelper-->>AgentController: Image path
            AgentController->>Agency: Get agency details
            Agency-->>AgentController: Agency object
            AgentController->>Agent: Fill and save agent data
            Agent-->>AgentController: Success
            AgentController-->>Browser: Redirect with success message
        else Create new agent
            AgentController->>Auth: Get current user
            Auth-->>AgentController: User object
            AgentController->>AgentController: Determine DMC ID based on role
            
            alt User is DMC (role_id: 11)
                AgentController->>AgentController: Set dmc_id = user->userId
            else User is Admin/Virtual DMC
                AgentController->>UserModel: Get Virtual DMC
                UserModel-->>AgentController: Virtual DMC
                AgentController->>AgentController: Set dmc_id = virtual_dmc->userId
            else Other roles
                AgentController->>UserModel: Traverse hierarchy to find DMC
                UserModel-->>AgentController: Parent DMC
                AgentController->>AgentController: Set dmc_id = parent_dmc->userId
            end
            
            AgentController->>CommonHelper: Upload ID proof image (if provided)
            CommonHelper-->>AgentController: Image path
            AgentController->>CommonHelper: Upload agent image (if provided)
            CommonHelper-->>AgentController: Image path
            AgentController->>Agency: Get agency details
            Agency-->>AgentController: Agency object
            AgentController->>Agent: Get last agent ID
            Agent-->>AgentController: Last agent ID
            AgentController->>CommonHelper: Generate unique agent ID
            CommonHelper-->>AgentController: New agent ID
            AgentController->>Agent: Create new agent record
            Agent->>Database: Insert agent
            Database-->>Agent: Success
            Agent-->>AgentController: Agent object
            
            Note over AgentController: Send welcome email to agent
            AgentController->>UserModel: Get DMC user details
            UserModel-->>AgentController: DMC user
            AgentController->>CommonHelper: Generate activation link
            CommonHelper-->>AgentController: Activation URL
            AgentController->>EmailService: Send agent creation email
            EmailService-->>AgentController: Email sent (or error logged)
            
            AgentController-->>Browser: Redirect with success message
        end
        
        Browser-->>User: Show success/error message
    end

    Note over User,AgentsImport: 👁️ PHASE 4: View Agent Details

    User->>Browser: Click on agent
    Browser->>AgentController: GET /agents/{encrypted_id}
    AgentController->>AgentController: Decrypt agent ID
    AgentController->>Agent: Find agent by ID
    Agent-->>AgentController: Agent object
    AgentController->>BladeView: Return agents.show view
    BladeView-->>Browser: Render HTML
    Browser-->>User: Display agent details

    Note over User,AgentsImport: ✏️ PHASE 5: Edit Agent Form

    User->>Browser: Click "Edit Agent"
    Browser->>AgentController: GET /agents/{encrypted_id}/edit
    AgentController->>AgentController: Decrypt agent ID
    AgentController->>Agent: Find agent by ID
    Agent-->>AgentController: Agent object
    AgentController->>Auth: Get authenticated user
    Auth-->>AgentController: User object
    
    Note over AgentController: Traverse hierarchy to find master DMC<br/>(Same logic as create)
    
    AgentController->>Country: Get card types for user countries
    Country-->>AgentController: Card types
    AgentController->>Agency: Get agencies based on user role
    Agency-->>AgentController: Agencies
    AgentController->>Country: Get all countries
    Country-->>AgentController: Countries
    AgentController->>Agent: Get country codes
    Agent-->>AgentController: Country codes
    AgentController->>BladeView: Return agents.edit-agent view
    BladeView-->>Browser: Render form
    Browser-->>User: Display edit form

    Note over User,AgentsImport: 💾 PHASE 6: Update Agent

    User->>Browser: Submit edit form
    Browser->>AgentController: PUT /agents/{id}
    AgentController->>Agent: Find agent by ID
    Agent-->>AgentController: Agent object
    AgentController->>AgentController: Validate request data
    
    Note over AgentController: Validation: salutation, name, agency_id,<br/>phone, designation, email (unique), password (optional)
    
    alt Validation fails
        AgentController-->>Browser: Return validation errors
        Browser-->>User: Show error message
    else Validation passes
        AgentController->>Agency: Get agency details
        Agency-->>AgentController: Agency object
        AgentController->>Agent: Update agent fields
        
        alt Password provided
            AgentController->>Agent: Hash and set password
        end
        
        AgentController->>Agent: Save agent
        Agent->>Database: Update agent
        Database-->>Agent: Success
        Agent-->>AgentController: Success
        
        Note over AgentController: Send update email to agent
        AgentController->>CommonHelper: Get DMC ID
        CommonHelper-->>AgentController: DMC ID
        AgentController->>UserModel: Get DMC user
        UserModel-->>AgentController: DMC user
        AgentController->>EmailService: Send agent update email
        EmailService-->>AgentController: Email sent (or error logged)
        
        AgentController-->>Browser: Redirect with success message
        Browser-->>User: Show success message
    end

    Note over User,AgentsImport: 🔍 PHASE 7: AJAX Helper Endpoints

    rect rgb(240, 248, 255)
        Note over User,AgentsImport: Get Sales Manager Details
        Browser->>AgentController: GET /agents/sales-manager/{userId}
        AgentController->>UserModel: Find manager by ID
        UserModel-->>AgentController: Manager or null
        
        alt Manager not found
            AgentController-->>Browser: JSON error response
        else Manager found
            AgentController->>Country: Get country by name
            Country-->>AgentController: Country object
            AgentController->>Country: Get ID cards for country
            Country-->>AgentController: ID cards
            AgentController-->>Browser: JSON success with country & ID cards
        end
    end

    rect rgb(240, 248, 255)
        Note over User,AgentsImport: Update DMC ID (Agent Selection)
        Browser->>AgentController: POST /agents/update-dmc-id
        AgentController->>Agent: Find agent by ID
        Agent-->>AgentController: Agent object
        AgentController->>AgentController: Parse existing DMC IDs (JSON or array)
        AgentController->>Auth: Get current user ID
        Auth-->>AgentController: Current user ID
        AgentController->>AgentController: Add current DMC ID if not exists
        AgentController->>Agent: Update dmc_id field
        Agent->>Database: Update agent
        Database-->>Agent: Success
        Agent-->>AgentController: Success
        AgentController-->>Browser: JSON success response
    end

    rect rgb(240, 248, 255)
        Note over User,AgentsImport: Fetch Cities by Country
        Browser->>AgentController: GET /agents/fetch-cities?country={name}
        AgentController->>City: Get cities by country
        City-->>AgentController: Cities collection
        AgentController-->>Browser: JSON cities array
    end

    rect rgb(240, 248, 255)
        Note over User,AgentsImport: Fetch Country Code
        Browser->>AgentController: GET /agents/fetch-country-code?country={name}
        AgentController->>Country: Get country by name
        Country-->>AgentController: Country or null
        
        alt Country found
            AgentController-->>Browser: JSON with country_code
        else Country not found
            AgentController-->>Browser: JSON error (404)
        end
    end

    Note over User,AgentsImport: 🔎 PHASE 8: Search Agents (DMC Only)

    User->>Browser: Search agents (DMC role)
    Browser->>AgentController: POST /agents/search
    AgentController->>Auth: Get authenticated user
    Auth-->>AgentController: User object
    
    alt User is not DMC (role_id != 11)
        AgentController-->>Browser: JSON error (permission denied)
    else User is DMC
        AgentController->>Agent: Build search query
        
        alt Agency name provided
            AgentController->>Agent: Filter by name (like)
        end
        alt Agent name provided
            AgentController->>Agent: Filter by company_name (like)
        end
        alt Country provided
            AgentController->>Agent: Filter by user_country
        end
        alt City provided
            AgentController->>Agent: Filter by city
        end
        
        Agent->>Database: Execute query
        Database-->>Agent: Agents collection
        Agent-->>AgentController: Agents
        
        Note over AgentController: Filter out agents already selected by DMC
        AgentController->>AgentController: For each agent, check dmc_id array
        AgentController->>AgentController: Exclude if DMC ID already in array
        AgentController->>AgentController: Return filtered agents
        
        AgentController-->>Browser: JSON with filtered agents
    end

    Note over User,AgentsImport: 🗑️ PHASE 9: Delete Agent

    User->>Browser: Click "Delete Agent"
    Browser->>AgentController: DELETE /agents/{id}
    AgentController->>Agent: Find agent by ID
    Agent-->>AgentController: Agent query builder
    AgentController->>Agent: Soft delete agent
    Agent->>Database: Update deleted_at
    Database-->>Agent: Success
    Agent-->>AgentController: Success
    AgentController-->>Browser: Redirect with success message
    Browser-->>User: Show success message

    Note over User,AgentsImport: 📥 PHASE 10: CSV Import - View & Download

    User->>Browser: Navigate to import page
    Browser->>AgentController: GET /agents/import
    AgentController->>Auth: Get authenticated user
    Auth-->>AgentController: User object
    AgentController->>AgentController: Check allowed roles (11, 33, 128-138, 12, 37, 38)
    
    alt User not allowed
        AgentController-->>Browser: 403 Forbidden
    else User allowed
        AgentController->>AgentController: Determine DMC ID for import
        AgentController->>Agency: Get available agencies for DMC
        Agency-->>AgentController: Agencies
        AgentController->>Database: Get upload history (last 10)
        Database-->>AgentController: Upload history
        AgentController->>BladeView: Return agents.import view
        BladeView-->>Browser: Render import page
        Browser-->>User: Display import form & history
    end

    User->>Browser: Click "Download Template"
    Browser->>AgentController: GET /agents/import/template
    AgentController->>AgentController: Generate CSV headers
    AgentController->>AgentController: Write sample data rows
    AgentController-->>Browser: CSV file download
    Browser-->>User: Download template file

    Note over User,AgentsImport: 📤 PHASE 11: CSV Import - Process File

    User->>Browser: Upload CSV file
    Browser->>AgentController: POST /agents/import
    AgentController->>AgentController: Validate file (CSV, max 10MB)
    
    alt Validation fails
        AgentController-->>Browser: Return error
        Browser-->>User: Show error message
    else Validation passes
        AgentController->>AgentController: Generate file hash (MD5)
        AgentController->>Cache: Check if file uploaded recently
        Cache-->>AgentController: Cached or not
        
        alt File uploaded recently (within 30 seconds)
            AgentController-->>Browser: Return duplicate upload error
            Browser-->>User: Show error message
        else Process import
            AgentController->>AgentsImport: Create import instance
            AgentController->>AgentsImport: Import file
            AgentsImport->>Database: Process each row
            Database-->>AgentsImport: Success/error per row
            AgentsImport-->>AgentController: Result (success count, error count, messages)
            
            AgentController->>Cache: Store upload hash (30 seconds)
            AgentController->>Database: Create upload history record
            Database-->>AgentController: History saved
            
            alt Errors occurred
                AgentController->>AgentController: Format error summary (max 10 displayed)
                AgentController-->>Browser: Return warning/error with summary
            else All successful
                AgentController-->>Browser: Return success message
            end
            
            Browser-->>User: Show import results
        end
    end
```

## Key Features

### 🔐 **Role-Based Access Control**
- **Admin/Virtual DMC**: Can view all agents
- **DMC**: Can view agents from their agencies
- **Sales Head**: Can view agents from parent DMC's agencies
- **Sales Manager**: Can view agents from grandparent DMC's agencies
- **Assistant Sales Manager**: Can view agents from great-grandparent DMC's agencies

### 🏗️ **Hierarchy Traversal**
The controller implements complex hierarchy traversal to:
- Find master DMC for country restrictions
- Determine DMC ID for agent assignment
- Filter agencies based on user role

### 📧 **Email Notifications**
- **Agent Creation**: Welcome email with activation link and credentials
- **Agent Update**: Notification email when agent details are updated

### 🔄 **Soft Delete Handling**
- Checks for soft-deleted agents with same email
- Restores and updates instead of creating duplicate
- Preserves existing DMC ID assignments

### 🖼️ **Image Upload**
- ID proof image upload via `CommonHelper::image_path()`
- Agent profile image upload
- Images stored in Azure Blob Storage

### 🔍 **DMC Assignment**
- Agents can be assigned to multiple DMCs (JSON array)
- `updateDmcId()` allows DMCs to select agents
- `searchAgents()` filters out already-selected agents

### 📥 **CSV Import**
- Duplicate upload prevention (30-second cache)
- Upload history tracking
- Detailed error reporting (max 10 errors displayed)
- Success/error count summary

### 🆔 **Unique ID Generation**
- Uses `CommonHelper::createId()` to generate unique agent IDs
- Checks for existing IDs to prevent collisions
- Increments until unique ID found

## Main Actors

- **User**: The person interacting with the system
- **Browser**: Web browser making HTTP requests
- **BladeView**: Laravel Blade template rendering
- **AgentController**: Main controller handling all agent operations
- **Auth**: Authentication service
- **Agent**: Agent model
- **Agency**: Agency model
- **UserModel**: User model for hierarchy traversal
- **Country**: Country model
- **City**: City model
- **CommonHelper**: Helper class for utilities
- **EmailService**: Email sending service
- **Database**: Database layer
- **Cache**: Cache service for duplicate prevention
- **AgentsImport**: CSV import class

## Special Notes

1. **DMC ID Resolution**: The controller uses complex logic to determine the correct DMC ID based on user role and hierarchy
2. **Permission Checks**: Multiple permission checks throughout (view, import)
3. **Email Failures**: Email sending failures are logged but don't fail the operation
4. **Image Paths**: Images are uploaded to Azure Blob Storage via `CommonHelper::image_path()`
5. **JSON Fields**: DMC IDs are stored as JSON arrays in the `dmc_id` field
6. **Soft Deletes**: Uses Laravel's soft delete feature for data recovery
7. **Validation**: Comprehensive validation with custom rules (email uniqueness, password strength)
8. **AJAX Endpoints**: Multiple AJAX endpoints for dynamic form updates
