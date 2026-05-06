<?php
namespace App\Services;

use App\Models\FormRelease;

class SubmissionExporter
{
    public function getRows(FormRelease $release): array
    {
        $release->load(['releaseSet.divisions.participants', 'releaseQuestions.options']);

        $questions = $release->releaseQuestions;
        $releaseSet = $release->releaseSet;

        // Build participant list from the release set's divisions
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
            // One row per submission (multiple rows per participant)
            $submissions = $release->submissions()
                ->with(['participant.division', 'answers.releaseQuestion'])
                ->orderBy('participant_id')
                ->orderBy('submitted_at')
                ->get();

            foreach ($submissions as $submission) {
                $p = $submission->participant;
                $row = [
                    $p->name,
                    $p->email,
                    $p->phone,
                    $p->division?->name ?? '',
                    $submission->status,
                    $submission->submitted_at?->format('Y-m-d H:i:s') ?? '',
                ];

                $answerMap = $submission->answers->keyBy('release_question_id');
                foreach ($questions as $q) {
                    $answer = $answerMap->get($q->id);
                    $row[]  = $answer ? $answer->display_value : '';
                }

                $rows[] = $row;
            }
        } else {
            // One row per participant (single-submission)
            $submissionMap = $release->submissions()
                ->with(['answers.releaseQuestion'])
                ->where('status', 'submitted')
                ->get()
                ->keyBy('participant_id');

            // Also include draft if no submitted version exists
            $draftMap = $release->submissions()
                ->with(['answers.releaseQuestion'])
                ->where('status', 'draft')
                ->get()
                ->keyBy('participant_id');

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
                    foreach ($questions as $q) {
                        $answer = $answerMap->get($q->id);
                        $row[]  = $answer ? $answer->display_value : '';
                    }
                } else {
                    foreach ($questions as $q) {
                        $row[] = '';
                    }
                }

                $rows[] = $row;
            }
        }

        return $rows;
    }
}
