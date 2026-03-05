<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\CreditNote;
use App\Models\Tour;
use App\Models\Order;
use App\Models\User;
use App\Models\Enquiry;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * Generate Proforma Invoice at TENTATIVE stage
     * 
     * @param int $tourId
     * @param array $data
     * @return Invoice
     */
    public function generateProformaInvoice($tourId, $data = [])
    {
        DB::beginTransaction();
        try {
            $tour = Tour::where('tour_id', $tourId)->first();
            if (!$tour) {
                throw new \Exception("Tour not found");
            }

            // Check if tour status is TENTATIVE or CONFIRMED (for directly confirmed tours)
            $allowedStatuses = ['Tentative', 'Confirmed'];
            if (!in_array($tour->tour_status, $allowedStatuses)) {
                throw new \Exception("Proforma invoice can only be generated for tours in Tentative or Confirmed status. Current status: " . ($tour->tour_status ?? 'N/A'));
            }

            // Check if any invoice already exists (proforma or final)
            $existingInvoice = Invoice::where('tour_id', $tourId)
                ->whereNull('deleted_at')
                ->first();

            if ($existingInvoice) {
                // If it's a proforma, return it for editing
                if ($existingInvoice->invoice_type === 'proforma') {
                    return $existingInvoice;
                }
                // If it's a final invoice, cannot generate proforma
                throw new \Exception("A final invoice already exists for this tour. Cannot generate proforma invoice.");
            }

            // Generate invoice ID
            $lastInvoice = Invoice::withTrashed()->orderBy('invoice_id', 'desc')->first();
            $maxInvoiceId = $lastInvoice->invoice_id ?? 0;
            $invoiceId = CommonHelper::createId($maxInvoiceId);

            // Generate proforma number
            $proformaNumber = 'PRO-' . str_pad($invoiceId, 6, '0', STR_PAD_LEFT);

            // Prepare invoice data
            $invoiceData = $this->prepareInvoiceData($tour, $data, 'proforma');
            $invoiceData['invoice_id'] = $invoiceId;
            $invoiceData['proforma_number'] = $proformaNumber;
            $invoiceData['invoice_date'] = now()->toDateString();
            $invoiceData['validity_date'] = $data['validity_date'] ?? now()->addDays(30)->toDateString();

            // Create invoice
            $invoice = Invoice::create($invoiceData);

            // Create invoice items
            if (isset($data['items']) && is_array($data['items'])) {
                $this->createInvoiceItems($invoice, $data['items']);
            } else {
                // Auto-generate items from tour orders
                $this->generateItemsFromTour($invoice, $tour);
            }

            // Calculate totals
            $this->calculateInvoiceTotals($invoice);

            DB::commit();
            return $invoice->fresh(['items']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating proforma invoice: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate Final Invoice directly from Tour (when no proforma exists)
     * Allowed statuses: Confirmed, Definite, Actual
     *
     * @param int $tourId
     * @param array $data
     * @return Invoice
     */
    public function generateFinalInvoiceForTour($tourId, $data = [])
    {
        DB::beginTransaction();
        try {
            $tour = Tour::where('tour_id', $tourId)->first();
            if (!$tour) {
                throw new \Exception("Tour not found");
            }

            $allowedStatuses = ['Confirmed', 'Definite', 'Actual'];
            if (!in_array($tour->tour_status, $allowedStatuses)) {
                throw new \Exception("Final invoice can only be generated for tours in Confirmed, Definite or Actual status. Current status: " . ($tour->tour_status ?? 'N/A'));
            }

            // If final invoice already exists, return it
            $existingFinal = Invoice::where('tour_id', $tourId)
                ->where('invoice_type', 'final')
                ->whereNull('deleted_at')
                ->first();
            if ($existingFinal) {
                DB::commit();
                return $existingFinal;
            }

            // If a proforma exists, convert it (conversion will include GST and delete proforma)
            $existingProforma = Invoice::where('tour_id', $tourId)
                ->where('invoice_type', 'proforma')
                ->whereNull('deleted_at')
                ->first();
            if ($existingProforma) {
                $final = $this->convertToFinalInvoice($existingProforma->invoice_id);
                DB::commit();
                return $final;
            }

            // Generate new invoice ID for final invoice
            $lastInvoice = Invoice::withTrashed()->orderBy('invoice_id', 'desc')->first();
            $maxInvoiceId = $lastInvoice->invoice_id ?? 0;
            $finalInvoiceId = CommonHelper::createId($maxInvoiceId);

            // Generate final invoice number
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($finalInvoiceId, 6, '0', STR_PAD_LEFT);

            // Refresh tour data and regenerate invoice data from latest tour/orders
            $tour = Tour::where('tour_id', $tourId)->first();
            $finalInvoiceData = $this->prepareInvoiceData($tour, $data, 'final');
            $finalInvoiceData['invoice_id'] = $finalInvoiceId;
            $finalInvoiceData['invoice_type'] = 'final';
            $finalInvoiceData['invoice_number'] = $invoiceNumber;
            $finalInvoiceData['invoice_date'] = now()->toDateString();
            $finalInvoiceData['due_date'] = $data['due_date'] ?? now()->addDays(30)->toDateString();
            $finalInvoiceData['status'] = 'issued';

            // Create final invoice
            $finalInvoice = Invoice::create($finalInvoiceData);

            // Generate items from latest orders (to get updated services)
            $this->generateItemsFromTour($finalInvoice, $tour);

            // Calculate totals with GST when applicable
            $this->calculateInvoiceTotals($finalInvoice, true);

            DB::commit();
            return $finalInvoice->fresh(['items']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating final invoice: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Convert Proforma Invoice to Final Invoice at DEFINITE stage
     * 
     * @param int $proformaInvoiceId
     * @return Invoice
     */
    public function convertToFinalInvoice($proformaInvoiceId)
    {
        DB::beginTransaction();
        try {
            $proformaInvoice = Invoice::with(['tour', 'items'])->where('invoice_id', $proformaInvoiceId)->first();
            
            if (!$proformaInvoice) {
                throw new \Exception("Proforma invoice not found");
            }

            if ($proformaInvoice->invoice_type !== 'proforma') {
                throw new \Exception("Only proforma invoices can be converted to final invoices");
            }

            $tour = $proformaInvoice->tour;
            $allowedStatuses = ['Confirmed', 'Definite', 'Actual'];
            if (!in_array($tour->tour_status, $allowedStatuses)) {
                throw new \Exception("Final invoice can only be generated for tours in Confirmed, Definite or Actual status");
            }

            // Check if final invoice already exists
            $existingFinal = Invoice::where('tour_id', $tour->tour_id)
                ->where('invoice_type', 'final')
                ->whereNull('deleted_at')
                ->first();

            if ($existingFinal) {
                throw new \Exception("Final invoice already exists for this tour");
            }

            // Generate new invoice ID for final invoice (new record, not update)
            $lastInvoice = Invoice::withTrashed()->orderBy('invoice_id', 'desc')->first();
            $maxInvoiceId = $lastInvoice->invoice_id ?? 0;
            $finalInvoiceId = CommonHelper::createId($maxInvoiceId);

            // Generate final invoice number
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($finalInvoiceId, 6, '0', STR_PAD_LEFT);

            // Refresh tour data and regenerate invoice data from latest tour/orders
            $tour = Tour::where('tour_id', $tour->tour_id)->first();
            $finalInvoiceData = $this->prepareInvoiceData($tour, [], 'final');
            $finalInvoiceData['invoice_id'] = $finalInvoiceId;
            $finalInvoiceData['invoice_type'] = 'final';
            $finalInvoiceData['invoice_number'] = $invoiceNumber;
            $finalInvoiceData['invoice_date'] = now()->toDateString();
            $finalInvoiceData['due_date'] = $proformaInvoice->due_date ?? now()->addDays(30)->toDateString();
            $finalInvoiceData['status'] = 'issued';

            // Create final invoice
            $finalInvoice = Invoice::create($finalInvoiceData);

            // Regenerate items from latest orders (to get updated services)
            $this->generateItemsFromTour($finalInvoice, $tour);

            // Calculate totals with GST
            $this->calculateInvoiceTotals($finalInvoice, true); // true = include GST

            // Mark proforma as cancelled (soft delete or status change)
            // Option 1: Soft delete proforma
            $proformaInvoice->delete();

            // Option 2: Or mark as cancelled (if you want to keep history)
            // $proformaInvoice->update(['status' => 'cancelled']);

            DB::commit();
            return $finalInvoice->fresh(['items']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error converting to final invoice: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update Proforma Invoice (editable)
     * 
     * @param int $invoiceId
     * @param array $data
     * @return Invoice
     */
    public function updateProformaInvoice($invoiceId, $data)
    {
        $invoice = Invoice::where('invoice_id', $invoiceId)->first();
        
        if (!$invoice) {
            throw new \Exception("Invoice not found");
        }

        if (!$invoice->isEditable()) {
            throw new \Exception("Only proforma invoices can be updated");
        }

        DB::beginTransaction();
        try {
            // Update invoice data
            $updateData = array_intersect_key($data, array_flip([
                'client_details', 'travel_company_details', 'destination',
                'travel_from_date', 'travel_to_date', 'duration_days',
                'no_of_adults', 'no_of_children', 'no_of_infants',
                'base_currency', 'validity_date', 'notes', 'terms_and_conditions',
                'currency_conversion', 'payment_terms', 'bank_details'
            ]));

            $invoice->update($updateData);

            // Update items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                $invoice->items()->delete();
                // Create new items
                $this->createInvoiceItems($invoice, $data['items']);
            }

            // Recalculate totals
            $this->calculateInvoiceTotals($invoice);

            DB::commit();
            return $invoice->fresh(['items']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating proforma invoice: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle cancellation - generate credit note if final invoice exists
     * 
     * @param int $tourId
     * @return CreditNote|null
     */
    public function handleCancellation($tourId)
    {
        $tour = Tour::where('tour_id', $tourId)->first();
        if (!$tour) {
            throw new \Exception("Tour not found");
        }

        $finalInvoice = Invoice::where('tour_id', $tourId)
            ->where('invoice_type', 'final')
            ->whereNull('deleted_at')
            ->first();

        if ($finalInvoice) {
            // Generate credit note for final invoice
            return $this->generateCreditNote($finalInvoice->invoice_id, [
                'reason' => 'cancellation',
                'reason_description' => 'Tour cancelled - credit note issued',
            ]);
        } else {
            // Cancel proforma invoice (soft delete)
            $proformaInvoice = Invoice::where('tour_id', $tourId)
                ->where('invoice_type', 'proforma')
                ->whereNull('deleted_at')
                ->first();

            if ($proformaInvoice) {
                $proformaInvoice->delete();
            }

            return null;
        }
    }

    /**
     * Generate Credit Note for final invoice
     * 
     * @param int $invoiceId
     * @param array $data
     * @return CreditNote
     */
    public function generateCreditNote($invoiceId, $data = [])
    {
        DB::beginTransaction();
        try {
            $invoice = Invoice::where('invoice_id', $invoiceId)->first();
            if (!$invoice || $invoice->invoice_type !== 'final') {
                throw new \Exception("Credit note can only be generated for final invoices");
            }

            // Generate credit note ID
            $lastCreditNote = CreditNote::withTrashed()->orderBy('credit_note_id', 'desc')->first();
            $maxCreditNoteId = $lastCreditNote->credit_note_id ?? 0;
            $creditNoteId = CommonHelper::createId($maxCreditNoteId);

            // Generate credit note number
            $creditNoteNumber = 'CN-' . date('Y') . '-' . str_pad($creditNoteId, 6, '0', STR_PAD_LEFT);

            $creditNoteData = [
                'credit_note_id' => $creditNoteId,
                'invoice_id' => $invoice->invoice_id,
                'tour_id' => $invoice->tour_id,
                'dmc_id' => $invoice->dmc_id,
                'credit_note_number' => $creditNoteNumber,
                'credit_note_date' => now()->toDateString(),
                'reason' => $data['reason'] ?? 'cancellation',
                'reason_description' => $data['reason_description'] ?? '',
                'currency' => $invoice->base_currency,
                'credit_amount' => $invoice->total_amount - $invoice->payment_received, // Outstanding amount
                'gst_amount' => $invoice->gst_amount,
                'total_credit' => $invoice->total_amount, // Full invoice amount
                'refund_status' => 'pending',
                'status' => 'issued',
                'notes' => $data['notes'] ?? '',
                'created_by' => auth()->id(),
            ];

            $creditNote = CreditNote::create($creditNoteData);

            DB::commit();
            return $creditNote;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating credit note: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prepare invoice data from tour
     */
    private function prepareInvoiceData($tour, $data, $type)
    {
        $agent = $tour->agent;
        $dmc = User::where('userId', $tour->dmc_id)->first();

        // Extract customer details from orders
        $clientDetails = $data['client_details'] ?? [];
        if (empty($clientDetails)) {
            $orders = Order::where('tour_id', $tour->tour_id)->get();
            $firstOrderWithCustomer = null;
            
            foreach ($orders as $order) {
                $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                if (is_array($orderData) && isset($orderData[0])) {
                    $firstBooking = $orderData[0];
                    if (isset($firstBooking['fullName'])) {
                        $firstOrderWithCustomer = $firstBooking;
                        break;
                    }
                }
            }
            
            if ($firstOrderWithCustomer) {
                $clientDetails = [
                    'address' => ($firstOrderWithCustomer['address1'] ?? '') . 
                                ($firstOrderWithCustomer['address2'] ? ', ' . $firstOrderWithCustomer['address2'] : ''),
                    'city' => $firstOrderWithCustomer['state'] ?? '',
                    'country' => '',
                    'email' => $firstOrderWithCustomer['email'] ?? '',
                    'phone' => ($firstOrderWithCustomer['countryCode'] ?? '') . ' ' . ($firstOrderWithCustomer['phone'] ?? ''),
                    'booking_id' => $tour->display_id ?? '',
                    'lead_guest_name' => $firstOrderWithCustomer['fullName'] ?? '',
                    'postal_code' => $firstOrderWithCustomer['zip'] ?? '',
                ];
            }
        }

        // Prepare travel company/agency details
        $travelCompanyDetails = $data['travel_company_details'] ?? [];
        if (empty($travelCompanyDetails) && $agent) {
            // Prefer agency details when available
            $agency = method_exists($agent, 'agency') ? $agent->agency : null;

            $travelCompanyDetails = [
                // Agent name
                'name' => $agent->name ?? '',

                // Travel agency company name
                'company_name' => $agency->agency_name ?? $agent->company_name ?? '',

                // Address from agency first, fallback to agent
                'address' => $agency->address ?? $agent->address ?? '',

                // Contact person from agency, fallback to agent name
                'contact_person' => $agency->contact_person ?? $agent->name ?? '',

                // Phone from agency, fallback to agent
                'phone' => $agency->phone ?? $agent->contact_no ?? '',

                // Email from agency, fallback to agent
                'email' => $agency->email ?? $agent->email ?? '',
            ];
        }

        // Calculate duration days
        $durationDays = $data['duration_days'] ?? null;
        if (!$durationDays && $tour->check_in_time && $tour->check_out_time) {
            $checkIn = \Carbon\Carbon::parse($tour->check_in_time);
            $checkOut = \Carbon\Carbon::parse($tour->check_out_time);
            $durationDays = $checkIn->diffInDays($checkOut) + 1;
        }

        return [
            'tour_id' => $tour->tour_id,
            'dmc_id' => $tour->dmc_id,
            'agent_id' => $tour->agent_id,
            'invoice_type' => $type,
            'status' => $type === 'proforma' ? 'draft' : 'issued',
            'destination' => $tour->destination ?? $data['destination'] ?? '',
            'travel_from_date' => $tour->check_in_time ?? $data['travel_from_date'] ?? null,
            'travel_to_date' => $tour->check_out_time ?? $data['travel_to_date'] ?? null,
            'duration_days' => $durationDays,
            'no_of_adults' => $tour->adult ?? $data['no_of_adults'] ?? 0,
            'no_of_children' => $tour->child ?? $data['no_of_children'] ?? 0,
            'no_of_infants' => $tour->infant ?? $data['no_of_infants'] ?? 0,
            'base_currency' => $data['base_currency'] ?? 'SGD',
            'client_details' => $clientDetails,
            'travel_company_details' => $travelCompanyDetails,
            'currency_conversion' => $data['currency_conversion'] ?? [],
            'payment_terms' => $data['payment_terms'] ?? [],
            'bank_details' => $data['bank_details'] ?? ($dmc->bank_details ?? null),
            'notes' => $data['notes'] ?? '',
            'terms_and_conditions' => $data['terms_and_conditions'] ?? '',
            'sent_by' => $data['sent_by'] ?? auth()->user()->name ?? null,
            'created_by' => auth()->id(),
        ];
    }

    /**
     * Create invoice items
     */
    private function createInvoiceItems($invoice, $items)
    {
        $displayOrder = 1;
        foreach ($items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->invoice_id,
                'item_type' => $item['item_type'] ?? null,
                'description' => $item['description'] ?? '',
                'service_details' => $item['service_details'] ?? [],
                'quantity_adults' => $item['quantity_adults'] ?? 0,
                'quantity_children' => $item['quantity_children'] ?? 0,
                'quantity_infants' => $item['quantity_infants'] ?? 0,
                'unit_price' => $item['unit_price'] ?? 0,
                'total_price' => $item['total_price'] ?? 0,
                'display_order' => $displayOrder++,
            ]);
        }
    }

    /**
     * Generate items from tour orders
     */
    private function generateItemsFromTour($invoice, $tour)
    {
        $orders = Order::where('tour_id', $tour->tour_id)->whereNull('deleted_at')->get();
        $items = [];
        $displayOrder = 1;

        foreach ($orders as $order) {
            $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            
            if (!is_array($orderData)) {
                continue;
            }

            // Handle array of bookings (most orders have this structure)
            $bookings = isset($orderData[0]) && is_array($orderData[0]) ? $orderData : [$orderData];
            
            foreach ($bookings as $booking) {
                if (!is_array($booking)) {
                    continue;
                }

                // Calculate total price based on service type
                $totalPrice = $booking['totalPrice'] ?? $booking['price'] ?? 0;
                
                // For attractions and restaurants, the create methods will calculate the grand total
                // So we pass the base price, but the method will add transfer/guide costs
                
                switch ($order->type) {
                    case 'hotel':
                        $items[] = $this->createHotelItem($booking, $totalPrice, $displayOrder++);
                        break;
                    case 'attraction':
                        $items[] = $this->createAttractionItem($booking, $totalPrice, $displayOrder++);
                        break;
                    case 'restaurant':
                        $items[] = $this->createRestaurantItem($booking, $totalPrice, $displayOrder++);
                        break;
                    case 'guide':
                        $items[] = $this->createGuideItem($booking, $totalPrice, $displayOrder++);
                        break;
                    case 'entry_port':
                        $items[] = $this->createEntryPortItem($booking, $totalPrice, $displayOrder++);
                        break;
                    case 'exit_port':
                        $items[] = $this->createExitPortItem($booking, $totalPrice, $displayOrder++);
                        break;
                    case 'travel_point':
                        $items[] = $this->createTravelPointItem($booking, $totalPrice, $displayOrder++);
                        break;
                    case 'travel_hourly':
                        $items[] = $this->createTravelHourlyItem($booking, $totalPrice, $displayOrder++);
                        break;
                    case 'local_transport':
                    case 'local_transfer':
                        $items[] = $this->createLocalTransportItem($booking, $totalPrice, $displayOrder++);
                        break;
                    case 'miscellaneous':
                        // Miscellaneous items only for Pro tours (is_pro = 1)
                        if ((int)($tour->is_pro ?? 0) === 1) {
                            $items[] = $this->createMiscellaneousItem($booking, $totalPrice, $displayOrder++);
                        }
                        break;
                    // Other types will be handled later when JSON format is provided
                    default:
                        // Skip for now - will be implemented later
                        break;
                }
            }
        }

        if (!empty($items)) {
            $this->createInvoiceItems($invoice, $items);
        }
    }

    /**
     * Create invoice item for hotel order
     */
    private function createHotelItem($booking, $totalPrice, $displayOrder)
    {
        $hotelDetails = $booking['hotelDetails'] ?? [];
        $hotelName = $hotelDetails['hotel_name'] ?? 'Hotel Booking';
        
        // Get booking dates
        $bookingDates = $booking['bookingDate'] ?? [];
        $checkInDate = !empty($bookingDates) ? $bookingDates[0] : '';
        $checkOutDate = !empty($bookingDates) && count($bookingDates) > 1 ? end($bookingDates) : '';
        
        // Get check in/out times from hotelDetails
        $checkInTime = $hotelDetails['checkInTime'] ?? '';
        $checkOutTime = $hotelDetails['checkOutTime'] ?? '';
        
        // Combine date and time
        $checkIn = $checkInDate ? ($checkInDate . ($checkInTime ? ' ' . $checkInTime : '')) : '';
        $checkOut = $checkOutDate ? ($checkOutDate . ($checkOutTime ? ' ' . $checkOutTime : '')) : '';
        
        // Calculate days
        $days = 0;
        if ($checkInDate && $checkOutDate) {
            $checkInCarbon = \Carbon\Carbon::parse($checkInDate);
            $checkOutCarbon = \Carbon\Carbon::parse($checkOutDate);
            $days = $checkInCarbon->diffInDays($checkOutCarbon);
        }

        // Get room category
        $rooms = $booking['rooms'] ?? [];
        $roomCategory = '';
        $totalPax = 0;
        if (!empty($rooms)) {
            $roomTypes = array_unique(array_column($rooms, 'room_type'));
            $roomCategory = implode(', ', $roomTypes);
            
            // Calculate total pax from all beds
            foreach ($rooms as $room) {
                if (isset($room['beds']) && is_array($room['beds'])) {
                    foreach ($room['beds'] as $bed) {
                        $totalPax += $bed['head_count'] ?? 0;
                    }
                }
            }
        }

        $description = $hotelName . ($roomCategory ? ' - ' . $roomCategory : '');

        // Child accommodation (child_with_bed / child_without_bed)
        $childWithBed = null;
        $childWithoutBed = null;
        if (isset($booking['child_with_bed']['enabled']) && $booking['child_with_bed']['enabled']) {
            $childWithBed = $booking['child_with_bed'];
        }
        if (isset($booking['child_without_bed']['enabled']) && $booking['child_without_bed']['enabled']) {
            $childWithoutBed = $booking['child_without_bed'];
        }

        return [
            'item_type' => 'hotel',
            'description' => $description,
            'service_details' => [
                'hotel_name' => $hotelName,
                'room_category' => $roomCategory,
                'location' => $hotelDetails['location'] ?? '',
                'check_in' => $checkIn,
                'check_in_date' => $checkInDate,
                'check_in_time' => $checkInTime,
                'check_out' => $checkOut,
                'check_out_date' => $checkOutDate,
                'check_out_time' => $checkOutTime,
                'no_of_days' => $days,
                'total_pax' => $totalPax,
                'confirmation' => '',
                'child_with_bed' => $childWithBed,
                'child_without_bed' => $childWithoutBed,
            ],
            'quantity_adults' => 0,
            'quantity_children' => 0,
            'quantity_infants' => 0,
            'unit_price' => $totalPrice,
            'total_price' => $totalPrice,
            'display_order' => $displayOrder,
        ];
    }

    /**
     * Create invoice item for attraction order
     */
    private function createAttractionItem($booking, $totalPrice, $displayOrder)
    {
        $attractionName = $booking['AttractionName'] ?? 'Attraction';
        $ticketName = $booking['ticketName'] ?? '';
        $ticketDetails = $booking['ticket_details'] ?? [];
        
        // Build ticket details string - just the ticket name, no prices
        $ticketDetailsStr = $ticketName;
        
        // Transfer information
        $transferOptions = $booking['transfer_options'] ?? [];
        $transferRequired = $transferOptions['transfer_required'] ?? false;
        $transferWay = $transferOptions['way'] ?? '';
        $transferType = $transferOptions['type'] ?? '';
        
        // Vehicle details
        $vehicleName = '';
        $vehicleType = '';
        $vehicleSeatingCapacity = '';
        $vehiclePrivatePrice = '';
        $vehicleDetailsStr = '';
        if (isset($transferOptions['vehicle_details']) && is_array($transferOptions['vehicle_details'])) {
            $vehicleDetails = $transferOptions['vehicle_details'];
            $vehicleName = $vehicleDetails['vehicle_name'] ?? '';
            $vehicleType = $vehicleDetails['vehicle_type'] ?? '';
            $vehicleSeatingCapacity = $vehicleDetails['seating_capacity'] ?? '';
            $vehiclePrivatePrice = $vehicleDetails['private_price'] ?? '';
            
            // Build vehicle details string - just the vehicle name, no type, seating, or price
            $vehicleDetailsStr = $vehicleName;
        }
        
        // Guide information
        $guideOptions = $booking['guide_options'] ?? [];
        $guideRequired = $guideOptions['guide_required'] ?? false;
        $guideName = $guideOptions['guide_name'] ?? '';
        $guideHours = $guideOptions['hours'] ?? $guideOptions['package_hours'] ?? '';
        $guideLanguage = $guideOptions['language'] ?? '';
        
        // Calculate prices
        $attractionBasePrice = $booking['price'] ?? $booking['totalPrice'] ?? 0;
        $transferCost = isset($transferOptions['cost']) && $transferOptions['cost'] > 0 ? (float) $transferOptions['cost'] : 0;
        $guideTotalPrice = isset($guideOptions['total_price']) && $guideOptions['total_price'] > 0 ? (float) $guideOptions['total_price'] : 0;
        $grandTotal = $attractionBasePrice + $transferCost + $guideTotalPrice;
        
        // Calculate total pax
        $totalPax = ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) + ($booking['seniorCount'] ?? 0) + ($booking['infantCount'] ?? 0);
        
        $description = $attractionName . ($ticketName ? ' - ' . $ticketName : '');

        return [
            'item_type' => 'attraction',
            'description' => $description,
            'service_details' => [
                'attraction_name' => $attractionName,
                'ticket_name' => $ticketName,
                'ticket_details' => $ticketDetailsStr,
                'booking_date' => $booking['bookingDate'] ?? '',
                'visit_time' => $booking['visitTime'] ?? '',
                'transfer_required' => $transferRequired ? 'Yes' : 'No',
                'transfer_way' => $transferWay,
                'transfer_type' => $transferType,
                'transfer_cost' => $transferCost,
                'vehicle_name' => $vehicleName,
                'vehicle_type' => $vehicleType,
                'vehicle_seating_capacity' => $vehicleSeatingCapacity,
                'vehicle_private_price' => $vehiclePrivatePrice,
                'vehicle_details' => $vehicleDetailsStr,
                'guide_required' => $guideRequired,
                'guide_name' => $guideName,
                'guide_hours' => $guideHours,
                'guide_language' => $guideLanguage,
                'guide_total_price' => $guideTotalPrice,
                'attraction_base_price' => $attractionBasePrice,
                'total_pax' => $totalPax,
            ],
            'quantity_adults' => $booking['adultCount'] ?? 0,
            'quantity_children' => $booking['childCount'] ?? 0,
            'quantity_infants' => $booking['infantCount'] ?? 0,
            'quantity_seniors' => $booking['seniorCount'] ?? 0,
            'unit_price' => $grandTotal,
            'total_price' => $grandTotal,
            'display_order' => $displayOrder,
        ];
    }

    /**
     * Create invoice item for restaurant order
     */
    private function createRestaurantItem($booking, $totalPrice, $displayOrder)
    {
        $restaurantName = $booking['restaurantName'] ?? 'Restaurant';
        $mealType = $booking['mealType'] ?? '';
        $mealSpecificType = $booking['mealSpecificType'] ?? '';
        
        // Transfer information
        $transferOptions = $booking['transfer_options'] ?? [];
        $transferRequired = $transferOptions['transfer_required'] ?? false;
        $transferWay = $transferOptions['way'] ?? '';
        $transferType = $transferOptions['type'] ?? '';
        
        // Vehicle details
        $vehicleName = '';
        $vehicleType = '';
        $vehicleSeatingCapacity = '';
        $vehiclePrivatePrice = '';
        $vehicleDetailsStr = '';
        if (isset($transferOptions['vehicle_details']) && is_array($transferOptions['vehicle_details'])) {
            $vehicleDetails = $transferOptions['vehicle_details'];
            $vehicleName = $vehicleDetails['vehicle_name'] ?? '';
            $vehicleType = $vehicleDetails['vehicle_type'] ?? '';
            $vehicleSeatingCapacity = $vehicleDetails['seating_capacity'] ?? '';
            $vehiclePrivatePrice = $vehicleDetails['private_price'] ?? '';
            
            // Build vehicle details string - just the vehicle name, no type, seating, or price
            $vehicleDetailsStr = $vehicleName;
        }
        
        // Guide options (for Pro tours) - pickup from total_price, cost, or sell
        $guideOptions = $booking['guide_options'] ?? [];
        $guideTotalPrice = 0;
        if (!empty($guideOptions)) {
            $gv = $guideOptions['total_price'] ?? $guideOptions['cost'] ?? $guideOptions['Cost'] ?? $guideOptions['sell'] ?? $guideOptions['Sell'] ?? 0;
            if ((float) $gv > 0) {
                $guideTotalPrice = (float) $gv;
            }
        }
        $guideName = $guideOptions['guide_name'] ?? $guideOptions['guideName'] ?? '';
        $guideHours = $guideOptions['hours'] ?? $guideOptions['package_hours'] ?? '';

        // Calculate prices
        $restaurantBasePrice = $booking['mealPrice'] ?? $booking['totalPrice'] ?? 0;
        $transferCost = isset($transferOptions['cost']) && $transferOptions['cost'] > 0 ? (float) $transferOptions['cost'] : 0;
        $grandTotal = $restaurantBasePrice + $transferCost + $guideTotalPrice;

        $description = $restaurantName . ($mealType ? ' - ' . $mealType : '') . ($mealSpecificType ? ' (' . $mealSpecificType . ')' : '');

        return [
            'item_type' => 'restaurant',
            'description' => $description,
            'service_details' => [
                'restaurant_name' => $restaurantName,
                'meal_type' => $mealType,
                'meal_specific_type' => $mealSpecificType,
                'booking_date' => $booking['bookingDate'] ?? '',
                'visit_time' => $booking['visitTime'] ?? '',
                'transfer_required' => $transferRequired ? 'Yes' : 'No',
                'transfer_way' => $transferWay,
                'transfer_type' => $transferType,
                'transfer_cost' => $transferCost,
                'vehicle_name' => $vehicleName,
                'vehicle_type' => $vehicleType,
                'vehicle_seating_capacity' => $vehicleSeatingCapacity,
                'vehicle_private_price' => $vehiclePrivatePrice,
                'vehicle_details' => $vehicleDetailsStr,
                'restaurant_base_price' => $restaurantBasePrice,
                'guide_options' => $guideOptions,
                'guide_name' => $guideName,
                'guide_hours' => $guideHours,
                'guide_total_price' => $guideTotalPrice,
                'adult_count' => $booking['adultCount'] ?? 0,
                'child_count' => $booking['childCount'] ?? 0,
            ],
            'quantity_adults' => $booking['adultCount'] ?? 0,
            'quantity_children' => $booking['childCount'] ?? 0,
            'quantity_infants' => $booking['infantCount'] ?? 0,
            'unit_price' => $grandTotal,
            'total_price' => $grandTotal,
            'display_order' => $displayOrder,
        ];
    }

    /**
     * Create invoice item for guide order
     */
    private function createGuideItem($booking, $totalPrice, $displayOrder)
    {
        $guideName = $booking['guide_name'] ?? 'Guide Service';
        $pickupDate = $booking['pickupdate'] ?? $booking['bookingDate'] ?? '';
        $hours = $booking['hours'] ?? 0;
        $adults = $booking['adults'] ?? 0;
        $children = $booking['children'] ?? 0;
        $infants = $booking['infants'] ?? 0;
        
        $description = $guideName . ($hours ? ' - ' . $hours . ' hours' : '');

        return [
            'item_type' => 'guide',
            'description' => $description,
            'service_details' => [
                'guide_name' => $guideName,
                'pickup_date' => $pickupDate,
                'hours' => $hours,
            ],
            'quantity_adults' => $adults,
            'quantity_children' => $children,
            'quantity_infants' => $infants,
            'unit_price' => $totalPrice,
            'total_price' => $totalPrice,
            'display_order' => $displayOrder,
        ];
    }

    /**
     * Create invoice item for entry_port order
     */
    private function createEntryPortItem($booking, $totalPrice, $displayOrder)
    {
        $entryPickup = $booking['entrypickup'] ?? '';
        $entryDropoff = $booking['entrydropoff'] ?? '';
        $pickupDate = $booking['pickupdate'] ?? $booking['bookingDate'] ?? '';
        $entryTime = $booking['entrytime'] ?? '';
        
        // Vehicle information - check multiple possible field names (vehicles_name is the standard for entry_port)
        $vehicleName = $booking['vehicles_name'] ?? $booking['vehicle_name'] ?? '';
        $vehicleType = $booking['type'] ?? $booking['vehicle_type'] ?? '';
        
        // Guide options (for Pro tours) - pickup from total_price, cost, or sell
        $guideOptions = $booking['guide_options'] ?? [];
        $guideTotalPrice = 0;
        if (!empty($guideOptions)) {
            $gv = $guideOptions['total_price'] ?? $guideOptions['cost'] ?? $guideOptions['Cost'] ?? $guideOptions['sell'] ?? $guideOptions['Sell'] ?? 0;
            if ((float) $gv > 0) {
                $guideTotalPrice = (float) $gv;
            }
        }
        $guideName = $guideOptions['guide_name'] ?? $guideOptions['guideName'] ?? '';
        $guideHours = $guideOptions['hours'] ?? $guideOptions['package_hours'] ?? '';
        
        $adults = $booking['adults'] ?? $booking['adultCount'] ?? 0;
        $children = $booking['children'] ?? $booking['childCount'] ?? 0;
        $infants = $booking['infants'] ?? $booking['infantCount'] ?? 0;

        $effectiveTotal = (float) $totalPrice + $guideTotalPrice;
        
        $description = 'Arrival Service' . ($entryPickup ? ' - ' . $entryPickup : '');

        return [
            'item_type' => 'entry_port',
            'description' => $description,
            'service_details' => [
                'entrypickup' => $entryPickup,
                'entrydropoff' => $entryDropoff,
                'vehicle_name' => $vehicleName,
                'vehicle_type' => $vehicleType,
                'pickup_date' => $pickupDate,
                'entrytime' => $entryTime,
                'guide_options' => $guideOptions,
                'guide_name' => $guideName,
                'guide_hours' => $guideHours,
                'guide_total_price' => $guideTotalPrice,
            ],
            'quantity_adults' => $adults,
            'quantity_children' => $children,
            'quantity_infants' => $infants,
            'unit_price' => $effectiveTotal,
            'total_price' => $effectiveTotal,
            'display_order' => $displayOrder,
        ];
    }

    /**
     * Create invoice item for exit_port order (departure)
     */
    private function createExitPortItem($booking, $totalPrice, $displayOrder)
    {
        $exitPickup = $booking['exitpickup'] ?? '';
        $exitDropoff = $booking['exitdropoff'] ?? '';
        $exitPickupDate = $booking['exitpickupdate'] ?? $booking['bookingDate'] ?? '';
        $entryTime = $booking['entrytime'] ?? '';
        
        // Vehicle information - check multiple possible field names (vehicles_name is the standard for exit_port)
        $vehicleName = $booking['vehicles_name'] ?? $booking['vehicle_name'] ?? '';
        $vehicleType = $booking['type'] ?? $booking['vehicle_type'] ?? '';
        
        // Guide options (for Pro tours) - pickup from total_price, cost, or sell
        $guideOptions = $booking['guide_options'] ?? [];
        $guideTotalPrice = 0;
        if (!empty($guideOptions)) {
            $gv = $guideOptions['total_price'] ?? $guideOptions['cost'] ?? $guideOptions['Cost'] ?? $guideOptions['sell'] ?? $guideOptions['Sell'] ?? 0;
            if ((float) $gv > 0) {
                $guideTotalPrice = (float) $gv;
            }
        }
        $guideName = $guideOptions['guide_name'] ?? $guideOptions['guideName'] ?? '';
        $guideHours = $guideOptions['hours'] ?? $guideOptions['package_hours'] ?? '';
        
        $adults = $booking['adults'] ?? $booking['adultCount'] ?? 0;
        $children = $booking['children'] ?? $booking['childCount'] ?? 0;
        $infants = $booking['infants'] ?? $booking['infantCount'] ?? 0;

        $effectiveTotal = (float) $totalPrice + $guideTotalPrice;
        
        $description = 'Departure Service' . ($exitPickup ? ' - ' . $exitPickup : '');

        return [
            'item_type' => 'exit_port',
            'description' => $description,
            'service_details' => [
                'exitpickup' => $exitPickup,
                'exitdropoff' => $exitDropoff,
                'vehicle_name' => $vehicleName,
                'vehicle_type' => $vehicleType,
                'exitpickupdate' => $exitPickupDate,
                'entrytime' => $entryTime,
                'guide_options' => $guideOptions,
                'guide_name' => $guideName,
                'guide_hours' => $guideHours,
                'guide_total_price' => $guideTotalPrice,
            ],
            'quantity_adults' => $adults,
            'quantity_children' => $children,
            'quantity_infants' => $infants,
            'unit_price' => $effectiveTotal,
            'total_price' => $effectiveTotal,
            'display_order' => $displayOrder,
        ];
    }

    /**
     * Create invoice item for miscellaneous order (Pro tours only)
     */
    private function createMiscellaneousItem($booking, $totalPrice, $displayOrder)
    {
        $itemName = $booking['item_name'] ?? $booking['miscellaneous_name'] ?? 'Miscellaneous Item';
        $bookingDate = $booking['bookingDate'] ?? '';
        $description = $booking['description'] ?? $booking['remarks'] ?? $itemName;
        $adults = (int)($booking['adultsQty'] ?? $booking['adults'] ?? $booking['adult'] ?? 0);
        $children = (int)($booking['childQty'] ?? $booking['children'] ?? $booking['child'] ?? 0);
        $infants = (int)($booking['infantQty'] ?? $booking['infants'] ?? $booking['infant'] ?? 0);

        // Calculate total price from pricing fields if available
        $computedTotal = $totalPrice;
        if (isset($booking['totalPrice']) && (float)$booking['totalPrice'] > 0) {
            $computedTotal = (float) $booking['totalPrice'];
        } elseif (isset($booking['adultSell']) || isset($booking['childSell'])) {
            $adultSell = (float)($booking['adultSell'] ?? 0);
            $childSell = (float)($booking['childSell'] ?? 0);
            $infantSell = (float)($booking['infantSell'] ?? 0);
            $computedTotal = ($adultSell * $adults) + ($childSell * $children) + ($infantSell * $infants);
        }

        return [
            'item_type' => 'miscellaneous',
            'description' => $description,
            'service_details' => [
                'item_name' => $itemName,
                'booking_date' => $bookingDate,
            ],
            'quantity_adults' => $adults,
            'quantity_children' => $children,
            'quantity_infants' => $infants,
            'unit_price' => round($computedTotal, 2),
            'total_price' => round($computedTotal, 2),
            'display_order' => $displayOrder,
        ];
    }

    /**
     * Create invoice item for travel_point order
     */
    private function createTravelPointItem($booking, $totalPrice, $displayOrder)
    {
        $entryPickup = $booking['entrypickup'] ?? '';
        $entryDropoff = $booking['entrydropoff'] ?? '';
        $pickupDate = $booking['pickupdate'] ?? $booking['bookingDate'] ?? '';
        $entryTime = $booking['entrytime'] ?? '';
        
        // Vehicle information
        $vehicleName = $booking['vehicles_name'] ?? $booking['vehicle_name'] ?? '';
        $vehicleType = $booking['type'] ?? $booking['vehicle_type'] ?? '';
        
        $adults = $booking['adults'] ?? $booking['adultCount'] ?? 0;
        $children = $booking['children'] ?? $booking['childCount'] ?? 0;
        $infants = $booking['infants'] ?? $booking['infantCount'] ?? 0;
        $totalPersons = $adults + $children + $infants;
        
        $description = 'Point to Point Transfer' . ($entryPickup ? ' - ' . $entryPickup : '');

        return [
            'item_type' => 'travel_point',
            'description' => $description,
            'service_details' => [
                'entrypickup' => $entryPickup,
                'entrydropoff' => $entryDropoff,
                'vehicle_name' => $vehicleName,
                'vehicle_type' => $vehicleType,
                'pickup_date' => $pickupDate,
                'entrytime' => $entryTime,
                'adults' => $adults,
                'children' => $children,
                'infants' => $infants,
                'total_persons' => $totalPersons,
            ],
            'quantity_adults' => $adults,
            'quantity_children' => $children,
            'quantity_infants' => $infants,
            'unit_price' => round($totalPrice),
            'total_price' => round($totalPrice),
            'display_order' => $displayOrder,
        ];
    }

    /**
     * Create invoice item for travel_hourly order
     */
    private function createTravelHourlyItem($booking, $totalPrice, $displayOrder)
    {
        $entryPickup = $booking['entrypickup'] ?? '';
        $entryDropoff = $booking['entrydropoff'] ?? '';
        $pickupDate = $booking['pickupdate'] ?? $booking['bookingDate'] ?? '';
        $entryTime = $booking['entrytime'] ?? '';
        $selectedHours = $booking['selectedHours'] ?? 0;
        
        // Vehicle information
        $vehicleName = $booking['vehicles_name'] ?? $booking['vehicle_name'] ?? '';
        $vehicleType = $booking['type'] ?? $booking['vehicle_type'] ?? '';
        
        $adults = $booking['adults'] ?? $booking['adultCount'] ?? 0;
        $children = $booking['children'] ?? $booking['childCount'] ?? 0;
        $infants = $booking['infants'] ?? $booking['infantCount'] ?? 0;
        $totalPersons = $adults + $children + $infants;
        
        $description = 'Hourly Tour Service' . ($entryPickup ? ' - ' . $entryPickup : '') . ($selectedHours ? ' (' . $selectedHours . ' hours)' : '');

        return [
            'item_type' => 'travel_hourly',
            'description' => $description,
            'service_details' => [
                'entrypickup' => $entryPickup,
                'entrydropoff' => $entryDropoff,
                'vehicle_name' => $vehicleName,
                'vehicle_type' => $vehicleType,
                'pickup_date' => $pickupDate,
                'entrytime' => $entryTime,
                'selectedHours' => $selectedHours,
                'adults' => $adults,
                'children' => $children,
                'infants' => $infants,
                'total_persons' => $totalPersons,
            ],
            'quantity_adults' => $adults,
            'quantity_children' => $children,
            'quantity_infants' => $infants,
            'unit_price' => round($totalPrice),
            'total_price' => round($totalPrice),
            'display_order' => $displayOrder,
        ];
    }

    /**
     * Create invoice item for local_transport order
     */
    private function createLocalTransportItem($booking, $totalPrice, $displayOrder)
    {
        $entryPickup = $booking['entrypickup'] ?? '';
        $entryDropoff = $booking['entrydropoff'] ?? '';
        $pickupDate = $booking['pickupdate'] ?? $booking['bookingDate'] ?? '';
        $entryTime = $booking['entrytime'] ?? '';
        
        // Vehicle information
        $vehicleName = $booking['vehicles_name'] ?? $booking['vehicle_name'] ?? '';
        $vehicleType = $booking['type'] ?? $booking['vehicle_type'] ?? '';
        
        $adults = $booking['adults'] ?? $booking['adultCount'] ?? 0;
        $children = $booking['children'] ?? $booking['childCount'] ?? 0;
        $infants = $booking['infants'] ?? $booking['infantCount'] ?? 0;
        $totalPersons = $adults + $children + $infants;
        
        $description = 'Local Transport Service' . ($entryPickup ? ' - ' . $entryPickup : '');

        return [
            'item_type' => 'local_transport',
            'description' => $description,
            'service_details' => [
                'entrypickup' => $entryPickup,
                'entrydropoff' => $entryDropoff,
                'vehicle_name' => $vehicleName,
                'vehicle_type' => $vehicleType,
                'pickup_date' => $pickupDate,
                'entrytime' => $entryTime,
                'adults' => $adults,
                'children' => $children,
                'infants' => $infants,
                'total_persons' => $totalPersons,
            ],
            'quantity_adults' => $adults,
            'quantity_children' => $children,
            'quantity_infants' => $infants,
            'unit_price' => round($totalPrice),
            'total_price' => round($totalPrice),
            'display_order' => $displayOrder,
        ];
    }

    /**
     * Get last negotiated amount from enquiry_comments for a tour
     */
    private function getLastNegotiatedAmount($tourId)
    {
        $lastEnquiry = Enquiry::where('tour_id', $tourId)
            ->whereNotNull('amount')
            ->orderBy('enquiry_id', 'desc')
            ->first();
        
        return $lastEnquiry ? $lastEnquiry->amount : null;
    }

    /**
     * Recalculate invoice totals (public method for refreshing totals)
     */
    public function recalculateInvoiceTotals($invoice)
    {
        return $this->calculateInvoiceTotals($invoice, true);
    }

    /**
     * Calculate invoice totals
     */
    private function calculateInvoiceTotals($invoice, $includeGst = false)
    {
        // Ensure relations exist
        $items = $invoice->items ?? collect([]);
        $itemsSubtotal = $items->sum('total_price');

        // Get tour to check status and calculate taxes
        $tour = $invoice->tour;
        $tourStatus = $tour->tour_status ?? '';
        
        $tourId = $tour->tour_id ?? $invoice->tour_id;

        // Current total from Orders JSON (source of truth for services)
        $ordersTotal = $this->getOrdersTotalForTour($tourId);

        // Baseline subtotal before sync (used when enquiry baseline is missing)
        $previousSubtotalBaseline = (float) ($invoice->subtotal ?? $itemsSubtotal);

        // Negotiated amount from enquiry_comments
        $negotiatedAmountRaw = $this->getLastNegotiatedAmount($tourId);

        /**
         * Business rule (as per screenshot):
         * - Actual Amount = sum of all current services (Orders total)
         * - Discount stays constant (based on first enquiry actual_amount - negotiated_amount)
         * - Base/Last Negotiated Amount should increase when new services are added
         *
         * So, Base Amount = Orders Total - DiscountFixed
         */
        $discountFixed = 0.0;
        $enquiryActual = null;
        try {
            $enquiry = Enquiry::where('tour_id', $tourId)->first();
            $enquiryActual = $enquiry?->actual_amount;
            if ($enquiryActual !== null && $negotiatedAmountRaw !== null) {
                $discountFixed = (float) $enquiryActual - (float) $negotiatedAmountRaw;
            }
        } catch (\Exception $e) {
            $enquiryActual = null;
            $discountFixed = 0.0;
        }

        if ($enquiryActual !== null && $negotiatedAmountRaw !== null) {
            $baseAmount = (float) $ordersTotal - (float) $discountFixed;
        } elseif ($negotiatedAmountRaw !== null) {
            // Fallback when enquiry baseline is missing: add newly added services vs last stored subtotal baseline
            $additionalServicesTotal = max(0, (float) $ordersTotal - (float) $previousSubtotalBaseline);
            $baseAmount = (float) $negotiatedAmountRaw + (float) $additionalServicesTotal;
        } else {
            $baseAmount = (float) $ordersTotal;
        }

        // Sync invoice items from orders if services changed, so service tables show newly added services
        $this->syncInvoiceItemsFromOrdersIfNeeded($invoice, $tourId, $ordersTotal);
        // Refresh items after sync for accurate subtotal storage
        $items = $invoice->items ?? collect([]);
        $itemsSubtotal = $items->sum('total_price');

        $gstAmount = 0;
        $serviceCharge = 0;
        $touristTax = 0;
        $taxBreakdown = [];

        // Only calculate GST/taxes for Confirmed, Definite, or Actual statuses (NOT Tentative)
        $statusesWithTax = ['Confirmed', 'Definite', 'Actual'];
        $shouldCalculateTax = in_array($tourStatus, $statusesWithTax);

        if ($shouldCalculateTax && $tour) {
            // Use TaxHelper to calculate taxes properly
            $persons = ($tour->adult ?? 0) + ($tour->child ?? 0);
            $days = \App\Helpers\TaxHelper::calculateDays($tour->check_in_time, $tour->check_out_time);
            
            $taxResult = \App\Helpers\TaxHelper::calculateTourTaxes($baseAmount, $tour->taxes, $persons, $days);
            $gstAmount = $taxResult['total_tax'] ?? 0;
            $taxBreakdown = $taxResult['breakdown'] ?? [];
        }

        // Final Price = Last Negotiated Amount + Taxes
        $finalPrice = $baseAmount + $gstAmount + $serviceCharge + $touristTax;
        $totalAmount = $finalPrice; // For backward compatibility
        
        // Calculate payment received from tour payment_details
        $totalPaid = 0;
        if ($tour && $tour->payment_details) {
            $paymentDetails = is_string($tour->payment_details) ? json_decode($tour->payment_details, true) : $tour->payment_details;
            if (is_array($paymentDetails)) {
                foreach ($paymentDetails as $payment) {
                    if (isset($payment['amount']) && isset($payment['status']) && $payment['status'] == 1) {
                        $totalPaid += (float) $payment['amount'];
                    }
                }
            }
        }
        
        // Outstanding Balance = Final Price - Payment Received
        $outstandingBalance = $finalPrice - $totalPaid;

        // Store tax breakdown in notes as JSON
        $notes = is_string($invoice->notes) ? json_decode($invoice->notes, true) : ($invoice->notes ?? []);
        if (!is_array($notes)) {
            $notes = [];
        }
        $notes['tax_breakdown'] = $taxBreakdown;
        $notes['base_amount'] = $baseAmount;
        $notes['negotiated_amount'] = $negotiatedAmountRaw;
        $notes['orders_total'] = $ordersTotal;
        $notes['discount_fixed'] = $discountFixed;
        $notes['previous_subtotal_baseline'] = $previousSubtotalBaseline;

        $invoice->update([
            // Subtotal should reflect current services total (Orders total)
            'subtotal' => $ordersTotal,
            'gst_amount' => $gstAmount,
            'service_charge' => $serviceCharge,
            'tourist_tax' => $touristTax,
            'total_amount' => $totalAmount,
            'payment_received' => $totalPaid,
            'outstanding_balance' => $outstandingBalance,
            'notes' => json_encode($notes),
        ]);
    }

    /**
     * Sum total price from Orders JSON for a tour (covers all service types)
     * - Attractions: base + transfer + guide
     * - Restaurants: base + transfer + guide (guide from guide_options cost/sell/total_price)
     * - Entry/Exit ports: base (vehicle) + guide
     * - Others: base only
     */
    private function getOrdersTotalForTour($tourId): float
    {
        $tour = \App\Models\Tour::where('tour_id', $tourId)->first();
        $isPro = $tour && (int)($tour->is_pro ?? 0) === 1;

        $orders = Order::where('tour_id', $tourId)->whereNull('deleted_at')->get();
        $sum = 0.0;
        foreach ($orders as $order) {
            $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            if (!is_array($orderData)) {
                continue;
            }
            $bookings = (isset($orderData[0]) && is_array($orderData[0])) ? $orderData : [$orderData];
            foreach ($bookings as $booking) {
                if (!is_array($booking)) {
                    continue;
                }

                $basePrice = (float)($booking['totalPrice'] ?? $booking['price'] ?? 0);

                // Normalise guide price for all types that support guide_options
                $guidePrice = 0.0;
                if (!empty($booking['guide_options']) && is_array($booking['guide_options'])) {
                    $go = $booking['guide_options'];
                    $gv = $go['total_price'] ?? $go['cost'] ?? $go['Cost'] ?? $go['sell'] ?? $go['Sell'] ?? 0;
                    if ((float)$gv > 0) {
                        $guidePrice = (float)$gv;
                    }
                }

                switch ($order->type) {
                    case 'attraction':
                        // Attraction base + transfer + guide
                        // For PRO tours, use totalPrice (pax-multiplied) for shared transfers
                        $transferCost = 0.0;
                        if (isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0) {
                            if ($isPro && isset($booking['transfer_options']['totalPrice'])) {
                                $transferCost = (float)$booking['transfer_options']['totalPrice'];
                            } else {
                                $transferCost = (float)$booking['transfer_options']['cost'];
                            }
                        }
                        $sum += $basePrice + $transferCost + $guidePrice;
                        break;

                    case 'restaurant':
                        // Restaurant meal base (mealPrice) + transfer + guide
                        // For PRO tours, use totalPrice (pax-multiplied) for shared transfers
                        $mealBase = (float)($booking['mealPrice'] ?? $booking['totalPrice'] ?? $booking['price'] ?? 0);
                        $transferCost = 0.0;
                        if (isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0) {
                            if ($isPro && isset($booking['transfer_options']['totalPrice'])) {
                                $transferCost = (float)$booking['transfer_options']['totalPrice'];
                            } else {
                                $transferCost = (float)$booking['transfer_options']['cost'];
                            }
                        }
                        $sum += $mealBase + $transferCost + $guidePrice;
                        break;

                    case 'entry_port':
                    case 'exit_port':
                        // Arrival/Departure transfer base (totalPrice) + guide
                        $sum += $basePrice + $guidePrice;
                        break;

                    default:
                        // Other services: just base price
                        $sum += $basePrice;
                        break;
                }
            }
        }
        return $sum;
    }

    /**
     * If Orders total differs from current invoice items subtotal, refresh items from Orders so UI/PDF shows new services.
     */
    private function syncInvoiceItemsFromOrdersIfNeeded($invoice, $tourId, $ordersTotal): void
    {
        try {
            $currentSubtotal = ($invoice->items ?? collect([]))->sum('total_price');
            if (abs((float)$ordersTotal - (float)$currentSubtotal) <= 0.01) {
                return;
            }

            $tour = $invoice->tour ?? Tour::where('tour_id', $tourId)->first();
            if (!$tour) {
                return;
            }

            // Replace items with latest from Orders
            $invoice->items()->delete(); // soft delete
            $this->generateItemsFromTour($invoice, $tour);
            $invoice->load('items');
        } catch (\Exception $e) {
            // Don't block invoice rendering if sync fails
            Log::warning('Invoice items sync failed: ' . $e->getMessage(), ['invoice_id' => $invoice->invoice_id ?? null, 'tour_id' => $tourId]);
        }
    }
}

