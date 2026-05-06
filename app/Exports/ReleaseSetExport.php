<?php

namespace App\Exports;

use App\Models\ReleaseSet;
use App\Services\SubmissionExporter;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReleaseSetExport implements WithMultipleSheets
{
    public function __construct(private readonly ReleaseSet $releaseSet) {}

    public function sheets(): array
    {
        $this->releaseSet->load('formReleases.form');

        $sheets = [];

        foreach ($this->releaseSet->formReleases as $release) {
            $sheets[] = new ReleaseSetSheetExport($release);
        }

        return $sheets;
    }
}

class ReleaseSetSheetExport implements FromArray, WithTitle, WithStyles
{
    public function __construct(private readonly \App\Models\FormRelease $release) {}

    public function array(): array
    {
        return (new SubmissionExporter)->getRows($this->release);
    }

    public function title(): string
    {
        // Sheet name max 31 chars, no special chars
        $title = $this->release->form?->title ?? "Form {$this->release->id}";
        return mb_substr(preg_replace('/[\/\\\?\*\[\]:]/', '', $title), 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
