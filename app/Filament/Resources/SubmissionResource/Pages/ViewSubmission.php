<?php

namespace App\Filament\Resources\SubmissionResource\Pages;

use App\Filament\Resources\SubmissionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\Computed;

class ViewSubmission extends ViewRecord
{
    protected static string $resource = SubmissionResource::class;

    protected string $view = 'filament.resources.submission-resource.pages.view-submission';

    public function getTitle(): string
    {
        return __('admin.submission_title', ['name' => $this->getRecord()->participant->name]);
    }

    #[Computed]
    public function questions()
    {
        return $this->getRecord()
            ->formRelease
            ->releaseQuestions()
            ->with('options')
            ->get();
    }

    #[Computed]
    public function answerMap()
    {
        return $this->getRecord()->answers->keyBy('release_question_id');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_this_release')
                ->label(fn () => __('admin.action_export_release'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn (): string => route('admin.releases.export', $this->getRecord()->form_release_id))
                ->openUrlInNewTab(),

            Action::make('back')
                ->label(fn () => __('admin.action_all_submissions'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(SubmissionResource::getUrl()),
        ];
    }
}
