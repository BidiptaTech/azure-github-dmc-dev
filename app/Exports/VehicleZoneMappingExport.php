<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class VehicleZoneMappingExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
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
        return [
            'vehicle_id',
            'mapping_type',
            'from_zone_id',
            'from_name',
            'from_type',
            'to_zone_id',
            'to_name',
            'to_type',
            'private_price',
            'shared_price',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();
        $headerRange = 'A1:' . $lastColumn . '1';

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

        return [];
    }
}
