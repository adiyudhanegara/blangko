<?php

namespace App\Services;

use App\Models\Division;
use App\Models\Participant;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ParticipantImportService
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

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);

        $col = fn (string $key) => array_search($key, $header);

        $nameCol       = $col('name');
        $emailCol      = $col('email');
        $phoneCol      = $col('phone');
        $identifierCol = $col('identifier');
        $divisionCol   = $col('division');
        $statusCol     = $col('status');

        if ($nameCol === false) {
            return ['errors' => ['Column "name" not found in the header row.'], 'count' => 0];
        }

        // Cache division names → ids to avoid N+1 per row
        $divisionMap = Division::whereNull('deleted_at')
            ->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id])
            ->all();

        $dataRows = array_slice($rows, 1);
        $rowNum   = 2;
        $count    = 0;

        foreach ($dataRows as $row) {
            $name = trim((string) ($row[$nameCol] ?? ''));

            if ($name === '') {
                $rowNum++;
                continue;
            }

            $email      = $emailCol !== false ? (trim((string) ($row[$emailCol] ?? '')) ?: null) : null;
            $phone      = $phoneCol !== false ? (trim((string) ($row[$phoneCol] ?? '')) ?: null) : null;
            $identifier = $identifierCol !== false ? (trim((string) ($row[$identifierCol] ?? '')) ?: null) : null;
            $status     = $statusCol !== false ? strtolower(trim((string) ($row[$statusCol] ?? ''))) : 'active';
            $status     = in_array($status, ['active', 'inactive'], true) ? $status : 'active';

            $divisionId = null;
            if ($divisionCol !== false) {
                $divisionName = strtolower(trim((string) ($row[$divisionCol] ?? '')));
                if ($divisionName !== '') {
                    $divisionId = $divisionMap[$divisionName] ?? null;
                    if ($divisionId === null) {
                        $errors[] = "Row {$rowNum}: division \"{$row[$divisionCol]}\" not found — participant \"{$name}\" imported without a division.";
                    }
                }
            }

            $attributes = [
                'name'        => $name,
                'phone'       => $phone,
                'identifier'  => $identifier,
                'division_id' => $divisionId,
                'status'      => $status,
                'deleted_at'  => null,
            ];

            if ($email !== null) {
                Participant::withTrashed()->updateOrCreate(['email' => $email], $attributes + ['email' => $email]);
            } elseif ($identifier !== null) {
                Participant::withTrashed()->updateOrCreate(['identifier' => $identifier], $attributes);
            } else {
                Participant::create($attributes);
            }

            $count++;
            $rowNum++;
        }

        if ($count === 0) {
            $errors[] = 'No data rows found (all rows were empty).';
        }

        return ['errors' => $errors, 'count' => $count];
    }
}
