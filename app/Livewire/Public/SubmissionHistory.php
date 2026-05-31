<?php

namespace App\Livewire\Public;

use App\Models\FormRelease;
use App\Models\ReleaseQuestion;
use App\Models\Submission;
use Livewire\Component;
use Livewire\Attributes\Computed;

class SubmissionHistory extends Component
{
    public FormRelease $release;

    public ?int $duplicateFromId = null;
    public bool $showDraftWarning = false;

    public function mount(): void
    {
        $participantId = session('blangko_participant_id');
        $releaseSetId  = session('blangko_release_set_id');

        if (!$participantId || $this->release->releaseSet?->id != $releaseSetId) {
            $this->redirectRoute('release.show', $this->release->releaseSet?->public_token ?? '');
            return;
        }

        if (!$this->release->allowsMultipleSubmissions()) {
            $this->redirectRoute('release.form', [
                $this->release->releaseSet->public_token,
                $this->release->id,
            ]);
        }
    }

    #[Computed]
    public function submissions()
    {
        return Submission::where('form_release_id', $this->release->id)
            ->where('participant_id', session('blangko_participant_id'))
            ->with(['answers.releaseQuestion.options'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function previewQuestions()
    {
        $form = $this->release->form;
        if (!empty($form->preview_question_ids)) {
            $ids = $form->preview_question_ids;
            return ReleaseQuestion::where('form_release_id', $this->release->id)
                ->whereIn('original_question_id', $ids)
                ->get()
                ->sortBy(fn($q) => array_search($q->original_question_id, $ids))
                ->values();
        }

        return ReleaseQuestion::where('form_release_id', $this->release->id)
            ->whereNotIn('type', ['file', 'textarea'])
            ->orderBy('order')
            ->limit(3)
            ->get();
    }

    public function deleteDraft(int $submissionId): void
    {
        $submission = Submission::where('id', $submissionId)
            ->where('participant_id', session('blangko_participant_id'))
            ->where('form_release_id', $this->release->id)
            ->where('status', 'draft')
            ->firstOrFail();

        $submission->delete();

        unset($this->submissions); // bust the computed cache
    }

    #[Computed]
    public function latestDraft(): ?Submission
    {
        return Submission::where('form_release_id', $this->release->id)
            ->where('participant_id', session('blangko_participant_id'))
            ->where('status', 'draft')
            ->latest()
            ->first();
    }

    public function addNew(): void
    {
        if ($this->latestDraft !== null) {
            $this->showDraftWarning = true;
            return;
        }

        $this->createNewDraft();
    }

    public function dismissWarning(): void
    {
        $this->showDraftWarning = false;
    }

    public function forceNewDraft(): void
    {
        $this->showDraftWarning = false;
        $this->createNewDraft();
    }

    private function createNewDraft(): void
    {
        $submission = Submission::create([
            'form_release_id' => $this->release->id,
            'participant_id'  => session('blangko_participant_id'),
            'status'          => 'draft',
            'ip_address'      => request()->ip(),
            'user_agent'      => request()->userAgent(),
        ]);

        $token = $this->release->releaseSet->public_token;
        $this->redirectRoute('release.submission.edit', [$token, $this->release->id, $submission->id]);
    }

    public function duplicateFrom(int $submissionId): void
    {
        $source = Submission::where('id', $submissionId)
            ->where('participant_id', session('blangko_participant_id'))
            ->where('form_release_id', $this->release->id)
            ->firstOrFail();

        // Create new draft
        $newSubmission = Submission::create([
            'form_release_id' => $this->release->id,
            'participant_id'  => session('blangko_participant_id'),
            'status'          => 'draft',
            'ip_address'      => request()->ip(),
            'user_agent'      => request()->userAgent(),
        ]);

        // Copy answers where allow_duplicate_in_new_submission = true
        foreach ($source->answers as $answer) {
            $question = $answer->releaseQuestion;
            if (!$question || !$question->allow_duplicate_in_new_submission) {
                continue;
            }

            $newSubmission->answers()->create([
                'release_question_id' => $answer->release_question_id,
                'value'               => $answer->value,
                'value_json'          => $answer->value_json,
                'file_path'           => $answer->file_path,
                'file_original_name'  => $answer->file_original_name,
                'file_paths'          => $answer->file_paths,
            ]);
        }

        $token = $this->release->releaseSet->public_token;
        $this->redirectRoute('release.submission.edit', [$token, $this->release->id, $newSubmission->id]);
    }

    public function render()
    {
        return view('livewire.public.submission-history')->layout('layouts.public');
    }
}
