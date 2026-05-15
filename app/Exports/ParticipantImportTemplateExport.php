<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantImportTemplateExport implements FromArray, WithTitle, WithStyles
{
    public function array(): array
    {
        return [
            ['name', 'email', 'phone', 'identifier', 'division', 'status'],
            ['John Doe', 'john@example.com', '081234567890', 'EMP001', 'Engineering', 'active'],
            ['Jane Smith', 'jane@example.com', '081234567891', 'EMP002', 'Marketing', 'active'],
            ['Bob Jones', '', '', 'EMP003', 'Finance', 'inactive'],
        ];
    }

    public function title(): string
    {
        return 'participants';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(12);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
