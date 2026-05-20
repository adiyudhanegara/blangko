<?php

namespace App\Filament\Resources\FormReleaseResource\Pages;

use App\Filament\Resources\FormReleaseResource;
use App\Filament\Resources\SubmissionResource\Pages\ViewSubmission;
use App\Models\FormRelease;
use App\Models\Submission;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ListReleaseSubmissions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = FormReleaseResource::class;

    protected string $view = 'filament.resources.form-release-resource.pages.list-submissions';

    public FormRelease $record;

    public function mount(int|string $release): void
    {
        $this->record = FormRelease::with(['form', 'releaseSet'])->findOrFail($release);
    }

    public function getTitle(): string
    {
        $setName  = $this->record->releaseSet?->name ?? __('admin.release_fallback', ['id' => $this->record->id]);
        $formName = $this->record->form?->title ?? __('admin.col_form');
        return __('admin.submissions_page_title', ['set' => $setName, 'form' => $formName]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('form_release_id', $this->record->id)
                    ->with(['participant.division'])
            )
            ->columns([
                TextColumn::make('participant.name')
                    ->label(__('admin.col_participant'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('participant.division.name')
                    ->label(__('admin.col_division'))
                    ->searchable()
                    ->default('—'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'success',
                        'draft'     => 'warning',
                        default     => 'gray',
                    }),

                TextColumn::make('answers_count')
                    ->counts('answers')
                    ->label(__('admin.col_answers'))
                    ->alignCenter(),

                TextColumn::make('submitted_at')
                    ->label(__('admin.col_submitted'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options(fn () => [
                        'draft'     => __('admin.status_draft'),
                        'submitted' => __('admin.col_submitted'),
                    ]),
            ])
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Submission $record): string => ViewSubmission::getUrl(['record' => $record->id])),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->emptyStateHeading(fn () => __('admin.empty_no_submissions'))
            ->emptyStateDescription(fn () => __('admin.empty_no_submissions_desc'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label(fn () => __('admin.action_export_excel'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(route('admin.releases.export', $this->record))
                ->openUrlInNewTab(),

            Action::make('back')
                ->label(fn () => __('admin.action_back_to_releases'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(FormReleaseResource::getUrl()),
        ];
    }
}
