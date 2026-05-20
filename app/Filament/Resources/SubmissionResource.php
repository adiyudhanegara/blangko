<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubmissionResource\Pages;
use App\Models\Submission;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav_form_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav_submissions');
    }

    public static function getModelLabel(): string
    {
        return __('admin.model_submission');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('participant.name')
                    ->label(__('admin.col_participant'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('participant.division.name')
                    ->label(__('admin.col_division'))
                    ->searchable()
                    ->sortable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('formRelease.releaseSet.name')
                    ->label(__('admin.col_release_set'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('formRelease.form.title')
                    ->label(__('admin.col_form'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'success',
                        'draft'     => 'warning',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('answers_count')
                    ->counts('answers')
                    ->label(__('admin.col_answers'))
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label(__('admin.col_submitted'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('last_edited_at')
                    ->label(__('admin.col_last_edited'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(fn () => [
                        'draft'     => __('admin.status_draft'),
                        'submitted' => __('admin.col_submitted'),
                    ]),

                Tables\Filters\SelectFilter::make('form_release_id')
                    ->label(__('admin.col_form'))
                    ->options(fn () => \App\Models\FormRelease::with('form')
                        ->get()
                        ->mapWithKeys(fn ($r) => [
                            $r->id => $r->form?->title ?? __('admin.release_fallback', ['id' => $r->id]),
                        ])
                        ->toArray()
                    )
                    ->searchable(),

                Tables\Filters\SelectFilter::make('division')
                    ->label(__('admin.col_division'))
                    ->relationship('participant.division', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->actions([
                Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Submission $record): string => Pages\ViewSubmission::getUrl(['record' => $record->id])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubmissions::route('/'),
            'view'  => Pages\ViewSubmission::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
