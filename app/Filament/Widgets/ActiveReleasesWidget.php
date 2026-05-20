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
            ->heading(__('admin.widget_active_release_sets'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('admin.col_name'))->searchable(),
                Tables\Columns\TextColumn::make('end_at')->label(__('admin.col_closes'))->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('form_releases_count')->label(__('admin.col_forms')),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color('success'),
            ])
            ->actions([
                Action::make('open-link')
                    ->label(__('admin.action_public_link'))
                    ->url(
                        fn (ReleaseSet $record) => route('release.show', $record->public_token),
                        shouldOpenInNewTab: true
                    )
                    ->icon('heroicon-o-link'),
            ]);
    }
}
