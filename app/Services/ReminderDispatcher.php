<?php
namespace App\Services;

use App\Jobs\SendReminderJob;
use App\Models\FormRelease;
use App\Models\Participant;
use App\Models\ReminderLog;
use Carbon\Carbon;

class ReminderDispatcher
{
    public function dispatch(FormRelease $release): void
    {
        if ($release->status !== 'open') return;
        if (empty($release->reminder_schedule)) return;

        $daysRemaining = (int) now()->diffInDays($release->end_at, false);

        if (!in_array($daysRemaining, $release->reminder_schedule)) return;

        // Get all participants in the release's divisions
        $participantIds = $release->divisions()
            ->with('participants')
            ->get()
            ->flatMap(fn ($d) => $d->participants->pluck('id'))
            ->unique();

        // Get participants who have already submitted
        $submittedIds = $release->submissions()
            ->where('status', 'submitted')
            ->pluck('participant_id');

        // Non-submitters
        $pending = Participant::whereIn('id', $participantIds)
            ->whereNotIn('id', $submittedIds)
            ->whereNotNull('email')
            ->where('status', 'active')
            ->get();

        foreach ($pending as $participant) {
            // Guard: already sent this (release, participant, day offset)
            $alreadySent = ReminderLog::where('form_release_id', $release->id)
                ->where('participant_id', $participant->id)
                ->whereDate('sent_at', Carbon::today())
                ->exists();

            if ($alreadySent) continue;

            SendReminderJob::dispatch($release, $participant);
        }
    }
}
