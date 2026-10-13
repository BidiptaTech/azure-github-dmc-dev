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
    protected function getAvailableCurrencies(): array
    {
        return CommonHelper::getInvoiceAvailableCurrencies();
    }

    /**
     * Build currency context for invoice views and PDFs.
     *
     * @return array{baseCurrency: string, selectedCurrency: string, currencyConversion: array<string, float>, exchangeRate: float}
     */
    protected function buildInvoiceCurrencyContext(Request $request, Invoice $invoice): array
    {
        $baseCurrency = CommonHelper::resolveInvoiceBaseCurrency($invoice);
        $selectedCurrency = CommonHelper::getInvoiceSelectedCurrency($request->query('currency'), $invoice);
        $currencyConversion = CommonHelper::buildInvoiceCurrencyConversion($invoice, $selectedCurrency);
        $exchangeRate = CommonHelper::getInvoiceExchangeRate($baseCurrency, $selectedCurrency, $currencyConversion);
        $isThirdPartyInvoice = CommonHelper::isInvoiceThirdPartyEnabled($invoice);
        $invoiceMultiGeo = CommonHelper::detectInvoiceMultiGeo($invoice);

        if ($isThirdPartyInvoice) {
            CommonHelper::enrichInvoiceItemsWithOrderGeo($invoice);
        }

        return compact(
            'baseCurrency',
            'selectedCurrency',
            'currencyConversion',
            'exchangeRate',
            'isThirdPartyInvoice',
            'invoiceMultiGeo'
        );
    }

    /**
     * @deprecated Use buildInvoiceCurrencyContext()
     */
    protected function getSelectedCurrency(Request $request): string
    {
        return strtoupper($request->query('currency', 'SGD'));
    }

    /**
     * @deprecated Use CommonHelper::buildInvoiceCurrencyConversion()
     */
    protected function buildCurrencyConversion(Invoice $invoice, string $selectedCurrency): array
    {
        return CommonHelper::buildInvoiceCurrencyConversion($invoice, $selectedCurrency);
    }

    /**
     * @deprecated Use CommonHelper::getInvoiceExchangeRate()
     */
    protected function getExchangeRate(string $selectedCurrency, array $currencyConversion): float
    {
        $baseCurrency = array_key_first($currencyConversion) ?: 'SGD';

        return CommonHelper::getInvoiceExchangeRate($baseCurrency, $selectedCurrency, $currencyConversion);
    }

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Resolve Blade view for invoice PDF (standard layout vs travel-agent / alternate layout).
     *
     * @param  string  $invoiceType  proforma|final
     * @param  string  $mode  full|price-only — when {@see $format} is {@code alternate}, {@code invoices.pdf.alternate}
     *                        branches on {@code mode} (line items vs aggregate price summary).
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

        $currency = $this->buildInvoiceCurrencyContext($request, $invoice);

        return view('invoices.show', array_merge($currency, [
            'invoice' => $invoice,
            'availableCurrencies' => $this->getAvailableCurrencies(),
        ]));
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

        return view('invoices.edit', [
            'invoice' => $invoice,
            'baseCurrency' => CommonHelper::resolveInvoiceBaseCurrency($invoice),
        ]);
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

        $currency = $this->buildInvoiceCurrencyContext($request, $invoice);
        $format = $request->query('format');
        $logoType = $request->query('logo_type', 'dmc');
        if (!in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }

        $viewName = $this->resolveInvoicePdfViewName($invoice->invoice_type, 'full', $format);

        $pdf = Pdf::loadView($viewName, array_merge($currency, [
            'invoice' => $invoice,
            'logoType' => $logoType,
            'mode' => 'full',
        ]))->setPaper('a4', 'portrait');

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

        $currency = $this->buildInvoiceCurrencyContext($request, $invoice);
        $format = $request->query('format');
        $logoType = $request->query('logo_type', 'dmc');
        if (!in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }

        $viewName = $this->resolveInvoicePdfViewName($invoice->invoice_type, 'price-only', $format);

        $pdf = Pdf::loadView($viewName, array_merge($currency, [
            'invoice' => $invoice,
            'logoType' => $logoType,
            'mode' => 'price-only',
        ]))->setPaper('a4', 'portrait');

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
        $currency = $this->buildInvoiceCurrencyContext($request, $invoice);
        $logoType = $request->query('logo_type', 'dmc');
        if (!in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }
        $format = $request->query('format', 'standard');
        if (!in_array($format, ['standard', 'alternate'], true)) {
            $format = 'standard';
        }
        $hasAgency = $invoice->agent && $invoice->agent->agency;

        return view('invoices.invoice-preview', array_merge($currency, [
            'invoice' => $invoice,
            'availableCurrencies' => $this->getAvailableCurrencies(),
            'mode' => $mode,
            'logoType' => $logoType,
            'hasAgency' => $hasAgency,
            'format' => $format,
        ]));
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
        $currency = $this->buildInvoiceCurrencyContext($request, $invoice);
        $preview = $request->boolean('preview', false);
        $logoType = $request->query('logo_type', 'dmc');
        if (!in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }
        $format = $request->query('format');

        $viewName = $this->resolveInvoicePdfViewName($invoice->invoice_type, $mode, $format);

        $pdf = Pdf::loadView($viewName, array_merge($currency, [
            'invoice' => $invoice,
            'logoType' => $logoType,
            'mode' => $mode,
        ]))->setPaper('a4', 'portrait');

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

        $currency = $this->buildInvoiceCurrencyContext($request, $invoice);
        $format = $request->query('format');
        $logoType = $request->query('logo_type', 'dmc');
        if (!in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }

        $viewName = $this->resolveInvoicePdfViewName($invoice->invoice_type, 'full', $format);

        $pdf = Pdf::loadView($viewName, array_merge($currency, [
            'invoice' => $invoice,
            'logoType' => $logoType,
            'mode' => 'full',
        ]))->setPaper('a4', 'portrait');

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
