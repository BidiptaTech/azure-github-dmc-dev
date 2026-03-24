<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\CreditNote;
use App\Models\Tour;
use App\Models\User;
use App\Models\Country;
use App\Services\InvoiceService;
use App\Helpers\CommonHelper;
use App\Helpers\CurrencyHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Crypt;

class InvoiceController extends Controller
{
    protected $invoiceService;

    /** Available currencies for invoice (same as quotation) */
    protected $availableCurrencies = [
        'SGD', 'USD', 'EUR', 'GBP', 'INR', 'AUD', 'NZD', 'CAD', 'CHF', 'JPY', 'CNY',
        'HKD', 'TWD', 'KRW', 'THB', 'MYR', 'IDR', 'PHP', 'VND', 'AED', 'SAR', 'QAR',
        'KWD', 'BHD', 'OMR', 'ZAR', 'NGN', 'EGP', 'KES', 'GHS', 'MAD', 'BRL', 'ARS',
        'CLP', 'COP', 'PEN', 'MXN', 'RUB', 'UAH', 'TRY', 'ILS', 'PLN', 'CZK', 'HUF',
        'RON', 'SEK', 'NOK', 'DKK', 'ISK', 'BGN', 'HRK', 'PKR', 'LKR', 'BDT', 'MVR',
        'KZT', 'DOP', 'JMD',
    ];

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Get selected currency from request, validate against available list.
     */
    protected function getSelectedCurrency(Request $request): string
    {
        $currentUser = Auth::user();
        $dmcId = CommonHelper::getDmcId($currentUser);
        $dmc = User::select('country')->where('userId', $dmcId)->first();
        $country = $dmc ? Country::select('currency')->where('name', $dmc->country)->first() : null;
        $currencyRaw = $country ? $country->currency : null;
        $defaultCurrency = CurrencyHelper::normalizeCurrencyToCode($currencyRaw, $this->availableCurrencies, 'SGD');
        $selected = strtoupper($request->query('currency', $defaultCurrency));
        return in_array($selected, $this->availableCurrencies, true) ? $selected : $defaultCurrency;
    }

    /**
     * Build currency conversion data for display.
     * Base amount in SGD; when selectedCurrency !== SGD, add converted amount.
     * Returns ['SGD' => amount] or ['SGD' => amount, 'INR' => convertedAmount] etc.
     */
    protected function buildCurrencyConversion(Invoice $invoice, string $selectedCurrency): array
    {
        $baseCurrency = strtoupper($invoice->base_currency ?? 'SGD');
        $tour = $invoice->tour;
        $tourStatus = $tour->tour_status ?? '';
        $statusesWithTax = ['Confirmed', 'Definite', 'Actual'];
        $shouldShowTax = in_array($tourStatus, $statusesWithTax);

        $notes = is_string($invoice->notes) ? json_decode($invoice->notes, true) : ($invoice->notes ?? []);
        $baseAmount = $notes['base_amount'] ?? ($invoice->getNegotiatedAmount() ?? ($invoice->total_amount ?? 0));
        $gstAmount = $invoice->gst_amount ?? 0;
        $finalPrice = $baseAmount + $gstAmount;
        $outstandingBalance = $invoice->outstanding_balance ?? 0;

        $amountInSgd = $shouldShowTax ? (float) $outstandingBalance : (float) $finalPrice;
        $conversion = ['SGD' => $amountInSgd];

        if ($selectedCurrency !== 'SGD') {
            $converted = CurrencyHelper::convertAmount($amountInSgd, 'SGD', $selectedCurrency);
            if ($converted !== null) {
                $conversion[$selectedCurrency] = $converted;
            }
        }

        return $conversion;
    }

    /**
     * Get exchange rate from SGD to selected currency (for dual-currency display).
     * Returns 1.0 when selectedCurrency is SGD.
     */
    protected function getExchangeRate(string $selectedCurrency, array $currencyConversion): float
    {
        if ($selectedCurrency === 'SGD') {
            return 1.0;
        }
        $sgdAmount = $currencyConversion['SGD'] ?? 0;
        $convertedAmount = $currencyConversion[$selectedCurrency] ?? null;
        if ($sgdAmount > 0 && $convertedAmount !== null && $convertedAmount > 0) {
            return (float) $convertedAmount / (float) $sgdAmount;
        }
        $rate = CurrencyHelper::getExchangeRate('SGD', $selectedCurrency);
        return ($rate !== null && $rate > 0) ? (float) $rate : 1.0;
    }

    /**
     * Resolve Blade view for invoice PDF (standard layout vs travel-agent / alternate layout).
     *
     * @param  string  $invoiceType  proforma|final
     * @param  string  $mode  full|price-only
     * @param  string|null  $format  standard|alternate
     */
    protected function resolveInvoicePdfViewName(string $invoiceType, string $mode, ?string $format): string
    {
        $format = $format ?? 'standard';
        if ($format === 'alternate') {
            return 'invoices.pdf.alternate';
        }
        if (!in_array($mode, ['full', 'price-only'], true)) {
            $mode = 'full';
        }
        if ($mode === 'price-only') {
            return $invoiceType === 'proforma' ? 'invoices.pdf.proforma-price-only' : 'invoices.pdf.final-price-only';
        }

        return $invoiceType === 'proforma' ? 'invoices.pdf.proforma' : 'invoices.pdf.final';
    }

    /**
     * Display listing of invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['tour', 'agent', 'dmc'])
            ->whereNull('deleted_at');

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('invoice_type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by tour_id
        if ($request->has('tour_id') && $request->tour_id) {
            $query->where('tour_id', $request->tour_id);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Generate/Create Proforma Invoice for a tour
     */
    public function generateProforma(Request $request, $tourId)
    {
        try {
            // Status validation is done in the service
            $invoice = $this->invoiceService->generateProformaInvoice($tourId, $request->all());

            return redirect()->route('invoices.show', Crypt::encrypt($invoice->invoice_id))
                ->with('success', 'Proforma invoice generated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Generate/Create Final Invoice for a tour (Definite/Actual/Confirmed stage)
     */
    public function generateFinal(Request $request, $tourId)
    {
        try {
            $invoice = $this->invoiceService->generateFinalInvoiceForTour($tourId, $request->all());

            return redirect()->route('invoices.show', Crypt::encrypt($invoice->invoice_id))
                ->with('success', 'Final invoice generated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show invoice details
     */
    public function show(Request $request, $invoiceId)
    {
        // Try to decrypt if encrypted, otherwise use as-is
        try {
            $decryptedId = Crypt::decrypt($invoiceId);
        } catch (\Exception $e) {
            $decryptedId = $invoiceId;
        }
        
        $invoice = Invoice::with(['tour', 'agent', 'dmc', 'items'])
            ->where('invoice_id', $decryptedId)
            ->firstOrFail();

        // Recalculate totals to ensure latest payment data and taxes are included
        $this->invoiceService->recalculateInvoiceTotals($invoice);
        $invoice->refresh();

        $selectedCurrency = $this->getSelectedCurrency($request);
        $currencyConversion = $this->buildCurrencyConversion($invoice, $selectedCurrency);

        return view('invoices.show', [
            'invoice' => $invoice,
            'selectedCurrency' => $selectedCurrency,
            'availableCurrencies' => $this->availableCurrencies,
            'currencyConversion' => $currencyConversion,
        ]);
    }

    /**
     * Edit Proforma Invoice (only proforma invoices are editable)
     */
    public function edit($invoiceId)
    {
        // Try to decrypt if encrypted, otherwise use as-is
        try {
            $decryptedId = Crypt::decrypt($invoiceId);
        } catch (\Exception $e) {
            $decryptedId = $invoiceId;
        }
        
        $invoice = Invoice::with(['tour', 'agent', 'dmc', 'items'])
            ->where('invoice_id', $decryptedId)
            ->firstOrFail();

        if (!$invoice->isEditable()) {
            return back()->with('error', 'Only proforma invoices can be edited');
        }

        return view('invoices.edit', compact('invoice'));
    }

    /**
     * Update Proforma Invoice
     */
    public function update(Request $request, $invoiceId)
    {
        try {
            // Try to decrypt if encrypted, otherwise use as-is
            try {
                $decryptedId = Crypt::decrypt($invoiceId);
            } catch (\Exception $e) {
                $decryptedId = $invoiceId;
            }
            
            $invoice = $this->invoiceService->updateProformaInvoice($decryptedId, $request->all());

            return redirect()->route('invoices.show', Crypt::encrypt($invoice->invoice_id))
                ->with('success', 'Proforma invoice updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Convert Proforma to Final Invoice (at DEFINITE stage)
     */
    public function convertToFinal(Request $request, $invoiceId)
    {
        try {
            // Try to decrypt if encrypted, otherwise use as-is
            try {
                $decryptedId = Crypt::decrypt($invoiceId);
            } catch (\Exception $e) {
                $decryptedId = $invoiceId;
            }
            
            $invoice = Invoice::where('invoice_id', $decryptedId)->first();
            if (!$invoice) {
                return back()->with('error', 'Invoice not found');
            }

            $tour = $invoice->tour;
            if ($tour->tour_status !== 'Definite') {
                return back()->with('error', 'Final invoice can only be generated for tours in Definite status');
            }

            $finalInvoice = $this->invoiceService->convertToFinalInvoice($decryptedId);

            return redirect()->route('invoices.show', Crypt::encrypt($finalInvoice->invoice_id))
                ->with('success', 'Final invoice generated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download Invoice as PDF (with services)
     */
    public function download(Request $request, $invoiceId)
    {
        // Try to decrypt if encrypted, otherwise use as-is
        try {
            $decryptedId = Crypt::decrypt($invoiceId);
        } catch (\Exception $e) {
            $decryptedId = $invoiceId;
        }
        
        $invoice = Invoice::with(['tour', 'agent', 'dmc', 'items'])
            ->where('invoice_id', $decryptedId)
            ->firstOrFail();

        // Recalculate totals to ensure latest payment data and taxes are included
        $this->invoiceService->recalculateInvoiceTotals($invoice);
        $invoice->refresh();

        $selectedCurrency = $this->getSelectedCurrency($request);
        $currencyConversion = $this->buildCurrencyConversion($invoice, $selectedCurrency);
        $exchangeRate = $this->getExchangeRate($selectedCurrency, $currencyConversion);
        $format = $request->query('format');
        $logoType = $request->query('logo_type', 'dmc');
        if (!in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }

        $viewName = $this->resolveInvoicePdfViewName($invoice->invoice_type, 'full', $format);

        $pdf = Pdf::loadView($viewName, [
            'invoice' => $invoice,
            'selectedCurrency' => $selectedCurrency,
            'currencyConversion' => $currencyConversion,
            'exchangeRate' => $exchangeRate,
            'logoType' => $logoType,
            'mode' => 'full',
        ])->setPaper('a4', 'portrait');

        $filename = $invoice->invoice_type === 'proforma'
            ? 'Proforma_Invoice_' . $invoice->proforma_number . '.pdf'
            : 'Invoice_' . $invoice->invoice_number . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download Invoice as PDF (price only, no services)
     */
    public function downloadPriceOnly(Request $request, $invoiceId)
    {
        // Try to decrypt if encrypted, otherwise use as-is
        try {
            $decryptedId = Crypt::decrypt($invoiceId);
        } catch (\Exception $e) {
            $decryptedId = $invoiceId;
        }
        
        $invoice = Invoice::with(['tour', 'agent', 'dmc', 'items'])
            ->where('invoice_id', $decryptedId)
            ->firstOrFail();

        // Recalculate totals to ensure latest payment data and taxes are included
        $this->invoiceService->recalculateInvoiceTotals($invoice);
        $invoice->refresh();

        $selectedCurrency = $this->getSelectedCurrency($request);
        $currencyConversion = $this->buildCurrencyConversion($invoice, $selectedCurrency);
        $exchangeRate = $this->getExchangeRate($selectedCurrency, $currencyConversion);
        $format = $request->query('format');
        $logoType = $request->query('logo_type', 'dmc');
        if (!in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }

        $viewName = $this->resolveInvoicePdfViewName($invoice->invoice_type, 'price-only', $format);

        $pdf = Pdf::loadView($viewName, [
            'invoice' => $invoice,
            'selectedCurrency' => $selectedCurrency,
            'currencyConversion' => $currencyConversion,
            'exchangeRate' => $exchangeRate,
            'logoType' => $logoType,
            'mode' => 'price-only',
        ])->setPaper('a4', 'portrait');

        $filename = $invoice->invoice_type === 'proforma'
            ? 'Proforma_Invoice_Price_Only_' . $invoice->proforma_number . '.pdf'
            : 'Invoice_Price_Only_' . $invoice->invoice_number . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Invoice preview page (like itinerary-preview): currency selector + embedded PDF + download.
     * Mode: full = with services, price-only = price breakup only.
     */
    public function preview(Request $request, $invoiceId)
    {
        try {
            $decryptedId = Crypt::decrypt($invoiceId);
        } catch (\Exception $e) {
            $decryptedId = $invoiceId;
        }

        $invoice = Invoice::with(['tour', 'agent.agency', 'dmc', 'items'])
            ->where('invoice_id', $decryptedId)
            ->firstOrFail();

        $this->invoiceService->recalculateInvoiceTotals($invoice);
        $invoice->refresh();

        $mode = $request->query('mode', 'full');
        if (!in_array($mode, ['full', 'price-only'], true)) {
            $mode = 'full';
        }
        $selectedCurrency = $this->getSelectedCurrency($request);
        $currencyConversion = $this->buildCurrencyConversion($invoice, $selectedCurrency);
        $logoType = $request->query('logo_type', 'dmc');
        if (!in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }
        $format = $request->query('format', 'standard');
        if (!in_array($format, ['standard', 'alternate'], true)) {
            $format = 'standard';
        }
        $hasAgency = $invoice->agent && $invoice->agent->agency;

        return view('invoices.invoice-preview', [
            'invoice' => $invoice,
            'selectedCurrency' => $selectedCurrency,
            'availableCurrencies' => $this->availableCurrencies,
            'currencyConversion' => $currencyConversion,
            'mode' => $mode,
            'logoType' => $logoType,
            'hasAgency' => $hasAgency,
            'format' => $format,
        ]);
    }

    /**
     * Stream or download invoice PDF (used by preview iframe and preview download button).
     * Query: mode (full|price-only), currency, preview (1=stream, 0=download)
     */
    public function invoicePdf(Request $request, $invoiceId)
    {
        try {
            $decryptedId = Crypt::decrypt($invoiceId);
        } catch (\Exception $e) {
            $decryptedId = $invoiceId;
        }

        $invoice = Invoice::with(['tour', 'agent.agency', 'dmc', 'items'])
            ->where('invoice_id', $decryptedId)
            ->firstOrFail();

        $this->invoiceService->recalculateInvoiceTotals($invoice);
        $invoice->refresh();

        $mode = $request->query('mode', 'full');
        if (!in_array($mode, ['full', 'price-only'], true)) {
            $mode = 'full';
        }
        $selectedCurrency = $this->getSelectedCurrency($request);
        $currencyConversion = $this->buildCurrencyConversion($invoice, $selectedCurrency);
        $exchangeRate = $this->getExchangeRate($selectedCurrency, $currencyConversion);
        $preview = $request->boolean('preview', false);
        $logoType = $request->query('logo_type', 'dmc');
        if (!in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }
        $format = $request->query('format');

        $viewName = $this->resolveInvoicePdfViewName($invoice->invoice_type, $mode, $format);

        $pdf = Pdf::loadView($viewName, [
            'invoice' => $invoice,
            'selectedCurrency' => $selectedCurrency,
            'currencyConversion' => $currencyConversion,
            'exchangeRate' => $exchangeRate,
            'logoType' => $logoType,
            'mode' => $mode,
        ])->setPaper('a4', 'portrait');

        if ($preview) {
            return $pdf->stream();
        }

        $filename = $mode === 'price-only'
            ? ($invoice->invoice_type === 'proforma' ? 'Proforma_Invoice_Price_Only_' . $invoice->proforma_number . '.pdf' : 'Invoice_Price_Only_' . $invoice->invoice_number . '.pdf')
            : ($invoice->invoice_type === 'proforma' ? 'Proforma_Invoice_' . $invoice->proforma_number . '.pdf' : 'Invoice_' . $invoice->invoice_number . '.pdf');

        return $pdf->download($filename);
    }

    /**
     * View Invoice as PDF in browser
     */
    public function view(Request $request, $invoiceId)
    {
        // Try to decrypt if encrypted, otherwise use as-is
        try {
            $decryptedId = Crypt::decrypt($invoiceId);
        } catch (\Exception $e) {
            $decryptedId = $invoiceId;
        }
        
        $invoice = Invoice::with(['tour', 'agent', 'dmc', 'items'])
            ->where('invoice_id', $decryptedId)
            ->firstOrFail();

        $selectedCurrency = $this->getSelectedCurrency($request);
        $currencyConversion = $this->buildCurrencyConversion($invoice, $selectedCurrency);
        $exchangeRate = $this->getExchangeRate($selectedCurrency, $currencyConversion);
        $format = $request->query('format');
        $logoType = $request->query('logo_type', 'dmc');
        if (!in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }

        $viewName = $this->resolveInvoicePdfViewName($invoice->invoice_type, 'full', $format);

        $pdf = Pdf::loadView($viewName, [
            'invoice' => $invoice,
            'selectedCurrency' => $selectedCurrency,
            'currencyConversion' => $currencyConversion,
            'exchangeRate' => $exchangeRate,
            'logoType' => $logoType,
            'mode' => 'full',
        ])->setPaper('a4', 'portrait');

        return $pdf->stream();
    }

    /**
     * Handle cancellation - generate credit note if needed
     */
    public function handleCancellation($tourId)
    {
        try {
            $creditNote = $this->invoiceService->handleCancellation($tourId);

            if ($creditNote) {
                return redirect()->route('credit-notes.show', $creditNote->credit_note_id)
                    ->with('success', 'Credit note generated successfully');
            } else {
                return back()->with('success', 'Proforma invoice cancelled successfully');
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
