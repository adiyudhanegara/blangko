<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormRelease;
use App\Models\Participant;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_is_unique_per_participant_per_release(): void
    {
        $admin = User::factory()->create();
        $form = Form::create([
            'title' => 'F', 'language' => 'id', 'status' => 'published',
            'allow_edit_after_submit' => true, 'created_by' => $admin->id,
        ]);
        $release = FormRelease::create([
            'form_id' => $form->id, 'name' => 'R', 'start_at' => now()->subDay(),
            'end_at' => now()->addDays(7), 'status' => 'open', 'created_by' => $admin->id,
        ]);
        $participant = Participant::create(['name' => 'Test', 'email' => 'test@example.com']);

        Submission::create([
            'form_release_id' => $release->id,
            'participant_id'  => $participant->id,
            'status'          => 'in_progress',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Submission::create([
            'form_release_id' => $release->id,
            'participant_id'  => $participant->id,
            'status'          => 'in_progress',
        ]);
    }
}
