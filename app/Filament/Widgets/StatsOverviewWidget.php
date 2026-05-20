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
            Stat::make(__('admin.stat_total_participants'), Participant::count())
                ->description(__('admin.stat_registered_participants'))
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make(__('admin.stat_total_forms'), Form::count())
                ->description(__('admin.stat_all_forms'))
                ->icon('heroicon-o-document-text')
                ->color('info'),
            Stat::make(__('admin.stat_active_releases'), ReleaseSet::where('status', 'open')->count())
                ->description(__('admin.stat_currently_open'))
                ->icon('heroicon-o-rocket-launch')
                ->color('success'),
            Stat::make(__('admin.stat_submissions_this_month'), Submission::where('status', 'submitted')
                ->whereMonth('submitted_at', now()->month)
                ->whereYear('submitted_at', now()->year)
                ->count())
                ->description(__('admin.stat_submitted_this_month'))
                ->icon('heroicon-o-check-circle')
                ->color('warning'),
        ];
    }
}
