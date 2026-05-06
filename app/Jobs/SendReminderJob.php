<?php
namespace App\Jobs;

use App\Models\Participant;
use App\Models\ReleaseSet;
use App\Models\ReminderLog;
use App\Notifications\ReleaseReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly ReleaseSet $releaseSet,
        public readonly Participant $participant,
    ) {}

    public function handle(): void
    {
        $daysRemaining = (int) now()->diffInDays($this->releaseSet->end_at, false);

        $log = ReminderLog::create([
            'release_set_id'       => $this->releaseSet->id,
            'participant_id'       => $this->participant->id,
            'reminder_offset_days' => $daysRemaining,
            'channel'              => 'email',
            'sent_at'              => now(),
            'status'               => 'sent',
        ]);

        try {
            $this->participant->notify(new ReleaseReminderNotification($this->releaseSet));
        } catch (Throwable $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        }
    }
}
