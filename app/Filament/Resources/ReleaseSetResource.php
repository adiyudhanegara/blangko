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
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav_form_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav_release_sets');
    }

    public static function getModelLabel(): string
    {
        return __('admin.model_release_set');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make(fn () => __('admin.section_details'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('period_label')
                        ->label(fn () => __('admin.field_period_label'))
                        ->placeholder('e.g. Q2 2025')
                        ->maxLength(100),
                ])
                ->columns(2),

            Section::make(fn () => __('admin.section_schedule_access'))
                ->schema([
                    Forms\Components\DateTimePicker::make('start_at')
                        ->label(fn () => __('admin.field_opens_at'))
                        ->required()
                        ->seconds(false),

                    Forms\Components\DateTimePicker::make('end_at')
                        ->label(fn () => __('admin.field_closes_at'))
                        ->required()
                        ->seconds(false)
                        ->after('start_at'),

                    Forms\Components\TagsInput::make('reminder_schedule')
                        ->label(fn () => __('admin.field_reminder_days'))
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

            Section::make(fn () => __('admin.section_forms_in_set'))
                ->schema([
                    Forms\Components\Repeater::make('formReleases')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('form_id')
                                ->label(fn () => __('admin.col_form'))
                                ->relationship('form', 'title')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(2),

                            Forms\Components\Toggle::make('is_required')
                                ->label(fn () => __('admin.col_required'))
                                ->default(true)
                                ->inline(false),

                            Forms\Components\TextInput::make('min_submissions_required')
                                ->label(fn () => __('admin.field_min_submissions'))
                                ->numeric()
                                ->nullable()
                                ->helperText('Leave blank for single submission.'),
                        ])
                        ->columns(4)
                        ->orderColumn('order')
                        ->reorderableWithDragAndDrop()
                        ->addActionLabel(fn () => __('admin.add_form'))
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
                    ->label(__('admin.col_name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (ReleaseSet $r) => $r->period_label),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.col_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'warning',
                        'open'      => 'success',
                        'closed'    => 'danger',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('start_at')
                    ->label(__('admin.col_opens'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_at')
                    ->label(__('admin.col_closes'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('form_releases_count')
                    ->counts('formReleases')
                    ->label(__('admin.col_forms'))
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('public_token')
                    ->label(__('admin.col_token'))
                    ->copyable()
                    ->copyMessage(fn () => __('admin.token_copied'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(fn () => [
                        'scheduled' => __('admin.status_scheduled'),
                        'open'      => __('admin.status_open'),
                        'closed'    => __('admin.status_closed'),
                        'cancelled' => __('admin.status_cancelled'),
                    ]),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),

                Action::make('publish')
                    ->label(fn () => __('admin.action_publish'))
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->visible(fn (ReleaseSet $record): bool => $record->status === 'scheduled')
                    ->requiresConfirmation()
                    ->modalHeading(fn () => __('admin.modal_publish_set_heading'))
                    ->modalDescription(fn () => __('admin.modal_publish_set_desc'))
                    ->action(function (ReleaseSet $record): void {
                        try {
                            ReleaseSetPublisher::publish($record);
                            Notification::make()->title(__('admin.notification_set_published'))->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title(__('admin.notification_publish_failed', ['message' => $e->getMessage()]))->danger()->send();
                        }
                    }),

                Action::make('close')
                    ->label(fn () => __('admin.action_close'))
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(fn (ReleaseSet $record): bool => $record->status === 'open')
                    ->requiresConfirmation()
                    ->modalHeading(fn () => __('admin.modal_close_set_heading'))
                    ->modalDescription(fn () => __('admin.modal_close_set_desc'))
                    ->action(function (ReleaseSet $record): void {
                        $record->update(['status' => 'closed']);
                        Notification::make()->title(__('admin.notification_set_closed'))->success()->send();
                    }),

                Action::make('reopen')
                    ->label(fn () => __('admin.action_reopen'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (ReleaseSet $record): bool => $record->status === 'closed')
                    ->requiresConfirmation()
                    ->modalHeading(fn () => __('admin.modal_reopen_set_heading'))
                    ->modalDescription(fn () => __('admin.modal_reopen_set_desc'))
                    ->action(function (ReleaseSet $record): void {
                        try {
                            ReleaseSetPublisher::reopen($record);
                            Notification::make()->title(__('admin.notification_set_reopened'))->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title(__('admin.notification_publish_failed', ['message' => $e->getMessage()]))->danger()->send();
                        }
                    }),

                Action::make('public-link')
                    ->label(fn () => __('admin.action_public_link'))
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->url(
                        fn (ReleaseSet $record): string => route('release.show', $record->public_token),
                        shouldOpenInNewTab: true,
                    ),

                Action::make('export')
                    ->label(fn () => __('admin.action_export'))
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
