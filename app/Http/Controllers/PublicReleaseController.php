<?php

namespace App\Http\Controllers;

use App\Models\FormRelease;
use App\Models\Participant;
use App\Models\ReleaseSet;
use App\Models\Submission;

class PublicReleaseController extends Controller
{
    /** Entry point: show identify/register form, or redirect to forms list if already identified. */
    public function show(string $token)
    {
        $set = ReleaseSet::where('public_token', $token)->firstOrFail();

        if ($set->status === 'scheduled') {
            return view('public.not-open', ['releaseSet' => $set, 'reason' => 'not_yet']);
        }

        if (in_array($set->status, ['closed', 'cancelled'])) {
            return view('public.not-open', ['releaseSet' => $set, 'reason' => 'closed']);
        }

        if (session('blangko_participant_id') && session('blangko_release_set_id') == $set->id) {
            return redirect()->route('release.forms', $token);
        }

        return view('livewire.public.release-set-entry-page', ['releaseSet' => $set]);
    }

    /** Release-set forms list — shown after identification. */
    public function forms(string $token)
    {
        $set = ReleaseSet::where('public_token', $token)->firstOrFail();

        if (!$set->isOpen()) {
            return redirect()->route('release.show', $token);
        }

        // Materialise a pending participant (registered but not yet written to DB)
        if (session('blangko_pending_participant') && session('blangko_release_set_id') == $set->id) {
            $participant = Participant::create(session('blangko_pending_participant'));
            session()->forget('blangko_pending_participant');
            session(['blangko_participant_id' => $participant->id]);
        }

        if (!session('blangko_participant_id')) {
            return redirect()->route('release.show', $token);
        }

        return view('livewire.public.release-set-forms-list-page', ['releaseSet' => $set]);
    }

    /** Open a form — finds/creates a submission and renders the form. */
    public function form(string $token, int $releaseId)
    {
        $set     = ReleaseSet::where('public_token', $token)->firstOrFail();
        $release = FormRelease::where('id', $releaseId)
            ->where('release_set_id', $set->id)
            ->firstOrFail();

        if (!$set->isOpen()) {
            return redirect()->route('release.show', $token);
        }

        $participantId = session('blangko_participant_id');
        if (!$participantId) {
            return redirect()->route('release.show', $token);
        }

        if ($release->allowsMultipleSubmissions()) {
            // Multi-submission: create a fresh draft and redirect to its edit URL
            $submission = Submission::create([
                'form_release_id' => $release->id,
                'participant_id'  => $participantId,
                'status'          => 'draft',
                'ip_address'      => request()->ip(),
                'user_agent'      => request()->userAgent(),
            ]);

            return redirect()->route('release.submission.edit', [$token, $release->id, $submission->id]);
        }

        // Single-submission: find or create the one submission for this participant
        $submission = Submission::firstOrCreate(
            ['form_release_id' => $release->id, 'participant_id' => $participantId],
            [
                'status'     => 'draft',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]
        );

        return view('livewire.public.submission-form-page', compact('release', 'submission'));
    }

    /** Edit a specific submission. */
    public function submissionEdit(string $token, int $releaseId, int $submissionId)
    {
        $set     = ReleaseSet::where('public_token', $token)->firstOrFail();
        $release = FormRelease::where('id', $releaseId)
            ->where('release_set_id', $set->id)
            ->firstOrFail();

        if (!$set->isOpen()) {
            return redirect()->route('release.show', $token);
        }

        $participantId = session('blangko_participant_id');
        if (!$participantId) {
            return redirect()->route('release.show', $token);
        }

        $submission = Submission::where('id', $submissionId)
            ->where('participant_id', $participantId)
            ->where('form_release_id', $release->id)
            ->firstOrFail();

        return view('livewire.public.submission-form-page', compact('release', 'submission'));
    }

    /** Submission history for multi-submission forms. */
    public function history(string $token, int $releaseId)
    {
        $set     = ReleaseSet::where('public_token', $token)->firstOrFail();
        $release = FormRelease::where('id', $releaseId)
            ->where('release_set_id', $set->id)
            ->firstOrFail();

        if (!$release->allowsMultipleSubmissions()) {
            return redirect()->route('release.form', [$token, $release->id]);
        }

        if (!session('blangko_participant_id')) {
            return redirect()->route('release.show', $token);
        }

        return view('livewire.public.submission-history-page', compact('release'));
    }
}
