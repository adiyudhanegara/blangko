<?php
namespace App\Console\Commands;

use App\Jobs\TransitionReleaseStatesJob;
use Illuminate\Console\Command;

class TransitionReleaseStates extends Command
{
    protected $signature = 'releases:transition';
    protected $description = 'Transition form release statuses (scheduled->open, open->closed)';

    public function handle(): int
    {
        TransitionReleaseStatesJob::dispatchSync();
        $this->info('Release states transitioned.');
        return Command::SUCCESS;
    }
}
