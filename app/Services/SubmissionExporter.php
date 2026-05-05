<?php
namespace App\Services;

use App\Models\FormRelease;
use App\Models\Participant;

class SubmissionExporter
{
    public function getRows(FormRelease $release): array
    {
        $questions = $release->releaseQuestions()->with('options')->get();
        $divisions = $release->divisions()->with('participants')->get();

        $participants = $divisions->flatMap(fn ($d) => $d->participants->map(function ($p) use ($d) {
            $p->division_name = $d->name;
            return $p;
        }))->unique('id');

        $submissionMap = $release->submissions()
            ->with(['answers.releaseQuestion'])
            ->get()
            ->keyBy('participant_id');

        $headers = ['Name', 'Email', 'Phone', 'Division', 'Submitted At'];
        foreach ($questions as $q) {
            $headers[] = $q->label;
        }

        $rows = [$headers];

        foreach ($participants as $participant) {
            $submission = $submissionMap->get($participant->id);
            $row = [
                $participant->name,
                $participant->email,
                $participant->phone,
                $participant->division_name,
                $submission?->submitted_at?->format('Y-m-d H:i:s') ?? '',
            ];

            if ($submission) {
                $answerMap = $submission->answers->keyBy('release_question_id');
                foreach ($questions as $q) {
                    $answer = $answerMap->get($q->id);
                    $row[] = $answer ? $answer->display_value : '';
                }
            } else {
                foreach ($questions as $q) {
                    $row[] = '';
                }
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
