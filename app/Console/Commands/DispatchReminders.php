<?php
namespace App\Console\Commands;

use App\Models\ReleaseSet;
use App\Services\ReminderDispatcher;
use Illuminate\Console\Command;

class DispatchReminders extends Command
{
    protected $signature = 'releases:reminders';
    protected $description = 'Dispatch email reminders for open release sets';

    public function handle(): int
    {
        $dispatcher = new ReminderDispatcher();

        ReleaseSet::where('status', 'open')->each(function (ReleaseSet $set) use ($dispatcher) {
            $dispatcher->dispatch($set);
        });

        $this->info('Reminders dispatched.');
        return Command::SUCCESS;
    }
}
