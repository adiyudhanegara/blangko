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
            ->heading(__('admin.widget_pending_assignments'))
            ->description(__('admin.widget_pending_assignments_desc'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.pcol_name')),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.pcol_email')),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('admin.pcol_phone')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label(__('admin.col_registered'))
                    ->sortable(),
            ])
            ->actions([
                Action::make('assign')
                    ->label(__('admin.bulk_assign_division'))
                    ->url(fn (Participant $record) => route('filament.admin.resources.participants.edit', $record))
                    ->icon('heroicon-o-pencil'),
            ]);
    }
}
