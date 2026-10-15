# Job Sheet Controller - Complete Sequence Diagram

```mermaid
sequenceDiagram
    autonumber
    participant User
    participant Browser
    participant View as Blade View
    participant Controller as JobSheetController
    participant Models as Models (Order, Jobsheet, Driver, Vehicle, Guide, Tour, etc.)
    participant DB as Database
    participant CommonHelper

    rect rgb(240, 248, 255)
        Note over User,CommonHelper: 🚗 PHASE 1: DRIVER JOBSHEET MANAGEMENT
        
        rect rgb(255, 250, 240)
            Note over User,CommonHelper: List Drivers for Jobsheet
            User->>Browser: Navigate to /jobsheet/drivers
            Browser->>Controller: GET index()
            Controller->>Controller: Get user role
            Controller->>Controller: Resolve DMC hierarchy<br/>(role 10, 19, 25, 62, 110)
            Controller->>Models: Query User WHERE master_dmc_id<br/>AND role_id = 11
            Controller->>Controller: Resolve DMC ID for drivers<br/>(role 11, 34, 66, 108, etc.)
            Controller->>Models: Query Driver WHERE dmc_id = resolvedDmcId
            Models->>DB: SELECT drivers
            DB-->>Models: Drivers list
            Controller-->>Browser: Return driver-jobs view
            Browser->>View: Render drivers list with action buttons
        end

        rect rgb(240, 255, 240)
            Note over User,CommonHelper: Create Driver Jobsheet Form
            User->>Browser: Navigate to /jobsheet/create-driver-jobsheet
            Browser->>Controller: GET createDriverJobsheet()
            Controller->>Controller: Get user role
            Controller->>Controller: Resolve DMC ID from hierarchy
            Controller->>Controller: Calculate tomorrow date<br/>(Carbon::tomorrow())
            Controller->>Models: Query Driver WHERE dmc_id = dmcId
            Controller->>Models: Query Vehicle WHERE dmc_id = dmcId
            Controller->>Models: Query Order WHERE type IN<br/>('entry_port', 'travel_hourly',<br/>'travel_point', 'exit_port',<br/>'local_transport')<br/>AND data->0->>dmc_id = dmcId<br/>AND data->0->>pickupdate = tomorrow
            Models->>DB: SELECT transportation orders
            Controller->>Models: Query Order WHERE type = 'attraction'<br/>AND data->0->>dmc_id = dmcId<br/>AND data->0->>bookingDate = tomorrow
            Controller->>Controller: Filter attraction orders<br/>WHERE transfer_required = true
            Controller->>Models: Query Order WHERE type = 'restaurant'<br/>AND data->0->>dmc_id = dmcId<br/>AND data->0->>bookingDate = tomorrow
            Controller->>Controller: Filter restaurant orders<br/>WHERE transfer_required = true
            Controller->>Controller: Merge all order types
            Controller->>Controller: Process each order:<br/>- Get zone for pickup/dropoff<br/>- Check existing jobsheet assignment<br/>- Get vehicle & driver from order<br/>- Normalize data structure
            loop For each order
                Controller->>Controller: getZoneForLocation(pickup, dmcId)
                Controller->>Models: Check Port/Hotel/Attraction/Restaurant<br/>to determine zone
                Controller->>Models: Query Jobsheet WHERE<br/>date, type, service_type,<br/>journey_time, order_id
                alt Jobsheet exists
                    Controller->>Models: Get driver & vehicle from jobsheet
                else No jobsheet
                    Controller->>Models: Get vehicle from order data
                    Controller->>Models: Get driver from vehicle (if assigned)
                end
                Controller->>Controller: Add zone, driver, vehicle to order
            end
            Controller-->>Browser: Return create-driver-jobsheet view
            Browser->>View: Render orders table with:<br/>- Pickup/dropoff locations<br/>- Zones<br/>- Assigned driver/vehicle<br/>- Assignment dropdowns
        end

        rect rgb(255, 240, 255)
            Note over User,CommonHelper: Update Driver/Vehicle Assignment
            User->>View: Select driver/vehicle from dropdown
            View->>Browser: AJAX POST /update-driver-vehicle-assignment<br/>{order_id, driver_id, vehicle_id,<br/>tour_id, date, dmc_id, type, etc.}
            Browser->>Controller: updateDriverVehicleAssignment()
            Controller->>Controller: Validate request data
            Controller->>Models: Find Order by booking_id
            Controller->>Controller: Extract entry_time from order data<br/>(if not provided)
            Controller->>Controller: Convert entry_time to HH:MM:SS format<br/>(handle AM/PM, numeric, etc.)
            Controller->>Models: Check existing Jobsheet WHERE<br/>date, journey_time, tour_id, order_id
            alt Existing jobsheet found
                Controller->>Controller: Update existing jobsheet
                alt Driver ID provided
                    Controller->>Models: Update jobsheet.driver_id
                    Controller->>Models: Find Vehicle WHERE driver_id<br/>AND dmc_id (auto-assign vehicle)
                end
                alt Vehicle ID provided
                    Controller->>Models: Update jobsheet.vehicle_id
                    Controller->>Models: Find Vehicle by vehicle_id
                    Controller->>Models: Get driver from vehicle<br/>(if vehicle has driver_id)
                    Controller->>Models: Update jobsheet.driver_id
                end
                Controller->>Models: Jobsheet::save()
                Controller->>Models: Order::update(['is_approve' => true])
            else No existing jobsheet
                Controller->>CommonHelper: Generate jobsheet_id
                Controller->>Models: Create new Jobsheet:<br/>- jobsheet_id, dmc_id, tour_id<br/>- date, type, journey_time<br/>- service_type, order_id<br/>- driver_id, vehicle_id<br/>- data (JSON: pickup, dropoff)
                Models->>DB: INSERT INTO jobsheets
            end
            Controller->>Models: Find Driver & Vehicle for response
            Controller-->>Browser: JSON {success, jobsheet, driver, vehicle}
            Browser->>View: Update UI with assigned driver/vehicle
        end

        rect rgb(240, 255, 255)
            Note over User,CommonHelper: Get Driver Schedule
            User->>View: Click "View Schedule" for driver
            View->>Browser: AJAX GET /get-driver-schedule/{driverId}
            Browser->>Controller: getDriverSchedule($driverId)
            Controller->>Models: Find Driver by driver_id
            Controller->>Models: Query Vehicle WHERE driver_id = driverId
            Controller->>Models: Query Jobsheet WHERE driver_id = driverId
            Controller->>Controller: Merge vehicle IDs from both sources
            Controller->>Models: Query Order WHERE type IN<br/>('entry_port', 'travel_hourly',<br/>'travel_point', 'exit_port',<br/>'local_transport')<br/>WHERE tour->tour_status IN<br/>('Confirmed', 'Definite', 'Actual')
            Controller->>Controller: Filter orders by vehicle_id<br/>(check if order vehicle in driver's vehicles)
            Controller->>Controller: Extract schedule data:<br/>- tour_id, pickup_date, pickup_time<br/>- pickup_location, dropoff_location<br/>- customer info, vehicle, driver<br/>- Check jobsheet for assigned driver/vehicle
            Controller->>Controller: Sort schedule by date & time
            Controller-->>Browser: JSON {success, schedule: [...]}
            Browser->>View: Display driver schedule in modal/table
        end

        rect rgb(255, 250, 240)
            Note over User,CommonHelper: Get Tour Orders (for specific tour & date)
            User->>View: Select tour & date
            View->>Browser: AJAX GET /get-tour-orders/{tourId}/{date}
            Browser->>Controller: getTourOrders($tourId, $date)
            Controller->>Models: Query Order WHERE tour_id = tourId<br/>AND type IN orderTypes
            Controller->>Controller: Filter orders by booking_date<br/>(check JSON data for date match)
            Controller->>Controller: Extract order data:<br/>- pickup_time, locations<br/>- customer info, vehicle, driver
            Controller->>Models: Query Vehicle & Driver for DMC
            Controller-->>Browser: JSON {success, data: orders,<br/>vehicles, drivers}
            Browser->>View: Populate orders table for tour
        end

        rect rgb(240, 255, 240)
            Note over User,CommonHelper: Get Orders by Date
            User->>View: Select date filter
            View->>Browser: AJAX GET /get-orders-by-date/{date}?type=driver
            Browser->>Controller: getOrdersByDate($date)
            Controller->>Controller: Get user role & resolve DMC ID
            Controller->>Controller: Determine order types<br/>(driver: transport types,<br/>guide: guide types)
            alt Type = 'driver'
                Controller->>Models: Query Order WHERE type IN<br/>transport types AND date = ?<br/>AND tour_status IN ('Confirmed', 'Definite', 'Actual')
                Controller->>Models: Query Order WHERE type = 'attraction'<br/>AND transfer_required = true
                Controller->>Models: Query Order WHERE type = 'restaurant'<br/>AND transfer_required = true
                Controller->>Controller: Merge all order types
                Controller->>Controller: Process each order:<br/>- Get zone info<br/>- Check jobsheet assignment<br/>- Normalize data structure
            end
            Controller->>Models: Query Driver & Vehicle WHERE dmc_id
            Controller-->>Browser: JSON {success, data: orders,<br/>drivers, vehicles}
            Browser->>View: Update orders table
        end

        rect rgb(255, 240, 255)
            Note over User,CommonHelper: Store Driver Assignments (Bulk)
            User->>View: Submit bulk assignments form
            View->>Browser: POST /jobsheet/store/driver/assignments<br/>{tourId, date, assignments: [...]}
            Browser->>Controller: storeDriverAssignments()
            Controller->>Controller: Validate request
            Controller->>Models: Find Tour by tour_id
            Controller->>Controller: Validate date is within tour range
            loop For each assignment
                Controller->>Models: Find Order by booking_id
                Controller->>Controller: Update order data JSON<br/>(add driver_id, driver_name,<br/>vehicles_id, vehicles_name)
                Controller->>Models: Order::save()
            end
            Controller->>Models: Jobsheet::updateOrCreate([<br/>tour_id, date, type: 'driver',<br/>assignments: JSON array])
            Models->>DB: UPDATE/INSERT jobsheets
            Controller-->>Browser: JSON {success, message, assignments}
            Browser->>View: Show success message
        end
    end

    rect rgb(255, 250, 240)
        Note over User,CommonHelper: 👨‍🏫 PHASE 2: GUIDE JOBSHEET MANAGEMENT
        
        rect rgb(240, 255, 240)
            Note over User,CommonHelper: List Guides for Jobsheet
            User->>Browser: Navigate to /jobsheet/guides
            Browser->>Controller: GET indexGuide()
            Controller->>Controller: Get user role
            Controller->>Controller: Resolve DMC hierarchy
            Controller->>Controller: Resolve DMC ID for guides
            Controller->>Models: Query Guide WHERE dmc_id = resolvedDmcId<br/>WITH languages relationship
            Models->>DB: SELECT guides with languages
            Controller-->>Browser: Return guide-jobs view
            Browser->>View: Render guides list
        end

        rect rgb(255, 250, 240)
            Note over User,CommonHelper: Create Guide Jobsheet Form
            User->>Browser: Navigate to /jobsheet/create-guide-jobsheet
            Browser->>Controller: GET createGuideJobsheet()
            Controller->>Controller: Get user role & resolve DMC ID
            Controller->>Controller: Calculate tomorrow date
            Controller->>Models: Query Guide WHERE dmc_id = dmcId<br/>WITH languages
            Controller->>Models: Query Order WHERE type IN<br/>('guide', 'dayguide', 'halfguide')<br/>AND data->0->>pickupdate = tomorrow<br/>AND data->0->>dmc_id = dmcId<br/>LEFT JOIN tours
            Models->>DB: SELECT guide orders
            Controller->>Controller: Process each order:<br/>- Convert entrytime to HH:MM:SS<br/>(handle various formats)<br/>- Check existing jobsheet assignment<br/>- Get guide from order data
            loop For each order
                Controller->>Controller: Parse entrytime:<br/>- Numeric: "4" → "04:00:00"<br/>- AM/PM: "4:00 PM" → "16:00:00"<br/>- HH:MM: "4:00" → "04:00:00"
                Controller->>Models: Query Jobsheet WHERE<br/>date, type, service_type,<br/>journey_time, order_id
                alt Jobsheet exists
                    Controller->>Models: Get guide from jobsheet
                else No jobsheet
                    Controller->>Models: Get guide from order data<br/>(guide_id from order)
                end
                Controller->>Controller: Attach guide info to order
            end
            Controller-->>Browser: Return create-guide-jobsheet view
            Browser->>View: Render guide orders table with:<br/>- Order details<br/>- Assigned guide<br/>- Guide selection dropdown
        end

        rect rgb(240, 255, 255)
            Note over User,CommonHelper: Update Guide Assignment
            User->>View: Select guide from dropdown
            View->>Browser: AJAX POST /update-guide-jobsheet<br/>{order_id, guide_id, tour_id,<br/>date, dmc_id, order_type, type, entry_time}
            Browser->>Controller: updateGuideJobsheet()
            Controller->>Models: Find Order by booking_id
            Controller->>Controller: Extract order data:<br/>- entrypickup, entrytime, type
            Controller->>Models: Check existing Jobsheet WHERE<br/>date, type, service_type,<br/>tour_id, date
            alt Existing jobsheet found
                Controller->>Models: Update jobsheet.guide_id
                Models->>DB: UPDATE jobsheets
            else No existing jobsheet
                Controller->>CommonHelper: Generate jobsheet_id
                Controller->>Models: Create new Jobsheet:<br/>- jobsheet_id, dmc_id, tour_id<br/>- date, type, journey_time<br/>- guide_id, service_type<br/>- order_id, data (JSON)
                Models->>DB: INSERT INTO jobsheets
            end
            Controller-->>Browser: JSON {success, message}
            Browser->>View: Update UI with assigned guide
        end

        rect rgb(255, 240, 255)
            Note over User,CommonHelper: Get Guide Schedule
            User->>View: Click "View Schedule" for guide
            View->>Browser: AJAX GET /get-guide-schedule/{guideId}
            Browser->>Controller: getGuideSchedule($guideId)
            Controller->>Models: Find Guide by guide_id
            Controller->>Models: Query Order WHERE type = 'guide'<br/>AND tour_status IN<br/>('Confirmed', 'Definite', 'Actual')
            Controller->>Controller: Filter orders by guide_id<br/>(check order data for guide_id match)
            Controller->>Controller: Extract schedule data:<br/>- tour_id, pickup_date, pickup_time<br/>- pickup_location, customer info<br/>- guide_name, service_type
            Controller->>Controller: Sort schedule by date & time
            Controller-->>Browser: JSON {success, schedule: [...]}
            Browser->>View: Display guide schedule
        end

        rect rgb(240, 255, 240)
            Note over User,CommonHelper: Get Tour Guide Orders
            User->>View: Select tour & date for guides
            View->>Browser: AJAX GET /get-tour-guide-orders/{tourId}/{date}
            Browser->>Controller: getTourGuideOrders($tourId, $date)
            Controller->>Models: Query Order WHERE tour_id = tourId<br/>AND type = 'guide'
            Controller->>Controller: Extract order data
            Controller->>Controller: Resolve DMC ID from order
            Controller->>Models: Query Guide WHERE dmc_id<br/>AND status = 1<br/>WITH languages
            Controller-->>Browser: JSON {success, data: orders,<br/>guides, dmc_id}
            Browser->>View: Populate guide orders table
        end

        rect rgb(255, 250, 240)
            Note over User,CommonHelper: Get Orders by Date (Guide Type)
            User->>View: Select date filter (guide type)
            View->>Browser: AJAX GET /get-orders-by-date/{date}?type=guide
            Browser->>Controller: getOrdersByDate($date)
            Controller->>Controller: Get user role & resolve DMC ID
            Controller->>Controller: Set order types = ['guide', 'dayguide', 'halfguide']
            Controller->>Models: Query Order WHERE type IN guide types<br/>AND data->0->>pickupdate = date<br/>AND data->0->>dmc_Id = dmcId<br/>AND tour_status IN ('Confirmed', 'Definite', 'Actual')<br/>LEFT JOIN tours
            Controller->>Controller: Process each order:<br/>- Check jobsheet assignment<br/>- Get guide from order/jobsheet<br/>- Add zone information
            Controller->>Models: Query Guide WHERE dmc_id<br/>WITH languages
            Controller-->>Browser: JSON {success, data: orders, guides}
            Browser->>View: Update guide orders table
        end
    end

    rect rgb(240, 248, 255)
        Note over User,CommonHelper: 📋 PHASE 3: JOBSHEET VIEWING & MANAGEMENT
        
        rect rgb(255, 250, 240)
            Note over User,CommonHelper: View All Jobsheets
            User->>Browser: Navigate to /jobsheet/view
            Browser->>Controller: GET viewJobsheets()
            Controller->>Controller: Get user role & resolve DMC ID
            Controller->>Models: Query Tour::select('tour_id', 'display_id')
            Controller-->>Browser: Return view-jobsheets view
            Browser->>View: Render jobsheets DataTable interface
        end

        rect rgb(240, 255, 240)
            Note over User,CommonHelper: Get Jobsheet Data (DataTables)
            User->>View: Load/refresh jobsheets table
            View->>Browser: AJAX GET /jobsheets/data?date={date?}
            Browser->>Controller: getJobsheetData()
            Controller->>Controller: Get user role & resolve DMC ID
            Controller->>Models: Query Jobsheet LEFT JOIN tours, users<br/>SELECT jobsheets.*, tour_display_id, dmc_name
            Controller->>Controller: Apply DMC filtering<br/>(single DMC or multiple DMCs for Master DMC)
            Controller->>Controller: Filter by date if provided
            Controller->>Controller: Decode jobsheet.data JSON
            Controller->>Controller: Format data for DataTables:<br/>- Extract pickup, dropoff, type<br/>- Get driver/vehicle/guide names<br/>- Format actions column
            Controller-->>Browser: JSON {draw, recordsTotal,<br/>recordsFiltered, data: [...]}
            Browser->>View: Populate DataTable
        end

        rect rgb(255, 240, 255)
            Note over User,CommonHelper: Get Jobsheet Details
            User->>View: Click "View" button
            View->>Browser: AJAX GET /jobsheets/{id}
            Browser->>Controller: getJobsheetDetails($id)
            Controller->>Models: Query Jobsheet LEFT JOIN tours, users<br/>WHERE jobsheet_id = id
            Controller-->>Browser: JSON {success, data: jobsheet}
            Browser->>View: Display jobsheet details modal
        end

        rect rgb(240, 255, 255)
            Note over User,CommonHelper: Export Jobsheets
            User->>View: Click "Export" button
            View->>Browser: AJAX GET /jobsheets/export?date={date?}
            Browser->>Controller: exportJobsheets()
            Controller->>Controller: Get user role & resolve DMC ID
            Controller->>Models: Query Jobsheet LEFT JOIN<br/>tours, drivers, vehicles, guides, users
            Controller->>Controller: Apply DMC & date filters
            Controller->>Controller: Format data for Excel:<br/>- Jobsheet ID, Tour ID, Date<br/>- Type, Service Type, Journey Time<br/>- Pickup/Dropoff, Driver, Vehicle, Guide<br/>- DMC, Created At
            Controller-->>Browser: JSON {success, data: [...]}
            Browser->>View: Download/display Excel data
        end
    end

    rect rgb(255, 250, 240)
        Note over User,CommonHelper: 🔍 PHASE 4: HELPER FUNCTIONS & AJAX ENDPOINTS
        
        rect rgb(240, 255, 240)
            Note over User,CommonHelper: Get Zone for Location
            Controller->>Controller: getZoneForLocation(locationName, dmcId)
            Controller->>Models: Check Port WHERE port_name LIKE locationName
            alt Port found
                Controller-->>Controller: Return port type
            else Not a port
                Controller->>Models: Check Hotel WHERE name LIKE locationName
                alt Hotel found
                    Controller->>Models: hotel->getZoneForDmc(dmcId)
                    Controller->>Models: Query Zone WHERE zone_id = zoneId
                    Controller-->>Controller: Return zone_name
                else Not a hotel
                    Controller->>Models: Check Attraction WHERE name LIKE locationName
                    alt Attraction found
                        Controller->>Models: attraction->getZoneForDmc(dmcId)
                        Controller->>Models: Query Zone WHERE zone_id = zoneId
                        Controller-->>Controller: Return zone_name
                    else Not an attraction
                        Controller->>Models: Check Restaurant WHERE name LIKE locationName
                        alt Restaurant found
                            Controller->>Models: restaurant->getZoneForDmc(dmcId)
                            Controller->>Models: Query Zone WHERE zone_id = zoneId
                            Controller-->>Controller: Return zone_name
                        else Not found
                            Controller-->>Controller: Return 'N/A'
                        end
                    end
                end
            end
        end

        rect rgb(255, 240, 255)
            Note over User,CommonHelper: AJAX Helper Endpoints
            User->>View: Select Master DMC
            View->>Browser: AJAX GET /get-dmcs/{masterDmcId}
            Browser->>Controller: getDmcsByMaster($masterDmcId)
            Controller->>Models: Query User WHERE master_dmc_id<br/>AND role_id = 11
            Controller-->>Browser: JSON {success, dmcs: [...]}
            
            User->>View: Select DMC
            View->>Browser: AJAX GET /get-drivers/{dmcId}
            Browser->>Controller: getDriversByDmc($dmcId)
            Controller->>Models: Query Driver WHERE dmc_id<br/>AND status = 1
            Controller-->>Browser: JSON {success, drivers: [...]}
            
            User->>View: Select DMC (for guides)
            View->>Browser: AJAX GET /get-guides/{dmcId}
            Browser->>Controller: getGuidesByDmc($dmcId)
            Controller->>Models: Query Guide WHERE dmc_id<br/>AND status = 1<br/>WITH languages
            Controller-->>Browser: JSON {success, guides: [...]}
            
            User->>View: Select tour
            View->>Browser: AJAX GET /get-tour-details/{tourId}
            Browser->>Controller: getTourDetails($tourId)
            Controller->>Models: Query Tour WHERE tour_id<br/>SELECT tour_id, check_in_time, check_out_time
            Controller->>Controller: Add tomorrow_date to response
            Controller-->>Browser: JSON {success, tour: {...}}
            
            User->>View: Load tours filter
            View->>Browser: AJAX GET /get-tours
            Browser->>Controller: getAllTours()
            Controller->>Controller: Get user role & resolve DMC ID
            alt DMC ID exists
                Controller->>Models: Query Tour JOIN Order<br/>WHERE order types IN transport/guide<br/>AND order data contains dmc_id
                Controller->>Controller: Filter tours with matching DMC ID
            else Admin user
                Controller->>Models: Query Tour::all()
            end
            Controller-->>Browser: JSON {success, tours: [...]}
        end
    end
```

## Key Features Covered

### 🔐 **Authentication & Authorization**
- Complex DMC hierarchy resolution (Master DMC, Product Head, Product Manager, Operation Head, Operation Manager)
- Role-based access control for drivers and guides
- DMC-specific filtering for all operations

### 🚗 **Driver Jobsheet Management**
1. **Order Types Handled**:
   - **Transport Orders**: entry_port, exit_port, travel_hourly, travel_point, local_transport
   - **Attraction Orders**: With transfer_required = true
   - **Restaurant Orders**: With transfer_required = true

2. **Assignment Priority**:
   - **Priority 1**: Jobsheet assignment (existing jobsheet record)
   - **Priority 2**: Vehicle from order data (if vehicle has driver)
   - **Priority 3**: Default (no assignment)

3. **Zone Resolution**:
   - Automatically determines zone for pickup/dropoff locations
   - Checks Port, Hotel, Attraction, Restaurant models
   - Uses DMC-specific zone assignments

4. **Data Normalization**:
   - Normalizes different order data structures
   - Extracts pickup time from various formats (visitTime, guide_options)
   - Standardizes location names

### 👨‍🏫 **Guide Jobsheet Management**
1. **Order Types Handled**:
   - guide, dayguide, halfguide

2. **Time Format Conversion**:
   - Handles multiple time formats:
     - Numeric: "4" → "04:00:00"
     - AM/PM: "4:00 PM" → "16:00:00"
     - HH:MM: "4:00" → "04:00:00"
     - HH:MM:SS: "04:00:00" (already correct)

3. **Guide Assignment**:
   - Checks existing jobsheet first
   - Falls back to guide from order data
   - Supports guide languages relationship

### 📋 **Jobsheet Viewing & Management**
1. **DataTables Integration**:
   - Server-side processing
   - DMC filtering
   - Date filtering
   - Export functionality

2. **Jobsheet Details**:
   - Complete jobsheet information
   - Related tour, driver, vehicle, guide data
   - JSON data decoding

### 🔍 **Helper Functions**
1. **Zone Resolution**:
   - `getZoneForLocation()`: Determines zone for any location
   - Checks multiple model types (Port, Hotel, Attraction, Restaurant)
   - Returns zone name or 'N/A'

2. **AJAX Endpoints**:
   - Get DMCs by Master DMC
   - Get Drivers by DMC
   - Get Guides by DMC
   - Get Tour Details
   - Get All Tours (filtered by DMC)

### 🎨 **Visual Organization**
- Color-coded phases for easy navigation
- Clear separation of driver vs guide flows
- Comprehensive assignment logic
- Detailed data processing steps

### 📊 **Data Processing**
- **JSON Data Handling**: Parses and processes complex JSON structures
- **Date Filtering**: Filters orders by booking_date, pickupdate, bookingDate
- **Time Parsing**: Handles multiple time formats with regex
- **Zone Calculation**: Dynamic zone resolution based on location type
- **Assignment Merging**: Combines multiple order types (transport + attraction + restaurant)

### 🔄 **Assignment Workflow**
1. **Load Orders**: Get orders for tomorrow or specific date
2. **Check Existing**: Look for existing jobsheet assignments
3. **Default Values**: Use order data as fallback
4. **User Assignment**: Allow user to select driver/vehicle/guide
5. **Save Assignment**: Create or update jobsheet record
6. **Update Order**: Mark order as approved (is_approve = true)

### 🎯 **Special Features**
- ✅ Tomorrow's date calculation (Carbon::tomorrow())
- ✅ Transfer requirement filtering (only orders with transfer_required = true)
- ✅ Tour status filtering (Confirmed, Definite, Actual)
- ✅ Automatic vehicle assignment when driver selected
- ✅ Automatic driver assignment when vehicle has driver
- ✅ Zone-based location resolution
- ✅ Data normalization for view compatibility
- ✅ Bulk assignment support
- ✅ Schedule viewing for drivers and guides
- ✅ Excel export functionality
