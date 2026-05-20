<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipantResource\Pages;
use App\Models\Division;
use App\Models\Participant;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav_participants');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav_participants');
    }

    public static function getModelLabel(): string
    {
        return __('admin.model_participant');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->email()
                ->nullable()
                ->maxLength(255),

            Forms\Components\TextInput::make('phone')
                ->tel()
                ->nullable()
                ->maxLength(50),

            Forms\Components\Select::make('division_id')
                ->label(fn () => __('admin.field_division'))
                ->relationship('division', 'name')
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\Select::make('status')
                ->options(fn () => [
                    'active'   => __('admin.status_active'),
                    'inactive' => __('admin.status_inactive'),
                ])
                ->default('active')
                ->required(),

            Forms\Components\TextInput::make('nip')
                ->label(fn () => __('admin.field_nip'))
                ->nullable()
                ->unique(ignoreRecord: true)
                ->maxLength(50),

            Forms\Components\TextInput::make('position')
                ->label(fn () => __('admin.field_position'))
                ->nullable()
                ->maxLength(255),

            Forms\Components\TextInput::make('identifier')
                ->label(fn () => __('admin.field_identifier'))
                ->nullable()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('admin.pcol_name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nip')->label(__('admin.nip_short'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('position')->label(__('admin.position_short'))->sortable()->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('email')->label(__('admin.pcol_email'))->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('phone')->label(__('admin.pcol_phone'))->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('division.name')->label(__('admin.col_division'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'inactive' => 'danger',
                        default    => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.col_created_at'))
                    ->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('division_id')
                    ->label(__('admin.col_division'))
                    ->relationship('division', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options(fn () => [
                        'active'   => __('admin.status_active'),
                        'inactive' => __('admin.status_inactive'),
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    BulkAction::make('assignDivision')
                        ->label(fn () => __('admin.bulk_assign_division'))
                        ->icon('heroicon-o-building-office')
                        ->form([
                            Forms\Components\Select::make('division_id')
                                ->label(fn () => __('admin.field_division'))
                                ->options(Division::query()->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(
                                fn (Participant $record) => $record->update(['division_id' => $data['division_id']])
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListParticipants::route('/'),
            'create' => Pages\CreateParticipant::route('/create'),
            'edit'   => Pages\EditParticipant::route('/{record}/edit'),
            'import' => Pages\ImportParticipants::route('/import'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }
}
