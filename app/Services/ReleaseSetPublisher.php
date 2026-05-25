<?php

namespace App\Services;

use App\Models\FormRelease;
use App\Models\ReleaseQuestion;
use App\Models\ReleaseSet;

class ReleaseSetPublisher
{
    /**
     * Publish a ReleaseSet: transition status to 'open' and snapshot every
     * attached FormRelease that has not yet been published.
     */
    public static function publish(ReleaseSet $set): void
    {
        if ($set->status !== 'scheduled') {
            throw new \RuntimeException("Release set #{$set->id} is not in 'scheduled' status.");
        }

        foreach ($set->formReleases as $release) {
            if ($release->published_at === null) {
                static::snapshotRelease($release);
            }
        }

        $set->update(['status' => 'open']);
    }

    /**
     * Reopen a closed ReleaseSet and snapshot any FormReleases added since it was closed.
     */
    public static function reopen(ReleaseSet $set): void
    {
        if ($set->status !== 'closed') {
            throw new \RuntimeException("Release set #{$set->id} is not in 'closed' status.");
        }

        foreach ($set->formReleases as $release) {
            if ($release->published_at === null) {
                static::snapshotRelease($release);
            }
        }

        $set->update(['status' => 'open']);
    }

    /**
     * Republish an open ReleaseSet: force re-snapshot every FormRelease that has
     * no submissions yet. FormReleases with existing submissions are skipped to
     * avoid cascading deletion of answer data.
     *
     * Returns ['updated' => int, 'skipped' => int].
     */
    public static function republish(ReleaseSet $set): array
    {
        if ($set->status !== 'open') {
            throw new \RuntimeException("Release set #{$set->id} is not in 'open' status.");
        }

        $updated = 0;
        $skipped = 0;

        foreach ($set->formReleases as $release) {
            if ($release->submissions()->exists()) {
                $skipped++;
                continue;
            }
            static::forceSnapshotRelease($release);
            $updated++;
        }

        return ['updated' => $updated, 'skipped' => $skipped];
    }

    /**
     * Snapshot a single FormRelease (used when a release is added to an already-open set).
     * Idempotent: if questions already exist, only updates published_at without duplicating.
     */
    public static function snapshotRelease(FormRelease $release): void
    {
        if ($release->releaseQuestions()->exists()) {
            $release->update(['published_at' => now()]);
            return;
        }

        static::buildSnapshot($release);
    }

    /**
     * Force re-snapshot a FormRelease by deleting its existing release questions
     * (options cascade via DB constraint) and re-creating from the current form.
     * Only call this when the release has no submissions.
     */
    public static function forceSnapshotRelease(FormRelease $release): void
    {
        $release->releaseQuestions()->delete();

        static::buildSnapshot($release);
    }

    private static function buildSnapshot(FormRelease $release): void
    {
        $questions = $release->form->questions()->with('options')->get();
        $oldToNew  = [];

        foreach ($questions as $question) {
            $rq = ReleaseQuestion::create([
                'form_release_id'                => $release->id,
                'original_question_id'           => $question->id,
                'type'                           => $question->type,
                'label'                          => $question->label,
                'help_text'                      => $question->help_text,
                'is_required'                    => $question->is_required,
                'order'                          => $question->order,
                'validation_rules'               => $question->validation_rules,
                'conditional_value'              => $question->conditional_value,
                'allow_duplicate_in_new_submission' => $question->allow_duplicate_in_new_submission,
            ]);
            $oldToNew[$question->id] = $rq->id;

            foreach ($question->options as $option) {
                $rq->options()->create([
                    'label'    => $option->label,
                    'value'    => $option->value,
                    'order'    => $option->order,
                    'is_other' => $option->is_other,
                ]);
            }
        }

        // Resolve conditional parent references
        foreach ($questions as $question) {
            if ($question->conditional_parent_id && isset($oldToNew[$question->conditional_parent_id])) {
                ReleaseQuestion::where('id', $oldToNew[$question->id])
                    ->update(['conditional_parent_id' => $oldToNew[$question->conditional_parent_id]]);
            }
        }

        $release->update(['published_at' => now()]);
    }
}
