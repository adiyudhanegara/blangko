<?php

namespace App\Filament\Widgets;

use App\Models\ReleaseSet;
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
                ReleaseSet::query()
                    ->where('status', 'open')
                    ->withCount('formReleases')
            )
            ->heading('Active Release Sets')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('end_at')->label('Closes')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('form_releases_count')->label('Forms'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color('success'),
            ])
            ->actions([
                Action::make('open-link')
                    ->label('Public Link')
                    ->url(
                        fn (ReleaseSet $record) => route('release.show', $record->public_token),
                        shouldOpenInNewTab: true
                    )
                    ->icon('heroicon-o-link'),
            ]);
    }
}
