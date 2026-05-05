<?php

namespace App\Exports;

use App\Models\FormRelease;
use App\Services\SubmissionExporter;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubmissionExport implements FromArray, WithStyles
{
    public function __construct(private readonly FormRelease $release) {}

    public function array(): array
    {
        return (new SubmissionExporter)->getRows($this->release);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
