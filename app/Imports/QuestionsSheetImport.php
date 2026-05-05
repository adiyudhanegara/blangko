<?php
namespace App\Imports;

use App\Models\Question;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsSheetImport implements ToCollection, WithHeadingRow
{
    private array $questionMap;
    private array $errors;

    private static array $validTypes = ['text','textarea','number','email','date','radio','checkbox','select','file'];

    public function __construct(
        private readonly int $formId,
        private readonly bool $replaceAll,
        array $questionMap,
        array $errors,
    ) {
        $this->questionMap = $questionMap;
        $this->errors = $errors;
    }

    public function collection(Collection $rows): void
    {
        if ($this->replaceAll) {
            Question::where('form_id', $this->formId)->delete();
        }

        $pendingConditionals = []; // order => conditional_parent_order

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            if (empty($row['label'])) {
                $this->errors[] = "Questions row {$rowNum}: label is required.";
                continue;
            }
            if (!in_array($row['type'] ?? '', self::$validTypes)) {
                $this->errors[] = "Questions row {$rowNum}: invalid type '{$row['type']}'.";
                continue;
            }

            $rules = null;
            if (!empty($row['validation_rules'])) {
                $decoded = json_decode($row['validation_rules'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->errors[] = "Questions row {$rowNum}: invalid JSON in validation_rules.";
                    continue;
                }
                $rules = $decoded;
            }

            $question = Question::create([
                'form_id'          => $this->formId,
                'type'             => $row['type'],
                'label'            => $row['label'],
                'help_text'        => $row['help_text'] ?? null,
                'is_required'      => strtolower($row['is_required'] ?? 'no') === 'yes',
                'order'            => (int) ($row['order'] ?? ($index + 1)),
                'validation_rules' => $rules,
                'conditional_value' => $row['conditional_value'] ?? null,
            ]);

            $this->questionMap[(int) $row['order']] = $question;

            if (!empty($row['conditional_parent_order'])) {
                $pendingConditionals[(int) $row['order']] = (int) $row['conditional_parent_order'];
            }
        }

        // Resolve conditional parents
        foreach ($pendingConditionals as $order => $parentOrder) {
            $question = $this->questionMap[$order] ?? null;
            $parent = $this->questionMap[$parentOrder] ?? null;
            if ($question && $parent) {
                $question->update(['conditional_parent_id' => $parent->id]);
            }
        }
    }
}
