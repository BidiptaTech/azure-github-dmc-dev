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
                $row->booking_id,
                $row->booking_date,
                $row->tour_name,
                $row->destination,
                $row->agent_name,
                $row->dmc_name,
                $row->pax,
                $row->selling_price,
                $row->agent_commission,
                $row->dmc_commission,
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
            'Booking ID',
            'Booking Date',
            'Tour Name',
            'Destination',
            'Agent Name',
            'DMC Name',
            'PAX',
            'Selling Price',
            'Agent Commission',
            'DMC Commission',
            'Net Profit',
            'Transaction Reference',
            'Payment Status',
            'Booking Status',
        ];
    }
}
