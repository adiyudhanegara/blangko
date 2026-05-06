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
     * Snapshot a single FormRelease (used when a release is added to an already-open set).
     */
    public static function snapshotRelease(FormRelease $release): void
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
