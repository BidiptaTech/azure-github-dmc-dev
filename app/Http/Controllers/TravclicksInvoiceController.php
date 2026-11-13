<?php

namespace App\Http\Controllers;

use App\Services\TravclicksInvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravclicksInvoiceController extends Controller
{
    /** Same access pattern as Itinerary / Quotation Settings in DMC portal */
    private const ALLOWED_ROLES = [11, 33, 34, 37, 38, 77, 84, 128, 131, 132, 134, 135, 137, 138];

    public function __construct(private TravclicksInvoiceService $service)
    {
    }

    private function authorizeAccess(): void
    {
        $user = Auth::user();
        if (!$user || !in_array((int) $user->role_id, self::ALLOWED_ROLES, true)) {
            abort(403, 'You do not have permission to access Travclicks Invoice.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $now = now();
        $selectedMonth = (int) $request->input('month', old('month', $now->month));
        $selectedYear = (int) $request->input('year', old('year', $now->year));
        $showPreview = $request->boolean('show')
            && $selectedMonth >= 1
            && $selectedMonth <= 12
            && $selectedYear >= 2000;

        $cacheBust = (string) ($request->input('v') ?: time());

        return view('travclicks-invoice.index', [
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'years' => range($now->year + 3, $now->year - 5),
            'showPreview' => $showPreview,
            'previewUrl' => $showPreview
                ? route('travclicks-invoice.pdf', [
                    'month' => $selectedMonth,
                    'year' => $selectedYear,
                    'v' => $cacheBust,
                ])
                : null,
            'downloadUrl' => $showPreview
                ? route('travclicks-invoice.download', [
                    'month' => $selectedMonth,
                    'year' => $selectedYear,
                    'v' => $cacheBust,
                ])
                : null,
        ]);
    }

    public function generate(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        return redirect()->route('travclicks-invoice.index', [
            'month' => (int) $validated['month'],
            'year' => (int) $validated['year'],
            'show' => 1,
            'v' => time(),
        ]);
    }

    public function pdf(Request $request)
    {
        return $this->renderPdf($request, true);
    }

    public function download(Request $request)
    {
        return $this->renderPdf($request, false);
    }

    private function renderPdf(Request $request, bool $preview)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        try {
            $payload = $this->service->build(
                Auth::user(),
                (int) $validated['month'],
                (int) $validated['year']
            );
        } catch (\RuntimeException $e) {
            if ($preview) {
                return response(
                    '<div style="font-family:Arial;padding:24px;color:#b91c1c;">' . e($e->getMessage()) . '</div>',
                    422
                )->header('Cache-Control', 'no-store, no-cache, must-revalidate');
            }

            return redirect()
                ->route('travclicks-invoice.index')
                ->withInput()
                ->with('error', $e->getMessage());
        }

        $pdf = Pdf::loadView('travclicks-invoice.pdf', [
            'data' => $payload,
            'travclicksLogo' => TravclicksInvoiceService::encodeLogo($payload['travclicks']['logo'] ?? null),
            'dmcLogo' => TravclicksInvoiceService::encodeLogo($payload['dmc']->logo ?? null),
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-php', false);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);

        $filename = sprintf(
            'Travclicks-Invoice-%s-%02d.pdf',
            $validated['year'],
            $validated['month']
        );

        $response = $preview ? $pdf->stream($filename) : $pdf->download($filename);
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
