<?php
namespace App\Imports;

use App\Models\Form;
use App\Models\Question;
use App\Models\QuestionOption;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QuestionsImport implements WithMultipleSheets
{
    public array $errors = [];
    private array $questionMap = []; // order => Question

    public function __construct(
        private readonly int $formId,
        private readonly bool $replaceAll = false,
    ) {}

    public function sheets(): array
    {
        return [
            0 => new QuestionsSheetImport($this->formId, $this->replaceAll, $this->questionMap, $this->errors),
            1 => new OptionsSheetImport($this->questionMap, $this->errors),
        ];
    }
}
