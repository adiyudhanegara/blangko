<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\ReleaseSet;

class CompletionCalculator
{
    /**
     * Returns true when the participant has satisfied every *required* form release
     * in the release set.
     */
    public function isComplete(ReleaseSet $set, Participant $participant): bool
    {
        foreach ($set->formReleases as $release) {
            if (! $release->is_required) {
                continue;
            }

            if (! $this->isSatisfied($release, $participant)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true when a participant has satisfied a single FormRelease.
     *
     * Rules:
     * - Single-submission form: at least 1 submitted submission.
     * - Multi-submission form with min_submissions_required: at least N submitted submissions.
     * - Multi-submission form without minimum: at least 1 submitted submission.
     */
    public function isSatisfied(\App\Models\FormRelease $release, Participant $participant): bool
    {
        $submittedCount = $release->submissions()
            ->where('participant_id', $participant->id)
            ->where('status', 'submitted')
            ->count();

        $min = $release->min_submissions_required ?? 1;

        return $submittedCount >= $min;
    }

    /**
     * Returns completion stats for a release set.
     *
     * @return array{total: int, complete: int, percentage: float}
     */
    public function stats(ReleaseSet $set): array
    {
        $set->load(['divisions.participants', 'formReleases']);

        $total    = 0;
        $complete = 0;

        foreach ($set->divisions as $division) {
            foreach ($division->participants as $participant) {
                $total++;
                if ($this->isComplete($set, $participant)) {
                    $complete++;
                }
            }
        }

        return [
            'total'      => $total,
            'complete'   => $complete,
            'percentage' => $total > 0 ? round($complete / $total * 100, 1) : 0.0,
        ];
    }

    /**
     * Returns participants who have NOT completed the release set.
     *
     * @return \Illuminate\Support\Collection<int, Participant>
     */
    public function nonSubmitters(ReleaseSet $set): \Illuminate\Support\Collection
    {
        $set->load(['divisions.participants', 'formReleases']);

        $pending = collect();

        foreach ($set->divisions as $division) {
            foreach ($division->participants as $participant) {
                if (! $this->isComplete($set, $participant)) {
                    $pending->push($participant);
                }
            }
        }

        return $pending->unique('id')->values();
    }
}
