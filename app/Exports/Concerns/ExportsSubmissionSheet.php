<?php

namespace App\Exports\Concerns;

use App\Models\FormExportTemplate;
use App\Services\SubmissionExporter;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Shared styling/drawing logic for submission sheet exports.
 *
 * Requires the consuming class to have a `SubmissionExporter $exporter` property
 * populated before drawings() or styles() are called.
 */
trait ExportsSubmissionSheet
{
    // ── WithDrawings ───────────────────────────────────────────────────────

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
            $drawing->setHeight(60);
            $drawing->setOffsetX(2);
            $drawing->setOffsetY(2);
            $drawing->getHyperlink()->setUrl($img['url']);

            $drawings[] = $drawing;
        }

        return $drawings;
    }

    // ── WithStyles ─────────────────────────────────────────────────────────

    public function styles(Worksheet $sheet): array
    {
        $template      = $this->exporter->getTemplate();
        $headerRow     = $this->exporter->getTableHeaderRow();
        $totalCols     = $this->exporter->getTotalCols();
        $lastColLetter = $totalCols > 0
            ? Coordinate::stringFromColumnIndex($totalCols)
            : 'A';

        // ── Image row heights ──────────────────────────────────────────────
        foreach ($this->exporter->getImageRows() as $rowNum) {
            $sheet->getRowDimension($rowNum)->setRowHeight(65);
        }

        // ── URL cells → blue underlined hyperlink ──────────────────────────
        foreach ($this->exporter->getUrlCells() as $uc) {
            $sheet->getCell($uc['cell'])->getHyperlink()->setUrl($uc['url']);
            $sheet->getStyle($uc['cell'])->applyFromArray([
                'font' => [
                    'color'     => ['argb' => Color::COLOR_BLUE],
                    'underline' => Font::UNDERLINE_SINGLE,
                ],
            ]);
        }

        // ── Template-driven header section ─────────────────────────────────
        if ($template) {
            $this->applyTemplateStyles($sheet, $template, $headerRow, $lastColLetter, $totalCols);
        }

        // ── Auto-size all columns ──────────────────────────────────────────
        for ($col = 1; $col <= max(1, $totalCols); $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        // ── Return row-level style declarations ────────────────────────────
        return [
            $headerRow => [
                'font' => ['bold' => true],
            ],
        ];
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function applyTemplateStyles(
        Worksheet $sheet,
        FormExportTemplate $template,
        int $headerRow,
        string $lastColLetter,
        int $totalCols,
    ): void {
        // Row 1 — title: merge across all columns, bold + underline + centered
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => [
                'bold'      => true,
                'underline' => Font::UNDERLINE_SINGLE,
                'size'      => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // Row 2 — subtitle: merge, bold, left-aligned
        $sheet->mergeCells("A2:{$lastColLetter}2");
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // Header row — bold, centered, wrapped, thin border
        $headerRange = "A{$headerRow}:{$lastColLetter}{$headerRow}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders'   => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(-1); // auto for wrapped text

        // Data rows — wrapped text, top-aligned, light borders
        $dataStart   = $this->exporter->getDataStartRow();
        $lastDataRow = $sheet->getHighestRow();

        if ($lastDataRow >= $dataStart) {
            $dataRange = "A{$dataStart}:{$lastColLetter}{$lastDataRow}";
            $sheet->getStyle($dataRange)->applyFromArray([
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
                'borders'   => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFCCCCCC'],
                    ],
                ],
            ]);
        }

        // Signature block below the data
        $this->applySignature($sheet, $template, $lastDataRow, $totalCols, $lastColLetter);
    }

    private function applySignature(
        Worksheet $sheet,
        FormExportTemplate $template,
        int $lastDataRow,
        int $totalCols,
        string $lastColLetter,
    ): void {
        if (!$template->signature_name && !$template->signature_role) {
            return;
        }

        // Place signature block in the last column (rightmost)
        $signColLetter = $lastColLetter;
        $signStartRow  = $lastDataRow + 3;

        if ($template->signature_role) {
            $sheet->setCellValue("{$signColLetter}{$signStartRow}", $template->signature_role);
        }

        // Leave 3 rows as a physical signature gap
        $nameRow = $signStartRow + 3;

        if ($template->signature_name) {
            $sheet->setCellValue("{$signColLetter}{$nameRow}", $template->signature_name);
            $sheet->getStyle("{$signColLetter}{$nameRow}")->applyFromArray([
                'font' => ['bold' => true],
            ]);
        }

        if ($template->signature_nip) {
            $sheet->setCellValue("{$signColLetter}" . ($nameRow + 1), 'Nip. ' . $template->signature_nip);
        }

        if ($template->signature_position) {
            $sheet->setCellValue("{$signColLetter}" . ($nameRow + 2), $template->signature_position);
        }
    }
}
