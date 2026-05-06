<?php

namespace App\Exports;

use App\Models\ReleaseSet;
use App\Services\SubmissionExporter;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
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

class ReleaseSetSheetExport implements FromArray, WithTitle, WithStyles, WithDrawings
{
    private SubmissionExporter $exporter;

    public function __construct(private readonly \App\Models\FormRelease $release) {}

    public function array(): array
    {
        $this->exporter = new SubmissionExporter();
        return $this->exporter->getRows($this->release);
    }

    public function drawings(): array
    {
        $drawings = [];

        foreach ($this->exporter->getImages() as $img) {
            if (!file_exists($img['path'])) {
                continue;
            }

            $drawing = new Drawing();
            $drawing->setName($img['name']);
            $drawing->setDescription($img['name']);
            $drawing->setPath($img['path']);
            $drawing->setCoordinates($img['cell']);
            $drawing->setHeight(80);
            $drawing->setOffsetX(2);
            $drawing->setOffsetY(2);
            $drawing->getHyperlink()->setUrl($img['url']);

            $drawings[] = $drawing;
        }

        return $drawings;
    }

    public function title(): string
    {
        $title = $this->release->form?->title ?? "Form {$this->release->id}";
        return mb_substr(preg_replace('/[\/\\\?\*\[\]:]/', '', $title), 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        foreach ($this->exporter->getImageRows() as $rowNum) {
            $sheet->getRowDimension($rowNum)->setRowHeight(65);
        }

        foreach ($this->exporter->getUrlCells() as $uc) {
            $sheet->getCell($uc['cell'])->getHyperlink()->setUrl($uc['url']);
            $sheet->getStyle($uc['cell'])->applyFromArray([
                'font' => [
                    'color'     => ['argb' => Color::COLOR_BLUE],
                    'underline' => Font::UNDERLINE_SINGLE,
                ],
            ]);
        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
