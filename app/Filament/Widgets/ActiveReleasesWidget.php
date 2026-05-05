<?php

namespace App\Filament\Widgets;

use App\Models\FormRelease;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;

class ActiveReleasesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FormRelease::query()
                    ->where('status', 'open')
                    ->with(['form', 'submissions'])
            )
            ->heading('Active Releases')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('form.title')->label('Form'),
                Tables\Columns\TextColumn::make('end_at')->label('Closes')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Submissions'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color('success'),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (FormRelease $record) => route('filament.admin.resources.form-releases.edit', $record))
                    ->icon('heroicon-o-eye'),

                Action::make('open-link')
                    ->label('Public Link')
                    ->url(
                        fn (FormRelease $record) => route('release.show', $record->public_token),
                        shouldOpenInNewTab: true
                    )
                    ->icon('heroicon-o-link'),
            ]);
    }
}
