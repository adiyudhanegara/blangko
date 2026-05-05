<?php
namespace App\Jobs;

use App\Models\FormRelease;
use App\Models\Participant;
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
        public readonly FormRelease $release,
        public readonly Participant $participant,
    ) {}

    public function handle(): void
    {
        $log = ReminderLog::create([
            'form_release_id' => $this->release->id,
            'participant_id'  => $this->participant->id,
            'channel'         => 'email',
            'sent_at'         => now(),
            'status'          => 'sent',
        ]);

        try {
            $this->participant->notify(new ReleaseReminderNotification($this->release));
        } catch (Throwable $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        }
    }
}
