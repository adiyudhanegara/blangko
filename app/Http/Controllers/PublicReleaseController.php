<?php

namespace App\Http\Controllers;

use App\Models\FormRelease;
use Illuminate\Http\Request;

class PublicReleaseController extends Controller
{
    public function show(string $token)
    {
        $release = FormRelease::where('public_token', $token)->firstOrFail();

        if ($release->status === 'scheduled') {
            return view('public.not-open', ['release' => $release, 'reason' => 'not_yet']);
        }

        if (in_array($release->status, ['closed', 'cancelled'])) {
            return view('public.not-open', ['release' => $release, 'reason' => 'closed']);
        }

        // Check if participant already has a session
        if (session('blangko_submission_id')) {
            $submission = \App\Models\Submission::find(session('blangko_submission_id'));
            if ($submission && $submission->form_release_id === $release->id) {
                return redirect()->route('release.form', $token);
            }
        }

        return view('livewire.public.release-entry-page', compact('release'));
    }

    public function form(string $token)
    {
        $release = FormRelease::where('public_token', $token)->firstOrFail();

        if (!$release->isOpen()) {
            return redirect()->route('release.show', $token);
        }

        return view('livewire.public.submission-form-page', compact('release'));
    }
}
