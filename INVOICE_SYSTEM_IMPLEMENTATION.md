# Invoice System Implementation Guide

## Overview
Complete invoice workflow system for DMC tour bookings with Proforma and Final Invoice support, including credit notes for cancellations.

## Database Schema

### Tables Created:
1. **invoices** - Main invoice table
2. **invoice_items** - Line items for each invoice
3. **credit_notes** - Credit notes for refunds/cancellations

### Key Features:
- **invoice_id** - Uses CommonHelper::createId() for unique IDs
- **soft deletes** - All tables support soft deletes (deleted_at)
- **JSON fields** - Flexible storage for client details, currency conversion, etc.
- **Status tracking** - Comprehensive status fields for workflow management

## Workflow

### 1. Proforma Invoice (TENTATIVE stage)
- **Generated at:** TENTATIVE tour status
- **Editable:** Yes
- **GST:** No GST applied
- **Accounting:** No accounting entries
- **Purpose:** Price sharing and advance collection
- **Numbering:** PRO-000001 format

### 2. Final Invoice (DEFINITE stage)
- **Generated at:** DEFINITE tour status
- **Editable:** No (immutable)
- **GST:** GST applicable
- **Accounting:** Full accounting entries
- **Purpose:** Tax invoice for compliance
- **Numbering:** INV-YYYY-000001 format (year-based)
- **Conversion:** Created from Proforma (new record, not update)

### 3. Cancellation & Refund
- **Before Final Invoice:** Cancel proforma (soft delete), no accounting impact
- **After Final Invoice:** Generate Credit Note
- **Credit Note:** Linked to Final Invoice, tracks refund status

## Models & Relationships

### Invoice Model
- BelongsTo: Tour, DMC (User), Agent
- HasMany: InvoiceItem, CreditNote
- Scopes: proforma(), final()
- Methods: isEditable(), isFinal()

### InvoiceItem Model
- BelongsTo: Invoice
- Stores service details as JSON

### CreditNote Model
- BelongsTo: Invoice, Tour, DMC
- Tracks refund status and details

## Service Class: InvoiceService

### Key Methods:
1. `generateProformaInvoice($tourId, $data)` - Create proforma at TENTATIVE
2. `convertToFinalInvoice($proformaInvoiceId)` - Convert to final at DEFINITE
3. `updateProformaInvoice($invoiceId, $data)` - Update editable proforma
4. `handleCancellation($tourId)` - Handle cancellation logic
5. `generateCreditNote($invoiceId, $data)` - Create credit note for refunds

## Routes

### Invoice Routes:
- `GET /invoices` - List invoices
- `GET /invoices/{id}` - Show invoice
- `GET /invoices/{id}/edit` - Edit proforma (only)
- `PUT /invoices/{id}` - Update proforma
- `GET /invoices/{id}/download` - Download PDF
- `GET /invoices/{id}/view` - View PDF in browser
- `POST /invoices/tour/{tourId}/generate-proforma` - Generate proforma
- `POST /invoices/{id}/convert-to-final` - Convert to final
- `POST /invoices/tour/{tourId}/handle-cancellation` - Handle cancellation

## Templates

### PDF Templates Created:
1. **proforma.blade.php** - Proforma invoice template
2. **final.blade.php** - Final/Tax invoice template

### Template Features:
- Based on provided invoice structure
- Includes all required fields (client, travel company, service details, etc.)
- Currency conversion display
- Payment terms and bank details (final invoice)
- GST and tax breakdown (final invoice only)

## Integration Points

### Booking Status Workflow:
1. **TENTATIVE** → Generate Proforma Invoice
2. **DEFINITE** → Convert Proforma to Final Invoice
3. **CANCELLED** → Handle cancellation (credit note if final exists)

### Integration with Booking Views:
- Add "Generate Invoice" button in tentative bookings view
- Add "Convert to Final" button in definite bookings view
- Add "Download Invoice" button in invoice listings

## Usage Examples

### Generate Proforma Invoice:
```php
$invoiceService = new InvoiceService();
$invoice = $invoiceService->generateProformaInvoice($tourId, [
    'client_details' => [...],
    'travel_company_details' => [...],
    'items' => [...],
]);
```

### Convert to Final:
```php
$finalInvoice = $invoiceService->convertToFinalInvoice($proformaInvoiceId);
```

### Handle Cancellation:
```php
$creditNote = $invoiceService->handleCancellation($tourId);
```

## Best Practices

1. **Audit Trail:** All invoices maintain created_by, updated_by, timestamps
2. **Soft Deletes:** Never hard delete invoices (audit requirement)
3. **Immutable Final Invoices:** Final invoices cannot be edited (use credit notes)
4. **Unique Invoice Numbers:** Final invoices have unique, year-based numbering
5. **Status Validation:** Service class validates tour status before invoice operations
6. **Transaction Safety:** All invoice operations wrapped in database transactions

## Next Steps

1. Run migrations: `php artisan migrate`
2. Add invoice generation buttons to booking views (tentative, definite)
3. Create invoice listing/index view
4. Create invoice edit form for proforma invoices
5. Integrate with payment tracking system
6. Add email sending functionality for invoices
7. Create credit note PDF template
8. Add invoice reporting/analytics

## Notes

- All invoice IDs use CommonHelper::createId() pattern
- Currency conversion stored as JSON for flexibility
- Service details stored as JSON to accommodate different service types
- Templates match the structure shown in provided images
- GST calculation can be customized in InvoiceService::calculateInvoiceTotals()

