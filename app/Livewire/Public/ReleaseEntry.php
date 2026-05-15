<?php

namespace App\Livewire\Public;

use App\Models\Division;
use App\Models\FormRelease;
use App\Models\Participant;
use App\Models\Submission;
use Illuminate\Support\Str;
use Livewire\Component;

class ReleaseEntry extends Component
{
    public FormRelease $release;

    // Step 1: identification
    public string $identifierType = 'phone'; // 'phone', 'email', or 'nip'
    public string $identifier = '';

    // Step 2: registration (new participant)
    public bool $showRegistration = false;
    public string $name = '';
    public ?int $divisionId = null;

    public ?string $errorMessage = null;

    protected function rules(): array
    {
        if ($this->showRegistration) {
            return [
                'name'       => 'required|string|max:255',
                'divisionId' => 'nullable|exists:divisions,id',
            ];
        }

        return ['identifier' => match ($this->identifierType) {
            'email' => 'required|email|max:255',
            default => 'required|string|max:50',
        }];
    }

    public function identify(): void
    {
        $this->errorMessage = null;
        $this->validate(['identifier' => match ($this->identifierType) {
            'email' => 'required|email|max:255',
            default => 'required|string|max:50',
        }]);

        $participant = match ($this->identifierType) {
            'phone' => Participant::where('phone', $this->identifier)->first(),
            'email' => Participant::where('email', $this->identifier)->first(),
            'nip'   => Participant::where('nip', $this->identifier)->first(),
            default => null,
        };

        if ($participant) {
            $this->startOrResumeSubmission($participant);
        } else {
            $this->showRegistration = true;
        }
    }

    public function register(): void
    {
        $this->validate([
            'name'       => 'required|string|max:255',
            'divisionId' => 'nullable|exists:divisions,id',
        ]);

        session([
            'blangko_pending_participant' => [
                'name'        => $this->name,
                'division_id' => $this->divisionId,
                'phone'       => $this->identifierType === 'phone' ? $this->identifier : null,
                'email'       => $this->identifierType === 'email' ? $this->identifier : null,
                'nip'         => $this->identifierType === 'nip'   ? $this->identifier : null,
                'status'      => 'active',
            ],
            'blangko_release_id' => $this->release->id,
        ]);

        $this->redirectRoute('release.form', $this->release->public_token);
    }

    protected function startOrResumeSubmission(Participant $participant): void
    {
        $submission = Submission::firstOrCreate(
            ['form_release_id' => $this->release->id, 'participant_id' => $participant->id],
            ['status' => 'draft', 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()],
        );

        session([
            'blangko_participant_id' => $participant->id,
            'blangko_submission_id'  => $submission->id,
        ]);

        $this->redirectRoute('release.form', $this->release->public_token);
    }

    public function render()
    {
        $divisions = Division::orderBy('name')->get();
        return view('livewire.public.release-entry', compact('divisions'))
            ->layout('layouts.public');
    }
}
