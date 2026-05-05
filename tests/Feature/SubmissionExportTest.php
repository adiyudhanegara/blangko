<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Division;
use App\Models\Form;
use App\Models\FormRelease;
use App\Models\Participant;
use App\Models\ReleaseQuestion;
use App\Models\Submission;
use App\Models\User;
use App\Services\SubmissionExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_includes_all_participants_and_non_submitters(): void
    {
        $admin = User::factory()->create();
        $division = Division::create(['name' => 'Eng', 'slug' => 'eng']);

        $p1 = Participant::create(['name' => 'Alice', 'email' => 'alice@test.com', 'division_id' => $division->id]);
        $p2 = Participant::create(['name' => 'Bob',   'email' => 'bob@test.com',   'division_id' => $division->id]);

        $form = Form::create([
            'title' => 'F', 'language' => 'id', 'status' => 'published',
            'allow_edit_after_submit' => true, 'created_by' => $admin->id,
        ]);

        $release = FormRelease::create([
            'form_id' => $form->id, 'name' => 'R', 'start_at' => now()->subDay(),
            'end_at' => now()->addDays(7), 'status' => 'open', 'created_by' => $admin->id,
        ]);
        $release->divisions()->attach($division->id);

        $rq = ReleaseQuestion::create([
            'form_release_id' => $release->id,
            'original_question_id' => null,
            'type' => 'text', 'label' => 'Name', 'order' => 1,
        ]);

        $submission = Submission::create([
            'form_release_id' => $release->id,
            'participant_id'  => $p1->id,
            'status'          => 'submitted',
            'submitted_at'    => now(),
        ]);
        Answer::create([
            'submission_id'       => $submission->id,
            'release_question_id' => $rq->id,
            'value'               => 'Alice Smith',
        ]);

        $exporter = new SubmissionExporter();
        $rows = $exporter->getRows($release);

        // Row 0 is header
        $this->assertEquals('Name', $rows[0][0]);

        $names = array_column(array_slice($rows, 1), 0);
        $this->assertContains('Alice', $names);
        $this->assertContains('Bob', $names);

        $aliceRow = collect(array_slice($rows, 1))->firstWhere(0, 'Alice');
        $this->assertNotEmpty($aliceRow[4]); // submitted_at

        $bobRow = collect(array_slice($rows, 1))->firstWhere(0, 'Bob');
        $this->assertEmpty($bobRow[4]); // no submission
    }
}
