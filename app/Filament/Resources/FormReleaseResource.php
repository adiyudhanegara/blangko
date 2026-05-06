<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormReleaseResource\Pages;
use App\Models\FormRelease;
use App\Services\ReleasePublisher;
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
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FormReleaseResource extends Resource
{
    protected static ?string $model = FormRelease::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rocket-launch';

    protected static string|\UnitEnum|null $navigationGroup = 'Forms';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('form_id')
                ->label('Form')
                ->relationship('form', 'title')
                ->searchable()
                ->preload()
                ->required()
                ->columnSpanFull(),

            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Forms\Components\DateTimePicker::make('start_at')
                ->label('Start Date & Time')
                ->required()
                ->seconds(false),

            Forms\Components\DateTimePicker::make('end_at')
                ->label('End Date & Time')
                ->required()
                ->seconds(false)
                ->after('start_at'),

            Forms\Components\Select::make('divisions')
                ->multiple()
                ->relationship('divisions', 'name')
                ->preload()
                ->required()
                ->columnSpanFull(),

            Forms\Components\TagsInput::make('reminder_schedule')
                ->label('Reminder Schedule')
                ->placeholder('3, 7, 1 days before close')
                ->helperText('Enter the number of days before closing date to send reminders.')
                ->columnSpanFull(),

            Forms\Components\Select::make('status')
                ->options([
                    'scheduled' => 'Scheduled',
                    'open'      => 'Open',
                    'closed'    => 'Closed',
                    'cancelled' => 'Cancelled',
                ])
                ->default('scheduled')
                ->required()
                ->disabled(fn (string $operation): bool => $operation === 'edit'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('form.title')
                    ->label('Form')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'warning',
                        'open'      => 'success',
                        'closed'    => 'danger',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('start_at')
                    ->label('Starts')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_at')
                    ->label('Ends')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Submissions')
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

                Tables\Filters\SelectFilter::make('form')
                    ->relationship('form', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),

                Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->visible(fn (FormRelease $record): bool => $record->status === 'scheduled')
                    ->requiresConfirmation()
                    ->modalHeading('Publish Release')
                    ->modalDescription('Are you sure you want to publish this release? This will make it open for submissions.')
                    ->action(function (FormRelease $record): void {
                        ReleasePublisher::publish($record);

                        Notification::make()
                            ->title('Release published successfully.')
                            ->success()
                            ->send();
                    }),

                Action::make('copy-link')
                    ->label('Copy Link')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->url(
                        fn (FormRelease $record): string => route('release.show', $record->public_token),
                        shouldOpenInNewTab: true,
                    )
                    ->tooltip('Open the public submission link in a new tab'),

                Action::make('view_submissions')
                    ->label('Submissions')
                    ->icon('heroicon-o-inbox-stack')
                    ->color('gray')
                    ->url(fn (FormRelease $record): string =>
                        Pages\ListReleaseSubmissions::getUrl(['release' => $record->id])
                    ),

                Action::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (FormRelease $record): string => route('admin.releases.export', $record))
                    ->openUrlInNewTab(),

                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'       => Pages\ListFormReleases::route('/'),
            'create'      => Pages\CreateFormRelease::route('/create'),
            'edit'        => Pages\EditFormRelease::route('/{record}/edit'),
            'submissions' => Pages\ListReleaseSubmissions::route('/{release}/submissions'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }
}
