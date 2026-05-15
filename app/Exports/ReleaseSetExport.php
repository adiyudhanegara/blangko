<?php

namespace App\Exports;

use App\Exports\Concerns\ExportsSubmissionSheet;
use App\Models\FormRelease;
use App\Models\ReleaseSet;
use App\Services\SubmissionExporter;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReleaseSetExport implements WithMultipleSheets
{
    public function __construct(private readonly ReleaseSet $releaseSet) {}

    public function sheets(): array
    {
        $this->releaseSet->load('formReleases.form');

        return $this->releaseSet->formReleases
            ->map(fn (FormRelease $release) => new ReleaseSetSheetExport($release))
            ->all();
    }
}

class ReleaseSetSheetExport implements FromArray, WithTitle, WithStyles, WithDrawings
{
    use ExportsSubmissionSheet;

    private SubmissionExporter $exporter;

    public function __construct(private readonly FormRelease $release) {}

    public function array(): array
    {
        $this->exporter = new SubmissionExporter();
        return $this->exporter->getRows($this->release);
    }

    public function title(): string
    {
        $raw = $this->release->form?->title ?? "Form {$this->release->id}";
        return mb_substr(preg_replace('/[\/\\\?\*\[\]:]/', '', $raw), 0, 31);
    }
}
