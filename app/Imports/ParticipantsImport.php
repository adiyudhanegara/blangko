<?php
namespace App\Imports;

use App\Models\Division;
use App\Models\Participant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;

class ParticipantsImport implements ToCollection, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    public array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            if (empty($row['name'])) {
                $this->errors[] = "Row {$rowNum}: name is required.";
                continue;
            }
            if (empty($row['email']) && empty($row['phone'])) {
                $this->errors[] = "Row {$rowNum}: email or phone is required.";
                continue;
            }

            $divisionId = null;
            if (!empty($row['division_name'])) {
                $division = Division::where('name', $row['division_name'])->first();
                if (!$division) {
                    $this->errors[] = "Row {$rowNum}: Division '{$row['division_name']}' not found.";
                    continue;
                }
                $divisionId = $division->id;
            }

            $existing = null;
            if (!empty($row['email'])) {
                $existing = Participant::where('email', $row['email'])->first();
            }
            if (!$existing && !empty($row['phone'])) {
                $existing = Participant::where('phone', $row['phone'])->first();
            }

            if ($existing) {
                $existing->update(['division_id' => $divisionId ?? $existing->division_id]);
            } else {
                Participant::create([
                    'name'        => $row['name'],
                    'email'       => $row['email'] ?: null,
                    'phone'       => $row['phone'] ?: null,
                    'division_id' => $divisionId,
                    'identifier'  => $row['identifier'] ?? null,
                    'status'      => 'active',
                ]);
            }
        }
    }
}
