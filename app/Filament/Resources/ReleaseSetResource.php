<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReleaseSetResource\Pages;
use App\Models\ReleaseSet;
use App\Services\ReleaseSetPublisher;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReleaseSetResource extends Resource
{
    protected static ?string $model = ReleaseSet::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|\UnitEnum|null $navigationGroup = 'Forms';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Release Sets';

    protected static ?string $modelLabel = 'Release Set';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            // ── Identity ──────────────────────────────────────────────
            Section::make('Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('period_label')
                        ->label('Period Label')
                        ->placeholder('e.g. Q2 2025')
                        ->maxLength(100),
                ])
                ->columns(2),

            // ── Schedule ──────────────────────────────────────────────
            Section::make('Schedule & Access')
                ->schema([
                    Forms\Components\DateTimePicker::make('start_at')
                        ->label('Opens At')
                        ->required()
                        ->seconds(false),

                    Forms\Components\DateTimePicker::make('end_at')
                        ->label('Closes At')
                        ->required()
                        ->seconds(false)
                        ->after('start_at'),

                    Forms\Components\TagsInput::make('reminder_schedule')
                        ->label('Reminder Days Before Close')
                        ->placeholder('e.g. 7, 3, 1')
                        ->helperText('Days before the closing date to send reminder emails.')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('divisions')
                        ->multiple()
                        ->relationship('divisions', 'name')
                        ->preload()
                        ->columnSpanFull()
                        ->helperText('Leave empty to allow all divisions.'),
                ])
                ->columns(2),

            // ── Forms ─────────────────────────────────────────────────
            Section::make('Forms in This Release Set')
                ->schema([
                    Forms\Components\Repeater::make('formReleases')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('form_id')
                                ->label('Form')
                                ->relationship('form', 'title')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(2),

                            Forms\Components\Toggle::make('is_required')
                                ->label('Required')
                                ->default(true)
                                ->inline(false),

                            Forms\Components\TextInput::make('min_submissions_required')
                                ->label('Min Submissions')
                                ->numeric()
                                ->nullable()
                                ->helperText('Leave blank for single submission.'),
                        ])
                        ->columns(4)
                        ->orderColumn('order')
                        ->reorderableWithDragAndDrop()
                        ->addActionLabel('Add Form')
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                            $data['created_by'] = auth()->id();
                            return $data;
                        }),
                ])
                ->columnSpanFull(),

        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (ReleaseSet $r) => $r->period_label),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'warning',
                        'open'      => 'success',
                        'closed'    => 'danger',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('start_at')
                    ->label('Opens')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_at')
                    ->label('Closes')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('form_releases_count')
                    ->counts('formReleases')
                    ->label('Forms')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('public_token')
                    ->label('Token')
                    ->copyable()
                    ->copyMessage('Token copied!')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'open'      => 'Open',
                        'closed'    => 'Closed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),

                Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->visible(fn (ReleaseSet $record): bool => $record->status === 'scheduled')
                    ->requiresConfirmation()
                    ->modalHeading('Publish Release Set')
                    ->modalDescription('This will snapshot all forms and open the set for submissions. This cannot be undone.')
                    ->action(function (ReleaseSet $record): void {
                        try {
                            ReleaseSetPublisher::publish($record);
                            Notification::make()->title('Release set published.')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Publish failed: ' . $e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(fn (ReleaseSet $record): bool => $record->status === 'open')
                    ->requiresConfirmation()
                    ->modalHeading('Close Release Set')
                    ->modalDescription('No new submissions will be accepted after closing.')
                    ->action(function (ReleaseSet $record): void {
                        $record->update(['status' => 'closed']);
                        Notification::make()->title('Release set closed.')->success()->send();
                    }),

                Action::make('public-link')
                    ->label('Public Link')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->url(
                        fn (ReleaseSet $record): string => route('release.show', $record->public_token),
                        shouldOpenInNewTab: true,
                    ),

                Action::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(
                        fn (ReleaseSet $record): string => route('admin.release-sets.export', $record),
                        shouldOpenInNewTab: true,
                    ),

                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReleaseSets::route('/'),
            'create' => Pages\CreateReleaseSet::route('/create'),
            'edit'   => Pages\EditReleaseSet::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }
}
