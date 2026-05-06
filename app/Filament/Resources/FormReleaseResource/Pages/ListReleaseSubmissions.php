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
use Illuminate\Database\Eloquent\Builder;

class ListReleaseSubmissions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = FormReleaseResource::class;

    protected string $view = 'filament.resources.form-release-resource.pages.list-submissions';

    public FormRelease $record;

    public function mount(int|string $release): void
    {
        $this->record = FormRelease::with('form')->findOrFail($release);
    }

    public function getTitle(): string
    {
        return 'Submissions — ' . $this->record->name;
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
                    ->label('Participant')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('participant.division.name')
                    ->label('Division')
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
                    ->label('Answers')
                    ->alignCenter(),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options(['draft' => 'Draft', 'submitted' => 'Submitted']),
            ])
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Submission $record): string => ViewSubmission::getUrl(['record' => $record->id])),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->emptyStateHeading('No submissions yet')
            ->emptyStateDescription('Participants have not submitted responses for this release.');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(route('admin.releases.export', $this->record))
                ->openUrlInNewTab(),

            Action::make('back')
                ->label('Back to Releases')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(FormReleaseResource::getUrl()),
        ];
    }
}
