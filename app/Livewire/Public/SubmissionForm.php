<?php

namespace App\Livewire\Public;

use App\Models\Answer;
use App\Models\FormRelease;
use App\Models\ReleaseQuestion;
use App\Models\Submission;
use App\Services\ConditionalLogicEvaluator;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class SubmissionForm extends Component
{
    use WithFileUploads;

    public FormRelease $release;
    public Submission $submission;

    public array $answers = [];
    public array $fileUploads = [];

    public bool $submitted = false;

    public function mount(): void
    {
        $participantId = session('blangko_participant_id');
        $submissionId = session('blangko_submission_id');

        if (!$participantId || !$submissionId) {
            $this->redirectRoute('release.show', $this->release->public_token);
            return;
        }

        $this->submission = Submission::where('id', $submissionId)
            ->where('participant_id', $participantId)
            ->where('form_release_id', $this->release->id)
            ->firstOrFail();

        if ($this->submission->status === 'submitted') {
            $this->submitted = true;
        }

        // Pre-initialize checkbox questions as empty arrays so Livewire knows the type
        foreach ($this->release->releaseQuestions as $question) {
            if ($question->type === 'checkbox') {
                $this->answers[$question->id] = [];
            }
        }

        // Load existing answers (overwrites the defaults above if a saved answer exists)
        foreach ($this->submission->answers as $answer) {
            if ($answer->value_json !== null) {
                $this->answers[$answer->release_question_id] = $answer->value_json;
            } else {
                $this->answers[$answer->release_question_id] = $answer->value;
            }
        }
    }

    public function isVisible(ReleaseQuestion $question): bool
    {
        return ConditionalLogicEvaluator::isVisible($question, $this->answers);
    }

    public function saveDraft(): void
    {
        $this->saveAnswers();
        $this->submission->update(['last_edited_at' => now()]);
        session()->flash('message', 'Draft saved.');
    }

    public function submit(): void
    {
        $this->validateAnswers();
        $this->saveAnswers();

        $this->submission->update([
            'status'         => 'submitted',
            'submitted_at'   => now(),
            'last_edited_at' => now(),
        ]);

        $this->submitted = true;
        session()->flash('message', 'Form submitted successfully!');
    }

    protected function validateAnswers(): void
    {
        $rules = [];
        $messages = [];

        foreach ($this->release->releaseQuestions as $question) {
            if (!$this->isVisible($question)) {
                continue;
            }

            $key = "answers.{$question->id}";
            $ruleParts = [];

            if ($question->is_required) {
                $ruleParts[] = 'required';
            } else {
                $ruleParts[] = 'nullable';
            }

            if ($question->type === 'email') {
                $ruleParts[] = 'email';
            }
            if ($question->type === 'number') {
                $ruleParts[] = 'numeric';
            }
            if ($question->type === 'checkbox') {
                // Ensure the value is an array before validation so a null/missing answer doesn't fail
                if (!is_array($this->answers[$question->id] ?? null)) {
                    $this->answers[$question->id] = [];
                }
                $ruleParts[] = 'array';
            }

            $vr = $question->validation_rules ?? [];
            if (!empty($vr['min'])) {
                $ruleParts[] = 'min:' . $vr['min'];
            }
            if (!empty($vr['max'])) {
                $ruleParts[] = 'max:' . $vr['max'];
            }

            if (!empty($ruleParts)) {
                $rules[$key] = implode('|', $ruleParts);
                $messages["{$key}.required"] = "{$question->label} is required.";
            }
        }

        $this->validate($rules, $messages);
    }

    protected function saveAnswers(): void
    {
        foreach ($this->release->releaseQuestions as $question) {
            $value = $this->answers[$question->id] ?? null;

            if ($question->type === 'file') {
                $upload = $this->fileUploads[$question->id] ?? null;
                if ($upload) {
                    $path = $upload->store("submissions/{$this->submission->id}", 'local');

                    Answer::updateOrCreate(
                        ['submission_id' => $this->submission->id, 'release_question_id' => $question->id],
                        ['file_path' => $path, 'file_original_name' => $upload->getClientOriginalName()],
                    );
                }
                continue;
            }

            if ($question->type === 'checkbox') {
                Answer::updateOrCreate(
                    ['submission_id' => $this->submission->id, 'release_question_id' => $question->id],
                    ['value' => null, 'value_json' => is_array($value) ? $value : []],
                );
            } else {
                Answer::updateOrCreate(
                    ['submission_id' => $this->submission->id, 'release_question_id' => $question->id],
                    ['value' => (string) ($value ?? ''), 'value_json' => null],
                );
            }
        }
    }

    public function canEdit(): bool
    {
        return $this->release->isOpen() && $this->release->form->allow_edit_after_submit;
    }

    public function editResponse(): void
    {
        if (!$this->canEdit()) {
            return;
        }
        $this->submitted = false;
    }

    public function render()
    {
        $questions = $this->release->releaseQuestions()->with('options')->get();
        return view('livewire.public.submission-form', compact('questions'))
            ->layout('layouts.public');
    }
}
