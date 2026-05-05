<?php
namespace App\Jobs;

use App\Models\FormRelease;
use App\Services\ReleasePublisher;
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
        // scheduled -> open
        FormRelease::where('status', 'scheduled')
            ->where('start_at', '<=', now())
            ->each(function (FormRelease $release) {
                try {
                    ReleasePublisher::publish($release);
                } catch (\Throwable $e) {
                    \Log::error("Failed to publish release #{$release->id}: " . $e->getMessage());
                }
            });

        // open -> closed
        FormRelease::where('status', 'open')
            ->where('end_at', '<=', now())
            ->update(['status' => 'closed']);
    }
}
