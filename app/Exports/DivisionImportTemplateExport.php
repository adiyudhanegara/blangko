<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DivisionImportTemplateExport implements FromArray, WithTitle, WithStyles
{
    public function array(): array
    {
        return [
            ['name', 'description'],
            ['Engineering', 'Software engineering team'],
            ['Marketing', 'Marketing and communications'],
            ['Finance', ''],
        ];
    }

    public function title(): string
    {
        return 'divisions';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(50);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
