# Sequence Diagrams Documentation

This directory contains Mermaid sequence diagrams for key workflows in the application.

## Available Diagrams

### Bookings Controller (`bookings-controller.md`)
**Purpose**: Comprehensive documentation of the BookingsController workflow, covering all booking status management, negotiations, payments, and tour operations.

**Key Flows Covered**:

#### 📋 **Phase 1: Display Booking Lists**
- New Enquiries (tour_status = 'New Enquiry')
- Follow Ups (Prospect, Tentative)
- Confirmed Bookings
- Definite Bookings
- Actual Bookings
- Cancelled & Refund Bookings

#### 💰 **Phase 2: Agent Negotiation Flow**
- Negotiate: Submit new offer, create Enquiry, deactivate previous
- Cancel: Update enquiry status, change tour status
- Confirm: Finalize booking, update orders, handle multi-enquiry

#### 💳 **Phase 3: Payment Functionality**
- Payment modal display
- Price calculations (subtotal, discount, tax, total)
- Payment recording

#### 📊 **Phase 4: Price Calculation & Display**
- Tour total price calculation (including transfer + guide)
- Discount calculation
- Settlement amount calculation

#### 🗑️ **Phase 5: Tour Cancellation**
- Cancel tour with status handling
- Refund - Pending for Definite tours

#### 💵 **Phase 6: Refund Processing**
- Display refunds list
- Process refund (Refund - Pending → Refunded)

#### 📄 **Phase 7: PDF Export**
- Export tour details as PDF
- HTML to PDF conversion

#### 👁️ **Phase 8: View Tour Details**
- Display complete tour information
- Payment history
- Service breakdown

#### 📈 **Phase 9: Booking Statistics**
- Dashboard statistics by status

#### 🔍 **Phase 10: Service Modal Displays**
- Hotel, attraction, restaurant, guide, transport modals
- Price calculations per service type

**Main Actors**:
- User
- Browser
- Blade View
- BookingsController
- Models (Tour, Order, Enquiry, Agent, etc.)
- Database
- CommonHelper
- Dompdf

**Special Features**:
- ✅ DMC ID resolution from user hierarchy
- ✅ Role-based filtering
- ✅ Recursive price extraction from JSON
- ✅ Multi-enquiry handling
- ✅ Transaction-safe operations
- ✅ Comprehensive negotiation workflow

### Hotel Controller (`hotel-controller.md`)
**Purpose**: Comprehensive documentation of the HotelController workflow, covering hotel management, room management, rate/season management, bed management, calendar, policies, facilities, and DMC operations.

**Key Flows Covered**:

#### 🏨 **Phase 1: Hotel Management**
- List hotels with role-based filtering
- Create hotel (image uploads, unique ID generation)
- Edit hotel (image management, JSON field handling)
- Delete hotel (DMC assignment check, Azure cleanup)

#### 📞 **Phase 2: Contact & Port Management**
- Contact details update (owner, directors, managers)
- Port of entry/exit/others management with coordinates

#### 🛏️ **Phase 3: Room Management**
- List rooms (admin base + DMC rooms)
- Create room (base room system, price calculation)
- Edit room (admin vs DMC logic, variant pricing)
- Delete room (usage check)

#### 💰 **Phase 4: Rate & Season Management**
- List rates (DMC-specific filtering)
- Create rate (Blackout Date, Fair Date)
- Create season (overlap detection, weekday/weekend pricing)
- Edit/update rates and seasons

#### 🛏️ **Phase 5: Bed Management**
- List beds (with DMC info for admin)
- Create bed (occupancy calculation, capacity check)
- Edit/delete beds

#### 📅 **Phase 6: Calendar & Policy Management**
- Hotel calendar (yearly view with rate overlays)
- Policy management (Property, Cancellation, Refund, Child, Pet, Terms)
- PDF uploads for policies

#### 🏢 **Phase 7: Facility & Conference Management**
- Facility selection and image management
- Conference room details

#### 🔄 **Phase 8: DMC Hotel Selection & Bulk Operations**
- DMC hotel selection (bulk and individual)
- Room import/export (CSV bulk operations)

**Main Actors**:
- User
- Browser
- Blade View
- HotelController
- Models (Hotel, Room, Rate, Bed, Facility, etc.)
- Database
- CommonHelper
- AzureStorage

**Special Features**:
- ✅ Base room system (admin creates base, DMC creates variants)
- ✅ Variant price calculation (base + variant)
- ✅ DMC-specific rates and beds
- ✅ Azure blob storage for images
- ✅ Date overlap detection for seasons
- ✅ Room capacity validation
- ✅ Complex DMC hierarchy resolution

### Hotel Restaurant Controller (`hotel-restaurant-controller.md`)
**Purpose**: Comprehensive documentation of the HotelRestaurantController workflow, covering restaurant management, meal management, and calendar operations.

**Key Flows Covered**:

#### 🍽️ **Phase 1: Restaurant Listing**
- List restaurants with complex role-based filtering
- DMC hierarchy resolution

#### 🏨 **Phase 2: Restaurant Creation (From Hotel)**
- Create restaurant form (hotel context)
- DMC assignment and duplicate location check
- Meal availability configuration
- Image uploads (master + gallery)

#### ✏️ **Phase 3: Restaurant Editing**
- Edit restaurant form
- Update meal availability & times
- Image management
- Delete restaurant (usage check)

#### 📅 **Phase 4: Restaurant Calendar**
- Close days management
- Holiday dates configuration

#### 🍽️ **Phase 5: Meal Management**
- Meals creation page (DMC-specific)
- Create meal (Breakfast/Lunch/Dinner, Buffet/Set Menu/A-La-Carte)
- Edit meal (permission checks)
- Delete meal
- Fetch DMC meals (AJAX for admin)

**Main Actors**:
- User
- Browser
- Blade View
- HotelRestaurantController
- Models (Restaurant, Meal, Hotel, etc.)
- Database
- CommonHelper
- AzureStorage

**Special Features**:
- ✅ Restaurant creation from hotel context
- ✅ DMC-specific meals
- ✅ Meal period types (Breakfast, Lunch, Dinner)
- ✅ Meal type classification (Buffet, Set Menu, A-La-Carte)
- ✅ Duplicate location prevention
- ✅ Usage validation before deletion
- ✅ AJAX-based dynamic meal loading

### Job Sheet Controller (`jobsheet-controller.md`)
**Purpose**: Comprehensive documentation of the JobSheetController workflow, covering driver and guide jobsheet assignment, schedule management, and jobsheet viewing operations.

**Key Flows Covered**:

#### 🚗 **Phase 1: Driver Jobsheet Management**
- List drivers for jobsheet (role-based filtering)
- Create driver jobsheet form (load orders for tomorrow)
- Update driver/vehicle assignment (individual & bulk)
- Get driver schedule (from orders)
- Get tour orders (for specific tour & date)
- Get orders by date (with filtering)

#### 👨‍🏫 **Phase 2: Guide Jobsheet Management**
- List guides for jobsheet
- Create guide jobsheet form (load guide orders for tomorrow)
- Update guide assignment
- Get guide schedule (from orders)
- Get tour guide orders
- Get orders by date (guide type)

#### 📋 **Phase 3: Jobsheet Viewing & Management**
- View all jobsheets (DataTables interface)
- Get jobsheet data (server-side processing)
- Get jobsheet details
- Export jobsheets to Excel

#### 🔍 **Phase 4: Helper Functions & AJAX Endpoints**
- Get zone for location (Port/Hotel/Attraction/Restaurant)
- Get DMCs by Master DMC
- Get Drivers/Guides by DMC
- Get Tour Details
- Get All Tours (filtered)

**Main Actors**:
- User
- Browser
- Blade View
- JobSheetController
- Models (Order, Jobsheet, Driver, Vehicle, Guide, Tour, Port, Hotel, Attraction, Restaurant, Zone)
- Database
- CommonHelper

**Special Features**:
- ✅ Complex order type handling (transport, attraction with transfer, restaurant with transfer)
- ✅ Assignment priority system (Jobsheet > Order data > Default)
- ✅ Zone resolution for pickup/dropoff locations
- ✅ Time format conversion (multiple formats supported)
- ✅ Data normalization for view compatibility
- ✅ Automatic driver/vehicle assignment logic
- ✅ Tomorrow's date calculation
- ✅ Transfer requirement filtering
- ✅ Tour status filtering (Confirmed, Definite, Actual)
- ✅ Bulk assignment support
- ✅ Schedule viewing for drivers and guides

### Agent Controller (`agent-controller.md`)
**Purpose**: Comprehensive documentation of the AgentController workflow, covering agent management, creation, editing, DMC assignment, search functionality, and CSV import operations.

**Key Flows Covered**:

#### 📋 **Phase 1: Agent Listing & Display**
- Role-based agent filtering (Admin, DMC, Sales Head, Sales Manager, Assistant Sales Manager)
- DMC hierarchy traversal
- Agency-based agent filtering

#### 🆕 **Phase 2: Agent Creation Form**
- Master DMC hierarchy resolution
- Country and card type loading
- Agency selection based on user role
- City and country code data loading

#### 💾 **Phase 3: Agent Creation (Store)**
- Form validation (salutation, name, email, phone, etc.)
- Soft-deleted agent restoration
- DMC ID resolution based on role
- Image uploads (ID proof, agent profile)
- Unique agent ID generation
- Welcome email notification

#### 👁️ **Phase 4: View Agent Details**
- Encrypted ID decryption
- Agent details display

#### ✏️ **Phase 5: Edit Agent Form**
- Master DMC hierarchy resolution (same as create)
- Pre-populated form data
- Agency and country data loading

#### 💾 **Phase 6: Update Agent**
- Form validation
- Password update (optional)
- Agency information update
- Update email notification

#### 🔍 **Phase 7: AJAX Helper Endpoints**
- Get Sales Manager Details (country and ID cards)
- Update DMC ID (agent selection by DMC)
- Fetch Cities by Country
- Fetch Country Code

#### 🔎 **Phase 8: Search Agents (DMC Only)**
- DMC-only search functionality
- Filter by agency name, agent name, country, city
- Exclude already-selected agents

#### 🗑️ **Phase 9: Delete Agent**
- Soft delete agent
- Preserve data for recovery

#### 📥 **Phase 10: CSV Import - View & Download**
- Import page with upload history
- Role-based access control
- Template download with sample data

#### 📤 **Phase 11: CSV Import - Process File**
- File validation and duplicate prevention
- Batch processing with error tracking
- Upload history recording
- Detailed error reporting

**Main Actors**:
- User
- Browser
- Blade View
- AgentController
- Models (Agent, Agency, User, Country, City)
- Database
- CommonHelper
- EmailService
- Cache
- AgentsImport

**Special Features**:
- ✅ Complex DMC hierarchy traversal
- ✅ Role-based access control
- ✅ Soft delete restoration
- ✅ Image uploads to Azure Blob Storage
- ✅ Multi-DMC agent assignment (JSON array)
- ✅ Email notifications (creation & update)
- ✅ Unique ID generation
- ✅ CSV import with duplicate prevention
- ✅ AJAX endpoints for dynamic form updates

### Agency Controller (`agency-controller.md`)
**Purpose**: Comprehensive documentation of the AgencyController workflow, covering agency management, creation, editing, DMC selection, branch management, status toggling, and CSV import operations.

**Key Flows Covered**:

#### 📋 **Phase 1: Agency Listing & Display**
- Role-based agency filtering
- DMC ID resolution
- Creator/updater relationship loading

#### 🆕 **Phase 2: Agency Creation Form**
- Active countries loading
- Simple form initialization

#### 💾 **Phase 3: Agency Creation (Store)**
- Form validation (agency name, email, contact details, branches)
- Soft-deleted agency restoration
- Logo upload handling
- Branch normalization (unique branch_uid generation)
- Unique agency ID generation
- DMC ID assignment

#### 👁️ **Phase 4: View Agency Details**
- Encrypted ID decryption
- Agency details with relationships

#### ✏️ **Phase 5: Edit Agency Form**
- Pre-populated form data
- Countries loading

#### 💾 **Phase 6: Update Agency**
- Form validation
- Logo update/clear handling
- Branch normalization
- Updated_by tracking

#### 🗑️ **Phase 7: Delete Agency**
- Role-based permission check
- DMC assignment validation (prevent deletion if assigned)
- Soft delete operation

#### 🔄 **Phase 8: Toggle Agency Status**
- Active/inactive status toggle
- Updated_by tracking

#### 🔍 **Phase 9: AJAX Helper Endpoints**
- Get Cities by Country
- Get Card Types by Country (formatted for Select2)

#### 🏢 **Phase 10: DMC Agency Selection - View Page**
- Role-based access control
- Filter agencies into selected/available groups
- Selection interface display

#### ✅ **Phase 11: Select Agency for DMC (AJAX)**
- Permission validation
- First DMC vs additional DMC logic
- Welcome email (first DMC only)
- Partnership email (all DMCs)
- DMC ID addition to agency

#### ❌ **Phase 12: Remove Agency from DMC (AJAX)**
- Permission validation
- DMC ID removal from agency
- JSON field update

#### 📥 **Phase 13: CSV Import - View & Download**
- Import page with upload history
- Template download with sample data

#### 📤 **Phase 14: CSV Import - Process File**
- File validation and duplicate prevention
- Batch processing with error tracking
- Upload history recording
- Detailed error reporting

**Main Actors**:
- User
- Browser
- Blade View
- AgencyController
- Models (Agency, Country, City)
- Database
- CommonHelper
- EmailService
- Cache
- AgenciesImport

**Special Features**:
- ✅ Role-based access control
- ✅ Branch management with unique UIDs
- ✅ DMC assignment system (JSON array)
- ✅ Email notifications (welcome & partnership)
- ✅ Soft delete handling
- ✅ Logo upload/management
- ✅ Status toggle functionality
- ✅ Deletion protection (if assigned to DMCs)
- ✅ CSV import with duplicate prevention
- ✅ AJAX endpoints for dynamic updates
- ✅ Admin/Virtual DMC restrictions on agency selection

### Single Tour Package Controller (`single-tour-package-controller.md`)
**Purpose**: Comprehensive documentation of the complete Single Tour Package Controller workflow, covering both frontend interactions and backend processing.

**Key Flows Covered**:

#### 📋 **Phase 1: Form Initialization**
- User permission checks and DMC ID resolution
- Loading countries, agents, ports
- Enquiry form integration (if provided)
- Pre-selected service data loading

#### 🏗️ **Phase 2: Tour Creation (Step 1)**
- Form validation (guests, dates, agent)
- Child ages validation
- Main guest and additional guests data collection
- Tour record creation with taxes
- Email notification to agent

#### 🔍 **Phase 3: Dynamic Data Loading (API Calls)**
- City and location APIs
- Hotel APIs (hotels, rooms, beds)
- Attraction APIs (attractions, tickets, transfer pricing)
- Restaurant APIs (restaurants, meals, transfer pricing)
- Guide APIs (guides with languages)
- Transport APIs (zone-based and city-based)
- Zone APIs (zone-assigned locations)

#### 💾 **Phase 4: Service Data Collection**
- Hotel selection and price calculation
- Attraction selection with transfer and guide
- Restaurant selection with transfer
- Guide selection and pricing
- Transport selection
- Entry/exit port services

#### 🚀 **Phase 5: Service Orders Creation (Step 2)**
- Service data validation
- Transaction-safe order creation
- Room ID fixing
- Transfer options processing
- Order grouping by type and date
- Tour details compilation

#### ✏️ **Phase 6: Edit Package Flow**
- Loading existing tour and orders
- Order grouping by type and day
- Customer info extraction
- Tour days calculation

#### 🔄 **Phase 7: Individual Service Selection**
- Adding services to existing tours
- Hotel, attraction, restaurant, guide, transport selection
- Booking type determination from tour status

#### 📝 **Phase 8: Service Order Update**
- Updating hotel, transport, attraction, guide, restaurant orders
- Data merging and validation
- Database updates

#### 🔍 **Phase 9: Add Services to Existing Tour**
- Loading services for existing tour
- Pre-filling form with tour information

**Main Actors**:
- User
- Browser
- CreateForm (Frontend)
- SingleTourPackageController
- Models (Tour, Order, Hotel, Attraction, Restaurant, Guide, Vehicle, etc.)
- Database
- EmailService
- CommonHelper

**Special Features**:
- ✅ Color-coded phases for easy navigation
- ✅ Comprehensive API endpoint documentation
- ✅ Detailed pricing calculation flows
- ✅ Transaction management
- ✅ Error handling and validation
- ✅ Zone-based and city-based transport pricing
- ✅ Room ID fixing mechanism
- ✅ Order grouping and organization

## How to View These Diagrams

### Option 1: Using Mermaid Live Editor
1. Visit [Mermaid Live Editor](https://mermaid.live/)
2. Copy the Mermaid code from any `.md` file
3. Paste into the editor to view the interactive diagram

### Option 2: Using VS Code
1. Install the "Markdown Preview Mermaid Support" extension
2. Open any `.md` file
3. Use the markdown preview to view the diagrams

### Option 3: Using GitHub/GitLab
- These diagrams will render automatically when viewing the files on GitHub or GitLab

### Option 4: Using Mermaid CLI
```bash
# Install Mermaid CLI
npm install -g @mermaid-js/mermaid-cli

# Generate PNG from diagram
mmdc -i docs/sequence-diagrams/invoice-edit.md -o invoice-edit.png
```

## Diagram Conventions

- **Solid arrows** (→): Synchronous calls/requests
- **Dashed arrows** (-->>): Return values/responses
- **Notes**: Used to group related operations
- **Alt blocks**: Show conditional logic (if/else)
- **Loop blocks**: Show iterative operations
- **Actors**: Represent different components/users in the system

## Understanding the Flows

### Single Tour Package Creation
The process is divided into two main steps:
1. **Step 1**: Create the tour record (basic tour information)
2. **Step 2**: Create service orders (hotels, attractions, restaurants, etc.)

This two-step approach allows users to:
- Save tour information first
- Add services incrementally
- Handle errors gracefully

### Invoice Editing
The invoice edit flow includes:
- Real-time price calculations as users modify items
- Dynamic item management (add/remove items)
- Automatic recalculation of subtotals and totals
- Transaction-safe database updates

## Contributing

When adding new sequence diagrams:
1. Follow the existing naming convention: `{component-name}.md`
2. Include clear notes to explain complex flows
3. Use consistent actor names across related diagrams
4. Document any assumptions or special cases
5. Update this README with the new diagram information

## Related Documentation

- Controller documentation: `app/Http/Controllers/`
- Service layer: `app/Services/`
- Model documentation: `app/Models/`
