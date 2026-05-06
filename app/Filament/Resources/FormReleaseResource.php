<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormReleaseResource\Pages;
use App\Models\FormRelease;
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
            Forms\Components\Select::make('release_set_id')
                ->label('Release Set')
                ->relationship('releaseSet', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->columnSpanFull(),

            Forms\Components\Select::make('form_id')
                ->label('Form')
                ->relationship('form', 'title')
                ->searchable()
                ->preload()
                ->required()
                ->columnSpanFull(),

            Forms\Components\Toggle::make('is_required')
                ->label('Required')
                ->default(true),

            Forms\Components\TextInput::make('order')
                ->label('Order')
                ->numeric()
                ->default(1)
                ->minValue(1),

            Forms\Components\TextInput::make('min_submissions_required')
                ->label('Min Submissions Required')
                ->numeric()
                ->nullable()
                ->helperText('Leave blank to default to 1 (single submission).'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('releaseSet.name')
                    ->label('Release Set')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('form.title')
                    ->label('Form')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('releaseSet.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'warning',
                        'open'      => 'success',
                        'closed'    => 'danger',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('releaseSet.end_at')
                    ->label('Closes')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),

                Tables\Columns\TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Submissions')
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('releaseSet.status')
                    ->label('Status')
                    ->relationship('releaseSet', 'status')
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
                    ->label('Publish Set')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->visible(fn (FormRelease $record): bool => $record->releaseSet?->status === 'scheduled')
                    ->requiresConfirmation()
                    ->modalHeading('Publish Release Set')
                    ->modalDescription('This will publish the entire release set, making all forms open for submissions.')
                    ->action(function (FormRelease $record): void {
                        ReleaseSetPublisher::publish($record->releaseSet);

                        Notification::make()
                            ->title('Release set published successfully.')
                            ->success()
                            ->send();
                    }),

                Action::make('copy-link')
                    ->label('Public Link')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->url(
                        fn (FormRelease $record): string => route('release.show', $record->releaseSet?->public_token ?? ''),
                        shouldOpenInNewTab: true,
                    )
                    ->visible(fn (FormRelease $record): bool => $record->releaseSet?->public_token !== null)
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
        return parent::getEloquentQuery()->withTrashed()->with('releaseSet');
    }
}
