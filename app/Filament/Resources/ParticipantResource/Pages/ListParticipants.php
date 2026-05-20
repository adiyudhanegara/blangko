<?php

namespace App\Filament\Resources\ParticipantResource\Pages;

use App\Filament\Resources\ParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParticipants extends ListRecords
{
    protected static string $resource = ParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label(fn () => __('admin.import_from_excel'))
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->url(ParticipantResource::getUrl('import')),
            Actions\CreateAction::make(),
        ];
    }
}
