<?php
namespace App\Services;

use App\Models\ReleaseQuestion;

class ConditionalLogicEvaluator
{
    /**
     * Returns true if the question should be shown based on current answers.
     * @param ReleaseQuestion $question
     * @param array $answers keyed by release_question_id => value (string or array)
     */
    public static function isVisible(ReleaseQuestion $question, array $answers): bool
    {
        if (!$question->conditional_parent_id) {
            return true;
        }

        $parentValue = $answers[$question->conditional_parent_id] ?? null;

        if (is_array($parentValue)) {
            return in_array($question->conditional_value, $parentValue);
        }

        return (string) $parentValue === (string) $question->conditional_value;
    }
}
