<?php
namespace App\Imports;

use App\Models\QuestionOption;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OptionsSheetImport implements ToCollection, WithHeadingRow
{
    private array $questionMap;
    private array $errors;

    public function __construct(array $questionMap, array $errors)
    {
        $this->questionMap = $questionMap;
        $this->errors = $errors;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $questionOrder = (int) ($row['question_order'] ?? 0);
            $question = $this->questionMap[$questionOrder] ?? null;

            if (!$question) {
                $this->errors[] = "Options row {$rowNum}: no question found for order {$questionOrder}.";
                continue;
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'label'       => $row['label'],
                'value'       => $row['value'],
                'order'       => (int) ($row['option_order'] ?? ($index + 1)),
            ]);
        }
    }
}
