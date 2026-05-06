<?php

namespace App\Livewire\Public;

use App\Models\Participant;
use App\Models\ReleaseSet;
use App\Models\Submission;
use App\Services\CompletionCalculator;
use Livewire\Component;

class ReleaseSetFormsList extends Component
{
    public ReleaseSet $releaseSet;

    public function mount(): void
    {
        if (!session('blangko_participant_id') || session('blangko_release_set_id') != $this->releaseSet->id) {
            $this->redirectRoute('release.show', $this->releaseSet->public_token);
        }
    }

    public function render()
    {
        $participantId = session('blangko_participant_id');
        $participant   = Participant::with('division')->findOrFail($participantId);

        // Only show form releases for the participant's division
        $this->releaseSet->load(['formReleases.form', 'divisions']);
        $divisionId = $participant->division_id;

        $divisionIds = $this->releaseSet->divisions->pluck('id');
        $releases    = $this->releaseSet->formReleases->filter(
            fn () => $divisionIds->isEmpty() || $divisionIds->contains($divisionId)
        );

        // Build status info per release
        $releaseStatuses = $releases->mapWithKeys(function ($release) use ($participantId) {
            $submissions = Submission::where('form_release_id', $release->id)
                ->where('participant_id', $participantId)
                ->orderBy('created_at', 'desc')
                ->get();

            $submittedCount = $submissions->where('status', 'submitted')->count();
            $draftCount     = $submissions->where('status', 'draft')->count();

            if ($release->allowsMultipleSubmissions()) {
                $status = $submittedCount > 0
                    ? ['type' => 'multi', 'submitted' => $submittedCount, 'draft' => $draftCount]
                    : ['type' => 'not_started'];
            } else {
                $sub = $submissions->first();
                if (!$sub) {
                    $status = ['type' => 'not_started'];
                } elseif ($sub->status === 'submitted') {
                    $status = ['type' => 'submitted'];
                } else {
                    $status = ['type' => 'draft'];
                }
            }

            return [$release->id => $status];
        });

        // Per-participant progress: how many required forms has this participant satisfied?
        $calculator      = app(CompletionCalculator::class);
        $requiredReleases = $releases->where('is_required', true);
        $completedCount   = $requiredReleases->filter(
            fn ($r) => $calculator->isSatisfied($r, $participant)
        )->count();
        $totalRequired    = $requiredReleases->count();

        $completionStats = [
            'total'      => $totalRequired,
            'complete'   => $completedCount,
            'percentage' => $totalRequired > 0 ? round($completedCount / $totalRequired * 100) : 0,
        ];

        return view('livewire.public.release-set-forms-list', compact(
            'participant', 'releases', 'releaseStatuses', 'completionStats'
        ))->layout('layouts.public');
    }
}
