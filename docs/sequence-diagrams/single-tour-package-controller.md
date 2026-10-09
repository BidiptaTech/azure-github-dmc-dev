# Single Tour Package Controller - Complete Sequence Diagram

```mermaid
sequenceDiagram
    autonumber
    participant User
    participant Browser
    participant CreateForm
    participant Controller as SingleTourPackageController
    participant Models as Models (Tour, Order, Hotel, etc.)
    participant DB as Database
    participant EmailService
    participant CommonHelper

    rect rgb(240, 248, 255)
        Note over User,CommonHelper: 📋 PHASE 1: FORM INITIALIZATION
        User->>Browser: Navigate to create form
        Browser->>Controller: GET /single-tour-package/create
        Controller->>Controller: Check user permissions & roles
        Controller->>Controller: Get DMC ID from user hierarchy
        Controller->>Models: Fetch countries (filtered by DMC country)
        Controller->>Models: Fetch agents (by agency & DMC)
        Controller->>Models: Fetch ports (by country)
        alt Enquiry ID provided
            Controller->>Models: Load EnquiryForm data
            Controller->>Models: Fetch pre-selected hotels
            Controller->>Models: Fetch pre-selected attractions
            Controller->>Models: Fetch pre-selected guides
            Controller->>Models: Fetch pre-selected vehicles
            Controller->>Models: Fetch pre-selected restaurants & meals
            Controller->>Models: Get entry/exit port locations
        end
        Controller-->>Browser: Return create view with all data
        Browser->>CreateForm: Render form with initialization
        CreateForm->>CreateForm: Setup Google Maps autocomplete
        CreateForm->>CreateForm: Initialize service sections
    end

    rect rgb(255, 250, 240)
        Note over User,CommonHelper: 🏗️ PHASE 2: TOUR CREATION (Step 1)
        User->>CreateForm: Fill tour details & click "Create Tour Package"
        CreateForm->>CreateForm: Validate form fields<br/>(country, dates, agent, guests)
        CreateForm->>CreateForm: Validate guest counts<br/>(adults ≥ 1, male + female = adults)
        CreateForm->>CreateForm: Validate child ages<br/>(if children > 0)
        CreateForm->>CreateForm: Collect main guest data
        CreateForm->>CreateForm: Collect additional guests data
        
        alt Tour not created yet
            CreateForm->>Browser: POST /single-tour-package/store (AJAX)
            Browser->>Controller: store()
            Controller->>Controller: Validate request data
            Controller->>CommonHelper: Generate tour_id & display_id
            Controller->>Models: Get DMC taxes (Tax model)
            Controller->>Models: Create Tour record<br/>(destination, dates, guests, agent, taxes)
            Models->>DB: INSERT INTO tours
            DB-->>Models: Tour created
            Models-->>Controller: Tour object
            Controller->>Models: Update EnquiryForm with tour_id
            Controller->>EmailService: Send tour proposal email to agent
            EmailService-->>Controller: Email sent
            Controller-->>Browser: JSON {success: true, tour_id, display_id}
            Browser->>CreateForm: Store tour_id globally
            CreateForm->>CreateForm: Show success notification
        else Tour already exists
            CreateForm->>CreateForm: Use existing tour_id
        end
    end

    rect rgb(240, 255, 240)
        Note over User,CommonHelper: 🔍 PHASE 3: DYNAMIC DATA LOADING (API Calls)
        
        rect rgb(255, 255, 240)
            Note over User,CommonHelper: City & Location APIs
            User->>CreateForm: Select country
            CreateForm->>Browser: GET /fetch-cities-by-country?country={name}
            Browser->>Controller: fetchCitiesByCountry()
            Controller->>Models: Query cities by country
            Models->>DB: SELECT cities WHERE country = ?
            DB-->>Models: Cities list
            Models-->>Controller: JSON {cities: [...]}
            Controller-->>Browser: Update city dropdown
        end

        rect rgb(240, 255, 255)
            Note over User,CommonHelper: Hotel APIs
            User->>CreateForm: Select city
            CreateForm->>Browser: GET /fetch-hotels?city={city}&dmc_id={id}
            Browser->>Controller: fetchHotels()
            Controller->>Models: Query hotels by city & DMC
            Models->>DB: SELECT hotels WHERE city = ? AND dmc_id JSON contains ?
            DB-->>Models: Hotels list
            Models-->>Controller: JSON {hotels: [...]}
            Controller-->>Browser: Populate hotel list
            
            User->>CreateForm: Select hotel
            CreateForm->>Browser: GET /fetch-rooms?hotel_id={id}&dmc_id={id}
            Browser->>Controller: fetchRooms()
            Controller->>Models: Query rooms by hotel & DMC
            Models->>DB: SELECT rooms WHERE hotel_id = ? AND created_by = ?
            DB-->>Models: Rooms with beds
            Models-->>Controller: JSON {rooms: [...]}
            Controller-->>Browser: Populate room selection
            
            User->>CreateForm: Select room
            CreateForm->>Browser: GET /fetch-beds?room_id={id}
            Browser->>Controller: fetchBeds()
            Controller->>Models: Query beds by room
            Models->>DB: SELECT beds WHERE room_id = ? AND is_active = 1
            DB-->>Models: Beds list
            Models-->>Controller: JSON {beds: [...]}
            Controller-->>Browser: Populate bed selection
        end

        rect rgb(255, 240, 255)
            Note over User,CommonHelper: Attraction APIs
            CreateForm->>Browser: GET /fetch-attractions-by-dmc?city={city}&dmc_id={id}
            Browser->>Controller: fetchAttractionsByDmc()
            Controller->>Models: Query attractions by city & DMC
            Controller->>Controller: Parse time slots from JSON
            Models-->>Controller: JSON {attractions: [{time_slots: [...]}]}
            Controller-->>Browser: Populate attraction list
            
            User->>CreateForm: Select attraction
            CreateForm->>Browser: GET /fetch-tickets?attraction_id={id}&dmc_id={id}
            Browser->>Controller: fetchTickets()
            Controller->>Models: Query tickets by attraction & DMC
            Models-->>Controller: JSON {tickets: [...]}
            Controller-->>Browser: Populate ticket selection
            
            User->>CreateForm: Select transfer options
            CreateForm->>Browser: GET /fetch-attraction-transfer-pricing<br/>(vehicle_id, attraction_id, pickup_location)
            Browser->>Controller: fetchAttractionTransferPricing()
            Controller->>Models: Get attraction zone assignments
            Controller->>Models: Get pickup location zone assignments
            Controller->>Models: Query VehicleZoneMapping
            Models->>DB: SELECT mappings WHERE from_zone_id = ? AND to_zone_id = ?
            DB-->>Models: Zone mapping with prices
            Models-->>Controller: JSON {price: amount, transfer_type: "Private/Shared"}
            Controller-->>Browser: Update transfer price
        end

        rect rgb(240, 255, 240)
            Note over User,CommonHelper: Restaurant APIs
            CreateForm->>Browser: GET /fetch-restaurants-by-dmc?city={city}&dmc_id={id}
            Browser->>Controller: fetchRestaurantsByDmc()
            Controller->>Models: Query restaurants by city & DMC
            Controller->>Controller: Format meal types with time slots
            Models-->>Controller: JSON {restaurants: [{meal_types: [...]}]}
            Controller-->>Browser: Populate restaurant list
            
            User->>CreateForm: Select restaurant
            CreateForm->>Browser: GET /fetch-meals-by-restaurant?restaurant_id={id}&meal_period={period}
            Browser->>Controller: fetchMealsByRestaurant()
            Controller->>Models: Query meals by restaurant & DMC
            Models-->>Controller: JSON {meals: [...]}
            Controller-->>Browser: Populate meal selection
            
            User->>CreateForm: Select transfer options
            CreateForm->>Browser: GET /fetch-restaurant-transfer-pricing
            Browser->>Controller: fetchRestaurantTransferPricing()
            Controller->>Models: Query zone mappings
            Models-->>Controller: JSON {price: amount}
            Controller-->>Browser: Update transfer price
        end

        rect rgb(255, 255, 240)
            Note over User,CommonHelper: Guide APIs
            CreateForm->>Browser: GET /fetch-guides-by-dmc?city={city}&dmc_id={id}
            Browser->>Controller: fetchGuidesByDmc()
            Controller->>Models: Query guides by city & DMC
            Controller->>Models: Load guide languages relationship
            Models-->>Controller: JSON {guides: [{languages: [...]}]}
            Controller-->>Browser: Populate guide list
        end

        rect rgb(240, 240, 255)
            Note over User,CommonHelper: Transport APIs
            User->>CreateForm: Select pickup & dropoff zones
            CreateForm->>Browser: GET /fetch-vehicles-by-zones<br/>(from_zone_id, to_zone_id, zone_status)
            Browser->>Controller: fetchVehiclesByZones()
            alt Zone status = 1 (Zone-based)
                Controller->>Models: Get actual zone IDs from locations
                Controller->>Models: Query VehicleZoneMapping (bidirectional)
                Models->>DB: SELECT mappings WHERE (from_zone_id, to_zone_id) OR (to_zone_id, from_zone_id)
                DB-->>Models: Zone mappings with prices
                Controller->>Controller: Deduplicate vehicles by vehicle_id
                Controller->>Controller: Use mapping prices (private_price, shared_price)
            else Zone status = 0 (City-based)
                Controller->>Models: Query vehicles by city & DMC
                Models->>DB: SELECT vehicles WHERE city = ? AND dmc_id = ?
                DB-->>Models: Vehicles list
                Controller->>Controller: Use base prices
            end
            Models-->>Controller: JSON {vehicles: [{private_price, shared_price}]}
            Controller-->>Browser: Populate vehicle selection
            
            alt Point-to-point or Hourly
                CreateForm->>Browser: GET /fetch-vehicles-by-city-and-dmc?city={city}&show_all={bool}
                Browser->>Controller: fetchVehiclesByCityAndDmc()
                Controller->>Models: Query vehicles by city & DMC
                Models-->>Controller: JSON {vehicles: [...]}
                Controller-->>Browser: Populate vehicle list
            end
        end

        rect rgb(255, 240, 240)
            Note over User,CommonHelper: Zone APIs
            CreateForm->>Browser: GET /fetch-zones?dmc_id={id}
            Browser->>Controller: fetchZones()
            Controller->>Models: Query zone-assigned locations<br/>(attractions, hotels, restaurants)
            Controller->>Models: Fallback to DMC-selected locations
            Models-->>Controller: JSON {zones: [{zone_id, zone_name, zone_type}]}
            Controller-->>Browser: Populate zone dropdowns
        end
    end

    rect rgb(255, 240, 248)
        Note over User,CommonHelper: 💾 PHASE 4: SERVICE DATA COLLECTION
        User->>CreateForm: Select hotel (rooms, beds, meals)
        CreateForm->>CreateForm: Calculate hotel price<br/>(bed_price × rooms × meals)
        CreateForm->>CreateForm: Add to hotel_data JSON
        
        User->>CreateForm: Select attraction (ticket, transfer, guide)
        CreateForm->>CreateForm: Calculate attraction total<br/>(base + transfer + guide)
        CreateForm->>CreateForm: Add to attraction_data JSON
        
        User->>CreateForm: Select restaurant (meal, transfer)
        CreateForm->>CreateForm: Calculate restaurant total<br/>(meal_price + transfer)
        CreateForm->>CreateForm: Add to restaurant_data JSON
        
        User->>CreateForm: Select guide (package hours, pickup time)
        CreateForm->>CreateForm: Calculate guide price<br/>(base_price + surcharge)
        CreateForm->>CreateForm: Add to guide_data JSON
        
        User->>CreateForm: Select transport (vehicle, pickup, dropoff)
        CreateForm->>CreateForm: Calculate transport price
        CreateForm->>CreateForm: Add to transport_data JSON
        
        User->>CreateForm: Select entry/exit port services
        CreateForm->>CreateForm: Add to entry_port_data / exit_port_data JSON
    end

    rect rgb(240, 248, 255)
        Note over User,CommonHelper: 🚀 PHASE 5: SERVICE ORDERS CREATION (Step 2)
        User->>CreateForm: Click "Submit All Services"
        CreateForm->>CreateForm: Validate service selections
        CreateForm->>CreateForm: Update all service data fields
        CreateForm->>CreateForm: Calculate total package price
        CreateForm->>Browser: POST /single-tour-package/store-orders (AJAX)
        Browser->>Controller: storeServiceOrders()
        Controller->>Controller: Validate request data
        Controller->>DB: BEGIN TRANSACTION
        
        loop For each service type
            Controller->>Controller: Decode JSON service data
            alt Hotel Service
                loop For each hotel booking
                    Controller->>Controller: Fix room IDs (replace generated IDs)
                    Controller->>Controller: Enhance hotel data<br/>(customer info, hotel details)
                    Controller->>Controller: Process transfer options
                    Controller->>CommonHelper: Generate unique booking_id
                    Controller->>Models: Create Order (type: 'hotel')
                    Models->>DB: INSERT INTO orders
                end
            else Attraction Service
                loop For each attraction
                    Controller->>Controller: Process transfer options<br/>(fetch vehicle name from DB)
                    Controller->>Controller: Normalize transfer type<br/>(Private/Shared)
                    Controller->>CommonHelper: Generate unique booking_id
                    Controller->>Models: Create Order (type: 'attraction')
                    Models->>DB: INSERT INTO orders
                end
            else Restaurant Service
                loop For each restaurant
                    Controller->>Controller: Process transfer options
                    Controller->>CommonHelper: Generate unique booking_id
                    Controller->>Models: Create Order (type: 'restaurant')
                    Models->>DB: INSERT INTO orders
                end
            else Guide Service
                loop For each guide
                    Controller->>CommonHelper: Generate unique booking_id
                    Controller->>Models: Create Order (type: 'guide')
                    Models->>DB: INSERT INTO orders
                end
            else Transport Service
                loop For each transport
                    Controller->>Controller: Determine travel_type<br/>(travel_hourly, travel_point, local_transport)
                    Controller->>CommonHelper: Generate unique booking_id
                    Controller->>Models: Create Order (type: travel_type)
                    Models->>DB: INSERT INTO orders
                end
            else Entry/Exit Port
                loop For each port service
                    Controller->>Controller: Format dates<br/>(extract from date range)
                    Controller->>CommonHelper: Generate unique booking_id
                    Controller->>Models: Create Order (type: entry_port/exit_port)
                    Models->>DB: INSERT INTO orders
                end
            end
        end
        
        Controller->>Models: Update local_transfer → local_transport
        Controller->>DB: COMMIT TRANSACTION
        Controller->>Models: Fetch tour details
        Controller->>Models: Fetch all service orders
        Controller->>Controller: Group services by date
        Controller->>Controller: Build tour_details object
        Controller-->>Browser: JSON {success: true, tour_details, created_orders}
        Browser->>CreateForm: Redirect to thank you page
    end

    rect rgb(255, 250, 240)
        Note over User,CommonHelper: ✏️ PHASE 6: EDIT PACKAGE FLOW
        User->>Browser: Navigate to edit package
        Browser->>Controller: GET /single-tour-package/editpackage/{tour_id}
        Controller->>Controller: Decrypt tour_id
        Controller->>Models: Find Tour by tour_id
        Controller->>Models: Get tour agent
        Controller->>CommonHelper: Get DMC ID
        Controller->>Models: Fetch hotels (by country & DMC)
        Controller->>Models: Fetch guides (by DMC)
        Controller->>Models: Fetch restaurants (by DMC)
        Controller->>Models: Fetch attractions (by DMC)
        Controller->>Models: Fetch vehicles (by DMC)
        Controller->>Models: Fetch countries, cities, ports
        Controller->>Models: Fetch all orders for tour
        Controller->>Controller: Extract customer info from first order
        Controller->>Controller: Calculate tour days (check-in to check-out)
        Controller->>Controller: Group orders by type
        Controller->>Controller: Group orders by day (booking date)
        Controller->>Controller: Separate hotel orders
        Controller-->>Browser: Return edit view with all data
        Browser->>CreateForm: Render edit form with existing orders
    end

    rect rgb(240, 255, 240)
        Note over User,CommonHelper: 🔄 PHASE 7: INDIVIDUAL SERVICE SELECTION
        User->>CreateForm: Click "Add Hotel" (from edit page)
        CreateForm->>Browser: POST /order-select-hotel
        Browser->>Controller: orderSelectHotel()
        Controller->>Models: Get tour & agent_id
        Controller->>Controller: Determine bookingType from tour_status
        Controller->>Controller: Fix room IDs in booking data
        Controller->>CommonHelper: Generate unique booking_id
        Controller->>Models: Create Order (type: 'hotel')
        Controller->>Models: Update tour destination (if hotel location provided)
        Controller-->>Browser: Redirect with success message
        
        Note over User,CommonHelper: Similar flows for other services...
        User->>CreateForm: Add attraction/restaurant/guide/transport
        CreateForm->>Browser: POST /order-select-{service}
        Browser->>Controller: orderSelect{Service}()
        Controller->>Models: Create Order
        Controller-->>Browser: Redirect with success
    end

    rect rgb(255, 240, 248)
        Note over User,CommonHelper: 📝 PHASE 8: SERVICE ORDER UPDATE
        User->>CreateForm: Edit service order
        CreateForm->>Browser: PUT /update-service-order/{order}
        Browser->>Controller: updateServiceOrder()
        Controller->>Controller: Validate request by service type
        Controller->>Models: Get existing order data
        
        alt Hotel Update
            Controller->>Controller: Merge hotel details
            Controller->>Controller: Fix room IDs
            Controller->>Controller: Update booking dates
        else Transport Update
            Controller->>Controller: Update pickup/dropoff locations
            Controller->>Controller: Update vehicle details
            Controller->>Controller: Update pricing
        else Attraction Update
            Controller->>Controller: Update attraction details
            Controller->>Controller: Update ticket & pricing
        else Guide Update
            Controller->>Controller: Update guide details
            Controller->>Controller: Update package hours
        else Restaurant Update
            Controller->>Controller: Update restaurant details
            Controller->>Controller: Update meal description
        end
        
        Controller->>Models: Save updated order
        Models->>DB: UPDATE orders SET data = ?
        DB-->>Models: Order updated
        Models-->>Controller: Success
        Controller-->>Browser: JSON {success: true, message: "Updated"}
        Browser->>CreateForm: Show success notification
    end

    rect rgb(240, 240, 255)
        Note over User,CommonHelper: 🔍 PHASE 9: ADD SERVICES TO EXISTING TOUR
        User->>Browser: Click "Add Services"
        Browser->>Controller: GET /single-tour-package/add-services/{tour_id}
        Controller->>Models: Find existing tour
        Controller->>Models: Fetch hotels (by city & DMC)
        Controller->>Models: Fetch attractions (by DMC)
        Controller->>Models: Fetch tickets (by attraction IDs)
        Controller->>Models: Fetch guides (by DMC)
        Controller->>Models: Fetch restaurants & meals (by DMC)
        Controller->>Models: Fetch zones (by city & DMC)
        Controller->>Models: Fetch vehicles (by zone mappings)
        Controller->>Controller: Prepare serviceData object
        Controller-->>Browser: Return create view with existing tour data
        Browser->>CreateForm: Render form (pre-filled with tour info)
    end
```

## Key Features Covered

### 🔐 **Authentication & Authorization**
- User permission checks
- DMC ID resolution from user hierarchy
- Role-based access control

### 📊 **Data Management**
- Enquiry form integration
- Tour creation with taxes
- Guest data collection (main + additional)
- Service data collection and validation

### 🔄 **Service Types Supported**
1. **Hotels**: Rooms, beds, meals, transfer options
2. **Attractions**: Tickets, transfer, guide options
3. **Restaurants**: Meals, transfer options
4. **Guides**: Package hours, languages, pricing
5. **Transport**: Point-to-point, hourly, local transfer
6. **Ports**: Entry/exit port services

### 💰 **Pricing Calculations**
- Hotel: `bed_price × number_of_rooms × number_of_meals`
- Attraction: `base_price + transfer_price + guide_price`
- Restaurant: `meal_price + transfer_price`
- Guide: `base_price + surcharge`
- Transport: Zone-based or city-based pricing

### 🗄️ **Database Operations**
- Transaction-safe order creation
- Room ID fixing (replace generated IDs with DB IDs)
- Order grouping by type and date
- Tour days calculation

### 📧 **Notifications**
- Tour proposal email to agent
- Success notifications
- Error handling

### 🎨 **Visual Organization**
- Color-coded phases for easy navigation
- Clear separation of concerns
- Comprehensive flow coverage
- Detailed API endpoint documentation
