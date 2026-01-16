# Bookings Controller - Complete Sequence Diagram

```mermaid
sequenceDiagram
    autonumber
    participant User
    participant Browser
    participant View as Blade View
    participant Controller as BookingsController
    participant Models as Models (Tour, Order, Enquiry, etc.)
    participant DB as Database
    participant CommonHelper
    participant Dompdf

    rect rgb(240, 248, 255)
        Note over User,Dompdf: 📋 PHASE 1: DISPLAY BOOKING LISTS
        
        rect rgb(255, 250, 240)
            Note over User,Dompdf: New Enquiries (tour_status = 'New Enquiry')
            User->>Browser: Navigate to /bookings/new-enquiries
            Browser->>Controller: GET newEnquiries()
            Controller->>Controller: Get user role & DMC ID
            Controller->>Controller: Resolve DMC ID from user hierarchy<br/>(role 11, 33, 34, 37, 38, etc.)
            Controller->>Models: Query Tour WHERE tour_status = 'New Enquiry'<br/>AND dmc_id = ?
            Models->>DB: SELECT tours with LEFT JOIN agents
            DB-->>Models: Tours list with agent info
            Controller->>Models: Query Enquiry WHERE dmcId = ?
            Models->>DB: SELECT enquiry_comments
            DB-->>Models: Enquiry comments
            Controller->>Controller: getFilteredAgents()<br/>(Filter agents by DMC)
            Controller->>Models: Query Country tax_percentage
            Models-->>Controller: Country tax data
            Controller-->>Browser: Return new-enquiries view
            Browser->>View: Render tours table with:<br/>- Tour details<br/>- Agent info<br/>- Service counts<br/>- Payment calculations<br/>- Negotiation buttons
            View->>View: Calculate tourTotalPrice<br/>(base + transfer + guide for each service)
            View->>View: Calculate discount & settlement amount
            View->>View: Check negotiation status<br/>(can agent negotiate?)
        end

        rect rgb(240, 255, 240)
            Note over User,Dompdf: Follow Ups (tour_status = 'Prospect' or 'Tentative')
            User->>Browser: Navigate to /bookings/follow-ups
            Browser->>Controller: GET followUps()
            Controller->>Controller: Get user role & DMC ID
            Controller->>Models: Query Tour WHERE tour_status IN ('Prospect', 'Tentative')
            Controller->>Models: LEFT JOIN enquiry_comments<br/>(latest enquiry per tour)
            Models->>DB: SELECT tours with enquiry data
            DB-->>Models: Tours with latest enquiry comments
            Controller->>Models: Query Country tax_percentage
            Controller-->>Browser: Return follow-ups view
            Browser->>View: Render tours with enquiry details
        end

        rect rgb(255, 240, 255)
            Note over User,Dompdf: Confirmed Bookings (tour_status = 'Confirmed')
            User->>Browser: Navigate to /bookings/confirmed
            Browser->>Controller: GET confirmedBookings()
            Controller->>Controller: Get user role & DMC ID
            Controller->>Models: Query Tour WHERE tour_status = 'Confirmed'<br/>WITH booking relationship<br/>(bookingType = 'booking')
            Models->>DB: SELECT tours with bookings
            DB-->>Models: Tours with confirmed bookings
            Controller-->>Browser: Return confirmed view
            Browser->>View: Render confirmed tours with payment details
        end

        rect rgb(240, 255, 255)
            Note over User,Dompdf: Definite Bookings (tour_status = 'Definite')
            User->>Browser: Navigate to /bookings/definite
            Browser->>Controller: GET definiteBookings()
            Controller->>Controller: Get user role & DMC ID
            Controller->>Models: Query Tour WHERE tour_status = 'Definite'<br/>WITH booking & agent relationships
            Models->>DB: SELECT tours
            DB-->>Models: Definite tours
            Controller->>Models: Query Country tax_percentage
            Controller-->>Browser: Return definite view
            Browser->>View: Render definite tours
        end

        rect rgb(255, 255, 240)
            Note over User,Dompdf: Actual Bookings (tour_status = 'Actual')
            User->>Browser: Navigate to /bookings/actual
            Browser->>Controller: GET actualBookings()
            Controller->>Controller: Get user role & DMC ID
            Controller->>Models: Query Tour WHERE tour_status = 'Actual'
            Models->>DB: SELECT tours
            DB-->>Models: Actual tours
            Controller->>Controller: Parse payment_details JSON<br/>for each tour
            Controller->>Models: Query Country tax_percentage
            Controller-->>Browser: Return actual view
            Browser->>View: Render actual tours with parsed payment details
        end

        rect rgb(255, 240, 240)
            Note over User,Dompdf: Cancelled & Refund Bookings
            User->>Browser: Navigate to /bookings/cancelled or /bookings/refunds
            Browser->>Controller: GET cancelledBookings() or refunds()
            Controller->>Controller: Get user role & DMC ID
            Controller->>Models: Query Tour WHERE tour_status LIKE 'Cancel%'<br/>OR tour_status IN ('Refund - Pending', 'Refunded')
            Models->>DB: SELECT tours
            DB-->>Models: Cancelled/refund tours
            Controller->>Models: Query Country tax_percentage
            Controller-->>Browser: Return cancelled/refunds view
            Browser->>View: Render cancelled/refund tours
        end
    end

    rect rgb(255, 250, 240)
        Note over User,Dompdf: 💰 PHASE 2: AGENT NEGOTIATION FLOW
        
        User->>View: Click "Negotiate by Agent" button
        View->>View: openAgentNegotiationModal(button)
        View->>View: Extract tour data:<br/>- tour_id<br/>- display_id<br/>- actual_amount<br/>- last_negotiated_amount<br/>- last_comment
        View->>View: Calculate max allowed amount<br/>(last amount or current amount)
        View->>View: Populate modal fields
        View->>View: Show modal
        
        alt Action: Negotiate
            User->>View: Enter amount & remarks, click "Negotiate"
            View->>View: submitAgentNegotiation('negotiate')
            View->>View: Validate amount (required, > 0)
            View->>Browser: POST /tours/agent-negotiation<br/>{tour_id, action: 'negotiate', amount, comment}
            Browser->>Controller: agentNegotiation()
            Controller->>Controller: Validate request data
            Controller->>Models: Find Tour by tour_id
            Controller->>Models: Get latest Enquiry
            Controller->>Models: Get active Enquiry (status = 1)
            Controller->>Controller: Calculate actual_amount<br/>(if not provided)
            Controller->>Controller: calculateOrdersTotalAmount(tour_id)
            Controller->>Models: Query all Orders for tour
            Models->>DB: SELECT orders WHERE tour_id = ?
            DB-->>Models: Orders list
            Controller->>Controller: extractOrderPayloadTotal()<br/>for each order (recursive)
            Controller->>Controller: Sum all order totals
            Controller->>Controller: Validate: amountOffered ≤ actualAmount
            alt Validation fails
                Controller-->>Browser: Redirect with error
            else Validation passes
                Controller->>CommonHelper: Generate new enquiry_id
                Controller->>Models: Create Enquiry record:<br/>- tour_id<br/>- status = 1 (active)<br/>- sender_type = 'agent'<br/>- receiver_type = 'OM'<br/>- amount (negotiated)<br/>- actual_amount<br/>- comment
                Models->>DB: INSERT INTO enquiry_comments
                DB-->>Models: Enquiry created
                Controller->>Models: Update previous active enquiry<br/>(status = 0)
                Models->>DB: UPDATE enquiry_comments SET status = 0
                Controller-->>Browser: Redirect with success message
                Browser->>View: Show success notification
            end
            
        else Action: Cancel Tour
            User->>View: Click "Cancel Tour" button
            View->>View: submitAgentNegotiation('cancel')
            View->>Browser: POST /tours/agent-negotiation<br/>{tour_id, action: 'cancel'}
            Browser->>Controller: agentNegotiation()
            Controller->>Models: Get active Enquiry
            Controller->>Models: Update active enquiry status = 3
            Models->>DB: UPDATE enquiry_comments SET status = 3
            Controller->>Controller: Determine new tour status
            alt Tour status = 'Definite'
                Controller->>Models: Update tour_status = 'Refund - Pending'
            else Other status
                Controller->>Models: Update tour_status = 'Cancel - {current_status}'
            end
            Models->>DB: UPDATE tours SET tour_status = ?
            Controller-->>Browser: Redirect with success message
            
        else Action: Confirm Tour
            User->>View: Click "Confirm" button
            View->>View: submitAgentNegotiation('confirm')
            View->>Browser: POST /tours/agent-negotiation<br/>{tour_id, action: 'confirm'}
            Browser->>Controller: agentNegotiation()
            Controller->>Models: Get active Enquiry
            Controller->>Models: Update active enquiry status = 2
            Models->>DB: UPDATE enquiry_comments SET status = 2
            Controller->>Models: Update all Orders<br/>SET bookingType = 'booking'
            Models->>DB: UPDATE orders SET bookingType = 'booking'
            Controller->>Models: Update tour_status = 'Confirmed'
            Models->>DB: UPDATE tours SET tour_status = 'Confirmed'
            alt Multi-enquiry tour
                Controller->>Models: Find EnquiryForm by multi_enq_id
                Controller->>Models: Cancel other enquiries<br/>SET status = 'cancelled'
                Controller->>Models: Soft delete other tours<br/>SET deleted_at = now()
                Models->>DB: UPDATE tours SET deleted_at = ?
            end
            Controller-->>Browser: Redirect with success message
        end
    end

    rect rgb(240, 255, 240)
        Note over User,Dompdf: 💳 PHASE 3: PAYMENT FUNCTIONALITY
        
        User->>View: Click "Add Payment" button
        View->>View: openAddPaymentModal(tour)
        View->>View: Calculate tourTotalPrice<br/>(sum all orders with transfer + guide)
        View->>View: Calculate payment details:<br/>- Subtotal<br/>- Discount<br/>- Tax<br/>- Total Amount<br/>- Paid Amount<br/>- Balance
        View->>View: Show payment modal with calculated values
        
        User->>View: Enter payment details & submit
        View->>Browser: POST /bookings/add-payment (if exists)
        Browser->>Controller: addPayment() or similar
        Controller->>Controller: Validate payment data
        Controller->>Models: Update tour payment_details JSON
        Models->>DB: UPDATE tours SET payment_details = ?
        Controller-->>Browser: Redirect with success
    end

    rect rgb(255, 240, 255)
        Note over User,Dompdf: 📊 PHASE 4: PRICE CALCULATION & DISPLAY
        
        rect rgb(240, 255, 255)
            Note over User,Dompdf: Tour Total Price Calculation
            View->>View: Calculate tourTotalPrice for each tour
            loop For each booking in tour->booking
                View->>View: Get booking data (JSON)
                View->>View: Extract totalPrice from booking
                alt Attraction Service
                    View->>View: Get transfer_options.cost
                    View->>View: Get guide_options.total_price
                    View->>View: Calculate: basePrice + transferPrice + guidePrice
                else Restaurant Service
                    View->>View: Get transfer_options.cost
                    View->>View: Calculate: mealPrice + transferPrice
                else Hotel Service
                    View->>View: Calculate: roomPrice × rooms × meals
                else Other Services
                    View->>View: Use totalPrice as-is
                end
                View->>View: Add to tourTotalPrice
            end
            View->>View: Display total in table
        end

        rect rgb(255, 255, 240)
            Note over User,Dompdf: Discount & Settlement Calculation
            View->>View: Get first enquiry actual_amount
            View->>View: Calculate discount = actual_amount - tourTotalPrice
            View->>View: Calculate settlementAmount = tourTotalPrice - discount
            View->>View: Display in negotiation modal
        end
    end

    rect rgb(240, 248, 255)
        Note over User,Dompdf: 🗑️ PHASE 5: TOUR CANCELLATION
        
        User->>View: Click "Cancel Tour" button
        View->>View: cancelTour(encryptedTourId, displayId)
        View->>View: Show confirmation dialog
        alt User confirms
            View->>Browser: POST /bookings/cancel-tour/{encryptedId}
            Browser->>Controller: cancelTour()
            Controller->>Controller: Decrypt tour_id
            Controller->>Models: Find Tour by tour_id
            Controller->>Controller: Check if already cancelled
            alt Tour status = 'Definite'
                Controller->>Models: Update tour_status = 'Refund - Pending'
            else Other status
                Controller->>Models: Update tour_status = 'Cancel-{current_status}'
            end
            Models->>DB: UPDATE tours SET tour_status = ?
            Controller-->>Browser: JSON {success: true, new_status}
            Browser->>View: Show success message
            Browser->>View: Reload page or update UI
        end
    end

    rect rgb(255, 250, 240)
        Note over User,Dompdf: 💵 PHASE 6: REFUND PROCESSING
        
        User->>Browser: Navigate to /bookings/refunds
        Browser->>Controller: GET refunds()
        Controller->>Controller: Get user role & DMC ID
        Controller->>Models: Query Tour WHERE tour_status IN<br/>('Refund - Pending', 'Refunded')
        Models->>DB: SELECT tours
        DB-->>Models: Refund tours
        Controller-->>Browser: Return refunds view
        
        User->>View: Click "Process Refund" button
        View->>Browser: POST /bookings/process-refund<br/>{tour_id}
        Browser->>Controller: processRefund()
        Controller->>Controller: Validate tour_id
        Controller->>Models: Find Tour WHERE tour_status = 'Refund - Pending'
        Controller->>Models: Update tour_status = 'Refunded'
        Models->>DB: UPDATE tours SET tour_status = 'Refunded'
        Controller-->>Browser: JSON {success: true, new_status: 'Refunded'}
        Browser->>View: Show success message
        Browser->>View: Update UI
    end

    rect rgb(240, 255, 240)
        Note over User,Dompdf: 📄 PHASE 7: PDF EXPORT
        
        User->>View: Click "Export PDF" button
        View->>Browser: GET /bookings/export-tour-pdf/{tourId}
        Browser->>Controller: exportTourPDF()
        Controller->>Models: Find Tour with agent info
        Controller->>Controller: Parse payment_details JSON
        alt POST request with HTML content
            Controller->>Controller: Use provided HTML content
            Controller->>Dompdf: Load HTML
            Controller->>Dompdf: Set paper size (A4, portrait)
            Controller->>Dompdf: Render PDF
            Dompdf-->>Controller: PDF binary data
            Controller-->>Browser: PDF file download
        else GET request
            Controller->>View: Render tour-pdf view
            View-->>Controller: HTML content
            Controller->>Dompdf: Load HTML
            Controller->>Dompdf: Render PDF
            Dompdf-->>Controller: PDF binary data
            Controller-->>Browser: PDF file download
        end
    end

    rect rgb(255, 240, 255)
        Note over User,Dompdf: 👁️ PHASE 8: VIEW TOUR DETAILS
        
        User->>Browser: Navigate to /bookings/view-tour/{encryptedId}
        Browser->>Controller: viewTour()
        Controller->>Controller: Decrypt tour_id
        Controller->>Models: Find Tour by tour_id
        Controller->>Controller: Parse payment_details JSON
        Controller-->>Browser: Return view-tour view
        Browser->>View: Render tour details with:<br/>- Tour information<br/>- Agent details<br/>- Service breakdown<br/>- Payment history<br/>- Booking details
    end

    rect rgb(240, 240, 255)
        Note over User,Dompdf: 📈 PHASE 9: BOOKING STATISTICS
        
        User->>Browser: GET /bookings/stats (AJAX)
        Browser->>Controller: getBookingStats()
        Controller->>Models: Count tours by status:<br/>- New Enquiry<br/>- Prospect<br/>- Tentative<br/>- Confirmed<br/>- Definite<br/>- Actual<br/>- Cancelled
        Models->>DB: SELECT COUNT(*) GROUP BY tour_status
        DB-->>Models: Statistics
        Models-->>Controller: Stats array
        Controller-->>Browser: JSON {stats: {...}}
        Browser->>View: Update dashboard statistics
    end

    rect rgb(255, 255, 240)
        Note over User,Dompdf: 🔍 PHASE 10: SERVICE MODAL DISPLAYS
        
        User->>View: Click service icon (hotel, attraction, etc.)
        View->>View: Open service modal
        View->>View: Parse order data (JSON)
        View->>View: Calculate service prices:<br/>- Hotel: room × meals<br/>- Attraction: base + transfer + guide<br/>- Restaurant: meal + transfer<br/>- Guide: base + surcharge<br/>- Transport: zone/city pricing
        View->>View: Display service details in modal
        View->>View: Show total price in header
    end
```

## Key Features Covered

### 🔐 **Authentication & Authorization**
- DMC ID resolution from user hierarchy (roles 11, 33, 34, 37, 38, etc.)
- Role-based tour filtering
- Agent filtering by DMC

### 📊 **Booking Status Management**
1. **New Enquiries**: `tour_status = 'New Enquiry'`
2. **Follow Ups**: `tour_status IN ('Prospect', 'Tentative')`
3. **Confirmed**: `tour_status = 'Confirmed'`
4. **Definite**: `tour_status = 'Definite'`
5. **Actual**: `tour_status = 'Actual'`
6. **Cancelled**: `tour_status LIKE 'Cancel%'`
7. **Refunds**: `tour_status IN ('Refund - Pending', 'Refunded')`

### 💰 **Negotiation Flow**
- **Negotiate**: Agent submits new offer → Creates Enquiry record → Deactivates previous enquiry
- **Cancel**: Updates enquiry status → Changes tour status (Refund - Pending if Definite)
- **Confirm**: Updates enquiry status → Changes all orders to 'booking' → Updates tour to 'Confirmed' → Handles multi-enquiry cleanup

### 💳 **Price Calculations**
- **Tour Total**: Sum of all orders (base + transfer + guide for attractions, meal + transfer for restaurants)
- **Actual Amount**: Calculated from order payloads (recursive extraction)
- **Discount**: Difference between actual and current total
- **Settlement Amount**: Total - discount

### 📝 **Order Data Processing**
- Recursive price extraction from nested JSON structures
- Priority keys: `totalPrice`, `total_price`, `price`, `amount`
- Handles both array and object payloads

### 🗄️ **Database Operations**
- Tour queries with LEFT JOIN agents
- Enquiry comments with latest per tour
- Order queries filtered by bookingType
- Multi-enquiry handling (cancelling duplicates)

### 📧 **Notifications & Feedback**
- Success/error messages via session flash
- SweetAlert notifications
- Real-time UI updates

### 🎨 **Visual Organization**
- Color-coded phases for easy navigation
- Clear separation of booking statuses
- Comprehensive flow coverage
- Detailed calculation steps
