<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Form;
use App\Models\FormRelease;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\ReleaseSet;
use App\Models\User;
use App\Services\ReleaseSetPublisher;
use Illuminate\Database\Seeder;

class ExampleFormSeeder extends Seeder
{
    public function run(): void
    {
        $admin     = User::where('email', 'admin@blangko.test')->firstOrFail();
        $divisions = Division::all();

        // ---------- Form ----------
        $form = Form::firstOrCreate(
            ['title' => 'Q2 2025 Performance Evaluation'],
            [
                'description'             => 'Quarterly performance evaluation for all teams.',
                'language'                => 'id',
                'status'                  => 'published',
                'allow_edit_after_submit' => true,
                'created_by'              => $admin->id,
            ]
        );

        $form->divisions()->sync($divisions->pluck('id'));

        if ($form->questions()->count() === 0) {
            $this->createQuestions($form);
        }

        // ---------- Release Set ----------
        $existed    = ReleaseSet::where('name', 'Q2 2025 Evaluation - All Teams')->exists();
        $releaseSet = ReleaseSet::firstOrCreate(
            ['name' => 'Q2 2025 Evaluation - All Teams'],
            [
                'description'       => 'Q2 2025 performance evaluation for all divisions.',
                'start_at'          => now()->subDay(),
                'end_at'            => now()->addDays(14),
                'status'            => 'scheduled',   // publish() transitions to 'open'
                'reminder_schedule' => [3, 1],
                'created_by'        => $admin->id,
            ]
        );

        $releaseSet->divisions()->sync($divisions->pluck('id'));

        // ---------- Form Release (one form per set) ----------
        FormRelease::firstOrCreate(
            ['release_set_id' => $releaseSet->id, 'form_id' => $form->id],
            [
                'is_required'              => true,
                'order'                    => 1,
                'min_submissions_required' => null,
                'created_by'               => $admin->id,
            ]
        );

        // Snapshot questions into release_questions and open the set (only on first seed)
        if (!$existed) {
            $releaseSet->refresh();
            ReleaseSetPublisher::publish($releaseSet);
        }
    }

    private function createQuestions(Form $form): void
    {
        // 1. Text
        Question::create([
            'form_id'          => $form->id,
            'type'             => 'text',
            'label'            => 'Full Name',
            'is_required'      => true,
            'order'            => 1,
            'validation_rules' => ['max' => 255],
        ]);

        // 2. Email
        Question::create([
            'form_id'     => $form->id,
            'type'        => 'email',
            'label'       => 'Work Email',
            'is_required' => true,
            'order'       => 2,
        ]);

        // 3. Radio with conditional
        $q3 = Question::create([
            'form_id'     => $form->id,
            'type'        => 'radio',
            'label'       => 'Are you currently working on a project?',
            'is_required' => true,
            'order'       => 3,
        ]);
        QuestionOption::create(['question_id' => $q3->id, 'label' => 'Yes', 'value' => 'yes', 'order' => 1]);
        QuestionOption::create(['question_id' => $q3->id, 'label' => 'No',  'value' => 'no',  'order' => 2]);

        // 4. Text (conditional on q3=yes)
        Question::create([
            'form_id'               => $form->id,
            'type'                  => 'text',
            'label'                 => 'Project Name',
            'help_text'             => 'Enter the name of the project you are working on',
            'is_required'           => false,
            'order'                 => 4,
            'conditional_parent_id' => $q3->id,
            'conditional_value'     => 'yes',
        ]);

        // 5. Textarea
        Question::create([
            'form_id'          => $form->id,
            'type'             => 'textarea',
            'label'            => 'Key Achievements This Quarter',
            'is_required'      => true,
            'order'            => 5,
            'validation_rules' => ['max' => 1000],
        ]);

        // 6. Select
        $q6 = Question::create([
            'form_id'     => $form->id,
            'type'        => 'select',
            'label'       => 'Self-Assessment Rating',
            'is_required' => true,
            'order'       => 6,
        ]);
        foreach (['Excellent', 'Good', 'Average', 'Needs Improvement'] as $i => $label) {
            QuestionOption::create([
                'question_id' => $q6->id,
                'label'       => $label,
                'value'       => strtolower(str_replace(' ', '_', $label)),
                'order'       => $i + 1,
            ]);
        }

        // 7. Checkbox
        $q7 = Question::create([
            'form_id'     => $form->id,
            'type'        => 'checkbox',
            'label'       => 'Areas you want to improve',
            'is_required' => false,
            'order'       => 7,
        ]);
        foreach (['Technical Skills', 'Communication', 'Leadership', 'Time Management', 'Collaboration'] as $i => $label) {
            QuestionOption::create([
                'question_id' => $q7->id,
                'label'       => $label,
                'value'       => strtolower(str_replace(' ', '_', $label)),
                'order'       => $i + 1,
            ]);
        }

        // 8. Number
        Question::create([
            'form_id'          => $form->id,
            'type'             => 'number',
            'label'            => 'Hours worked on training this quarter',
            'is_required'      => false,
            'order'            => 8,
            'validation_rules' => ['min' => 0, 'max' => 500],
        ]);

        // 9. Date
        Question::create([
            'form_id'     => $form->id,
            'type'        => 'date',
            'label'       => 'Date of last performance review',
            'is_required' => false,
            'order'       => 9,
        ]);
    }
}
