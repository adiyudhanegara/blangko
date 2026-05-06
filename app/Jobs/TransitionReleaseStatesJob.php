<?php
namespace App\Jobs;

use App\Models\ReleaseSet;
use App\Services\ReleaseSetPublisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TransitionReleaseStatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // scheduled -> open (when start_at has passed)
        ReleaseSet::where('status', 'scheduled')
            ->where('start_at', '<=', now())
            ->each(function (ReleaseSet $set) {
                try {
                    ReleaseSetPublisher::publish($set);
                } catch (\Throwable $e) {
                    \Log::error("Failed to publish release set #{$set->id}: " . $e->getMessage());
                }
            });

        // open -> closed (when end_at has passed)
        ReleaseSet::where('status', 'open')
            ->where('end_at', '<=', now())
            ->update(['status' => 'closed']);
    }
}
