<?php

namespace App\Services\Invoices;

use App\Helpers\CommonHelper;
use App\Models\BankDetail;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Carbon\Carbon;

/**
 * Builds the normalized invoiceData contract for custom Twig templates.
 *
 * Blade PDF views remain the system fallback and continue to receive the
 * Eloquent invoice + currency context unchanged.
 */
class InvoiceDataBuilder
{
    /**
     * @param  array<string, mixed>  $viewData  Controller PDF payload (invoice, currencies, logoType, mode, …)
     * @return array<string, mixed>
     */
    public function forViewData(array $viewData, string $invoiceTemplateType): array
    {
        $invoice = $viewData['invoice'] ?? null;
        if (! $invoice instanceof Invoice) {
            return array_merge($viewData, [
                'invoiceData' => $this->emptyContract($invoiceTemplateType),
            ]);
        }

        $normalized = $this->toNormalizedContract($invoice, $viewData, $invoiceTemplateType);

        return array_merge($viewData, [
            'invoiceData' => $normalized,
        ]);
    }

    /**
     * @param  array<string, mixed>  $viewData
     * @return array<string, mixed>
     */
    public function toNormalizedContract(Invoice $invoice, array $viewData, string $invoiceTemplateType): array
    {
        $logoType = strtolower((string) ($viewData['logoType'] ?? 'dmc'));
        if (! in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }

        $mode = (string) ($viewData['mode'] ?? 'full');
        if (! in_array($mode, ['full', 'price-only'], true)) {
            $mode = 'full';
        }

        $branding = $this->resolveBranding($invoice, $logoType);
        $client = is_array($invoice->client_details) ? $invoice->client_details : [];
        $travelCompany = is_array($invoice->travel_company_details) ? $invoice->travel_company_details : [];
        $notes = is_array($invoice->notes) ? $invoice->notes : [];
        $currencyConversion = is_array($viewData['currencyConversion'] ?? null)
            ? $viewData['currencyConversion']
            : (is_array($invoice->currency_conversion) ? $invoice->currency_conversion : []);

        // DMC / invoice accounting currency vs preview selector (?currency=).
        $baseCurrency = strtoupper(trim((string) (
            $viewData['baseCurrency']
            ?? CommonHelper::resolveInvoiceBaseCurrency($invoice)
            ?? $invoice->base_currency
            ?? 'SGD'
        )));
        $displayCurrency = strtoupper(trim((string) (
            $viewData['selectedCurrency']
            ?? $baseCurrency
        )));
        if ($baseCurrency === '') {
            $baseCurrency = 'SGD';
        }
        if ($displayCurrency === '') {
            $displayCurrency = $baseCurrency;
        }

        $isProforma = ($invoice->invoice_type ?? '') === 'proforma';
        $docNumber = $isProforma
            ? ($invoice->proforma_number ?? null)
            : ($invoice->invoice_number ?? null);
        $docTitle = $isProforma ? 'PROFORMA INVOICE' : 'INVOICE';

        $typeLabels = [
            'hotel' => 'Hotel Services',
            'entry_port' => 'Arrival Services',
            'exit_port' => 'Departure Services',
            'attraction' => 'Attraction Services',
            'restaurant' => 'Restaurant Services',
            'guide' => 'Guide Services',
            'travel_point' => 'Point to Point Transfer',
            'travel_hourly' => 'Hourly Tour Services',
            'local_transport' => 'Local Transport Services',
            'miscellaneous' => 'Miscellaneous',
            'other' => 'Other Services',
        ];

        $items = [];
        $byType = [];
        $bookingCurrencyCounts = [];
        foreach ($invoice->items ?? [] as $item) {
            if (! $item instanceof InvoiceItem) {
                continue;
            }
            $adults = $this->toFloat($item->quantity_adults ?? null) ?? 0.0;
            $children = $this->toFloat($item->quantity_children ?? null) ?? 0.0;
            $infants = $this->toFloat($item->quantity_infants ?? null) ?? 0.0;
            $rawDetails = is_array($item->service_details) ? $item->service_details : [];
            $type = (string) ($item->item_type ?? 'other');
            $type = $type !== '' ? $type : 'other';
            $details = $this->normalizeServiceDetails($rawDetails, $type);
            $country = trim((string) ($details['country'] ?? ''));
            $city = trim((string) ($details['city'] ?? ''));
            $bookingCurrency = strtoupper(trim((string) ($details['currency'] ?? '')));
            if ($bookingCurrency === '') {
                $bookingCurrency = $baseCurrency;
            }
            $bookingCurrencyCounts[$bookingCurrency] = ($bookingCurrencyCounts[$bookingCurrency] ?? 0) + 1;
            $bookingDate = $this->resolveItemBookingDate($details, $type);

            $nativeUnit = $this->toFloat($item->unit_price ?? null) ?? 0.0;
            $nativeTotal = $this->toFloat($item->total_price ?? null) ?? 0.0;
            // Line items are stored in booking currency (e.g. SGD); convert to display (DMC base by default).
            $displayUnit = CommonHelper::convertInvoiceAmountToSelected(
                $nativeUnit,
                $bookingCurrency,
                $displayCurrency,
                $baseCurrency
            );
            $displayTotal = CommonHelper::convertInvoiceAmountToSelected(
                $nativeTotal,
                $bookingCurrency,
                $displayCurrency,
                $baseCurrency
            );

            $row = [
                'id' => $item->id ?? null,
                'item_type' => $type,
                'description' => $item->description ?? null,
                'service_details' => $details,
                // Precomputed lines for Twig — never poke missing JSON keys under strict_variables.
                'detail_lines' => $this->buildItemDetailLines($type, $details),
                'quantity_adults' => $adults,
                'quantity_children' => $children,
                'quantity_infants' => $infants,
                'quantity' => $adults + $children + $infants,
                // Display amounts (match Amount column currency).
                'unit_price' => $displayUnit,
                'total_price' => $displayTotal,
                // Native booking amounts as stored on invoice_items.
                'unit_price_native' => $nativeUnit,
                'total_price_native' => $nativeTotal,
                'currency_native' => $bookingCurrency,
                'booking_currency' => $bookingCurrency,
                'display_order' => $item->display_order ?? null,
                'booking_date' => $bookingDate,
                'booking_date_display' => $this->formatDisplayDate($bookingDate),
                'country' => $country !== '' ? $country : null,
                'city' => $city !== '' ? $city : null,
                // Label currency for the converted amount shown in templates.
                'currency' => $displayCurrency,
                'geo_label' => $this->formatGeoLabel($city, $country),
            ];
            $items[] = $row;
            $byType[$row['item_type']][] = $row;
        }

        arsort($bookingCurrencyCounts);
        $bookingCurrency = $bookingCurrencyCounts !== []
            ? (string) array_key_first($bookingCurrencyCounts)
            : $baseCurrency;

        $multiGeo = is_array($viewData['invoiceMultiGeo'] ?? null)
            ? $viewData['invoiceMultiGeo']
            : CommonHelper::detectInvoiceMultiGeo($invoice);
        $isMulti = (bool) ($multiGeo['is_multi'] ?? false);
        if (! $isMulti) {
            // Also treat as multi when line items span more than one city/country.
            $isMulti = $this->itemsSpanMultipleGeos($items);
        }
        $byCity = $this->groupItemsByCity($items, $typeLabels, $displayCurrency);

        $banks = $this->resolveBankDetails($invoice);
        $paymentTerms = $this->resolvePaymentTerms($invoice, $branding, $banks);

        // Invoice header totals are stored in DMC base currency — convert when preview currency differs.
        $subtotalBase = $this->toFloat($invoice->subtotal ?? null) ?? 0.0;
        $gstBase = $this->toFloat($invoice->gst_amount ?? null) ?? 0.0;
        $serviceChargeBase = $this->toFloat($invoice->service_charge ?? null) ?? 0.0;
        $touristTaxBase = $this->toFloat($invoice->tourist_tax ?? null) ?? 0.0;
        $totalBase = $this->toFloat($invoice->total_amount ?? null) ?? 0.0;
        $paidBase = $this->toFloat($invoice->payment_received ?? null) ?? 0.0;
        $outstandingBase = $this->toFloat($invoice->outstanding_balance ?? null) ?? 0.0;
        $baseAmountBase = $this->toFloat($notes['base_amount'] ?? null);
        $negotiatedBase = $this->toFloat($notes['negotiated_amount'] ?? null);
        $ordersTotalBase = $this->toFloat($notes['orders_total'] ?? null);
        $discountFixedBase = $this->toFloat($notes['discount_fixed'] ?? null);

        $toDisplay = static function (?float $amount) use ($baseCurrency, $displayCurrency): ?float {
            if ($amount === null) {
                return null;
            }

            return CommonHelper::convertInvoiceAmountToSelected(
                $amount,
                $baseCurrency,
                $displayCurrency,
                $baseCurrency
            );
        };

        return [
            'invoice' => [
                'id' => $invoice->invoice_id ?? null,
                'type' => $invoice->invoice_type ?? null,
                'template_type' => $invoiceTemplateType,
                'status' => $invoice->status ?? null,
                'number' => $docNumber,
                'doc_title' => $docTitle,
                'invoice_number' => $invoice->invoice_number ?? null,
                'proforma_number' => $invoice->proforma_number ?? null,
                'invoice_date' => $this->toDateString($invoice->invoice_date ?? null),
                'invoice_date_display' => $this->formatDisplayDate($invoice->invoice_date ?? null),
                'due_date' => $this->toDateString($invoice->due_date ?? null),
                'due_date_display' => $this->formatDisplayDate($invoice->due_date ?? null),
                'validity_date' => $this->toDateString($invoice->validity_date ?? null),
                'validity_date_display' => $this->formatDisplayDate($invoice->validity_date ?? null),
                'sent_by' => $invoice->sent_by ?? null,
                'mode' => $mode,
            ],
            'branding' => $branding,
            'client' => [
                'lead_guest_name' => $client['lead_guest_name'] ?? null,
                'booking_id' => $client['booking_id'] ?? null,
                'address' => $client['address'] ?? null,
                'city' => $client['city'] ?? null,
                'country' => $client['country'] ?? null,
                'postal_code' => $client['postal_code'] ?? null,
                'email' => $client['email'] ?? null,
                'phone' => $client['phone'] ?? null,
                'raw' => $client,
            ],
            'travel_company' => [
                'name' => $travelCompany['name'] ?? null,
                'company_name' => $travelCompany['company_name'] ?? null,
                'address' => $travelCompany['address'] ?? null,
                'contact_person' => $travelCompany['contact_person'] ?? null,
                'phone' => $travelCompany['phone'] ?? null,
                'email' => $travelCompany['email'] ?? null,
                'raw' => $travelCompany,
            ],
            'travel' => [
                'destination' => $invoice->destination ?? null,
                'from_date' => $this->toDateString($invoice->travel_from_date ?? null),
                'from_date_display' => $this->formatDisplayDate($invoice->travel_from_date ?? null),
                'to_date' => $this->toDateString($invoice->travel_to_date ?? null),
                'to_date_display' => $this->formatDisplayDate($invoice->travel_to_date ?? null),
                'duration_days' => $invoice->duration_days ?? null,
                'adults' => $invoice->no_of_adults ?? null,
                'children' => $invoice->no_of_children ?? null,
                'infants' => $invoice->no_of_infants ?? null,
                'is_multi_city' => $isMulti,
                'cities' => array_values($multiGeo['cities'] ?? []),
                'countries' => array_values($multiGeo['countries'] ?? []),
            ],
            'items' => $items,
            'services' => [
                'is_multi' => $isMulti,
                'by_type' => $byType,
                'by_city' => $byCity,
                'type_labels' => $typeLabels,
            ],
            'pricing' => [
                // DMC accounting currency (invoice.base_currency / company currency).
                'base_currency' => $baseCurrency,
                // Currency shown on the PDF (query ?currency= or DMC base).
                'display_currency' => $displayCurrency,
                // Most common booking currency on line items (e.g. SGD for a Singapore tour).
                'booking_currency' => $bookingCurrency,
                'exchange_rate' => $this->toFloat($viewData['exchangeRate'] ?? 1),
                'currency_conversion' => $currencyConversion,
                // Totals below are in display_currency (converted from base when needed).
                'subtotal' => $toDisplay($subtotalBase),
                'gst_amount' => $toDisplay($gstBase),
                'service_charge' => $toDisplay($serviceChargeBase),
                'tourist_tax' => $toDisplay($touristTaxBase),
                'total_amount' => $toDisplay($totalBase),
                'payment_received' => $toDisplay($paidBase),
                'outstanding_balance' => $toDisplay($outstandingBase),
                'base_amount' => $baseAmountBase !== null ? $toDisplay($baseAmountBase) : null,
                'negotiated_amount' => $negotiatedBase !== null ? $toDisplay($negotiatedBase) : null,
                'orders_total' => $ordersTotalBase !== null ? $toDisplay($ordersTotalBase) : null,
                'discount_fixed' => $discountFixedBase !== null ? $toDisplay($discountFixedBase) : null,
                'tax_breakdown' => is_array($notes['tax_breakdown'] ?? null) ? $notes['tax_breakdown'] : [],
            ],
            'bank_details' => $banks,
            'payment_terms' => $paymentTerms,
            'terms_and_conditions' => $invoice->terms_and_conditions ?? null,
            'notes' => $notes,
            'tour' => [
                'id' => $invoice->tour_id ?? null,
                'status' => $invoice->tour->tour_status ?? null,
            ],
            'presentation' => [
                'logoType' => $logoType,
                'mode' => $mode,
                'is_third_party' => (bool) ($viewData['isThirdPartyInvoice'] ?? false),
                'is_multi_city' => $isMulti,
                'multi_geo' => [
                    'is_multi' => $isMulti,
                    'countries' => array_values($multiGeo['countries'] ?? []),
                    'cities' => array_values($multiGeo['cities'] ?? []),
                    'currencies' => array_values($multiGeo['currencies'] ?? []),
                ],
            ],
            'template' => [
                'type' => $invoiceTemplateType,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyContract(string $invoiceTemplateType): array
    {
        return [
            'invoice' => ['template_type' => $invoiceTemplateType, 'doc_title' => 'INVOICE'],
            'branding' => [],
            'client' => [],
            'travel_company' => [],
            'travel' => [],
            'items' => [],
            'services' => ['is_multi' => false, 'by_type' => [], 'by_city' => [], 'type_labels' => []],
            'pricing' => [],
            'bank_details' => [],
            'payment_terms' => [],
            'terms_and_conditions' => null,
            'notes' => [],
            'tour' => [],
            'presentation' => [],
            'template' => ['type' => $invoiceTemplateType],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resolveBankDetails(Invoice $invoice): array
    {
        $dmcId = (int) ($invoice->dmc_id ?? $invoice->tour->dmc_id ?? 0);
        if ($dmcId > 0) {
            $rows = BankDetail::query()
                ->where('dmc_id', $dmcId)
                ->where('is_active', 1)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get();

            if ($rows->isNotEmpty()) {
                $out = [];
                foreach ($rows as $row) {
                    $india = is_array($row->india_bank_details ?? null) ? $row->india_bank_details : [];
                $out[] = [
                    'bank_name' => $india['bank_name'] ?? ($row->bank_type ?? null),
                    'account_name' => $row->account_name ?? null,
                    'account_number' => $row->account_number ?? null,
                    'swift_code' => $row->swift_bic_iban ?? ($india['swift'] ?? null),
                    'branch' => $row->bank_address ?? ($india['branch'] ?? null),
                    'bank_code' => $row->bank_code ?? null,
                    'branch_code' => $row->branch_code ?? null,
                    'ifsc' => $row->ifsc ?? null,
                    'aba_routing' => $row->aba_routing ?? null,
                    'currency' => $india['currency'] ?? null,
                    'payment_terms' => is_array($row->payment_terms ?? null) ? $row->payment_terms : [],
                ];
                }

                return $out;
            }
        }

        return is_array($invoice->bank_details) ? array_values($invoice->bank_details) : [];
    }

    /**
     * @param  array<string, mixed>  $branding
     * @param  list<array<string, mixed>>  $banks
     * @return list<string>
     */
    private function resolvePaymentTerms(Invoice $invoice, array $branding, array $banks): array
    {
        if (! empty($banks[0]['payment_terms']) && is_array($banks[0]['payment_terms'])) {
            return array_values(array_filter(array_map('strval', $banks[0]['payment_terms'])));
        }

        if (is_array($invoice->payment_terms) && $invoice->payment_terms !== []) {
            return array_values(array_filter(array_map('strval', $invoice->payment_terms)));
        }

        $company = (string) ($branding['company_name'] ?? 'DMC');
        $email = (string) ($branding['email'] ?? 'accounts@example.com');

        return [
            'Please pay the amount before the payment due date to avoid auto release of booking. Bank details are mentioned below.',
            'Payment/Remittance to be made in the currency as stated in the invoice.',
            'Bank charges to be borne by remitter. Please ensure that '.$company.' receive full payment as per Invoice.',
            'To ensure prompt credit, please send payment details along with remittance advice at '.$email.'.',
            'Interest @ 18% will be charged on all overdues.',
            'This document is not a voucher. Voucher will be issued after the receipt of full monies & necessary documents.',
        ];
    }

    private function formatDisplayDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('jS M Y');
        } catch (\Throwable) {
            return is_string($value) ? $value : null;
        }
    }

    /**
     * Resolve service date from the JSON keys actually stored per item type.
     *
     * @param  array<string, mixed>  $details
     */
    private function resolveItemBookingDate(array $details, string $itemType): ?string
    {
        $candidates = match ($itemType) {
            'entry_port', 'local_transport', 'travel_point', 'travel_hourly' => [
                'pickup_date', 'pickupdate', 'booking_date', 'bookingDate', 'date',
            ],
            'exit_port' => [
                'exitpickupdate', 'pickup_date', 'pickupdate', 'booking_date', 'bookingDate', 'date',
            ],
            'hotel' => [
                'check_in_date', 'check_in', 'booking_date', 'date',
            ],
            default => [
                'booking_date', 'bookingDate', 'date', 'pickup_date', 'pickupdate',
                'exitpickupdate', 'check_in_date', 'check_in',
            ],
        };

        foreach ($candidates as $key) {
            $value = $details[$key] ?? null;
            if ($value === null || $value === '') {
                continue;
            }
            if (is_string($value) || is_numeric($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * Ensure every item's service_details has the same optional keys (null when absent)
     * so sandboxed Twig strict_variables never fails across hotel/restaurant/attraction/etc.
     *
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    private function normalizeServiceDetails(array $details, string $itemType): array
    {
        $defaults = [
            'country' => null,
            'city' => null,
            'currency' => null,
            'booking_date' => null,
            'date' => null,
            'location' => null,
            // Hotel
            'hotel_name' => null,
            'room_category' => null,
            'check_in' => null,
            'check_in_date' => null,
            'check_in_time' => null,
            'check_out' => null,
            'check_out_date' => null,
            'check_out_time' => null,
            'no_of_days' => null,
            'total_pax' => null,
            'confirmation' => null,
            'child_with_bed' => null,
            'child_without_bed' => null,
            // Restaurant / attraction meal
            'restaurant_name' => null,
            'attraction_name' => null,
            'meal_type' => null,
            'meal_type_label' => null,
            'meal_specific_type' => null,
            'visit_time' => null,
            // Transfer
            'transfer_required' => null,
            'transfer_type' => null,
            'transfer_way' => null,
            'transfer_cost' => null,
            'vehicle_name' => null,
            'vehicle_type' => null,
            'vehicle_details' => null,
            // Guide
            'guide_name' => null,
            'guide_hours' => null,
            'guide_total_price' => null,
            'guide_options' => null,
            // Port / transport (arrival / departure / local transfer / point / hourly)
            'port_name' => null,
            'pickup_point' => null,
            'drop_point' => null,
            'pickup_date' => null,
            'pickupdate' => null,
            'exitpickupdate' => null,
            'entrypickup' => null,
            'entrydropoff' => null,
            'exitpickup' => null,
            'exitdropoff' => null,
            'entrytime' => null,
            'vehicle' => null,
            'language' => null,
            'package_hours' => null,
            'selectedHours' => null,
            'adult_count' => null,
            'child_count' => null,
            'infant_count' => null,
            'adults' => null,
            'children' => null,
            'infants' => null,
            'restaurant_base_price' => null,
            'attraction_base_price' => null,
        ];

        $merged = array_merge($defaults, $details);

        // Flatten nested guide_options when top-level guide fields are empty.
        $guideOptions = is_array($merged['guide_options'] ?? null) ? $merged['guide_options'] : [];
        if (($merged['guide_name'] === null || $merged['guide_name'] === '') && ! empty($guideOptions['guide_name'])) {
            $merged['guide_name'] = $guideOptions['guide_name'];
        }
        if (($merged['guide_hours'] === null || $merged['guide_hours'] === '') && isset($guideOptions['hours'])) {
            $merged['guide_hours'] = $guideOptions['hours'];
        } elseif (($merged['guide_hours'] === null || $merged['guide_hours'] === '') && isset($guideOptions['package_hours'])) {
            $merged['guide_hours'] = $guideOptions['package_hours'];
        }
        if (($merged['guide_total_price'] === null || $merged['guide_total_price'] === '') && isset($guideOptions['total_price'])) {
            $merged['guide_total_price'] = $guideOptions['total_price'];
        }

        // Keep item_type hint for templates that branch on it.
        $merged['item_type'] = $itemType;

        return $merged;
    }

    /**
     * Build human-readable detail lines from service JSON (same idea as Blade PDF rows).
     *
     * @param  array<string, mixed>  $details
     * @return list<string>
     */
    private function buildItemDetailLines(string $itemType, array $details): array
    {
        $lines = [];

        $push = static function (?string $label, mixed $value) use (&$lines): void {
            if ($value === null || $value === '' || $value === false) {
                return;
            }
            if (is_bool($value)) {
                $value = $value ? 'Yes' : 'No';
            }
            if (is_array($value)) {
                return;
            }
            $text = trim((string) $value);
            if ($text === '') {
                return;
            }
            $lines[] = $label !== null && $label !== '' ? $label.': '.$text : $text;
        };

        if ($itemType === 'hotel') {
            $push('Room', $details['room_category'] ?? null);
            $checkIn = $this->formatDisplayDate($details['check_in_date'] ?? $details['check_in'] ?? null);
            $checkOut = $this->formatDisplayDate($details['check_out_date'] ?? $details['check_out'] ?? null);
            if ($checkIn || $checkOut) {
                $lines[] = 'Stay: '.($checkIn ?? '—').' → '.($checkOut ?? '—');
            }
            $push('Nights', $details['no_of_days'] ?? null);
            $push('Location', $details['location'] ?? null);
            $push('Confirmation', $details['confirmation'] ?? null);
        } elseif ($itemType === 'restaurant') {
            $meal = $details['meal_type_label'] ?? $details['meal_type'] ?? null;
            if ($meal) {
                $specific = trim((string) ($details['meal_specific_type'] ?? ''));
                $lines[] = 'Meal: '.$meal.($specific !== '' ? ' ('.$specific.')' : '');
            }
            $push('Visit time', $details['visit_time'] ?? null);
        } elseif ($itemType === 'attraction') {
            $push('Visit time', $details['visit_time'] ?? null);
            $meal = $details['meal_type_label'] ?? $details['meal_type'] ?? null;
            if ($meal) {
                $lines[] = 'Meal: '.$meal;
            }
        } elseif (in_array($itemType, ['entry_port', 'exit_port'], true)) {
            $push('Pickup', $details['entrypickup'] ?? ($details['exitpickup'] ?? ($details['pickup_point'] ?? null)));
            $push('Drop', $details['entrydropoff'] ?? ($details['exitdropoff'] ?? ($details['drop_point'] ?? null)));
            $push('Time', $details['entrytime'] ?? null);
            $push('Vehicle', $details['vehicle_name'] ?? ($details['vehicle_type'] ?? null));
        } elseif (in_array($itemType, ['travel_point', 'travel_hourly', 'local_transport'], true)) {
            $push('Vehicle', $details['vehicle_name'] ?? ($details['vehicle'] ?? ($details['vehicle_type'] ?? null)));
            $push('Pickup', $details['entrypickup'] ?? ($details['pickup_point'] ?? null));
            $push('Drop', $details['entrydropoff'] ?? ($details['drop_point'] ?? null));
            $push('Time', $details['entrytime'] ?? null);
            $push('Hours', $details['selectedHours'] ?? ($details['package_hours'] ?? ($details['guide_hours'] ?? null)));
        } elseif ($itemType === 'guide') {
            $push('Language', $details['language'] ?? null);
            $push('Hours', $details['package_hours'] ?? ($details['guide_hours'] ?? null));
        }

        $transferRequired = $details['transfer_required'] ?? null;
        $hasTransfer = $transferRequired === true
            || $transferRequired === 1
            || $transferRequired === '1'
            || (is_string($transferRequired) && in_array(strtolower($transferRequired), ['yes', 'true'], true));
        if ($hasTransfer || ! empty($details['transfer_type']) || ! empty($details['transfer_way'])) {
            $parts = array_filter([
                trim((string) ($details['transfer_type'] ?? '')),
                trim((string) ($details['transfer_way'] ?? '')),
            ], static fn ($v) => $v !== '');
            $lines[] = 'Transfer: '.($parts !== [] ? implode(' ', $parts) : 'Yes');
            $push('Vehicle', $details['vehicle_name'] ?? ($details['vehicle_details'] ?? null));
        }

        if (! empty($details['guide_name'])) {
            $guide = 'Guide: '.$details['guide_name'];
            if ($details['guide_hours'] !== null && $details['guide_hours'] !== '') {
                $guide .= ' ('.$details['guide_hours'].'h)';
            }
            $lines[] = $guide;
        }

        return array_values(array_unique($lines));
    }

    private function formatGeoLabel(?string $city, ?string $country): string
    {
        $city = trim((string) $city);
        $country = trim((string) $country);

        if ($city !== '' && $country !== '') {
            return $city.', '.$country;
        }
        if ($city !== '') {
            return $city;
        }
        if ($country !== '') {
            return $country;
        }

        return 'Unspecified location';
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function itemsSpanMultipleGeos(array $items): bool
    {
        $cities = [];
        $countries = [];
        foreach ($items as $item) {
            $city = trim((string) ($item['city'] ?? ''));
            $country = trim((string) ($item['country'] ?? ''));
            if ($city !== '') {
                $cities[mb_strtolower($city)] = true;
            }
            if ($country !== '') {
                $countries[mb_strtolower($country)] = true;
            }
        }

        return count($cities) > 1 || count($countries) > 1;
    }

    /**
     * Group line items by city/country for multi-city invoice templates.
     * Amounts are already converted to $displayCurrency; do not split groups by booking currency.
     *
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, string>  $typeLabels
     * @return list<array<string, mixed>>
     */
    private function groupItemsByCity(array $items, array $typeLabels, string $displayCurrency = ''): array
    {
        $displayCurrency = strtoupper(trim($displayCurrency));
        $groups = [];
        foreach ($items as $item) {
            $city = trim((string) ($item['city'] ?? ''));
            $country = trim((string) ($item['country'] ?? ''));
            $key = mb_strtolower($country.'|'.$city);
            if ($key === '|') {
                $key = 'unspecified';
            }

            if (! isset($groups[$key])) {
                $label = $this->formatGeoLabel($city !== '' ? $city : null, $country !== '' ? $country : null);
                $groups[$key] = [
                    'key' => $key,
                    'label' => $label,
                    'city' => $city !== '' ? $city : null,
                    'country' => $country !== '' ? $country : null,
                    'currency' => $displayCurrency !== ''
                        ? $displayCurrency
                        : (strtoupper(trim((string) ($item['currency'] ?? ''))) ?: null),
                    'items' => [],
                    'by_type' => [],
                    'subtotal' => 0.0,
                    'item_count' => 0,
                ];
            }

            $type = (string) ($item['item_type'] ?? 'other');
            $groups[$key]['items'][] = $item;
            $groups[$key]['by_type'][$type][] = $item;
            $groups[$key]['subtotal'] += (float) ($item['total_price'] ?? 0);
            $groups[$key]['item_count']++;
        }

        // Prefer a stable display order: country then city.
        uasort($groups, static function (array $a, array $b): int {
            return strcasecmp(
                trim(($a['country'] ?? '').' '.($a['city'] ?? '')),
                trim(($b['country'] ?? '').' '.($b['city'] ?? ''))
            );
        });

        $out = [];
        foreach ($groups as $group) {
            $sections = [];
            foreach ($group['by_type'] as $type => $typeItems) {
                $sections[] = [
                    'item_type' => $type,
                    'title' => ($typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)))
                        .' — '.$group['label'],
                    'title_short' => $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)),
                    'items' => $typeItems,
                ];
            }
            $group['sections'] = $sections;
            $out[] = $group;
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveBranding(Invoice $invoice, string $logoType): array
    {
        if ($logoType === 'agency' && $invoice->agent && $invoice->agent->agency) {
            $agency = $invoice->agent->agency;
            $logo = $agency->logo ?? null;

            return [
                'mode' => 'agency',
                'company_name' => $agency->agency_name
                    ?? ($invoice->travel_company_details['company_name'] ?? null),
                'logo' => $this->logoToDataUri(is_string($logo) ? $logo : null),
                'address' => $agency->address ?? null,
                'phone' => $agency->phone ?? null,
                'email' => $agency->email ?? null,
                'company_reg_no' => null,
                'licence_no' => null,
            ];
        }

        $dmcUser = $invoice->dmc;
        $rootDmc = $dmcUser;
        $visited = [];
        while (
            $rootDmc
            && (int) ($rootDmc->role_id ?? 0) !== 11
            && $rootDmc->created_by
            && ! in_array($rootDmc->created_by, $visited, true)
        ) {
            $visited[] = $rootDmc->created_by;
            $rootDmc = User::query()->where('userId', $rootDmc->created_by)->first();
        }
        if (! $rootDmc) {
            $rootDmc = $dmcUser;
        }

        if (! $rootDmc && ! $dmcUser) {
            return [
                'mode' => 'dmc',
                'company_name' => null,
                'logo' => null,
                'address' => null,
                'phone' => null,
                'email' => null,
                'company_reg_no' => null,
                'licence_no' => null,
            ];
        }

        $logo = $rootDmc->logo ?? $dmcUser->logo ?? null;
        $reg = trim((string) ($rootDmc->company_reg_no ?? ''));
        if ($reg === '' && $dmcUser) {
            $reg = trim((string) ($dmcUser->company_reg_no ?? ''));
        }
        $lic = $rootDmc->ta_licence_no ?? $rootDmc->licence_no
            ?? $dmcUser->ta_licence_no ?? $dmcUser->licence_no ?? null;

        return [
            'mode' => 'dmc',
            'company_name' => $rootDmc->company_name ?? $dmcUser->company_name ?? null,
            'logo' => $this->logoToDataUri(is_string($logo) ? $logo : null),
            'address' => $rootDmc->company_address
                ?? $rootDmc->address
                ?? $dmcUser->company_address
                ?? $dmcUser->address
                ?? null,
            'phone' => $rootDmc->company_phone
                ?? $rootDmc->phone
                ?? $rootDmc->mobile
                ?? $dmcUser->company_phone
                ?? $dmcUser->phone
                ?? $dmcUser->mobile
                ?? null,
            'email' => $rootDmc->company_email
                ?? $rootDmc->email
                ?? $dmcUser->company_email
                ?? $dmcUser->email
                ?? null,
            'company_reg_no' => $reg !== '' ? $reg : null,
            'licence_no' => ($lic !== null && trim((string) $lic) !== '') ? trim((string) $lic) : null,
        ];
    }

    private function logoToDataUri(?string $logo): ?string
    {
        if ($logo === null || trim($logo) === '') {
            return null;
        }

        try {
            if (preg_match('/^data:image\/(png|jpe?g|gif);base64,/i', $logo)) {
                return $logo;
            }

            // Stored data-URI with wrong/unknown MIME — re-detect from bytes.
            if (preg_match('/^data:image\/[^;]+;base64,(.+)$/is', $logo, $m)) {
                $content = base64_decode($m[1], true);
                if ($content === false || $content === '') {
                    return null;
                }

                return $this->bytesToImageDataUri($content);
            }

            $content = preg_match('/^https?:\/\//i', $logo)
                ? @file_get_contents($logo)
                : @file_get_contents(public_path(ltrim($logo, '/')));

            if ($content) {
                return $this->bytesToImageDataUri($content);
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private function bytesToImageDataUri(string $content): ?string
    {
        $info = @getimagesizefromstring($content);
        if (! is_array($info) || empty($info['mime'])) {
            return null;
        }

        $mime = strtolower((string) $info['mime']);
        if (! in_array($mime, ['image/png', 'image/jpeg', 'image/gif'], true)) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($content);
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function toDateString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_string($value)) {
            return $value;
        }

        return null;
    }
}
