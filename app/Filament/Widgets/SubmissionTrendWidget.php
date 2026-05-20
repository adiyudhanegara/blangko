<?php

namespace App\Filament\Widgets;

use App\Models\Submission;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SubmissionTrendWidget extends ChartWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected ?string $maxHeight = '300px';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return __('admin.widget_submission_trend');
    }

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));

        $counts = Submission::query()
            ->where('status', 'submitted')
            ->whereBetween('submitted_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(submitted_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        return [
            'datasets' => [
                [
                    'label' => __('admin.chart_submissions'),
                    'data' => $days->map(fn ($d) => $counts->get($d, 0))->values()->all(),
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99,102,241,0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('d M'))->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
