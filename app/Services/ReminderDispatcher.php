<?php
namespace App\Services;

use App\Jobs\SendReminderJob;
use App\Models\Participant;
use App\Models\ReleaseSet;
use App\Models\ReminderLog;
use Carbon\Carbon;

class ReminderDispatcher
{
    public function dispatch(ReleaseSet $set): void
    {
        if (!$set->isOpen()) return;
        if (empty($set->reminder_schedule)) return;

        $daysRemaining = (int) now()->diffInDays($set->end_at, false);

        if (!in_array($daysRemaining, $set->reminder_schedule)) return;

        // Participants in the set's divisions (or all participants if no division restriction)
        $divisionIds = $set->divisions()->pluck('divisions.id');

        $participantQuery = Participant::whereNotNull('email')->where('status', 'active');
        if ($divisionIds->isNotEmpty()) {
            $participantQuery->whereIn('division_id', $divisionIds);
        }

        $calculator = app(CompletionCalculator::class);

        foreach ($participantQuery->cursor() as $participant) {
            if ($calculator->isComplete($set, $participant)) continue;

            // Guard: already sent for this (set, participant, day offset)
            $alreadySent = ReminderLog::where('release_set_id', $set->id)
                ->where('participant_id', $participant->id)
                ->where('reminder_offset_days', $daysRemaining)
                ->exists();

            if ($alreadySent) continue;

            SendReminderJob::dispatch($set, $participant);
        }
    }
}
