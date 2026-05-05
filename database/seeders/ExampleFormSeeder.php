<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Form;
use App\Models\FormRelease;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExampleFormSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@blangko.test')->firstOrFail();
        $divisions = Division::all();

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

        // Create an example open release
        $release = FormRelease::firstOrCreate(
            ['form_id' => $form->id, 'name' => 'Q2 2025 Evaluation - All Teams'],
            [
                'public_token'      => Str::random(32),
                'start_at'          => now()->subDay(),
                'end_at'            => now()->addDays(14),
                'status'            => 'scheduled',
                'reminder_schedule' => [3, 1],
                'created_by'        => $admin->id,
            ]
        );

        $release->divisions()->sync($divisions->pluck('id'));
    }

    private function createQuestions(Form $form): void
    {
        // 1. Text
        $q1 = Question::create([
            'form_id'     => $form->id,
            'type'        => 'text',
            'label'       => 'Full Name',
            'is_required' => true,
            'order'       => 1,
            'validation_rules' => ['max' => 255],
        ]);

        // 2. Email
        $q2 = Question::create([
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
        $q4 = Question::create([
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
        $q5 = Question::create([
            'form_id'     => $form->id,
            'type'        => 'textarea',
            'label'       => 'Key Achievements This Quarter',
            'is_required' => true,
            'order'       => 5,
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
            QuestionOption::create(['question_id' => $q6->id, 'label' => $label, 'value' => strtolower(str_replace(' ', '_', $label)), 'order' => $i + 1]);
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
            QuestionOption::create(['question_id' => $q7->id, 'label' => $label, 'value' => strtolower(str_replace(' ', '_', $label)), 'order' => $i + 1]);
        }

        // 8. Number
        $q8 = Question::create([
            'form_id'     => $form->id,
            'type'        => 'number',
            'label'       => 'Hours worked on training this quarter',
            'is_required' => false,
            'order'       => 8,
            'validation_rules' => ['min' => 0, 'max' => 500],
        ]);

        // 9. Date (not file — using date as 9th for simplicity, file needs storage setup)
        $q9 = Question::create([
            'form_id'     => $form->id,
            'type'        => 'date',
            'label'       => 'Date of last performance review',
            'is_required' => false,
            'order'       => 9,
        ]);
    }
}
