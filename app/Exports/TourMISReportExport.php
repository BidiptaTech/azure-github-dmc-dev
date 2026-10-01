<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class TourMISReportExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected Collection $report
    ) {}

    public function collection(): Collection
    {
        return $this->report->map(function ($row) {
            return [
                $row->booking_date,
                $row->type ?? '—',
                $row->tour_name,
                $row->destination,
                $row->agent_name,
                $row->dmc_name,
                $row->pax,
                $row->selling_price,
                $row->net_profit,
                $row->transaction_reference_number,
                $row->payment_status,
                $row->booking_status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Booking Date',
            'Type',
            'Tour Id',
            'Destination',
            'Agent Name',
            'DMC Name',
            'PAX',
            'Selling Price',
            'Net Profit',
            'Transaction Reference',
            'Payment Status',
            'Booking Status',
        ];
    }
}
