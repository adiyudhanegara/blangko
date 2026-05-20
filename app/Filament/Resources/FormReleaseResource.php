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
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav_form_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav_form_releases');
    }

    public static function getModelLabel(): string
    {
        return __('admin.model_form_release');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('release_set_id')
                ->label(fn () => __('admin.field_release_set'))
                ->relationship('releaseSet', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->columnSpanFull(),

            Forms\Components\Select::make('form_id')
                ->label(fn () => __('admin.col_form'))
                ->relationship('form', 'title')
                ->searchable()
                ->preload()
                ->required()
                ->columnSpanFull(),

            Forms\Components\Toggle::make('is_required')
                ->label(fn () => __('admin.field_is_required'))
                ->default(true),

            Forms\Components\TextInput::make('order')
                ->label(fn () => __('admin.col_order'))
                ->numeric()
                ->default(1)
                ->minValue(1),

            Forms\Components\TextInput::make('min_submissions_required')
                ->label(fn () => __('admin.field_min_submissions_required'))
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
                    ->label(__('admin.col_release_set'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('form.title')
                    ->label(__('admin.col_form'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('releaseSet.status')
                    ->label(__('admin.col_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'warning',
                        'open'      => 'success',
                        'closed'    => 'danger',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('releaseSet.end_at')
                    ->label(__('admin.col_closes'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_required')
                    ->label(__('admin.col_required'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label(__('admin.col_submissions'))
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('releaseSet.status')
                    ->label(__('admin.col_status'))
                    ->relationship('releaseSet', 'status')
                    ->options(fn () => [
                        'scheduled' => __('admin.status_scheduled'),
                        'open'      => __('admin.status_open'),
                        'closed'    => __('admin.status_closed'),
                        'cancelled' => __('admin.status_cancelled'),
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
                    ->label(fn () => __('admin.action_publish_set'))
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->visible(fn (FormRelease $record): bool => $record->releaseSet?->status === 'scheduled')
                    ->requiresConfirmation()
                    ->modalHeading(fn () => __('admin.modal_publish_formrel_heading'))
                    ->modalDescription(fn () => __('admin.modal_publish_formrel_desc'))
                    ->action(function (FormRelease $record): void {
                        ReleaseSetPublisher::publish($record->releaseSet);

                        Notification::make()
                            ->title(__('admin.notification_formrel_published'))
                            ->success()
                            ->send();
                    }),

                Action::make('copy-link')
                    ->label(fn () => __('admin.action_public_link'))
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->url(
                        fn (FormRelease $record): string => route('release.show', $record->releaseSet?->public_token ?? ''),
                        shouldOpenInNewTab: true,
                    )
                    ->visible(fn (FormRelease $record): bool => $record->releaseSet?->public_token !== null)
                    ->tooltip(fn () => __('admin.tooltip_public_link')),

                Action::make('view_submissions')
                    ->label(fn () => __('admin.action_view_submissions'))
                    ->icon('heroicon-o-inbox-stack')
                    ->color('gray')
                    ->url(fn (FormRelease $record): string =>
                        Pages\ListReleaseSubmissions::getUrl(['release' => $record->id])
                    ),

                Action::make('export')
                    ->label(fn () => __('admin.action_export'))
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
