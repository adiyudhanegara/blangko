<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Form;
use App\Models\FormRelease;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\ReleaseQuestion;
use App\Models\User;
use App\Services\ReleasePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReleasePublisherTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Form $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        $this->form = Form::create([
            'title'                   => 'Test Form',
            'language'                => 'id',
            'status'                  => 'published',
            'allow_edit_after_submit' => true,
            'created_by'              => $this->admin->id,
        ]);
    }

    public function test_publish_snapshots_questions(): void
    {
        $q1 = Question::create([
            'form_id'     => $this->form->id,
            'type'        => 'text',
            'label'       => 'Name',
            'is_required' => true,
            'order'       => 1,
        ]);

        $q2 = Question::create([
            'form_id'     => $this->form->id,
            'type'        => 'radio',
            'label'       => 'Employed?',
            'is_required' => true,
            'order'       => 2,
        ]);
        QuestionOption::create(['question_id' => $q2->id, 'label' => 'Yes', 'value' => 'yes', 'order' => 1]);
        QuestionOption::create(['question_id' => $q2->id, 'label' => 'No',  'value' => 'no',  'order' => 2]);

        $release = FormRelease::create([
            'form_id'    => $this->form->id,
            'name'       => 'Test Release',
            'start_at'   => now()->subMinute(),
            'end_at'     => now()->addDays(7),
            'status'     => 'scheduled',
            'created_by' => $this->admin->id,
        ]);

        ReleasePublisher::publish($release);

        $release->refresh();
        $this->assertEquals('open', $release->status);
        $this->assertNotNull($release->published_at);

        $snapshots = ReleaseQuestion::where('form_release_id', $release->id)->get();
        $this->assertCount(2, $snapshots);

        $radioSnapshot = $snapshots->firstWhere('type', 'radio');
        $this->assertCount(2, $radioSnapshot->options);
    }

    public function test_publish_resolves_conditional_parents(): void
    {
        $q1 = Question::create([
            'form_id' => $this->form->id, 'type' => 'radio', 'label' => 'Q1', 'order' => 1,
        ]);
        $q2 = Question::create([
            'form_id'               => $this->form->id,
            'type'                  => 'text',
            'label'                 => 'Q2',
            'order'                 => 2,
            'conditional_parent_id' => $q1->id,
            'conditional_value'     => 'yes',
        ]);

        $release = FormRelease::create([
            'form_id' => $this->form->id, 'name' => 'R', 'start_at' => now()->subMinute(),
            'end_at' => now()->addDays(7), 'status' => 'scheduled', 'created_by' => $this->admin->id,
        ]);

        ReleasePublisher::publish($release);

        $snapshots = ReleaseQuestion::where('form_release_id', $release->id)->orderBy('order')->get();
        $this->assertNotNull($snapshots[1]->conditional_parent_id);
        $this->assertEquals($snapshots[0]->id, $snapshots[1]->conditional_parent_id);
    }

    public function test_publish_throws_if_not_scheduled(): void
    {
        $release = FormRelease::create([
            'form_id' => $this->form->id, 'name' => 'R', 'start_at' => now()->subDay(),
            'end_at' => now()->addDays(7), 'status' => 'open', 'created_by' => $this->admin->id,
        ]);

        $this->expectException(\RuntimeException::class);
        ReleasePublisher::publish($release);
    }

    public function test_editing_form_after_publish_does_not_affect_snapshot(): void
    {
        Question::create(['form_id' => $this->form->id, 'type' => 'text', 'label' => 'Original', 'order' => 1]);

        $release = FormRelease::create([
            'form_id' => $this->form->id, 'name' => 'R', 'start_at' => now()->subMinute(),
            'end_at' => now()->addDays(7), 'status' => 'scheduled', 'created_by' => $this->admin->id,
        ]);

        ReleasePublisher::publish($release);

        // Edit original question
        $this->form->questions()->first()->update(['label' => 'Modified']);

        $snapshot = ReleaseQuestion::where('form_release_id', $release->id)->first();
        $this->assertEquals('Original', $snapshot->label);
    }
}
