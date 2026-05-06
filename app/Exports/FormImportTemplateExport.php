<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FormImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new FormSheetTemplate(),
            new QuestionsSheetTemplate(),
            new OptionsSheetTemplate(),
            new ExportTemplateSheetTemplate(),
        ];
    }
}

// ── Sheet 1: form ─────────────────────────────────────────────────────────────

class FormSheetTemplate implements FromArray, WithTitle, WithStyles
{
    public function array(): array
    {
        return [
            // Header row
            ['title', 'description', 'language', 'status', 'allow_edit_after_submit', 'allow_multiple_submissions', 'allow_duplicate_from_previous'],
            // Example data row
            ['My Evaluation Form', 'Form description here', 'id', 'draft', 'yes', 'no', 'no'],
        ];
    }

    public function title(): string
    {
        return 'form';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

// ── Sheet 2: questions ────────────────────────────────────────────────────────

class QuestionsSheetTemplate implements FromArray, WithTitle, WithStyles
{
    public function array(): array
    {
        return [
            // Header row
            [
                'order', 'type', 'label', 'help_text', 'is_required',
                'validation_rules', 'conditional_parent_order', 'conditional_value',
                'allow_duplicate_in_new_submission',
            ],
            // Example rows — types: text textarea number email date radio checkbox select file
            [1, 'text',     'Full Name',  '',                  'yes', '', '', '', 'no'],
            [2, 'radio',    'Gender',     'Select one option', 'yes', '', '', '', 'no'],
            [3, 'checkbox', 'Skills',     '',                  'no',  '', '', '', 'no'],
            [4, 'textarea', 'Comments',   'Optional feedback', 'no',  '', 2,  'male', 'no'],
            [5, 'date',     'Date of Birth', '',               'no',  '', '', '', 'no'],
        ];
    }

    public function title(): string
    {
        return 'questions';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

// ── Sheet 3: options ──────────────────────────────────────────────────────────

class OptionsSheetTemplate implements FromArray, WithTitle, WithStyles
{
    public function array(): array
    {
        return [
            // Header row  (only for radio/checkbox/select questions)
            ['question_order', 'option_order', 'label', 'value', 'is_other'],
            // Options for question 2 (Gender — radio)
            [2, 1, 'Male',   'male',   'no'],
            [2, 2, 'Female', 'female', 'no'],
            // Options for question 3 (Skills — checkbox)
            [3, 1, 'JavaScript', 'javascript', 'no'],
            [3, 2, 'PHP',        'php',        'no'],
            [3, 3, 'Python',     'python',     'no'],
            [3, 4, 'Other',      'other',      'yes'],
        ];
    }

    public function title(): string
    {
        return 'options';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

// ── Sheet 4: export_template (optional) ───────────────────────────────────────

class ExportTemplateSheetTemplate implements FromArray, WithTitle, WithStyles
{
    public function array(): array
    {
        return [
            // Header row — leave blank or fill in to configure the Excel export layout
            [
                'title_text', 'subtitle_template', 'show_auto_number', 'auto_number_label',
                'signature_role', 'signature_name', 'signature_nip', 'signature_position',
            ],
            // Example (this entire sheet is optional — delete the data row to skip template)
            [
                'EVALUATION FORM', 'Period: {period_label}', 'no', 'No.',
                'Director', '', '', 'Head of Department',
            ],
        ];
    }

    public function title(): string
    {
        return 'export_template';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
