<?php

namespace App\Filament\Pages;

use App\Models\ReleaseSet;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class ReleaseDashboard extends Page
{
    protected string $view = 'filament.pages.release-dashboard';

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string                 $navigationLabel = 'Release Dashboard';
    protected static string|\UnitEnum|null   $navigationGroup = 'Forms';
    protected static ?int                    $navigationSort  = 0;
    protected static ?string                 $title           = 'Release Dashboard';

    public function getViewData(): array
    {
        return [
            'releaseSets' => $this->buildDashboardData(),
        ];
    }

    private function buildDashboardData(): Collection
    {
        $sets = ReleaseSet::whereNull('deleted_at')
            ->with([
                'divisions.participants' => fn ($q) => $q->where('status', 'active')->orderBy('name'),
                'formReleases.form',
                'formReleases.submissions' => fn ($q) => $q
                    ->where('status', 'submitted')
                    ->select('id', 'form_release_id', 'participant_id'),
            ])
            ->orderByDesc('start_at')
            ->get();

        return $sets->map(function (ReleaseSet $set) {
            // Unique active participants; attach division relation so view can display it
            $participants = $set->divisions
                ->flatMap(fn ($d) => $d->participants->each(fn ($p) => $p->setRelation('division', $d)))
                ->unique('id')
                ->values();

            $total = $participants->count();

            $forms = $set->formReleases->map(function ($release) use ($participants, $total) {
                $submittedIds   = $release->submissions->pluck('participant_id')->unique();
                $submittedCount = $submittedIds->count();
                $pending        = $participants->filter(fn ($p) => !$submittedIds->contains($p->id))->values();

                return [
                    'release'         => $release,
                    'submitted_count' => $submittedCount,
                    'total'           => $total,
                    'is_complete'     => $total > 0 && $submittedCount >= $total,
                    'percent'         => $total > 0 ? min(100, (int) round($submittedCount / $total * 100)) : 0,
                    'pending'         => $pending,
                ];
            });

            $completeForms = $forms->where('is_complete', true)->count();
            $totalForms    = $forms->count();

            return [
                'set'            => $set,
                'total'          => $total,
                'total_forms'    => $totalForms,
                'complete_forms' => $completeForms,
                'set_percent'    => $totalForms > 0 ? (int) round($completeForms / $totalForms * 100) : 0,
                'forms'          => $forms,
            ];
        });
    }
}
