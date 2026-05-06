<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormExportTemplate;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FormImportService
{
    private static array $validTypes = [
        'text', 'textarea', 'number', 'email', 'date',
        'radio', 'checkbox', 'select', 'file',
    ];
    private static array $validLanguages = ['id', 'en'];
    private static array $validStatuses  = ['draft', 'published', 'archived'];

    /**
     * Read, validate, and import a form from an Excel file.
     *
     * @return array{form: ?Form, errors: string[]}
     */
    public function import(string $absolutePath): array
    {
        try {
            $spreadsheet = IOFactory::load($absolutePath);
        } catch (\Throwable $e) {
            return ['form' => null, 'errors' => ['Could not read file: ' . $e->getMessage()]];
        }

        $rawSheets = [];
        foreach ($spreadsheet->getWorksheetIterator() as $i => $ws) {
            $rawSheets[$i] = $ws->toArray(null, true, true, false);
        }

        $errors = $this->validate($rawSheets);

        if (!empty($errors)) {
            return ['form' => null, 'errors' => $errors];
        }

        $form = DB::transaction(fn () => $this->persist($rawSheets));

        return ['form' => $form, 'errors' => []];
    }

    // ── Validation ────────────────────────────────────────────────────────

    private function validate(array $rawSheets): array
    {
        $errors = [];

        $errors = array_merge($errors, $this->validateFormSheet($rawSheets[0] ?? []));

        [$questionErrors, $questionOrders] = $this->validateQuestionsSheet($rawSheets[1] ?? []);
        $errors = array_merge($errors, $questionErrors);

        $errors = array_merge($errors, $this->validateOptionsSheet($rawSheets[2] ?? [], $questionOrders));

        return $errors;
    }

    private function validateFormSheet(array $sheet): array
    {
        $errors = [];

        if (empty($sheet)) {
            return ['Sheet "form" is empty or missing.'];
        }

        $headers = $this->parseHeaders($sheet[0] ?? []);
        $data    = $sheet[1] ?? null;

        if (empty($data) || $this->isEmptyRow($data)) {
            return ['Sheet "form": row 2 must contain the form details.'];
        }

        $row = $this->combineRow($headers, $data);

        if (empty($row['title'])) {
            $errors[] = 'Sheet "form": title is required.';
        }

        $lang = strtolower((string) ($row['language'] ?? ''));
        if (!in_array($lang, self::$validLanguages, true)) {
            $errors[] = 'Sheet "form": language must be one of: ' . implode(', ', self::$validLanguages) . '.';
        }

        $status = strtolower((string) ($row['status'] ?? 'draft'));
        if ($status !== '' && !in_array($status, self::$validStatuses, true)) {
            $errors[] = 'Sheet "form": status must be one of: ' . implode(', ', self::$validStatuses) . '.';
        }

        return $errors;
    }

    /** @return array{string[], int[]} */
    private function validateQuestionsSheet(array $sheet): array
    {
        $errors  = [];
        $orders  = [];

        if (empty($sheet)) {
            return [['Sheet "questions" is empty or missing.'], []];
        }

        $headers = $this->parseHeaders($sheet[0] ?? []);
        $hasData = false;

        for ($i = 1; $i < count($sheet); $i++) {
            if ($this->isEmptyRow($sheet[$i])) {
                continue;
            }
            $hasData = true;
            $rowNum  = $i + 1;
            $row     = $this->combineRow($headers, $sheet[$i]);

            if (empty($row['label'])) {
                $errors[] = "Sheet \"questions\" row {$rowNum}: label is required.";
            }

            $type = strtolower((string) ($row['type'] ?? ''));
            if (!in_array($type, self::$validTypes, true)) {
                $errors[] = "Sheet \"questions\" row {$rowNum}: type must be one of: " . implode(', ', self::$validTypes) . '.';
            }

            if (empty($row['order']) && $row['order'] !== '0') {
                $errors[] = "Sheet \"questions\" row {$rowNum}: order is required.";
            } else {
                $order = (int) $row['order'];
                if (in_array($order, $orders, true)) {
                    $errors[] = "Sheet \"questions\" row {$rowNum}: duplicate order {$order}.";
                }
                $orders[] = $order;
            }

            if (!empty($row['validation_rules'])) {
                json_decode((string) $row['validation_rules']);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errors[] = "Sheet \"questions\" row {$rowNum}: validation_rules is not valid JSON.";
                }
            }
        }

        if (!$hasData) {
            $errors[] = 'Sheet "questions": no question rows found.';
        }

        // Second pass: validate conditional_parent_order references
        for ($i = 1; $i < count($sheet); $i++) {
            if ($this->isEmptyRow($sheet[$i])) {
                continue;
            }
            $rowNum = $i + 1;
            $row    = $this->combineRow($headers, $sheet[$i]);

            if (!empty($row['conditional_parent_order'])) {
                $parentOrder = (int) $row['conditional_parent_order'];
                if (!in_array($parentOrder, $orders, true)) {
                    $errors[] = "Sheet \"questions\" row {$rowNum}: conditional_parent_order {$parentOrder} does not match any question order.";
                }
            }
        }

        return [$errors, $orders];
    }

    private function validateOptionsSheet(array $sheet, array $questionOrders): array
    {
        $errors = [];

        if (empty($sheet) || count($sheet) <= 1) {
            return [];
        }

        $headers = $this->parseHeaders($sheet[0] ?? []);

        for ($i = 1; $i < count($sheet); $i++) {
            if ($this->isEmptyRow($sheet[$i])) {
                continue;
            }
            $rowNum = $i + 1;
            $row    = $this->combineRow($headers, $sheet[$i]);

            if (empty($row['question_order'])) {
                $errors[] = "Sheet \"options\" row {$rowNum}: question_order is required.";
            } elseif (!in_array((int) $row['question_order'], $questionOrders, true)) {
                $errors[] = "Sheet \"options\" row {$rowNum}: question_order {$row['question_order']} does not match any question.";
            }

            if (empty($row['label'])) {
                $errors[] = "Sheet \"options\" row {$rowNum}: label is required.";
            }

            if (empty($row['value'])) {
                $errors[] = "Sheet \"options\" row {$rowNum}: value is required.";
            }
        }

        return $errors;
    }

    // ── Persistence ───────────────────────────────────────────────────────

    private function persist(array $rawSheets): Form
    {
        $form = $this->persistForm($rawSheets[0]);

        $questionMap = $this->persistQuestions($rawSheets[1], $form->id);

        $this->persistOptions($rawSheets[2] ?? [], $questionMap);

        $this->persistExportTemplate($rawSheets[3] ?? [], $form->id);

        return $form;
    }

    private function persistForm(array $sheet): Form
    {
        $headers = $this->parseHeaders($sheet[0]);
        $row     = $this->combineRow($headers, $sheet[1]);

        return Form::create([
            'title'                         => $row['title'],
            'description'                   => $row['description'] ?? null,
            'language'                      => strtolower($row['language'] ?? 'id'),
            'status'                        => strtolower($row['status'] ?? 'draft'),
            'allow_edit_after_submit'       => $this->parseBool($row['allow_edit_after_submit'] ?? 'yes'),
            'allow_multiple_submissions'    => $this->parseBool($row['allow_multiple_submissions'] ?? 'no'),
            'allow_duplicate_from_previous' => $this->parseBool($row['allow_duplicate_from_previous'] ?? 'no'),
            'created_by'                    => auth()->id(),
        ]);
    }

    /** @return array<int, Question> keyed by question order */
    private function persistQuestions(array $sheet, int $formId): array
    {
        $headers     = $this->parseHeaders($sheet[0]);
        $questionMap = [];

        for ($i = 1; $i < count($sheet); $i++) {
            if ($this->isEmptyRow($sheet[$i])) {
                continue;
            }
            $row   = $this->combineRow($headers, $sheet[$i]);
            $order = (int) ($row['order'] ?? $i);

            $question = Question::create([
                'form_id'                          => $formId,
                'type'                             => strtolower($row['type']),
                'label'                            => $row['label'],
                'help_text'                        => $row['help_text'] ?? null,
                'is_required'                      => $this->parseBool($row['is_required'] ?? 'no'),
                'order'                            => $order,
                'validation_rules'                 => !empty($row['validation_rules'])
                                                        ? json_decode((string) $row['validation_rules'], true)
                                                        : null,
                'conditional_value'                => $row['conditional_value'] ?? null,
                'allow_duplicate_in_new_submission' => $this->parseBool($row['allow_duplicate_in_new_submission'] ?? 'no'),
            ]);

            $questionMap[$order] = $question;
        }

        // Second pass: wire conditional parents
        for ($i = 1; $i < count($sheet); $i++) {
            if ($this->isEmptyRow($sheet[$i])) {
                continue;
            }
            $row = $this->combineRow($headers, $sheet[$i]);
            if (!empty($row['conditional_parent_order'])) {
                $order       = (int) ($row['order'] ?? $i);
                $parentOrder = (int) $row['conditional_parent_order'];
                $q           = $questionMap[$order] ?? null;
                $parent      = $questionMap[$parentOrder] ?? null;
                if ($q && $parent) {
                    $q->update(['conditional_parent_id' => $parent->id]);
                }
            }
        }

        return $questionMap;
    }

    private function persistOptions(array $sheet, array $questionMap): void
    {
        if (empty($sheet) || count($sheet) <= 1) {
            return;
        }

        $headers = $this->parseHeaders($sheet[0]);

        for ($i = 1; $i < count($sheet); $i++) {
            if ($this->isEmptyRow($sheet[$i])) {
                continue;
            }
            $row      = $this->combineRow($headers, $sheet[$i]);
            $question = $questionMap[(int) ($row['question_order'] ?? 0)] ?? null;

            if (!$question) {
                continue;
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'label'       => $row['label'],
                'value'       => $row['value'],
                'order'       => (int) ($row['option_order'] ?? $i),
                'is_other'    => $this->parseBool($row['is_other'] ?? 'no'),
            ]);
        }
    }

    private function persistExportTemplate(array $sheet, int $formId): void
    {
        if (empty($sheet) || count($sheet) <= 1) {
            return;
        }

        $headers = $this->parseHeaders($sheet[0] ?? []);
        $rowData = $sheet[1] ?? [];

        if ($this->isEmptyRow($rowData)) {
            return;
        }

        $row = $this->combineRow($headers, $rowData);

        FormExportTemplate::create([
            'form_id'            => $formId,
            'title_text'         => $row['title_text'] ?? null,
            'subtitle_template'  => $row['subtitle_template'] ?? null,
            'show_auto_number'   => $this->parseBool($row['show_auto_number'] ?? 'no'),
            'auto_number_label'  => $row['auto_number_label'] ?? null,
            'signature_role'     => $row['signature_role'] ?? null,
            'signature_name'     => $row['signature_name'] ?? null,
            'signature_nip'      => $row['signature_nip'] ?? null,
            'signature_position' => $row['signature_position'] ?? null,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function parseHeaders(array $headerRow): array
    {
        return array_map(
            fn ($h) => strtolower(trim((string) $h)),
            $headerRow,
        );
    }

    private function combineRow(array $headers, array $row): array
    {
        $padded = array_pad($row, count($headers), null);
        return array_combine($headers, array_slice($padded, 0, count($headers)));
    }

    private function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, fn ($cell) => $cell !== null && $cell !== ''));
    }

    private function parseBool(mixed $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['yes', '1', 'true', 'y'], true);
    }
}
