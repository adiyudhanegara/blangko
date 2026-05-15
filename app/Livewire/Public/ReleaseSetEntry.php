<?php

namespace App\Livewire\Public;

use App\Models\Division;
use App\Models\Participant;
use App\Models\ReleaseSet;
use Livewire\Component;

class ReleaseSetEntry extends Component
{
    public ReleaseSet $releaseSet;

    public string $identifierType = 'nip'; // 'nip', 'phone', or 'email'
    public string $identifier     = '';

    public bool    $showRegistration = false;
    public string  $name             = '';
    public ?int    $divisionId       = null;

    public ?string $errorMessage = null;

    public function identify(): void
    {
        $this->errorMessage = null;
        $this->validate([
            'identifier' => match ($this->identifierType) {
                'email' => 'required|email|max:255',
                default => 'required|string|max:50',
            },
        ]);

        $participant = match ($this->identifierType) {
            'phone' => Participant::where('phone', $this->identifier)->first(),
            'email' => Participant::where('email', $this->identifier)->first(),
            'nip'   => Participant::where('nip', $this->identifier)->first(),
            default => null,
        };

        if ($participant) {
            $this->startSession($participant);
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

        // Defer DB write — participant is created when the forms-list page loads
        session([
            'blangko_pending_participant' => [
                'name'        => $this->name,
                'division_id' => $this->divisionId,
                'phone'       => $this->identifierType === 'phone' ? $this->identifier : null,
                'email'       => $this->identifierType === 'email' ? $this->identifier : null,
                'nip'         => $this->identifierType === 'nip'   ? $this->identifier : null,
                'status'      => 'active',
            ],
            'blangko_release_set_id' => $this->releaseSet->id,
        ]);

        $this->redirectRoute('release.forms', $this->releaseSet->public_token);
    }

    protected function startSession(Participant $participant): void
    {
        session([
            'blangko_participant_id' => $participant->id,
            'blangko_release_set_id' => $this->releaseSet->id,
        ]);

        $this->redirectRoute('release.forms', $this->releaseSet->public_token);
    }

    public function render()
    {
        $divisions = Division::orderBy('name')->get();
        return view('livewire.public.release-set-entry', compact('divisions'))
            ->layout('layouts.public');
    }
}
