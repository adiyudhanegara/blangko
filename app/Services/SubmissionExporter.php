<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\FormExportTemplate;
use App\Models\FormRelease;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SubmissionExporter
{
    private static array $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** @var array<array{cell:string,path:string,name:string,url:string}> */
    private array $images = [];

    /** @var array<array{cell:string,url:string}> */
    private array $urlCells = [];

    /** @var int[] Excel row numbers that contain an image (for height adjustment) */
    private array $imageRows = [];

    private int $tableHeaderRow = 1;
    private int $dataStartRow   = 2;
    private int $totalCols      = 0;
    private ?FormExportTemplate $template = null;

    public function getImages(): array              { return $this->images; }
    public function getUrlCells(): array            { return $this->urlCells; }
    public function getImageRows(): array           { return array_unique($this->imageRows); }
    public function getTableHeaderRow(): int        { return $this->tableHeaderRow; }
    public function getDataStartRow(): int          { return $this->dataStartRow; }
    public function getTotalCols(): int             { return $this->totalCols; }
    public function getTemplate(): ?FormExportTemplate { return $this->template; }

    public function getRows(FormRelease $release): array
    {
        $this->images    = [];
        $this->urlCells  = [];
        $this->imageRows = [];

        $release->load([
            'form.exportTemplate',
            'releaseSet.divisions.participants.division',
            'releaseQuestions.options',
        ]);

        $this->template = $release->form?->exportTemplate;
        $questions      = $release->releaseQuestions;
        $releaseSet     = $release->releaseSet;

        [$participantCols, $questionList] = $this->resolveColumns($questions);

        $hasNo           = (bool) ($this->template?->show_auto_number);
        $this->totalCols = ($hasNo ? 1 : 0) + count($participantCols) + count($questionList);

        $rows = [];

        // ── Template pre-rows ─────────────────────────────────────────────
        if ($this->template) {
            $blank = array_fill(0, $this->totalCols, '');

            // Row 1: title
            $titleRow    = $blank;
            $titleRow[0] = $this->template->title_text ?? '';
            $rows[]      = $titleRow;

            // Row 2: subtitle
            $subRow    = $blank;
            $subRow[0] = $this->template->resolveSubtitle($releaseSet?->period_label);
            $rows[]    = $subRow;

            // Row 3: empty spacer
            $rows[] = $blank;

            $this->tableHeaderRow = 4;
            $this->dataStartRow   = 5;
        } else {
            $this->tableHeaderRow = 1;
            $this->dataStartRow   = 2;
        }

        // ── Column header row ─────────────────────────────────────────────
        $headers = [];
        if ($hasNo) {
            $headers[] = $this->template->auto_number_label ?: 'NO';
        }
        foreach ($participantCols as $col) {
            $headers[] = $col['label'];
        }
        foreach ($questionList as $q) {
            $headers[] = $q->label;
        }
        $rows[] = $headers;

        // ── Collect participants from divisions linked to the release set ──
        $participants = $releaseSet
            ? $releaseSet->divisions
                ->flatMap(fn ($d) => $d->participants->map(function ($p) use ($d) {
                    $p->division_name = $d->name;
                    return $p;
                }))
                ->unique('id')
            : collect();

        $isMulti = $release->allowsMultipleSubmissions();

        if ($isMulti) {
            $submissions = $release->submissions()
                ->with(['participant.division', 'answers.releaseQuestion'])
                ->orderBy('participant_id')
                ->orderBy('submitted_at')
                ->get();

            $autoNum = 1;
            foreach ($submissions as $submission) {
                $excelRow  = count($rows) + 1;
                $answerMap = $submission->answers->keyBy('release_question_id');
                $rows[]    = $this->buildDataRow(
                    $submission->participant,
                    $submission,
                    $participantCols,
                    $questionList,
                    $answerMap,
                    $excelRow,
                    $hasNo ? $autoNum++ : null,
                );
            }
        } else {
            $submissionMap = $release->submissions()
                ->with(['answers.releaseQuestion'])
                ->where('status', 'submitted')
                ->get()
                ->keyBy('participant_id');

            $draftMap = $release->submissions()
                ->with(['answers.releaseQuestion'])
                ->where('status', 'draft')
                ->get()
                ->keyBy('participant_id');

            $autoNum = 1;
            foreach ($participants as $participant) {
                $submission = $submissionMap->get($participant->id)
                           ?? $draftMap->get($participant->id);

                $excelRow  = count($rows) + 1;
                $answerMap = $submission
                    ? $submission->answers->keyBy('release_question_id')
                    : collect();

                $rows[] = $this->buildDataRow(
                    $participant,
                    $submission,
                    $participantCols,
                    $questionList,
                    $answerMap,
                    $excelRow,
                    $hasNo ? $autoNum++ : null,
                );
            }
        }

        return $rows;
    }

    // ── Column resolution ──────────────────────────────────────────────────

    private function resolveColumns($questions): array
    {
        if (!$this->template) {
            // Default: fixed metadata columns; questions start at column G
            $participantCols = [
                ['field' => 'id',           'label' => 'Submission ID'],
                ['field' => 'name',         'label' => 'Participant Name'],
                ['field' => 'nip',          'label' => 'Participant Code'],
                ['field' => 'status',       'label' => 'Status'],
                ['field' => 'submitted_at', 'label' => 'Submitted At'],
                ['field' => 'updated_at',   'label' => 'Updated At'],
            ];
            return [$participantCols, $questions->all()];
        }

        $configured = $this->template->participant_columns
            ?? FormExportTemplate::defaultParticipantColumns();

        $enabled = array_values(array_filter($configured, fn ($c) => $c['enabled'] ?? false));

        return [$enabled, $questions->all()];
    }

    // ── Data row builder ───────────────────────────────────────────────────

    private function buildDataRow(
        $participant,
        $submission,
        array $participantCols,
        array $questionList,
        $answerMap,
        int $excelRow,
        ?int $autoNum,
    ): array {
        $row = [];

        if ($autoNum !== null) {
            $row[] = $autoNum;
        }

        foreach ($participantCols as $col) {
            $row[] = match ($col['field']) {
                'id'           => $submission?->id ?? '',
                'name'         => $participant?->name ?? '',
                'email'        => $participant?->email ?? '',
                'phone'        => $participant?->phone ?? '',
                'division'     => $participant?->division?->name ?? $participant?->division_name ?? '',
                'identifier'   => $participant?->identifier ?? '',
                'nip'          => $participant?->nip ?? '',
                'position'     => $participant?->position ?? '',
                'status'       => $submission?->status ?? 'not started',
                'submitted_at' => $submission?->submitted_at?->format('Y-m-d H:i:s') ?? '',
                'updated_at'   => $submission?->updated_at?->format('Y-m-d H:i:s') ?? '',
                default        => '',
            };
        }

        // 1-based column index where question columns begin
        $firstQColIdx = count($row) + 1;

        foreach ($questionList as $qIdx => $q) {
            $answer = $answerMap->get($q->id);

            if ($q->type === 'file' && $answer) {
                $cellRef = Coordinate::stringFromColumnIndex($firstQColIdx + $qIdx) . $excelRow;
                $row[]   = $this->buildFileCell($answer, $cellRef);
            } else {
                $row[] = $answer ? $answer->display_value : '';
            }
        }

        return $row;
    }

    // ── File cell handling ─────────────────────────────────────────────────

    private function buildFileCell(Answer $answer, string $cellRef): string
    {
        if ($answer->file_path) {
            $absPath = storage_path('app/' . $answer->file_path);
            $ext     = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
            $name    = $answer->file_original_name ?? basename($absPath);
            $url     = route('admin.file.serve', $answer->id);

            if (file_exists($absPath) && in_array($ext, self::$imageExts, true)) {
                $this->images[]    = ['cell' => $cellRef, 'path' => $absPath, 'name' => $name, 'url' => $url];
                $this->imageRows[] = $this->rowNum($cellRef);
                return '';
            }

            $this->urlCells[] = ['cell' => $cellRef, 'url' => $url];
            return $name;
        }

        if (!empty($answer->file_paths)) {
            $names    = [];
            $firstUrl = null;

            foreach ($answer->file_paths as $idx => $f) {
                $names[] = $f['original_name'] ?? basename($f['path']);
                if ($firstUrl === null) {
                    $firstUrl = route('admin.file.serve', [$answer->id, $idx]);
                }
            }

            if ($firstUrl) {
                $this->urlCells[] = ['cell' => $cellRef, 'url' => $firstUrl];
            }

            return implode("\n", $names);
        }

        return '';
    }

    private function rowNum(string $cellRef): int
    {
        return (int) preg_replace('/[A-Z]+/', '', $cellRef);
    }
}
