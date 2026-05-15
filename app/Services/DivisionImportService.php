<?php

namespace App\Services;

use App\Models\Division;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DivisionImportService
{
    public function import(string $absolutePath): array
    {
        $errors = [];

        try {
            $spreadsheet = IOFactory::load($absolutePath);
        } catch (\Throwable $e) {
            return ['errors' => ['Could not read file: ' . $e->getMessage()], 'count' => 0];
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows  = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return ['errors' => ['The file is empty.'], 'count' => 0];
        }

        // First row is the header — build a column index map
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);
        $nameCol = array_search('name', $header);
        $descCol = array_search('description', $header);

        if ($nameCol === false) {
            return ['errors' => ['Column "name" not found in the first row.'], 'count' => 0];
        }

        $dataRows = array_slice($rows, 1);
        $rowNum   = 2;
        $count    = 0;

        foreach ($dataRows as $row) {
            $name = trim((string) ($row[$nameCol] ?? ''));

            if ($name === '') {
                $rowNum++;
                continue;
            }

            $description = $descCol !== false ? trim((string) ($row[$descCol] ?? '')) : '';

            Division::withTrashed()->updateOrCreate(
                ['name' => $name],
                [
                    'slug'        => Str::slug($name),
                    'description' => $description ?: null,
                    'deleted_at'  => null,
                ],
            );

            $count++;
            $rowNum++;
        }

        if ($count === 0) {
            $errors[] = 'No data rows found (all rows were empty).';
        }

        return ['errors' => $errors, 'count' => $count];
    }
}
