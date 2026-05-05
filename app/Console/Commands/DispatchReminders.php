<?php
namespace App\Console\Commands;

use App\Models\FormRelease;
use App\Services\ReminderDispatcher;
use Illuminate\Console\Command;

class DispatchReminders extends Command
{
    protected $signature = 'releases:reminders';
    protected $description = 'Dispatch email reminders for open releases';

    public function handle(): int
    {
        $dispatcher = new ReminderDispatcher();

        FormRelease::where('status', 'open')->each(function (FormRelease $release) use ($dispatcher) {
            $dispatcher->dispatch($release);
        });

        $this->info('Reminders dispatched.');
        return Command::SUCCESS;
    }
}
