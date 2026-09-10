<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VehicleHourlyPricesExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    /**
     * @param array<int, array<int, mixed>> $rows
     */
    public function __construct(
        protected array $rows
    ) {}

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        return $this->rows;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        $headers = [
            'vehicle_id',
            'vehicle_name',
            'plate_no',
        ];

        for ($hour = 1; $hour <= 12; $hour++) {
            $headers[] = 'hourly_price_' . $hour;
        }

        return $headers;
    }

    public function styles(Worksheet $sheet): array
    {
        $headerRange = 'A1:O1';

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '066EB3'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Locked identity columns (read-only look)
        $sheet->getStyle('A1:C1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('475569');

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = max(2, (int) $sheet->getHighestRow());
                $dataRange = 'A1:O' . $highestRow;

                // Visible grid borders between all rows and columns
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '94A3B8'],
                        ],
                    ],
                ]);

                // Identity columns: locked + muted background
                $sheet->getStyle('A2:C' . $highestRow)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E2E8F0'],
                    ],
                    'protection' => [
                        'locked' => Protection::PROTECTION_PROTECTED,
                    ],
                ]);

                // Hourly price columns: editable
                $sheet->getStyle('D2:O' . $highestRow)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'ECFDF5'],
                    ],
                    'protection' => [
                        'locked' => Protection::PROTECTION_UNPROTECTED,
                    ],
                ]);

                // Header row stays locked
                $sheet->getStyle('A1:O1')->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);

                $protection = $sheet->getProtection();
                $protection->setSheet(true);
                $protection->setSort(false);
                $protection->setInsertRows(false);
                $protection->setInsertColumns(false);
                $protection->setDeleteRows(false);
                $protection->setDeleteColumns(false);
                $protection->setFormatCells(false);
                // Soft lock so casual users cannot unprotect easily
                $protection->setPassword('hourly_prices_readonly');
            },
        ];
    }
}
