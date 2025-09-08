<?php

namespace App\Exports;

use App\Models\OpeningStock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OpeningStocksExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return OpeningStock::with(['product.category', 'product.brand', 'product.unit'])
            ->where('is_active', true)
            ->orderBy('opening_date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Product Code',
            'Product Name',
            'Category',
            'Brand',
            'Unit',
            'Quantity',
            'Unit Cost',
            'Total Cost',
            'Opening Date',
            'Notes'
        ];
    }

    public function map($openingStock): array
    {
        return [
            $openingStock->product->code,
            $openingStock->product->name,
            $openingStock->product->category->name ?? '',
            $openingStock->product->brand->name ?? '',
            $openingStock->product->unit->name ?? '',
            $openingStock->quantity,
            $openingStock->unit_cost,
            $openingStock->total_cost,
            $openingStock->opening_date->format('Y-m-d'),
            $openingStock->notes ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
