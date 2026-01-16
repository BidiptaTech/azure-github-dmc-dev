# Hotel Controller - Complete Sequence Diagram

```mermaid
sequenceDiagram
    autonumber
    participant User
    participant Browser
    participant View as Blade View
    participant Controller as HotelController
    participant Models as Models (Hotel, Room, Rate, Bed, etc.)
    participant DB as Database
    participant CommonHelper
    participant AzureStorage

    rect rgb(240, 248, 255)
        Note over User,AzureStorage: 🏨 PHASE 1: HOTEL MANAGEMENT
        
        rect rgb(255, 250, 240)
            Note over User,AzureStorage: List Hotels
            User->>Browser: Navigate to /hotels
            Browser->>Controller: GET index()
            Controller->>Controller: Check permission: 'view hotel'
            Controller->>Controller: Get user role & resolve DMC ID
            Controller->>Controller: Resolve DMC hierarchy<br/>(role 10, 11, 25, 59, 83, etc.)
            Controller->>Models: Query Hotel based on role:<br/>- Admin: status IN (1, 3)<br/>- DMC: whereJsonContains('dmc_id', dmc_id)<br/>- Master DMC: filter by master_dmc_id
            Models->>DB: SELECT hotels with filters
            DB-->>Models: Hotels list
            Models-->>Controller: Hotels collection
            Controller-->>Browser: Return hotels view
            Browser->>View: Render hotels table with:<br/>- Hotel details<br/>- Status indicators<br/>- Action buttons
        end

        rect rgb(240, 255, 240)
            Note over User,AzureStorage: Create Hotel
            User->>Browser: Navigate to /hotels/create
            Browser->>Controller: GET create()
            Controller->>Models: Query HotelCategory::all()
            Controller->>Models: Query Facility::all()
            Controller->>Models: Query Country::where('is_active', 1)
            Controller->>Controller: Resolve DMC users based on role
            Controller-->>Browser: Return add-hotel view
            Browser->>View: Render hotel creation form
            
            User->>View: Fill form & submit
            View->>Browser: POST /hotels/store
            Browser->>Controller: store()
            Controller->>Controller: Validate request data
            Controller->>CommonHelper: Generate display_id<br/>(countryCode + hotelName + random)
            Controller->>CommonHelper: Generate unique_id
            Controller->>Controller: Process master_image upload
            Controller->>CommonHelper: image_path('file_storage', image)
            CommonHelper->>AzureStorage: Upload to Azure Blob
            AzureStorage-->>CommonHelper: Image URL
            Controller->>Controller: Process gallery images (all_images)
            loop For each image
                Controller->>CommonHelper: image_path('file_storage', image)
                CommonHelper->>AzureStorage: Upload image
                AzureStorage-->>CommonHelper: Image URL
            end
            Controller->>Models: Hotel::create([<br/>name, address, coordinates,<br/>images, status, dmc_id, etc.])
            Models->>DB: INSERT INTO hotels
            DB-->>Models: Hotel created
            Models-->>Controller: Hotel instance
            Controller-->>Browser: Redirect to contact details
            Browser->>View: Show success message
        end

        rect rgb(255, 240, 255)
            Note over User,AzureStorage: Edit Hotel
            User->>Browser: Navigate to /hotels/{id}/edit
            Browser->>Controller: GET edit($id)
            Controller->>Controller: Check permission: 'edit hotel'
            Controller->>Models: Find Hotel by hotel_unique_id
            Models->>DB: SELECT hotel WHERE hotel_unique_id = ?
            DB-->>Models: Hotel data
            Controller->>Models: Query Facility::all()
            Controller->>Models: Query HotelCategory::all()
            Controller->>Models: Query Country & City
            Controller->>Controller: Decode JSON fields:<br/>- port_of_entry<br/>- port_of_exit<br/>- others<br/>- images<br/>- weekend_days
            Controller-->>Browser: Return edit-hotel view
            Browser->>View: Render edit form with hotel data
            
            User->>View: Update form & submit
            View->>Browser: PUT /hotels/{id}
            Browser->>Controller: update($id)
            Controller->>Controller: Validate request
            Controller->>Controller: Check request size (100MB limit)
            Controller->>Models: Find Hotel by hotel_unique_id
            Controller->>Controller: Handle master image:<br/>- Check if removed<br/>- Delete old from Azure<br/>- Upload new if provided
            Controller->>CommonHelper: deleteAzureImage(old_image)
            CommonHelper->>AzureStorage: Delete old image
            Controller->>Controller: Handle gallery images:<br/>- Get existing images<br/>- Filter removed images<br/>- Delete removed from Azure<br/>- Upload new images
            Controller->>Models: Hotel::update([<br/>name, address, images,<br/>facilities, policies, etc.])
            Models->>DB: UPDATE hotels SET ...
            DB-->>Models: Hotel updated
            Controller-->>Browser: Redirect to contact details
            Browser->>View: Show success message
        end

        rect rgb(240, 255, 255)
            Note over User,AzureStorage: Delete Hotel
            User->>Browser: Click delete button
            Browser->>Controller: DELETE /hotels/{id}
            Controller->>Controller: destroy($id)
            Controller->>Models: Find Hotel by hotel_unique_id
            Controller->>Models: Check DMC assignments<br/>hotel->getSelectedDmcIds()
            alt Hotel has DMC assignments
                Controller-->>Browser: Error: Cannot delete<br/>(hotel assigned to DMCs)
            else No DMC assignments
                Controller->>CommonHelper: deleteAzureImage(main_image)
                CommonHelper->>AzureStorage: Delete main image
                Controller->>Controller: Delete gallery images
                loop For each gallery image
                    Controller->>CommonHelper: deleteAzureImage(image)
                    CommonHelper->>AzureStorage: Delete image
                end
                Controller->>Models: Hotel::delete()
                Controller->>Models: Room::where('hotel_id', id)->delete()
                Models->>DB: DELETE FROM hotels<br/>DELETE FROM rooms
                Controller-->>Browser: Redirect with success
            end
        end
    end

    rect rgb(255, 250, 240)
        Note over User,AzureStorage: 📞 PHASE 2: CONTACT & PORT MANAGEMENT
        
        rect rgb(240, 255, 240)
            Note over User,AzureStorage: Contact Details
            User->>Browser: Navigate to /hotels/{id}/contact
            Browser->>Controller: GET hotelcontacts($hotelId)
            Controller->>Models: Find Hotel by hotel_unique_id
            Controller-->>Browser: Return contactdetails view
            Browser->>View: Render contact form
            
            User->>View: Update contacts & submit
            View->>Browser: POST /hotels/update-contacts
            Browser->>Controller: updatecontacts()
            Controller->>Controller: Validate contact data
            Controller->>Models: Hotel::update([<br/>hotel_owner_company_name,<br/>management_comp_name,<br/>reservation contacts,<br/>director contacts, etc.])
            Models->>DB: UPDATE hotels SET ...
            Controller-->>Browser: Redirect to ports page
        end

        rect rgb(255, 240, 255)
            Note over User,AzureStorage: Port Management
            User->>Browser: Navigate to /hotels/{id}/ports
            Browser->>Controller: GET showPorts($id)
            Controller->>Models: Find Hotel
            Controller->>Controller: Decode port JSON:<br/>- port_of_entry<br/>- port_of_exit<br/>- others
            Controller-->>Browser: Return ports view
            Browser->>View: Render port management form
            
            User->>View: Add/update ports & submit
            View->>Browser: POST /hotels/update-ports
            Browser->>Controller: updateports()
            Controller->>Controller: processPortData()<br/>for entry, exit, others
            Controller->>Controller: Build port data arrays:<br/>- type, port_name<br/>- latitude, longitude<br/>- distance
            Controller->>Models: Hotel::update([<br/>port_of_entry: JSON,<br/>port_of_exit: JSON,<br/>others: JSON])
            Models->>DB: UPDATE hotels SET port_of_entry, ...
            Controller-->>Browser: Redirect to facilities
        end
    end

    rect rgb(240, 255, 240)
        Note over User,AzureStorage: 🛏️ PHASE 3: ROOM MANAGEMENT
        
        rect rgb(255, 250, 240)
            Note over User,AzureStorage: List Rooms
            User->>Browser: Navigate to /hotels/{id}/rooms
            Browser->>Controller: GET hotelrooms()
            Controller->>Controller: Get user role
            alt Admin (role 1, 20)
                Controller->>Models: Query Room with hotel<br/>GROUP BY hotel_id
            else DMC Users
                Controller->>Models: Query Room WHERE<br/>hotel->whereJsonContains('dmc_id', userId)<br/>AND (dmc_base_room = 1 OR created_by = userId)
            end
            Models->>DB: SELECT rooms
            Controller->>Models: Query Restaurant::all()
            Controller->>Models: Query Meal::all()
            Controller-->>Browser: Return rooms view
            Browser->>View: Render rooms list
        end

        rect rgb(240, 255, 240)
            Note over User,AzureStorage: Create Room
            User->>Browser: Navigate to /hotels/{id}/create-room
            Browser->>Controller: GET createHotelRooms($hotel_unique_id)
            Controller->>Models: Find Hotel by hotel_unique_id
            Controller->>Controller: Get user role & DMC info
            Controller->>Models: Query RoomType::all()
            Controller->>Models: Query Restaurant::all()
            Controller->>Models: Query Meal grouped by type
            Controller->>Controller: Determine rooms to show:<br/>- Admin: all rooms (base + DMC)<br/>- DMC: own rooms + base rooms
            Controller-->>Browser: Return create-room view
            Browser->>View: Render room creation form
            
            User->>View: Fill room form & submit
            View->>Browser: POST /hotels/store-room
            Browser->>Controller: storeroom()
            Controller->>Controller: Validate request
            Controller->>Controller: Check user role (only admin/virtual DMC)
            Controller->>CommonHelper: Generate room_id
            Controller->>Controller: Process images (all_images)
            Controller->>CommonHelper: image_path('file_storage', image)
            Controller->>Controller: Process master_image
            Controller->>Controller: Check for admin base room
            Controller->>Controller: Determine if base room<br/>(Standard or first room)
            Controller->>Controller: Calculate prices:<br/>- Base prices OR<br/>- Base + variant_price
            Controller->>Models: Room::create([<br/>room_type, no_of_room,<br/>weekday_price, weekend_price,<br/>double prices, meals,<br/>dmc_base_room, base_room, etc.])
            Models->>DB: INSERT INTO rooms
            Controller-->>Browser: Redirect to create-room
            Browser->>View: Show success message
        end

        rect rgb(255, 240, 255)
            Note over User,AzureStorage: Edit Room
            User->>Browser: Navigate to /hotels/{id}/edit-room/{room_id}
            Browser->>Controller: GET editroom($encrypted_id)
            Controller->>Controller: Decrypt room_id
            Controller->>Models: Find Room by room_id
            Controller->>Controller: Determine room to edit:<br/>- Admin: original room<br/>- DMC: own room OR original
            Controller->>Controller: Find base room reference
            Controller->>Controller: Calculate commission prices<br/>(if DMC user)
            Controller-->>Browser: Return editroom view
            Browser->>View: Render edit form
            
            User->>View: Update room & submit
            View->>Browser: PUT /hotels/update-room
            Browser->>Controller: updateroom()
            Controller->>Controller: Validate request
            Controller->>Models: Find original room
            alt Admin user
                Controller->>Controller: updateExistingRoom()
                Controller->>Controller: Handle master image<br/>(remove old, upload new)
                Controller->>Controller: Handle gallery images
                Controller->>Controller: Calculate final prices<br/>(base + variant if applicable)
                Controller->>Models: Room::update([...])
            else DMC user
                Controller->>Controller: Check if DMC has own room
                alt DMC has own room
                    Controller->>Controller: updateExistingRoom()
                else First time edit
                    Controller->>Controller: createDmcRoom()
                    Controller->>CommonHelper: Generate new room_id
                    Controller->>Controller: Determine if base room
                    Controller->>Controller: Calculate prices from DMC base
                    Controller->>Models: Room::create([<br/>created_by: userId,<br/>dmc_base_room: 0])
                end
            end
            Models->>DB: UPDATE/INSERT rooms
            Controller-->>Browser: Redirect to create-room
        end

        rect rgb(240, 255, 255)
            Note over User,AzureStorage: Delete Room
            User->>Browser: Click delete room
            Browser->>Controller: DELETE /hotels/delete-room/{id}
            Controller->>Controller: deleteroom($id)
            Controller->>Models: Find Room
            Controller->>Models: Check if used in Bed table
            alt Room used in beds
                Controller-->>Browser: Error: Room in use
            else Room not used
                Controller->>CommonHelper: deleteAzureImage(master_image)
                Controller->>Controller: Delete gallery images
                Controller->>Models: Room::delete()
                Models->>DB: DELETE FROM rooms
                Controller-->>Browser: Redirect with success
            end
        end
    end

    rect rgb(255, 240, 255)
        Note over User,AzureStorage: 💰 PHASE 4: RATE & SEASON MANAGEMENT
        
        rect rgb(240, 255, 240)
            Note over User,AzureStorage: List Rates
            User->>Browser: Navigate to /hotels/{id}/rates
            Browser->>Controller: GET hotelrates($hotelId)
            Controller->>Models: Find Hotel
            Controller->>Controller: Get user role
            alt Admin (role 1)
                Controller->>Models: Query Rate WHERE<br/>hotel_id = ? AND event_type != 'Season'<br/>WITH user relationship
                Controller->>Controller: Add DMC info to each rate
            else DMC Users
                Controller->>Models: Query Rate WHERE<br/>hotel_id = ? AND dmc_id = userId
            end
            Models->>DB: SELECT rates
            Controller-->>Browser: Return rates view
            Browser->>View: Render rates list
        end

        rect rgb(255, 250, 240)
            Note over User,AzureStorage: Create Rate
            User->>View: Fill rate form & submit
            View->>Browser: POST /hotels/store-rates
            Browser->>Controller: storerates()
            Controller->>Controller: Validate request
            Controller->>Controller: Parse date_range<br/>(m/d/Y format)
            Controller->>CommonHelper: Generate rate_id
            Controller->>Controller: Set DMC ID:<br/>- Admin: from request<br/>- DMC: from auth user
            Controller->>Models: Rate::create([<br/>event, event_type,<br/>price, surcharge,<br/>start_date, end_date,<br/>dmc_id, is_active])
            Models->>DB: INSERT INTO rates
            Controller-->>Browser: Redirect back with success
        end

        rect rgb(240, 255, 255)
            Note over User,AzureStorage: Create Season
            User->>Browser: Navigate to /hotels/{id}/season
            Browser->>Controller: GET hotelseason($hotelId)
            Controller->>Models: Find Hotel & Room
            Controller->>Controller: Get user role & filter rates
            Controller-->>Browser: Return season view
            
            User->>View: Fill season form & submit
            View->>Browser: POST /hotels/store-season
            Browser->>Controller: storeseason()
            Controller->>Controller: Validate request
            Controller->>Controller: Parse date_range
            Controller->>Controller: Check for overlapping dates
            Controller->>Models: Rate::where('hotel_id', ?)<br/>WHERE event_type = 'Season'<br/>WHERE dates overlap
            alt Overlapping dates found
                Controller-->>Browser: Error: Date range overlaps
            else No overlap
                Controller->>CommonHelper: Generate rate_id
                Controller->>Controller: Set DMC ID
                Controller->>Models: Rate::create([<br/>event_type: 'Season',<br/>weekday_price, weekend_price,<br/>double prices, dates, dmc_id])
                Models->>DB: INSERT INTO rates
                Controller-->>Browser: Redirect back with success
            end
        end

        rect rgb(255, 240, 255)
            Note over User,AzureStorage: Edit/Update Rate/Season
            User->>Browser: Navigate to edit rate/season
            Browser->>Controller: GET editrate() or editseason()
            Controller->>Models: Find Rate by rate_id
            Controller->>Models: Find Hotel
            Controller-->>Browser: Return edit-rate/edit-season view
            
            User->>View: Update & submit
            View->>Browser: PUT /hotels/update-rates or update-season
            Browser->>Controller: updaterates() or updateseason()
            Controller->>Controller: Parse date_range
            Controller->>Models: Rate::update([<br/>event, price, dates,<br/>is_active])
            Models->>DB: UPDATE rates
            Controller-->>Browser: Redirect back with success
        end
    end

    rect rgb(240, 248, 255)
        Note over User,AzureStorage: 🛏️ PHASE 5: BED MANAGEMENT
        
        rect rgb(255, 250, 240)
            Note over User,AzureStorage: List Beds
            User->>Browser: Navigate to /hotels/{id}/beds
            Browser->>Controller: GET hotelbeds($id)
            Controller->>Models: Find Hotel
            Controller->>Models: Query Room WHERE hotel_id
            Controller->>Controller: Get user role
            alt Admin
                Controller->>Models: Query Bed WITH room & user<br/>WHERE hotel_id = ?
                Controller->>Controller: Add DMC info to beds
            else DMC Users
                Controller->>Models: Query Bed WHERE dmc_id = userId
            end
            Controller->>Models: Query BedMaster WHERE hotel_id
            Controller-->>Browser: Return beds view
            Browser->>View: Render beds list
        end

        rect rgb(240, 255, 240)
            Note over User,AzureStorage: Create Bed
            User->>View: Fill bed form & submit
            View->>Browser: POST /hotels/store-beds
            Browser->>Controller: storebeds()
            Controller->>Controller: Validate request
            Controller->>Controller: Set DMC ID based on role
            Controller->>Models: Find Room by room_id
            Controller->>Controller: Check room availability<br/>(no_of_room vs beds sum)
            alt Room capacity exceeded
                Controller-->>Browser: Error: Room capacity filled
            else Room available
                Controller->>CommonHelper: Generate bed_id
                Controller->>Models: Find BedMaster by bedId
                Controller->>Controller: Calculate max_occupancy<br/>(king*2 + queen*2 + twin*2 + single + bunk*2)
                Controller->>Models: Bed::create([<br/>room_id, bed_master_id,<br/>no_of_rooms, max_occupancy,<br/>adult_count, child_count,<br/>extra_bed, baby_cot,<br/>dmc_id, is_active])
                Models->>DB: INSERT INTO beds
                Controller->>Models: Hotel::update(['is_complete' => 1])
                Controller-->>Browser: Redirect back with success
            end
        end

        rect rgb(255, 240, 255)
            Note over User,AzureStorage: Edit/Delete Bed
            User->>Browser: Navigate to edit bed
            Browser->>Controller: GET editbed($id, $hotelId)
            Controller->>Models: Find Hotel, Bed, Room
            Controller->>Models: Query BedMaster, Room
            Controller-->>Browser: Return edit-beds view
            
            User->>View: Update & submit
            View->>Browser: PUT /hotels/update-bed
            Browser->>Controller: updatebed()
            Controller->>Controller: Validate request
            Controller->>Models: Find Bed & Room
            Controller->>Controller: Check room capacity
            Controller->>Models: Bed::update([...])
            Models->>DB: UPDATE beds
            Controller-->>Browser: Redirect back with success
            
            User->>Browser: Click delete bed
            Browser->>Controller: DELETE /hotels/delete-bed/{id}
            Controller->>Controller: deletebed($hotelId, $bedId)
            Controller->>Models: Bed::delete()
            Models->>DB: DELETE FROM beds
            Controller-->>Browser: Redirect back with success
        end
    end

    rect rgb(255, 250, 240)
        Note over User,AzureStorage: 📅 PHASE 6: CALENDAR & POLICY MANAGEMENT
        
        rect rgb(240, 255, 240)
            Note over User,AzureStorage: Hotel Calendar
            User->>Browser: Navigate to /hotels/{id}/calendar/{year?}
            Browser->>Controller: GET calender($id, $year)
            Controller->>Models: Find Hotel with category & rooms
            Controller->>Controller: Apply DMC filtering
            Controller->>Controller: Get weekend_days from hotel
            Controller->>Models: Query Rate WHERE hotel_id<br/>AND is_active = 1<br/>Filter by DMC if not admin
            Controller->>Controller: Generate base prices for year<br/>(weekday vs weekend)
            Controller->>Controller: Apply rate overlays:<br/>- Blackout Date: fixed price<br/>- Fair Date: base + surcharge<br/>- Season: weekday/weekend prices
            Controller->>Controller: Order rates by priority:<br/>1. Blackout, 2. Fair, 3. Season, 4. Other
            Controller-->>Browser: Return calender view
            Browser->>View: Render calendar with rate colors
        end

        rect rgb(255, 240, 255)
            Note over User,AzureStorage: Policy Management
            User->>Browser: Navigate to /hotels/{id}/policy
            Browser->>Controller: GET policy($id)
            Controller->>Models: Find Hotel
            Controller->>Models: Query HotelPolicy WHERE hotel_id
            Controller->>Controller: Decode cancellation_json
            Controller-->>Browser: Return policy view
            
            User->>View: Update property policy & submit
            View->>Browser: POST /hotels/update-policy
            Browser->>Controller: updatepolicy()
            Controller->>Controller: Handle policy file upload
            Controller->>CommonHelper: image_path('file_storage', file)
            Controller->>Models: HotelPolicy::updateOrCreate([<br/>name, policy, extras,<br/>property, file])
            Models->>DB: UPDATE/INSERT hotel_policies
            Controller-->>Browser: Redirect to cancellation tab
            
            User->>View: Update cancellation policy
            View->>Browser: POST /hotels/update-cancellation-policy
            Browser->>Controller: updatecancellationPolicy()
            Controller->>Controller: Build cancellation_data array<br/>(duration, price, type)
            Controller->>Controller: Handle cancellation PDF upload
            Controller->>Models: Hotel::update([<br/>cancellation_type,<br/>cancellation_data: JSON,<br/>cancellation_pdf, policy])
            Controller-->>Browser: Redirect to refund tab
            
            User->>View: Update refund/child/pet/terms policies
            View->>Browser: POST update policies
            Browser->>Controller: updateRefundPolicy() /<br/>updateChildPolicy() /<br/>updatePetPolicy() /<br/>updateTermsPolicy()
            Controller->>Controller: Handle PDF uploads
            Controller->>Models: Hotel::update([<br/>refundpolicy, childpolicy,<br/>petpolicy, termspolicy,<br/>respective PDFs])
            Models->>DB: UPDATE hotels
            Controller-->>Browser: Redirect with success
        end
    end

    rect rgb(240, 255, 240)
        Note over User,AzureStorage: 🏢 PHASE 7: FACILITY & CONFERENCE MANAGEMENT
        
        rect rgb(255, 250, 240)
            Note over User,AzureStorage: Facility Management
            User->>Browser: Navigate to /hotels/{id}/facility
            Browser->>Controller: GET hotelfacility($id)
            Controller->>Models: Find Hotel
            Controller->>Controller: Decode facilities JSON
            Controller->>Models: Query Facility WHERE facilityId IN (...)
            Controller->>Models: Query Category::where('status', 1)
            Controller->>Controller: Decode facilities_images JSON
            Controller-->>Browser: Return facility view
            Browser->>View: Render facility selection form
            
            User->>View: Select facilities & upload images
            View->>Browser: POST /hotels/store-facility
            Browser->>Controller: storeFacility()
            Controller->>Controller: Validate request
            Controller->>Controller: Decode selected_facilities JSON
            Controller->>Controller: Process facility images<br/>(associate with facilityId)
            loop For each uploaded image
                Controller->>CommonHelper: image_path('file_storage', image)
                CommonHelper->>AzureStorage: Upload image
            end
            Controller->>Models: Hotel::update([<br/>facilities: JSON,<br/>facilities_images: JSON])
            Models->>DB: UPDATE hotels
            Controller-->>Browser: Redirect back with success
            
            User->>View: Edit specific facility
            View->>Browser: GET /hotels/{id}/edit-facility/{facilityId}
            Browser->>Controller: GET editfacility($facilityId, $hotelId)
            Controller->>Models: Find Hotel
            Controller->>Controller: Get images for facility
            Controller-->>Browser: Return editfacility view
            
            User->>View: Update facility images
            View->>Browser: POST /hotels/update-facility
            Browser->>Controller: updateFacility()
            Controller->>Controller: Handle existing & new images
            Controller->>Models: Hotel::update([facilities_images: JSON])
            Controller-->>Browser: Redirect back
            
            User->>Browser: Delete facility
            Browser->>Controller: DELETE /hotels/{id}/delete-facility/{facilityId}
            Controller->>Controller: destroyfacility($hotelId, $facilityId)
            Controller->>Controller: Remove from facilities array
            Controller->>Controller: Delete facility images from Azure
            Controller->>Models: Hotel::update([facilities, facilities_images])
            Controller-->>Browser: Redirect back
        end

        rect rgb(240, 255, 255)
            Note over User,AzureStorage: Conference Room
            User->>Browser: Navigate to /hotels/{id}/conference
            Browser->>Controller: GET conference($id)
            Controller->>Models: Find Hotel
            Controller->>Controller: Get conference_room data
            Controller-->>Browser: Return conference view
            
            User->>View: Update conference & submit
            View->>Browser: POST /hotels/update-conference
            Browser->>Controller: updateConference()
            Controller->>Models: Hotel::update([conference_room])
            Models->>DB: UPDATE hotels
            Controller-->>Browser: Redirect back with success
        end
    end

    rect rgb(255, 240, 255)
        Note over User,AzureStorage: 🔄 PHASE 8: DMC HOTEL SELECTION & BULK OPERATIONS
        
        rect rgb(240, 255, 240)
            Note over User,AzureStorage: DMC Hotel Selection
            User->>Browser: Navigate to /dmc/hotels
            Browser->>Controller: GET dmcHotelsSelection()
            Controller->>Controller: Check user role (11, 35, 77, etc.)
            Controller->>Controller: Resolve DMC ID from hierarchy
            Controller->>Models: Query Hotel WHERE status = 1
            Controller->>Controller: Filter selected vs available hotels<br/>using hasSelectedByDmc()
            Controller-->>Browser: Return hotels selection view
            Browser->>View: Render hotel selection interface
            
            User->>View: Select/remove hotels
            View->>Browser: POST /dmc/update-hotels or<br/>POST /dmc/select-hotel or<br/>POST /dmc/remove-hotel
            Browser->>Controller: updateDmcHotels() or<br/>selectHotel() or removeHotel()
            Controller->>Controller: Resolve DMC ID
            alt Bulk update
                Controller->>Models: Reset all hotels for DMC<br/>(removeDmcId for all)
                Controller->>Models: Add DMC to selected hotels<br/>(addDmcId for selected)
            else Individual select/remove
                Controller->>Models: Find Hotel
                Controller->>Models: hotel->addDmcId(dmc_id) or<br/>hotel->removeDmcId(dmc_id)
            end
            Models->>DB: UPDATE hotels SET dmc_id = JSON
            Controller-->>Browser: JSON response or redirect
        end

        rect rgb(255, 250, 240)
            Note over User,AzureStorage: Room Import/Export
            User->>Browser: Navigate to /hotels/{id}/rooms-import
            Browser->>Controller: GET roomsImportView($hotel_id)
            Controller->>Controller: Check user_type = 2 (DMC)
            Controller->>Models: Find Hotel
            Controller->>Controller: Verify DMC access
            Controller->>Models: Query UploadHistory WHERE<br/>upload_type = 'rooms'<br/>AND hotel_id = ?
            Controller-->>Browser: Return rooms-import view
            
            User->>View: Download template
            View->>Browser: GET /hotels/{id}/rooms-download-template
            Browser->>Controller: roomsDownloadTemplate($hotel_id)
            Controller->>Models: Query Room WHERE hotel_id<br/>AND created_by = 1 (admin base)
            Controller->>Controller: Build CSV data
            Controller->>Controller: Generate CSV file
            Controller-->>Browser: Download CSV template
            
            User->>View: Upload CSV file
            View->>Browser: POST /hotels/rooms-import
            Browser->>Controller: roomsImport()
            Controller->>Controller: Validate file (CSV, max 10MB)
            Controller->>Controller: Check duplicate upload (cache)
            Controller->>Controller: Process CSV import
            Controller->>Models: RoomsImport::import()
            Controller->>Models: UploadHistory::createRecord()
            Controller-->>Browser: Redirect with import results
        end
    end
```

## Key Features Covered

### 🔐 **Authentication & Authorization**
- Role-based access control (Admin, DMC, Master DMC, Product Head, etc.)
- DMC ID resolution from user hierarchy
- Permission checks for each operation
- DMC filtering for hotels, rooms, rates, beds

### 🏨 **Hotel Management**
1. **List Hotels**: Role-based filtering with DMC hierarchy
2. **Create Hotel**: Image uploads to Azure, unique ID generation
3. **Edit Hotel**: Image management (add/remove), JSON field handling
4. **Delete Hotel**: DMC assignment check, Azure image cleanup

### 📞 **Contact & Port Management**
- Contact details update (owner, directors, managers)
- Port of entry/exit/others management with coordinates
- JSON data processing for port arrays

### 🛏️ **Room Management**
- **Base Room System**: Admin creates base rooms, DMC creates variants
- **Price Calculation**: Base price + variant price logic
- **Room Types**: Standard, Deluxe, Suite, etc.
- **Meal Integration**: Breakfast, lunch, dinner options
- **Image Management**: Master image + gallery images

### 💰 **Rate & Season Management**
- **Rate Types**: Blackout Date, Fair Date, Season
- **Date Overlap Detection**: Prevents conflicting seasons
- **DMC-Specific Rates**: Each DMC has own rates
- **Calendar Integration**: Visual rate display by date

### 🛏️ **Bed Management**
- **Bed Types**: King, Queen, Twin, Single, Bunk
- **Occupancy Calculation**: Max occupancy based on bed types
- **Room Capacity Check**: Prevents overbooking
- **Extra Bed & Baby Cot**: Optional add-ons with pricing

### 📅 **Calendar & Policy Management**
- **Dynamic Calendar**: Yearly view with rate overlays
- **Policy Types**: Property, Cancellation, Refund, Child, Pet, Terms
- **PDF Uploads**: Policy documents stored in Azure
- **JSON Policies**: Structured cancellation/refund rules

### 🏢 **Facility & Conference**
- **Facility Selection**: Multi-select with categories
- **Facility Images**: Per-facility image galleries
- **Conference Rooms**: Hotel conference room details

### 🔄 **DMC Operations**
- **Hotel Selection**: DMC selects available hotels
- **Bulk Operations**: Select/remove multiple hotels
- **Individual Operations**: AJAX-based single hotel selection

### 📊 **Bulk Import/Export**
- **Room Import**: CSV upload for bulk room updates
- **Template Download**: Pre-filled CSV with existing data
- **Upload History**: Track import operations
- **Duplicate Prevention**: Cache-based upload throttling

### 🎨 **Visual Organization**
- Color-coded phases for easy navigation
- Clear separation of management areas
- Comprehensive flow coverage
- Detailed calculation steps
