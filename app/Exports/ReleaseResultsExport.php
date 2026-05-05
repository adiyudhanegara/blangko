<?php
namespace App\Exports;

use App\Models\FormRelease;
use App\Services\SubmissionExporter;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReleaseResultsExport implements FromArray, WithTitle, WithStyles
{
    private array $rows;

    public function __construct(FormRelease $release)
    {
        $this->rows = (new SubmissionExporter())->getRows($release);
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Results';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
