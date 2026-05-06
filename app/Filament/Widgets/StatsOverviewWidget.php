<?php

namespace App\Filament\Widgets;

use App\Models\Form;
use App\Models\Participant;
use App\Models\ReleaseSet;
use App\Models\Submission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Participants', Participant::count())
                ->description('Registered participants')
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Total Forms', Form::count())
                ->description('All forms')
                ->icon('heroicon-o-document-text')
                ->color('info'),
            Stat::make('Active Releases', ReleaseSet::where('status', 'open')->count())
                ->description('Currently open')
                ->icon('heroicon-o-rocket-launch')
                ->color('success'),
            Stat::make('Submissions This Month', Submission::where('status', 'submitted')
                ->whereMonth('submitted_at', now()->month)
                ->whereYear('submitted_at', now()->year)
                ->count())
                ->description('Submitted this month')
                ->icon('heroicon-o-check-circle')
                ->color('warning'),
        ];
    }
}
