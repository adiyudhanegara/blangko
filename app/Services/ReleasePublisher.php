<?php
namespace App\Services;

use App\Models\FormRelease;
use App\Models\ReleaseQuestion;
use Illuminate\Support\Str;

class ReleasePublisher
{
    public static function publish(FormRelease $release): void
    {
        if ($release->status !== 'scheduled') {
            throw new \RuntimeException("Release #{$release->id} is not in scheduled status.");
        }

        if (empty($release->public_token)) {
            $release->public_token = Str::random(32);
        }

        // Snapshot questions
        $questions = $release->form->questions()->with('options')->get();
        $oldToNew = []; // old question_id => new release_question_id

        foreach ($questions as $question) {
            $rq = ReleaseQuestion::create([
                'form_release_id'       => $release->id,
                'original_question_id'  => $question->id,
                'type'                  => $question->type,
                'label'                 => $question->label,
                'help_text'             => $question->help_text,
                'is_required'           => $question->is_required,
                'order'                 => $question->order,
                'validation_rules'      => $question->validation_rules,
                'conditional_value'     => $question->conditional_value,
                // conditional_parent_id resolved below
            ]);
            $oldToNew[$question->id] = $rq->id;

            foreach ($question->options as $option) {
                $rq->options()->create([
                    'label' => $option->label,
                    'value' => $option->value,
                    'order' => $option->order,
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

        $release->update([
            'status'       => 'open',
            'published_at' => now(),
        ]);
    }
}
