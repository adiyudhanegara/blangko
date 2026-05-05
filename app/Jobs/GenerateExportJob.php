<?php
namespace App\Jobs;

use App\Exports\ReleaseResultsExport;
use App\Models\FormRelease;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class GenerateExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly FormRelease $release,
        public readonly User $requestedBy,
        public readonly string $format = 'xlsx',
    ) {}

    public function handle(): void
    {
        $filename = "release-{$this->release->id}-results-" . now()->format('Ymd-His') . '.' . $this->format;
        $path = "exports/{$filename}";

        Excel::store(new ReleaseResultsExport($this->release), $path, 'local');

        // Notify admin (simplified — just log for now; extend with DB notification)
        \Log::info("Export ready for release #{$this->release->id}: {$path}");
    }
}
