<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Services\MISReportService;
use App\Exports\TourMISReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MISReportController extends Controller
{
    public function __construct(
        protected MISReportService $misReportService
    ) {}

    protected function getDmcIdForUser(): ?string
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }
        $dmcId = CommonHelper::getDmcId($user);
        if ($dmcId !== null && $dmcId !== '') {
            return $dmcId;
        }
        if (in_array($user->role_id ?? 0, [11])) {
            return $user->userId;
        }
        return null;
    }

    /**
     * Display the Tour MIS Report page with filters (10 per page, scoped by logged-in user's DMC).
     */
    public function tourMIS(Request $request)
    {
        $dmcId = $this->getDmcIdForUser();
        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'agent' => $request->input('agent'),
            'dmc' => $request->input('dmc'),
            'booking_status' => $request->input('booking_status'),
        ];

        $report = $this->misReportService->getTourMISReport($filters, $dmcId, 10);
        $agents = $this->misReportService->getAgentsForFilter($dmcId);
        $dmcs = $this->misReportService->getDmcsForFilter($dmcId);

        return view('mis.tours', [
            'report' => $report,
            'agents' => $agents,
            'dmcs' => $dmcs,
            'filters' => $filters,
        ]);
    }

    /**
     * Export Tour MIS Report to Excel (all filtered rows, scoped by logged-in user's DMC).
     */
    public function tourMISExport(Request $request): BinaryFileResponse
    {
        $dmcId = $this->getDmcIdForUser();
        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'agent' => $request->input('agent'),
            'dmc' => $request->input('dmc'),
            'booking_status' => $request->input('booking_status'),
        ];

        $report = $this->misReportService->getTourMISReport($filters, $dmcId, null);

        $filename = 'tour-mis-report-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new TourMISReportExport($report), $filename, \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Export Tour MIS Report to PDF (all filtered rows, scoped by logged-in user's DMC).
     */
    public function tourMISExportPdf(Request $request)
    {
        $dmcId = $this->getDmcIdForUser();
        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'agent' => $request->input('agent'),
            'dmc' => $request->input('dmc'),
            'booking_status' => $request->input('booking_status'),
        ];

        $report = $this->misReportService->getTourMISReport($filters, $dmcId, null);

        $pdf = Pdf::loadView('mis.tours-pdf', [
            'report' => $report,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        $filename = 'tour-mis-report-' . now()->format('Y-m-d-His') . '.pdf';

        return $pdf->download($filename);
    }
}
