<?php

namespace App\Exports\Concerns;

use App\Models\FormExportTemplate;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
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
        $lastDataRow   = $sheet->getHighestRow();

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

        // ── Solid black borders on the full table (header + all data rows) ─
        if ($lastDataRow >= $headerRow && $totalCols > 0) {
            $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$lastDataRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FF000000'],
                    ],
                ],
            ]);
        }

        // ── Template-driven formatting (title, subtitle, header, data rows) ─
        if ($template) {
            $this->applyTemplateStyles(
                $sheet, $template, $headerRow, $lastColLetter, $lastDataRow
            );
        }

        // ── Column widths ──────────────────────────────────────────────────
        if ($template) {
            // Width driven by data content so long header labels don't bloat columns.
            // Headers use wrapText so they wrap within the data-driven width.
            $dataStart = $this->exporter->getDataStartRow();
            for ($col = 1; $col <= max(1, $totalCols); $col++) {
                $maxLen = 0;
                for ($row = $dataStart; $row <= $lastDataRow; $row++) {
                    $val = (string) $sheet->getCellByColumnAndRow($col, $row)->getValue();
                    foreach (explode("\n", $val) as $line) {
                        $maxLen = max($maxLen, mb_strlen($line));
                    }
                }
                $width = min(35, max(8, (int) ceil($maxLen * 1.15) + 2));
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(false);
                $sheet->getColumnDimensionByColumn($col)->setWidth($width);
            }
        } else {
            for ($col = 1; $col <= max(1, $totalCols); $col++) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
        }

        // ── Signature (after width loop so fixed width sticks) ─────────────
        if ($template) {
            $this->applySignature($sheet, $template, $lastDataRow, $totalCols);
        }

        // ── Return row-level style declarations ────────────────────────────
        return [
            $headerRow => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFD8D8D8'],
                ],
            ],
        ];
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function applyTemplateStyles(
        Worksheet $sheet,
        FormExportTemplate $template,
        int $headerRow,
        string $lastColLetter,
        int $lastDataRow,
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

        // Header row — centered, wrapped, gray fill (borders already applied globally)
        $headerRange = "A{$headerRow}:{$lastColLetter}{$headerRow}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font'      => ['bold' => true],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFD8D8D8'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(-1); // auto for wrapped text

        // Data rows — wrapped text, top-aligned (borders already applied globally)
        $dataStart = $this->exporter->getDataStartRow();
        if ($lastDataRow >= $dataStart) {
            $sheet->getStyle("A{$dataStart}:{$lastColLetter}{$lastDataRow}")->applyFromArray([
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
            ]);
        }
    }

    private function applySignature(
        Worksheet $sheet,
        FormExportTemplate $template,
        int $lastDataRow,
        int $totalCols,
    ): void {
        if (!$template->signature_name && !$template->signature_role) {
            return;
        }

        // Determine which column the signature block occupies
        $position     = $template->signature_position ?? 'right';
        $signColIndex = match ($position) {
            'left'   => 1,
            'center' => max(1, (int) ceil($totalCols / 2)),
            default  => max(1, $totalCols), // 'right'
        };
        $signColLetter = Coordinate::stringFromColumnIndex($signColIndex);

        // Give the signature column a generous fixed width so text is not clipped
        $sheet->getColumnDimension($signColLetter)->setAutoSize(false);
        $sheet->getColumnDimension($signColLetter)->setWidth(35);

        $signStartRow = $lastDataRow + 3;
        $nextRow      = $signStartRow;

        if ($template->signature_role) {
            $sheet->setCellValue("{$signColLetter}{$nextRow}", $template->signature_role);
            $sheet->getStyle("{$signColLetter}{$nextRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $nextRow++;
        }

        // 3-row gap for a physical signature space
        $nextRow += 3;

        if ($template->signature_name) {
            $sheet->setCellValue("{$signColLetter}{$nextRow}", $template->signature_name);
            $sheet->getStyle("{$signColLetter}{$nextRow}")->applyFromArray([
                'font'      => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $nextRow++;
        }

        if ($template->signature_nip) {
            $sheet->setCellValue("{$signColLetter}{$nextRow}", 'NIP. ' . $template->signature_nip);
            $sheet->getStyle("{$signColLetter}{$nextRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
    }
}
