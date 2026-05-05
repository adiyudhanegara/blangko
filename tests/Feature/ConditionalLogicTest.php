<?php

namespace Tests\Feature;

use App\Models\ReleaseQuestion;
use App\Services\ConditionalLogicEvaluator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConditionalLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_question_without_conditional_is_always_visible(): void
    {
        $q = new ReleaseQuestion([
            'form_release_id' => 1, 'type' => 'text', 'label' => 'Name', 'order' => 1,
        ]);

        $this->assertTrue(ConditionalLogicEvaluator::isVisible($q, []));
    }

    public function test_question_is_visible_when_parent_matches(): void
    {
        $q = new ReleaseQuestion([
            'form_release_id'       => 1,
            'type'                  => 'text',
            'label'                 => 'Company Name',
            'order'                 => 2,
            'conditional_parent_id' => 99,
            'conditional_value'     => 'yes',
        ]);

        $this->assertTrue(ConditionalLogicEvaluator::isVisible($q, [99 => 'yes']));
    }

    public function test_question_is_hidden_when_parent_does_not_match(): void
    {
        $q = new ReleaseQuestion([
            'form_release_id'       => 1,
            'type'                  => 'text',
            'label'                 => 'Company Name',
            'order'                 => 2,
            'conditional_parent_id' => 99,
            'conditional_value'     => 'yes',
        ]);

        $this->assertFalse(ConditionalLogicEvaluator::isVisible($q, [99 => 'no']));
    }

    public function test_question_is_visible_when_parent_is_checkbox_containing_value(): void
    {
        $q = new ReleaseQuestion([
            'form_release_id'       => 1,
            'type'                  => 'text',
            'label'                 => 'Detail',
            'order'                 => 3,
            'conditional_parent_id' => 99,
            'conditional_value'     => 'leadership',
        ]);

        $this->assertTrue(ConditionalLogicEvaluator::isVisible($q, [99 => ['communication', 'leadership']]));
        $this->assertFalse(ConditionalLogicEvaluator::isVisible($q, [99 => ['communication']]));
    }
}
