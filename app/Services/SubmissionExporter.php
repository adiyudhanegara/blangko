<?php
namespace App\Services;

use App\Models\Answer;
use App\Models\FormRelease;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SubmissionExporter
{
    private static array $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** @var array<array{cell:string,path:string,name:string,url:string}> */
    private array $images = [];

    /** @var array<array{cell:string,url:string}> cells that hold filename text + need a hyperlink */
    private array $urlCells = [];

    /** @var int[] Excel row numbers that contain an image (for row-height adjustment) */
    private array $imageRows = [];

    public function getImages(): array   { return $this->images; }
    public function getUrlCells(): array { return $this->urlCells; }
    public function getImageRows(): array { return array_unique($this->imageRows); }

    public function getRows(FormRelease $release): array
    {
        $this->images    = [];
        $this->urlCells  = [];
        $this->imageRows = [];

        $release->load(['releaseSet.divisions.participants', 'releaseQuestions.options']);

        $questions  = $release->releaseQuestions;
        $releaseSet = $release->releaseSet;

        $participants = $releaseSet
            ? $releaseSet->divisions->flatMap(fn ($d) => $d->participants->map(function ($p) use ($d) {
                $p->division_name = $d->name;
                return $p;
            }))->unique('id')
            : collect();

        $isMulti = $release->allowsMultipleSubmissions();

        $headers = ['Name', 'Email', 'Phone', 'Division', 'Status', 'Submitted At'];
        foreach ($questions as $q) {
            $headers[] = $q->label;
        }

        $rows = [$headers];

        if ($isMulti) {
            $submissions = $release->submissions()
                ->with(['participant.division', 'answers.releaseQuestion'])
                ->orderBy('participant_id')
                ->orderBy('submitted_at')
                ->get();

            $excelRow = 2;
            foreach ($submissions as $submission) {
                $p   = $submission->participant;
                $row = [
                    $p->name,
                    $p->email,
                    $p->phone,
                    $p->division?->name ?? '',
                    $submission->status,
                    $submission->submitted_at?->format('Y-m-d H:i:s') ?? '',
                ];

                $answerMap = $submission->answers->keyBy('release_question_id');
                $this->appendAnswerCells($row, $questions, $answerMap, $excelRow);

                $rows[] = $row;
                $excelRow++;
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

            $excelRow = 2;
            foreach ($participants as $participant) {
                $submission = $submissionMap->get($participant->id)
                           ?? $draftMap->get($participant->id);

                $row = [
                    $participant->name,
                    $participant->email,
                    $participant->phone,
                    $participant->division_name ?? '',
                    $submission?->status ?? 'not started',
                    $submission?->submitted_at?->format('Y-m-d H:i:s') ?? '',
                ];

                if ($submission) {
                    $answerMap = $submission->answers->keyBy('release_question_id');
                    $this->appendAnswerCells($row, $questions, $answerMap, $excelRow);
                } else {
                    foreach ($questions as $q) {
                        $row[] = '';
                    }
                }

                $rows[] = $row;
                $excelRow++;
            }
        }

        return $rows;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function appendAnswerCells(array &$row, $questions, $answerMap, int $excelRow): void
    {
        $qColIndex = 0; // fixed cols A-F (1-6), questions start at G (7)

        foreach ($questions as $q) {
            $answer = $answerMap->get($q->id);

            if ($q->type === 'file' && $answer) {
                $cellRef = Coordinate::stringFromColumnIndex(7 + $qColIndex) . $excelRow;
                $row[]   = $this->buildFileCell($answer, $cellRef);
            } else {
                $row[] = $answer ? $answer->display_value : '';
            }

            $qColIndex++;
        }
    }

    private function buildFileCell(Answer $answer, string $cellRef): string
    {
        // ── Single file ───────────────────────────────────────────────
        if ($answer->file_path) {
            $absPath = storage_path('app/' . $answer->file_path);
            $ext     = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
            $name    = $answer->file_original_name ?? basename($absPath);
            $url     = route('admin.file.serve', $answer->id);

            if (file_exists($absPath) && in_array($ext, self::$imageExts, true)) {
                // Image → Drawing (floats over cell) + hyperlink on the drawing
                $this->images[]    = compact('cellRef', 'absPath', 'name', 'url') + ['cell' => $cellRef, 'path' => $absPath];
                $this->imageRows[] = $this->rowNum($cellRef);
                return ''; // cell is empty; image sits on top
            }

            // Non-image → filename text + Excel hyperlink
            $this->urlCells[] = ['cell' => $cellRef, 'url' => $url];
            return $name;
        }

        // ── Multiple files ────────────────────────────────────────────
        if (!empty($answer->file_paths)) {
            // Cell shows all filenames; hyperlink points to first file
            $names   = [];
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
