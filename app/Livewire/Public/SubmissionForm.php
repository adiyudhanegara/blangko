<?php

namespace App\Livewire\Public;

use App\Models\Answer;
use App\Models\FormRelease;
use App\Models\ReleaseQuestion;
use App\Models\Submission;
use App\Services\ConditionalLogicEvaluator;
use Livewire\Component;
use Livewire\WithFileUploads;

class SubmissionForm extends Component
{
    use WithFileUploads;

    public FormRelease $release;
    public Submission  $submission;

    public array $answers       = [];
    public array $otherText     = []; // free-text for "Other" option per question
    public $fileUploads         = [];
    /** Existing stored files keyed by question id: [ ['answer_id'=>int, 'files'=>[...]] ] */
    public array $existingFiles = [];

    public bool $submitted = false;

    public function mount(Submission $submission): void
    {
        $this->submission = $submission;

        // Redirect if participant session doesn't match
        $participantId = session('blangko_participant_id');
        $releaseSetId  = session('blangko_release_set_id');

        if (!$participantId
            || $this->submission->participant_id != $participantId
            || $this->release->releaseSet?->id != $releaseSetId
        ) {
            $this->redirectRoute('release.show', $this->release->releaseSet?->public_token ?? '');
            return;
        }

        if ($this->submission->status === 'submitted') {
            $this->submitted = true;
        }

        // Pre-initialize checkbox questions as empty arrays
        foreach ($this->release->releaseQuestions as $question) {
            if ($question->type === 'checkbox') {
                $this->answers[$question->id] = [];
            }
        }

        // Load existing answers
        foreach ($this->submission->answers as $answer) {
            $this->loadAnswer($answer);
        }
    }

    protected function loadAnswer(Answer $answer): void
    {
        $qid = $answer->release_question_id;

        // File answers are not stored in $answers — track them separately for display
        if ($answer->file_path !== null || !empty($answer->file_paths)) {
            $files = [];
            if ($answer->file_path) {
                $files[] = [
                    'url'  => route('public.file.serve', $answer->id),
                    'name' => $answer->file_original_name ?? basename($answer->file_path),
                    'ext'  => strtolower(pathinfo($answer->file_path, PATHINFO_EXTENSION)),
                ];
            } elseif ($answer->file_paths) {
                foreach ($answer->file_paths as $idx => $f) {
                    $files[] = [
                        'url'  => route('public.file.serve', [$answer->id, $idx]),
                        'name' => $f['original_name'] ?? basename($f['path']),
                        'ext'  => strtolower(pathinfo($f['path'], PATHINFO_EXTENSION)),
                    ];
                }
            }
            $this->existingFiles[$qid] = $files;
            return;
        }

        if ($answer->value_json !== null) {
            $json = $answer->value_json;

            // Radio/select with Other: {"option": "...", "other_text": "..."}
            if (isset($json['option'])) {
                $this->answers[$qid] = $json['option'];
                if (isset($json['other_text'])) {
                    $this->otherText[$qid] = $json['other_text'];
                }
                return;
            }

            // Checkbox with optional Other: {"values": [...], "other_text": "..."}
            if (isset($json['values'])) {
                $this->answers[$qid] = $json['values'];
                if (isset($json['other_text'])) {
                    $this->otherText[$qid] = $json['other_text'];
                }
                return;
            }

            // Plain array (legacy checkbox)
            $this->answers[$qid] = $json;
        } else {
            $this->answers[$qid] = $answer->value;
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
        session()->flash('message', __('public.draft_saved'));
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
        session()->flash('message', __('public.submitted_success'));
    }

    protected function validateAnswers(): void
    {
        $rules    = [];
        $messages = [];

        foreach ($this->release->releaseQuestions as $question) {
            if (!$this->isVisible($question)) {
                continue;
            }

            $key       = "answers.{$question->id}";
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
                if (!is_array($this->answers[$question->id] ?? null)) {
                    $this->answers[$question->id] = [];
                }
                $ruleParts[] = 'array';
            }

            if ($question->type === 'file') {
                // Only require a new upload when no existing file is already saved
                if ($question->is_required && empty($this->existingFiles[$question->id])) {
                    $fileKey            = "fileUploads.{$question->id}";
                    $rules[$fileKey]    = 'required';
                    $messages["{$fileKey}.required"] = __('public.field_required', ['field' => $question->label]);
                }
                continue;
            }

            $vr = $question->validation_rules ?? [];
            if (!empty($vr['min'])) {
                $ruleParts[] = 'min:' . $vr['min'];
            }
            if (!empty($vr['max'])) {
                $ruleParts[] = 'max:' . $vr['max'];
            }

            if (!empty($ruleParts)) {
                $rules[$key]                 = implode('|', $ruleParts);
                $messages["{$key}.required"] = __('public.field_required', ['field' => $question->label]);
            }

            // Validate "Other" free text when the Other option is selected and required
            if ($question->is_required && $this->isOtherSelected($question)) {
                $otherKey            = "otherText.{$question->id}";
                $rules[$otherKey]    = 'required|string|max:500';
                $messages["{$otherKey}.required"] = __('public.other_required', ['field' => $question->label]);
            }
        }

        $this->validate($rules, $messages);
    }

    protected function isOtherSelected(ReleaseQuestion $question): bool
    {
        $value = $this->answers[$question->id] ?? null;

        if ($question->type === 'checkbox') {
            return is_array($value) && in_array('other', $value, true);
        }

        return $value === 'other';
    }

    protected function saveAnswers(): void
    {
        foreach ($this->release->releaseQuestions as $question) {
            $value = $this->answers[$question->id] ?? null;

            if ($question->type === 'file') {
                $this->saveFileAnswer($question);
                continue;
            }

            if ($question->type === 'checkbox') {
                $values = is_array($value) ? $value : [];
                $json   = ['values' => $values];

                if (in_array('other', $values, true) && !empty($this->otherText[$question->id])) {
                    $json['other_text'] = $this->otherText[$question->id];
                }

                Answer::updateOrCreate(
                    ['submission_id' => $this->submission->id, 'release_question_id' => $question->id],
                    ['value' => null, 'value_json' => $json]
                );
                continue;
            }

            // Radio / select with potential Other option
            if (in_array($question->type, ['radio', 'select']) && $value === 'other') {
                $json = [
                    'option'     => 'other',
                    'other_text' => $this->otherText[$question->id] ?? '',
                ];
                Answer::updateOrCreate(
                    ['submission_id' => $this->submission->id, 'release_question_id' => $question->id],
                    ['value' => null, 'value_json' => $json]
                );
                continue;
            }

            Answer::updateOrCreate(
                ['submission_id' => $this->submission->id, 'release_question_id' => $question->id],
                ['value' => (string) ($value ?? ''), 'value_json' => null]
            );
        }
    }

    protected function saveFileAnswer(ReleaseQuestion $question): void
    {
        $upload = $this->fileUploads[$question->id] ?? null;
        if (!$upload) {
            return;
        }

        $maxFiles = (int) ($question->validation_rules['max_files'] ?? 1);

        if ($maxFiles <= 1 || !is_array($upload)) {
            $file = is_array($upload) ? $upload[0] : $upload;

            // Capture before store() moves the temp file
            $originalName = $file->getClientOriginalName();
            $path         = $file->store("submissions/{$this->submission->id}", 'local');

            Answer::updateOrCreate(
                ['submission_id' => $this->submission->id, 'release_question_id' => $question->id],
                ['file_path' => $path, 'file_original_name' => $originalName, 'file_paths' => null]
            );
        } else {
            $filePaths = [];
            foreach ((array) $upload as $file) {
                $originalName = $file->getClientOriginalName();
                $size         = $file->getSize();
                $mime         = $file->getMimeType();
                $path         = $file->store("submissions/{$this->submission->id}", 'local');

                $filePaths[] = compact('path', 'originalName', 'size', 'mime') + ['original_name' => $originalName];
            }

            Answer::updateOrCreate(
                ['submission_id' => $this->submission->id, 'release_question_id' => $question->id],
                ['file_path' => null, 'file_original_name' => null, 'file_paths' => $filePaths]
            );
        }

        // Null out so Livewire dehydration doesn't try to stat the now-moved temp file
        $this->fileUploads[$question->id] = null;
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

    public function backUrl(): string
    {
        $token = $this->release->releaseSet?->public_token ?? '';

        if ($this->release->allowsMultipleSubmissions()) {
            return route('release.history', [$token, $this->release->id]);
        }

        return route('release.forms', $token);
    }

    public function render()
    {
        $questions = $this->release->releaseQuestions()->with('options')->get();
        return view('livewire.public.submission-form', compact('questions'))
            ->layout('layouts.public');
    }
}
