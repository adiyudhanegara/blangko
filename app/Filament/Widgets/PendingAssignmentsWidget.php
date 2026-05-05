<?php

namespace App\Filament\Widgets;

use App\Models\Participant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;

class PendingAssignmentsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Participant::query()->whereNull('division_id'))
            ->heading('Pending Division Assignments')
            ->description('Participants who have not been assigned to a division yet.')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Registered')
                    ->sortable(),
            ])
            ->actions([
                Action::make('assign')
                    ->label('Assign Division')
                    ->url(fn (Participant $record) => route('filament.admin.resources.participants.edit', $record))
                    ->icon('heroicon-o-pencil'),
            ]);
    }
}
