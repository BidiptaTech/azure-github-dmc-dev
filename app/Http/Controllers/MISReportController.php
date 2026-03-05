<?php

namespace App\Http\Controllers;

use App\Services\MISReportService;
use App\Exports\TourMISReportExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MISReportController extends Controller
{
    public function __construct(
        protected MISReportService $misReportService
    ) {}

    /**
     * Display the Tour MIS Report page with filters.
     */
    public function tourMIS(Request $request)
    {
        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'agent' => $request->input('agent'),
            'dmc' => $request->input('dmc'),
            'booking_status' => $request->input('booking_status'),
        ];

        $report = $this->misReportService->getTourMISReport($filters);
        $agents = $this->misReportService->getAgentsForFilter();
        $dmcs = $this->misReportService->getDmcsForFilter();

        return view('mis.tours', [
            'report' => $report,
            'agents' => $agents,
            'dmcs' => $dmcs,
            'filters' => $filters,
        ]);
    }

    /**
     * Export Tour MIS Report to Excel.
     */
    public function tourMISExport(Request $request): BinaryFileResponse
    {
        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'agent' => $request->input('agent'),
            'dmc' => $request->input('dmc'),
            'booking_status' => $request->input('booking_status'),
        ];

        $report = $this->misReportService->getTourMISReport($filters);

        $filename = 'tour-mis-report-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new TourMISReportExport($report), $filename, \Maatwebsite\Excel\Excel::XLSX);
    }
}
