<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OpeningStocksTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['PROD001', '100.000', '10.50', '2024-01-01', 'Sample opening stock'],
            ['PROD002', '50.000', '25.00', '2024-01-01', 'Another sample'],
        ];
    }

    public function headings(): array
    {
        return [
            'Product Code *',
            'Quantity *',
            'Unit Cost *',
            'Opening Date (YYYY-MM-DD)',
            'Notes'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
