<?php

namespace App\Exports;

use App\Exports\Concerns\ExportsSubmissionSheet;
use App\Models\FormRelease;
use App\Services\SubmissionExporter;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;

class SubmissionExport implements FromArray, WithStyles, WithDrawings
{
    use ExportsSubmissionSheet;

    private SubmissionExporter $exporter;

    public function __construct(private readonly FormRelease $release) {}

    public function array(): array
    {
        $this->exporter = new SubmissionExporter();
        return $this->exporter->getRows($this->release);
    }
}
